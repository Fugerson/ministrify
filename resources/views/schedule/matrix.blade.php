@extends('layouts.app')

@section('title', __('app.schedule_assignments'))

@section('actions')
@if(auth()->user()->can('create', \App\Models\Event::class))
<a href="{{ route('events.create') }}"
   class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-xl transition-colors">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    {{ __('app.schedule_new_event') }}
</a>
@endif
@endsection

@section('content')
<div x-data="matrixView()" x-init="loadData()" class="space-y-4">
    {{-- View Toggle & Filters --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            {{-- View Toggle --}}
            <div class="flex items-center gap-1.5 sm:gap-2">
                <a href="{{ route('schedule') }}"
                   class="px-3 sm:px-4 py-2 text-sm font-medium rounded-xl whitespace-nowrap text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700">
                    {{ __('app.schedule_calendar') }}
                </a>
                <span class="px-3 sm:px-4 py-2 text-sm font-medium rounded-xl whitespace-nowrap bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300">
                    {{ __('app.schedule_assignments') }}
                </span>
            </div>

            {{-- Filters --}}
            <div class="flex flex-wrap items-center gap-2">
                <select x-model="serviceType" @change="loadData()"
                        class="rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm py-2">
                    @foreach($serviceTypes as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>

                <select x-model="weeks" @change="loadData()"
                        class="rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm py-2">
                    <option value="4">4 {{ __('app.schedule_weeks_label') }}</option>
                    <option value="8">8 {{ __('app.schedule_weeks_label') }}</option>
                    <option value="12">12 {{ __('app.schedule_weeks_label') }}</option>
                </select>

                <div class="flex items-center gap-0.5">
                    <button @click="prevPeriod()" type="button"
                        class="w-9 h-9 flex items-center justify-center hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <button @click="nextPeriod()" type="button"
                        class="w-9 h-9 flex items-center justify-center hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>

                <span class="text-sm sm:text-base font-semibold text-gray-700 dark:text-gray-300" x-text="periodLabel"></span>
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
            <p class="text-gray-500 dark:text-gray-400 text-lg">{{ __('app.schedule_no_events_period') }}</p>
            <p class="text-gray-400 dark:text-gray-500 text-sm mt-1">{{ __('app.schedule_try_other') }}</p>
        </div>
    </template>

    {{-- Empty Ministries --}}
    <template x-if="!loading && events.length > 0 && ministries.length === 0">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
            <p class="text-gray-500 dark:text-gray-400 text-lg">{{ __('app.schedule_no_teams_display') }}</p>
            <p class="text-gray-400 dark:text-gray-500 text-sm mt-1">{{ __('app.schedule_mark_teams_hint') }}</p>
        </div>
    </template>

    {{-- Matrix Grid --}}
    <template x-if="!loading && events.length > 0 && ministries.length > 0">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse min-w-[600px]">
                    {{-- Header: event dates --}}
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700">
                            <th class="sticky left-0 z-20 bg-gray-50 dark:bg-gray-700 px-3 sm:px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider border-b border-r border-gray-200 dark:border-gray-600 w-[160px] sm:w-[200px] min-w-[160px] sm:min-w-[200px]">
                                {{ __('app.schedule_team_role') }}
                            </th>
                            <template x-for="event in events" :key="event.id">
                                <th class="px-2 py-3 text-center border-b border-gray-200 dark:border-gray-600 min-w-[140px]"
                                    :class="isNearestEvent(event) ? 'bg-primary-50 dark:bg-primary-900/30' : 'bg-gray-50 dark:bg-gray-700'">
                                    <a :href="'/events/' + event.id"
                                       class="block hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                                        <div class="text-[10px] font-medium uppercase tracking-wide"
                                             :class="isNearestEvent(event) ? 'text-primary-600 dark:text-primary-400' : 'text-gray-400 dark:text-gray-500'"
                                             x-text="event.dayOfWeek"></div>
                                        <div class="text-sm font-bold"
                                             :class="isNearestEvent(event) ? 'text-primary-700 dark:text-primary-300' : 'text-gray-900 dark:text-white'"
                                             x-text="event.dateLabel"></div>
                                        <template x-if="event.time">
                                            <div class="text-[10px]"
                                                 :class="isNearestEvent(event) ? 'text-primary-500 dark:text-primary-400' : 'text-gray-400 dark:text-gray-500'"
                                                 x-text="event.time"></div>
                                        </template>
                                    </a>
                                </th>
                            </template>
                        </tr>
                    </thead>

                    {{-- Body --}}
                    <tbody>
                        <template x-for="(ministry, mIdx) in ministries" :key="ministry.id">
                            <template x-for="(role, roleIdx) in ministry.roles" :key="role.type + '_' + role.id">
                                <tr class="border-b border-gray-100 dark:border-gray-700/50 group/row hover:bg-gray-50/50 dark:hover:bg-gray-700/20 transition-colors"
                                    :class="roleIdx === 0 ? (mIdx > 0 ? 'border-t-2 border-gray-200 dark:border-gray-600' : 'border-t border-gray-200 dark:border-gray-600') : ''">
                                    {{-- Role label (with ministry header on first role) --}}
                                    <td class="sticky left-0 z-10 bg-white dark:bg-gray-800 group-hover/row:bg-gray-50 dark:group-hover/row:bg-gray-700 px-3 sm:px-4 border-r border-gray-200 dark:border-gray-600 transition-colors"
                                        :class="roleIdx === 0 ? 'pt-3 pb-2.5' : 'py-2.5'">
                                        <template x-if="roleIdx === 0">
                                            <div class="flex items-center gap-2 mb-1.5 pb-1 border-b"
                                                 :style="'border-color:' + (ministry.color || '#6B7280') + '40'">
                                                <span class="w-1 h-4 rounded-full flex-shrink-0" :style="'background:' + (ministry.color || '#6B7280')"></span>
                                                <span class="text-[11px] font-bold uppercase tracking-wide"
                                                      :style="'color:' + (ministry.color || '#6B7280')"
                                                      x-text="ministry.name"></span>
                                            </div>
                                        </template>
                                        <div class="flex items-center gap-2">
                                            <span class="w-1.5 h-1.5 rounded-full flex-shrink-0 bg-gray-300 dark:bg-gray-600"></span>
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300" x-text="role.name"></span>
                                        </div>
                                    </td>

                                    {{-- Cells --}}
                                    <template x-for="event in events" :key="event.id">
                                        <td class="px-1.5 py-1.5 text-center border-l border-gray-100 dark:border-gray-700/50 cursor-pointer"
                                            :class="isNearestEvent(event) ? 'bg-primary-50/30 dark:bg-primary-900/10' : ''"
                                            @click="openCellDropdown(ministry, role, event, $event)">
                                            <div class="min-h-[40px] flex flex-col items-center justify-center gap-0.5 rounded-lg px-1 py-1 transition-all duration-150"
                                                 :class="getCellClasses(ministry.id, role, event.id)">
                                                <template x-for="person in getCellPersons(ministry.id, role, event.id)" :key="person.id">
                                                    <div class="flex items-center gap-1 text-xs leading-tight w-full justify-center">
                                                        <span class="w-1.5 h-1.5 rounded-full flex-shrink-0"
                                                              :class="statusDotClass(person.status)"></span>
                                                        <span class="truncate max-w-[110px] font-medium" x-text="person.person_name"
                                                              :class="statusTextClass(person.status)"></span>
                                                    </div>
                                                </template>
                                                {{-- Cell-level notes icon --}}
                                                <template x-if="getCellNotes(ministry.id, role, event.id)">
                                                    <div class="text-[10px] text-amber-500 dark:text-amber-400 truncate max-w-[120px] mt-0.5" :title="getCellNotes(ministry.id, role, event.id)" x-text="getCellNotes(ministry.id, role, event.id)"></div>
                                                </template>
                                                {{-- Empty cell --}}
                                                <template x-if="getCellPersons(ministry.id, role, event.id).length === 0">
                                                    <div class="flex items-center justify-center w-full h-full">
                                                        <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 group-hover/row:text-primary-400 dark:group-hover/row:text-primary-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                        </svg>
                                                    </div>
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
            <div class="px-4 py-2.5 border-t border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/25">
                <div class="flex flex-wrap items-center gap-x-5 gap-y-1 text-xs text-gray-500 dark:text-gray-400">
                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-green-500"></span> {{ __('app.schedule_confirmed_legend') }}
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span> {{ __('app.schedule_pending_legend') }}
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-red-500"></span> {{ __('app.schedule_declined_legend') }}
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-gray-400"></span> {{ __('app.schedule_unconfirmed_legend') }}
                    </span>
                </div>
            </div>
        </div>
    </template>

    {{-- Toast notification --}}
    <div x-show="toast.show" x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-2"
         class="fixed bottom-6 right-6 z-50 px-4 py-2.5 rounded-xl shadow-lg text-sm font-medium"
         :class="toast.type === 'success' ? 'bg-green-600 text-white' : 'bg-red-600 text-white'">
        <span x-text="toast.message"></span>
    </div>

    {{-- Assign/Action Dropdown --}}
    <div x-show="dropdown.open" x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
         @click.outside="dropdown.open = false"
         @keydown.escape.window="dropdown.open = false"
         class="fixed z-50 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 w-72 overflow-hidden"
         :style="'top:' + dropdown.y + 'px;left:' + dropdown.x + 'px'">

        {{-- Dropdown header with context --}}
        <div class="px-3 py-2 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div class="min-w-0">
                    <div class="text-xs font-semibold text-gray-900 dark:text-white truncate"
                         x-text="dropdown.role?.name"></div>
                    <div class="text-[10px] text-gray-500 dark:text-gray-400"
                         x-text="dropdown.event?.dayOfWeek + ' ' + dropdown.event?.dateLabel + (dropdown.event?.time ? ', ' + dropdown.event?.time : '')"></div>
                </div>
                <button @click="dropdown.open = false"
                    class="p-1 -mr-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Current assignments --}}
        <template x-if="dropdown.persons.length > 0">
            <div class="border-b border-gray-200 dark:border-gray-700">
                <template x-for="person in dropdown.persons" :key="person.id">
                    <div class="flex items-center justify-between px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="w-2 h-2 rounded-full flex-shrink-0" :class="statusDotClass(person.status)"></span>
                            <span class="text-sm text-gray-900 dark:text-white truncate" x-text="person.person_name"></span>
                            <span class="text-[10px] px-1.5 py-0.5 rounded-full flex-shrink-0"
                                  :class="statusBadgeClass(person.status)"
                                  x-text="statusLabel(person.status)"></span>
                        </div>
                        <div class="flex items-center gap-0.5 flex-shrink-0 ml-2">
                            <template x-if="person.has_telegram && person.source !== 'assignment'">
                                <button @click.stop="notifyPerson(person)"
                                    class="p-1.5 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20"
                                    :title="@js( __("app.schedule_send_telegram") )">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"/>
                                    </svg>
                                </button>
                            </template>
                            <button @click.stop="removePerson(person)"
                                class="p-1.5 text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20"
                                :title="@js( __("app.schedule_delete") )">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </template>

        {{-- Cell-level notes --}}
        <div class="px-3 py-2 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-1.5 mb-1">
                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                </svg>
                <span class="text-[11px] text-gray-500 dark:text-gray-400 font-medium">{{ __('app.schedule_position_note') }}</span>
            </div>
            <input type="text" :value="dropdown.cellNotes || ''"
                   @input.debounce.600ms="saveCellNotes($event.target.value)"
                   placeholder="{{ __('app.schedule_note_placeholder') }}"
                   class="w-full px-2 py-1.5 text-xs rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 dark:text-gray-200 focus:ring-primary-500 focus:border-primary-500 placeholder-gray-400 dark:placeholder-gray-500">
        </div>

        {{-- Add member search --}}
        <div class="p-2">
            <input type="text" x-model="dropdown.search" x-ref="dropdownSearch"
                   placeholder="{{ __('app.schedule_search_member') }}"
                   class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 dark:text-gray-200 focus:ring-primary-500 focus:border-primary-500 placeholder-gray-400 dark:placeholder-gray-500"
                   @keydown.escape="dropdown.open = false">
        </div>
        <div class="overflow-y-auto max-h-44 pb-1">
            <template x-for="member in filteredMembers()" :key="member.id">
                <button @click="assignPerson(member)"
                    class="w-full text-left px-3 py-2 text-sm hover:bg-primary-50 dark:hover:bg-primary-900/30 text-gray-700 dark:text-gray-300 transition-colors flex items-center gap-2">
                    <svg class="w-3.5 h-3.5 text-primary-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span class="truncate" x-text="member.name"></span>
                </button>
            </template>
            <template x-if="filteredMembers().length === 0 && dropdown.search">
                <div class="px-3 py-3 text-sm text-gray-400 dark:text-gray-500 text-center">
                    {{ __('app.schedule_no_one_found') }}
                </div>
            </template>
            <template x-if="filteredMembers().length === 0 && !dropdown.search && dropdown.persons.length > 0">
                <div class="px-3 py-3 text-sm text-gray-400 dark:text-gray-500 text-center">
                    {{ __('app.schedule_all_assigned_legend') }}
                </div>
            </template>
        </div>
    </div>
</div>

<script>
function matrixView() {
    const savedFilters = filterStorage.load('schedule_matrix', {
        serviceType: 'sunday_service',
        weeks: '4',
    });

    return {
        loading: false,
        serviceType: savedFilters.serviceType,
        weeks: savedFilters.weeks,
        startDate: null,
        events: [],
        ministries: [],
        grid: {},
        members: {},
        periodLabel: '',
        nearestEventId: null,

        dropdown: {
            open: false,
            x: 0,
            y: 0,
            ministry: null,
            role: null,
            event: null,
            persons: [],
            search: '',
            cellNotes: '',
        },

        toast: {
            show: false,
            message: '',
            type: 'success',
            timer: null,
        },

        busy: false,

        init() {
            const now = new Date();
            const day = now.getDay();
            const diff = now.getDate() - day + (day === 0 ? -6 : 1);
            this.startDate = new Date(now.setDate(diff));
            this.startDate.setHours(0, 0, 0, 0);

            this.$watch('serviceType', () => this._saveFilters());
            this.$watch('weeks', () => this._saveFilters());
        },

        _saveFilters() {
            filterStorage.save('schedule_matrix', {
                serviceType: this.serviceType,
                weeks: this.weeks,
            });
        },

        formatDate(d) {
            return d.getFullYear() + '-' +
                String(d.getMonth() + 1).padStart(2, '0') + '-' +
                String(d.getDate()).padStart(2, '0');
        },

        updatePeriodLabel() {
            const months = @js( __("app.schedule_months_short") ).split(',');
            const end = new Date(this.startDate);
            end.setDate(end.getDate() + this.weeks * 7 - 1);

            const startLabel = this.startDate.getDate() + ' ' + months[this.startDate.getMonth()];
            const endLabel = end.getDate() + ' ' + months[end.getMonth()];

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

        findNearestEvent() {
            const today = this.formatDate(new Date());
            // Find first event >= today, or last event if all past
            let nearest = null;
            for (const event of this.events) {
                if (event.date >= today) {
                    nearest = event.id;
                    break;
                }
            }
            this.nearestEventId = nearest || (this.events.length > 0 ? this.events[this.events.length - 1].id : null);
        },

        isNearestEvent(event) {
            return event.id === this.nearestEventId;
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
                this.findNearestEvent();
            } catch (e) {
                console.error('Matrix load error:', e);
                this.showToast(@js( __("app.schedule_load_error") ), 'error');
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

        getCellNotes(ministryId, role, eventId) {
            const persons = this.getCellPersons(ministryId, role, eventId);
            for (const p of persons) {
                if (p.notes) return p.notes;
            }
            return null;
        },

        getCellClasses(ministryId, role, eventId) {
            const persons = this.getCellPersons(ministryId, role, eventId);
            if (persons.length === 0) {
                return 'border border-dashed border-gray-200 dark:border-gray-700 hover:border-primary-300 dark:hover:border-primary-700 hover:bg-primary-50/50 dark:hover:bg-primary-900/20';
            }
            const hasDeclined = persons.some(p => p.status === 'declined');
            if (hasDeclined) return 'bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/30 ring-1 ring-red-200 dark:ring-red-800';
            const allConfirmed = persons.every(p => p.status === 'confirmed' || p.status === 'attended');
            if (allConfirmed) return 'bg-green-50 dark:bg-green-900/20 hover:bg-green-100 dark:hover:bg-green-900/30';
            return 'bg-amber-50 dark:bg-amber-900/20 hover:bg-amber-100 dark:hover:bg-amber-900/30';
        },

        statusDotClass(status) {
            switch(status) {
                case 'confirmed': return 'bg-green-500';
                case 'pending': return 'bg-amber-500';
                case 'declined': return 'bg-red-500';
                case 'attended': return 'bg-blue-500';
                default: return 'bg-gray-400';
            }
        },

        statusTextClass(status) {
            switch(status) {
                case 'confirmed': return 'text-green-700 dark:text-green-400';
                case 'pending': return 'text-amber-700 dark:text-amber-300';
                case 'declined': return 'text-red-500 dark:text-red-400 line-through';
                case 'attended': return 'text-blue-700 dark:text-blue-400';
                default: return 'text-gray-700 dark:text-gray-300';
            }
        },

        statusBadgeClass(status) {
            switch(status) {
                case 'confirmed': return 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400';
                case 'pending': return 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400';
                case 'declined': return 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400';
                case 'attended': return 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400';
                default: return 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400';
            }
        },

        statusLabel(status) {
            const labels = {
                confirmed: @js( __("app.schedule_yes_label") ),
                pending: @js( __("app.schedule_pending_label") ),
                declined: @js( __("app.schedule_no_label") ),
                attended: @js( __("app.schedule_was_label") ),
            };
            return labels[status] || '—';
        },

        openCellDropdown(ministry, role, event, $event) {
            const rect = $event.currentTarget.getBoundingClientRect();
            const dropdownWidth = 288;
            const dropdownHeight = 360;

            let x = rect.left + (rect.width / 2) - (dropdownWidth / 2);
            let y = rect.bottom + 6;

            if (x + dropdownWidth > window.innerWidth - 8) x = window.innerWidth - dropdownWidth - 8;
            if (y + dropdownHeight > window.innerHeight) y = rect.top - dropdownHeight - 6;
            if (x < 8) x = 8;
            if (y < 8) y = 8;

            this.dropdown.x = x;
            this.dropdown.y = y;
            this.dropdown.ministry = ministry;
            this.dropdown.role = role;
            this.dropdown.event = event;
            this.dropdown.persons = this.getCellPersons(ministry.id, role, event.id);
            this.dropdown.search = '';
            this.dropdown.cellNotes = this.getCellNotes(ministry.id, role, event.id) || '';
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

        showToast(message, type = 'success') {
            if (this.toast.timer) clearTimeout(this.toast.timer);
            this.toast.message = message;
            this.toast.type = type;
            this.toast.show = true;
            this.toast.timer = setTimeout(() => { this.toast.show = false; }, 2000);
        },

        async assignPerson(member) {
            const { ministry, role, event } = this.dropdown;
            if (!ministry || !role || !event || this.busy) return;
            this.busy = true;

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
                    if (!this.grid[mKey]) this.grid[mKey] = {};
                    if (!this.grid[mKey][rKey]) this.grid[mKey][rKey] = {};
                    if (!this.grid[mKey][rKey][eKey]) this.grid[mKey][rKey][eKey] = [];

                    this.grid[mKey][rKey][eKey].push({
                        id: data.id,
                        person_id: member.id,
                        person_name: member.short_name || member.name,
                        status: data.status || 'pending',
                        has_telegram: member.has_telegram,
                        source: role.type === 'ministry_role' ? 'ministry_team' : 'assignment',
                        notes: null,
                    });

                    this.dropdown.persons = this.grid[mKey][rKey][eKey];
                    this.showToast((member.short_name || member.name) + ' — ' + @js( __("app.schedule_assigned_toast") ));

                    // Auto-close if no more available members
                    if (this.filteredMembers().length === 0) {
                        setTimeout(() => { this.dropdown.open = false; }, 600);
                    }
                } else {
                    const err = await resp.json().catch(() => ({}));
                    this.showToast(err.error || err.message || @js( __("app.schedule_assign_error") ), 'error');
                }
            } catch (e) {
                console.error('Assign error:', e);
                this.showToast(@js( __("app.schedule_assign_error") ), 'error');
            } finally {
                this.busy = false;
            }
        },

        async removePerson(person) {
            const { ministry, role, event } = this.dropdown;
            if (!ministry || !role || !event || this.busy) return;
            this.busy = true;

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
                    if (this.grid[mKey]?.[rKey]?.[eKey]) {
                        this.grid[mKey][rKey][eKey] = this.grid[mKey][rKey][eKey].filter(p => p.id !== person.id);
                    }
                    this.dropdown.persons = this.grid[mKey]?.[rKey]?.[eKey] || [];
                    this.showToast(person.person_name + ' — ' + @js( __("app.schedule_removed_toast") ));

                    // Auto-close if empty
                    if (this.dropdown.persons.length === 0) {
                        setTimeout(() => { this.dropdown.open = false; }, 400);
                    }
                }
            } catch (e) {
                console.error('Remove error:', e);
                this.showToast(@js( __("app.schedule_error") ), 'error');
            } finally {
                this.busy = false;
            }
        },

        async saveCellNotes(value) {
            const { ministry, role, event, persons } = this.dropdown;
            if (!ministry || !role || !event || persons.length === 0) return;

            const notes = value.trim() || null;
            this.dropdown.cellNotes = notes || '';

            const mKey = String(ministry.id);
            const rKey = this.getRoleKey(role);
            const eKey = String(event.id);

            try {
                // Save to all entries in this cell
                const promises = persons.map(person => {
                    const url = person.source === 'assignment'
                        ? `/rotation/assignment/${person.id}/notes`
                        : `/events/${event.id}/ministry-team/${person.id}/notes`;

                    return fetch(url, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ notes }),
                    });
                });

                await Promise.all(promises);

                // Update in grid
                if (this.grid[mKey]?.[rKey]?.[eKey]) {
                    this.grid[mKey][rKey][eKey].forEach(p => p.notes = notes);
                }
                this.showToast(@js( __("app.schedule_note_saved") ));
            } catch (e) {
                console.error('Save cell notes error:', e);
                this.showToast(@js( __("app.schedule_error") ), 'error');
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
                    const mKey = String(this.dropdown.ministry.id);
                    const rKey = this.getRoleKey(this.dropdown.role);
                    const eKey = String(event.id);
                    if (this.grid[mKey]?.[rKey]?.[eKey]) {
                        const p = this.grid[mKey][rKey][eKey].find(p => p.id === person.id);
                        if (p) p.status = 'pending';
                    }
                    this.dropdown.persons = [...(this.grid[mKey]?.[rKey]?.[eKey] || [])];
                    this.showToast(@js( __("app.schedule_notification_sent") ));
                } else {
                    this.showToast(data.message || @js( __("app.schedule_notification_failed") ), 'error');
                }
            } catch (e) {
                console.error('Notify error:', e);
                this.showToast(@js( __("app.schedule_error") ), 'error');
            }
        },
    };
}
</script>
@endsection
