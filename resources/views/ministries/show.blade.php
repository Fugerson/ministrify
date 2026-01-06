@extends('layouts.app')

@section('title', $ministry->name)

@section('actions')
@can('manage-ministry', $ministry)
<a href="{{ route('ministries.edit', $ministry) }}"
   class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors">
    Налаштування
</a>
@endcan
@endsection

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                @if($ministry->color)
                    <div class="w-4 h-4 rounded-full" style="background-color: {{ $ministry->color }}"></div>
                @endif
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $ministry->name }}</h1>
                    @if($ministry->leader)
                        <p class="text-gray-500 dark:text-gray-400">Лідер: {{ $ministry->leader->full_name }}</p>
                    @endif
                </div>
            </div>

            @if($ministry->monthly_budget)
                <div class="text-right">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Бюджет на місяць</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($ministry->monthly_budget, 0, ',', ' ') }} ₴</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Витрачено: {{ number_format($ministry->spent_this_month, 0, ',', ' ') }} ₴</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Tabs -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm"
         x-data="{
            activeTab: '{{ $tab }}',
            setTab(tab) {
                this.activeTab = tab;
                const url = new URL(window.location);
                url.searchParams.set('tab', tab);
                history.pushState({}, '', url);
            }
         }">
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="flex -mb-px">
                <button @click="setTab('schedule')" type="button"
                   :class="activeTab === 'schedule' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
                   class="px-6 py-3 border-b-2 text-sm font-medium">
                    Розклад
                </button>
                <button @click="setTab('members')" type="button"
                   :class="activeTab === 'members' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
                   class="px-6 py-3 border-b-2 text-sm font-medium">
                    Учасники ({{ $ministry->members->count() }})
                </button>
                <button @click="setTab('positions')" type="button"
                   :class="activeTab === 'positions' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
                   class="px-6 py-3 border-b-2 text-sm font-medium">
                    Позиції
                </button>
                <button @click="setTab('meetings')" type="button"
                   :class="activeTab === 'meetings' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
                   class="px-6 py-3 border-b-2 text-sm font-medium flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Зустрічі
                    @if($ministry->meetings->where('date', '>=', now())->count() > 0)
                    <span class="ml-1 px-1.5 py-0.5 text-xs bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 rounded-full">{{ $ministry->meetings->where('date', '>=', now())->count() }}</span>
                    @endif
                </button>
                @can('manage-ministry', $ministry)
                <button @click="setTab('expenses')" type="button"
                   :class="activeTab === 'expenses' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
                   class="px-6 py-3 border-b-2 text-sm font-medium">
                    Витрати
                </button>
                @endcan
                <button @click="setTab('rotation')" type="button"
                   :class="activeTab === 'rotation' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
                   class="px-6 py-3 border-b-2 text-sm font-medium flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Ротація
                </button>
            </nav>
        </div>

        <div class="p-6">
            <div x-show="activeTab === 'schedule'"{{ $tab !== 'schedule' ? ' style="display:none"' : '' }}>
                <!-- Upcoming events -->
                @if($ministry->events->count() > 0)
                    <div class="space-y-4">
                        @foreach($ministry->events as $event)
                            <a href="{{ route('events.show', $event) }}"
                               class="block p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $event->title }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $event->date->format('d.m.Y') }} о {{ $event->time->format('H:i') }}</p>
                                    </div>
                                    <div class="text-sm">
                                        @if($event->isFullyStaffed())
                                            <span class="text-green-600 dark:text-green-400">✅ {{ $event->filled_positions_count }}/{{ $event->total_positions_count }}</span>
                                        @else
                                            <span class="text-yellow-600 dark:text-yellow-400">⚠ {{ $event->filled_positions_count }}/{{ $event->total_positions_count }}</span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-center text-gray-500 dark:text-gray-400 py-8">Немає запланованих подій</p>
                @endif

                @can('manage-ministry', $ministry)
                <div class="mt-4">
                    <a href="{{ route('events.create', ['ministry' => $ministry->id]) }}"
                       class="inline-flex items-center text-primary-600 dark:text-primary-400 hover:text-primary-500">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Створити подію
                    </a>
                </div>
                @endcan
            </div>

            <div x-show="activeTab === 'members'"{{ $tab !== 'members' ? ' style="display:none"' : '' }}>
                <!-- Add member form -->
                @can('manage-ministry', $ministry)
                @if($availablePeople->count() > 0)
                <form method="POST" action="{{ route('ministries.members.add', $ministry) }}" class="mb-6 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                    @csrf
                    <div class="flex gap-3">
                        <div class="flex-1">
                            <x-person-select name="person_id" :people="$availablePeople" placeholder="Оберіть людину..." :required="true" :nullable="false" />
                        </div>
                        <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg whitespace-nowrap">
                            Додати
                        </button>
                    </div>
                </form>
                @endif
                @endcan

                <!-- Members list -->
                @if($ministry->members->count() > 0)
                <div class="space-y-2">
                    @foreach($ministry->members as $member)
                        @php
                            $positionIds = is_array($member->pivot->position_ids)
                                ? $member->pivot->position_ids
                                : json_decode($member->pivot->position_ids ?? '[]', true);
                            $positions = $ministry->positions->whereIn('id', $positionIds ?? []);
                        @endphp
                        <div class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                    <span class="text-gray-600 dark:text-gray-400">{{ substr($member->first_name, 0, 1) }}</span>
                                </div>
                                <div class="ml-3">
                                    <a href="{{ route('people.show', $member) }}" class="font-medium text-gray-900 dark:text-white hover:text-primary-600 dark:hover:text-primary-400">
                                        {{ $member->full_name }}
                                    </a>
                                    @if($positions->count() > 0)
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $positions->pluck('name')->implode(', ') }}</p>
                                    @endif
                                </div>
                            </div>
                            @can('manage-ministry', $ministry)
                            <form method="POST" action="{{ route('ministries.members.remove', [$ministry, $member]) }}"
                                  onsubmit="return confirm('Видалити учасника?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 text-sm">
                                    Видалити
                                </button>
                            </form>
                            @endcan
                        </div>
                    @endforeach
                </div>
                @else
                <p class="text-center text-gray-500 dark:text-gray-400 py-8">Немає учасників</p>
                @endif
            </div>

            <div x-show="activeTab === 'positions'"{{ $tab !== 'positions' ? ' style="display:none"' : '' }}>
                <!-- Positions -->
                <div class="space-y-2">
                    @foreach($ministry->positions as $position)
                        <div class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                            <span class="font-medium text-gray-900 dark:text-white">{{ $position->name }}</span>
                            @can('manage-ministry', $ministry)
                            <div class="flex items-center space-x-2">
                                <form method="POST" action="{{ route('positions.destroy', $position) }}"
                                      onsubmit="return confirm('Видалити позицію?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 text-sm">
                                        Видалити
                                    </button>
                                </form>
                            </div>
                            @endcan
                        </div>
                    @endforeach
                </div>

                @can('manage-ministry', $ministry)
                <form method="POST" action="{{ route('positions.store', $ministry) }}" class="mt-4 flex gap-2">
                    @csrf
                    <input type="text" name="name" placeholder="Нова позиція" required
                           class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg">
                        Додати
                    </button>
                </form>
                @endcan
            </div>

            <div x-show="activeTab === 'meetings'"{{ $tab !== 'meetings' ? ' style="display:none"' : '' }}>
                <!-- Meetings -->
                @if($ministry->meetings->count() > 0)
                    <div class="space-y-3">
                        @foreach($ministry->meetings as $meeting)
                            <a href="{{ route('meetings.show', [$ministry, $meeting]) }}"
                               class="block p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $meeting->title }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $meeting->date->format('d.m.Y') }} о {{ $meeting->time->format('H:i') }}
                                            @if($meeting->location)
                                                • {{ $meeting->location }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $meeting->attendees->count() }} учасників
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-center text-gray-500 dark:text-gray-400 py-8">Немає запланованих зустрічей</p>
                @endif

                @can('manage-ministry', $ministry)
                <div class="mt-4">
                    <a href="{{ route('meetings.create', $ministry) }}"
                       class="inline-flex items-center text-primary-600 dark:text-primary-400 hover:text-primary-500">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Створити зустріч
                    </a>
                </div>
                @endcan
            </div>

            <div x-show="activeTab === 'expenses'"{{ $tab !== 'expenses' ? ' style="display:none"' : '' }}>
                <!-- Expenses -->
                @if($ministry->expenses->count() > 0)
                    <div class="space-y-2">
                        @foreach($ministry->expenses as $expense)
                            <div class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $expense->description }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $expense->date->format('d.m.Y') }}
                                        @if($expense->category)
                                            • {{ $expense->category->name }}
                                        @endif
                                    </p>
                                </div>
                                <span class="font-medium text-gray-900 dark:text-white">{{ number_format($expense->amount, 0, ',', ' ') }} ₴</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-center text-gray-500 dark:text-gray-400 py-8">Немає витрат за цей місяць</p>
                @endif

                <div class="mt-4">
                    <a href="{{ route('finances.expenses.create', ['ministry' => $ministry->id]) }}"
                       class="inline-flex items-center text-primary-600 dark:text-primary-400 hover:text-primary-500">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Додати витрату
                    </a>
                </div>
            </div>

            <div x-show="activeTab === 'rotation'"{{ $tab !== 'rotation' ? ' style="display:none"' : '' }}>
                <!-- Rotation Info -->
                <div class="bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/30 dark:to-purple-900/30 rounded-xl border border-indigo-100 dark:border-indigo-800 p-4 mb-6">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-lg bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-white">Автоматичний розподіл</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                Система враховує навантаження, навички та доступність служителів.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('rotation.ministry', $ministry) }}"
                       class="flex-1 inline-flex items-center justify-center px-4 py-3 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Переглянути розклад
                    </a>
                    <button type="button" onclick="autoAssignMinistry({{ $ministry->id }}, '{{ $ministry->name }}')"
                            class="flex-1 inline-flex items-center justify-center px-4 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Авто-розподіл
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Linked Tasks -->
    <x-linked-cards entityType="ministry" :entityId="$ministry->id" :boards="$boards" />

    <a href="{{ route('ministries.index') }}" class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-sm">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Назад до списку
    </a>
