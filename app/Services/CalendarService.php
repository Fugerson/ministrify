<?php

namespace App\Services;

use App\Models\Church;
use App\Models\Event;
use App\Models\Ministry;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CalendarService
{
    /**
     * Export events to iCal format (.ics)
     */
    public function exportToIcal(Church $church, ?int $ministryId = null, ?Carbon $startDate = null, ?Carbon $endDate = null): string
    {
        $query = Event::where('church_id', $church->id)
            ->with('ministry');

        if ($ministryId) {
            $query->where('ministry_id', $ministryId);
        }

        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }

        $events = $query->orderBy('date')->get();

        return $this->generateIcalContent($events, $church);
    }

    /**
     * Generate iCal content from events
     */
    private function generateIcalContent(Collection $events, Church $church): string
    {
        $ical = "BEGIN:VCALENDAR\r\n";
        $ical .= "VERSION:2.0\r\n";
        $ical .= "PRODID:-//Ministrify//{$church->name}//UK\r\n";
        $ical .= "CALSCALE:GREGORIAN\r\n";
        $ical .= "METHOD:PUBLISH\r\n";
        $ical .= "X-WR-CALNAME:" . $this->escapeIcalText($church->name) . "\r\n";
        $ical .= "X-WR-TIMEZONE:Europe/Kyiv\r\n";

        // Add timezone definition
        $ical .= $this->getTimezoneComponent();

        foreach ($events as $event) {
            $ical .= $this->eventToVevent($event, $church);
        }

        $ical .= "END:VCALENDAR\r\n";

        return $ical;
    }

    /**
     * Convert an event to VEVENT component
     */
    private function eventToVevent(Event $event, Church $church): string
    {
        $uid = $event->id . '@' . Str::slug($church->name) . '.ministrify';
        $dtstart = $this->formatIcalDateTime($event->date, $event->time);

        // Handle all-day events
        if (!$event->time) {
            return $this->eventToVdayEvent($event, $church);
        }

        // Use end_time if available, otherwise assume 1 hour duration
        if ($event->end_time) {
            $endDate = $event->end_date ?? $event->date;
            $endTime = Carbon::parse($endDate->format('Y-m-d') . ' ' . $event->end_time->format('H:i'));
        } else {
            $endTime = Carbon::parse($event->date->format('Y-m-d') . ' ' . $event->time->format('H:i'))
                ->addHour();
        }
        $dtend = $this->formatIcalDateTime($endTime, $endTime);

        $description = '';
        if ($event->ministry) {
            $description .= "Служіння: " . $event->ministry->name . "\\n";
        }
        if ($event->notes) {
            $description .= $event->notes;
        }

        $vevent = "BEGIN:VEVENT\r\n";
        $vevent .= "UID:{$uid}\r\n";
        $vevent .= "DTSTAMP:" . gmdate('Ymd\THis\Z') . "\r\n";
        $vevent .= "DTSTART;TZID=Europe/Kyiv:{$dtstart}\r\n";
        $vevent .= "DTEND;TZID=Europe/Kyiv:{$dtend}\r\n";
        $vevent .= "SUMMARY:" . $this->escapeIcalText($event->title) . "\r\n";

        if ($description) {
            $vevent .= "DESCRIPTION:" . $this->escapeIcalText($description) . "\r\n";
        }

        if ($event->location) {
            $vevent .= "LOCATION:" . $this->escapeIcalText($event->location) . "\r\n";
        }

        if ($event->ministry) {
            $vevent .= "CATEGORIES:" . $this->escapeIcalText($event->ministry->name) . "\r\n";
        }

        $vevent .= "STATUS:CONFIRMED\r\n";
        $vevent .= "TRANSP:OPAQUE\r\n";
        $vevent .= "END:VEVENT\r\n";

        return $vevent;
    }

    /**
     * Generate VEVENT for all-day event
     */
    private function eventToVdayEvent(Event $event, Church $church): string
    {
        $uid = $event->id . '@' . Str::slug($church->name) . '.ministrify';

        $description = '';
        if ($event->ministry) {
            $description .= "Служіння: " . $event->ministry->name . "\\n";
        }
        if ($event->notes) {
            $description .= $event->notes;
        }

        $vevent = "BEGIN:VEVENT\r\n";
        $vevent .= "UID:{$uid}\r\n";
        $vevent .= "DTSTART;VALUE=DATE:" . $event->date->format('Ymd') . "\r\n";
        $vevent .= "DTEND;VALUE=DATE:" . $event->date->copy()->addDay()->format('Ymd') . "\r\n";
        $vevent .= "SUMMARY:" . $this->escapeIcalText($event->title) . "\r\n";

        if ($description) {
            $vevent .= "DESCRIPTION:" . $this->escapeIcalText($description) . "\r\n";
        }
        if ($event->location) {
            $vevent .= "LOCATION:" . $this->escapeIcalText($event->location) . "\r\n";
        }
        if ($event->ministry) {
            $vevent .= "CATEGORIES:" . $this->escapeIcalText($event->ministry->name) . "\r\n";
        }

        $vevent .= "STATUS:CONFIRMED\r\n";
        $vevent .= "TRANSP:TRANSPARENT\r\n";
        $vevent .= "END:VEVENT\r\n";

        return $vevent;
    }

    /**
     * Format datetime for iCal
     */
    private function formatIcalDateTime($date, $time): string
    {
        $datetime = Carbon::parse($date->format('Y-m-d') . ' ' . $time->format('H:i'));
        return $datetime->format('Ymd\THis');
    }

    /**
     * Escape text for iCal format
     */
    private function escapeIcalText(string $text): string
    {
        $text = str_replace('\\', '\\\\', $text);
        $text = str_replace("\n", '\\n', $text);
        $text = str_replace("\r", '', $text);
        $text = str_replace(',', '\\,', $text);
        $text = str_replace(';', '\\;', $text);
        return $text;
    }

    /**
     * Get timezone component for Ukraine
     */
    private function getTimezoneComponent(): string
    {
        return "BEGIN:VTIMEZONE\r\n" .
               "TZID:Europe/Kyiv\r\n" .
               "BEGIN:STANDARD\r\n" .
               "DTSTART:19700101T000000\r\n" .
               "TZOFFSETFROM:+0200\r\n" .
               "TZOFFSETTO:+0200\r\n" .
               "TZNAME:EET\r\n" .
               "END:STANDARD\r\n" .
               "END:VTIMEZONE\r\n";
    }

    /**
     * Import events from iCal file
     */
    public function importFromIcal(UploadedFile $file, Church $church, Ministry $ministry): array
    {
        $content = file_get_contents($file->getRealPath());

        return $this->parseIcalContent($content, $church, $ministry);
    }

    /**
     * Import events from iCal content string
     */
    public function importFromIcalContent(string $content, Church $church, Ministry $ministry): array
    {
        return $this->parseIcalContent($content, $church, $ministry);
    }

    /**
     * Parse iCal content and create events
     */
    private function parseIcalContent(string $content, Church $church, Ministry $ministry, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $imported = [];
        $skipped = [];
        $errors = [];

        // Normalize line endings
        $content = str_replace(["\r\n", "\r"], "\n", $content);

        // Unfold long lines (RFC 5545)
        $content = preg_replace("/\n[ \t]/", "", $content);

        // Extract VEVENT blocks
        preg_match_all('/BEGIN:VEVENT(.+?)END:VEVENT/s', $content, $matches);

        foreach ($matches[1] as $veventContent) {
            try {
                $eventData = $this->parseVevent($veventContent);

                if (!$eventData) {
                    $skipped[] = 'Подія без дати або назви';
                    continue;
                }

                // Apply date filters
                if ($startDate && $eventData['date']->lt($startDate)) {
                    continue;
                }
                if ($endDate && $eventData['date']->gt($endDate)) {
                    continue;
                }

                // Check for duplicates
                $exists = Event::where('church_id', $church->id)
                    ->where('title', $eventData['title'])
                    ->where('date', $eventData['date'])
                    ->exists();

                if ($exists) {
                    $skipped[] = $eventData['title'] . ' (' . $eventData['date']->format('d.m.Y') . ') - вже існує';
                    continue;
                }

                // Create event
                $event = Event::create([
                    'church_id' => $church->id,
                    'ministry_id' => $ministry->id,
                    'title' => $eventData['title'],
                    'date' => $eventData['date'],
                    'time' => $eventData['time'],
                    'notes' => $eventData['description'] ?? null,
                    'location' => $eventData['location'] ?? null,
                ]);

                $imported[] = $event;

            } catch (\Exception $e) {
                $errors[] = 'Помилка парсингу події: ' . $e->getMessage();
            }
        }

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors,
            'total_imported' => count($imported),
            'total_skipped' => count($skipped),
            'total_errors' => count($errors),
        ];
    }

    /**
     * Parse a single VEVENT block
     */
    private function parseVevent(string $content): ?array
    {
        $data = [];

        // Parse SUMMARY (title)
        if (preg_match('/SUMMARY:(.+)/i', $content, $match)) {
            $data['title'] = $this->unescapeIcalText(trim($match[1]));
        }

        // Parse DTSTART
        if (preg_match('/DTSTART[^:]*:(\d{8}T?\d{0,6}Z?)/i', $content, $match)) {
            $dtstart = $this->parseIcalDate($match[1]);
            if ($dtstart) {
                $data['date'] = $dtstart->copy()->startOfDay();
                $data['time'] = $dtstart;
            }
        }

        // Parse DESCRIPTION
        if (preg_match('/DESCRIPTION:(.+?)(?=\n[A-Z]|$)/si', $content, $match)) {
            $data['description'] = $this->unescapeIcalText(trim($match[1]));
        }

        // Parse LOCATION
        if (preg_match('/LOCATION:(.+)/i', $content, $match)) {
            $data['location'] = $this->unescapeIcalText(trim($match[1]));
        }

        // Validate required fields
        if (empty($data['title']) || empty($data['date'])) {
            return null;
        }

        // All-day events: keep time as null
        if (!isset($data['time']) || !$data['time']) {
            $data['time'] = null;
        }

        return $data;
    }

    /**
     * Parse iCal date format
     */
    private function parseIcalDate(string $dateStr): ?Carbon
    {
        // Remove any timezone suffix
        $dateStr = rtrim($dateStr, 'Z');

        try {
            // Full datetime: 20231225T143000
            if (strlen($dateStr) >= 15) {
                return Carbon::createFromFormat('Ymd\THis', $dateStr);
            }
            // Date with time without seconds: 20231225T1430
            if (strlen($dateStr) >= 13) {
                return Carbon::createFromFormat('Ymd\THi', $dateStr);
            }
            // Date only: 20231225 (all-day event)
            if (strlen($dateStr) >= 8) {
                return Carbon::createFromFormat('Ymd', substr($dateStr, 0, 8))->startOfDay();
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }

    /**
     * Unescape iCal text
     */
    private function unescapeIcalText(string $text): string
    {
        $text = str_replace('\\n', "\n", $text);
        $text = str_replace('\\,', ',', $text);
        $text = str_replace('\\;', ';', $text);
        $text = str_replace('\\\\', '\\', $text);
        return $text;
    }

    /**
     * Generate Google Calendar import URL
     */
    public function getGoogleCalendarUrl(Event $event): string
    {
        if (!$event->time) {
            $startDate = Carbon::parse($event->date->format('Y-m-d'));
            $endDate = $startDate->copy()->addDay();
        } else {
            $startDate = Carbon::parse($event->date->format('Y-m-d') . ' ' . $event->time->format('H:i'));
            $endDate = $startDate->copy()->addHour();
        }

        $params = [
            'action' => 'TEMPLATE',
            'text' => $event->title,
            'dates' => $startDate->format('Ymd\THis') . '/' . $endDate->format('Ymd\THis'),
            'details' => $event->notes ?? '',
            'location' => $event->location ?? '',
        ];

        if ($event->ministry) {
            $params['details'] = "Служіння: " . $event->ministry->name . "\n" . ($event->notes ?? '');
        }

        return 'https://calendar.google.com/calendar/render?' . http_build_query($params);
    }

    /**
     * Generate subscription URL for calendar feed
     */
    public function getSubscriptionUrl(Church $church, string $token): string
    {
        return route('calendar.feed', ['token' => $token]);
    }

}
