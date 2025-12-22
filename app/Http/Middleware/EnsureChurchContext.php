<?php

namespace App\Http\Middleware;

use App\Models\Church;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureChurchContext
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Check if super admin is impersonating a church
        if ($user->isSuperAdmin() && session('impersonate_church_id')) {
            $church = Church::find(session('impersonate_church_id'));
            if (!$church) {
                session()->forget('impersonate_church_id');
                return redirect()->route('system.index')
                    ->with('error', 'Церква не знайдена.');
            }
        } else {
            $church = $user->church;
        }

        if (!$church) {
            // Super admins without a church can still access if not impersonating
            if ($user->isSuperAdmin()) {
                return redirect()->route('system.index')
                    ->with('warning', 'Оберіть церкву для роботи.');
            }

            auth()->logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'Ваш акаунт не прив\'язаний до церкви.']);
        }

        // Share church with all views
        view()->share('currentChurch', $church);

        return $next($request);
    }
}
