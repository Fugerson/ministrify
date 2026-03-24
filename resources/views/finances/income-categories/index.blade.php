@extends('layouts.app')

@section('title', __('app.income_categories_title'))

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <a href="{{ route('settings.index') }}" class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-sm">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        {{ __('app.back_to_settings') }}
    </a>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('app.income_categories_title') }}</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('app.income_categories_desc') }}</p>
        </div>

        <div class="p-6">
            <!-- Add new category form -->
            <form @submit.prevent="submit($refs.addForm)" x-ref="addForm"
                  x-data="{ ...ajaxForm({ url: '{{ route('settings.income-categories.store') }}', method: 'POST', resetOnSuccess: true, stayOnPage: true, onSuccess() { _addIncomeCategory(this); } }) }"
                  class="mb-6 p-4 bg-gray-50 dark:bg-gray-900/50 rounded-lg">
                <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-4">{{ __('app.add_category') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <input type="text" name="name" placeholder="{{ __('app.category_name_placeholder') }}" required
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                        <template x-if="errors.name">
                            <p class="mt-1 text-sm text-red-600" x-text="errors.name[0]"></p>
                        </template>
                    </div>
                    <div>
                        <input type="color" name="color" value="#3B82F6"
                               class="w-full h-10 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer">
                    </div>
                    <div>
                        <button type="submit" :disabled="saving" class="w-full px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50">
                            {{ __('app.add_btn') }}
                        </button>
                    </div>
                </div>
            </form>

            <!-- Categories list -->
            <div id="categories-list" class="space-y-3">
                @forelse($categories as $category)
                    <div x-data="{ editing: false }" data-category class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                        <div x-show="!editing" class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center text-lg" style="background-color: {{ $category->color }}20">
                                    <div class="w-4 h-4 rounded-full" style="background-color: {{ $category->color }}"></div>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $category->name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $category->incomes_count }} {{ __('app.entries_count') }}</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <button @click="editing = true" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                @if($category->incomes_count == 0)
                                    <button type="button"
                                            @click="ajaxDelete('{{ route('settings.income-categories.destroy', $category) }}', @js( __('messages.confirm_delete_category') ), () => $el.closest('[data-category]').remove())"
                                            class="text-red-400 hover:text-red-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </div>

                        <!-- Edit form -->
                        <form x-show="editing"
                              @submit.prevent="submit($refs.editForm{{ $category->id }})" x-ref="editForm{{ $category->id }}"
                              x-data="{ ...ajaxForm({ url: '{{ route('settings.income-categories.update', $category) }}', method: 'PUT', stayOnPage: true, onSuccess() { _updateIncomeCategory(this); } }) }">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <input type="text" name="name" value="{{ $category->name }}" required
                                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                                </div>
                                {{-- emoji field removed --}}
                                <div>
                                    <input type="color" name="color" value="{{ $category->color }}"
                                           class="w-full h-10 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer">
                                </div>
                                <div class="flex items-center space-x-2">
                                    <button type="submit" :disabled="saving" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50">
                                        {{ __('app.save_btn') }}
                                    </button>
                                    <button type="button" @click="editing = false" class="px-4 py-2 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                                        {{ __('app.cancel') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                @empty
                    <p class="text-center text-gray-500 dark:text-gray-400 py-8">{{ __('app.no_categories_yet') }}. {{ __('app.add_first_income_category') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
<script>
function _addIncomeCategory(ctx) {
    var form = ctx.$refs.addForm;
    var name = form.querySelector('[name="name"]').value;
    var color = form.querySelector('[name="color"]').value || '#3B82F6';
    var list = document.getElementById('categories-list');
    if (!list) { /* DOM element missing, SPA reload as fallback */ Livewire.navigate(window.location.href); return; }
    var empty = list.querySelector('.text-center.text-gray-500');
    if (empty) empty.remove();
    var safeName = name.replace(/&/g, '\x26amp;').replace(/</g, '\x26lt;').replace(/>/g, '\x26gt;');
    var el = document.createElement('div');
    el.setAttribute('data-category', '');
    el.className = 'border border-gray-200 dark:border-gray-700 rounded-lg p-4';
    el.innerHTML = '\x3Cdiv class="flex items-center justify-between">\x3Cdiv class="flex items-center space-x-4">\x3Cdiv class="w-10 h-10 rounded-full flex items-center justify-center text-lg" style="background-color: ' + color + '20">\x3Cdiv class="w-4 h-4 rounded-full" style="background-color: ' + color + '">\x3C/div>\x3C/div>\x3Cdiv>\x3Cp class="font-medium text-gray-900 dark:text-white">' + safeName + '\x3C/p>\x3Cp class="text-xs text-gray-500 dark:text-gray-400 mt-1">0 ' + @js(__('app.entries_count') ) + '\x3C/p>\x3C/div>\x3C/div>\x3C/div>';
    list.appendChild(el);
}

function _updateIncomeCategory(ctx) {
    var cat = ctx.$el.closest('[data-category]');
    if (!cat) return;
    var form = ctx.$el;
    var name = form.querySelector('[name="name"]').value;
    var color = form.querySelector('[name="color"]').value || '#3B82F6';
    var nameEl = cat.querySelector('.font-medium.text-gray-900');
    if (nameEl) nameEl.textContent = name;
    var iconEl = cat.querySelector('.w-10.h-10');
    if (iconEl) { iconEl.style.backgroundColor = color + '20'; var dot = iconEl.querySelector('.w-4.h-4'); if (dot) dot.style.backgroundColor = color; }
    var editing = cat.__x ? cat.__x.$data : (cat._x_dataStack ? cat._x_dataStack[0] : null);
    if (editing) editing.editing = false;
}
</script>
@endsection
