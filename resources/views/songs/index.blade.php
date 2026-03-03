@extends('layouts.app')

@section('title', 'Бібліотека пісень')

@section('actions')
<div class="flex items-center gap-2">
    <a href="{{ route('songs.import.page') }}"
       class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
        </svg>
        Імпорт
    </a>
    <a href="{{ route('songs.create') }}"
       class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Додати пісню
    </a>
</div>
@endsection

@section('content')
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

<div x-data="songsBoard()" x-init="initSortable()" class="space-y-4">
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
        </div>
        <div class="mt-3 text-sm text-gray-500 dark:text-gray-400">
            Знайдено: <span x-text="totalVisible"></span> пісень
        </div>
    </div>

    <!-- Kanban Board -->
    <template x-if="columns.length > 0 && songs.length > 0">
        <div class="overflow-x-auto pb-4 -mx-4 px-4 lg:-mx-6 lg:px-6">
            <div class="flex gap-4" style="min-width: max-content;">
                <!-- Tag Columns -->
                <template x-for="col in columns" :key="col">
                    <div class="w-72 flex-shrink-0 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-700 flex flex-col max-h-[calc(100vh-260px)]">
                        <!-- Column Header -->
                        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-between">
                                <h3 class="font-semibold text-gray-900 dark:text-white text-sm" x-text="col"></h3>
                                <span class="px-2 py-0.5 bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400 text-xs rounded-full"
                                      x-text="getSongsForColumn(col).length"></span>
                            </div>
                        </div>
                        <!-- Cards List -->
                        <div class="p-2 flex-1 overflow-y-auto space-y-2 sortable-column" :data-tag="col">
                            <template x-for="song in getSongsForColumn(col)" :key="song.id">
                                <a :href="'/songs/' + song.id"
                                   class="block bg-white dark:bg-gray-800 rounded-lg p-3 shadow-sm hover:shadow-md transition-shadow cursor-grab active:cursor-grabbing border border-gray-100 dark:border-gray-700"
                                   :data-song-id="song.id">
                                    <h4 class="font-medium text-gray-900 dark:text-white text-sm line-clamp-1" x-text="song.title"></h4>
                                    <p x-show="song.artist" class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-1" x-text="song.artist"></p>
                                    <div class="flex items-center gap-2 mt-2">
                                        <span x-show="song.key" class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 text-[10px] font-mono rounded" x-text="song.key"></span>
                                        <span x-show="song.bpm" class="text-[10px] text-gray-400" x-text="song.bpm + ' BPM'"></span>
                                        <span class="text-[10px] text-gray-400 ml-auto" x-text="song.times_used + 'x'"></span>
                                    </div>
                                </a>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Untagged Column -->
                <div class="w-72 flex-shrink-0 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-700 border-dashed flex flex-col max-h-[calc(100vh-260px)]">
                    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <h3 class="font-semibold text-gray-500 dark:text-gray-400 text-sm">Без тегу</h3>
                            <span class="px-2 py-0.5 bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400 text-xs rounded-full"
                                  x-text="getUntaggedSongs().length"></span>
                        </div>
                    </div>
                    <div class="p-2 flex-1 overflow-y-auto space-y-2 sortable-column" data-tag="__untagged__">
                        <template x-for="song in getUntaggedSongs()" :key="song.id">
                            <a :href="'/songs/' + song.id"
                               class="block bg-white dark:bg-gray-800 rounded-lg p-3 shadow-sm hover:shadow-md transition-shadow cursor-grab active:cursor-grabbing border border-gray-100 dark:border-gray-700"
                               :data-song-id="song.id">
                                <h4 class="font-medium text-gray-900 dark:text-white text-sm line-clamp-1" x-text="song.title"></h4>
                                <p x-show="song.artist" class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-1" x-text="song.artist"></p>
                                <div class="flex items-center gap-2 mt-2">
                                    <span x-show="song.key" class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 text-[10px] font-mono rounded" x-text="song.key"></span>
                                    <span x-show="song.bpm" class="text-[10px] text-gray-400" x-text="song.bpm + ' BPM'"></span>
                                    <span class="text-[10px] text-gray-400 ml-auto" x-text="song.times_used + 'x'"></span>
                                </div>
                            </a>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- No columns configured -->
    <template x-if="columns.length === 0 && songs.length > 0">
        <div class="space-y-4">
            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="text-sm text-amber-800 dark:text-amber-200">Колонки дошки не налаштовані. Перейдіть в <a href="{{ route('settings.index') }}?tab=data" class="underline font-medium">Налаштування → Дані</a>, щоб додати теги-колонки.</p>
                    </div>
                </div>
            </div>
            <!-- Fallback grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <template x-for="song in filteredSongs" :key="song.id">
                    <a :href="'/songs/' + song.id"
                       class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 hover:shadow-md transition-all hover:-translate-y-0.5 group">
                        <div class="flex items-start justify-between mb-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-600 rounded-lg flex items-center justify-center text-white">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
        </div>
    </template>

    <!-- Empty Library -->
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

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
function songsBoard() {
    return {
        songs: @json($songsJson),
        columns: @json($boardTags),
        search: '',
        filterKey: '',
        moving: false,

        get filteredSongs() {
            let result = this.songs;
            if (this.search) {
                const s = this.search.toLowerCase();
                result = result.filter(song =>
                    song.title.toLowerCase().includes(s) ||
                    (song.artist && song.artist.toLowerCase().includes(s))
                );
            }
            if (this.filterKey) {
                result = result.filter(song => song.key === this.filterKey);
            }
            return result.sort((a, b) => a.title.localeCompare(b.title, 'uk'));
        },

        get totalVisible() {
            if (this.columns.length === 0) return this.filteredSongs.length;
            let count = 0;
            this.columns.forEach(col => { count += this.getSongsForColumn(col).length; });
            count += this.getUntaggedSongs().length;
            return count;
        },

        getSongsForColumn(tag) {
            return this.filteredSongs.filter(s => s.tags && s.tags.includes(tag));
        },

        getUntaggedSongs() {
            return this.filteredSongs.filter(s => {
                if (!s.tags || s.tags.length === 0) return true;
                return !s.tags.some(t => this.columns.includes(t));
            });
        },

        initSortable() {
            this.$nextTick(() => {
                if (typeof Sortable === 'undefined' || this.columns.length === 0) return;
                document.querySelectorAll('.sortable-column').forEach(el => {
                    Sortable.create(el, {
                        group: 'songs',
                        animation: 150,
                        ghostClass: 'opacity-30',
                        dragClass: 'shadow-lg',
                        filter: 'a',
                        preventOnFilter: false,
                        delay: 200,
                        delayOnTouchOnly: true,
                        onEnd: (evt) => {
                            if (evt.from === evt.to) return;
                            const songId = parseInt(evt.item.dataset.songId);
                            const fromTag = evt.from.dataset.tag;
                            const toTag = evt.to.dataset.tag;
                            this.moveSong(songId, fromTag === '__untagged__' ? null : fromTag, toTag === '__untagged__' ? null : toTag);
                        }
                    });
                });
            });
        },

        async moveSong(songId, fromTag, toTag) {
            if (this.moving) return;
            this.moving = true;

            // Optimistic update
            const song = this.songs.find(s => s.id === songId);
            if (!song) { this.moving = false; return; }

            const oldTags = [...(song.tags || [])];
            let newTags = [...oldTags];
            if (fromTag) newTags = newTags.filter(t => t !== fromTag);
            if (toTag && !newTags.includes(toTag)) newTags.push(toTag);
            song.tags = newTags;

            try {
                const resp = await fetch(`/songs/${songId}/move-tag`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ from_tag: fromTag, to_tag: toTag })
                });
                const data = await resp.json();
                if (data.success) {
                    song.tags = data.tags;
                } else {
                    song.tags = oldTags;
                }
            } catch (e) {
                song.tags = oldTags;
            }
            this.moving = false;
            // Re-init sortable since Alpine re-renders DOM
            this.initSortable();
        }
    }
}
</script>
@endsection
