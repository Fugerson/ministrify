@extends('layouts.app')

@section('title', 'Витрати')

@section('actions')
<a href="{{ route('finances.expenses.create') }}"
   class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
    </svg>
    Витрата
</a>
@endsection

@section('content')
@php
    $months = ['Січень', 'Лютий', 'Березень', 'Квітень', 'Травень', 'Червень', 'Липень', 'Серпень', 'Вересень', 'Жовтень', 'Листопад', 'Грудень'];
@endphp

<div class="space-y-6">
    <!-- Back link -->
    <a href="{{ route('finances.index') }}" class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-sm">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        До аналітики
    </a>

    <!-- Summary card -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-4">
                <a href="{{ route('finances.expenses.index', ['year' => $month == 1 ? $year - 1 : $year, 'month' => $month == 1 ? 12 : $month - 1]) }}"
                   class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $months[$month - 1] }} {{ $year }}</h2>
                <a href="{{ route('finances.expenses.index', ['year' => $month == 12 ? $year + 1 : $year, 'month' => $month == 12 ? 1 : $month + 1]) }}"
                   class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>

            @admin
            <a href="{{ route('finances.index') }}" class="text-primary-600 dark:text-primary-400 hover:text-primary-500 text-sm">
                Повний звіт
            </a>
            @endadmin
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                <p class="text-sm text-blue-600 dark:text-blue-400">Бюджет</p>
                <p class="text-2xl font-bold text-blue-700 dark:text-blue-300">{{ number_format($totals['budget'], 0, ',', ' ') }} ₴</p>
            </div>
            <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4">
                <p class="text-sm text-red-600 dark:text-red-400">Витрачено</p>
                <p class="text-2xl font-bold text-red-700 dark:text-red-300">{{ number_format($totals['spent'], 0, ',', ' ') }} ₴</p>
            </div>
            <div class="bg-{{ $totals['budget'] - $totals['spent'] < 0 ? 'orange' : 'green' }}-50 dark:bg-{{ $totals['budget'] - $totals['spent'] < 0 ? 'orange' : 'green' }}-900/20 rounded-lg p-4">
                <p class="text-sm {{ $totals['budget'] - $totals['spent'] < 0 ? 'text-orange-600 dark:text-orange-400' : 'text-green-600 dark:text-green-400' }}">Залишок</p>
                <p class="text-2xl font-bold {{ $totals['budget'] - $totals['spent'] < 0 ? 'text-orange-700 dark:text-orange-300' : 'text-green-700 dark:text-green-300' }}">
                    {{ number_format($totals['budget'] - $totals['spent'], 0, ',', ' ') }} ₴
                </p>
            </div>
        </div>
    </div>

    <!-- Expenses list -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <form method="GET" class="flex items-center space-x-4">
                <input type="hidden" name="year" value="{{ $year }}">
                <input type="hidden" name="month" value="{{ $month }}">
                <select name="ministry" onchange="this.form.submit()"
                        class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                    <option value="">Всі команди</option>
                    @foreach($ministries as $ministry)
                        <option value="{{ $ministry->id }}" {{ request('ministry') == $ministry->id ? 'selected' : '' }}>
                            {{ $ministry->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Дата</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Опис</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Команда</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Категорія</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Сума</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Дії</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($expenses as $expense)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $expense->date->format('d.m') }}
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $expense->description }}</p>
                                @if($expense->notes)
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ Str::limit($expense->notes, 50) }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $expense->ministry?->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $expense->category?->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-red-600 dark:text-red-400 text-right">
                                -{{ number_format($expense->amount, 0, ',', ' ') }} ₴
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <a href="{{ route('finances.expenses.edit', $expense) }}" class="text-primary-600 dark:text-primary-400 hover:text-primary-900 dark:hover:text-primary-300">
                                    Редагувати
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                Немає витрат за цей місяць
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($expenses->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $expenses->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
