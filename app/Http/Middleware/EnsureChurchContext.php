<?php

namespace App\Http\Middleware;

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

        $church = auth()->user()->church;

        if (!$church) {
            auth()->logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'Ваш акаунт не прив\'язаний до церкви.']);
        }

        // Share church with all views
        view()->share('currentChurch', $church);

        return $next($request);
    }
}
