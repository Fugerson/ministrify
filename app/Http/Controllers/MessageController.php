<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\MessageLog;
use App\Models\MessageTemplate;
use App\Models\Ministry;
use App\Models\Person;
use App\Models\Tag;
use App\Services\TelegramService;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index()
    {
        $templates = MessageTemplate::where('church_id', auth()->user()->church_id)->get();
        $logs = MessageLog::where('church_id', auth()->user()->church_id)
            ->with('user')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return view('messages.index', compact('templates', 'logs'));
    }

    public function create()
    {
        $tags = Tag::where('church_id', auth()->user()->church_id)->get();
        $ministries = Ministry::where('church_id', auth()->user()->church_id)->get();
        $groups = Group::where('church_id', auth()->user()->church_id)->get();
        $templates = MessageTemplate::where('church_id', auth()->user()->church_id)->get();

        return view('messages.create', compact('tags', 'ministries', 'groups', 'templates'));
    }

    public function preview(Request $request)
    {
        $churchId = auth()->user()->church_id;
        $recipients = $this->getRecipients($request, $churchId);

        return response()->json([
            'count' => $recipients->count(),
            'preview' => $recipients->take(10)->map(fn($p) => [
                'name' => $p->full_name,
                'telegram' => $p->telegram_chat_id ? true : false,
            ]),
        ]);
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:4000',
            'recipient_type' => 'required|in:all,tag,ministry,group,custom',
            'tag_id' => 'nullable|exists:tags,id',
            'ministry_id' => 'nullable|exists:ministries,id',
            'group_id' => 'nullable|exists:groups,id',
            'person_ids' => 'nullable|array',
        ]);

        $churchId = auth()->user()->church_id;
        $church = auth()->user()->church;
        $recipients = $this->getRecipients($request, $churchId);

        if ($recipients->isEmpty()) {
            return back()->with('error', 'Немає отримувачів з Telegram');
        }

        if (!$church->telegram_bot_token) {
            return back()->with('error', 'Telegram бот не налаштовано. Перейдіть в Налаштування → Інтеграції.');
        }

        $telegram = new TelegramService($church->telegram_bot_token);
        $sent = 0;
        $failed = 0;

        foreach ($recipients as $person) {
            if (!$person->telegram_chat_id) {
                continue;
            }

            try {
                $telegram->sendMessage($person->telegram_chat_id, $validated['message']);
                $sent++;
            } catch (\Exception $e) {
                $failed++;
            }
        }

        MessageLog::create([
            'church_id' => $churchId,
            'user_id' => auth()->id(),
            'type' => 'telegram',
            'content' => $validated['message'],
            'recipients' => [
                'type' => $validated['recipient_type'],
                'ids' => $recipients->pluck('id')->toArray(),
            ],
            'sent_count' => $sent,
            'failed_count' => $failed,
        ]);

        return redirect()->route('messages.index')
            ->with('success', "Надіслано: {$sent}, помилок: {$failed}");
    }

    public function storeTemplate(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string|max:4000',
        ]);

        MessageTemplate::create([
            'church_id' => auth()->user()->church_id,
            'name' => $validated['name'],
            'content' => $validated['content'],
            'type' => 'telegram',
        ]);

        return back()->with('success', 'Шаблон збережено');
    }

    public function destroyTemplate(MessageTemplate $template)
    {
        if ($template->church_id !== auth()->user()->church_id) {
            abort(403);
        }

        $template->delete();

        return back()->with('success', 'Шаблон видалено');
    }

    private function getRecipients(Request $request, int $churchId)
    {
        $query = Person::where('church_id', $churchId)
            ->whereNotNull('telegram_chat_id');

        switch ($request->recipient_type) {
            case 'tag':
                $query->whereHas('tags', fn($q) => $q->where('tags.id', $request->tag_id));
                break;
            case 'ministry':
                $query->whereHas('ministries', fn($q) => $q->where('ministries.id', $request->ministry_id));
                break;
            case 'group':
                $query->whereHas('groups', fn($q) => $q->where('groups.id', $request->group_id));
                break;
            case 'custom':
                if ($request->person_ids) {
                    $query->whereIn('id', $request->person_ids);
                }
                break;
        }

        return $query->get();
    }
}
