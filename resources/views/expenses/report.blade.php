@extends('layouts.app')

@section('title', __('app.report_title'))

@section('content')
@php
    $months = [__('app.january'), __('app.february'), __('app.march'), __('app.april'), __('app.may'), __('app.june'), __('app.july'), __('app.august'), __('app.september'), __('app.october'), __('app.november'), __('app.december')];
@endphp

<div class="space-y-6">
    <!-- Summary -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $months[$month - 1] }} {{ $year }}</h2>
        </div>

        <div class="grid grid-cols-3 gap-6">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.report_total_budget') }}</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totalBudget, 0, ',', ' ') }} &#8372;</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.report_spent') }}</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totalSpent, 0, ',', ' ') }} &#8372;</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.report_balance') }}</p>
                <p class="text-3xl font-bold {{ $totalBudget - $totalSpent < 0 ? 'text-red-600' : 'text-green-600' }}">
                    {{ number_format($totalBudget - $totalSpent, 0, ',', ' ') }} &#8372;
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- By ministry -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('app.report_by_ministry') }}</h3>
            </div>
            <div class="p-6 space-y-4">
                @foreach($byMinistry as $ministry)
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $ministry['name'] }}</span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                {{ number_format($ministry['spent'], 0, ',', ' ') }}
                                @if($ministry['budget'])
                                    / {{ number_format($ministry['budget'], 0, ',', ' ') }} &#8372;
                                @endif
                            </span>
                        </div>
                        @if($ministry['budget'])
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="h-2 rounded-full {{ $ministry['percentage'] > 90 ? 'bg-red-500' : ($ministry['percentage'] > 70 ? 'bg-yellow-500' : 'bg-green-500') }}"
                                     style="width: {{ min(100, $ministry['percentage']) }}%"></div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <!-- By category -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('app.report_by_category') }}</h3>
            </div>
            <div class="p-6 space-y-3">
                @foreach($byCategory as $category)
                    @if($category->total > 0)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $category->name }}</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($category->total, 0, ',', ' ') }} &#8372;</span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    <!-- Recent expenses -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('app.report_recent_expenses') }}</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('app.expense_date') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('app.expense_team') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('app.expense_description') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('app.expense_amount') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('app.report_who') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($recentExpenses as $expense)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $expense->date->format('d.m') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $expense->ministry?->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $expense->description }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white text-right">{{ number_format($expense->amount, 0, ',', ' ') }} &#8372;</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $expense->user?->name ?? __('app.report_deleted_user') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <a href="{{ route('finances.transactions', ['filter' => 'expense']) }}" class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-sm">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        {{ __('app.expense_back') }}
    </a>
</div>
@endsection
