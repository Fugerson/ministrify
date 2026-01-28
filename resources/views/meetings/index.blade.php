@extends('layouts.app')

@section('title', 'Зустрічі - ' . $ministry->name)

@section('actions')
<a href="{{ route('meetings.create', $ministry) }}" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-xl hover:bg-primary-700 transition-colors">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
    </svg>
    Нова зустріч
</a>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Back link -->
    <div class="flex items-center justify-between">
        <a href="{{ route('ministries.show', $ministry) }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white flex items-center gap-1">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            {{ $ministry->name }}
        </a>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 md:gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 md:p-5">
            <div class="flex items-center">
                <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 md:w-6 md:h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="ml-3 md:ml-4 min-w-0">
                    <p class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white">{{ $meetings->total() }}</p>
                    <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400 truncate">Всього</p>
                </div>
            </div>
        </div>

        @php
            $upcomingCount = $ministry->upcomingMeetings()->count();
            $completedCount = $ministry->meetings()->where('status', 'completed')->count();
            $thisMonth = $ministry->meetings()->whereMonth('date', now()->month)->count();
        @endphp

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 md:p-5">
            <div class="flex items-center">
                <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 md:w-6 md:h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-3 md:ml-4 min-w-0">
                    <p class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white">{{ $upcomingCount }}</p>
                    <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400 truncate">Заплановано</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 md:p-5">
            <div class="flex items-center">
                <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 md:w-6 md:h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-3 md:ml-4 min-w-0">
                    <p class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white">{{ $completedCount }}</p>
                    <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400 truncate">Проведено</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 md:p-5">
            <div class="flex items-center">
                <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 md:w-6 md:h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="ml-3 md:ml-4 min-w-0">
                    <p class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white">{{ $thisMonth }}</p>
                    <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400 truncate">Цей місяць</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Meetings List -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        @if($meetings->isEmpty())
        <div class="p-12 text-center">
            <div class="w-20 h-20 mx-auto mb-4 bg-gradient-to-br from-blue-100 to-blue-50 dark:from-blue-900 dark:to-blue-800 rounded-2xl flex items-center justify-center">
                <svg class="w-10 h-10 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Немає зустрічей</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-6">Створіть першу зустріч для команди</p>
            <a href="{{ route('meetings.create', $ministry) }}" class="inline-flex items-center px-5 py-2.5 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700 transition-all">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Створити зустріч
            </a>
        </div>
        @else
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            @foreach($meetings as $meeting)
            <a href="{{ route('meetings.show', [$ministry, $meeting]) }}" class="block p-3 md:p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 md:gap-4 min-w-0 flex-1">
                        <!-- Date badge -->
                        <div class="w-12 h-12 md:w-14 md:h-14 rounded-xl bg-gray-100 dark:bg-gray-700 flex flex-col items-center justify-center flex-shrink-0">
                            <span class="text-[10px] md:text-xs text-gray-500 dark:text-gray-400 uppercase">{{ $meeting->date->translatedFormat('M') }}</span>
                            <span class="text-lg md:text-xl font-bold text-gray-900 dark:text-white">{{ $meeting->date->format('d') }}</span>
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="font-semibold text-gray-900 dark:text-white text-sm md:text-base truncate">{{ $meeting->title }}</h3>
                                @php
                                    $statusColors = [
                                        'planned' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                                        'in_progress' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300',
                                        'completed' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                                        'cancelled' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                                    ];
                                @endphp
                                <span class="px-1.5 md:px-2 py-0.5 text-[10px] md:text-xs font-medium rounded-full {{ $statusColors[$meeting->status] }} flex-shrink-0">
                                    {{ $meeting->status_label }}
                                </span>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 md:gap-4 mt-1 text-xs md:text-sm text-gray-500 dark:text-gray-400">
                                @if($meeting->start_time)
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ $meeting->start_time->format('H:i') }}
                                </span>
                                @endif
                                @if($meeting->location)
                                <span class="flex items-center gap-1 hidden sm:flex">
                                    <svg class="w-3.5 h-3.5 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    </svg>
                                    <span class="truncate max-w-[100px] md:max-w-none">{{ $meeting->location }}</span>
                                </span>
                                @endif
                                @if($meeting->theme)
                                <span class="flex items-center gap-1 hidden md:flex">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                    </svg>
                                    {{ $meeting->theme }}
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 md:gap-4 flex-shrink-0">
                        <!-- Attendees count (mobile: single stat) -->
                        <div class="text-center hidden sm:block">
                            <span class="text-base md:text-lg font-semibold text-gray-900 dark:text-white">{{ $meeting->agendaItems->count() }}</span>
                            <p class="text-[10px] md:text-xs text-gray-500 dark:text-gray-400">пунктів</p>
                        </div>
                        <div class="text-center">
                            <span class="text-base md:text-lg font-semibold text-gray-900 dark:text-white">{{ $meeting->attendees->count() }}</span>
                            <p class="text-[10px] md:text-xs text-gray-500 dark:text-gray-400">осіб</p>
                        </div>

                        <svg class="w-4 h-4 md:w-5 md:h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </a>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($meetings->hasPages())
        <div class="p-4 border-t border-gray-200 dark:border-gray-700">
            {{ $meetings->links() }}
        </div>
        @endif
        @endif
    </div>
</div>
@endsection
