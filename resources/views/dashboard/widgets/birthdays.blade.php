{{-- Birthdays Widget --}}
<div class="bg-gradient-to-r from-pink-50 to-purple-50 dark:from-pink-900/30 dark:to-purple-900/30 rounded-2xl border border-pink-100 dark:border-pink-800 p-4"
     x-data="birthdayWidget()" x-cloak>
    <div class="flex items-center justify-between mb-3">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-pink-100 dark:bg-pink-900 flex items-center justify-center">
                <span class="text-xl">🎂</span>
            </div>
            <div>
                <h3 class="font-semibold text-gray-900 dark:text-white">Дні народження</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    <span x-text="count"></span> <span x-text="countLabel"></span>
                </p>
            </div>
        </div>
        <div class="flex items-center gap-1">
            <button @click="prevMonth()" class="p-1.5 rounded-lg hover:bg-pink-100 dark:hover:bg-pink-900/50 text-gray-500 dark:text-gray-400 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <button @click="currentMonth = {{ now()->month }}; loadBirthdays()"
                    class="px-3 py-1 text-sm font-medium rounded-lg transition-colors min-w-[120px] text-center"
                    :class="currentMonth === {{ now()->month }} ? 'bg-pink-200 dark:bg-pink-800 text-pink-800 dark:text-pink-200' : 'hover:bg-pink-100 dark:hover:bg-pink-900/50 text-gray-700 dark:text-gray-300'"
                    x-text="monthName">
            </button>
            <button @click="nextMonth()" class="p-1.5 rounded-lg hover:bg-pink-100 dark:hover:bg-pink-900/50 text-gray-500 dark:text-gray-400 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
    </div>
    <div x-show="loading" class="flex justify-center py-6">
        <svg class="w-6 h-6 animate-spin text-pink-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
    </div>
    <div x-show="!loading && people.length > 0" class="flex flex-wrap gap-2">
        <template x-for="person in sortedPeople" :key="person.id">
            <a :href="person.url" class="flex items-center gap-2 px-3 py-2 rounded-xl hover:shadow-md transition-shadow"
               :class="person.is_today ? 'bg-yellow-50 dark:bg-yellow-900/30 ring-2 ring-yellow-400 dark:ring-yellow-500' : 'bg-white dark:bg-gray-800'">
                <template x-if="person.photo">
                    <img :src="person.photo" class="w-8 h-8 rounded-full object-cover" :class="person.is_today && 'ring-2 ring-yellow-400'">
                </template>
                <template x-if="!person.photo">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center"
                         :class="person.is_today ? 'bg-yellow-100 dark:bg-yellow-900' : 'bg-primary-100 dark:bg-primary-900'">
                        <span class="text-xs font-medium" :class="person.is_today ? 'text-yellow-700 dark:text-yellow-300' : 'text-primary-600 dark:text-primary-400'" x-text="person.initial"></span>
                    </div>
                </template>
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                        <span x-text="person.name"></span>
                        <template x-if="person.is_today"><span class="ml-1">🎉</span></template>
                    </p>
                    <p class="text-xs" :class="person.is_today ? 'text-yellow-600 dark:text-yellow-400 font-medium' : 'text-gray-500 dark:text-gray-400'" x-text="person.is_today ? 'Сьогодні!' : person.day + ' ' + person.month_short"></p>
                </div>
            </a>
        </template>
    </div>
    <div x-show="!loading && people.length === 0" class="text-center py-6 text-sm text-gray-500 dark:text-gray-400">
        Немає днів народження в цьому місяці
    </div>
</div>

@php
    $todayDay = now()->day;
    $todayMonth = now()->month;
    $birthdayInitialData = $birthdaysThisMonth->sortBy(fn($p) => $p->birth_date->day)->values()->map(fn($p) => [
        'id' => $p->id,
        'name' => $p->full_name,
        'initial' => mb_substr($p->first_name, 0, 1),
        'day' => $p->birth_date->format('d'),
        'month_short' => $p->birth_date->translatedFormat('M'),
        'photo' => $p->photo ? \Illuminate\Support\Facades\Storage::url($p->photo) : null,
        'url' => route('people.show', $p),
        'is_today' => $p->birth_date->day === $todayDay && $p->birth_date->month === $todayMonth,
    ]);
@endphp

@push('scripts')
<script>
function birthdayWidget() {
    const monthNames = ['Січень','Лютий','Березень','Квітень','Травень','Червень','Липень','Серпень','Вересень','Жовтень','Листопад','Грудень'];
    const initialData = @json($birthdayInitialData);

    return {
        currentMonth: {{ now()->month }},
        people: initialData,
        count: initialData.length,
        loading: false,

        get sortedPeople() {
            return [...this.people].sort((a, b) => {
                if (a.is_today && !b.is_today) return -1;
                if (!a.is_today && b.is_today) return 1;
                return 0;
            });
        },

        get monthName() {
            return monthNames[this.currentMonth - 1];
        },

        get countLabel() {
            const n = this.count;
            if (n % 10 === 1 && n % 100 !== 11) return 'особа';
            if ([2,3,4].includes(n % 10) && ![12,13,14].includes(n % 100)) return 'особи';
            return 'осіб';
        },

        prevMonth() {
            this.currentMonth = this.currentMonth === 1 ? 12 : this.currentMonth - 1;
            this.loadBirthdays();
        },

        nextMonth() {
            this.currentMonth = this.currentMonth === 12 ? 1 : this.currentMonth + 1;
            this.loadBirthdays();
        },

        async loadBirthdays() {
            this.loading = true;
            try {
                const res = await fetch(`{{ route('dashboard.birthdays') }}?month=${this.currentMonth}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                this.people = data.people;
                this.count = data.count;
            } catch (e) {
                console.error('Failed to load birthdays:', e);
            } finally {
                this.loading = false;
            }
        }
    };
}
</script>
@endpush
