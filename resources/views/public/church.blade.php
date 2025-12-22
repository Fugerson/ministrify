@extends('public.layout')

@section('title', $church->name)

@section('content')
<!-- Hero Section -->
<section class="relative overflow-hidden">
    @if($church->cover_image)
        <div class="absolute inset-0">
            <img src="{{ Storage::url($church->cover_image) }}" alt="" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-r from-gray-900/90 to-gray-900/70"></div>
        </div>
    @else
        <div class="absolute inset-0 bg-gradient-to-br from-primary-600 via-primary-700 to-primary-900"></div>
        <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width=\"60\" height=\"60\" viewBox=\"0 0 60 60\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cg fill=\"none\" fill-rule=\"evenodd\"%3E%3Cg fill=\"%23ffffff\" fill-opacity=\"0.05\"%3E%3Cpath d=\"M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')]"></div>
    @endif

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 md:py-32">
        <div class="max-w-3xl">
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-6 leading-tight">
                {{ $church->name }}
            </h1>
            @if($church->public_description)
                <p class="text-xl text-white/80 mb-8 leading-relaxed">
                    {{ $church->public_description }}
                </p>
            @endif
            <div class="flex flex-wrap gap-4">
                <a href="{{ route('public.events', $church->slug) }}"
                   class="inline-flex items-center gap-2 px-6 py-3 bg-white text-primary-700 font-semibold rounded-xl hover:bg-gray-100 transition-colors shadow-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Наші події
                </a>
                <a href="{{ route('public.donate', $church->slug) }}"
                   class="inline-flex items-center gap-2 px-6 py-3 bg-primary-500 text-white font-semibold rounded-xl hover:bg-primary-400 transition-colors border-2 border-white/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                    Підтримати
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Service Times -->
@if($church->service_times)
<section class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-wrap items-center justify-center gap-8 text-center">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-primary-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="text-left">
                    <p class="text-sm text-gray-500">Розклад богослужінь</p>
                    <p class="font-semibold text-gray-900">{{ $church->service_times }}</p>
                </div>
            </div>
            @if($church->address)
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-primary-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div class="text-left">
                    <p class="text-sm text-gray-500">Адреса</p>
                    <p class="font-semibold text-gray-900">{{ $church->address }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</section>
@endif

<!-- Pastor's Message -->
@if($church->pastor_name && $church->pastor_message)
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-3xl shadow-xl overflow-hidden">
            <div class="md:flex">
                @if($church->pastor_photo)
                <div class="md:w-1/3">
                    <img src="{{ Storage::url($church->pastor_photo) }}" alt="{{ $church->pastor_name }}" class="w-full h-64 md:h-full object-cover">
                </div>
                @endif
                <div class="p-8 md:p-12 {{ $church->pastor_photo ? 'md:w-2/3' : '' }}">
                    <div class="flex items-center gap-2 text-primary-600 mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                        <span class="text-sm font-medium">Слово пастора</span>
                    </div>
                    <blockquote class="text-xl md:text-2xl text-gray-700 leading-relaxed mb-6 italic">
                        "{{ $church->pastor_message }}"
                    </blockquote>
                    <p class="font-semibold text-gray-900">{{ $church->pastor_name }}</p>
                    <p class="text-sm text-gray-500">Пастор церкви</p>
                </div>
            </div>
        </div>
    </div>
</section>
@endif

<!-- Upcoming Events -->
@if($upcomingEvents->count() > 0)
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
                                <h3 class="font-semibold text-gray-900 group-hover:text-primary-600 transition-colors truncate">{{ $event->title }}</h3>
                                <p class="text-sm text-gray-500">{{ $event->time->format('H:i') }}@if($event->location) &bull; {{ $event->location }}@endif</p>
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

<!-- Ministries -->
@if($ministries->count() > 0)
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900">Наші служіння</h2>
            <p class="text-gray-600 mt-2">Знайдіть своє місце в служінні</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($ministries as $ministry)
                <a href="{{ route('public.ministry', [$church->slug, $ministry->slug]) }}"
                   class="group bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-xl hover:border-primary-200 transition-all duration-300">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 rounded-xl flex items-center justify-center flex-shrink-0"
                             style="background-color: {{ $ministry->color }}20;">
                            @if($ministry->icon)
                                <span class="text-2xl">{{ $ministry->icon }}</span>
                            @else
                                <svg class="w-7 h-7" style="color: {{ $ministry->color }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-gray-900 group-hover:text-primary-600 transition-colors">{{ $ministry->name }}</h3>
                            @if($ministry->public_description)
                                <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ $ministry->public_description }}</p>
                            @elseif($ministry->description)
                                <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ $ministry->description }}</p>
                            @endif
                            <p class="text-xs text-gray-400 mt-2">{{ $ministry->members_count }} учасників</p>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Groups -->
