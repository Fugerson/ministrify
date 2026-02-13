<?php

namespace App\Http\Middleware;

use App\Models\Church;
use App\Models\Person;
use App\Models\TelegramMessage;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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

            // Auto-switch: if church_id is null but user has pivot records, switch to first church
            if (!$church) {
                $firstPivot = DB::table('church_user')
                    ->where('user_id', $user->id)
                    ->orderBy('joined_at')
                    ->first();

                if ($firstPivot) {
                    $user->switchToChurch($firstPivot->church_id);
                    $user->refresh();
                    $church = $user->church;
                }
            }
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

        // Set correct Person for the active church
        $person = Person::where('user_id', $user->id)
            ->where('church_id', $church->id)
            ->first();

        // Fallback: resolve via pivot's person_id and auto-link
        if (!$person) {
            $pivot = DB::table('church_user')
                ->where('user_id', $user->id)
                ->where('church_id', $church->id)
                ->first();

            if ($pivot?->person_id) {
                $person = Person::find($pivot->person_id);
                if ($person && !$person->user_id) {
                    $person->update(['user_id' => $user->id]);
                }
            }

            // Still no person? Try by email and link
            if (!$person) {
                $person = Person::where('church_id', $church->id)
                    ->where('email', $user->email)
                    ->first();
                if ($person && !$person->user_id) {
                    $person->update(['user_id' => $user->id]);
                }
                // Update pivot to point to correct person
                if ($person && $pivot && $pivot->person_id !== $person->id) {
                    DB::table('church_user')
                        ->where('user_id', $user->id)
                        ->where('church_id', $church->id)
                        ->update(['person_id' => $person->id]);
                }
            }
        }

        if ($person) {
            $user->setRelation('person', $person);
        }

        // Share church with all views
        view()->share('currentChurch', $church);

        // Share user's churches for the switcher component
        $userChurches = $user->churches()->select('churches.id', 'churches.name', 'churches.logo')->get();
        view()->share('userChurches', $userChurches);

        // Share unread Telegram messages count for admins (cached for 120s)
        if ($user->isAdmin()) {
            $unreadTelegramCount = Cache::remember(
                "church:{$church->id}:unread_telegram",
                120,
                fn () => TelegramMessage::where('church_id', $church->id)
                    ->where('direction', 'incoming')
                    ->where('is_read', false)
                    ->count()
            );
            view()->share('unreadTelegramCount', $unreadTelegramCount);
        }

        return $next($request);
    }
}
