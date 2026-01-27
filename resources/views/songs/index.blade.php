@extends('layouts.app')

@section('title', 'Бібліотека пісень')

@section('actions')
<a href="{{ route('songs.create') }}"
   class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Додати пісню
</a>
@endsection

@section('content')
<div x-data="songsLibrary()" class="space-y-6">
    <!-- Search & Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
        <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px] relative">
                <input type="text" x-model="search"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"
                       placeholder="Пошук пісень...">
            </div>
            <select x-model="filterKey"
                    class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                <option value="">Усі тональності</option>
                @foreach(\App\Models\Song::KEYS as $key => $label)
                    <option value="{{ $key }}">{{ $key }}</option>
                @endforeach
            </select>
            <select x-model="filterTag"
                    class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                <option value="">Усі теги</option>
                @foreach($allTags as $tag)
                    <option value="{{ $tag }}">{{ $tag }}</option>
                @endforeach
            </select>
            <select x-model="sortBy"
                    class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                <option value="title">За назвою</option>
                <option value="recent">Нові</option>
                <option value="popular">Популярні</option>
                <option value="last_used">Недавно використані</option>
            </select>
        </div>
        <div class="mt-3 text-sm text-gray-500 dark:text-gray-400">
            Знайдено: <span x-text="filteredSongs.length"></span> пісень
        </div>
    </div>

    <!-- Songs Grid -->
    <template x-if="filteredSongs.length > 0">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            <template x-for="song in filteredSongs" :key="song.id">
                <a :href="'/songs/' + song.id"
                   class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 hover:shadow-md transition-all hover:-translate-y-0.5 group">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-12 h-12 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center text-white group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                            </svg>
                        </div>
                        <span x-show="song.key" class="px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 text-xs font-mono rounded" x-text="song.key"></span>
                    </div>

                    <h3 class="font-semibold text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 line-clamp-1" x-text="song.title"></h3>

                    <p x-show="song.artist" class="text-sm text-gray-500 dark:text-gray-400 mt-1" x-text="song.artist"></p>

                    <template x-if="song.tags && song.tags.length > 0">
                        <div class="flex flex-wrap gap-1 mt-3">
                            <template x-for="tag in song.tags.slice(0, 3)" :key="tag">
                                <span class="px-2 py-0.5 bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 text-xs rounded-full" x-text="tag"></span>
                            </template>
                        </div>
                    </template>

                    <div class="flex items-center justify-between mt-4 text-xs text-gray-500 dark:text-gray-400">
                        <span x-text="song.times_used + ' разів'"></span>
                        <span x-show="song.bpm" x-text="song.bpm + ' BPM'"></span>
                    </div>
                </a>
            </template>
        </div>
    </template>

    <!-- Empty State -->
    <template x-if="filteredSongs.length === 0 && songs.length > 0">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-12 text-center">
            <div class="w-20 h-20 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Нічого не знайдено</h3>
            <p class="text-gray-500 dark:text-gray-400">Спробуйте змінити параметри пошуку</p>
        </div>
    </template>

    <template x-if="songs.length === 0">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-12 text-center">
            <div class="w-20 h-20 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-10 h-10 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Бібліотека порожня</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-6">Почніть додавати пісні до вашої бібліотеки</p>
            <a href="{{ route('songs.create') }}"
               class="inline-flex items-center px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Додати першу пісню
            </a>
        </div>
    </template>
</div>

@php
$songsJson = $songs->map(function($s) {
    return [
        'id' => $s->id,
        'title' => $s->title,
        'artist' => $s->artist,
        'key' => $s->key,
        'bpm' => $s->bpm,
        'tags' => $s->tags ?? [],
        'times_used' => $s->times_used ?? 0,
        'last_used_at' => $s->last_used_at,
        'created_at' => $s->created_at,
    ];
});
@endphp
<script>
function songsLibrary() {
    return {
        songs: @json($songsJson),
        search: '',
        filterKey: '',
        filterTag: '',
        sortBy: 'title',

        get filteredSongs() {
            let result = this.songs;

            // Search filter
            if (this.search) {
                const searchLower = this.search.toLowerCase();
                result = result.filter(song =>
                    song.title.toLowerCase().includes(searchLower) ||
                    (song.artist && song.artist.toLowerCase().includes(searchLower))
                );
            }

            // Key filter
            if (this.filterKey) {
                result = result.filter(song => song.key === this.filterKey);
            }

            // Tag filter
            if (this.filterTag) {
                result = result.filter(song =>
                    song.tags && song.tags.includes(this.filterTag)
                );
            }

            // Sort
            result = [...result].sort((a, b) => {
                switch (this.sortBy) {
                    case 'recent':
                        return new Date(b.created_at) - new Date(a.created_at);
                    case 'popular':
                        return (b.times_used || 0) - (a.times_used || 0);
                    case 'last_used':
                        if (!a.last_used_at && !b.last_used_at) return 0;
                        if (!a.last_used_at) return 1;
                        if (!b.last_used_at) return -1;
                        return new Date(b.last_used_at) - new Date(a.last_used_at);
                    default: // title
                        return a.title.localeCompare(b.title, 'uk');
                }
            });

            return result;
        }
    }
}
</script>
@endsection
