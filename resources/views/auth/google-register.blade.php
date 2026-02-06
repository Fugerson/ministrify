@extends('layouts.guest')

@section('title', 'Завершення реєстрації')

@section('content')
<div class="text-center mb-6">
    <div class="mx-auto w-16 h-16 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center mb-4">
        @if($googleUser['avatar'])
            <img src="{{ $googleUser['avatar'] }}" alt="" class="w-14 h-14 rounded-full">
        @else
            <svg class="w-8 h-8 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
        @endif
    </div>
    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Привіт, {{ $googleUser['name'] }}!</h2>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $googleUser['email'] }}</p>
</div>

<form method="POST" action="{{ route('auth.google.complete') }}" class="space-y-5" x-data="{ action: 'create_church' }">
    @csrf

    <div class="space-y-3">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Що бажаєте зробити?</label>

        <label class="flex items-center p-4 bg-gray-50 dark:bg-gray-700 rounded-xl cursor-pointer border-2 transition-all"
               :class="action === 'create_church' ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-transparent hover:border-gray-200 dark:hover:border-gray-600'">
            <input type="radio" name="action" value="create_church" x-model="action" class="sr-only">
            <div class="w-10 h-10 bg-primary-100 dark:bg-primary-900/30 rounded-lg flex items-center justify-center mr-4">
                <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div>
                <div class="font-medium text-gray-900 dark:text-white">Створити нову церкву</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Я адміністратор і хочу зареєструвати церкву</div>
            </div>
        </label>

        <label class="flex items-center p-4 bg-gray-50 dark:bg-gray-700 rounded-xl cursor-pointer border-2 transition-all"
               :class="action === 'join_church' ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-transparent hover:border-gray-200 dark:hover:border-gray-600'">
            <input type="radio" name="action" value="join_church" x-model="action" class="sr-only">
            <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center mr-4">
                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
            </div>
            <div>
                <div class="font-medium text-gray-900 dark:text-white">Приєднатися до церкви</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Моя церква вже є в системі</div>
            </div>
        </label>
    </div>

    <!-- Create church fields -->
    <div x-show="action === 'create_church'" x-cloak class="space-y-4">
        <div>
            <label for="church_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Назва церкви</label>
            <input type="text" name="church_name" id="church_name" value="{{ old('church_name') }}"
                   class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 dark:text-white border-0 rounded-xl focus:bg-white dark:focus:bg-gray-600 focus:ring-2 focus:ring-primary-500/20 transition-all"
                   placeholder="Церква Нове Життя">
            @error('church_name')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="city" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Місто</label>
            <input type="text" name="city" id="city" value="{{ old('city') }}"
                   class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 dark:text-white border-0 rounded-xl focus:bg-white dark:focus:bg-gray-600 focus:ring-2 focus:ring-primary-500/20 transition-all"
                   placeholder="Київ">
            @error('city')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- Join church fields -->
    <div x-show="action === 'join_church'" x-cloak class="space-y-4">
        <div>
            <label for="church_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Оберіть церкву</label>
            <select name="church_id" id="church_id"
                    class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 dark:text-white border-0 rounded-xl focus:bg-white dark:focus:bg-gray-600 focus:ring-2 focus:ring-primary-500/20 transition-all">
                <option value="">-- Оберіть церкву --</option>
                @foreach($churches as $church)
                    <option value="{{ $church->id }}" {{ old('church_id') == $church->id ? 'selected' : '' }}>
                        {{ $church->name }}{{ $church->city ? ' — ' . $church->city : '' }}
                    </option>
                @endforeach
            </select>
            @error('church_id')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
            @if($churches->isEmpty())
                <p class="mt-2 text-sm text-amber-600 dark:text-amber-400">Наразі немає церков, які приймають нових учасників. Зверніться до адміністратора вашої церкви.</p>
            @endif
        </div>
    </div>

    @if(session('error'))
        <div class="p-4 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 rounded-xl text-sm">
            {{ session('error') }}
        </div>
    @endif

    <button type="submit"
            class="w-full py-3.5 px-4 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white font-semibold rounded-xl shadow-lg shadow-primary-500/30 transition-all duration-200 transform hover:scale-[1.02]">
        Продовжити
    </button>
</form>

<div class="mt-6 text-center">
    <a href="{{ route('login') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
        ← Повернутися до входу
    </a>
</div>
@endsection
