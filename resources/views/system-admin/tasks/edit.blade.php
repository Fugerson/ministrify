@extends('layouts.system-admin')

@section('title', 'Редагувати задачу')

@section('content')
<div class="max-w-2xl">
    <a href="{{ route('system.tasks.index') }}" class="inline-flex items-center text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-sm mb-6">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Назад до списку
    </a>

    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Редагувати задачу</h2>
            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                <span>Створено: {{ $task->created_at->format('d.m.Y H:i') }}</span>
                @if($task->completed_at)
                    <span class="text-green-600 dark:text-green-400">| Виконано: {{ $task->completed_at->format('d.m.Y H:i') }}</span>
                @endif
            </div>
        </div>

        <form action="{{ route('system.tasks.update', $task) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            @if($task->supportTicket)
                <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Пов'язаний тікет:</p>
                    <a href="{{ route('system.support.show', $task->supportTicket) }}" class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 font-medium">
                        {{ $task->supportTicket->subject }}
                    </a>
                </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Назва *</label>
                <input type="text" name="title" value="{{ old('title', $task->title) }}" required
                       class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                       placeholder="Що потрібно зробити?">
                @error('title')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Опис</label>
                <textarea name="description" rows="4"
                          class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                          placeholder="Детальний опис задачі...">{{ old('description', $task->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Тип *</label>
                    <select name="type" required
                            class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        <option value="bug" {{ old('type', $task->type) === 'bug' ? 'selected' : '' }}>Баг</option>
                        <option value="feature" {{ old('type', $task->type) === 'feature' ? 'selected' : '' }}>Фіча</option>
                        <option value="improvement" {{ old('type', $task->type) === 'improvement' ? 'selected' : '' }}>Покращення</option>
                        <option value="other" {{ old('type', $task->type) === 'other' ? 'selected' : '' }}>Інше</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Пріоритет *</label>
                    <select name="priority" required
                            class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        <option value="low" {{ old('priority', $task->priority) === 'low' ? 'selected' : '' }}>Низький</option>
                        <option value="normal" {{ old('priority', $task->priority) === 'normal' ? 'selected' : '' }}>Нормальний</option>
                        <option value="high" {{ old('priority', $task->priority) === 'high' ? 'selected' : '' }}>Високий</option>
                        <option value="critical" {{ old('priority', $task->priority) === 'critical' ? 'selected' : '' }}>Критичний</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Статус *</label>
                    <select name="status" required
                            class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        <option value="backlog" {{ old('status', $task->status) === 'backlog' ? 'selected' : '' }}>Беклог</option>
                        <option value="todo" {{ old('status', $task->status) === 'todo' ? 'selected' : '' }}>До виконання</option>
                        <option value="in_progress" {{ old('status', $task->status) === 'in_progress' ? 'selected' : '' }}>В роботі</option>
                        <option value="testing" {{ old('status', $task->status) === 'testing' ? 'selected' : '' }}>Тестування</option>
                        <option value="done" {{ old('status', $task->status) === 'done' ? 'selected' : '' }}>Виконано</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Виконавець</label>
                    <select name="assigned_to"
                            class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        <option value="">Не призначено</option>
                        @foreach($admins as $admin)
                            <option value="{{ $admin->id }}" {{ old('assigned_to', $task->assigned_to) == $admin->id ? 'selected' : '' }}>
                                {{ $admin->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Дедлайн</label>
                    <input type="date" name="due_date" value="{{ old('due_date', $task->due_date?->format('Y-m-d')) }}"
                           class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Пов'язаний тікет</label>
                    <select name="support_ticket_id"
                            class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        <option value="">Немає</option>
                        @foreach($supportTickets as $ticket)
                            <option value="{{ $ticket->id }}" {{ old('support_ticket_id', $task->support_ticket_id) == $ticket->id ? 'selected' : '' }}>
                                #{{ $ticket->id }}: {{ Str::limit($ticket->subject, 40) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-between pt-4">
                <div class="flex items-center gap-4">
                    <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors">
                        Зберегти
                    </button>
                    <a href="{{ route('system.tasks.index') }}" class="px-6 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-900 dark:text-white font-medium rounded-lg transition-colors">
                        Скасувати
                    </a>
                </div>

                <form action="{{ route('system.tasks.destroy', $task) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('messages.confirm_delete_task') }}')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 transition-colors">
                        Видалити
                    </button>
                </form>
            </div>
        </form>
    </div>

    @if($task->creator)
        <div class="mt-4 text-sm text-gray-400 dark:text-gray-500">
            Створено: {{ $task->creator->name }}
        </div>
    @endif
</div>
@endsection
