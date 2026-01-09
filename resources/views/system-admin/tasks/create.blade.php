@extends('layouts.system-admin')

@section('title', 'Нова задача')

@section('content')
<div class="max-w-2xl">
    <a href="{{ route('system.tasks.index') }}" class="inline-flex items-center text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-sm mb-6">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Назад до списку
    </a>

    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Нова задача</h2>

        <form action="{{ route('system.tasks.store') }}" method="POST" class="space-y-6">
            @csrf

            @if($supportTicket)
                <input type="hidden" name="support_ticket_id" value="{{ $supportTicket->id }}">
                <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Пов'язаний тікет:</p>
                    <p class="text-gray-900 dark:text-white font-medium">{{ $supportTicket->subject }}</p>
                </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Назва *</label>
                <input type="text" name="title" value="{{ old('title', $supportTicket?->subject) }}" required
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
                          placeholder="Детальний опис задачі...">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Тип *</label>
                    <select name="type" required
                            class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        <option value="bug" {{ old('type', $supportTicket?->category === 'bug' ? 'bug' : '') === 'bug' ? 'selected' : '' }}>Баг</option>
                        <option value="feature" {{ old('type', $supportTicket?->category === 'feature' ? 'feature' : '') === 'feature' ? 'selected' : '' }}>Фіча</option>
                        <option value="improvement" {{ old('type') === 'improvement' ? 'selected' : '' }}>Покращення</option>
                        <option value="other" {{ old('type') === 'other' ? 'selected' : '' }}>Інше</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Пріоритет *</label>
                    <select name="priority" required
                            class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Низький</option>
                        <option value="normal" {{ old('priority', 'normal') === 'normal' ? 'selected' : '' }}>Нормальний</option>
                        <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>Високий</option>
                        <option value="critical" {{ old('priority') === 'critical' ? 'selected' : '' }}>Критичний</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Статус *</label>
                    <select name="status" required
                            class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        <option value="backlog" {{ old('status', 'backlog') === 'backlog' ? 'selected' : '' }}>Беклог</option>
                        <option value="todo" {{ old('status') === 'todo' ? 'selected' : '' }}>До виконання</option>
                        <option value="in_progress" {{ old('status') === 'in_progress' ? 'selected' : '' }}>В роботі</option>
                        <option value="testing" {{ old('status') === 'testing' ? 'selected' : '' }}>Тестування</option>
                        <option value="done" {{ old('status') === 'done' ? 'selected' : '' }}>Виконано</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Виконавець</label>
                    <select name="assigned_to"
                            class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        <option value="">Не призначено</option>
                        @foreach($admins as $admin)
                            <option value="{{ $admin->id }}" {{ old('assigned_to') == $admin->id ? 'selected' : '' }}>
                                {{ $admin->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Дедлайн</label>
                <input type="date" name="due_date" value="{{ old('due_date') }}"
                       class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="flex items-center gap-4 pt-4">
                <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors">
                    Створити задачу
                </button>
                <a href="{{ route('system.tasks.index') }}" class="px-6 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-900 dark:text-white font-medium rounded-lg transition-colors">
                    Скасувати
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
