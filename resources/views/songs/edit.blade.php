@extends('layouts.app')

@section('title', 'Редагувати: ' . $song->title)

@section('content')
<div class="max-w-4xl mx-auto">
    <a href="{{ route('songs.show', $song) }}" class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-sm mb-6">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Назад
    </a>

    <form action="{{ route('songs.update', $song) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Редагувати пісню</h2>
                <button type="button" onclick="if(confirm('Видалити цю пісню?')) document.getElementById('delete-song-form').submit()"
                        class="text-red-600 hover:text-red-700 text-sm">Видалити</button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Назва <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title', $song->title) }}" required
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Автор</label>
                    <input type="text" name="artist" value="{{ old('artist', $song->artist) }}" list="artists-list"
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"
                           placeholder="Виберіть або введіть">
                    <datalist id="artists-list">
                        @foreach($artists as $artist)
                            <option value="{{ $artist }}">
                        @endforeach
                    </datalist>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Тональність</label>
                    <select name="key" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                        <option value="">Не вказано</option>
                        @foreach(\App\Models\Song::KEYS as $key => $label)
                            <option value="{{ $key }}" {{ old('key', $song->key) === $key ? 'selected' : '' }}>{{ $key }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">BPM</label>
                    <input type="number" name="bpm" value="{{ old('bpm', $song->bpm) }}" min="30" max="300"
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                </div>
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Теги</label>
                @php $selectedTags = old('tags', $song->tags ?? []); @endphp
                @if($allTags->count() > 0)
                    <div class="flex flex-wrap gap-2 mb-3">
                        @foreach($allTags as $tag)
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="tags[]" value="{{ $tag }}"
                                       {{ in_array($tag, $selectedTags) ? 'checked' : '' }}
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
                       placeholder="Або додайте нові теги через кому">
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Коментарі</label>
                <textarea name="notes" rows="3"
                          class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"
                          placeholder="Нотатки для команди">{{ old('notes', $song->notes) }}</textarea>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Текст і акорди</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Текст</label>
                    <textarea name="lyrics" rows="6" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 font-mono text-sm">{{ old('lyrics', $song->lyrics) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Текст з акордами</label>
                    <textarea name="chords" rows="10" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 font-mono text-sm">{{ old('chords', $song->chords) }}</textarea>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Посилання</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">CCLI</label>
                    <input type="text" name="ccli_number" value="{{ old('ccli_number', $song->ccli_number) }}"
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">YouTube</label>
                    <input type="url" name="youtube_url" value="{{ old('youtube_url', $song->youtube_url) }}"
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Spotify</label>
                    <input type="url" name="spotify_url" value="{{ old('spotify_url', $song->spotify_url) }}"
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                </div>
            </div>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('songs.show', $song) }}" class="px-4 py-2 text-gray-700 dark:text-gray-300">Скасувати</a>
            <button type="submit" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg">Зберегти</button>
        </div>
    </form>

    <form id="delete-song-form" action="{{ route('songs.destroy', $song) }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>
@endsection
