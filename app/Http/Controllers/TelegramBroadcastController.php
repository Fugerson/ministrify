<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Services\TelegramService;
use Illuminate\Http\Request;

class TelegramBroadcastController extends Controller
{
    public function index()
    {
        $church = auth()->user()->church;

        $recipients = Person::where('church_id', $church->id)
            ->whereNotNull('telegram_chat_id')
            ->orderBy('first_name')
            ->get();

        $hasBot = !empty($church->telegram_bot_token);

        return view('telegram.broadcast', compact('recipients', 'hasBot'));
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:4000',
            'recipients' => 'required|array|min:1',
            'recipients.*' => 'exists:people,id',
        ]);

        $church = auth()->user()->church;

        if (empty($church->telegram_bot_token)) {
            return back()->with('error', 'Telegram бот не налаштований');
        }

        $telegram = new TelegramService($church->telegram_bot_token);

        $recipients = Person::where('church_id', $church->id)
            ->whereIn('id', $validated['recipients'])
            ->whereNotNull('telegram_chat_id')
            ->get();

        $sent = 0;
        $failed = 0;

        foreach ($recipients as $person) {
            try {
                $telegram->sendMessage($person->telegram_chat_id, $validated['message']);
                $sent++;
            } catch (\Exception $e) {
                $failed++;
            }
        }

        return back()->with('success', "Надіслано: {$sent} повідомлень" . ($failed > 0 ? ", помилок: {$failed}" : ''));
    }
}
