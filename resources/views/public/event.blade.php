@extends('public.layout')

@section('title', $event->title . ' - ' . $church->name)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Breadcrumb -->
    <nav class="mb-8">
        <ol class="flex items-center gap-2 text-sm">
            <li><a href="{{ route('public.church', $church->slug) }}" class="text-gray-500 hover:text-primary-600">Головна</a></li>
            <li class="text-gray-400">/</li>
            <li><a href="{{ route('public.events', $church->slug) }}" class="text-gray-500 hover:text-primary-600">Події</a></li>
            <li class="text-gray-400">/</li>
            <li class="text-gray-900 font-medium">{{ $event->title }}</li>
        </ol>
    </nav>

    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        @if($event->cover_image)
            <div class="h-64 md:h-80 overflow-hidden">
                <img src="{{ Storage::url($event->cover_image) }}" alt="{{ $event->title }}" class="w-full h-full object-cover">
            </div>
        @else
            <div class="h-32 bg-gradient-to-r from-primary-500 to-primary-700"></div>
        @endif

        <div class="p-8 md:p-10">
            <!-- Event Info -->
            <div class="flex flex-wrap items-start gap-6 mb-8">
                <div class="flex-shrink-0 w-20 text-center bg-primary-50 rounded-xl py-3">
                    <p class="text-3xl font-bold text-primary-600">{{ $event->date->format('d') }}</p>
                    <p class="text-sm text-primary-700 uppercase font-medium">{{ $event->date->translatedFormat('M') }}</p>
                    <p class="text-xs text-gray-500">{{ $event->date->format('Y') }}</p>
                </div>
                <div class="flex-1 min-w-0">
                    @if($event->ministry)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium mb-2"
                              style="background-color: {{ $event->ministry->color }}30; color: {{ $event->ministry->color }};">
                            {{ $event->ministry->name }}
                        </span>
                    @endif
                    <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-3">{{ $event->title }}</h1>
                    <div class="flex flex-wrap gap-4 text-gray-600">
                        @if($event->time)
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>{{ $event->time->format('H:i') }}</span>
                        </div>
                        @else
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span>Весь день</span>
                        </div>
                        @endif
                        @if($event->location)
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span>{{ $event->location }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Description -->
            @if($event->public_description || $event->notes)
                <div class="prose prose-lg max-w-none mb-8">
                    {!! nl2br(e($event->public_description ?? $event->notes)) !!}
                </div>
            @endif

            <!-- Registration Form -->
            @if($event->allow_registration)
                <div class="border-t border-gray-100 pt-8" x-data="{ showForm: false }">
                    @if($event->canAcceptRegistrations())
                        <div class="bg-primary-50 rounded-2xl p-6 mb-6">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h3 class="font-semibold text-gray-900">Реєстрація на подію</h3>
                                    @if($event->registration_limit)
                                        <p class="text-sm text-gray-600">Залишилось {{ $event->remaining_spaces }} місць з {{ $event->registration_limit }}</p>
                                    @endif
                                    @if($event->registration_deadline)
                                        <p class="text-sm text-gray-600">Реєстрація до {{ $event->registration_deadline->format('d.m.Y H:i') }}</p>
                                    @endif
                                </div>
                                <button @click="showForm = !showForm"
                                        class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors">
                                    <span x-show="!showForm">Зареєструватися</span>
                                    <span x-show="showForm">Приховати форму</span>
                                </button>
                            </div>

                            <div x-show="showForm" x-cloak x-transition class="mt-6">
                                <form action="{{ route('public.event.register', [$church->slug, $event]) }}" method="POST" class="space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
                                    @csrf
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Ім'я *</label>
                                            <input type="text" name="first_name" required value="{{ old('first_name') }}"
                                                   class="w-full px-4 py-2.5 border {{ $errors->has('first_name') ? 'border-red-500' : 'border-gray-300' }} rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                            @error('first_name')
                                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Прізвище *</label>
                                            <input type="text" name="last_name" required value="{{ old('last_name') }}"
                                                   class="w-full px-4 py-2.5 border {{ $errors->has('last_name') ? 'border-red-500' : 'border-gray-300' }} rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                            @error('last_name')
                                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                                            <input type="email" name="email" required value="{{ old('email') }}"
                                                   class="w-full px-4 py-2.5 border {{ $errors->has('email') ? 'border-red-500' : 'border-gray-300' }} rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                            @error('email')
                                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Телефон</label>
                                            <input type="tel" name="phone" value="{{ old('phone') }}"
                                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Кількість гостей (окрім вас)</label>
                                        <select name="guests" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                            @for($i = 0; $i <= 10; $i++)
                                                <option value="{{ $i }}" {{ old('guests') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Коментар</label>
                                        <textarea name="notes" rows="3"
                                                  class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500">{{ old('notes') }}</textarea>
                                    </div>
                                    <button type="submit" :disabled="submitting"
                                            class="w-full py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors disabled:opacity-50">
                                        <span x-show="!submitting">Підтвердити реєстрацію</span>
                                        <span x-show="submitting" class="inline-flex items-center justify-center gap-2">
                                            <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                            Реєстрація...
                                        </span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="bg-gray-100 rounded-2xl p-6 text-center">
                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-gray-600">
                                @if($event->date->isPast())
                                    Ця подія вже відбулася
                                @elseif($event->registration_deadline && $event->registration_deadline->isPast())
                                    Час реєстрації закінчився
                                @elseif($event->registration_limit && $event->remaining_spaces <= 0)
                                    Всі місця зайняті
                                @else
                                    Реєстрація закрита
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Back link -->
    <div class="mt-8 text-center">
        <a href="{{ route('public.events', $church->slug) }}" class="inline-flex items-center gap-2 text-primary-600 hover:text-primary-700 font-medium">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Повернутись до списку подій
        </a>
    </div>
</div>
@endsection
