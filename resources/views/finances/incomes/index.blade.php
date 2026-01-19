@extends('layouts.app')

@section('title', 'Надходження')

@section('actions')
<a href="{{ route('finances.incomes.create') }}"
   class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Надходження
</a>
@endsection

@section('content')
@include('finances.partials.tabs')

<div id="finance-content">
@php
    $months = ['Січень', 'Лютий', 'Березень', 'Квітень', 'Травень', 'Червень', 'Липень', 'Серпень', 'Вересень', 'Жовтень', 'Листопад', 'Грудень'];
@endphp

<div class="space-y-6">

    <!-- Summary card -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 md:p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-2 sm:space-x-4">
                <a href="{{ route('finances.incomes', ['year' => $month == 1 ? $year - 1 : $year, 'month' => $month == 1 ? 12 : $month - 1]) }}"
                   class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <h2 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white">{{ $months[$month - 1] }} {{ $year }}</h2>
                <a href="{{ route('finances.incomes', ['year' => $month == 12 ? $year + 1 : $year, 'month' => $month == 12 ? 1 : $month + 1]) }}"
                   class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 md:gap-6">
            <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                <p class="text-sm text-green-600 dark:text-green-400">Загалом</p>
                <p class="text-2xl font-bold text-green-700 dark:text-green-300">{{ number_format($totals['total'], 0, ',', ' ') }} ₴</p>
            </div>
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                <p class="text-sm text-blue-600 dark:text-blue-400">Десятини</p>
                <p class="text-2xl font-bold text-blue-700 dark:text-blue-300">{{ number_format($totals['tithes'], 0, ',', ' ') }} ₴</p>
            </div>
            <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4">
                <p class="text-sm text-purple-600 dark:text-purple-400">Пожертви</p>
                <p class="text-2xl font-bold text-purple-700 dark:text-purple-300">{{ number_format($totals['offerings'], 0, ',', ' ') }} ₴</p>
            </div>
        </div>
    </div>

    <!-- Incomes list -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">
        <div class="px-3 md:px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <form method="GET" class="flex items-center space-x-4">
                <input type="hidden" name="year" value="{{ $year }}">
                <input type="hidden" name="month" value="{{ $month }}">
                <select name="category" onchange="this.form.submit()"
                        class="w-full sm:w-auto px-3 py-2.5 sm:py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                    <option value="">Усі категорії</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                            {{ $category->icon ?? '💰' }} {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Дата</th>
                        <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase hidden sm:table-cell">Категорія</th>
                        <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase hidden md:table-cell">Спосіб</th>
                        <th class="px-3 md:px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Сума</th>
                        <th class="px-3 md:px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Дії</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($incomes as $income)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-3 md:px-6 py-3 md:py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">{{ $income->date->format('d.m') }}</div>
                                <!-- Mobile: show category under date -->
                                <div class="sm:hidden text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    {{ $income->category?->icon ?? '💰' }} {{ $income->category?->name ?? 'Без категорії' }}
                                </div>
                            </td>
                            <td class="px-3 md:px-6 py-3 md:py-4 whitespace-nowrap hidden sm:table-cell">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" style="background-color: {{ $income->category?->color ?? '#3B82F6' }}20; color: {{ $income->category?->color ?? '#3B82F6' }}">
                                    {{ $income->category?->icon ?? '💰' }} {{ $income->category?->name ?? 'Без категорії' }}
                                </span>
                            </td>
                            <td class="px-3 md:px-6 py-3 md:py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 hidden md:table-cell">
                                {{ $income->payment_method_label }}
                            </td>
                            <td class="px-3 md:px-6 py-3 md:py-4 whitespace-nowrap text-right">
                                <span class="text-sm font-semibold text-green-600 dark:text-green-400">
                                    +{{ \App\Helpers\CurrencyHelper::format($income->amount, $income->currency ?? 'UAH') }}
                                </span>
                                @if(($income->currency ?? 'UAH') !== 'UAH' && $income->amount_uah)
                                <span class="block text-xs text-gray-400 dark:text-gray-500">
                                    {{ number_format($income->amount_uah, 0, ',', ' ') }} ₴
                                </span>
                                @endif
                            </td>
                            <td class="px-3 md:px-6 py-3 md:py-4 whitespace-nowrap text-right text-sm">
                                <a href="{{ route('finances.incomes.edit', $income) }}" class="p-2 inline-flex text-primary-600 dark:text-primary-400 hover:text-primary-900 dark:hover:text-primary-300 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-lg">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 md:px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                Немає надходжень за цей місяць
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($incomes->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $incomes->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
</div><!-- /finance-content -->
@endsection
