@extends('layouts.app')

@section('title', 'Тарифи та оплата')

@section('content')
@include('partials.section-tabs', ['tabs' => [
    ['route' => 'settings.index', 'label' => 'Загальні', 'active' => 'settings.*', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>'],
    ['route' => 'website-builder.index', 'label' => 'Сайт', 'active' => 'website-builder.*', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>'],
    ['route' => 'billing.index', 'label' => 'Тарифи', 'active' => 'billing.*', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>'],
    ['route' => 'support.index', 'label' => 'Підтримка', 'active' => 'support.*', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>'],
]])

<div class="space-y-6">
    <!-- Current Plan -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Поточний план</h2>
            @if($church->subscription_ends_at)
                <div class="text-sm">
                    @if($church->isSubscriptionExpiringSoon())
                        <span class="text-yellow-600 dark:text-yellow-400">
                            Закінчується через {{ $church->subscription_days_left }} дн.
                        </span>
                    @elseif($church->isSubscriptionExpired())
                        <span class="text-red-600 dark:text-red-400">Термін дії закінчився</span>
                    @else
                        <span class="text-gray-500 dark:text-gray-400">
                            Активний до {{ $church->subscription_ends_at->format('d.m.Y') }}
                        </span>
                    @endif
                </div>
            @endif
        </div>

        @if($church->plan)
            <div class="flex items-center gap-4">
                <div class="flex-1">
                    <h3 class="text-2xl font-bold text-primary-600 dark:text-primary-400">{{ $church->plan->name }}</h3>
                    @if($church->plan->description)
                        <p class="text-gray-600 dark:text-gray-400 mt-1">{{ $church->plan->description }}</p>
                    @endif
                </div>
                <div class="text-right">
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $church->plan->formatted_price_monthly }}
                    </p>
                    @if(!$church->plan->isFree() && $church->billing_period === 'yearly')
                        <p class="text-sm text-gray-500 dark:text-gray-400">Річна підписка</p>
                    @endif
                </div>
            </div>
        @else
            <p class="text-gray-500 dark:text-gray-400">План не вибрано</p>
        @endif
    </div>

    <!-- Usage Stats -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Використання ресурсів</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($usage as $resource => $data)
                @php
                    $labels = [
                        'people' => 'Люди',
                        'ministries' => 'Служіння',
                        'users' => 'Користувачі',
                        'events_per_month' => 'Події/місяць',
                    ];
                    $label = $labels[$resource] ?? $resource;
                    $current = $data['current'];
                    $limit = $data['limit'];
                    $percentage = $limit > 0 ? min(100, ($current / $limit) * 100) : 0;
                    $isUnlimited = $limit === 0;
                    $isNearLimit = !$isUnlimited && $percentage >= 80;
                    $isAtLimit = !$isUnlimited && $percentage >= 100;
                @endphp
                <div class="p-4 rounded-lg border {{ $isAtLimit ? 'border-red-300 dark:border-red-700 bg-red-50 dark:bg-red-900/20' : ($isNearLimit ? 'border-yellow-300 dark:border-yellow-700 bg-yellow-50 dark:bg-yellow-900/20' : 'border-gray-200 dark:border-gray-700') }}">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</span>
                        <span class="text-sm font-bold {{ $isAtLimit ? 'text-red-600 dark:text-red-400' : ($isNearLimit ? 'text-yellow-600 dark:text-yellow-400' : 'text-gray-900 dark:text-white') }}">
                            {{ $current }}{{ $isUnlimited ? '' : ' / ' . $limit }}
                        </span>
                    </div>
                    @unless($isUnlimited)
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="h-2 rounded-full {{ $isAtLimit ? 'bg-red-500' : ($isNearLimit ? 'bg-yellow-500' : 'bg-primary-500') }}"
                                 style="width: {{ min(100, $percentage) }}%"></div>
                        </div>
                    @else
                        <div class="text-xs text-gray-500 dark:text-gray-400">Необмежено</div>
                    @endunless
                </div>
            @endforeach
        </div>
    </div>

    <!-- Available Plans -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Доступні плани</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($plans as $plan)
                @php
                    $isCurrent = $church->subscription_plan_id === $plan->id || ($church->subscription_plan_id === null && $plan->isFree());
                @endphp
                <div class="relative rounded-xl border-2 {{ $isCurrent ? 'border-primary-500' : 'border-gray-200 dark:border-gray-700' }} p-6">
                    @if($isCurrent)
                        <div class="absolute -top-3 left-4 px-2 bg-primary-500 text-white text-xs font-medium rounded">
                            Поточний
                        </div>
                    @endif

                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">{{ $plan->name }}</h3>

                    <div class="mb-4">
                        <span class="text-3xl font-bold text-gray-900 dark:text-white">
                            {{ $plan->isFree() ? 'Безкоштовно' : number_format($plan->price_monthly_uah, 0, ',', ' ') . ' ₴' }}
                        </span>
                        @unless($plan->isFree())
                            <span class="text-gray-500 dark:text-gray-400">/міс</span>
                            @if($plan->yearly_savings_percent > 0)
                                <div class="text-sm text-green-600 dark:text-green-400">
                                    {{ number_format($plan->price_yearly_uah, 0, ',', ' ') }} ₴/рік
                                    (економія {{ $plan->yearly_savings_percent }}%)
                                </div>
                            @endif
                        @endunless
                    </div>

                    @if($plan->description)
                        <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">{{ $plan->description }}</p>
                    @endif

                    <ul class="space-y-2 mb-6">
                        <li class="flex items-center text-sm">
                            <svg class="w-4 h-4 mr-2 {{ $plan->max_people > 0 ? 'text-primary-500' : 'text-green-500' }}" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">
                                {{ $plan->max_people > 0 ? 'До ' . $plan->max_people . ' людей' : 'Необмежено людей' }}
                            </span>
                        </li>
                        <li class="flex items-center text-sm">
                            <svg class="w-4 h-4 mr-2 {{ $plan->max_ministries > 0 ? 'text-primary-500' : 'text-green-500' }}" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">
                                {{ $plan->max_ministries > 0 ? 'До ' . $plan->max_ministries . ' служінь' : 'Необмежено служінь' }}
                            </span>
                        </li>
                        <li class="flex items-center text-sm">
                            <svg class="w-4 h-4 mr-2 {{ $plan->has_telegram_bot ? 'text-green-500' : 'text-gray-400' }}" fill="currentColor" viewBox="0 0 20 20">
                                @if($plan->has_telegram_bot)
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                @else
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                @endif
                            </svg>
                            <span class="{{ $plan->has_telegram_bot ? 'text-gray-700 dark:text-gray-300' : 'text-gray-400' }}">
                                Telegram бот
                            </span>
                        </li>
                        <li class="flex items-center text-sm">
                            <svg class="w-4 h-4 mr-2 {{ $plan->has_finances ? 'text-green-500' : 'text-gray-400' }}" fill="currentColor" viewBox="0 0 20 20">
                                @if($plan->has_finances)
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                @else
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                @endif
                            </svg>
                            <span class="{{ $plan->has_finances ? 'text-gray-700 dark:text-gray-300' : 'text-gray-400' }}">
                                Фінанси
                            </span>
                        </li>
                        <li class="flex items-center text-sm">
                            <svg class="w-4 h-4 mr-2 {{ $plan->has_website_builder ? 'text-green-500' : 'text-gray-400' }}" fill="currentColor" viewBox="0 0 20 20">
                                @if($plan->has_website_builder)
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                @else
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                @endif
                            </svg>
                            <span class="{{ $plan->has_website_builder ? 'text-gray-700 dark:text-gray-300' : 'text-gray-400' }}">
                                Конструктор сайту
                            </span>
                        </li>
                    </ul>

                    @if($isCurrent)
                        <button disabled class="w-full px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 rounded-lg cursor-not-allowed">
                            Поточний план
                        </button>
                    @elseif($plan->isFree())
                        <form method="POST" action="{{ route('billing.downgrade') }}"
                              onsubmit="return confirm('Ви впевнені, що хочете перейти на безкоштовний план?')">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                Перейти на Free
                            </button>
                        </form>
                    @else
                        <a href="{{ route('billing.upgrade', $plan) }}"
                           class="block w-full px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-center rounded-lg transition-colors">
                            Обрати план
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <!-- Payment History -->
    @if($payments->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Історія платежів</h2>
                <a href="{{ route('billing.history') }}" class="text-primary-600 dark:text-primary-400 hover:underline text-sm">
                    Всі платежі
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-500 dark:text-gray-400">Дата</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-500 dark:text-gray-400">Опис</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-500 dark:text-gray-400">Сума</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-500 dark:text-gray-400">Статус</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $payment)
                            <tr class="border-b border-gray-100 dark:border-gray-700">
                                <td class="py-3 px-4 text-sm text-gray-900 dark:text-white">
                                    {{ $payment->created_at->format('d.m.Y H:i') }}
                                </td>
                                <td class="py-3 px-4 text-sm text-gray-600 dark:text-gray-400">
                                    {{ $payment->description }}
                                </td>
                                <td class="py-3 px-4 text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $payment->formatted_amount }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full
                                        @if($payment->status === 'success') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                                        @elseif($payment->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400
                                        @elseif($payment->status === 'failure') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400
                                        @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-400
                                        @endif">
                                        {{ $payment->status_label }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
