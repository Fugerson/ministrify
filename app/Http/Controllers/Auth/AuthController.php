<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Rules\SecurePassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // First check credentials without logging in
        $user = \App\Models\User::where('email', $credentials['email'])->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            // Check if 2FA is enabled
            if ($user->two_factor_confirmed_at) {
                // Store user ID in session and redirect to 2FA challenge
                $request->session()->put('2fa_user_id', $user->id);
                $request->session()->put('2fa_remember', $request->boolean('remember'));

                Log::channel('security')->info('2FA challenge initiated', [
                    'user_id' => $user->id,
                    'email' => $credentials['email'],
                    'ip' => $request->ip(),
                ]);

                return redirect()->route('two-factor.challenge');
            }

            // No 2FA, proceed with normal login
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            // Log successful login
            Log::channel('security')->info('User logged in', [
                'user_id' => Auth::id(),
                'email' => $credentials['email'],
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Create audit log entry for login
            AuditLog::create([
                'church_id' => $user->church_id,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'action' => 'login',
                'model_type' => 'App\\Models\\User',
                'model_id' => $user->id,
                'model_name' => $user->name,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Redirect super admins to system admin panel
            if ($user->isSuperAdmin()) {
                return redirect()->intended(route('system.index'));
            }

            return redirect()->intended(route('dashboard'));
        }

        // Log failed login attempt
        Log::channel('security')->warning('Failed login attempt', [
            'email' => $credentials['email'],
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->withErrors([
            'email' => 'Невірний email або пароль.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        // Create audit log entry for logout before logging out
        if ($user) {
            AuditLog::create([
                'church_id' => $user->church_id,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'action' => 'logout',
                'model_type' => 'App\\Models\\User',
                'model_id' => $user->id,
                'model_name' => $user->name,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Log logout
        Log::channel('security')->info('User logged out', [
            'user_id' => $user?->id,
            'ip' => $request->ip(),
        ]);

        return redirect()->route('login');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    }

    public function showResetPassword(string $token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', new SecurePassword],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'password_set_at' => now(),
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }

    /**
     * Show email verification notice
     */
    public function verificationNotice(Request $request)
    {
        return $request->user()->hasVerifiedEmail()
            ? redirect()->intended(route('dashboard'))
            : view('auth.verify-email');
    }

    /**
     * Verify email
     */
    public function verifyEmail(Request $request)
    {
        $user = \App\Models\User::findOrFail($request->route('id'));

        if (!hash_equals(sha1($user->getEmailForVerification()), (string) $request->route('hash'))) {
            return redirect()->route('verification.notice')->with('error', 'Невірне посилання для верифікації.');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard'))->with('success', 'Email вже підтверджено.');
        }

        $user->markEmailAsVerified();

        return redirect()->intended(route('dashboard'))->with('success', 'Email успішно підтверджено!');
    }

    /**
     * Resend verification email
     */
    public function resendVerification(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard'));
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'Лист для підтвердження надіслано!');
    }
}
