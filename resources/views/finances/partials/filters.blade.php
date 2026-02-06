{{-- Universal Finance Period Selector - shared across all finance tabs (except "Моя карта") --}}
{{-- Period is stored in localStorage and persists between tab switches --}}

@php
    $currentRoute = Route::currentRouteName();
    $showFilters = !in_array($currentRoute, ['finances.index', 'finances.cards']);
@endphp

@if($showFilters)
<div x-data="financePeriodFilter()" x-init="init()" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 mb-4">
    <div class="flex flex-wrap items-center gap-3">
        <!-- Quick Period Buttons -->
        <div class="flex flex-wrap gap-2">
            <template x-for="[key, label] in Object.entries(periodLabels)" :key="key">
                <button type="button"
                        @click="setPeriod(key)"
                        :class="activePeriod === key && !customMode ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'"
                        class="px-3 py-1.5 text-sm font-medium rounded-lg transition-colors"
                        x-text="label">
                </button>
            </template>

            <!-- Custom Date Range Button -->
            <button type="button"
                    @click="toggleCustomMode()"
                    :class="customMode ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'"
                    class="px-3 py-1.5 text-sm font-medium rounded-lg transition-colors inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span class="hidden sm:inline">Період</span>
            </button>
        </div>

        <!-- Custom Date Inputs (shown when customMode is true) -->
        <div x-show="customMode" x-cloak class="flex items-center gap-2">
            <input type="date" x-model="customStart" @change="applyCustomRange()"
                   class="px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
            <span class="text-gray-400">—</span>
            <input type="date" x-model="customEnd" @change="applyCustomRange()"
                   class="px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
        </div>

        <!-- Date Display -->
        <div class="flex-1 text-center">
            <span class="text-sm text-gray-500 dark:text-gray-400" x-text="dateRangeDisplay"></span>
        </div>

        <!-- Balance Info -->
        <div class="text-right" x-show="currentBalance !== null">
            <span class="text-sm text-gray-500 dark:text-gray-400">Баланс:</span>
            <span class="ml-1 font-semibold" :class="currentBalance >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'"
                  x-text="formatNumber(currentBalance) + ' ₴'">
            </span>
        </div>
    </div>
</div>

