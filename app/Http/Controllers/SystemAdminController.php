<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Church;
use App\Models\User;
use App\Models\Person;
use App\Models\Event;
use App\Models\Expense;
use App\Models\Income;
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

        // Financial summary
        $finances = [
            'total_income' => Income::sum('amount'),
            'total_expenses' => Expense::sum('amount'),
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
        $auditLogs = AuditLog::where('church_id', $church->id)->latest()->take(20)->get();

        // Church finances
        $finances = [
            'income' => Income::where('church_id', $church->id)->sum('amount'),
            'expenses' => Expense::where('church_id', $church->id)->sum('amount'),
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
}
