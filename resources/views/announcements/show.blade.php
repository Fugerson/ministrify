@extends('layouts.app')

@section('title', $announcement->title)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('announcements.index') }}" class="inline-flex items-center text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Всі оголошення
        </a>
    </div>

    <article class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <!-- Pinned Badge -->
        @if($announcement->is_pinned)
        <div class="bg-amber-50 dark:bg-amber-900/30 px-6 py-3 border-b border-amber-100 dark:border-amber-800 flex items-center text-amber-700 dark:text-amber-400">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 2a1 1 0 011 1v1.323l3.954.99a1 1 0 01.756.97v.01a1 1 0 01-.756.97L11 8.253V17a1 1 0 11-2 0V8.253L5.046 7.263a1 1 0 010-1.94L9 4.323V3a1 1 0 011-1z"/>
            </svg>
            Закріплене оголошення
        </div>
        @endif

        <div class="p-6 lg:p-8">
            <!-- Header -->
            <header class="mb-6">
                <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white mb-4">
                    {{ $announcement->title }}
                </h1>
                <div class="flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center mr-2">
                            <span class="text-sm font-medium text-primary-600 dark:text-primary-400">{{ mb_substr($announcement->author->name, 0, 1) }}</span>
                        </div>
                        <span>{{ $announcement->author->name }}</span>
                    </div>
                    <span>•</span>
                    <time datetime="{{ $announcement->created_at->toIso8601String() }}">
                        {{ $announcement->created_at->format('d.m.Y, H:i') }}
                    </time>
                </div>
            </header>

            <!-- Content -->
            <div class="prose dark:prose-invert max-w-none">
                {!! nl2br(e($announcement->content)) !!}
            </div>

            @if($announcement->expires_at)
            <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl text-sm text-gray-600 dark:text-gray-400">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Актуально до: {{ $announcement->expires_at->format('d.m.Y') }}
            </div>
            @endif
        </div>

        @if(auth()->user()->canEdit('announcements'))
        <div class="px-6 lg:px-8 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700 flex items-center gap-4">
            <a href="{{ route('announcements.edit', $announcement) }}"
               class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-200 font-medium rounded-xl">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Редагувати
            </a>
            <form action="{{ route('announcements.pin', $announcement) }}" method="POST" class="inline">
                @csrf
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-200 font-medium rounded-xl">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 2a1 1 0 011 1v1.323l3.954.99a1 1 0 01.756.97v.01a1 1 0 01-.756.97L11 8.253V17a1 1 0 11-2 0V8.253L5.046 7.263a1 1 0 010-1.94L9 4.323V3a1 1 0 011-1z"/>
                    </svg>
                    {{ $announcement->is_pinned ? 'Відкріпити' : 'Закріпити' }}
                </button>
            </form>
            <form action="{{ route('announcements.destroy', $announcement) }}" method="POST" class="inline ml-auto"
                  onsubmit="return confirm('Видалити це оголошення?')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-red-100 dark:bg-red-900/30 hover:bg-red-200 dark:hover:bg-red-900/50 text-red-700 dark:text-red-400 font-medium rounded-xl">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Видалити
                </button>
            </form>
        </div>
        @endif
    </article>
</div>
@endsection