</div>

<!-- Auto-Assign Modal -->
<div id="autoAssignModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeAutoAssignModal()"></div>
    <div class="absolute inset-4 md:inset-auto md:top-1/2 md:left-1/2 md:-translate-x-1/2 md:-translate-y-1/2 md:w-full md:max-w-lg bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white" id="modalTitle">Авто-розподіл</h3>
            <button onclick="closeAutoAssignModal()" class="p-2 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="p-6">
            <div id="modalContent"></div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentMinistryId = null;

function autoAssignMinistry(ministryId, ministryName) {
    currentMinistryId = ministryId;
    document.getElementById('modalTitle').textContent = 'Авто-розподіл: ' + ministryName;
    document.getElementById('modalContent').innerHTML = `
        <div class="space-y-4">
            <p class="text-gray-600 dark:text-gray-400">Система автоматично призначить служителів на всі події.</p>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Період</label>
                <select id="weeksSelect" class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl">
                    <option value="1">1 тиждень</option>
                    <option value="2">2 тижні</option>
                    <option value="4" selected>4 тижні</option>
                </select>
            </div>
            <div class="flex gap-3">
                <button onclick="closeAutoAssignModal()" class="flex-1 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium rounded-xl">Скасувати</button>
                <button onclick="runAutoAssign()" class="flex-1 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl">Запустити</button>
            </div>
        </div>
    `;
    document.getElementById('autoAssignModal').classList.remove('hidden');
}

