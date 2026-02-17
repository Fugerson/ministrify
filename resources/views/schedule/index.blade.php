@extends('layouts.app')

@section('title', __('Події'))

@section('actions')
@if(auth()->user()->can('create', \App\Models\Event::class))
<a href="{{ route('events.create') }}"
   class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-xl transition-colors">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    {{ __('Нова подія') }}
</a>
@endif
@endsection

@section('content')
<div class="space-y-4">
    <!-- View Options -->
    <div id="schedule-options" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-2">
                <a href="{{ route('events.index') }}"
                   class="px-4 py-2 text-sm font-medium rounded-xl bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300">
                    {{ __('Список') }}
                </a>
                <a href="{{ route('schedule') }}"
                   class="px-4 py-2 text-sm font-medium rounded-xl text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700">
                    {{ __('Календар') }}
                </a>
            </div>

            @if($ministries->count() > 0)
            <form method="GET" class="flex items-center gap-2">
                <select name="ministry" onchange="this.form.submit()"
                        class="rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                    <option value="">{{ __('Всі команди') }}</option>
                    @foreach($ministries as $ministry)
                        <option value="{{ $ministry->id }}" {{ request('ministry') == $ministry->id ? 'selected' : '' }}>
                            {{ $ministry->name }}
                        </option>
                    @endforeach
                </select>
            </form>
            @endif
        </div>
    </div>

    <!-- Events List -->
    <div id="events-list" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        @if($events->count() > 0)
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($events as $event)
                <a href="{{ route('events.show', $event) }}"
                   class="block px-4 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1 flex-wrap">
                                @if($event->ministry)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                          style="background-color: {{ $event->ministry->color }}30; color: {{ $event->ministry->color }}">
                                        {{ $event->ministry->icon }} {{ $event->ministry->name }}
                                    </span>
                                @endif
                                @if($event->is_public)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                                        {{ __('Публічна') }}
                                    </span>
                                @endif
                                @if($event->google_event_id)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs text-blue-500 dark:text-blue-400" title="Google Calendar">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                                        </svg>
                                    </span>
                                @endif
                                @if($event->parent_event_id || $event->recurrence_rule)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs text-gray-500 dark:text-gray-400" title="{{ __('Повторювана подія') }}">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                        </svg>
                                    </span>
                                @endif
                                @if($event->ministry && $event->ministry->positions->count() > 0)
                                    @php
                                        $filled = $event->assignments->count();
                                        $total = $event->ministry->positions->count();
                                        $isFullyStaffed = $filled >= $total;
                                    @endphp
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium {{ $isFullyStaffed ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' }}">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        {{ $filled }}/{{ $total }}
                                    </span>
                                @endif
                            </div>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white truncate">
                                {{ $event->title }}
                            </h3>
                            @if($event->location)
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    {{ $event->location }}
                                </p>
                            @endif
                        </div>
                        <div class="text-right flex-shrink-0 ml-4">
                            @php
                                $daysUntil = (int) now()->startOfDay()->diffInDays($event->date->startOfDay(), false);
                            @endphp
                            @if($daysUntil === 0)
                                <div class="text-sm font-semibold text-primary-600 dark:text-primary-400">{{ __('Сьогодні') }}</div>
                            @elseif($daysUntil === 1)
                                <div class="text-sm font-semibold text-primary-600 dark:text-primary-400">{{ __('Завтра') }}</div>
                            @elseif($daysUntil === 2)
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ __('Післязавтра') }}</div>
                            @elseif($daysUntil >= 3 && $daysUntil <= 6)
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ \Illuminate\Support\Str::ucfirst($event->date->translatedFormat('l')) }}</div>
                            @else
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $event->date->format('d.m.Y') }}
                                </div>
                            @endif
                            @if($event->time)
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ \Carbon\Carbon::parse($event->time)->format('H:i') }}
                                </div>
                            @else
                                <div class="text-xs text-gray-400 dark:text-gray-500">{{ __('Весь день') }}</div>
                            @endif
                        </div>
                    </div>
                </a>
                @endforeach
            </div>

            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                {{ $events->links() }}
            </div>
        @else
            <div class="px-4 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('Немає подій') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Найближчі події не заплановано.') }}</p>
                @if(auth()->user()->can('create', \App\Models\Event::class))
                <div class="mt-6">
                    <a href="{{ route('events.create') }}"
                       class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-xl transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        {{ __('Створити подію') }}
                    </a>
                </div>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection
