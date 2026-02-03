@extends('layouts.app')

@section('title', $ministry->name)

@section('actions')
@endsection

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 sm:p-6">
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
                if (tab !== 'resources') {
                    url.searchParams.delete('folder');
                }
                history.pushState({}, '', url);
            }
         }">
        <div class="border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
            <nav class="flex -mb-px whitespace-nowrap">
                @can('manage-ministry', $ministry)
                <button @click="setTab('goals')" type="button"
                   :class="activeTab === 'goals' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
                   class="px-3 sm:px-6 py-3 border-b-2 text-sm font-medium flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Планування
                </button>
                @endcan
                <button @click="setTab('schedule')" type="button"
                   :class="activeTab === 'schedule' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
                   class="px-3 sm:px-6 py-3 border-b-2 text-sm font-medium">
                    Події
                </button>
                <button @click="setTab('members')" type="button"
                   :class="activeTab === 'members' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
                   class="px-3 sm:px-6 py-3 border-b-2 text-sm font-medium">
                    Команда ({{ $ministry->members->count() }})
                </button>
                @can('manage-ministry', $ministry)
                <button @click="setTab('expenses')" type="button"
                   :class="activeTab === 'expenses' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
                   class="px-3 sm:px-6 py-3 border-b-2 text-sm font-medium">
                    Витрати
                </button>
                @endcan
                <button @click="setTab('resources')" type="button"
                   :class="activeTab === 'resources' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
                   class="px-3 sm:px-6 py-3 border-b-2 text-sm font-medium flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                    </svg>
                    Ресурси
                </button>
                @if($ministry->is_worship_ministry)
                <button @click="setTab('songs')" type="button"
                   :class="activeTab === 'songs' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
                   class="px-3 sm:px-6 py-3 border-b-2 text-sm font-medium flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                    </svg>
                    Бібліотека пісень
                </button>
                @endif
                @can('manage-ministry', $ministry)
                <button @click="setTab('settings')" type="button"
                   :class="activeTab === 'settings' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
                   class="px-3 sm:px-6 py-3 border-b-2 text-sm font-medium flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Налаштування
                </button>
                @endcan
            </nav>
        </div>

        <div class="p-6">
            <div x-show="activeTab === 'schedule'"{{ $tab !== 'schedule' ? ' style="display:none"' : '' }}>
                @if($ministry->is_worship_ministry)
                    {{-- Worship ministry: calendar view --}}
                    @php
                        $worshipEventsGrouped = $worshipEvents->groupBy(fn($e) => $e->date->format('Y-m-d'));
                    @endphp
                    <div x-data="worshipCalendar()" x-init="init()">
                        {{-- Header --}}
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <button @click="prevMonth()" class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                </button>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white min-w-[160px] text-center" x-text="monthYearLabel"></h3>
                                <button @click="nextMonth()" class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </button>
                                <button @click="goToToday()" class="ml-2 px-2 py-1 text-xs text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded">Сьогодні</button>
                            </div>
                            <a href="{{ route('ministries.worship-stats', $ministry) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                Статистика
                            </a>
                        </div>

                        {{-- Day names --}}
                        <div class="grid grid-cols-7 mb-1">
                            <template x-for="day in ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Нд']" :key="day">
                                <div class="text-center text-xs font-medium text-gray-500 dark:text-gray-400 py-2" x-text="day"></div>
                            </template>
                        </div>

                        {{-- Calendar grid --}}
                        <div class="grid grid-cols-7 border-t border-l border-gray-200 dark:border-gray-700">
                            <template x-for="(day, index) in calendarDays" :key="index">
                                <div class="border-r border-b border-gray-200 dark:border-gray-700 min-h-[80px] sm:min-h-[100px] p-1"
                                     :class="{ 'bg-gray-50 dark:bg-gray-800/50': !day.isCurrentMonth, 'bg-white dark:bg-gray-800': day.isCurrentMonth }">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-xs font-medium"
                                              :class="{
                                                  'text-gray-400 dark:text-gray-600': !day.isCurrentMonth,
                                                  'text-gray-700 dark:text-gray-300': day.isCurrentMonth && !day.isToday,
                                                  'bg-purple-600 text-white rounded-full w-6 h-6 flex items-center justify-center': day.isToday
                                              }"
                                              x-text="day.date"></span>
                                    </div>
                                    <div class="space-y-0.5">
                                        <template x-for="event in day.events.slice(0, 2)" :key="event.id">
                                            <button @click="openEventModal(event)"
                                               class="block w-full text-left px-1 py-0.5 text-xs rounded truncate transition-colors cursor-pointer"
                                               :class="event.isPast ? 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400' : 'bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300 hover:bg-purple-200 dark:hover:bg-purple-900/60'">
                                                <span x-text="event.time" class="font-medium"></span>
                                                <span x-text="event.title" class="hidden sm:inline"></span>
                                            </button>
                                        </template>
                                        <template x-if="day.events.length > 2">
                                            <div class="text-xs text-gray-500 dark:text-gray-400 px-1" x-text="'+' + (day.events.length - 2) + ' ще'"></div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Events list below calendar --}}
                        <div class="mt-6" x-show="currentMonthEvents.length > 0">
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Події цього місяця</h4>
                            <div class="space-y-2">
                                <template x-for="event in currentMonthEvents" :key="event.id">
                                    <button @click="openEventModal(event)"
                                       class="block w-full text-left p-3 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors cursor-pointer"
                                       :class="{ 'opacity-60': event.isPast }">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-gradient-to-br from-purple-500 to-pink-500 text-white">
                                                    <span class="text-xs font-bold" x-text="event.day"></span>
                                                </div>
                                                <div>
                                                    <p class="font-medium text-gray-900 dark:text-white text-sm" x-text="event.title"></p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400" x-text="event.fullDate + ' о ' + event.time"></p>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                                                <template x-if="event.songsCount > 0">
                                                    <span class="flex items-center gap-1">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                                                        </svg>
                                                        <span x-text="event.songsCount"></span>
                                                    </span>
                                                </template>
                                                <template x-if="event.teamCount > 0">
                                                    <span class="flex items-center gap-1">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                                                        </svg>
                                                        <span x-text="event.teamCount"></span>
                                                    </span>
                                                </template>
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </div>
                                        </div>
                                    </button>
                                </template>
                            </div>
                        </div>

                        {{-- Empty state --}}
                        <div x-show="allEvents.length === 0" class="text-center py-8">
                            <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                                </svg>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Немає запланованих подій</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Створіть подію з галочкою "Подія з музичним супроводом"</p>
                        </div>

                        {{-- Event Detail Modal --}}
                        <div x-show="showModal" x-cloak
                             class="fixed inset-0 z-50 overflow-y-auto"
                             @keydown.escape.window="closeModal()">
                            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                                {{-- Backdrop --}}
                                <div x-show="showModal"
                                     x-transition:enter="ease-out duration-300"
                                     x-transition:enter-start="opacity-0"
                                     x-transition:enter-end="opacity-100"
                                     x-transition:leave="ease-in duration-200"
                                     x-transition:leave-start="opacity-100"
                                     x-transition:leave-end="opacity-0"
                                     class="fixed inset-0 bg-black/50"
                                     @click="closeModal()"></div>

                                {{-- Modal Panel --}}
                                <div x-show="showModal"
                                     x-transition:enter="ease-out duration-300"
                                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                     x-transition:leave="ease-in duration-200"
                                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                     class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-3xl mx-4 overflow-hidden"
                                     @click.stop>
                                    {{-- Header --}}
                                    <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white" x-text="modalEvent?.title"></h3>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                <span x-text="modalEvent?.date"></span> о <span x-text="modalEvent?.time"></span>
                                            </p>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <a :href="modalRoutes.eventUrl" class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400">
                                                Перейти до події
                                            </a>
                                            <button @click="closeModal()" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Loading state --}}
                                    <div x-show="modalLoading" class="p-8 text-center">
                                        <svg class="animate-spin h-8 w-8 text-purple-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Завантаження...</p>
                                    </div>

                                    {{-- Content --}}
                                    <div x-show="!modalLoading" class="p-4 max-h-[70vh] overflow-y-auto">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            {{-- Songs Section --}}
                                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2 mb-3">
                                                    <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                                                    </svg>
                                                    Пісні
                                                </h4>

                                                <template x-if="modalSongs.length > 0">
                                                    <div class="space-y-2 mb-3">
                                                        <template x-for="(song, index) in modalSongs" :key="song.id">
                                                            <div class="flex items-center gap-2 p-2 bg-white dark:bg-gray-800 rounded-lg group">
                                                                <span class="w-5 h-5 rounded-full bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 flex items-center justify-center text-xs font-medium" x-text="index + 1"></span>
                                                                <div class="flex-1 min-w-0">
                                                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate" x-text="song.title"></p>
                                                                    <template x-if="song.key">
                                                                        <span class="text-xs text-gray-500 dark:text-gray-400" x-text="'Тональність: ' + song.key"></span>
                                                                    </template>
                                                                </div>
                                                                <button @click="removeSong(song.id)" class="p-1 text-gray-400 hover:text-red-600 dark:hover:text-red-400 opacity-0 group-hover:opacity-100 transition-opacity">
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </template>

                                                <template x-if="modalSongs.length === 0">
                                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Пісні ще не додані</p>
                                                </template>

                                                {{-- Add song form --}}
                                                <div class="border-t border-gray-200 dark:border-gray-600 pt-3">
                                                    <div class="flex gap-2">
                                                        <select x-model="selectedSongId" class="flex-1 px-2 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                                            <option value="">Оберіть пісню...</option>
                                                            <template x-for="song in modalAvailableSongs.filter(s => !s.inEvent)" :key="song.id">
                                                                <option :value="song.id" x-text="song.title + (song.key ? ' (' + song.key + ')' : '')"></option>
                                                            </template>
                                                        </select>
                                                        <input type="text" x-model="selectedKey" placeholder="Тон." class="w-14 px-2 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                                        <button @click="addSong()" :disabled="!selectedSongId" class="px-3 py-1.5 bg-purple-600 text-white text-sm rounded-lg hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                                            +
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Team Section --}}
                                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2 mb-3">
                                                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                    </svg>
                                                    Команда
                                                </h4>

                                                <template x-if="modalRoles.length > 0">
                                                    <div class="space-y-3 mb-3">
                                                        <template x-for="role in modalRoles" :key="role.id">
                                                            <div class="p-2 bg-white dark:bg-gray-800 rounded-lg">
                                                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1" x-text="role.name"></p>
                                                                <template x-if="modalTeam.filter(t => t.role_id == role.id).length > 0">
                                                                    <div class="space-y-1">
                                                                        <template x-for="member in modalTeam.filter(t => t.role_id == role.id)" :key="member.id">
                                                                            <div class="flex items-center justify-between py-0.5 group">
                                                                                <span class="text-sm text-gray-900 dark:text-white" x-text="member.person_name"></span>
                                                                                <button @click="removeTeamMember(member.id)" class="p-1 text-gray-400 hover:text-red-600 dark:hover:text-red-400 opacity-0 group-hover:opacity-100 transition-opacity">
                                                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                                                    </svg>
                                                                                </button>
                                                                            </div>
                                                                        </template>
                                                                    </div>
                                                                </template>
                                                                <template x-if="modalTeam.filter(t => t.role_id == role.id).length === 0">
                                                                    <p class="text-xs text-gray-400 dark:text-gray-500">Не призначено</p>
                                                                </template>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </template>

                                                <template x-if="modalRoles.length === 0">
                                                    <div class="text-center py-3">
                                                        <p class="text-sm text-gray-500 dark:text-gray-400">Спочатку налаштуйте ролі в налаштуваннях служіння</p>
                                                    </div>
                                                </template>

                                                {{-- Add team member form --}}
                                                <template x-if="modalRoles.length > 0">
                                                    <div class="border-t border-gray-200 dark:border-gray-600 pt-3">
                                                        <div class="grid grid-cols-2 gap-2 mb-2">
                                                            <select x-model="selectedPersonId" class="px-2 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                                                <option value="">Учасник...</option>
                                                                <template x-for="member in modalMembers" :key="member.id">
                                                                    <option :value="member.id" x-text="member.name"></option>
                                                                </template>
                                                            </select>
                                                            <select x-model="selectedRoleId" class="px-2 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                                                <option value="">Роль...</option>
                                                                <template x-for="role in modalRoles" :key="role.id">
                                                                    <option :value="role.id" x-text="role.name"></option>
                                                                </template>
                                                            </select>
                                                        </div>
                                                        <button @click="addTeamMember()" :disabled="!selectedPersonId || !selectedRoleId" class="w-full px-3 py-1.5 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                                            Додати учасника
                                                        </button>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @php
                        $calendarEventsData = $worshipEvents->map(function($e) use ($ministry) {
                            return [
                                'id' => $e->id,
                                'title' => $e->title,
                                'date' => $e->date->format('Y-m-d'),
                                'day' => $e->date->format('d'),
                                'time' => $e->time->format('H:i'),
                                'fullDate' => $e->date->translatedFormat('l, j M'),
                                'dataUrl' => route('ministries.worship-events.data', [$ministry, $e]),
                                'songsCount' => $e->songs_count ?? 0,
                                'teamCount' => $e->team_count ?? 0,
                                'isPast' => $e->date->isPast(),
                            ];
                        })->values();
                    @endphp
                    <script>
                        function worshipCalendar() {
                            return {
                                currentYear: new Date().getFullYear(),
                                currentMonth: new Date().getMonth(),
                                today: new Date(),
                                allEvents: @json($calendarEventsData),
                                monthNames: ['Січень', 'Лютий', 'Березень', 'Квітень', 'Травень', 'Червень', 'Липень', 'Серпень', 'Вересень', 'Жовтень', 'Листопад', 'Грудень'],

                                // Modal state
                                showModal: false,
                                modalLoading: false,
                                modalEvent: null,
                                modalSongs: [],
                                modalTeam: [],
                                modalRoles: [],
                                modalMembers: [],
                                modalAvailableSongs: [],
                                modalRoutes: {},

                                // Form state
                                selectedSongId: '',
                                selectedKey: '',
                                selectedPersonId: '',
                                selectedRoleId: '',

                                init() {
                                    // Start from current month
                                },

                                async openEventModal(event) {
                                    this.showModal = true;
                                    this.modalLoading = true;
                                    this.modalEvent = { title: event.title, date: event.fullDate, time: event.time };

                                    try {
                                        const response = await fetch(event.dataUrl);
                                        const data = await response.json();

                                        this.modalEvent = data.event;
                                        this.modalSongs = data.songs;
                                        this.modalTeam = data.team;
                                        this.modalRoles = data.worshipRoles;
                                        this.modalMembers = data.members;
                                        this.modalAvailableSongs = data.availableSongs;
                                        this.modalRoutes = data.routes;
                                    } catch (error) {
                                        console.error('Error loading event:', error);
                                    }

                                    this.modalLoading = false;
                                },

                                closeModal() {
                                    this.showModal = false;
                                    this.modalEvent = null;
                                    this.selectedSongId = '';
                                    this.selectedKey = '';
                                    this.selectedPersonId = '';
                                    this.selectedRoleId = '';
                                },

                                async addSong() {
                                    if (!this.selectedSongId) return;

                                    const formData = new FormData();
                                    formData.append('song_id', this.selectedSongId);
                                    formData.append('key', this.selectedKey);

                                    try {
                                        const response = await fetch(this.modalRoutes.addSong, {
                                            method: 'POST',
                                            headers: {
                                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                                'Accept': 'application/json'
                                            },
                                            body: formData
                                        });

                                        if (response.ok) {
                                            // Add to local list
                                            const song = this.modalAvailableSongs.find(s => s.id == this.selectedSongId);
                                            if (song) {
                                                this.modalSongs.push({
                                                    id: song.id,
                                                    title: song.title,
                                                    key: this.selectedKey || song.key
                                                });
                                                song.inEvent = true;
                                            }
                                            this.selectedSongId = '';
                                            this.selectedKey = '';

                                            // Update calendar event counts
                                            const evt = this.allEvents.find(e => e.id == this.modalEvent.id);
                                            if (evt) evt.songsCount++;
                                        }
                                    } catch (error) {
                                        console.error('Error adding song:', error);
                                    }
                                },

                                async removeSong(songId) {
                                    try {
                                        const response = await fetch(this.modalRoutes.addSong.replace('/songs', '/songs/' + songId), {
                                            method: 'DELETE',
                                            headers: {
                                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                                'Accept': 'application/json'
                                            }
                                        });

                                        if (response.ok) {
                                            this.modalSongs = this.modalSongs.filter(s => s.id !== songId);
                                            const song = this.modalAvailableSongs.find(s => s.id == songId);
                                            if (song) song.inEvent = false;

                                            const evt = this.allEvents.find(e => e.id == this.modalEvent.id);
                                            if (evt && evt.songsCount > 0) evt.songsCount--;
                                        }
                                    } catch (error) {
                                        console.error('Error removing song:', error);
                                    }
                                },

                                async addTeamMember() {
                                    if (!this.selectedPersonId || !this.selectedRoleId) return;

                                    const formData = new FormData();
                                    formData.append('person_id', this.selectedPersonId);
                                    formData.append('worship_role_id', this.selectedRoleId);

                                    try {
                                        const response = await fetch(this.modalRoutes.addTeam, {
                                            method: 'POST',
                                            headers: {
                                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                                'Accept': 'application/json'
                                            },
                                            body: formData
                                        });

                                        if (response.ok) {
                                            const person = this.modalMembers.find(m => m.id == this.selectedPersonId);
                                            const role = this.modalRoles.find(r => r.id == this.selectedRoleId);
                                            if (person && role) {
                                                const result = await response.json();
                                                this.modalTeam.push({
                                                    id: result.id || Date.now(),
                                                    person_id: person.id,
                                                    person_name: person.name,
                                                    role_id: role.id,
                                                    role_name: role.name
                                                });

                                                const evt = this.allEvents.find(e => e.id == this.modalEvent.id);
                                                if (evt) evt.teamCount++;
                                            }
                                            this.selectedPersonId = '';
                                            this.selectedRoleId = '';
                                        }
                                    } catch (error) {
                                        console.error('Error adding team member:', error);
                                    }
                                },

                                async removeTeamMember(memberId) {
                                    try {
                                        const response = await fetch(this.modalRoutes.addTeam.replace('/worship-team', '/worship-team/' + memberId), {
                                            method: 'DELETE',
                                            headers: {
                                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                                'Accept': 'application/json'
                                            }
                                        });

                                        if (response.ok) {
                                            this.modalTeam = this.modalTeam.filter(t => t.id !== memberId);

                                            const evt = this.allEvents.find(e => e.id == this.modalEvent.id);
                                            if (evt && evt.teamCount > 0) evt.teamCount--;
                                        }
                                    } catch (error) {
                                        console.error('Error removing team member:', error);
                                    }
                                },

                                get monthYearLabel() {
                                    return this.monthNames[this.currentMonth] + ' ' + this.currentYear;
                                },

                                get calendarDays() {
                                    const days = [];
                                    const firstDay = new Date(this.currentYear, this.currentMonth, 1);
                                    const lastDay = new Date(this.currentYear, this.currentMonth + 1, 0);

                                    // Get day of week (0 = Sunday, adjust for Monday start)
                                    let startDay = firstDay.getDay();
                                    startDay = startDay === 0 ? 6 : startDay - 1;

                                    // Previous month days
                                    const prevLastDay = new Date(this.currentYear, this.currentMonth, 0).getDate();
                                    for (let i = startDay - 1; i >= 0; i--) {
                                        const d = prevLastDay - i;
                                        const dateStr = this.formatDate(this.currentYear, this.currentMonth - 1, d);
                                        days.push({
                                            date: d,
                                            isCurrentMonth: false,
                                            isToday: false,
                                            events: this.getEventsForDate(dateStr)
                                        });
                                    }

                                    // Current month days
                                    for (let d = 1; d <= lastDay.getDate(); d++) {
                                        const dateStr = this.formatDate(this.currentYear, this.currentMonth, d);
                                        const isToday = this.today.getFullYear() === this.currentYear &&
                                                       this.today.getMonth() === this.currentMonth &&
                                                       this.today.getDate() === d;
                                        days.push({
                                            date: d,
                                            isCurrentMonth: true,
                                            isToday: isToday,
                                            events: this.getEventsForDate(dateStr)
                                        });
                                    }

                                    // Next month days (fill to 42 cells = 6 rows)
                                    const remaining = 42 - days.length;
                                    for (let d = 1; d <= remaining; d++) {
                                        const dateStr = this.formatDate(this.currentYear, this.currentMonth + 1, d);
                                        days.push({
                                            date: d,
                                            isCurrentMonth: false,
                                            isToday: false,
                                            events: this.getEventsForDate(dateStr)
                                        });
                                    }

                                    return days;
                                },

                                get currentMonthEvents() {
                                    return this.allEvents.filter(e => {
                                        const d = new Date(e.date);
                                        return d.getFullYear() === this.currentYear && d.getMonth() === this.currentMonth;
                                    }).sort((a, b) => new Date(a.date) - new Date(b.date));
                                },

                                formatDate(year, month, day) {
                                    const y = month < 0 ? year - 1 : (month > 11 ? year + 1 : year);
                                    const m = month < 0 ? 11 : (month > 11 ? 0 : month);
                                    return `${y}-${String(m + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                                },

                                getEventsForDate(dateStr) {
                                    return this.allEvents.filter(e => e.date === dateStr);
                                },

                                prevMonth() {
                                    if (this.currentMonth === 0) {
                                        this.currentMonth = 11;
                                        this.currentYear--;
                                    } else {
                                        this.currentMonth--;
                                    }
                                },

                                nextMonth() {
                                    if (this.currentMonth === 11) {
                                        this.currentMonth = 0;
                                        this.currentYear++;
                                    } else {
                                        this.currentMonth++;
                                    }
                                },

                                goToToday() {
                                    this.currentYear = this.today.getFullYear();
                                    this.currentMonth = this.today.getMonth();
                                }
                            };
                        }
                    </script>
                @else
                    {{-- Regular ministry: show ministry events --}}
                    @if($ministry->events->count() > 0)
                        <div class="space-y-2">
                            @foreach($ministry->events as $event)
                                <a href="{{ route('events.show', $event) }}"
                                   class="block p-3 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-blue-100 dark:bg-blue-900/30">
                                            <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-white text-sm">{{ $event->title }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $event->date->format('d.m.Y') }} о {{ $event->time->format('H:i') }}</p>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="text-center text-gray-500 dark:text-gray-400 py-8 text-sm">Немає запланованих подій</p>
                    @endif

                    @can('manage-ministry', $ministry)
                    <div class="mt-4">
                        <a href="{{ route('events.create', ['ministry' => $ministry->id]) }}"
                           class="inline-flex items-center text-primary-600 dark:text-primary-400 hover:text-primary-500 text-sm">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Створити подію
                        </a>
                    </div>
                    @endcan
                @endif
            </div>

            <div x-show="activeTab === 'members'"{{ $tab !== 'members' ? ' style="display:none"' : '' }}>
                <!-- Add member form -->
                @can('manage-ministry', $ministry)
                @if($availablePeople->count() > 0)
                <form method="POST" action="{{ route('ministries.members.add', $ministry) }}" class="mb-4 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                    @csrf
                    <div class="flex gap-2">
                        <div class="flex-1">
                            <x-person-select name="person_id" :people="$availablePeople" placeholder="Додати учасника..." :required="true" :nullable="false" />
                        </div>
                        <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg whitespace-nowrap text-sm">
                            Додати
                        </button>
                    </div>
                </form>
                @endif
                @endcan

                @if($ministry->members->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($ministry->members as $member)
                        <div class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                            <a href="{{ route('people.show', $member) }}" class="flex items-center hover:opacity-80">
                                @if($member->photo)
                                <div x-data="{ hover: false, r: {} }" @mouseenter="hover = true; r = $el.getBoundingClientRect()" @mouseleave="hover = false">
                                    <img src="{{ Storage::url($member->photo) }}" alt="{{ $member->full_name }}" class="w-10 h-10 rounded-full object-cover" loading="lazy">
                                    <div class="fixed z-[100] pointer-events-none" :style="`left:${r.left+r.width/2}px;top:${r.top-8}px;transform:translate(-50%,-100%)`">
                                        <img src="{{ Storage::url($member->photo) }}" :class="hover ? 'opacity-100 scale-100' : 'opacity-0 scale-75'" class="w-32 h-32 rounded-xl object-cover shadow-xl ring-2 ring-white dark:ring-gray-800 transition-all duration-200 ease-out origin-bottom">
                                    </div>
                                </div>
                                @else
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center">
                                    <span class="text-white text-sm font-medium">{{ mb_substr($member->first_name, 0, 1) }}{{ mb_substr($member->last_name, 0, 1) }}</span>
                                </div>
                                @endif
                                <span class="ml-3 font-medium text-gray-900 dark:text-white text-sm">{{ $member->full_name }}</span>
                            </a>
                            @can('manage-ministry', $ministry)
                            <form method="POST" action="{{ route('ministries.members.remove', [$ministry, $member]) }}"
                                  onsubmit="return confirm('Видалити учасника?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-gray-400 hover:text-red-600 dark:hover:text-red-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </form>
                            @endcan
                        </div>
                    @endforeach
                </div>
                @else
                <p class="text-center text-gray-500 dark:text-gray-400 py-6 text-sm">Немає учасників</p>
                @endif
            </div>

            @php
                $allTransactions = \App\Models\Transaction::where('church_id', $ministry->church_id)
                    ->where('ministry_id', $ministry->id)
                    ->where('direction', 'out')
                    ->with(['category', 'attachments'])
                    ->orderByDesc('date')
                    ->get();
            @endphp
            <div x-show="activeTab === 'expenses'"{{ $tab !== 'expenses' ? ' style="display:none"' : '' }}
                 x-data="{
                     search: '',
                     sortBy: 'date_desc',
                     filterPeriod: 'month',
                     currentMonth: {{ now()->month }},
                     currentYear: {{ now()->year }},
                     allTransactions: {{ Js::from($allTransactions->map(fn($t) => [
                         'id' => $t->id,
                         'amount' => $t->amount,
                         'currency' => $t->currency ?? '₴',
                         'description' => $t->description,
                         'date' => $t->date->format('Y-m-d'),
                         'month' => (int)$t->date->format('m'),
                         'year' => (int)$t->date->format('Y'),
                         'date_formatted' => $t->date->format('d.m.Y'),
                         'category' => $t->category?->name,
                         'payment_method' => $t->payment_method,
                         'notes' => $t->notes,
                         'attachments' => $t->attachments->map(fn($a) => [
                             'url' => Storage::url($a->path),
                             'is_image' => str_starts_with($a->mime_type, 'image/')
                         ])
                     ])) }},
                     get filteredTransactions() {
                         let result = this.allTransactions;
                         // Filter by period
                         if (this.filterPeriod === 'month') {
                             result = result.filter(t => t.month === this.currentMonth && t.year === this.currentYear);
                         } else if (this.filterPeriod === 'year') {
                             result = result.filter(t => t.year === this.currentYear);
                         }
                         // Filter by search
                         if (this.search) {
                             const s = this.search.toLowerCase();
                             result = result.filter(t =>
                                 t.description?.toLowerCase().includes(s) ||
                                 t.category?.toLowerCase().includes(s) ||
                                 t.notes?.toLowerCase().includes(s)
                             );
                         }
                         // Sort
                         result = [...result].sort((a, b) => {
                             if (this.sortBy === 'date_desc') return b.date.localeCompare(a.date);
                             if (this.sortBy === 'date_asc') return a.date.localeCompare(b.date);
                             if (this.sortBy === 'amount_desc') return b.amount - a.amount;
                             if (this.sortBy === 'amount_asc') return a.amount - b.amount;
                             return 0;
                         });
                         return result;
                     },
                     get totalSum() {
                         return this.filteredTransactions.reduce((sum, t) => sum + parseFloat(t.amount), 0);
                     },
                     monthNames: ['', 'Січень', 'Лютий', 'Березень', 'Квітень', 'Травень', 'Червень', 'Липень', 'Серпень', 'Вересень', 'Жовтень', 'Листопад', 'Грудень'],
                     prevPeriod() {
                         if (this.filterPeriod === 'month') {
                             this.currentMonth--;
                             if (this.currentMonth < 1) { this.currentMonth = 12; this.currentYear--; }
                         } else {
                             this.currentYear--;
                         }
                     },
                     nextPeriod() {
                         if (this.filterPeriod === 'month') {
                             this.currentMonth++;
                             if (this.currentMonth > 12) { this.currentMonth = 1; this.currentYear++; }
                         } else {
                             this.currentYear++;
                         }
                     }
                 }">
                <!-- Period selector -->
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <div class="flex items-center gap-2">
                        <button @click="prevPeriod()" class="p-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <span class="font-medium text-gray-900 dark:text-white min-w-[100px] sm:min-w-[140px] text-center" x-text="filterPeriod === 'month' ? monthNames[currentMonth] + ' ' + currentYear : currentYear"></span>
                        <button @click="nextPeriod()" class="p-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                        <select x-model="filterPeriod" class="ml-2 px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="month">Місяць</option>
                            <option value="year">Рік</option>
                            <option value="all">Всі</option>
                        </select>
                    </div>
                    <div class="text-right">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Всього:</span>
                        <span class="ml-1 font-semibold text-gray-900 dark:text-white" x-text="new Intl.NumberFormat('uk-UA').format(totalSum) + ' ₴'"></span>
                    </div>
                </div>

                <!-- Filters -->
                <div x-show="allTransactions.length > 0" class="flex flex-col sm:flex-row gap-3 mb-4">
                    <div class="flex-1">
                        <input type="text" x-model="search" placeholder="Пошук..."
                               class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                    </div>
                    <select x-model="sortBy" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <option value="date_desc">Спочатку нові</option>
                        <option value="date_asc">Спочатку старі</option>
                        <option value="amount_desc">Сума ↓</option>
                        <option value="amount_asc">Сума ↑</option>
                    </select>
                </div>

                <!-- Expenses List -->
                <div class="overflow-x-auto" x-show="filteredTransactions.length > 0">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                                <th class="py-2 pr-3 font-medium">Сума</th>
                                <th class="py-2 px-3 font-medium">Категорія</th>
                                <th class="py-2 px-3 font-medium">Опис</th>
                                <th class="py-2 px-3 font-medium">Дата</th>
                                <th class="py-2 px-3 font-medium">Примітка</th>
                                <th class="py-2 px-3 font-medium">Чек</th>
                                @can('manage-ministry', $ministry)
                                <th class="py-2 pl-3 font-medium"></th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="t in filteredTransactions" :key="t.id">
                                <tr class="border-b border-gray-200 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                    <td class="py-2 pr-3">
                                        <span class="font-semibold text-gray-900 dark:text-white whitespace-nowrap" x-text="new Intl.NumberFormat('uk-UA').format(t.amount) + ' ' + t.currency"></span>
                                    </td>
                                    <td class="py-2 px-3">
                                        <span x-show="t.category" class="px-2 py-0.5 text-xs rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400" x-text="t.category"></span>
                                    </td>
                                    <td class="py-2 px-3 text-gray-900 dark:text-white" x-text="t.description"></td>
                                    <td class="py-2 px-3 text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                        <span x-text="t.date_formatted"></span>
                                        <span x-show="t.payment_method" class="text-xs" x-text="' • ' + (t.payment_method === 'cash' ? 'Готівка' : 'Картка')"></span>
                                    </td>
                                    <td class="py-2 px-3 text-gray-500 dark:text-gray-400 text-xs italic max-w-[100px] sm:max-w-[150px] truncate" x-text="t.notes" :title="t.notes"></td>
                                    <td class="py-2 px-3">
                                        <template x-if="t.attachments.length > 0">
                                            <a :href="t.attachments[0].url" target="_blank" class="text-primary-600 dark:text-primary-400 hover:underline text-xs flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                                <span x-text="t.attachments.length"></span>
                                            </a>
                                        </template>
                                    </td>
                                    @can('manage-ministry', $ministry)
                                    <td class="py-2 pl-3">
                                        <div class="flex items-center gap-1">
                                            <a :href="'/finances/expenses/' + t.id + '/edit?redirect_to=ministry&ministry={{ $ministry->id }}'"
                                               class="p-1 text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 rounded"
                                               title="Редагувати">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                            <form method="POST" :action="'/finances/expenses/' + t.id" onsubmit="return confirm('Видалити витрату?')">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="redirect_to" value="ministry">
                                                <input type="hidden" name="ministry_id" value="{{ $ministry->id }}">
                                                <button type="submit" class="p-1 text-gray-400 hover:text-red-600 dark:hover:text-red-400 rounded" title="Видалити">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                    @endcan
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <p x-show="allTransactions.length === 0" class="text-center text-gray-500 dark:text-gray-400 py-8">Немає витрат</p>
                <p x-show="allTransactions.length > 0 && filteredTransactions.length === 0" class="text-center text-gray-500 dark:text-gray-400 py-8">Немає витрат за цей період</p>

                <div class="mt-4">
                    <a href="{{ route('finances.expenses.create', ['ministry' => $ministry->id, 'redirect_to' => 'ministry']) }}"
                       class="inline-flex items-center text-primary-600 dark:text-primary-400 hover:text-primary-500">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Додати витрату
                    </a>
                </div>
            </div>

            <div x-show="activeTab === 'resources'"{{ $tab !== 'resources' ? ' style="display:none"' : '' }}
                 x-data="resourcesManager()">

                <!-- Breadcrumbs -->
                <nav class="flex items-center gap-2 text-sm mb-4">
                    <a href="{{ route('ministries.show', ['ministry' => $ministry, 'tab' => 'resources']) }}"
                       class="text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                        </svg>
                        Ресурси
                    </a>
                    @foreach($breadcrumbs as $crumb)
                    <span class="text-gray-400">/</span>
                    @if($loop->last)
                    <span class="text-gray-900 dark:text-white font-medium">{{ $crumb->name }}</span>
                    @else
                    <a href="{{ route('ministries.show', ['ministry' => $ministry, 'tab' => 'resources', 'folder' => $crumb->id]) }}"
                       class="text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">
                        {{ $crumb->name }}
                    </a>
                    @endif
                    @endforeach
                </nav>

                <!-- Actions -->
                @can('manage-ministry', $ministry)
                <div class="flex flex-wrap items-center gap-3 mb-4">
                    <button @click="showCreateFolder = true" class="inline-flex items-center text-sm text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                        Папка
                    </button>
                    <button @click="createAndOpenDocument()" class="inline-flex items-center text-sm text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Документ
                    </button>
                    <label class="inline-flex items-center text-sm text-primary-600 dark:text-primary-400 hover:text-primary-500 transition-colors cursor-pointer"
                           :class="uploading && 'opacity-50 pointer-events-none'">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        <span x-text="uploading ? 'Завантаження...' : 'Завантажити файл'"></span>
                        <input type="file" class="hidden" @change="uploadFile($event)" multiple :disabled="uploading">
                    </label>
                </div>
                <template x-if="uploadError">
                    <div class="mb-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-sm text-red-700 dark:text-red-300" x-text="uploadError"></div>
                </template>
                @endcan

                <!-- Resources list -->
                @if($resources->count() > 0)
                <div class="space-y-1.5">
                    @foreach($resources as $resource)
                    @php
                        $isFolder = $resource->isFolder();
                        $isDoc = $resource->isDocument();
                        $mime = $resource->mime_type ?? '';
                        // Icon colors
                        if ($isFolder) { $iconBg = 'bg-amber-100 dark:bg-amber-900/30'; $iconColor = 'text-amber-600 dark:text-amber-400'; }
                        elseif ($isDoc) { $iconBg = 'bg-blue-100 dark:bg-blue-900/30'; $iconColor = 'text-blue-600 dark:text-blue-400'; }
                        elseif (str_contains($mime, 'pdf')) { $iconBg = 'bg-red-100 dark:bg-red-900/30'; $iconColor = 'text-red-600 dark:text-red-400'; }
                        elseif (str_starts_with($mime, 'image/')) { $iconBg = 'bg-green-100 dark:bg-green-900/30'; $iconColor = 'text-green-600 dark:text-green-400'; }
                        elseif (str_starts_with($mime, 'audio/')) { $iconBg = 'bg-pink-100 dark:bg-pink-900/30'; $iconColor = 'text-pink-600 dark:text-pink-400'; }
                        elseif (str_starts_with($mime, 'video/')) { $iconBg = 'bg-purple-100 dark:bg-purple-900/30'; $iconColor = 'text-purple-600 dark:text-purple-400'; }
                        elseif (str_contains($mime, 'word') || str_contains($mime, 'document')) { $iconBg = 'bg-blue-100 dark:bg-blue-900/30'; $iconColor = 'text-blue-600 dark:text-blue-400'; }
                        elseif (str_contains($mime, 'excel') || str_contains($mime, 'spreadsheet')) { $iconBg = 'bg-emerald-100 dark:bg-emerald-900/30'; $iconColor = 'text-emerald-600 dark:text-emerald-400'; }
                        else { $iconBg = 'bg-gray-100 dark:bg-gray-700'; $iconColor = 'text-gray-500 dark:text-gray-400'; }
                    @endphp
                    <div class="group flex items-center gap-3 p-2.5 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer transition-colors"
                         @if($isFolder)
                         @click="window.location.href='{{ route('ministries.show', ['ministry' => $ministry, 'tab' => 'resources', 'folder' => $resource->id]) }}'"
                         @elseif($isDoc)
                         @click="openDocument({{ json_encode(['id' => $resource->id, 'name' => $resource->name, 'content' => $resource->content ?? '']) }})"
                         @else
                         @click="showPreview({{ json_encode([
                             'id' => $resource->id,
                             'name' => $resource->name,
                             'icon' => $resource->icon,
                             'size' => $resource->formatted_size,
                             'mime' => $resource->mime_type,
                             'url' => $resource->file_path ? Storage::url($resource->file_path) : '',
                             'downloadUrl' => route('resources.download', $resource),
                             'createdAt' => $resource->created_at->format('d.m.Y H:i'),
                             'creator' => $resource->creator?->name,
                         ]) }})"
                         @endif>
                        {{-- Icon --}}
                        <div class="w-9 h-9 rounded-lg {{ $iconBg }} flex items-center justify-center flex-shrink-0">
                            @if($isFolder)
                            <svg class="w-5 h-5 {{ $iconColor }}" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/></svg>
                            @elseif($isDoc)
                            <svg class="w-5 h-5 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            @elseif(str_starts_with($mime, 'image/'))
                            <svg class="w-5 h-5 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            @elseif(str_starts_with($mime, 'audio/'))
                            <svg class="w-5 h-5 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/></svg>
                            @elseif(str_starts_with($mime, 'video/'))
                            <svg class="w-5 h-5 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            @else
                            <svg class="w-5 h-5 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            @endif
                        </div>
                        {{-- Name & meta --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $resource->name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                @if($isFolder) Папка
                                @elseif($isDoc) Документ
                                @else {{ $resource->formatted_size }}
                                @endif
                                <span class="mx-1">&middot;</span>
                                {{ $resource->created_at->format('d.m.Y') }}
                            </p>
                        </div>
                        {{-- Context menu --}}
                        @can('manage-ministry', $ministry)
                        <button @click.stop="openMenu({{ $resource->id }}, '{{ addslashes($resource->name) }}', $event)"
                                class="p-1.5 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg>
                        </button>
                        @endcan
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-center text-gray-500 dark:text-gray-400 py-8 text-sm">
                    {{ count($breadcrumbs) > 0 ? 'Папка порожня' : 'Немає ресурсів' }}
                </p>
                @endif

                <!-- Create folder modal -->
                @can('manage-ministry', $ministry)
                <div x-show="showCreateFolder" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
                    <div class="min-h-screen px-4 flex items-center justify-center">
                        <div class="fixed inset-0 bg-black/50" @click="showCreateFolder = false"></div>
                        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Нова папка</h3>
                            <form method="POST" action="{{ route('ministries.resources.folder.create', $ministry) }}">
                                @csrf
                                <input type="hidden" name="parent_id" value="{{ $currentFolder?->id }}">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Назва</label>
                                    <input type="text" name="name" required autofocus
                                           class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white"
                                           placeholder="Назва папки...">
                                </div>
                                <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 sm:gap-3 mt-6">
                                    <button type="button" @click="showCreateFolder = false"
                                            class="w-full sm:w-auto px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900">
                                        Скасувати
                                    </button>
                                    <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700">
                                        Створити
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Context menu -->
                <div x-show="menuOpen" x-cloak
                     :style="`top: ${menuY}px; left: ${menuX}px`"
                     @click.away="menuOpen = false"
                     class="fixed z-50 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 py-1 min-w-48">
                    <button @click="showRenameModal()" class="w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Перейменувати
                    </button>
                    <button @click="deleteItem()" class="w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Видалити
                    </button>
                </div>

                <!-- Rename modal -->
                <div x-show="showRename" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
                    <div class="min-h-screen px-4 flex items-center justify-center">
                        <div class="fixed inset-0 bg-black/50" @click="showRename = false"></div>
                        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Перейменувати</h3>
                            <form @submit.prevent="submitRename()">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Нова назва</label>
                                    <input type="text" x-model="renameName" required x-ref="renameInput"
                                           class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white"
                                           placeholder="Назва...">
                                </div>
                                <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 sm:gap-3 mt-6">
                                    <button type="button" @click="showRename = false"
                                            class="w-full sm:w-auto px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900">
                                        Скасувати
                                    </button>
                                    <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700">
                                        Зберегти
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endcan

                <!-- File preview modal -->
                <div x-show="previewFile" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
                    <div class="min-h-screen px-4 flex items-center justify-center">
                        <div class="fixed inset-0 bg-black/70" @click="previewFile = null"></div>
                        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-2xl w-full overflow-hidden">
                            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="text-3xl" x-text="previewFile?.icon"></span>
                                    <div>
                                        <h3 class="font-semibold text-gray-900 dark:text-white" x-text="previewFile?.name"></h3>
                                        <p class="text-sm text-gray-500" x-text="previewFile?.size"></p>
                                    </div>
                                </div>
                                <button @click="previewFile = null" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="p-4">
                                <template x-if="previewFile?.mime?.startsWith('image/')">
                                    <img :src="previewFile?.url" class="max-h-96 mx-auto rounded-lg">
                                </template>
                                <template x-if="previewFile?.mime?.startsWith('audio/')">
                                    <audio :src="previewFile?.url" controls class="w-full"></audio>
                                </template>
                                <template x-if="previewFile?.mime?.startsWith('video/')">
                                    <video :src="previewFile?.url" controls class="max-h-96 mx-auto rounded-lg"></video>
                                </template>
                                <template x-if="previewFile?.mime === 'application/pdf'">
                                    <iframe :src="previewFile?.url" class="w-full h-96 rounded-lg"></iframe>
                                </template>
                                <template x-if="previewFile && !previewFile?.mime?.startsWith('image/') && !previewFile?.mime?.startsWith('audio/') && !previewFile?.mime?.startsWith('video/') && previewFile?.mime !== 'application/pdf'">
                                    <div class="text-center py-8">
                                        <span class="text-6xl block mb-4" x-text="previewFile?.icon"></span>
                                        <p class="text-gray-500 dark:text-gray-400">Попередній перегляд недоступний</p>
                                    </div>
                                </template>
                            </div>
                            <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
                                <div class="flex items-center justify-between">
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        <span x-text="previewFile?.createdAt"></span>
                                        <span x-show="previewFile?.creator"> &mdash; <span x-text="previewFile?.creator"></span></span>
                                    </div>
                                    <a :href="previewFile?.downloadUrl"
                                       class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                        Завантажити
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Document editor modal -->
                <div x-show="showDocEditor" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4 sm:p-6 md:p-10">
                    <!-- Backdrop -->
                    <div x-show="showDocEditor"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="fixed inset-0 bg-black/40 backdrop-blur-sm"></div>

                    <!-- Modal card -->
                    <div x-show="showDocEditor"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                         x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                         class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl flex flex-col w-full max-w-5xl max-h-[90vh] overflow-hidden">

                        <!-- Header -->
                        <div class="flex items-center justify-between px-4 py-2.5 border-b border-gray-200 dark:border-gray-700 shrink-0">
                            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <input type="text" x-model="docName"
                                       class="flex-1 min-w-0 text-sm font-semibold text-gray-900 dark:text-white bg-transparent border-0 p-0 focus:ring-0 focus:outline-none placeholder-gray-400"
                                       placeholder="Назва документа...">
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0 ml-3">
                                <span x-show="docSaving" x-transition class="text-xs text-blue-500">Збереження...</span>
                                <span x-show="docSaved" x-transition class="text-xs text-green-500">Збережено</span>
                                <button @click="saveDocument()" :disabled="docSaving"
                                        class="px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50">
                                    Зберегти
                                </button>
                                <button @click="closeDocEditor()"
                                        class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-lg transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>

                        <!-- Toolbar -->
                        <div class="doc-toolbar" id="doc-toolbar">
                            <select onchange="docExecBlock(this.value); this.value='';" title="Блок">
                                <option value="">Формат</option>
                                <option value="p">Текст</option>
                                <option value="h1">Заголовок 1</option>
                                <option value="h2">Заголовок 2</option>
                                <option value="h3">Заголовок 3</option>
                                <option value="blockquote">Цитата</option>
                            </select>
                            <select onchange="docExecFontSize(this.value); this.value='';" title="Розмір">
                                <option value="">Розмір</option>
                                <option value="1">10</option>
                                <option value="2">13</option>
                                <option value="3">16</option>
                                <option value="4">18</option>
                                <option value="5">24</option>
                                <option value="6">32</option>
                                <option value="7">48</option>
                            </select>
                            <div class="tb-sep"></div>
                            <button class="tb-btn" onclick="docExec('bold')" title="Жирний"><b>B</b></button>
                            <button class="tb-btn" onclick="docExec('italic')" title="Курсив"><i>I</i></button>
                            <button class="tb-btn" onclick="docExec('underline')" title="Підкреслити"><u>U</u></button>
                            <button class="tb-btn" onclick="docExec('strikeThrough')" title="Закреслити"><s>S</s></button>
                            <div class="tb-sep"></div>
                            <input type="color" value="#000000" onchange="docExecColor('foreColor', this.value)" title="Колір тексту">
                            <input type="color" value="#ffffff" onchange="docExecColor('hiliteColor', this.value)" title="Колір фону">
                            <div class="tb-sep"></div>
                            <button class="tb-btn" onclick="docExec('justifyLeft')" title="Ліворуч">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="15" y2="12"/><line x1="3" y1="18" x2="18" y2="18"/></svg>
                            </button>
                            <button class="tb-btn" onclick="docExec('justifyCenter')" title="По центру">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="6" y1="12" x2="18" y2="12"/><line x1="4" y1="18" x2="20" y2="18"/></svg>
                            </button>
                            <button class="tb-btn" onclick="docExec('justifyRight')" title="Праворуч">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="9" y1="12" x2="21" y2="12"/><line x1="6" y1="18" x2="21" y2="18"/></svg>
                            </button>
                            <div class="tb-sep"></div>
                            <button class="tb-btn" onclick="docExec('insertUnorderedList')" title="Список">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="9" y1="6" x2="20" y2="6"/><line x1="9" y1="12" x2="20" y2="12"/><line x1="9" y1="18" x2="20" y2="18"/><circle cx="4" cy="6" r="1.5" fill="currentColor"/><circle cx="4" cy="12" r="1.5" fill="currentColor"/><circle cx="4" cy="18" r="1.5" fill="currentColor"/></svg>
                            </button>
                            <button class="tb-btn" onclick="docExec('insertOrderedList')" title="Нумерований список">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="10" y1="6" x2="20" y2="6"/><line x1="10" y1="12" x2="20" y2="12"/><line x1="10" y1="18" x2="20" y2="18"/><text x="3" y="8" font-size="8" fill="currentColor" stroke="none">1</text><text x="3" y="14" font-size="8" fill="currentColor" stroke="none">2</text><text x="3" y="20" font-size="8" fill="currentColor" stroke="none">3</text></svg>
                            </button>
                            <button class="tb-btn" onclick="docExec('outdent')" title="Зменшити відступ">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="11" y1="12" x2="21" y2="12"/><line x1="11" y1="18" x2="21" y2="18"/><polyline points="7 9 3 12 7 15"/></svg>
                            </button>
                            <button class="tb-btn" onclick="docExec('indent')" title="Збільшити відступ">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="11" y1="12" x2="21" y2="12"/><line x1="11" y1="18" x2="21" y2="18"/><polyline points="3 9 7 12 3 15"/></svg>
                            </button>
                            <div class="tb-sep"></div>
                            <button class="tb-btn" onclick="docInsertLink()" title="Посилання">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>
                            </button>
                            <button class="tb-btn" onclick="docInsertImage()" title="Зображення">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                            </button>
                            <button class="tb-btn" onclick="docExec('removeFormat')" title="Очистити форматування">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="4" x2="20" y2="20"/><path d="M6 4h8l-4 16"/></svg>
                            </button>
                        </div>
                        <!-- Editable area -->
                        <div class="flex-1 overflow-y-auto min-h-0">
                            <div id="doc-editable" class="doc-editable" contenteditable="true" data-placeholder="Почніть вводити текст..."></div>
                        </div>
                    </div>
                </div>

                <!-- Hidden forms for rename & delete -->
                <form id="resRenameForm" method="POST" class="hidden">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="name" id="resRenameInput">
                </form>
                <form id="resDeleteForm" method="POST" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            </div>

            <!-- Goals Tab -->
            @can('manage-ministry', $ministry)
            <div x-show="activeTab === 'goals'"{{ $tab !== 'goals' ? ' style="display:none"' : '' }}
                 x-data="goalsManager()">

                <!-- Vision - Main section at top -->
                <div class="bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-xl p-5 mb-6 border border-indigo-100 dark:border-indigo-800">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center">
                                <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">Бачення служіння</h3>
                                <p class="text-xs text-indigo-600 dark:text-indigo-400">Куди ми рухаємось</p>
                            </div>
                        </div>
                        <button @click="editingVision = !editingVision" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 text-sm font-medium">
                            <span x-text="editingVision ? 'Скасувати' : 'Редагувати'"></span>
                        </button>
                    </div>
                    <div x-show="!editingVision">
                        @if($ministry->vision)
                            <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed">{{ $ministry->vision }}</p>
                        @else
                            <p class="text-gray-500 dark:text-gray-400 italic">Бачення ще не визначено. Опишіть, куди рухається ваше служіння та чого ви хочете досягти.</p>
                        @endif
                    </div>
                    <form x-show="editingVision" method="POST" action="{{ route('ministries.vision.update', $ministry) }}" class="space-y-3">
                        @csrf
                        <textarea name="vision" rows="4" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500" placeholder="Опишіть бачення вашого служіння...">{{ $ministry->vision }}</textarea>
                        <div class="flex justify-end">
                            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg">Зберегти</button>
                        </div>
                    </form>
                </div>

                <!-- Goals Header -->
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Цілі</h3>
                    <div class="flex gap-2">
                        <button @click="showTaskModal = true; taskForm.goal_id = ''" class="inline-flex items-center px-3 py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Задача
                        </button>
                        <button @click="showGoalModal = true; resetGoalForm()" class="inline-flex items-center px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Ціль
                        </button>
                    </div>
                </div>

                <!-- Goals List -->
                @if($ministry->goals->count() > 0)
                    <div class="space-y-4">
                        @foreach($ministry->goals as $goal)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                                <div class="p-4 bg-gray-50 dark:bg-gray-700/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div class="flex items-start gap-3">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0
                                            @if($goal->status === 'active') bg-blue-100 dark:bg-blue-900/30
                                            @elseif($goal->status === 'completed') bg-green-100 dark:bg-green-900/30
                                            @elseif($goal->status === 'on_hold') bg-yellow-100 dark:bg-yellow-900/30
                                            @else bg-red-100 dark:bg-red-900/30 @endif">
                                            @if($goal->status === 'completed')
                                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            @else
                                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            @endif
                                        </div>
                                        <div>
                                            <h4 class="font-medium text-gray-900 dark:text-white">{{ $goal->title }}</h4>
                                            @if($goal->description)
                                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ Str::limit($goal->description, 100) }}</p>
                                            @endif
                                            <div class="flex flex-wrap items-center gap-2 mt-2 text-xs">
                                                <span class="px-2 py-0.5 rounded bg-{{ $goal->status_color }}-100 dark:bg-{{ $goal->status_color }}-900/30 text-{{ $goal->status_color }}-700 dark:text-{{ $goal->status_color }}-300">{{ $goal->status_label }}</span>
                                                @if($goal->period)
                                                    <span class="px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">{{ $goal->period }}</span>
                                                @endif
                                                @if($goal->due_date)
                                                    <span class="text-gray-500 @if($goal->is_overdue) text-red-600 @endif">до {{ $goal->due_date->format('d.m.Y') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <div class="flex items-center gap-2">
                                            <div class="w-20 h-1.5 bg-gray-200 dark:bg-gray-600 rounded-full overflow-hidden">
                                                <div class="h-full bg-primary-500 rounded-full" style="width: {{ $goal->calculated_progress }}%"></div>
                                            </div>
                                            <span class="text-xs font-medium text-gray-500">{{ $goal->calculated_progress }}%</span>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <button @click="editGoal({{ $goal->id }}, {{ json_encode(['title' => $goal->title, 'description' => $goal->description, 'period' => $goal->period, 'due_date' => $goal->due_date?->format('Y-m-d'), 'priority' => $goal->priority, 'status' => $goal->status]) }})" class="p-1.5 text-gray-400 hover:text-gray-600">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            </button>
                                            <form method="POST" action="{{ route('ministries.goals.destroy', [$ministry, $goal]) }}" onsubmit="return confirm('Видалити?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @if($goal->tasks->count() > 0)
                                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach($goal->tasks as $task)
                                            <div class="p-3 flex items-center gap-3 hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                                <form method="POST" action="{{ route('ministries.tasks.toggle', [$ministry, $task]) }}">
                                                    @csrf
                                                    <button type="submit" class="w-5 h-5 rounded-full border-2 flex items-center justify-center @if($task->is_done) border-green-500 bg-green-500 @else border-gray-300 hover:border-primary-500 @endif">
                                                        @if($task->is_done)<svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>@endif
                                                    </button>
                                                </form>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm text-gray-900 dark:text-white @if($task->is_done) line-through text-gray-500 @endif">{{ $task->title }}</p>
                                                    <div class="flex items-center gap-2 mt-0.5 text-xs text-gray-500">
                                                        @if($task->assignee)<span>{{ $task->assignee->full_name }}</span>@endif
                                                        @if($task->due_date)<span class="@if($task->is_overdue) text-red-600 @endif">{{ $task->due_date->format('d.m') }}</span>@endif
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-1">
                                                    <button @click="editTask({{ $task->id }}, {{ json_encode(['title' => $task->title, 'description' => $task->description, 'goal_id' => $task->goal_id, 'assigned_to' => $task->assigned_to, 'due_date' => $task->due_date?->format('Y-m-d'), 'priority' => $task->priority, 'status' => $task->status]) }})" class="p-1 text-gray-400 hover:text-gray-600"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></button>
                                                    <form method="POST" action="{{ route('ministries.tasks.destroy', [$ministry, $task]) }}" onsubmit="return confirm('Видалити?')">@csrf @method('DELETE')<button type="submit" class="p-1 text-gray-400 hover:text-red-600"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button></form>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                <div class="p-2 bg-gray-50 dark:bg-gray-700/30 border-t border-gray-200 dark:border-gray-700">
                                    <button @click="showTaskModal = true; taskForm.goal_id = {{ $goal->id }}" class="text-xs text-gray-500 hover:text-primary-600 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        Додати задачу
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="w-12 h-12 mx-auto mb-3 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm mb-3">Цілей ще немає</p>
                        <button @click="showGoalModal = true; resetGoalForm()" class="inline-flex items-center px-3 py-1.5 bg-primary-600 text-white text-sm rounded-lg hover:bg-primary-700">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Створити ціль
                        </button>
                    </div>
                @endif

                <!-- Goal Modal -->
                <div x-show="showGoalModal" class="fixed inset-0 z-50" style="display: none;">
                    <div class="absolute inset-0 bg-black/50" @click="showGoalModal = false"></div>
                    <div class="absolute inset-4 md:inset-auto md:top-1/2 md:left-1/2 md:-translate-x-1/2 md:-translate-y-1/2 md:w-full md:max-w-md bg-white dark:bg-gray-800 rounded-xl shadow-xl overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                            <h3 class="font-semibold text-gray-900 dark:text-white" x-text="editingGoalId ? 'Редагувати ціль' : 'Нова ціль'"></h3>
                            <button @click="showGoalModal = false" class="text-gray-400 hover:text-gray-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                        </div>
                        <form :action="editingGoalId ? '{{ url('ministries/' . $ministry->id . '/goals') }}/' + editingGoalId : '{{ route('ministries.goals.store', $ministry) }}'" method="POST" class="p-4 space-y-4">
                            @csrf
                            <template x-if="editingGoalId"><input type="hidden" name="_method" value="PUT"></template>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Назва *</label>
                                <input type="text" name="title" x-model="goalForm.title" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Опис</label>
                                <textarea name="description" x-model="goalForm.description" rows="2" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Період</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Від</span>
                                        <input type="date" x-model="goalForm.period_start" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                    </div>
                                    <div>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">До</span>
                                        <input type="date" x-model="goalForm.period_end" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                    </div>
                                </div>
                                <input type="hidden" name="period" :value="goalForm.period_start && goalForm.period_end ?
                                    new Date(goalForm.period_start).toLocaleDateString('uk-UA') + ' – ' + new Date(goalForm.period_end).toLocaleDateString('uk-UA') :
                                    (goalForm.period_start ? new Date(goalForm.period_start).toLocaleDateString('uk-UA') : '')">
                            </div>
                            <input type="hidden" name="due_date" :value="goalForm.period_end || ''">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Пріоритет</label>
                                    <select name="priority" x-model="goalForm.priority" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                        <option value="low">Низький</option>
                                        <option value="medium">Середній</option>
                                        <option value="high">Високий</option>
                                    </select>
                                </div>
                                <div x-show="editingGoalId">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Статус</label>
                                    <select name="status" x-model="goalForm.status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                        <option value="active">Активна</option>
                                        <option value="completed">Виконана</option>
                                        <option value="on_hold">На паузі</option>
                                        <option value="cancelled">Скасована</option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex gap-2 pt-2">
                                <button type="button" @click="showGoalModal = false" class="flex-1 px-3 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg">Скасувати</button>
                                <button type="submit" class="flex-1 px-3 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg" x-text="editingGoalId ? 'Зберегти' : 'Створити'"></button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Task Modal -->
                <div x-show="showTaskModal" class="fixed inset-0 z-50" style="display: none;">
                    <div class="absolute inset-0 bg-black/50" @click="showTaskModal = false"></div>
                    <div class="absolute inset-4 md:inset-auto md:top-1/2 md:left-1/2 md:-translate-x-1/2 md:-translate-y-1/2 md:w-full md:max-w-md bg-white dark:bg-gray-800 rounded-xl shadow-xl overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                            <h3 class="font-semibold text-gray-900 dark:text-white" x-text="editingTaskId ? 'Редагувати задачу' : 'Нова задача'"></h3>
                            <button @click="showTaskModal = false" class="text-gray-400 hover:text-gray-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                        </div>
                        <form :action="editingTaskId ? '{{ url('ministries/' . $ministry->id . '/tasks') }}/' + editingTaskId : '{{ route('ministries.tasks.store', $ministry) }}'" method="POST" class="p-4 space-y-4">
                            @csrf
                            <template x-if="editingTaskId"><input type="hidden" name="_method" value="PUT"></template>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Назва *</label>
                                <input type="text" name="title" x-model="taskForm.title" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ціль</label>
                                <select name="goal_id" x-model="taskForm.goal_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                    <option value="">Без цілі</option>
                                    @foreach($ministry->goals as $goal)
                                        <option value="{{ $goal->id }}">{{ $goal->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Виконавець</label>
                                    <select name="assigned_to" x-model="taskForm.assigned_to" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                        <option value="">-</option>
                                        @foreach($ministry->members as $member)
                                            <option value="{{ $member->id }}">{{ $member->full_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Дедлайн</label>
                                    <input type="date" name="due_date" x-model="taskForm.due_date" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Пріоритет</label>
                                    <select name="priority" x-model="taskForm.priority" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                        <option value="low">Низький</option>
                                        <option value="medium">Середній</option>
                                        <option value="high">Високий</option>
                                    </select>
                                </div>
                                <div x-show="editingTaskId">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Статус</label>
                                    <select name="status" x-model="taskForm.status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                        <option value="todo">До виконання</option>
                                        <option value="in_progress">В процесі</option>
                                        <option value="done">Виконано</option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex gap-2 pt-2">
                                <button type="button" @click="showTaskModal = false" class="flex-1 px-3 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg">Скасувати</button>
                                <button type="submit" class="flex-1 px-3 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg" x-text="editingTaskId ? 'Зберегти' : 'Створити'"></button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Linked Tasks (Boards) -->
                <div class="mt-6">
                    <x-linked-cards entityType="ministry" :entityId="$ministry->id" :boards="$boards" />
                </div>

                <a href="{{ route('ministries.index') }}" class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-sm mt-4">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Назад до списку
                </a>
            </div>
            @endcan

            <!-- Songs Library Tab (for worship ministries) -->
            @if($ministry->is_worship_ministry)
            <div x-show="activeTab === 'songs'"{{ $tab !== 'songs' ? ' style="display:none"' : '' }}
                 x-data="songsLibrary()">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Бібліотека пісень</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Знайдено: <span x-text="filteredSongs.length"></span> пісень
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        @can('manage-ministry', $ministry)
                        <a href="{{ route('songs.import.page') }}"
                           class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                            Імпорт
                        </a>
                        <button @click="openCreateModal()"
                           class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition-colors">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Додати пісню
                        </button>
                        @endcan
                    </div>
                </div>

                <!-- Search & Filters -->
                <div class="flex flex-wrap gap-3 mb-4">
                    <div class="flex-1 min-w-[200px]">
                        <input type="text" x-model="search" placeholder="Пошук пісень..."
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div class="relative w-44" x-data="{
                        open: false,
                        query: '',
                        keys: @js(\App\Models\Song::KEYS),
                        get filtered() {
                            if (!this.query) return Object.entries(this.keys);
                            const q = this.query.toLowerCase();
                            return Object.entries(this.keys).filter(([key, label]) =>
                                key.toLowerCase().includes(q) || label.toLowerCase().includes(q)
                            );
                        }
                    }">
                        <input type="text"
                               x-model="query"
                               @focus="open = true"
                               @click.away="open = false"
                               @keydown.escape="open = false; query = filterKey"
                               placeholder="Тональність"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                        <div x-show="open" x-transition
                             class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg max-h-60 overflow-auto">
                            <div @click="filterKey = ''; query = ''; open = false"
                                 class="px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm text-gray-500">
                                Усі тональності
                            </div>
                            <template x-for="[key, label] in filtered" :key="key">
                                <div @click="filterKey = key; query = key; open = false"
                                     class="px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm"
                                     :class="filterKey === key ? 'bg-primary-50 dark:bg-primary-900/30' : ''">
                                    <span x-text="key" class="font-medium"></span>
                                    <span x-text="' - ' + label" class="text-gray-500 text-xs"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                    <select x-model="filterTag"
                            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                        <option value="">Усі теги</option>
                        <template x-for="tag in allTags" :key="tag">
                            <option :value="tag" x-text="tag"></option>
                        </template>
                    </select>
                    <select x-model="sortBy" @change="sortDir = (sortBy === 'popular' || sortBy === 'recent' || sortBy === 'bpm') ? 'desc' : 'asc'"
                            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                        <option value="title">За назвою</option>
                        <option value="artist">За виконавцем</option>
                        <option value="recent">Нові</option>
                        <option value="popular">Популярні</option>
                    </select>
                </div>

                <!-- Songs List -->
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                    <!-- Header -->
                    <div class="hidden md:grid md:grid-cols-12 gap-2 px-4 py-2.5 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider select-none">
                        <div class="col-span-3 cursor-pointer hover:text-gray-700 dark:hover:text-gray-200 flex items-center gap-1" @click="toggleSort('title')">
                            Назва
                            <svg x-show="sortBy === 'title'" class="w-3 h-3" :class="sortDir === 'asc' ? '' : 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                        </div>
                        <div class="col-span-2 cursor-pointer hover:text-gray-700 dark:hover:text-gray-200 flex items-center gap-1" @click="toggleSort('artist')">
                            Виконавець
                            <svg x-show="sortBy === 'artist'" class="w-3 h-3" :class="sortDir === 'asc' ? '' : 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                        </div>
                        <div class="col-span-2 text-center cursor-pointer hover:text-gray-700 dark:hover:text-gray-200 flex items-center justify-center gap-1" @click="toggleSort('key')">
                            Тональність
                            <svg x-show="sortBy === 'key'" class="w-3 h-3" :class="sortDir === 'asc' ? '' : 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                        </div>
                        <div class="col-span-1 text-center cursor-pointer hover:text-gray-700 dark:hover:text-gray-200 flex items-center justify-center gap-1" @click="toggleSort('bpm')">
                            BPM
                            <svg x-show="sortBy === 'bpm'" class="w-3 h-3" :class="sortDir === 'asc' ? '' : 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                        </div>
                        <div class="col-span-2">Теги</div>
                        <div class="col-span-2 text-center cursor-pointer hover:text-gray-700 dark:hover:text-gray-200 flex items-center justify-center gap-1" @click="toggleSort('popular')">
                            Використано
                            <svg x-show="sortBy === 'popular'" class="w-3 h-3" :class="sortDir === 'asc' ? '' : 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                        </div>
                    </div>
                    <!-- Rows -->
                    <template x-for="song in filteredSongs" :key="song.id">
                        <div @click="openSongModal(song)"
                             class="grid grid-cols-1 md:grid-cols-12 gap-1 md:gap-2 px-4 py-3 cursor-pointer border-b border-gray-100 dark:border-gray-700/50 last:border-b-0 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors items-center group">
                            <!-- Title (always visible) -->
                            <div class="md:col-span-3 flex items-center gap-2 min-w-0">
                                <span class="font-medium text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 truncate" x-text="song.title"></span>
                            </div>
                            <!-- Artist (always visible) -->
                            <div class="md:col-span-2 min-w-0">
                                <span class="text-sm text-gray-500 dark:text-gray-400 truncate block" x-text="song.artist || '—'"></span>
                            </div>
                            <!-- Key badge (always visible) -->
                            <div class="md:col-span-2 md:text-center">
                                <span x-show="song.key" class="inline-block px-2 py-0.5 bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 text-xs font-mono rounded" x-text="song.key"></span>
                                <span x-show="!song.key" class="text-xs text-gray-400">—</span>
                            </div>
                            <!-- BPM (hidden on mobile) -->
                            <div class="hidden md:block md:col-span-1 text-center text-sm text-gray-500 dark:text-gray-400">
                                <span x-text="song.bpm || '—'"></span>
                            </div>
                            <!-- Tags (hidden on mobile) -->
                            <div class="hidden md:flex md:col-span-2 flex-wrap gap-1 min-w-0">
                                <template x-for="tag in (song.tags || []).slice(0, 3)" :key="tag">
                                    <span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 text-xs rounded-full truncate max-w-[120px]" x-text="tag"></span>
                                </template>
                                <span x-show="song.tags && song.tags.length > 3" class="text-xs text-gray-400" x-text="'+' + (song.tags.length - 3)"></span>
                            </div>
                            <!-- Times used (hidden on mobile) -->
                            <div class="hidden md:block md:col-span-2 text-center text-sm text-gray-500 dark:text-gray-400">
                                <span x-text="(song.times_used || 0)"></span>
                            </div>
                            <!-- Mobile: secondary info row -->
                            <div class="md:hidden flex items-center gap-3 text-xs text-gray-400 mt-0.5">
                                <span x-show="song.bpm" x-text="song.bpm + ' BPM'"></span>
                                <span x-text="(song.times_used || 0) + ' раз'"></span>
                                <template x-for="tag in (song.tags || []).slice(0, 2)" :key="tag">
                                    <span class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-700 rounded-full" x-text="tag"></span>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Empty States -->
                <div x-show="filteredSongs.length === 0 && songs.length > 0" class="text-center py-12">
                    <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Нічого не знайдено</h3>
                    <p class="text-gray-500 dark:text-gray-400">Спробуйте змінити параметри пошуку</p>
                </div>

                <div x-show="songs.length === 0" class="text-center py-12">
                    <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Немає пісень</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-4">Додайте першу пісню до бібліотеки</p>
                    @can('manage-ministry', $ministry)
                    <button @click="openCreateModal()" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Додати пісню
                    </button>
                    @endcan
                </div>

                <!-- Song Modal (Create/Edit) -->
                <div x-show="showModal" x-cloak
                     class="fixed inset-0 z-50 overflow-y-auto"
                     @keydown.escape.window="showModal = false">
                    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                        <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                             class="fixed inset-0 bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75"
                             @click="showModal = false"></div>

                        <div x-show="showModal" x-transition:enter="ease-out duration-300"
                             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                             x-transition:leave="ease-in duration-200"
                             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                             class="relative inline-block w-full max-w-3xl p-6 my-8 text-left align-middle bg-white dark:bg-gray-800 rounded-2xl shadow-xl transform transition-all max-h-[90vh] overflow-y-auto">

                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-xl font-semibold text-gray-900 dark:text-white" x-text="editingId ? 'Редагувати пісню' : 'Додати пісню'"></h3>
                                <button @click="showModal = false" class="text-gray-400 hover:text-gray-500">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>

                            <form @submit.prevent="saveSong()">
                                <div class="space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Назва <span class="text-red-500">*</span></label>
                                            <input type="text" x-model="form.title" required
                                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Автор</label>
                                            <input type="text" x-model="form.artist" list="artists-list-modal"
                                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                                            <datalist id="artists-list-modal">
                                                <template x-for="artist in artists" :key="artist">
                                                    <option :value="artist"></option>
                                                </template>
                                            </datalist>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Тональність</label>
                                            <div class="relative">
                                                <input type="text"
                                                       x-model="keyQuery"
                                                       @focus="keyDropdownOpen = true"
                                                       @click.away="keyDropdownOpen = false"
                                                       @keydown.escape="keyDropdownOpen = false"
                                                       @keydown.enter.prevent="filteredKeys.length && selectKey(filteredKeys[0][0])"
                                                       placeholder="Почніть вводити..."
                                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                                                <div x-show="keyDropdownOpen && filteredKeys.length > 0" x-transition
                                                     class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg max-h-48 overflow-auto">
                                                    <div @click="selectKey('')"
                                                         class="px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm text-gray-500">
                                                        Не вказано
                                                    </div>
                                                    <template x-for="[key, label] in filteredKeys" :key="key">
                                                        <div @click="selectKey(key)"
                                                             class="px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm"
                                                             :class="form.key === key ? 'bg-primary-50 dark:bg-primary-900/30' : ''">
                                                            <span x-text="key" class="font-medium"></span>
                                                            <span x-text="' — ' + label" class="text-gray-500 text-xs"></span>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">BPM</label>
                                            <input type="number" x-model="form.bpm" min="30" max="300"
                                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Теги</label>
                                        <div class="flex flex-wrap gap-2 mb-2">
                                            <template x-for="tag in allTags" :key="tag">
                                                <label class="inline-flex items-center cursor-pointer">
                                                    <input type="checkbox" :value="tag" x-model="form.tags" class="sr-only peer">
                                                    <span class="px-3 py-1.5 rounded-full text-sm border transition-all peer-checked:bg-primary-600 peer-checked:text-white peer-checked:border-primary-600 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:border-primary-400" x-text="tag"></span>
                                                </label>
                                            </template>
                                        </div>
                                        <input type="text" x-model="form.new_tag" placeholder="Нові теги через кому"
                                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Коментарі</label>
                                        <textarea x-model="form.notes" rows="2"
                                                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"
                                                  placeholder="Нотатки для команди"></textarea>
                                    </div>

                                    <!-- Resource Links -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Посилання на ресурси</label>
                                        <template x-for="(link, index) in form.resource_links" :key="index">
                                            <div class="flex gap-2 mb-2">
                                                <input x-model="link.label" type="text" placeholder="Назва"
                                                       class="flex-1 px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500">
                                                <input x-model="link.url" type="url" placeholder="https://..."
                                                       class="flex-[2] px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500">
                                                <button type="button" @click="form.resource_links.splice(index, 1)"
                                                        class="px-2 py-1.5 text-gray-400 hover:text-red-500 transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </template>
                                        <button type="button" @click="form.resource_links.push({label: '', url: ''})"
                                                class="inline-flex items-center text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                            Додати посилання
                                        </button>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Текст</label>
                                        <textarea x-model="form.lyrics" rows="4"
                                                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 font-mono text-sm"></textarea>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Текст з акордами</label>
                                        <textarea x-model="form.chords" rows="6"
                                                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 font-mono text-sm"
                                                  placeholder="[C]Святий, Святий, [Am]Святий..."></textarea>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">CCLI</label>
                                            <input type="text" x-model="form.ccli_number"
                                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">YouTube</label>
                                            <input type="url" x-model="form.youtube_url"
                                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Spotify</label>
                                            <input type="url" x-model="form.spotify_url"
                                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                                        </div>
                                    </div>
                                </div>

                                <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                                    <button type="button" @click="showModal = false; if(editingId && viewingSong) showViewModal = true; resetForm();"
                                            class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                        Скасувати
                                    </button>
                                    <button type="submit" :disabled="saving"
                                            class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50">
                                        <span x-show="!saving" x-text="editingId ? 'Зберегти' : 'Додати'"></span>
                                        <span x-show="saving">Збереження...</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Song View Modal -->
                <div x-show="showViewModal" x-cloak
                     class="fixed inset-0 z-50 overflow-y-auto"
                     @keydown.escape.window="showViewModal = false">
                    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                        <div x-show="showViewModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                             class="fixed inset-0 bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75"
                             @click="showViewModal = false"></div>

                        <div x-show="showViewModal" x-transition:enter="ease-out duration-300"
                             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                             x-transition:leave="ease-in duration-200"
                             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                             class="relative inline-block w-full max-w-3xl p-6 my-8 text-left align-middle bg-white dark:bg-gray-800 rounded-2xl shadow-xl transform transition-all max-h-[90vh] overflow-y-auto">

                            <template x-if="viewingSong">
                                <div>
                                    <!-- Header -->
                                    <div class="flex items-start justify-between mb-4">
                                        <div>
                                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white" x-text="viewingSong.title"></h3>
                                            <p x-show="viewingSong.artist" class="text-gray-500 dark:text-gray-400 mt-1" x-text="viewingSong.artist"></p>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span x-show="viewingSong.key" class="px-3 py-1 bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 text-sm font-mono rounded-lg" x-text="viewingSong.key"></span>
                                            <span x-show="viewingSong.bpm" class="px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 text-sm rounded-lg" x-text="viewingSong.bpm + ' BPM'"></span>
                                            <button @click="showViewModal = false" class="ml-2 text-gray-400 hover:text-gray-500">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Tags -->
                                    <div x-show="viewingSong.tags && viewingSong.tags.length > 0" class="flex flex-wrap gap-2 mb-4">
                                        <template x-for="tag in viewingSong.tags" :key="tag">
                                            <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 text-sm rounded-full" x-text="tag"></span>
                                        </template>
                                    </div>

                                    <!-- Notes -->
                                    <div x-show="viewingSong.notes" class="mb-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                                        <p class="text-sm text-yellow-800 dark:text-yellow-200">
                                            <span class="font-medium">Коментарі:</span> <span x-html="(viewingSong.notes || '').replace(/(https?:\/\/[^\s<]+)/g, '<a href=&quot;$1&quot; target=&quot;_blank&quot; rel=&quot;noopener&quot; class=&quot;underline text-blue-600 dark:text-blue-400 hover:text-blue-800&quot;>$1</a>')"></span>
                                        </p>
                                    </div>

                                    <!-- Resource Links -->
                                    <div x-show="viewingSong.resource_links && viewingSong.resource_links.length > 0" class="mb-4">
                                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ресурси</h4>
                                        <div class="flex flex-wrap gap-2">
                                            <template x-for="link in (viewingSong.resource_links || [])" :key="link.url">
                                                <a :href="link.url" target="_blank" rel="noopener noreferrer"
                                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-sm rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors">
                                                    <span x-text="link.label"></span>
                                                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                    </svg>
                                                </a>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Links -->
                                    <div x-show="viewingSong.youtube_url || viewingSong.spotify_url" class="flex flex-wrap gap-2 mb-4">
                                        <a x-show="viewingSong.youtube_url" :href="viewingSong.youtube_url" target="_blank"
                                           class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700">
                                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                                            YouTube
                                        </a>
                                        <a x-show="viewingSong.spotify_url" :href="viewingSong.spotify_url" target="_blank"
                                           class="inline-flex items-center px-4 py-2 bg-[#1DB954] text-white text-sm rounded-lg hover:bg-[#1ed760]">
                                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/></svg>
                                            Spotify
                                        </a>
                                    </div>

                                    <!-- Chords -->
                                    <div x-show="viewingSong.chords" class="mb-4">
                                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Текст з акордами</h4>
                                        <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg font-mono text-sm whitespace-pre-wrap max-h-96 overflow-y-auto" x-html="formatChords(viewingSong.chords)"></div>
                                    </div>

                                    <!-- Lyrics (if no chords) -->
                                    <div x-show="viewingSong.lyrics && !viewingSong.chords" class="mb-4">
                                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Текст</h4>
                                        <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg text-sm whitespace-pre-wrap max-h-96 overflow-y-auto" x-text="viewingSong.lyrics"></div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            Використано <span x-text="viewingSong.times_used || 0"></span> раз
                                        </div>
                                        @can('manage-ministry', $ministry)
                                        <div class="flex items-center gap-2">
                                            <button @click="showViewModal = false; openEditModal(viewingSong)"
                                                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-lg transition-colors">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                                Редагувати
                                            </button>
                                            <button @click="showViewModal = false; deleteSong(viewingSong)"
                                                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                Видалити
                                            </button>
                                        </div>
                                        @endcan
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Settings Tab -->
            @can('manage-ministry', $ministry)
            <div x-show="activeTab === 'settings'"{{ $tab !== 'settings' ? ' style="display:none"' : '' }}
                 x-data="settingsTab()"
                 x-init="init()">
                <div class="max-w-2xl space-y-8">
                    <!-- General Settings -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Загальні налаштування</h3>
                        <form method="POST" action="{{ route('ministries.update', $ministry) }}" class="space-y-4">
                            @csrf
                            @method('PUT')

                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Назва</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $ministry->name) }}" required
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            </div>

                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Опис</label>
                                <textarea name="description" id="description" rows="2"
                                          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">{{ old('description', $ministry->description) }}</textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Лідер</label>
                                <x-person-select name="leader_id" :people="$availablePeople->merge($ministry->leader ? collect([$ministry->leader]) : collect())->unique('id')->sortBy('last_name')" :selected="old('leader_id', $ministry->leader_id)" placeholder="Пошук лідера..." />
                            </div>

                            <div>
                                <label for="color" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Колір</label>
                                <input type="color" name="color" id="color" value="{{ old('color', $ministry->color ?? '#3b82f6') }}"
                                       class="w-16 h-10 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer">
                            </div>

                            <div class="flex items-center gap-3 pt-2">
                                <input type="hidden" name="is_worship_ministry" value="0">
                                <input type="checkbox" name="is_worship_ministry" id="is_worship_ministry" value="1"
                                       {{ old('is_worship_ministry', $ministry->is_worship_ministry) ? 'checked' : '' }}
                                       class="w-5 h-5 rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500">
                                <label for="is_worship_ministry" class="text-sm text-gray-700 dark:text-gray-300">
                                    <span class="font-medium">Музичне служіння</span>
                                    <span class="block text-gray-500 dark:text-gray-400 text-xs">Показувати бібліотеку пісень та Music Stand</span>
                                </label>
                            </div>

                            <div class="pt-4">
                                <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                                    Зберегти зміни
                                </button>
                            </div>
                        </form>
                    </div>

                    <hr class="border-gray-200 dark:border-gray-700">

                    <!-- Access Settings -->
                    <div>
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

                        <!-- Specific -->
                        <label class="flex items-start gap-3 p-4 border rounded-xl cursor-pointer transition-all"
                               :class="visibility === 'specific' ? 'border-red-500 bg-red-50 dark:bg-red-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'">
                            <input type="radio" name="visibility" value="specific" x-model="visibility" @change="saveVisibility()" class="mt-1 w-5 h-5 text-red-600 focus:ring-red-500 border-gray-300 dark:border-gray-600">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    <span class="font-medium text-gray-900 dark:text-white">Тільки конкретні люди</span>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Тільки адміністратори та люди, вибрані нижче</p>
                            </div>
                        </label>
                    </div>

                    <!-- Additional People with Access -->
                    <div class="mt-6 p-4 border rounded-xl"
                         :class="visibility === 'specific' ? 'border-red-300 dark:border-red-700 bg-red-50 dark:bg-red-900/20' : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30'">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-5 h-5" :class="visibility === 'specific' ? 'text-red-500' : 'text-blue-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                            <h4 class="font-medium text-gray-900 dark:text-white" x-text="visibility === 'specific' ? 'Люди з доступом' : 'Додаткові люди з доступом'"></h4>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-3" x-text="visibility === 'specific' ? 'Тільки ці люди (та адміністратори) матимуть доступ до команди' : 'Ці люди матимуть доступ незалежно від вибраної опції вище'"></p>

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

                </div>

                    @if($ministry->is_worship_ministry)
                    <hr class="border-gray-200 dark:border-gray-700">

                    <!-- Worship Roles Settings -->
                    <div x-data="worshipRolesManager()">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Роли музыкального служения</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Настройте инструменты и роли для команды прославления</p>

                        <!-- Roles List -->
                        <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl">
                            @if($worshipRoles->count() > 0)
                                <div class="divide-y divide-gray-200 dark:divide-gray-600">
                                    @foreach($worshipRoles as $role)
                                        <div class="p-3 flex items-center justify-between group">
                                            <div class="flex items-center gap-3">
                                                <div class="w-9 h-9 rounded-lg flex items-center justify-center text-base"
                                                     style="background-color: {{ $role->color ?? '#6366f1' }}20; color: {{ $role->color ?? '#6366f1' }}">
                                                    {{ $role->icon ?? '🎵' }}
                                                </div>
                                                <span class="font-medium text-gray-900 dark:text-white text-sm">{{ $role->name }}</span>
                                            </div>
                                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <button type="button"
                                                        @click="editRole({{ $role->id }}, '{{ addslashes($role->name) }}', '{{ $role->icon }}', '{{ $role->color }}')"
                                                        class="p-1.5 text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 rounded">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                    </svg>
                                                </button>
                                                <form action="{{ route('ministries.worship-roles.destroy', [$ministry, $role]) }}" method="POST" onsubmit="return confirm('Удалить роль?')" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 dark:hover:text-red-400 rounded">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="p-6 text-center">
                                    <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-gray-100 dark:bg-gray-600 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                                        </svg>
                                    </div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Нет ролей. Добавьте роли ниже.</p>
                                </div>
                            @endif

                            <!-- Add Role Form -->
                            <div class="p-3 border-t border-gray-200 dark:border-gray-600">
                                <form action="{{ route('ministries.worship-roles.store', $ministry) }}" method="POST" class="flex flex-wrap gap-2">
                                    @csrf
                                    <input type="text" name="name" placeholder="Название роли (напр. Вокал, Гитара)" required
                                           class="flex-1 min-w-[150px] px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                    <input type="text" name="icon" placeholder="🎵" maxlength="5"
                                           class="w-14 px-2 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-center">
                                    <input type="color" name="color" value="#6366f1"
                                           class="w-10 h-10 rounded-lg border border-gray-300 dark:border-gray-600 cursor-pointer">
                                    <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">
                                        Добавить
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Suggested Roles -->
                        @if($worshipRoles->count() === 0)
                        <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
                            <h4 class="text-sm font-medium text-blue-800 dark:text-blue-300 mb-2">Рекомендуемые роли:</h4>
                            <div class="flex flex-wrap gap-2">
                                @foreach(['🎤 Ведущий вокал', '🎤 Бэк-вокал', '🎸 Акустическая гитара', '🎸 Электрогитара', '🎸 Бас', '🎹 Клавиши', '🥁 Барабаны', '🎚 Звук', '💻 Медиа'] as $suggestion)
                                    <button type="button" @click="addSuggested('{{ $suggestion }}')"
                                            class="px-2.5 py-1 text-xs bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-600 transition-colors">
                                        {{ $suggestion }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Edit Role Modal -->
                        <div x-show="showEditModal" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="showEditModal = false">
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6 w-full max-w-md mx-4" @click.stop>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Редактировать роль</h3>
                                <form :action="editFormAction" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Название</label>
                                            <input type="text" name="name" x-model="editName" required
                                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                        </div>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Иконка</label>
                                                <input type="text" name="icon" x-model="editIcon" maxlength="5"
                                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-center">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Цвет</label>
                                                <input type="color" name="color" x-model="editColor"
                                                       class="w-full h-10 rounded-lg border border-gray-300 dark:border-gray-600 cursor-pointer">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex justify-end gap-2 mt-6">
                                        <button type="button" @click="showEditModal = false" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                                            Отмена
                                        </button>
                                        <button type="submit" class="px-4 py-2 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition-colors">
                                            Сохранить
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <script>
                        function worshipRolesManager() {
                            return {
                                showEditModal: false,
                                editFormAction: '',
                                editName: '',
                                editIcon: '',
                                editColor: '#6366f1',

                                editRole(id, name, icon, color) {
                                    this.editFormAction = '/ministries/{{ $ministry->id }}/worship-roles/' + id;
                                    this.editName = name;
                                    this.editIcon = icon || '';
                                    this.editColor = color || '#6366f1';
                                    this.showEditModal = true;
                                },

                                addSuggested(text) {
                                    const parts = text.split(' ');
                                    const icon = parts[0];
                                    const name = parts.slice(1).join(' ');

                                    const form = document.querySelector('form[action*="worship-roles"][method="POST"]:not([action*="update"])');
                                    form.querySelector('input[name="name"]').value = name;
                                    form.querySelector('input[name="icon"]').value = icon;
                                    form.submit();
                                }
                            };
                        }
                    </script>
                    @endif
            </div>
            </div>
            @endcan
        </div>
    </div>

</div>

@push('styles')
<style>
    /* Custom document editor */
    .doc-toolbar { display: flex; flex-wrap: wrap; gap: 2px; padding: 6px 10px; border-bottom: 1px solid #e5e7eb; background: #f9fafb; position: sticky; top: 0; z-index: 10; }
    .dark .doc-toolbar { background: #1e293b; border-bottom-color: #374151; }
    .doc-toolbar .tb-btn { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 6px; border: none; background: none; cursor: pointer; color: #4b5563; font-size: 14px; transition: all .15s; }
    .doc-toolbar .tb-btn:hover { background: #e5e7eb; color: #111827; }
    .dark .doc-toolbar .tb-btn { color: #9ca3af; }
    .dark .doc-toolbar .tb-btn:hover { background: #374151; color: #f3f4f6; }
    .doc-toolbar .tb-btn.active { background: #dbeafe; color: #2563eb; }
    .dark .doc-toolbar .tb-btn.active { background: #1e3a5f; color: #60a5fa; }
    .doc-toolbar .tb-sep { width: 1px; height: 24px; background: #d1d5db; margin: 4px 4px; align-self: center; }
    .dark .doc-toolbar .tb-sep { background: #4b5563; }
    .doc-toolbar select { height: 32px; border-radius: 6px; border: 1px solid #d1d5db; background: #fff; color: #374151; font-size: 13px; padding: 0 6px; cursor: pointer; }
    .dark .doc-toolbar select { background: #1f2937; border-color: #4b5563; color: #d1d5db; }
    .doc-toolbar input[type="color"] { width: 32px; height: 32px; border: 1px solid #d1d5db; border-radius: 6px; padding: 2px; cursor: pointer; background: #fff; }

    .doc-editable { min-height: 400px; padding: 32px 40px; font-family: Arial, sans-serif; font-size: 15px; line-height: 1.7; color: #202124; outline: none; }
    .dark .doc-editable { color: #e2e8f0; }
    .doc-editable:empty::before { content: attr(data-placeholder); color: #9ca3af; pointer-events: none; }
    .doc-editable h1 { font-size: 26px; margin: 20px 0 8px; font-weight: 700; }
    .doc-editable h2 { font-size: 22px; margin: 18px 0 6px; font-weight: 600; }
    .doc-editable h3 { font-size: 18px; margin: 16px 0 4px; font-weight: 600; }
    .doc-editable p { margin: 0 0 8px; }
    .doc-editable blockquote { border-left: 4px solid #4285f4; padding-left: 16px; color: #5f6368; margin: 16px 0; }
    .doc-editable ul, .doc-editable ol { padding-left: 24px; margin: 8px 0; }
    .doc-editable table { border-collapse: collapse; width: 100%; margin: 12px 0; }
    .doc-editable td, .doc-editable th { border: 1px solid #dadce0; padding: 8px 12px; }
    .doc-editable a { color: #1a73e8; text-decoration: underline; }
    .doc-editable img { max-width: 100%; height: auto; border-radius: 4px; }
    @media (max-width: 640px) {
        .doc-editable { padding: 20px 16px; }
    }
</style>
@endpush

@push('scripts')
<script>
/* === Custom Document Editor (zero dependencies) === */
function docExec(cmd, val) { document.execCommand(cmd, false, val || null); document.getElementById('doc-editable')?.focus(); }
function docExecBlock(tag) { if (!tag) return; document.execCommand('formatBlock', false, tag); document.getElementById('doc-editable')?.focus(); }
function docExecFontSize(size) { if (!size) return; document.execCommand('fontSize', false, size); document.getElementById('doc-editable')?.focus(); }
function docExecColor(cmd, val) { document.execCommand(cmd, false, val); document.getElementById('doc-editable')?.focus(); }
function docInsertLink() {
    const url = prompt('URL посилання:', 'https://');
    if (url) document.execCommand('createLink', false, url);
    document.getElementById('doc-editable')?.focus();
}
function docInsertImage() {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';
    input.onchange = () => {
        const file = input.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = (e) => {
            document.execCommand('insertImage', false, e.target.result);
        };
        reader.readAsDataURL(file);
    };
    input.click();
}

function resourcesManager() {
    return {
        showCreateFolder: false,
        showRename: false,
        showDocEditor: false,
        renameName: '',
        menuOpen: false,
        menuX: 0,
        menuY: 0,
        selectedId: null,
        selectedName: '',
        previewFile: null,
        uploading: false,
        uploadError: '',
        docId: null,
        docName: '',
        docContent: '',
        docSaving: false,
        docSaved: false,
        _docCreated: false,

        openMenu(id, name, event) {
            event.stopPropagation();
            this.selectedId = id;
            this.selectedName = name;
            this.menuX = Math.min(event.clientX, window.innerWidth - 200);
            this.menuY = Math.min(event.clientY, window.innerHeight - 100);
            this.menuOpen = true;
        },

        showRenameModal() {
            this.menuOpen = false;
            this.renameName = this.selectedName;
            this.showRename = true;
            this.$nextTick(() => this.$refs.renameInput?.focus());
        },

        submitRename() {
            if (!this.renameName.trim()) return;
            const form = document.getElementById('resRenameForm');
            form.action = `/resources/${this.selectedId}/rename`;
            document.getElementById('resRenameInput').value = this.renameName;
            form.submit();
        },

        showPreview(file) {
            this.previewFile = file;
        },

        async uploadFile(event) {
            const files = event.target.files;
            if (!files.length) return;
            this.uploading = true;
            this.uploadError = '';

            for (const file of files) {
                const formData = new FormData();
                formData.append('file', file);
                formData.append('parent_id', '{{ $currentFolder?->id ?? "" }}');
                formData.append('_token', '{{ csrf_token() }}');

                try {
                    const response = await fetch('{{ route("ministries.resources.upload", $ministry) }}', {
                        method: 'POST',
                        body: formData
                    });
                    if (!response.ok) {
                        const data = await response.json();
                        this.uploadError = data.message || 'Помилка завантаження';
                    }
                } catch (error) {
                    this.uploadError = 'Помилка завантаження';
                }
            }

            event.target.value = '';
            this.uploading = false;
            if (!this.uploadError) window.location.reload();
        },

        deleteItem() {
            this.menuOpen = false;
            if (!confirm('Видалити цей елемент?')) return;
            const form = document.getElementById('resDeleteForm');
            form.action = `/resources/${this.selectedId}`;
            form.submit();
        },

        async createAndOpenDocument() {
            try {
                const response = await fetch('{{ route("ministries.resources.document.create", $ministry) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        name: 'Новий документ',
                        parent_id: '{{ $currentFolder?->id ?? "" }}' || null
                    })
                });

                if (response.ok) {
                    const data = await response.json();
                    this._docCreated = true;
                    this.openDocument({ id: data.id, name: 'Новий документ', content: '' });
                } else {
                    const err = await response.json();
                    alert(err.message || 'Помилка');
                }
            } catch (error) {
                alert('Помилка створення документа');
            }
        },

        async openDocument(doc) {
            this.docId = doc.id;
            this.docName = doc.name;
            this.docContent = doc.content || '';
            this.showDocEditor = true;
            this.docSaved = false;
            document.body.style.overflow = 'hidden';

            await this.$nextTick();

            const editable = document.getElementById('doc-editable');
            if (editable) {
                editable.innerHTML = this.docContent;
                editable.focus();
                // Ctrl+S
                editable.onkeydown = (e) => {
                    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                        e.preventDefault();
                        this.saveDocument();
                    }
                };
            }
        },

        async saveDocument() {
            const editable = document.getElementById('doc-editable');
            if (!editable || !this.docId) return;
            this.docSaving = true;
            this.docSaved = false;

            try {
                const content = editable.innerHTML;
                const response = await fetch(`/resources/${this.docId}/content`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ name: this.docName, content })
                });

                if (response.ok) {
                    this.docSaved = true;
                    setTimeout(() => this.docSaved = false, 3000);
                } else {
                    alert('Помилка збереження');
                }
            } catch (error) {
                alert('Помилка збереження');
            } finally {
                this.docSaving = false;
            }
        },

        closeDocEditor() {
            const editable = document.getElementById('doc-editable');
            if (editable) editable.innerHTML = '';
            document.body.style.overflow = '';
            this.showDocEditor = false;
            if (this._docCreated) {
                this._docCreated = false;
                window.location.reload();
            }
        }
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
function goalsManager() {
    return {
        editingVision: false,
        showGoalModal: false,
        showTaskModal: false,
        editingGoalId: null,
        editingTaskId: null,
        goalForm: { title: '', description: '', period: '', period_start: '', period_end: '', due_date: '', priority: 'medium', status: 'active' },
        taskForm: { title: '', description: '', goal_id: '', assigned_to: '', due_date: '', priority: 'medium', status: 'todo' },
        resetGoalForm() {
            this.editingGoalId = null;
            this.goalForm = { title: '', description: '', period: '', period_start: '', period_end: '', due_date: '', priority: 'medium', status: 'active' };
        },
        resetTaskForm() {
            this.editingTaskId = null;
            this.taskForm = { title: '', description: '', goal_id: '', assigned_to: '', due_date: '', priority: 'medium', status: 'todo' };
        },
        editGoal(id, data) {
            this.editingGoalId = id;
            this.goalForm = { ...data, period_start: '', period_end: '' };
            // Parse period string if exists (format: "dd.mm.yyyy – dd.mm.yyyy" or "dd.mm.yyyy")
            if (data.period) {
                const parts = data.period.split(' – ');
                if (parts.length === 2) {
                    // Convert dd.mm.yyyy to yyyy-mm-dd for date inputs
                    const parseDate = (str) => {
                        const [d, m, y] = str.split('.');
                        return `${y}-${m}-${d}`;
                    };
                    this.goalForm.period_start = parseDate(parts[0]);
                    this.goalForm.period_end = parseDate(parts[1]);
                }
            }
            this.showGoalModal = true;
        },
        editTask(id, data) {
            this.editingTaskId = id;
            this.taskForm = { ...data };
            this.showTaskModal = true;
        }
    }
}

@php
    $songsCollection = collect($songs ?? []);
    $songsData = $songsCollection->map(function($s) {
        return [
            'id' => $s->id,
            'title' => $s->title,
            'artist' => $s->artist,
            'key' => $s->key,
            'bpm' => $s->bpm,
            'lyrics' => $s->lyrics,
            'chords' => $s->chords,
            'ccli_number' => $s->ccli_number,
            'youtube_url' => $s->youtube_url,
            'spotify_url' => $s->spotify_url,
            'tags' => $s->tags ?? [],
            'notes' => $s->notes,
            'resource_links' => $s->resource_links ?? [],
            'times_used' => $s->times_used ?? 0,
            'created_at' => $s->created_at,
        ];
    });
    $allTagsData = $songsCollection->pluck('tags')->flatten()->filter()->unique()->sort()->values();
    $artistsData = $songsCollection->pluck('artist')->filter()->unique()->sort()->values();
@endphp
function songsLibrary() {
    return {
        songs: @json($songsData),
        allTags: @json($allTagsData),
        artists: @json($artistsData),
        search: '',
        filterKey: '',
        filterTag: '',
        sortBy: 'title',
        sortDir: 'asc',
        keyQuery: '',
        keyDropdownOpen: false,
        songKeysMap: @js(\App\Models\Song::KEYS),
        get filteredKeys() {
            if (!this.keyQuery) return Object.entries(this.songKeysMap);
            const q = this.keyQuery.toLowerCase();
            return Object.entries(this.songKeysMap).filter(([key, label]) =>
                key.toLowerCase().includes(q) || label.toLowerCase().includes(q)
            );
        },
        selectKey(key) {
            this.form.key = key;
            this.keyQuery = key;
            this.keyDropdownOpen = false;
        },
        expandedSong: null,
        showModal: false,
        editingId: null,
        saving: false,
        viewingSong: null,
        showViewModal: false,
        form: {
            title: '', artist: '', key: '', bpm: '', lyrics: '', chords: '',
            ccli_number: '', youtube_url: '', spotify_url: '', tags: [], new_tag: '', notes: '',
            resource_links: []
        },

        get filteredSongs() {
            let result = this.songs;
            if (this.search) {
                const s = this.search.toLowerCase();
                result = result.filter(song =>
                    song.title.toLowerCase().includes(s) ||
                    (song.artist && song.artist.toLowerCase().includes(s))
                );
            }
            if (this.filterKey) {
                result = result.filter(song => song.key === this.filterKey);
            }
            if (this.filterTag) {
                result = result.filter(song => song.tags && song.tags.includes(this.filterTag));
            }
            const dir = this.sortDir === 'asc' ? 1 : -1;
            return [...result].sort((a, b) => {
                switch (this.sortBy) {
                    case 'artist': return dir * (a.artist || '').localeCompare(b.artist || '', 'uk');
                    case 'key': return dir * (a.key || '').localeCompare(b.key || '', 'uk');
                    case 'bpm': return dir * ((a.bpm || 0) - (b.bpm || 0));
                    case 'recent': return dir * (new Date(b.created_at) - new Date(a.created_at));
                    case 'popular': return dir * ((b.times_used || 0) - (a.times_used || 0));
                    default: return dir * a.title.localeCompare(b.title, 'uk');
                }
            });
        },

        toggleSort(col) {
            if (this.sortBy === col) {
                this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortBy = col;
                this.sortDir = (col === 'popular' || col === 'recent' || col === 'bpm') ? 'desc' : 'asc';
            }
        },

        toggleSong(id) {
            this.expandedSong = this.expandedSong === id ? null : id;
        },

        formatChords(chords) {
            if (!chords) return '';
            let html = chords.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            html = html.replace(/\[([A-G][#b]?m?(?:add|sus|dim|aug|maj|min)?[0-9]?(?:\/[A-G][#b]?)?)\]/g,
                '<span class="inline-block px-1 py-0.5 bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300 text-xs font-mono rounded mx-0.5">$1</span>');
            return html.replace(/\n/g, '<br>');
        },

        resetForm() {
            this.form = {
                title: '', artist: '', key: '', bpm: '', lyrics: '', chords: '',
                ccli_number: '', youtube_url: '', spotify_url: '', tags: [], new_tag: '', notes: '',
                resource_links: []
            };
            this.keyQuery = '';
            this.keyDropdownOpen = false;
            this.editingId = null;
        },

        openCreateModal() {
            this.resetForm();
            this.showModal = true;
        },

        openSongModal(song) {
            this.viewingSong = song;
            this.showViewModal = true;
        },

        openEditModal(song) {
            this.editingId = song.id;
            this.viewingSong = song; // Keep reference to return to view modal after save
            this.showViewModal = false;
            this.form = {
                title: song.title || '',
                artist: song.artist || '',
                key: song.key || '',
                bpm: song.bpm || '',
                lyrics: song.lyrics || '',
                chords: song.chords || '',
                ccli_number: song.ccli_number || '',
                youtube_url: song.youtube_url || '',
                spotify_url: song.spotify_url || '',
                tags: song.tags ? [...song.tags] : [],
                new_tag: '',
                notes: song.notes || '',
                resource_links: song.resource_links ? song.resource_links.map(l => ({...l})) : []
            };
            this.keyQuery = song.key || '';
            this.keyDropdownOpen = false;
            this.showModal = true;
        },

        async saveSong() {
            this.saving = true;
            const url = this.editingId ? `/songs/${this.editingId}` : '/songs';
            const method = this.editingId ? 'PUT' : 'POST';

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.form)
                });

                if (response.ok) {
                    const data = await response.json();
                    const wasEditing = this.editingId;
                    if (this.editingId) {
                        const index = this.songs.findIndex(s => s.id === this.editingId);
                        if (index !== -1) {
                            this.songs[index] = { ...this.songs[index], ...data.song };
                            // Update viewingSong if it was being viewed
                            if (this.viewingSong && this.viewingSong.id === this.editingId) {
                                this.viewingSong = this.songs[index];
                            }
                        }
                    } else {
                        this.songs.push(data.song);
                    }
                    // Update tags list
                    this.allTags = [...new Set(this.songs.flatMap(s => s.tags || []))].sort();
                    this.artists = [...new Set(this.songs.map(s => s.artist).filter(Boolean))].sort();
                    this.showModal = false;
                    this.resetForm();
                    // Re-open view modal if we were editing
                    if (wasEditing && this.viewingSong) {
                        this.showViewModal = true;
                    }
                } else {
                    const err = await response.json();
                    alert(err.message || 'Помилка збереження');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Помилка збереження');
            } finally {
                this.saving = false;
            }
        },

        async deleteSong(song) {
            if (!confirm(`Видалити пісню "${song.title}"?`)) return;

            try {
                const response = await fetch(`/songs/${song.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    this.songs = this.songs.filter(s => s.id !== song.id);
                    this.expandedSong = null;
                    this.allTags = [...new Set(this.songs.flatMap(s => s.tags || []))].sort();
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Помилка видалення');
            }
        }
    }
}

function settingsTab() {
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
