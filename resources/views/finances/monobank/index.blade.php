@extends('layouts.app')

@section('title', 'Monobank')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Monobank</h1>
            <p class="text-gray-500 dark:text-gray-400">Синхронізація транзакцій з особистої картки</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('finances.index') }}" class="inline-flex items-center px-4 py-2 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                До фінансів
            </a>
        </div>
    </div>

    @if(!$isConnected)
    <!-- Not Connected State -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-8 text-center">
        <div class="w-20 h-20 mx-auto mb-6 bg-gradient-to-br from-gray-900 to-gray-700 rounded-2xl flex items-center justify-center">
            <svg class="w-10 h-10 text-white" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
            </svg>
        </div>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Підключіть Monobank</h2>
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
                    class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500"
                    required>
                @error('token')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="w-full px-6 py-3 bg-gray-900 hover:bg-gray-800 text-white font-medium rounded-xl transition-colors">
                Підключити Monobank
            </button>
        </form>
    </div>
    @else
    <!-- Connected State -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Connection Info Card -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-900 dark:text-white">Підключення</h3>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1.5"></span>
                    Активно
                </span>
            </div>

            @if($clientName)
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                <span class="font-medium text-gray-900 dark:text-white">{{ $clientName }}</span>
            </p>
            @endif

            @if(count($accounts) > 0)
            <div class="mb-4">
                <label class="block text-sm text-gray-500 dark:text-gray-400 mb-2">Обраний рахунок</label>
                <form action="{{ route('finances.monobank.select-account') }}" method="POST">
                    @csrf
                    <select name="account_id" onchange="this.form.submit()"
                        class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white text-sm">
                        @foreach($accounts as $account)
                        <option value="{{ $account['id'] }}" {{ $church->monobank_account_id === $account['id'] ? 'selected' : '' }}>
                            {{ $account['masked_pan'] ?? 'Картка' }} ({{ number_format($account['balance'], 2) }} {{ $account['currency'] }})
                        </option>
                        @endforeach
                    </select>
                </form>
            </div>
            @endif

            <form action="{{ route('finances.monobank.disconnect') }}" method="POST" onsubmit="return confirm('Відключити Monobank?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full px-4 py-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg transition-colors text-sm font-medium">
                    Відключити
                </button>
            </form>
        </div>

        <!-- Stats Cards -->
        <div class="lg:col-span-2 grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Всього</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
                <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $stats['income'] }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Надходжень</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
                <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ $stats['unprocessed'] }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Не оброблено</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
                <p class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ $stats['last_sync'] ? $stats['last_sync']->diffForHumans() : 'Ніколи' }}
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Остання синхр.</p>
            </div>
        </div>
    </div>

    <!-- Sync Controls -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h3 class="font-semibold text-gray-900 dark:text-white">Синхронізація</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Завантажити нові транзакції з Monobank</p>
            </div>
            <form action="{{ route('finances.monobank.sync') }}" method="POST" class="flex items-center gap-3">
                @csrf
                <select name="days" class="px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm">
                    <option value="7">За 7 днів</option>
                    <option value="14">За 14 днів</option>
                    <option value="30">За 30 днів</option>
                </select>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Синхронізувати
                </button>
            </form>
        </div>
    </div>

    <!-- Transactions List -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
            <h3 class="font-semibold text-gray-900 dark:text-white">Транзакції</h3>
        </div>

        @if($transactions->isEmpty())
        <div class="p-12 text-center">
            <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <p class="text-gray-500 dark:text-gray-400">Немає транзакцій. Натисніть "Синхронізувати"</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Дата</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Опис</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Сума</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Статус</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Дії</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($transactions as $tx)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 {{ $tx->is_ignored ? 'opacity-50' : '' }}">
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-white">{{ $tx->mono_time->format('d.m.Y') }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $tx->mono_time->format('H:i') }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-gray-900 dark:text-white">{{ $tx->counterpart_display }}</div>
                            @if($tx->comment)
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $tx->comment }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <span class="font-medium {{ $tx->is_income ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ $tx->formatted_amount }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($tx->is_processed)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300">
                                Імпортовано
                            </span>
                            @elseif($tx->is_ignored)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">
                                Приховано
                            </span>
                            @elseif($tx->is_income)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300">
                                Новий
                            </span>
                            @else
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">
                                Витрата
                            </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            @if(!$tx->is_processed && $tx->is_income && !$tx->is_ignored)
                            <div x-data="{ open: false }" class="relative inline-block">
                                <button @click="open = !open" class="px-3 py-1.5 text-sm bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors">
                                    Імпорт
                                </button>
                                <div x-show="open" @click.outside="open = false" x-cloak
                                    class="absolute right-0 mt-2 w-72 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 z-10 p-4">
                                    <form action="{{ route('finances.monobank.import', $tx) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="category_id" value="{{ $donationCategory?->id }}">
                                        <div class="mb-3">
                                            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Опис</label>
                                            <input type="text" name="description" value="{{ $tx->counterpart_display }}"
                                                class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm">
                                        </div>
                                        <button type="submit" class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium">
                                            Імпортувати як пожертву
                                        </button>
                                    </form>
                                    <form action="{{ route('finances.monobank.ignore', $tx) }}" method="POST" class="mt-2">
                                        @csrf
                                        <button type="submit" class="w-full px-4 py-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg text-sm">
                                            Приховати
                                        </button>
                                    </form>
                                </div>
                            </div>
                            @elseif($tx->is_ignored)
                            <form action="{{ route('finances.monobank.restore', $tx) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-sm text-primary-600 hover:text-primary-700">
                                    Відновити
                                </button>
                            </form>
                            @elseif($tx->is_processed && $tx->transaction)
                            <a href="{{ route('finances.index') }}" class="text-sm text-primary-600 hover:text-primary-700">
                                Переглянути
                            </a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
    @endif
</div>
@endsection
