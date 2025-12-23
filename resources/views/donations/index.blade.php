@extends('layouts.app')

@section('title', 'Пожертви')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Пожертви</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Управління онлайн-пожертвами та кампаніями</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('donations.qr') }}" class="btn-secondary flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                </svg>
                QR-код
            </a>
            <a href="{{ route('donations.export') }}" class="btn-secondary flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Експорт
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Цього місяця</span>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total_month'], 0, ',', ' ') }} ₴</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Цього року</span>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total_year'], 0, ',', ' ') }} ₴</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Донорів</span>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['donors_count'] }}</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-lg bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </div>
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Регулярних</span>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['recurring_count'] }}</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-lg bg-pink-100 dark:bg-pink-900/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-pink-600 dark:text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Середня</span>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['avg_donation'], 0, ',', ' ') }} ₴</p>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-8">
        <!-- Left Column: Chart + Donations -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Динаміка пожертв</h3>
                <div class="h-64" x-data="donationChart({{ json_encode($chartData) }})">
                    <canvas x-ref="chart"></canvas>
                </div>
            </div>

            <!-- Recent Donations -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Останні пожертви</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Донор</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Сума</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Призначення</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Метод</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Статус</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Дата</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($donations as $donation)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                                @if($donation->is_anonymous)
                                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                    </svg>
                                                @else
                                                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ substr($donation->donor_name ?? 'A', 0, 1) }}</span>
                                                @endif
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900 dark:text-white text-sm">{{ $donation->donor_display_name }}</p>
                                                @if($donation->donor_email)
                                                    <p class="text-xs text-gray-500">{{ $donation->donor_email }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="font-semibold text-gray-900 dark:text-white">{{ $donation->formatted_amount }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ $donation->purpose ?? ($donation->campaign?->name ?? 'Загальна') }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($donation->payment_method === 'liqpay')
                                            <span class="inline-flex items-center gap-1 text-xs font-medium text-green-700 bg-green-100 dark:bg-green-900/30 dark:text-green-400 px-2 py-1 rounded">
                                                LiqPay
                                            </span>
                                        @elseif($donation->payment_method === 'monobank')
                                            <span class="inline-flex items-center gap-1 text-xs font-medium text-gray-700 bg-gray-100 dark:bg-gray-700 dark:text-gray-300 px-2 py-1 rounded">
                                                Monobank
                                            </span>
                                        @else
                                            <span class="text-sm text-gray-500">{{ $donation->payment_method }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                                            @if($donation->status === 'completed') bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400
                                            @elseif($donation->status === 'pending') bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400
                                            @elseif($donation->status === 'failed') bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400
                                            @else bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300
                                            @endif">
                                            {{ $donation->status_label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $donation->created_at->format('d.m.Y H:i') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                        <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                        </svg>
                                        <p>Ще немає пожертв</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($donations->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                        {{ $donations->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Column: Campaigns + Top Donors -->
        <div class="space-y-6">
            <!-- Campaigns -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Кампанії зборів</h3>
                    <button type="button" onclick="document.getElementById('newCampaignModal').classList.remove('hidden')"
                        class="text-sm text-primary-600 hover:text-primary-700 font-medium">+ Нова</button>
                </div>
                <div class="p-6 space-y-4">
                    @forelse($campaigns as $campaign)
                        <div class="border border-gray-100 dark:border-gray-700 rounded-xl p-4">
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="font-medium text-gray-900 dark:text-white">{{ $campaign->name }}</h4>
                                <div class="flex gap-1">
                                    <form action="{{ route('donations.campaigns.toggle', $campaign) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700" title="{{ $campaign->is_active ? 'Призупинити' : 'Активувати' }}">
                                            @if($campaign->is_active)
                                                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            @endif
                                        </button>
                                    </form>
                                    <form action="{{ route('donations.campaigns.destroy', $campaign) }}" method="POST" class="inline" onsubmit="return confirm('Видалити кампанію?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 rounded hover:bg-red-50 dark:hover:bg-red-900/20">
                                            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            @if($campaign->goal_amount)
                                <div class="mb-2">
                                    <div class="flex justify-between text-xs mb-1">
                                        <span class="text-gray-500 dark:text-gray-400">{{ number_format($campaign->raised_amount, 0, ',', ' ') }} ₴</span>
                                        <span class="font-medium text-gray-700 dark:text-gray-300">{{ $campaign->progress_percent }}%</span>
                                    </div>
                                    <div class="h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                        <div class="h-full bg-primary-500 rounded-full transition-all" style="width: {{ $campaign->progress_percent }}%"></div>
                                    </div>
                                </div>
                            @endif
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                @if($campaign->days_remaining !== null)
                                    @if($campaign->days_remaining > 0)
                                        Залишилось {{ $campaign->days_remaining }} днів
                                    @else
                                        Завершено
                                    @endif
                                @else
                                    Безстрокова
                                @endif
                            </p>
                        </div>
                    @empty
                        <p class="text-center text-gray-500 dark:text-gray-400 py-4">Немає активних кампаній</p>
                    @endforelse
                </div>
            </div>

            <!-- Top Donors -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Топ донорів ({{ now()->year }})</h3>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($topDonors as $donor)
                        <div class="px-6 py-3 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                                    <span class="text-sm font-medium text-primary-600 dark:text-primary-400">{{ substr($donor->donor_name ?? 'A', 0, 1) }}</span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $donor->donor_name }}</p>
                                    <p class="text-xs text-gray-500">{{ $donor->donations_count }} пожертв</p>
                                </div>
                            </div>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ number_format($donor->total_amount, 0, ',', ' ') }} ₴</span>
                        </div>
                    @empty
                        <p class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Ще немає даних</p>
                    @endforelse
                </div>
            </div>

            <!-- By Purpose -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-900 dark:text-white">По призначенню</h3>
                </div>
                <div class="p-6 space-y-3">
                    @forelse($byPurpose as $item)
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ $item->purpose }}</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ number_format($item->total_amount, 0, ',', ' ') }} ₴</span>
                        </div>
                    @empty
                        <p class="text-center text-gray-500 dark:text-gray-400">Немає даних</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Campaign Modal -->
<div id="newCampaignModal" class="hidden fixed inset-0 z-50 overflow-y-auto" x-data>
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-900/50" onclick="document.getElementById('newCampaignModal').classList.add('hidden')"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Нова кампанія збору</h3>
            <form action="{{ route('donations.campaigns.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Назва</label>
                        <input type="text" name="name" required class="input w-full" placeholder="Наприклад: Збір на ремонт">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Опис</label>
                        <textarea name="description" rows="2" class="input w-full" placeholder="Коротко про мету збору"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ціль (грн)</label>
                        <input type="number" name="goal_amount" min="0" step="100" class="input w-full" placeholder="100000">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Початок</label>
                            <input type="date" name="start_date" class="input w-full">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Кінець</label>
                            <input type="date" name="end_date" class="input w-full">
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="document.getElementById('newCampaignModal').classList.add('hidden')" class="btn-secondary">Скасувати</button>
                    <button type="submit" class="btn-primary">Створити</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function donationChart(data) {
    return {
        init() {
            const ctx = this.$refs.chart.getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.map(d => d.month),
                    datasets: [{
                        label: 'Пожертви (грн)',
                        data: data.map(d => d.amount),
                        backgroundColor: 'rgba(99, 102, 241, 0.5)',
                        borderColor: 'rgb(99, 102, 241)',
                        borderWidth: 1,
                        borderRadius: 8,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString() + ' ₴';
                                }
                            }
                        }
                    }
                }
            });
        }
    }
}
</script>
@endsection
