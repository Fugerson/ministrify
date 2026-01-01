<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\RequiresChurch;
use App\Models\Person;
use App\Models\TelegramMessage;
use App\Services\TelegramService;
use Illuminate\Http\Request;

class TelegramBroadcastController extends Controller
{
    use RequiresChurch;

    public function index()
    {
        $church = $this->getChurchOrFail();

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

        $church = $this->getChurchOrFail();

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

                // Save outgoing message
                TelegramMessage::create([
                    'church_id' => $church->id,
                    'person_id' => $person->id,
                    'direction' => 'outgoing',
                    'message' => $validated['message'],
                    'is_read' => true,
                ]);

                $sent++;
            } catch (\Exception $e) {
                $failed++;
            }
        }

        return back()->with('success', "Надіслано: {$sent} повідомлень" . ($failed > 0 ? ", помилок: {$failed}" : ''));
    }
}
