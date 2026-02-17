@extends('layouts.guest')

@section('title', __('Реєстрація'))

@section('content')
@if(config('services.google.client_id'))
<a href="{{ route('auth.google') }}"
   class="w-full flex items-center justify-center gap-3 py-3 px-4 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-600 transition-all duration-200 mb-6">
    <svg class="w-5 h-5" viewBox="0 0 24 24">
        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
    </svg>
    <span class="font-medium text-gray-700 dark:text-gray-200">{{ __('Швидка реєстрація з Google') }}</span>
</a>

<div class="relative mb-6">
    <div class="absolute inset-0 flex items-center">
        <div class="w-full border-t border-gray-200 dark:border-gray-700"></div>
    </div>
    <div class="relative flex justify-center text-sm">
        <span class="px-4 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400">{{ __('або заповніть форму') }}</span>
    </div>
</div>
@endif

<form method="POST" action="{{ route('register') }}" x-data="{ showPassword: false }">
    @csrf

    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">{{ __('Реєстрація церкви') }}</h2>

    <div class="mb-4">
        <label for="church_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Назва церкви') }}</label>
        <input type="text" name="church_name" id="church_name" value="{{ old('church_name') }}" required
               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
               >
    </div>

    <div class="mb-4">
        <label for="city" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Місто') }}</label>
        <input type="text" name="city" id="city" value="{{ old('city') }}" required
               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
               >
    </div>

    <hr class="my-6 border-gray-200 dark:border-gray-700">

    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">{{ __('Адміністратор') }}</h3>

    <div class="mb-4">
        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Ваше ім\'я') }}</label>
        <input type="text" name="name" id="name" value="{{ old('name') }}" required
               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
               >
    </div>

    <div class="mb-4">
        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Email') }}</label>
        <input type="email" name="email" id="email" value="{{ old('email') }}" required
               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
               >
    </div>

    <div class="mb-4">
        <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Телефон (необов\'язково)') }}</label>
        <input type="tel" name="phone" id="phone" value="{{ old('phone') }}"
               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
               >
    </div>

    <div class="mb-4">
        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Пароль') }}</label>
        <div class="relative">
            <input :type="showPassword ? 'text' : 'password'" name="password" id="password" required
                   class="w-full px-3 py-2 pr-10 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            <button type="button" @click="showPassword = !showPassword"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                </svg>
            </button>
        </div>
    </div>

    <div class="mb-6">
        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Підтвердження пароля') }}</label>
        <div class="relative">
            <input :type="showPassword ? 'text' : 'password'" name="password_confirmation" id="password_confirmation" required
                   class="w-full px-3 py-2 pr-10 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            <button type="button" @click="showPassword = !showPassword"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                </svg>
            </button>
        </div>
    </div>

    <button type="submit"
            class="w-full py-2 px-4 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
        {{ __('Зареєструвати') }}
    </button>
</form>

<div class="mt-6 text-center">
    <p class="text-sm text-gray-600 dark:text-gray-400">
        {{ __('Вже є акаунт?') }}
        <a href="{{ route('login') }}" class="text-primary-600 hover:text-primary-500 font-medium">
            {{ __('Увійти') }}
        </a>
    </p>
</div>
@endsection