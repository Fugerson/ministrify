@extends('layouts.app')

@section('title', 'Music Stand - ' . $event->title)

@section('content')
<div class="space-y-6" x-data="musicStand()">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('music-stand.index') }}" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $event->title }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $event->date->translatedFormat('l, d F Y') }}
                    @if($event->time)
                        · {{ $event->time->format('H:i') }}
                    @endif
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button @click="toggleFullscreen()" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors" title="Повноекранний режим">
                <svg x-show="!isFullscreen" class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                </svg>
                <svg x-show="isFullscreen" x-cloak class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    @if($worshipItems->isEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-12 text-center">
            <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l12-3v13"/>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Немає пісень</h3>
            <p class="text-gray-500 dark:text-gray-400">Для цього служіння ще не додано пісні</p>
        </div>
    @else
        <!-- Song List -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="p-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h2 class="font-semibold text-gray-900 dark:text-white">Порядок пісень</h2>
                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $worshipItems->count() }} пісень</span>
            </div>

            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach($worshipItems as $index => $item)
                    <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer transition-colors"
                         @click="selectSong({{ $index }})"
                         :class="{ 'bg-primary-50 dark:bg-primary-900/20': currentSongIndex === {{ $index }} }">
                        <div class="flex items-center gap-4">
                            <div class="w-8 h-8 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center flex-shrink-0">
                                <span class="text-sm font-semibold text-purple-600 dark:text-purple-400">{{ $index + 1 }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-medium text-gray-900 dark:text-white truncate">{{ $item->title }}</h3>
                                @if($item->notes)
                                    <p class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ $item->notes }}</p>
                                @endif
                            </div>
                            @if($item->start_time)
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ \Carbon\Carbon::parse($item->start_time)->format('H:i') }}
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Song Viewer -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden"
             :class="{ 'fixed inset-0 z-50 rounded-none': isFullscreen }">

            <!-- Viewer Header -->
            <div class="p-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between sticky top-0 bg-white dark:bg-gray-800 z-10">
                <div class="flex items-center gap-4">
                    <button @click="prevSong()" :disabled="currentSongIndex === 0"
                            class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <div class="text-center">
                        <h2 class="font-semibold text-gray-900 dark:text-white" x-text="songs[currentSongIndex]?.title || 'Виберіть пісню'"></h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            <span x-text="currentSongIndex + 1"></span> / <span x-text="songs.length"></span>
                        </p>
                    </div>
                    <button @click="nextSong()" :disabled="currentSongIndex >= songs.length - 1"
                            class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Font Size -->
                    <div class="flex items-center gap-1 bg-gray-100 dark:bg-gray-700 rounded-lg p-1">
                        <button @click="decreaseFontSize()" class="p-1.5 hover:bg-white dark:hover:bg-gray-600 rounded transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                            </svg>
                        </button>
                        <span class="px-2 text-sm font-mono" x-text="fontSize + 'px'"></span>
                        <button @click="increaseFontSize()" class="p-1.5 hover:bg-white dark:hover:bg-gray-600 rounded transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Key Transpose -->
                    <select x-model="transposeKey" @change="loadSongWithKey()"
                            class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-sm font-mono focus:ring-2 focus:ring-primary-500">
                        <option value="">Оригінал</option>
                        @foreach(\App\Models\Song::KEYS as $key => $label)
                            <option value="{{ $key }}">{{ $key }}</option>
                        @endforeach
                    </select>

                    <!-- Fullscreen toggle (in viewer) -->
                    <button @click="toggleFullscreen()" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        <svg x-show="!isFullscreen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                        </svg>
                        <svg x-show="isFullscreen" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9V4.5M9 9H4.5M9 9L3.75 3.75M9 15v4.5M9 15H4.5M9 15l-5.25 5.25M15 9h4.5M15 9V4.5M15 9l5.25-5.25M15 15h4.5M15 15v4.5m0-4.5l5.25 5.25"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Song Content -->
            <div class="p-6 overflow-auto" :style="{ fontSize: fontSize + 'px' }" :class="{ 'h-[calc(100vh-80px)]': isFullscreen }">
                <div x-show="loading" class="flex items-center justify-center py-12">
                    <svg class="animate-spin h-8 w-8 text-primary-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </div>

                <div x-show="!loading && currentSong" x-cloak>
                    <!-- Song Meta -->
                    <div class="flex items-center gap-4 mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
                        <span x-show="currentSong?.key" class="px-3 py-1 bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 font-mono rounded-lg">
                            <span x-text="transposeKey || currentSong?.key"></span>
                            <span x-show="transposeKey && transposeKey !== currentSong?.key" class="text-xs opacity-60">(з <span x-text="currentSong?.key"></span>)</span>
                        </span>
                        <span x-show="currentSong?.bpm" class="px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded-lg">
                            <span x-text="currentSong?.bpm"></span> BPM
                        </span>
                    </div>

                    <!-- Chords/Lyrics -->
                    <div class="font-mono leading-loose whitespace-pre-wrap song-content" x-html="currentSong?.chordsHtml || currentSong?.lyrics || 'Текст не додано'"></div>
                </div>

                <div x-show="!loading && !currentSong" class="text-center py-12 text-gray-500 dark:text-gray-400">
                    <p>Виберіть пісню зі списку вище</p>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
    .song-content .chord {
        @apply inline-block px-1.5 py-0.5 bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300 text-sm font-bold rounded mx-0.5;
    }
</style>

<script>
function musicStand() {
    return {
        songs: @json($worshipItems->map(fn($item) => ['id' => $item->id, 'title' => $item->title, 'notes' => $item->notes])),
        currentSongIndex: 0,
        currentSong: null,
        transposeKey: '',
        fontSize: 18,
        isFullscreen: false,
        loading: false,

        init() {
            if (this.songs.length > 0) {
                this.selectSong(0);
            }

            // Keyboard navigation
            document.addEventListener('keydown', (e) => {
                if (e.key === 'ArrowLeft') this.prevSong();
                if (e.key === 'ArrowRight') this.nextSong();
                if (e.key === 'Escape' && this.isFullscreen) this.toggleFullscreen();
                if (e.key === '+' || e.key === '=') this.increaseFontSize();
                if (e.key === '-') this.decreaseFontSize();
            });
        },

        selectSong(index) {
            this.currentSongIndex = index;
            this.loadSong();
        },

        async loadSong() {
            const songId = this.songs[this.currentSongIndex]?.id;
            if (!songId) return;

            this.loading = true;

            // For now, we'll display the item info directly
            // In a full implementation, you would load the actual song from the database
            const item = this.songs[this.currentSongIndex];
            this.currentSong = {
                title: item.title,
                notes: item.notes,
                chordsHtml: item.notes ? item.notes.replace(/\n/g, '<br>') : null,
            };

            this.loading = false;
        },

        async loadSongWithKey() {
            await this.loadSong();
        },

        prevSong() {
            if (this.currentSongIndex > 0) {
                this.selectSong(this.currentSongIndex - 1);
            }
        },

        nextSong() {
            if (this.currentSongIndex < this.songs.length - 1) {
                this.selectSong(this.currentSongIndex + 1);
            }
        },

        increaseFontSize() {
            if (this.fontSize < 32) this.fontSize += 2;
        },

        decreaseFontSize() {
            if (this.fontSize > 12) this.fontSize -= 2;
        },

        toggleFullscreen() {
            this.isFullscreen = !this.isFullscreen;
            if (this.isFullscreen) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        }
    }
}
</script>
@endsection
