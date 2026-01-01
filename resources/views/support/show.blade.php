@extends('layouts.app')

@section('title', $ticket->subject)

@section('content')
<div class="max-w-3xl mx-auto">
    <a href="{{ route('support.index') }}" class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-sm mb-6">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Назад до списку
    </a>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-2 mb-2">
                <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-{{ $ticket->category_color }}-100 text-{{ $ticket->category_color }}-700 dark:bg-{{ $ticket->category_color }}-900/30 dark:text-{{ $ticket->category_color }}-400">
                    {{ $ticket->category_label }}
                </span>
                <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-{{ $ticket->status_color }}-100 text-{{ $ticket->status_color }}-700 dark:bg-{{ $ticket->status_color }}-900/30 dark:text-{{ $ticket->status_color }}-400">
                    {{ $ticket->status_label }}
                </span>
            </div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $ticket->subject }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Створено {{ $ticket->created_at->format('d.m.Y H:i') }}</p>
        </div>

        <!-- Messages -->
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            @foreach($messages as $message)
                <div class="p-6 {{ $message->is_from_admin ? 'bg-blue-50 dark:bg-blue-900/10' : '' }}">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0
                            {{ $message->is_from_admin ? 'bg-primary-100 dark:bg-primary-900/30' : 'bg-gray-100 dark:bg-gray-700' }}">
                            @if($message->is_from_admin)
                                <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            @else
                                <span class="text-lg">{{ mb_substr($message->user->name, 0, 1) }}</span>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="font-medium text-gray-900 dark:text-white">
                                    {{ $message->is_from_admin ? 'Підтримка' : $message->user->name }}
                                </span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $message->created_at->format('d.m.Y H:i') }}
                                </span>
                            </div>
                            <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300">
                                {!! nl2br(e($message->message)) !!}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @if(!in_array($ticket->status, ['closed', 'resolved']))
        <!-- Reply Form -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="font-semibold text-gray-900 dark:text-white">Відповісти</h2>
            </div>
            <form action="{{ route('support.reply', $ticket) }}" method="POST" class="p-6">
                @csrf
                <textarea name="message" rows="4" required
                          placeholder="Напишіть вашу відповідь..."
                          class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 mb-4"></textarea>
                <div class="flex items-center justify-between">
                    <form action="{{ route('support.close', $ticket) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                            Закрити запит
                        </button>
                    </form>
                    <button type="submit" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                        Надіслати
                    </button>
                </div>
            </form>
        </div>
    @else
        <div class="bg-gray-100 dark:bg-gray-700 rounded-xl p-6 text-center">
            <p class="text-gray-600 dark:text-gray-400">Цей запит закрито.</p>
        </div>
    @endif
</div>
@endsection
