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
                            <a href="{{ $card->column->board->ministry_id ? route('boards.show', $card->column->board) : route('boards.index') }}" class="hover:text-primary-600">
                                {{ $card->column->board->display_name }}
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

                    <button type="button" x-data="{ done: {{ $card->is_completed ? 'true' : 'false' }} }"
                            @click="ajaxAction('{{ route('boards.cards.toggle', $card) }}', 'POST').then(() => { done = !done; $el.closest('.flex').querySelector('h1').classList.toggle('line-through'); $el.closest('.flex').querySelector('h1').classList.toggle('text-gray-400'); })"
                            class="p-2 rounded-lg transition-colors"
                            :class="done ? 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-400 dark:bg-gray-700 hover:bg-green-100 hover:text-green-600'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </button>
                </div>

                <!-- Description -->
                <form @submit.prevent="submit($refs.descForm)" x-ref="descForm"
                      x-data="{ ...ajaxForm({ url: '{{ route('boards.cards.update', $card) }}', method: 'PUT', stayOnPage: true }), editing: false }">
                    <input type="hidden" name="title" value="{{ $card->title }}">

                    <div class="flex items-center gap-2 mb-2">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                        </svg>
                        <h3 class="font-medium text-gray-900 dark:text-white">{{ __('Опис') }}</h3>
                    </div>

                    <div x-show="!editing" @click="editing = true" class="cursor-pointer">
                        @if($card->description)
                            <p class="text-gray-600 dark:text-gray-300 whitespace-pre-wrap">{{ $card->description }}</p>
                        @else
                            <p class="text-gray-400 dark:text-gray-500 italic">{{ __('Натисніть, щоб додати опис...') }}</p>
                        @endif
                    </div>

                    <div x-show="editing" x-cloak>
                        <textarea name="description" rows="4"
                                  class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">{{ $card->description }}</textarea>
                        <div class="flex items-center gap-2 mt-2">
                            <button type="submit" :disabled="saving"
                                    class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50">
                                {{ __('Зберегти') }}
                            </button>
                            <button type="button" @click="editing = false"
                                    class="px-4 py-2 text-gray-600 dark:text-gray-400 text-sm font-medium">
                                {{ __('Скасувати') }}
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
                        <h3 class="font-medium text-gray-900 dark:text-white">{{ __('Чеклист') }}</h3>
                    </div>

                    @if($card->checklistItems->count() > 0)
                        <span class="text-sm text-gray-500 dark:text-gray-400" data-progress-counter>
                            {{ $card->checklistItems->where('is_completed', true)->count() }}/{{ $card->checklistItems->count() }}
                        </span>
                    @endif
                </div>

                @if($card->checklistItems->count() > 0)
                    <div class="w-full h-2 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden mb-4">
                        <div class="h-full bg-green-500 rounded-full transition-all duration-300" data-progress-bar
                             style="width: {{ $card->checklist_progress }}%"></div>
                    </div>
                @endif

                <div class="space-y-2">
                    @foreach($card->checklistItems as $item)
                        <div class="flex items-center gap-3 group" data-checklist-item x-data="{ checked: {{ $item->is_completed ? 'true' : 'false' }} }">
                            <button type="button"
                                    @click="ajaxAction('{{ route('boards.cards.checklist.toggle', $item) }}', 'POST').then(() => { checked = !checked; /* update progress */ const items = document.querySelectorAll('[data-checklist-item]'); const total = items.length; let done = 0; items.forEach(i => { if (i.__x && i.__x.$data.checked) done++; else if (i._x_dataStack && i._x_dataStack[0].checked) done++; }); const pct = total ? Math.round(done/total*100) : 0; const bar = document.querySelector('[data-progress-bar]'); if (bar) bar.style.width = pct + '%'; const counter = document.querySelector('[data-progress-counter]'); if (counter) counter.textContent = done + '/' + total; })"
                                    class="w-5 h-5 rounded border-2 flex items-center justify-center transition-colors"
                                    :class="checked ? 'bg-green-500 border-green-500 text-white' : 'border-gray-300 dark:border-gray-600 hover:border-green-500'">
                                <svg x-show="checked" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                </svg>
                            </button>
                            <span class="flex-1 text-gray-700 dark:text-gray-300" :class="checked && 'line-through text-gray-400 dark:text-gray-500'">
                                {{ $item->title }}
                            </span>
                            <button type="button"
                                    @click="ajaxDelete('{{ route('boards.cards.checklist.destroy', $item) }}', @js( __('Видалити пункт?') ), () => $el.closest('[data-checklist-item]').remove())"
                                    class="opacity-0 group-hover:opacity-100 transition-opacity p-1 text-gray-400 hover:text-red-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    @endforeach
                </div>

                <!-- Add item -->
                <div class="mt-4" x-data="{ adding: false, ...ajaxForm({ url: '{{ route('boards.cards.checklist.store', $card) }}', method: 'POST', stayOnPage: true, resetOnSuccess: true, onSuccess(data) { _cardAddChecklistItem(this); } }) }">
                    <template x-if="!adding">
                        <button type="button" @click="adding = true"
                                class="text-sm text-gray-500 dark:text-gray-400 hover:text-primary-600 font-medium flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            {{ __('Додати пункт') }}
                        </button>
                    </template>
                    <template x-if="adding">
                        <form @submit.prevent="submit($refs.checklistForm)" x-ref="checklistForm">
                            <div class="flex items-center gap-2">
                                <input type="text" name="title" required autofocus placeholder="{{ __('Назва пункту...') }}"
                                       class="flex-1 px-3 py-2 bg-gray-50 dark:bg-gray-700 border-0 rounded-lg focus:ring-2 focus:ring-primary-500 dark:text-white text-sm">
                                <button type="submit" :disabled="saving" class="px-3 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg disabled:opacity-50">
                                    {{ __('Додати') }}
                                </button>
                                <button type="button" @click="adding = false" class="p-2 text-gray-400 hover:text-gray-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </form>
                    </template>
                </div>
            </div>

            <!-- Comments -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center gap-2 mb-4">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <h3 class="font-medium text-gray-900 dark:text-white">{{ __('Коментарі') }}</h3>
                </div>

                <!-- Add comment -->
                <form @submit.prevent="submit($refs.commentForm)" x-ref="commentForm"
                      x-data="{ ...ajaxForm({ url: '{{ route('boards.cards.comments.store', $card) }}', method: 'POST', resetOnSuccess: true, stayOnPage: true, onSuccess() { _cardAddComment(this); } }) }"
                      class="mb-4">
                    <textarea name="content" rows="2" required placeholder="{{ __('Написати коментар...') }}"
                              class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white text-sm"></textarea>
                    <button type="submit" :disabled="saving" class="mt-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50">
                        {{ __('Коментувати') }}
                    </button>
                </form>

                <!-- Comments list -->
                <div class="space-y-4">
                    @forelse($card->comments as $comment)
                        <div class="flex gap-3 group" data-comment>
                            <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center flex-shrink-0">
                                <span class="text-primary-600 dark:text-primary-400 text-sm font-medium">
                                    {{ substr($comment->user?->name ?? '?', 0, 1) }}
                                </span>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-gray-900 dark:text-white text-sm">{{ $comment->user?->name ?? __('Видалений') }}</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-gray-600 dark:text-gray-300 text-sm mt-1">{{ $comment->content }}</p>
                            </div>
                            @if($comment->user_id === auth()->id())
                                <button type="button"
                                        @click="ajaxDelete('{{ route('boards.comments.destroy', $comment) }}', @js( __('Видалити коментар?') ), () => $el.closest('[data-comment]').remove())"
                                        class="opacity-0 group-hover:opacity-100 transition-opacity p-1 text-gray-400 hover:text-red-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            @endif
                        </div>
                    @empty
                        <p class="text-center text-gray-500 dark:text-gray-400 text-sm py-4">{{ __('Немає коментарів') }}</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Card Details -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">{{ __('Деталі') }}</h3>

                <form @submit.prevent="submit($refs.detailsForm)" x-ref="detailsForm"
                      x-data="{ ...ajaxForm({ url: '{{ route('boards.cards.update', $card) }}', method: 'PUT', stayOnPage: true }) }"
                      class="space-y-4">
                    <input type="hidden" name="title" value="{{ $card->title }}">
                    <input type="hidden" name="description" value="{{ $card->description }}">

                    <!-- Column -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Колонка') }}</label>
                        <select name="column_id"
                                class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border-0 rounded-lg focus:ring-2 focus:ring-primary-500 dark:text-white text-sm"
                                @change="submit($refs.detailsForm)">
                            @foreach($columns as $column)
                                <option value="{{ $column->id }}" {{ $card->column_id === $column->id ? 'selected' : '' }}>
                                    {{ $column->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Priority -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Пріоритет') }}</label>
                        <select name="priority"
                                class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border-0 rounded-lg focus:ring-2 focus:ring-primary-500 dark:text-white text-sm"
                                @change="submit($refs.detailsForm)">
                            <option value="low" {{ $card->priority === 'low' ? 'selected' : '' }}>{{ __('Низький') }}</option>
                            <option value="medium" {{ $card->priority === 'medium' ? 'selected' : '' }}>{{ __('Середній') }}</option>
                            <option value="high" {{ $card->priority === 'high' ? 'selected' : '' }}>{{ __('Високий') }}</option>
                            <option value="urgent" {{ $card->priority === 'urgent' ? 'selected' : '' }}>{{ __('Терміновий') }}</option>
                        </select>
                    </div>

                    <!-- Assignee -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Відповідальний') }}</label>
                        <select name="assigned_to"
                                class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border-0 rounded-lg focus:ring-2 focus:ring-primary-500 dark:text-white text-sm"
                                @change="submit($refs.detailsForm)">
                            <option value="">{{ __('Не призначено') }}</option>
                            @foreach($people as $person)
                                <option value="{{ $person->id }}" {{ $card->assigned_to === $person->id ? 'selected' : '' }}>
                                    {{ $person->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Due Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Дедлайн') }}</label>
                        <input type="date" name="due_date"
                               value="{{ $card->due_date?->format('Y-m-d') }}"
                               class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border-0 rounded-lg focus:ring-2 focus:ring-primary-500 dark:text-white text-sm"
                               @change="submit($refs.detailsForm)">
                    </div>
                </form>
            </div>

            <!-- Created info -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                <div class="space-y-3 text-sm">
                    <div class="flex items-center justify-between text-gray-500 dark:text-gray-400">
                        <span>{{ __('Створено') }}</span>
                        <span>{{ $card->created_at->format('d.m.Y H:i') }}</span>
                    </div>
                    @if($card->creator)
                        <div class="flex items-center justify-between text-gray-500 dark:text-gray-400">
                            <span>{{ __('Автор') }}</span>
                            <span>{{ $card->creator->name }}</span>
                        </div>
                    @endif
                    @if($card->is_completed)
                        <div class="flex items-center justify-between text-green-600 dark:text-green-400">
                            <span>{{ __('Завершено') }}</span>
                            <span>{{ $card->completed_at?->format('d.m.Y H:i') }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">{{ __('Дії') }}</h3>
                <div class="space-y-2">
                    <a href="{{ $card->column->board->ministry_id ? route('boards.show', $card->column->board) : route('boards.index') }}"
                       class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-gray-700 dark:text-gray-300">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        <span>{{ __('Назад до дошки') }}</span>
                    </a>

                    <button type="button"
                            @click="ajaxDelete('{{ route('boards.cards.destroy', $card) }}', @js( __('Видалити картку?') ), null, '{{ $card->column->board->ministry_id ? route('boards.show', $card->column->board) : route('boards.index') }}')"
                            class="w-full flex items-center gap-3 p-3 rounded-xl hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors text-red-600 dark:text-red-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        <span>{{ __('Видалити картку') }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<script>
function _cardAddChecklistItem(ctx) {
    var title = ctx.$refs.checklistForm.querySelector('input[name=title]').value;
    var list = document.querySelector('.space-y-2');
    if (list) {
        var item = document.createElement('div');
        item.className = 'flex items-center gap-3 group';
        item.setAttribute('data-checklist-item', '');
        var safe = title.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
        item.innerHTML = '<button type="button" class="w-5 h-5 rounded border-2 flex items-center justify-center transition-colors border-gray-300 dark:border-gray-600 hover:border-green-500"></button><span class="flex-1 text-gray-700 dark:text-gray-300">' + safe + '</span>';
        list.appendChild(item);
    }
    ctx.adding = false;
    var items = document.querySelectorAll('[data-checklist-item]');
    var counter = document.querySelector('[data-progress-counter]');
    if (counter) counter.textContent = '0/' + items.length;
}

function _cardAddComment(ctx) {
    var ta = ctx.$refs.commentForm.querySelector('textarea');
    var text = ta.value;
    var list = document.querySelector('.space-y-4');
    if (list) {
        var safe = text.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\n/g,'<br>');
        var el = document.createElement('div');
        el.className = 'flex gap-3 group';
        el.setAttribute('data-comment', '');
        el.innerHTML = '<div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center flex-shrink-0"><span class="text-primary-600 dark:text-primary-400 text-sm font-medium">{{ mb_substr(auth()->user()->name, 0, 1) }}</span></div><div class="flex-1"><div class="flex items-center gap-2"><span class="font-medium text-gray-900 dark:text-white text-sm">{{ auth()->user()->name }}</span><span class="text-xs text-gray-500 dark:text-gray-400">щойно</span></div><p class="text-gray-600 dark:text-gray-300 text-sm mt-1">' + safe + '</p></div>';
        list.appendChild(el);
    }
}
</script>
