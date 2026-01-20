<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\TelegramMessage;
use App\Services\TelegramService;
use Illuminate\Http\Request;

class TelegramChatController extends Controller
{
    public function index()
    {
        $church = auth()->user()->church;

        if (!$church) {
            return redirect()->route('dashboard')
                ->with('error', 'Telegram чат доступний тільки для користувачів з церквою.');
        }

        // Get people with Telegram linked, with their latest message
        $conversations = Person::where('church_id', $church->id)
            ->whereNotNull('telegram_chat_id')
            ->withCount(['telegramMessages as unread_count' => function ($query) {
                $query->where('direction', 'incoming')->where('is_read', false);
            }])
            ->with(['telegramMessages' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->get()
            ->sortByDesc(function ($person) {
                return $person->telegramMessages->first()?->created_at;
            });

        $hasBot = !empty(config('services.telegram.bot_token'));
        $totalUnread = TelegramMessage::where('church_id', $church->id)
            ->where('direction', 'incoming')
            ->where('is_read', false)
            ->count();

        return view('telegram.chats', compact('conversations', 'hasBot', 'totalUnread'));
    }

    public function show(Person $person)
    {
        $church = auth()->user()->church;

        if (!$church) {
            return redirect()->route('dashboard')
                ->with('error', 'Telegram чат доступний тільки для користувачів з церквою.');
        }

        // Ensure person belongs to this church
        if ($person->church_id !== $church->id) {
            abort(403);
        }

        // Mark all incoming messages as read
        TelegramMessage::where('church_id', $church->id)
            ->where('person_id', $person->id)
            ->where('direction', 'incoming')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        // Get messages
        $messages = TelegramMessage::where('church_id', $church->id)
            ->where('person_id', $person->id)
            ->orderBy('created_at', 'asc')
            ->get();

        $hasBot = !empty(config('services.telegram.bot_token'));

        return view('telegram.chat', compact('person', 'messages', 'hasBot'));
    }

    public function send(Request $request, Person $person)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:4000',
        ]);

        $church = auth()->user()->church;

        if (!$church) {
            return redirect()->route('dashboard')
                ->with('error', 'Telegram чат доступний тільки для користувачів з церквою.');
        }

        // Ensure person belongs to this church
        if ($person->church_id !== $church->id) {
            abort(403);
        }

        if (empty(config('services.telegram.bot_token'))) {
            return back()->with('error', 'Telegram бот не налаштований');
        }

        if (empty($person->telegram_chat_id)) {
            return back()->with('error', 'Людина не підключена до Telegram');
        }

        try {
            $telegram = TelegramService::make();
            $telegram->sendMessage($person->telegram_chat_id, $validated['message']);

            // Save outgoing message
            TelegramMessage::create([
                'church_id' => $church->id,
                'person_id' => $person->id,
                'direction' => 'outgoing',
                'message' => $validated['message'],
                'is_read' => true,
            ]);

            return back();
        } catch (\Exception $e) {
            return back()->with('error', 'Помилка надсилання: ' . $e->getMessage());
        }
    }
}
