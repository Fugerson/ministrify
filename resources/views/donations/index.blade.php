@extends('layouts.app')

@section('title', __('app.donations_title'))

@section('content')
@include('finances.partials.tabs')

<div id="finance-content">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('app.donations_title') }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('app.donations_manage_desc') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('donations.export') }}" class="btn-secondary flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                {{ __('app.donations_export') }}
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 md:gap-4 mb-6 md:mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 md:p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('app.donations_this_month') }}</span>
            </div>
            <p class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total_month'], 0, ',', ' ') }} ₴</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 md:p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('app.donations_this_year') }}</span>
            </div>
            <p class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total_year'], 0, ',', ' ') }} ₴</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 md:p-6 shadow-sm border border-gray-200 dark:border-gray-700 col-span-2 sm:col-span-1">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('app.donations_total_transactions') }}</span>
            </div>
            <p class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['transactions_count'] ?? 0 }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8">
        <!-- Left Column: Chart + Donations -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">{{ __('app.donations_dynamics') }}</h3>
                <div class="h-64" x-data="donationChart({{ json_encode($chartData) }})">
                    <canvas x-ref="chart"></canvas>
                </div>
            </div>

            <!-- Recent Donations -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ __('app.donations_recent') }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('app.donations_date') }}</th>
                                <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('app.donations_amount') }}</th>
                                <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase hidden sm:table-cell">{{ __('app.donations_purpose') }}</th>
                                <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase hidden md:table-cell">{{ __('app.donations_method') }}</th>
                                <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('app.donations_status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($donations as $donation)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-3 md:px-6 py-3 md:py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ ($donation->date ?? $donation->created_at)->format('d.m.Y') }}
                                    </td>
                                    <td class="px-3 md:px-6 py-3 md:py-4 whitespace-nowrap">
                                        <span class="font-semibold text-green-600 dark:text-green-400">{{ $donation->formatted_amount }}</span>
                                    </td>
                                    <td class="px-3 md:px-6 py-3 md:py-4 hidden sm:table-cell">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ $donation->purpose ?? ($donation->campaign?->name ?? __('app.donations_general')) }}</span>
                                    </td>
                                    <td class="px-3 md:px-6 py-3 md:py-4 whitespace-nowrap hidden md:table-cell">
                                        @if($donation->payment_method === 'liqpay')
                                            <span class="inline-flex items-center gap-1 text-xs font-medium text-green-700 bg-green-100 dark:bg-green-900/30 dark:text-green-400 px-2 py-1 rounded">
                                                LiqPay
                                            </span>
                                        @elseif($donation->payment_method === 'monobank')
                                            <span class="inline-flex items-center gap-1 text-xs font-medium text-gray-700 bg-gray-100 dark:bg-gray-700 dark:text-gray-300 px-2 py-1 rounded">
                                                Monobank
                                            </span>
                                        @else
                                            <span class="text-sm text-gray-500">{{ $donation->payment_method ?? __('app.donations_card') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-3 md:px-6 py-3 md:py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                                            @if($donation->status === 'completed') bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400
                                            @elseif($donation->status === 'pending') bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400
                                            @elseif($donation->status === 'failed') bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400
                                            @else bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300
                                            @endif">
                                            {{ $donation->status_label }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                        <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                        </svg>
                                        <p>{{ __('app.donations_empty') }}</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($donations->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        {{ $donations->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Column: Campaigns + Top Donors -->
        <div class="space-y-6">
            <!-- Campaigns -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ __('app.donations_campaigns') }}</h3>
                    <button type="button" onclick="document.getElementById('newCampaignModal').classList.remove('hidden')"
                        class="text-sm text-primary-600 hover:text-primary-700 font-medium">{{ __('app.donations_new_campaign') }}</button>
                </div>
                <div id="campaigns-list" class="p-6 space-y-4">
                    @forelse($campaigns as $campaign)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="font-medium text-gray-900 dark:text-white">{{ $campaign->name }}</h4>
                                <div class="flex gap-1">
                                    <button type="button" x-data="{ active: {{ $campaign->is_active ? 'true' : 'false' }} }"
                                            @click="ajaxAction('{{ route('donations.campaigns.toggle', $campaign) }}', 'POST').then(() => { active = !active; })"
                                            class="p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700" :title="active ? '{{ __('app.donations_pause') }}' : '{{ __('app.donations_activate') }}'">
                                        <svg x-show="active" class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <svg x-show="!active" x-cloak class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </button>
                                    <button type="button"
                                            @click="ajaxDelete('{{ route('donations.campaigns.destroy', $campaign) }}', '{{ __('app.donations_delete_campaign') }}', () => $el.closest('.border.border-gray-200').remove())"
                                            class="p-1 rounded hover:bg-red-50 dark:hover:bg-red-900/20">
                                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
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
                                        {{ __('app.donations_days_remaining', ['count' => $campaign->days_remaining]) }}
                                    @else
                                        {{ __('app.donations_completed') }}
                                    @endif
                                @else
                                    {{ __('app.donations_open_ended') }}
                                @endif
                            </p>
                        </div>
                    @empty
                        <p class="text-center text-gray-500 dark:text-gray-400 py-4">{{ __('app.donations_no_campaigns') }}</p>
                    @endforelse
                </div>
            </div>

            <!-- By Purpose -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ __('app.donations_by_purpose') }}</h3>
                </div>
                <div class="p-6 space-y-3">
                    @forelse($byPurpose as $item)
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ $item->purpose }}</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ number_format($item->total_amount, 0, ',', ' ') }} ₴</span>
                        </div>
                    @empty
                        <p class="text-center text-gray-500 dark:text-gray-400">{{ __('app.donations_no_data') }}</p>
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
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('app.donations_new_campaign_title') }}</h3>
            <form @submit.prevent="submit($refs.campaignForm)" x-ref="campaignForm"
                  x-data="{ ...ajaxForm({ url: '{{ route('donations.campaigns.store') }}', method: 'POST', resetOnSuccess: true, stayOnPage: true, onSuccess(data) { _addCampaign(this, data); } }) }">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.donations_name') }}</label>
                        <input type="text" name="name" required class="input w-full">
                        <template x-if="errors.name">
                            <p class="mt-1 text-sm text-red-600" x-text="errors.name[0]"></p>
                        </template>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.donations_description') }}</label>
                        <textarea name="description" rows="2" class="input w-full" placeholder="{{ __('app.donations_description_placeholder') }}"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.donations_goal_uah') }}</label>
                        <input type="number" name="goal_amount" min="0" step="100" class="input w-full">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.donations_start') }}</label>
                            <input type="date" name="start_date" class="input w-full">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.donations_end') }}</label>
                            <input type="date" name="end_date" class="input w-full">
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="document.getElementById('newCampaignModal').classList.add('hidden')" class="btn-secondary">{{ __('app.donations_cancel') }}</button>
                    <button type="submit" :disabled="saving" class="btn-primary disabled:opacity-50">{{ __('app.donations_create') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function _addCampaign(ctx, data) {
    document.getElementById('newCampaignModal').classList.add('hidden');
    var list = document.getElementById('campaigns-list');
    if (!list) return;
    var empty = list.querySelector('.text-center.text-gray-500');
    if (empty) empty.remove();
    var form = ctx.$refs.campaignForm;
    var name = form.querySelector('[name="name"]').value;
    var goal = form.querySelector('[name="goal_amount"]').value;
    var endDate = form.querySelector('[name="end_date"]').value;
    var safeName = name.replace(/&/g, '\x26amp;').replace(/</g, '\x26lt;').replace(/>/g, '\x26gt;');
    var id = data.id;
    var dateText = @json(__('app.donations_open_ended'));
    if (endDate) {
        var d = new Date(endDate);
        var now = new Date();
        var diff = Math.ceil((d - now) / 86400000);
        dateText = diff > 0 ? @json(__('app.donations_days_remaining', ['count' => ''])) + diff : @json(__('app.donations_completed'));
    }
    var goalHtml = '';
    if (goal && parseFloat(goal) > 0) {
        goalHtml = '\x3Cdiv class="mb-2">\x3Cdiv class="flex justify-between text-xs mb-1">\x3Cspan class="text-gray-500 dark:text-gray-400">0 ₴\x3C/span>\x3Cspan class="font-medium text-gray-700 dark:text-gray-300">0%\x3C/span>\x3C/div>\x3Cdiv class="h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">\x3Cdiv class="h-full bg-primary-500 rounded-full" style="width:0%">\x3C/div>\x3C/div>\x3C/div>';
    }
    var el = document.createElement('div');
    el.className = 'border border-gray-200 dark:border-gray-700 rounded-xl p-4';
    el.setAttribute('x-data', '');
    el.innerHTML = '\x3Cdiv class="flex justify-between items-start mb-2">\x3Ch4 class="font-medium text-gray-900 dark:text-white">' + safeName + '\x3C/h4>\x3Cdiv class="flex gap-1">\x3Cbutton type="button" x-data="{ active: true }" @click="ajaxAction(\'/donations/campaigns/' + id + '/toggle\', \'POST\').then(() => { active = !active; })" class="p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700" :title="active ? \'' + @json(__('app.donations_pause')) + '\' : \'' + @json(__('app.donations_activate')) + '\'">\x3Csvg x-show="active" class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">\x3Cpath stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>\x3C/svg>\x3Csvg x-show="!active" x-cloak class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">\x3Cpath stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>\x3C/svg>\x3C/button>\x3Cbutton type="button" @click="ajaxDelete(\'/donations/campaigns/' + id + '\', \'' + @json(__('app.donations_delete_campaign')) + '\', () => $el.closest(\'.border.border-gray-200\').remove())" class="p-1 rounded hover:bg-red-50 dark:hover:bg-red-900/20">\x3Csvg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">\x3Cpath stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>\x3C/svg>\x3C/button>\x3C/div>\x3C/div>' + goalHtml + '\x3Cp class="text-xs text-gray-500 dark:text-gray-400">' + dateText + '\x3C/p>';
    list.appendChild(el);
    Alpine.initTree(el);
}
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
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
                        label: @json(__('app.donations_chart_label')),
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
</div><!-- /finance-content -->
@endsection
