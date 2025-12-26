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
<div class="space-y-6">
    <!-- Search & Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
        <form class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"
                       placeholder="Пошук пісень...">
            </div>
            <select name="key" onchange="this.form.submit()"
                    class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                <option value="">Усі тональності</option>
                @foreach(\App\Models\Song::KEYS as $key => $label)
                    <option value="{{ $key }}" {{ request('key') === $key ? 'selected' : '' }}>{{ $key }}</option>
                @endforeach
            </select>
            <select name="tag" onchange="this.form.submit()"
                    class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                <option value="">Усі теги</option>
                @foreach($allTags as $tag)
                    <option value="{{ $tag }}" {{ request('tag') === $tag ? 'selected' : '' }}>{{ $tag }}</option>
                @endforeach
            </select>
            <select name="sort" onchange="this.form.submit()"
                    class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                <option value="title" {{ request('sort') === 'title' ? 'selected' : '' }}>За назвою</option>
                <option value="recent" {{ request('sort') === 'recent' ? 'selected' : '' }}>Нові</option>
                <option value="popular" {{ request('sort') === 'popular' ? 'selected' : '' }}>Популярні</option>
                <option value="last_used" {{ request('sort') === 'last_used' ? 'selected' : '' }}>Недавно використані</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg">
                Знайти
            </button>
        </form>
    </div>

    <!-- Songs Grid -->
    @if($songs->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($songs as $song)
                <a href="{{ route('songs.show', $song) }}"
                   class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 hover:shadow-md transition-all hover:-translate-y-0.5 group">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-12 h-12 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center text-white group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                            </svg>
                        </div>
                        @if($song->key)
                            <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 text-xs font-mono rounded">
                                {{ $song->key }}
                            </span>
                        @endif
                    </div>

                    <h3 class="font-semibold text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 line-clamp-1">
                        {{ $song->title }}
                    </h3>

                    @if($song->artist)
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $song->artist }}</p>
                    @endif

                    @if($song->tags && count($song->tags) > 0)
                        <div class="flex flex-wrap gap-1 mt-3">
                            @foreach(array_slice($song->tags, 0, 3) as $tag)
                                <span class="px-2 py-0.5 bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 text-xs rounded-full">
                                    {{ $tag }}
                                </span>
                            @endforeach
                        </div>
                    @endif

                    <div class="flex items-center justify-between mt-4 text-xs text-gray-500 dark:text-gray-400">
                        <span>{{ $song->times_used }} разів</span>
                        @if($song->bpm)
                            <span>{{ $song->bpm }} BPM</span>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $songs->links() }}
        </div>
    @else
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
    @endif
</div>
@endsection
