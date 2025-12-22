<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $church = $this->getCurrentChurch();

        $query = AuditLog::where('church_id', $church->id)
            ->with('user')
            ->orderByDesc('created_at');

        // Filter by action
        if ($action = $request->get('action')) {
            $query->where('action', $action);
        }

        // Filter by model type
        if ($modelType = $request->get('model')) {
            $query->where('model_type', 'App\\Models\\' . $modelType);
        }

        // Filter by user
        if ($userId = $request->get('user')) {
            $query->where('user_id', $userId);
        }

        // Filter by date range
        if ($from = $request->get('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->get('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('model_name', 'like', "%{$search}%")
                  ->orWhere('user_name', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(50)->withQueryString();

        // Get filter options
        $users = $church->users()->select('id', 'name')->get();
        $actions = ['created', 'updated', 'deleted', 'restored', 'login', 'logout'];
        $models = ['Person', 'Event', 'Ministry', 'Group', 'Expense', 'Income', 'User'];

        return view('settings.audit-logs', compact('logs', 'users', 'actions', 'models'));
    }

    public function show(AuditLog $auditLog)
    {
        $church = $this->getCurrentChurch();

        if ($auditLog->church_id !== $church->id) {
            abort(404);
        }

        return response()->json([
            'log' => $auditLog,
            'changes' => $auditLog->changes_summary,
        ]);
    }
}
