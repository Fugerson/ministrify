<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
                ->with('error', 'Не вдалося увійти через Google. Спробуйте ще раз.');
        }

        // Find user by google_id or email
        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if ($user) {
            // Update google_id if not set (user registered via email before)
            if (!$user->google_id) {
                $user->update(['google_id' => $googleUser->getId()]);
            }

            // Mark email as verified if not already (Google verified it)
            if (!$user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
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
                    'description' => 'Вхід через Google',
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
        $church = \App\Models\Church::find($churchId);

        if (!$church) {
            return redirect()->route('join')->with('error', 'Церкву не знайдено.');
        }

        // Check if self-registration is enabled
        if ($church->getSetting('self_registration_enabled') === false) {
            return redirect()->route('join')->with('error', 'Ця церква не приймає нові реєстрації.');
        }

        // Parse name
        $nameParts = explode(' ', $googleUser->getName(), 2);
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? '';

        // Create user
        $user = User::create([
            'church_id' => $church->id,
            'name' => $googleUser->getName(),
            'email' => $googleUser->getEmail(),
            'google_id' => $googleUser->getId(),
            'password' => bcrypt(\Illuminate\Support\Str::random(32)),
            'email_verified_at' => now(),
            'church_role_id' => null, // Basic access
            'onboarding_completed' => true,
        ]);

        // Create linked Person record
        \App\Models\Person::create([
            'church_id' => $church->id,
            'user_id' => $user->id,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $googleUser->getEmail(),
            'membership_status' => 'newcomer',
        ]);

        // Login
        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('success', 'Ласкаво просимо! Ваш акаунт створено.');
    }

    /**
     * Show registration options for new Google user
     */
    public function showRegisterOptions(Request $request)
    {
        $googleUser = $request->session()->get('google_user');

        if (!$googleUser) {
            return redirect()->route('login');
        }

        return view('auth.google-register', compact('googleUser'));
    }

    /**
     * Complete registration for new Google user
     */
    public function completeRegistration(Request $request)
    {
        $googleUser = $request->session()->get('google_user');

        if (!$googleUser) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'action' => 'required|in:create_church,join_church',
            'church_name' => 'required_if:action,create_church|nullable|string|max:255',
            'city' => 'required_if:action,create_church|nullable|string|max:255',
            'invite_code' => 'required_if:action,join_church|nullable|string',
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
        $baseSlug = \Illuminate\Support\Str::slug($validated['church_name']);
        $slug = $baseSlug;
        $counter = 1;
        while (\App\Models\Church::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        $church = \App\Models\Church::create([
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

        // Create admin user
        $user = User::create([
            'church_id' => $church->id,
            'name' => $googleUser['name'],
            'email' => $googleUser['email'],
            'google_id' => $googleUser['id'],
            'password' => bcrypt(\Illuminate\Support\Str::random(32)),
            'email_verified_at' => now(),
        ]);

        // Create default tags
        $defaultTags = [
            ['name' => 'Волонтер', 'color' => '#3b82f6'],
            ['name' => 'Лідер', 'color' => '#22c55e'],
            ['name' => 'Новий', 'color' => '#f59e0b'],
            ['name' => 'Член церкви', 'color' => '#8b5cf6'],
        ];

        foreach ($defaultTags as $tag) {
            \App\Models\Tag::create([
                'church_id' => $church->id,
                'name' => $tag['name'],
                'color' => $tag['color'],
            ]);
        }

        // Create default expense categories
        $defaultCategories = ['Обладнання', 'Витратні матеріали', 'Їжа та напої', 'Оренда', 'Транспорт', 'Інше'];
        foreach ($defaultCategories as $category) {
            \App\Models\ExpenseCategory::create([
                'church_id' => $church->id,
                'name' => $category,
            ]);
        }

        // Clear session and login
        $request->session()->forget('google_user');
        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('success', 'Ласкаво просимо до Ministrify!');
    }

    /**
     * Join existing church with Google user
     */
    protected function joinChurchWithGoogleUser(Request $request, array $googleUser, array $validated)
    {
        // TODO: Implement invite code logic
        // For now, show error
        return back()->with('error', 'Функція приєднання до церкви за кодом ще в розробці.');
    }
}
