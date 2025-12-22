@extends('public.layout')

@section('title', $group->name . ' - ' . $church->name)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Breadcrumb -->
    <nav class="mb-8">
        <ol class="flex items-center gap-2 text-sm">
            <li><a href="{{ route('public.church', $church->slug) }}" class="text-gray-500 hover:text-primary-600">Головна</a></li>
            <li class="text-gray-400">/</li>
            <li class="text-gray-900 font-medium">{{ $group->name }}</li>
        </ol>
    </nav>

    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        @if($group->cover_image)
            <div class="h-64 md:h-80 overflow-hidden">
                <img src="{{ Storage::url($group->cover_image) }}" alt="{{ $group->name }}" class="w-full h-full object-cover">
            </div>
        @else
            <div class="h-40 flex items-center justify-center" style="background: linear-gradient(135deg, {{ $group->color ?? '#6366f1' }} 0%, {{ $group->color ?? '#6366f1' }}99 100%);">
                <svg class="w-20 h-20 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
        @endif

        <div class="p-8 md:p-10">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">{{ $group->name }}</h1>

            <div class="flex flex-wrap gap-4 mb-6">
                @if($group->meeting_schedule || ($group->meeting_day && $group->meeting_time))
                    <div class="flex items-center gap-2 text-gray-600">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>
                            @if($group->meeting_schedule)
                                {{ $group->meeting_schedule }}
                            @else
                                {{ $group->meeting_day_name }}, {{ $group->meeting_time->format('H:i') }}
                            @endif
                        </span>
                    </div>
                @endif
                @if($group->location)
                    <div class="flex items-center gap-2 text-gray-600">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span>{{ $group->location }}</span>
                    </div>
                @endif
                <div class="flex items-center gap-2 text-gray-600">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span>{{ $group->members_count }} учасників</span>
                </div>
            </div>

            @if($group->leader)
                <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-xl mb-6">
                    <div class="w-12 h-12 rounded-full bg-primary-100 flex items-center justify-center">
                        @if($group->leader->photo)
                            <img src="{{ Storage::url($group->leader->photo) }}" alt="{{ $group->leader->full_name }}" class="w-12 h-12 rounded-full object-cover">
                        @else
                            <span class="text-lg font-semibold text-primary-600">{{ substr($group->leader->first_name, 0, 1) }}</span>
                        @endif
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Лідер групи</p>
                        <p class="font-semibold text-gray-900">{{ $group->leader->full_name }}</p>
                    </div>
                </div>
            @endif

            @if($group->public_description || $group->description)
                <div class="prose prose-lg max-w-none mb-8">
                    {!! nl2br(e($group->public_description ?? $group->description)) !!}
                </div>
            @endif

            <!-- Join Form -->
            @if($group->allow_join_requests)
                <div class="border-t border-gray-100 pt-8" x-data="{ showForm: false }">
                    <div class="bg-primary-50 rounded-2xl p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="font-semibold text-gray-900">Приєднатися до групи</h3>
                                <p class="text-sm text-gray-600">Заповніть форму і лідер групи зв'яжеться з вами</p>
                            </div>
                            <button @click="showForm = !showForm"
                                    class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors">
                                <span x-show="!showForm">Подати заявку</span>
                                <span x-show="showForm">Приховати форму</span>
                            </button>
                        </div>

                        <div x-show="showForm" x-cloak x-transition class="mt-6">
                            <form action="{{ route('public.group.join', [$church->slug, $group->slug]) }}" method="POST" class="space-y-4">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Ім'я *</label>
                                        <input type="text" name="first_name" required value="{{ old('first_name') }}"
                                               class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Прізвище *</label>
                                        <input type="text" name="last_name" required value="{{ old('last_name') }}"
                                               class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                                        <input type="email" name="email" required value="{{ old('email') }}"
                                               class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Телефон</label>
                                        <input type="tel" name="phone" value="{{ old('phone') }}"
                                               class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Повідомлення</label>
                                    <textarea name="message" rows="3"
                                              class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500" placeholder="Розкажіть трохи про себе...">{{ old('message') }}</textarea>
                                </div>
                                <button type="submit"
                                        class="w-full py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors">
                                    Надіслати заявку
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Back link -->
    <div class="mt-8 text-center">
        <a href="{{ route('public.church', $church->slug) }}" class="inline-flex items-center gap-2 text-primary-600 hover:text-primary-700 font-medium">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Повернутись на головну
        </a>
    </div>
</div>
@endsection
