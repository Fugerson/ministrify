<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Church;
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
use Illuminate\Support\Str;

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
     * Handle join church registration
     */
    public function join(Request $request)
    {
        $request->validate([
            'church_id' => ['required', 'exists:churches,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'confirmed', new SecurePassword],
        ]);

        $church = Church::findOrFail($request->church_id);

        // Check if self-registration is enabled for this church
        if ($church->getSetting('self_registration_enabled') === false) {
            return back()->with('error', 'Ця церква не приймає нові реєстрації.');
        }

        DB::transaction(function () use ($request, $church) {
            // Parse name into first_name and last_name
            $nameParts = explode(' ', $request->name, 2);
            $firstName = $nameParts[0];
            $lastName = $nameParts[1] ?? '';

            // Create user without role (basic access)
            $user = User::create([
                'church_id' => $church->id,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'church_role_id' => null, // No role = basic access
                'onboarding_completed' => true,
            ]);

            // Create linked Person record
            $person = Person::create([
                'church_id' => $church->id,
                'user_id' => $user->id,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $request->email,
                'phone' => $request->phone,
                'membership_status' => 'newcomer',
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
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
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

            // Create admin user
            $user = User::create([
                'church_id' => $church->id,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'admin',
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
