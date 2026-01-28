@extends('layouts.app')

@section('title', 'Трекер завдань')

@section('content')
<div class="h-full -mt-2" x-data="churchBoard()" x-init="init()" @keydown.window="handleKeydown($event)">
    <!-- Header with filters -->
    <div class="mb-4 space-y-3">
        <!-- Title & Stats Row -->
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Трекер завдань</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Всі завдання команд в одному місці</p>
            </div>

            <!-- Quick Stats -->
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-6 px-4 py-2 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                    <div class="text-center">
                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</p>
                        <p class="text-xs text-gray-500">Всього</p>
                    </div>
                    <div class="text-center">
                        <p class="text-lg font-bold text-green-600 dark:text-green-400">{{ $stats['completed'] }}</p>
                        <p class="text-xs text-gray-500">Готово</p>
                    </div>
                    <div class="text-center">
                        <p class="text-lg font-bold {{ $stats['overdue'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">{{ $stats['overdue'] }}</p>
                        <p class="text-xs text-gray-500">Прострочено</p>
                    </div>
                    <div class="text-center">
                        <p class="text-lg font-bold text-primary-600 dark:text-primary-400">{{ $stats['my_tasks'] }}</p>
                        <p class="text-xs text-gray-500">Мої</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Filters Bar (Shortcut Style) -->
        <div class="flex items-center gap-2 flex-wrap bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-2 shadow-sm">
            <!-- Search -->
            <div class="relative flex-1 min-w-[200px] max-w-xs" x-data="{ showDropdown: false }" @click.away="showDropdown = false">
                <input type="text" x-model="searchQuery" placeholder="Пошук... (/)"
                       x-ref="searchInput"
                       @focus="showDropdown = true"
                       @keydown.escape="showDropdown = false; $el.blur()"
                       @keydown.enter.prevent="if (filteredCards.length) openCard(filteredCards[0].id)"
                       class="w-full pl-9 pr-4 py-2 bg-gray-50 dark:bg-gray-900 border-0 rounded-lg text-sm dark:text-white focus:ring-2 focus:ring-primary-500 placeholder-gray-400">
                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <kbd class="absolute right-2 top-1/2 -translate-y-1/2 px-1.5 py-0.5 text-[10px] font-mono text-gray-400 bg-gray-200 dark:bg-gray-700 rounded hidden sm:inline">/</kbd>

                <!-- Search Dropdown -->
                <div x-show="showDropdown && searchQuery.length > 0" x-transition
                     class="absolute z-50 mt-1 w-80 max-h-72 overflow-y-auto bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl">
                    <template x-if="filteredCards.length === 0">
                        <div class="p-3 text-sm text-gray-500 dark:text-gray-400 text-center">Нічого не знайдено</div>
                    </template>
                    <template x-for="card in filteredCards.slice(0, 10)" :key="card.id">
                        <button @click="openCard(card.id); showDropdown = false; searchQuery = ''"
                                class="w-full px-3 py-2 text-left hover:bg-gray-50 dark:hover:bg-gray-700/50 flex items-start gap-2 border-b border-gray-100 dark:border-gray-700/50 last:border-0">
                            <span class="text-xs font-mono text-gray-400 dark:text-gray-500 flex-shrink-0 mt-0.5" x-text="'#' + card.id"></span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-900 dark:text-white truncate" x-text="card.title"></p>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="text-xs text-gray-500 dark:text-gray-400" x-text="card.columnName"></span>
                                    <template x-if="card.epicName">
                                        <span class="text-xs px-1.5 py-0.5 rounded-full" :style="`background-color: ${card.epicColor}20; color: ${card.epicColor}`" x-text="card.epicName"></span>
                                    </template>
                                </div>
                            </div>
                        </button>
                    </template>
                </div>
            </div>

            <div class="h-6 w-px bg-gray-200 dark:bg-gray-700 hidden sm:block"></div>

            <!-- Epic Filter -->
            <select x-model="filters.epic"
                    class="px-3 py-2 bg-gray-50 dark:bg-gray-900 border-0 rounded-lg text-sm dark:text-white focus:ring-2 focus:ring-primary-500">
                <option value="">Всі проєкти</option>
                @foreach($epics as $epic)
                    <option value="{{ $epic['id'] }}">{{ $epic['name'] }}</option>
                @endforeach
            </select>

            <!-- Priority Filter -->
            <select x-model="filters.priority"
                    class="px-3 py-2 bg-gray-50 dark:bg-gray-900 border-0 rounded-lg text-sm dark:text-white focus:ring-2 focus:ring-primary-500">
                <option value="">Пріоритет</option>
                <option value="urgent">🔴 Терміново</option>
                <option value="high">🟠 Високий</option>
                <option value="medium">🟡 Середній</option>
                <option value="low">⚪ Низький</option>
            </select>

            <!-- Assignee Filter -->
            <select x-model="filters.assignee"
                    class="px-3 py-2 bg-gray-50 dark:bg-gray-900 border-0 rounded-lg text-sm dark:text-white focus:ring-2 focus:ring-primary-500">
                <option value="">Виконавець</option>
                <option value="me">👤 Мої завдання</option>
                <option value="unassigned">❓ Без виконавця</option>
                @foreach($people as $person)
                    <option value="{{ $person->id }}">{{ $person->full_name }}</option>
                @endforeach
            </select>

            <!-- Ministry Filter -->
            <select x-model="filters.ministry"
                    class="px-3 py-2 bg-gray-50 dark:bg-gray-900 border-0 rounded-lg text-sm dark:text-white focus:ring-2 focus:ring-primary-500">
                <option value="">Команда</option>
                @foreach($ministries as $ministry)
                    <option value="{{ $ministry->id }}">{{ $ministry->name }}</option>
                @endforeach
            </select>

            <!-- Saved Views -->
            <div class="hidden sm:flex items-center gap-1">
                <button @click="applyPreset('overdue')"
                        class="px-2 py-1.5 text-xs font-medium rounded-lg transition-colors"
                        :class="activePreset === 'overdue' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700'">
                    Прострочені
                </button>
                <button @click="applyPreset('my')"
                        class="px-2 py-1.5 text-xs font-medium rounded-lg transition-colors"
                        :class="activePreset === 'my' ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700'">
                    Мої
                </button>
            </div>

            <!-- Clear Filters -->
            <template x-if="hasActiveFilters">
                <button @click="clearFilters()" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </template>

            <div class="flex-1"></div>

            <!-- Actions -->
            <button @click="openAddCardModal()"
                    class="flex items-center gap-2 px-3 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span class="hidden sm:inline">Нове завдання</span>
                <kbd class="hidden sm:inline px-1.5 py-0.5 text-[10px] font-mono bg-primary-500 rounded">N</kbd>
            </button>

            <button @click="showEpicModal = true"
                    class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                    title="Новий проєкт">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
            </button>

            <button @click="showShortcuts = true"
                    class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                    title="Шорткати (?)">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex gap-4">
        <!-- Epics Sidebar -->
        @if(count($epics) > 0)
        <div class="hidden lg:block w-64 flex-shrink-0">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="p-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Проєкти</h3>
                    <button @click="showEpicModal = true" class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </button>
                </div>
                <div class="p-2 space-y-1 max-h-[calc(100vh-320px)] overflow-y-auto">
                    @foreach($epics as $epic)
                    <button @click="filters.epic = filters.epic == '{{ $epic['id'] }}' ? '' : '{{ $epic['id'] }}'"
                            class="w-full p-2 rounded-lg text-left transition-all"
                            :class="filters.epic == '{{ $epic['id'] }}' ? 'bg-gray-100 dark:bg-gray-700' : 'hover:bg-gray-50 dark:hover:bg-gray-700/50'">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full flex-shrink-0" style="background-color: {{ $epic['color'] }}"></div>
                            <span class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $epic['name'] }}</span>
                            <span class="text-xs text-gray-400 ml-auto">{{ $epic['total'] }}</span>
                        </div>
                        @if($epic['total'] > 0)
                        <div class="mt-2 flex items-center gap-2">
                            <div class="flex-1 h-1.5 bg-gray-200 dark:bg-gray-600 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all" style="width: {{ $epic['progress'] }}%; background-color: {{ $epic['color'] }}"></div>
                            </div>
                            <span class="text-[10px] text-gray-500">{{ $epic['completed'] }}/{{ $epic['total'] }}</span>
                        </div>
                        @endif
                    </button>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Kanban Columns Container -->
        <div class="flex-1 overflow-hidden">
            <div class="kanban-container flex gap-4 pb-4 overflow-x-auto scrollbar-thin" id="kanban-columns">
                @foreach($board->columns as $column)
                    @php
                        $columnColors = [
                            'gray' => ['bg' => 'bg-gray-100', 'border' => 'border-gray-300', 'dot' => 'bg-gray-400'],
                            'blue' => ['bg' => 'bg-blue-50', 'border' => 'border-blue-300', 'dot' => 'bg-blue-500'],
                            'yellow' => ['bg' => 'bg-amber-50', 'border' => 'border-amber-300', 'dot' => 'bg-amber-500'],
                            'green' => ['bg' => 'bg-green-50', 'border' => 'border-green-300', 'dot' => 'bg-green-500'],
                            'red' => ['bg' => 'bg-red-50', 'border' => 'border-red-300', 'dot' => 'bg-red-500'],
                            'purple' => ['bg' => 'bg-purple-50', 'border' => 'border-purple-300', 'dot' => 'bg-purple-500'],
                        ];
                        $colors = $columnColors[$column->color] ?? $columnColors['gray'];
                    @endphp
                    <div class="kanban-column flex-shrink-0 w-72 sm:w-80 bg-gray-50/80 dark:bg-gray-800/50 rounded-xl flex flex-col border border-gray-200/50 dark:border-gray-700/50"
                         data-column-id="{{ $column->id }}"
                         x-data="{ collapsed: false }">

                        <!-- Column Header with Color Strip -->
                        <div class="relative">
                            <div class="absolute top-0 left-0 right-0 h-1 rounded-t-xl {{ $colors['dot'] }}"></div>
                            <div class="p-3 pt-4 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full {{ $colors['dot'] }}"></div>
                                    <h3 class="font-semibold text-gray-800 dark:text-white text-sm">{{ $column->name }}</h3>
                                    <span class="column-count text-xs text-gray-400 dark:text-gray-500 bg-gray-200/50 dark:bg-gray-700/50 px-2 py-0.5 rounded-full font-medium"
                                          data-column-id="{{ $column->id }}">
                                        {{ $column->cards->count() }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <button type="button" @click="collapsed = !collapsed"
                                            class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                        <svg class="w-4 h-4 transition-transform" :class="collapsed ? '-rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                    <button type="button" @click="openAddCardModal({{ $column->id }})"
                                            class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Cards Container -->
                        <div x-show="!collapsed" x-collapse
                             class="flex-1 p-2 space-y-2 min-h-[80px] kanban-cards overflow-y-auto max-h-[50vh] lg:max-h-[calc(100vh-300px)]"
                             data-column-id="{{ $column->id }}">
                            @foreach($column->cards as $card)
                                <div class="kanban-card group bg-white dark:bg-gray-700 rounded-xl border border-gray-100 dark:border-gray-600 p-3 cursor-pointer hover:shadow-md hover:border-gray-200 dark:hover:border-gray-500 transition-all duration-200 overflow-hidden relative"
                                     draggable="true"
                                     data-card-id="{{ $card->id }}"
                                     data-priority="{{ $card->priority }}"
                                     data-assignee="{{ $card->assigned_to ?? 'unassigned' }}"
                                     data-ministry="{{ $card->ministry_id ?? '' }}"
                                     data-epic="{{ $card->epic_id ?? '' }}"
                                     data-due="{{ $card->due_date?->format('Y-m-d') ?? '' }}"
                                     data-title="{{ strtolower($card->title) }}"
                                     data-overdue="{{ $card->isOverdue() ? '1' : '0' }}"
                                     @click="openCard({{ $card->id }})">

                                    <!-- Top row: Epic + ID -->
                                    <div class="card-top-row flex items-center gap-1.5 mb-2 flex-wrap">
                                        @if($card->epic)
                                            <span class="epic-badge inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[10px] font-medium"
                                                  style="background-color: {{ $card->epic->color }}20; color: {{ $card->epic->color }}">
                                                <span class="w-1.5 h-1.5 rounded-full" style="background-color: {{ $card->epic->color }}"></span>
                                                {{ Str::limit($card->epic->name, 15) }}
                                            </span>
                                        @endif
                                        <span class="text-[10px] font-mono text-gray-400 dark:text-gray-500 ml-auto">#{{ $card->id }}</span>
                                    </div>

                                    <!-- Title -->
                                    <p class="card-title text-sm font-medium text-gray-900 dark:text-white leading-snug mb-2 {{ $card->is_completed ? 'line-through text-gray-400 dark:text-gray-500' : '' }}">
                                        {{ $card->title }}
                                    </p>

                                    <!-- Description preview -->
                                    @if($card->description)
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3 line-clamp-2">{{ Str::limit($card->description, 80) }}</p>
                                    @endif

                                    <!-- Bottom row: Priority, Due, Meta -->
                                    <div class="card-meta flex items-center justify-between text-xs">
                                        <div class="card-badges flex items-center gap-2 flex-wrap">
                                            <!-- Priority indicator -->
                                            @if($card->priority === 'urgent')
                                                <span class="priority-badge inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300 font-medium">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                                    Терм.
                                                </span>
                                            @elseif($card->priority === 'high')
                                                <span class="priority-badge inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-orange-100 text-orange-700 dark:bg-orange-900/50 dark:text-orange-300 font-medium">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                                                    Вис.
                                                </span>
                                            @elseif($card->priority === 'medium')
                                                <span class="priority-badge w-1.5 h-1.5 rounded-full bg-yellow-500" title="Середній"></span>
                                            @endif

                                            <!-- Due date -->
                                            @if($card->due_date)
                                                <span class="due-badge inline-flex items-center gap-1 {{ $card->isOverdue() ? 'text-red-600 dark:text-red-400 font-medium' : ($card->isDueSoon() ? 'text-orange-600 dark:text-orange-400' : 'text-gray-500 dark:text-gray-400') }}">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                    {{ $card->due_date->format('d.m') }}
                                                </span>
                                            @endif

                                            <!-- Checklist progress -->
                                            @if($card->checklistItems->count() > 0)
                                                @php
                                                    $completed = $card->checklistItems->where('is_completed', true)->count();
                                                    $total = $card->checklistItems->count();
                                                @endphp
                                                <span class="checklist-badge inline-flex items-center gap-1 {{ $completed === $total ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400' }}">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                    {{ $completed }}/{{ $total }}
                                                </span>
                                            @endif

                                            <!-- Comments -->
                                            @if($card->comments->count() > 0)
                                                <span class="comment-badge inline-flex items-center gap-1 text-gray-500 dark:text-gray-400">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                                    </svg>
                                                    {{ $card->comments->count() }}
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Assignee avatar -->
                                        <div class="card-assignee flex-shrink-0">
                                        @if($card->assignee)
                                            <div title="{{ $card->assignee->full_name }}">
                                                @if($card->assignee->photo)
                                                    <img src="{{ Storage::url($card->assignee->photo) }}"
                                                         class="w-6 h-6 rounded-full object-cover ring-2 ring-white dark:ring-gray-700" loading="lazy">
                                                @else
                                                    <div class="w-6 h-6 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center ring-2 ring-white dark:ring-gray-700">
                                                        <span class="text-primary-600 dark:text-primary-400 text-xs font-medium">
                                                            {{ mb_substr($card->assignee->first_name, 0, 1) }}
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                        </div>
                                    </div>

                                    <!-- Quick complete button -->
                                    <button @click.stop="toggleComplete({{ $card->id }})"
                                            class="absolute top-2 right-2 p-1.5 rounded-lg opacity-0 group-hover:opacity-100 transition-all
                                                   {{ $card->is_completed ? 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-400 dark:bg-gray-600 hover:text-green-600 dark:hover:text-green-400' }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>

                        <!-- Quick Add -->
                        <div x-show="!collapsed" class="p-2 border-t border-gray-200/50 dark:border-gray-700/50">
                            <button type="button" @click="openAddCardModal({{ $column->id }})"
                                    class="w-full p-2 text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg text-sm transition-colors flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span>Додати завдання</span>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Card Slide-Over Panel -->
    @include('boards._card_panel')

    <!-- Add Card Modal -->
    @include('boards._add_card_modal')

    <!-- Epic Modal -->
    @include('boards._epic_modal')

    <!-- Keyboard Shortcuts Modal -->
    @include('boards._shortcuts_modal')
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
            'columnId' => $col->id,
            'epicId' => $card->epic_id,
            'epicName' => $card->epic?->name,
            'epicColor' => $card->epic?->color,
        ];
    }
}
$epicsData = $epics->toArray();
@endphp

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
function churchBoard() {
    return {
        filters: {
            priority: '',
            assignee: '',
            ministry: '',
            epic: ''
        },
        searchQuery: '',
        activePreset: '',
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
            epicId: '',
            assignedTo: '',
            dueDate: ''
        },
        showEpicModal: false,
        newEpic: { name: '', color: '#6366f1', description: '' },
        cards: @json($board->columns->flatMap->cards->keyBy('id')),
        allCards: @json($allCardsData),
        epics: @json($epicsData),
        peopleList: @json($people->map(fn($p) => ['id' => $p->id, 'name' => $p->full_name, 'photo' => $p->photo ? Storage::url($p->photo) : null])),
        showShortcuts: false,
        myPersonId: {{ auth()->user()->person?->id ?? 'null' }},
        boardId: {{ $board->id }},
        csrfToken: document.querySelector('meta[name="csrf-token"]').content,
        selectedCardIndex: -1,

        get hasActiveFilters() {
            return this.filters.priority || this.filters.assignee || this.filters.ministry || this.filters.epic || this.searchQuery;
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
            this.$watch('filters', () => { this.applyFilters(); this.activePreset = ''; }, { deep: true });
            this.$watch('searchQuery', () => this.applyFilters());

            // Open card from URL
            const urlParams = new URLSearchParams(window.location.search);
            const cardId = urlParams.get('card');
            if (cardId) {
                this.openCard(parseInt(cardId));
                window.history.replaceState({}, '', window.location.pathname);
            }
        },

        handleKeydown(e) {
            // Skip if typing in input
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.isContentEditable) {
                if (e.key === 'Escape') {
                    e.target.blur();
                }
                return;
            }

            // Global shortcuts
            switch(e.key) {
                case '/':
                    e.preventDefault();
                    this.$refs.searchInput?.focus();
                    break;
                case '?':
                    e.preventDefault();
                    this.showShortcuts = !this.showShortcuts;
                    break;
                case 'Escape':
                    this.closePanel();
                    this.showShortcuts = false;
                    this.addCardModal.open = false;
                    this.showEpicModal = false;
                    break;
                case 'n':
                case 'N':
                    if (!e.metaKey && !e.ctrlKey) {
                        e.preventDefault();
                        this.openAddCardModal();
                    }
                    break;
                case 'c':
                case 'C':
                    if (this.cardPanel.open && this.cardPanel.data) {
                        e.preventDefault();
                        this.toggleCardComplete();
                    }
                    break;
                case 'm':
                case 'M':
                    if (this.cardPanel.open) {
                        e.preventDefault();
                        // Focus column select
                    }
                    break;
            }
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

                        this.updateColumnCount(evt.from.dataset.columnId);
                        this.updateColumnCount(newColumnId);
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

                if (show && this.filters.epic && card.dataset.epic !== this.filters.epic) {
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

                // Overdue preset
                if (this.activePreset === 'overdue' && card.dataset.overdue !== '1') {
                    show = false;
                }

                card.style.display = show ? '' : 'none';

                const columnId = card.closest('.kanban-cards')?.dataset.columnId;
                if (columnId) {
                    if (!counts[columnId]) counts[columnId] = 0;
                    if (show) counts[columnId]++;
                }
            });

            document.querySelectorAll('.column-count').forEach(el => {
                const columnId = el.dataset.columnId;
                if (counts[columnId] !== undefined) {
                    el.textContent = counts[columnId];
                }
            });
        },

        applyPreset(preset) {
            this.clearFilters();
            this.activePreset = preset;

            if (preset === 'my') {
                this.filters.assignee = 'me';
            } else if (preset === 'overdue') {
                this.applyFilters();
            }
        },

        clearFilters() {
            this.filters = { priority: '', assignee: '', ministry: '', epic: '' };
            this.searchQuery = '';
            this.activePreset = '';
        },

        openAddCardModal(columnId = null) {
            this.addCardModal = {
                open: true,
                loading: false,
                columnId: columnId || {{ $board->columns->first()?->id ?? 'null' }},
                title: '',
                description: '',
                priority: 'medium',
                ministryId: '',
                epicId: '',
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
                        epic_id: this.addCardModal.epicId || null,
                        assigned_to: this.addCardModal.assignedTo || null,
                        due_date: this.addCardModal.dueDate || null
                    })
                });

                if (response.ok) {
                    const data = await response.json();
                    if (data.success && data.card) {
                        // Find epic info
                        const epic = this.epics.find(e => e.id == data.card.epic_id);
                        // Find assignee info
                        const assignee = this.peopleList.find(p => p.id == data.card.assigned_to);

                        // Insert card dynamically
                        this.insertCardToDOM(data.card, this.addCardModal.columnId, epic, assignee);

                        // Add to allCards for search
                        this.allCards.push({
                            id: data.card.id,
                            title: data.card.title,
                            priority: data.card.priority,
                            columnName: this.getColumnName(this.addCardModal.columnId),
                            columnId: this.addCardModal.columnId,
                            epicId: data.card.epic_id,
                            epicName: epic?.name,
                            epicColor: epic?.color
                        });

                        // Close modal and show toast
                        this.addCardModal.open = false;
                        if (window.showGlobalToast) showGlobalToast('Завдання створено', 'success');
                    }
                }
            } catch (error) {
                console.error('Error:', error);
            } finally {
                this.addCardModal.loading = false;
            }
        },

        getColumnName(columnId) {
            const col = document.querySelector(`.kanban-column[data-column-id="${columnId}"] h3`);
            return col ? col.textContent.trim() : '';
        },

        insertCardToDOM(card, columnId, epic, assignee) {
            const column = document.querySelector(`.kanban-cards[data-column-id="${columnId}"]`);
            if (!column) return;

            const cardHtml = this.renderCardHtml(card, epic, assignee);
            column.insertAdjacentHTML('afterbegin', cardHtml);
            this.updateColumnCount(columnId);

            // Re-init sortable for the new card
            this.initSortable();
        },

        renderCardHtml(card, epic, assignee) {
            const priorityBadge = this.getPriorityBadgeHtml(card.priority);
            const epicBadge = epic ? this.getEpicBadgeHtml(epic) : '';
            const dueBadge = card.due_date ? this.getDueBadgeHtml(card.due_date) : '';
            const assigneeHtml = assignee ? this.getAssigneeHtml(assignee) : '';
            const descHtml = card.description ? `<p class="text-xs text-gray-500 dark:text-gray-400 mb-3 line-clamp-2">${this.escapeHtml(card.description).substring(0, 80)}</p>` : '';

            return `
                <div class="kanban-card group bg-white dark:bg-gray-700 rounded-xl border border-gray-100 dark:border-gray-600 p-3 cursor-pointer hover:shadow-md hover:border-gray-200 dark:hover:border-gray-500 transition-all duration-200 overflow-hidden relative"
                     draggable="true"
                     data-card-id="${card.id}"
                     data-priority="${card.priority || 'low'}"
                     data-assignee="${card.assigned_to || 'unassigned'}"
                     data-ministry="${card.ministry_id || ''}"
                     data-epic="${card.epic_id || ''}"
                     data-due="${card.due_date || ''}"
                     data-title="${(card.title || '').toLowerCase()}"
                     data-overdue="0"
                     onclick="document.querySelector('[x-data]').__x.$data.openCard(${card.id})">
                    <div class="card-top-row flex items-center gap-1.5 mb-2 flex-wrap">
                        ${epicBadge}
                        <span class="text-[10px] font-mono text-gray-400 dark:text-gray-500 ml-auto">#${card.id}</span>
                    </div>
                    <p class="card-title text-sm font-medium text-gray-900 dark:text-white leading-snug mb-2">${this.escapeHtml(card.title)}</p>
                    ${descHtml}
                    <div class="card-meta flex items-center justify-between text-xs">
                        <div class="card-badges flex items-center gap-2 flex-wrap">
                            ${priorityBadge}
                            ${dueBadge}
                        </div>
                        <div class="card-assignee flex-shrink-0">${assigneeHtml}</div>
                    </div>
                    <button onclick="event.stopPropagation(); document.querySelector('[x-data]').__x.$data.toggleComplete(${card.id})"
                            class="absolute top-2 right-2 p-1.5 rounded-lg opacity-0 group-hover:opacity-100 transition-all bg-gray-100 text-gray-400 dark:bg-gray-600 hover:text-green-600 dark:hover:text-green-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </button>
                </div>`;
        },

        getPriorityBadgeHtml(priority) {
            const badges = {
                'urgent': '<span class="priority-badge inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300 font-medium"><span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>Терм.</span>',
                'high': '<span class="priority-badge inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-orange-100 text-orange-700 dark:bg-orange-900/50 dark:text-orange-300 font-medium"><span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>Вис.</span>',
                'medium': '<span class="priority-badge w-1.5 h-1.5 rounded-full bg-yellow-500" title="Середній"></span>'
            };
            return badges[priority] || '';
        },

        getEpicBadgeHtml(epic) {
            return `<span class="epic-badge inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[10px] font-medium" style="background-color: ${epic.color}20; color: ${epic.color}"><span class="w-1.5 h-1.5 rounded-full" style="background-color: ${epic.color}"></span>${this.escapeHtml(epic.name).substring(0, 15)}</span>`;
        },

        getDueBadgeHtml(dueDate) {
            const date = new Date(dueDate);
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            return `<span class="due-badge inline-flex items-center gap-1 text-gray-500 dark:text-gray-400"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>${day}.${month}</span>`;
        },

        getAssigneeHtml(assignee) {
            if (assignee.photo) {
                return `<img src="${assignee.photo}" class="w-6 h-6 rounded-full object-cover ring-2 ring-white dark:ring-gray-700" title="${this.escapeHtml(assignee.name)}">`;
            }
            return `<div class="w-6 h-6 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center ring-2 ring-white dark:ring-gray-700" title="${this.escapeHtml(assignee.name)}"><span class="text-primary-600 dark:text-primary-400 text-xs font-medium">${assignee.name.charAt(0)}</span></div>`;
        },

        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text || '';
            return div.innerHTML;
        },

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

            // Update DOM
            const cardEl = document.querySelector(`[data-card-id="${cardId}"]`);
            if (cardEl) {
                if (field === 'title') {
                    const titleEl = cardEl.querySelector('.card-title');
                    if (titleEl) titleEl.textContent = value;
                    cardEl.dataset.title = value.toLowerCase();
                }

                if (field === 'column_id') {
                    const newColumn = document.querySelector(`.kanban-cards[data-column-id="${value}"]`);
                    if (newColumn && cardEl.parentElement.dataset.columnId !== value.toString()) {
                        const oldColumn = cardEl.parentElement;
                        newColumn.insertBefore(cardEl, newColumn.firstChild);
                        this.updateColumnCount(oldColumn.dataset.columnId);
                        this.updateColumnCount(value);
                    }
                }

                if (field === 'epic_id') {
                    cardEl.dataset.epic = value || '';
                    // Update epic badge on card
                    const topRow = cardEl.querySelector('.card-top-row');
                    if (topRow) {
                        const existingBadge = topRow.querySelector('.epic-badge');
                        if (existingBadge) existingBadge.remove();

                        if (value) {
                            const epic = this.epics.find(e => e.id == value);
                            if (epic) {
                                topRow.insertAdjacentHTML('afterbegin', this.getEpicBadgeHtml(epic));
                            }
                        }
                    }
                }

                if (field === 'priority') {
                    cardEl.dataset.priority = value || 'low';
                    // Update priority badge
                    const badges = cardEl.querySelector('.card-badges');
                    if (badges) {
                        const existingBadge = badges.querySelector('.priority-badge');
                        if (existingBadge) existingBadge.remove();
                        const newBadge = this.getPriorityBadgeHtml(value);
                        if (newBadge) {
                            badges.insertAdjacentHTML('afterbegin', newBadge);
                        }
                    }
                }

                if (field === 'due_date') {
                    cardEl.dataset.due = value || '';
                    // Update due badge
                    const badges = cardEl.querySelector('.card-badges');
                    if (badges) {
                        const existingBadge = badges.querySelector('.due-badge');
                        if (existingBadge) existingBadge.remove();
                        if (value) {
                            badges.insertAdjacentHTML('beforeend', this.getDueBadgeHtml(value));
                        }
                    }
                }

                if (field === 'assigned_to') {
                    cardEl.dataset.assignee = value || 'unassigned';
                    // Update assignee avatar
                    const assigneeContainer = cardEl.querySelector('.card-assignee');
                    if (assigneeContainer) {
                        assigneeContainer.innerHTML = '';
                        if (value) {
                            const assignee = this.peopleList.find(p => p.id == value);
                            if (assignee) {
                                assigneeContainer.innerHTML = this.getAssigneeHtml(assignee);
                            }
                        }
                    }
                }
            }

            // Update allCards for search
            const cardIndex = this.allCards.findIndex(c => c.id === cardId);
            if (cardIndex !== -1) {
                if (field === 'title') this.allCards[cardIndex].title = value;
                if (field === 'priority') this.allCards[cardIndex].priority = value;
                if (field === 'epic_id') {
                    this.allCards[cardIndex].epicId = value;
                    const epic = this.epics.find(e => e.id == value);
                    this.allCards[cardIndex].epicName = epic?.name;
                    this.allCards[cardIndex].epicColor = epic?.color;
                }
            }
        },

        updateColumnCount(columnId) {
            const column = document.querySelector(`.kanban-cards[data-column-id="${columnId}"]`);
            const countEl = document.querySelector(`.column-count[data-column-id="${columnId}"]`);
            if (column && countEl) {
                countEl.textContent = column.querySelectorAll('.kanban-card:not([style*="display: none"])').length;
            }
        },

        async toggleComplete(cardId) {
            const response = await fetch(`/boards/cards/${cardId}/toggle`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();

            const cardEl = document.querySelector(`[data-card-id="${cardId}"]`);
            if (cardEl) {
                const titleEl = cardEl.querySelector('.card-title');
                if (titleEl) {
                    titleEl.classList.toggle('line-through', result.is_completed);
                    titleEl.classList.toggle('text-gray-400', result.is_completed);
                    titleEl.classList.toggle('dark:text-gray-500', result.is_completed);
                }

                // Update complete button style
                const completeBtn = cardEl.querySelector('button.absolute.top-2.right-2');
                if (completeBtn) {
                    if (result.is_completed) {
                        completeBtn.classList.add('bg-green-100', 'text-green-600', 'dark:bg-green-900/30', 'dark:text-green-400');
                        completeBtn.classList.remove('bg-gray-100', 'text-gray-400', 'dark:bg-gray-600');
                    } else {
                        completeBtn.classList.remove('bg-green-100', 'text-green-600', 'dark:bg-green-900/30', 'dark:text-green-400');
                        completeBtn.classList.add('bg-gray-100', 'text-gray-400', 'dark:bg-gray-600');
                    }
                }
            }

            if (this.cardPanel.data && this.cardPanel.data.card.id === cardId) {
                this.cardPanel.data.card.is_completed = result.is_completed;
            }
        },

        async toggleCardComplete() {
            if (!this.cardPanel.data) return;
            await this.toggleComplete(this.cardPanel.data.card.id);
        },

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
                this.updateCardCommentCount(cardId, this.cardPanel.data.comments.length);
            }
        },

        async deleteComment(comment) {
            if (!confirm('Видалити коментар?')) return;

            const cardId = this.cardPanel.data.card.id;
            await fetch(`/boards/comments/${comment.id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': this.csrfToken, 'Accept': 'application/json' }
            });

            this.cardPanel.data.comments = this.cardPanel.data.comments.filter(c => c.id !== comment.id);
            this.updateCardCommentCount(cardId, this.cardPanel.data.comments.length);
        },

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
            }
        },

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
                this.updateCardChecklistCount(cardId);
            }
        },

        async toggleChecklistItem(item) {
            const cardId = this.cardPanel.data.card.id;
            await fetch(`/boards/cards/checklist/${item.id}/toggle`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': this.csrfToken, 'Accept': 'application/json' }
            });

            item.is_completed = !item.is_completed;
            this.updateCardChecklistCount(cardId);
        },

        async deleteChecklistItem(item) {
            const cardId = this.cardPanel.data.card.id;
            await fetch(`/boards/cards/checklist/${item.id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': this.csrfToken, 'Accept': 'application/json' }
            });

            this.cardPanel.data.checklist = this.cardPanel.data.checklist.filter(i => i.id !== item.id);
            this.updateCardChecklistCount(cardId);
        },

        updateCardChecklistCount(cardId) {
            const cardEl = document.querySelector(`[data-card-id="${cardId}"]`);
            if (!cardEl || !this.cardPanel.data) return;

            const badges = cardEl.querySelector('.card-badges');
            if (!badges) return;

            // Remove existing checklist badge
            const existingBadge = badges.querySelector('.checklist-badge');
            if (existingBadge) existingBadge.remove();

            const checklist = this.cardPanel.data.checklist;
            if (checklist.length > 0) {
                const completed = checklist.filter(i => i.is_completed).length;
                const total = checklist.length;
                const isComplete = completed === total;
                const colorClass = isComplete ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400';
                badges.insertAdjacentHTML('beforeend', `<span class="checklist-badge inline-flex items-center gap-1 ${colorClass}"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>${completed}/${total}</span>`);
            }
        },

        updateCardCommentCount(cardId, count) {
            const cardEl = document.querySelector(`[data-card-id="${cardId}"]`);
            if (!cardEl) return;

            const badges = cardEl.querySelector('.card-badges');
            if (!badges) return;

            // Remove existing comment badge
            const existingBadge = badges.querySelector('.comment-badge');
            if (existingBadge) existingBadge.remove();

            if (count > 0) {
                badges.insertAdjacentHTML('beforeend', `<span class="comment-badge inline-flex items-center gap-1 text-gray-500 dark:text-gray-400"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>${count}</span>`);
            }
        },

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
        },

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
                    // Find epic and assignee info
                    const epic = this.epics.find(e => e.id == result.card.epic_id);
                    const assignee = this.peopleList.find(p => p.id == result.card.assigned_to);

                    // Insert duplicated card
                    this.insertCardToDOM(result.card, columnId, epic, assignee);

                    // Add to allCards
                    this.allCards.push({
                        id: result.card.id,
                        title: result.card.title,
                        priority: result.card.priority,
                        columnName: this.getColumnName(columnId),
                        columnId: columnId,
                        epicId: result.card.epic_id,
                        epicName: epic?.name,
                        epicColor: epic?.color
                    });

                    this.closePanel();
                    if (window.showGlobalToast) showGlobalToast('Завдання дубльовано', 'success');
                }
            } catch (error) {
                console.error('Duplicate error:', error);
            }
        },

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
                this.updateCardCommentCount(cardId, this.cardPanel.data.comments.length);
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
                    const cardEl = document.querySelector(`[data-card-id="${cardId}"]`);
                    if (cardEl) {
                        const columnId = cardEl.closest('.kanban-cards')?.dataset.columnId;
                        cardEl.remove();
                        if (columnId) this.updateColumnCount(columnId);
                    }

                    // Remove from allCards array for search
                    this.allCards = this.allCards.filter(c => c.id !== cardId);

                    if (this.cardPanel.cardId === cardId) {
                        this.closePanel();
                    }

                    if (window.showGlobalToast) showGlobalToast('Завдання видалено', 'success');
                }
            } catch (error) {
                console.error('Delete error:', error);
            }
        },

        async createEpic() {
            if (!this.newEpic.name.trim()) return;

            try {
                const response = await fetch(`/boards/${this.boardId}/epics`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.newEpic)
                });

                const result = await response.json();
                if (result.success && result.epic) {
                    // Add to epics array
                    const newEpic = {
                        id: result.epic.id,
                        name: result.epic.name,
                        color: result.epic.color,
                        description: result.epic.description,
                        total: 0,
                        completed: 0,
                        progress: 0
                    };
                    this.epics.push(newEpic);

                    // Add to filter dropdown
                    const filterSelect = document.querySelector('select[x-model="filters.epic"]');
                    if (filterSelect) {
                        const option = document.createElement('option');
                        option.value = result.epic.id;
                        option.textContent = result.epic.name;
                        filterSelect.appendChild(option);
                    }

                    // Close modal and reset form
                    this.showEpicModal = false;
                    this.newEpic = { name: '', color: '#6366f1', description: '' };

                    if (window.showGlobalToast) showGlobalToast('Проєкт створено', 'success');
                }
            } catch (error) {
                console.error('Error:', error);
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
.scrollbar-thin::-webkit-scrollbar { width: 6px; height: 6px; }
.scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
.scrollbar-thin::-webkit-scrollbar-thumb { background: rgba(156, 163, 175, 0.3); border-radius: 3px; }
.scrollbar-thin::-webkit-scrollbar-thumb:hover { background: rgba(156, 163, 175, 0.5); }
</style>
@endsection
