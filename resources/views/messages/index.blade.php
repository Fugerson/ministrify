@extends('layouts.app')

@section('title', 'Розсилка')

@section('actions')
<a href="{{ route('messages.create') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-xl hover:bg-primary-700 transition-colors">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
    </svg>
    Нова розсилка
</a>
@endsection

@section('content')
<x-comm-tabs />

<div class="space-y-6">
    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900 flex items-center justify-center mr-3">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $logs->sum('sent_count') }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Надіслано</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-900 flex items-center justify-center mr-3">
                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $templates->count() }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Шаблонів</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-xl bg-green-100 dark:bg-green-900 flex items-center justify-center mr-3">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $logs->count() }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Розсилок</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Templates -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h3 class="font-semibold text-gray-900 dark:text-white">Шаблони</h3>
                <button type="button" onclick="document.getElementById('templateModal').classList.remove('hidden')"
                        class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 font-medium">
                    + Новий
                </button>
            </div>
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($templates as $template)
                <div class="p-4">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="font-medium text-gray-900 dark:text-white">{{ $template->name }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">{{ Str::limit($template->content, 100) }}</p>
                        </div>
                        <form method="POST" action="{{ route('messages.templates.destroy', $template) }}" onsubmit="return confirm('{{ __('messages.confirm_delete_short') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="ml-2 p-1 text-gray-400 hover:text-red-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
                @empty
                <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                    Немає шаблонів
                </div>
                @endforelse
            </div>
        </div>

        <!-- History -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="font-semibold text-gray-900 dark:text-white">Історія розсилок</h3>
            </div>
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($logs as $log)
                <div class="p-4">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2">
                                <span class="px-2 py-0.5 text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 rounded">{{ strtoupper($log->type) }}</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $log->created_at->format('d.m.Y H:i') }}</span>
                            </div>
                            <p class="text-gray-900 dark:text-white mt-2 line-clamp-2">{{ Str::limit($log->content, 150) }}</p>
                            <div class="flex items-center space-x-4 mt-2 text-sm">
                                <span class="text-green-600 dark:text-green-400">Надіслано: {{ $log->sent_count }}</span>
                                @if($log->failed_count > 0)
                                <span class="text-red-600 dark:text-red-400">Помилок: {{ $log->failed_count }}</span>
                                @endif
                                <span class="text-gray-500 dark:text-gray-400">{{ $log->user?->name ?? 'Видалений' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                    Немає розсилок
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Template Modal -->
<div id="templateModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="min-h-screen px-4 flex items-center justify-center">
        <div class="fixed inset-0 bg-black/50" onclick="document.getElementById('templateModal').classList.add('hidden')"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Новий шаблон</h3>
            <form method="POST" action="{{ route('messages.templates.store') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Назва</label>
                        <input type="text" name="name" required placeholder="Привітання з днем народження"
                               class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Текст повідомлення</label>
                        <textarea name="content" rows="4" required placeholder="Привіт, {first_name}!..."
                                  class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl dark:text-white"></textarea>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Змінні: {first_name}, {last_name}, {full_name}</p>
                    </div>
                </div>
                <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 sm:gap-3 mt-6">
                    <button type="button" onclick="document.getElementById('templateModal').classList.add('hidden')"
                            class="w-full sm:w-auto px-4 py-2 text-gray-700 dark:text-gray-300">
                        Скасувати
                    </button>
                    <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700">
                        Зберегти
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
