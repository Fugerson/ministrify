@extends('layouts.app')

@section('title', __('app.service_planning'))

@section('content')
<div class="space-y-4" x-data="servicePlanningMatrix()" x-init="loadData()">

    {{-- Header --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 px-4 py-3">
        <div class="flex items-center justify-between gap-2">
            {{-- Left: title + filter --}}
            <div class="flex items-center gap-2 min-w-0">
                <h1 class="text-lg font-semibold text-gray-900 dark:text-white whitespace-nowrap">{{ __('app.service_planning') }}</h1>

                {{-- Event type filter --}}
                <div class="relative" x-data="{ filterOpen: false }">
                    <button @click="filterOpen = !filterOpen" type="button"
                        class="flex items-center gap-1.5 px-2.5 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                        <span>{{ __('Фільтр') }}</span>
                        <template x-if="hiddenEventTitles.size > 0">
                            <span class="w-4 h-4 flex items-center justify-center text-[9px] font-bold bg-primary-100 dark:bg-primary-900/40 text-primary-600 dark:text-primary-400 rounded-full"
                                  x-text="hiddenEventTitles.size"></span>
                        </template>
                    </button>
                    <div x-show="filterOpen" @click.outside="filterOpen = false"
                         x-transition
                         class="absolute left-0 top-full mt-1 z-30 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 w-72 py-1 max-h-80 overflow-y-auto">
                        <template x-for="title in uniqueEventTitles" :key="title">
                            <label class="flex items-center gap-2.5 px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer transition-colors">
                                <input type="checkbox" :checked="!hiddenEventTitles.has(title)"
                                       @change="toggleEventTitleFilter(title)"
                                       class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500">
                                <span class="text-sm text-gray-700 dark:text-gray-300 truncate" x-text="title"></span>
                                <span class="text-[10px] text-gray-400 dark:text-gray-500 ml-auto flex-shrink-0"
                                      x-text="eventCountByTitle(title)"></span>
                            </label>
                        </template>
                        <template x-if="uniqueEventTitles.length > 1">
                            <div class="border-t border-gray-200 dark:border-gray-700 mt-1 pt-1 px-3 py-1.5 flex gap-2">
                                <button @click="showAllEventTitles()" type="button" class="text-xs text-primary-600 dark:text-primary-400 hover:underline">{{ __('Показати всі') }}</button>
                                <span class="text-gray-300 dark:text-gray-600">|</span>
                                <button @click="hideAllEventTitles()" type="button" class="text-xs text-gray-500 dark:text-gray-400 hover:underline">{{ __('Приховати всі') }}</button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Right: date navigation --}}
            <div class="flex items-center gap-1" x-data="{ pickerOpen: false, pickerYear: new Date().getFullYear() }">
                <button @click="prevPeriod()" type="button"
                   class="w-9 h-9 flex items-center justify-center hover:bg-gray-100 dark:hover:bg-gray-700 active:bg-gray-200 dark:active:bg-gray-600 rounded-lg transition-colors">
                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>

                {{-- Clickable date label → opens month picker --}}
                <div class="relative">
                    <button @click="pickerOpen = !pickerOpen; pickerYear = startDate.getFullYear()" type="button"
                        class="flex items-center gap-1.5 px-2.5 py-1.5 text-sm font-semibold text-gray-900 dark:text-white rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors whitespace-nowrap">
                        <span x-text="periodLabel"></span>
                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    {{-- Month/year picker dropdown --}}
                    <div x-show="pickerOpen" @click.outside="pickerOpen = false" x-transition
                         class="absolute right-0 top-full mt-1 z-30 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 p-3 min-w-[240px]">

                        {{-- Year navigation --}}
                        <div class="flex items-center justify-between mb-3">
                            <button @click="pickerYear--" type="button"
                                class="w-8 h-8 flex items-center justify-center hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>
                            <span class="text-sm font-bold text-gray-900 dark:text-white" x-text="pickerYear"></span>
                            <button @click="pickerYear++" type="button"
                                class="w-8 h-8 flex items-center justify-center hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                        </div>

                        {{-- Month grid 3x4 --}}
                        <div class="grid grid-cols-3 gap-1.5 mb-3">
                            <template x-for="(month, index) in ['Січ','Лют','Бер','Кві','Тра','Чер','Лип','Сер','Вер','Жов','Лис','Гру']" :key="index">
                                <button @click="pickMonth(pickerYear, index); pickerOpen = false" type="button"
                                    :class="startDate.getFullYear() === pickerYear && startDate.getMonth() === index
                                        ? 'bg-primary-500 text-white font-bold'
                                        : (pickerYear === new Date().getFullYear() && index === new Date().getMonth()
                                            ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 font-medium'
                                            : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700')"
                                    class="px-2 py-2 rounded-lg text-sm transition-colors">
                                    <span x-text="month"></span>
                                </button>
                            </template>
                        </div>

                        {{-- Period buttons --}}
                        <div class="flex gap-1.5 mb-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                            <button @click="weeks = '4'; loadData(); pickerOpen = false" type="button"
                                :class="weeks == 4 ? 'bg-primary-100 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300 border-primary-300 dark:border-primary-700' : 'bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-600'"
                                class="flex-1 px-2 py-1.5 text-xs font-medium rounded-lg border transition-colors">4 тиж</button>
                            <button @click="weeks = '8'; loadData(); pickerOpen = false" type="button"
                                :class="weeks == 8 ? 'bg-primary-100 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300 border-primary-300 dark:border-primary-700' : 'bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-600'"
                                class="flex-1 px-2 py-1.5 text-xs font-medium rounded-lg border transition-colors">8 тиж</button>
                            <button @click="weeks = '12'; loadData(); pickerOpen = false" type="button"
                                :class="weeks == 12 ? 'bg-primary-100 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300 border-primary-300 dark:border-primary-700' : 'bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-600'"
                                class="flex-1 px-2 py-1.5 text-xs font-medium rounded-lg border transition-colors">12 тиж</button>
                        </div>

                        {{-- Today button --}}
                        <button @click="goToday(); pickerOpen = false" type="button"
                            class="w-full py-1.5 text-xs font-medium text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/30 rounded-lg transition-colors">
                            {{ __('common.today') }}
                        </button>
                    </div>
                </div>

                <button @click="nextPeriod()" type="button"
                   class="w-9 h-9 flex items-center justify-center hover:bg-gray-100 dark:hover:bg-gray-700 active:bg-gray-200 dark:active:bg-gray-600 rounded-lg transition-colors">
                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
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
    <template x-if="!loading && allEvents.length === 0">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <p class="text-gray-500 dark:text-gray-400 text-lg">{{ __('Немає подій за цей період') }}</p>
            <p class="text-gray-400 dark:text-gray-500 text-sm mt-1">{{ __('Створіть події типу "Служіння" в розкладі') }}</p>
        </div>
    </template>

    {{-- Empty Ministries --}}
    <template x-if="!loading && allEvents.length > 0 && ministries.length === 0">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
            <p class="text-gray-500 dark:text-gray-400 text-lg">{{ __('Немає команд для відображення') }}</p>
            <p class="text-gray-400 dark:text-gray-500 text-sm mt-1">{{ __('Додайте ролі в команди через налаштування команди') }}</p>
        </div>
    </template>

    {{-- Matrix Grid --}}
    <template x-if="!loading && allEvents.length > 0 && ministries.length > 0">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse min-w-[600px]">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700">
                            <th class="sticky left-0 z-20 bg-gray-50 dark:bg-gray-700 px-3 sm:px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider border-b border-r border-gray-200 dark:border-gray-600 w-[160px] sm:w-[200px] min-w-[160px] sm:min-w-[200px]">
                                {{ __('Команда / Роль') }}
                            </th>
                            <template x-for="event in filteredEvents()" :key="event.id">
                                <th class="px-2 py-2 text-center border-b border-gray-200 dark:border-gray-600 min-w-[140px]"
                                    :class="isNearestEvent(event) ? 'bg-primary-50 dark:bg-primary-900/30' : 'bg-gray-50 dark:bg-gray-700'">
                                    <a :href="'/events/' + event.id"
                                       class="block hover:text-primary-600 dark:hover:text-primary-400 transition-colors"
                                       :title="event.title">
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
                                        <div class="text-[10px] font-medium truncate max-w-[130px] mx-auto mt-0.5"
                                             :class="isNearestEvent(event) ? 'text-primary-600 dark:text-primary-400' : 'text-gray-500 dark:text-gray-400'"
                                             x-text="event.title"></div>
                                        <template x-if="event.ministryName">
                                            <div class="text-[9px] truncate max-w-[130px] mx-auto"
                                                 :style="'color:' + event.ministryColor"
                                                 x-text="event.ministryName"></div>
                                        </template>
                                    </a>
                                </th>
                            </template>
                        </tr>
                    </thead>

                    <tbody>
                        <template x-for="(ministry, mIdx) in ministries" :key="ministry.id">
                            <template x-for="(row, rowIdx) in getMinistryRows(ministry)" :key="row.key">
                                <tr class="border-b border-gray-100 dark:border-gray-700/50 group/row hover:bg-gray-50/50 dark:hover:bg-gray-700/20 transition-colors"
                                    :class="rowIdx === 0 ? (mIdx > 0 ? 'border-t-2 border-gray-200 dark:border-gray-600' : 'border-t border-gray-200 dark:border-gray-600') : ''"
                                    x-show="row.type === 'header' || isExpanded(ministry.id)">

                                    {{-- Left column --}}
                                    <td class="sticky left-0 z-10 bg-white dark:bg-gray-800 group-hover/row:bg-gray-50 dark:group-hover/row:bg-gray-700 px-3 sm:px-4 border-r border-gray-200 dark:border-gray-600 transition-colors"
                                        :class="row.type === 'header' ? 'py-2.5 cursor-pointer' : 'py-2.5'"
                                        @click="row.type === 'header' && toggleMinistry(ministry.id)">

                                        {{-- Ministry Header Row --}}
                                        <template x-if="row.type === 'header'">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-2">
                                                    <svg class="w-3.5 h-3.5 transition-transform text-gray-400"
                                                         :class="isExpanded(ministry.id) ? 'rotate-90' : ''"
                                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                    </svg>
                                                    <span class="w-1 h-4 rounded-full flex-shrink-0" :style="'background:' + (ministry.color || '#6B7280')"></span>
                                                    <span class="text-[11px] font-bold uppercase tracking-wide"
                                                          :style="'color:' + (ministry.color || '#6B7280')"
                                                          x-text="(ministry.icon ? ministry.icon + ' ' : '') + ministry.name"></span>
                                                    <span class="text-[10px] text-gray-400 dark:text-gray-500" x-text="'(' + ministry.roles.length + ')'"></span>
                                                </div>
                                                <template x-if="canSelfSignup(ministry)">
                                                    <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-primary-100 dark:bg-primary-900/40 text-primary-600 dark:text-primary-400 font-medium">
                                                        {{ __('app.you') }}
                                                    </span>
                                                </template>
                                            </div>
                                        </template>

                                        {{-- Role Row --}}
                                        <template x-if="row.type === 'role'">
                                            <div class="flex items-center gap-2 pl-6">
                                                <template x-if="row.role.icon">
                                                    <span class="text-sm flex-shrink-0" x-text="row.role.icon"></span>
                                                </template>
                                                <template x-if="!row.role.icon">
                                                    <span class="w-1.5 h-1.5 rounded-full flex-shrink-0 bg-gray-300 dark:bg-gray-600"></span>
                                                </template>
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300" x-text="row.role.name"></span>
                                            </div>
                                        </template>
                                    </td>

                                    {{-- Event cells --}}
                                    <template x-for="event in filteredEvents()" :key="event.id">
                                        <td class="px-1.5 py-1.5 text-center border-l border-gray-100 dark:border-gray-700/50"
                                            :class="isNearestEvent(event) ? 'bg-primary-50/30 dark:bg-primary-900/10' : ''">

                                            {{-- Summary cell (collapsed header) --}}
                                            <template x-if="row.type === 'header'">
                                                <div class="min-h-[32px] flex items-center justify-center cursor-pointer"
                                                     @click.stop="toggleMinistry(ministry.id)">
                                                    <template x-if="!isExpanded(ministry.id)">
                                                        <span class="text-xs font-semibold px-2 py-1 rounded-lg"
                                                              :class="getSummaryClasses(ministry, event)"
                                                              x-text="getSummaryText(ministry, event)"></span>
                                                    </template>
                                                </div>
                                            </template>

                                            {{-- Role cell (expanded) --}}
                                            <template x-if="row.type === 'role'">
                                                <div class="min-h-[40px] flex flex-col items-center justify-center gap-0.5 rounded-lg px-1 py-1 transition-all duration-150 cursor-pointer"
                                                     :class="getCellClasses(ministry.id, row.role, event.id)"
                                                     @click="openCellDropdown(ministry, row.role, event, $event)">
                                                    <template x-for="person in getCellPersons(ministry.id, row.role, event.id)" :key="person.id">
                                                        <div class="flex items-center gap-1 text-xs leading-tight w-full justify-center">
                                                            <span class="w-1.5 h-1.5 rounded-full flex-shrink-0"
                                                                  :class="statusDotClass(person.status)"></span>
                                                            <span class="truncate max-w-[110px] font-medium" x-text="isMe(person) ? person.person_name + ' (' + '{{ __("app.you") }}' + ')' : person.person_name"
                                                                  :class="statusTextClass(person.status)"></span>
                                                        </div>
                                                    </template>
                                                    <template x-if="getCellNotes(ministry.id, row.role, event.id)">
                                                        <div class="text-[10px] text-amber-500 dark:text-amber-400 truncate max-w-[120px] mt-0.5" :title="getCellNotes(ministry.id, row.role, event.id)" x-text="'💬 ' + getCellNotes(ministry.id, row.role, event.id)"></div>
                                                    </template>
                                                    <template x-if="getCellPersons(ministry.id, row.role, event.id).length === 0">
                                                        <div class="flex items-center justify-center w-full h-full">
                                                            <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 group-hover/row:text-primary-400 dark:group-hover/row:text-primary-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                            </svg>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
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
                        <span class="w-2 h-2 rounded-full bg-green-500"></span> {{ __('Підтверджено') }}
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span> {{ __('Очікує') }}
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-red-500"></span> {{ __('Відхилено') }}
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-gray-400"></span> {{ __('Не підтверджено') }}
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

        <div class="px-3 py-2 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div class="min-w-0">
                    <div class="text-xs font-semibold text-gray-900 dark:text-white truncate"
                         x-text="dropdown.role?.icon ? dropdown.role.icon + ' ' + dropdown.role?.name : dropdown.role?.name"></div>
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

        {{-- Assigned persons --}}
        <template x-if="dropdown.persons.length > 0">
            <div class="border-b border-gray-200 dark:border-gray-700">
                <template x-for="person in dropdown.persons" :key="person.id">
                    <div class="flex items-center justify-between px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="w-2 h-2 rounded-full flex-shrink-0" :class="statusDotClass(person.status)"></span>
                            <span class="text-sm text-gray-900 dark:text-white truncate" x-text="isMe(person) ? person.person_name + ' (' + '{{ __("app.you") }}' + ')' : person.person_name"></span>
                            <span class="text-[10px] px-1.5 py-0.5 rounded-full flex-shrink-0"
                                  :class="statusBadgeClass(person.status)"
                                  x-text="statusLabel(person.status)"></span>
                        </div>
                        <div class="flex items-center gap-0.5 flex-shrink-0 ml-2">
                            {{-- Telegram notify (leader only) --}}
                            <template x-if="isLeader && person.has_telegram && person.source !== 'assignment'">
                                <button @click.stop="notifyPerson(person)"
                                    class="p-1.5 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20"
                                    :title="'{{ __('Надіслати в Telegram') }}'">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"/>
                                    </svg>
                                </button>
                            </template>
                            {{-- Remove: leader can remove anyone, member can unsubscribe self --}}
                            <template x-if="isLeader || isMe(person)">
                                <button @click.stop="isMe(person) && !isLeader ? selfUnsubscribe(person) : removePerson(person)"
                                    class="p-1.5 text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20"
                                    :title="isMe(person) ? '{{ __("app.unsubscribe") }}' : '{{ __('Видалити') }}'">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </template>

        {{-- Notes (leader only) --}}
        <template x-if="isLeader">
            <div class="px-3 py-2 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-1.5 mb-1">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                    </svg>
                    <span class="text-[11px] text-gray-500 dark:text-gray-400 font-medium">{{ __('Примітка до позиції') }}</span>
                </div>
                <input type="text" :value="dropdown.cellNotes || ''"
                       @input.debounce.600ms="saveCellNotes($event.target.value)"
                       placeholder="{{ __('Примітка...') }}"
                       class="w-full px-2 py-1.5 text-xs rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 dark:text-gray-200 focus:ring-primary-500 focus:border-primary-500 placeholder-gray-400 dark:placeholder-gray-500">
            </div>
        </template>

        {{-- Self-signup button (team members only, not leader view) --}}
        <template x-if="!isLeader && canSelfSignup(dropdown.ministry) && !isMeAssigned()">
            <div class="px-3 py-2 border-b border-gray-200 dark:border-gray-700">
                <button @click="selfSignup()"
                    :disabled="busy"
                    class="w-full px-3 py-2 text-sm font-medium bg-primary-600 hover:bg-primary-700 disabled:bg-primary-400 text-white rounded-lg transition-colors flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('app.sign_up') }}
                </button>
            </div>
        </template>

        {{-- Leader: search & assign --}}
        <template x-if="isLeader">
            <div>
                <div class="p-2">
                    <input type="text" x-model="dropdown.search" x-ref="dropdownSearch"
                           placeholder="{{ __('Пошук учасника...') }}"
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
                            {{ __('Нікого не знайдено') }}
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </div>
</div>

<script>
function servicePlanningMatrix() {
    return {
        loading: false,
        _savedFilters: filterStorage.load('service_planning', { weeks: '4', hiddenEventTitles: [] }),
        weeks: null, // set in init from saved
        startDate: null,
        events: [],
        ministries: [],
        grid: {},
        cellNotes: {},
        members: {},
        periodLabel: '',
        nearestEventId: null,
        collapsed: JSON.parse(localStorage.getItem('sp_collapsed') || '{}'),
        hiddenEventTitles: new Set(), // set in init from saved
        uniqueEventTitles: [],
        allEvents: [],

        // Self-signup context
        currentPersonId: null,
        myMinistryIds: [],
        isLeader: false,

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

        toast: { show: false, message: '', type: 'success', timer: null },
        busy: false,

        init() {
            // Restore saved filters
            this.weeks = this._savedFilters.weeks;
            this.hiddenEventTitles = new Set(this._savedFilters.hiddenEventTitles);

            const now = new Date();
            const day = now.getDay();
            const diff = now.getDate() - day + (day === 0 ? -6 : 1);
            this.startDate = new Date(now.setDate(diff));
            this.startDate.setHours(0, 0, 0, 0);

            // Watch for filter changes
            this.$watch('weeks', () => this._saveFilters());
            this.$watch('hiddenEventTitles', () => this._saveFilters());
        },

        _saveFilters() {
            filterStorage.save('service_planning', {
                weeks: this.weeks,
                hiddenEventTitles: Array.from(this.hiddenEventTitles),
            });
        },

        formatDate(d) {
            return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
        },

        updatePeriodLabel() {
            const months = ['Січень', 'Лютий', 'Березень', 'Квітень', 'Травень', 'Червень', 'Липень', 'Серпень', 'Вересень', 'Жовтень', 'Листопад', 'Грудень'];
            const end = new Date(this.startDate);
            end.setDate(end.getDate() + this.weeks * 7 - 1);
            if (this.startDate.getMonth() === end.getMonth() && this.startDate.getFullYear() === end.getFullYear()) {
                this.periodLabel = months[this.startDate.getMonth()] + ' ' + this.startDate.getFullYear();
            } else if (this.startDate.getFullYear() === end.getFullYear()) {
                this.periodLabel = months[this.startDate.getMonth()] + ' — ' + months[end.getMonth()] + ' ' + end.getFullYear();
            } else {
                this.periodLabel = months[this.startDate.getMonth()] + ' ' + this.startDate.getFullYear() + ' — ' + months[end.getMonth()] + ' ' + end.getFullYear();
            }
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

        jumpToDate(dateStr) {
            const d = new Date(dateStr + 'T00:00:00');
            const day = d.getDay();
            const diff = d.getDate() - day + (day === 0 ? -6 : 1);
            this.startDate = new Date(d.setDate(diff));
            this.startDate.setHours(0, 0, 0, 0);
            this.loadData();
        },

        pickMonth(year, monthIndex) {
            const d = new Date(year, monthIndex, 1);
            const day = d.getDay();
            const diff = d.getDate() - day + (day === 0 ? -6 : 1);
            this.startDate = new Date(d.setDate(diff));
            this.startDate.setHours(0, 0, 0, 0);
            this.loadData();
        },

        findNearestEvent() {
            const today = this.formatDate(new Date());
            let nearest = null;
            const evts = this.allEvents;
            for (const event of evts) {
                if (event.date >= today) { nearest = event.id; break; }
            }
            this.nearestEventId = nearest || (evts.length > 0 ? evts[evts.length - 1].id : null);
        },

        isNearestEvent(event) { return event.id === this.nearestEventId; },

        // Event ministry filter
        filteredEvents() {
            if (this.hiddenEventTitles.size === 0) return this.allEvents;
            return this.allEvents.filter(e => !this.hiddenEventTitles.has(e.ministryName));
        },

        buildUniqueEventTitles() {
            const seen = new Set();
            const titles = [];
            for (const e of this.allEvents) {
                if (e.ministryName && !seen.has(e.ministryName)) {
                    seen.add(e.ministryName);
                    titles.push(e.ministryName);
                }
            }
            titles.sort();
            this.uniqueEventTitles = titles;
        },

        eventCountByTitle(title) {
            return this.allEvents.filter(e => e.ministryName === title).length;
        },

        toggleEventTitleFilter(title) {
            if (this.hiddenEventTitles.has(title)) {
                this.hiddenEventTitles.delete(title);
            } else {
                this.hiddenEventTitles.add(title);
            }
            // Force reactivity
            this.hiddenEventTitles = new Set(this.hiddenEventTitles);
        },

        showAllEventTitles() {
            this.hiddenEventTitles = new Set();
        },

        hideAllEventTitles() {
            this.hiddenEventTitles = new Set(this.uniqueEventTitles);
        },

        async loadData() {
            this.loading = true;
            this.dropdown.open = false;
            this.updatePeriodLabel();

            try {
                const params = new URLSearchParams({
                    weeks: this.weeks,
                    start_date: this.formatDate(this.startDate),
                });

                const resp = await fetch(`{{ route('schedule.matrix-data') }}?${params}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (!resp.ok) throw new Error('Failed to load');

                const data = await resp.json();
                this.allEvents = data.events;
                this.events = data.events;
                this.buildUniqueEventTitles();
                this.ministries = data.ministriesData;
                this.grid = data.grid;
                this.cellNotes = data.cellNotes || {};
                this.members = data.members;
                this.currentPersonId = data.currentPersonId;
                this.myMinistryIds = data.myMinistryIds || [];
                this.isLeader = data.isLeader || false;
                this.findNearestEvent();
            } catch (e) {
                console.error('Matrix load error:', e);
                this.showToast('{{ __("Помилка завантаження") }}', 'error');
            } finally {
                this.loading = false;
            }
        },

        // Collapsible ministries
        toggleMinistry(ministryId) {
            this.collapsed[ministryId] = !this.collapsed[ministryId];
            localStorage.setItem('sp_collapsed', JSON.stringify(this.collapsed));
        },

        isExpanded(ministryId) {
            return !this.collapsed[ministryId];
        },

        getMinistryRows(ministry) {
            const rows = [{ key: 'header_' + ministry.id, type: 'header' }];
            for (const role of ministry.roles) {
                rows.push({ key: role.type + '_' + role.id, type: 'role', role });
            }
            return rows;
        },

        getSummaryText(ministry, event) {
            let total = ministry.roles.length;
            let filled = 0;
            for (const role of ministry.roles) {
                const persons = this.getCellPersons(ministry.id, role, event.id);
                if (persons.length > 0) filled++;
            }
            return filled + '/' + total;
        },

        getSummaryClasses(ministry, event) {
            let total = ministry.roles.length;
            let filled = 0;
            for (const role of ministry.roles) {
                const persons = this.getCellPersons(ministry.id, role, event.id);
                if (persons.length > 0) filled++;
            }
            if (filled === 0) return 'text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-gray-700';
            if (filled === total) return 'text-green-700 dark:text-green-400 bg-green-100 dark:bg-green-900/40';
            return 'text-amber-700 dark:text-amber-400 bg-amber-100 dark:bg-amber-900/40';
        },

        // Self-signup helpers
        canSelfSignup(ministry) {
            return ministry && this.myMinistryIds.includes(ministry.id);
        },

        isMe(person) {
            return person && person.person_id === this.currentPersonId;
        },

        isMeAssigned() {
            return this.dropdown.persons.some(p => this.isMe(p));
        },

        async selfSignup() {
            const { ministry, role, event } = this.dropdown;
            if (!ministry || !role || !event || this.busy) return;
            this.busy = true;

            try {
                const resp = await fetch(`/events/${event.id}/self-signup`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        ministry_id: ministry.id,
                        ministry_role_id: role.id,
                    }),
                });

                const data = await resp.json();
                if (resp.ok) {
                    const mKey = String(ministry.id);
                    const rKey = this.getRoleKey(role);
                    const eKey = String(event.id);
                    if (!this.grid[mKey]) this.grid[mKey] = {};
                    if (!this.grid[mKey][rKey]) this.grid[mKey][rKey] = {};
                    if (!this.grid[mKey][rKey][eKey]) this.grid[mKey][rKey][eKey] = [];

                    this.grid[mKey][rKey][eKey].push({
                        id: data.id,
                        person_id: this.currentPersonId,
                        person_name: data.person_name,
                        status: 'confirmed',
                        has_telegram: false,
                        source: 'ministry_team',
                        notes: null,
                    });

                    this.dropdown.persons = this.grid[mKey][rKey][eKey];
                    this.showToast('{{ __("app.you_signed_up") }}');
                    setTimeout(() => { this.dropdown.open = false; }, 600);
                } else {
                    this.showToast(data.error || data.message || '{{ __("Помилка") }}', 'error');
                }
            } catch (e) {
                console.error('Self-signup error:', e);
                this.showToast('{{ __("Помилка") }}', 'error');
            } finally {
                this.busy = false;
            }
        },

        async selfUnsubscribe(person) {
            const { ministry, role, event } = this.dropdown;
            if (!ministry || !event || this.busy) return;
            this.busy = true;

            try {
                const resp = await fetch(`/events/${event.id}/self-unsubscribe/${person.id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (resp.ok) {
                    const mKey = String(ministry.id);
                    const rKey = this.getRoleKey(role);
                    const eKey = String(event.id);
                    if (this.grid[mKey]?.[rKey]?.[eKey]) {
                        this.grid[mKey][rKey][eKey] = this.grid[mKey][rKey][eKey].filter(p => p.id !== person.id);
                    }
                    this.dropdown.persons = this.grid[mKey]?.[rKey]?.[eKey] || [];
                    this.showToast('{{ __("app.you_unsubscribed") }}');
                    if (this.dropdown.persons.length === 0) {
                        setTimeout(() => { this.dropdown.open = false; }, 400);
                    }
                }
            } catch (e) {
                console.error('Unsubscribe error:', e);
                this.showToast('{{ __("Помилка") }}', 'error');
            } finally {
                this.busy = false;
            }
        },

        // Existing matrix methods
        getRoleKey(role) { return role.type + '_' + role.id; },

        getCellPersons(ministryId, role, eventId) {
            return this.grid?.[String(ministryId)]?.[this.getRoleKey(role)]?.[String(eventId)] || [];
        },

        getCellNotes(ministryId, role, eventId) {
            // Check independent cell notes first
            const noteKey = eventId + '_' + role.type + '_' + role.id;
            if (this.cellNotes[noteKey]) return this.cellNotes[noteKey];
            // Fall back to person-level notes
            for (const p of this.getCellPersons(ministryId, role, eventId)) {
                if (p.notes) return p.notes;
            }
            return null;
        },

        getCellClasses(ministryId, role, eventId) {
            const persons = this.getCellPersons(ministryId, role, eventId);
            if (persons.length === 0) return 'border border-dashed border-gray-200 dark:border-gray-700 hover:border-primary-300 dark:hover:border-primary-700 hover:bg-primary-50/50 dark:hover:bg-primary-900/20';
            if (persons.some(p => p.status === 'declined')) return 'bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/30 ring-1 ring-red-200 dark:ring-red-800';
            if (persons.every(p => p.status === 'confirmed' || p.status === 'attended')) return 'bg-green-50 dark:bg-green-900/20 hover:bg-green-100 dark:hover:bg-green-900/30';
            return 'bg-amber-50 dark:bg-amber-900/20 hover:bg-amber-100 dark:hover:bg-amber-900/30';
        },

        statusDotClass(s) { return { confirmed: 'bg-green-500', pending: 'bg-amber-500', declined: 'bg-red-500', attended: 'bg-blue-500' }[s] || 'bg-gray-400'; },
        statusTextClass(s) { return { confirmed: 'text-green-700 dark:text-green-400', pending: 'text-amber-700 dark:text-amber-300', declined: 'text-red-500 dark:text-red-400 line-through', attended: 'text-blue-700 dark:text-blue-400' }[s] || 'text-gray-700 dark:text-gray-300'; },
        statusBadgeClass(s) { return { confirmed: 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400', pending: 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400', declined: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400', attended: 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400' }[s] || 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400'; },
        statusLabel(s) { return { confirmed: '{{ __("Так") }}', pending: '{{ __("Очікує") }}', declined: '{{ __("Ні") }}', attended: '{{ __("Був") }}' }[s] || '—'; },

        openCellDropdown(ministry, role, event, $event) {
            // Don't open dropdown for non-leaders on foreign teams (view-only)
            if (!this.isLeader && !this.canSelfSignup(ministry)) return;

            const rect = $event.currentTarget.getBoundingClientRect();
            const dw = 288, dh = 360;
            let x = rect.left + (rect.width / 2) - (dw / 2);
            let y = rect.bottom + 6;
            if (x + dw > window.innerWidth - 8) x = window.innerWidth - dw - 8;
            if (y + dh > window.innerHeight) y = rect.top - dh - 6;
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

            this.$nextTick(() => { this.$refs.dropdownSearch?.focus(); });
        },

        filteredMembers() {
            if (!this.dropdown.ministry) return [];
            const mKey = String(this.dropdown.ministry.id);
            const allMembers = this.members[mKey] || [];
            const assignedIds = this.dropdown.persons.map(p => p.person_id);
            const search = this.dropdown.search.toLowerCase();
            return allMembers.filter(m => !assignedIds.includes(m.id) && (!search || m.name.toLowerCase().includes(search)));
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
            const mKey = String(ministry.id), rKey = this.getRoleKey(role), eKey = String(event.id);

            try {
                let url, body;
                if (role.type === 'ministry_role') {
                    url = `/events/${event.id}/ministry-team`;
                    body = { ministry_id: ministry.id, person_id: member.id, ministry_role_id: role.id };
                } else {
                    url = `/rotation/event/${event.id}/assign-position`;
                    body = { position_id: role.id, person_id: member.id };
                }

                const resp = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify(body),
                });

                if (resp.ok) {
                    const data = await resp.json();
                    if (!this.grid[mKey]) this.grid[mKey] = {};
                    if (!this.grid[mKey][rKey]) this.grid[mKey][rKey] = {};
                    if (!this.grid[mKey][rKey][eKey]) this.grid[mKey][rKey][eKey] = [];

                    this.grid[mKey][rKey][eKey].push({
                        id: data.id, person_id: member.id,
                        person_name: member.short_name || member.name,
                        status: data.status || 'pending', has_telegram: member.has_telegram,
                        source: role.type === 'ministry_role' ? 'ministry_team' : 'assignment', notes: null,
                    });

                    this.dropdown.persons = this.grid[mKey][rKey][eKey];
                    this.showToast((member.short_name || member.name) + ' — {{ __("призначено") }}');
                    if (this.filteredMembers().length === 0) setTimeout(() => { this.dropdown.open = false; }, 600);
                } else {
                    const err = await resp.json().catch(() => ({}));
                    this.showToast(err.error || err.message || '{{ __("Помилка при призначенні") }}', 'error');
                }
            } catch (e) {
                console.error('Assign error:', e);
                this.showToast('{{ __("Помилка при призначенні") }}', 'error');
            } finally { this.busy = false; }
        },

        async removePerson(person) {
            const { ministry, role, event } = this.dropdown;
            if (!ministry || !role || !event || this.busy) return;
            this.busy = true;
            const mKey = String(ministry.id), rKey = this.getRoleKey(role), eKey = String(event.id);

            try {
                const url = person.source === 'assignment' ? `/rotation/assignment/${person.id}` : `/events/${event.id}/ministry-team/${person.id}`;
                const resp = await fetch(url, {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest' },
                });

                if (resp.ok) {
                    if (this.grid[mKey]?.[rKey]?.[eKey]) this.grid[mKey][rKey][eKey] = this.grid[mKey][rKey][eKey].filter(p => p.id !== person.id);
                    this.dropdown.persons = this.grid[mKey]?.[rKey]?.[eKey] || [];
                    this.showToast(person.person_name + ' — {{ __("видалено") }}');
                    if (this.dropdown.persons.length === 0) setTimeout(() => { this.dropdown.open = false; }, 400);
                }
            } catch (e) {
                console.error('Remove error:', e);
                this.showToast('{{ __("Помилка") }}', 'error');
            } finally { this.busy = false; }
        },

        async saveCellNotes(value) {
            const { ministry, role, event } = this.dropdown;
            if (!ministry || !role || !event) return;
            const notes = value.trim() || null;
            this.dropdown.cellNotes = notes || '';
            const noteKey = event.id + '_' + role.type + '_' + role.id;

            try {
                // Save independent cell note
                await fetch(`/events/${event.id}/cell-note`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({ role_type: role.type, role_id: role.id, notes }),
                });

                // Update local state
                if (notes) {
                    this.cellNotes[noteKey] = notes;
                } else {
                    delete this.cellNotes[noteKey];
                }

                this.showToast('{{ __("Примітку збережено") }}');
            } catch (e) {
                console.error('Save cell notes error:', e);
                this.showToast('{{ __("Помилка") }}', 'error');
            }
        },

        async notifyPerson(person) {
            const { event } = this.dropdown;
            if (!event) return;
            try {
                const resp = await fetch(`/events/${event.id}/ministry-team/${person.id}/notify`, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest' },
                });
                const data = await resp.json();
                if (data.success) {
                    const mKey = String(this.dropdown.ministry.id), rKey = this.getRoleKey(this.dropdown.role), eKey = String(event.id);
                    if (this.grid[mKey]?.[rKey]?.[eKey]) { const p = this.grid[mKey][rKey][eKey].find(p => p.id === person.id); if (p) p.status = 'pending'; }
                    this.dropdown.persons = [...(this.grid[mKey]?.[rKey]?.[eKey] || [])];
                    this.showToast('{{ __("Повідомлення надіслано") }}');
                } else {
                    this.showToast(data.message || '{{ __("Не вдалося надіслати") }}', 'error');
                }
            } catch (e) {
                console.error('Notify error:', e);
                this.showToast('{{ __("Помилка") }}', 'error');
            }
        },
    };
}
</script>
@endsection
