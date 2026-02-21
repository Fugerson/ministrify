<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\ServicePlanTemplate;
use Illuminate\Http\Request;

class ServicePlanTemplateController extends Controller
{
    /**
     * List all templates for the current church
     */
    public function index()
    {
        abort_unless(auth()->user()->canView('events'), 403);

        $church = $this->getCurrentChurch();

        $templates = ServicePlanTemplate::where('church_id', $church->id)
            ->orderBy('name')
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'description' => $t->description,
                'items_count' => count($t->items),
            ]);

        return response()->json([
            'templates' => $templates,
        ]);
    }

    /**
     * Create a template from an event's current plan
     */
    public function store(Request $request, Event $event)
    {
        $this->authorize('managePlan', $event);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'include_responsible' => 'boolean',
        ]);

        $church = $this->getCurrentChurch();

        // Verify event belongs to church
        if ($event->church_id !== $church->id) {
            abort(403);
        }

        // Check if event has plan items
        if ($event->planItems()->count() === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Ця подія не має плану служіння',
            ], 422);
        }

        $template = ServicePlanTemplate::createFromEvent(
            $event,
            $validated['name'],
            $validated['include_responsible'] ?? false,
            $validated['description'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Шаблон збережено',
            'template' => [
                'id' => $template->id,
                'name' => $template->name,
                'description' => $template->description,
                'items_count' => count($template->items),
            ],
        ]);
    }

    /**
     * Apply a custom template to an event
     */
    public function apply(Request $request, Event $event, ServicePlanTemplate $template)
    {
        $this->authorize('managePlan', $event);

        $church = $this->getCurrentChurch();

        // Verify template belongs to church
        if ($template->church_id !== $church->id) {
            abort(403);
        }

        // Verify event belongs to church
        if ($event->church_id !== $church->id) {
            abort(403);
        }

        // Get start time from event or default
        $startTime = $event->time ?? \Carbon\Carbon::parse('10:00');

        $template->applyToEvent($event, $startTime);

        return response()->json([
            'success' => true,
            'message' => 'Шаблон застосовано',
            'count' => count($template->items),
        ]);
    }

    /**
     * Delete a template
     */
    public function destroy(ServicePlanTemplate $template)
    {
        abort_unless(auth()->user()->canEdit('events'), 403);

        $church = $this->getCurrentChurch();

        // Verify template belongs to church
        if ($template->church_id !== $church->id) {
            abort(403);
        }

        $template->delete();

        return response()->json([
            'success' => true,
            'message' => 'Шаблон видалено',
        ]);
    }
}
