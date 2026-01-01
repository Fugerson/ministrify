@extends('layouts.app')

@section('title', 'Мій розклад')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Мій розклад</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Ваші майбутні відповідальності</p>
        </div>

        <div class="divide-y divide-gray-100 dark:divide-gray-700">
            @forelse($responsibilities as $responsibility)
                <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-3">
                                <div class="text-center">
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $responsibility->event->date->format('d') }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">{{ $responsibility->event->date->translatedFormat('M') }}</p>
                                </div>
                                <div>
                                    <a href="{{ route('events.show', $responsibility->event) }}" class="font-medium text-gray-900 dark:text-white hover:text-primary-600 dark:hover:text-primary-400">
                                        {{ $responsibility->event->title }}
                                    </a>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $responsibility->event->time?->format('H:i') }} - {{ $responsibility->name }}
                                    </p>
                                    @if($responsibility->event->ministry)
                                        <p class="text-xs text-gray-400">{{ $responsibility->event->ministry->name }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="text-xs px-2 py-1 rounded-full
                                @if($responsibility->isConfirmed()) bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400
                                @elseif($responsibility->isPending()) bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400
                                @elseif($responsibility->isDeclined()) bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400
                                @else bg-gray-100 text-gray-700 dark:bg-gray-600 dark:text-gray-300 @endif">
                                {{ $responsibility->status_label }}
                            </span>

                            @if($responsibility->isPending())
                                <div class="flex gap-1">
                                    <form method="POST" action="{{ route('responsibilities.confirm', $responsibility) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="p-1.5 text-green-600 hover:bg-green-100 dark:hover:bg-green-900/30 rounded-lg" title="Підтвердити">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('responsibilities.decline', $responsibility) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="p-1.5 text-red-600 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-lg" title="Відхилити">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p>У вас немає майбутніх відповідальностей</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
