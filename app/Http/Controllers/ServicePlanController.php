<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Person;
use App\Models\ServicePlanItem;
use App\Rules\BelongsToChurch;
use App\Services\TelegramService;
use Illuminate\Http\Request;

class ServicePlanController extends Controller
{
    /**
     * Store a new plan item
     */
    public function store(Request $request, Event $event)
    {
        $this->authorize('managePlan', $event);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'nullable|string|in:' . implode(',', array_keys(ServicePlanItem::typeLabels())),
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'responsible_id' => ['nullable', 'exists:people,id', new BelongsToChurch(Person::class)],
            'responsible_names' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'song_id' => 'nullable|exists:songs,id',
        ]);

        // Get next sort order
        $maxOrder = $event->planItems()->max('sort_order') ?? 0;
        $validated['sort_order'] = $maxOrder + 1;

        $item = $event->planItems()->create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'item' => $item->load(['responsible', 'song']),
                'message' => 'Пункт плану додано',
            ]);
        }

        return back()->with('success', 'Пункт плану додано');
    }

    /**
     * Update a plan item
     */
    public function update(Request $request, Event $event, ServicePlanItem $item)
    {
        $this->authorize('managePlan', $event);

        // Verify item belongs to event
        if ($item->event_id !== $event->id) {
            abort(403);
        }

        // Support partial updates - all fields optional
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'type' => 'nullable|string|in:' . implode(',', array_keys(ServicePlanItem::typeLabels())),
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'responsible_id' => ['nullable', 'exists:people,id', new BelongsToChurch(Person::class)],
            'responsible_names' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'nullable|string|in:planned,confirmed,declined,completed',
            'song_id' => 'nullable|exists:songs,id',
        ]);

        // Convert empty strings to null
        if (array_key_exists('responsible_id', $validated) && empty($validated['responsible_id'])) {
            $validated['responsible_id'] = null;
        }
        if (array_key_exists('responsible_names', $validated) && empty($validated['responsible_names'])) {
            $validated['responsible_names'] = null;
        }

        $item->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'item' => $item->fresh()->load('responsible'),
                'message' => 'Пункт плану оновлено',
            ]);
        }

        return back()->with('success', 'Пункт плану оновлено');
    }

    /**
     * Delete a plan item
     */
    public function destroy(Request $request, Event $event, ServicePlanItem $item)
    {
        $this->authorize('managePlan', $event);

        // Verify item belongs to event
        if ($item->event_id !== $event->id) {
            abort(403);
        }

        $item->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Пункт плану видалено',
            ]);
        }

        return back()->with('success', 'Пункт плану видалено');
    }

    /**
     * Reorder plan items via AJAX
     */
    public function reorder(Request $request, Event $event)
    {
        $this->authorize('managePlan', $event);

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:service_plan_items,id',
            'items.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($validated['items'] as $itemData) {
            ServicePlanItem::where('id', $itemData['id'])
                ->where('event_id', $event->id)
                ->update(['sort_order' => $itemData['sort_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Порядок оновлено',
        ]);
    }

    /**
     * Duplicate plan from another event
     */
    public function duplicate(Request $request, Event $event, Event $source)
    {
        $this->authorize('managePlan', $event);

        // Verify source belongs to the same church
        if ($source->church_id !== $event->church_id) {
            abort(404);
        }

        // Verify source is a service with plan
        if (!$source->is_service || !$source->planItems()->exists()) {
            return back()->with('error', 'Джерело не має плану служіння');
        }

        // Clear existing items if any
        if ($request->get('replace', false)) {
            $event->planItems()->delete();
        }

        // Copy items
        $event->duplicatePlanFrom($source);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'План скопійовано',
                'redirect' => route('events.plan.index', $event),
            ]);
        }

        return redirect()->route('events.plan.index', $event)
            ->with('success', 'План скопійовано з ' . $source->title . ' (' . $source->date->format('d.m.Y') . ')');
    }

    /**
     * Quick add common items
     */
    public function quickAdd(Request $request, Event $event)
    {
        $this->authorize('managePlan', $event);

        $validated = $request->validate([
            'type' => 'required|string|in:' . implode(',', array_keys(ServicePlanItem::typeLabels())),
        ]);

        $typeLabels = ServicePlanItem::typeLabels();
        $title = $typeLabels[$validated['type']];

        // Get service start time or default to 10:00
        $startTime = $event->time ? $event->time->format('H:i') : '10:00';

        // Get last item's end time for consecutive scheduling
        $lastItem = $event->planItems()->orderByDesc('sort_order')->first();
        if ($lastItem && $lastItem->end_time) {
            $startTime = \Carbon\Carbon::parse($lastItem->end_time)->format('H:i');
        }

        $duration = ServicePlanItem::getDefaultDuration($validated['type']);
        $endTime = \Carbon\Carbon::parse($startTime)->addMinutes($duration)->format('H:i');

        $maxOrder = $event->planItems()->max('sort_order') ?? 0;

        $item = $event->planItems()->create([
            'title' => $title,
            'type' => $validated['type'],
            'start_time' => $startTime,
            'end_time' => $endTime,
            'sort_order' => $maxOrder + 1,
            'status' => 'planned',
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'item' => $item->load('responsible'),
                'message' => 'Пункт додано',
            ]);
        }

        return back()->with('success', 'Пункт додано');
    }

    /**
     * Print-friendly version of the plan
     */
    public function print(Event $event)
    {
        $this->authorize('managePlan', $event);

        $event->load(['planItems.responsible', 'ministry', 'church', 'assignments.person', 'assignments.position']);

        return view('events.plan.print', compact('event'));
    }

    /**
     * Update item status via AJAX
     */
    public function updateStatus(Request $request, Event $event, ServicePlanItem $item)
    {
        $this->authorize('managePlan', $event);

        if ($item->event_id !== $event->id) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|string|in:planned,confirmed,declined,completed',
        ]);

        $item->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'status' => $item->status,
            'status_label' => $item->status_label,
        ]);
    }

    /**
     * Get item data for editing via AJAX
     */
    public function itemData(Event $event, ServicePlanItem $item)
    {
        $this->authorize('managePlan', $event);

        if ($item->event_id !== $event->id) {
            abort(403);
        }

        return response()->json([
            'type' => $item->type ?? '',
            'title' => $item->title,
            'start_time' => $item->start_time ? \Carbon\Carbon::parse($item->start_time)->format('H:i') : '',
            'end_time' => $item->end_time ? \Carbon\Carbon::parse($item->end_time)->format('H:i') : '',
            'responsible_id' => $item->responsible_id ?? '',
            'responsible_names' => $item->responsible_names ?? '',
            'description' => $item->description ?? '',
            'notes' => $item->notes ?? '',
            'status' => $item->status,
        ]);
    }

    /**
     * Apply a predefined template
     */
    public function applyTemplate(Request $request, Event $event)
    {
        $this->authorize('managePlan', $event);

        $validated = $request->validate([
            'template' => 'required|string|in:sunday,prayer,communion,baptism',
        ]);

        $templates = [
            'sunday' => [
                ['type' => 'worship', 'title' => 'Прославлення', 'duration' => 30],
                ['type' => 'announcement', 'title' => 'Оголошення', 'duration' => 10],
                ['type' => 'offering', 'title' => 'Пожертва', 'duration' => 5],
                ['type' => 'sermon', 'title' => 'Проповідь', 'duration' => 40],
                ['type' => 'prayer', 'title' => 'Заключна молитва', 'duration' => 5],
            ],
            'prayer' => [
                ['type' => 'worship', 'title' => 'Прославлення', 'duration' => 20],
                ['type' => 'prayer', 'title' => 'Молитва', 'duration' => 30],
                ['type' => 'testimony', 'title' => 'Свідчення', 'duration' => 15],
                ['type' => 'prayer', 'title' => 'Спільна молитва', 'duration' => 20],
            ],
            'communion' => [
                ['type' => 'worship', 'title' => 'Прославлення', 'duration' => 25],
                ['type' => 'announcement', 'title' => 'Оголошення', 'duration' => 10],
                ['type' => 'offering', 'title' => 'Пожертва', 'duration' => 5],
                ['type' => 'sermon', 'title' => 'Проповідь', 'duration' => 35],
                ['type' => 'communion', 'title' => 'Причастя', 'duration' => 15],
                ['type' => 'prayer', 'title' => 'Заключна молитва', 'duration' => 5],
            ],
            'baptism' => [
                ['type' => 'worship', 'title' => 'Прославлення', 'duration' => 20],
                ['type' => 'sermon', 'title' => 'Слово про хрещення', 'duration' => 20],
                ['type' => 'baptism', 'title' => 'Хрещення', 'duration' => 30],
                ['type' => 'testimony', 'title' => 'Свідчення', 'duration' => 15],
                ['type' => 'worship', 'title' => 'Прославлення', 'duration' => 15],
                ['type' => 'prayer', 'title' => 'Молитва благословення', 'duration' => 10],
            ],
        ];

        $items = $templates[$validated['template']];
        $startTime = $event->time ? $event->time->format('H:i') : '10:00';
        $currentTime = \Carbon\Carbon::parse($startTime);
        $maxOrder = $event->planItems()->max('sort_order') ?? 0;

        foreach ($items as $index => $item) {
            $endTime = $currentTime->copy()->addMinutes($item['duration']);

            $event->planItems()->create([
                'title' => $item['title'],
                'type' => $item['type'],
                'start_time' => $currentTime->format('H:i'),
                'end_time' => $endTime->format('H:i'),
                'sort_order' => $maxOrder + $index + 1,
                'status' => 'planned',
            ]);

            $currentTime = $endTime;
        }

        return response()->json([
            'success' => true,
            'message' => 'Шаблон застосовано',
            'count' => count($items),
        ]);
    }

    /**
     * Bulk add multiple types at once
     */
    public function bulkAdd(Request $request, Event $event)
    {
        $this->authorize('managePlan', $event);

        $validated = $request->validate([
            'types' => 'required|array|min:1',
            'types.*' => 'string|in:' . implode(',', array_keys(ServicePlanItem::typeLabels())),
        ]);

        $typeLabels = ServicePlanItem::typeLabels();
        // Get last item's end time or event start time
        $lastItem = $event->planItems()->orderByDesc('sort_order')->first();
        $startTime = $event->time ? $event->time->format('H:i') : '10:00';

        if ($lastItem && $lastItem->end_time) {
            $startTime = \Carbon\Carbon::parse($lastItem->end_time)->format('H:i');
        }

        $currentTime = \Carbon\Carbon::parse($startTime);
        $maxOrder = $event->planItems()->max('sort_order') ?? 0;

        foreach ($validated['types'] as $index => $type) {
            $duration = ServicePlanItem::getDefaultDuration($type);
            $endTime = $currentTime->copy()->addMinutes($duration);

            $event->planItems()->create([
                'title' => $typeLabels[$type],
                'type' => $type,
                'start_time' => $currentTime->format('H:i'),
                'end_time' => $endTime->format('H:i'),
                'sort_order' => $maxOrder + $index + 1,
                'status' => 'planned',
            ]);

            $currentTime = $endTime;
        }

        return response()->json([
            'success' => true,
            'message' => 'Пункти додано',
            'count' => count($validated['types']),
        ]);
    }

    /**
     * Parse text and create plan items
     */
    public function parseText(Request $request, Event $event)
    {
        $this->authorize('managePlan', $event);

        $validated = $request->validate([
            'text' => 'required|string',
        ]);

        $lines = preg_split('/\r?\n/', trim($validated['text']));
        $typeLabels = ServicePlanItem::typeLabels();
        $typeMap = array_flip($typeLabels); // Ukrainian label => type key

        // Also add some common variations
        $typeAliases = [
            'пісня' => 'worship',
            'пісні' => 'worship',
            'хвала' => 'worship',
            'слово' => 'sermon',
            'привітання' => 'announcement',
            'вітання' => 'announcement',
            'хліболамання' => 'communion',
            'вечеря' => 'communion',
        ];

        $maxOrder = $event->planItems()->max('sort_order') ?? 0;
        $createdCount = 0;

        // Get starting time
        $lastItem = $event->planItems()->orderByDesc('sort_order')->first();
        $defaultStartTime = $event->time ? $event->time->format('H:i') : '10:00';
        if ($lastItem && $lastItem->end_time) {
            $defaultStartTime = \Carbon\Carbon::parse($lastItem->end_time)->format('H:i');
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Parse line format: [HH:MM] Title [(XX хв)] [- Responsible]
            $startTime = null;
            $duration = null;
            $responsible = null;
            $title = $line;
            $detectedType = null;

            // Extract time at the beginning: "10:00 ..."
            if (preg_match('/^(\d{1,2}:\d{2})\s+(.+)$/', $line, $matches)) {
                $startTime = $matches[1];
                $title = $matches[2];
            }

            // Extract duration: "... (30 хв)" or "... (30хв)"
            if (preg_match('/\((\d+)\s*хв\.?\)/', $title, $matches)) {
                $duration = (int)$matches[1];
                $title = trim(preg_replace('/\(\d+\s*хв\.?\)/', '', $title));
            }

            // Extract responsible: "... - Name"
            if (preg_match('/^(.+?)\s+-\s+(.+)$/', $title, $matches)) {
                $title = trim($matches[1]);
                $responsible = trim($matches[2]);
            }

            // Try to detect type from title
            $lowerTitle = mb_strtolower($title);
            foreach ($typeAliases as $alias => $type) {
                if (mb_strpos($lowerTitle, $alias) !== false) {
                    $detectedType = $type;
                    break;
                }
            }
            if (!$detectedType) {
                foreach ($typeMap as $label => $type) {
                    if (mb_strpos($lowerTitle, mb_strtolower($label)) !== false) {
                        $detectedType = $type;
                        break;
                    }
                }
            }

            // Calculate times
            $itemStartTime = $startTime ?: $defaultStartTime;
            $itemEndTime = null;
            if ($duration) {
                $itemEndTime = \Carbon\Carbon::parse($itemStartTime)->addMinutes($duration)->format('H:i');
                $defaultStartTime = $itemEndTime; // Next item starts after this one
            } elseif ($startTime) {
                $defaultStartTime = $startTime; // Use this time for reference
            }

            $maxOrder++;
            $event->planItems()->create([
                'title' => $title,
                'type' => $detectedType,
                'start_time' => $itemStartTime,
                'end_time' => $itemEndTime,
                'responsible_names' => $responsible,
                'sort_order' => $maxOrder,
                'status' => 'planned',
            ]);

            $createdCount++;
        }

        return response()->json([
            'success' => true,
            'message' => "Додано {$createdCount} пунктів",
            'count' => $createdCount,
        ]);
    }

    /**
     * Send Telegram notification to responsible person
     */
    public function sendNotification(Request $request, Event $event, ServicePlanItem $item)
    {
        $this->authorize('managePlan', $event);

        if ($item->event_id !== $event->id) {
            abort(403);
        }

        // Get person - either from request (specific person) or fallback to responsible_id
        $personId = $request->input('person_id') ?? $item->responsible_id;

        if (!$personId) {
            return response()->json([
                'success' => false,
                'message' => 'Немає призначеної людини',
            ], 422);
        }

        $person = Person::where('id', $personId)
            ->where('church_id', $event->church_id)
            ->first();

        if (!$person) {
            return response()->json([
                'success' => false,
                'message' => 'Людину не знайдено',
            ], 422);
        }

        if (!$person->telegram_chat_id) {
            return response()->json([
                'success' => false,
                'message' => 'У цієї людини не підключений Telegram',
            ], 422);
        }

        $church = $event->church;
        if (!$church?->isNotificationEnabled('notify_on_plan_request')) {
            return response()->json([
                'success' => false,
                'message' => 'Сповіщення про план служіння вимкнено в налаштуваннях',
            ], 422);
        }

        if (!config('services.telegram.bot_token')) {
            return response()->json([
                'success' => false,
                'message' => 'Telegram бот не налаштований для цієї церкви',
            ], 500);
        }

        try {
            $telegram = TelegramService::make();
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Telegram бот не налаштований',
            ], 500);
        }

        $timeStr = $item->start_time ? \Carbon\Carbon::parse($item->start_time)->format('H:i') : 'час уточнюється';
        $message = "📋 <b>Запит на участь</b>\n\n"
            . "📅 {$event->date->format('d.m.Y')} ({$this->getDayName($event->date)})\n"
            . "⏰ {$timeStr}\n"
            . "📝 {$item->title}\n\n"
            . "Чи можете ви взяти участь?";

        // Include person_id in callback for tracking individual responses
        $keyboard = [
            [
                ['text' => '✅ Так, зможу', 'callback_data' => "plan_confirm_{$item->id}_{$personId}"],
                ['text' => '❌ Не можу', 'callback_data' => "plan_decline_{$item->id}_{$personId}"],
            ],
        ];

        $sent = $telegram->sendMessage($person->telegram_chat_id, $message, $keyboard);

        if ($sent) {
            // Set person status to pending
            $item->setPersonStatus($personId, 'pending');

            // Log notification sent
            $this->logAuditAction('notification_sent', 'Event', $event->id, $event->title, [
                'plan_item_id' => $item->id,
                'plan_item_title' => $item->title,
                'person_id' => $personId,
                'person_name' => $person->full_name,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Запит надіслано в Telegram',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Не вдалося надіслати повідомлення',
        ], 500);
    }

    private function getDayName(\DateTime $date): string
    {
        $days = ['Неділя', 'Понеділок', 'Вівторок', 'Середа', 'Четвер', 'П\'ятниця', 'Субота'];
        return $days[$date->format('w')];
    }
}
