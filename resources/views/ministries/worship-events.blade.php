@extends('layouts.app')

@section('title', 'Музичні події - ' . $ministry->name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('ministries.show', $ministry) }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Музичні події</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $ministry->name }}</p>
            </div>
        </div>
        <a href="{{ route('ministries.worship-stats', $ministry) }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            Статистика
        </a>
    </div>

    <!-- Events List -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">
        @if($events->count() > 0)
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($events as $event)
                    <a href="{{ route('ministries.show', ['ministry' => $ministry, 'tab' => 'schedule']) }}"
                       class="block p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors {{ $event->date->isPast() ? 'opacity-60' : '' }}">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="flex-shrink-0 w-14 h-14 rounded-xl bg-gradient-to-br from-purple-500 to-pink-500 flex flex-col items-center justify-center text-white">
                                    <span class="text-xs font-medium uppercase">{{ $event->date->translatedFormat('M') }}</span>
                                    <span class="text-xl font-bold leading-none">{{ $event->date->format('d') }}</span>
                                </div>
                                <div>
                                    <h3 class="font-medium text-gray-900 dark:text-white">{{ $event->title }}</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $event->date->translatedFormat('l') }} о {{ $event->time?->format('H:i') }}
                                    </p>
                                    <div class="flex items-center gap-3 mt-1">
                                        @if($event->songs->count() > 0)
                                            <span class="inline-flex items-center gap-1 text-xs text-purple-600 dark:text-purple-400">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                                                </svg>
                                                {{ $event->songs->count() }} {{ trans_choice('пісня|пісні|пісень', $event->songs->count()) }}
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400 dark:text-gray-500">Пісні не додані</span>
                                        @endif

                                        @if($event->ministryTeams->count() > 0)
                                            <span class="inline-flex items-center gap-1 text-xs text-blue-600 dark:text-blue-400">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                </svg>
                                                {{ $event->ministryTeams->count() }} {{ trans_choice('учасник|учасники|учасників', $event->ministryTeams->count()) }}
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400 dark:text-gray-500">Команда не вказана</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="p-8 text-center">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">Немає музичних подій</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Створіть подію з галочкою "Подія з музичним супроводом"
                </p>
            </div>
        @endif
    </div>
</div>
@endsection
