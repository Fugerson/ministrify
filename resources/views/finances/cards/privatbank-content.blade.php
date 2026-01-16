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
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">PrivatBank не підключено</h2>
        <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md mx-auto">
            Автоматично імпортуйте транзакції з вашої картки PrivatBank для обліку пожертв
        </p>

        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-6 max-w-md mx-auto mb-6 text-left">
            <h3 class="font-medium text-gray-900 dark:text-white mb-3">Як підключити:</h3>
            <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                <li class="flex items-start gap-2">
                    <span class="flex-shrink-0 w-5 h-5 rounded-full bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 flex items-center justify-center text-xs font-medium">1</span>
                    <span>Увійдіть в <a href="https://www.privat24.ua/rd/merchant" target="_blank" class="text-green-600 hover:underline">Приват24 Бізнес</a></span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="flex-shrink-0 w-5 h-5 rounded-full bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 flex items-center justify-center text-xs font-medium">2</span>
                    <span>Перейдіть в "Налаштування → API"</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="flex-shrink-0 w-5 h-5 rounded-full bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 flex items-center justify-center text-xs font-medium">3</span>
                    <span>Створіть Merchant та отримайте ID і пароль</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="flex-shrink-0 w-5 h-5 rounded-full bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 flex items-center justify-center text-xs font-medium">4</span>
                    <span>Введіть дані нижче</span>
                </li>
            </ol>
        </div>

        <form action="{{ route('finances.privatbank.connect') }}" method="POST" class="max-w-md mx-auto space-y-4" autocomplete="off">
            @csrf
            <div>
                <input type="text" name="merchant_id" placeholder="Merchant ID" autocomplete="off"
                       class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-transparent"
                       required>
                @error('merchant_id')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <input type="text" name="password" placeholder="Пароль Merchant" autocomplete="new-password"
                       class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-transparent"
                       required>
                @error('password')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <input type="text" name="card_number" placeholder="Номер картки (останні 4 цифри)" autocomplete="off"
                       class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-transparent"
                       required>
                @error('card_number')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="w-full px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                Підключити PrivatBank
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
                            Підключено
                        </span>
                        @if($church->privatbank_card_number)
                            <span class="ml-2">Картка: **** {{ $church->privatbank_card_number }}</span>
                        @endif
                        @if($church->privatbank_auto_sync)
                            <span class="ml-2 text-green-600">Автосинхронізація</span>
                        @endif
                    </p>
                </div>
            </div>

            <a href="{{ route('finances.privatbank.index') }}"
               class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Переглянути транзакції
            </a>
        </div>
    </div>
@endif
