@extends('layouts.guest')

@section('title', 'Посилання застаріло')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <div class="mx-auto w-16 h-16 bg-amber-100 dark:bg-amber-900/30 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white">
                Посилання застаріло
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Посилання для підтвердження email більше не дійсне
            </p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-8">
            <div class="text-center space-y-4">
                <p class="text-gray-600 dark:text-gray-300">
                    Посилання для верифікації дійсне протягом 24 годин. Увійдіть до свого акаунту, щоб отримати нове посилання.
                </p>

                <a href="{{ route('login') }}"
                   class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                    Увійти до акаунту
                </a>

                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Після входу ви зможете надіслати новий лист для підтвердження
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
