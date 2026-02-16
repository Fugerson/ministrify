{{-- Events Section --}}
@if(isset($upcomingEvents) && $upcomingEvents->count() > 0)
<section class="py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-3xl font-bold text-gray-900">Найближчі події</h2>
                <p class="text-gray-600 mt-1">Приєднуйтесь до нас</p>
            </div>
            <a href="{{ route('public.events', $church->slug) }}" class="text-primary-600 hover:text-primary-700 font-medium flex items-center gap-1">
                Всі події
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($upcomingEvents as $event)
                <a href="{{ route('public.event', [$church->slug, $event]) }}"
                   class="group bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl hover:border-primary-200 transition-all duration-300">
                    @if($event->cover_image)
                        <div class="h-48 overflow-hidden">
                            <img src="{{ Storage::url($event->cover_image) }}" alt="{{ $event->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">
                        </div>
                    @else
                        <div class="h-48 bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center">
                            <svg class="w-16 h-16 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    @endif
                    <div class="p-5">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="flex-shrink-0 w-14 text-center">
                                <p class="text-2xl font-bold text-primary-600">{{ $event->date->format('d') }}</p>
                                <p class="text-xs text-gray-500 uppercase">{{ $event->date->translatedFormat('M') }}</p>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-gray-900 group-hover:text-primary-600 transition-colors truncate">{{ $event->title }}</h3>
                                <p class="text-sm text-gray-500">{{ $event->time?->format('H:i') }}@if($event->location) &bull; {{ $event->location }}@endif</p>
                            </div>
                        </div>
                        @if($event->allow_registration)
                            <div class="pt-3 border-t border-gray-100">
                                <span class="inline-flex items-center gap-1 text-sm text-primary-600 font-medium">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                    Реєстрація відкрита
                                </span>
                            </div>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif
