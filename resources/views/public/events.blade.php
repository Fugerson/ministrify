@extends('public.layout')

@section('title', 'Події - ' . $church->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Header -->
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Наші події</h1>
        <p class="text-xl text-gray-600 max-w-2xl mx-auto">
            Приєднуйтесь до нас на богослужіннях, семінарах та інших заходах нашої громади
        </p>
    </div>

    @if($events->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($events as $event)
                <a href="{{ route('public.event', [$church->slug, $event]) }}"
                   class="group bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl hover:border-primary-200 transition-all duration-300">
                    @if($event->cover_image)
                        <div class="h-48 overflow-hidden">
                            <img src="{{ Storage::url($event->cover_image) }}" alt="{{ $event->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
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
                                <h3 class="font-semibold text-gray-900 group-hover:text-primary-600 transition-colors">{{ $event->title }}</h3>
                                <p class="text-sm text-gray-500">{{ $event->time->format('H:i') }}@if($event->location) &bull; {{ $event->location }}@endif</p>
                            </div>
                        </div>
                        @if($event->public_description)
                            <p class="text-sm text-gray-500 line-clamp-2 mb-3">{{ $event->public_description }}</p>
                        @endif
                        @if($event->allow_registration)
                            <div class="pt-3 border-t border-gray-100 flex items-center justify-between">
                                <span class="inline-flex items-center gap-1 text-sm text-primary-600 font-medium">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                    Реєстрація відкрита
                                </span>
                                @if($event->registration_limit)
                                    <span class="text-xs text-gray-400">
                                        {{ $event->remaining_spaces ?? 0 }} місць
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-12">
            {{ $events->links() }}
        </div>
    @else
        <div class="text-center py-16">
            <div class="w-20 h-20 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Немає запланованих подій</h3>
            <p class="text-gray-500">Слідкуйте за оновленнями - нові події з'являться незабаром</p>
        </div>
    @endif
</div>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
@endsection
