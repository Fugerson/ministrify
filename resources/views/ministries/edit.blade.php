@extends('layouts.app')

@section('title', 'Редагувати команду')

@section('content')
<div class="max-w-2xl mx-auto">
    <a href="{{ route('ministries.show', $ministry) }}" class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-sm mb-6">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Назад
    </a>

    <form @submit.prevent="submit($refs.ministryEditForm)" x-ref="ministryEditForm" class="space-y-6"
          x-data="{ ...ajaxForm({ url: '{{ route('ministries.update', $ministry) }}', method: 'PUT' }) }">

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Редагувати команду</h2>

            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Назва *</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $ministry->name) }}" required
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <template x-if="errors.name">
                        <p class="mt-1 text-sm text-red-500" x-text="errors.name[0]"></p>
                    </template>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Опис</label>
                    <textarea name="description" id="description" rows="2"
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">{{ old('description', $ministry->description) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Лідер</label>
                    <x-person-select name="leader_id" :people="$people" :selected="old('leader_id', $ministry->leader_id)" placeholder="Пошук лідера..." />
                </div>

                <div>
                    <label for="color" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Колір</label>
                    <input type="color" name="color" id="color" value="{{ old('color', $ministry->color ?? '#3b82f6') }}"
                           class="w-16 h-10 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer">
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <input type="hidden" name="is_worship_ministry" value="0">
                    <input type="checkbox" name="is_worship_ministry" id="is_worship_ministry" value="1"
                           {{ old('is_worship_ministry', $ministry->is_worship_ministry) ? 'checked' : '' }}
                           class="w-5 h-5 rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500">
                    <label for="is_worship_ministry" class="text-sm text-gray-700 dark:text-gray-300">
                        <span class="font-medium">Музичне служіння</span>
                        <span class="block text-gray-500 dark:text-gray-400 text-xs">Показувати бібліотеку пісень та Music Stand</span>
                    </label>
                </div>

            </div>
        </div>

        <div class="flex items-center justify-between">
            @if(auth()->user()->canDelete('ministries'))
            <button type="button"
                    @click="ajaxDelete('{{ route('ministries.destroy', $ministry) }}', '{{ __('messages.confirm_delete_ministry') }}', null, '{{ route('ministries.index') }}')"
                    class="text-red-600 hover:text-red-800 text-sm font-medium">
                Видалити команду
            </button>
            @else
            <div></div>
            @endif

            <div class="flex flex-col-reverse sm:flex-row sm:items-center gap-2 sm:gap-3">
                <a href="{{ route('ministries.show', $ministry) }}" class="w-full sm:w-auto px-4 py-2 text-center text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                    Скасувати
                </a>
                <button type="submit" :disabled="saving"
                        class="w-full sm:w-auto px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50">
                    <span x-show="!saving">Зберегти</span>
                    <span x-show="saving" class="flex items-center justify-center gap-2">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        Збереження...
                    </span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
