@extends('layouts.app')

@section('title', 'Про нас')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('website-builder.index') }}" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Про нас</h1>
            <p class="text-gray-600 dark:text-gray-400">Розкажіть про вашу церкву</p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-400 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('website-builder.about.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Mission -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Місія</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Чому існує ваша церква</p>
            </div>
            <div class="p-6">
                <textarea name="mission" rows="4" placeholder="Наша місія — поширювати Євангеліє..."
                          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">{{ $aboutContent['mission'] ?? '' }}</textarea>
            </div>
        </div>

        <!-- Vision -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Візія</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Ким ви хочете стати</p>
            </div>
            <div class="p-6">
                <textarea name="vision" rows="4" placeholder="Ми бачимо церкву, яка..."
                          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">{{ $aboutContent['vision'] ?? '' }}</textarea>
            </div>
        </div>

        <!-- Values -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700" x-data="{ values: {{ json_encode($aboutContent['values'] ?? ['']) }} }">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Цінності</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Що для вас важливо</p>
                </div>
                <button type="button" @click="values.push('')" class="px-3 py-1.5 text-sm bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400 rounded-lg hover:bg-primary-200 dark:hover:bg-primary-900/50 transition-colors">
                    + Додати цінність
                </button>
            </div>
            <div class="p-6 space-y-3">
                <template x-for="(value, index) in values" :key="index">
                    <div class="flex gap-2">
                        <input type="text" :name="'values[' + index + ']'" x-model="values[index]"
                               class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <button type="button" @click="values.splice(index, 1)" x-show="values.length > 1"
                                class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </template>
            </div>
        </div>

        <!-- History -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Історія</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Розкажіть про шлях вашої церкви</p>
            </div>
            <div class="p-6">
                <textarea name="history" rows="8" placeholder="Наша церква була заснована..."
                          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">{{ $aboutContent['history'] ?? '' }}</textarea>
            </div>
        </div>

        <!-- Beliefs -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Віровчення</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Основи вашої віри</p>
            </div>
            <div class="p-6">
                <textarea name="beliefs" rows="8" placeholder="Ми віримо..."
                          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">{{ $aboutContent['beliefs'] ?? '' }}</textarea>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                Зберегти
            </button>
        </div>
    </form>
</div>
@endsection
