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
                {{-- TODO: Розкоментувати після бета-тестування
                <a href="{{ route('public.donate', $church->slug) }}"
                   class="inline-flex items-center gap-2 px-6 py-3 bg-primary-500 text-white font-semibold rounded-xl hover:bg-primary-400 transition-colors border-2 border-white/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                    Підтримати
                </a>
                --}}
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

<!-- Leadership / Staff -->
@if(isset($staff) && $staff->count() > 0)
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900">Наша команда</h2>
            <p class="text-gray-600 mt-2">Люди, які служать у церкві</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($staff as $member)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-shadow">
                    @if($member->photo)
                        <div class="h-56 overflow-hidden">
                            <img src="{{ Storage::url($member->photo) }}" alt="{{ $member->name }}" class="w-full h-full object-cover">
                        </div>
                    @else
                        <div class="h-56 bg-gradient-to-br from-primary-100 to-primary-200 flex items-center justify-center">
                            <svg class="w-20 h-20 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                    @endif
                    <div class="p-5 text-center">
                        <h3 class="font-semibold text-gray-900">{{ $member->name }}</h3>
                        <p class="text-sm text-primary-600">{{ $member->title }}</p>
                        @if($member->bio)
                            <p class="text-sm text-gray-500 mt-2 line-clamp-2">{{ $member->bio }}</p>
                        @endif
                        @if($member->email || $member->facebook_url || $member->instagram_url)
                            <div class="flex items-center justify-center gap-3 mt-4">
                                @if($member->email)
                                    <a href="mailto:{{ $member->email }}" class="text-gray-400 hover:text-primary-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    </a>
                                @endif
                                @if($member->facebook_url)
                                    <a href="{{ $member->facebook_url }}" target="_blank" class="text-gray-400 hover:text-blue-600">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                    </a>
                                @endif
                                @if($member->instagram_url)
                                    <a href="{{ $member->instagram_url }}" target="_blank" class="text-gray-400 hover:text-pink-600">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z"/></svg>
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
                            <svg class="w-7 h-7" style="color: {{ $ministry->color }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
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

<!-- Sermons -->
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
                            <img src="{{ Storage::url($sermon->thumbnail) }}" alt="{{ $sermon->title }}" class="w-full h-full object-cover">
                        </div>
                    @elseif($sermon->video_url && str_contains($sermon->video_url, 'youtube'))
                        @php
                            preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $sermon->video_url, $matches);
                            $videoId = $matches[1] ?? null;
                        @endphp
                        @if($videoId)
                            <div class="h-48 overflow-hidden">
                                <img src="https://img.youtube.com/vi/{{ $videoId }}/maxresdefault.jpg" alt="{{ $sermon->title }}" class="w-full h-full object-cover">
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

<!-- Gallery -->
@if(isset($galleries) && $galleries->count() > 0)
<section class="py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900">Галерея</h2>
            <p class="text-gray-600 mt-2">Моменти з життя нашої церкви</p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($galleries as $gallery)
                @if($gallery->images && count($gallery->images) > 0)
                    @foreach(array_slice($gallery->images, 0, 4) as $image)
                        <div class="aspect-square rounded-xl overflow-hidden">
                            <img src="{{ Storage::url($image) }}" alt="{{ $gallery->title }}" class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                        </div>
                    @endforeach
                @endif
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Testimonials -->
@if(isset($testimonials) && $testimonials->count() > 0)
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900">Свідчення</h2>
            <p class="text-gray-600 mt-2">Історії змінених життів</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($testimonials as $testimonial)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <svg class="w-10 h-10 text-primary-200 mb-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/>
                    </svg>
                    <p class="text-gray-600 leading-relaxed mb-4">{{ $testimonial->content }}</p>
                    <div class="flex items-center gap-3">
                        @if($testimonial->photo)
                            <img src="{{ Storage::url($testimonial->photo) }}" alt="{{ $testimonial->author_name }}" class="w-10 h-10 rounded-full object-cover">
                        @else
                            <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                                <span class="text-primary-600 font-semibold">{{ substr($testimonial->author_name, 0, 1) }}</span>
                            </div>
                        @endif
                        <div>
                            <p class="font-semibold text-gray-900">{{ $testimonial->author_name }}</p>
                            @if($testimonial->author_role)
                                <p class="text-sm text-gray-500">{{ $testimonial->author_role }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Blog -->
@if(isset($blogPosts) && $blogPosts->count() > 0)
<section class="py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900">Блог</h2>
            <p class="text-gray-600 mt-2">Статті та роздуми</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($blogPosts as $post)
                <article class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-shadow">
                    @if($post->featured_image)
                        <div class="h-48 overflow-hidden">
                            <img src="{{ Storage::url($post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover">
                        </div>
                    @endif
                    <div class="p-5">
                        <p class="text-sm text-gray-500 mb-2">{{ $post->published_at->translatedFormat('d F Y') }}</p>
                        <h3 class="font-semibold text-gray-900 mb-2">{{ $post->title }}</h3>
                        @if($post->excerpt)
                            <p class="text-sm text-gray-600 line-clamp-3">{{ $post->excerpt }}</p>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- FAQ -->
@if(isset($faqs) && $faqs->count() > 0)
<section class="py-16 bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900">Часті запитання</h2>
            <p class="text-gray-600 mt-2">Відповіді на популярні запитання</p>
        </div>

        <div class="space-y-4">
            @foreach($faqs as $faq)
                <details class="group bg-white rounded-xl shadow-sm border border-gray-100">
                    <summary class="flex items-center justify-between p-5 cursor-pointer list-none">
                        <h3 class="font-semibold text-gray-900 pr-4">{{ $faq->question }}</h3>
                        <svg class="w-5 h-5 text-gray-400 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </summary>
                    <div class="px-5 pb-5 text-gray-600">
                        {!! nl2br(e($faq->answer)) !!}
                    </div>
                </details>
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
                    {{-- TODO: Розкоментувати після бета-тестування
                    <a href="{{ route('public.donate', $church->slug) }}?campaign={{ $campaign->name }}"
                       class="mt-4 block w-full py-2.5 text-center text-sm font-medium text-primary-600 hover:text-primary-700 border border-primary-200 hover:border-primary-300 rounded-xl transition-colors">
                        Пожертвувати
                    </a>
                    --}}
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
