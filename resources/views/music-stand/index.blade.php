@extends('layouts.app')

@section('title', 'Music Stand')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-900/50 flex items-center justify-center">
                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Music Stand</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Пісні для служінь</p>
            </div>
        </div>
        <a href="{{ route('songs.index') }}" class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400">
            Бібліотека пісень
        </a>
    </div>

    @if($upcomingServices->isEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
            <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Немає запланованих служінь</h3>
            <p class="text-gray-500 dark:text-gray-400">Коли з'являться служіння з піснями, вони будуть тут</p>
        </div>
    @else
        <div class="grid gap-4">
            @foreach($upcomingServices as $service)
                <a href="{{ route('music-stand.show', $service) }}"
                   class="block bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:border-primary-300 dark:hover:border-primary-700 transition-colors">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <div class="w-14 h-14 rounded-xl flex flex-col items-center justify-center text-center"
                                     style="background-color: {{ $service->ministry?->color ?? '#6366f1' }}20">
                                    <span class="text-lg font-bold" style="color: {{ $service->ministry?->color ?? '#6366f1' }}">
                                        {{ $service->date->format('d') }}
                                    </span>
                                    <span class="text-xs uppercase" style="color: {{ $service->ministry?->color ?? '#6366f1' }}">
                                        {{ $service->date->translatedFormat('M') }}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">{{ $service->title }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $service->date->translatedFormat('l') }}, {{ $service->time?->format('H:i') }}
                                    @if($service->ministry)
                                        <span class="mx-1">·</span>
                                        {{ $service->ministry->name }}
                                    @endif
                                </p>

                                @if($service->planItems->count() > 0)
                                    <div class="flex flex-wrap gap-2 mt-3">
                                        @foreach($service->planItems->take(5) as $item)
                                            <span class="inline-flex items-center px-2.5 py-1 bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 text-xs font-medium rounded-lg">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z"/>
                                                </svg>
                                                {{ Str::limit($item->title, 25) }}
                                            </span>
                                        @endforeach
                                        @if($service->planItems->count() > 5)
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                +{{ $service->planItems->count() - 5 }}
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center text-gray-400">
                            <span class="text-sm mr-2">{{ $service->planItems->count() }} пісень</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif

    <!-- Quick Song Search -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6"
         x-data="{ search: '', songs: {{ json_encode($allSongs) }} }">
        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Швидкий пошук пісні</h3>
        <input type="text" x-model="search" placeholder="Введіть назву пісні..."
               class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">

        <div x-show="search.length > 1" x-cloak class="mt-3 max-h-60 overflow-y-auto">
            <template x-for="song in songs.filter(s => s.title.toLowerCase().includes(search.toLowerCase()) || (s.artist && s.artist.toLowerCase().includes(search.toLowerCase())))" :key="song.id">
                <a :href="'{{ route('songs.show', '') }}/' + song.id"
                   class="flex items-center justify-between p-3 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition-colors">
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white" x-text="song.title"></p>
                        <p class="text-sm text-gray-500 dark:text-gray-400" x-text="song.artist"></p>
                    </div>
                    <span x-show="song.key" class="px-2 py-1 bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 text-xs font-mono rounded" x-text="song.key"></span>
                </a>
            </template>
        </div>
    </div>
</div>
@endsection
