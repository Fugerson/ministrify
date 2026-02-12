<?php

namespace App\Http\Controllers;

use App\Models\Ministry;
use App\Models\MinistryGoal;
use App\Models\MinistryTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class MinistryGoalController extends Controller
{
    public function index(Ministry $ministry)
    {
        // Redirect to ministry show page with goals tab
        return redirect()->route('ministries.show', ['ministry' => $ministry, 'tab' => 'goals']);
    }

    public function storeGoal(Request $request, Ministry $ministry)
    {
        $this->authorizeMinistry($ministry);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'period' => 'nullable|string|max:50',
            'due_date' => 'nullable|date',
            'priority' => 'nullable|in:low,medium,high',
        ]);

        $validated['church_id'] = $this->getCurrentChurch()->id;
        $validated['ministry_id'] = $ministry->id;
        $validated['created_by'] = auth()->id();
        $validated['status'] = 'active';

        MinistryGoal::create($validated);

        return back()->with('success', 'Ціль додано');
    }

    public function updateGoal(Request $request, Ministry $ministry, MinistryGoal $goal)
    {
        $this->authorizeMinistry($ministry);
        abort_unless($goal->ministry_id === $ministry->id, 404);
        abort_unless($goal->ministry_id === $ministry->id, 404);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'period' => 'nullable|string|max:50',
            'due_date' => 'nullable|date',
            'priority' => 'nullable|in:low,medium,high',
            'status' => 'nullable|in:active,completed,on_hold,cancelled',
        ]);

        if (isset($validated['status']) && $validated['status'] === 'completed' && $goal->status !== 'completed') {
            $validated['completed_at'] = now();
            $validated['progress'] = 100;
        }

        $goal->update($validated);

        return back()->with('success', 'Ціль оновлено');
    }

    public function destroyGoal(Ministry $ministry, MinistryGoal $goal)
    {
        $this->authorizeMinistry($ministry);
        abort_unless($goal->ministry_id === $ministry->id, 404);

        $goal->delete();

        return back()->with('success', 'Ціль видалено');
    }

    public function storeTask(Request $request, Ministry $ministry)
    {
        $this->authorizeMinistry($ministry);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'goal_id' => ['nullable', Rule::exists('ministry_goals', 'id')->where('ministry_id', $ministry->id)],
            'assigned_to' => ['nullable', Rule::exists('people', 'id')->where('church_id', $this->getCurrentChurch()->id)],
            'due_date' => 'nullable|date',
            'priority' => 'nullable|in:low,medium,high',
        ]);

        $validated['church_id'] = $this->getCurrentChurch()->id;
        $validated['ministry_id'] = $ministry->id;
        $validated['created_by'] = auth()->id();
        $validated['status'] = 'todo';
        $validated['sort_order'] = MinistryTask::where('ministry_id', $ministry->id)->max('sort_order') + 1;

        MinistryTask::create($validated);

        return back()->with('success', 'Задачу додано');
    }

    public function updateTask(Request $request, Ministry $ministry, MinistryTask $task)
    {
        $this->authorizeMinistry($ministry);
        abort_unless($task->ministry_id === $ministry->id, 404);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'goal_id' => ['nullable', Rule::exists('ministry_goals', 'id')->where('ministry_id', $ministry->id)],
            'assigned_to' => ['nullable', Rule::exists('people', 'id')->where('church_id', $this->getCurrentChurch()->id)],
            'due_date' => 'nullable|date',
            'priority' => 'nullable|in:low,medium,high',
            'status' => 'nullable|in:todo,in_progress,done',
        ]);

        $oldStatus = $task->status;
        $task->update($validated);

        // Update goal progress if status changed
        if (isset($validated['status']) && $validated['status'] !== $oldStatus && $task->goal) {
            $task->goal->updateProgressFromTasks();
        }

        return back()->with('success', 'Задачу оновлено');
    }

    public function toggleTask(Ministry $ministry, MinistryTask $task)
    {
        $this->authorizeMinistry($ministry);
        abort_unless($task->ministry_id === $ministry->id, 404);

        $task->toggle();

        return back()->with('success', 'Статус задачі оновлено');
    }

    public function updateTaskStatus(Request $request, Ministry $ministry, MinistryTask $task)
    {
        $this->authorizeMinistry($ministry);
        abort_unless($task->ministry_id === $ministry->id, 404);

        $validated = $request->validate([
            'status' => 'required|in:todo,in_progress,done',
        ]);

        $oldStatus = $task->status;

        if ($validated['status'] === 'done') {
            $task->markAsDone();
        } elseif ($validated['status'] === 'todo') {
            $task->markAsTodo();
        } else {
            $task->update(['status' => $validated['status']]);
            if ($task->goal) {
                $task->goal->updateProgressFromTasks();
            }
        }

        return back()->with('success', 'Статус задачі оновлено');
    }

    public function destroyTask(Ministry $ministry, MinistryTask $task)
    {
        $this->authorizeMinistry($ministry);
        abort_unless($task->ministry_id === $ministry->id, 404);

        $goal = $task->goal;
        $task->delete();

        // Update goal progress
        if ($goal) {
            $goal->updateProgressFromTasks();
        }

        return back()->with('success', 'Задачу видалено');
    }

    public function updateVision(Request $request, Ministry $ministry)
    {
        $this->authorizeMinistry($ministry);

        $validated = $request->validate([
            'vision' => 'nullable|string|max:5000',
        ]);

        $ministry->update($validated);

        return back()->with('success', 'Бачення оновлено');
    }

    private function authorizeMinistry(Ministry $ministry): void
    {
        if ($ministry->church_id !== $this->getCurrentChurch()->id) {
            abort(404);
        }

        Gate::authorize('manage-ministry', $ministry);
    }
}
