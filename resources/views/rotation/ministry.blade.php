@extends('layouts.app')

@section('title', 'Ротація - ' . $ministry->name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('rotation.index') }}" class="p-2 bg-gray-100 dark:bg-gray-700 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background-color: {{ $ministry->color ?? '#3b82f6' }}30;">
                    <div class="w-4 h-4 rounded-full" style="background-color: {{ $ministry->color ?? '#3b82f6' }}"></div>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $ministry->name }}</h1>
                    <p class="text-gray-500 dark:text-gray-400">Ротація служительів</p>
                </div>
            </div>
        </div>
        <button onclick="autoAssignAll()"
                class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Авто-розподіл
        </button>
    </div>

    <!-- Balance Score Card -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <h3 class="font-semibold text-gray-900 dark:text-white">Баланс навантаження</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">За останні 3 місяці</p>
            </div>
            <div class="flex items-center gap-6">
                <div class="text-center">
                    <p class="text-3xl font-bold {{ $report['balance_score'] >= 70 ? 'text-green-600' : ($report['balance_score'] >= 40 ? 'text-amber-600' : 'text-red-600') }}">
                        {{ $report['balance_score'] }}%
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Рівномірність</p>
                </div>
                <div class="text-center">
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $report['total_events'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Подій</p>
                </div>
                <div class="text-center">
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $report['average_per_member'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Середнє на особу</p>
                </div>
            </div>
        </div>

        <!-- Member Distribution -->
        <div class="mt-6">
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Розподіл по учасниках</h4>
            <div class="space-y-2 max-h-48 overflow-y-auto">
                @foreach($report['member_stats'] as $memberId => $stat)
                <div class="flex items-center gap-3">
                    <span class="text-sm text-gray-600 dark:text-gray-400 w-32 truncate">{{ $stat['name'] }}</span>
                    <div class="flex-1 h-2 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                        @php $maxAssignments = max(array_column($report['member_stats'], 'assignments')); @endphp
                        <div class="h-full rounded-full bg-primary-500"
                             style="width: {{ $maxAssignments > 0 ? ($stat['assignments'] / $maxAssignments) * 100 : 0 }}%"></div>
                    </div>
                    <span class="text-sm font-medium text-gray-900 dark:text-white w-8 text-right">{{ $stat['assignments'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Upcoming Events -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="font-semibold text-gray-900 dark:text-white">Найближчі події</h3>
        </div>
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($events as $event)
            <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-700 flex flex-col items-center justify-center flex-shrink-0">
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $event->date->translatedFormat('M') }}</span>
                            <span class="text-lg font-bold text-gray-900 dark:text-white">{{ $event->date->format('d') }}</span>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-white">{{ $event->title }}</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $event->date->translatedFormat('l') }} • {{ $event->time?->format('H:i') }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <!-- Assignments Preview -->
                        <div class="flex -space-x-2">
                            @foreach($event->assignments->take(4) as $assignment)
                            @if($assignment->person)
                            <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center border-2 border-white dark:border-gray-800"
                                 title="{{ $assignment->person->full_name }}">
                                <span class="text-xs font-medium text-primary-600 dark:text-primary-400">
                                    {{ mb_substr($assignment->person->first_name, 0, 1) }}
                                </span>
                            </div>
                            @endif
                            @endforeach
                            @if($event->assignments->count() > 4)
                            <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center border-2 border-white dark:border-gray-800">
                                <span class="text-xs font-medium text-gray-600 dark:text-gray-400">+{{ $event->assignments->count() - 4 }}</span>
                            </div>
                            @endif
                        </div>

                        <!-- Status & Actions -->
                        @php
                            $totalPositions = $ministry->positions->sum('max_per_event') ?: $ministry->positions->count();
                            $filledPositions = $event->assignments->count();
                            $isFullyStaffed = $filledPositions >= $totalPositions;
                        @endphp

                        <div class="flex items-center gap-2">
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $isFullyStaffed ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400' }}">
                                {{ $filledPositions }}/{{ $totalPositions }}
                            </span>
                            <button onclick="autoAssignEvent({{ $event->id }})"
                                    class="p-2 text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors"
                                    title="Авто-розподіл">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                            </button>
                            <a href="{{ route('events.show', $event) }}"
                               class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                               title="Переглянути">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Position Details (expandable) -->
                @if($ministry->positions->count() > 0)
                <div class="mt-4 pl-16 grid grid-cols-2 md:grid-cols-4 gap-2">
                    @foreach($ministry->positions as $position)
                    @php
                        $positionAssignments = $event->assignments->where('position_id', $position->id);
                        $needed = $position->max_per_event ?? 1;
                        $filled = $positionAssignments->count();
                    @endphp
                    <div class="px-3 py-2 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $position->name }}</p>
                        <div class="flex items-center justify-between mt-1">
                            <div class="flex -space-x-1">
                                @foreach($positionAssignments->take(3) as $assignment)
                                <div class="w-5 h-5 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center text-[10px] font-medium text-primary-600 dark:text-primary-400 border border-white dark:border-gray-700">
                                    {{ mb_substr($assignment->person?->first_name ?? '?', 0, 1) }}
                                </div>
                                @endforeach
                            </div>
                            <span class="text-xs font-medium {{ $filled >= $needed ? 'text-green-600' : 'text-amber-600' }}">
                                {{ $filled }}/{{ $needed }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            @empty
            <div class="p-8 text-center">
                <p class="text-gray-500 dark:text-gray-400">Немає запланованих подій на найближчі 4 тижні</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Members Table -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="font-semibold text-gray-900 dark:text-white">Учасники команди</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Учасник</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Позиції</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Призначень (3 міс)</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">% участі</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($members as $member)
                    @php
                        $memberStat = $report['member_stats'][$member->id] ?? ['assignments' => 0, 'percentage' => 0];
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center">
                                    <span class="text-sm font-medium text-primary-600 dark:text-primary-400">
                                        {{ mb_substr($member->first_name, 0, 1) }}
                                    </span>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $member->full_name }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $member->phone }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                @php
                                    $memberPositionIds = $member->pivot->position_ids ?? [];
                                    if (is_string($memberPositionIds)) {
                                        $memberPositionIds = json_decode($memberPositionIds, true) ?? [];
                                    }
                                @endphp
                                @foreach($memberPositionIds as $positionId)
                                    @if(isset($positions[$positionId]))
                                    <span class="px-2 py-0.5 text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded">
                                        {{ $positions[$positionId]->name }}
                                    </span>
                                    @endif
                                @endforeach
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="font-medium text-gray-900 dark:text-white">{{ $memberStat['assignments'] }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <div class="w-16 h-2 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full bg-primary-500" style="width: {{ min(100, $memberStat['percentage']) }}%"></div>
                                </div>
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ $memberStat['percentage'] }}%</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Result Toast -->
<div id="resultToast" class="fixed bottom-4 right-4 z-50 hidden transform transition-transform">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-4 max-w-sm">
        <div class="flex items-start gap-3">
            <div id="toastIcon" class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0"></div>
            <div>
                <p id="toastTitle" class="font-medium text-gray-900 dark:text-white"></p>
                <p id="toastMessage" class="text-sm text-gray-500 dark:text-gray-400 mt-1"></p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showToast(type, title, message) {
    const toast = document.getElementById('resultToast');
    const icon = document.getElementById('toastIcon');
    const titleEl = document.getElementById('toastTitle');
    const messageEl = document.getElementById('toastMessage');

    titleEl.textContent = title;
    messageEl.textContent = message;

    if (type === 'success') {
        icon.innerHTML = '<svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
        icon.className = 'w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 bg-green-100 dark:bg-green-900/30';
    } else {
        icon.innerHTML = '<svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
        icon.className = 'w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 bg-red-100 dark:bg-red-900/30';
    }

    toast.classList.remove('hidden');
    setTimeout(() => {
        toast.classList.add('hidden');
    }, 5000);
}

async function autoAssignEvent(eventId) {
    try {
        const response = await fetch(`/rotation/event/${eventId}/auto-assign`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        });

        const data = await response.json().catch(() => ({}));

        if (data.success) {
            showToast('success', 'Розподіл завершено', data.message);
            setTimeout(() => location.reload(), 2000);
        } else {
            showToast('error', 'Помилка', 'Не вдалося виконати розподіл');
        }
    } catch (error) {
        showToast('error', 'Помилка', 'Помилка з\'єднання');
    }
}

async function autoAssignAll() {
    if (!confirm('Запустити авто-розподіл для всіх подій на 4 тижні?')) return;

    try {
        const response = await fetch(`/rotation/ministry/{{ $ministry->id }}/auto-assign`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ weeks: 4 }),
        });

        const data = await response.json().catch(() => ({}));

        if (data.success) {
            showToast('success', 'Розподіл завершено',
                `${data.summary.assigned} призначень для ${data.summary.events} подій`);
            setTimeout(() => location.reload(), 2000);
        } else {
            showToast('error', 'Помилка', 'Не вдалося виконати розподіл');
        }
    } catch (error) {
        showToast('error', 'Помилка', 'Помилка з\'єднання');
    }
}
</script>
@endpush
@endsection
