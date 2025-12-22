<?php

namespace App\Http\Controllers;

use App\Services\TwoFactorService;
use Illuminate\Http\Request;

class TwoFactorController extends Controller
{
    public function __construct(
        private TwoFactorService $twoFactor
    ) {}

    /**
     * Show 2FA setup page
     */
    public function show()
    {
        $user = auth()->user();

        $enabled = $user->two_factor_confirmed_at !== null;
        $recoveryCodes = [];

        if ($enabled && $user->two_factor_recovery_codes) {
            $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true) ?? [];
        }

        return view('auth.two-factor.show', compact('enabled', 'recoveryCodes'));
    }

    /**
     * Enable 2FA - show QR code
     */
    public function enable()
    {
        $user = auth()->user();

        if ($user->two_factor_confirmed_at) {
            return redirect()->route('two-factor.show')
                ->with('error', 'Двофакторна аутентифікація вже увімкнена.');
        }

        $secret = $this->twoFactor->generateSecretKey();

        // Store temporarily in session
        session(['2fa_secret' => $secret]);

        $qrCodeUrl = $this->twoFactor->getQrCodeUrl($user, $secret);
        $qrCodeSvg = $this->twoFactor->getQrCodeSvg($qrCodeUrl);

        return view('auth.two-factor.enable', compact('secret', 'qrCodeSvg'));
    }

    /**
     * Confirm 2FA setup
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = auth()->user();
        $secret = session('2fa_secret');

        if (!$secret) {
            return back()->with('error', 'Сесія закінчилася. Спробуйте ще раз.');
        }

        if (!$this->twoFactor->verify($secret, $request->code)) {
            return back()->with('error', 'Неправильний код. Спробуйте ще раз.');
        }

        // Generate recovery codes
        $recoveryCodes = $this->twoFactor->generateRecoveryCodes();

        $user->update([
            'two_factor_secret' => encrypt($secret),
            'two_factor_recovery_codes' => encrypt(json_encode($recoveryCodes)),
            'two_factor_confirmed_at' => now(),
        ]);

        session()->forget('2fa_secret');

        return view('auth.two-factor.recovery-codes', compact('recoveryCodes'));
    }

    /**
     * Disable 2FA
     */
    public function disable(Request $request)
    {
        $request->validate([
            'password' => 'required|current_password',
        ]);

        $user = auth()->user();

        $user->update([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);

        return redirect()->route('two-factor.show')
            ->with('success', 'Двофакторну аутентифікацію вимкнено.');
    }

    /**
     * Regenerate recovery codes
     */
    public function regenerateRecoveryCodes(Request $request)
    {
        $request->validate([
            'password' => 'required|current_password',
        ]);

        $user = auth()->user();

        if (!$user->two_factor_confirmed_at) {
            return back()->with('error', '2FA не увімкнено.');
        }

        $recoveryCodes = $this->twoFactor->generateRecoveryCodes();

        $user->update([
            'two_factor_recovery_codes' => encrypt(json_encode($recoveryCodes)),
        ]);

        return view('auth.two-factor.recovery-codes', compact('recoveryCodes'));
    }

    /**
     * Show 2FA challenge during login
     */
    public function challenge()
    {
        if (!session('2fa_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor.challenge');
    }

    /**
     * Verify 2FA code during login
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $userId = session('2fa_user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = \App\Models\User::find($userId);
        if (!$user) {
            return redirect()->route('login');
        }

        $code = str_replace('-', '', $request->code);

        // Try TOTP code first
        $secret = decrypt($user->two_factor_secret);
        if ($this->twoFactor->verify($secret, $code)) {
            session()->forget('2fa_user_id');
            auth()->login($user, session('2fa_remember', false));
            session()->forget('2fa_remember');

            return redirect()->intended(route('dashboard'));
        }

        // Try recovery code
        if ($this->twoFactor->verifyRecoveryCode($user, $request->code)) {
            session()->forget('2fa_user_id');
            auth()->login($user, session('2fa_remember', false));
            session()->forget('2fa_remember');

            return redirect()->intended(route('dashboard'))
                ->with('warning', 'Ви використали код відновлення. Залишилось: ' .
                    count(json_decode(decrypt($user->two_factor_recovery_codes), true)));
        }

        return back()->with('error', 'Неправильний код.');
    }
}
