<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Event;
use App\Models\Group;
use App\Models\Ministry;
use App\Models\Person;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $churchId = $this->getCurrentChurch()->id;

        if (strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $search = addcslashes($query, '%_');
        $results = [];

        // Search people
        $people = Person::where('church_id', $churchId)
            ->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })
            ->limit(5)
            ->get();

        foreach ($people as $person) {
            $results[] = [
                'type' => 'person',
                'icon' => 'user',
                'title' => $person->full_name,
                'subtitle' => $this->maskContact($person->phone, $person->email),
                'url' => route('people.show', $person),
            ];
        }

        // Search ministries
        $ministries = Ministry::where('church_id', $churchId)
            ->where('name', 'like', "%{$search}%")
            ->withCount('members')
            ->limit(3)
            ->get();

        foreach ($ministries as $ministry) {
            $results[] = [
                'type' => 'ministry',
                'icon' => 'church',
                'title' => $ministry->name,
                'subtitle' => $ministry->members_count . ' учасників',
                'url' => route('ministries.show', $ministry),
                'color' => $ministry->color,
            ];
        }

        // Search groups
        $groups = Group::where('church_id', $churchId)
            ->where('name', 'like', "%{$search}%")
            ->limit(3)
            ->get();

        foreach ($groups as $group) {
            $results[] = [
                'type' => 'group',
                'icon' => 'users',
                'title' => $group->name,
                'subtitle' => $group->location ?? 'Домашня група',
                'url' => route('groups.show', $group),
            ];
        }

        // Search events
        $events = Event::where('church_id', $churchId)
            ->where('title', 'like', "%{$search}%")
            ->where('date', '>=', now()->subDays(7))
            ->orderBy('date')
            ->limit(3)
            ->get();

        foreach ($events as $event) {
            $results[] = [
                'type' => 'event',
                'icon' => 'calendar',
                'title' => $event->title,
                'subtitle' => $event->date->format('d.m.Y'),
                'url' => route('events.show', $event),
            ];
        }

        // Search boards
        $boards = Board::where('church_id', $churchId)
            ->where('is_archived', false)
            ->where('name', 'like', "%{$search}%")
            ->withCount('cards')
            ->limit(3)
            ->get();

        foreach ($boards as $board) {
            $results[] = [
                'type' => 'board',
                'icon' => 'kanban',
                'title' => $board->name,
                'subtitle' => $board->cards_count . ' карток',
                'url' => route('boards.show', $board),
            ];
        }

        return response()->json(['results' => $results]);
    }

    public function quickActions()
    {
        $user = auth()->user();

        $actions = [
            [
                'key' => 'n',
                'label' => 'Нова людина',
                'url' => route('people.create'),
                'icon' => 'user-plus',
            ],
            [
                'key' => 'e',
                'label' => 'Нова подія',
                'url' => route('events.create'),
                'icon' => 'calendar-plus',
            ],
        ];

        if ($user->hasRole(['admin', 'leader'])) {
            $actions[] = [
                'key' => 'x',
                'label' => 'Нова витрата',
                'url' => route('expenses.create'),
                'icon' => 'receipt',
            ];
        }

        $actions[] = [
            'key' => 'g',
            'label' => 'Нова група',
            'url' => route('groups.create'),
            'icon' => 'users',
        ];

        $actions[] = [
            'key' => 'b',
            'label' => 'Нова дошка',
            'url' => route('boards.create'),
            'icon' => 'kanban',
        ];

        return response()->json(['actions' => $actions]);
    }

    protected function maskContact(?string $phone, ?string $email): string
    {
        if ($phone) {
            // +380501234567 → +380***4567
            $len = strlen($phone);
            if ($len > 4) {
                return substr($phone, 0, max(3, $len - 7)) . '***' . substr($phone, -4);
            }
            return '***';
        }

        if ($email) {
            return \App\Services\SecurityAlertService::maskEmail($email);
        }

        return '';
    }
}
