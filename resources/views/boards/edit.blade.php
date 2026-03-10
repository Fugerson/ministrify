@extends('layouts.app')

@section('title', __('app.board_settings'))

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <form @submit.prevent="submit($refs.f)" x-ref="f" x-data="{ ...ajaxForm({ url: '{{ route('boards.update', $board) }}', method: 'PUT' }), color: '{{ old('color', $board->color) }}' }" class="space-y-6">

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('app.board_info') }}</h2>

            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.board_name') }} *</label>
                    <input type="text" name="name" id="name" required value="{{ old('name', $board->name) }}"
                           class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                    <template x-if="errors.name">
                        <p class="mt-1 text-sm text-red-600" x-text="errors.name[0]"></p>
                    </template>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.description') }}</label>
                    <textarea name="description" id="description" rows="3"
                              class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">{{ old('description', $board->description) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.color') }}</label>
                    <div class="flex items-center gap-3">
                        @php
                            $colors = ['#ef4444', '#f97316', '#f59e0b', '#84cc16', '#22c55e', '#14b8a6', '#06b6d4', '#3b82f6', '#6366f1', '#8b5cf6', '#a855f7', '#ec4899'];
                        @endphp
                        @foreach($colors as $c)
                            <button type="button"
                                    @click="color = '{{ $c }}'"
                                    class="w-8 h-8 rounded-lg transition-transform hover:scale-110"
                                    :class="color === '{{ $c }}' ? 'ring-2 ring-offset-2 dark:ring-offset-gray-800 ring-gray-900 dark:ring-white scale-110' : ''"
                                    style="background-color: {{ $c }}"></button>
                        @endforeach
                    </div>
                    <input type="hidden" name="color" :value="color">
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                @if(auth()->user()->canDelete('boards'))
                <button type="button"
                        @click="ajaxAction('{{ route('boards.archive', $board) }}', 'POST').then(() => setTimeout(() => Livewire.navigate('{{ route('boards.index') }}'), 600))"
                        class="text-yellow-600 dark:text-yellow-400 hover:text-yellow-700 text-sm font-medium">
                    {{ __('app.archive') }}
                </button>
                <button type="button"
                        @click="ajaxDelete('{{ route('boards.destroy', $board) }}', @js( __('app.delete_board_confirm') ), null, '{{ route('boards.index') }}')"
                        class="text-red-600 dark:text-red-400 hover:text-red-700 text-sm font-medium">
                    {{ __('app.delete') }}
                </button>
                @endif
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('boards.show', $board) }}"
                   class="px-5 py-2.5 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white font-medium">
                    {{ __('app.cancel') }}
                </a>
                <button type="submit" :disabled="saving"
                        class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors disabled:opacity-50">
                    {{ __('app.save') }}
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
