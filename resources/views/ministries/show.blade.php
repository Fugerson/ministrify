@extends('layouts.app')

@section('title', $ministry->icon . ' ' . $ministry->name)

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
    <!-- Linked Tasks -->
    <x-linked-cards entityType="ministry" :entityId="$ministry->id" :boards="$boards" />

    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <span class="text-4xl">{{ $ministry->icon }}</span>
                <div class="ml-4">
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
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="flex -mb-px">
                <a href="{{ route('ministries.show', ['ministry' => $ministry, 'tab' => 'schedule']) }}"
                   class="px-6 py-3 border-b-2 text-sm font-medium {{ $tab === 'schedule' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600' }}">
                    Розклад
                </a>
                <a href="{{ route('ministries.show', ['ministry' => $ministry, 'tab' => 'members']) }}"
                   class="px-6 py-3 border-b-2 text-sm font-medium {{ $tab === 'members' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600' }}">
                    Учасники ({{ $ministry->members->count() }})
                </a>
                <a href="{{ route('ministries.show', ['ministry' => $ministry, 'tab' => 'positions']) }}"
                   class="px-6 py-3 border-b-2 text-sm font-medium {{ $tab === 'positions' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600' }}">
                    Позиції
                </a>
                @can('manage-ministry', $ministry)
                <a href="{{ route('ministries.show', ['ministry' => $ministry, 'tab' => 'expenses']) }}"
                   class="px-6 py-3 border-b-2 text-sm font-medium {{ $tab === 'expenses' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600' }}">
                    Витрати
                </a>
                @endcan
            </nav>
        </div>

        <div class="p-6">
            @if($tab === 'schedule')
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

            @elseif($tab === 'members')
                <!-- Members list -->
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

            @elseif($tab === 'positions')
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

            @elseif($tab === 'expenses')
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
                    <a href="{{ route('finances.expenses.create') }}"
                       class="inline-flex items-center text-primary-600 dark:text-primary-400 hover:text-primary-500">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Додати витрату
                    </a>
                </div>
            @endif
        </div>
    </div>

    <a href="{{ route('ministries.index') }}" class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-sm">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Назад до списку
    </a>
</div>
@endsection
