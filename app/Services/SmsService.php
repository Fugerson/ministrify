<?php

namespace App\Services;

use App\Models\Church;
use App\Models\Person;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected ?string $accountSid;
    protected ?string $authToken;
    protected ?string $fromNumber;

    public function __construct()
    {
        $this->accountSid = config('services.twilio.sid');
        $this->authToken = config('services.twilio.token');
        $this->fromNumber = config('services.twilio.from');
    }

    /**
     * Check if SMS service is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->accountSid)
            && !empty($this->authToken)
            && !empty($this->fromNumber);
    }

    /**
     * Send SMS to a single phone number
     */
    public function send(string $to, string $message): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'SMS service not configured',
            ];
        }

        // Normalize phone number
        $to = $this->normalizePhoneNumber($to);
        if (!$to) {
            return [
                'success' => false,
                'error' => 'Invalid phone number',
            ];
        }

        try {
            $response = Http::withBasicAuth($this->accountSid, $this->authToken)
                ->asForm()
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$this->accountSid}/Messages.json", [
                    'From' => $this->fromNumber,
                    'To' => $to,
                    'Body' => $message,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('SMS sent successfully', ['to' => $to, 'sid' => $data['sid'] ?? null]);
                return [
                    'success' => true,
                    'sid' => $data['sid'] ?? null,
                ];
            }

            Log::error('SMS send failed', ['to' => $to, 'response' => $response->body()]);
            return [
                'success' => false,
                'error' => $response->json()['message'] ?? 'Unknown error',
            ];
        } catch (\Exception $e) {
            Log::error('SMS exception', ['to' => $to, 'error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send SMS to a person
     */
    public function sendToPerson(Person $person, string $message): array
    {
        if (!$person->phone) {
            return [
                'success' => false,
                'error' => 'Person has no phone number',
            ];
        }

        return $this->send($person->phone, $message);
    }

    /**
     * Send SMS to multiple people
     */
    public function sendBulk(array $phoneNumbers, string $message): array
    {
        $results = [
            'sent' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($phoneNumbers as $phone) {
            $result = $this->send($phone, $message);
            if ($result['success']) {
                $results['sent']++;
            } else {
                $results['failed']++;
                $results['errors'][] = ['phone' => $phone, 'error' => $result['error']];
            }

            // Rate limiting - Twilio allows 1 message per second
            usleep(100000); // 100ms delay
        }

        return $results;
    }

    /**
     * Send event reminder to assigned people
     */
    public function sendEventReminder(\App\Models\Event $event): array
    {
        $message = $this->buildEventReminderMessage($event);
        $phoneNumbers = [];

        foreach ($event->assignments as $assignment) {
            if ($assignment->person && $assignment->person->phone) {
                $phoneNumbers[] = $assignment->person->phone;
            }
        }

        if (empty($phoneNumbers)) {
            return ['sent' => 0, 'failed' => 0, 'errors' => ['No phone numbers found']];
        }

        return $this->sendBulk(array_unique($phoneNumbers), $message);
    }

    /**
     * Build event reminder message
     */
    private function buildEventReminderMessage(\App\Models\Event $event): string
    {
        $date = $event->date->format('d.m.Y');
        $time = $event->time ? $event->time->format('H:i') : '';

        return "Нагадування: {$event->title} - {$date}" . ($time ? " о {$time}" : '') . ". Ministrify";
    }

    /**
     * Normalize phone number to E.164 format
     */
    private function normalizePhoneNumber(string $phone): ?string
    {
        // Remove all non-digit characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Handle Ukrainian numbers
        if (strlen($phone) === 10 && str_starts_with($phone, '0')) {
            return '+38' . $phone;
        }

        if (strlen($phone) === 12 && str_starts_with($phone, '38')) {
            return '+' . $phone;
        }

        if (strlen($phone) === 9) {
            return '+380' . $phone;
        }

        // If already has country code
        if (strlen($phone) >= 11 && strlen($phone) <= 15) {
            return '+' . $phone;
        }

        return null;
    }

    /**
     * Get SMS balance/usage from Twilio
     */
    public function getBalance(): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $response = Http::withBasicAuth($this->accountSid, $this->authToken)
                ->get("https://api.twilio.com/2010-04-01/Accounts/{$this->accountSid}/Balance.json");

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error('Failed to get SMS balance', ['error' => $e->getMessage()]);
        }

        return null;
    }
}
