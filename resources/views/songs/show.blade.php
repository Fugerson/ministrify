@extends('layouts.app')

@section('title', $song->title)

@section('actions')
<div class="flex items-center space-x-2">
    <a href="{{ route('songs.edit', $song) }}"
       class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
        </svg>
        Редагувати
    </a>
</div>
@endsection

@section('content')
<div class="max-w-5xl mx-auto">
    <a href="{{ route('songs.index') }}" class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-sm mb-6">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Назад до бібліотеки
    </a>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Header -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $song->title }}</h1>
                        @if($song->artist)
                            <p class="text-gray-500 dark:text-gray-400 mt-1">{{ $song->artist }}</p>
                        @endif
                    </div>
                    <div class="flex items-center space-x-2">
                        @if($song->key)
                            <span class="px-3 py-1 bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 text-sm font-mono rounded-lg">
                                {{ $song->key }}
                            </span>
                        @endif
                        @if($song->bpm)
                            <span class="px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 text-sm rounded-lg">
                                {{ $song->bpm }} BPM
                            </span>
                        @endif
                    </div>
                </div>

                @if($song->tags && count($song->tags) > 0)
                    <div class="flex flex-wrap gap-2 mt-4">
                        @foreach($song->tags as $tag)
                            <span class="px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 text-sm rounded-full">
                                {{ $tag }}
                            </span>
                        @endforeach
                    </div>
                @endif

                <div class="flex items-center gap-4 mt-4 text-sm text-gray-500 dark:text-gray-400">
                    <span>Використано {{ $song->times_used }} разів</span>
                    @if($song->last_used_at)
                        <span>Востаннє: {{ $song->last_used_at->diffForHumans() }}</span>
                    @endif
                </div>

                @if($song->notes)
                    <div class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                        <p class="text-sm text-yellow-800 dark:text-yellow-200">
                            <span class="font-medium">Коментарі:</span> {{ $song->notes }}
                        </p>
                    </div>
                @endif
            </div>

            <!-- Chords -->
            @if($song->chords)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Текст з акордами</h2>
                    <div class="prose dark:prose-invert max-w-none font-mono text-sm leading-relaxed whitespace-pre-wrap">
                        {!! $song->chords_html !!}
                    </div>
                </div>
            @endif

            <!-- Lyrics only (if no chords) -->
            @if($song->lyrics && !$song->chords)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Текст</h2>
                    <div class="prose dark:prose-invert max-w-none whitespace-pre-wrap">
                        {{ $song->lyrics }}
                    </div>
                </div>
            @endif

            <!-- YouTube Embed -->
            @if($song->youtube_id)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Відео</h2>
                    <div class="aspect-video rounded-lg overflow-hidden">
                        <iframe
                            src="https://www.youtube.com/embed/{{ $song->youtube_id }}"
                            class="w-full h-full"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen>
                        </iframe>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Дії</h3>
                <div class="space-y-3">
                    <a href="{{ route('songs.edit', $song) }}"
                       class="flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Редагувати
                    </a>
                    @if($song->spotify_url)
                        <a href="{{ $song->spotify_url }}" target="_blank"
                           class="flex items-center px-4 py-2 bg-[#1DB954] text-white rounded-lg hover:bg-[#1ed760] transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/>
                            </svg>
                            Відкрити в Spotify
                        </a>
                    @endif
                    @if($song->youtube_url)
                        <a href="{{ $song->youtube_url }}" target="_blank"
                           class="flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                            </svg>
                            YouTube
                        </a>
                    @endif
                </div>
            </div>

            <!-- Info -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Інформація</h3>
                <dl class="space-y-3 text-sm">
                    @if($song->ccli_number)
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">CCLI</dt>
                            <dd class="font-medium text-gray-900 dark:text-white">{{ $song->ccli_number }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Додано</dt>
                        <dd class="font-medium text-gray-900 dark:text-white">{{ $song->created_at->format('d.m.Y') }}</dd>
                    </div>
                    @if($song->creator)
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Додав</dt>
                            <dd class="font-medium text-gray-900 dark:text-white">{{ $song->creator->name }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            <!-- Recent Events -->
            @if($song->events->count() > 0)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Останні події</h3>
                    <div class="space-y-2">
                        @foreach($song->events as $event)
                            <a href="{{ route('events.show', $event) }}"
                               class="block p-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $event->title }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $event->date->format('d.m.Y') }}</p>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
