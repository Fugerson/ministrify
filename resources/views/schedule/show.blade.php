@extends('layouts.app')

@section('title', $event->title)

@section('actions')
@if($event->ministry)
@can('manage-ministry', $event->ministry)
<a href="{{ route('events.edit', $event) }}"
   class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors">
    Редагувати
</a>
@endcan
@endif
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
        <div class="flex items-start justify-between">
            <div class="flex items-center">
                <div class="w-14 h-14 rounded-xl flex items-center justify-center"
                     style="background-color: {{ $event->ministry?->color ?? '#3b82f6' }}20;">
                    <svg class="w-7 h-7" style="color: {{ $event->ministry?->color ?? '#3b82f6' }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $event->title }}</h1>
                    <p class="text-gray-500 dark:text-gray-400">{{ $event->ministry?->name ?? 'Без служіння' }}</p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $event->date?->format('d.m.Y') ?? '-' }}</p>
                <p class="text-gray-500 dark:text-gray-400">{{ $event->time?->format('H:i') ?? '-' }}</p>
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
                $confirmedResp = $event->responsibilities->where('status', 'confirmed')->count();
                $totalResp = $event->responsibilities->count();
            @endphp
            @if($totalResp > 0)
            <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                <span>{{ $confirmedResp }}/{{ $totalResp }} відповідальностей</span>
            </div>
            @endif
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
            <!-- Responsibilities -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                            <h2 class="font-semibold text-gray-900 dark:text-white">Відповідальності</h2>
                        </div>
                        @php
                            $confirmedResp = $event->responsibilities->where('status', 'confirmed')->count();
                            $totalResp = $event->responsibilities->count();
                        @endphp
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ $confirmedResp }}/{{ $totalResp }}</span>
                    </div>
                </div>

                <div class="p-4 space-y-3">
                    @forelse($event->responsibilities as $responsibility)
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                            <div class="flex-1">
                                <p class="font-medium text-gray-900 dark:text-white">{{ $responsibility->name }}</p>
                                @if($responsibility->person)
                                    <div class="mt-1 flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                                            <span class="text-primary-600 dark:text-primary-400 text-xs font-medium">
                                                {{ substr($responsibility->person->first_name, 0, 1) }}
                                            </span>
                                        </div>
                                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ $responsibility->person->full_name }}</span>
                                        <span class="text-xs px-2 py-0.5 rounded-full
                                            @if($responsibility->isConfirmed()) bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400
                                            @elseif($responsibility->isPending()) bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400
                                            @elseif($responsibility->isDeclined()) bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400
                                            @else bg-gray-100 text-gray-700 dark:bg-gray-600 dark:text-gray-300 @endif">
                                            {{ $responsibility->status_icon }} {{ $responsibility->status_label }}
                                        </span>
                                    </div>
                                @else
                                    <p class="mt-1 text-sm text-gray-400">Не призначено</p>
                                @endif
                            </div>

                            <div class="flex items-center gap-1" x-data="{ open: false }">
                                @if($responsibility->person)
                                    @if($responsibility->isPending() || $responsibility->isDeclined())
                                        <form method="POST" action="{{ route('responsibilities.resend', $responsibility) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="p-2 text-gray-400 hover:text-primary-600 dark:hover:text-primary-400" title="Надіслати повторно">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('responsibilities.unassign', $responsibility) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="p-2 text-gray-400 hover:text-red-600 dark:hover:text-red-400" title="Зняти призначення">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </form>
                                @else
                                    <div class="relative">
                                        <button @click="open = !open" type="button" class="px-3 py-1.5 text-sm bg-primary-600 hover:bg-primary-700 text-white rounded-lg">
                                            Призначити
                                        </button>
                                        <div x-show="open" @click.away="open = false" x-cloak
                                             class="absolute right-0 mt-2 w-64 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 z-50 max-h-64 overflow-y-auto">
                                            @foreach($availablePeople as $person)
                                                @if($person->telegram_chat_id)
                                                    <form method="POST" action="{{ route('responsibilities.assign', $responsibility) }}">
                                                        @csrf
                                                        <input type="hidden" name="person_id" value="{{ $person->id }}">
                                                        <button type="submit" class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2">
                                                            <div class="w-6 h-6 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-xs text-primary-600 dark:text-primary-400">
                                                                {{ substr($person->first_name, 0, 1) }}
                                                            </div>
                                                            <span>{{ $person->full_name }}</span>
                                                            <svg class="w-4 h-4 text-blue-500 ml-auto" fill="currentColor" viewBox="0 0 24 24">
                                                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"/>
                                                            </svg>
                                                        </button>
                                                    </form>
                                                @endif
                                            @endforeach
                                            @if($availablePeople->where('telegram_chat_id', '!=', null)->isEmpty())
                                                <p class="px-3 py-2 text-sm text-gray-500">Немає людей з Telegram</p>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                                <form method="POST" action="{{ route('responsibilities.destroy', $responsibility) }}" class="inline" onsubmit="return confirm('Видалити відповідальність?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-gray-400 hover:text-red-600 dark:hover:text-red-400" title="Видалити">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">Немає відповідальностей</p>
                    @endforelse

                    <!-- Add new responsibility -->
                    <form method="POST" action="{{ route('events.responsibilities.store', $event) }}" class="flex gap-2 mt-4">
                        @csrf
                        <input type="text" name="name" required placeholder="Нова відповідальність (напр. Перекус, Ігри)"
                               class="flex-1 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-primary-500 focus:border-primary-500">
                        <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">
                            Додати
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
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
                    @if($event->is_service)
                        <!-- Service Plan -->
                        <a href="{{ route('events.plan.index', $event) }}"
                           class="flex items-center gap-3 p-3 rounded-xl bg-primary-50 dark:bg-primary-900/30 hover:bg-primary-100 dark:hover:bg-primary-900/50 transition-colors text-primary-700 dark:text-primary-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <span class="font-medium">План служіння</span>
                            @if($event->planItems->isNotEmpty())
                                <span class="ml-auto text-xs bg-primary-200 dark:bg-primary-800 text-primary-700 dark:text-primary-300 px-2 py-0.5 rounded-full">
                                    {{ $event->planItems->count() }}
                                </span>
                            @endif
                        </a>
                    @endif

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

                        <x-delete-confirm
                            :action="route('events.destroy', $event)"
                            title="Видалити подію?"
                            message="Ви впевнені, що хочете видалити цю подію? Усі призначення також будуть видалені."
                            button-text="Видалити подію"
                            button-class="w-full flex items-center gap-3 p-3 rounded-xl hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors text-red-600 dark:text-red-400"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            <span>Видалити подію</span>
                        </x-delete-confirm>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
