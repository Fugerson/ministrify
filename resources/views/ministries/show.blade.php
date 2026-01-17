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
                    <div class="flex items-center gap-2">
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $ministry->name }}</h1>
                        @php $visibility = $ministry->visibility ?? 'public'; @endphp
                        @if($visibility !== 'public')
                            @php
                                $badgeColors = [
                                    'members' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
                                    'leaders' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
                                    'specific' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                                ];
                                $badgeLabels = [
                                    'members' => 'Тільки учасники',
                                    'leaders' => 'Тільки лідери',
                                    'specific' => 'Конкретні люди',
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $badgeColors[$visibility] ?? 'bg-gray-100 text-gray-800' }}">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                {{ $badgeLabels[$visibility] ?? 'Приватна' }}
                            </span>
                        @endif
                    </div>
                    @if($ministry->leader)
                        <p class="text-gray-500 dark:text-gray-400">Лідер: {{ $ministry->leader->full_name }}</p>
                    @endif
                </div>
            </div>
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
        <div class="border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
            <nav class="flex -mb-px whitespace-nowrap">
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
                <button @click="setTab('resources')" type="button"
                   :class="activeTab === 'resources' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
                   class="px-6 py-3 border-b-2 text-sm font-medium flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                    </svg>
                    Ресурси
                </button>
                @can('manage-ministry', $ministry)
                <button @click="setTab('access')" type="button"
                   :class="activeTab === 'access' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
                   class="px-6 py-3 border-b-2 text-sm font-medium flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    Доступ
                </button>
                @endcan
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
                                @if($member->photo)
                                <img src="{{ Storage::url($member->photo) }}" alt="{{ $member->full_name }}" class="w-12 h-12 rounded-full object-cover" loading="lazy">
                                @else
                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center">
                                    <span class="text-white font-medium">{{ mb_substr($member->first_name, 0, 1) }}{{ mb_substr($member->last_name, 0, 1) }}</span>
                                </div>
                                @endif
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

            <div x-show="activeTab === 'resources'"{{ $tab !== 'resources' ? ' style="display:none"' : '' }}>
                <!-- Resources actions -->
                @can('manage-ministry', $ministry)
                <div class="flex items-center justify-end gap-2 mb-4">
                    <a href="{{ route('ministries.resources', $ministry) }}"
                       class="inline-flex items-center px-3 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                        Відкрити менеджер
                    </a>
                </div>
                @endcan

                <!-- Resources list -->
                @if($resources->count() > 0)
                <div class="space-y-2">
                    @foreach($resources as $resource)
                    <div class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <div class="flex items-center gap-3">
                            @if($resource->isFolder())
                            <a href="{{ route('ministries.resources.folder', [$ministry, $resource]) }}" class="flex items-center gap-3 flex-1">
                                <svg class="w-5 h-5 text-yellow-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
                                </svg>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $resource->name }}</span>
                            </a>
                            @else
                            <div class="flex items-center gap-3">
                                @if($resource->mime_type && str_starts_with($resource->mime_type, 'image/'))
                                <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                @elseif($resource->mime_type === 'application/pdf')
                                <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                                @else
                                <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                @endif
                                <span class="font-medium text-gray-900 dark:text-white">{{ $resource->name }}</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $resource->formatted_size }}</span>
                            </div>
                            @endif
                        </div>
                        @if($resource->isFile())
                        <a href="{{ route('resources.download', $resource) }}"
                           class="p-2 text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                        </a>
                        @endif
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8">
                    <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                        </svg>
                    </div>
                    <p class="text-gray-500 dark:text-gray-400 mb-4">Немає ресурсів</p>
                    @can('manage-ministry', $ministry)
                    <a href="{{ route('ministries.resources', $ministry) }}"
                       class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Додати ресурси
                    </a>
                    @endcan
                </div>
                @endif
            </div>

            <!-- Access Settings Tab -->
            @can('manage-ministry', $ministry)
            <div x-show="activeTab === 'access'"{{ $tab !== 'access' ? ' style="display:none"' : '' }}
                 x-data="accessSettings()"
                 x-init="init()">
                <div class="max-w-2xl">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Налаштування доступу</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                        Визначте, хто може бачити цю команду та її деталі
                    </p>

                    <div class="space-y-3">
                        <!-- Public -->
                        <label class="flex items-start gap-3 p-4 border rounded-xl cursor-pointer transition-all"
                               :class="visibility === 'public' ? 'border-green-500 bg-green-50 dark:bg-green-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'">
                            <input type="radio" name="visibility" value="public" x-model="visibility" @change="saveVisibility()" class="mt-1 w-5 h-5 text-green-600 focus:ring-green-500 border-gray-300 dark:border-gray-600">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span class="font-medium text-gray-900 dark:text-white">Всі користувачі</span>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Всі користувачі церкви можуть бачити цю команду</p>
                            </div>
                        </label>

                        <!-- Members -->
                        <label class="flex items-start gap-3 p-4 border rounded-xl cursor-pointer transition-all"
                               :class="visibility === 'members' ? 'border-amber-500 bg-amber-50 dark:bg-amber-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'">
                            <input type="radio" name="visibility" value="members" x-model="visibility" @change="saveVisibility()" class="mt-1 w-5 h-5 text-amber-600 focus:ring-amber-500 border-gray-300 dark:border-gray-600">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                    </svg>
                                    <span class="font-medium text-gray-900 dark:text-white">Тільки учасники команди</span>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Тільки учасники цієї команди та адміністратори</p>
                            </div>
                        </label>

                        <!-- Leaders -->
                        <label class="flex items-start gap-3 p-4 border rounded-xl cursor-pointer transition-all"
                               :class="visibility === 'leaders' ? 'border-purple-500 bg-purple-50 dark:bg-purple-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'">
                            <input type="radio" name="visibility" value="leaders" x-model="visibility" @change="saveVisibility()" class="mt-1 w-5 h-5 text-purple-600 focus:ring-purple-500 border-gray-300 dark:border-gray-600">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                    <span class="font-medium text-gray-900 dark:text-white">Тільки лідери служінь</span>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Тільки адміністратори та лідери всіх служінь церкви</p>
                            </div>
                        </label>
                    </div>

                    <!-- Additional People with Access -->
                    <div class="mt-6 p-4 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-700/30">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                            <h4 class="font-medium text-gray-900 dark:text-white">Додаткові люди з доступом</h4>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Ці люди матимуть доступ незалежно від вибраної опції вище</p>

                        <!-- Selected people tags -->
                        <div class="flex flex-wrap gap-2 mb-3" x-show="allowedPeople.length > 0">
                            <template x-for="person in allowedPeople" :key="person.id">
                                <span class="inline-flex items-center gap-2 px-2 py-1 bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 text-sm rounded-lg">
                                    <template x-if="person.photo">
                                        <img :src="person.photo" class="w-6 h-6 rounded-full object-cover">
                                    </template>
                                    <template x-if="!person.photo">
                                        <div class="w-6 h-6 rounded-full bg-primary-500 flex items-center justify-center">
                                            <span class="text-xs text-white font-medium" x-text="person.initials"></span>
                                        </div>
                                    </template>
                                    <span x-text="person.name"></span>
                                    <button type="button" @click="removePerson(person.id)" class="hover:text-primary-900 dark:hover:text-primary-100">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </span>
                            </template>
                        </div>

                        <!-- Select people dropdown -->
                        <div class="flex gap-2">
                            <select x-model="selectedPersonId" class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                <option value="">Оберіть людину...</option>
                                <template x-for="person in availablePeopleFiltered" :key="person.id">
                                    <option :value="person.id" x-text="person.full_name"></option>
                                </template>
                            </select>
                            <button type="button" @click="addSelectedPerson()" :disabled="!selectedPersonId"
                                    class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                Додати
                            </button>
                        </div>
                    </div>

                    <!-- Save indicator -->
                    <div class="mt-4 flex items-center gap-2" x-show="saved" x-transition>
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-sm text-green-600 dark:text-green-400">Збережено</span>
                    </div>

                    <!-- Current access info -->
                    <div class="mt-8 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Поточний доступ:</h4>
                        <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                            <li class="flex items-center gap-2">
                                <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                                Адміністратори - завжди мають доступ
                            </li>
                            <li class="flex items-center gap-2" x-show="visibility === 'members'">
                                <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                                {{ $ministry->members->count() }} учасників команди
                            </li>
                            <li class="flex items-center gap-2" x-show="visibility === 'leaders'">
                                <span class="w-2 h-2 bg-purple-500 rounded-full"></span>
                                Лідери всіх служінь церкви
                            </li>
                            <li class="flex items-center gap-2" x-show="visibility === 'public'">
                                <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                                Всі користувачі церкви
                            </li>
                            <li class="flex items-center gap-2" x-show="allowedPeople.length > 0">
                                <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                                <span x-text="'+ ' + allowedPeople.length + ' додаткових людей'"></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            @endcan
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

