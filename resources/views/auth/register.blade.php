@extends('layouts.guest')

@section('title', 'Реєстрація')

@section('content')
<form method="POST" action="{{ route('register') }}">
    @csrf

    <h2 class="text-xl font-semibold text-gray-900 mb-6">Реєстрація церкви</h2>

    <div class="mb-4">
        <label for="church_name" class="block text-sm font-medium text-gray-700 mb-1">Назва церкви</label>
        <input type="text" name="church_name" id="church_name" value="{{ old('church_name') }}" required
               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
               placeholder="Церква 'Благодать'">
    </div>

    <div class="mb-4">
        <label for="city" class="block text-sm font-medium text-gray-700 mb-1">Місто</label>
        <input type="text" name="city" id="city" value="{{ old('city') }}" required
               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
               placeholder="Київ">
    </div>

    <hr class="my-6">

    <h3 class="text-lg font-medium text-gray-900 mb-4">Адміністратор</h3>

    <div class="mb-4">
        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Ваше ім'я</label>
        <input type="text" name="name" id="name" value="{{ old('name') }}" required
               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
               placeholder="Іван Петренко">
    </div>

    <div class="mb-4">
        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
        <input type="email" name="email" id="email" value="{{ old('email') }}" required
               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
               placeholder="admin@church.ua">
    </div>

    <div class="mb-4">
        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Телефон (необов'язково)</label>
        <input type="tel" name="phone" id="phone" value="{{ old('phone') }}"
               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
               placeholder="+380 67 123 4567">
    </div>

    <div class="mb-4">
        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Пароль</label>
        <input type="password" name="password" id="password" required
               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
               placeholder="Мінімум 8 символів">
    </div>

    <div class="mb-6">
        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Підтвердження пароля</label>
        <input type="password" name="password_confirmation" id="password_confirmation" required
               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
    </div>

    <button type="submit"
            class="w-full py-2 px-4 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
        Зареєструвати
    </button>
</form>

<div class="mt-6 text-center">
    <p class="text-sm text-gray-600">
        Вже є акаунт?
        <a href="{{ route('login') }}" class="text-primary-600 hover:text-primary-500 font-medium">
            Увійти
        </a>
    </p>
</div>
@endsection
