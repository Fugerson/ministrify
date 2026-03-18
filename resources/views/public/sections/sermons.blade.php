{{-- Sermons Section --}}
@if(isset($sermons) && $sermons->count() > 0)
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900">{{ __('app.public_sermons_title') }}</h2>
            <p class="text-gray-600 mt-2">{{ __('app.public_sermons_subtitle') }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($sermons as $sermon)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-shadow">
                    @if($sermon->thumbnail)
                        <div class="h-48 overflow-hidden">
                            <img src="{{ Storage::url($sermon->thumbnail) }}" alt="{{ $sermon->title }}" class="w-full h-full object-cover" loading="lazy">
                        </div>
                    @elseif($sermon->youtube_url)
                        @php
                            $videoId = $sermon->youtube_id;
                        @endphp
                        @if($videoId)
                            <div class="h-48 overflow-hidden">
                                <img src="https://img.youtube.com/vi/{{ $videoId }}/maxresdefault.jpg" alt="{{ $sermon->title }}" class="w-full h-full object-cover" loading="lazy">
                            </div>
                        @endif
                    @else
                        <div class="h-48 bg-gradient-to-br from-red-500 to-red-700 flex items-center justify-center">
                            <svg class="w-16 h-16 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    @endif
                    <div class="p-5">
                        <p class="text-sm text-gray-500 mb-2">{{ $sermon->sermon_date->translatedFormat('d F Y') }}</p>
                        <h3 class="font-semibold text-gray-900">{{ $sermon->title }}</h3>
                        @if($sermon->speaker?->name)
                            <p class="text-sm text-primary-600 mt-1">{{ $sermon->speaker->name }}</p>
                        @endif
                        @if($sermon->hasVideo() || $sermon->hasAudio())
                            <div class="flex gap-2 mt-4">
                                @if($sermon->youtube_url)
                                    <a href="{{ $sermon->youtube_url }}" target="_blank" class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-100 text-red-700 rounded-lg text-sm hover:bg-red-200 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                        </svg>
                                        {{ __('app.public_sermons_watch') }}
                                    </a>
                                @elseif($sermon->vimeo_url)
                                    <a href="{{ $sermon->vimeo_url }}" target="_blank" class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg text-sm hover:bg-blue-200 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                        </svg>
                                        {{ __('app.public_sermons_watch') }}
                                    </a>
                                @endif
                                @if($sermon->podcast_url)
                                    <a href="{{ $sermon->podcast_url }}" target="_blank" class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
                                        </svg>
                                        {{ __('app.public_sermons_listen') }}
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@else
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900">{{ __('app.public_sermons_title') }}</h2>
            <p class="text-gray-600 mt-2">{{ __('app.public_sermons_subtitle') }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach([
                ['title' => __('app.public_sermon_demo_1'), 'speaker' => __('app.public_sermon_demo_speaker'), 'date' => now()->subDays(7)->translatedFormat('d F Y'), 'color' => 'from-primary-500 to-primary-700'],
                ['title' => __('app.public_sermon_demo_2'), 'speaker' => __('app.public_sermon_demo_speaker'), 'date' => now()->subDays(14)->translatedFormat('d F Y'), 'color' => 'from-indigo-500 to-indigo-700'],
                ['title' => __('app.public_sermon_demo_3'), 'speaker' => __('app.public_sermon_demo_speaker'), 'date' => now()->subDays(21)->translatedFormat('d F Y'), 'color' => 'from-purple-500 to-purple-700'],
            ] as $sermon)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-shadow">
                    <div class="h-48 bg-gradient-to-br {{ $sermon['color'] }} flex items-center justify-center">
                        <svg class="w-16 h-16 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="p-5">
                        <p class="text-sm text-gray-500 mb-2">{{ $sermon['date'] }}</p>
                        <h3 class="font-semibold text-gray-900">{{ $sermon['title'] }}</h3>
                        <p class="text-sm text-primary-600 mt-1">{{ $sermon['speaker'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
