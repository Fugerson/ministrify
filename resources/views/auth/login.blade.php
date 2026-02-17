@extends('layouts.guest')

@section('title', __('Вхід'))

@section('content')
<form method="POST" action="{{ route('login') }}" class="space-y-5">
    @csrf

    <div>
        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Email') }}</label>
        <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
               class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 border-0 rounded-xl focus:bg-white dark:focus:bg-gray-600 focus:ring-2 focus:ring-primary-500/20 transition-all">
    </div>

    <div x-data="{ showPassword: false }">
        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Пароль') }}</label>
        <div class="relative">
            <input :type="showPassword ? 'text' : 'password'" name="password" id="password" required
                   class="w-full px-4 py-3 pr-12 bg-gray-50 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 border-0 rounded-xl focus:bg-white dark:focus:bg-gray-600 focus:ring-2 focus:ring-primary-500/20 transition-all">
            <button type="button" @click="showPassword = !showPassword"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 focus:outline-none">
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

    <div class="flex items-center justify-between">
        <label class="flex items-center cursor-pointer">
            <input type="checkbox" name="remember" class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-primary-600 focus:ring-primary-500 focus:ring-offset-0 dark:focus:ring-offset-gray-800">
            <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Запам\'ятати') }}</span>
        </label>

        <a href="{{ route('password.request') }}" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
            {{ __('Забули пароль?') }}
        </a>
    </div>

    <button type="submit"
            class="w-full py-3.5 px-4 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white font-semibold rounded-xl shadow-lg shadow-primary-500/30 transition-all duration-200 transform hover:scale-[1.02]">
        {{ __('Увійти') }}
    </button>

    @if(config('services.google.client_id'))
    <div class="relative my-6">
        <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-200 dark:border-gray-700"></div>
        </div>
        <div class="relative flex justify-center text-sm">
            <span class="px-4 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400">{{ __('або') }}</span>
        </div>
    </div>

    <a href="{{ route('auth.google') }}"
       class="w-full flex items-center justify-center gap-3 py-3 px-4 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-600 transition-all duration-200">
        <svg class="w-5 h-5" viewBox="0 0 24 24">
            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
        </svg>
        <span class="font-medium text-gray-700 dark:text-gray-200">{{ __('Увійти з Google') }}</span>
    </a>
    @endif
</form>

<div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700 text-center space-y-3">
    <div>
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Хочете приєднатися до церкви?') }}</p>
        <a href="{{ route('join') }}" class="mt-1 inline-flex items-center text-primary-600 hover:text-primary-700 font-semibold">
            {{ __('Приєднатися') }}
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>
    <div>
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Хочете зареєструвати нову церкву?') }}</p>
        <a href="{{ route('register') }}" class="mt-1 inline-flex items-center text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 font-medium">
            {{ __('Реєстрація церкви') }} →
        </a>
    </div>
</div>
@endsection