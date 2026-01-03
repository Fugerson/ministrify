<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Church;
use App\Models\User;
use App\Models\Person;
use App\Models\Event;
use App\Models\Transaction;
use App\Models\SupportTicket;
use App\Models\SupportMessage;
use App\Models\AdminTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SystemAdminController extends Controller
{
    /**
     * System admin dashboard
     */
    public function index()
    {
        // System-wide statistics
        $stats = [
            'churches' => Church::count(),
            'users' => User::count(),
            'people' => Person::count(),
            'events' => Event::count(),
        ];

        // Financial summary (using unified Transaction model)
        $finances = [
            'total_income' => Transaction::incoming()->completed()->sum('amount'),
            'total_expenses' => Transaction::outgoing()->completed()->sum('amount'),
        ];

        // Recent churches
        $recentChurches = Church::withCount(['users', 'people', 'events'])
            ->latest()
            ->take(5)
            ->get();

        // Recent users
        $recentUsers = User::with('church')
            ->latest()
            ->take(10)
            ->get();

        // Recent audit logs
        $recentLogs = AuditLog::with(['user', 'church'])
            ->latest()
            ->take(20)
            ->get();

        // Growth data
        $monthlyGrowth = $this->getMonthlyGrowth();

        return view('system-admin.index', compact(
            'stats', 'finances', 'recentChurches', 'recentUsers', 'recentLogs', 'monthlyGrowth'
        ));
    }

    /**
     * List all churches
     */
    public function churches(Request $request)
    {
        $query = Church::withCount(['users', 'people', 'events', 'ministries']);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('city', 'like', "%{$request->search}%");
            });
        }

        $churches = $query->latest()->paginate(20);

        return view('system-admin.churches.index', compact('churches'));
    }

    /**
     * Show church details
     */
    public function showChurch(Church $church)
    {
        $church->loadCount(['users', 'people', 'events', 'ministries', 'groups', 'boards']);

        $users = $church->users()->with('person')->get();
        $recentEvents = $church->events()->latest()->take(10)->get();
        $auditLogs = AuditLog::with('user')->where('church_id', $church->id)->latest()->take(20)->get();

        // Church finances (using unified Transaction model)
        $finances = [
            'income' => Transaction::where('church_id', $church->id)->incoming()->completed()->sum('amount'),
            'expenses' => Transaction::where('church_id', $church->id)->outgoing()->completed()->sum('amount'),
        ];

        return view('system-admin.churches.show', compact('church', 'users', 'recentEvents', 'auditLogs', 'finances'));
    }

    /**
     * List all users
     */
    public function users(Request $request)
    {
        $query = User::with('church');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        if ($request->church_id) {
            $query->where('church_id', $request->church_id);
        }

        if ($request->role) {
            $query->where('role', $request->role);
        }

        if ($request->has('super_admin')) {
            $query->where('is_super_admin', true);
        }

        $users = $query->latest()->paginate(20);
        $churches = Church::orderBy('name')->get();

        return view('system-admin.users.index', compact('users', 'churches'));
    }

    /**
     * Edit user
     */
    public function editUser(User $user)
    {
        $churches = Church::orderBy('name')->get();
        return view('system-admin.users.edit', compact('user', 'churches'));
    }

    /**
     * Update user
     */
    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'church_id' => 'nullable|exists:churches,id',
            'role' => 'required|in:admin,leader,volunteer',
            'is_super_admin' => 'boolean',
            'password' => 'nullable|min:8',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->church_id = $validated['church_id'];
        $user->role = $validated['role'];
        $user->is_super_admin = $request->boolean('is_super_admin');

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('system.users.index')
            ->with('success', 'Користувача оновлено.');
    }

    /**
     * Delete user
     */
    public function destroyUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Неможливо видалити себе.');
        }

        $user->delete();

        return redirect()->route('system.users.index')
            ->with('success', 'Користувача видалено.');
    }

    /**
     * Impersonate user (login as)
     */
    public function impersonateUser(User $user)
    {
        $adminId = auth()->id();
        $adminName = auth()->user()->name;

        // Log the impersonation action BEFORE switching users
        AuditLog::create([
            'user_id' => $adminId,
            'church_id' => $user->church_id,
            'action' => 'impersonate',
            'model_type' => User::class,
            'model_id' => $user->id,
            'changes' => [
                'admin_id' => $adminId,
                'admin_name' => $adminName,
                'target_user_id' => $user->id,
                'target_user_name' => $user->name,
                'target_user_email' => $user->email,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ],
        ]);

        // Store original admin ID in session
        session(['impersonating_from' => $adminId]);

        // Login as the target user
        auth()->login($user);

        return redirect()->route('dashboard')
            ->with('success', "Ви увійшли як {$user->name}. Натисніть «Повернутись» у верхній панелі.");
    }

    /**
     * Stop impersonating and return to super admin
     */
    public function stopImpersonating()
    {
        $originalUserId = session('impersonating_from');
        $impersonatedUser = auth()->user();

        if ($originalUserId) {
            $originalUser = User::find($originalUserId);
            if ($originalUser && $originalUser->is_super_admin) {
                // Log the end of impersonation
                AuditLog::create([
                    'user_id' => $originalUserId,
                    'church_id' => $impersonatedUser->church_id ?? null,
                    'action' => 'stop_impersonate',
                    'model_type' => User::class,
                    'model_id' => $impersonatedUser->id,
                    'changes' => [
                        'admin_id' => $originalUserId,
                        'admin_name' => $originalUser->name,
                        'impersonated_user_id' => $impersonatedUser->id,
                        'impersonated_user_name' => $impersonatedUser->name,
                        'ip_address' => request()->ip(),
                    ],
                ]);

                auth()->login($originalUser);
                session()->forget('impersonating_from');

                return redirect()->route('system.users.index')
                    ->with('success', 'Ви повернулись до свого акаунту.');
            }
        }

        return redirect()->route('dashboard');
    }

    /**
     * System-wide audit logs
     */
    public function auditLogs(Request $request)
    {
        $query = AuditLog::with(['user', 'church']);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('action', 'like', "%{$request->search}%")
                  ->orWhere('model_type', 'like', "%{$request->search}%");
            });
        }

        if ($request->church_id) {
            $query->where('church_id', $request->church_id);
        }

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        $logs = $query->latest()->paginate(50);
        $churches = Church::orderBy('name')->get();

        return view('system-admin.audit-logs', compact('logs', 'churches'));
    }

    /**
     * System settings
     */
    public function settings()
    {
        return view('system-admin.settings');
    }

    /**
     * Get monthly growth data
     */
    private function getMonthlyGrowth(): array
    {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = [
                'month' => $date->format('M Y'),
                'churches' => Church::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
                'users' => User::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
                'people' => Person::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
            ];
        }
        return $months;
    }

    /**
     * Create new church
     */
    public function createChurch()
    {
        return view('system-admin.churches.create');
    }

    /**
     * Store new church
     */
    public function storeChurch(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|min:8',
        ]);

        // Create church
        $church = Church::create([
            'name' => $validated['name'],
            'city' => $validated['city'],
            'address' => $validated['address'],
            'slug' => \Illuminate\Support\Str::slug($validated['name']),
        ]);

        // Create admin user
        $user = User::create([
            'church_id' => $church->id,
            'name' => $validated['admin_name'],
            'email' => $validated['admin_email'],
            'password' => Hash::make($validated['admin_password']),
            'role' => 'admin',
        ]);

        return redirect()->route('system.churches.index')
            ->with('success', "Церкву \"{$church->name}\" створено з адміністратором {$user->email}");
    }

    /**
     * Switch to church context
     */
    public function switchToChurch(Church $church)
    {
        // Create a temporary session to work in that church's context
        session(['impersonate_church_id' => $church->id]);

        return redirect()->route('dashboard')
            ->with('success', "Ви перейшли в контекст церкви: {$church->name}");
    }

    /**
     * Exit church context
     */
    public function exitChurchContext()
    {
        session()->forget('impersonate_church_id');

        return redirect()->route('system.index')
            ->with('success', 'Ви повернулись до системної адмінки.');
    }

    /**
     * Support tickets list
     */
    public function supportTickets(Request $request)
    {
        $query = SupportTicket::with(['user', 'church', 'latestMessage']);

        if ($request->status && $request->status !== 'all') {
            if ($request->status === 'open') {
                $query->open();
            } else {
                $query->where('status', $request->status);
            }
        } else {
            $query->open(); // Default to open tickets
        }

        if ($request->category) {
            $query->where('category', $request->category);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('subject', 'like', "%{$request->search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$request->search}%")
                      ->orWhere('email', 'like', "%{$request->search}%"));
            });
        }

        $tickets = $query->orderByDesc('last_reply_at')->paginate(20);

        $stats = [
            'open' => SupportTicket::open()->count(),
            'waiting' => SupportTicket::where('status', 'waiting')->count(),
            'resolved' => SupportTicket::where('status', 'resolved')->count(),
        ];

        return view('system-admin.support.index', compact('tickets', 'stats'));
    }

    /**
     * Show support ticket
     */
    public function showSupportTicket(SupportTicket $ticket)
    {
        $ticket->load(['user', 'church']);

        // Mark user messages as read
        $ticket->messages()
            ->where('is_from_admin', false)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = $ticket->messages()
            ->with('user')
            ->orderBy('created_at')
            ->get();

        $admins = User::where('is_super_admin', true)->get();

        return view('system-admin.support.show', compact('ticket', 'messages', 'admins'));
    }

    /**
     * Reply to support ticket
     */
    public function replySupportTicket(Request $request, SupportTicket $ticket)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:10000',
            'is_internal' => 'boolean',
            'status' => 'nullable|in:open,in_progress,waiting,resolved,closed',
        ]);

        SupportMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'message' => $validated['message'],
            'is_from_admin' => true,
            'is_internal' => $request->boolean('is_internal'),
        ]);

        $updateData = ['last_reply_at' => now()];

        if ($request->status) {
            $updateData['status'] = $request->status;
            if ($request->status === 'resolved') {
                $updateData['resolved_at'] = now();
            }
        } elseif (!$request->boolean('is_internal')) {
            // If not internal note, set status to waiting for user response
            $updateData['status'] = 'waiting';
        }

        $ticket->update($updateData);

        return redirect()->route('system.support.show', $ticket)
            ->with('success', $request->boolean('is_internal') ? 'Внутрішню нотатку додано.' : 'Відповідь надіслано!');
    }

    /**
     * Update ticket status/assignment
     */
    public function updateSupportTicket(Request $request, SupportTicket $ticket)
    {
        $validated = $request->validate([
            'status' => 'nullable|in:open,in_progress,waiting,resolved,closed',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        if ($request->has('status')) {
            $ticket->status = $validated['status'];
            if ($validated['status'] === 'resolved') {
                $ticket->resolved_at = now();
            }
        }

        if ($request->has('priority')) {
            $ticket->priority = $validated['priority'];
        }

        if ($request->has('assigned_to')) {
            $ticket->assigned_to = $validated['assigned_to'];
        }

        $ticket->save();

        return redirect()->route('system.support.show', $ticket)
            ->with('success', 'Тікет оновлено.');
    }

    /**
     * Admin tasks list
     */
    public function tasks(Request $request)
    {
        $query = AdminTask::with(['creator', 'assignee', 'supportTicket']);

        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->priority) {
            $query->where('priority', $request->priority);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        $tasks = $query->orderByRaw("FIELD(priority, 'critical', 'high', 'normal', 'low')")
            ->orderByRaw("FIELD(status, 'in_progress', 'todo', 'testing', 'backlog', 'done')")
            ->orderByDesc('created_at')
            ->paginate(20);

        $stats = [
            'backlog' => AdminTask::where('status', 'backlog')->count(),
            'todo' => AdminTask::where('status', 'todo')->count(),
            'in_progress' => AdminTask::where('status', 'in_progress')->count(),
            'testing' => AdminTask::where('status', 'testing')->count(),
            'done' => AdminTask::where('status', 'done')->count(),
        ];

        $admins = User::where('is_super_admin', true)->get();

        return view('system-admin.tasks.index', compact('tasks', 'stats', 'admins'));
    }

    /**
     * Create task form
     */
    public function createTask(Request $request)
    {
        $admins = User::where('is_super_admin', true)->get();
        $supportTicket = null;

        if ($request->from_ticket) {
            $supportTicket = SupportTicket::find($request->from_ticket);
        }

        return view('system-admin.tasks.create', compact('admins', 'supportTicket'));
    }

    /**
     * Store new task
     */
    public function storeTask(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:10000',
            'type' => 'required|in:bug,feature,improvement,other',
            'priority' => 'required|in:low,normal,high,critical',
            'status' => 'required|in:backlog,todo,in_progress,testing,done',
            'assigned_to' => 'nullable|exists:users,id',
            'support_ticket_id' => 'nullable|exists:support_tickets,id',
            'due_date' => 'nullable|date',
        ]);

        $validated['created_by'] = auth()->id();

        if ($validated['status'] === 'done') {
            $validated['completed_at'] = now();
        }

        $task = AdminTask::create($validated);

        return redirect()->route('system.tasks.index')
            ->with('success', 'Задачу створено!');
    }

    /**
     * Edit task form
     */
    public function editTask(AdminTask $task)
    {
        $admins = User::where('is_super_admin', true)->get();
        $supportTickets = SupportTicket::whereNull('resolved_at')
            ->orWhere('resolved_at', '>', now()->subDays(7))
            ->orderByDesc('created_at')
            ->get();

        return view('system-admin.tasks.edit', compact('task', 'admins', 'supportTickets'));
    }

    /**
     * Update task
     */
    public function updateTask(Request $request, AdminTask $task)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:10000',
            'type' => 'required|in:bug,feature,improvement,other',
            'priority' => 'required|in:low,normal,high,critical',
            'status' => 'required|in:backlog,todo,in_progress,testing,done',
            'assigned_to' => 'nullable|exists:users,id',
            'support_ticket_id' => 'nullable|exists:support_tickets,id',
            'due_date' => 'nullable|date',
        ]);

        // Handle completion
        if ($validated['status'] === 'done' && $task->status !== 'done') {
            $validated['completed_at'] = now();
        } elseif ($validated['status'] !== 'done') {
            $validated['completed_at'] = null;
        }

        $task->update($validated);

        return redirect()->route('system.tasks.index')
            ->with('success', 'Задачу оновлено!');
    }

    /**
     * Quick update task status
     */
    public function updateTaskStatus(Request $request, AdminTask $task)
    {
        $validated = $request->validate([
            'status' => 'required|in:backlog,todo,in_progress,testing,done',
        ]);

        $task->status = $validated['status'];

        if ($validated['status'] === 'done' && !$task->completed_at) {
            $task->completed_at = now();
        } elseif ($validated['status'] !== 'done') {
            $task->completed_at = null;
        }

        $task->save();

        return back()->with('success', 'Статус оновлено!');
    }

    /**
     * Delete task
     */
    public function destroyTask(AdminTask $task)
    {
        $task->delete();

        return redirect()->route('system.tasks.index')
            ->with('success', 'Задачу видалено.');
    }
}
