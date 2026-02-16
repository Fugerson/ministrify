{{-- Sermons Section --}}
@if(isset($sermons) && $sermons->count() > 0)
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900">Проповіді</h2>
            <p class="text-gray-600 mt-2">Слово Боже для вашого життя</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($sermons as $sermon)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-shadow">
                    @if($sermon->thumbnail)
                        <div class="h-48 overflow-hidden">
                            <img src="{{ Storage::url($sermon->thumbnail) }}" alt="{{ $sermon->title }}" class="w-full h-full object-cover" loading="lazy">
                        </div>
                    @elseif($sermon->video_url && str_contains($sermon->video_url, 'youtube'))
                        @php
                            preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $sermon->video_url, $matches);
                            $videoId = $matches[1] ?? null;
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
                        @if($sermon->speaker_name)
                            <p class="text-sm text-primary-600 mt-1">{{ $sermon->speaker_name }}</p>
                        @endif
                        @if($sermon->video_url || $sermon->audio_url)
                            <div class="flex gap-2 mt-4">
                                @if($sermon->video_url)
                                    <a href="{{ $sermon->video_url }}" target="_blank" class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-100 text-red-700 rounded-lg text-sm hover:bg-red-200 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                        </svg>
                                        Дивитись
                                    </a>
                                @endif
                                @if($sermon->audio_url)
                                    <a href="{{ $sermon->audio_url }}" target="_blank" class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
                                        </svg>
                                        Слухати
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
@endif
