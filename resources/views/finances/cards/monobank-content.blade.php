@php
    $monobankConnected = !empty($church->monobank_token);
@endphp

@if(!$monobankConnected)
    {{-- Not Connected State --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-8 text-center">
        <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
        </div>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Monobank не підключено</h2>
        <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md mx-auto">
            Автоматично імпортуйте транзакції з вашої картки Monobank для обліку пожертв
        </p>

        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-6 max-w-md mx-auto mb-6 text-left">
            <h3 class="font-medium text-gray-900 dark:text-white mb-3">Як отримати токен:</h3>
            <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                <li class="flex items-start gap-2">
                    <span class="flex-shrink-0 w-5 h-5 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex items-center justify-center text-xs font-medium">1</span>
                    <span>Перейдіть на <a href="https://api.monobank.ua/" target="_blank" class="text-primary-600 hover:underline">api.monobank.ua</a></span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="flex-shrink-0 w-5 h-5 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex items-center justify-center text-xs font-medium">2</span>
                    <span>Відскануйте QR-код у застосунку Monobank</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="flex-shrink-0 w-5 h-5 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex items-center justify-center text-xs font-medium">3</span>
                    <span>Натисніть <strong>"Активувати токен"</strong></span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="flex-shrink-0 w-5 h-5 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex items-center justify-center text-xs font-medium">4</span>
                    <span>Скопіюйте отриманий токен</span>
                </li>
            </ol>
        </div>

        <form action="{{ route('finances.monobank.connect') }}" method="POST" class="max-w-md mx-auto">
            @csrf
            <div class="mb-4">
                <input type="text" name="token" placeholder="Вставте токен сюди..."
                       class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                       required>
                @error('token')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="w-full px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                Підключити Monobank
            </button>
        </form>
    </div>
@else
    {{-- Connected - Show summary and link to full page --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-black rounded-xl flex items-center justify-center">
                    <span class="text-white font-bold text-lg">M</span>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Monobank</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        <span class="inline-flex items-center gap-1 text-green-600">
                            <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                            Підключено
                        </span>
                        @if($church->monobank_auto_sync)
                            <span class="ml-2 text-green-600">Автосинхронізація</span>
                        @endif
                    </p>
                </div>
            </div>

            <a href="{{ route('finances.monobank.index') }}"
               class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Переглянути транзакції
            </a>
        </div>
    </div>
@endif