@php
    $allowedPeopleData = collect($ministry->allowed_person_ids ?? [])->map(function($id) {
        $p = \App\Models\Person::find($id);
        if (!$p) return ['id' => $id, 'name' => 'Unknown', 'photo' => null, 'initials' => '?'];
        return [
            'id' => $id,
            'name' => $p->full_name,
            'photo' => $p->photo ? \Illuminate\Support\Facades\Storage::url($p->photo) : null,
            'initials' => mb_substr($p->first_name, 0, 1) . mb_substr($p->last_name, 0, 1)
        ];
    })->values();

    $allPeopleData = $registeredUsers->map(fn($p) => [
        'id' => $p->id,
        'full_name' => $p->full_name,
        'photo' => $p->photo ? \Illuminate\Support\Facades\Storage::url($p->photo) : null,
        'initials' => mb_substr($p->first_name, 0, 1) . mb_substr($p->last_name, 0, 1)
    ])->values();
@endphp
function accessSettings() {
    return {
        visibility: '{{ $ministry->visibility ?? "public" }}',
        allowedPeople: @json($allowedPeopleData),
        selectedPersonId: '',
        saved: false,
        allPeople: @json($allPeopleData),
        init() {},
        get availablePeopleFiltered() {
            const selectedIds = this.allowedPeople.map(p => p.id);
            return this.allPeople.filter(p => !selectedIds.includes(p.id));
        },
        addSelectedPerson() {
            if (!this.selectedPersonId) return;
            const person = this.allPeople.find(p => p.id == this.selectedPersonId);
            if (person && !this.allowedPeople.find(p => p.id === person.id)) {
                this.allowedPeople.push({ id: person.id, name: person.full_name, photo: person.photo, initials: person.initials });
                this.saveVisibility();
            }
            this.selectedPersonId = '';
        },
        removePerson(personId) {
            this.allowedPeople = this.allowedPeople.filter(p => p.id !== personId);
            this.saveVisibility();
        },
        async saveVisibility() {
            try {
                const response = await fetch('{{ route("ministries.update-visibility", $ministry) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        visibility: this.visibility,
                        allowed_person_ids: this.allowedPeople.map(p => p.id)
                    })
                });
                if (response.ok) {
                    this.saved = true;
                    setTimeout(() => this.saved = false, 2000);
                }
            } catch (error) {
                console.error('Error saving visibility:', error);
            }
        }
    }
}
</script>
@endpush
@endsection
