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
                    <option value="" {{ empty($month) ? 'selected' : '' }}>Весь рік</option>
                    @foreach([1 => 'Січень', 2 => 'Лютий', 3 => 'Березень', 4 => 'Квітень', 5 => 'Травень', 6 => 'Червень', 7 => 'Липень', 8 => 'Серпень', 9 => 'Вересень', 10 => 'Жовтень', 11 => 'Листопад', 12 => 'Грудень'] as $m => $name)
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
    return {
        selectedYear: '{{ $year }}',
        selectedMonth: '{{ $month ?? "" }}',

        init() {
            this.initChart();
            this.initPaymentMethodsChart();
        },

        updatePeriod() {
            const params = new URLSearchParams();
            params.set('year', this.selectedYear);
            if (this.selectedMonth) params.set('month', this.selectedMonth);
            window.location.href = '{{ route("finances.index") }}?' + params.toString();
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
