@extends('layouts.app')

@section('title', $ministry->icon . ' ' . $ministry->name)

@section('actions')
@can('manage-ministry', $ministry)
<a href="{{ route('ministries.edit', $ministry) }}"
   class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">
    Налаштування
</a>
@endcan
@endsection

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <span class="text-4xl">{{ $ministry->icon }}</span>
                <div class="ml-4">
                    <h1 class="text-2xl font-bold text-gray-900">{{ $ministry->name }}</h1>
                    @if($ministry->leader)
                        <p class="text-gray-500">Лідер: {{ $ministry->leader->full_name }}</p>
                    @endif
                </div>
            </div>

            @if($ministry->monthly_budget)
                <div class="text-right">
                    <p class="text-sm text-gray-500">Бюджет на місяць</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($ministry->monthly_budget, 0, ',', ' ') }} &#8372;</p>
                    <p class="text-sm text-gray-500">Витрачено: {{ number_format($ministry->spent_this_month, 0, ',', ' ') }} &#8372;</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Tabs -->
    <div class="bg-white rounded-lg shadow">
        <div class="border-b">
            <nav class="flex -mb-px">
                <a href="{{ route('ministries.show', ['ministry' => $ministry, 'tab' => 'schedule']) }}"
                   class="px-6 py-3 border-b-2 text-sm font-medium {{ $tab === 'schedule' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Розклад
                </a>
                <a href="{{ route('ministries.show', ['ministry' => $ministry, 'tab' => 'members']) }}"
                   class="px-6 py-3 border-b-2 text-sm font-medium {{ $tab === 'members' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Учасники ({{ $ministry->members->count() }})
                </a>
                <a href="{{ route('ministries.show', ['ministry' => $ministry, 'tab' => 'positions']) }}"
                   class="px-6 py-3 border-b-2 text-sm font-medium {{ $tab === 'positions' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Позиції
                </a>
                @can('manage-ministry', $ministry)
                <a href="{{ route('ministries.show', ['ministry' => $ministry, 'tab' => 'expenses']) }}"
                   class="px-6 py-3 border-b-2 text-sm font-medium {{ $tab === 'expenses' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
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
                               class="block p-4 border rounded-lg hover:bg-gray-50">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $event->title }}</p>
                                        <p class="text-sm text-gray-500">{{ $event->date->format('d.m.Y') }} о {{ $event->time->format('H:i') }}</p>
                                    </div>
                                    <div class="text-sm">
                                        @if($event->isFullyStaffed())
                                            <span class="text-green-600">&#9989; {{ $event->filled_positions_count }}/{{ $event->total_positions_count }}</span>
                                        @else
                                            <span class="text-yellow-600">&#9888; {{ $event->filled_positions_count }}/{{ $event->total_positions_count }}</span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-center text-gray-500 py-8">Немає запланованих подій</p>
                @endif

                @can('manage-ministry', $ministry)
                <div class="mt-4">
                    <a href="{{ route('events.create', ['ministry' => $ministry->id]) }}"
                       class="inline-flex items-center text-primary-600 hover:text-primary-500">
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
                        <div class="flex items-center justify-between p-3 border rounded-lg">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
                                    <span class="text-gray-600">{{ substr($member->first_name, 0, 1) }}</span>
                                </div>
                                <div class="ml-3">
                                    <a href="{{ route('people.show', $member) }}" class="font-medium text-gray-900 hover:text-primary-600">
                                        {{ $member->full_name }}
                                    </a>
                                    @if($positions->count() > 0)
                                        <p class="text-sm text-gray-500">{{ $positions->pluck('name')->implode(', ') }}</p>
                                    @endif
                                </div>
                            </div>
                            @can('manage-ministry', $ministry)
                            <form method="POST" action="{{ route('ministries.members.remove', [$ministry, $member]) }}"
                                  onsubmit="return confirm('Видалити учасника?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
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
                        <div class="flex items-center justify-between p-3 border rounded-lg">
                            <span class="font-medium text-gray-900">{{ $position->name }}</span>
                            @can('manage-ministry', $ministry)
                            <div class="flex items-center space-x-2">
                                <form method="POST" action="{{ route('positions.destroy', $position) }}"
                                      onsubmit="return confirm('Видалити позицію?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
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
                           class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
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
                            <div class="flex items-center justify-between p-3 border rounded-lg">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $expense->description }}</p>
                                    <p class="text-sm text-gray-500">
                                        {{ $expense->date->format('d.m.Y') }}
                                        @if($expense->category)
                                            &bull; {{ $expense->category->name }}
                                        @endif
                                    </p>
                                </div>
                                <span class="font-medium text-gray-900">{{ number_format($expense->amount, 0, ',', ' ') }} &#8372;</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-center text-gray-500 py-8">Немає витрат за цей місяць</p>
                @endif

                <div class="mt-4">
                    <a href="{{ route('expenses.create') }}"
                       class="inline-flex items-center text-primary-600 hover:text-primary-500">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Додати витрату
                    </a>
                </div>
            @endif
        </div>
    </div>

    <a href="{{ route('ministries.index') }}" class="inline-block text-gray-600 hover:text-gray-900">
        &larr; Назад до списку
    </a>
</div>
@endsection
