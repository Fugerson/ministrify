<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Church;
use App\Models\ChurchRole;
use App\Models\ExpenseCategory;
use App\Models\Person;
use App\Models\Tag;
use App\Models\User;
use App\Rules\SecurePassword;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Show join church form
     */
    public function showJoin()
    {
        $churches = Church::where('settings->self_registration_enabled', true)
            ->orWhereNull('settings->self_registration_enabled') // Default: allow
            ->orderBy('name')
            ->get(['id', 'name', 'city']);

        return view('auth.join', compact('churches'));
    }

    /**
     * Handle join church registration.
     * Supports multi-church: if email already exists and password matches, join the new church.
     */
    public function join(Request $request)
    {
        $request->validate([
            'church_id' => ['required', 'exists:churches,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'confirmed', new SecurePassword],
        ]);

        $church = Church::findOrFail($request->church_id);

        // Check if self-registration is enabled for this church
        if ($church->getSetting('self_registration_enabled') === false) {
            return back()->with('error', 'Ця церква не приймає нові реєстрації.');
        }

        // Find default volunteer role for this church
        $volunteerRole = ChurchRole::where('church_id', $church->id)
            ->where('slug', 'volunteer')
            ->first();

        // Check if an active user with this email already exists → multi-church join
        $existingUser = User::where('email', $request->email)->first();

        if ($existingUser) {
            // Verify password
            if (!Hash::check($request->password, $existingUser->password)) {
                return back()->withInput()->withErrors(['password' => 'Невірний пароль для існуючого акаунту.']);
            }

            // Check if already a member
            if ($existingUser->belongsToChurch($church->id)) {
                Auth::login($existingUser);
                $existingUser->switchToChurch($church->id);
                return redirect()->route('dashboard')
                    ->with('info', 'Ви вже є членом цієї церкви.');
            }

            // Join the new church (no role — pending approval)
            $existingUser->joinChurch($church->id);
            $existingUser->switchToChurch($church->id);

            Auth::login($existingUser);

            Log::channel('security')->info('Existing user joined new church', [
                'user_id' => $existingUser->id,
                'email' => $request->email,
                'church_id' => $church->id,
                'ip' => $request->ip(),
            ]);

            return redirect()->route('dashboard')
                ->with('success', 'Ви приєднались до ' . $church->name . '!');
        }

        // Check if a soft-deleted user with this email exists — restore instead of creating new
        $trashedUser = User::onlyTrashed()->where('email', $request->email)->first();

        if ($trashedUser) {
            $trashedUser->restore();
            $trashedUser->update([
                'church_id' => $church->id,
                'name' => $request->name,
                'password' => Hash::make($request->password),
                'church_role_id' => $volunteerRole?->id,
                'onboarding_completed' => true,
            ]);

            // Create Person record if not exists for this church
            $person = Person::where('user_id', $trashedUser->id)
                ->where('church_id', $church->id)
                ->first();

            if (!$person) {
                $nameParts = explode(' ', $request->name, 2);
                $person = Person::create([
                    'church_id' => $church->id,
                    'user_id' => $trashedUser->id,
                    'first_name' => $nameParts[0],
                    'last_name' => $nameParts[1] ?? '',
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'membership_status' => 'newcomer',
                ]);
            }

            // Create or update pivot record
            DB::table('church_user')->updateOrInsert(
                ['user_id' => $trashedUser->id, 'church_id' => $church->id],
                [
                    'church_role_id' => $volunteerRole?->id,
                    'person_id' => $person->id,
                    'joined_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            Log::channel('security')->info('Soft-deleted user restored via join', [
                'user_id' => $trashedUser->id,
                'email' => $request->email,
                'church_id' => $church->id,
                'ip' => $request->ip(),
            ]);

            Auth::login($trashedUser);

            return redirect()->route('dashboard')
                ->with('success', 'Ласкаво просимо назад! Ваш акаунт відновлено.');
        }

        DB::transaction(function () use ($request, $church, $volunteerRole) {
            // Parse name into first_name and last_name
            $nameParts = explode(' ', $request->name, 2);
            $firstName = $nameParts[0];
            $lastName = $nameParts[1] ?? '';

            // Create user with default volunteer role
            $user = User::create([
                'church_id' => $church->id,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'church_role_id' => $volunteerRole?->id,
                'onboarding_completed' => true,
            ]);

            // Find existing Person by email or create new
            $person = Person::where('church_id', $church->id)
                ->where('email', $request->email)
                ->whereNull('user_id')
                ->first();

            if ($person) {
                $person->update(['user_id' => $user->id]);
            } else {
                $person = Person::create([
                    'church_id' => $church->id,
                    'user_id' => $user->id,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'membership_status' => 'newcomer',
                ]);
            }

            // Create pivot record
            DB::table('church_user')->insert([
                'user_id' => $user->id,
                'church_id' => $church->id,
                'church_role_id' => $volunteerRole?->id,
                'person_id' => $person->id,
                'joined_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Fire registered event to send verification email
            event(new Registered($user));

            Auth::login($user);
        });

        return redirect()->route('dashboard')
            ->with('success', 'Ласкаво просимо! Ваш акаунт створено.');
    }

    public function register(Request $request)
    {
        $request->validate([
            'church_name' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->whereNull('deleted_at')],
            'password' => ['required', 'string', 'confirmed', new SecurePassword],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        DB::transaction(function () use ($request) {
            // Create church with unique slug
            $baseSlug = Str::slug($request->church_name);
            $slug = $baseSlug;
            $counter = 1;
            while (Church::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }

            $church = Church::create([
                'name' => $request->church_name,
                'city' => $request->city,
                'slug' => $slug,
                'settings' => [
                    'notifications' => [
                        'reminder_day_before' => true,
                        'reminder_same_day' => true,
                        'notify_leader_on_decline' => true,
                    ],
                ],
            ]);

            // Restore soft-deleted user or create new admin user
            // Query ChurchRole directly instead of via relationship - the relationship
            // would return empty due to Eloquent caching (roles were just created in booted())
            $adminRole = \App\Models\ChurchRole::where('church_id', $church->id)
                ->where('is_admin_role', true)
                ->first();
            $trashedUser = User::onlyTrashed()->where('email', $request->email)->first();

            if ($trashedUser) {
                $trashedUser->restore();
                $trashedUser->update([
                    'church_id' => $church->id,
                    'name' => $request->name,
                    'password' => Hash::make($request->password),
                    'role' => 'admin',
                    'church_role_id' => $adminRole?->id,
                ]);
                $user = $trashedUser;

                Log::channel('security')->info('Soft-deleted user restored via church registration', [
                    'user_id' => $user->id,
                    'email' => $request->email,
                    'church_id' => $church->id,
                ]);
            } else {
                $user = User::create([
                    'church_id' => $church->id,
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'role' => 'admin',
                    'church_role_id' => $adminRole?->id,
                ]);
            }

            // Create Person record for admin (if not already exists for THIS church)
            $person = Person::where('user_id', $user->id)->where('church_id', $church->id)->first();
            if (!$person) {
                $nameParts = explode(' ', $request->name, 2);
                $person = Person::create([
                    'church_id' => $church->id,
                    'user_id' => $user->id,
                    'first_name' => $nameParts[0],
                    'last_name' => $nameParts[1] ?? '',
                    'email' => $request->email,
                    'phone' => $request->phone ?? null,
                    'membership_status' => 'member',
                ]);
            }

            // Create pivot record
            DB::table('church_user')->insert([
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
            $defaultCategories = [
                'Обладнання',
                'Витратні матеріали',
                'Їжа та напої',
                'Оренда',
                'Транспорт',
                'Інше',
            ];

            foreach ($defaultCategories as $category) {
                ExpenseCategory::create([
                    'church_id' => $church->id,
                    'name' => $category,
                ]);
            }

            // Fire registered event to send verification email
            event(new Registered($user));

            Auth::login($user);
        });

        return redirect()->route('verification.notice');
    }
}
