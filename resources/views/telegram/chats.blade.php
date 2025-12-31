@extends('layouts.app')

@section('title', 'Telegram чати')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Telegram чати</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                @if($totalUnread > 0)
                    <span class="text-primary-600 dark:text-primary-400 font-medium">{{ $totalUnread }} непрочитаних</span>
                @else
                    Всі повідомлення прочитані
                @endif
            </p>
        </div>
        <a href="{{ route('telegram.broadcast.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
            </svg>
            Розсилка
        </a>
    </div>

    @if(!$hasBot)
        <div class="bg-amber-100 dark:bg-amber-900/30 border border-amber-400 dark:border-amber-600 text-amber-700 dark:text-amber-400 px-4 py-3 rounded-lg">
            Telegram бот не налаштований. <a href="{{ route('settings.index') }}" class="underline">Налаштувати</a>
        </div>
    @elseif($conversations->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 divide-y divide-gray-200 dark:divide-gray-700">
            @foreach($conversations as $person)
                @php
                    $lastMessage = $person->telegramMessages->first();
                @endphp
                <a href="{{ route('telegram.chat.show', $person) }}"
                   class="flex items-center gap-4 p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors {{ $person->unread_count > 0 ? 'bg-primary-50 dark:bg-primary-900/20' : '' }}">
                    <!-- Avatar -->
                    <div class="relative flex-shrink-0">
                        <div class="w-12 h-12 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                            <span class="text-primary-600 dark:text-primary-400 text-lg font-medium">
                                {{ mb_substr($person->first_name, 0, 1) }}
                            </span>
                        </div>
                        @if($person->unread_count > 0)
                            <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center">
                                {{ $person->unread_count > 9 ? '9+' : $person->unread_count }}
                            </span>
                        @endif
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                {{ $person->first_name }} {{ $person->last_name }}
                            </h3>
                            @if($lastMessage)
                                <span class="text-xs text-gray-500 dark:text-gray-400 flex-shrink-0 ml-2">
                                    {{ $lastMessage->created_at->diffForHumans(short: true) }}
                                </span>
                            @endif
                        </div>
                        @if($lastMessage)
                            <p class="text-sm text-gray-600 dark:text-gray-400 truncate mt-0.5">
                                @if($lastMessage->isOutgoing())
                                    <span class="text-gray-400 dark:text-gray-500">Ви: </span>
                                @endif
                                {{ Str::limit($lastMessage->message, 50) }}
                            </p>
                        @else
                            <p class="text-sm text-gray-400 dark:text-gray-500 mt-0.5">Немає повідомлень</p>
                        @endif
                    </div>

                    <!-- Arrow -->
                    <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            @endforeach
        </div>
    @else
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-8 text-center">
            <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Немає чатів</h3>
            <p class="text-gray-500 dark:text-gray-400">
                Ніхто ще не підключив Telegram.<br>
                Люди можуть підключитись через бота.
            </p>
        </div>
    @endif
</div>
@endsection
