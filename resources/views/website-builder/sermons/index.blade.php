@extends('layouts.app')

@section('title', __('app.wb_sermons_title'))

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('website-builder.index') }}" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('app.wb_sermons_title') }}</h1>
                <p class="text-gray-600 dark:text-gray-400">{{ __('app.wb_sermons_subtitle') }}</p>
            </div>
        </div>
        @if(auth()->user()->canEdit('website'))
        <div class="flex gap-2">
            <a href="{{ route('website-builder.sermons.series.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition-colors">
                {{ __('app.wb_series') }}
            </a>
            <a href="{{ route('website-builder.sermons.create') }}" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('app.wb_add_sermon') }}
            </a>
        </div>
        @endif
    </div>


    @if($sermons->isEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
            <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('app.wb_no_sermons_yet') }}</h3>
            <p class="text-gray-500 dark:text-gray-400 mt-1">{{ __('app.wb_add_sermon_hint') }}</p>
            @if(auth()->user()->canEdit('website'))
            <a href="{{ route('website-builder.sermons.create') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors mt-4">
                {{ __('app.wb_add_sermon') }}
            </a>
            @endif
        </div>
    @else
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 divide-y divide-gray-200 dark:divide-gray-700">
            @foreach($sermons as $sermon)
                <div class="flex items-center gap-4 p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <!-- Thumbnail -->
                    <div class="w-24 h-16 rounded-lg bg-gray-100 dark:bg-gray-700 flex-shrink-0 overflow-hidden relative">
                        @if($sermon->thumbnail)
                            <img src="{{ Storage::url($sermon->thumbnail) }}" alt="{{ $sermon->title }}" class="w-full h-full object-cover" loading="lazy">
                        @elseif($sermon->thumbnail_url)
                            <img src="{{ $sermon->thumbnail_url }}" alt="{{ $sermon->title }}" class="w-full h-full object-cover" loading="lazy">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                </svg>
                            </div>
                        @endif
                        @if($sermon->hasVideo())
                            <div class="absolute inset-0 flex items-center justify-center bg-black/30">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M8 5v14l11-7z"/>
                                </svg>
                            </div>
                        @endif
                    </div>

                    <!-- Info -->
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-900 dark:text-white truncate">{{ $sermon->title }}</p>
                        <div class="flex items-center gap-2 mt-1 text-sm text-gray-500 dark:text-gray-400">
                            @if($sermon->series)
                                <span class="px-2 py-0.5 bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400 rounded text-xs">{{ $sermon->series->title }}</span>
                            @endif
                            <span>{{ $sermon->speaker?->name ?? __('app.wb_speaker_default') }}</span>
                            <span>&middot;</span>
                            <span>{{ $sermon->sermon_date->format('d.m.Y') }}</span>
                        </div>
                    </div>

                    <!-- Type Icons -->
                    <div class="flex items-center gap-2">
                        @if($sermon->hasVideo())
                            <span class="p-1.5 bg-red-100 dark:bg-red-900/30 rounded-full" title="{{ __('app.wb_video') }}">
                                <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                            </span>
                        @endif
                        @if($sermon->hasAudio())
                            <span class="p-1.5 bg-blue-100 dark:bg-blue-900/30 rounded-full" title="{{ __('app.wb_audio') }}">
                                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
                                </svg>
                            </span>
                        @endif
                    </div>

                    <!-- Status -->
                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $sermon->is_public ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                        {{ $sermon->is_public ? __('app.wb_public') : __('app.wb_hidden') }}
                    </span>

                    <!-- Actions -->
                    @if(auth()->user()->canEdit('website'))
                    <div class="flex gap-1">
                        <a href="{{ route('website-builder.sermons.edit', $sermon) }}" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                        <button type="button" @click="ajaxDelete('{{ route('website-builder.sermons.destroy', $sermon) }}', @js( __('messages.confirm_delete_sermon') ), () => $el.closest('.flex.items-center.gap-4').remove())" class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $sermons->links() }}
        </div>
    @endif
</div>
@endsection
