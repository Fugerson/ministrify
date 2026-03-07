@extends('layouts.app')

@section('title', __('app.pm_new_message'))

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('pm.index') }}" class="inline-flex items-center text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            {{ __('app.pm_back_to_messages') }}
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h1 class="text-xl font-bold text-gray-900 dark:text-white mb-6">{{ __('app.pm_new_message') }}</h1>

        <form @submit.prevent="submit($refs.pmForm)" x-ref="pmForm" class="space-y-6"
              x-data="{ ...ajaxForm({ url: '{{ route('pm.store') }}', method: 'POST' }) }">

            <!-- Recipient -->
            <div x-data="{ search: '', open: false, selected: null }">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.pm_to_label') }}</label>

                <div class="relative">
                    <input type="text"
                           x-model="search"
                           @focus="open = true"
                           @click.away="open = false"
                           placeholder="{{ __('app.pm_start_typing_name') }}"
                           class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                           :class="selected ? 'hidden' : ''">

                    <div x-show="selected" class="flex items-center justify-between px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center mr-3">
                                <span class="text-sm font-medium text-primary-600 dark:text-primary-400" x-text="selected?.name?.charAt(0)"></span>
                            </div>
                            <span class="text-gray-900 dark:text-white" x-text="selected?.name"></span>
                        </div>
                        <button type="button" @click="selected = null; search = ''" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <input type="hidden" name="recipient_id" :value="selected?.id">

                    <!-- Dropdown -->
                    <div x-show="open && !selected" x-cloak
                         class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg max-h-60 overflow-y-auto">
                        @foreach($users as $u)
                        <button type="button"
                                x-show="@js(mb_strtolower($u->name)).includes(search.toLowerCase()) || search === ''"
                                @click="selected = { id: {{ $u->id }}, name: @js($u->name) }; open = false"
                                class="w-full flex items-center px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 text-left">
                            <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center mr-3">
                                <span class="text-lg font-medium text-primary-600 dark:text-primary-400">{{ mb_substr($u->name, 0, 1) }}</span>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">{{ $u->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $u->email }}</p>
                            </div>
                        </button>
                        @endforeach
                    </div>
                </div>
                <template x-if="errors.recipient_id">
                    <p class="mt-1 text-sm text-red-600" x-text="errors.recipient_id[0]"></p>
                </template>
            </div>

            <!-- Message -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.pm_message_label') }}</label>
                <textarea name="message"
                          rows="5"
                          required
                          placeholder="{{ __('app.pm_write_your_message') }}"
                          class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent resize-none">{{ old('message') }}</textarea>
                <template x-if="errors.message">
                    <p class="mt-1 text-sm text-red-600" x-text="errors.message[0]"></p>
                </template>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('pm.index') }}"
                   class="px-6 py-3 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-xl">
                    {{ __('app.msg_cancel') }}
                </a>
                <button type="submit" :disabled="saving"
                        class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl">
                    <span x-text="saving ? @json(__('app.msg_sending')) : @json(__('app.msg_send'))"></span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
