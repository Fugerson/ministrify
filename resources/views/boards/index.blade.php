@extends('layouts.app')

@section('title', 'Трекер завдань')

@section('content')
<div class="h-full -mt-2" x-data="churchBoard()" x-init="init()">
    <!-- Header with filters -->
    <div class="mb-4 space-y-4">
        <!-- Title & Stats Row -->
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Трекер завдань</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Всі завдання команд в одному місці</p>
            </div>

            <!-- Quick Stats -->
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-6 px-4 py-2 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                    <div class="text-center">
                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</p>
                        <p class="text-xs text-gray-500">Всього</p>
                    </div>
                    <div class="text-center">
                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $stats['completed'] }}</p>
                        <p class="text-xs text-gray-500">Готово</p>
                    </div>
                    <div class="text-center">
                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $stats['overdue'] }}</p>
                        <p class="text-xs text-gray-500">Прострочено</p>
                    </div>
                    <div class="text-center">
                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $stats['my_tasks'] }}</p>
                        <p class="text-xs text-gray-500">Мої</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="flex items-center gap-3 flex-wrap">
            <!-- Search with dropdown -->
            <div class="relative" x-data="{ showDropdown: false, searchFocused: false }" @click.away="showDropdown = false">
                <input type="text" x-model="searchQuery" placeholder="Пошук по ID або назві... (/)"
                       @keydown.slash.window.prevent="$el.focus()"
                       @focus="showDropdown = true; searchFocused = true"
                       @blur="searchFocused = false; setTimeout(() => { if (!searchFocused) showDropdown = false }, 200)"
                       @keydown.escape="showDropdown = false"
                       @keydown.enter.prevent="if (filteredCards.length) openCard(filteredCards[0].id)"
                       class="w-48 sm:w-72 pl-9 pr-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>

                <!-- Dropdown with cards list -->
                <div x-show="showDropdown && searchQuery.length > 0"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute z-50 mt-1 w-80 max-h-72 overflow-y-auto bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl">
                    <template x-if="filteredCards.length === 0">
                        <div class="p-3 text-sm text-gray-500 dark:text-gray-400 text-center">
                            Нічого не знайдено
                        </div>
                    </template>
                    <template x-for="card in filteredCards.slice(0, 10)" :key="card.id">
                        <button @click="openCard(card.id); showDropdown = false; searchQuery = ''"
                                class="w-full px-3 py-2 text-left hover:bg-gray-50 dark:hover:bg-gray-700/50 flex items-start gap-2 border-b border-gray-100 dark:border-gray-700/50 last:border-0">
                            <span class="text-xs font-mono text-gray-400 dark:text-gray-500 flex-shrink-0 mt-0.5"
                                  x-text="'#' + card.id"></span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-900 dark:text-white truncate" x-text="card.title"></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400" x-text="card.columnName"></p>
                            </div>
                            <span class="text-xs px-1.5 py-0.5 rounded flex-shrink-0"
                                  :class="{
                                      'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300': card.priority === 'urgent',
                                      'bg-orange-100 text-orange-700 dark:bg-orange-900/50 dark:text-orange-300': card.priority === 'high',
                                      'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/50 dark:text-yellow-300': card.priority === 'medium',
                                      'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400': card.priority === 'low' || !card.priority
                                  }"
                                  x-show="card.priority && card.priority !== 'low'"
                                  x-text="{'urgent': 'Терм.', 'high': 'Вис.', 'medium': 'Сер.'}[card.priority] || ''"></span>
                        </button>
                    </template>
                    <template x-if="filteredCards.length > 10">
                        <div class="p-2 text-xs text-center text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">
                            + ще <span x-text="filteredCards.length - 10"></span> результатів...
                        </div>
                    </template>
                </div>
            </div>

            <!-- Ministry Filter -->
            <select x-model="filters.ministry"
                    class="px-3 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                <option value="">Всі команди</option>
                @foreach($ministries as $ministry)
                    <option value="{{ $ministry->id }}">{{ $ministry->name }}</option>
                @endforeach
            </select>

            <!-- Priority Filter -->
            <select x-model="filters.priority"
                    class="px-3 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                <option value="">Всі пріоритети</option>
                <option value="urgent">Терміново</option>
                <option value="high">Високий</option>
                <option value="medium">Середній</option>
                <option value="low">Низький</option>
            </select>

            <!-- Assignee Filter -->
            <select x-model="filters.assignee"
                    class="px-3 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                <option value="">Всі виконавці</option>
                <option value="me">Мої завдання</option>
                <option value="unassigned">Без виконавця</option>
                @foreach($people as $person)
                    <option value="{{ $person->id }}">{{ $person->full_name }}</option>
                @endforeach
            </select>

            <!-- Clear filters -->
            <template x-if="hasActiveFilters">
                <button @click="clearFilters()" class="px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700 rounded-lg transition-all flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Скинути
                </button>
            </template>

            <!-- Spacer -->
            <div class="flex-1"></div>

            <!-- Shortcuts help -->
            <button @click="showShortcuts = true"
                    class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                    title="Шорткати (?)">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Kanban Columns Container -->
    <div class="kanban-container flex gap-4 pb-4 overflow-x-auto scrollbar-thin" id="kanban-columns">
        @foreach($board->columns as $column)
            <div class="kanban-column flex-shrink-0 w-72 sm:w-80 bg-gray-50 dark:bg-gray-800/50 rounded-2xl flex flex-col border border-gray-200/50 dark:border-gray-700/50"
                 data-column-id="{{ $column->id }}">

                <!-- Column Header -->
                <div class="p-3 flex items-center justify-between border-b border-gray-200/50 dark:border-gray-700/50">
                    <div class="flex items-center gap-2">
                        <h3 class="font-semibold text-gray-800 dark:text-white text-sm">{{ $column->name }}</h3>
                        <span class="column-count text-xs text-gray-400 dark:text-gray-500 bg-gray-200/50 dark:bg-gray-700/50 px-2 py-0.5 rounded-full font-medium"
                              data-column-id="{{ $column->id }}">
                            {{ $column->cards->count() }}
                        </span>
                    </div>
                    <button type="button" @click="showAddCard = {{ $column->id }}; $nextTick(() => $refs.cardInput{{ $column->id }}?.focus())"
                            class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </button>
                </div>

                <!-- Cards Container -->
                <div class="flex-1 p-2 space-y-2 min-h-[120px] kanban-cards overflow-y-auto max-h-[50vh] lg:max-h-[calc(100vh-280px)]"
                     data-column-id="{{ $column->id }}">
                    @foreach($column->cards as $card)
                        <div class="kanban-card group bg-white dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 p-3 cursor-pointer hover:border-gray-300 dark:hover:border-gray-500 transition-all duration-150 overflow-hidden
                                 @if($card->priority === 'urgent') border-l-4 border-l-red-500 @elseif($card->priority === 'high') border-l-4 border-l-orange-500 @elseif($card->priority === 'medium') border-l-4 border-l-yellow-500 @endif"
                             draggable="true"
                             data-card-id="{{ $card->id }}"
                             data-priority="{{ $card->priority }}"
                             data-assignee="{{ $card->assigned_to ?? 'unassigned' }}"
                             data-ministry="{{ $card->ministry_id ?? '' }}"
                             data-due="{{ $card->due_date?->format('Y-m-d') ?? '' }}"
                             data-title="{{ strtolower($card->title) }}"
                             @click="openCard({{ $card->id }})">

                            <!-- Top badges row -->
                            <div class="flex items-center gap-1.5 mb-1.5 flex-wrap">
                                <span class="text-[10px] font-mono text-gray-400 dark:text-gray-500">#{{ $card->id }}</span>
                                <span class="priority-badge inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[10px] font-medium
                                    @if($card->priority === 'urgent') bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300
                                    @elseif($card->priority === 'high') bg-orange-100 text-orange-700 dark:bg-orange-900/50 dark:text-orange-300
                                    @elseif($card->priority === 'medium') bg-yellow-100 text-yellow-700 dark:bg-yellow-900/50 dark:text-yellow-300
                                    @else hidden @endif">
                                    <svg class="priority-icon w-2.5 h-2.5 {{ $card->priority !== 'urgent' ? 'hidden' : '' }}" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                    <span class="priority-text">{{ ['urgent' => 'Терміново', 'high' => 'Високий', 'medium' => 'Середній'][$card->priority] ?? '' }}</span>
                                </span>

                                @if($card->isOverdue())
                                    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[10px] font-medium bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300">
                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Прострочено
                                    </span>
                                @elseif($card->isDueSoon())
                                    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[10px] font-medium bg-orange-100 text-orange-700 dark:bg-orange-900/50 dark:text-orange-300">
                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Скоро
                                    </span>
                                @endif

                                @if($card->ministry)
                                    <span class="text-[10px] text-gray-500 dark:text-gray-400">{{ $card->ministry->name }}</span>
                                @endif
                            </div>

                            <!-- Title -->
                            <p class="text-sm font-medium text-gray-900 dark:text-white leading-snug {{ $card->is_completed ? 'line-through text-gray-400 dark:text-gray-500' : '' }}">
                                {{ $card->title }}
                            </p>

                            <!-- Description preview -->
                            @if($card->description)
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">{{ Str::limit($card->description, 60) }}</p>
                            @endif

                            <!-- Meta info -->
                            <div class="mt-2 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                                <div class="flex items-center gap-3">
                                    @if($card->due_date)
                                        <span class="inline-flex items-center gap-1 {{ $card->isOverdue() ? 'text-red-600 dark:text-red-400' : ($card->isDueSoon() ? 'text-orange-600 dark:text-orange-400' : '') }}">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            {{ $card->due_date->format('d.m') }}
                                        </span>
                                    @endif

                                    @if($card->checklistItems->count() > 0)
                                        @php
                                            $completed = $card->checklistItems->where('is_completed', true)->count();
                                            $total = $card->checklistItems->count();
                                        @endphp
                                        <span class="inline-flex items-center gap-1 {{ $completed === $total ? 'text-green-600 dark:text-green-400' : '' }}">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                            </svg>
                                            {{ $completed }}/{{ $total }}
                                        </span>
                                    @endif

                                    @if($card->comments->count() > 0)
                                        <span class="inline-flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                            </svg>
                                            {{ $card->comments->count() }}
                                        </span>
                                    @endif

                                    @if($card->attachments->count() > 0)
                                        <span class="inline-flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                            </svg>
                                            {{ $card->attachments->count() }}
                                        </span>
                                    @endif
                                </div>

                                @if($card->assignee)
                                    <div class="flex-shrink-0" title="{{ $card->assignee->full_name }}">
                                        @if($card->assignee->photo)
                                            <img src="{{ Storage::url($card->assignee->photo) }}"
                                                 class="w-5 h-5 rounded-full object-cover">
                                        @else
                                            <div class="w-5 h-5 rounded-full bg-gray-300 dark:bg-gray-500 flex items-center justify-center">
                                                <span class="text-gray-600 dark:text-gray-200 text-xs font-medium">
                                                    {{ mb_substr($card->assignee->first_name, 0, 1) }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <!-- Quick complete button -->
                            <button @click.stop="toggleComplete({{ $card->id }})"
                                    class="absolute top-2 right-2 p-1 rounded opacity-0 group-hover:opacity-100 transition-opacity
                                           {{ $card->is_completed ? 'bg-gray-200 text-gray-600 dark:bg-gray-600 dark:text-gray-300' : 'bg-gray-100 text-gray-400 dark:bg-gray-600 hover:text-gray-600 dark:hover:text-gray-300' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </button>
                        </div>
                    @endforeach
                </div>

                <!-- Quick Add Button -->
                <div class="p-2 border-t border-gray-200/50 dark:border-gray-700/50">
                    <button type="button" @click="openAddCardModal({{ $column->id }})"
                            class="w-full p-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 rounded-lg text-sm transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Додати</span>
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Card Slide-Over Panel (like Shortcut) -->
<div x-show="cardPanel.open" x-cloak class="fixed inset-0 z-50 overflow-hidden">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/40" @click="closePanel()" x-show="cardPanel.open"
         x-transition:enter="transition-opacity ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"></div>

    <!-- Panel -->
    <div class="absolute inset-y-0 right-0 flex max-w-full">
        <div x-show="cardPanel.open"
             x-transition:enter="transform transition ease-out duration-200"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transform transition ease-in duration-150"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full"
             class="w-screen max-w-3xl">
            <div class="h-full bg-white dark:bg-gray-900 shadow-xl flex flex-col">
                <template x-if="cardPanel.loading">
                    <div class="flex-1 flex items-center justify-center">
                        <div class="text-gray-500">Завантаження...</div>
                    </div>
                </template>

                <template x-if="!cardPanel.loading && cardPanel.data">
                    <div class="flex-1 flex flex-col overflow-hidden">
                        <!-- Header -->
                        <div class="flex-shrink-0 px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-white dark:from-gray-800 dark:to-gray-900">
                            <div class="flex items-start justify-between">
                                <div class="flex-1 pr-4">
                                    <!-- Status & Priority badges -->
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                              :class="{
                                                  'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300': cardPanel.data.column_name === 'Нові',
                                                  'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300': cardPanel.data.column_name === 'До виконання',
                                                  'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/50 dark:text-yellow-300': cardPanel.data.column_name === 'В процесі',
                                                  'bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-300': cardPanel.data.column_name === 'Завершено'
                                              }"
                                              x-text="cardPanel.data.column_name"></span>

                                        <template x-if="cardPanel.data.card.priority">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium"
                                                  :class="{
                                                      'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300': cardPanel.data.card.priority === 'urgent',
                                                      'bg-orange-100 text-orange-700 dark:bg-orange-900/50 dark:text-orange-300': cardPanel.data.card.priority === 'high',
                                                      'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/50 dark:text-yellow-300': cardPanel.data.card.priority === 'medium',
                                                      'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400': cardPanel.data.card.priority === 'low'
                                                  }">
                                                <template x-if="cardPanel.data.card.priority === 'urgent'">
                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                                </template>
                                                <span x-text="{'urgent': 'Терміново', 'high': 'Високий', 'medium': 'Середній', 'low': 'Низький'}[cardPanel.data.card.priority]"></span>
                                            </span>
                                        </template>

                                        <template x-if="cardPanel.data.card.is_completed">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-300">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                Завершено
                                            </span>
                                        </template>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-mono text-gray-400 dark:text-gray-500 cursor-pointer hover:text-primary-500 transition-colors"
                                              @click="navigator.clipboard.writeText('#' + cardPanel.data.card.id); $dispatch('notify', {message: 'ID скопійовано'})"
                                              title="Натисніть щоб скопіювати">
                                            #<span x-text="cardPanel.data.card.id"></span>
                                        </span>
                                        <input type="text" x-model="cardPanel.data.card.title"
                                               @blur="saveCardField('title', cardPanel.data.card.title)"
                                               class="flex-1 text-xl font-bold text-gray-900 dark:text-white bg-transparent border-0 p-0 focus:ring-0 focus:outline-none"
                                               :class="{ 'line-through opacity-60': cardPanel.data.card.is_completed }">
                                    </div>

                                    <!-- Quick stats bar -->
                                    <div class="flex items-center gap-4 mt-3 text-xs text-gray-500 dark:text-gray-400">
                                        <template x-if="cardPanel.data.card.due_date">
                                            <span class="inline-flex items-center gap-1"
                                                  :class="{
                                                      'text-red-600 dark:text-red-400 font-medium': new Date(cardPanel.data.card.due_date) < new Date() && !cardPanel.data.card.is_completed,
                                                      'text-orange-600 dark:text-orange-400': new Date(cardPanel.data.card.due_date) <= new Date(Date.now() + 2*24*60*60*1000) && new Date(cardPanel.data.card.due_date) >= new Date() && !cardPanel.data.card.is_completed
                                                  }">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                <span x-text="new Date(cardPanel.data.card.due_date).toLocaleDateString('uk-UA', {day: 'numeric', month: 'short'})"></span>
                                                <template x-if="new Date(cardPanel.data.card.due_date) < new Date() && !cardPanel.data.card.is_completed">
                                                    <span class="text-red-600 dark:text-red-400">(прострочено)</span>
                                                </template>
                                            </span>
                                        </template>

                                        <template x-if="cardPanel.data.checklist && cardPanel.data.checklist.length > 0">
                                            <span class="inline-flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                                <span x-text="`${cardPanel.data.checklist.filter(i => i.is_completed).length}/${cardPanel.data.checklist.length}`"></span>
                                            </span>
                                        </template>

                                        <template x-if="cardPanel.data.comments && cardPanel.data.comments.length > 0">
                                            <span class="inline-flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                                <span x-text="cardPanel.data.comments.length"></span>
                                            </span>
                                        </template>

                                        <template x-if="cardPanel.data.attachments && cardPanel.data.attachments.length > 0">
                                            <span class="inline-flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                                <span x-text="cardPanel.data.attachments.length"></span>
                                            </span>
                                        </template>

                                        <template x-if="cardPanel.data.related_cards && cardPanel.data.related_cards.length > 0">
                                            <span class="inline-flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                                <span x-text="cardPanel.data.related_cards.length"></span>
                                            </span>
                                        </template>
                                    </div>
                                </div>
                                <button @click="closePanel()" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Body with scroll - two column layout -->
                        <div class="flex-1 overflow-y-auto">
                            <div class="flex">
                                <!-- Main content column -->
                                <div class="flex-1 px-6 py-4 space-y-6 border-r border-gray-200 dark:border-gray-700">
                                    <!-- Description -->
                                    <div>
                                        <label class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                                            Опис
                                        </label>
                                        <textarea x-model="cardPanel.data.card.description"
                                                  @blur="saveCardField('description', cardPanel.data.card.description)"
                                                  rows="4" placeholder="Додайте детальний опис завдання..."
                                                  class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-sm dark:text-white resize-none"></textarea>
                                    </div>

                                    <!-- Checklist -->
                                    <div>
                                        <div class="flex items-center justify-between mb-3">
                                            <label class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                                Чеклист
                                            </label>
                                            <template x-if="cardPanel.data.checklist.length > 0">
                                                <span class="text-xs text-gray-400" x-text="`${cardPanel.data.checklist.filter(i => i.is_completed).length}/${cardPanel.data.checklist.length}`"></span>
                                            </template>
                                        </div>

                                        <template x-if="cardPanel.data.checklist.length > 0">
                                            <div class="w-full h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full mb-3">
                                                <div class="h-full bg-gray-600 dark:bg-gray-400 rounded-full transition-all"
                                                     :style="`width: ${(cardPanel.data.checklist.filter(i => i.is_completed).length / cardPanel.data.checklist.length) * 100}%`"></div>
                                            </div>
                                        </template>

                                        <div class="space-y-1">
                                            <template x-for="item in cardPanel.data.checklist" :key="item.id">
                                                <div class="flex items-center gap-2 group py-1">
                                                    <button @click="toggleChecklistItem(item)" class="w-4 h-4 rounded border flex items-center justify-center transition-colors"
                                                            :class="item.is_completed ? 'bg-gray-600 border-gray-600 text-white' : 'border-gray-300 dark:border-gray-600 hover:border-gray-400'">
                                                        <template x-if="item.is_completed">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                                            </svg>
                                                        </template>
                                                    </button>
                                                    <span class="flex-1 text-sm" :class="item.is_completed ? 'line-through text-gray-400' : 'text-gray-700 dark:text-gray-300'" x-text="item.title"></span>
                                                    <button @click="deleteChecklistItem(item)" class="p-1 text-gray-400 hover:text-gray-600 opacity-0 group-hover:opacity-100">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </template>
                                        </div>

                                        <div class="mt-2" x-data="{ adding: false, newItem: '' }">
                                            <template x-if="!adding">
                                                <button @click="adding = true" class="text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 flex items-center gap-1">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                    </svg>
                                                    Додати пункт
                                                </button>
                                            </template>
                                            <template x-if="adding">
                                                <div class="flex items-center gap-2">
                                                    <input type="text" x-model="newItem" @keydown.enter="addChecklistItem(newItem); newItem=''; adding=false"
                                                           @keydown.escape="adding=false" placeholder="Назва пункту..." autofocus
                                                           class="flex-1 px-2 py-1 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded text-sm dark:text-white">
                                                    <button @click="addChecklistItem(newItem); newItem=''; adding=false" class="px-2 py-1 bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900 text-sm rounded">OK</button>
                                                    <button @click="adding=false" class="p-1 text-gray-400 hover:text-gray-600">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Attachments -->
                                    <div>
                                        <label class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-3">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                            Файли
                                            <template x-if="cardPanel.data.attachments && cardPanel.data.attachments.length > 0">
                                                <span class="px-1.5 py-0.5 bg-gray-200 dark:bg-gray-700 rounded text-xs" x-text="cardPanel.data.attachments.length"></span>
                                            </template>
                                        </label>

                                        <!-- File list -->
                                        <div class="space-y-2 mb-3">
                                            <template x-for="file in cardPanel.data.attachments" :key="file.id">
                                                <div class="flex items-center gap-3 p-2 bg-gray-50 dark:bg-gray-800 rounded-lg group">
                                                    <template x-if="file.is_image">
                                                        <img :src="file.url" class="w-10 h-10 object-cover rounded">
                                                    </template>
                                                    <template x-if="!file.is_image">
                                                        <div class="w-10 h-10 bg-gray-200 dark:bg-gray-700 rounded flex items-center justify-center">
                                                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                            </svg>
                                                        </div>
                                                    </template>
                                                    <div class="flex-1 min-w-0">
                                                        <a :href="file.url" target="_blank" class="text-sm font-medium text-gray-900 dark:text-white hover:underline truncate block" x-text="file.name"></a>
                                                        <p class="text-xs text-gray-500" x-text="`${file.size} • ${file.created_at}`"></p>
                                                    </div>
                                                    <button @click="deleteAttachment(file)" class="p-1 text-gray-400 hover:text-gray-600 opacity-0 group-hover:opacity-100">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </template>
                                        </div>

                                        <!-- Upload button -->
                                        <label class="flex items-center gap-2 px-3 py-2 bg-gray-50 dark:bg-gray-800 border border-dashed border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:border-gray-400 transition-colors">
                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                            <span class="text-sm text-gray-500">Додати файл</span>
                                            <input type="file" class="hidden" @change="uploadAttachment($event)">
                                        </label>
                                    </div>

                                    <!-- Related Cards -->
                                    <div>
                                        <label class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-3">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                            Пов'язані завдання
                                            <template x-if="cardPanel.data.related_cards && cardPanel.data.related_cards.length > 0">
                                                <span class="px-1.5 py-0.5 bg-gray-200 dark:bg-gray-700 rounded text-xs" x-text="cardPanel.data.related_cards.length"></span>
                                            </template>
                                        </label>

                                        <div class="space-y-2 mb-3">
                                            <template x-for="related in cardPanel.data.related_cards" :key="related.id">
                                                <div class="flex items-center gap-2 p-2 bg-gray-50 dark:bg-gray-800 rounded-lg group">
                                                    <div class="w-4 h-4 rounded border flex items-center justify-center"
                                                         :class="related.is_completed ? 'bg-gray-600 border-gray-600 text-white' : 'border-gray-300'">
                                                        <template x-if="related.is_completed">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                                            </svg>
                                                        </template>
                                                    </div>
                                                    <span class="flex-1 text-sm text-gray-700 dark:text-gray-300 cursor-pointer hover:underline"
                                                          :class="related.is_completed ? 'line-through text-gray-400' : ''"
                                                          @click="closePanel(); openCard(related.id)" x-text="related.title"></span>
                                                    <span class="text-xs text-gray-400" x-text="related.column_name"></span>
                                                    <button @click="removeRelatedCard(related)" class="p-1 text-gray-400 hover:text-gray-600 opacity-0 group-hover:opacity-100">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </template>
                                        </div>

                                        <!-- Add related card -->
                                        <div x-data="{ adding: false, search: '', selectedCard: null }">
                                            <template x-if="!adding">
                                                <button @click="adding = true" class="text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 flex items-center gap-1">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                                    </svg>
                                                    Пов'язати завдання
                                                </button>
                                            </template>
                                            <template x-if="adding">
                                                <div class="space-y-2">
                                                    <select @change="if($event.target.value) { addRelatedCard($event.target.value); $event.target.value=''; adding=false; }"
                                                            class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-sm dark:text-white">
                                                        <option value="">Оберіть завдання...</option>
                                                        <template x-for="card in cardPanel.data.available_cards" :key="card.id">
                                                            <option :value="card.id" x-text="`${card.title} (${card.column_name})`"></option>
                                                        </template>
                                                    </select>
                                                    <button @click="adding=false" class="text-xs text-gray-500 hover:text-gray-700">Скасувати</button>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Comments -->
                                    <div>
                                        <label class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-3">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                            Коментарі
                                            <template x-if="cardPanel.data.comments && cardPanel.data.comments.length > 0">
                                                <span class="px-1.5 py-0.5 bg-gray-200 dark:bg-gray-700 rounded text-xs" x-text="cardPanel.data.comments.length"></span>
                                            </template>
                                        </label>

                                        <!-- Add comment with @mention hint -->
                                        <div class="mb-4" x-data="{ newComment: '', showMentions: false, commentFiles: [], fileNames: [] }">
                                            <div class="relative">
                                                <textarea x-model="newComment" rows="2" placeholder="Написати коментар... (@ для згадки)"
                                                          @keydown.cmd.enter="addCommentWithFiles(newComment, commentFiles); newComment=''; commentFiles=[]; fileNames=[]"
                                                          @keydown.ctrl.enter="addCommentWithFiles(newComment, commentFiles); newComment=''; commentFiles=[]; fileNames=[]"
                                                          @input="showMentions = newComment.includes('@') && newComment.slice(-1) === '@'"
                                                          class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-sm dark:text-white resize-none"></textarea>

                                                <!-- Mentions dropdown -->
                                                <div x-show="showMentions" x-cloak class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg max-h-40 overflow-y-auto">
                                                    <template x-for="person in cardPanel.data.people" :key="person.id">
                                                        <button @click="newComment = newComment.slice(0, -1) + person.name + ' '; showMentions = false"
                                                                class="w-full px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2">
                                                            <div class="w-6 h-6 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center">
                                                                <span class="text-xs" x-text="person.initial"></span>
                                                            </div>
                                                            <span x-text="person.name"></span>
                                                        </button>
                                                    </template>
                                                </div>
                                            </div>

                                            <!-- Selected files preview -->
                                            <template x-if="fileNames.length > 0">
                                                <div class="flex flex-wrap gap-1 mt-2">
                                                    <template x-for="(name, idx) in fileNames" :key="idx">
                                                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs text-gray-600 dark:text-gray-300">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                                            </svg>
                                                            <span x-text="name"></span>
                                                            <button @click="commentFiles.splice(idx, 1); fileNames.splice(idx, 1)" class="text-gray-400 hover:text-gray-600">&times;</button>
                                                        </span>
                                                    </template>
                                                </div>
                                            </template>

                                            <div class="flex items-center gap-2 mt-2">
                                                <button @click="addCommentWithFiles(newComment, commentFiles); newComment=''; commentFiles=[]; fileNames=[]" :disabled="!newComment.trim()"
                                                        class="px-3 py-1.5 bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900 text-sm rounded-lg disabled:opacity-50">
                                                    Коментувати
                                                </button>
                                                <label class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 cursor-pointer rounded hover:bg-gray-100 dark:hover:bg-gray-700">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                                    </svg>
                                                    <input type="file" multiple class="hidden" @change="
                                                        for (let f of $event.target.files) {
                                                            commentFiles.push(f);
                                                            fileNames.push(f.name);
                                                        }
                                                        $event.target.value = '';
                                                    ">
                                                </label>
                                            </div>
                                        </div>

                                        <!-- Comments list -->
                                        <div class="space-y-4">
                                            <template x-for="comment in cardPanel.data.comments" :key="comment.id">
                                                <div class="flex gap-3 group" x-data="{ editing: false, editContent: comment.content }">
                                                    <div class="w-7 h-7 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center flex-shrink-0">
                                                        <span class="text-gray-600 dark:text-gray-300 text-xs font-medium" x-text="comment.user_initial"></span>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex items-center gap-2">
                                                            <span class="font-medium text-gray-900 dark:text-white text-sm" x-text="comment.user_name"></span>
                                                            <span class="text-xs text-gray-500" x-text="comment.created_at"></span>
                                                            <template x-if="comment.is_edited">
                                                                <span class="text-xs text-gray-400">(ред.)</span>
                                                            </template>
                                                        </div>
                                                        <!-- View mode -->
                                                        <template x-if="!editing">
                                                            <div>
                                                                <p class="text-gray-600 dark:text-gray-300 text-sm mt-0.5 whitespace-pre-wrap" x-text="comment.content"></p>
                                                                <!-- Comment attachments -->
                                                                <template x-if="comment.attachments && comment.attachments.length > 0">
                                                                    <div class="flex flex-wrap gap-2 mt-2">
                                                                        <template x-for="att in comment.attachments" :key="att.name">
                                                                            <a :href="att.url" target="_blank" class="inline-flex items-center gap-1 px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600">
                                                                                <template x-if="att.is_image">
                                                                                    <img :src="att.url" class="w-8 h-8 object-cover rounded">
                                                                                </template>
                                                                                <template x-if="!att.is_image">
                                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                                                                    </svg>
                                                                                </template>
                                                                                <span x-text="att.name.length > 15 ? att.name.substring(0, 15) + '...' : att.name"></span>
                                                                            </a>
                                                                        </template>
                                                                    </div>
                                                                </template>
                                                            </div>
                                                        </template>
                                                        <!-- Edit mode -->
                                                        <template x-if="editing">
                                                            <div class="mt-1">
                                                                <textarea x-model="editContent" rows="2"
                                                                          class="w-full px-2 py-1 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded text-sm dark:text-white resize-none"></textarea>
                                                                <div class="flex gap-2 mt-1">
                                                                    <button @click="updateComment(comment, editContent); editing=false" class="px-2 py-1 bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900 text-xs rounded">Зберегти</button>
                                                                    <button @click="editing=false; editContent=comment.content" class="px-2 py-1 text-gray-500 text-xs">Скасувати</button>
                                                                </div>
                                                            </div>
                                                        </template>
                                                    </div>
                                                    <template x-if="comment.is_mine && !editing">
                                                        <div class="flex gap-1 opacity-0 group-hover:opacity-100">
                                                            <button @click="editing=true" class="p-1 text-gray-400 hover:text-gray-600">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                                </svg>
                                                            </button>
                                                            <button @click="deleteComment(comment)" class="p-1 text-gray-400 hover:text-gray-600">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                            <template x-if="cardPanel.data.comments.length === 0">
                                                <p class="text-center text-gray-400 text-sm py-4">Немає коментарів</p>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Activity Log -->
                                    <div x-data="{ showActivity: false }" class="border-t border-gray-200 dark:border-gray-700 pt-4">
                                        <button @click="showActivity = !showActivity" class="flex items-center justify-between w-full text-left group">
                                            <label class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 cursor-pointer group-hover:text-gray-700 dark:group-hover:text-gray-300">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                Історія змін
                                                <template x-if="cardPanel.data.activities && cardPanel.data.activities.length > 0">
                                                    <span class="px-1.5 py-0.5 bg-gray-200 dark:bg-gray-700 rounded text-xs" x-text="cardPanel.data.activities.length"></span>
                                                </template>
                                            </label>
                                            <svg class="w-4 h-4 text-gray-400 transition-transform" :class="{ 'rotate-180': showActivity }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </button>

                                        <div x-show="showActivity" x-collapse class="mt-3">
                                            <div class="space-y-3 max-h-64 overflow-y-auto">
                                                <template x-for="activity in cardPanel.data.activities" :key="activity.id">
                                                    <div class="flex gap-3 text-sm">
                                                        <div class="w-6 h-6 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center flex-shrink-0">
                                                            <span class="text-gray-500 dark:text-gray-400 text-xs" x-text="activity.user_initial"></span>
                                                        </div>
                                                        <div class="flex-1 min-w-0">
                                                            <p class="text-gray-600 dark:text-gray-300 text-xs" x-text="activity.description"></p>
                                                            <p class="text-gray-400 text-xs mt-0.5" x-text="activity.created_at" :title="activity.created_at_full"></p>

                                                            <!-- Comment diff display -->
                                                            <template x-if="activity.action === 'comment_edited' && activity.old_value && activity.new_value">
                                                                <div class="mt-2 text-xs border-l-2 border-gray-200 dark:border-gray-600 pl-2 space-y-1">
                                                                    <div class="bg-red-50 dark:bg-red-900/20 px-2 py-1 rounded text-red-700 dark:text-red-300 line-through">
                                                                        <span x-text="activity.old_value.length > 100 ? activity.old_value.substring(0, 100) + '...' : activity.old_value"></span>
                                                                    </div>
                                                                    <div class="bg-green-50 dark:bg-green-900/20 px-2 py-1 rounded text-green-700 dark:text-green-300">
                                                                        <span x-text="activity.new_value.length > 100 ? activity.new_value.substring(0, 100) + '...' : activity.new_value"></span>
                                                                    </div>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </template>
                                                <template x-if="!cardPanel.data.activities || cardPanel.data.activities.length === 0">
                                                    <p class="text-center text-gray-400 text-xs py-2">Немає історії</p>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Sidebar -->
                                <div class="w-72 flex-shrink-0 px-4 py-4 space-y-4 bg-gray-50 dark:bg-gray-800/50">
                                    <div class="space-y-3">
                                        <div>
                                            <label class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mb-1.5">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                                Статус
                                            </label>
                                            <select x-model="cardPanel.data.card.column_id" @change="saveCardField('column_id', cardPanel.data.card.column_id)"
                                                    class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-sm dark:text-white">
                                                <template x-for="col in cardPanel.data.columns" :key="col.id">
                                                    <option :value="col.id" x-text="col.name"></option>
                                                </template>
                                            </select>
                                        </div>

                                        <div x-data="{
                                            open: false,
                                            search: '',
                                            get filtered() {
                                                if (!this.search) return cardPanel.data.people || [];
                                                return (cardPanel.data.people || []).filter(p => p.name.toLowerCase().includes(this.search.toLowerCase()));
                                            },
                                            get selectedPerson() {
                                                return (cardPanel.data.people || []).find(p => p.id == cardPanel.data.card.assigned_to);
                                            },
                                            select(id) {
                                                cardPanel.data.card.assigned_to = id;
                                                saveCardField('assigned_to', id);
                                                this.open = false;
                                                this.search = '';
                                            }
                                        }">
                                            <label class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mb-1.5">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                Відповідальний
                                            </label>
                                            <div class="relative">
                                                <button type="button" @click="open = !open; $nextTick(() => open && $refs.panelSearchInput.focus())"
                                                        class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-sm text-left flex items-center justify-between dark:text-white">
                                                    <span x-show="!selectedPerson" class="text-gray-500">Не призначено</span>
                                                    <template x-if="selectedPerson">
                                                        <span class="flex items-center gap-2">
                                                            <span class="w-5 h-5 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-xs text-primary-600 dark:text-primary-400" x-text="selectedPerson.name.charAt(0)"></span>
                                                            <span x-text="selectedPerson.name" class="truncate"></span>
                                                        </span>
                                                    </template>
                                                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                    </svg>
                                                </button>

                                                <div x-show="open" @click.away="open = false" x-transition
                                                     class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg overflow-hidden">
                                                    <div class="p-2 border-b border-gray-200 dark:border-gray-700">
                                                        <input type="text" x-model="search" x-ref="panelSearchInput"
                                                               placeholder="Пошук..."
                                                               class="w-full px-2 py-1.5 bg-gray-50 dark:bg-gray-700 border-0 rounded text-sm dark:text-white focus:ring-1 focus:ring-primary-500">
                                                    </div>
                                                    <div class="max-h-40 overflow-y-auto">
                                                        <button type="button" @click="select('')"
                                                                class="w-full px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2"
                                                                :class="!cardPanel.data.card.assigned_to ? 'bg-primary-50 dark:bg-primary-900/20' : ''">
                                                            <span class="w-5 h-5 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center text-xs">—</span>
                                                            <span class="text-gray-500 dark:text-gray-400">Не призначено</span>
                                                        </button>
                                                        <template x-for="person in filtered" :key="person.id">
                                                            <button type="button" @click="select(person.id)"
                                                                    class="w-full px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-white flex items-center gap-2"
                                                                    :class="cardPanel.data.card.assigned_to == person.id ? 'bg-primary-50 dark:bg-primary-900/20' : ''">
                                                                <span class="w-5 h-5 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-xs text-primary-600 dark:text-primary-400" x-text="person.name.charAt(0)"></span>
                                                                <span x-text="person.name" class="truncate"></span>
                                                            </button>
                                                        </template>
                                                        <div x-show="filtered.length === 0" class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400">
                                                            Нікого не знайдено
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mb-1.5">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>
                                                Пріоритет
                                            </label>
                                            <div class="flex gap-1">
                                                <button @click="cardPanel.data.card.priority = 'low'; saveCardField('priority', 'low')"
                                                        class="flex-1 px-2 py-1.5 text-xs rounded-lg transition-colors"
                                                        :class="cardPanel.data.card.priority === 'low' ? 'bg-gray-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-600'">
                                                    Низький
                                                </button>
                                                <button @click="cardPanel.data.card.priority = 'medium'; saveCardField('priority', 'medium')"
                                                        class="flex-1 px-2 py-1.5 text-xs rounded-lg transition-colors"
                                                        :class="cardPanel.data.card.priority === 'medium' ? 'bg-yellow-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-600'">
                                                    Середній
                                                </button>
                                                <button @click="cardPanel.data.card.priority = 'high'; saveCardField('priority', 'high')"
                                                        class="flex-1 px-2 py-1.5 text-xs rounded-lg transition-colors"
                                                        :class="cardPanel.data.card.priority === 'high' ? 'bg-orange-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-600'">
                                                    Високий
                                                </button>
                                                <button @click="cardPanel.data.card.priority = 'urgent'; saveCardField('priority', 'urgent')"
                                                        class="flex-1 px-2 py-1.5 text-xs rounded-lg transition-colors"
                                                        :class="cardPanel.data.card.priority === 'urgent' ? 'bg-red-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-600'">
                                                    🔥
                                                </button>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mb-1.5">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                Дедлайн
                                            </label>
                                            <div class="relative">
                                                <input type="date" x-model="cardPanel.data.card.due_date" @change="saveCardField('due_date', cardPanel.data.card.due_date)"
                                                       class="w-full px-3 py-2 bg-white dark:bg-gray-800 border rounded-lg text-sm dark:text-white"
                                                       :class="{
                                                           'border-red-300 dark:border-red-600': cardPanel.data.card.due_date && new Date(cardPanel.data.card.due_date) < new Date() && !cardPanel.data.card.is_completed,
                                                           'border-orange-300 dark:border-orange-600': cardPanel.data.card.due_date && new Date(cardPanel.data.card.due_date) <= new Date(Date.now() + 2*24*60*60*1000) && new Date(cardPanel.data.card.due_date) >= new Date() && !cardPanel.data.card.is_completed,
                                                           'border-gray-200 dark:border-gray-700': !cardPanel.data.card.due_date || cardPanel.data.card.is_completed
                                                       }">
                                                <template x-if="cardPanel.data.card.due_date && new Date(cardPanel.data.card.due_date) < new Date() && !cardPanel.data.card.is_completed">
                                                    <span class="absolute right-2 top-1/2 -translate-y-1/2 text-xs text-red-500 font-medium">Прострочено!</span>
                                                </template>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Meta info -->
                                    <div class="pt-4 border-t border-gray-200 dark:border-gray-700 space-y-2">
                                        <template x-if="cardPanel.data.card.creator">
                                            <div class="flex items-center gap-2 text-xs text-gray-500">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                <span>Створив: <span class="text-gray-700 dark:text-gray-300" x-text="cardPanel.data.card.creator.name"></span></span>
                                            </div>
                                        </template>
                                        <div class="flex items-center gap-2 text-xs text-gray-500">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span>Створено: <span class="text-gray-700 dark:text-gray-300" x-text="new Date(cardPanel.data.card.created_at).toLocaleDateString('uk-UA')"></span></span>
                                        </div>
                                        <template x-if="cardPanel.data.card.is_completed && cardPanel.data.card.completed_at">
                                            <div class="flex items-center gap-2 text-xs text-green-600 dark:text-green-400">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                <span>Завершено: <span x-text="new Date(cardPanel.data.card.completed_at).toLocaleDateString('uk-UA')"></span></span>
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Actions -->
                                    <div class="pt-4 border-t border-gray-200 dark:border-gray-700 space-y-1">
                                        <button @click="duplicateCard()" class="w-full px-3 py-2 text-sm text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                            </svg>
                                            Дублювати
                                        </button>
                                        <button @click="deleteCard(cardPanel.data.card.id)" class="w-full px-3 py-2 text-sm text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Видалити
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<!-- Add Card Slide-Over Panel -->
<div x-show="addCardModal.open" x-cloak class="fixed inset-0 z-50 overflow-hidden">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/40" @click="addCardModal.open = false" x-show="addCardModal.open"
         x-transition:enter="transition-opacity ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"></div>

    <!-- Panel -->
    <div class="absolute inset-y-0 right-0 flex max-w-full">
        <div x-show="addCardModal.open"
             x-transition:enter="transform transition ease-out duration-200"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transform transition ease-in duration-150"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full"
             class="w-screen max-w-xl">
            <div class="h-full bg-white dark:bg-gray-900 shadow-xl flex flex-col">
                <!-- Header -->
                <div class="flex-shrink-0 px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-white dark:from-gray-800 dark:to-gray-900">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-primary-100 dark:bg-primary-900/50 flex items-center justify-center">
                                <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Нове завдання</h2>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Створіть нову картку</p>
                            </div>
                        </div>
                        <button @click="addCardModal.open = false"
                                class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Form Content -->
                <form @submit.prevent="submitNewCard()" class="flex-1 overflow-y-auto">
                    <div class="p-6 space-y-6">
                        <!-- Title -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Назва *</label>
                            <input type="text" x-model="addCardModal.title" x-ref="addCardTitle" required
                                   placeholder="Що потрібно зробити?"
                                   class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:text-white text-lg">
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Опис</label>
                            <textarea x-model="addCardModal.description" rows="4"
                                      placeholder="Детальний опис завдання..."
                                      class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:text-white resize-none"></textarea>
                        </div>

                        <!-- Column -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Колонка</label>
                            <select x-model="addCardModal.columnId"
                                    class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:text-white">
                                @foreach($board->columns as $column)
                                    <option value="{{ $column->id }}">{{ $column->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Priority -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Пріоритет</label>
                            <div class="grid grid-cols-4 gap-2">
                                <label class="relative">
                                    <input type="radio" x-model="addCardModal.priority" value="low" class="peer sr-only">
                                    <div class="p-3 text-center rounded-xl border-2 cursor-pointer transition-all
                                                peer-checked:border-gray-500 peer-checked:bg-gray-50 dark:peer-checked:bg-gray-800
                                                border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600">
                                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Низький</span>
                                    </div>
                                </label>
                                <label class="relative">
                                    <input type="radio" x-model="addCardModal.priority" value="medium" class="peer sr-only">
                                    <div class="p-3 text-center rounded-xl border-2 cursor-pointer transition-all
                                                peer-checked:border-yellow-500 peer-checked:bg-yellow-50 dark:peer-checked:bg-yellow-900/20
                                                border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600">
                                        <span class="text-sm font-medium text-yellow-600 dark:text-yellow-400">Середній</span>
                                    </div>
                                </label>
                                <label class="relative">
                                    <input type="radio" x-model="addCardModal.priority" value="high" class="peer sr-only">
                                    <div class="p-3 text-center rounded-xl border-2 cursor-pointer transition-all
                                                peer-checked:border-orange-500 peer-checked:bg-orange-50 dark:peer-checked:bg-orange-900/20
                                                border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600">
                                        <span class="text-sm font-medium text-orange-600 dark:text-orange-400">Високий</span>
                                    </div>
                                </label>
                                <label class="relative">
                                    <input type="radio" x-model="addCardModal.priority" value="urgent" class="peer sr-only">
                                    <div class="p-3 text-center rounded-xl border-2 cursor-pointer transition-all
                                                peer-checked:border-red-500 peer-checked:bg-red-50 dark:peer-checked:bg-red-900/20
                                                border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600">
                                        <span class="text-sm font-medium text-red-600 dark:text-red-400">Терміново</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Ministry -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Команда</label>
                            <select x-model="addCardModal.ministryId"
                                    class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:text-white">
                                <option value="">Без команди</option>
                                @foreach($ministries as $ministry)
                                    <option value="{{ $ministry->id }}">{{ $ministry->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Assignee -->
                        <div x-data="{
                            open: false,
                            search: '',
                            people: @js($people->map(fn($p) => ['id' => $p->id, 'name' => $p->full_name, 'photo' => $p->photo ? Storage::url($p->photo) : null])),
                            get filtered() {
                                if (!this.search) return this.people;
                                return this.people.filter(p => p.name.toLowerCase().includes(this.search.toLowerCase()));
                            },
                            get selectedPerson() {
                                return this.people.find(p => p.id == addCardModal.assignedTo);
                            },
                            select(id) {
                                addCardModal.assignedTo = id;
                                this.open = false;
                                this.search = '';
                            }
                        }">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Виконавець</label>
                            <div class="relative">
                                <button type="button" @click="open = !open; $nextTick(() => open && $refs.searchInput.focus())"
                                        class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-left flex items-center justify-between dark:text-white">
                                    <span x-show="!selectedPerson" class="text-gray-500">Оберіть виконавця...</span>
                                    <template x-if="selectedPerson">
                                        <span class="flex items-center gap-2">
                                            <template x-if="selectedPerson.photo">
                                                <img :src="selectedPerson.photo" class="w-6 h-6 rounded-full object-cover">
                                            </template>
                                            <template x-if="!selectedPerson.photo">
                                                <span class="w-6 h-6 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-xs text-primary-600 dark:text-primary-400" x-text="selectedPerson.name.charAt(0)"></span>
                                            </template>
                                            <span x-text="selectedPerson.name"></span>
                                        </span>
                                    </template>
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>

                                <div x-show="open" @click.away="open = false" x-transition
                                     class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg overflow-hidden">
                                    <div class="p-2 border-b border-gray-200 dark:border-gray-700">
                                        <input type="text" x-model="search" x-ref="searchInput"
                                               placeholder="Пошук..."
                                               class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border-0 rounded-lg text-sm dark:text-white focus:ring-2 focus:ring-primary-500">
                                    </div>
                                    <div class="max-h-48 overflow-y-auto">
                                        <button type="button" @click="select('')"
                                                class="w-full px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2"
                                                :class="!addCardModal.assignedTo ? 'bg-primary-50 dark:bg-primary-900/20' : ''">
                                            <span class="w-6 h-6 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center text-xs">—</span>
                                            <span class="text-gray-500 dark:text-gray-400">Без виконавця</span>
                                        </button>
                                        <template x-for="person in filtered" :key="person.id">
                                            <button type="button" @click="select(person.id)"
                                                    class="w-full px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-white flex items-center gap-2"
                                                    :class="addCardModal.assignedTo == person.id ? 'bg-primary-50 dark:bg-primary-900/20' : ''">
                                                <template x-if="person.photo">
                                                    <img :src="person.photo" class="w-6 h-6 rounded-full object-cover">
                                                </template>
                                                <template x-if="!person.photo">
                                                    <span class="w-6 h-6 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-xs text-primary-600 dark:text-primary-400" x-text="person.name.charAt(0)"></span>
                                                </template>
                                                <span x-text="person.name"></span>
                                            </button>
                                        </template>
                                        <div x-show="filtered.length === 0" class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400">
                                            Нікого не знайдено
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Due Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Дедлайн</label>
                            <input type="date" x-model="addCardModal.dueDate"
                                   class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:text-white">
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="flex-shrink-0 px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                        <div class="flex items-center justify-end gap-3">
                            <button type="button" @click="addCardModal.open = false"
                                    class="px-5 py-2.5 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white font-medium rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                Скасувати
                            </button>
                            <button type="submit" :disabled="addCardModal.loading || !addCardModal.title.trim()"
                                    class="px-6 py-2.5 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700 transition-colors disabled:opacity-50 flex items-center gap-2">
                                <template x-if="addCardModal.loading">
                                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </template>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Створити завдання
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Keyboard Shortcuts Modal -->
<div x-show="showShortcuts" x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     @keydown.escape.window="showShortcuts = false">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="showShortcuts = false"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-lg z-10 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Клавіатурні скорочення</h3>
                <button @click="showShortcuts = false" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="space-y-3 text-sm">
                <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <span class="text-gray-700 dark:text-gray-300">Пошук</span>
                    <kbd class="px-2 py-1 bg-gray-200 dark:bg-gray-600 rounded text-xs font-mono">/</kbd>
                </div>
                <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <span class="text-gray-700 dark:text-gray-300">Нова картка</span>
                    <kbd class="px-2 py-1 bg-gray-200 dark:bg-gray-600 rounded text-xs font-mono">N</kbd>
                </div>
                <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <span class="text-gray-700 dark:text-gray-300">Ця довідка</span>
                    <kbd class="px-2 py-1 bg-gray-200 dark:bg-gray-600 rounded text-xs font-mono">?</kbd>
                </div>
                <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <span class="text-gray-700 dark:text-gray-300">Закрити</span>
                    <kbd class="px-2 py-1 bg-gray-200 dark:bg-gray-600 rounded text-xs font-mono">Esc</kbd>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

@php
$allCardsData = [];
foreach ($board->columns as $col) {
    foreach ($col->cards as $card) {
        $allCardsData[] = [
            'id' => $card->id,
            'title' => $card->title,
            'priority' => $card->priority,
            'columnName' => $col->name,
            'columnId' => $col->id
        ];
    }
}
@endphp

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
function churchBoard() {
    return {
        showAddCard: null,
        filters: {
            priority: '',
            assignee: '',
            ministry: ''
        },
        searchQuery: '',
        cardPanel: {
            open: false,
            loading: false,
            data: null,
            cardId: null
        },
        addCardModal: {
            open: false,
            loading: false,
            columnId: {{ $board->columns->first()?->id ?? 'null' }},
            title: '',
            description: '',
            priority: 'medium',
            ministryId: '',
            assignedTo: '',
            dueDate: ''
        },
        cards: @json($board->columns->flatMap->cards->keyBy('id')),
        allCards: @json($allCardsData),
        showShortcuts: false,
        myPersonId: {{ auth()->user()->person?->id ?? 'null' }},
        csrfToken: document.querySelector('meta[name="csrf-token"]').content,

        get hasActiveFilters() {
            return this.filters.priority || this.filters.assignee || this.filters.ministry || this.searchQuery;
        },

        get filteredCards() {
            if (!this.searchQuery) return [];
            const q = this.searchQuery.toLowerCase().replace('#', '');
            return this.allCards.filter(card =>
                card.id.toString().includes(q) ||
                card.title.toLowerCase().includes(q)
            );
        },

        init() {
            this.initSortable();
            this.initKeyboardShortcuts();
            this.$watch('filters', () => this.applyFilters(), { deep: true });
            this.$watch('searchQuery', () => this.applyFilters());

            // Open card from URL parameter
            const urlParams = new URLSearchParams(window.location.search);
            const cardId = urlParams.get('card');
            if (cardId) {
                this.openCard(parseInt(cardId));
                // Clean URL
                window.history.replaceState({}, '', window.location.pathname);
            }
        },

        initKeyboardShortcuts() {
            document.addEventListener('keydown', (e) => {
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.isContentEditable) {
                    if (e.key === 'Escape') {
                        e.target.blur();
                        this.showAddCard = null;
                    }
                    return;
                }

                if (e.key === '?') {
                    e.preventDefault();
                    this.showShortcuts = !this.showShortcuts;
                    return;
                }

                if (e.key === 'Escape') {
                    this.closePanel();
                    this.showShortcuts = false;
                    this.showAddCard = null;
                    return;
                }

                if (e.key === 'n' && !e.metaKey && !e.ctrlKey) {
                    e.preventDefault();
                    this.openAddCardModal();
                    return;
                }
            });
        },

        initSortable() {
            document.querySelectorAll('.kanban-cards').forEach(container => {
                new Sortable(container, {
                    group: 'cards',
                    animation: 200,
                    ghostClass: 'opacity-40',
                    chosenClass: 'shadow-lg',
                    dragClass: 'rotate-2',
                    handle: '.kanban-card',
                    draggable: '.kanban-card',
                    onEnd: (evt) => {
                        const cardId = evt.item.dataset.cardId;
                        const newColumnId = evt.to.dataset.columnId;
                        const newIndex = evt.newIndex;

                        fetch(`/boards/cards/${cardId}/move`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                            },
                            body: JSON.stringify({
                                column_id: newColumnId,
                                position: newIndex
                            })
                        });
                    }
                });
            });
        },

        applyFilters() {
            const query = this.searchQuery.toLowerCase();
            let counts = {};

            document.querySelectorAll('.kanban-card').forEach(card => {
                let show = true;

                if (query) {
                    const title = card.dataset.title || '';
                    if (!title.includes(query)) show = false;
                }

                if (show && this.filters.priority && card.dataset.priority !== this.filters.priority) {
                    show = false;
                }

                if (show && this.filters.ministry && card.dataset.ministry !== this.filters.ministry) {
                    show = false;
                }

                if (show && this.filters.assignee) {
                    if (this.filters.assignee === 'me') {
                        if (card.dataset.assignee !== String(this.myPersonId)) show = false;
                    } else if (this.filters.assignee === 'unassigned') {
                        if (card.dataset.assignee !== 'unassigned') show = false;
                    } else if (card.dataset.assignee !== this.filters.assignee) {
                        show = false;
                    }
                }

                card.style.display = show ? '' : 'none';

                const columnId = card.closest('.kanban-cards').dataset.columnId;
                if (!counts[columnId]) counts[columnId] = 0;
                if (show) counts[columnId]++;
            });

            document.querySelectorAll('.column-count').forEach(el => {
                const columnId = el.dataset.columnId;
                if (counts[columnId] !== undefined) {
                    el.textContent = counts[columnId];
                }
            });
        },

        clearFilters() {
            this.filters = { priority: '', assignee: '', ministry: '' };
            this.searchQuery = '';
        },

        // Add Card Modal Methods
        openAddCardModal(columnId = null) {
            this.addCardModal = {
                open: true,
                loading: false,
                columnId: columnId || {{ $board->columns->first()?->id ?? 'null' }},
                title: '',
                description: '',
                priority: 'medium',
                ministryId: '',
                assignedTo: '',
                dueDate: ''
            };
            this.$nextTick(() => {
                this.$refs.addCardTitle?.focus();
            });
        },

        async submitNewCard() {
            if (!this.addCardModal.title.trim()) return;

            this.addCardModal.loading = true;

            try {
                const response = await fetch(`/boards/columns/${this.addCardModal.columnId}/cards`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        title: this.addCardModal.title,
                        description: this.addCardModal.description,
                        priority: this.addCardModal.priority,
                        ministry_id: this.addCardModal.ministryId || null,
                        assigned_to: this.addCardModal.assignedTo || null,
                        due_date: this.addCardModal.dueDate || null
                    })
                });

                if (response.ok) {
                    const data = await response.json();
                    if (data.success && data.card) {
                        this.insertNewCard(data.card, this.addCardModal.columnId);
                        this.addCardModal.open = false;
                        if (window.showGlobalToast) showGlobalToast('Завдання створено', 'success');
                    }
                } else {
                    const data = await response.json();
                    alert(data.message || 'Помилка при створенні завдання');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Помилка при створенні завдання');
            } finally {
                this.addCardModal.loading = false;
            }
        },

        insertNewCard(card, columnId) {
            const column = document.querySelector(`.kanban-cards[data-column-id="${columnId}"]`);
            if (!column) return;

            const priorityBorder = {
                'urgent': 'border-l-4 border-l-red-500',
                'high': 'border-l-4 border-l-orange-500',
                'medium': 'border-l-4 border-l-yellow-500'
            }[card.priority] || '';

            const priorityBadge = {
                'urgent': '<span class="priority-badge inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[10px] font-medium bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300">Терміново</span>',
                'high': '<span class="priority-badge inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[10px] font-medium bg-orange-100 text-orange-700 dark:bg-orange-900/50 dark:text-orange-300">Високий</span>',
                'medium': '<span class="priority-badge inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[10px] font-medium bg-yellow-100 text-yellow-700 dark:bg-yellow-900/50 dark:text-yellow-300">Середній</span>'
            }[card.priority] || '';

            const cardHtml = `
                <div class="kanban-card bg-white dark:bg-gray-700 p-3 rounded-xl shadow-sm border border-gray-100 dark:border-gray-600 cursor-pointer hover:shadow-md transition-all ${priorityBorder}"
                     draggable="true"
                     data-card-id="${card.id}"
                     data-priority="${card.priority || 'low'}"
                     data-assignee="${card.assigned_to || 'unassigned'}"
                     data-ministry="${card.ministry_id || ''}"
                     data-due="${card.due_date || ''}"
                     data-title="${(card.title || '').toLowerCase()}"
                     onclick="document.querySelector('[x-data]').__x.$data.openCard(${card.id})">
                    <div class="flex items-center gap-1.5 mb-1.5 flex-wrap">
                        <span class="text-[10px] font-mono text-gray-400 dark:text-gray-500">#${card.id}</span>
                        ${priorityBadge}
                    </div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white mb-2">${card.title}</p>
                </div>
            `;

            column.insertAdjacentHTML('afterbegin', cardHtml);
            this.updateColumnCount(columnId);
        },

        // Card Panel Methods
        async openCard(cardId) {
            this.cardPanel.open = true;
            this.cardPanel.loading = true;
            this.cardPanel.cardId = cardId;

            try {
                const response = await fetch(`/boards/cards/${cardId}`, {
                    headers: { 'Accept': 'application/json' }
                });
                this.cardPanel.data = await response.json();
            } catch (e) {
                console.error('Error loading card:', e);
            } finally {
                this.cardPanel.loading = false;
            }
        },

        closePanel() {
            this.cardPanel.open = false;
            this.cardPanel.data = null;
            this.cardPanel.cardId = null;
        },

        async saveCardField(field, value) {
            if (!this.cardPanel.data) return;

            const cardId = this.cardPanel.data.card.id;
            const data = { ...this.cardPanel.data.card, [field]: value };

            await fetch(`/boards/cards/${cardId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });

            // Update the card in the kanban view
            const cardEl = document.querySelector(`[data-card-id="${cardId}"]`);
            if (cardEl) {
                // Update title
                if (field === 'title') {
                    const titleEl = cardEl.querySelector('p.text-sm.font-medium');
                    if (titleEl) titleEl.textContent = value;
                    cardEl.dataset.title = value.toLowerCase();
                }

                // Update column - move card to new column
                if (field === 'column_id') {
                    const newColumn = document.querySelector(`.kanban-cards[data-column-id="${value}"]`);
                    if (newColumn && cardEl.parentElement.dataset.columnId !== value.toString()) {
                        const oldColumn = cardEl.parentElement;
                        newColumn.insertBefore(cardEl, newColumn.firstChild);
                        this.updateColumnCount(oldColumn.dataset.columnId);
                        this.updateColumnCount(value);
                    }
                    const colOption = document.querySelector(`select option[value="${value}"]`);
                    if (colOption) {
                        this.cardPanel.data.column_name = colOption.textContent.trim();
                    }
                }

                // Update priority
                if (field === 'priority') {
                    cardEl.dataset.priority = value;
                    // Update border
                    cardEl.classList.remove('border-l-4', 'border-l-red-500', 'border-l-orange-500', 'border-l-yellow-500');
                    if (value === 'urgent') cardEl.classList.add('border-l-4', 'border-l-red-500');
                    else if (value === 'high') cardEl.classList.add('border-l-4', 'border-l-orange-500');
                    else if (value === 'medium') cardEl.classList.add('border-l-4', 'border-l-yellow-500');

                    // Update badge
                    const badge = cardEl.querySelector('.priority-badge');
                    if (badge) {
                        const priorityLabels = { urgent: 'Терміново', high: 'Високий', medium: 'Середній', low: '' };
                        const priorityClasses = {
                            urgent: 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300',
                            high: 'bg-orange-100 text-orange-700 dark:bg-orange-900/50 dark:text-orange-300',
                            medium: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/50 dark:text-yellow-300'
                        };

                        // Remove old classes
                        badge.classList.remove('hidden', 'bg-red-100', 'text-red-700', 'dark:bg-red-900/50', 'dark:text-red-300',
                            'bg-orange-100', 'text-orange-700', 'dark:bg-orange-900/50', 'dark:text-orange-300',
                            'bg-yellow-100', 'text-yellow-700', 'dark:bg-yellow-900/50', 'dark:text-yellow-300');

                        if (value === 'low' || !value) {
                            badge.classList.add('hidden');
                        } else {
                            priorityClasses[value]?.split(' ').forEach(c => badge.classList.add(c));
                            const textEl = badge.querySelector('.priority-text');
                            if (textEl) textEl.textContent = priorityLabels[value] || '';
                            const iconEl = badge.querySelector('.priority-icon');
                            if (iconEl) {
                                if (value === 'urgent') iconEl.classList.remove('hidden');
                                else iconEl.classList.add('hidden');
                            }
                        }
                    }
                }

                // Update assignee
                if (field === 'assigned_to') {
                    cardEl.dataset.assignee = value || 'unassigned';
                }

                // Update due date
                if (field === 'due_date') {
                    cardEl.dataset.due = value || '';
                }
            }

            // Update in allCards for search
            const cardIndex = this.allCards.findIndex(c => c.id === cardId);
            if (cardIndex !== -1) {
                this.allCards[cardIndex] = { ...this.allCards[cardIndex], [field]: value };
                if (field === 'column_id') {
                    const colOption = document.querySelector(`select option[value="${value}"]`);
                    if (colOption) this.allCards[cardIndex].columnName = colOption.textContent.trim();
                }
            }
        },

        updateColumnCount(columnId) {
            const column = document.querySelector(`.kanban-cards[data-column-id="${columnId}"]`);
            const countEl = document.querySelector(`.column-count[data-column-id="${columnId}"]`);
            if (column && countEl) {
                countEl.textContent = column.querySelectorAll('.kanban-card').length;
            }
        },

        async toggleCardComplete() {
            if (!this.cardPanel.data) return;

            const cardId = this.cardPanel.data.card.id;
            await fetch(`/boards/cards/${cardId}/toggle`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': this.csrfToken }
            });

            this.cardPanel.data.card.is_completed = !this.cardPanel.data.card.is_completed;

            // Update the card in the kanban view
            const cardEl = document.querySelector(`[data-card-id="${cardId}"]`);
            if (cardEl) {
                const titleEl = cardEl.querySelector('p.text-sm.font-medium');
                if (titleEl) {
                    if (this.cardPanel.data.card.is_completed) {
                        titleEl.classList.add('line-through', 'text-gray-400', 'dark:text-gray-500');
                    } else {
                        titleEl.classList.remove('line-through', 'text-gray-400', 'dark:text-gray-500');
                    }
                }
            }
        },

        // Comments
        async addComment(content) {
            if (!content.trim() || !this.cardPanel.data) return;

            const cardId = this.cardPanel.data.card.id;
            const response = await fetch(`/boards/cards/${cardId}/comments`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ content })
            });

            const result = await response.json();
            if (result.success) {
                this.cardPanel.data.comments.unshift(result.comment);
            }
        },

        async deleteComment(comment) {
            if (!confirm('Видалити коментар?')) return;

            await fetch(`/boards/comments/${comment.id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': this.csrfToken, 'Accept': 'application/json' }
            });

            this.cardPanel.data.comments = this.cardPanel.data.comments.filter(c => c.id !== comment.id);
        },

        // Checklist
        async addChecklistItem(title) {
            if (!title.trim() || !this.cardPanel.data) return;

            const cardId = this.cardPanel.data.card.id;
            const response = await fetch(`/boards/cards/${cardId}/checklist`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ title })
            });

            const result = await response.json();
            if (result.success) {
                this.cardPanel.data.checklist.push(result.item);
            }
        },

        async toggleChecklistItem(item) {
            await fetch(`/boards/cards/checklist/${item.id}/toggle`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': this.csrfToken, 'Accept': 'application/json' }
            });

            item.is_completed = !item.is_completed;
        },

        async deleteChecklistItem(item) {
            await fetch(`/boards/cards/checklist/${item.id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': this.csrfToken, 'Accept': 'application/json' }
            });

            this.cardPanel.data.checklist = this.cardPanel.data.checklist.filter(i => i.id !== item.id);
        },

        // Update comment
        async updateComment(comment, newContent) {
            if (!newContent.trim()) return;

            const response = await fetch(`/boards/comments/${comment.id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ content: newContent })
            });

            const result = await response.json();
            if (result.success) {
                comment.content = newContent;
                comment.is_edited = true;
                comment.updated_at = result.comment.updated_at;
            }
        },

        // Attachments
        async uploadAttachment(event) {
            const file = event.target.files[0];
            if (!file || !this.cardPanel.data) return;

            const formData = new FormData();
            formData.append('file', file);

            const cardId = this.cardPanel.data.card.id;
            const response = await fetch(`/boards/cards/${cardId}/attachments`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            });

            const result = await response.json();
            if (result.success) {
                this.cardPanel.data.attachments.push(result.attachment);
            }

            event.target.value = '';
        },

        async deleteAttachment(file) {
            if (!confirm('Видалити файл?')) return;

            await fetch(`/boards/attachments/${file.id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': this.csrfToken, 'Accept': 'application/json' }
            });

            this.cardPanel.data.attachments = this.cardPanel.data.attachments.filter(a => a.id !== file.id);
        },

        // Related cards
        async addRelatedCard(relatedCardId) {
            if (!relatedCardId || !this.cardPanel.data) return;

            const cardId = this.cardPanel.data.card.id;
            const response = await fetch(`/boards/cards/${cardId}/related`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ related_card_id: relatedCardId })
            });

            const result = await response.json();
            if (result.success) {
                this.cardPanel.data.related_cards.push(result.related_card);
                this.cardPanel.data.available_cards = this.cardPanel.data.available_cards.filter(c => c.id != relatedCardId);
            }
        },

        async removeRelatedCard(related) {
            const cardId = this.cardPanel.data.card.id;
            await fetch(`/boards/cards/${cardId}/related/${related.id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': this.csrfToken, 'Accept': 'application/json' }
            });

            this.cardPanel.data.related_cards = this.cardPanel.data.related_cards.filter(r => r.id !== related.id);
            this.cardPanel.data.available_cards.push({
                id: related.id,
                title: related.title,
                column_name: related.column_name
            });
        },

        // Duplicate card
        async duplicateCard() {
            if (!this.cardPanel.data) return;

            const cardId = this.cardPanel.data.card.id;
            const columnId = this.cardPanel.data.card.column_id;

            try {
                const response = await fetch(`/boards/cards/${cardId}/duplicate`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();
                if (result.success && result.card) {
                    this.insertNewCard(result.card, columnId);
                    this.closePanel();
                    if (window.showGlobalToast) showGlobalToast('Завдання дубльовано', 'success');
                }
            } catch (error) {
                console.error('Duplicate error:', error);
                if (window.showGlobalToast) showGlobalToast('Помилка дублювання', 'error');
            }
        },

        // Add comment with files
        async addCommentWithFiles(content, files) {
            if (!content.trim() || !this.cardPanel.data) return;

            const cardId = this.cardPanel.data.card.id;
            const formData = new FormData();
            formData.append('content', content);

            if (files && files.length > 0) {
                files.forEach((file, idx) => {
                    formData.append(`files[${idx}]`, file);
                });
            }

            const response = await fetch(`/boards/cards/${cardId}/comments`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            });

            const result = await response.json();
            if (result.success) {
                this.cardPanel.data.comments.unshift(result.comment);
            }
        },

        async deleteCard(cardId) {
            if (!confirm('Видалити завдання?')) return;

            try {
                const response = await fetch(`/boards/cards/${cardId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    // Remove card from DOM
                    const cardEl = document.querySelector(`[data-card-id="${cardId}"]`);
                    if (cardEl) {
                        const columnId = cardEl.closest('.kanban-cards')?.dataset.columnId;
                        cardEl.remove();
                        if (columnId) this.updateColumnCount(columnId);
                    }
                    // Close panel if open
                    if (this.cardPanel.cardId === cardId) {
                        this.closePanel();
                    }
                    if (window.showGlobalToast) showGlobalToast('Завдання видалено', 'success');
                }
            } catch (error) {
                console.error('Delete error:', error);
                if (window.showGlobalToast) showGlobalToast('Помилка видалення', 'error');
            }
        }
    }
}
</script>

<style>
.kanban-card { position: relative; }
.kanban-card.sortable-ghost { opacity: 0.4; }
.kanban-card.sortable-chosen { transform: rotate(2deg); }
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
</style>
@endsection
