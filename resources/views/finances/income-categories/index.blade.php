@extends('layouts.app')

@section('title', 'Категорії надходжень')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <a href="{{ route('settings.index') }}" class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-sm">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        До налаштувань
    </a>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Категорії надходжень</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Налаштуйте категорії для відстеження різних типів надходжень</p>
        </div>

        <div class="p-6">
            <!-- Add new category form -->
            <form action="{{ route('settings.income-categories.store') }}" method="POST" class="mb-6 p-4 bg-gray-50 dark:bg-gray-900/50 rounded-lg">
                @csrf
                <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-4">Додати категорію</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <input type="text" name="name" placeholder="Назва категорії" required
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <input type="text" name="icon" placeholder="Емодзі" maxlength="10"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <input type="color" name="color" value="#3B82F6"
                               class="w-full h-10 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer">
                    </div>
                    <div>
                        <button type="submit" class="w-full px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                            Додати
                        </button>
                    </div>
                </div>
                <div class="flex items-center gap-6 mt-4">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="is_tithe" value="1" class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Десятина</span>
                    </label>
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="is_offering" value="1" class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Пожертва</span>
                    </label>
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="is_donation" value="1" class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Цільова пожертва</span>
                    </label>
                </div>
            </form>

            <!-- Categories list -->
            <div class="space-y-3">
                @forelse($categories as $category)
                    <div x-data="{ editing: false }" class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                        <div x-show="!editing" class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center text-lg" style="background-color: {{ $category->color }}20">
                                    {{ $category->icon ?? '💰' }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $category->name }}</p>
                                    <div class="flex items-center space-x-2 mt-1">
                                        @if($category->is_tithe)
                                            <span class="px-2 py-0.5 text-xs rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300">Десятина</span>
                                        @endif
                                        @if($category->is_offering)
                                            <span class="px-2 py-0.5 text-xs rounded-full bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300">Пожертва</span>
                                        @endif
                                        @if($category->is_donation)
                                            <span class="px-2 py-0.5 text-xs rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300">Цільова</span>
                                        @endif
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $category->incomes_count }} записів</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <button @click="editing = true" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                @if($category->incomes_count == 0)
                                    <form action="{{ route('settings.income-categories.destroy', $category) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Видалити категорію?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-600">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        <!-- Edit form -->
                        <form x-show="editing" action="{{ route('settings.income-categories.update', $category) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <input type="text" name="name" value="{{ $category->name }}" required
                                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                                </div>
                                <div>
                                    <input type="text" name="icon" value="{{ $category->icon }}" maxlength="10"
                                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                                </div>
                                <div>
                                    <input type="color" name="color" value="{{ $category->color }}"
                                           class="w-full h-10 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer">
                                </div>
                                <div class="flex items-center space-x-2">
                                    <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                                        Зберегти
                                    </button>
                                    <button type="button" @click="editing = false" class="px-4 py-2 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                                        Скасувати
                                    </button>
                                </div>
                            </div>
                            <div class="flex items-center gap-6 mt-4">
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="checkbox" name="is_tithe" value="1" {{ $category->is_tithe ? 'checked' : '' }} class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">Десятина</span>
                                </label>
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="checkbox" name="is_offering" value="1" {{ $category->is_offering ? 'checked' : '' }} class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">Пожертва</span>
                                </label>
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="checkbox" name="is_donation" value="1" {{ $category->is_donation ? 'checked' : '' }} class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">Цільова пожертва</span>
                                </label>
                            </div>
                        </form>
                    </div>
                @empty
                    <p class="text-center text-gray-500 dark:text-gray-400 py-8">Немає категорій. Додайте першу категорію вище.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