<script>
function financePeriodFilter() {
    return {
        activePeriod: 'month',
        customMode: false,
        customStart: '',
        customEnd: '',
        currentBalance: {{ $currentBalance ?? 'null' }},
        periodLabels: {
            'today': 'Сьогодні',
            'week': 'Тиждень',
            'month': 'Місяць',
            'quarter': 'Квартал',
            'year': 'Рік'
        },

        init() {
            // localStorage is the source of truth for user's period selection
            const saved = localStorage.getItem('financePeriod');
            const savedCustom = localStorage.getItem('financeCustomRange');

            if (savedCustom) {
                try {
                    const parsed = JSON.parse(savedCustom);
                    if (parsed.start && parsed.end) {
                        this.customMode = true;
                        this.customStart = parsed.start;
                        this.customEnd = parsed.end;
                    }
                } catch (e) {}
            } else if (saved && this.periodLabels[saved]) {
                this.activePeriod = saved;
            } else {
                // Fallback: URL params (e.g. shared link with no localStorage)
                const urlParams = new URLSearchParams(window.location.search);
                const urlStart = urlParams.get('start_date');
                const urlEnd = urlParams.get('end_date');
                if (urlStart && urlEnd) {
                    this.customMode = true;
                    this.customStart = urlStart;
                    this.customEnd = urlEnd;
                }
            }

            // Expose period globally
            window.financePeriod = this.activePeriod;
            window.financeDateRange = this.dateRange;
            window.financeCustomMode = this.customMode;

            // Dispatch initial event (not a user action, won't trigger reload)
            this.$nextTick(() => {
                window.dispatchEvent(new CustomEvent('finance-period-changed', {
                    detail: { period: this.activePeriod, dateRange: this.dateRange, customMode: this.customMode, isUserAction: false }
                }));
            });
        },

        get dateRange() {
            if (this.customMode && this.customStart && this.customEnd) {
                return {
                    start: new Date(this.customStart + 'T00:00:00'),
                    end: new Date(this.customEnd + 'T23:59:59')
                };
            }

            const now = new Date();
            let start, end;

            switch (this.activePeriod) {
                case 'today':
                    start = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                    end = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 23, 59, 59);
                    break;
                case 'week':
                    const dayOfWeek = now.getDay();
                    const diff = now.getDate() - dayOfWeek + (dayOfWeek === 0 ? -6 : 1);
                    start = new Date(now.getFullYear(), now.getMonth(), diff);
                    end = new Date(start.getTime() + 6 * 24 * 60 * 60 * 1000);
                    end.setHours(23, 59, 59);
                    break;
                case 'month':
                    start = new Date(now.getFullYear(), now.getMonth(), 1);
                    end = new Date(now.getFullYear(), now.getMonth() + 1, 0, 23, 59, 59);
                    break;
                case 'quarter':
                    const quarter = Math.floor(now.getMonth() / 3);
                    start = new Date(now.getFullYear(), quarter * 3, 1);
                    end = new Date(now.getFullYear(), quarter * 3 + 3, 0, 23, 59, 59);
                    break;
                case 'year':
                default:
                    start = new Date(now.getFullYear(), 0, 1);
                    end = new Date(now.getFullYear(), 11, 31, 23, 59, 59);
                    break;
            }

            return { start, end };
        },

        get dateRangeDisplay() {
            const { start, end } = this.dateRange;
            const formatDate = (d) => d.toLocaleDateString('uk-UA', { day: '2-digit', month: '2-digit', year: 'numeric' });
            return formatDate(start) + ' — ' + formatDate(end);
        },

        toggleCustomMode() {
            this.customMode = !this.customMode;
            if (this.customMode) {
                // Set default custom range to current month if not set
                if (!this.customStart || !this.customEnd) {
                    const now = new Date();
                    const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
                    const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                    this.customStart = formatDateLocal(firstDay);
                    this.customEnd = formatDateLocal(lastDay);
                }
                this.saveCustomRange();
            } else {
                localStorage.removeItem('financeCustomRange');
                this.dispatchChange();
            }
        },

        applyCustomRange() {
            if (this.customStart && this.customEnd) {
                this.saveCustomRange();
            }
        },

        saveCustomRange() {
            localStorage.setItem('financeCustomRange', JSON.stringify({
                start: this.customStart,
                end: this.customEnd
            }));
            localStorage.removeItem('financePeriod');
            this.dispatchChange();
        },

        setPeriod(period) {
            this.customMode = false;
            this.activePeriod = period;
            localStorage.setItem('financePeriod', period);
            localStorage.removeItem('financeCustomRange');
            this.dispatchChange();
        },

        dispatchChange() {
            window.financePeriod = this.activePeriod;
            window.financeDateRange = this.dateRange;
            window.financeCustomMode = this.customMode;
            window.dispatchEvent(new CustomEvent('finance-period-changed', {
                detail: { period: this.activePeriod, dateRange: this.dateRange, customMode: this.customMode, isUserAction: true }
            }));
        },

        formatNumber(num) {
            if (num === null) return '';
            return new Intl.NumberFormat('uk-UA').format(Math.round(num));
        }
    }
}

// Format Date to YYYY-MM-DD in local timezone (not UTC!)
window.formatDateLocal = function(d) {
    if (!(d instanceof Date)) return d;
    return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
};

// Global helper to get current period
window.getFinancePeriod = function() {
    return window.financePeriod || 'month';
};

window.getFinanceDateRange = function() {
    return window.financeDateRange || null;
};

// Handler for pages that need to reload (journal, budgets)
window.handlePeriodReload = function(detail) {
    if (!detail || !detail.dateRange) return;

    const url = new URL(window.location.href);
    const { start, end } = detail.dateRange;
    const startDate = formatDateLocal(start);
    const endDate = formatDateLocal(end);

    // If dates already match, don't reload
    const currentStart = url.searchParams.get('start_date');
    const currentEnd = url.searchParams.get('end_date');
    if (currentStart === startDate && currentEnd === endDate) {
        return;
    }

    // Remove old params
    url.searchParams.delete('year');
    url.searchParams.delete('month');
    // Add new date range params
    url.searchParams.set('start_date', startDate);
    url.searchParams.set('end_date', endDate);

    window.location.href = url.toString();
};
</script>
@endif
