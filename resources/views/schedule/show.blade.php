@extends('layouts.app')

@section('title', $event->title)

@section('actions')
@can('manage-ministry', $event->ministry)
<div class="flex items-center space-x-2">
    <form method="POST" action="{{ route('assignments.notify-all', $event) }}">
        @csrf
        <button type="submit" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors">
            Надіслати сповіщення
        </button>
    </form>
    <a href="{{ route('events.edit', $event) }}"
       class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors">
        Редагувати
    </a>
</div>
@endcan
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
        <div class="flex items-start justify-between">
            <div class="flex items-center">
                <div class="w-14 h-14 rounded-xl flex items-center justify-center"
                     style="background-color: {{ $event->ministry->color }}20;">
                    <svg class="w-7 h-7" style="color: {{ $event->ministry->color }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $event->title }}</h1>
                    <p class="text-gray-500 dark:text-gray-400">{{ $event->ministry->name }}</p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $event->date->format('d.m.Y') }}</p>
                <p class="text-gray-500 dark:text-gray-400">{{ $event->time->format('H:i') }}</p>
            </div>
        </div>

        @if($event->notes)
            <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-xl">
                <p class="text-sm text-gray-600 dark:text-gray-300">{{ $event->notes }}</p>
            </div>
        @endif

        <!-- Quick stats -->
        <div class="mt-4 flex items-center gap-4 text-sm">
            @php
                $confirmed = $event->assignments->where('status', 'confirmed')->count();
                $total = $event->ministry->positions->count();
            @endphp
            <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span>{{ $confirmed }}/{{ $total }} позицій заповнено</span>
            </div>
            @if($event->checklist)
                <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    <span>Чеклист: {{ $event->checklist->progress }}%</span>
                </div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Positions and assignments -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <h2 class="font-semibold text-gray-900 dark:text-white">Позиції</h2>
                    </div>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $event->assignments->count() }} / {{ $event->ministry->positions->count() }}</span>
                </div>

                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($event->ministry->positions as $position)
                        @php
                            $assignment = $event->assignments->firstWhere('position_id', $position->id);
                        @endphp
                        <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $position->name }}</p>

                                    @if($assignment)
                                        <div class="mt-2 flex items-center">
                                            @if($assignment->person->photo)
                                                <img src="{{ Storage::url($assignment->person->photo) }}"
                                                     class="w-8 h-8 rounded-full object-cover">
                                            @else
                                                <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                                                    <span class="text-primary-600 dark:text-primary-400 text-sm font-medium">
                                                        {{ substr($assignment->person->first_name, 0, 1) }}
                                                    </span>
                                                </div>
                                            @endif
                                            <div class="ml-3">
                                                <a href="{{ route('people.show', $assignment->person) }}"
                                                   class="text-gray-900 dark:text-white hover:text-primary-600 dark:hover:text-primary-400 font-medium">
                                                    {{ $assignment->person->full_name }}
                                                </a>
                                                <p class="text-sm">
                                                    @if($assignment->isConfirmed())
                                                        <span class="inline-flex items-center gap-1 text-green-600 dark:text-green-400">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                            </svg>
                                                            Підтверджено
                                                        </span>
                                                    @elseif($assignment->isPending())
                                                        <span class="inline-flex items-center gap-1 text-yellow-600 dark:text-yellow-400">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                            </svg>
                                                            Очікує
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center gap-1 text-red-600 dark:text-red-400">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                            </svg>
                                                            Відхилено
                                                        </span>
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    @else
                                        <p class="mt-2 text-sm text-yellow-600 dark:text-yellow-400 flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                            </svg>
                                            Не призначено
                                        </p>
                                    @endif
                                </div>

                                @can('manage-ministry', $event->ministry)
                                <div>
                                    @if($assignment)
                                        <form method="POST" action="{{ route('assignments.destroy', $assignment) }}"
                                              onsubmit="return confirm('Видалити призначення?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @else
                                        <div x-data="{ open: false }" class="relative">
                                            <button @click="open = !open" type="button"
                                                    class="px-3 py-1.5 bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400 text-sm font-medium rounded-lg hover:bg-primary-200 dark:hover:bg-primary-900/50 transition-colors">
                                                Призначити
                                            </button>

                                            <div x-show="open" x-cloak @click.away="open = false"
                                                 x-transition:enter="transition ease-out duration-100"
                                                 x-transition:enter-start="opacity-0 scale-95"
                                                 x-transition:enter-end="opacity-100 scale-100"
                                                 class="absolute right-0 mt-2 w-64 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg z-10 overflow-hidden">
                                                <div class="p-2 max-h-64 overflow-y-auto">
                                                    @foreach($availablePeople->filter(fn($p) => $p->hasPositionInMinistry($event->ministry, $position)) as $person)
                                                        <form method="POST" action="{{ route('assignments.store', $event) }}">
                                                            @csrf
                                                            <input type="hidden" name="position_id" value="{{ $position->id }}">
                                                            <input type="hidden" name="person_id" value="{{ $person->id }}">
                                                            <button type="submit"
                                                                    class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg flex items-center gap-2">
                                                                @if($person->photo)
                                                                    <img src="{{ Storage::url($person->photo) }}" class="w-6 h-6 rounded-full object-cover">
                                                                @else
                                                                    <div class="w-6 h-6 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center text-xs">
                                                                        {{ substr($person->first_name, 0, 1) }}
                                                                    </div>
                                                                @endif
                                                                {{ $person->full_name }}
                                                            </button>
                                                        </form>
                                                    @endforeach

                                                    @if($availablePeople->filter(fn($p) => $p->hasPositionInMinistry($event->ministry, $position))->isEmpty())
                                                        <p class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400">Немає доступних людей</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                @endcan
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-8">
            <!-- Linked Tasks from Boards -->
            <x-linked-cards entityType="event" :entityId="$event->id" :boards="$boards" />

            <!-- Checklist -->
            @can('manage-ministry', $event->ministry)
                <x-event-checklist :event="$event" :templates="$checklistTemplates" />
            @endcan

            <!-- Quick Actions -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Швидкі дії</h3>
                <div class="space-y-2">
                    <!-- Add to Google Calendar -->
                    <a href="{{ route('events.google', $event) }}" target="_blank"
                       class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-gray-700 dark:text-gray-300">
                        <svg class="w-5 h-5 text-gray-400" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19.5 3h-15A1.5 1.5 0 003 4.5v15A1.5 1.5 0 004.5 21h15a1.5 1.5 0 001.5-1.5v-15A1.5 1.5 0 0019.5 3zM8.25 18.75h-2.5v-2.5h2.5v2.5zm0-4h-2.5v-2.5h2.5v2.5zm0-4h-2.5v-2.5h2.5v2.5zm4 8h-2.5v-2.5h2.5v2.5zm0-4h-2.5v-2.5h2.5v2.5zm0-4h-2.5v-2.5h2.5v2.5zm4 8h-2.5v-2.5h2.5v2.5zm0-4h-2.5v-2.5h2.5v2.5zm0-4h-2.5v-2.5h2.5v2.5z"/>
                        </svg>
                        <span>Додати в Google Calendar</span>
                    </a>

                    <a href="{{ route('schedule') }}"
                       class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-gray-700 dark:text-gray-300">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>Назад до розкладу</span>
                    </a>

                    @can('manage-ministry', $event->ministry)
                        <a href="{{ route('events.edit', $event) }}"
                           class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-gray-700 dark:text-gray-300">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            <span>Редагувати подію</span>
                        </a>

                        <form method="POST" action="{{ route('events.destroy', $event) }}"
                              onsubmit="return confirm('Ви впевнені, що хочете видалити цю подію?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="w-full flex items-center gap-3 p-3 rounded-xl hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors text-red-600 dark:text-red-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                <span>Видалити подію</span>
                            </button>
                        </form>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
