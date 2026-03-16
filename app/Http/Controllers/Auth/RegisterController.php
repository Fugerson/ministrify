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
use App\Rules\SecurePassword;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
            ->get(['id', 'name', 'city', 'logo']);

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
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'confirmed', new SecurePassword],
        ]);

        $church = Church::findOrFail($request->church_id);

        // Check if self-registration is enabled for this church
        if ($church->getSetting('self_registration_enabled') === false) {
            return back()->with('error', 'Ця церква не приймає нові реєстрації.');
        }

        // Find default volunteer role for this church (for approval pending status)
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
                $request->session()->regenerate();
                $existingUser->switchToChurch($church->id);
                return redirect()->route('dashboard')
                    ->with('info', 'Ви вже є членом цієї церкви.');
            }

            // Join the new church (no role — pending approval)
            $existingUser->joinChurch($church->id, null, $request->phone);
            $existingUser->switchToChurch($church->id);

            Auth::login($existingUser);
            $request->session()->regenerate();

            Log::channel('security')->info('Existing user joined new church', [
                'user_id' => $existingUser->id,
                'email' => $request->email,
                'church_id' => $church->id,
                'ip' => $request->ip(),
            ]);

            AuditLog::create([
                'church_id' => $church->id,
                'user_id' => $existingUser->id,
                'user_name' => $existingUser->name,
                'action' => 'joined_church',
                'model_type' => User::class,
                'model_id' => $existingUser->id,
                'model_name' => $existingUser->name,
                'notes' => 'Приєднався до церкви (існуючий акаунт)',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('dashboard')
                ->with('success', 'Ви приєднались до ' . $church->name . '!');
        }

        // Wipe old soft-deleted user completely (allow re-registration from scratch)
        $oldUser = User::onlyTrashed()->where('email', $request->email)->first();
        if ($oldUser) {
            DB::table('church_user')->where('user_id', $oldUser->id)->delete();
            Person::where('user_id', $oldUser->id)->forceDelete();
            $oldUser->forceDelete();
        }

        // Pre-check: find existing Person and block if already linked to a user
        $nameParts = explode(' ', $request->name, 2);
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? '';

        $existingPerson = Person::where('church_id', $church->id)
            ->where('email', $request->email)
            ->first();

        if ($existingPerson && $existingPerson->user_id) {
            throw ValidationException::withMessages([
                'email' => __('auth.person_already_registered_email'),
            ]);
        }

        if (!$existingPerson && $firstName && $lastName) {
            $existingPerson = Person::where('church_id', $church->id)
                ->where('first_name', $firstName)
                ->where('last_name', $lastName)
                ->first();

            if ($existingPerson && $existingPerson->user_id) {
                throw ValidationException::withMessages([
                    'name' => __('auth.person_already_registered_name'),
                ]);
            }
        }

        if (!$existingPerson && $request->phone) {
            $existingPerson = Person::findByPhoneInChurch($request->phone, $church->id, false);

            if ($existingPerson && $existingPerson->user_id) {
                throw ValidationException::withMessages([
                    'phone' => __('auth.person_already_registered_phone'),
                ]);
            }
        }

        $user = DB::transaction(function () use ($request, $church, $volunteerRole, $existingPerson, $firstName, $lastName) {
            // Create user with PENDING volunteer role approval
            // Don't assign church_role_id yet - set to pending approval instead
            $user = User::create([
                'church_id' => $church->id,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'requested_church_role_id' => $volunteerRole?->id,
                'servant_approval_status' => 'pending',
                'onboarding_completed' => true,
            ]);

            $person = $existingPerson;

            if ($person) {
                $person->update([
                    'user_id' => $user->id,
                    'email' => $person->email ?: $request->email,
                    'phone' => $person->phone ?: $request->phone,
                ]);
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

            // Create pivot record (without church_role_id - pending approval)
            DB::table('church_user')->insert([
                'user_id' => $user->id,
                'church_id' => $church->id,
                'church_role_id' => null,  // No role until approved
                'role_approval_status' => 'pending',  // Mark as pending
                'person_id' => $person->id,
                'joined_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $user;
        });

        // Fire registered event AFTER transaction commits so queue worker can find the user
        event(new Registered($user));

        Auth::login($user);
        $request->session()->regenerate();

        Log::channel('security')->info('User self-registered and joined church (pending approval)', [
            'user_id' => $user->id,
            'email' => $request->email,
            'church_id' => $church->id,
            'requested_role' => 'volunteer',
            'ip' => $request->ip(),
        ]);

        AuditLog::create([
            'church_id' => $user->church_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'action' => 'registered',
            'model_type' => User::class,
            'model_id' => $user->id,
            'model_name' => $user->name,
            'notes' => 'Зареєструвався та приєднався до церкви (очікує одобрення)',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Notify admins about new pending user (email + Telegram)
        try {
            $admins = User::where('church_id', $church->id)
                ->whereNotNull('church_role_id')
                ->whereHas('churchRole', fn ($q) => $q->where('is_admin_role', true))
                ->with('person')
                ->get();

            foreach ($admins as $admin) {
                // Email
                $admin->notify(new \App\Notifications\NewPendingApproval(
                    $user->name, $user->email, $church->name
                ));

                // Telegram
                if ($admin->person?->telegram_chat_id) {
                    try {
                        $tg = new \App\Services\TelegramService();
                        $tg->sendMessage(
                            $admin->person->telegram_chat_id,
                            "🔔 <b>Новий користувач очікує одобрення</b>\n\n"
                            . "👤 {$user->name}\n"
                            . "📧 {$user->email}\n"
                            . "⛪ {$church->name}\n\n"
                            . "Перейдіть в налаштування щоб одобрити або відхилити заявку."
                        );
                    } catch (\Exception $e) {
                        Log::warning('Failed to send Telegram approval notification', ['admin_id' => $admin->id, 'error' => $e->getMessage()]);
                    }
                }
            }

            \Illuminate\Support\Facades\Cache::forget("church:{$church->id}:pending_approvals");
        } catch (\Exception $e) {
            Log::warning('Failed to notify admins about pending approval', ['error' => $e->getMessage()]);
        }

        return redirect()->route('dashboard')
            ->with('success', 'Ласкаво просимо! Ваш акаунт створено. Адміністратор церкви повинен одобрити вашу реєстрацію.');
    }

    public function register(Request $request)
    {
        $request->validate([
            'church_name' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255', Rule::unique('users')->whereNull('deleted_at')],
            'password' => ['required', 'string', 'confirmed', new SecurePassword],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        // Wipe old soft-deleted user completely (allow re-registration from scratch)
        $oldUser = User::onlyTrashed()->where('email', $request->email)->first();
        if ($oldUser) {
            DB::table('church_user')->where('user_id', $oldUser->id)->delete();
            Person::where('user_id', $oldUser->id)->forceDelete();
            $oldUser->forceDelete();
        }

        $user = DB::transaction(function () use ($request) {
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

            // Query ChurchRole directly instead of via relationship - the relationship
            // would return empty due to Eloquent caching (roles were just created in booted())
            $adminRole = \App\Models\ChurchRole::where('church_id', $church->id)
                ->where('is_admin_role', true)
                ->first();

            $user = User::create([
                'church_id' => $church->id,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'admin',
                'church_role_id' => $adminRole?->id,
            ]);

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

            return $user;
        });

        // Fire registered event AFTER transaction commits so queue worker can find the user
        event(new Registered($user));

        Auth::login($user);
        $request->session()->regenerate();

        AuditLog::create([
            'church_id' => $user->church_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'action' => 'registered',
            'model_type' => User::class,
            'model_id' => $user->id,
            'model_name' => $user->name,
            'notes' => 'Зареєструвався та створив нову церкву',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('verification.notice');
    }
}
