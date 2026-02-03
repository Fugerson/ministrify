{{-- Mini Calendar Widget --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 lg:p-5"
     x-data="miniCalendar()" x-cloak>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/50 flex items-center justify-center">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <h3 class="font-semibold text-gray-900 dark:text-white" x-text="monthName + ' ' + year"></h3>
        </div>
        <div class="flex items-center gap-1">
            <button @click="prevMonth()" class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <button @click="goToday()" class="px-2 py-1 text-xs font-medium rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 transition-colors">
                Сьогодні
            </button>
            <button @click="nextMonth()" class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
    </div>

    {{-- Day of week headers (Monday first) --}}
    <div class="grid grid-cols-7 mb-1">
        <template x-for="day in ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Нд']" :key="day">
            <div class="text-center text-xs font-medium text-gray-400 dark:text-gray-500 py-1" x-text="day"></div>
        </template>
    </div>

    {{-- Loading spinner --}}
    <div x-show="loading" class="flex justify-center py-10">
        <svg class="w-6 h-6 animate-spin text-amber-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
    </div>

    {{-- Calendar grid --}}
    <div x-show="!loading" class="grid grid-cols-7">
        <template x-for="(cell, idx) in calendarCells" :key="idx">
            <div class="relative flex flex-col items-center py-1">
                <template x-if="cell.day !== null">
                    <div>
                        <template x-if="cell.hasEvent">
                            <a :href="'{{ route('schedule') }}?date=' + cell.date"
                               class="w-8 h-8 flex items-center justify-center rounded-full text-sm transition-colors cursor-pointer"
                               :class="{
                                   'bg-amber-500 text-white font-bold': cell.isToday,
                                   'hover:bg-amber-50 dark:hover:bg-amber-900/30 text-gray-900 dark:text-white font-medium': !cell.isToday
                               }"
                               x-text="cell.day">
                            </a>
                        </template>
                        <template x-if="!cell.hasEvent">
                            <span class="w-8 h-8 flex items-center justify-center rounded-full text-sm"
                                  :class="{
                                      'bg-amber-500 text-white font-bold': cell.isToday,
                                      'text-gray-400 dark:text-gray-500': !cell.isToday
                                  }"
                                  x-text="cell.day">
                            </span>
                        </template>
                        {{-- Event dot indicator --}}
                        <template x-if="cell.hasEvent && !cell.isToday">
                            <span class="block w-1.5 h-1.5 rounded-full bg-amber-500 dark:bg-amber-400 mx-auto mt-0.5"></span>
                        </template>
                        <template x-if="cell.hasEvent && cell.isToday">
                            <span class="block w-1.5 h-1.5 rounded-full bg-white mx-auto mt-0.5"></span>
                        </template>
                        <template x-if="!cell.hasEvent">
                            <span class="block w-1.5 h-1.5 mx-auto mt-0.5"></span>
                        </template>
                    </div>
                </template>
                <template x-if="cell.day === null">
                    <span class="w-8 h-8"></span>
                </template>
            </div>
        </template>
    </div>
</div>

@push('scripts')
<script>
function miniCalendar() {
    const monthNames = ['Січень','Лютий','Березень','Квітень','Травень','Червень','Липень','Серпень','Вересень','Жовтень','Листопад','Грудень'];
    const today = new Date();
    const initialEvents = @json($calendarEvents ?? []);

    return {
        month: today.getMonth(),
        year: today.getFullYear(),
        eventDates: initialEvents,
        loading: false,

        get monthName() {
            return monthNames[this.month];
        },

        get calendarCells() {
            const cells = [];
            const firstDay = new Date(this.year, this.month, 1);
            // getDay() returns 0=Sun, we need Monday=0, so shift
            let startDow = firstDay.getDay() - 1;
            if (startDow < 0) startDow = 6;

            const daysInMonth = new Date(this.year, this.month + 1, 0).getDate();

            // Empty cells before first day
            for (let i = 0; i < startDow; i++) {
                cells.push({ day: null, date: null, hasEvent: false, isToday: false });
            }

            for (let d = 1; d <= daysInMonth; d++) {
                const dateStr = this.year + '-' + String(this.month + 1).padStart(2, '0') + '-' + String(d).padStart(2, '0');
                const isToday = (d === today.getDate() && this.month === today.getMonth() && this.year === today.getFullYear());
                const hasEvent = this.eventDates.includes(dateStr);
                cells.push({ day: d, date: dateStr, hasEvent, isToday });
            }

            return cells;
        },

        prevMonth() {
            if (this.month === 0) {
                this.month = 11;
                this.year--;
            } else {
                this.month--;
            }
            this.loadEvents();
        },

        nextMonth() {
            if (this.month === 11) {
                this.month = 0;
                this.year++;
            } else {
                this.month++;
            }
            this.loadEvents();
        },

        goToday() {
            this.month = today.getMonth();
            this.year = today.getFullYear();
            this.eventDates = initialEvents;
        },

        async loadEvents() {
            this.loading = true;
            try {
                const res = await fetch(`{{ route('dashboard.calendar-events') }}?month=${this.month + 1}&year=${this.year}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                this.eventDates = data.dates || [];
            } catch (e) {
                console.error('Failed to load calendar events:', e);
                this.eventDates = [];
            } finally {
                this.loading = false;
            }
        }
    };
}
</script>
@endpush
