@php
    $privatbankConnected = !empty($church->privatbank_merchant_id);
@endphp

@if(!$privatbankConnected)
    {{-- Not Connected State --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-8 text-center">
        <div class="w-16 h-16 mx-auto mb-4 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
        </div>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">{{ __('app.privatbank_not_connected') }}</h2>
        <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md mx-auto">
            {{ __('app.privat_auto_import_desc') }}
        </p>

        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-6 max-w-md mx-auto mb-6 text-left">
            <h3 class="font-medium text-gray-900 dark:text-white mb-3">{{ __('app.how_to_connect') }}</h3>
            <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                <li class="flex items-start gap-2">
                    <span class="flex-shrink-0 w-5 h-5 rounded-full bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 flex items-center justify-center text-xs font-medium">1</span>
                    <span>{{ __('app.privat_card_step_login') }} <a href="https://www.privat24.ua/rd/merchant" target="_blank" class="text-green-600 hover:underline">Privat24 Business</a></span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="flex-shrink-0 w-5 h-5 rounded-full bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 flex items-center justify-center text-xs font-medium">2</span>
                    <span>{{ __('app.privat_card_step_settings') }}</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="flex-shrink-0 w-5 h-5 rounded-full bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 flex items-center justify-center text-xs font-medium">3</span>
                    <span>{{ __('app.privat_card_step_create') }}</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="flex-shrink-0 w-5 h-5 rounded-full bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 flex items-center justify-center text-xs font-medium">4</span>
                    <span>{{ __('app.privat_card_step_enter') }}</span>
                </li>
            </ol>
        </div>

        <form @submit.prevent="submit($refs.pbForm)" x-ref="pbForm" x-data="{ ...ajaxForm({ url: '{{ route('finances.privatbank.connect') }}', method: 'POST' }) }" class="max-w-md mx-auto space-y-4" autocomplete="off">
            <div>
                <input type="text" name="merchant_id" placeholder="Merchant ID" autocomplete="off"
                       class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-transparent"
                       required>
                <template x-if="errors.merchant_id"><p class="mt-1 text-sm text-red-500" x-text="errors.merchant_id[0]"></p></template>
            </div>
            <div>
                <input type="text" name="password" placeholder="{{ __('app.merchant_password') }}" autocomplete="new-password"
                       class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-transparent"
                       required>
                <template x-if="errors.password"><p class="mt-1 text-sm text-red-500" x-text="errors.password[0]"></p></template>
            </div>
            <div>
                <input type="text" name="card_number" placeholder="{{ __('app.card_number_last4') }}" autocomplete="off"
                       class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-transparent"
                       required>
                <template x-if="errors.card_number"><p class="mt-1 text-sm text-red-500" x-text="errors.card_number[0]"></p></template>
            </div>
            <button type="submit" :disabled="saving" class="w-full px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50">
                <span x-show="!saving">{{ __('app.connect_privatbank') }}</span>
                <span x-show="saving">{{ __('app.connecting') }}</span>
            </button>
        </form>
    </div>
@else
    {{-- Connected - Show summary and link to full page --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-green-600 rounded-xl flex items-center justify-center">
                    <span class="text-white font-bold text-lg">P</span>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">PrivatBank</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        <span class="inline-flex items-center gap-1 text-green-600">
                            <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                            {{ __('app.connected') }}
                        </span>
                        @if($church->privatbank_card_number)
                            <span class="ml-2">{{ __('app.card_label') }} **** {{ $church->privatbank_card_number }}</span>
                        @endif
                        @if($church->privatbank_auto_sync)
                            <span class="ml-2 text-green-600">{{ __('app.auto_sync') }}</span>
                        @endif
                    </p>
                </div>
            </div>

            <a href="{{ route('finances.privatbank.index') }}"
               class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                {{ __('app.view_transactions') }}
            </a>
        </div>
    </div>
@endif
