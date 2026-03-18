@extends('layouts.app')

@section('title', __('app.songs_add_title'))

@section('content')
<div class="max-w-4xl mx-auto">
    <a href="{{ route('songs.index') }}" class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-sm mb-6">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        {{ __('app.songs_back_to_library') }}
    </a>

    <form @submit.prevent="submit($refs.f)" x-ref="f" x-data="{ ...ajaxForm({url:'{{ route("songs.store") }}', method:'POST'}) }" class="space-y-6">

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z"/>
                </svg>
                {{ __('app.songs_basic_info') }}
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('app.songs_title_required') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                    <template x-if="errors.title">
                        <p class="mt-1 text-sm text-red-500" x-text="errors.title[0]"></p>
                    </template>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('app.songs_author_artist_label') }}
                    </label>
                    <input type="text" name="artist" value="{{ old('artist') }}" list="artists-list"
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"
                           placeholder="{{ __('app.songs_select_or_enter_new') }}">
                    <datalist id="artists-list">
                        @foreach($artists as $artist)
                            <option value="{{ $artist }}">
                        @endforeach
                    </datalist>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('app.songs_key_label') }}
                    </label>
                    <select name="key"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                        <option value="">{{ __('app.songs_not_specified') }}</option>
                        @foreach(\App\Models\Song::keyLabels() as $key => $label)
                            <option value="{{ $key }}" {{ old('key') === $key ? 'selected' : '' }}>{{ $key }} - {{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('app.songs_bpm_tempo_label') }}
                    </label>
                    <input type="number" name="bpm" value="{{ old('bpm') }}" min="30" max="300"
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"
                           placeholder="120">
                </div>
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('app.songs_tags_label') }}
                </label>
                @if($allTags->count() > 0)
                    <div class="flex flex-wrap gap-2 mb-3">
                        @foreach($allTags as $tag)
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="tags[]" value="{{ $tag }}"
                                       {{ in_array($tag, old('tags', [])) ? 'checked' : '' }}
                                       class="sr-only peer">
                                <span class="px-3 py-1.5 rounded-full text-sm border transition-all
                                       peer-checked:bg-primary-600 peer-checked:text-white peer-checked:border-primary-600
                                       border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300
                                       hover:border-primary-400 dark:hover:border-primary-500">
                                    {{ $tag }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                @endif
                <input type="text" name="new_tag" value="{{ old('new_tag') }}"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"
                       placeholder="{{ __('app.songs_add_new_tags') }}">
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    {{ __('app.songs_comments_label') }}
                </label>
                <textarea name="notes" rows="3"
                          class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"
                          placeholder="{{ __('app.songs_team_notes_extended') }}">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('app.songs_lyrics_and_chords') }}</h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('app.songs_song_lyrics_label') }}
                    </label>
                    <textarea name="lyrics" rows="8"
                              class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 font-mono text-sm"
                              placeholder="Verse 1:
Holy, Holy, Holy...

Chorus:
...">{{ old('lyrics') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('app.songs_chordpro_label') }}
                    </label>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                        {{ __('app.songs_chordpro_hint') }}
                    </p>
                    <textarea name="chords" rows="10"
                              class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 font-mono text-sm"
                              placeholder="{{ __('app.ministry_chords_placeholder') }}">{{ old('chords') }}</textarea>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('app.songs_links_label') }}</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('app.songs_ccli_number') }}
                    </label>
                    <input type="text" name="ccli_number" value="{{ old('ccli_number') }}"
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"
                           placeholder="123456">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        YouTube
                    </label>
                    <input type="url" name="youtube_url" value="{{ old('youtube_url') }}"
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"
                           placeholder="https://youtube.com/watch?v=...">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Spotify
                    </label>
                    <input type="url" name="spotify_url" value="{{ old('spotify_url') }}"
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"
                           placeholder="https://open.spotify.com/track/...">
                </div>
            </div>
        </div>

        <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 sm:gap-3">
            <a href="{{ route('songs.index') }}"
               class="w-full sm:w-auto px-4 py-2 text-center text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                {{ __('app.songs_cancel') }}
            </a>
            <button type="submit" :disabled="saving"
                    class="w-full sm:w-auto px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50">
                <span x-show="!saving">{{ __('app.songs_save_song') }}</span>
                <span x-show="saving" class="flex items-center justify-center gap-2">
                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    {{ __('app.songs_saving') }}
                </span>
            </button>
        </div>
    </form>
</div>
@endsection