function closeAutoAssignModal() {
    document.getElementById('autoAssignModal').classList.add('hidden');
}

async function runAutoAssign() {
    const weeks = document.getElementById('weeksSelect').value;
    document.getElementById('modalContent').innerHTML = `
        <div class="flex flex-col items-center justify-center py-8">
            <svg class="animate-spin h-10 w-10 text-primary-500 mb-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <p class="text-gray-600 dark:text-gray-400">Виконується розподіл...</p>
        </div>
    `;
    try {
        const response = await fetch(`/rotation/ministry/${currentMinistryId}/auto-assign`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
            body: JSON.stringify({ weeks: parseInt(weeks) }),
        });
        const data = await response.json();
        if (data.success) {
            document.getElementById('modalContent').innerHTML = `
                <div class="text-center">
                    <div class="w-16 h-16 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Розподіл завершено!</h4>
                    <div class="grid grid-cols-3 gap-4 mb-6">
                        <div class="text-center p-3 bg-gray-50 dark:bg-gray-700 rounded-xl"><p class="text-2xl font-bold">${data.summary.events}</p><p class="text-xs text-gray-500">Подій</p></div>
                        <div class="text-center p-3 bg-green-50 dark:bg-green-900/30 rounded-xl"><p class="text-2xl font-bold text-green-600">${data.summary.assigned}</p><p class="text-xs text-gray-500">Призначено</p></div>
                        <div class="text-center p-3 bg-amber-50 dark:bg-amber-900/30 rounded-xl"><p class="text-2xl font-bold text-amber-600">${data.summary.unassigned}</p><p class="text-xs text-gray-500">Не заповнено</p></div>
                    </div>
                    <button onclick="closeAutoAssignModal()" class="w-full px-4 py-2 bg-primary-600 text-white font-medium rounded-xl">Закрити</button>
                </div>
            `;
        } else {
            document.getElementById('modalContent').innerHTML = `<div class="text-center py-4"><p class="text-red-600">Помилка при розподілі</p><button onclick="closeAutoAssignModal()" class="mt-4 px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded-xl">Закрити</button></div>`;
        }
    } catch (error) {
        document.getElementById('modalContent').innerHTML = `<div class="text-center py-4"><p class="text-red-600">Помилка з'єднання</p><button onclick="closeAutoAssignModal()" class="mt-4 px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded-xl">Закрити</button></div>`;
    }
}
</script>
@endpush
@endsection
