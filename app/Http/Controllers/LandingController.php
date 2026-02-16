<?php

namespace App\Http\Controllers;

use App\Models\Church;
use App\Rules\Honeypot;
use App\Rules\Recaptcha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

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
        $request->validate([
            'website' => [new Honeypot],
            'recaptcha_token' => [new Recaptcha('contact')],
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'church' => 'nullable|string|max:100',
            'message' => 'required|string|max:2000',
        ]);

        // Create support ticket
        $ticket = \App\Models\SupportTicket::create([
            'guest_name' => $validated['name'],
            'guest_email' => $validated['email'],
            'subject' => 'Повідомлення з контактної форми' . ($validated['church'] ? ' — ' . $validated['church'] : ''),
            'category' => 'question',
            'priority' => 'normal',
            'status' => 'open',
        ]);

        // Add the message
        \App\Models\SupportMessage::create([
            'ticket_id' => $ticket->id,
            'message' => $validated['message'],
            'is_from_admin' => false,
        ]);

        return back()->with('success', 'Дякуємо! Ми зв\'яжемося з вами найближчим часом.');
    }

    /**
     * Documentation page
     */
    public function docs()
    {
        return view('landing.docs');
    }

    /**
     * FAQ page
     */
    public function faq()
    {
        return view('landing.faq');
    }

    /**
     * Terms of service page
     */
    public function terms()
    {
        return view('landing.terms');
    }

    /**
     * Privacy policy page
     */
    public function privacy()
    {
        return view('landing.privacy');
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
        $request->validate([
            'website' => [new Honeypot],
            'recaptcha_token' => [new Recaptcha('register')],
        ]);

        $validated = $request->validate([
            'church_name' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'admin_name' => 'required|string|max:100',
            'email' => ['required', 'email', Rule::unique('users')->whereNull('deleted_at')],
            'phone' => 'nullable|string|max:20',
            'password' => 'required|min:8|confirmed',
        ]);

        // Create church
        $church = Church::create([
            'name' => $validated['church_name'],
            'city' => $validated['city'],
            'slug' => \Str::slug($validated['church_name']) . '-' . \Str::random(4),
        ]);

        // Restore soft-deleted user or create new admin user
        // Query ChurchRole directly instead of via relationship - the relationship
        // would return empty due to Eloquent caching (roles were just created in booted())
        $adminRole = \App\Models\ChurchRole::where('church_id', $church->id)
            ->where('is_admin_role', true)
            ->first();
        $trashedUser = \App\Models\User::onlyTrashed()->where('email', $validated['email'])->first();

        if ($trashedUser) {
            $trashedUser->restore();
            $trashedUser->update([
                'name' => $validated['admin_name'],
                'phone' => $validated['phone'] ?? null,
                'password' => bcrypt($validated['password']),
                'church_id' => $church->id,
                'role' => 'admin',
                'church_role_id' => $adminRole?->id,
            ]);
            $user = $trashedUser;

            Log::channel('security')->info('Soft-deleted user restored via landing registration', [
                'user_id' => $user->id,
                'email' => $validated['email'],
                'church_id' => $church->id,
            ]);
        } else {
            $user = \App\Models\User::create([
                'name' => $validated['admin_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'password' => bcrypt($validated['password']),
                'church_id' => $church->id,
                'role' => 'admin',
                'church_role_id' => $adminRole?->id,
            ]);
        }

        // Create person record for admin (if not already exists)
        $person = \App\Models\Person::where('user_id', $user->id)
            ->where('church_id', $church->id)
            ->first();

        if (!$person) {
            $person = \App\Models\Person::create([
                'church_id' => $church->id,
                'user_id' => $user->id,
                'first_name' => explode(' ', $validated['admin_name'])[0],
                'last_name' => explode(' ', $validated['admin_name'])[1] ?? '',
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'church_role' => 'admin',
                'membership_status' => 'member',
            ]);
        }

        // Create pivot record
        \Illuminate\Support\Facades\DB::table('church_user')->updateOrInsert(
            ['user_id' => $user->id, 'church_id' => $church->id],
            [
                'church_role_id' => $adminRole?->id,
                'person_id' => $person->id,
                'joined_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        auth()->login($user);

        return redirect()->route('dashboard')->with('success', 'Вітаємо! Ваша церква зареєстрована.');
    }
}
