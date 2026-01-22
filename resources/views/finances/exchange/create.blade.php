@extends('layouts.app')

@section('title', 'Обмін валюти')

@section('content')
<div class="max-w-lg mx-auto">
    <a href="{{ route('finances.index') }}" class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-sm mb-6">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Назад
    </a>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Обмін валюти</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Зареєструйте обмін валюти</p>
        </div>

        <form action="{{ route('finances.exchange.store') }}" method="POST" class="p-6 space-y-6"
              x-data="{
                  fromCurrency: 'USD',
                  toCurrency: 'UAH',
                  fromAmount: '',
                  toAmount: '',
                  rate: {{ $exchangeRates['USD'] ?? 41 }},
                  nbuRates: {{ json_encode($exchangeRates) }},
                  init() {
                      this.updateDefaultRate();
                  },
                  updateDefaultRate() {
                      if (this.fromCurrency !== 'UAH' && this.toCurrency === 'UAH') {
                          this.rate = this.nbuRates[this.fromCurrency] || 1;
                      } else if (this.fromCurrency === 'UAH' && this.toCurrency !== 'UAH') {
                          this.rate = this.nbuRates[this.toCurrency] || 1;
                      } else {
                          this.rate = 1;
                      }
                      this.calculate();
                  },
                  calculate() {
                      if (!this.fromAmount || this.fromAmount <= 0 || !this.rate) {
                          this.toAmount = '';
                          return;
                      }
                      if (this.fromCurrency !== 'UAH' && this.toCurrency === 'UAH') {
                          this.toAmount = (this.fromAmount * this.rate).toFixed(2);
                      } else if (this.fromCurrency === 'UAH' && this.toCurrency !== 'UAH') {
                          this.toAmount = (this.fromAmount / this.rate).toFixed(2);
                      } else {
                          this.toAmount = (this.fromAmount * this.rate).toFixed(2);
                      }
                  },
                  calculateRate() {
                      if (this.fromAmount > 0 && this.toAmount > 0) {
                          if (this.fromCurrency !== 'UAH' && this.toCurrency === 'UAH') {
                              this.rate = (this.toAmount / this.fromAmount).toFixed(4);
                          } else if (this.fromCurrency === 'UAH' && this.toCurrency !== 'UAH') {
                              this.rate = (this.fromAmount / this.toAmount).toFixed(4);
                          }
                      }
                  }
              }">
            @csrf

            <!-- From Currency -->
            <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4">
                <label class="block text-sm font-medium text-red-700 dark:text-red-300 mb-2">
                    Віддаєте
                </label>
                <div class="flex gap-3">
                    <div class="flex-1">
                        <input type="number" name="from_amount" x-model="fromAmount" @input="calculate()" step="0.01" min="0.01" required
                               class="w-full px-4 py-3 text-lg border border-red-200 dark:border-red-800 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-red-500 focus:border-red-500"
                               placeholder="0.00">
                    </div>
                    <select name="from_currency" x-model="fromCurrency" @change="updateDefaultRate()"
                            class="w-28 px-3 py-3 text-lg border border-red-200 dark:border-red-800 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        @foreach($enabledCurrencies as $curr)
                            <option value="{{ $curr }}" {{ $curr === 'USD' ? 'selected' : '' }}>{{ \App\Helpers\CurrencyHelper::symbol($curr) }} {{ $curr }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Exchange Rate -->
            <div class="bg-amber-50 dark:bg-amber-900/20 rounded-lg p-4">
                <label class="block text-sm font-medium text-amber-700 dark:text-amber-300 mb-2">
                    Курс обміну
                </label>
                <div class="flex items-center gap-3">
                    <span class="text-gray-600 dark:text-gray-400">1</span>
                    <span class="font-medium text-gray-900 dark:text-white" x-text="fromCurrency !== 'UAH' ? fromCurrency : toCurrency"></span>
                    <span class="text-gray-600 dark:text-gray-400">=</span>
                    <input type="number" x-model="rate" @input="calculate()" step="0.0001" min="0.0001" required
                           class="w-32 px-3 py-2 text-center border border-amber-200 dark:border-amber-800 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                    <span class="font-medium text-gray-900 dark:text-white" x-text="fromCurrency !== 'UAH' ? 'UAH' : toCurrency"></span>
                </div>
                <p class="text-xs text-amber-600 dark:text-amber-400 mt-2">
                    Курс НБУ: <span x-text="(nbuRates[fromCurrency !== 'UAH' ? fromCurrency : toCurrency] || 0).toFixed(2)"></span> ₴
                </p>
            </div>

            <!-- Arrow -->
            <div class="flex justify-center">
                <div class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                    </svg>
                </div>
            </div>

            <!-- To Currency -->
            <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                <label class="block text-sm font-medium text-green-700 dark:text-green-300 mb-2">
                    Отримуєте
                </label>
                <div class="flex gap-3">
                    <div class="flex-1">
                        <input type="number" name="to_amount" x-model="toAmount" @input="calculateRate()" step="0.01" min="0.01" required
                               class="w-full px-4 py-3 text-lg border border-green-200 dark:border-green-800 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-green-500"
                               placeholder="0.00">
                    </div>
                    <select name="to_currency" x-model="toCurrency" @change="updateDefaultRate()"
                            class="w-28 px-3 py-3 text-lg border border-green-200 dark:border-green-800 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        @foreach($enabledCurrencies as $curr)
                            <option value="{{ $curr }}" {{ $curr === 'UAH' ? 'selected' : '' }}>{{ \App\Helpers\CurrencyHelper::symbol($curr) }} {{ $curr }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Date -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Дата обміну
                </label>
                <input type="date" name="date" value="{{ now()->format('Y-m-d') }}" required
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Нотатки
                </label>
                <input type="text" name="notes" maxlength="500"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                       placeholder="Де обміняли, курс тощо...">
            </div>

            <!-- Submit -->
            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('finances.index') }}"
                   class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                    Скасувати
                </a>
                <button type="submit"
                        class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                    Зареєструвати обмін
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
