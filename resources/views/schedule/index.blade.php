@extends('layouts.app')

@section('title', 'Події')

@section('actions')
@leader
<a href="{{ route('events.create') }}"
   class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-xl transition-colors">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Нова подія
</a>
@endleader
@endsection

@section('content')
<div class="space-y-4">
    <!-- View Options -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-2">
                <a href="{{ route('events.index') }}"
                   class="px-4 py-2 text-sm font-medium rounded-xl bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300">
                    Список
                </a>
                <a href="{{ route('schedule') }}"
                   class="px-4 py-2 text-sm font-medium rounded-xl text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700">
                    Календар
                </a>
            </div>

            @if($ministries->count() > 0)
            <form method="GET" class="flex items-center gap-2">
                <select name="ministry" onchange="this.form.submit()"
                        class="rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                    <option value="">Всі команди</option>
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
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        @if($events->count() > 0)
            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach($events as $event)
                <a href="{{ route('events.show', $event) }}"
                   class="block px-4 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                @if($event->ministry)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                          style="background-color: {{ $event->ministry->color }}20; color: {{ $event->ministry->color }}">
                                        {{ $event->ministry->icon }} {{ $event->ministry->name }}
                                    </span>
                                @endif
                                @if($event->is_public)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                                        Публічна
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
                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $event->date->format('d.m.Y') }}
                            </div>
                            @if($event->time)
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ \Carbon\Carbon::parse($event->time)->format('H:i') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </a>
                @endforeach
            </div>

            <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">
                {{ $events->links() }}
            </div>
        @else
            <div class="px-4 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Немає подій</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Найближчі події не заплановано.</p>
                @leader
                <div class="mt-6">
                    <a href="{{ route('events.create') }}"
                       class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-xl transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Створити подію
                    </a>
                </div>
                @endleader
            </div>
        @endif
    </div>
</div>
@endsection
