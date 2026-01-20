<?php

namespace App\Http\Controllers;

use App\Models\ChurchRole;
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
        $templates = MessageTemplate::where('church_id', $this->getCurrentChurch()->id)->get();
        $logs = MessageLog::where('church_id', $this->getCurrentChurch()->id)
            ->with('user')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return view('messages.index', compact('templates', 'logs'));
    }

    public function create()
    {
        $churchId = $this->getCurrentChurch()->id;
        $tags = Tag::where('church_id', $churchId)->get();
        $ministries = Ministry::where('church_id', $churchId)->get();
        $groups = Group::where('church_id', $churchId)->get();
        $templates = MessageTemplate::where('church_id', $churchId)->get();
        $roles = ChurchRole::where('church_id', $churchId)->orderBy('sort_order')->get();

        return view('messages.create', compact('tags', 'ministries', 'groups', 'templates', 'roles'));
    }

    public function preview(Request $request)
    {
        $churchId = $this->getCurrentChurch()->id;
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
            'recipient_type' => 'required|in:all,tag,ministry,group,custom,gender,birthday,membership,age,new_members,role',
            'tag_id' => 'nullable|exists:tags,id',
            'ministry_id' => 'nullable|exists:ministries,id',
            'group_id' => 'nullable|exists:groups,id',
            'person_ids' => 'nullable|array',
            'gender' => 'nullable|in:male,female',
            'membership_status' => 'nullable|string',
            'age_group' => 'nullable|in:youth,adults,seniors',
            'church_role_id' => 'nullable|exists:church_roles,id',
        ]);

        $churchId = $this->getCurrentChurch()->id;
        $church = auth()->user()->church;
        $recipients = $this->getRecipients($request, $churchId);

        if ($recipients->isEmpty()) {
            return back()->with('error', 'Немає отримувачів з Telegram');
        }

        if (!config('services.telegram.bot_token')) {
            return back()->with('error', 'Telegram бот не налаштовано. Перейдіть в Налаштування → Інтеграції.');
        }

        $telegram = TelegramService::make();
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
            'church_id' => $this->getCurrentChurch()->id,
            'name' => $validated['name'],
            'content' => $validated['content'],
            'type' => 'telegram',
        ]);

        return back()->with('success', 'Шаблон збережено');
    }

    public function destroyTemplate(MessageTemplate $template)
    {
        if ($template->church_id !== $this->getCurrentChurch()->id) {
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
            case 'gender':
                if ($request->gender) {
                    $query->where('gender', $request->gender);
                }
                break;
            case 'birthday':
                // Birthday this month
                $query->whereMonth('birth_date', now()->month);
                break;
            case 'membership':
                if ($request->membership_status) {
                    $query->where('membership_status', $request->membership_status);
                }
                break;
            case 'age':
                if ($request->age_group) {
                    $now = now();
                    switch ($request->age_group) {
                        case 'youth': // 14-30
                            $query->whereNotNull('birth_date')
                                ->whereDate('birth_date', '<=', $now->copy()->subYears(14))
                                ->whereDate('birth_date', '>=', $now->copy()->subYears(30));
                            break;
                        case 'adults': // 30-60
                            $query->whereNotNull('birth_date')
                                ->whereDate('birth_date', '<=', $now->copy()->subYears(30))
                                ->whereDate('birth_date', '>=', $now->copy()->subYears(60));
                            break;
                        case 'seniors': // 60+
                            $query->whereNotNull('birth_date')
                                ->whereDate('birth_date', '<=', $now->copy()->subYears(60));
                            break;
                    }
                }
                break;
            case 'new_members':
                // Joined in last 30 days
                $query->where(function ($q) {
                    $q->whereDate('joined_date', '>=', now()->subDays(30))
                        ->orWhereDate('created_at', '>=', now()->subDays(30));
                });
                break;
            case 'role':
                if ($request->church_role_id) {
                    $query->where('church_role_id', $request->church_role_id);
                }
                break;
        }

        return $query->get();
    }
}
