@extends('layouts.guest')

@section('title', 'Вхід')

@section('content')
<form method="POST" action="{{ route('login') }}" class="space-y-5">
    @csrf

    <div>
        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
        <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
               placeholder="your@email.com"
               class="w-full px-4 py-3 bg-gray-50 border-0 rounded-xl focus:bg-white focus:ring-2 focus:ring-primary-500/20 transition-all">
    </div>

    <div>
        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Пароль</label>
        <input type="password" name="password" id="password" required
               placeholder="••••••••"
               class="w-full px-4 py-3 bg-gray-50 border-0 rounded-xl focus:bg-white focus:ring-2 focus:ring-primary-500/20 transition-all">
    </div>

    <div class="flex items-center justify-between">
        <label class="flex items-center cursor-pointer">
            <input type="checkbox" name="remember" class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 focus:ring-offset-0">
            <span class="ml-2 text-sm text-gray-600">Запам'ятати</span>
        </label>

        <a href="{{ route('password.request') }}" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
            Забули пароль?
        </a>
    </div>

    <button type="submit"
            class="w-full py-3.5 px-4 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white font-semibold rounded-xl shadow-lg shadow-primary-500/30 transition-all duration-200 transform hover:scale-[1.02]">
        Увійти
    </button>
</form>

<div class="mt-8 pt-6 border-t border-gray-100 text-center">
    <p class="text-sm text-gray-500">
        Ще немає акаунту?
    </p>
    <a href="{{ route('register') }}" class="mt-2 inline-flex items-center text-primary-600 hover:text-primary-700 font-semibold">
        Зареєструвати церкву
        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
    </a>
</div>
@endsection
