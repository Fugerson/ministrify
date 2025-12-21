@extends('layouts.app')

@section('title', 'Витрати')

@section('actions')
<a href="{{ route('expenses.create') }}"
   class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Витрата
</a>
@endsection

@section('content')
@php
    $months = ['Січень', 'Лютий', 'Березень', 'Квітень', 'Травень', 'Червень', 'Липень', 'Серпень', 'Вересень', 'Жовтень', 'Листопад', 'Грудень'];
@endphp

<div class="space-y-6">
    <!-- Summary card -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-4">
                <a href="{{ route('expenses.index', ['year' => $month == 1 ? $year - 1 : $year, 'month' => $month == 1 ? 12 : $month - 1]) }}"
                   class="p-2 hover:bg-gray-100 rounded-lg">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <h2 class="text-xl font-semibold text-gray-900">{{ $months[$month - 1] }} {{ $year }}</h2>
                <a href="{{ route('expenses.index', ['year' => $month == 12 ? $year + 1 : $year, 'month' => $month == 12 ? 1 : $month + 1]) }}"
                   class="p-2 hover:bg-gray-100 rounded-lg">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>

            @admin
            <a href="{{ route('expenses.report') }}" class="text-primary-600 hover:text-primary-500 text-sm">
                Повний звіт
            </a>
            @endadmin
        </div>

        <div class="grid grid-cols-3 gap-6">
            <div>
                <p class="text-sm text-gray-500">Бюджет</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($totals['budget'], 0, ',', ' ') }} &#8372;</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Витрачено</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($totals['spent'], 0, ',', ' ') }} &#8372;</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Залишок</p>
                <p class="text-2xl font-bold {{ $totals['budget'] - $totals['spent'] < 0 ? 'text-red-600' : 'text-green-600' }}">
                    {{ number_format($totals['budget'] - $totals['spent'], 0, ',', ' ') }} &#8372;
                </p>
            </div>
        </div>
    </div>

    <!-- Expenses list -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b">
            <form method="GET" class="flex items-center space-x-4">
                <input type="hidden" name="year" value="{{ $year }}">
                <input type="hidden" name="month" value="{{ $month }}">
                <select name="ministry" onchange="this.form.submit()"
                        class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">Всі служіння</option>
                    @foreach($ministries as $ministry)
                        <option value="{{ $ministry->id }}" {{ request('ministry') == $ministry->id ? 'selected' : '' }}>
                            {{ $ministry->icon }} {{ $ministry->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Дата</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Опис</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Служіння</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Категорія</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Сума</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Дії</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($expenses as $expense)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $expense->date->format('d.m') }}
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-medium text-gray-900">{{ $expense->description }}</p>
                                @if($expense->notes)
                                    <p class="text-sm text-gray-500">{{ Str::limit($expense->notes, 50) }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $expense->ministry->icon }} {{ $expense->ministry->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $expense->category?->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                                {{ number_format($expense->amount, 0, ',', ' ') }} &#8372;
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <a href="{{ route('expenses.edit', $expense) }}" class="text-primary-600 hover:text-primary-900">
                                    Редагувати
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                Немає витрат за цей місяць
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($expenses->hasPages())
            <div class="px-6 py-4 border-t">
                {{ $expenses->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