@if($groups->count() > 0)
<section class="py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900">Малі групи</h2>
            <p class="text-gray-600 mt-2">Спільнота, яка росте разом</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($groups as $group)
                <a href="{{ route('public.group', [$church->slug, $group->slug]) }}"
                   class="group bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl hover:border-primary-200 transition-all duration-300">
                    @if($group->cover_image)
                        <div class="h-40 overflow-hidden">
                            <img src="{{ Storage::url($group->cover_image) }}" alt="{{ $group->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        </div>
                    @else
                        <div class="h-40 flex items-center justify-center" style="background: linear-gradient(135deg, {{ $group->color ?? '#6366f1' }} 0%, {{ $group->color ?? '#6366f1' }}99 100%);">
                            <svg class="w-12 h-12 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                    @endif
                    <div class="p-5">
                        <h3 class="font-semibold text-gray-900 group-hover:text-primary-600 transition-colors">{{ $group->name }}</h3>
                        @if($group->meeting_schedule || ($group->meeting_day && $group->meeting_time))
                            <p class="text-sm text-gray-500 mt-1">
                                @if($group->meeting_schedule)
                                    {{ $group->meeting_schedule }}
                                @else
                                    {{ $group->meeting_day_name }}, {{ $group->meeting_time->format('H:i') }}
                                @endif
                            </p>
                        @endif
                        <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100">
                            <span class="text-xs text-gray-400">{{ $group->members_count }} учасників</span>
                            @if($group->allow_join_requests)
                                <span class="text-xs text-primary-600 font-medium">Приєднатися</span>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Donation Campaigns -->
@if($campaigns->count() > 0)
<section class="py-16 bg-gradient-to-br from-primary-50 to-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900">Підтримайте нас</h2>
            <p class="text-gray-600 mt-2">Ваша пожертва допомагає нам служити громаді</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl mx-auto">
            @foreach($campaigns as $campaign)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-semibold text-gray-900 text-lg">{{ $campaign->name }}</h3>
                    @if($campaign->description)
                        <p class="text-sm text-gray-500 mt-2">{{ $campaign->description }}</p>
                    @endif
                    @if($campaign->goal_amount)
                        <div class="mt-4">
                            <div class="flex justify-between text-sm mb-2">
                                <span class="text-gray-500">Зібрано</span>
                                <span class="font-medium text-gray-900">{{ number_format($campaign->raised_amount, 0, ',', ' ') }} / {{ number_format($campaign->goal_amount, 0, ',', ' ') }} грн</span>
                            </div>
                            <div class="w-full h-3 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-primary-500 rounded-full transition-all duration-500" style="width: {{ $campaign->progress_percent }}%"></div>
                            </div>
                        </div>
                    @endif
                    <a href="{{ route('public.donate', $church->slug) }}?campaign={{ $campaign->name }}"
                       class="mt-4 block w-full py-2.5 text-center text-sm font-medium text-primary-600 hover:text-primary-700 border border-primary-200 hover:border-primary-300 rounded-xl transition-colors">
                        Пожертвувати
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- CTA Section -->
<section class="py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gradient-to-r from-primary-600 to-primary-700 rounded-3xl p-8 md:p-12 text-center text-white relative overflow-hidden">
            <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width=\"60\" height=\"60\" viewBox=\"0 0 60 60\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cg fill=\"none\" fill-rule=\"evenodd\"%3E%3Cg fill=\"%23ffffff\" fill-opacity=\"0.1\"%3E%3Cpath d=\"M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')]"></div>
            <div class="relative">
                <h2 class="text-3xl md:text-4xl font-bold mb-4">Приєднуйтесь до нас</h2>
                <p class="text-xl text-white/80 mb-8 max-w-2xl mx-auto">
                    Будемо раді бачити вас на наших богослужіннях та заходах
                </p>
                <div class="flex flex-wrap justify-center gap-4">
                    <a href="{{ route('public.contact', $church->slug) }}"
                       class="px-8 py-3 bg-white text-primary-700 font-semibold rounded-xl hover:bg-gray-100 transition-colors shadow-lg">
                        Зв'язатися з нами
                    </a>
                    <a href="{{ route('public.events', $church->slug) }}"
                       class="px-8 py-3 bg-primary-500 text-white font-semibold rounded-xl hover:bg-primary-400 transition-colors border-2 border-white/20">
                        Переглянути події
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
@endsection
