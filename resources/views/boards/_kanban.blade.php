{{-- Kanban Board Partial --}}
{{-- Expects: $board, $people, $ministries, $epics --}}
{{-- Optional: $embedded (bool) - hides page header when embedded in another page --}}
@php $embedded = $embedded ?? false; @endphp

<div class="{{ $embedded ? '' : 'h-full -mt-2' }}" x-data="churchBoard()" x-init="init()" @keydown.window="handleKeydown($event)">
    <!-- Header with filters -->
    <div class="mb-4 space-y-3">
        @if(!$embedded)
        <!-- Title & Stats Row (standalone page only) -->
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-3">
                @if($board->ministry)
                    <a href="{{ route('ministries.show', $board->ministry) }}?tab=board"
                       class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                       title="{{ __('app.board_back_to_team') }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                    <div class="w-9 h-9 rounded-lg flex items-center justify-center text-base"
                         style="background-color: {{ $board->display_color }}20">
                        <span class="w-3 h-3 rounded-full" style="background-color: {{ $board->display_color }}"></span>
                    </div>
                @endif
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $board->display_name }}</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $board->ministry_id ? __('app.board_team_board') : __('app.board_all_tasks_in_one_place') }}
                    </p>
                </div>
            </div>

        </div>
        @endif

        <!-- Toolbar -->
        <div class="flex flex-wrap items-center gap-2 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-2 shadow-sm">
            <!-- Search -->
            <div class="relative flex-1 min-w-[120px] sm:min-w-[180px] sm:max-w-xs" x-data="{ showDropdown: false }" @click.away="showDropdown = false">
                <input type="text" x-model="searchQuery" placeholder="{{ __('app.board_search_placeholder') }}"
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
                     class="absolute z-50 mt-1 w-full sm:w-80 max-w-[calc(100vw-2rem)] max-h-72 overflow-y-auto bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl">
                    <template x-if="filteredCards.length === 0">
                        <div class="p-3 text-sm text-gray-500 dark:text-gray-400 text-center">{{ __('app.board_nothing_found') }}</div>
                    </template>
                    <template x-for="card in filteredCards.slice(0, 10)" :key="card.id">
                        <button @click="openCard(card.id); showDropdown = false; searchQuery = ''"
                                class="w-full px-3 py-2 text-left hover:bg-gray-50 dark:hover:bg-gray-700/50 flex items-start gap-2 border-b border-gray-200 dark:border-gray-700/50 last:border-0">
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

            <!-- Filters Button + Dropdown -->
            <div class="relative z-[70]" x-data="{ showFilters: false }" @click.away="showFilters = false">
                <button @click="showFilters = !showFilters"
                        class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors"
                        :class="activeFilterCount > 0 ? 'bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <span class="hidden sm:inline">{{ __('app.board_filters') }}</span>
                    <span x-show="activeFilterCount > 0"
                          class="min-w-[18px] h-[18px] flex items-center justify-center text-[10px] font-bold rounded-full bg-primary-600 text-white"
                          x-text="activeFilterCount"></span>
                </button>

                <!-- Dropdown Panel -->
                <div x-show="showFilters" x-transition
                     class="absolute left-0 sm:left-auto sm:right-auto z-[70] mt-2 w-[calc(100vw-2rem)] sm:w-80 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl">
                    <div class="p-3 space-y-3">
                        <!-- Quick Presets -->
                        <div class="flex items-center gap-2">
                            <button @click="applyPreset('my')"
                                    class="flex-1 px-3 py-2 text-xs font-medium rounded-lg transition-colors text-center"
                                    :class="activePreset === 'my' ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300' : 'bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600'">
                                {{ __('app.board_my_tasks') }}
                            </button>
                            <button @click="applyPreset('overdue')"
                                    class="flex-1 px-3 py-2 text-xs font-medium rounded-lg transition-colors text-center"
                                    :class="activePreset === 'overdue' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' : 'bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600'">
                                {{ __('app.board_overdue') }}
                            </button>
                        </div>

                        <div class="h-px bg-gray-200 dark:bg-gray-700"></div>

                        <!-- Epic -->
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">{{ __('app.board_project') }}</label>
                            <div class="flex flex-wrap gap-1.5">
                                <template x-for="epic in epics" :key="epic.id">
                                    <button @click="filters.epic = filters.epic == String(epic.id) ? '' : String(epic.id)"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-medium transition-all border"
                                            :class="filters.epic == String(epic.id)
                                                ? 'shadow-sm'
                                                : 'bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:border-gray-300 dark:hover:border-gray-500'"
                                            :style="filters.epic == String(epic.id)
                                                ? `border-color: ${epic.color}; background-color: ${epic.color}15; color: ${epic.color}`
                                                : ''">
                                        <span class="w-2 h-2 rounded-full flex-shrink-0" :style="`background-color: ${epic.color}`"></span>
                                        <span x-text="epic.name"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- Priority -->
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('app.board_priority') }}</label>
                            <select x-model="filters.priority"
                                    class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-sm dark:text-white focus:ring-2 focus:ring-primary-500">
                                <option value="">{{ __('app.board_all') }}</option>
                                <option value="urgent">{{ __('app.board_urgent') }}</option>
                                <option value="high">{{ __('app.board_high') }}</option>
                                <option value="medium">{{ __('app.board_medium') }}</option>
                                <option value="low">{{ __('app.board_low') }}</option>
                            </select>
                        </div>

                        <!-- Assignee -->
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('app.board_assignee') }}</label>
                            <select x-model="filters.assignee"
                                    class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-sm dark:text-white focus:ring-2 focus:ring-primary-500">
                                <option value="">{{ __('app.board_all') }}</option>
                                <option value="me">{{ __('app.board_my_tasks_short') }}</option>
                                <option value="unassigned">{{ __('app.board_unassigned') }}</option>
                                @foreach($people as $person)
                                    <option value="{{ $person->id }}">{{ $person->full_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Ministry -->
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('app.board_team') }}</label>
                            <select x-model="filters.ministry"
                                    class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-sm dark:text-white focus:ring-2 focus:ring-primary-500">
                                <option value="">{{ __('app.board_all_teams') }}</option>
                                @foreach($ministries as $ministry)
                                    <option value="{{ $ministry->id }}">{{ $ministry->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Clear -->
                        <template x-if="activeFilterCount > 0">
                            <button @click="clearFilters()" class="w-full px-3 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors font-medium">
                                {{ __('app.board_clear_all_filters') }}
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Active filter chips -->
            <div class="hidden sm:flex items-center gap-1.5 overflow-x-auto">
                <template x-if="filters.epic">
                    <button @click="filters.epic = ''" class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 whitespace-nowrap">
                        <span class="w-2 h-2 rounded-full" :style="`background-color: ${epics.find(e => e.id == filters.epic)?.color}`"></span>
                        <span x-text="epics.find(e => e.id == filters.epic)?.name"></span>
                        <svg class="w-3 h-3 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </template>
                <template x-if="filters.priority">
                    <button @click="filters.priority = ''" class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 whitespace-nowrap">
                        <span x-text="filterLabels.priority[filters.priority]"></span>
                        <svg class="w-3 h-3 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </template>
                <template x-if="filters.assignee">
                    <button @click="filters.assignee = ''" class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 whitespace-nowrap">
                        <span x-text="getAssigneeLabel(filters.assignee)"></span>
                        <svg class="w-3 h-3 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </template>
                <template x-if="filters.ministry">
                    <button @click="filters.ministry = ''" class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 whitespace-nowrap">
                        <span x-text="ministriesList.find(m => m.id == filters.ministry)?.name || @js(__('app.board_team'))"></span>
                        <svg class="w-3 h-3 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </template>
            </div>

            <div class="flex-1"></div>

            <!-- Actions -->
            @if(auth()->user()->canCreate('boards'))
            <button @click="openAddCardModal()"
                    class="flex items-center gap-2 px-3 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span class="hidden sm:inline">{{ __('app.board_task') }}</span>
                <kbd class="hidden sm:inline px-1.5 py-0.5 text-[10px] font-mono bg-primary-500 rounded">N</kbd>
            </button>
            @endif

            <button @click="editingEpic = null; newEpic = { name: '', color: '#6366f1', description: '', showInGeneral: false }; showEpicModal = true"
                    class="flex items-center gap-2 px-3 py-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm font-medium rounded-lg transition-colors border border-gray-200 dark:border-gray-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                <span class="hidden sm:inline">{{ __('app.board_project') }}</span>
            </button>

        </div>
    </div>

    <!-- Kanban Columns Container -->
    <div class="overflow-x-hidden overflow-y-visible">
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
                    <div class="kanban-column flex-shrink-0 w-[calc(100vw-2rem)] sm:w-72 md:w-80 bg-gray-50/80 dark:bg-gray-800/50 rounded-xl flex flex-col border border-gray-200/50 dark:border-gray-700/50"
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
                                    @if(auth()->user()->canEdit('boards'))
                                    <div class="relative" x-data="{ colMenu: false }">
                                        <button type="button" @click.stop="colMenu = !colMenu"
                                                class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                            </svg>
                                        </button>
                                        <div x-show="colMenu" @click.away="colMenu = false" x-transition
                                             class="absolute right-0 top-8 z-50 w-40 bg-white dark:bg-gray-700 rounded-xl shadow-lg border border-gray-200 dark:border-gray-600 py-1">
                                            <button @click="openRenameColumnModal({{ $column->id }}, '{{ addslashes($column->name) }}'); colMenu = false"
                                                    class="w-full px-3 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                {{ __('app.board_rename') }}
                                            </button>
                                            @if(auth()->user()->canDelete('boards'))
                                            <button @click="deleteColumn({{ $column->id }}); colMenu = false"
                                                    class="w-full px-3 py-2 text-left text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                {{ __('app.delete') }}
                                            </button>
                                            @endif
                                        </div>
                                    </div>
                                    @endif
                                    <button type="button" @click="collapsed = !collapsed"
                                            class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                        <svg class="w-4 h-4 transition-transform" :class="collapsed ? '-rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                    @if(auth()->user()->canCreate('boards'))
                                    <button type="button" @click="openAddCardModal({{ $column->id }})"
                                            class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                    </button>
                                    @endif
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
                                                  style="background-color: {{ $card->epic->color }}30; color: {{ $card->epic->color }}">
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
                                                    {{ __('app.board_urgent_short') }}
                                                </span>
                                            @elseif($card->priority === 'high')
                                                <span class="priority-badge inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-orange-100 text-orange-700 dark:bg-orange-900/50 dark:text-orange-300 font-medium">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                                                    {{ __('app.board_high_short') }}
                                                </span>
                                            @elseif($card->priority === 'medium')
                                                <span class="priority-badge w-1.5 h-1.5 rounded-full bg-yellow-500" title="{{ __('app.board_medium') }}"></span>
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
                                            class="absolute top-2 right-2 p-1.5 rounded-lg sm:opacity-0 sm:group-hover:opacity-100 transition-all
                                                   {{ $card->is_completed ? 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-400 dark:bg-gray-600 hover:text-green-600 dark:hover:text-green-400' }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>

                        @if(auth()->user()->canCreate('boards'))
                        <!-- Quick Add -->
                        <div x-show="!collapsed" class="p-2 border-t border-gray-200/50 dark:border-gray-700/50">
                            <button type="button" @click="openAddCardModal({{ $column->id }})"
                                    class="w-full p-2 text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg text-sm transition-colors flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span>{{ __('app.board_add_task') }}</span>
                            </button>
                        </div>
                        @endif
                    </div>
                @endforeach

                @if(auth()->user()->canCreate('boards'))
                <div class="flex-shrink-0 w-[calc(100vw-2rem)] sm:w-72 md:w-80">
                    <button @click="openAddColumnModal()"
                            class="w-full p-4 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-400 hover:border-gray-400 dark:hover:border-gray-500 transition-colors flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span class="text-sm font-medium">{{ __('app.board_add_column') }}</span>
                    </button>
                </div>
                @endif
            </div>
        </div>

    <!-- Card Slide-Over Panel -->
    @include('boards._card_panel')

    <!-- Add Card Modal -->
    @if(auth()->user()->canCreate('boards'))
    @include('boards._add_card_modal')
    @endif

    <!-- Epic Modal -->
    @include('boards._epic_modal')

    <!-- Keyboard Shortcuts Modal -->
    @include('boards._shortcuts_modal')

    <!-- Add Column Modal -->
    <div x-show="addColumnModal.open" x-cloak
         x-transition:enter="transition-opacity ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/25" @click="addColumnModal.open = false"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 w-full max-w-sm mx-4 p-6"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             @click.stop>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4" x-text="addColumnModal.renameId ? @js(__('app.board_rename_column')) : @js(__('app.board_add_column'))"></h3>
            <input x-ref="columnNameInput" x-model="addColumnModal.name" type="text"
                   placeholder="{{ __('app.board_column_name_placeholder') }}"
                   @keydown.enter="addColumnModal.renameId ? renameColumn(addColumnModal.renameId, addColumnModal.name) : addColumn(); addColumnModal.open = false"
                   @keydown.escape="addColumnModal.open = false"
                   class="w-full px-4 py-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            <div class="flex justify-end gap-2 mt-4">
                <button @click="addColumnModal.open = false" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">{{ __('common.cancel') }}</button>
                <button @click="addColumnModal.renameId ? renameColumn(addColumnModal.renameId, addColumnModal.name) : addColumn(); addColumnModal.open = false" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition-colors" x-text="addColumnModal.renameId ? @js(__('common.save')) : @js(__('common.create'))"></button>
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
// File objects stored outside Alpine to avoid Proxy wrapping
let _pendingCommentFiles = [];
let _editCommentFiles = [];

function churchBoard() {
    const _savedBoardFilters = filterStorage.load('boards_kanban', {
        filters: { priority: '', assignee: '', ministry: '', epic: '' },
        searchQuery: '',
        activePreset: ''
    });
    return {
        canCreateCards: @json(auth()->user()->canCreate('boards')),
        canEditCards: @json(auth()->user()->canEdit('boards')),
        canDeleteCards: @json(auth()->user()->canDelete('boards')),
        filters: _savedBoardFilters.filters,
        searchQuery: _savedBoardFilters.searchQuery,
        activePreset: _savedBoardFilters.activePreset,
        cardPanel: {
            open: false,
            loading: false,
            data: null,
            cardId: null,
            error: null
        },
        addColumnModal: { open: false, name: '' },
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
        editingEpic: null,
        epicModalLoading: false,
        newEpic: { name: '', color: '#6366f1', description: '', showInGeneral: false },
        cards: @json($board->columns->flatMap->cards->keyBy('id')),
        allCards: @json($allCardsData),
        epics: @json($epicsData),
        peopleList: @json($people->map(fn($p) => ['id' => $p->id, 'name' => $p->full_name, 'photo' => $p->photo ? Storage::url($p->photo) : null])),
        ministriesList: @json($ministries->map(fn($m) => ['id' => $m->id, 'name' => $m->name])),
        filterLabels: {
            priority: { urgent: @js(__('app.board_urgent')), high: @js(__('app.board_high')), medium: @js(__('app.board_medium')), low: @js(__('app.board_low')) }
        },
        showShortcuts: false,
        myPersonId: {{ auth()->user()->person?->id ?? 'null' }},
        boardId: {{ $board->id }},
        boardMinistryId: {{ $board->ministry_id ?? 'null' }},
        csrfToken: document.querySelector('meta[name="csrf-token"]').content,
        selectedCardIndex: -1,
        commentText: '',
        commentFileNames: [],
        editFileNames: [],
        lightboxUrl: null,

        get hasActiveFilters() {
            return this.filters.priority || this.filters.assignee || this.filters.ministry || this.filters.epic || this.searchQuery;
        },

        get activeFilterCount() {
            return [this.filters.priority, this.filters.assignee, this.filters.ministry, this.filters.epic].filter(Boolean).length;
        },

        getAssigneeLabel(val) {
            if (val === 'me') return @js(__('app.board_my_short'));
            if (val === 'unassigned') return @js(__('app.board_unassigned'));
            const p = this.peopleList.find(p => p.id == val);
            return p ? p.name : val;
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
            this.$watch('filters', () => { this.applyFilters(); this.activePreset = ''; this._saveBoardFilters(); }, { deep: true });
            this.$watch('searchQuery', () => { this.applyFilters(); this._saveBoardFilters(); });
            this.$watch('activePreset', () => this._saveBoardFilters());

            // Apply restored filters on load
            if (this.searchQuery || this.filters.priority || this.filters.assignee || this.filters.ministry || this.filters.epic || this.activePreset) {
                this.$nextTick(() => this.applyFilters());
            }

            // Expose methods globally for dynamically generated card HTML
            window.boardOpenCard = (id) => this.openCard(id);
            window.boardToggleComplete = (id) => this.toggleComplete(id);

            // Open card from URL
            const urlParams = new URLSearchParams(window.location.search);
            const cardId = urlParams.get('card');
            if (cardId) {
                this.openCard(parseInt(cardId));
                window.history.replaceState({}, '', window.location.pathname);
            }
        },

        _saveBoardFilters() {
            filterStorage.save('boards_kanban', {
                filters: this.filters,
                searchQuery: this.searchQuery,
                activePreset: this.activePreset,
            });
        },

        handleKeydown(e) {
            // Skip if kanban is hidden (e.g., inactive tab)
            if (this.$el.offsetHeight === 0) return;

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
                    if (this.showShortcuts) { this.showShortcuts = false; }
                    else if (this.showEpicModal) { this.showEpicModal = false; this.editingEpic = null; }
                    else if (this.addCardModal.open) { this.addCardModal.open = false; }
                    else if (this.cardPanel.open) { this.closePanel(); }
                    break;
                case 'n':
                case 'N':
                    if (!e.metaKey && !e.ctrlKey && this.canCreateCards) {
                        e.preventDefault();
                        this.openAddCardModal();
                    }
                    break;
                case 'c':
                case 'C':
                    if (this.cardPanel.open && this.cardPanel.data && this.canEditCards) {
                        e.preventDefault();
                        this.toggleCardComplete();
                    }
                    break;
                case 'm':
                case 'M':
                    if (this.cardPanel.open && this.canEditCards) {
                        e.preventDefault();
                        const statusSelect = document.querySelector('[x-model="cardPanel.data.card.column_id"]');
                        if (statusSelect) statusSelect.focus();
                    }
                    break;
            }
        },

        initSortable() {
            if (!this.canEditCards) return;
            if (typeof Sortable === 'undefined') {
                // Sortable CDN may not have loaded yet — retry shortly
                setTimeout(() => this.initSortable(), 100);
                return;
            }
            document.querySelectorAll('.kanban-cards').forEach(container => {
                if (container._sortable) container._sortable.destroy();
                container._sortable = new Sortable(container, {
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
                el.textContent = counts[columnId] || 0;
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
                ministryId: this.boardMinistryId ? String(this.boardMinistryId) : '',
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
                    const data = await response.json().catch(() => ({}));
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
                        if (window.showGlobalToast) showGlobalToast(@js(__('app.board_task_created')), 'success');
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
                     onclick="boardOpenCard(${card.id})">
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
                    <button onclick="event.stopPropagation(); boardToggleComplete(${card.id})"
                            class="absolute top-2 right-2 p-1.5 rounded-lg sm:opacity-0 sm:group-hover:opacity-100 transition-all bg-gray-100 text-gray-400 dark:bg-gray-600 hover:text-green-600 dark:hover:text-green-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </button>
                </div>`;
        },

        getPriorityBadgeHtml(priority) {
            const badges = {
                'urgent': '<span class="priority-badge inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300 font-medium"><span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>' + @js(__('app.board_urgent_short')) + '</span>',
                'high': '<span class="priority-badge inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-orange-100 text-orange-700 dark:bg-orange-900/50 dark:text-orange-300 font-medium"><span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>' + @js(__('app.board_high_short')) + '</span>',
                'medium': '<span class="priority-badge w-1.5 h-1.5 rounded-full bg-yellow-500" title="' + @js(__('app.board_medium')) + '"></span>'
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
            this.cardPanel.error = null;
            this.resetCommentForm();

            try {
                const response = await fetch(`/boards/cards/${cardId}`, {
                    headers: { 'Accept': 'application/json' }
                });

                if (!response.ok) {
                    this.cardPanel.error = response.status === 403
                        ? @js(__('app.board_no_access'))
                        : @js(__('app.board_error_loading_task'));
                    return;
                }

                const data = await response.json();

                // Fix due_date: ISO timestamp → YYYY-MM-DD for <input type="date">
                if (data.card && data.card.due_date) {
                    data.card.due_date = data.card.due_date.substring(0, 10);
                }

                // Normalize null epic_id to empty string for <select> matching
                if (data.card && data.card.epic_id == null) {
                    data.card.epic_id = '';
                }

                this.cardPanel.data = data;
            } catch (e) {
                console.error('Error loading card:', e);
                this.cardPanel.error = @js(__('app.board_error_loading_task'));
            } finally {
                this.cardPanel.loading = false;

                // Re-trigger reactivity after x-for renders <option> elements
                this.$nextTick(() => {
                    if (this.cardPanel.data) {
                        this.cardPanel.data = {...this.cardPanel.data};
                    }
                });
            }
        },

        closePanel() {
            this.cardPanel.open = false;
            this.cardPanel.data = null;
            this.cardPanel.cardId = null;
            this.cardPanel.error = null;
            this.resetCommentForm();
        },

        async saveCardField(field, value) {
            if (!this.cardPanel.data) return;

            const cardId = this.cardPanel.data.card.id;
            const card = this.cardPanel.data.card;
            const data = {
                title: card.title,
                description: card.description,
                priority: card.priority,
                due_date: card.due_date,
                assigned_to: card.assigned_to,
                epic_id: card.epic_id || null,
                column_id: card.column_id,
                [field]: value === '' ? null : value
            };

            try {
            const resp = await fetch(`/boards/cards/${cardId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });
            if (!resp.ok) { console.error('Save failed:', resp.status); return; }

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
            } catch (e) { console.error('Save field error:', e); }
        },

        updateColumnCount(columnId) {
            const column = document.querySelector(`.kanban-cards[data-column-id="${columnId}"]`);
            const countEl = document.querySelector(`.column-count[data-column-id="${columnId}"]`);
            if (column && countEl) {
                countEl.textContent = column.querySelectorAll('.kanban-card:not([style*="display: none"])').length;
            }
        },

        openAddColumnModal() {
            this.addColumnModal = { open: true, name: '' };
            this.$nextTick(() => this.$refs.columnNameInput?.focus());
        },
        async addColumn() {
            const name = this.addColumnModal.name;
            if (!name || !name.trim()) return;
            this.addColumnModal.open = false;
            try {
                const response = await fetch(`/boards/{{ $board->id }}/columns`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': this.csrfToken, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ name: name.trim() })
                });
                const data = await response.json().catch(() => ({}));
                if (response.ok && data.column) {
                    this.insertColumnDOM(data.column);
                    if (window.showGlobalToast) showGlobalToast(data.message || @js(__('app.board_column_added')), 'success');
                } else {
                    if (window.showGlobalToast) showGlobalToast(data.message || @js(__('app.board_error')), 'error');
                }
            } catch (e) { console.error(e); }
        },

        insertColumnDOM(col) {
            const colorMap = {
                gray: { dot: 'bg-gray-400' }, blue: { dot: 'bg-blue-500' },
                yellow: { dot: 'bg-amber-500' }, green: { dot: 'bg-green-500' },
                red: { dot: 'bg-red-500' }, purple: { dot: 'bg-purple-500' },
            };
            const colors = colorMap[col.color] || colorMap.gray;
            const colId = col.id;
            const colName = col.name.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');

            const html = `<div class="kanban-column flex-shrink-0 w-[calc(100vw-2rem)] sm:w-72 md:w-80 bg-gray-50/80 dark:bg-gray-800/50 rounded-xl flex flex-col border border-gray-200/50 dark:border-gray-700/50"
                 data-column-id="${colId}" x-data="{ collapsed: false }">
                <div class="relative">
                    <div class="absolute top-0 left-0 right-0 h-1 rounded-t-xl ${colors.dot}"></div>
                    <div class="p-3 pt-4 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full ${colors.dot}"></div>
                            <h3 class="font-semibold text-gray-800 dark:text-white text-sm">${colName}</h3>
                            <span class="column-count text-xs text-gray-400 dark:text-gray-500 bg-gray-200/50 dark:bg-gray-700/50 px-2 py-0.5 rounded-full font-medium"
                                  data-column-id="${colId}">0</span>
                        </div>
                        <div class="flex items-center gap-1">
                            ${this.canEditCards ? `<div class="relative" x-data="{ colMenu: false }">
                                <button type="button" @click.stop="colMenu = !colMenu"
                                        class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                    </svg>
                                </button>
                                <div x-show="colMenu" @click.away="colMenu = false" x-transition
                                     class="absolute right-0 top-8 z-50 w-40 bg-white dark:bg-gray-700 rounded-xl shadow-lg border border-gray-200 dark:border-gray-600 py-1">
                                    <button @click="openRenameColumnModal(${colId}, '${colName.replace(/'/g, "\\'")}'); colMenu = false"
                                            class="w-full px-3 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        ${@json(__('app.board_rename'))}
                                    </button>
                                    ${this.canDeleteCards ? `<button @click="deleteColumn(${colId}); colMenu = false"
                                            class="w-full px-3 py-2 text-left text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        ${@json(__('app.delete'))}
                                    </button>` : ''}
                                </div>
                            </div>` : ''}
                            <button type="button" @click="collapsed = !collapsed"
                                    class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                <svg class="w-4 h-4 transition-transform" :class="collapsed ? '-rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            ${this.canCreateCards ? `<button type="button" @click="openAddCardModal(${colId})"
                                    class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </button>` : ''}
                        </div>
                    </div>
                </div>
                <div x-show="!collapsed" x-collapse
                     class="flex-1 p-2 space-y-2 min-h-[80px] kanban-cards overflow-y-auto max-h-[50vh] lg:max-h-[calc(100vh-300px)]"
                     data-column-id="${colId}">
                </div>
            </div>`;

            const container = document.getElementById('kanban-columns');
            // Insert before the "Add Column" button wrapper (last child of container)
            const addBtn = container.querySelector(':scope > div:last-child');
            const temp = document.createElement('div');
            temp.innerHTML = html;
            const newCol = temp.firstElementChild;
            if (addBtn) {
                container.insertBefore(newCol, addBtn);
            } else {
                container.appendChild(newCol);
            }
            // Initialize Alpine on the new column
            Alpine.initTree(newCol);
            // Re-init sortable so the new column's cards container is drag-enabled
            this.initSortable();
            // Update addCardModal default column
            this.addCardModal.columnId = colId;
        },

        openRenameColumnModal(columnId, currentName) {
            this.addColumnModal = { open: true, name: currentName, renameId: columnId };
            this.$nextTick(() => this.$refs.columnNameInput?.focus());
        },
        async renameColumn(columnId, currentName) {
            const name = currentName;
            if (!name || !name.trim()) return;
            try {
                const response = await fetch(`/boards/columns/${columnId}`, {
                    method: 'PUT',
                    headers: { 'X-CSRF-TOKEN': this.csrfToken, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ name: name.trim() })
                });
                if (response.ok) {
                    const header = document.querySelector(`.kanban-column[data-column-id="${columnId}"] h3`);
                    if (header) header.textContent = name.trim();
                    if (window.showGlobalToast) showGlobalToast(@js(__('app.board_column_renamed')), 'success');
                } else { const data = await response.json().catch(() => ({})); if (window.showGlobalToast) showGlobalToast(data.message || @js(__('app.board_error')), 'error'); }
            } catch (e) { console.error(e); }
        },

        async deleteColumn(columnId) {
            if (!await confirmDialog(@js(__('app.board_delete_column_confirm')))) return;
            try {
                const response = await fetch(`/boards/columns/${columnId}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': this.csrfToken, 'Accept': 'application/json' }
                });
                if (response.ok) {
                    document.querySelector(`.kanban-column[data-column-id="${columnId}"]`)?.remove();
                    if (window.showGlobalToast) showGlobalToast(@js(__('app.board_column_deleted')), 'success');
                } else { const data = await response.json().catch(() => ({})); if (window.showGlobalToast) showGlobalToast(data.message || @js(__('app.board_cannot_delete_column_with_cards')), 'error'); }
            } catch (e) { console.error(e); }
        },

        async toggleComplete(cardId) {
            try {
            const response = await fetch(`/boards/cards/${cardId}/toggle`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) return;
            const result = await response.json().catch(() => ({}));

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
            } catch (e) { console.error('Toggle error:', e); }
        },

        async toggleCardComplete() {
            if (!this.cardPanel.data) return;
            await this.toggleComplete(this.cardPanel.data.card.id);
        },

        async deleteComment(comment) {
            if (!await confirmDialog(@js( __('messages.confirm_delete_comment') ))) return;

            const cardId = this.cardPanel.data.card.id;
            try {
                await fetch(`/boards/comments/${comment.id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': this.csrfToken, 'Accept': 'application/json' }
                });

                this.cardPanel.data.comments = this.cardPanel.data.comments.filter(c => c.id !== comment.id);
                this.updateCardCommentCount(cardId, this.cardPanel.data.comments.length);
            } catch (e) { console.error('Delete comment error:', e); }
        },

        onEditFilesChange(e) {
            const files = Array.from(e.target.files);
            if (files.length === 0) return;
            _editCommentFiles = [..._editCommentFiles, ...files];
            this.editFileNames = [...(this.editFileNames || []), ...files.map(f => f.name)];
            e.target.value = '';
        },

        removeEditFile(idx) {
            _editCommentFiles = _editCommentFiles.filter((_, i) => i !== idx);
            this.editFileNames = this.editFileNames.filter((_, i) => i !== idx);
        },

        async updateComment(comment, newContent) {
            const content = (newContent || '').trim();
            const files = [..._editCommentFiles];

            if (!content && files.length === 0) return;

            _editCommentFiles = [];
            this.editFileNames = [];

            try {
                let response;
                if (files.length > 0) {
                    const formData = new FormData();
                    formData.append('_method', 'PUT');
                    if (content) formData.append('content', content);
                    files.forEach((file, idx) => { formData.append(`files[${idx}]`, file); });

                    response = await fetch(`/boards/comments/${comment.id}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Accept': 'application/json'
                        },
                        body: formData
                    });
                } else {
                    response = await fetch(`/boards/comments/${comment.id}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ content: content })
                    });
                }

                const result = await response.json().catch(() => ({}));
                if (result.success) {
                    comment.content = content;
                    comment.is_edited = true;
                    if (result.attachments !== undefined) {
                        comment.attachments = result.attachments;
                    }
                }
            } catch (e) {
                console.error('Update comment error:', e);
            }
        },

        async deleteCommentAttachment(comment, attIndex) {
            if (!await confirmDialog(@js(__('messages.confirm_delete_attachment')))) return;
            try {
                const response = await fetch(`/boards/comments/${comment.id}/attachments/${attIndex}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    if (window.showGlobalToast) showGlobalToast(@js(__('app.board_error_deleting')), 'error');
                    return;
                }

                const result = await response.json().catch(() => ({}));
                if (result.success) {
                    comment.attachments = result.attachments;
                }
            } catch (e) {
                console.error('Delete comment attachment error:', e);
            }
        },

        async addChecklistItem(title) {
            if (!title.trim() || !this.cardPanel.data) return;

            const cardId = this.cardPanel.data.card.id;
            try {
                const response = await fetch(`/boards/cards/${cardId}/checklist`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ title })
                });

                if (!response.ok) return;
                const result = await response.json().catch(() => ({}));
                if (result.success) {
                    this.cardPanel.data.checklist.push(result.item);
                    this.updateCardChecklistCount(cardId);
                }
            } catch (e) { console.error('Checklist error:', e); }
        },

        async toggleChecklistItem(item) {
            const cardId = this.cardPanel.data.card.id;
            try {
                await fetch(`/boards/cards/checklist/${item.id}/toggle`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': this.csrfToken, 'Accept': 'application/json' }
                });
                item.is_completed = !item.is_completed;
                this.updateCardChecklistCount(cardId);
            } catch (e) { console.error('Toggle checklist error:', e); }
        },

        async deleteChecklistItem(item) {
            if (!await confirmDialog(@js(__('messages.confirm_delete_checklist_item')))) return;
            const cardId = this.cardPanel.data.card.id;
            try {
                await fetch(`/boards/cards/checklist/${item.id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': this.csrfToken, 'Accept': 'application/json' }
                });
                this.cardPanel.data.checklist = this.cardPanel.data.checklist.filter(i => i.id !== item.id);
                this.updateCardChecklistCount(cardId);
            } catch (e) { console.error('Delete checklist error:', e); }
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
            try {
                const response = await fetch(`/boards/cards/${cardId}/attachments`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                if (!response.ok) {
                    if (window.showGlobalToast) showGlobalToast(@js(__('app.board_error_uploading_file')), 'error');
                    return;
                }
                const result = await response.json().catch(() => ({}));
                if (result.success) {
                    this.cardPanel.data.attachments.push(result.attachment);
                    if (window.showGlobalToast) showGlobalToast(@js(__('app.board_file_uploaded')), 'success');
                }
            } catch (e) {
                console.error('Upload error:', e);
                if (window.showGlobalToast) showGlobalToast(@js(__('app.board_error_uploading')), 'error');
            }

            event.target.value = '';
        },

        async uploadAttachments(event) {
            const files = event.target.files;
            if (!files || files.length === 0 || !this.cardPanel.data) return;

            const cardId = this.cardPanel.data.card.id;
            let uploaded = 0;

            for (const file of files) {
                const formData = new FormData();
                formData.append('file', file);
                try {
                    const response = await fetch(`/boards/cards/${cardId}/attachments`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Accept': 'application/json'
                        },
                        body: formData
                    });
                    if (response.ok) {
                        const result = await response.json().catch(() => ({}));
                        if (result.success) {
                            this.cardPanel.data.attachments.push(result.attachment);
                            uploaded++;
                        }
                    }
                } catch (e) { console.error('Upload error:', e); }
            }

            if (uploaded > 0 && window.showGlobalToast) {
                showGlobalToast(@js(__('app.board_uploaded_files')).replace(':count', uploaded), 'success');
            }
            event.target.value = '';
        },

        async deleteAttachment(file) {
            if (!await confirmDialog(@js( __('messages.confirm_delete_file') ))) return;

            try {
                await fetch(`/boards/attachments/${file.id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': this.csrfToken, 'Accept': 'application/json' }
                });
                this.cardPanel.data.attachments = this.cardPanel.data.attachments.filter(a => a.id !== file.id);
            } catch (e) { console.error('Delete attachment error:', e); }
        },

        async addRelatedCard(relatedCardId) {
            if (!relatedCardId || !this.cardPanel.data) return;

            const cardId = this.cardPanel.data.card.id;
            try {
                const response = await fetch(`/boards/cards/${cardId}/related`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ related_card_id: relatedCardId })
                });

                if (!response.ok) return;
                const result = await response.json().catch(() => ({}));
                if (result.success) {
                    this.cardPanel.data.related_cards.push(result.related_card);
                    this.cardPanel.data.available_cards = this.cardPanel.data.available_cards.filter(c => c.id != relatedCardId);
                }
            } catch (e) { console.error('Add related error:', e); }
        },

        async removeRelatedCard(related) {
            if (!await confirmDialog(@js(__('messages.confirm_remove_related_card')))) return;
            const cardId = this.cardPanel.data.card.id;
            try {
                await fetch(`/boards/cards/${cardId}/related/${related.id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': this.csrfToken, 'Accept': 'application/json' }
                });
                this.cardPanel.data.related_cards = this.cardPanel.data.related_cards.filter(r => r.id !== related.id);
            } catch (e) { console.error('Remove related error:', e); }
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

                const result = await response.json().catch(() => ({}));
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
                    if (window.showGlobalToast) showGlobalToast(@js(__('app.board_task_duplicated')), 'success');
                }
            } catch (error) {
                console.error('Duplicate error:', error);
            }
        },

        // --- Comment with files (file objects stored in _pendingCommentFiles outside Alpine) ---

        onCommentFilesChange(e) {
            const files = Array.from(e.target.files);
            if (files.length === 0) return;
            _pendingCommentFiles = [..._pendingCommentFiles, ...files];
            this.commentFileNames = [...this.commentFileNames, ...files.map(f => f.name)];
            e.target.value = '';
        },

        removeCommentFile(idx) {
            _pendingCommentFiles = _pendingCommentFiles.filter((_, i) => i !== idx);
            this.commentFileNames = this.commentFileNames.filter((_, i) => i !== idx);
        },

        resetCommentForm() {
            this.commentText = '';
            this.commentFileNames = [];
            _pendingCommentFiles = [];
        },

        async submitComment() {
            const content = (this.commentText || '').trim();
            const files = [..._pendingCommentFiles]; // copy before clearing

            if (!content && files.length === 0) return;
            if (!this.cardPanel.data) return;

            const cardId = this.cardPanel.data.card.id;

            // Clear form immediately
            this.resetCommentForm();

            const formData = new FormData();
            if (content) {
                formData.append('content', content);
            }
            files.forEach((file, idx) => {
                formData.append(`files[${idx}]`, file);
            });

            try {
                const response = await fetch(`/boards/cards/${cardId}/comments`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                if (!response.ok) {
                    const errText = await response.text();
                    console.error('Comment error:', response.status, errText);
                    if (window.showGlobalToast) showGlobalToast(@js(__('app.board_error_adding_comment')), 'error');
                    return;
                }

                const result = await response.json().catch(() => ({}));
                if (result.success) {
                    this.cardPanel.data.comments.unshift(result.comment);
                    this.updateCardCommentCount(cardId, this.cardPanel.data.comments.length);
                }
            } catch (e) {
                console.error('Comment error:', e);
                if (window.showGlobalToast) showGlobalToast(@js(__('app.board_error_prefix')) + e.message, 'error');
            }
        },

        async deleteCard(cardId) {
            if (!await confirmDialog(@js( __('messages.confirm_delete_task') ))) return;

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

                    if (window.showGlobalToast) showGlobalToast(@js(__('app.board_task_deleted')), 'success');
                }
            } catch (error) {
                console.error('Delete error:', error);
            }
        },

        openEditEpic(epic) {
            this.editingEpic = epic.id;
            this.newEpic = { name: epic.name, color: epic.color, description: epic.description || '', showInGeneral: epic.show_in_general || false };
            this.showEpicModal = true;
        },

        async createEpic() {
            if (!this.newEpic.name.trim()) return;

            this.epicModalLoading = true;
            try {
                const response = await fetch(`/boards/${this.boardId}/epics`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        name: this.newEpic.name,
                        color: this.newEpic.color,
                        description: this.newEpic.description,
                        show_in_general: this.newEpic.showInGeneral ? 1 : 0,
                    })
                });

                if (!response.ok) {
                    console.error(`HTTP ${response.status}: ${response.statusText}`);
                    const errorText = await response.text();
                    console.error('Response body:', errorText);
                    if (window.showGlobalToast) showGlobalToast(@js(__('app.board_error_creating_project')), 'error');
                    return;
                }

                const result = await response.json().catch(() => ({}));

                if (result.success && result.epic) {
                    const newEpic = {
                        id: result.epic.id,
                        name: result.epic.name,
                        color: result.epic.color,
                        description: result.epic.description,
                        show_in_general: result.epic.show_in_general,
                        total: 0,
                        completed: 0,
                        progress: 0
                    };
                    this.epics.push(newEpic);

                    this.showEpicModal = false;
                    this.editingEpic = null;
                    this.newEpic = { name: '', color: '#6366f1', description: '', showInGeneral: false };

                    if (window.showGlobalToast) showGlobalToast(@js(__('app.board_project_created')), 'success');
                } else {
                    console.error('No success in response:', result);
                }
            } catch (error) {
                console.error('createEpic error:', error);
                if (window.showGlobalToast) showGlobalToast(@js(__('app.board_error_prefix')) + error.message, 'error');
            } finally {
                this.epicModalLoading = false;
            }
        },

        async updateEpic() {
            if (!this.newEpic.name.trim() || !this.editingEpic) return;

            this.epicModalLoading = true;
            try {
                const response = await fetch(`/boards/epics/${this.editingEpic}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        name: this.newEpic.name,
                        color: this.newEpic.color,
                        description: this.newEpic.description,
                        show_in_general: this.newEpic.showInGeneral ? 1 : 0,
                    })
                });

                if (!response.ok) {
                    console.error(`HTTP ${response.status}: ${response.statusText}`);
                    if (window.showGlobalToast) showGlobalToast(@js(__('app.board_error_updating_project')), 'error');
                    return;
                }

                const result = await response.json().catch(() => ({}));

                if (result.success && result.epic) {
                    // Update in epics array
                    const idx = this.epics.findIndex(e => e.id === this.editingEpic);
                    if (idx !== -1) {
                        this.epics[idx].name = result.epic.name;
                        this.epics[idx].color = result.epic.color;
                        this.epics[idx].description = result.epic.description;
                        this.epics[idx].show_in_general = result.epic.show_in_general;
                    }

                    // Update epic badges on cards in DOM
                    document.querySelectorAll(`[data-epic="${this.editingEpic}"]`).forEach(cardEl => {
                        const topRow = cardEl.querySelector('.card-top-row');
                        if (topRow) {
                            const badge = topRow.querySelector('.epic-badge');
                            if (badge) badge.remove();
                            topRow.insertAdjacentHTML('afterbegin', this.getEpicBadgeHtml(result.epic));
                        }
                    });

                    // Update allCards search data
                    this.allCards.forEach(c => {
                        if (c.epicId == this.editingEpic) {
                            c.epicName = result.epic.name;
                            c.epicColor = result.epic.color;
                        }
                    });

                    this.showEpicModal = false;
                    this.editingEpic = null;
                    this.newEpic = { name: '', color: '#6366f1', description: '', showInGeneral: false };

                    if (window.showGlobalToast) showGlobalToast(@js(__('app.board_project_updated')), 'success');
                } else {
                    console.error('No success in response:', result);
                }
            } catch (error) {
                console.error('updateEpic error:', error);
                if (window.showGlobalToast) showGlobalToast(@js(__('app.board_error_prefix')) + error.message, 'error');
            } finally {
                this.epicModalLoading = false;
            }
        },

        async deleteEpic(epic) {
            if (!await confirmDialog(@js( __('messages.confirm_delete_project') ).replace(':name', epic.name))) return;

            try {
                const response = await fetch(`/boards/epics/${epic.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    // Remove from epics array
                    this.epics = this.epics.filter(e => e.id !== epic.id);

                    // Reset filter if this epic was active
                    if (this.filters.epic == String(epic.id)) {
                        this.filters.epic = '';
                    }

                    // Remove epic badges from cards
                    document.querySelectorAll(`[data-epic="${epic.id}"]`).forEach(cardEl => {
                        cardEl.dataset.epic = '';
                        const badge = cardEl.querySelector('.epic-badge');
                        if (badge) badge.remove();
                    });

                    // Update allCards
                    this.allCards.forEach(c => {
                        if (c.epicId == epic.id) {
                            c.epicId = null;
                            c.epicName = null;
                            c.epicColor = null;
                        }
                    });

                    if (window.showGlobalToast) showGlobalToast(@js(__('app.board_project_deleted')), 'success');
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
.kanban-card.sortable-ghost { opacity: 0.25; border: 2px dashed rgba(99, 102, 241, 0.5) !important; background: rgba(99, 102, 241, 0.05); }
.kanban-card.sortable-chosen { transform: rotate(2deg) scale(1.02); box-shadow: 0 8px 24px rgba(0,0,0,0.12); }
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.scrollbar-thin::-webkit-scrollbar { width: 6px; height: 6px; }
.scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
.scrollbar-thin::-webkit-scrollbar-thumb { background: rgba(156, 163, 175, 0.3); border-radius: 3px; }
.scrollbar-thin::-webkit-scrollbar-thumb:hover { background: rgba(156, 163, 175, 0.5); }
</style>
