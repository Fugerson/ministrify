@extends('layouts.app')

@section('title', 'Підтримка')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Підтримка</h1>
        <a href="{{ route('support.create') }}" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
            Новий запит
        </a>
    </div>

    @if($tickets->isEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-12 text-center">
            <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Немає запитів</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-4">Маєте питання або знайшли помилку? Напишіть нам!</p>
            <a href="{{ route('support.create') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                Створити запит
            </a>
        </div>
    @else
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($tickets as $ticket)
                    <a href="{{ route('support.show', $ticket) }}" class="block p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <div class="flex items-start justify-between">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-{{ $ticket->category_color }}-100 text-{{ $ticket->category_color }}-700 dark:bg-{{ $ticket->category_color }}-900/30 dark:text-{{ $ticket->category_color }}-400">
                                        {{ $ticket->category_label }}
                                    </span>
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-{{ $ticket->status_color }}-100 text-{{ $ticket->status_color }}-700 dark:bg-{{ $ticket->status_color }}-900/30 dark:text-{{ $ticket->status_color }}-400">
                                        {{ $ticket->status_label }}
                                    </span>
                                    @if($ticket->unreadMessagesForUser() > 0)
                                        <span class="px-2 py-0.5 text-xs font-bold rounded-full bg-red-500 text-white">
                                            {{ $ticket->unreadMessagesForUser() }} нових
                                        </span>
                                    @endif
                                </div>
                                <h3 class="font-medium text-gray-900 dark:text-white truncate">{{ $ticket->subject }}</h3>
                                @if($ticket->latestMessage)
                                    <p class="text-sm text-gray-500 dark:text-gray-400 truncate mt-1">
                                        {{ Str::limit($ticket->latestMessage->message, 100) }}
                                    </p>
                                @endif
                            </div>
                            <div class="text-right text-sm text-gray-500 dark:text-gray-400 ml-4">
                                <p>{{ $ticket->updated_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

        <div class="mt-4">
            {{ $tickets->links() }}
        </div>
    @endif
</div>
@endsection
