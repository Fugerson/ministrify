<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Church;
use App\Models\ChurchRole;
use App\Models\ExpenseCategory;
use App\Models\Person;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Redirect to Google OAuth
     */
    public function redirectToGoogle(Request $request)
    {
        // If joining a specific church, save church_id in session
        if ($request->has('church_id')) {
            $request->session()->put('google_join_church_id', $request->church_id);
        }

        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google OAuth callback
     */
    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            Log::error('Google OAuth failed', ['error' => $e->getMessage()]);

            return redirect()->route('login')
                ->with('error', __('messages.google_login_failed'));
        }

        // Find user by google_id first, then by email (prioritize google_id to avoid collision)
        $user = User::withTrashed()
            ->where('google_id', $googleUser->getId())
            ->first();

        if (! $user) {
            $user = User::withTrashed()
                ->where('email', $googleUser->getEmail())
                ->first();
        }

        // Soft-deleted user
        if ($user && $user->trashed()) {
            $joinChurchId = $request->session()->get('google_join_church_id');

            if ($joinChurchId) {
                // Joining a church — wipe old data completely, proceed as new user
                Log::channel('security')->info('Soft-deleted user re-joining via Google', [
                    'old_user_id' => $user->id,
                    'email' => $googleUser->getEmail(),
                    'church_id' => $joinChurchId,
                ]);
                $this->wipeTrashedUser($googleUser->getEmail());
                $user = null;
            } else {
                // Pure login attempt — block
                Log::channel('security')->info('Soft-deleted user attempted Google login', [
                    'user_id' => $user->id,
                    'email' => $googleUser->getEmail(),
                ]);

                return redirect()->route('login')
                    ->with('error', __('messages.account_not_found'));
            }
        }

        if ($user) {
            // Update google_id if not set (user registered via email before)
            if (! $user->google_id) {
                $user->update(['google_id' => $googleUser->getId()]);
            }

            // Mark email as verified if not already (Google verified it)
            if (! $user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
            }

            // Check if user is trying to join a specific church via Google
            $joinChurchId = $request->session()->pull('google_join_church_id');
            if ($joinChurchId) {
                $church = Church::find($joinChurchId);
                if ($church) {
                    // Join if not already a member
                    if (! $user->belongsToChurch($joinChurchId)) {
                        if ($church->getSetting('self_registration_enabled') !== false) {
                            $user->joinChurch($church->id);
                        }
                    }

                    // Always switch to the requested church
                    $user->switchToChurch($church->id);

                    Log::channel('security')->info('User joined/switched to church via Google', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'church_id' => $church->id,
                        'ip' => $request->ip(),
                    ]);

                    Auth::login($user, true);
                    $request->session()->regenerate();

                    AuditLog::create([
                        'church_id' => $church->id,
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                        'action' => 'login',
                        'model_type' => User::class,
                        'model_id' => $user->id,
                        'model_name' => $user->name,
                        'notes' => __('messages.audit_login_google'),
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                    ]);

                    AuditLog::create([
                        'church_id' => $church->id,
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                        'action' => 'joined_church',
                        'model_type' => User::class,
                        'model_id' => $user->id,
                        'model_name' => $user->name,
                        'notes' => __('messages.audit_joined_church_google'),
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                    ]);

                    return redirect()->route('dashboard')
                        ->with('success', __('messages.joined_church', ['name' => $church->name]));
                }
            }

            // Login
            Auth::login($user, true);
            $request->session()->regenerate();

            // Log successful login
            Log::channel('security')->info('User logged in via Google', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
            ]);

            // Audit log
            if ($user->church_id) {
                AuditLog::create([
                    'church_id' => $user->church_id,
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'action' => 'login',
                    'model_type' => User::class,
                    'model_id' => $user->id,
                    'model_name' => $user->name,
                    'notes' => __('messages.audit_login_google'),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            }

            // Redirect
            if ($user->isSuperAdmin()) {
                return redirect()->route('system.index');
            }

            return redirect()->route('dashboard');
        }

        // Check if user is joining a specific church via Google
        $joinChurchId = $request->session()->pull('google_join_church_id');

        if ($joinChurchId) {
            return $this->createUserForChurch($request, $googleUser, $joinChurchId);
        }

        // New user - store Google data in session and redirect to choose flow
        $request->session()->put('google_user', [
            'id' => $googleUser->getId(),
            'name' => $googleUser->getName(),
            'email' => $googleUser->getEmail(),
            'avatar' => $googleUser->getAvatar(),
        ]);

        return redirect()->route('auth.google.register');
    }

    /**
     * Create user for a specific church (self-registration via Google)
     */
    protected function createUserForChurch(Request $request, $googleUser, $churchId)
    {
        $church = Church::find($churchId);

        if (! $church) {
            return redirect()->route('join')->with('error', __('messages.church_not_found'));
        }

        // Check if self-registration is enabled
        if ($church->getSetting('self_registration_enabled') === false) {
            return redirect()->route('join')->with('error', __('messages.church_registration_closed'));
        }

        // Check if user with this email already exists
        $existingUser = User::where('email', $googleUser->getEmail())->first();
        if ($existingUser) {

            if (! $existingUser->google_id) {
                $existingUser->update(['google_id' => $googleUser->getId()]);
            }
            if (! $existingUser->hasVerifiedEmail()) {
                $existingUser->markEmailAsVerified();
            }

            // Join church (creates pivot + Person, no role — pending)
            $existingUser->joinChurch($church->id);
            $existingUser->switchToChurch($church->id);

            Log::channel('security')->info('User joined church via Google', [
                'user_id' => $existingUser->id,
                'email' => $googleUser->getEmail(),
                'church_id' => $church->id,
            ]);

            Auth::login($existingUser, true);
            $request->session()->regenerate();

            AuditLog::create([
                'church_id' => $church->id,
                'user_id' => $existingUser->id,
                'user_name' => $existingUser->name,
                'action' => 'login',
                'model_type' => User::class,
                'model_id' => $existingUser->id,
                'model_name' => $existingUser->name,
                'notes' => __('messages.audit_login_google'),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            AuditLog::create([
                'church_id' => $church->id,
                'user_id' => $existingUser->id,
                'user_name' => $existingUser->name,
                'action' => 'joined_church',
                'model_type' => User::class,
                'model_id' => $existingUser->id,
                'model_name' => $existingUser->name,
                'notes' => __('messages.audit_joined_church_google'),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('dashboard');
        }

        // Create user
        $user = User::create([
            'church_id' => $church->id,
            'name' => $googleUser->getName(),
            'email' => $googleUser->getEmail(),
            'google_id' => $googleUser->getId(),
            'password' => bcrypt(Str::random(32)),
            'church_role_id' => null,
            'onboarding_completed' => true,
        ]);
        $user->markEmailAsVerified();

        // joinChurch handles Person find/create + pivot (no duplicates)
        $user->joinChurch($church->id);
        $user->switchToChurch($church->id);

        // Login
        Auth::login($user, true);
        $request->session()->regenerate();

        AuditLog::create([
            'church_id' => $church->id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'action' => 'registered',
            'model_type' => User::class,
            'model_id' => $user->id,
            'model_name' => $user->name,
            'notes' => __('messages.audit_registered_google_joined'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('dashboard')
            ->with('success', __('messages.welcome_account_created'));
    }

    /**
     * Show registration options for new Google user
     */
    public function showRegisterOptions(Request $request)
    {
        $googleUser = $request->session()->get('google_user');

        if (! $googleUser) {
            return redirect()->route('login');
        }

        // Get churches that allow self-registration
        $churches = Church::where('settings->self_registration_enabled', true)
            ->orWhereNull('settings->self_registration_enabled')
            ->orderBy('name')
            ->get(['id', 'name', 'city']);

        return view('auth.google-register', compact('googleUser', 'churches'));
    }

    /**
     * Complete registration for new Google user
     */
    public function completeRegistration(Request $request)
    {
        $googleUser = $request->session()->get('google_user');

        if (! $googleUser) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'action' => 'required|in:create_church,join_church',
            'church_name' => 'required_if:action,create_church|nullable|string|max:255',
            'city' => 'required_if:action,create_church|nullable|string|max:255',
            'church_id' => 'required_if:action,join_church|nullable|exists:churches,id',
        ]);

        if ($validated['action'] === 'create_church') {
            // Create new church (similar to RegisterController)
            return $this->createChurchWithGoogleUser($request, $googleUser, $validated);
        } else {
            // Join existing church via invite code
            return $this->joinChurchWithGoogleUser($request, $googleUser, $validated);
        }
    }

    /**
     * Create new church with Google user as admin
     */
    protected function createChurchWithGoogleUser(Request $request, array $googleUser, array $validated)
    {
        // Wipe old soft-deleted user completely (allow re-registration from scratch)
        $this->wipeTrashedUser($googleUser['email']);

        $baseSlug = Str::slug($validated['church_name']);
        $slug = $baseSlug;
        $counter = 1;
        while (Church::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter++;
        }

        $church = Church::create([
            'name' => $validated['church_name'],
            'city' => $validated['city'],
            'slug' => $slug,
            'settings' => [
                'notifications' => [
                    'reminder_day_before' => true,
                    'reminder_same_day' => true,
                    'notify_leader_on_decline' => true,
                ],
            ],
        ]);

        // Get admin role - query directly instead of via relationship due to Eloquent caching
        $adminRole = ChurchRole::where('church_id', $church->id)
            ->where('is_admin_role', true)
            ->first();

        $user = User::create([
            'church_id' => $church->id,
            'name' => $googleUser['name'],
            'email' => $googleUser['email'],
            'google_id' => $googleUser['id'],
            'password' => bcrypt(Str::random(32)),
            'role' => 'admin',
            'church_role_id' => $adminRole?->id,
        ]);
        $user->markEmailAsVerified();

        // Create Person record for admin (if not already exists)
        $person = Person::where('user_id', $user->id)->where('church_id', $church->id)->first();
        if (! $person) {
            $nameParts = explode(' ', $googleUser['name'], 2);
            $person = Person::create([
                'church_id' => $church->id,
                'user_id' => $user->id,
                'first_name' => $nameParts[0],
                'last_name' => $nameParts[1] ?? '',
                'email' => $googleUser['email'],
                'membership_status' => 'member',
            ]);
        }

        // Create pivot record
        DB::table('church_user')->insertOrIgnore([
            'user_id' => $user->id,
            'church_id' => $church->id,
            'church_role_id' => $adminRole?->id,
            'person_id' => $person->id,
            'joined_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create default tags
        $defaultTags = [
            ['name' => 'Волонтер', 'color' => '#3b82f6'],
            ['name' => 'Лідер', 'color' => '#22c55e'],
            ['name' => 'Новий', 'color' => '#f59e0b'],
            ['name' => 'Член церкви', 'color' => '#8b5cf6'],
        ];

        foreach ($defaultTags as $tag) {
            Tag::create([
                'church_id' => $church->id,
                'name' => $tag['name'],
                'color' => $tag['color'],
            ]);
        }

        // Create default expense categories
        $defaultCategories = ['Обладнання', 'Витратні матеріали', 'Їжа та напої', 'Оренда', 'Транспорт', 'Інше'];
        foreach ($defaultCategories as $category) {
            ExpenseCategory::create([
                'church_id' => $church->id,
                'name' => $category,
            ]);
        }

        // Clear session and login
        $request->session()->forget('google_user');
        Auth::login($user, true);
        $request->session()->regenerate();

        AuditLog::create([
            'church_id' => $church->id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'action' => 'registered',
            'model_type' => User::class,
            'model_id' => $user->id,
            'model_name' => $user->name,
            'notes' => __('messages.audit_registered_google_created_church'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('dashboard')
            ->with('success', __('messages.welcome_to_ministrify'));
    }

    /**
     * Join existing church with Google user
     */
    protected function joinChurchWithGoogleUser(Request $request, array $googleUser, array $validated)
    {
        $church = Church::findOrFail($validated['church_id']);

        // Check if self-registration is enabled for this church
        if ($church->getSetting('self_registration_enabled') === false) {
            return back()->with('error', __('messages.church_registration_closed'));
        }

        // Wipe old soft-deleted user completely (allow re-registration from scratch)
        $this->wipeTrashedUser($googleUser['email']);

        $user = User::create([
            'church_id' => $church->id,
            'name' => $googleUser['name'],
            'email' => $googleUser['email'],
            'google_id' => $googleUser['id'],
            'password' => bcrypt(Str::random(32)),
            'onboarding_completed' => true,
        ]);
        $user->markEmailAsVerified();

        // Join church (creates pivot + Person, no role — pending)
        $user->joinChurch($church->id);
        $user->switchToChurch($church->id);

        // Clear session and login
        $request->session()->forget('google_user');
        Auth::login($user, true);
        $request->session()->regenerate();

        AuditLog::create([
            'church_id' => $church->id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'action' => 'registered',
            'model_type' => User::class,
            'model_id' => $user->id,
            'model_name' => $user->name,
            'notes' => __('messages.audit_registered_google_joined'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('dashboard')
            ->with('success', __('messages.welcome_to_church', ['name' => $church->name]));
    }

    /**
     * Completely wipe a soft-deleted user and all their data.
     */
    private function wipeTrashedUser(string $email): void
    {
        $oldUser = User::onlyTrashed()->where('email', $email)->first();
        if (! $oldUser) {
            return;
        }

        DB::table('church_user')->where('user_id', $oldUser->id)->delete();
        Person::where('user_id', $oldUser->id)->forceDelete();
        $oldUser->forceDelete();
    }
}
