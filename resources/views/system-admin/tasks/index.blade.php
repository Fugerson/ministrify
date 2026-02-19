@extends('layouts.system-admin')

@section('title', 'Задачі')

@section('actions')
<a href="{{ route('system.tasks.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors">
    + Нова задача
</a>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Беклог</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['backlog'] }}</p>
                </div>
                <div class="w-10 h-10 bg-gray-100 dark:bg-gray-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">До виконання</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['todo'] }}</p>
                </div>
                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">В роботі</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['in_progress'] }}</p>
                </div>
                <div class="w-10 h-10 bg-yellow-100 dark:bg-yellow-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Тестування</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['testing'] }}</p>
                </div>
                <div class="w-10 h-10 bg-purple-100 dark:bg-purple-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Виконано</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['done'] }}</p>
                </div>
                <div class="w-10 h-10 bg-green-100 dark:bg-green-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
        <form action="{{ route('system.tasks.index') }}" method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Пошук..."
                       class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <select name="status" class="px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                <option value="">Активні</option>
                <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>Всі</option>
                <option value="backlog" {{ request('status') === 'backlog' ? 'selected' : '' }}>Беклог</option>
                <option value="todo" {{ request('status') === 'todo' ? 'selected' : '' }}>До виконання</option>
                <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>В роботі</option>
                <option value="testing" {{ request('status') === 'testing' ? 'selected' : '' }}>Тестування</option>
                <option value="done" {{ request('status') === 'done' ? 'selected' : '' }}>Виконано</option>
            </select>
            <select name="type" class="px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                <option value="">Всі типи</option>
                <option value="bug" {{ request('type') === 'bug' ? 'selected' : '' }}>Баг</option>
                <option value="feature" {{ request('type') === 'feature' ? 'selected' : '' }}>Фіча</option>
                <option value="improvement" {{ request('type') === 'improvement' ? 'selected' : '' }}>Покращення</option>
                <option value="other" {{ request('type') === 'other' ? 'selected' : '' }}>Інше</option>
            </select>
            <select name="priority" class="px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                <option value="">Всі пріоритети</option>
                <option value="critical" {{ request('priority') === 'critical' ? 'selected' : '' }}>Критичний</option>
                <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>Високий</option>
                <option value="normal" {{ request('priority') === 'normal' ? 'selected' : '' }}>Нормальний</option>
                <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Низький</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors">
                Фільтрувати
            </button>
        </form>
    </div>

    <!-- Tasks List -->
    <div class="bg-white dark:bg-gray-800 rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700">
        @if($tasks->isEmpty())
            <div class="p-12 text-center">
                <svg class="w-12 h-12 text-gray-400 dark:text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                <p class="text-gray-500 dark:text-gray-400">Немає задач</p>
                <a href="{{ route('system.tasks.create') }}" class="inline-block mt-4 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors">
                    Створити першу задачу
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Задача</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Тип</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Пріоритет</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Статус</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Виконавець</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Дії</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($tasks as $task)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-gray-900 dark:text-white font-medium">{{ $task->title }}</p>
                                    @if($task->supportTicket)
                                        <a href="{{ route('system.support.show', $task->supportTicket) }}" class="text-sm text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300">
                                            Тікет #{{ $task->supportTicket->id }}
                                        </a>
                                    @endif
                                    @if($task->due_date)
                                        <p class="text-sm {{ $task->due_date->isPast() && $task->status !== 'done' ? 'text-red-600 dark:text-red-400' : 'text-gray-500 dark:text-gray-400' }}">
                                            Дедлайн: {{ $task->due_date->format('d.m.Y') }}
                                        </p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $task->type_color }}-500/20 text-{{ $task->type_color }}-400">
                                    {{ $task->type_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $task->priority_color }}-500/20 text-{{ $task->priority_color }}-400">
                                    {{ $task->priority_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <form action="{{ route('system.tasks.update-status', $task) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" onchange="this.form.submit()"
                                            class="px-2 py-1 text-xs font-medium rounded-full border-0 bg-{{ $task->status_color }}-500/20 text-{{ $task->status_color }}-400 focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                                        <option value="backlog" {{ $task->status === 'backlog' ? 'selected' : '' }}>Беклог</option>
                                        <option value="todo" {{ $task->status === 'todo' ? 'selected' : '' }}>До виконання</option>
                                        <option value="in_progress" {{ $task->status === 'in_progress' ? 'selected' : '' }}>В роботі</option>
                                        <option value="testing" {{ $task->status === 'testing' ? 'selected' : '' }}>Тестування</option>
                                        <option value="done" {{ $task->status === 'done' ? 'selected' : '' }}>Виконано</option>
                                    </select>
                                </form>
                            </td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400 text-sm">
                                {{ $task->assignee?->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('system.tasks.edit', $task) }}" class="p-2 text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <form action="{{ route('system.tasks.destroy', $task) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('messages.confirm_delete_task') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        @endif
    </div>

    <div>
        {{ $tasks->links() }}
    </div>
</div>
@endsection
