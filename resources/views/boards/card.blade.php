@extends('layouts.app')

@section('title', $card->title)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Header -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
                            <a href="{{ route('boards.index') }}" class="hover:text-primary-600">
                                Завдання
                            </a>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                            <span>{{ $card->column->name }}</span>
                        </div>
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white {{ $card->is_completed ? 'line-through text-gray-400' : '' }}">
                            {{ $card->title }}
                        </h1>
                    </div>

                    <form method="POST" action="{{ route('boards.cards.toggle', $card) }}">
                        @csrf
                        <button type="submit"
                                class="p-2 rounded-lg transition-colors {{ $card->is_completed ? 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-400 dark:bg-gray-700 hover:bg-green-100 hover:text-green-600' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </button>
                    </form>
                </div>

                <!-- Description -->
                <form method="POST" action="{{ route('boards.cards.update', $card) }}" x-data="{ editing: false }">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="title" value="{{ $card->title }}">

                    <div class="flex items-center gap-2 mb-2">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                        </svg>
                        <h3 class="font-medium text-gray-900 dark:text-white">Опис</h3>
                    </div>

                    <div x-show="!editing" @click="editing = true" class="cursor-pointer">
                        @if($card->description)
                            <p class="text-gray-600 dark:text-gray-300 whitespace-pre-wrap">{{ $card->description }}</p>
                        @else
                            <p class="text-gray-400 dark:text-gray-500 italic">Натисніть, щоб додати опис...</p>
                        @endif
                    </div>

                    <div x-show="editing" x-cloak>
                        <textarea name="description" rows="4"
                                  class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">{{ $card->description }}</textarea>
                        <div class="flex items-center gap-2 mt-2">
                            <button type="submit"
                                    class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                                Зберегти
                            </button>
                            <button type="button" @click="editing = false"
                                    class="px-4 py-2 text-gray-600 dark:text-gray-400 text-sm font-medium">
                                Скасувати
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Checklist -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        <h3 class="font-medium text-gray-900 dark:text-white">Чеклист</h3>
                    </div>

                    @if($card->checklistItems->count() > 0)
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $card->checklistItems->where('is_completed', true)->count() }}/{{ $card->checklistItems->count() }}
                        </span>
                    @endif
                </div>

                @if($card->checklistItems->count() > 0)
                    <div class="w-full h-2 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden mb-4">
                        <div class="h-full bg-green-500 rounded-full transition-all duration-300"
                             style="width: {{ $card->checklist_progress }}%"></div>
                    </div>
                @endif

                <div class="space-y-2">
                    @foreach($card->checklistItems as $item)
                        <div class="flex items-center gap-3 group">
                            <form method="POST" action="{{ route('boards.cards.checklist.toggle', $item) }}">
                                @csrf
                                <button type="submit" class="w-5 h-5 rounded border-2 flex items-center justify-center transition-colors
                                    {{ $item->is_completed ? 'bg-green-500 border-green-500 text-white' : 'border-gray-300 dark:border-gray-600 hover:border-green-500' }}">
                                    @if($item->is_completed)
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    @endif
                                </button>
                            </form>
                            <span class="flex-1 text-gray-700 dark:text-gray-300 {{ $item->is_completed ? 'line-through text-gray-400 dark:text-gray-500' : '' }}">
                                {{ $item->title }}
                            </span>
                            <form method="POST" action="{{ route('boards.cards.checklist.destroy', $item) }}"
                                  class="opacity-0 group-hover:opacity-100 transition-opacity">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1 text-gray-400 hover:text-red-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>

                <!-- Add item -->
                <form method="POST" action="{{ route('boards.cards.checklist.store', $card) }}" class="mt-4" x-data="{ adding: false }">
                    @csrf
                    <template x-if="!adding">
                        <button type="button" @click="adding = true"
                                class="text-sm text-gray-500 dark:text-gray-400 hover:text-primary-600 font-medium flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Додати пункт
                        </button>
                    </template>
                    <template x-if="adding">
                        <div class="flex items-center gap-2">
                            <input type="text" name="title" required autofocus placeholder="Назва пункту..."
                                   class="flex-1 px-3 py-2 bg-gray-50 dark:bg-gray-700 border-0 rounded-lg focus:ring-2 focus:ring-primary-500 dark:text-white text-sm">
                            <button type="submit" class="px-3 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">
                                Додати
                            </button>
                            <button type="button" @click="adding = false" class="p-2 text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </template>
                </form>
            </div>

            <!-- Comments -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center gap-2 mb-4">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <h3 class="font-medium text-gray-900 dark:text-white">Коментарі</h3>
                </div>

                <!-- Add comment -->
                <form method="POST" action="{{ route('boards.cards.comments.store', $card) }}" class="mb-4">
                    @csrf
                    <textarea name="content" rows="2" required placeholder="Написати коментар..."
                              class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white text-sm"></textarea>
                    <button type="submit" class="mt-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                        Коментувати
                    </button>
                </form>

                <!-- Comments list -->
                <div class="space-y-4">
                    @forelse($card->comments as $comment)
                        <div class="flex gap-3 group">
                            <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center flex-shrink-0">
                                <span class="text-primary-600 dark:text-primary-400 text-sm font-medium">
                                    {{ substr($comment->user?->name ?? '?', 0, 1) }}
                                </span>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-gray-900 dark:text-white text-sm">{{ $comment->user?->name ?? 'Видалений' }}</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-gray-600 dark:text-gray-300 text-sm mt-1">{{ $comment->content }}</p>
                            </div>
                            @if($comment->user_id === auth()->id())
                                <form method="POST" action="{{ route('boards.comments.destroy', $comment) }}"
                                      class="opacity-0 group-hover:opacity-100 transition-opacity">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 text-gray-400 hover:text-red-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    @empty
                        <p class="text-center text-gray-500 dark:text-gray-400 text-sm py-4">Немає коментарів</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Card Details -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Деталі</h3>

                <form method="POST" action="{{ route('boards.cards.update', $card) }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="title" value="{{ $card->title }}">
                    <input type="hidden" name="description" value="{{ $card->description }}">

                    <!-- Column -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Колонка</label>
                        <select name="column_id"
                                class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border-0 rounded-lg focus:ring-2 focus:ring-primary-500 dark:text-white text-sm"
                                onchange="this.form.submit()">
                            @foreach($columns as $column)
                                <option value="{{ $column->id }}" {{ $card->column_id === $column->id ? 'selected' : '' }}>
                                    {{ $column->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Priority -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Пріоритет</label>
                        <select name="priority"
                                class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border-0 rounded-lg focus:ring-2 focus:ring-primary-500 dark:text-white text-sm"
                                onchange="this.form.submit()">
                            <option value="low" {{ $card->priority === 'low' ? 'selected' : '' }}>Низький</option>
                            <option value="medium" {{ $card->priority === 'medium' ? 'selected' : '' }}>Середній</option>
                            <option value="high" {{ $card->priority === 'high' ? 'selected' : '' }}>Високий</option>
                            <option value="urgent" {{ $card->priority === 'urgent' ? 'selected' : '' }}>Терміновий</option>
                        </select>
                    </div>

                    <!-- Assignee -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Відповідальний</label>
                        <select name="assigned_to"
                                class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border-0 rounded-lg focus:ring-2 focus:ring-primary-500 dark:text-white text-sm"
                                onchange="this.form.submit()">
                            <option value="">Не призначено</option>
                            @foreach($people as $person)
                                <option value="{{ $person->id }}" {{ $card->assigned_to === $person->id ? 'selected' : '' }}>
                                    {{ $person->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Due Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Дедлайн</label>
                        <input type="date" name="due_date"
                               value="{{ $card->due_date?->format('Y-m-d') }}"
                               class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border-0 rounded-lg focus:ring-2 focus:ring-primary-500 dark:text-white text-sm"
                               onchange="this.form.submit()">
                    </div>
                </form>
            </div>

            <!-- Created info -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                <div class="space-y-3 text-sm">
                    <div class="flex items-center justify-between text-gray-500 dark:text-gray-400">
                        <span>Створено</span>
                        <span>{{ $card->created_at->format('d.m.Y H:i') }}</span>
                    </div>
                    @if($card->creator)
                        <div class="flex items-center justify-between text-gray-500 dark:text-gray-400">
                            <span>Автор</span>
                            <span>{{ $card->creator->name }}</span>
                        </div>
                    @endif
                    @if($card->is_completed)
                        <div class="flex items-center justify-between text-green-600 dark:text-green-400">
                            <span>Завершено</span>
                            <span>{{ $card->completed_at?->format('d.m.Y H:i') }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Дії</h3>
                <div class="space-y-2">
                    <a href="{{ route('boards.index') }}"
                       class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-gray-700 dark:text-gray-300">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        <span>Назад до завдань</span>
                    </a>

                    <form method="POST" action="{{ route('boards.cards.destroy', $card) }}"
                          onsubmit="return confirm('Видалити картку?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="w-full flex items-center gap-3 p-3 rounded-xl hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors text-red-600 dark:text-red-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            <span>Видалити картку</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
