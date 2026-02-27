@extends('layouts.app')

@section('title', __('Матриця розкладу'))

@section('actions')
@if(auth()->user()->can('create', \App\Models\Event::class))
<a href="{{ route('events.create') }}"
   class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-xl transition-colors">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    {{ __('Нова подія') }}
</a>
@endif
@endsection

@section('content')
<div x-data="matrixView()" x-init="loadData()" class="space-y-4">
    {{-- View Toggle & Filters --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            {{-- View Toggle --}}
            <div class="flex items-center gap-2">
                <a href="{{ route('events.index') }}"
                   class="px-4 py-2 text-sm font-medium rounded-xl text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700">
                    {{ __('Список') }}
                </a>
                <a href="{{ route('schedule') }}"
                   class="px-4 py-2 text-sm font-medium rounded-xl text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700">
                    {{ __('Календар') }}
                </a>
                <span class="px-4 py-2 text-sm font-medium rounded-xl bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300">
                    {{ __('Матриця') }}
                </span>
            </div>

            {{-- Filters --}}
            <div class="flex flex-wrap items-center gap-2">
                {{-- Service Type --}}
                <select x-model="serviceType" @change="loadData()"
                        class="rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm py-2">
                    @foreach($serviceTypes as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>

                {{-- Weeks --}}
                <select x-model="weeks" @change="loadData()"
                        class="rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm py-2">
                    <option value="4">4 {{ __('тижні') }}</option>
                    <option value="8">8 {{ __('тижнів') }}</option>
                    <option value="12">12 {{ __('тижнів') }}</option>
                </select>

                {{-- Navigation --}}
                <div class="flex items-center gap-1">
                    <button @click="prevPeriod()" type="button"
                        class="w-9 h-9 flex items-center justify-center hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <button @click="goToday()" type="button"
                        class="px-3 py-1.5 text-xs font-medium rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-gray-700 dark:text-gray-300">
                        {{ __('Сьогодні') }}
                    </button>
                    <button @click="nextPeriod()" type="button"
                        class="w-9 h-9 flex items-center justify-center hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>

                <span class="text-sm font-medium text-gray-700 dark:text-gray-300" x-text="periodLabel"></span>
            </div>
        </div>
    </div>

    {{-- Loading --}}
    <div x-show="loading" class="flex items-center justify-center py-12">
        <svg class="animate-spin h-8 w-8 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
    </div>

    {{-- Empty State --}}
    <template x-if="!loading && events.length === 0">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <p class="text-gray-500 dark:text-gray-400 text-lg">{{ __('Немає подій за цей період') }}</p>
            <p class="text-gray-400 dark:text-gray-500 text-sm mt-1">{{ __('Спробуйте обрати інший тип служіння або період') }}</p>
        </div>
    </template>

    {{-- Empty Ministries --}}
    <template x-if="!loading && events.length > 0 && ministries.length === 0">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
            <p class="text-gray-500 dark:text-gray-400 text-lg">{{ __('Немає команд для відображення') }}</p>
            <p class="text-gray-400 dark:text-gray-500 text-sm mt-1">{{ __('Позначте команди як "Частина недільного служіння" в налаштуваннях') }}</p>
        </div>
    </template>

    {{-- Matrix Grid --}}
    <template x-if="!loading && events.length > 0 && ministries.length > 0">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse min-w-[600px]">
                    {{-- Header: event dates --}}
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/50">
                            <th class="sticky left-0 z-20 bg-gray-50 dark:bg-gray-700/50 px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider border-b border-r border-gray-200 dark:border-gray-600 min-w-[180px]">
                                {{ __('Команда / Роль') }}
                            </th>
                            <template x-for="event in events" :key="event.id">
                                <th class="px-3 py-3 text-center border-b border-gray-200 dark:border-gray-600 min-w-[130px]">
                                    <a :href="'/events/' + event.id" class="hover:text-primary-600 dark:hover:text-primary-400">
                                        <div class="text-xs font-medium text-gray-500 dark:text-gray-400" x-text="event.dayOfWeek"></div>
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white" x-text="event.dateLabel"></div>
                                        <div class="text-xs text-gray-400 dark:text-gray-500" x-text="event.time || ''"></div>
                                    </a>
                                </th>
                            </template>
                        </tr>
                    </thead>

                    {{-- Body: ministry groups with roles --}}
                    <tbody>
                        <template x-for="ministry in ministries" :key="ministry.id">
                            <template x-for="(role, roleIdx) in ministry.roles" :key="role.type + '_' + role.id">
                                <tr :class="roleIdx === 0 ? 'border-t-2 border-gray-300 dark:border-gray-500' : ''"
                                    class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50/50 dark:hover:bg-gray-700/25">
                                    {{-- Row label: ministry name (first role) + role name --}}
                                    <td class="sticky left-0 z-10 bg-white dark:bg-gray-800 px-4 py-2 border-r border-gray-200 dark:border-gray-600"
                                        :class="roleIdx === 0 ? 'pt-3' : ''">
                                        <template x-if="roleIdx === 0">
                                            <div class="text-xs font-bold uppercase tracking-wide mb-1"
                                                 :style="'color:' + (ministry.color || '#6B7280')">
                                                <span x-text="ministry.icon || ''"></span>
                                                <span x-text="ministry.name"></span>
                                            </div>
                                        </template>
                                        <div class="flex items-center gap-1.5">
                                            <template x-if="role.icon">
                                                <span class="text-xs" x-text="role.icon"></span>
                                            </template>
                                            <span class="text-sm text-gray-700 dark:text-gray-300" x-text="role.name"></span>
                                        </div>
                                    </td>

                                    {{-- Cells: assignments per event --}}
                                    <template x-for="event in events" :key="event.id">
                                        <td class="px-2 py-1.5 text-center border-l border-gray-100 dark:border-gray-700/50 relative group"
                                            @click="openCellDropdown(ministry, role, event, $event)">
                                            <div class="min-h-[36px] flex flex-col items-center justify-center gap-0.5 cursor-pointer rounded-lg px-1 py-1 transition-colors"
                                                 :class="getCellBg(ministry.id, role, event.id)">
                                                <template x-for="person in getCellPersons(ministry.id, role, event.id)" :key="person.id">
                                                    <div class="flex items-center gap-1 text-xs leading-tight w-full justify-center">
                                                        <span class="w-1.5 h-1.5 rounded-full flex-shrink-0"
                                                              :class="statusDotClass(person.status)"></span>
                                                        <span class="truncate max-w-[100px]" x-text="person.person_name"
                                                              :class="statusTextClass(person.status)"></span>
                                                    </div>
                                                </template>
                                                {{-- Empty cell indicator --}}
                                                <template x-if="getCellPersons(ministry.id, role, event.id).length === 0">
                                                    <span class="text-gray-300 dark:text-gray-600 opacity-0 group-hover:opacity-100 transition-opacity">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                        </svg>
                                                    </span>
                                                </template>
                                            </div>
                                        </td>
                                    </template>
                                </tr>
                            </template>
                        </template>
                    </tbody>
                </table>
            </div>

            {{-- Legend --}}
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/25">
                <div class="flex flex-wrap items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-green-500"></span> {{ __('Підтверджено') }}
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-yellow-500"></span> {{ __('Очікує') }}
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-red-500"></span> {{ __('Відхилено') }}
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span> {{ __('Був присутній') }}
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-gray-400"></span> {{ __('Не підтверджено') }}
                    </span>
                </div>
            </div>
        </div>
    </template>

    {{-- Assign/Action Dropdown --}}
    <div x-show="dropdown.open" x-transition.opacity
         @click.outside="dropdown.open = false"
         @keydown.escape.window="dropdown.open = false"
         class="fixed z-50 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 w-72 max-h-80 overflow-hidden"
         :style="'top:' + dropdown.y + 'px;left:' + dropdown.x + 'px'">

        {{-- Current assignments --}}
        <template x-if="dropdown.persons.length > 0">
            <div class="border-b border-gray-200 dark:border-gray-700">
                <div class="px-3 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                    {{ __('Призначені') }}
                </div>
                <template x-for="person in dropdown.persons" :key="person.id">
                    <div class="flex items-center justify-between px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full flex-shrink-0" :class="statusDotClass(person.status)"></span>
                            <span class="text-sm text-gray-900 dark:text-white" x-text="person.person_name"></span>
                        </div>
                        <div class="flex items-center gap-1">
                            <template x-if="person.has_telegram">
                                <button @click.stop="notifyPerson(person)"
                                    class="p-1 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                                    :title="'{{ __('Надіслати в Telegram') }}'">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"/>
                                    </svg>
                                </button>
                            </template>
                            <button @click.stop="removePerson(person)"
                                class="p-1 text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors"
                                :title="'{{ __('Видалити') }}'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </template>

        {{-- Add member --}}
        <div class="p-2">
            <input type="text" x-model="dropdown.search" x-ref="dropdownSearch"
                   placeholder="{{ __('Пошук учасника...') }}"
                   class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 focus:ring-primary-500 focus:border-primary-500"
                   @keydown.escape="dropdown.open = false">
        </div>
        <div class="overflow-y-auto max-h-40">
            <template x-for="member in filteredMembers()" :key="member.id">
                <button @click="assignPerson(member)"
                    class="w-full text-left px-3 py-2 text-sm hover:bg-primary-50 dark:hover:bg-primary-900/30 text-gray-700 dark:text-gray-300 transition-colors flex items-center gap-2">
                    <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span x-text="member.name"></span>
                </button>
            </template>
            <template x-if="filteredMembers().length === 0">
                <div class="px-3 py-3 text-sm text-gray-400 dark:text-gray-500 text-center">
                    {{ __('Немає доступних учасників') }}
                </div>
            </template>
        </div>
    </div>
</div>

<script>
function matrixView() {
    return {
        loading: false,
        serviceType: 'sunday_service',
        weeks: 4,
        startDate: null,
        events: [],
        ministries: [],
        grid: {},
        members: {},
        periodLabel: '',

        dropdown: {
            open: false,
            x: 0,
            y: 0,
            ministry: null,
            role: null,
            event: null,
            persons: [],
            search: '',
        },

        init() {
            // Start from current week Monday
            const now = new Date();
            const day = now.getDay();
            const diff = now.getDate() - day + (day === 0 ? -6 : 1);
            this.startDate = new Date(now.setDate(diff));
            this.startDate.setHours(0, 0, 0, 0);
        },

        formatDate(d) {
            return d.getFullYear() + '-' +
                String(d.getMonth() + 1).padStart(2, '0') + '-' +
                String(d.getDate()).padStart(2, '0');
        },

        updatePeriodLabel() {
            const months = ['січ', 'лют', 'бер', 'кві', 'тра', 'чер', 'лип', 'сер', 'вер', 'жов', 'лис', 'гру'];
            const end = new Date(this.startDate);
            end.setDate(end.getDate() + this.weeks * 7 - 1);

            const startLabel = this.startDate.getDate() + ' ' + months[this.startDate.getMonth()];
            const endLabel = end.getDate() + ' ' + months[end.getMonth()];
            const yearPart = this.startDate.getFullYear() !== end.getFullYear()
                ? this.startDate.getFullYear() + ' — ' + endLabel + ' ' + end.getFullYear()
                : startLabel + ' — ' + endLabel + ' ' + this.startDate.getFullYear();

            this.periodLabel = this.startDate.getFullYear() === end.getFullYear()
                ? startLabel + ' — ' + endLabel + ', ' + this.startDate.getFullYear()
                : startLabel + ' ' + this.startDate.getFullYear() + ' — ' + endLabel + ' ' + end.getFullYear();
        },

        prevPeriod() {
            this.startDate.setDate(this.startDate.getDate() - this.weeks * 7);
            this.startDate = new Date(this.startDate);
            this.loadData();
        },

        nextPeriod() {
            this.startDate.setDate(this.startDate.getDate() + this.weeks * 7);
            this.startDate = new Date(this.startDate);
            this.loadData();
        },

        goToday() {
            const now = new Date();
            const day = now.getDay();
            const diff = now.getDate() - day + (day === 0 ? -6 : 1);
            this.startDate = new Date(now.setDate(diff));
            this.startDate.setHours(0, 0, 0, 0);
            this.loadData();
        },

        async loadData() {
            this.loading = true;
            this.dropdown.open = false;
            this.updatePeriodLabel();

            try {
                const params = new URLSearchParams({
                    service_type: this.serviceType,
                    weeks: this.weeks,
                    start_date: this.formatDate(this.startDate),
                });

                const resp = await fetch(`{{ route('schedule.matrix-data') }}?${params}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (!resp.ok) throw new Error('Failed to load');

                const data = await resp.json();
                this.events = data.events;
                this.ministries = data.ministriesData;
                this.grid = data.grid;
                this.members = data.members;
            } catch (e) {
                console.error('Matrix load error:', e);
            } finally {
                this.loading = false;
            }
        },

        getRoleKey(role) {
            return role.type + '_' + role.id;
        },

        getCellPersons(ministryId, role, eventId) {
            const mKey = String(ministryId);
            const rKey = this.getRoleKey(role);
            const eKey = String(eventId);
            return this.grid?.[mKey]?.[rKey]?.[eKey] || [];
        },

        getCellBg(ministryId, role, eventId) {
            const persons = this.getCellPersons(ministryId, role, eventId);
            if (persons.length === 0) return 'hover:bg-gray-100 dark:hover:bg-gray-700/50';
            const hasDeclined = persons.some(p => p.status === 'declined');
            if (hasDeclined) return 'bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/30';
            const allConfirmed = persons.every(p => p.status === 'confirmed' || p.status === 'attended');
            if (allConfirmed) return 'bg-green-50 dark:bg-green-900/20 hover:bg-green-100 dark:hover:bg-green-900/30';
            return 'bg-yellow-50 dark:bg-yellow-900/20 hover:bg-yellow-100 dark:hover:bg-yellow-900/30';
        },

        statusDotClass(status) {
            switch(status) {
                case 'confirmed': return 'bg-green-500';
                case 'pending': return 'bg-yellow-500';
                case 'declined': return 'bg-red-500';
                case 'attended': return 'bg-blue-500';
                default: return 'bg-gray-400';
            }
        },

        statusTextClass(status) {
            switch(status) {
                case 'confirmed': return 'text-green-700 dark:text-green-400';
                case 'pending': return 'text-yellow-700 dark:text-yellow-400';
                case 'declined': return 'text-red-500 dark:text-red-400 line-through';
                case 'attended': return 'text-blue-700 dark:text-blue-400';
                default: return 'text-gray-700 dark:text-gray-300';
            }
        },

        openCellDropdown(ministry, role, event, $event) {
            const rect = $event.currentTarget.getBoundingClientRect();
            const dropdownWidth = 288;
            const dropdownHeight = 320;

            let x = rect.left;
            let y = rect.bottom + 4;

            // Keep within viewport
            if (x + dropdownWidth > window.innerWidth) {
                x = window.innerWidth - dropdownWidth - 8;
            }
            if (y + dropdownHeight > window.innerHeight) {
                y = rect.top - dropdownHeight - 4;
            }
            if (x < 0) x = 8;
            if (y < 0) y = 8;

            this.dropdown.x = x;
            this.dropdown.y = y;
            this.dropdown.ministry = ministry;
            this.dropdown.role = role;
            this.dropdown.event = event;
            this.dropdown.persons = this.getCellPersons(ministry.id, role, event.id);
            this.dropdown.search = '';
            this.dropdown.open = true;

            this.$nextTick(() => {
                this.$refs.dropdownSearch?.focus();
            });
        },

        filteredMembers() {
            if (!this.dropdown.ministry) return [];
            const mKey = String(this.dropdown.ministry.id);
            const allMembers = this.members[mKey] || [];
            const assignedIds = this.dropdown.persons.map(p => p.person_id);
            const search = this.dropdown.search.toLowerCase();

            return allMembers.filter(m => {
                if (assignedIds.includes(m.id)) return false;
                if (search && !m.name.toLowerCase().includes(search)) return false;
                return true;
            });
        },

        async assignPerson(member) {
            const { ministry, role, event } = this.dropdown;
            if (!ministry || !role || !event) return;

            const mKey = String(ministry.id);
            const rKey = this.getRoleKey(role);
            const eKey = String(event.id);

            try {
                let url, body;

                if (role.type === 'ministry_role') {
                    url = `/events/${event.id}/ministry-team`;
                    body = {
                        ministry_id: ministry.id,
                        person_id: member.id,
                        ministry_role_id: role.id,
                    };
                } else {
                    url = `/rotation/event/${event.id}/assign-position`;
                    body = {
                        position_id: role.id,
                        person_id: member.id,
                    };
                }

                const resp = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(body),
                });

                if (resp.ok) {
                    const data = await resp.json();
                    // Update grid locally
                    if (!this.grid[mKey]) this.grid[mKey] = {};
                    if (!this.grid[mKey][rKey]) this.grid[mKey][rKey] = {};
                    if (!this.grid[mKey][rKey][eKey]) this.grid[mKey][rKey][eKey] = [];

                    this.grid[mKey][rKey][eKey].push({
                        id: data.id,
                        person_id: member.id,
                        person_name: member.short_name || member.name,
                        status: null,
                        has_telegram: member.has_telegram,
                        source: role.type === 'ministry_role' ? 'ministry_team' : 'assignment',
                    });

                    // Update dropdown persons reference
                    this.dropdown.persons = this.grid[mKey][rKey][eKey];
                } else {
                    const err = await resp.json().catch(() => ({}));
                    alert(err.error || err.message || 'Помилка при призначенні');
                }
            } catch (e) {
                console.error('Assign error:', e);
                alert('Помилка при призначенні');
            }
        },

        async removePerson(person) {
            const { ministry, role, event } = this.dropdown;
            if (!ministry || !role || !event) return;

            const mKey = String(ministry.id);
            const rKey = this.getRoleKey(role);
            const eKey = String(event.id);

            try {
                let url;
                if (person.source === 'assignment') {
                    url = `/rotation/assignment/${person.id}`;
                } else {
                    url = `/events/${event.id}/ministry-team/${person.id}`;
                }

                const resp = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (resp.ok) {
                    // Remove from grid
                    if (this.grid[mKey]?.[rKey]?.[eKey]) {
                        this.grid[mKey][rKey][eKey] = this.grid[mKey][rKey][eKey].filter(p => p.id !== person.id);
                    }
                    this.dropdown.persons = this.grid[mKey]?.[rKey]?.[eKey] || [];
                }
            } catch (e) {
                console.error('Remove error:', e);
            }
        },

        async notifyPerson(person) {
            const { event } = this.dropdown;
            if (!event) return;

            try {
                const url = `/events/${event.id}/ministry-team/${person.id}/notify`;
                const resp = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const data = await resp.json();
                if (data.success) {
                    // Update status in grid
                    const mKey = String(this.dropdown.ministry.id);
                    const rKey = this.getRoleKey(this.dropdown.role);
                    const eKey = String(event.id);
                    if (this.grid[mKey]?.[rKey]?.[eKey]) {
                        const p = this.grid[mKey][rKey][eKey].find(p => p.id === person.id);
                        if (p) p.status = 'pending';
                    }
                    this.dropdown.persons = [...(this.grid[mKey]?.[rKey]?.[eKey] || [])];
                } else {
                    alert(data.message || 'Не вдалося надіслати');
                }
            } catch (e) {
                console.error('Notify error:', e);
            }
        },
    };
}
</script>
@endsection
