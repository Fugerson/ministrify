@extends('layouts.app')

@section('title', 'Повідомлення')

@section('actions')
<a href="{{ route('pm.create') }}" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl">
    + Нове повідомлення
</a>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Повідомлення</h1>
            @if($unreadCount > 0)
            <p class="text-sm text-primary-600 dark:text-primary-400">{{ $unreadCount }} непрочитаних</p>
            @endif
        </div>
    </div>

    <!-- Conversations List -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        @forelse($conversations as $userId => $lastMessage)
            @php
                $otherUser = $lastMessage->sender_id === auth()->id() ? $lastMessage->recipient : $lastMessage->sender;
                $isUnread = $lastMessage->recipient_id === auth()->id() && !$lastMessage->read_at;
            @endphp
            <a href="{{ route('pm.show', $otherUser) }}"
               class="flex items-center px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700 last:border-0 transition-colors {{ $isUnread ? 'bg-primary-50 dark:bg-primary-900/20' : '' }}">
                <!-- Avatar -->
                <div class="relative">
                    <div class="w-12 h-12 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center">
                        <span class="text-lg font-medium text-primary-600 dark:text-primary-400">
                            {{ mb_substr($otherUser->name, 0, 1) }}
                        </span>
                    </div>
                    @if($isUnread)
                    <span class="absolute -top-1 -right-1 w-4 h-4 bg-primary-600 rounded-full border-2 border-white dark:border-gray-800"></span>
                    @endif
                </div>

                <!-- Content -->
                <div class="flex-1 ml-4 min-w-0">
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold text-gray-900 dark:text-white {{ $isUnread ? 'text-primary-600 dark:text-primary-400' : '' }}">
                            {{ $otherUser->name }}
                        </h3>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $lastMessage->created_at->diffForHumans(null, true) }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 truncate mt-0.5 {{ $isUnread ? 'font-medium' : '' }}">
                        @if($lastMessage->sender_id === auth()->id())
                        <span class="text-gray-400">Ви:</span>
                        @endif
                        {{ Str::limit($lastMessage->message, 60) }}
                    </p>
                </div>

                <!-- Arrow -->
                <svg class="w-5 h-5 text-gray-400 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        @empty
            <div class="px-6 py-12 text-center">
                <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">Немає повідомлень</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-4">Почніть спілкування з кимось із церкви</p>
                <a href="{{ route('pm.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Написати повідомлення
                </a>
            </div>
        @endforelse
    </div>
</div>
@endsection
