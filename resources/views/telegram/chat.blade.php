@extends('layouts.app')

@section('title', $person->first_name . ' ' . $person->last_name . ' - Telegram')

@section('content')
<div class="max-w-3xl mx-auto flex flex-col h-[calc(100vh-8rem)]">
    <!-- Header -->
    <div class="flex items-center gap-4 mb-4">
        <a href="{{ route('telegram.chat.index') }}" class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                <span class="text-primary-600 dark:text-primary-400 font-medium">
                    {{ mb_substr($person->first_name, 0, 1) }}
                </span>
            </div>
            <div>
                <h1 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ $person->first_name }} {{ $person->last_name }}
                </h1>
                @if($person->telegram_username)
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $person->telegram_username }}</p>
                @endif
            </div>
        </div>
    </div>

    @if(session('error'))
        <div class="bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-400 px-4 py-3 rounded-lg mb-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- Messages -->
    <div class="flex-1 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden flex flex-col">
        <div id="messages" class="flex-1 overflow-y-auto p-4 space-y-3">
            @forelse($messages as $message)
                <div class="flex {{ $message->isOutgoing() ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[75%] {{ $message->isOutgoing() ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white' }} rounded-2xl px-4 py-2 {{ $message->isOutgoing() ? 'rounded-br-md' : 'rounded-bl-md' }}">
                        <p class="text-sm whitespace-pre-wrap break-words">{{ $message->message }}</p>
                        <p class="text-xs {{ $message->isOutgoing() ? 'text-primary-200' : 'text-gray-500 dark:text-gray-400' }} mt-1">
                            {{ $message->created_at->format('H:i') }}
                        </p>
                    </div>
                </div>
            @empty
                <div class="h-full flex items-center justify-center text-gray-500 dark:text-gray-400">
                    <p>Почніть розмову</p>
                </div>
            @endforelse
        </div>

        <!-- Input -->
        @if($hasBot)
            <form method="POST" action="{{ route('telegram.chat.send', $person) }}" class="border-t border-gray-200 dark:border-gray-700 p-4">
                @csrf
                <div class="flex gap-3">
                    <textarea name="message" rows="1" required
                              class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white resize-none"
                              placeholder="Напишіть повідомлення..."
                              onkeydown="if(event.key === 'Enter' && !event.shiftKey) { event.preventDefault(); this.form.submit(); }"></textarea>
                    <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                    </button>
                </div>
            </form>
        @else
            <div class="border-t border-gray-200 dark:border-gray-700 p-4 bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 text-sm text-center">
                Telegram бот не налаштований. <a href="{{ route('settings.index') }}" class="underline">Налаштувати</a>
            </div>
        @endif
    </div>
</div>

<script>
// Scroll to bottom on load
onPageReady(function() {
    const messages = document.getElementById('messages');
    messages.scrollTop = messages.scrollHeight;
});
</script>
@endsection
