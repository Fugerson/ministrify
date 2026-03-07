<div x-data="financesDashboard()" class="space-y-6">
    <!-- Period selector -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center space-x-2">
                <select x-model="selectedYear" @change="updatePeriod()"
                        class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                    @for($y = now()->year; $y >= now()->year - 5; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
                <select x-model="selectedMonth" @change="updatePeriod()"
                        class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                    <option value="" {{ empty($month) ? 'selected' : '' }}>{{ __('app.full_year') }}</option>
                    @foreach([1 => __('app.month_january'), 2 => __('app.month_february'), 3 => __('app.month_march'), 4 => __('app.month_april'), 5 => __('app.month_may'), 6 => __('app.month_june'), 7 => __('app.month_july'), 8 => __('app.month_august'), 9 => __('app.month_september'), 10 => __('app.month_october'), 11 => __('app.month_november'), 12 => __('app.month_december')] as $m => $name)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    @include('finances.partials.content.analytics-body')
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
function financesDashboard() {
    // Restore saved filters if page loaded without explicit params
    const _urlParams = new URLSearchParams(window.location.search);
    if (!_urlParams.has('year') && !_urlParams.has('month')) {
        const _saved = filterStorage.load('finance_analytics', { selectedYear: '', selectedMonth: '' });
        if (_saved.selectedYear) {
            const params = new URLSearchParams();
            params.set('year', _saved.selectedYear);
            if (_saved.selectedMonth) params.set('month', _saved.selectedMonth);
            Livewire.navigate('{{ route("finances.index") }}?' + params.toString());
            return {};
        }
    }

    return {
        selectedYear: '{{ $year }}',
        selectedMonth: '{{ $month ?? "" }}',

        init() {
            // Save current filters so they persist on next visit
            filterStorage.save('finance_analytics', {
                selectedYear: this.selectedYear,
                selectedMonth: this.selectedMonth
            });
            this.initChart();
            this.initPaymentMethodsChart();
        },

        updatePeriod() {
            filterStorage.save('finance_analytics', {
                selectedYear: this.selectedYear,
                selectedMonth: this.selectedMonth
            });
            const params = new URLSearchParams();
            params.set('year', this.selectedYear);
            if (this.selectedMonth) params.set('month', this.selectedMonth);
            Livewire.navigate('{{ route("finances.index") }}?' + params.toString());
        },

        initChart() {
            // Chart initialization will be handled by the analytics-body partial
        },

        initPaymentMethodsChart() {
            // Chart initialization will be handled by the analytics-body partial
        }
    }
}
</script>
