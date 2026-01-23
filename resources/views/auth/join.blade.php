@extends('layouts.guest')

@section('title', 'Приєднатися до церкви')

@section('content')
<div class="text-center mb-6">
    <div class="mx-auto w-16 h-16 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center mb-4">
        <svg class="w-8 h-8 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
        </svg>
    </div>
    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Приєднатися до церкви</h2>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Оберіть вашу церкву та створіть акаунт</p>
</div>

<form method="POST" action="{{ route('join.store') }}" class="space-y-5" x-data="{ showPassword: false }">
    @csrf

    <!-- Church Select -->
    <div>
        <label for="church_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ваша церква</label>
        <select name="church_id" id="church_id" required
                class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 dark:text-white border-0 rounded-xl focus:bg-white dark:focus:bg-gray-600 focus:ring-2 focus:ring-primary-500/20 transition-all">
            <option value="">Оберіть церкву...</option>
            @foreach($churches as $church)
                <option value="{{ $church->id }}" {{ old('church_id') == $church->id ? 'selected' : '' }}>
                    {{ $church->name }} ({{ $church->city }})
                </option>
            @endforeach
        </select>
        @error('church_id')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <hr class="border-gray-200 dark:border-gray-700">

    <!-- Name -->
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ваше ім'я</label>
        <input type="text" name="name" id="name" value="{{ old('name') }}" required autocomplete="name"
               class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 dark:text-white border-0 rounded-xl focus:bg-white dark:focus:bg-gray-600 focus:ring-2 focus:ring-primary-500/20 transition-all"
               placeholder="Іван Петренко">
        @error('name')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <!-- Email -->
    <div>
        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email</label>
        <input type="email" name="email" id="email" value="{{ old('email') }}" required autocomplete="email"
               class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 dark:text-white border-0 rounded-xl focus:bg-white dark:focus:bg-gray-600 focus:ring-2 focus:ring-primary-500/20 transition-all"
               placeholder="ivan@example.com">
        @error('email')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <!-- Phone (optional) -->
    <div>
        <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Телефон <span class="text-gray-400">(необов'язково)</span></label>
        <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" autocomplete="tel"
               class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 dark:text-white border-0 rounded-xl focus:bg-white dark:focus:bg-gray-600 focus:ring-2 focus:ring-primary-500/20 transition-all"
               placeholder="+380 XX XXX XX XX">
    </div>

    <!-- Password -->
    <div>
        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Пароль</label>
        <div class="relative">
            <input :type="showPassword ? 'text' : 'password'" name="password" id="password" required autocomplete="new-password"
                   class="w-full px-4 py-3 pr-12 bg-gray-50 dark:bg-gray-700 dark:text-white border-0 rounded-xl focus:bg-white dark:focus:bg-gray-600 focus:ring-2 focus:ring-primary-500/20 transition-all">
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
        @error('password')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <!-- Password Confirmation -->
    <div>
        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Підтвердження пароля</label>
        <input :type="showPassword ? 'text' : 'password'" name="password_confirmation" id="password_confirmation" required autocomplete="new-password"
               class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 dark:text-white border-0 rounded-xl focus:bg-white dark:focus:bg-gray-600 focus:ring-2 focus:ring-primary-500/20 transition-all">
    </div>

    @if(session('error'))
        <div class="p-4 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 rounded-xl text-sm">
            {{ session('error') }}
        </div>
    @endif

    <button type="submit"
            class="w-full py-3.5 px-4 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white font-semibold rounded-xl shadow-lg shadow-primary-500/30 transition-all duration-200 transform hover:scale-[1.02]">
        Приєднатися
    </button>
</form>

<div class="mt-6 text-center space-y-2">
    <p class="text-sm text-gray-500 dark:text-gray-400">
        Вже маєте акаунт?
        <a href="{{ route('login') }}" class="text-primary-600 hover:text-primary-700 font-medium">Увійти</a>
    </p>
    <p class="text-sm text-gray-500 dark:text-gray-400">
        Хочете зареєструвати нову церкву?
        <a href="{{ route('register') }}" class="text-primary-600 hover:text-primary-700 font-medium">Реєстрація церкви</a>
    </p>
</div>
@endsection
