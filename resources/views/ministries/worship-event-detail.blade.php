@extends('layouts.app')

@section('title', $event->title . ' - Команда')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('ministries.worship-events', $ministry) }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $event->title }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $event->date->translatedFormat('l, j F Y') }} о {{ $event->time?->format('H:i') }}
                </p>
            </div>
        </div>
        <a href="{{ route('events.show', $event) }}" class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400">
            Перейти до події
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Songs Section -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                    </svg>
                    Пісні
                </h2>
            </div>

            @if($event->songs->count() > 0)
                <div class="space-y-2 mb-4" x-data="{ dragging: null }">
                    @foreach($event->songs as $index => $song)
                        <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg group">
                            <span class="w-6 h-6 rounded-full bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 flex items-center justify-center text-sm font-medium">
                                {{ $index + 1 }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-900 dark:text-white truncate">{{ $song->title }}</p>
                                @if($song->pivot->key)
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Тональність: {{ $song->pivot->key }}</span>
                                @endif
                            </div>
                            <a href="{{ route('songs.show', $song) }}" class="p-1.5 text-gray-400 hover:text-primary-600 dark:hover:text-primary-400" title="Переглянути">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            <form action="{{ route('events.songs.remove', [$event, $song]) }}" method="POST" class="opacity-0 group-hover:opacity-100 transition-opacity">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 dark:hover:text-red-400" title="Видалити">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Пісні ще не додані</p>
            @endif

            <!-- Add Song Form -->
            <form action="{{ route('events.songs.add', $event) }}" method="POST" class="border-t border-gray-200 dark:border-gray-700 pt-4">
                @csrf
                <div class="flex gap-2">
                    <select name="song_id" required class="flex-1 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <option value="">Оберіть пісню...</option>
                        @foreach($availableSongs as $song)
                            @if(!$event->songs->contains('id', $song->id))
                                <option value="{{ $song->id }}">{{ $song->title }} @if($song->key)({{ $song->key }})@endif</option>
                            @endif
                        @endforeach
                    </select>
                    <input type="text" name="key" placeholder="Тональність" class="w-24 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">
                        Додати
                    </button>
                </div>
            </form>
        </div>

        <!-- Team Section -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Команда
                </h2>
            </div>

            @if($worshipRoles->count() > 0)
                <div class="space-y-4 mb-4">
                    @foreach($worshipRoles as $role)
                        @php
                            $roleMembers = $event->worshipTeam->where('worship_role_id', $role->id);
                        @endphp
                        <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $role->name }}</span>
                            </div>
                            @if($roleMembers->count() > 0)
                                <div class="space-y-1">
                                    @foreach($roleMembers as $member)
                                        <div class="flex items-center justify-between py-1">
                                            <span class="text-sm text-gray-900 dark:text-white">{{ $member->person?->full_name ?? 'Видалено' }}</span>
                                            <form action="{{ route('events.worship-team.remove', [$event, $member]) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1 text-gray-400 hover:text-red-600 dark:hover:text-red-400">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-xs text-gray-400 dark:text-gray-500">Не призначено</p>
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- Add Team Member Form -->
                <form action="{{ route('events.worship-team.add', $event) }}" method="POST" class="border-t border-gray-200 dark:border-gray-700 pt-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-2 mb-2">
                        <select name="person_id" required class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="">Оберіть учасника...</option>
                            @foreach($members as $member)
                                <option value="{{ $member->id }}">{{ $member->full_name }}</option>
                            @endforeach
                        </select>
                        <select name="worship_role_id" required class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="">Роль...</option>
                            @foreach($worshipRoles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="w-full px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">
                        Додати учасника
                    </button>
                </form>
            @else
                <div class="text-center py-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Спочатку налаштуйте ролі</p>
                    <a href="{{ route('settings.worship-roles') }}" class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400">
                        Налаштувати ролі
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
