@extends('layouts.app')

@section('title', 'Підтримка')

@section('content')
@include('partials.section-tabs', ['tabs' => [
    ['route' => 'settings.index', 'label' => 'Загальні', 'active' => 'settings.*', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>'],
    ['route' => 'website-builder.index', 'label' => 'Сайт', 'active' => 'website-builder.*', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>'],
    ['route' => 'billing.index', 'label' => 'Тарифи', 'active' => 'billing.*', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>'],
    ['route' => 'support.index', 'label' => 'Підтримка', 'active' => 'support.*', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>'],
]])

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
