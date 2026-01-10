@extends('layouts.app')

@section('title', 'Новий шаблон чеклиста')

@section('content')
<div class="max-w-2xl mx-auto" x-data="checklistForm()">
    <form method="POST" action="{{ route('checklists.templates.store') }}" class="space-y-6">
        @csrf

        <!-- Basic Info -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Інформація про шаблон</h2>

            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Назва *</label>
                    <input type="text" name="name" id="name" required
                           class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Опис</label>
                    <textarea name="description" id="description" rows="2"
                              placeholder="Короткий опис для чого цей чеклист..."
                              class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white"></textarea>
                </div>
            </div>
        </div>

        <!-- Checklist Items -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Пункти чеклиста</h2>
                <button type="button" @click="addItem()"
                        class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 font-medium flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Додати пункт
                </button>
            </div>

            <div class="space-y-3">
                <template x-for="(item, index) in items" :key="index">
                    <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                        <div class="flex items-center justify-center w-6 h-6 rounded bg-gray-200 dark:bg-gray-600 text-gray-500 dark:text-gray-400 text-xs font-medium mt-2"
                             x-text="index + 1"></div>
                        <div class="flex-1 space-y-2">
                            <input type="text" :name="'items[' + index + '][title]'" x-model="item.title"
                                   placeholder="Назва пункту *" required
                                   class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:text-white text-sm">
                            <input type="text" :name="'items[' + index + '][description]'" x-model="item.description"
                                   placeholder="Опис (опціонально)"
                                   class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:text-white text-sm">
                        </div>
                        <button type="button" @click="removeItem(index)" x-show="items.length > 1"
                                class="p-2 text-gray-400 hover:text-red-500 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </template>
            </div>

            <button type="button" @click="addItem()"
                    class="mt-4 w-full p-3 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-xl text-gray-500 dark:text-gray-400 hover:border-primary-400 hover:text-primary-600 transition-colors flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Додати ще пункт
            </button>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('checklists.templates') }}"
               class="px-5 py-2.5 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white font-medium">
                Скасувати
            </a>
            <button type="submit"
                    class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors">
                Створити шаблон
            </button>
        </div>
    </form>
</div>

<script>
function checklistForm() {
    return {
        items: [{ title: '', description: '' }],

        addItem() {
            this.items.push({ title: '', description: '' });
        },

        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            }
        }
    }
}
</script>
@endsection
