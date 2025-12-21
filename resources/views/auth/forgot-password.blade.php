@extends('layouts.guest')

@section('title', 'Відновлення пароля')

@section('content')
<div class="mb-6 text-sm text-gray-600">
    Забули пароль? Введіть email і ми надішлемо посилання для відновлення.
</div>

<form method="POST" action="{{ route('password.email') }}">
    @csrf

    <div class="mb-4">
        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
        <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
    </div>

    <button type="submit"
            class="w-full py-2 px-4 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
        Надіслати посилання
    </button>
</form>

<div class="mt-6 text-center">
    <a href="{{ route('login') }}" class="text-sm text-primary-600 hover:text-primary-500">
        Повернутися до входу
    </a>
</div>
@endsection
