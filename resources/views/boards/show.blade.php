@extends('layouts.app')

@section('title', $board->name)

@section('actions')
<div class="flex items-center gap-2">
    <a href="{{ route('boards.edit', $board) }}"
       class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
    </a>
</div>
@endsection

@section('content')
<div class="h-full -mt-2" x-data="kanbanBoard()" x-init="init()">
    <!-- Board Header -->
    <div class="flex items-center justify-between mb-4 flex-wrap gap-4">
        <div class="flex items-center gap-4">
            <div class="w-1.5 h-10 rounded-full" style="background-color: {{ $board->color }}"></div>
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $board->name }}</h1>
                @if($board->description)
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $board->description }}</p>
                @endif
            </div>
        </div>

        <!-- Board Stats & Filters -->
        <div class="flex items-center gap-3">
            <!-- Search -->
            <div class="relative" x-show="showSearch" x-transition>
                <input type="text" x-ref="searchInput" x-model="searchQuery"
                       @keydown.escape="showSearch = false; searchQuery = ''"
                       placeholder="Пошук карток..."
                       class="w-48 sm:w-64 px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-sm dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                <button @click="showSearch = false; searchQuery = ''"
                        class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Search trigger -->
            <button x-show="!showSearch" @click="showSearch = true; $nextTick(() => $refs.searchInput?.focus())"
                    class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                    title="Пошук (/)">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </button>

            <!-- Shortcuts help -->
            <button @click="showShortcuts = true"
                    class="hidden sm:flex p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                    title="Шорткати (?)">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </button>

            <!-- Filter -->
            <div class="relative" x-data="{ filterOpen: false }">
                <button @click="filterOpen = !filterOpen"
                        class="flex items-center gap-2 px-3 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-sm text-gray-600 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <span>Фільтр</span>
                    <template x-if="activeFilters > 0">
                        <span class="w-5 h-5 bg-primary-500 text-white text-xs rounded-full flex items-center justify-center" x-text="activeFilters"></span>
                    </template>
                </button>

                <div x-show="filterOpen" x-cloak @click.away="filterOpen = false"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="absolute right-0 mt-2 w-64 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 z-30 p-4">
                    <h4 class="font-medium text-gray-900 dark:text-white text-sm mb-3">Фільтри</h4>

                    <div class="space-y-3">
                        <div>
                            <label class="text-xs text-gray-500 dark:text-gray-400 mb-1 block">Пріоритет</label>
                            <select x-model="filters.priority" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border-0 rounded-lg text-sm dark:text-white">
                                <option value="">Всі</option>
                                <option value="urgent">Терміновий</option>
                                <option value="high">Високий</option>
                                <option value="medium">Середній</option>
                                <option value="low">Низький</option>
                            </select>
                        </div>

                        <div>
                            <label class="text-xs text-gray-500 dark:text-gray-400 mb-1 block">Відповідальний</label>
                            <select x-model="filters.assignee" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border-0 rounded-lg text-sm dark:text-white">
                                <option value="">Всі</option>
                                <option value="unassigned">Не призначено</option>
                                @foreach($people as $person)
                                    <option value="{{ $person->id }}">{{ $person->full_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="text-xs text-gray-500 dark:text-gray-400 mb-1 block">Дедлайн</label>
                            <select x-model="filters.dueDate" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border-0 rounded-lg text-sm dark:text-white">
                                <option value="">Всі</option>
                                <option value="overdue">Прострочені</option>
                                <option value="today">Сьогодні</option>
                                <option value="week">Цього тижня</option>
                                <option value="none">Без дедлайну</option>
                            </select>
                        </div>

                        <button @click="clearFilters()" class="w-full text-sm text-primary-600 dark:text-primary-400 hover:underline">
                            Скинути фільтри
                        </button>
                    </div>
                </div>
            </div>

            <!-- Progress -->
            <div class="hidden sm:flex items-center gap-3 px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg">
                <div class="w-32 h-2 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-500"
                         style="width: {{ $board->progress }}%; background-color: {{ $board->color }}"></div>
                </div>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $board->progress }}%</span>
            </div>
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
                        <div class="w-2.5 h-2.5 rounded-full ring-2 ring-offset-2 ring-offset-gray-50 dark:ring-offset-gray-800"
                             style="background-color: {{ $column->color === 'gray' ? '#9ca3af' : ($column->color === 'blue' ? '#3b82f6' : ($column->color === 'yellow' ? '#eab308' : ($column->color === 'green' ? '#22c55e' : '#9ca3af'))) }}; ring-color: {{ $column->color === 'gray' ? '#9ca3af' : ($column->color === 'blue' ? '#3b82f6' : ($column->color === 'yellow' ? '#eab308' : ($column->color === 'green' ? '#22c55e' : '#9ca3af'))) }}20;"></div>
                        <h3 class="font-semibold text-gray-800 dark:text-white text-sm">{{ $column->name }}</h3>
                        <span class="text-xs text-gray-400 dark:text-gray-500 bg-gray-200/50 dark:bg-gray-700/50 px-2 py-0.5 rounded-full font-medium">
                            {{ $column->cards->count() }}
                        </span>
                    </div>
                    <div class="flex items-center">
                        <button type="button" @click="showAddCard = {{ $column->id }}; $nextTick(() => $refs.cardInput{{ $column->id }}?.focus())"
                                class="p-1.5 text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Cards Container -->
                <div class="flex-1 p-2 space-y-2 min-h-[120px] kanban-cards overflow-y-auto max-h-[calc(100vh-320px)]"
                     data-column-id="{{ $column->id }}">
                    @foreach($column->cards as $card)
                        <div class="kanban-card group bg-white dark:bg-gray-700 rounded-xl shadow-sm border border-gray-100 dark:border-gray-600 p-3 cursor-pointer hover:shadow-md hover:border-primary-200 dark:hover:border-primary-700 transition-all duration-200"
                             draggable="true"
                             data-card-id="{{ $card->id }}"
                             data-priority="{{ $card->priority }}"
                             data-assignee="{{ $card->assigned_to ?? 'unassigned' }}"
                             data-due="{{ $card->due_date?->format('Y-m-d') ?? '' }}"
                             @click="openCard({{ $card->id }})">

                            <!-- Priority indicator -->
                            @if($card->priority === 'urgent' || $card->priority === 'high')
                                <div class="absolute -left-px top-3 bottom-3 w-1 rounded-r-full {{ $card->priority === 'urgent' ? 'bg-red-500' : 'bg-orange-500' }}"></div>
                            @endif

                            <!-- Labels -->
                            @if($card->labels && count($card->labels) > 0)
                                <div class="flex flex-wrap gap-1.5 mb-2">
                                    @foreach(array_slice($card->labels, 0, 3) as $label)
                                        <span class="h-1.5 w-8 rounded-full" style="background-color: {{ $label['color'] ?? '#6366f1' }}"></span>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Title -->
                            <p class="text-sm font-medium text-gray-800 dark:text-white leading-snug {{ $card->is_completed ? 'line-through text-gray-400 dark:text-gray-500' : '' }}">
                                {{ $card->title }}
                            </p>

                            <!-- Description preview -->
                            @if($card->description)
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">{{ Str::limit($card->description, 60) }}</p>
                            @endif

                            <!-- Meta info -->
                            <div class="mt-3 flex items-center justify-between">
                                <div class="flex items-center gap-2 flex-wrap">
                                    @if($card->due_date)
                                        <span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-lg font-medium
                                            {{ $card->isOverdue() ? 'bg-red-50 text-red-600 dark:bg-red-900/30 dark:text-red-400' :
                                               ($card->isDueSoon() ? 'bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400' :
                                               'bg-gray-100 text-gray-600 dark:bg-gray-600 dark:text-gray-300') }}">
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
                                            $allDone = $completed === $total;
                                        @endphp
                                        <span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-lg font-medium {{ $allDone ? 'bg-green-50 text-green-600 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-500 dark:bg-gray-600 dark:text-gray-400' }}">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                            </svg>
                                            {{ $completed }}/{{ $total }}
                                        </span>
                                    @endif

                                    @if($card->comments->count() > 0)
                                        <span class="inline-flex items-center gap-1 text-xs text-gray-400 dark:text-gray-500">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                            </svg>
                                            {{ $card->comments->count() }}
                                        </span>
                                    @endif
                                </div>

                                @if($card->assignee)
                                    <div class="flex-shrink-0">
                                        @if($card->assignee->photo)
                                            <img src="{{ Storage::url($card->assignee->photo) }}"
                                                 class="w-6 h-6 rounded-full object-cover ring-2 ring-white dark:ring-gray-700"
                                                 title="{{ $card->assignee->full_name }}">
                                        @else
                                            <div class="w-6 h-6 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center ring-2 ring-white dark:ring-gray-700"
                                                 title="{{ $card->assignee->full_name }}">
                                                <span class="text-white text-xs font-medium">
                                                    {{ mb_substr($card->assignee->first_name, 0, 1) }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <!-- Quick complete button -->
                            <button @click.stop="toggleComplete({{ $card->id }})"
                                    class="absolute top-2 right-2 p-1 rounded-lg opacity-0 group-hover:opacity-100 transition-all
                                           {{ $card->is_completed ? 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-400 dark:bg-gray-600 hover:text-green-600 dark:hover:text-green-400' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </button>
                        </div>
                    @endforeach
                </div>

                <!-- Add Card Form -->
                <div class="p-2" x-show="showAddCard === {{ $column->id }}" x-cloak>
                    <form method="POST" action="{{ route('boards.cards.store', $column) }}" class="space-y-2">
                        @csrf
                        <textarea name="title" rows="2" required placeholder="Введіть назву картки..."
                                  x-ref="cardInput{{ $column->id }}"
                                  @keydown.escape="showAddCard = null"
                                  @keydown.cmd.enter="$el.form.submit()"
                                  @keydown.ctrl.enter="$el.form.submit()"
                                  class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:text-white text-sm resize-none shadow-sm"></textarea>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <button type="submit"
                                        class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                                    Додати
                                </button>
                                <button type="button" @click="showAddCard = null"
                                        class="px-3 py-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 text-sm font-medium rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                    Скасувати
                                </button>
                            </div>
                            <span class="text-xs text-gray-400 dark:text-gray-500 hidden sm:inline">Cmd+Enter</span>
                        </div>
                    </form>
                </div>

                <!-- Quick Add Button (when form is hidden) -->
                <div class="p-2 border-t border-gray-200/50 dark:border-gray-700/50" x-show="showAddCard !== {{ $column->id }}">
                    <button type="button" @click="showAddCard = {{ $column->id }}; $nextTick(() => $refs.cardInput{{ $column->id }}?.focus())"
                            class="w-full p-2.5 text-gray-500 dark:text-gray-400 hover:bg-white dark:hover:bg-gray-700 hover:text-gray-700 dark:hover:text-gray-200 rounded-xl text-sm font-medium transition-all flex items-center justify-center gap-2 group">
                        <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Додати картку</span>
                    </button>
                </div>
            </div>
        @endforeach

        <!-- Add Column Button -->
        <div class="flex-shrink-0 w-72 sm:w-80" x-data="{ adding: false }">
            <template x-if="!adding">
                <button @click="adding = true; $nextTick(() => $refs.columnInput.focus())"
                        class="w-full h-full min-h-[200px] p-4 bg-gray-100/50 dark:bg-gray-800/30 hover:bg-gray-100 dark:hover:bg-gray-800/50 border-2 border-dashed border-gray-300 dark:border-gray-700 hover:border-primary-400 dark:hover:border-primary-600 rounded-2xl text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 font-medium transition-all flex flex-col items-center justify-center gap-3 group">
                    <div class="w-12 h-12 rounded-xl bg-gray-200 dark:bg-gray-700 group-hover:bg-primary-100 dark:group-hover:bg-primary-900/30 flex items-center justify-center transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>
                    <span>Додати колонку</span>
                </button>
            </template>

            <template x-if="adding">
                <form method="POST" action="{{ route('boards.columns.store', $board) }}"
                      class="bg-gray-50 dark:bg-gray-800/50 rounded-2xl p-4 border border-gray-200/50 dark:border-gray-700/50">
                    @csrf
                    <input type="text" name="name" required x-ref="columnInput" placeholder="Назва колонки..."
                           @keydown.escape="adding = false"
                           class="w-full px-4 py-3 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:text-white text-sm">
                    <div class="flex items-center gap-2 mt-3">
                        <button type="submit"
                                class="flex-1 px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-xl transition-colors">
                            Створити
                        </button>
                        <button type="button" @click="adding = false"
                                class="px-4 py-2.5 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-sm font-medium rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            Скасувати
                        </button>
                    </div>
                </form>
            </template>
        </div>
    </div>
</div>

<!-- Card Modal -->
<div x-show="cardModal.open" x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     @keydown.escape.window="cardModal.open = false">
    <div class="flex items-start justify-center min-h-screen px-4 pt-20 pb-10">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="cardModal.open = false"></div>

        <div x-show="cardModal.open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-2xl z-10">

            <template x-if="cardModal.card">
                <div>
                    <!-- Modal Header -->
                    <div class="flex items-start justify-between p-6 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex-1 pr-4">
                            <input type="text" x-model="cardModal.card.title"
                                   @blur="updateCard(cardModal.card.id, { title: cardModal.card.title })"
                                   class="text-xl font-bold text-gray-900 dark:text-white bg-transparent border-0 p-0 w-full focus:ring-0">
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                в колонці <span class="font-medium" x-text="cardModal.card.column_name"></span>
                            </p>
                        </div>
                        <button @click="cardModal.open = false" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 max-h-[60vh] overflow-y-auto">
                        <!-- Description -->
                        <div class="mb-6">
                            <label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                                </svg>
                                Опис
                            </label>
                            <textarea x-model="cardModal.card.description"
                                      @blur="updateCard(cardModal.card.id, { description: cardModal.card.description })"
                                      rows="3" placeholder="Додайте детальний опис..."
                                      class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white text-sm resize-none"></textarea>
                        </div>

                        <!-- Quick Actions Grid -->
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Пріоритет</label>
                                <select x-model="cardModal.card.priority"
                                        @change="updateCard(cardModal.card.id, { priority: cardModal.card.priority })"
                                        class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white text-sm">
                                    <option value="low">Низький</option>
                                    <option value="medium">Середній</option>
                                    <option value="high">Високий</option>
                                    <option value="urgent">Терміновий</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Дедлайн</label>
                                <input type="date" x-model="cardModal.card.due_date"
                                       @change="updateCard(cardModal.card.id, { due_date: cardModal.card.due_date })"
                                       class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white text-sm">
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="flex items-center justify-between p-6 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 rounded-b-2xl">
                        <a :href="`/boards/cards/${cardModal.card.id}`" class="text-sm text-primary-600 dark:text-primary-400 hover:underline font-medium">
                            Відкрити повністю
                        </a>
                        <div class="flex items-center gap-2">
                            <button @click="toggleComplete(cardModal.card.id); cardModal.open = false"
                                    class="px-4 py-2 text-sm font-medium rounded-lg transition-colors"
                                    :class="cardModal.card.is_completed ? 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200' : 'bg-green-100 text-green-700 hover:bg-green-200'">
                                <span x-text="cardModal.card.is_completed ? 'Повернути' : 'Завершити'"></span>
                            </button>
                            <button @click="deleteCard(cardModal.card.id)"
                                    class="px-4 py-2 bg-red-100 text-red-700 hover:bg-red-200 text-sm font-medium rounded-lg transition-colors">
                                Видалити
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

<!-- Keyboard Shortcuts Modal -->
<div x-show="showShortcuts" x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     @keydown.escape.window="showShortcuts = false">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="showShortcuts = false"></div>

        <div x-show="showShortcuts"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-lg z-10 p-6">

            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Клавіатурні скорочення</h3>
                <button @click="showShortcuts = false" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="space-y-4">
                <div>
                    <h4 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Навігація</h4>
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <span class="text-gray-700 dark:text-gray-300">Вгору/Вниз</span>
                            <div class="flex gap-1">
                                <kbd class="px-2 py-1 bg-gray-200 dark:bg-gray-600 rounded text-xs font-mono">↑</kbd>
                                <kbd class="px-2 py-1 bg-gray-200 dark:bg-gray-600 rounded text-xs font-mono">↓</kbd>
                            </div>
                        </div>
                        <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <span class="text-gray-700 dark:text-gray-300">Колонки</span>
                            <div class="flex gap-1">
                                <kbd class="px-2 py-1 bg-gray-200 dark:bg-gray-600 rounded text-xs font-mono">←</kbd>
                                <kbd class="px-2 py-1 bg-gray-200 dark:bg-gray-600 rounded text-xs font-mono">→</kbd>
                            </div>
                        </div>
                        <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <span class="text-gray-700 dark:text-gray-300">До колонки</span>
                            <kbd class="px-2 py-1 bg-gray-200 dark:bg-gray-600 rounded text-xs font-mono">1-9</kbd>
                        </div>
                        <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <span class="text-gray-700 dark:text-gray-300">Пошук</span>
                            <kbd class="px-2 py-1 bg-gray-200 dark:bg-gray-600 rounded text-xs font-mono">/</kbd>
                        </div>
                    </div>
                </div>

                <div>
                    <h4 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Дії</h4>
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <span class="text-gray-700 dark:text-gray-300">Нова картка</span>
                            <kbd class="px-2 py-1 bg-gray-200 dark:bg-gray-600 rounded text-xs font-mono">N</kbd>
                        </div>
                        <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <span class="text-gray-700 dark:text-gray-300">Відкрити</span>
                            <kbd class="px-2 py-1 bg-gray-200 dark:bg-gray-600 rounded text-xs font-mono">Enter</kbd>
                        </div>
                        <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <span class="text-gray-700 dark:text-gray-300">Завершити</span>
                            <kbd class="px-2 py-1 bg-gray-200 dark:bg-gray-600 rounded text-xs font-mono">C</kbd>
                        </div>
                        <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <span class="text-gray-700 dark:text-gray-300">Видалити</span>
                            <kbd class="px-2 py-1 bg-gray-200 dark:bg-gray-600 rounded text-xs font-mono">D</kbd>
                        </div>
                    </div>
                </div>

                <div>
                    <h4 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Загальні</h4>
                    <div class="grid grid-cols-2 gap-2 text-sm">
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

            <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                <p class="text-xs text-gray-500 dark:text-gray-400 text-center">
                    Використовуйте Cmd/Ctrl + Enter для швидкого збереження
                </p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
function kanbanBoard() {
    return {
        showAddCard: null,
        filters: {
            priority: '',
            assignee: '',
            dueDate: ''
        },
        cardModal: {
            open: false,
            card: null
        },
        cards: @json($board->columns->flatMap->cards->keyBy('id')),
        selectedCardIndex: -1,
        selectedColumnIndex: 0,
        columns: [],
        showShortcuts: false,
        searchQuery: '',
        showSearch: false,

        get activeFilters() {
            return [this.filters.priority, this.filters.assignee, this.filters.dueDate].filter(f => f).length;
        },

        init() {
            this.initSortable();
            this.initKeyboardShortcuts();
            this.columns = Array.from(document.querySelectorAll('.kanban-column'));
            this.$watch('filters', () => this.applyFilters(), { deep: true });
            this.$watch('searchQuery', () => this.searchCards());
        },

        initKeyboardShortcuts() {
            document.addEventListener('keydown', (e) => {
                // Skip if typing in input/textarea
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.isContentEditable) {
                    if (e.key === 'Escape') {
                        e.target.blur();
                        this.showAddCard = null;
                        this.showSearch = false;
                    }
                    return;
                }

                // Show shortcuts help
                if (e.key === '?') {
                    e.preventDefault();
                    this.showShortcuts = !this.showShortcuts;
                    return;
                }

                // Search
                if (e.key === '/' || (e.key === 'f' && (e.metaKey || e.ctrlKey))) {
                    e.preventDefault();
                    this.showSearch = true;
                    this.$nextTick(() => this.$refs.searchInput?.focus());
                    return;
                }

                // Close modals
                if (e.key === 'Escape') {
                    this.cardModal.open = false;
                    this.showShortcuts = false;
                    this.showSearch = false;
                    this.showAddCard = null;
                    return;
                }

                // New card in first column
                if (e.key === 'n' && !e.metaKey && !e.ctrlKey) {
                    e.preventDefault();
                    const firstColumn = this.columns[0];
                    if (firstColumn) {
                        const columnId = firstColumn.dataset.columnId;
                        this.showAddCard = parseInt(columnId);
                        this.$nextTick(() => {
                            const input = document.querySelector(`[x-ref="cardInput${columnId}"]`);
                            if (input) input.focus();
                        });
                    }
                    return;
                }

                // Arrow key navigation
                if (['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown'].includes(e.key)) {
                    e.preventDefault();
                    this.navigateCards(e.key);
                    return;
                }

                // Enter to open selected card
                if (e.key === 'Enter' && this.selectedCardIndex >= 0) {
                    e.preventDefault();
                    const cards = this.getVisibleCardsInColumn(this.selectedColumnIndex);
                    if (cards[this.selectedCardIndex]) {
                        this.openCard(parseInt(cards[this.selectedCardIndex].dataset.cardId));
                    }
                    return;
                }

                // Quick complete with 'c'
                if (e.key === 'c' && this.selectedCardIndex >= 0) {
                    e.preventDefault();
                    const cards = this.getVisibleCardsInColumn(this.selectedColumnIndex);
                    if (cards[this.selectedCardIndex]) {
                        this.toggleComplete(parseInt(cards[this.selectedCardIndex].dataset.cardId));
                    }
                    return;
                }

                // Delete with 'd' or 'Delete'
                if ((e.key === 'd' || e.key === 'Delete') && this.selectedCardIndex >= 0) {
                    e.preventDefault();
                    const cards = this.getVisibleCardsInColumn(this.selectedColumnIndex);
                    if (cards[this.selectedCardIndex]) {
                        this.deleteCard(parseInt(cards[this.selectedCardIndex].dataset.cardId));
                    }
                    return;
                }

                // Quick column switch with 1-9
                if (/^[1-9]$/.test(e.key)) {
                    const colIndex = parseInt(e.key) - 1;
                    if (colIndex < this.columns.length) {
                        this.selectedColumnIndex = colIndex;
                        this.selectedCardIndex = 0;
                        this.highlightSelected();
                    }
                    return;
                }
            });
        },

        getVisibleCardsInColumn(colIndex) {
            if (!this.columns[colIndex]) return [];
            return Array.from(this.columns[colIndex].querySelectorAll('.kanban-card:not([style*="display: none"])'));
        },

        navigateCards(key) {
            const cards = this.getVisibleCardsInColumn(this.selectedColumnIndex);

            if (key === 'ArrowDown') {
                this.selectedCardIndex = Math.min(this.selectedCardIndex + 1, cards.length - 1);
            } else if (key === 'ArrowUp') {
                this.selectedCardIndex = Math.max(this.selectedCardIndex - 1, 0);
            } else if (key === 'ArrowRight') {
                this.selectedColumnIndex = Math.min(this.selectedColumnIndex + 1, this.columns.length - 2);
                this.selectedCardIndex = Math.min(this.selectedCardIndex, this.getVisibleCardsInColumn(this.selectedColumnIndex).length - 1);
                if (this.selectedCardIndex < 0) this.selectedCardIndex = 0;
            } else if (key === 'ArrowLeft') {
                this.selectedColumnIndex = Math.max(this.selectedColumnIndex - 1, 0);
                this.selectedCardIndex = Math.min(this.selectedCardIndex, this.getVisibleCardsInColumn(this.selectedColumnIndex).length - 1);
                if (this.selectedCardIndex < 0) this.selectedCardIndex = 0;
            }

            this.highlightSelected();
        },

        highlightSelected() {
            // Remove all highlights
            document.querySelectorAll('.kanban-card').forEach(c => c.classList.remove('ring-2', 'ring-primary-500'));

            // Add highlight to selected
            const cards = this.getVisibleCardsInColumn(this.selectedColumnIndex);
            if (cards[this.selectedCardIndex]) {
                cards[this.selectedCardIndex].classList.add('ring-2', 'ring-primary-500');
                cards[this.selectedCardIndex].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        },

        searchCards() {
            const query = this.searchQuery.toLowerCase();
            document.querySelectorAll('.kanban-card').forEach(card => {
                const title = card.querySelector('p')?.textContent?.toLowerCase() || '';
                const desc = card.querySelectorAll('p')[1]?.textContent?.toLowerCase() || '';
                const matches = !query || title.includes(query) || desc.includes(query);
                card.style.display = matches ? '' : 'none';
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
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
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
            document.querySelectorAll('.kanban-card').forEach(card => {
                let show = true;

                if (this.filters.priority && card.dataset.priority !== this.filters.priority) {
                    show = false;
                }

                if (this.filters.assignee) {
                    if (this.filters.assignee === 'unassigned' && card.dataset.assignee !== 'unassigned') {
                        show = false;
                    } else if (this.filters.assignee !== 'unassigned' && card.dataset.assignee !== this.filters.assignee) {
                        show = false;
                    }
                }

                if (this.filters.dueDate) {
                    const due = card.dataset.due;
                    const today = new Date().toISOString().split('T')[0];
                    const weekEnd = new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];

                    if (this.filters.dueDate === 'overdue' && (!due || due >= today)) show = false;
                    if (this.filters.dueDate === 'today' && due !== today) show = false;
                    if (this.filters.dueDate === 'week' && (!due || due > weekEnd)) show = false;
                    if (this.filters.dueDate === 'none' && due) show = false;
                }

                card.style.display = show ? '' : 'none';
            });
        },

        clearFilters() {
            this.filters = { priority: '', assignee: '', dueDate: '' };
        },

        openCard(cardId) {
            const card = this.cards[cardId];
            if (card) {
                this.cardModal.card = { ...card };
                this.cardModal.open = true;
            } else {
                window.location.href = `/boards/cards/${cardId}`;
            }
        },

        updateCard(cardId, data) {
            fetch(`/boards/cards/${cardId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ ...this.cards[cardId], ...data })
            }).then(r => {
                if (r.ok) {
                    this.cards[cardId] = { ...this.cards[cardId], ...data };
                }
            });
        },

        toggleComplete(cardId) {
            fetch(`/boards/cards/${cardId}/toggle`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                }
            }).then(() => window.location.reload());
        },

        deleteCard(cardId) {
            if (confirm('Видалити картку?')) {
                fetch(`/boards/cards/${cardId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    }
                }).then(() => window.location.reload());
            }
        }
    }
}
</script>

<style>
.kanban-card {
    position: relative;
}
.kanban-card.sortable-ghost {
    opacity: 0.4;
}
.kanban-card.sortable-chosen {
    transform: rotate(2deg);
}
.scrollbar-thin::-webkit-scrollbar {
    height: 8px;
}
.scrollbar-thin::-webkit-scrollbar-track {
    background: transparent;
}
.scrollbar-thin::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 4px;
}
.dark .scrollbar-thin::-webkit-scrollbar-thumb {
    background: #4b5563;
}
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
@endsection
