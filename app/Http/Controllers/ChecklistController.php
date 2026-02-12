<?php

namespace App\Http\Controllers;

use App\Models\ChecklistTemplate;
use App\Models\ChecklistTemplateItem;
use App\Models\Event;
use App\Models\EventChecklist;
use App\Models\EventChecklistItem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ChecklistController extends Controller
{
    // Checklist Templates
    public function templates()
    {
        $church = $this->getCurrentChurch();

        $templates = ChecklistTemplate::where('church_id', $church->id)
            ->with('items')
            ->orderBy('name')
            ->get();

        return view('checklists.templates.index', compact('templates'));
    }

    public function createTemplate()
    {
        return view('checklists.templates.create');
    }

    public function storeTemplate(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.title' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
        ]);

        $church = $this->getCurrentChurch();

        $template = ChecklistTemplate::create([
            'church_id' => $church->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        foreach ($validated['items'] as $index => $item) {
            $template->items()->create([
                'title' => $item['title'],
                'description' => $item['description'] ?? null,
                'order' => $index,
            ]);
        }

        return redirect()->route('checklists.templates')
            ->with('success', 'Шаблон створено успішно.');
    }

    public function editTemplate(ChecklistTemplate $template)
    {
        $this->authorizeChurchResource($template);

        $template->load('items');

        return view('checklists.templates.edit', compact('template'));
    }

    public function updateTemplate(Request $request, ChecklistTemplate $template)
    {
        $this->authorizeChurchResource($template);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|integer',
            'items.*.title' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
        ]);

        $template->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        // Get existing item IDs
        $existingIds = collect($validated['items'])
            ->pluck('id')
            ->filter()
            ->toArray();

        // Delete removed items
        $template->items()->whereNotIn('id', $existingIds)->delete();

        // Update/create items
        foreach ($validated['items'] as $index => $itemData) {
            if (isset($itemData['id'])) {
                $template->items()->where('id', $itemData['id'])->update([
                    'title' => $itemData['title'],
                    'description' => $itemData['description'] ?? null,
                    'order' => $index,
                ]);
            } else {
                $template->items()->create([
                    'title' => $itemData['title'],
                    'description' => $itemData['description'] ?? null,
                    'order' => $index,
                ]);
            }
        }

        return redirect()->route('checklists.templates')
            ->with('success', 'Шаблон оновлено успішно.');
    }

    public function destroyTemplate(ChecklistTemplate $template)
    {
        $this->authorizeChurchResource($template);

        $template->delete();

        return redirect()->route('checklists.templates')->with('success', 'Шаблон видалено.');
    }

    // Event Checklists
    public function createForEvent(Request $request, Event $event)
    {
        $church = $this->getCurrentChurch();

        if ($event->church_id !== $church->id) {
            abort(403);
        }

        $validated = $request->validate([
            'template_id' => 'nullable|exists:checklist_templates,id',
        ]);

        // Verify template belongs to church
        if (!empty($validated['template_id'])) {
            $template = ChecklistTemplate::with('items')->find($validated['template_id']);
            if (!$template || $template->church_id !== $church->id) {
                abort(403);
            }
        }

        // Create checklist for event
        $checklist = EventChecklist::create([
            'event_id' => $event->id,
            'checklist_template_id' => $validated['template_id'] ?? null,
        ]);

        // If template selected, copy items from template
        if (!empty($validated['template_id'])) {
            foreach ($template->items as $item) {
                $checklist->items()->create([
                    'title' => $item->title,
                    'description' => $item->description,
                    'order' => $item->order,
                ]);
            }
        }

        // Log checklist creation
        $this->logAuditAction('checklist_created', 'Event', $event->id, $event->title, [
            'checklist_id' => $checklist->id,
            'template_id' => $validated['template_id'] ?? null,
            'items_count' => $checklist->items->count(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'checklist' => [
                    'id' => $checklist->id,
                    'items' => $checklist->items->map(fn($i) => [
                        'id' => $i->id,
                        'title' => $i->title,
                        'description' => $i->description,
                        'is_completed' => $i->is_completed,
                    ]),
                ],
            ]);
        }

        return redirect()->route('events.show', $event)
            ->with('success', 'Чеклист додано до події.');
    }

    public function addItem(Request $request, EventChecklist $checklist)
    {
        $this->authorizeEventChecklist($checklist);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => ['nullable', Rule::exists('people', 'id')->where('church_id', $this->getCurrentChurch()->id)],
        ]);

        $maxOrder = $checklist->items()->max('order') ?? -1;

        $item = $checklist->items()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'assigned_to' => $validated['assigned_to'] ?? null,
            'order' => $maxOrder + 1,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'item' => [
                    'id' => $item->id,
                    'title' => $item->title,
                    'description' => $item->description,
                    'is_completed' => false,
                ],
            ]);
        }

        return back()->with('success', 'Пункт додано.');
    }

    public function toggleItem(EventChecklistItem $item)
    {
        $this->authorizeChecklistItem($item);

        $item->update([
            'is_completed' => !$item->is_completed,
            'completed_by' => $item->is_completed ? null : auth()->id(),
            'completed_at' => $item->is_completed ? null : now(),
        ]);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_completed' => $item->is_completed,
                'progress' => $item->eventChecklist->progress,
            ]);
        }

        return back();
    }

    public function updateItem(Request $request, EventChecklistItem $item)
    {
        $this->authorizeChecklistItem($item);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => ['nullable', Rule::exists('people', 'id')->where('church_id', $this->getCurrentChurch()->id)],
        ]);

        $item->update($validated);

        return back()->with('success', 'Пункт оновлено.');
    }

    public function deleteItem(EventChecklistItem $item)
    {
        $this->authorizeChecklistItem($item);

        $item->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Пункт видалено.');
    }

    public function deleteChecklist(EventChecklist $checklist)
    {
        $this->authorizeEventChecklist($checklist);

        $event = $checklist->event;
        $checklist->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('events.show', $event)
            ->with('success', 'Чеклист видалено.');
    }

    private function authorizeChurchResource($model)
    {
        $church = $this->getCurrentChurch();
        if ($model->church_id !== $church->id) {
            abort(403);
        }
    }

    private function authorizeEventChecklist(EventChecklist $checklist)
    {
        $checklist->loadMissing('event');
        $church = $this->getCurrentChurch();
        if ($checklist->event->church_id !== $church->id) {
            abort(403);
        }
    }

    private function authorizeChecklistItem(EventChecklistItem $item)
    {
        $item->loadMissing('eventChecklist.event');
        $church = $this->getCurrentChurch();
        if ($item->eventChecklist->event->church_id !== $church->id) {
            abort(403);
        }
    }
}
