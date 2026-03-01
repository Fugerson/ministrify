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
        $allRecipients = $this->getRecipients($request, $churchId, telegramOnly: false);
        $withTelegram = $allRecipients->filter(fn($p) => !empty($p->telegram_chat_id))->count();

        return response()->json([
            'total' => $allRecipients->count(),
            'with_telegram' => $withTelegram,
            'without_telegram' => $allRecipients->count() - $withTelegram,
            'preview' => $allRecipients->take(10)->map(fn($p) => [
                'name' => $p->full_name,
                'telegram' => !empty($p->telegram_chat_id),
            ]),
        ]);
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:4000',
            'recipient_type' => 'required|in:all,tag,ministry,group,custom,gender,birthday,membership,age,new_members,role',
            'tag_id' => ['nullable', \Illuminate\Validation\Rule::exists('tags', 'id')->where('church_id', $this->getCurrentChurch()->id)],
            'ministry_id' => ['nullable', \Illuminate\Validation\Rule::exists('ministries', 'id')->where('church_id', $this->getCurrentChurch()->id)],
            'group_id' => ['nullable', \Illuminate\Validation\Rule::exists('groups', 'id')->where('church_id', $this->getCurrentChurch()->id)],
            'person_ids' => 'nullable|array',
            'gender' => 'nullable|in:male,female',
            'membership_status' => 'nullable|string',
            'age_group' => 'nullable|in:youth,adults,seniors',
            'church_role_id' => ['nullable', \Illuminate\Validation\Rule::exists('church_roles', 'id')->where('church_id', $this->getCurrentChurch()->id)],
        ]);

        $church = $this->getCurrentChurch();
        $churchId = $church->id;
        $allRecipients = $this->getRecipients($request, $churchId, telegramOnly: false);
        $recipients = $allRecipients->filter(fn($p) => !empty($p->telegram_chat_id));
        $skippedNoTelegram = $allRecipients->count() - $recipients->count();

        if ($recipients->isEmpty()) {
            $msg = __('Немає отримувачів з Telegram');
            if ($skippedNoTelegram > 0) {
                $msg .= ' (' . __(':count без Telegram', ['count' => $skippedNoTelegram]) . ')';
            }
            return $this->errorResponse($request, $msg);
        }

        if (!config('services.telegram.bot_token')) {
            return $this->errorResponse($request, 'Telegram бот не налаштовано. Перейдіть в Налаштування → Інтеграції.');
        }

        $telegram = TelegramService::make();
        $sent = 0;
        $failed = 0;

        foreach ($recipients as $index => $person) {
            try {
                // Substitute template variables for each recipient
                $personalMessage = str_replace(
                    ['{first_name}', '{last_name}', '{full_name}', '{phone}'],
                    [$person->first_name ?? '', $person->last_name ?? '', $person->full_name ?? '', $person->phone ?? ''],
                    $validated['message']
                );
                $telegram->sendMessage($person->telegram_chat_id, $personalMessage);
                $sent++;

                // Rate limit: Telegram allows ~30 msg/sec, pause every 25 messages
                if (($index + 1) % 25 === 0) {
                    usleep(1100000); // 1.1 seconds
                }
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
                'total' => $recipients->count(),
            ],
            'sent_count' => $sent,
            'failed_count' => $failed,
        ]);

        $msg = __('Надіслано: :sent', ['sent' => $sent]);
        if ($skippedNoTelegram > 0) {
            $msg .= ', ' . __('пропущено (без Telegram): :skipped', ['skipped' => $skippedNoTelegram]);
        }
        if ($failed > 0) {
            $msg .= ', ' . __('помилок: :failed', ['failed' => $failed]);
        }

        return $this->successResponse($request, $msg, 'messages.index');
    }

    public function storeTemplate(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string|max:4000',
        ]);

        $template = MessageTemplate::create([
            'church_id' => $this->getCurrentChurch()->id,
            'name' => $validated['name'],
            'content' => $validated['content'],
            'type' => 'telegram',
        ]);

        return $this->successResponse($request, 'Шаблон збережено', null, [], ['id' => $template->id]);
    }

    public function destroyTemplate(Request $request, MessageTemplate $template)
    {
        if ($template->church_id !== $this->getCurrentChurch()->id) {
            abort(403);
        }

        $template->delete();

        return $this->successResponse($request, 'Шаблон видалено');
    }

    private function getRecipients(Request $request, int $churchId, bool $telegramOnly = true)
    {
        $query = Person::where('church_id', $churchId);
        if ($telegramOnly) {
            $query->whereNotNull('telegram_chat_id');
        }

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
                if ($request->person_ids && is_array($request->person_ids) && count($request->person_ids) > 0) {
                    $query->whereIn('id', $request->person_ids);
                } else {
                    $query->whereRaw('1=0'); // No recipients selected
                }
                break;
            case 'gender':
                if ($request->gender) {
                    $query->where('gender', $request->gender);
                } else {
                    $query->whereRaw('1=0'); // No gender selected
                }
                break;
            case 'birthday':
                // Birthday this month
                $query->whereMonth('birth_date', now()->month);
                break;
            case 'membership':
                if ($request->membership_status) {
                    $query->where('membership_status', $request->membership_status);
                } else {
                    $query->whereRaw('1=0'); // No status selected
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
                        default:
                            $query->whereRaw('1=0'); // Invalid age group
                    }
                } else {
                    $query->whereRaw('1=0'); // No age group selected
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
                    $query->whereHas('user', fn($q) => $q->where('church_role_id', $request->church_role_id));
                } else {
                    $query->whereRaw('1=0'); // No role selected
                }
                break;
            default:
                $query->whereRaw('1=0'); // Unknown recipient type — select nobody
        }

        return $query->get();
    }
}
