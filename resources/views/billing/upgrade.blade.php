@extends('layouts.app')

@section('title', 'Оплата підписки')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Оплата підписки</h1>

        <div class="mb-8">
            <h2 class="text-xl font-bold text-primary-600 dark:text-primary-400 mb-2">{{ $plan->name }}</h2>
            @if($plan->description)
                <p class="text-gray-600 dark:text-gray-400">{{ $plan->description }}</p>
            @endif
        </div>

        <form method="POST" action="{{ route('billing.pay', $plan) }}" x-data="{ period: 'monthly' }">
            @csrf

            <div class="space-y-4 mb-8">
                <label class="block p-4 border-2 rounded-xl cursor-pointer transition-colors"
                       :class="period === 'monthly' ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-gray-200 dark:border-gray-700'">
                    <input type="radio" name="period" value="monthly" x-model="period" class="sr-only">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="font-medium text-gray-900 dark:text-white">Щомісячна оплата</span>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Оплата кожного місяця</p>
                        </div>
                        <div class="text-right">
                            <span class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ number_format($plan->price_monthly_uah, 0, ',', ' ') }} ₴
                            </span>
                            <span class="text-gray-500 dark:text-gray-400">/міс</span>
                        </div>
                    </div>
                </label>

                @if($plan->price_yearly > 0)
                    <label class="block p-4 border-2 rounded-xl cursor-pointer transition-colors relative"
                           :class="period === 'yearly' ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-gray-200 dark:border-gray-700'">
                        <input type="radio" name="period" value="yearly" x-model="period" class="sr-only">
                        @if($plan->yearly_savings_percent > 0)
                            <div class="absolute -top-2 right-4 px-2 py-0.5 bg-green-500 text-white text-xs font-medium rounded">
                                Економія {{ $plan->yearly_savings_percent }}%
                            </div>
                        @endif
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="font-medium text-gray-900 dark:text-white">Річна оплата</span>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Оплата за рік наперед</p>
                            </div>
                            <div class="text-right">
                                <span class="text-2xl font-bold text-gray-900 dark:text-white">
                                    {{ number_format($plan->price_yearly_uah, 0, ',', ' ') }} ₴
                                </span>
                                <span class="text-gray-500 dark:text-gray-400">/рік</span>
                            </div>
                        </div>
                    </label>
                @endif
            </div>

            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 mb-6">
                <h3 class="font-medium text-gray-900 dark:text-white mb-3">Що входить в план:</h3>
                <ul class="space-y-2 text-sm">
                    <li class="flex items-center text-gray-700 dark:text-gray-300">
                        <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        {{ $plan->max_people > 0 ? 'До ' . $plan->max_people . ' людей' : 'Необмежено людей' }}
                    </li>
                    <li class="flex items-center text-gray-700 dark:text-gray-300">
                        <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        {{ $plan->max_ministries > 0 ? 'До ' . $plan->max_ministries . ' служінь' : 'Необмежено служінь' }}
                    </li>
                    @if($plan->has_telegram_bot)
                        <li class="flex items-center text-gray-700 dark:text-gray-300">
                            <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Telegram бот для сповіщень
                        </li>
                    @endif
                    @if($plan->has_finances)
                        <li class="flex items-center text-gray-700 dark:text-gray-300">
                            <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Фінанси та облік
                        </li>
                    @endif
                    @if($plan->has_website_builder)
                        <li class="flex items-center text-gray-700 dark:text-gray-300">
                            <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Конструктор сайту
                        </li>
                    @endif
                </ul>
            </div>

            <div class="flex items-center gap-4">
                <a href="{{ route('billing.index') }}"
                   class="px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition-colors">
                    Назад
                </a>
                <button type="submit"
                        class="flex-1 px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Оплатити через LiqPay
                </button>
            </div>
        </form>
    </div>

    <div class="mt-6 text-center text-sm text-gray-500 dark:text-gray-400">
        <p>Оплата захищена LiqPay (ПриватБанк)</p>
        <p>Ви можете скасувати підписку в будь-який час</p>
    </div>
</div>
@endsection
