<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Church;
use App\Models\ExpenseCategory;
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
