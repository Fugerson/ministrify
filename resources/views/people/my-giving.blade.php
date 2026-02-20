@extends('layouts.app')

@section('title', 'Мої пожертви')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Мої пожертви</h1>
        <a href="{{ route('my-profile') }}" class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400">
            ← Назад до профілю
        </a>
    </div>

    <!-- Stats cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Цього місяця</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total_this_month'], 0, ',', ' ') }} ₴</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Цього року</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total_this_year'], 0, ',', ' ') }} ₴</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Всього</p>
            <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">{{ number_format($stats['total_lifetime'], 0, ',', ' ') }} ₴</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Кількість</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['donations_count'] }}</p>
        </div>
    </div>

    <!-- Monthly chart -->
    @if(count($monthlyData) > 0)
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Пожертви по місяцях ({{ now()->year }})</h2>
        <div class="h-48">
            <canvas id="monthlyChart"></canvas>
        </div>
    </div>
    @endif

    <!-- Transactions list -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Історія пожертв</h2>
            @if($years->count() > 0)
            <form method="GET" class="flex items-center gap-2">
                <select name="year" onchange="this.form.submit()" class="text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-1.5">
                    <option value="all" {{ $year === 'all' ? 'selected' : '' }}>Всі роки</option>
                    @foreach($years as $y)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </form>
            @endif
        </div>

        @if($transactions->isEmpty())
        <x-empty-state
            title="Немає пожертв"
            description="Історія ваших пожертв буде відображатися тут"
            icon="inbox"
        />
        @else
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            @foreach($transactions as $transaction)
            <div class="px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">
                            {{ $transaction->description ?? $transaction->category?->name ?? 'Пожертва' }}
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $transaction->date->format('d.m.Y') }}
                            @if($transaction->source_type)
                                · {{ $transaction->source_type_label }}
                            @endif
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="font-semibold text-green-600 dark:text-green-400">
                        +{{ number_format($transaction->amount, 0, ',', ' ') }} ₴
                    </p>
                </div>
            </div>
            @endforeach
        </div>

        @if($transactions->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $transactions->links() }}
        </div>
        @endif
        @endif
    </div>
</div>

@if(count($monthlyData) > 0)
<script>
onPageReady(function() {
    var el = document.getElementById('monthlyChart');
    if (!el) return;
    var old = Chart.getChart(el); if (old) old.destroy();
    const ctx = el.getContext('2d');
    const months = ['Січ', 'Лют', 'Бер', 'Кві', 'Тра', 'Чер', 'Лип', 'Сер', 'Вер', 'Жов', 'Лис', 'Гру'];
    const data = @json($monthlyData);

    const chartData = months.map((_, i) => data[i + 1] || 0);

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: months,
            datasets: [{
                label: 'Пожертви',
                data: chartData,
                backgroundColor: 'rgba(34, 197, 94, 0.5)',
                borderColor: 'rgb(34, 197, 94)',
                borderWidth: 1,
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString('uk-UA') + ' ₴';
                        }
                    }
                }
            }
        }
    });
});
</script>
@endif
@endsection
