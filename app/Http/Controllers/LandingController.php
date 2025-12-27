<?php

namespace App\Http\Controllers;

use App\Models\Church;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    /**
     * Landing home page
     */
    public function home()
    {
        $stats = [
            'churches' => Church::count(),
            'members' => \App\Models\Person::count(),
            'events' => \App\Models\Event::count(),
        ];

        return view('landing.home', compact('stats'));
    }

    /**
     * Features page
     */
    public function features()
    {
        return view('landing.features');
    }

    /**
     * Pricing page
     */
    public function pricing()
    {
        return view('landing.pricing');
    }

    /**
     * Contact page
     */
    public function contact()
    {
        return view('landing.contact');
    }

    /**
     * Handle contact form
     */
    public function sendContact(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'church' => 'nullable|string|max:100',
            'message' => 'required|string|max:2000',
        ]);

        // TODO: Send email or save to database

        return back()->with('success', 'Дякуємо! Ми зв\'яжемося з вами найближчим часом.');
    }

    /**
     * Church registration page
     */
    public function register()
    {
        return view('landing.register');
    }

    /**
     * Process church registration
     */
    public function processRegistration(Request $request)
    {
        $validated = $request->validate([
            'church_name' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'admin_name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|min:8|confirmed',
        ]);

        // Create church
        $church = Church::create([
            'name' => $validated['church_name'],
            'city' => $validated['city'],
            'slug' => \Str::slug($validated['church_name']) . '-' . \Str::random(4),
        ]);

        // Create admin user
        $user = \App\Models\User::create([
            'name' => $validated['admin_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => bcrypt($validated['password']),
            'church_id' => $church->id,
            'role' => 'admin',
        ]);

        // Create person record for admin
        \App\Models\Person::create([
            'church_id' => $church->id,
            'user_id' => $user->id,
            'first_name' => explode(' ', $validated['admin_name'])[0],
            'last_name' => explode(' ', $validated['admin_name'])[1] ?? '',
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'church_role' => 'admin',
        ]);

        auth()->login($user);

        return redirect()->route('dashboard')->with('success', 'Вітаємо! Ваша церква зареєстрована.');
    }
}
