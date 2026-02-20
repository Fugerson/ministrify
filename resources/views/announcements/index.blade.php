@extends('layouts.app')

@section('title', 'Оголошення')

@section('actions')
@if(auth()->user()->canCreate('announcements'))
<a href="{{ route('announcements.create') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-xl transition-colors">
    + Нове оголошення
</a>
@endif
@endsection

@section('content')
<x-comm-tabs />

<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Оголошення церкви</h1>
            @if($unreadCount > 0)
            <p class="text-sm text-primary-600 dark:text-primary-400">{{ $unreadCount }} нових</p>
            @endif
        </div>
    </div>

    <!-- Announcements List -->
    <div class="space-y-4">
        @forelse($announcements as $announcement)
            @php
                $isUnread = !$announcement->isReadBy(auth()->user());
            @endphp
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden {{ $isUnread ? 'ring-2 ring-primary-500' : '' }}">
                <!-- Pinned Badge -->
                @if($announcement->is_pinned)
                <div class="bg-amber-50 dark:bg-amber-900/30 px-4 py-2 border-b border-amber-100 dark:border-amber-800 flex items-center text-amber-700 dark:text-amber-400 text-sm">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 2a1 1 0 011 1v1.323l3.954.99a1 1 0 01.756.97v.01a1 1 0 01-.756.97L11 8.253V17a1 1 0 11-2 0V8.253L5.046 7.263a1 1 0 010-1.94L9 4.323V3a1 1 0 011-1z"/>
                    </svg>
                    Закріплене оголошення
                </div>
                @endif

                <a href="{{ route('announcements.show', $announcement) }}" class="block p-4 sm:p-6 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-2">
                                @if($isUnread)
                                <span class="w-2 h-2 bg-primary-600 rounded-full"></span>
                                @endif
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white {{ $isUnread ? 'text-primary-600 dark:text-primary-400' : '' }}">
                                    {{ $announcement->title }}
                                </h3>
                            </div>
                            <p class="text-gray-600 dark:text-gray-400 line-clamp-2">
                                {{ Str::limit(strip_tags($announcement->content), 150) }}
                            </p>
                            <div class="flex items-center gap-2 sm:gap-4 mt-3 text-sm text-gray-500 dark:text-gray-400">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    {{ $announcement->author?->name ?? 'Видалений' }}
                                </span>
                                <span>{{ $announcement->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 ml-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </a>

                @if(auth()->user()->canEdit('announcements'))
                <div class="px-6 py-3 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700 flex items-center gap-2">
                    <form action="{{ route('announcements.pin', $announcement) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-gray-500 hover:text-amber-600 dark:text-gray-400 dark:hover:text-amber-400">
                            {{ $announcement->is_pinned ? 'Відкріпити' : 'Закріпити' }}
                        </button>
                    </form>
                    <span class="text-gray-300 dark:text-gray-600">|</span>
                    <a href="{{ route('announcements.edit', $announcement) }}" class="text-sm text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400">
                        Редагувати
                    </a>
                    <span class="text-gray-300 dark:text-gray-600">|</span>
                    <form action="{{ route('announcements.destroy', $announcement) }}" method="POST" class="inline"
                          onsubmit="return confirm('{{ __('messages.confirm_delete_announcement') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-sm text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400">
                            Видалити
                        </button>
                    </form>
                </div>
                @endif
            </div>
        @empty
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
                <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">Немає оголошень</h3>
                <p class="text-gray-500 dark:text-gray-400">Тут будуть важливі повідомлення від церкви</p>
            </div>
        @endforelse
    </div>

    @if($announcements->hasPages())
    <div class="mt-6">
        {{ $announcements->links() }}
    </div>
    @endif
</div>
@endsection
