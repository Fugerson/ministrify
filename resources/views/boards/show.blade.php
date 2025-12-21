@extends('layouts.app')

@section('title', $board->name)

@section('actions')
<div class="flex items-center gap-2">
    <a href="{{ route('boards.edit', $board) }}"
       class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors">
        Налаштування
    </a>
</div>
@endsection

@section('content')
<div class="h-full" x-data="kanbanBoard()" x-init="init()">
    <!-- Board Header -->
    <div class="flex items-center gap-4 mb-6">
        <div class="w-3 h-8 rounded" style="background-color: {{ $board->color }}"></div>
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $board->name }}</h1>
            @if($board->description)
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $board->description }}</p>
            @endif
        </div>
        <div class="ml-auto flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
            <span>{{ $board->cards->count() }} карток</span>
            <span>{{ $board->progress }}% завершено</span>
        </div>
    </div>

    <!-- Kanban Columns -->
    <div class="flex gap-4 overflow-x-auto pb-4 -mx-6 px-6" style="min-height: calc(100vh - 300px);">
        @foreach($board->columns as $column)
            <div class="flex-shrink-0 w-80 bg-gray-100 dark:bg-gray-800 rounded-xl flex flex-col max-h-full"
                 data-column-id="{{ $column->id }}">
                <!-- Column Header -->
                <div class="p-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full"
                             style="background-color: {{ $column->color === 'gray' ? '#9ca3af' : ($column->color === 'blue' ? '#3b82f6' : ($column->color === 'yellow' ? '#eab308' : ($column->color === 'green' ? '#22c55e' : '#9ca3af'))) }}"></div>
                        <h3 class="font-semibold text-gray-900 dark:text-white text-sm">{{ $column->name }}</h3>
                        <span class="text-xs text-gray-500 dark:text-gray-400 bg-gray-200 dark:bg-gray-700 px-1.5 py-0.5 rounded">
                            {{ $column->cards->count() }}
                        </span>
                    </div>
                    <div class="flex items-center gap-1">
                        <button type="button" @click="showAddCard = {{ $column->id }}"
                                class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </button>
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                </svg>
                            </button>
                            <div x-show="open" x-cloak @click.away="open = false"
                                 class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 z-20 overflow-hidden">
                                @if($column->cards->count() === 0)
                                    <form method="POST" action="{{ route('boards.columns.destroy', $column) }}"
                                          onsubmit="return confirm('Видалити колонку?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full px-4 py-2 text-left text-sm text-red-600 dark:text-red-400 hover:bg-gray-50 dark:hover:bg-gray-700">
                                            Видалити колонку
                                        </button>
                                    </form>
                                @else
                                    <p class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">
                                        Спочатку видаліть всі картки
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cards Container -->
                <div class="flex-1 overflow-y-auto p-2 space-y-2 min-h-[100px] kanban-cards"
                     data-column-id="{{ $column->id }}"
                     @dragover.prevent
                     @drop="handleDrop($event, {{ $column->id }})">
                    @foreach($column->cards as $card)
                        <div class="bg-white dark:bg-gray-700 rounded-lg shadow-sm border border-gray-200 dark:border-gray-600 p-3 cursor-pointer hover:shadow-md transition-shadow group"
                             draggable="true"
                             data-card-id="{{ $card->id }}"
                             @dragstart="handleDragStart($event, {{ $card->id }})"
                             @dragend="handleDragEnd($event)"
                             @click="window.location.href = '{{ route('boards.cards.show', $card) }}'">

                            <!-- Labels -->
                            @if($card->labels && count($card->labels) > 0)
                                <div class="flex flex-wrap gap-1 mb-2">
                                    @foreach($card->labels as $label)
                                        <span class="px-2 py-0.5 text-xs font-medium rounded"
                                              style="background-color: {{ $label['color'] ?? '#6366f1' }}20; color: {{ $label['color'] ?? '#6366f1' }}">
                                            {{ $label['name'] ?? '' }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Title -->
                            <p class="text-sm font-medium text-gray-900 dark:text-white {{ $card->is_completed ? 'line-through text-gray-400 dark:text-gray-500' : '' }}">
                                {{ $card->title }}
                            </p>

                            <!-- Meta info -->
                            <div class="mt-2 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    @if($card->due_date)
                                        <span class="inline-flex items-center gap-1 text-xs px-1.5 py-0.5 rounded
                                            {{ $card->isOverdue() ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' :
                                               ($card->isDueSoon() ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400' :
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
                                        @endphp
                                        <span class="inline-flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                            </svg>
                                            {{ $completed }}/{{ $total }}
                                        </span>
                                    @endif

                                    @if($card->priority !== 'medium')
                                        <span class="w-2 h-2 rounded-full
                                            {{ $card->priority === 'urgent' ? 'bg-red-500' :
                                               ($card->priority === 'high' ? 'bg-orange-500' : 'bg-green-500') }}"></span>
                                    @endif
                                </div>

                                @if($card->assignee)
                                    @if($card->assignee->photo)
                                        <img src="{{ Storage::url($card->assignee->photo) }}"
                                             class="w-6 h-6 rounded-full object-cover"
                                             title="{{ $card->assignee->full_name }}">
                                    @else
                                        <div class="w-6 h-6 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center"
                                             title="{{ $card->assignee->full_name }}">
                                            <span class="text-primary-600 dark:text-primary-400 text-xs font-medium">
                                                {{ substr($card->assignee->first_name, 0, 1) }}
                                            </span>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Add Card Form -->
                <div class="p-2" x-show="showAddCard === {{ $column->id }}" x-cloak>
                    <form method="POST" action="{{ route('boards.cards.store', $column) }}" class="space-y-2">
                        @csrf
                        <textarea name="title" rows="2" required placeholder="Назва картки..."
                                  class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:text-white text-sm resize-none"></textarea>
                        <div class="flex items-center gap-2">
                            <button type="submit"
                                    class="px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                                Додати
                            </button>
                            <button type="button" @click="showAddCard = null"
                                    class="px-3 py-1.5 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-sm font-medium">
                                Скасувати
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Quick Add Button -->
                <div class="p-2" x-show="showAddCard !== {{ $column->id }}">
                    <button type="button" @click="showAddCard = {{ $column->id }}"
                            class="w-full p-2 text-gray-500 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg text-sm font-medium transition-colors flex items-center justify-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Додати картку
                    </button>
                </div>
            </div>
        @endforeach

        <!-- Add Column -->
        <div class="flex-shrink-0 w-80" x-data="{ adding: false }">
            <template x-if="!adding">
                <button @click="adding = true"
                        class="w-full p-4 bg-gray-100/50 dark:bg-gray-800/50 hover:bg-gray-100 dark:hover:bg-gray-800 border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-xl text-gray-500 dark:text-gray-400 font-medium transition-colors flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Додати колонку
                </button>
            </template>

            <template x-if="adding">
                <form method="POST" action="{{ route('boards.columns.store', $board) }}"
                      class="bg-gray-100 dark:bg-gray-800 rounded-xl p-3">
                    @csrf
                    <input type="text" name="name" required autofocus placeholder="Назва колонки..."
                           class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:text-white text-sm">
                    <div class="flex items-center gap-2 mt-2">
                        <button type="submit"
                                class="px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                            Додати
                        </button>
                        <button type="button" @click="adding = false"
                                class="px-3 py-1.5 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-sm font-medium">
                            Скасувати
                        </button>
                    </div>
                </form>
            </template>
        </div>
    </div>
</div>

<script>
function kanbanBoard() {
    return {
        showAddCard: null,
        draggedCard: null,

        init() {
            // Initialize drag and drop
        },

        handleDragStart(event, cardId) {
            this.draggedCard = cardId;
            event.target.classList.add('opacity-50');
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', cardId);
        },

        handleDragEnd(event) {
            event.target.classList.remove('opacity-50');
            this.draggedCard = null;
        },

        handleDrop(event, columnId) {
            event.preventDefault();
            const cardId = event.dataTransfer.getData('text/plain');

            if (!cardId) return;

            // Get drop position
            const container = event.currentTarget;
            const cards = [...container.querySelectorAll('[data-card-id]')];
            let position = 0;

            for (let i = 0; i < cards.length; i++) {
                const rect = cards[i].getBoundingClientRect();
                if (event.clientY < rect.top + rect.height / 2) {
                    position = i;
                    break;
                }
                position = i + 1;
            }

            // Send move request
            fetch(`/boards/cards/${cardId}/move`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    column_id: columnId,
                    position: position
                })
            }).then(response => {
                if (response.ok) {
                    window.location.reload();
                }
            });
        }
    }
}
</script>

<style>
    .kanban-cards {
        min-height: 100px;
    }
    .kanban-cards.drag-over {
        background-color: rgba(99, 102, 241, 0.1);
    }
</style>
@endsection
