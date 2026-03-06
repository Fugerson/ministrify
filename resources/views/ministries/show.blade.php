@extends('layouts.app')

@section('title', $ministry->name)

@section('actions')
@endsection

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 sm:p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                @if($ministry->color)
                    <div class="w-4 h-4 rounded-full" style="background-color: {{ $ministry->color }}"></div>
                @endif
                <div>
                    <div class="flex items-center gap-2">
                        <h1 id="ministry-name" class="text-2xl font-bold text-gray-900 dark:text-white">{{ $ministry->name }}</h1>
                        @php $visibility = $ministry->visibility ?? 'public'; @endphp
                        @if($visibility !== 'public')
                            @php
                                $badgeColors = [
                                    'members' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
                                    'leaders' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
                                    'specific' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                                ];
                                $badgeLabels = [
                                    'members' => __('app.members_only'),
                                    'leaders' => __('app.leaders_only'),
                                    'specific' => __('app.specific_people'),
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $badgeColors[$visibility] ?? 'bg-gray-100 text-gray-800' }}">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                {{ $badgeLabels[$visibility] ?? __('app.private') }}
                            </span>
                        @endif
                    </div>
                    @if($ministry->leader)
                        <p class="text-gray-500 dark:text-gray-400">{{ __('app.leader_prefix') }} {{ $ministry->leader->full_name }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm"
         x-data="{
            activeTab: new URLSearchParams(window.location.search).has('tab')
                ? '{{ $tab }}'
                : (filterStorage.load('ministry_tab', { tab: '{{ $tab }}' }).tab || '{{ $tab }}'),
            init() {
                filterStorage.save('ministry_tab', { tab: this.activeTab });
            },
            setTab(tab) {
                this.activeTab = tab;
                filterStorage.save('ministry_tab', { tab });
                const url = new URL(window.location);
                url.searchParams.set('tab', tab);
                if (tab !== 'resources') {
                    url.searchParams.delete('folder');
                }
                history.pushState({}, '', url);
            }
         }">
        <div class="border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
            <nav class="flex -mb-px whitespace-nowrap">
                <button @click="setTab('goals')" type="button"
                   :class="activeTab === 'goals' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
                   class="px-3 sm:px-6 py-3 border-b-2 text-sm font-medium flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ __('app.tab_planning') }}
                </button>
                <button @click="setTab('schedule')" type="button"
                   :class="activeTab === 'schedule' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
                   class="px-3 sm:px-6 py-3 border-b-2 text-sm font-medium">
                    {{ __('app.tab_events') }}
                </button>
                <button @click="setTab('members')" type="button"
                   :class="activeTab === 'members' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
                   class="px-3 sm:px-6 py-3 border-b-2 text-sm font-medium">
                    {{ __('app.tab_team') }} ({{ $ministry->members->count() }})
                </button>
                <button @click="setTab('expenses')" type="button"
                   :class="activeTab === 'expenses' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
                   class="px-3 sm:px-6 py-3 border-b-2 text-sm font-medium">
                    {{ __('app.tab_budget') }}
                </button>
                <button @click="setTab('board')" type="button"
                   :class="activeTab === 'board' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
                   class="px-3 sm:px-6 py-3 border-b-2 text-sm font-medium flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7"/>
                    </svg>
                    {{ __('app.tab_tasks') }}
                </button>
                <button @click="setTab('resources')" type="button"
                   :class="activeTab === 'resources' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
                   class="px-3 sm:px-6 py-3 border-b-2 text-sm font-medium flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                    </svg>
                    {{ __('app.tab_resources') }}
                </button>
                @if($ministry->is_worship_ministry)
                <button @click="setTab('songs')" type="button"
                   :class="activeTab === 'songs' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
                   class="px-3 sm:px-6 py-3 border-b-2 text-sm font-medium flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                    </svg>
                    {{ __('app.tab_songs_library') }}
                </button>
                @endif
                @can('manage-ministry', $ministry)
                <button @click="setTab('settings')" type="button"
                   :class="activeTab === 'settings' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
                   class="px-3 sm:px-6 py-3 border-b-2 text-sm font-medium flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    {{ __('app.tab_settings') }}
                </button>
                @endcan
            </nav>
        </div>

        <div class="p-6">
            <div x-show="activeTab === 'schedule'"{{ $tab !== 'schedule' ? ' style="display:none"' : '' }}>
                    {{-- Schedule calendar view --}}
                    @php
                        $scheduleEventsGrouped = $scheduleEvents->groupBy(fn($e) => $e->date->format('Y-m-d'));
                    @endphp
                    <div x-data="worshipCalendar()" x-init="init()">
                        {{-- Header --}}
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 gap-3">
                            <div class="flex items-center gap-2">
                                <button @click="prevMonth()" class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                </button>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white min-w-[160px] text-center" x-text="monthYearLabel"></h3>
                                <button @click="nextMonth()" class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </button>
                                <button @click="goToToday()" class="ml-2 px-2 py-1 text-xs text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded">{{ __('app.today_btn_calendar') }}</button>
                            </div>
                            <div class="flex items-center gap-2">
                                {{-- View switcher --}}
                                <div class="flex bg-gray-100 dark:bg-gray-700 rounded-lg p-0.5">
                                    <button @click="gridView = false; let u = new URL(window.location); u.searchParams.delete('view'); history.replaceState({}, '', u)"
                                        :class="!gridView ? 'bg-white dark:bg-gray-600 shadow-sm text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                                        class="px-3 py-1 text-xs font-medium rounded-md transition-colors">
                                        {{ __('app.calendar_view') }}
                                    </button>
                                    <button @click="gridView = true; loadGrid(); let u = new URL(window.location); u.searchParams.set('view', 'grid'); history.replaceState({}, '', u)"
                                        :class="gridView ? 'bg-white dark:bg-gray-600 shadow-sm text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                                        class="px-3 py-1 text-xs font-medium rounded-md transition-colors">
                                        {{ __('app.grid_view') }}
                                    </button>
                                </div>
                                @if($ministry->is_worship_ministry)
                                <a href="{{ route('ministries.worship-stats', $ministry) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                    </svg>
                                    {{ __('app.statistics_tab') }}
                                </a>
                                @endif
                                @can('contribute-ministry', $ministry)
                                <a href="{{ route('events.create', ['ministry_id' => $ministry->id]) }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    {{ __('app.create') }}
                                </a>
                                @endcan
                            </div>
                        </div>

                        {{-- Schedule Grid View --}}
                        <div x-show="gridView" x-cloak>
                            {{-- Loading --}}
                            <div x-show="gridLoading" class="flex items-center justify-center py-12">
                                <svg class="animate-spin h-8 w-8 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>

                            {{-- Event cards --}}
                            <div x-show="!gridLoading">
                                {{-- Empty state --}}
                                <template x-if="gridData.events.length === 0">
                                    <div class="text-center py-8">
                                        <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                            </svg>
                                        </div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.no_events_this_month') }}</p>
                                    </div>
                                </template>

                                {{-- Cards grid --}}
                                <template x-if="gridData.events.length > 0">
                                    <div class="flex gap-4 overflow-x-auto pb-4 snap-x">
                                        <template x-for="event in gridData.events" :key="event.id">
                                            <div class="flex-shrink-0 w-56 sm:w-64 snap-start bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md hover:border-purple-300 dark:hover:border-purple-600 transition-all cursor-pointer flex flex-col"
                                                 @click="openEventModal(event)">
                                                {{-- Card header --}}
                                                <div class="px-3 py-2 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 rounded-t-xl">
                                                    <div class="flex items-center justify-between">
                                                        <div>
                                                            <span class="text-sm font-semibold text-gray-900 dark:text-white" x-text="event.dateLabel"></span>
                                                            <span class="text-xs text-gray-400 dark:text-gray-500 ml-1" x-text="'· ' + event.dayOfWeek"></span>
                                                        </div>
                                                        <span x-show="event.time" class="text-[11px] text-gray-400 dark:text-gray-500" x-text="event.time"></span>
                                                    </div>
                                                </div>

                                                {{-- Summary bar --}}
                                                <div class="px-3 py-1.5 flex items-center gap-3 text-[11px] text-gray-500 dark:text-gray-400 border-b border-gray-50 dark:border-gray-700/50">
                                                    <span x-text="'🎵 ' + event.songsCount + ' {{ __('app.songs_lowercase') }}'"></span>
                                                    <span x-text="'👥 ' + event.teamCount"></span>
                                                </div>

                                                {{-- Card body: flat member list --}}
                                                <div class="flex-1 px-3 py-2 space-y-0.5 min-h-[50px]">
                                                    {{-- Empty state --}}
                                                    <template x-if="getEventRoles(event.id).length === 0">
                                                        <p class="text-xs text-gray-400 dark:text-gray-500 italic py-3 text-center">{{ __('app.empty') }}</p>
                                                    </template>

                                                    {{-- Flat list of members with role icons --}}
                                                    <template x-for="roleGroup in getEventRoles(event.id)" :key="'rg-'+event.id+'-'+roleGroup.roleId">
                                                        <template x-for="member in roleGroup.members" :key="'m-'+member.id">
                                                            <div class="flex items-center gap-1.5 py-0.5">
                                                                <span x-show="roleGroup.roleIcon" x-text="roleGroup.roleIcon" class="text-xs flex-shrink-0" :title="roleGroup.roleName"></span>
                                                                <span x-show="!roleGroup.roleIcon" class="w-4 h-4 flex-shrink-0 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center text-[9px] text-gray-500 dark:text-gray-400" x-text="roleGroup.roleName.charAt(0)"></span>
                                                                <span class="text-xs text-gray-700 dark:text-gray-300 truncate" x-text="member.person_name"></span>
                                                                <span x-show="member.status === 'confirmed'" class="text-[10px] flex-shrink-0">✅</span>
                                                                <span x-show="member.status === 'declined'" class="text-[10px] flex-shrink-0">❌</span>
                                                                <span x-show="member.status === 'pending'" class="text-[10px] flex-shrink-0">⏳</span>
                                                            </div>
                                                        </template>
                                                    </template>
                                                </div>

                                                {{-- Card footer --}}
                                                <div class="px-3 py-2 border-t border-gray-100 dark:border-gray-700 relative">
                                                    <div class="flex gap-1">
                                                        {{-- Signup button --}}
                                                        <template x-if="isCurrentMember() && !isSignedUp(event.id)">
                                                            <button @click.stop="signupEvent = signupEvent === event.id ? null : event.id"
                                                                class="flex-1 text-xs text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 font-medium py-1 rounded-lg hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-colors flex items-center justify-center gap-1">
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                                                {{ __('app.sign_up_btn') }}
                                                            </button>
                                                        </template>
                                                        {{-- Open button --}}
                                                        <button @click.stop="openEventModal(event)"
                                                            :class="isCurrentMember() && !isSignedUp(event.id) ? 'flex-1' : 'w-full'"
                                                            class="text-xs text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300 font-medium py-1 rounded-lg hover:bg-purple-50 dark:hover:bg-purple-900/20 transition-colors flex items-center justify-center gap-1">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                            {{ __('app.open_event_btn') }}
                                                        </button>
                                                    </div>

                                                    {{-- Role selection popup --}}
                                                    <div x-show="signupEvent === event.id"
                                                         x-transition:enter="transition ease-out duration-100"
                                                         x-transition:enter-start="opacity-0 scale-95"
                                                         x-transition:enter-end="opacity-100 scale-100"
                                                         x-transition:leave="transition ease-in duration-75"
                                                         x-transition:leave-start="opacity-100 scale-100"
                                                         x-transition:leave-end="opacity-0 scale-95"
                                                         @click.stop
                                                         @click.outside="signupEvent = null"
                                                         class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 w-48 bg-white dark:bg-gray-700 rounded-lg shadow-lg border border-gray-200 dark:border-gray-600 py-1 z-20">
                                                        <div class="px-2 py-1 text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">{{ __('app.choose_role_label') }}</div>
                                                        <template x-for="role in gridData.roles" :key="'signup-'+event.id+'-'+role.id">
                                                            <button @click.stop="selfSignup(event.id, role.id)"
                                                                class="w-full text-left px-3 py-1.5 text-xs text-gray-700 dark:text-gray-200 hover:bg-purple-50 dark:hover:bg-purple-900/30 flex items-center gap-2 transition-colors">
                                                                <span x-show="role.icon" x-text="role.icon" class="text-sm"></span>
                                                                <span x-show="!role.icon" class="w-4 h-4 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center text-[9px]" x-text="role.name.charAt(0)"></span>
                                                                <span x-text="role.name"></span>
                                                            </button>
                                                        </template>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>

                        </div>

                        {{-- Calendar view --}}
                        <div x-show="!gridView">

                        {{-- Day names --}}
                        <div class="grid grid-cols-7 mb-1">
                            <template x-for="day in '{{ __('app.day_names_short_js') }}'.split(',')" :key="day">
                                <div class="text-center text-xs font-medium text-gray-500 dark:text-gray-400 py-2" x-text="day"></div>
                            </template>
                        </div>

                        {{-- Calendar grid --}}
                        <div class="grid grid-cols-7 border-t border-l border-gray-200 dark:border-gray-700">
                            <template x-for="(day, index) in calendarDays" :key="index">
                                <div class="border-r border-b border-gray-200 dark:border-gray-700 min-h-[80px] sm:min-h-[100px] p-1"
                                     :class="{ 'bg-gray-50 dark:bg-gray-800/50': !day.isCurrentMonth, 'bg-white dark:bg-gray-800': day.isCurrentMonth }">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-xs font-medium"
                                              :class="{
                                                  'text-gray-400 dark:text-gray-600': !day.isCurrentMonth,
                                                  'text-gray-700 dark:text-gray-300': day.isCurrentMonth && !day.isToday,
                                                  'bg-purple-600 text-white rounded-full w-6 h-6 flex items-center justify-center': day.isToday
                                              }"
                                              x-text="day.date"></span>
                                    </div>
                                    <div class="space-y-0.5">
                                        <template x-for="event in day.events" :key="event.id">
                                            <button @click="event.isSundayService ? openEventModal(event) : (Livewire.navigate(event.eventUrl))"
                                               class="block w-full text-left px-1 py-0.5 text-xs rounded truncate transition-colors cursor-pointer"
                                               :class="event.isPast ? 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400' : 'bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300 hover:bg-purple-200 dark:hover:bg-purple-900/60'">
                                                <span x-text="event.time" class="font-medium"></span>
                                                <span x-text="event.title" class="hidden sm:inline"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Events list below calendar --}}
                        <div class="mt-6" x-show="currentMonthEvents.length > 0">
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{ __('app.events_this_month_heading') }}</h4>
                            <div class="space-y-2">
                                <template x-for="event in currentMonthEvents" :key="event.id">
                                    <button @click="event.isSundayService ? openEventModal(event) : (Livewire.navigate(event.eventUrl))"
                                       class="block w-full text-left p-3 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors cursor-pointer"
                                       :class="{ 'opacity-60': event.isPast }">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-gradient-to-br from-purple-500 to-pink-500 text-white">
                                                    <span class="text-xs font-bold" x-text="event.day"></span>
                                                </div>
                                                <div>
                                                    <p class="font-medium text-gray-900 dark:text-white text-sm" x-text="event.title"></p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400" x-text="event.fullDate + '{{ __('app.ministry_at_time') }}' + event.time"></p>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                                                <template x-if="event.songsCount > 0">
                                                    <span class="flex items-center gap-1">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                                                        </svg>
                                                        <span x-text="event.songsCount"></span>
                                                    </span>
                                                </template>
                                                <template x-if="event.teamCount > 0">
                                                    <span class="flex items-center gap-1">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                                                        </svg>
                                                        <span x-text="event.teamCount"></span>
                                                    </span>
                                                </template>
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </div>
                                        </div>
                                    </button>
                                </template>
                            </div>
                        </div>

                        {{-- Empty state --}}
                        <div x-show="allEvents.length === 0 && !gridView" class="text-center py-8">
                            <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                                </svg>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.no_planned_events_msg') }}</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ __('app.create_music_event_hint') }}</p>
                        </div>

                        </div>{{-- end calendar view wrapper --}}

                        @php
                            $canEditEvent = $ministry->isMember() || auth()->user()->canEdit('events');
                        @endphp

                        {{-- Event Detail Modal --}}
                        <div x-show="showModal" x-cloak
                             class="fixed inset-0 z-[100] overflow-y-auto"
                             @keydown.escape.window="closeModal()">
                            {{-- Backdrop --}}
                            <div x-show="showModal"
                                 x-transition:enter="ease-out duration-300"
                                 x-transition:enter-start="opacity-0"
                                 x-transition:enter-end="opacity-100"
                                 x-transition:leave="ease-in duration-200"
                                 x-transition:leave-start="opacity-100"
                                 x-transition:leave-end="opacity-0"
                                 class="fixed inset-0 bg-black/50"
                                 @click="closeModal()"></div>

                            {{-- Modal Panel --}}
                            <div class="fixed inset-0 flex items-center justify-center p-4">
                                <div x-show="showModal"
                                     x-transition:enter="ease-out duration-300"
                                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                     x-transition:leave="ease-in duration-200"
                                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                     class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-5xl max-h-[90vh] flex flex-col"
                                     @click.stop>
                                    {{-- Header --}}
                                    <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white" x-text="modalEvent?.title"></h3>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                <span x-text="modalEvent?.date"></span> о <span x-text="modalEvent?.time"></span>
                                            </p>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <a :href="modalRoutes.eventUrl" class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400">
                                                {{ __('app.go_to_event') }}
                                            </a>
                                            <button @click="closeModal()" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Loading state --}}
                                    <div x-show="modalLoading" class="p-8 text-center">
                                        <svg class="animate-spin h-8 w-8 text-purple-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('app.loading_text') }}</p>
                                    </div>

                                    {{-- Content --}}
                                    <div x-show="!modalLoading" class="p-4 flex-1 overflow-y-auto overflow-x-hidden">
                                        <div class="flex flex-col lg:flex-row gap-4">
                                            {{-- Songs Section (Left) - only for worship ministries --}}
                                            <div x-show="isWorshipMinistry" class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 lg:w-2/5 overflow-hidden">
                                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2 mb-3">
                                                    <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                                                    </svg>
                                                    {{ __('app.songs_tab_label') }}
                                                </h4>

                                                <template x-if="modalSongs.length > 0">
                                                    <div class="space-y-1 mb-3">
                                                        <template x-for="(song, index) in modalSongs" :key="song.id">
                                                            <div @click="selectSong(song)"
                                                                 :class="selectedSongForTeam && selectedSongForTeam.id == song.id ? 'ring-2 ring-purple-500 bg-purple-50 dark:bg-purple-900/20' : 'bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700'"
                                                                 class="flex items-center gap-2 p-2 rounded-lg cursor-pointer group transition-all">
                                                                <span class="w-5 h-5 rounded-full bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 flex items-center justify-center text-xs font-medium" x-text="index + 1"></span>
                                                                <div class="flex-1 min-w-0">
                                                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate" x-text="song.title"></p>
                                                                    <div class="flex items-center gap-2">
                                                                        <template x-if="song.key">
                                                                            <span class="text-xs text-gray-500 dark:text-gray-400" x-text="song.key"></span>
                                                                        </template>
                                                                        <span class="text-xs text-blue-500" x-text="(new Set(song.team?.map(t => t.person_id) || [])).size + ' {{ __('app.ministry_participants_short') }}'"></span>
                                                                    </div>
                                                                </div>
                                                                @if($canEditEvent)
                                                                <button @click.stop="removeSong(song.id)" class="p-1 text-gray-400 hover:text-red-600 dark:hover:text-red-400 opacity-0 group-hover:opacity-100 transition-opacity">
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                                    </svg>
                                                                </button>
                                                                @endif
                                                            </div>
                                                        </template>
                                                    </div>
                                                </template>

                                                <template x-if="modalSongs.length === 0">
                                                    <div class="text-center py-4 mb-3">
                                                        <svg class="w-8 h-8 mx-auto text-gray-300 dark:text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                                                        </svg>
                                                        <p class="text-sm text-gray-400 dark:text-gray-500">{{ __('app.songs_not_added_yet') }}</p>
                                                    </div>
                                                </template>

                                                {{-- Add song form --}}
                                                @if($canEditEvent)
                                                <div class="border-t border-gray-200 dark:border-gray-600 pt-3">
                                                    <div class="flex gap-2 items-center">
                                                        <div class="flex-1 min-w-0">
                                                            <select x-model="selectedSongId"
                                                                    x-effect="updateSongSelect($el)"
                                                                    class="w-full px-2 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white truncate">
                                                            </select>
                                                        </div>
                                                        <input type="text" x-model="selectedKey" placeholder="{{ __('app.song_key_label') }}" class="w-14 shrink-0 px-2 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                                        <button @click="addSong()" :disabled="!selectedSongId" class="shrink-0 px-3 py-1.5 bg-purple-600 text-white text-sm rounded-lg hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                                            +
                                                        </button>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>

                                            {{-- Team Section (Right for worship, Full width for non-worship) --}}
                                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 lg:flex-1 flex flex-col">
                                                {{-- Worship: team per song --}}
                                                <template x-if="isWorshipMinistry && selectedSongForTeam">
                                                    <div class="flex flex-col h-full">
                                                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2 mb-3">
                                                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                            </svg>
                                                            {{ __('app.team_heading') }}: <span class="text-purple-600 dark:text-purple-400" x-text="selectedSongForTeam.title"></span>
                                                        </h4>

                                                        <div class="flex-1 overflow-y-auto mb-3">
                                                            <template x-if="selectedSongForTeam.team && selectedSongForTeam.team.length > 0">
                                                                <div class="space-y-2">
                                                                    <template x-for="member in selectedSongForTeam.team" :key="member.id">
                                                                        <div class="flex items-center justify-between p-2 bg-white dark:bg-gray-800 rounded-lg group">
                                                                            <div class="flex items-center gap-2">
                                                                                <span class="text-sm font-medium text-gray-900 dark:text-white" x-text="member.person_name"></span>
                                                                                <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300" x-text="member.role_name"></span>
                                                                            </div>
                                                                            @if($canEditEvent)
                                                                            <button @click="removeTeamMember(member.id)" class="p-1 text-gray-400 hover:text-red-600 dark:hover:text-red-400 opacity-0 group-hover:opacity-100 transition-opacity">
                                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                                                </svg>
                                                                            </button>
                                                                            @endif
                                                                        </div>
                                                                    </template>
                                                                </div>
                                                            </template>
                                                            <template x-if="!selectedSongForTeam.team || selectedSongForTeam.team.length === 0">
                                                                <div class="text-center py-6">
                                                                    <svg class="w-10 h-10 mx-auto text-gray-300 dark:text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                                    </svg>
                                                                    <p class="text-sm text-gray-400 dark:text-gray-500">{{ __('app.team_not_assigned_msg') }}</p>
                                                                </div>
                                                            </template>
                                                        </div>

                                                        @if($canEditEvent)
                                                        <template x-if="modalRoles.length > 0">
                                                            <div class="border-t border-gray-200 dark:border-gray-600 pt-3 mt-auto">
                                                                <div class="grid grid-cols-2 gap-2 mb-2">
                                                                    <select x-model="selectedPersonId"
                                                                            x-effect="updateMemberSelect($el)"
                                                                            class="px-2 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                                                    </select>
                                                                    <select x-model="selectedRoleId"
                                                                            x-effect="updateRoleSelect($el)"
                                                                            class="px-2 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                                                    </select>
                                                                </div>
                                                                <button @click="addTeamMember()" :disabled="!selectedPersonId || !selectedRoleId" class="w-full px-3 py-1.5 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                                                    {{ __('app.add_member_btn') }}
                                                                </button>
                                                            </div>
                                                        </template>
                                                        @endif
                                                    </div>
                                                </template>

                                                {{-- Worship: no song selected — show general team --}}
                                                <template x-if="isWorshipMinistry && !selectedSongForTeam">
                                                    <div class="flex flex-col h-full">
                                                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2 mb-3">
                                                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                            </svg>
                                                            {{ __('app.ministry_team_label') }}
                                                        </h4>
                                                        <div class="flex-1 overflow-y-auto mb-3">
                                                            <template x-if="modalGeneralTeam.length > 0">
                                                                <div class="space-y-2">
                                                                    <template x-for="member in modalGeneralTeam" :key="'wt-'+member.id">
                                                                        <div class="flex items-center justify-between p-2 bg-white dark:bg-gray-800 rounded-lg group">
                                                                            <div class="flex items-center gap-2">
                                                                                <span class="text-sm font-medium text-gray-900 dark:text-white" x-text="member.person_name"></span>
                                                                                <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300" x-text="member.role_name"></span>
                                                                            </div>
                                                                            @if($canEditEvent)
                                                                            <button @click="removeTeamMember(member.id)" class="p-1 text-gray-400 hover:text-red-600 dark:hover:text-red-400 opacity-0 group-hover:opacity-100 transition-opacity">
                                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                                                </svg>
                                                                            </button>
                                                                            @endif
                                                                        </div>
                                                                    </template>
                                                                </div>
                                                            </template>
                                                            <template x-if="modalGeneralTeam.length === 0">
                                                                <div class="flex flex-col items-center justify-center py-8 text-center">
                                                                    <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                                                                    </svg>
                                                                    <p class="text-sm text-gray-500 dark:text-gray-400">{!! __('app.ministry_select_song_hint') !!}</p>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </template>

                                                {{-- Non-worship: general team list --}}
                                                <template x-if="!isWorshipMinistry">
                                                    <div class="flex flex-col h-full">
                                                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2 mb-3">
                                                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                            </svg>
                                                            {{ __('app.ministry_team_label') }}
                                                        </h4>

                                                        <div class="flex-1 overflow-y-auto mb-3">
                                                            <template x-if="modalGeneralTeam.length > 0">
                                                                <div class="space-y-2">
                                                                    <template x-for="member in modalGeneralTeam" :key="member.id">
                                                                        <div class="flex items-center justify-between p-2 bg-white dark:bg-gray-800 rounded-lg group">
                                                                            <div class="flex items-center gap-2">
                                                                                <span class="text-sm font-medium text-gray-900 dark:text-white" x-text="member.person_name"></span>
                                                                                <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300" x-text="member.role_name"></span>
                                                                            </div>
                                                                            @if($canEditEvent)
                                                                            <button @click="removeTeamMember(member.id)" class="p-1 text-gray-400 hover:text-red-600 dark:hover:text-red-400 opacity-0 group-hover:opacity-100 transition-opacity">
                                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                                                </svg>
                                                                            </button>
                                                                            @endif
                                                                        </div>
                                                                    </template>
                                                                </div>
                                                            </template>
                                                            <template x-if="modalGeneralTeam.length === 0">
                                                                <div class="text-center py-6">
                                                                    <svg class="w-10 h-10 mx-auto text-gray-300 dark:text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                                    </svg>
                                                                    <p class="text-sm text-gray-400 dark:text-gray-500">{{ __('app.team_not_assigned_msg') }}</p>
                                                                </div>
                                                            </template>
                                                        </div>

                                                        @if($canEditEvent)
                                                        <template x-if="modalRoles.length > 0">
                                                            <div class="border-t border-gray-200 dark:border-gray-600 pt-3 mt-auto">
                                                                <div class="grid grid-cols-2 gap-2 mb-2">
                                                                    <select x-model="selectedPersonId"
                                                                            x-effect="updateMemberSelect($el)"
                                                                            class="px-2 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                                                    </select>
                                                                    <select x-model="selectedRoleId"
                                                                            x-effect="updateRoleSelect($el)"
                                                                            class="px-2 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                                                    </select>
                                                                </div>
                                                                <button @click="addGeneralTeamMember()" :disabled="!selectedPersonId || !selectedRoleId" class="w-full px-3 py-1.5 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                                                    {{ __('app.add_member_btn') }}
                                                                </button>
                                                            </div>
                                                        </template>
                                                        @endif
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                    </div>

                    @php
                        $calendarEventsData = $scheduleEvents->map(function($e) use ($ministry) {
                            return [
                                'id' => $e->id,
                                'title' => $e->title,
                                'date' => $e->date->format('Y-m-d'),
                                'day' => $e->date->format('d'),
                                'time' => $e->time?->format('H:i') ?? '',
                                'fullDate' => $e->date->translatedFormat('l, j M'),
                                'dataUrl' => route('ministries.worship-events.data', [$ministry, $e]),
                                'eventUrl' => route('events.show', $e),
                                'isSundayService' => $e->service_type === 'sunday_service',
                                'songsCount' => $e->songs_count ?? 0,
                                'teamCount' => $e->team_count ?? 0,
                                'isPast' => $e->date->isPast(),
                            ];
                        })->values();
                    @endphp
                    <script>
                        function worshipCalendar() {
                            return {
                                currentYear: new Date().getFullYear(),
                                currentMonth: new Date().getMonth(),
                                today: new Date(),
                                allEvents: @json($calendarEventsData),
                                isWorshipMinistry: {{ $ministry->is_worship_ministry ? 'true' : 'false' }},
                                ministryId: {{ $ministry->id }},
                                gridUrl: '{{ route('ministries.schedule-grid', $ministry) }}',
                                monthNames: @json([__('app.january'), __('app.february'), __('app.march'), __('app.april'), __('app.may'), __('app.june'), __('app.july'), __('app.august'), __('app.september'), __('app.october'), __('app.november'), __('app.december')]),

                                // Grid state
                                gridView: new URL(window.location).searchParams.get('view') === 'grid',
                                gridData: { events: [], roles: [], grid: {}, members: [], songs: {}, currentPersonId: null },
                                signupEvent: null,
                                gridLoading: false,
                                editingCell: null,
                                gridDropdown: { open: false, eventId: null, roleId: null, style: {} },

                                // Modal state
                                showModal: false,
                                modalLoading: false,
                                modalEvent: null,
                                modalSongs: [], // Each song has .team array
                                modalGeneralTeam: [], // Team not assigned to a song
                                modalRoles: [],
                                modalMembers: [],
                                modalAvailableSongs: [],
                                modalRoutes: {},

                                // Form state
                                selectedSongId: '',
                                selectedKey: '',
                                selectedPersonId: '',
                                selectedRoleId: '',
                                selectedSongForTeam: null, // Currently selected song for team editing

                                init() {
                                    if (this.gridView) this.loadGrid();
                                },

                                async openEventModal(event) {
                                    this.showModal = true;
                                    this.modalLoading = true;
                                    this.modalEvent = { title: event.title, date: event.fullDate, time: event.time };
                                    this.selectedSongForTeam = null;

                                    try {
                                        const response = await fetch(event.dataUrl);
                                        const data = await response.json().catch(() => ({}));

                                        this.modalEvent = data.event;
                                        this.modalSongs = data.songs || [];
                                        this.modalGeneralTeam = data.generalTeam || [];
                                        this.modalRoles = data.ministryRoles;
                                        this.modalMembers = data.members;
                                        this.modalAvailableSongs = data.availableSongs || [];
                                        this.modalRoutes = data.routes;

                                        // Auto-select first song if available (worship only)
                                        if (this.isWorshipMinistry && this.modalSongs.length > 0) {
                                            this.selectedSongForTeam = this.modalSongs[0];
                                        }
                                    } catch (error) {
                                        console.error('Error loading event:', error);
                                    }

                                    this.modalLoading = false;
                                },

                                closeModal() {
                                    this.showModal = false;
                                    this.modalEvent = null;
                                    this.selectedSongId = '';
                                    this.selectedKey = '';
                                    this.selectedPersonId = '';
                                    this.selectedRoleId = '';
                                    this.selectedSongForTeam = null;
                                    if (this.gridView) this.loadGrid();
                                },

                                selectSong(song) {
                                    this.selectedSongForTeam = song;
                                },

                                getSongTeamByRole(roleId) {
                                    if (!this.selectedSongForTeam || !this.selectedSongForTeam.team) return [];
                                    return this.selectedSongForTeam.team.filter(t => t.role_id == roleId);
                                },

                                updateSongSelect(el) {
                                    let html = '<option value="">{{ __('app.ministry_select_song_placeholder') }}</option>';
                                    this.modalAvailableSongs.filter(s => !s.inEvent).forEach(s => {
                                        html += '<option value="' + s.id + '">' + s.title + (s.key ? ' (' + s.key + ')' : '') + '</option>';
                                    });
                                    el.innerHTML = html;
                                },

                                updateMemberSelect(el) {
                                    let html = '<option value="">{{ __('app.ministry_member_placeholder') }}</option>';
                                    this.modalMembers.forEach(m => {
                                        html += '<option value="' + m.id + '">' + m.name + '</option>';
                                    });
                                    el.innerHTML = html;
                                },

                                updateRoleSelect(el) {
                                    let html = '<option value="">{{ __('app.ministry_role_placeholder') }}</option>';
                                    this.modalRoles.forEach(r => {
                                        html += '<option value="' + r.id + '">' + r.name + '</option>';
                                    });
                                    el.innerHTML = html;
                                },

                                async addSong() {
                                    if (!this.selectedSongId) return;

                                    const formData = new FormData();
                                    formData.append('song_id', this.selectedSongId);
                                    formData.append('key', this.selectedKey);

                                    try {
                                        const response = await fetch(this.modalRoutes.addSong, {
                                            method: 'POST',
                                            headers: {
                                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                                'Accept': 'application/json'
                                            },
                                            body: formData
                                        });

                                        if (response.ok) {
                                            const result = await response.json().catch(() => ({}));
                                            // Add to local list with empty team
                                            const song = this.modalAvailableSongs.find(s => s.id == this.selectedSongId);
                                            if (song) {
                                                const newSong = {
                                                    id: song.id,
                                                    event_song_id: result.event_song_id,
                                                    title: song.title,
                                                    key: this.selectedKey || song.key,
                                                    team: [] // Empty team for new song
                                                };
                                                this.modalSongs.push(newSong);
                                                song.inEvent = true;

                                                // Auto-select the new song
                                                this.selectedSongForTeam = newSong;
                                            }
                                            this.selectedSongId = '';
                                            this.selectedKey = '';

                                            // Update calendar event counts
                                            const evt = this.allEvents.find(e => e.id == this.modalEvent.id);
                                            if (evt) evt.songsCount++;
                                        }
                                    } catch (error) {
                                        console.error('Error adding song:', error);
                                    }
                                },

                                async removeSong(songId) {
                                    try {
                                        const response = await fetch(this.modalRoutes.addSong.replace('/songs', '/songs/' + songId), {
                                            method: 'DELETE',
                                            headers: {
                                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                                'Accept': 'application/json'
                                            }
                                        });

                                        if (response.ok) {
                                            // Clear selection if removing selected song
                                            if (this.selectedSongForTeam && this.selectedSongForTeam.id === songId) {
                                                this.selectedSongForTeam = null;
                                            }

                                            this.modalSongs = this.modalSongs.filter(s => s.id !== songId);
                                            const song = this.modalAvailableSongs.find(s => s.id == songId);
                                            if (song) song.inEvent = false;

                                            // Select first remaining song
                                            if (!this.selectedSongForTeam && this.modalSongs.length > 0) {
                                                this.selectedSongForTeam = this.modalSongs[0];
                                            }

                                            const evt = this.allEvents.find(e => e.id == this.modalEvent.id);
                                            if (evt && evt.songsCount > 0) evt.songsCount--;
                                        }
                                    } catch (error) {
                                        console.error('Error removing song:', error);
                                    }
                                },

                                async addTeamMember() {
                                    if (!this.selectedPersonId || !this.selectedRoleId || !this.selectedSongForTeam) return;

                                    const formData = new FormData();
                                    formData.append('person_id', this.selectedPersonId);
                                    formData.append('ministry_role_id', this.selectedRoleId);
                                    formData.append('ministry_id', this.ministryId);
                                    formData.append('event_song_id', this.selectedSongForTeam.event_song_id);

                                    try {
                                        const response = await fetch(this.modalRoutes.addTeam, {
                                            method: 'POST',
                                            headers: {
                                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                                'Accept': 'application/json'
                                            },
                                            body: formData
                                        });

                                        if (response.ok) {
                                            const person = this.modalMembers.find(m => m.id == this.selectedPersonId);
                                            const role = this.modalRoles.find(r => r.id == this.selectedRoleId);
                                            if (person && role) {
                                                const result = await response.json().catch(() => ({}));
                                                if (!this.selectedSongForTeam.team) {
                                                    this.selectedSongForTeam.team = [];
                                                }
                                                this.selectedSongForTeam.team.push({
                                                    id: result.id || Date.now(),
                                                    person_id: person.id,
                                                    person_name: person.name,
                                                    role_id: role.id,
                                                    role_name: role.name
                                                });

                                                const evt = this.allEvents.find(e => e.id == this.modalEvent.id);
                                                if (evt) evt.teamCount++;
                                            }
                                            this.selectedPersonId = '';
                                            this.selectedRoleId = '';
                                        }
                                    } catch (error) {
                                        console.error('Error adding team member:', error);
                                    }
                                },

                                async addGeneralTeamMember() {
                                    if (!this.selectedPersonId || !this.selectedRoleId) return;

                                    const formData = new FormData();
                                    formData.append('person_id', this.selectedPersonId);
                                    formData.append('ministry_role_id', this.selectedRoleId);
                                    formData.append('ministry_id', this.ministryId);

                                    try {
                                        const response = await fetch(this.modalRoutes.addTeam, {
                                            method: 'POST',
                                            headers: {
                                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                                'Accept': 'application/json'
                                            },
                                            body: formData
                                        });

                                        if (response.ok) {
                                            const person = this.modalMembers.find(m => m.id == this.selectedPersonId);
                                            const role = this.modalRoles.find(r => r.id == this.selectedRoleId);
                                            if (person && role) {
                                                const result = await response.json().catch(() => ({}));
                                                this.modalGeneralTeam.push({
                                                    id: result.id || Date.now(),
                                                    person_id: person.id,
                                                    person_name: person.name,
                                                    role_id: role.id,
                                                    role_name: role.name
                                                });

                                                const evt = this.allEvents.find(e => e.id == this.modalEvent.id);
                                                if (evt) evt.teamCount++;
                                            }
                                            this.selectedPersonId = '';
                                            this.selectedRoleId = '';
                                        }
                                    } catch (error) {
                                        console.error('Error adding team member:', error);
                                    }
                                },

                                async removeTeamMember(memberId) {
                                    try {
                                        const response = await fetch(this.modalRoutes.addTeam.replace('/worship-team', '/worship-team/' + memberId), {
                                            method: 'DELETE',
                                            headers: {
                                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                                'Accept': 'application/json'
                                            }
                                        });

                                        if (response.ok) {
                                            // Remove from song team or general team
                                            if (this.selectedSongForTeam && this.selectedSongForTeam.team) {
                                                this.selectedSongForTeam.team = this.selectedSongForTeam.team.filter(t => t.id !== memberId);
                                            }
                                            this.modalGeneralTeam = this.modalGeneralTeam.filter(t => t.id !== memberId);

                                            const evt = this.allEvents.find(e => e.id == this.modalEvent.id);
                                            if (evt && evt.teamCount > 0) evt.teamCount--;
                                        }
                                    } catch (error) {
                                        console.error('Error removing team member:', error);
                                    }
                                },

                                get monthYearLabel() {
                                    return this.monthNames[this.currentMonth] + ' ' + this.currentYear;
                                },

                                get calendarDays() {
                                    const days = [];
                                    const firstDay = new Date(this.currentYear, this.currentMonth, 1);
                                    const lastDay = new Date(this.currentYear, this.currentMonth + 1, 0);

                                    // Get day of week (0 = Sunday, adjust for Monday start)
                                    let startDay = firstDay.getDay();
                                    startDay = startDay === 0 ? 6 : startDay - 1;

                                    // Previous month days
                                    const prevLastDay = new Date(this.currentYear, this.currentMonth, 0).getDate();
                                    for (let i = startDay - 1; i >= 0; i--) {
                                        const d = prevLastDay - i;
                                        const dateStr = this.formatDate(this.currentYear, this.currentMonth - 1, d);
                                        days.push({
                                            date: d,
                                            isCurrentMonth: false,
                                            isToday: false,
                                            events: this.getEventsForDate(dateStr)
                                        });
                                    }

                                    // Current month days
                                    for (let d = 1; d <= lastDay.getDate(); d++) {
                                        const dateStr = this.formatDate(this.currentYear, this.currentMonth, d);
                                        const isToday = this.today.getFullYear() === this.currentYear &&
                                                       this.today.getMonth() === this.currentMonth &&
                                                       this.today.getDate() === d;
                                        days.push({
                                            date: d,
                                            isCurrentMonth: true,
                                            isToday: isToday,
                                            events: this.getEventsForDate(dateStr)
                                        });
                                    }

                                    // Next month days (fill to 42 cells = 6 rows)
                                    const remaining = 42 - days.length;
                                    for (let d = 1; d <= remaining; d++) {
                                        const dateStr = this.formatDate(this.currentYear, this.currentMonth + 1, d);
                                        days.push({
                                            date: d,
                                            isCurrentMonth: false,
                                            isToday: false,
                                            events: this.getEventsForDate(dateStr)
                                        });
                                    }

                                    return days;
                                },

                                get currentMonthEvents() {
                                    return this.allEvents.filter(e => {
                                        const d = new Date(e.date);
                                        return d.getFullYear() === this.currentYear && d.getMonth() === this.currentMonth;
                                    }).sort((a, b) => new Date(a.date) - new Date(b.date));
                                },

                                formatDate(year, month, day) {
                                    const y = month < 0 ? year - 1 : (month > 11 ? year + 1 : year);
                                    const m = month < 0 ? 11 : (month > 11 ? 0 : month);
                                    return `${y}-${String(m + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                                },

                                getEventsForDate(dateStr) {
                                    return this.allEvents.filter(e => e.date === dateStr);
                                },

                                prevMonth() {
                                    if (this.currentMonth === 0) {
                                        this.currentMonth = 11;
                                        this.currentYear--;
                                    } else {
                                        this.currentMonth--;
                                    }
                                    if (this.gridView) this.loadGrid();
                                },

                                nextMonth() {
                                    if (this.currentMonth === 11) {
                                        this.currentMonth = 0;
                                        this.currentYear++;
                                    } else {
                                        this.currentMonth++;
                                    }
                                    if (this.gridView) this.loadGrid();
                                },

                                goToToday() {
                                    this.currentYear = this.today.getFullYear();
                                    this.currentMonth = this.today.getMonth();
                                    if (this.gridView) this.loadGrid();
                                },

                                // Grid methods
                                openGridDropdown(e, eventId, roleId) {
                                    if (this.gridDropdown.open && this.gridDropdown.eventId === eventId && this.gridDropdown.roleId === roleId) {
                                        this.gridDropdown.open = false;
                                        return;
                                    }
                                    const rect = e.target.getBoundingClientRect();
                                    const spaceBelow = window.innerHeight - rect.bottom;
                                    const spaceRight = window.innerWidth - rect.left;
                                    const dh = Math.min(192, this.gridData.members.length * 30 + 8);
                                    const openUp = spaceBelow < dh + 8 && rect.top > dh;
                                    this.gridDropdown = {
                                        open: true,
                                        eventId,
                                        roleId,
                                        style: {
                                            left: (spaceRight < 200 ? rect.right - 192 : rect.left) + 'px',
                                            ...(openUp
                                                ? { bottom: (window.innerHeight - rect.top + 4) + 'px', top: 'auto' }
                                                : { top: (rect.bottom + 4) + 'px', bottom: 'auto' })
                                        }
                                    };
                                },

                                async loadGrid() {
                                    this.gridDropdown.open = false;
                                    this.gridLoading = true;
                                    try {
                                        const url = this.gridUrl + '?year=' + this.currentYear + '&month=' + (this.currentMonth + 1);
                                        const res = await fetch(url, {
                                            headers: { 'Accept': 'application/json' }
                                        });
                                        this.gridData = await res.json();
                                    } catch (error) {
                                        console.error('Error loading grid:', error);
                                    }
                                    this.gridLoading = false;
                                },

                                getGridCell(roleId, eventId) {
                                    return this.gridData.grid?.[roleId]?.[eventId] || [];
                                },

                                getEventRoles(eventId) {
                                    const result = [];
                                    const eId = String(eventId);
                                    for (const role of this.gridData.roles) {
                                        const rId = String(role.id);
                                        const members = this.gridData.grid?.[rId]?.[eId] || [];
                                        if (members.length > 0) {
                                            result.push({
                                                roleId: role.id,
                                                roleName: role.name,
                                                roleIcon: role.icon,
                                                members: members
                                            });
                                        }
                                    }
                                    return result;
                                },

                                getEventSongs(eventId) {
                                    return this.gridData.songs?.[String(eventId)] || [];
                                },

                                getEventTeamCount(eventId) {
                                    const eId = String(eventId);
                                    let count = 0;
                                    for (const rId in (this.gridData.grid || {})) {
                                        count += (this.gridData.grid[rId]?.[eId] || []).length;
                                    }
                                    return count;
                                },

                                isCurrentMember() {
                                    if (!this.gridData.currentPersonId) return false;
                                    return this.gridData.members.some(m => m.id === this.gridData.currentPersonId);
                                },

                                isSignedUp(eventId) {
                                    const eId = String(eventId);
                                    const pid = this.gridData.currentPersonId;
                                    if (!pid) return true;
                                    for (const rId in (this.gridData.grid || {})) {
                                        const members = this.gridData.grid[rId]?.[eId] || [];
                                        if (members.some(m => m.person_id == pid)) return true;
                                    }
                                    return false;
                                },

                                async selfSignup(eventId, roleId) {
                                    await this.gridAssign(eventId, roleId, this.gridData.currentPersonId);
                                    this.signupEvent = null;
                                },

                                async gridAssign(eventId, roleId, personId) {
                                    const formData = new FormData();
                                    formData.append('person_id', personId);
                                    formData.append('ministry_role_id', roleId);
                                    formData.append('ministry_id', this.ministryId);

                                    try {
                                        const response = await fetch('/events/' + eventId + '/ministry-team', {
                                            method: 'POST',
                                            headers: {
                                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                                'Accept': 'application/json'
                                            },
                                            body: formData
                                        });

                                        if (response.ok) {
                                            const result = await response.json().catch(() => ({}));
                                            const member = this.gridData.members.find(m => m.id == personId);
                                            const rId = String(roleId);
                                            const eId = String(eventId);

                                            if (!this.gridData.grid[rId]) this.gridData.grid[rId] = {};
                                            if (!this.gridData.grid[rId][eId]) this.gridData.grid[rId][eId] = [];

                                            const firstName = member ? member.name.split(' ')[0] : '?';
                                            const lastName = member ? member.name.split(' ').slice(1).join(' ') : '';
                                            const shortName = firstName + (lastName ? ' ' + lastName.charAt(0) + '.' : '');

                                            this.gridData.grid[rId][eId].push({
                                                id: result.id,
                                                person_id: personId,
                                                person_name: shortName,
                                                status: null,
                                                has_telegram: member ? member.has_telegram : false
                                            });

                                            // Force reactivity
                                            this.gridData = { ...this.gridData, grid: { ...this.gridData.grid } };
                                        } else {
                                            const err = await response.json().catch(() => ({}));
                                            if (err.error) alert(err.error);
                                        }
                                    } catch (error) {
                                        console.error('Error assigning:', error);
                                    }
                                    this.gridDropdown.open = false;
                                },

                                async gridRemove(eventId, memberId, roleId) {
                                    try {
                                        const response = await fetch('/events/' + eventId + '/ministry-team/' + memberId, {
                                            method: 'DELETE',
                                            headers: {
                                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                                'Accept': 'application/json'
                                            }
                                        });

                                        if (response.ok) {
                                            const rId = String(roleId);
                                            const eId = String(eventId);
                                            if (this.gridData.grid[rId] && this.gridData.grid[rId][eId]) {
                                                this.gridData.grid[rId][eId] = this.gridData.grid[rId][eId].filter(m => m.id !== memberId);
                                                // Force reactivity
                                                this.gridData = { ...this.gridData, grid: { ...this.gridData.grid } };
                                            }
                                        }
                                    } catch (error) {
                                        console.error('Error removing:', error);
                                    }
                                },

                                async gridNotify(eventId, memberId, roleId) {
                                    try {
                                        const response = await fetch('/events/' + eventId + '/ministry-team/' + memberId + '/notify', {
                                            method: 'POST',
                                            headers: {
                                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                                'Accept': 'application/json'
                                            }
                                        });

                                        const result = await response.json().catch(() => ({}));
                                        if (response.ok && result.success) {
                                            // Update status to pending
                                            const rId = String(roleId);
                                            const eId = String(eventId);
                                            if (this.gridData.grid[rId]?.[eId]) {
                                                const m = this.gridData.grid[rId][eId].find(m => m.id === memberId);
                                                if (m) m.status = 'pending';
                                                this.gridData = { ...this.gridData, grid: { ...this.gridData.grid } };
                                            }
                                        } else {
                                            alert(result.message || '{{ __('app.ministry_error_fallback') }}');
                                        }
                                    } catch (error) {
                                        console.error('Error sending notification:', error);
                                    }
                                }
                            };
                        }
                    </script>
            </div>

            <div x-show="activeTab === 'members'"{{ $tab !== 'members' ? ' style="display:none"' : '' }}
                 x-data="{ membersView: localStorage.getItem('ministry_members_view') || 'grid' }"
                 x-init="$watch('membersView', v => localStorage.setItem('ministry_members_view', v))">
                @php
                    $leader = $ministry->leader;
                    $sortedMembers = $ministry->members->sortBy(function ($m) use ($ministry) {
                        if ($m->id === $ministry->leader_id) return 0;
                        if ($m->pivot->role === 'co-leader') return 1;
                        return 2;
                    });
                    $positions = $ministry->positions->keyBy('id');
                @endphp

                <!-- Add member form + View switcher -->
                <div class="flex items-center justify-between mb-4 gap-3 flex-wrap">
                    @can('contribute-ministry', $ministry)
                    @if($availablePeople->count() > 0)
                    <form @submit.prevent="submit($refs.addMemberForm)" x-ref="addMemberForm"
                          x-data="{ ...ajaxForm({ url: '{{ route('ministries.members.add', $ministry) }}', method: 'POST', onSuccess: () => window.location.reload(), stayOnPage: true }) }"
                          class="flex-1 min-w-0">
                        <div class="flex gap-2">
                            <div class="flex-1">
                                <x-person-select name="person_id" :people="$availablePeople" placeholder="{{ __('app.ministry_add_member_placeholder') }}" :required="true" :nullable="false" />
                            </div>
                            <button type="submit" :disabled="saving" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg whitespace-nowrap text-sm">
                                {{ __('app.ministry_add_btn') }}
                            </button>
                        </div>
                    </form>
                    @else
                    <div></div>
                    @endif
                    @else
                    <div></div>
                    @endcan

                    {{-- View switcher --}}
                    <div class="inline-flex items-center bg-gray-100 dark:bg-gray-700 rounded-lg p-0.5">
                        <button @click="membersView = 'grid'" type="button"
                                :class="membersView === 'grid' ? 'bg-white dark:bg-gray-600 shadow-sm text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                                class="p-1.5 rounded-md transition-all" title="{{ __('app.ministry_view_grid') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        </button>
                        <button @click="membersView = 'list'" type="button"
                                :class="membersView === 'list' ? 'bg-white dark:bg-gray-600 shadow-sm text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                                class="p-1.5 rounded-md transition-all" title="{{ __('app.ministry_view_list') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>
                        <button @click="membersView = 'compact'" type="button"
                                :class="membersView === 'compact' ? 'bg-white dark:bg-gray-600 shadow-sm text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                                class="p-1.5 rounded-md transition-all" title="{{ __('app.ministry_view_compact') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        </button>
                    </div>
                </div>

                @if($sortedMembers->count() > 0)

                {{-- ===== GRID VIEW ===== --}}
                <div x-show="membersView === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                    @foreach($sortedMembers as $member)
                        @php
                            $isLeader = $member->id === $ministry->leader_id;
                            $pivotRole = $member->pivot->role ?? 'member';
                            $memberPositionIds = json_decode($member->pivot->position_ids ?? '[]', true) ?: [];
                            $memberPositions = collect($memberPositionIds)->map(fn($id) => $positions[$id]->name ?? null)->filter();
                            $positionText = $member->pivot->position;
                        @endphp
                        <div class="relative bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl hover:shadow-md transition-shadow group"
                             x-data="{
                                 role: '{{ $isLeader ? 'leader' : $pivotRole }}',
                                 open: false,
                                 saving: false,
                                 async setRole(newRole) {
                                     this.saving = true;
                                     try {
                                         const res = await fetch('{{ route('ministries.members.role', [$ministry, $member]) }}', {
                                             method: 'PATCH',
                                             headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
                                             body: JSON.stringify({ role: newRole })
                                         });
                                         if (res.ok) {
                                             this.role = newRole;
                                             if (newRole === 'leader') setTimeout(() => location.reload(), 300);
                                         }
                                     } catch (e) { console.error(e); }
                                     this.saving = false;
                                     this.open = false;
                                 },
                                 get accentClass() {
                                     if (this.role === 'leader') return 'bg-gradient-to-r from-amber-400 to-amber-500';
                                     if (this.role === 'co-leader') return 'bg-gradient-to-r from-primary-400 to-primary-500';
                                     return '';
                                 }
                             }">
                            {{-- Top accent --}}
                            <div class="h-1 rounded-t-xl transition-all" :class="accentClass"></div>

                            <div class="p-4">
                                <div class="flex items-start gap-3">
                                    {{-- Avatar --}}
                                    <a href="{{ route('people.show', $member) }}" class="shrink-0" x-data="{ imgErr: false }">
                                        @if($member->photo)
                                        <img x-show="!imgErr" x-on:error="imgErr = true" src="{{ Storage::url($member->photo) }}" alt="" class="w-12 h-12 rounded-full object-cover ring-2 ring-gray-100 dark:ring-gray-700" loading="lazy">
                                        <div x-show="imgErr" x-cloak class="w-12 h-12 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center ring-2 ring-gray-100 dark:ring-gray-700">
                                            <span class="text-white text-sm font-semibold">{{ mb_substr($member->first_name, 0, 1) }}{{ mb_substr($member->last_name, 0, 1) }}</span>
                                        </div>
                                        @else
                                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center ring-2 ring-gray-100 dark:ring-gray-700">
                                            <span class="text-white text-sm font-semibold">{{ mb_substr($member->first_name, 0, 1) }}{{ mb_substr($member->last_name, 0, 1) }}</span>
                                        </div>
                                        @endif
                                    </a>

                                    {{-- Info --}}
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <a href="{{ route('people.show', $member) }}" class="font-semibold text-gray-900 dark:text-white text-sm hover:text-primary-600 dark:hover:text-primary-400 truncate">
                                                {{ $member->full_name }}
                                            </a>
                                            {{-- Role badge --}}
                                            @can('manage-ministry', $ministry)
                                            <div class="relative">
                                                <button type="button" @click="open = !open"
                                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium transition-colors"
                                                        :class="{
                                                            'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400 hover:bg-amber-200 dark:hover:bg-amber-900/50': role === 'leader',
                                                            'bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-400 hover:bg-primary-200 dark:hover:bg-primary-900/50': role === 'co-leader',
                                                            'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-600': role === 'member'
                                                        }">
                                                    <span x-text="role === 'leader' ? '{{ __('app.ministry_role_leader') }}' : (role === 'co-leader' ? '{{ __('app.ministry_role_co_leader') }}' : '{{ __('app.ministry_role_member') }}')"></span>
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                                </button>
                                                <div x-show="open" @click.away="open = false" x-transition
                                                     class="absolute left-0 mt-1 w-36 bg-white dark:bg-gray-700 rounded-lg shadow-lg z-20 border border-gray-200 dark:border-gray-600 py-1">
                                                    <button type="button" @click="setRole('leader')" :disabled="saving"
                                                            class="w-full text-left px-3 py-1.5 text-xs hover:bg-gray-50 dark:hover:bg-gray-600 flex items-center gap-2"
                                                            :class="role === 'leader' ? 'text-amber-700 dark:text-amber-400 font-semibold' : 'text-gray-700 dark:text-gray-300'">
                                                        <span class="w-2 h-2 rounded-full bg-amber-400"></span> {{ __('app.ministry_role_leader') }}
                                                    </button>
                                                    <button type="button" @click="setRole('co-leader')" :disabled="saving"
                                                            class="w-full text-left px-3 py-1.5 text-xs hover:bg-gray-50 dark:hover:bg-gray-600 flex items-center gap-2"
                                                            :class="role === 'co-leader' ? 'text-primary-700 dark:text-primary-400 font-semibold' : 'text-gray-700 dark:text-gray-300'">
                                                        <span class="w-2 h-2 rounded-full bg-primary-400"></span> {{ __('app.ministry_role_co_leader') }}
                                                    </button>
                                                    <button type="button" @click="setRole('member')" :disabled="saving"
                                                            class="w-full text-left px-3 py-1.5 text-xs hover:bg-gray-50 dark:hover:bg-gray-600 flex items-center gap-2"
                                                            :class="role === 'member' ? 'text-gray-900 dark:text-white font-semibold' : 'text-gray-700 dark:text-gray-300'">
                                                        <span class="w-2 h-2 rounded-full bg-gray-400"></span> {{ __('app.ministry_role_member') }}
                                                    </button>
                                                </div>
                                            </div>
                                            @else
                                            <template x-if="role === 'leader'">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">{{ __('app.ministry_role_leader') }}</span>
                                            </template>
                                            <template x-if="role === 'co-leader'">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-400">{{ __('app.ministry_role_co_leader') }}</span>
                                            </template>
                                            @endcan
                                        </div>

                                        {{-- Positions --}}
                                        @if($memberPositions->isNotEmpty() || $positionText)
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 truncate">
                                            {{ $memberPositions->isNotEmpty() ? $memberPositions->implode(', ') : $positionText }}
                                        </p>
                                        @endif

                                        {{-- Contact --}}
                                        <div class="flex items-center gap-3 mt-2">
                                            @if($member->phone)
                                            <a href="tel:{{ $member->phone }}" class="inline-flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400" title="{{ $member->phone }}">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                                <span class="hidden sm:inline">{{ $member->phone }}</span>
                                            </a>
                                            @endif
                                            @if($member->telegram_username)
                                            <a href="https://t.me/{{ ltrim($member->telegram_username, '@') }}" target="_blank" class="inline-flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400 hover:text-blue-500" title="Telegram">
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.479.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                                            </a>
                                            @endif
                                            @if($member->email)
                                            <a href="mailto:{{ $member->email }}" class="inline-flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400" title="{{ $member->email }}">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                            </a>
                                            @endif
                                            {{-- Status indicators --}}
                                            @if($member->user_id || $member->telegram_chat_id)
                                            <span class="w-px h-3 bg-gray-200 dark:bg-gray-600"></span>
                                            @endif
                                            @if($member->user_id)
                                            <span class="text-emerald-500 dark:text-emerald-400" title="{{ __('app.ministry_user_ministrify') }}">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                            </span>
                                            @endif
                                            @if($member->telegram_chat_id)
                                            <span class="text-emerald-500 dark:text-emerald-400" title="{{ __('app.ministry_telegram_connected') }}">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                            </span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Remove button --}}
                                    @can('contribute-ministry', $ministry)
                                    <button @click="ajaxDelete('{{ route('ministries.members.remove', [$ministry, $member]) }}', '{{ __('messages.confirm_remove_team_member') }}', () => $el.closest('.group').remove())"
                                            class="shrink-0 p-1.5 text-gray-300 dark:text-gray-600 hover:text-red-500 dark:hover:text-red-400 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- ===== LIST VIEW ===== --}}
                <div x-show="membersView === 'list'" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($sortedMembers as $member)
                            @php
                                $isLeader = $member->id === $ministry->leader_id;
                                $pivotRole = $member->pivot->role ?? 'member';
                                $memberPositionIds = json_decode($member->pivot->position_ids ?? '[]', true) ?: [];
                                $memberPositions = collect($memberPositionIds)->map(fn($id) => $positions[$id]->name ?? null)->filter();
                                $positionText = $member->pivot->position;
                            @endphp
                            <div class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group"
                                 x-data="{
                                     role: '{{ $isLeader ? 'leader' : $pivotRole }}',
                                     open: false,
                                     saving: false,
                                     async setRole(newRole) {
                                         this.saving = true;
                                         try {
                                             const res = await fetch('{{ route('ministries.members.role', [$ministry, $member]) }}', {
                                                 method: 'PATCH',
                                                 headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
                                                 body: JSON.stringify({ role: newRole })
                                             });
                                             if (res.ok) {
                                                 this.role = newRole;
                                                 if (newRole === 'leader') setTimeout(() => location.reload(), 300);
                                             }
                                         } catch (e) { console.error(e); }
                                         this.saving = false;
                                         this.open = false;
                                     }
                                 }">
                                {{-- Avatar --}}
                                <a href="{{ route('people.show', $member) }}" class="shrink-0" x-data="{ imgErr: false }">
                                    @if($member->photo)
                                    <img x-show="!imgErr" x-on:error="imgErr = true" src="{{ Storage::url($member->photo) }}" alt="" class="w-10 h-10 rounded-full object-cover" loading="lazy">
                                    <div x-show="imgErr" x-cloak class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center">
                                        <span class="text-white text-sm font-semibold">{{ mb_substr($member->first_name, 0, 1) }}{{ mb_substr($member->last_name, 0, 1) }}</span>
                                    </div>
                                    @else
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center">
                                        <span class="text-white text-sm font-semibold">{{ mb_substr($member->first_name, 0, 1) }}{{ mb_substr($member->last_name, 0, 1) }}</span>
                                    </div>
                                    @endif
                                </a>

                                {{-- Name + role --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('people.show', $member) }}" class="font-medium text-sm text-gray-900 dark:text-white hover:text-primary-600 dark:hover:text-primary-400 truncate">
                                            {{ $member->full_name }}
                                        </a>
                                        @can('manage-ministry', $ministry)
                                        <div class="relative">
                                            <button type="button" @click="open = !open"
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium transition-colors"
                                                    :class="{
                                                        'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400': role === 'leader',
                                                        'bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-400': role === 'co-leader',
                                                        'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400': role === 'member'
                                                    }">
                                                <span x-text="role === 'leader' ? '{{ __('app.ministry_role_leader') }}' : (role === 'co-leader' ? '{{ __('app.ministry_role_co_leader') }}' : '{{ __('app.ministry_role_member') }}')"></span>
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                            </button>
                                            <div x-show="open" @click.away="open = false" x-transition
                                                 class="absolute left-0 mt-1 w-36 bg-white dark:bg-gray-700 rounded-lg shadow-lg z-20 border border-gray-200 dark:border-gray-600 py-1">
                                                <button type="button" @click="setRole('leader')" :disabled="saving" class="w-full text-left px-3 py-1.5 text-xs hover:bg-gray-50 dark:hover:bg-gray-600 flex items-center gap-2" :class="role === 'leader' ? 'text-amber-700 dark:text-amber-400 font-semibold' : 'text-gray-700 dark:text-gray-300'">
                                                    <span class="w-2 h-2 rounded-full bg-amber-400"></span> {{ __('app.ministry_role_leader') }}
                                                </button>
                                                <button type="button" @click="setRole('co-leader')" :disabled="saving" class="w-full text-left px-3 py-1.5 text-xs hover:bg-gray-50 dark:hover:bg-gray-600 flex items-center gap-2" :class="role === 'co-leader' ? 'text-primary-700 dark:text-primary-400 font-semibold' : 'text-gray-700 dark:text-gray-300'">
                                                    <span class="w-2 h-2 rounded-full bg-primary-400"></span> {{ __('app.ministry_role_co_leader') }}
                                                </button>
                                                <button type="button" @click="setRole('member')" :disabled="saving" class="w-full text-left px-3 py-1.5 text-xs hover:bg-gray-50 dark:hover:bg-gray-600 flex items-center gap-2" :class="role === 'member' ? 'text-gray-900 dark:text-white font-semibold' : 'text-gray-700 dark:text-gray-300'">
                                                    <span class="w-2 h-2 rounded-full bg-gray-400"></span> {{ __('app.ministry_role_member') }}
                                                </button>
                                            </div>
                                        </div>
                                        @else
                                        <template x-if="role === 'leader'">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">{{ __('app.ministry_role_leader') }}</span>
                                        </template>
                                        <template x-if="role === 'co-leader'">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-400">{{ __('app.ministry_role_co_leader') }}</span>
                                        </template>
                                        @endcan
                                    </div>
                                    @if($memberPositions->isNotEmpty() || $positionText)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                        {{ $memberPositions->isNotEmpty() ? $memberPositions->implode(', ') : $positionText }}
                                    </p>
                                    @endif
                                </div>

                                {{-- Contact icons --}}
                                <div class="hidden sm:flex items-center gap-2 shrink-0">
                                    @if($member->phone)
                                    <a href="tel:{{ $member->phone }}" class="text-gray-400 hover:text-primary-600 dark:hover:text-primary-400" title="{{ $member->phone }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    </a>
                                    @endif
                                    @if($member->telegram_username)
                                    <a href="https://t.me/{{ ltrim($member->telegram_username, '@') }}" target="_blank" class="text-gray-400 hover:text-blue-500" title="Telegram">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.479.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                                    </a>
                                    @endif
                                    @if($member->email)
                                    <a href="mailto:{{ $member->email }}" class="text-gray-400 hover:text-primary-600 dark:hover:text-primary-400" title="{{ $member->email }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    </a>
                                    @endif
                                    {{-- Status indicators --}}
                                    @if($member->user_id || $member->telegram_chat_id)
                                    <span class="w-px h-3.5 bg-gray-200 dark:bg-gray-600"></span>
                                    @endif
                                    @if($member->user_id)
                                    <span class="text-emerald-500 dark:text-emerald-400" title="{{ __('app.ministry_user_ministrify') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    </span>
                                    @endif
                                    @if($member->telegram_chat_id)
                                    <span class="text-emerald-500 dark:text-emerald-400" title="{{ __('app.ministry_telegram_connected') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                    </span>
                                    @endif
                                </div>

                                {{-- Phone text (mobile only) --}}
                                <span class="sm:hidden text-xs text-gray-400 shrink-0">{{ $member->phone }}</span>

                                {{-- Remove button --}}
                                @can('contribute-ministry', $ministry)
                                <button @click="ajaxDelete('{{ route('ministries.members.remove', [$ministry, $member]) }}', '{{ __('messages.confirm_remove_team_member') }}', () => $el.closest('.group').remove())"
                                        class="shrink-0 p-1.5 text-gray-300 dark:text-gray-600 hover:text-red-500 dark:hover:text-red-400 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                                @endcan
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- ===== COMPACT VIEW ===== --}}
                <div x-show="membersView === 'compact'" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                    <div class="divide-y divide-gray-100 dark:divide-gray-700/50">
                        @foreach($sortedMembers as $member)
                            @php
                                $isLeader = $member->id === $ministry->leader_id;
                                $pivotRole = $member->pivot->role ?? 'member';
                            @endphp
                            <div class="flex items-center gap-2 px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group"
                                 x-data="{ role: '{{ $isLeader ? 'leader' : $pivotRole }}' }">
                                {{-- Color dot for role --}}
                                <span class="w-2 h-2 rounded-full shrink-0"
                                      :class="role === 'leader' ? 'bg-amber-400' : (role === 'co-leader' ? 'bg-primary-400' : 'bg-gray-300 dark:bg-gray-600')"></span>

                                <a href="{{ route('people.show', $member) }}" class="text-sm text-gray-900 dark:text-white hover:text-primary-600 dark:hover:text-primary-400 truncate">
                                    {{ $member->full_name }}
                                </a>

                                <span class="text-xs text-gray-400 dark:text-gray-500 shrink-0"
                                      x-text="role === 'leader' ? '{{ __('app.ministry_role_leader') }}' : (role === 'co-leader' ? '{{ __('app.ministry_role_co_leader') }}' : '')"></span>

                                <span class="flex-1"></span>

                                @if($member->phone)
                                <span class="hidden sm:inline text-xs text-gray-400">{{ $member->phone }}</span>
                                @endif
                                @if($member->user_id)
                                <span class="hidden sm:inline text-emerald-500 dark:text-emerald-400" title="{{ __('app.ministry_user_ministrify') }}">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                </span>
                                @endif
                                @if($member->telegram_chat_id)
                                <span class="hidden sm:inline text-emerald-500 dark:text-emerald-400" title="{{ __('app.ministry_telegram_connected') }}">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                </span>
                                @endif

                                {{-- Remove button --}}
                                @can('contribute-ministry', $ministry)
                                <button @click="ajaxDelete('{{ route('ministries.members.remove', [$ministry, $member]) }}', '{{ __('messages.confirm_remove_team_member') }}', () => $el.closest('.group').remove())"
                                        class="shrink-0 p-1 text-gray-300 dark:text-gray-600 hover:text-red-500 dark:hover:text-red-400 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                                @endcan
                            </div>
                        @endforeach
                    </div>
                </div>

                @else
                <div class="text-center py-10">
                    <svg class="mx-auto w-12 h-12 text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">{{ __('app.ministry_no_members') }}</p>
                </div>
                @endif
            </div>

            <div x-show="activeTab === 'expenses'"{{ $tab !== 'expenses' ? ' style="display:none"' : '' }}
                 x-data="budgetPage()"
                 x-init="loadBudget()">
                {{-- ===== BUDGET PLANNING SECTION (fully dynamic via Alpine.js) ===== --}}
                <div class="mb-6">
                    {{-- Budget header with unified month navigation --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <h3 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                    <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                    {{ __('app.ministry_budget_label') }}
                                </h3>
                                <div class="flex items-center gap-2">
                                    <button @click="prevMonth()" class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                    </button>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300 min-w-[120px] text-center" x-text="monthNames[currentMonth] + ' ' + currentYear"></span>
                                    <button @click="nextMonth()" class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </button>
                                </div>
                            </div>
                            {{-- Summary line --}}
                            <div x-show="budget.total_allocated > 0 || budget.total_income > 0 || budget.total_spent > 0 || budget.has_items" class="mt-2 flex flex-wrap items-center gap-3 text-sm">
                                <span class="text-green-600 dark:text-green-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
                                    {{ __('app.ministry_received') }} <span x-text="fmt((budget.total_allocated || 0) + (budget.total_income || 0)) + ' ₴'"></span>
                                </span>
                                <span class="text-gray-300 dark:text-gray-600">|</span>
                                <span class="text-red-500 dark:text-red-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg>
                                    {{ __('app.ministry_spent') }} <span x-text="fmt(budget.total_spent) + ' ₴'"></span>
                                </span>
                                <span class="text-gray-300 dark:text-gray-600">|</span>
                                <span class="font-medium px-2 py-0.5 rounded-full text-xs"
                                      :class="balance >= 0 ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400'">
                                    <span x-text="'{{ __('app.ministry_balance') }} ' + (balance >= 0 ? '+' : '') + fmt(balance) + ' ₴'"></span>
                                </span>
                                @can('contribute-ministry', $ministry)
                                <button x-show="budget.has_items" @click="copyBudget()" class="ml-auto text-xs text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 flex items-center gap-1" title="{{ __('app.ministry_copy_next_month') }}">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    {{ __('app.ministry_copy_next_month') }}
                                </button>
                                @endcan
                            </div>
                        </div>

                        {{-- Progress bar --}}
                        <div x-show="budget.has_items && budget.effective_budget > 0" class="px-4 py-2 bg-gray-50 dark:bg-gray-700/20">
                            <div class="flex items-center gap-3">
                                <div class="flex-1 bg-gray-200 dark:bg-gray-600 rounded-full h-2.5">
                                    <div class="h-2.5 rounded-full transition-all"
                                         :class="budgetPct > 100 ? 'bg-red-500' : (budgetPct > 80 ? 'bg-orange-500' : 'bg-green-500')"
                                         :style="'width: ' + Math.min(100, budgetPct) + '%'"></div>
                                </div>
                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 whitespace-nowrap" x-text="budgetPct + '%'"></span>
                            </div>
                        </div>

                        {{-- Loading state --}}
                        <div x-show="budgetLoading" class="px-4 py-8 text-center">
                            <svg class="animate-spin h-6 w-6 text-primary-500 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>

                        {{-- Items table (dynamic) --}}
                        <div x-show="budget.has_items && !budgetLoading" class="px-4 py-3">
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="text-xs text-gray-500 dark:text-gray-400 uppercase border-b border-gray-200 dark:border-gray-600">
                                            <th class="px-2 py-2 text-left">{{ __('app.ministry_item_name') }}</th>
                                            <th class="px-2 py-2 text-center hidden sm:table-cell">{{ __('app.ministry_date_col') }}</th>
                                            <th class="px-2 py-2 text-right">{{ __('app.ministry_plan') }}</th>
                                            <th class="px-2 py-2 text-right">{{ __('app.ministry_fact') }}</th>
                                            <th class="px-2 py-2 text-right">{{ __('app.ministry_difference') }}</th>
                                            <th class="px-2 py-2 text-left hidden sm:table-cell">{{ __('app.ministry_responsible') }}</th>
                                            @can('contribute-ministry', $ministry)
                                            <th class="px-2 py-2 text-center w-16"></th>
                                            @endcan
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                                        <template x-for="bi in budget.items" :key="bi.id">
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 group">
                                                <td class="px-2 py-2.5">
                                                    <div class="font-medium text-gray-900 dark:text-white" x-text="bi.name"></div>
                                                    <div x-show="bi.category_name" class="text-xs text-gray-400" x-text="(bi.category_icon || '') + ' ' + (bi.category_name || '')"></div>
                                                </td>
                                                <td class="px-2 py-2.5 text-center hidden sm:table-cell whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                                                    <span x-show="bi.planned_date" x-text="bi.planned_date ? new Date(bi.planned_date).toLocaleDateString('uk-UA', {day: '2-digit', month: '2-digit'}) : ''"></span>
                                                </td>
                                                <td class="px-2 py-2.5 text-right whitespace-nowrap text-gray-600 dark:text-gray-300" x-text="fmt(bi.planned_amount) + ' ₴'"></td>
                                                <td class="px-2 py-2.5 text-right whitespace-nowrap font-medium text-gray-900 dark:text-white" x-text="fmt(bi.actual) + ' ₴'"></td>
                                                <td class="px-2 py-2.5 text-right whitespace-nowrap">
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium"
                                                          :class="bi.difference >= 0 ? 'text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-900/20' : 'text-red-700 dark:text-red-400 bg-red-50 dark:bg-red-900/20'"
                                                          x-text="(bi.difference >= 0 ? '+' : '') + fmt(bi.difference) + ' ₴'"></span>
                                                </td>
                                                <td class="px-2 py-2.5 hidden sm:table-cell">
                                                    <div class="flex flex-wrap gap-1">
                                                        <template x-for="person in bi.responsible" :key="person.id">
                                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400" x-text="person.name"></span>
                                                        </template>
                                                    </div>
                                                </td>
                                                @can('contribute-ministry', $ministry)
                                                <td class="px-2 py-2.5 text-center">
                                                    <div class="flex items-center justify-center gap-0.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                                        <button @click="openItemModal('edit', bi)"
                                                                class="p-1.5 text-gray-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-lg" title="{{ __('app.ministry_edit_title') }}">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                            </svg>
                                                        </button>
                                                        <button @click="deleteItem(bi.id, bi.name)"
                                                                class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg" title="{{ __('app.ministry_delete_title') }}">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </td>
                                                @endcan
                                            </tr>
                                        </template>

                                        <tr x-show="budget.unmatched_spent > 0" class="bg-orange-50/50 dark:bg-orange-900/10">
                                            <td class="px-2 py-2.5 text-gray-500 italic">{{ __('app.ministry_other_expenses') }}</td>
                                            <td class="px-2 py-2.5 hidden sm:table-cell"></td>
                                            <td class="px-2 py-2.5 text-right text-gray-400">—</td>
                                            <td class="px-2 py-2.5 text-right font-medium text-gray-900 dark:text-white" x-text="fmt(budget.unmatched_spent) + ' ₴'"></td>
                                            <td class="px-2 py-2.5"></td>
                                            <td class="px-2 py-2.5 hidden sm:table-cell"></td>
                                            @can('contribute-ministry', $ministry)<td></td>@endcan
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr class="border-t-2 border-gray-200 dark:border-gray-600 font-semibold text-sm">
                                            <td class="px-2 py-2.5 text-gray-900 dark:text-white">{{ __('app.ministry_total') }}</td>
                                            <td class="px-2 py-2.5 hidden sm:table-cell"></td>
                                            <td class="px-2 py-2.5 text-right text-gray-900 dark:text-white" x-text="fmt(totalPlanned) + ' ₴'"></td>
                                            <td class="px-2 py-2.5 text-right text-gray-900 dark:text-white" x-text="fmt(budget.total_spent) + ' ₴'"></td>
                                            <td class="px-2 py-2.5 text-right">
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-semibold"
                                                      :class="budgetRemaining >= 0 ? 'text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-900/20' : 'text-red-700 dark:text-red-400 bg-red-50 dark:bg-red-900/20'"
                                                      x-text="(budgetRemaining >= 0 ? '+' : '') + fmt(budgetRemaining) + ' ₴'"></span>
                                            </td>
                                            <td class="px-2 py-2.5 hidden sm:table-cell"></td>
                                            @can('contribute-ministry', $ministry)<td></td>@endcan
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        {{-- Empty state --}}
                        <div x-show="!budget.has_items && !budgetLoading" class="p-6 text-center">
                            <div class="mx-auto w-12 h-12 rounded-full bg-primary-50 dark:bg-primary-900/20 flex items-center justify-center mb-3">
                                <svg class="w-6 h-6 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-1">{{ __('app.ministry_budget_empty_title') }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 max-w-sm mx-auto">
                                {{ __('app.ministry_budget_empty_desc') }}
                            </p>
                            @can('contribute-ministry', $ministry)
                            <button @click="openItemModal('create')"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                {{ __('app.ministry_add_first_item') }}
                            </button>
                            @endcan
                        </div>

                        {{-- Add item button (when items exist) --}}
                        @can('contribute-ministry', $ministry)
                        <div x-show="budget.has_items && !budgetLoading" class="px-4 py-2.5 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/20">
                            <button @click="openItemModal('create')"
                                    class="inline-flex items-center gap-1.5 text-sm font-medium text-primary-600 dark:text-primary-400 hover:text-primary-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                {{ __('app.ministry_add_first_item') }}
                            </button>
                        </div>
                        @endcan
                    </div>

                    {{-- Budget Item Modal --}}
                    <div x-show="showItemModal" x-cloak
                         class="fixed inset-0 z-50 overflow-y-auto"
                         x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                         x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                        <div class="flex items-center justify-center min-h-screen p-4">
                            <div class="fixed inset-0 bg-black/50" @click="showItemModal = false"></div>
                            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-lg w-full p-6"
                                 x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                                 @click.stop>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4" x-text="itemModalTitle"></h3>
                                <form @submit.prevent="saveItem()" class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Назва *</label>
                                        <input type="text" x-model="itemForm.name" required maxlength="255"
                                               placeholder="Оренда, Перекуси, Матеріали..."
                                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Запланована сума (₴) *</label>
                                        <input type="number" x-model="itemForm.planned_amount" required min="0" step="0.01"
                                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Планова дата</label>
                                        <input type="date" x-model="itemForm.planned_date"
                                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                                        <p class="text-xs text-gray-500 mt-1">Коли очікується ця витрата</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Категорія витрат</label>
                                        <select x-model="itemForm.category_id"
                                                :class="{ 'hidden': itemForm.category_id === '__custom__' }"
                                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                                            <option value="">Без категорії</option>
                                            @foreach($expenseCategories as $cat)
                                                <option value="{{ $cat->id }}">{{ $cat->icon ?? '💸' }} {{ $cat->name }}</option>
                                            @endforeach
                                            <option value="__custom__">Інше (ввести вручну)...</option>
                                        </select>
                                        <div x-show="itemForm.category_id === '__custom__'" class="flex gap-2">
                                            <input type="text" x-model="itemForm.category_name" placeholder="Назва категорії..."
                                                   class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                                            <button type="button" @click="itemForm.category_id = ''; itemForm.category_name = ''"
                                                    class="px-3 py-2 text-gray-500 hover:text-red-500 border border-gray-300 dark:border-gray-600 rounded-lg">✕</button>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">Витрати з цією категорією автоматично враховуються у факті</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Відповідальні</label>
                                        <div class="border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 max-h-32 overflow-y-auto p-2 space-y-1">
                                            @foreach($ministry->members as $member)
                                            <label class="flex items-center gap-2 px-2 py-1 rounded hover:bg-gray-50 dark:hover:bg-gray-600 cursor-pointer">
                                                <input type="checkbox" value="{{ $member->id }}" x-model="itemForm.person_ids"
                                                       class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500">
                                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $member->full_name }}</span>
                                            </label>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Нотатки</label>
                                        <textarea x-model="itemForm.notes" rows="2" maxlength="500"
                                                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"></textarea>
                                    </div>
                                    <div class="flex justify-end gap-3 pt-2">
                                        <button type="button" @click="showItemModal = false" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">Скасувати</button>
                                        <button type="submit" :disabled="itemSaving"
                                                class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg disabled:opacity-50">
                                            <span x-show="!itemSaving" x-text="itemMode === 'create' ? 'Додати' : 'Зберегти'"></span>
                                            <span x-show="itemSaving">Збереження...</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===== INCOME SECTION ===== --}}
                <div class="mb-6">
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                        <h3 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                            </svg>
                            Надходження
                            <span class="text-xs font-normal text-gray-400" x-text="monthNames[currentMonth] + ' ' + currentYear"></span>
                        </h3>
                        <span class="text-sm font-semibold text-green-600 dark:text-green-400" x-text="fmt(totalIncome) + ' ₴'"></span>
                    </div>

                    {{-- Income list --}}
                    <div x-show="filteredIncome.length > 0" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden mb-3">
                        <div class="divide-y divide-gray-100 dark:divide-gray-700/50">
                            <template x-for="inc in filteredIncome" :key="inc.id">
                                <div class="px-4 py-2.5 flex items-center justify-between gap-3 hover:bg-gray-50 dark:hover:bg-gray-700/30 group">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="font-semibold text-green-600 dark:text-green-400 whitespace-nowrap" x-text="'+' + new Intl.NumberFormat('uk-UA').format(inc.amount) + ' ' + inc.currency"></span>
                                            <span class="text-sm text-gray-900 dark:text-white truncate" x-text="inc.description"></span>
                                        </div>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="text-xs text-gray-400" x-text="inc.date_formatted"></span>
                                            <span x-show="inc.notes" class="text-xs text-gray-400 italic truncate" x-text="inc.notes"></span>
                                        </div>
                                    </div>
                                    @can('contribute-ministry', $ministry)
                                    <button @click="deleteIncome(inc.id)"
                                            class="p-1 text-gray-400 hover:text-red-600 dark:hover:text-red-400 rounded opacity-0 group-hover:opacity-100 transition-opacity shrink-0" title="{{ __('app.ministry_delete_title') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                    @endcan
                                </div>
                            </template>
                        </div>
                    </div>

                    <p x-show="filteredIncome.length === 0" class="text-center text-gray-400 dark:text-gray-500 py-4 text-sm">Немає надходжень за цей період</p>

                    @can('contribute-ministry', $ministry)
                    <button @click="openIncomeModal()"
                            class="inline-flex items-center gap-1.5 text-sm text-green-600 dark:text-green-400 hover:text-green-500 font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Додати надходження
                    </button>
                    @endcan
                </div>

                {{-- ===== INCOME MODAL ===== --}}
                <div x-show="showIncomeModal" x-cloak
                     class="fixed inset-0 z-50 overflow-y-auto"
                     x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                    <div class="flex items-center justify-center min-h-screen p-4">
                        <div class="fixed inset-0 bg-black/50" @click="showIncomeModal = false"></div>
                        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full p-6"
                             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                             @click.stop>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Нове надходження</h3>
                            <form @submit.prevent="saveIncome()" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Сума *</label>
                                    <div class="flex gap-2">
                                        <input type="number" x-model="incomeForm.amount" required min="0.01" step="0.01"
                                               placeholder="0.00"
                                               class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500">
                                        <select x-model="incomeForm.currency"
                                                class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500">
                                            @foreach($enabledCurrencies as $cur)
                                            <option value="{{ $cur }}">{{ $cur }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Дата *</label>
                                    <input type="date" x-model="incomeForm.date" required
                                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Опис *</label>
                                    <input type="text" x-model="incomeForm.description" required maxlength="255"
                                           placeholder="Місячне фінансування, пожертва..."
                                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Примітка</label>
                                    <textarea x-model="incomeForm.notes" rows="2" maxlength="500"
                                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500"></textarea>
                                </div>
                                <div class="flex justify-end gap-3 pt-2">
                                    <button type="button" @click="showIncomeModal = false" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">Скасувати</button>
                                    <button type="submit" :disabled="incomeSaving"
                                            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg disabled:opacity-50">
                                        <span x-show="!incomeSaving">Додати</span>
                                        <span x-show="incomeSaving">Збереження...</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- ===== EXPENSES LIST SECTION ===== --}}
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <h3 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Витрати
                        <span class="text-xs font-normal text-gray-400" x-text="monthNames[currentMonth] + ' ' + currentYear"></span>
                    </h3>
                    <span class="text-sm font-semibold text-gray-900 dark:text-white" x-text="fmt(totalSum) + ' ₴'"></span>
                </div>

                <!-- Search & Sort -->
                <div x-show="allTransactions.length > 0" class="flex flex-col sm:flex-row gap-3 mb-4">
                    <div class="flex-1">
                        <input type="text" x-model="search" placeholder="Пошук..."
                               class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                    </div>
                    <select x-model="sortBy" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <option value="date_desc">Спочатку нові</option>
                        <option value="date_asc">Спочатку старі</option>
                        <option value="amount_desc">Сума ↓</option>
                        <option value="amount_asc">Сума ↑</option>
                    </select>
                </div>

                <!-- Expenses Table -->
                <div class="overflow-x-auto" x-show="filteredTransactions.length > 0">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                                <th class="py-2 pr-3 font-medium">Сума</th>
                                <th class="py-2 px-3 font-medium">Категорія</th>
                                <th class="py-2 px-3 font-medium">Опис</th>
                                <th class="py-2 px-3 font-medium">Дата</th>
                                <th class="py-2 px-3 font-medium">Примітка</th>
                                <th class="py-2 px-3 font-medium">Чек</th>
                                @can('contribute-ministry', $ministry)
                                <th class="py-2 pl-3 font-medium"></th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="t in filteredTransactions" :key="t.id">
                                <tr class="border-b border-gray-200 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                    <td class="py-2 pr-3">
                                        <span class="font-semibold text-gray-900 dark:text-white whitespace-nowrap" x-text="new Intl.NumberFormat('uk-UA').format(t.amount) + ' ' + t.currency"></span>
                                    </td>
                                    <td class="py-2 px-3">
                                        <span x-show="t.category" class="px-2 py-0.5 text-xs rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400" x-text="t.category"></span>
                                    </td>
                                    <td class="py-2 px-3 text-gray-900 dark:text-white" x-text="t.description"></td>
                                    <td class="py-2 px-3 text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                        <span x-text="t.date_formatted"></span>
                                        <span x-show="t.payment_method" class="text-xs" x-text="' • ' + (t.payment_method === 'cash' ? 'Готівка' : 'Картка')"></span>
                                    </td>
                                    <td class="py-2 px-3 text-gray-500 dark:text-gray-400 text-xs italic max-w-[100px] sm:max-w-[150px] truncate" x-text="t.notes" :title="t.notes"></td>
                                    <td class="py-2 px-3">
                                        <template x-if="t.attachments.length > 0">
                                            <button @click="t.attachments[0].is_image ? $dispatch('open-lightbox', t.attachments[0].url) : window.open(t.attachments[0].url, '_blank')" class="text-primary-600 dark:text-primary-400 hover:underline text-xs flex items-center gap-1 cursor-pointer">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                                <span x-text="t.attachments.length"></span>
                                            </button>
                                        </template>
                                    </td>
                                    @can('contribute-ministry', $ministry)
                                    <td class="py-2 pl-3">
                                        <div class="flex items-center gap-1">
                                            <button @click="openExpenseModal('edit', t)"
                                                    class="p-1 text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 rounded"
                                                    title="{{ __('app.ministry_edit_title') }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>
                                            <button @click="deleteExpense(t.id)"
                                                    class="p-1 text-gray-400 hover:text-red-600 dark:hover:text-red-400 rounded" title="{{ __('app.ministry_delete_title') }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                    @endcan
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <p x-show="filteredTransactions.length === 0" class="text-center text-gray-500 dark:text-gray-400 py-6">Немає витрат за цей період</p>

                @can('contribute-ministry', $ministry)
                <div class="mt-3">
                    <button @click="openExpenseModal('create')"
                            class="inline-flex items-center gap-1.5 text-sm text-primary-600 dark:text-primary-400 hover:text-primary-500 font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Додати витрату
                    </button>
                </div>
                @endcan

                {{-- ===== EXPENSE MODAL ===== --}}
                <div x-show="showExpenseModal" x-cloak
                     class="fixed inset-0 z-50 overflow-y-auto"
                     x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                    <div class="flex items-start justify-center min-h-screen p-4 pt-16">
                        <div class="fixed inset-0 bg-black/50" @click="showExpenseModal = false"></div>
                        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-lg w-full p-6 max-h-[85vh] overflow-y-auto"
                             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                             @click.stop>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4" x-text="expenseModalTitle"></h3>
                            <form @submit.prevent="saveExpense()" class="space-y-4">
                                {{-- Amount + Currency --}}
                                <div class="flex gap-3">
                                    <div class="flex-1">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Сума *</label>
                                        <input type="number" x-model="expenseForm.amount" required min="0.01" step="0.01"
                                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                                    </div>
                                    <div class="w-28">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Валюта</label>
                                        <select x-model="expenseForm.currency"
                                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                                            @foreach($enabledCurrencies as $cur)
                                                <option value="{{ $cur }}">{{ $cur }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- Date --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Дата *</label>
                                    <input type="date" x-model="expenseForm.date" required
                                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                                </div>

                                {{-- Description --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Опис *</label>
                                    <input type="text" x-model="expenseForm.description" required maxlength="255"
                                           placeholder="Опис витрати..."
                                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                                </div>

                                {{-- Category --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Категорія</label>
                                    <select x-model="expenseForm.category_id"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"
                                            :class="{ 'hidden': expenseForm.category_id === '__custom__' }">
                                        <option value="">Без категорії</option>
                                        @foreach($expenseCategories as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->icon ?? '💸' }} {{ $cat->name }}</option>
                                        @endforeach
                                        <option value="__custom__">Інше (ввести вручну)...</option>
                                    </select>
                                    <div x-show="expenseForm.category_id === '__custom__'" class="flex gap-2">
                                        <input type="text" x-model="expenseForm.category_name" placeholder="Назва категорії..."
                                               class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                                        <button type="button" @click="expenseForm.category_id = ''; expenseForm.category_name = ''"
                                                class="px-3 py-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300" title="Назад до списку">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                </div>

                                {{-- Two columns: Expense type + Payment method --}}
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Тип витрати</label>
                                        <select x-model="expenseForm.expense_type"
                                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                                            <option value="">—</option>
                                            <option value="one_time">Разова</option>
                                            <option value="recurring">Регулярна</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Спосіб оплати</label>
                                        <select x-model="expenseForm.payment_method"
                                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                                            <option value="">—</option>
                                            <option value="cash">Готівка</option>
                                            <option value="card">Картка</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- Budget Item --}}
                                <div x-show="budget.has_items">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Стаття бюджету</label>
                                    <select x-model="expenseForm.budget_item_id"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                                        <option value="">Без прив'язки</option>
                                        <template x-for="bi in budget.items" :key="bi.id">
                                            <option :value="bi.id" x-text="bi.name + ' (' + fmt(bi.planned_amount) + ' ₴)'"></option>
                                        </template>
                                    </select>
                                </div>

                                {{-- Notes --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Нотатки</label>
                                    <textarea x-model="expenseForm.notes" rows="2"
                                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"></textarea>
                                </div>

                                {{-- Existing Attachments (edit mode) --}}
                                <div x-show="existingAttachments.length > 0">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Існуючі чеки</label>
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="att in existingAttachments" :key="att.id">
                                            <div x-show="!deleteAttachmentIds.includes(att.id)" class="relative group">
                                                <template x-if="att.is_image">
                                                    <img :src="att.url" @click.stop="$dispatch('open-lightbox', att.url)" class="w-16 h-16 object-cover rounded-lg border border-gray-200 dark:border-gray-600 cursor-pointer hover:opacity-80 transition-opacity">
                                                </template>
                                                <template x-if="!att.is_image">
                                                    <a :href="att.url" target="_blank" @click.stop class="w-16 h-16 flex items-center justify-center rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                                    </a>
                                                </template>
                                                <button type="button" @click="removeExistingAttachment(att.id)"
                                                        class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition-opacity">×</button>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                {{-- New Receipts --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Чеки</label>
                                    <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-4 text-center cursor-pointer hover:border-primary-400 transition-colors"
                                         @click="$refs.expenseFileInput.click()"
                                         @dragover.prevent="$el.classList.add('border-primary-400')"
                                         @dragleave.prevent="$el.classList.remove('border-primary-400')"
                                         @drop.prevent="$el.classList.remove('border-primary-400'); addExpenseFiles({target: {files: $event.dataTransfer.files, value: ''}})">
                                        <svg class="w-8 h-8 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <p class="text-sm text-gray-500">Натисніть або перетягніть файли</p>
                                        <p class="text-xs text-gray-400 mt-1">JPG, PNG, PDF — до 10 МБ</p>
                                    </div>
                                    <input type="file" x-ref="expenseFileInput" multiple accept="image/*,.pdf" class="hidden" @change="addExpenseFiles($event)">

                                    {{-- File previews --}}
                                    <div x-show="expensePreviews.length > 0" class="flex flex-wrap gap-2 mt-2">
                                        <template x-for="(preview, idx) in expensePreviews" :key="idx">
                                            <div class="relative group">
                                                <template x-if="preview.url">
                                                    <img :src="preview.url" @click.stop="$dispatch('open-lightbox', preview.url)" class="w-16 h-16 object-cover rounded-lg border border-gray-200 dark:border-gray-600 cursor-pointer hover:opacity-80 transition-opacity">
                                                </template>
                                                <template x-if="!preview.url">
                                                    <div class="w-16 h-16 flex items-center justify-center rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-xs text-gray-500 p-1 text-center" x-text="preview.name"></div>
                                                </template>
                                                <button type="button" @click="removeExpenseFile(idx)"
                                                        class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition-opacity">×</button>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                {{-- Buttons --}}
                                <div class="flex justify-end gap-3 pt-2">
                                    <button type="button" @click="showExpenseModal = false" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">Скасувати</button>
                                    <button type="submit" :disabled="expenseSaving"
                                            class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg disabled:opacity-50">
                                        <span x-show="!expenseSaving" x-text="expenseMode === 'create' ? 'Додати' : 'Зберегти'"></span>
                                        <span x-show="expenseSaving">Збереження...</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div x-show="activeTab === 'resources'"{{ $tab !== 'resources' ? ' style="display:none"' : '' }}
                 x-data="resourcesManager()">

                <!-- Breadcrumbs -->
                <nav class="flex items-center gap-2 text-sm mb-4">
                    <a href="{{ route('ministries.show', ['ministry' => $ministry, 'tab' => 'resources']) }}"
                       class="text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                        </svg>
                        Ресурси
                    </a>
                    @foreach($breadcrumbs as $crumb)
                    <span class="text-gray-400">/</span>
                    @if($loop->last)
                    <span class="text-gray-900 dark:text-white font-medium">{{ $crumb->name }}</span>
                    @else
                    <a href="{{ route('ministries.show', ['ministry' => $ministry, 'tab' => 'resources', 'folder' => $crumb->id]) }}"
                       class="text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">
                        {{ $crumb->name }}
                    </a>
                    @endif
                    @endforeach
                </nav>

                <!-- Actions -->
                @can('contribute-ministry', $ministry)
                <div class="flex flex-wrap items-center gap-3 mb-4">
                    <button @click="showCreateFolder = true" class="inline-flex items-center text-sm text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                        Папка
                    </button>
                    <button @click="createAndOpenDocument()" class="inline-flex items-center text-sm text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Документ
                    </button>
                    <label class="inline-flex items-center text-sm text-primary-600 dark:text-primary-400 hover:text-primary-500 transition-colors cursor-pointer"
                           :class="uploading && 'opacity-50 pointer-events-none'">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        <span x-text="uploading ? 'Завантаження...' : 'Завантажити файл'"></span>
                        <input type="file" class="hidden" @change="uploadFile($event)" multiple :disabled="uploading">
                    </label>
                </div>
                <template x-if="uploadError">
                    <div class="mb-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-sm text-red-700 dark:text-red-300" x-text="uploadError"></div>
                </template>
                @endcan

                <!-- Resources list -->
                @if($resources->count() > 0)
                <div id="ministry-resources-list" class="space-y-1.5">
                    @foreach($resources as $resource)
                    @php
                        $isFolder = $resource->isFolder();
                        $isDoc = $resource->isDocument();
                        $mime = $resource->mime_type ?? '';
                        // Icon colors
                        if ($isFolder) { $iconBg = 'bg-amber-100 dark:bg-amber-900/30'; $iconColor = 'text-amber-600 dark:text-amber-400'; }
                        elseif ($isDoc) { $iconBg = 'bg-blue-100 dark:bg-blue-900/30'; $iconColor = 'text-blue-600 dark:text-blue-400'; }
                        elseif (str_contains($mime, 'pdf')) { $iconBg = 'bg-red-100 dark:bg-red-900/30'; $iconColor = 'text-red-600 dark:text-red-400'; }
                        elseif (str_starts_with($mime, 'image/')) { $iconBg = 'bg-green-100 dark:bg-green-900/30'; $iconColor = 'text-green-600 dark:text-green-400'; }
                        elseif (str_starts_with($mime, 'audio/')) { $iconBg = 'bg-pink-100 dark:bg-pink-900/30'; $iconColor = 'text-pink-600 dark:text-pink-400'; }
                        elseif (str_starts_with($mime, 'video/')) { $iconBg = 'bg-purple-100 dark:bg-purple-900/30'; $iconColor = 'text-purple-600 dark:text-purple-400'; }
                        elseif (str_contains($mime, 'word') || str_contains($mime, 'document')) { $iconBg = 'bg-blue-100 dark:bg-blue-900/30'; $iconColor = 'text-blue-600 dark:text-blue-400'; }
                        elseif (str_contains($mime, 'excel') || str_contains($mime, 'spreadsheet')) { $iconBg = 'bg-emerald-100 dark:bg-emerald-900/30'; $iconColor = 'text-emerald-600 dark:text-emerald-400'; }
                        else { $iconBg = 'bg-gray-100 dark:bg-gray-700'; $iconColor = 'text-gray-500 dark:text-gray-400'; }
                    @endphp
                    <div class="group flex items-center gap-3 p-2.5 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer transition-colors" data-resource-id="{{ $resource->id }}"
                         @if($isFolder)
                         @click="Livewire.navigate('{{ route('ministries.show', ['ministry' => $ministry, 'tab' => 'resources', 'folder' => $resource->id]) }}')"
                         @elseif($isDoc)
                         @click="openDocument({{ json_encode(['id' => $resource->id, 'name' => $resource->name, 'content' => $resource->content ?? '']) }})"
                         @else
                         @click="showPreview({{ json_encode([
                             'id' => $resource->id,
                             'name' => $resource->name,
                             'icon' => $resource->icon,
                             'size' => $resource->formatted_size,
                             'mime' => $resource->mime_type,
                             'url' => $resource->file_path ? Storage::url($resource->file_path) : '',
                             'downloadUrl' => route('resources.download', $resource),
                             'createdAt' => $resource->created_at->format('d.m.Y H:i'),
                             'creator' => $resource->creator?->name,
                         ]) }})"
                         @endif>
                        {{-- Icon --}}
                        <div class="w-9 h-9 rounded-lg {{ $iconBg }} flex items-center justify-center flex-shrink-0">
                            @if($isFolder)
                            <svg class="w-5 h-5 {{ $iconColor }}" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/></svg>
                            @elseif($isDoc)
                            <svg class="w-5 h-5 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            @elseif(str_starts_with($mime, 'image/'))
                            <svg class="w-5 h-5 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            @elseif(str_starts_with($mime, 'audio/'))
                            <svg class="w-5 h-5 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/></svg>
                            @elseif(str_starts_with($mime, 'video/'))
                            <svg class="w-5 h-5 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            @else
                            <svg class="w-5 h-5 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            @endif
                        </div>
                        {{-- Name & meta --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $resource->name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                @if($isFolder) Папка
                                @elseif($isDoc) Документ
                                @else {{ $resource->formatted_size }}
                                @endif
                                <span class="mx-1">&middot;</span>
                                {{ $resource->created_at->format('d.m.Y') }}
                            </p>
                        </div>
                        {{-- Context menu --}}
                        @can('contribute-ministry', $ministry)
                        <button @click.stop="openMenu({{ $resource->id }}, '{{ addslashes($resource->name) }}', $event)"
                                class="p-1.5 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg>
                        </button>
                        @endcan
                    </div>
                    @endforeach
                </div>
                @else
                <p id="ministry-resources-empty" class="text-center text-gray-500 dark:text-gray-400 py-8 text-sm">
                    {{ count($breadcrumbs) > 0 ? 'Папка порожня' : 'Немає ресурсів' }}
                </p>
                @endif

                <!-- Create folder modal -->
                @can('contribute-ministry', $ministry)
                <div x-show="showCreateFolder" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
                    <div class="min-h-screen px-4 flex items-center justify-center">
                        <div class="fixed inset-0 bg-black/50" @click="showCreateFolder = false"></div>
                        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Нова папка</h3>
                            <form @submit.prevent="submit($refs.createFolderForm)" x-ref="createFolderForm"
                                  x-data="{ ...ajaxForm({ url: '{{ route('ministries.resources.folder.create', $ministry) }}', method: 'POST', stayOnPage: true, resetOnSuccess: true, onSuccess(data) { _addMinistryResourceFolder(this, data); } }) }">
                                <input type="hidden" name="parent_id" value="{{ $currentFolder?->id }}">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Назва</label>
                                    <input type="text" name="name" required autofocus
                                           class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white"
                                           placeholder="Назва папки...">
                                </div>
                                <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 sm:gap-3 mt-6">
                                    <button type="button" @click="showCreateFolder = false"
                                            class="w-full sm:w-auto px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900">
                                        Скасувати
                                    </button>
                                    <button type="submit" :disabled="saving" class="w-full sm:w-auto px-4 py-2 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700">
                                        Створити
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Context menu -->
                <div x-show="menuOpen" x-cloak
                     :style="`top: ${menuY}px; left: ${menuX}px`"
                     @click.away="menuOpen = false"
                     class="fixed z-50 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 py-1 min-w-48">
                    <button @click="showRenameModal()" class="w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Перейменувати
                    </button>
                    <button @click="deleteItem()" class="w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Видалити
                    </button>
                </div>

                <!-- Rename modal -->
                <div x-show="showRename" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
                    <div class="min-h-screen px-4 flex items-center justify-center">
                        <div class="fixed inset-0 bg-black/50" @click="showRename = false"></div>
                        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Перейменувати</h3>
                            <form @submit.prevent="submitRename()">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Нова назва</label>
                                    <input type="text" x-model="renameName" required x-ref="renameInput"
                                           class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white"
                                           placeholder="Назва...">
                                </div>
                                <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 sm:gap-3 mt-6">
                                    <button type="button" @click="showRename = false"
                                            class="w-full sm:w-auto px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900">
                                        Скасувати
                                    </button>
                                    <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700">
                                        Зберегти
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endcan

                <!-- File preview modal -->
                <div x-show="previewFile" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
                    <div class="min-h-screen px-4 flex items-center justify-center">
                        <div class="fixed inset-0 bg-black/70" @click="previewFile = null"></div>
                        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-2xl w-full overflow-hidden">
                            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="text-3xl" x-text="previewFile?.icon"></span>
                                    <div>
                                        <h3 class="font-semibold text-gray-900 dark:text-white" x-text="previewFile?.name"></h3>
                                        <p class="text-sm text-gray-500" x-text="previewFile?.size"></p>
                                    </div>
                                </div>
                                <button @click="previewFile = null" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="p-4">
                                <template x-if="previewFile?.mime?.startsWith('image/')">
                                    <img :src="previewFile?.url" class="max-h-96 mx-auto rounded-lg">
                                </template>
                                <template x-if="previewFile?.mime?.startsWith('audio/')">
                                    <audio :src="previewFile?.url" controls class="w-full"></audio>
                                </template>
                                <template x-if="previewFile?.mime?.startsWith('video/')">
                                    <video :src="previewFile?.url" controls class="max-h-96 mx-auto rounded-lg"></video>
                                </template>
                                <template x-if="previewFile?.mime === 'application/pdf'">
                                    <iframe :src="previewFile?.url" class="w-full h-96 rounded-lg"></iframe>
                                </template>
                                <template x-if="previewFile && !previewFile?.mime?.startsWith('image/') && !previewFile?.mime?.startsWith('audio/') && !previewFile?.mime?.startsWith('video/') && previewFile?.mime !== 'application/pdf'">
                                    <div class="text-center py-8">
                                        <span class="text-6xl block mb-4" x-text="previewFile?.icon"></span>
                                        <p class="text-gray-500 dark:text-gray-400">Попередній перегляд недоступний</p>
                                    </div>
                                </template>
                            </div>
                            <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
                                <div class="flex items-center justify-between">
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        <span x-text="previewFile?.createdAt"></span>
                                        <span x-show="previewFile?.creator"> &mdash; <span x-text="previewFile?.creator"></span></span>
                                    </div>
                                    <a :href="previewFile?.downloadUrl"
                                       class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                        Завантажити
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Document editor modal -->
                <div x-show="showDocEditor" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4 sm:p-6 md:p-10">
                    <!-- Backdrop -->
                    <div x-show="showDocEditor"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="fixed inset-0 bg-black/40 backdrop-blur-sm"></div>

                    <!-- Modal card -->
                    <div x-show="showDocEditor"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                         x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                         class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl flex flex-col w-full max-w-5xl max-h-[90vh] overflow-hidden">

                        <!-- Header -->
                        <div class="flex items-center justify-between px-4 py-2.5 border-b border-gray-200 dark:border-gray-700 shrink-0">
                            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <input type="text" x-model="docName"
                                       class="flex-1 min-w-0 text-sm font-semibold text-gray-900 dark:text-white bg-transparent border-0 p-0 focus:ring-0 focus:outline-none placeholder-gray-400"
                                       placeholder="Назва документа...">
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0 ml-3">
                                <span x-show="docSaving" x-transition class="text-xs text-blue-500">Збереження...</span>
                                <span x-show="docSaved" x-transition class="text-xs text-green-500">Збережено</span>
                                <button @click="saveDocument()" :disabled="docSaving"
                                        class="px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50">
                                    Зберегти
                                </button>
                                <button @click="closeDocEditor()"
                                        class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-lg transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>

                        <!-- Toolbar -->
                        <div class="doc-toolbar" id="doc-toolbar">
                            <select onchange="docExecBlock(this.value); this.value='';" title="Блок">
                                <option value="">Формат</option>
                                <option value="p">Текст</option>
                                <option value="h1">Заголовок 1</option>
                                <option value="h2">Заголовок 2</option>
                                <option value="h3">Заголовок 3</option>
                                <option value="blockquote">Цитата</option>
                            </select>
                            <select onchange="docExecFontSize(this.value); this.value='';" title="Розмір">
                                <option value="">Розмір</option>
                                <option value="1">10</option>
                                <option value="2">13</option>
                                <option value="3">16</option>
                                <option value="4">18</option>
                                <option value="5">24</option>
                                <option value="6">32</option>
                                <option value="7">48</option>
                            </select>
                            <div class="tb-sep"></div>
                            <button class="tb-btn" onclick="docExec('bold')" title="Жирний"><b>B</b></button>
                            <button class="tb-btn" onclick="docExec('italic')" title="Курсив"><i>I</i></button>
                            <button class="tb-btn" onclick="docExec('underline')" title="Підкреслити"><u>U</u></button>
                            <button class="tb-btn" onclick="docExec('strikeThrough')" title="Закреслити"><s>S</s></button>
                            <div class="tb-sep"></div>
                            <input type="color" value="#000000" onchange="docExecColor('foreColor', this.value)" title="Колір тексту">
                            <input type="color" value="#ffffff" onchange="docExecColor('hiliteColor', this.value)" title="Колір фону">
                            <div class="tb-sep"></div>
                            <button class="tb-btn" onclick="docExec('justifyLeft')" title="Ліворуч">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="15" y2="12"/><line x1="3" y1="18" x2="18" y2="18"/></svg>
                            </button>
                            <button class="tb-btn" onclick="docExec('justifyCenter')" title="По центру">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="6" y1="12" x2="18" y2="12"/><line x1="4" y1="18" x2="20" y2="18"/></svg>
                            </button>
                            <button class="tb-btn" onclick="docExec('justifyRight')" title="Праворуч">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="9" y1="12" x2="21" y2="12"/><line x1="6" y1="18" x2="21" y2="18"/></svg>
                            </button>
                            <div class="tb-sep"></div>
                            <button class="tb-btn" onclick="docExec('insertUnorderedList')" title="Список">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="9" y1="6" x2="20" y2="6"/><line x1="9" y1="12" x2="20" y2="12"/><line x1="9" y1="18" x2="20" y2="18"/><circle cx="4" cy="6" r="1.5" fill="currentColor"/><circle cx="4" cy="12" r="1.5" fill="currentColor"/><circle cx="4" cy="18" r="1.5" fill="currentColor"/></svg>
                            </button>
                            <button class="tb-btn" onclick="docExec('insertOrderedList')" title="Нумерований список">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="10" y1="6" x2="20" y2="6"/><line x1="10" y1="12" x2="20" y2="12"/><line x1="10" y1="18" x2="20" y2="18"/><text x="3" y="8" font-size="8" fill="currentColor" stroke="none">1</text><text x="3" y="14" font-size="8" fill="currentColor" stroke="none">2</text><text x="3" y="20" font-size="8" fill="currentColor" stroke="none">3</text></svg>
                            </button>
                            <button class="tb-btn" onclick="docExec('outdent')" title="Зменшити відступ">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="11" y1="12" x2="21" y2="12"/><line x1="11" y1="18" x2="21" y2="18"/><polyline points="7 9 3 12 7 15"/></svg>
                            </button>
                            <button class="tb-btn" onclick="docExec('indent')" title="Збільшити відступ">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="11" y1="12" x2="21" y2="12"/><line x1="11" y1="18" x2="21" y2="18"/><polyline points="3 9 7 12 3 15"/></svg>
                            </button>
                            <div class="tb-sep"></div>
                            <button class="tb-btn" onclick="docInsertLink()" title="Посилання">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>
                            </button>
                            <button class="tb-btn" onclick="docInsertImage()" title="Зображення">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                            </button>
                            <button class="tb-btn" onclick="docExec('removeFormat')" title="Очистити форматування">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="4" x2="20" y2="20"/><path d="M6 4h8l-4 16"/></svg>
                            </button>
                        </div>
                        <!-- Editable area -->
                        <div class="flex-1 overflow-y-auto min-h-0">
                            <div id="doc-editable" class="doc-editable" contenteditable="true" data-placeholder="Почніть вводити текст..."></div>
                        </div>
                    </div>
                </div>

                <!-- Rename & delete handled via AJAX in resourcesManager() -->
            </div>

            <!-- Board Tab -->
            <div x-show="activeTab === 'board'"{{ $tab !== 'board' ? ' style="display:none"' : '' }}>
                @include('boards._kanban', [
                    'board' => $ministryBoard,
                    'people' => $boardPeople,
                    'ministries' => $boardMinistries,
                    'epics' => $boardEpics,
                    'embedded' => true,
                ])
            </div>

            <!-- Goals Tab -->
            <div x-show="activeTab === 'goals'"{{ $tab !== 'goals' ? ' style="display:none"' : '' }}
                 x-data="goalsManager()">

                <!-- Vision - Main section at top -->
                <div class="bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-xl p-5 mb-6 border border-indigo-100 dark:border-indigo-800">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center">
                                <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">Бачення служіння</h3>
                                <p class="text-xs text-indigo-600 dark:text-indigo-400">Куди ми рухаємось</p>
                            </div>
                        </div>
                        @can('contribute-ministry', $ministry)
                        <button @click="editingVision = !editingVision" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 text-sm font-medium">
                            <span x-text="editingVision ? 'Скасувати' : 'Редагувати'"></span>
                        </button>
                        @endcan
                    </div>
                    <div x-show="!editingVision">
                        @if($ministry->vision)
                            <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed">{{ $ministry->vision }}</p>
                        @else
                            <p class="text-gray-500 dark:text-gray-400 italic">Бачення ще не визначено.</p>
                        @endif
                    </div>
                    @can('contribute-ministry', $ministry)
                    <form x-show="editingVision" @submit.prevent="submit($refs.visionForm)" x-ref="visionForm"
                          x-data="{ ...ajaxForm({ url: '{{ route('ministries.vision.update', $ministry) }}', method: 'POST', stayOnPage: true, onSuccess() { const ta = this.$refs.visionForm.querySelector('textarea'); const txt = ta.value; const display = this.$refs.visionForm.previousElementSibling; if (display) { const p = display.querySelector('p'); if (p) { p.textContent = txt || 'Бачення ще не визначено.'; p.className = txt ? 'text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed' : 'text-gray-500 dark:text-gray-400 italic'; } } this.editingVision = false; } }) }"
                          class="space-y-3">
                        <textarea name="vision" rows="4" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500" placeholder="Опишіть бачення вашого служіння...">{{ $ministry->vision }}</textarea>
                        <div class="flex justify-end">
                            <button type="submit" :disabled="saving" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg">Зберегти</button>
                        </div>
                    </form>
                    @endcan
                </div>

                <!-- Goals Header -->
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Цілі</h3>
                    @can('contribute-ministry', $ministry)
                    <div class="flex gap-2">
                        <button @click="showTaskModal = true; taskForm.goal_id = ''" class="inline-flex items-center px-3 py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Задача
                        </button>
                        <button @click="showGoalModal = true; resetGoalForm()" class="inline-flex items-center px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Ціль
                        </button>
                    </div>
                    @endcan
                </div>

                <!-- Goals List -->
                @if($ministry->goals->count() > 0)
                    <div class="space-y-4">
                        @foreach($ministry->goals as $goal)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                                <div class="p-4 bg-gray-50 dark:bg-gray-700/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div class="flex items-start gap-3">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0
                                            @if($goal->status === 'active') bg-blue-100 dark:bg-blue-900/30
                                            @elseif($goal->status === 'completed') bg-green-100 dark:bg-green-900/30
                                            @elseif($goal->status === 'on_hold') bg-yellow-100 dark:bg-yellow-900/30
                                            @else bg-red-100 dark:bg-red-900/30 @endif">
                                            @if($goal->status === 'completed')
                                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            @else
                                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            @endif
                                        </div>
                                        <div>
                                            <h4 class="font-medium text-gray-900 dark:text-white">{{ $goal->title }}</h4>
                                            @if($goal->description)
                                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ Str::limit($goal->description, 100) }}</p>
                                            @endif
                                            <div class="flex flex-wrap items-center gap-2 mt-2 text-xs">
                                                <span class="px-2 py-0.5 rounded bg-{{ $goal->status_color }}-100 dark:bg-{{ $goal->status_color }}-900/30 text-{{ $goal->status_color }}-700 dark:text-{{ $goal->status_color }}-300">{{ $goal->status_label }}</span>
                                                @if($goal->period)
                                                    <span class="px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">{{ $goal->period }}</span>
                                                @endif
                                                @if($goal->due_date)
                                                    <span class="text-gray-500 @if($goal->is_overdue) text-red-600 @endif">до {{ $goal->due_date->format('d.m.Y') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <div class="flex items-center gap-2">
                                            <div class="w-20 h-1.5 bg-gray-200 dark:bg-gray-600 rounded-full overflow-hidden">
                                                <div class="h-full bg-primary-500 rounded-full" style="width: {{ $goal->calculated_progress }}%"></div>
                                            </div>
                                            <span class="text-xs font-medium text-gray-500">{{ $goal->calculated_progress }}%</span>
                                        </div>
                                        @can('contribute-ministry', $ministry)
                                        <div class="flex items-center gap-1">
                                            <button @click="editGoal({{ $goal->id }}, {{ json_encode(['title' => $goal->title, 'description' => $goal->description, 'period' => $goal->period, 'due_date' => $goal->due_date?->format('Y-m-d'), 'priority' => $goal->priority, 'status' => $goal->status]) }})" class="p-1.5 text-gray-400 hover:text-gray-600">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            </button>
                                            <button @click="ajaxDelete('{{ route('ministries.goals.destroy', [$ministry, $goal]) }}', '{{ __('messages.confirm_delete_short') }}', () => $el.closest('.border.border-gray-200').remove())"
                                                    class="p-1.5 text-gray-400 hover:text-red-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                        </div>
                                        @endcan
                                    </div>
                                </div>
                                @if($goal->tasks->count() > 0)
                                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach($goal->tasks as $task)
                                            <div class="p-3 flex items-center gap-3 hover:bg-gray-50 dark:hover:bg-gray-700/30" x-data="{ done: {{ $task->is_done ? 'true' : 'false' }} }">
                                                @can('contribute-ministry', $ministry)
                                                <button @click="ajaxAction('{{ route('ministries.tasks.toggle', [$ministry, $task]) }}', 'POST').then(() => { done = !done; })"
                                                        class="w-5 h-5 rounded-full border-2 flex items-center justify-center"
                                                        :class="done ? 'border-green-500 bg-green-500' : 'border-gray-300 hover:border-primary-500'">
                                                    <svg x-show="done" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                </button>
                                                @else
                                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0"
                                                     :class="done ? 'border-green-500 bg-green-500' : 'border-gray-300'">
                                                    <svg x-show="done" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                </div>
                                                @endcan
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm text-gray-900 dark:text-white" :class="done && 'line-through text-gray-500'">{{ $task->title }}</p>
                                                    <div class="flex items-center gap-2 mt-0.5 text-xs text-gray-500">
                                                        @if($task->assignee)<span>{{ $task->assignee->full_name }}</span>@endif
                                                        @if($task->due_date)<span class="@if($task->is_overdue) text-red-600 @endif">{{ $task->due_date->format('d.m') }}</span>@endif
                                                    </div>
                                                </div>
                                                @can('contribute-ministry', $ministry)
                                                <div class="flex items-center gap-1">
                                                    <button @click="editTask({{ $task->id }}, {{ json_encode(['title' => $task->title, 'description' => $task->description, 'goal_id' => $task->goal_id, 'assigned_to' => $task->assigned_to, 'due_date' => $task->due_date?->format('Y-m-d'), 'priority' => $task->priority, 'status' => $task->status]) }})" class="p-1 text-gray-400 hover:text-gray-600"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></button>
                                                    <button @click="ajaxDelete('{{ route('ministries.tasks.destroy', [$ministry, $task]) }}', '{{ __('messages.confirm_delete_short') }}', () => $el.closest('.p-3.flex.items-center').remove())" class="p-1 text-gray-400 hover:text-red-600"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                                </div>
                                                @endcan
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                @can('contribute-ministry', $ministry)
                                <div class="p-2 bg-gray-50 dark:bg-gray-700/30 border-t border-gray-200 dark:border-gray-700">
                                    <button @click="showTaskModal = true; taskForm.goal_id = {{ $goal->id }}" class="text-xs text-gray-500 hover:text-primary-600 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        Додати задачу
                                    </button>
                                </div>
                                @endcan
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="w-12 h-12 mx-auto mb-3 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm mb-3">Цілей ще немає</p>
                        @can('contribute-ministry', $ministry)
                        <button @click="showGoalModal = true; resetGoalForm()" class="inline-flex items-center px-3 py-1.5 bg-primary-600 text-white text-sm rounded-lg hover:bg-primary-700">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Створити ціль
                        </button>
                        @endcan
                    </div>
                @endif

                @can('contribute-ministry', $ministry)
                <!-- Goal Modal -->
                <div x-show="showGoalModal" class="fixed inset-0 z-50" style="display: none;">
                    <div class="absolute inset-0 bg-black/50" @click="showGoalModal = false"></div>
                    <div class="absolute inset-4 md:inset-auto md:top-1/2 md:left-1/2 md:-translate-x-1/2 md:-translate-y-1/2 md:w-full md:max-w-md bg-white dark:bg-gray-800 rounded-xl shadow-xl overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                            <h3 class="font-semibold text-gray-900 dark:text-white" x-text="editingGoalId ? 'Редагувати ціль' : 'Нова ціль'"></h3>
                            <button @click="showGoalModal = false" class="text-gray-400 hover:text-gray-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                        </div>
                        <form @submit.prevent="
                                  const url = editingGoalId ? '{{ url('ministries/' . $ministry->id . '/goals') }}/' + editingGoalId : '{{ route('ministries.goals.store', $ministry) }}';
                                  const method = editingGoalId ? 'PUT' : 'POST';
                                  const fd = new FormData($refs.goalForm);
                                  fetch(url, {
                                      method: method,
                                      headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                                      body: fd
                                  }).then(r => { if (r.ok) window.location.reload(); else r.json().then(d => alert(d.message || 'Помилка')).catch(() => alert('Помилка')); }).catch(() => alert('Помилка'));
                              " x-ref="goalForm" class="p-4 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Назва *</label>
                                <input type="text" name="title" x-model="goalForm.title" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Опис</label>
                                <textarea name="description" x-model="goalForm.description" rows="2" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Період</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Від</span>
                                        <input type="date" x-model="goalForm.period_start" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                    </div>
                                    <div>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">До</span>
                                        <input type="date" x-model="goalForm.period_end" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                    </div>
                                </div>
                                <input type="hidden" name="period" :value="goalForm.period_start && goalForm.period_end ?
                                    new Date(goalForm.period_start).toLocaleDateString('uk-UA') + ' – ' + new Date(goalForm.period_end).toLocaleDateString('uk-UA') :
                                    (goalForm.period_start ? new Date(goalForm.period_start).toLocaleDateString('uk-UA') : '')">
                            </div>
                            <input type="hidden" name="due_date" :value="goalForm.period_end || ''">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Пріоритет</label>
                                    <select name="priority" x-model="goalForm.priority" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                        <option value="low">Низький</option>
                                        <option value="medium">Середній</option>
                                        <option value="high">Високий</option>
                                    </select>
                                </div>
                                <div x-show="editingGoalId">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Статус</label>
                                    <select name="status" x-model="goalForm.status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                        <option value="active">Активна</option>
                                        <option value="completed">Виконана</option>
                                        <option value="on_hold">На паузі</option>
                                        <option value="cancelled">Скасована</option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex gap-2 pt-2">
                                <button type="button" @click="showGoalModal = false" class="flex-1 px-3 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg">Скасувати</button>
                                <button type="submit" class="flex-1 px-3 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg" x-text="editingGoalId ? 'Зберегти' : 'Створити'"></button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Task Modal -->
                <div x-show="showTaskModal" class="fixed inset-0 z-50" style="display: none;">
                    <div class="absolute inset-0 bg-black/50" @click="showTaskModal = false"></div>
                    <div class="absolute inset-4 md:inset-auto md:top-1/2 md:left-1/2 md:-translate-x-1/2 md:-translate-y-1/2 md:w-full md:max-w-md bg-white dark:bg-gray-800 rounded-xl shadow-xl overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                            <h3 class="font-semibold text-gray-900 dark:text-white" x-text="editingTaskId ? 'Редагувати задачу' : 'Нова задача'"></h3>
                            <button @click="showTaskModal = false" class="text-gray-400 hover:text-gray-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                        </div>
                        <form @submit.prevent="
                                  const url = editingTaskId ? '{{ url('ministries/' . $ministry->id . '/tasks') }}/' + editingTaskId : '{{ route('ministries.tasks.store', $ministry) }}';
                                  const method = editingTaskId ? 'PUT' : 'POST';
                                  const fd = new FormData($refs.taskForm);
                                  fetch(url, {
                                      method: method,
                                      headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                                      body: fd
                                  }).then(r => { if (r.ok) window.location.reload(); else r.json().then(d => alert(d.message || 'Помилка')).catch(() => alert('Помилка')); }).catch(() => alert('Помилка'));
                              " x-ref="taskForm" class="p-4 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Назва *</label>
                                <input type="text" name="title" x-model="taskForm.title" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ціль</label>
                                <select name="goal_id" x-model="taskForm.goal_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                    <option value="">Без цілі</option>
                                    @foreach($ministry->goals as $goal)
                                        <option value="{{ $goal->id }}">{{ $goal->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Виконавець</label>
                                    <select name="assigned_to" x-model="taskForm.assigned_to" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                        <option value="">-</option>
                                        @foreach($ministry->members as $member)
                                            <option value="{{ $member->id }}">{{ $member->full_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Дедлайн</label>
                                    <input type="date" name="due_date" x-model="taskForm.due_date" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Пріоритет</label>
                                    <select name="priority" x-model="taskForm.priority" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                        <option value="low">Низький</option>
                                        <option value="medium">Середній</option>
                                        <option value="high">Високий</option>
                                    </select>
                                </div>
                                <div x-show="editingTaskId">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Статус</label>
                                    <select name="status" x-model="taskForm.status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                        <option value="todo">До виконання</option>
                                        <option value="in_progress">В процесі</option>
                                        <option value="done">Виконано</option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex gap-2 pt-2">
                                <button type="button" @click="showTaskModal = false" class="flex-1 px-3 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg">Скасувати</button>
                                <button type="submit" class="flex-1 px-3 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg" x-text="editingTaskId ? 'Зберегти' : 'Створити'"></button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Linked Tasks (Boards) -->
                <div class="mt-6">
                    <x-linked-cards entityType="ministry" :entityId="$ministry->id" :boards="$boards" createAction="board" />
                </div>
                @endcan

                <a href="{{ route('ministries.index') }}" class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-sm mt-4">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Назад до списку
                </a>
            </div>

            <!-- Songs Library Tab (for worship ministries) -->
            @if($ministry->is_worship_ministry)
            <div x-show="activeTab === 'songs'"{{ $tab !== 'songs' ? ' style="display:none"' : '' }}
                 x-data="songsLibrary()">
                <!-- Header -->
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Бібліотека пісень</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Всього: <span x-text="songs.length"></span> пісень
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        @can('contribute-ministry', $ministry)
                        <a href="{{ route('songs.import.page') }}"
                           class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                            Імпорт
                        </a>
                        <button @click="openCreateModal()"
                           class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition-colors">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Додати пісню
                        </button>
                        @endcan
                    </div>
                </div>

                <!-- Search & Filters -->
                <div class="mb-4 space-y-3">
                    <div class="flex flex-wrap gap-3">
                        <input type="text" x-model="search" placeholder="Пошук пісень..."
                               class="flex-1 min-w-[200px] px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 text-sm">
                        <select x-model="filterArtist"
                                class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                            <option value="">Усі виконавці</option>
                            <template x-for="a in artists" :key="a">
                                <option :value="a" x-text="a"></option>
                            </template>
                        </select>
                        <select x-model="filterBpm"
                                class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                            <option value="">Будь-який темп</option>
                            <option value="slow">Повільні (< 80 BPM)</option>
                            <option value="medium">Середні (80–120 BPM)</option>
                            <option value="fast">Швидкі (> 120 BPM)</option>
                            <option value="none">Без BPM</option>
                        </select>
                        <select x-model="filterUsage"
                                class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                            <option value="">Будь-яке використання</option>
                            <option value="frequent">Часто (5+ разів)</option>
                            <option value="moderate">Помірно (1–4 рази)</option>
                            <option value="never">Ніколи не використані</option>
                        </select>
                        <select x-model="filterContent"
                                class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                            <option value="">Будь-який контент</option>
                            <option value="has_lyrics">Зі словами</option>
                            <option value="has_chords">З акордами</option>
                            <option value="has_youtube">З YouTube</option>
                            <option value="has_spotify">Зі Spotify</option>
                            <option value="no_lyrics">Без слів</option>
                            <option value="no_chords">Без акордів</option>
                        </select>
                    </div>
                    <div x-show="filterArtist || filterBpm || filterUsage || filterContent" class="flex items-center gap-2">
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            Знайдено: <span x-text="filteredSongs.length"></span> пісень
                        </span>
                        <button x-on:click="filterArtist=''; filterBpm=''; filterUsage=''; filterContent=''; search=''; filterKey=''; filterTag=''"
                                class="text-xs text-primary-600 dark:text-primary-400 hover:underline">
                            Скинути фільтри
                        </button>
                    </div>
                </div>

                <!-- Kanban Columns -->
                <template x-if="boardTags.length > 0 && songs.length > 0">
                    <div class="overflow-x-auto pb-4 -mx-2">
                        <div class="flex gap-4 px-2" style="min-width: max-content;">
                            <template x-for="(col, colIdx) in boardTags" :key="col">
                                <div class="w-80 flex-shrink-0 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 flex flex-col max-h-[calc(100vh-280px)]">
                                    <!-- Column Header -->
                                    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between"
                                         :class="colIdx === 0 ? 'bg-blue-50 dark:bg-blue-900/20' : colIdx === 1 ? 'bg-teal-50 dark:bg-teal-900/20' : colIdx === 2 ? 'bg-amber-50 dark:bg-amber-900/20' : 'bg-gray-50 dark:bg-gray-700/50'"
                                         :style="'border-top: 3px solid ' + (colIdx === 0 ? '#3b82f6' : colIdx === 1 ? '#14b8a6' : colIdx === 2 ? '#f59e0b' : '#6b7280')">
                                        <h3 class="font-semibold text-sm" :class="colIdx === 0 ? 'text-blue-700 dark:text-blue-300' : colIdx === 1 ? 'text-teal-700 dark:text-teal-300' : colIdx === 2 ? 'text-amber-700 dark:text-amber-300' : 'text-gray-700 dark:text-gray-300'" x-text="col"></h3>
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full"
                                              :class="colIdx === 0 ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300' : colIdx === 1 ? 'bg-teal-100 text-teal-700 dark:bg-teal-900/40 dark:text-teal-300' : colIdx === 2 ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'"
                                              x-text="getSongsForColumn(col).length"></span>
                                    </div>
                                    <!-- Song Rows -->
                                    <div class="song-column flex-1 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700/50" :data-tag="col">
                                        <template x-for="song in getSongsForColumn(col)" :key="song.id">
                                            <div class="song-item flex items-center gap-2 px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700/30 group" :data-song-id="song.id">
                                                @can('contribute-ministry', $ministry)
                                                <svg class="drag-handle w-4 h-4 flex-shrink-0 text-gray-300 dark:text-gray-600 opacity-0 group-hover:opacity-100 transition-opacity" fill="currentColor" viewBox="0 0 24 24"><circle cx="9" cy="6" r="1.5"/><circle cx="15" cy="6" r="1.5"/><circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/><circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/></svg>
                                                @endcan
                                                <div class="flex-1 min-w-0 cursor-pointer" @click="openSongModal(song)">
                                                    <span class="text-sm text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 truncate block" x-text="song.title"></span>
                                                </div>
                                                @can('contribute-ministry', $ministry)
                                                <div class="relative flex-shrink-0" x-data="{ open: false }">
                                                    <button @click.stop="open = !open" class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded opacity-0 group-hover:opacity-100 transition-opacity" title="Перемістити">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/></svg>
                                                    </button>
                                                    <div x-show="open" @click.away="open = false" x-transition
                                                         class="absolute right-0 top-8 z-50 w-48 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg py-1">
                                                        <template x-for="targetTag in boardTags.filter(t => t !== col)" :key="targetTag">
                                                            <button @click.stop="moveSong(song.id, col, targetTag); open = false"
                                                                    class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                                <span class="text-gray-400 mr-1">&rarr;</span> <span x-text="targetTag"></span>
                                                            </button>
                                                        </template>
                                                        <button @click.stop="moveSong(song.id, col, null); open = false"
                                                                class="w-full text-left px-3 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                            <span class="mr-1">&times;</span> Прибрати тег
                                                        </button>
                                                    </div>
                                                </div>
                                                @endcan
                                            </div>
                                        </template>
                                        <div x-show="getSongsForColumn(col).length === 0" class="px-3 py-6 text-center text-xs text-gray-400">
                                            Немає пісень
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <!-- Untagged Column -->
                            <div class="w-80 flex-shrink-0 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 border-dashed flex flex-col max-h-[calc(100vh-280px)]">
                                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gray-50 dark:bg-gray-700/50" style="border-top: 3px solid #9ca3af">
                                    <h3 class="font-semibold text-gray-500 dark:text-gray-400 text-sm">Без тегу</h3>
                                    <span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 text-xs font-medium rounded-full"
                                          x-text="getUntaggedSongs().length"></span>
                                </div>
                                <div class="song-column flex-1 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700/50" data-tag="">
                                    <template x-for="song in getUntaggedSongs()" :key="song.id">
                                        <div class="song-item flex items-center gap-2 px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700/30 group" :data-song-id="song.id">
                                            @can('contribute-ministry', $ministry)
                                            <svg class="drag-handle w-4 h-4 flex-shrink-0 text-gray-300 dark:text-gray-600 opacity-0 group-hover:opacity-100 transition-opacity" fill="currentColor" viewBox="0 0 24 24"><circle cx="9" cy="6" r="1.5"/><circle cx="15" cy="6" r="1.5"/><circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/><circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/></svg>
                                            @endcan
                                            <div class="flex-1 min-w-0 cursor-pointer" @click="openSongModal(song)">
                                                <span class="text-sm text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 truncate block" x-text="song.title"></span>
                                            </div>
                                            @can('contribute-ministry', $ministry)
                                            <div class="relative flex-shrink-0" x-data="{ open: false }">
                                                <button @click.stop="open = !open" class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded opacity-0 group-hover:opacity-100 transition-opacity" title="Додати тег">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                </button>
                                                <div x-show="open" @click.away="open = false" x-transition
                                                     class="absolute right-0 top-8 z-50 w-48 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg py-1">
                                                    <template x-for="targetTag in boardTags" :key="targetTag">
                                                        <button @click.stop="moveSong(song.id, null, targetTag); open = false"
                                                                class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                            <span class="text-gray-400 mr-1">&rarr;</span> <span x-text="targetTag"></span>
                                                        </button>
                                                    </template>
                                                </div>
                                            </div>
                                            @endcan
                                        </div>
                                    </template>
                                    <div x-show="getUntaggedSongs().length === 0" class="px-3 py-6 text-center text-xs text-gray-400">
                                        Немає пісень
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- No board tags configured — show hint + fallback list -->
                <template x-if="boardTags.length === 0 && songs.length > 0">
                    <div>
                        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-4 mb-4">
                            <p class="text-sm text-amber-800 dark:text-amber-200">
                                Колонки не налаштовані. Перейдіть в <a href="{{ route('settings.index') }}?tab=data" class="underline font-medium">Налаштування → Дані</a>, щоб додати теги-колонки для дошки пісень.
                            </p>
                        </div>
                        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl divide-y divide-gray-100 dark:divide-gray-700/50">
                            <template x-for="song in filteredSongs" :key="song.id">
                                <div @click="openSongModal(song)" class="px-4 py-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/30 flex items-center gap-3">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white truncate" x-text="song.title"></span>
                                    <span class="text-xs text-gray-400 truncate" x-text="song.artist || ''"></span>
                                    <span x-show="song.key" class="ml-auto px-2 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 text-xs font-mono rounded flex-shrink-0" x-text="song.key"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Empty Library -->
                <div x-show="songs.length === 0" class="text-center py-12">
                    <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Немає пісень</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-4">Додайте першу пісню до бібліотеки</p>
                    @can('contribute-ministry', $ministry)
                    <button @click="openCreateModal()" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Додати пісню
                    </button>
                    @endcan
                </div>

                <!-- Song Modal (Create/Edit) -->
                <div x-show="showModal" x-cloak
                     class="fixed inset-0 z-50 overflow-y-auto"
                     @keydown.escape.window="showModal = false">
                    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                        <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                             class="fixed inset-0 bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75"
                             @click="showModal = false"></div>

                        <div x-show="showModal" x-transition:enter="ease-out duration-300"
                             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                             x-transition:leave="ease-in duration-200"
                             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                             class="relative inline-block w-full max-w-3xl p-6 my-8 text-left align-middle bg-white dark:bg-gray-800 rounded-2xl shadow-xl transform transition-all max-h-[90vh] overflow-y-auto">

                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-xl font-semibold text-gray-900 dark:text-white" x-text="editingId ? 'Редагувати пісню' : 'Додати пісню'"></h3>
                                <button @click="showModal = false" class="text-gray-400 hover:text-gray-500">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>

                            <form @submit.prevent="saveSong()">
                                <div class="space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Назва <span class="text-red-500">*</span></label>
                                            <input type="text" x-model="form.title" required
                                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Автор</label>
                                            <input type="text" x-model="form.artist" list="artists-list-modal"
                                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                                            <datalist id="artists-list-modal">
                                                <template x-for="artist in artists" :key="artist">
                                                    <option :value="artist"></option>
                                                </template>
                                            </datalist>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Тональність</label>
                                            <div class="relative">
                                                <input type="text"
                                                       x-model="keyQuery"
                                                       @focus="keyDropdownOpen = true"
                                                       @click.away="keyDropdownOpen = false"
                                                       @keydown.escape="keyDropdownOpen = false"
                                                       @keydown.enter.prevent="filteredKeys.length && selectKey(filteredKeys[0][0])"
                                                       placeholder="Почніть вводити..."
                                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                                                <div x-show="keyDropdownOpen && filteredKeys.length > 0" x-transition
                                                     class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg max-h-48 overflow-auto">
                                                    <div @click="selectKey('')"
                                                         class="px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm text-gray-500">
                                                        Не вказано
                                                    </div>
                                                    <template x-for="[key, label] in filteredKeys" :key="key">
                                                        <div @click="selectKey(key)"
                                                             class="px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm"
                                                             :class="form.key === key ? 'bg-primary-50 dark:bg-primary-900/30' : ''">
                                                            <span x-text="key" class="font-medium"></span>
                                                            <span x-text="' — ' + label" class="text-gray-500 text-xs"></span>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">BPM</label>
                                            <input type="number" x-model="form.bpm" min="30" max="300"
                                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Теги</label>
                                        <div class="flex flex-wrap gap-2 mb-2">
                                            <template x-for="tag in allTags" :key="tag">
                                                <label class="inline-flex items-center cursor-pointer">
                                                    <input type="checkbox" :value="tag" x-model="form.tags" class="sr-only peer">
                                                    <span class="px-3 py-1.5 rounded-full text-sm border transition-all peer-checked:bg-primary-600 peer-checked:text-white peer-checked:border-primary-600 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:border-primary-400" x-text="tag"></span>
                                                </label>
                                            </template>
                                        </div>
                                        <input type="text" x-model="form.new_tag" placeholder="Нові теги через кому"
                                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Коментарі</label>
                                        <textarea x-model="form.notes" rows="2"
                                                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"
                                                  placeholder="Нотатки для команди"></textarea>
                                    </div>

                                    <!-- Resource Links -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Посилання на ресурси</label>
                                        <template x-for="(link, index) in form.resource_links" :key="index">
                                            <div class="flex gap-2 mb-2">
                                                <input x-model="link.label" type="text" placeholder="Назва"
                                                       class="flex-1 px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500">
                                                <input x-model="link.url" type="url" placeholder="https://..."
                                                       class="flex-[2] px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500">
                                                <button type="button" @click="form.resource_links.splice(index, 1)"
                                                        class="px-2 py-1.5 text-gray-400 hover:text-red-500 transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </template>
                                        <button type="button" @click="form.resource_links.push({label: '', url: ''})"
                                                class="inline-flex items-center text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                            Додати посилання
                                        </button>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Текст</label>
                                        <textarea x-model="form.lyrics" rows="4"
                                                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 font-mono text-sm"></textarea>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Текст з акордами</label>
                                        <textarea x-model="form.chords" rows="6"
                                                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 font-mono text-sm"
                                                  placeholder="[C]Святий, Святий, [Am]Святий..."></textarea>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">CCLI</label>
                                            <input type="text" x-model="form.ccli_number"
                                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">YouTube</label>
                                            <input type="url" x-model="form.youtube_url"
                                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Spotify</label>
                                            <input type="url" x-model="form.spotify_url"
                                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                                        </div>
                                    </div>
                                </div>

                                <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                                    <button type="button" @click="showModal = false; if(editingId && viewingSong) showViewModal = true; resetForm();"
                                            class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                        Скасувати
                                    </button>
                                    <button type="submit" :disabled="saving"
                                            class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50">
                                        <span x-show="!saving" x-text="editingId ? 'Зберегти' : 'Додати'"></span>
                                        <span x-show="saving">Збереження...</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Song View Modal -->
                <div x-show="showViewModal" x-cloak
                     class="fixed inset-0 z-50 overflow-y-auto"
                     @keydown.escape.window="showViewModal = false">
                    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                        <div x-show="showViewModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                             class="fixed inset-0 bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75"
                             @click="showViewModal = false"></div>

                        <div x-show="showViewModal" x-transition:enter="ease-out duration-300"
                             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                             x-transition:leave="ease-in duration-200"
                             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                             class="relative inline-block w-full max-w-3xl p-6 my-8 text-left align-middle bg-white dark:bg-gray-800 rounded-2xl shadow-xl transform transition-all max-h-[90vh] overflow-y-auto">

                            <template x-if="viewingSong">
                                <div>
                                    <!-- Header -->
                                    <div class="flex items-start justify-between mb-4">
                                        <div>
                                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white" x-text="viewingSong.title"></h3>
                                            <p x-show="viewingSong.artist" class="text-gray-500 dark:text-gray-400 mt-1" x-text="viewingSong.artist"></p>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span x-show="viewingSong.key" class="px-3 py-1 bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 text-sm font-mono rounded-lg" x-text="viewingSong.key"></span>
                                            <span x-show="viewingSong.bpm" class="px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 text-sm rounded-lg" x-text="viewingSong.bpm + ' BPM'"></span>
                                            <button @click="showViewModal = false" class="ml-2 text-gray-400 hover:text-gray-500">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Tags -->
                                    <div x-show="viewingSong.tags && viewingSong.tags.length > 0" class="flex flex-wrap gap-2 mb-4">
                                        <template x-for="tag in viewingSong.tags" :key="tag">
                                            <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 text-sm rounded-full" x-text="tag"></span>
                                        </template>
                                    </div>

                                    <!-- Notes -->
                                    <div x-show="viewingSong.notes" class="mb-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                                        <p class="text-sm text-yellow-800 dark:text-yellow-200">
                                            <span class="font-medium">Коментарі:</span> <span x-text="viewingSong.notes"></span>
                                        </p>
                                    </div>

                                    <!-- Resource Links -->
                                    <div x-show="viewingSong.resource_links && viewingSong.resource_links.length > 0" class="mb-4">
                                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ресурси</h4>
                                        <div class="flex flex-wrap gap-2">
                                            <template x-for="link in (viewingSong.resource_links || [])" :key="link.url">
                                                <a :href="link.url" target="_blank" rel="noopener noreferrer"
                                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-sm rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors">
                                                    <span x-text="link.label"></span>
                                                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                    </svg>
                                                </a>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Links -->
                                    <div x-show="viewingSong.youtube_url || viewingSong.spotify_url" class="flex flex-wrap gap-2 mb-4">
                                        <a x-show="viewingSong.youtube_url" :href="viewingSong.youtube_url" target="_blank"
                                           class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700">
                                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                                            YouTube
                                        </a>
                                        <a x-show="viewingSong.spotify_url" :href="viewingSong.spotify_url" target="_blank"
                                           class="inline-flex items-center px-4 py-2 bg-[#1DB954] text-white text-sm rounded-lg hover:bg-[#1ed760]">
                                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/></svg>
                                            Spotify
                                        </a>
                                    </div>

                                    <!-- Chords -->
                                    <div x-show="viewingSong.chords" class="mb-4">
                                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Текст з акордами</h4>
                                        <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg font-mono text-sm whitespace-pre-wrap max-h-96 overflow-y-auto" x-html="formatChords(viewingSong.chords)"></div>
                                    </div>

                                    <!-- Lyrics (if no chords) -->
                                    <div x-show="viewingSong.lyrics && !viewingSong.chords" class="mb-4">
                                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Текст</h4>
                                        <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg text-sm whitespace-pre-wrap max-h-96 overflow-y-auto" x-text="viewingSong.lyrics"></div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            Використано <span x-text="viewingSong.times_used || 0"></span> раз
                                        </div>
                                        @can('contribute-ministry', $ministry)
                                        <div class="flex items-center gap-2">
                                            <button @click="showViewModal = false; openEditModal(viewingSong)"
                                                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-lg transition-colors">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                                Редагувати
                                            </button>
                                            <button @click="showViewModal = false; deleteSong(viewingSong)"
                                                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                Видалити
                                            </button>
                                        </div>
                                        @endcan
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
            @endif


            <!-- Settings Tab -->
            @can('manage-ministry', $ministry)
            <div x-show="activeTab === 'settings'"{{ $tab !== 'settings' ? ' style="display:none"' : '' }}
                 x-data="settingsTab()"
                 x-init="init()">
                <div class="max-w-2xl space-y-8">
                    <!-- General Settings -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Загальні налаштування</h3>
                        <form @submit.prevent="submit($refs.settingsForm)" x-ref="settingsForm"
                              x-data="{ ...ajaxForm({ url: '{{ route('ministries.update', $ministry) }}', method: 'PUT', stayOnPage: true, onSuccess() { _updateMinistryHeader(this); } }) }"
                              class="space-y-4">

                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Назва</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $ministry->name) }}" required
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            </div>

                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Опис</label>
                                <textarea name="description" id="description" rows="2"
                                          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">{{ old('description', $ministry->description) }}</textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Лідер</label>
                                <x-person-select name="leader_id" :people="$availablePeople->merge($ministry->members)->merge($ministry->leader ? collect([$ministry->leader]) : collect())->unique('id')->sortBy('last_name')" :selected="old('leader_id', $ministry->leader_id)" placeholder="Пошук лідера..." />
                            </div>

                            <div>
                                <label for="color" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Колір</label>
                                <input type="color" name="color" id="color" value="{{ old('color', $ministry->color ?? '#3b82f6') }}"
                                       class="w-16 h-10 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer">
                            </div>

                            <div class="flex items-center gap-3 pt-2">
                                <input type="hidden" name="is_worship_ministry" value="0">
                                <input type="checkbox" name="is_worship_ministry" id="is_worship_ministry" value="1"
                                       {{ old('is_worship_ministry', $ministry->is_worship_ministry) ? 'checked' : '' }}
                                       class="w-5 h-5 rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500">
                                <label for="is_worship_ministry" class="text-sm text-gray-700 dark:text-gray-300">
                                    <span class="font-medium">Музичне служіння</span>
                                    <span class="block text-gray-500 dark:text-gray-400 text-xs">Показувати бібліотеку пісень та Music Stand</span>
                                </label>
                            </div>

                            <div class="pt-4">
                                <button type="submit" :disabled="saving" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                                    Зберегти зміни
                                </button>
                            </div>
                        </form>
                    </div>

                    @if($ministry->is_worship_ministry)
                    <hr class="border-gray-200 dark:border-gray-700">

                    <!-- Song Board Tags -->
                    <div x-data="songBoardTagsConfig()">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Колонки дошки пісень</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Теги, які стануть колонками на вкладці "Пісні"</p>

                        <div class="space-y-2 mb-4">
                            <template x-for="(tag, i) in tags" :key="i">
                                <div class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                                    <span class="text-gray-900 dark:text-white text-sm" x-text="tag"></span>
                                    <button @click="removeTag(i)" class="text-red-500 hover:text-red-700 text-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </template>
                            <template x-if="tags.length === 0">
                                <p class="text-gray-400 text-sm py-2">Немає колонок. Додайте теги нижче.</p>
                            </template>
                        </div>

                        <form @submit.prevent="addTag()" class="flex gap-2">
                            <input type="text" x-model="newTag" placeholder="Новий тег (напр. Регулярні)..." maxlength="50"
                                   class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500">
                            <button type="submit" :disabled="saving || !newTag.trim()"
                                    class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg text-sm transition-colors disabled:opacity-50">
                                Додати
                            </button>
                        </form>
                    </div>
                    @endif

                    <hr class="border-gray-200 dark:border-gray-700">

                    <!-- Access Settings -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Налаштування доступу</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                            Визначте, хто може бачити цю команду та її деталі
                        </p>

                    <div class="space-y-3">
                        <!-- Public -->
                        <label class="flex items-start gap-3 p-4 border rounded-xl cursor-pointer transition-all"
                               :class="visibility === 'public' ? 'border-green-500 bg-green-50 dark:bg-green-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'">
                            <input type="radio" name="visibility" value="public" x-model="visibility" @change="saveVisibility()" class="mt-1 w-5 h-5 text-green-600 focus:ring-green-500 border-gray-300 dark:border-gray-600">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span class="font-medium text-gray-900 dark:text-white">Всі користувачі</span>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Всі користувачі церкви можуть бачити цю команду</p>
                            </div>
                        </label>

                        <!-- Members -->
                        <label class="flex items-start gap-3 p-4 border rounded-xl cursor-pointer transition-all"
                               :class="visibility === 'members' ? 'border-amber-500 bg-amber-50 dark:bg-amber-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'">
                            <input type="radio" name="visibility" value="members" x-model="visibility" @change="saveVisibility()" class="mt-1 w-5 h-5 text-amber-600 focus:ring-amber-500 border-gray-300 dark:border-gray-600">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                    </svg>
                                    <span class="font-medium text-gray-900 dark:text-white">Тільки учасники команди</span>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Тільки учасники цієї команди та адміністратори</p>
                            </div>
                        </label>

                        <!-- Leaders -->
                        <label class="flex items-start gap-3 p-4 border rounded-xl cursor-pointer transition-all"
                               :class="visibility === 'leaders' ? 'border-purple-500 bg-purple-50 dark:bg-purple-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'">
                            <input type="radio" name="visibility" value="leaders" x-model="visibility" @change="saveVisibility()" class="mt-1 w-5 h-5 text-purple-600 focus:ring-purple-500 border-gray-300 dark:border-gray-600">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                    <span class="font-medium text-gray-900 dark:text-white">Тільки лідери служінь</span>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Тільки адміністратори та лідери всіх служінь церкви</p>
                            </div>
                        </label>

                        <!-- Specific -->
                        <label class="flex items-start gap-3 p-4 border rounded-xl cursor-pointer transition-all"
                               :class="visibility === 'specific' ? 'border-red-500 bg-red-50 dark:bg-red-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'">
                            <input type="radio" name="visibility" value="specific" x-model="visibility" @change="saveVisibility()" class="mt-1 w-5 h-5 text-red-600 focus:ring-red-500 border-gray-300 dark:border-gray-600">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    <span class="font-medium text-gray-900 dark:text-white">Тільки конкретні люди</span>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Тільки адміністратори та люди, вибрані нижче</p>
                            </div>
                        </label>
                    </div>

                    <!-- Additional People with Access -->
                    <div class="mt-6 p-4 border rounded-xl"
                         :class="visibility === 'specific' ? 'border-red-300 dark:border-red-700 bg-red-50 dark:bg-red-900/20' : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30'">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-5 h-5" :class="visibility === 'specific' ? 'text-red-500' : 'text-blue-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                            <h4 class="font-medium text-gray-900 dark:text-white" x-text="visibility === 'specific' ? 'Люди з доступом' : 'Додаткові люди з доступом'"></h4>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-3" x-text="visibility === 'specific' ? 'Тільки ці люди (та адміністратори) матимуть доступ до команди' : 'Ці люди матимуть доступ незалежно від вибраної опції вище'"></p>

                        <!-- Selected people tags -->
                        <div class="flex flex-wrap gap-2 mb-3" x-show="allowedPeople.length > 0">
                            <template x-for="person in allowedPeople" :key="person.id">
                                <span class="inline-flex items-center gap-2 px-2 py-1 bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 text-sm rounded-lg">
                                    <template x-if="person.photo">
                                        <img :src="person.photo" class="w-6 h-6 rounded-full object-cover">
                                    </template>
                                    <template x-if="!person.photo">
                                        <div class="w-6 h-6 rounded-full bg-primary-500 flex items-center justify-center">
                                            <span class="text-xs text-white font-medium" x-text="person.initials"></span>
                                        </div>
                                    </template>
                                    <span x-text="person.name"></span>
                                    <button type="button" @click="removePerson(person.id)" class="hover:text-primary-900 dark:hover:text-primary-100">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </span>
                            </template>
                        </div>

                        <!-- Select people dropdown -->
                        <div class="flex gap-2">
                            <select x-model="selectedPersonId" class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                <option value="">Оберіть людину...</option>
                                <template x-for="person in availablePeopleFiltered" :key="person.id">
                                    <option :value="person.id" x-text="person.full_name"></option>
                                </template>
                            </select>
                            <button type="button" @click="addSelectedPerson()" :disabled="!selectedPersonId"
                                    class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                Додати
                            </button>
                        </div>
                    </div>

                    <!-- Save indicator -->
                    <div class="mt-4 flex items-center gap-2" x-show="saved" x-transition>
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-sm text-green-600 dark:text-green-400">Збережено</span>
                    </div>

                </div>

                    <hr class="border-gray-200 dark:border-gray-700">

                    <!-- Ministry Roles Settings (AJAX) -->
                    <div x-data="ministryRolesManager()" x-init="init(@js($ministryRoles->map(fn($r) => ['id' => $r->id, 'name' => $r->name, 'icon' => $r->icon ?? '', 'color' => $r->color ?? '#3b82f6'])->values()), '{{ route('ministries.ministry-roles.store', $ministry) }}', '{{ url('ministries/' . $ministry->id . '/ministry-roles') }}')">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Ролі служіння</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            @if($ministry->is_worship_ministry)
                                Налаштуйте інструменти та ролі для команди прославлення
                            @else
                                Налаштуйте ролі для команди (напр. Камера, Звук, Стрім)
                            @endif
                        </p>

                        <!-- Roles List -->
                        <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl">
                            <template x-if="roles.length > 0">
                                <div class="divide-y divide-gray-200 dark:divide-gray-600">
                                    <template x-for="role in roles" :key="role.id">
                                        <div class="p-3 flex items-center justify-between group">
                                            <div class="flex items-center gap-3">
                                                <div class="w-9 h-9 rounded-lg flex items-center justify-center text-base"
                                                     :style="`background-color: ${role.color}20; color: ${role.color}`"
                                                     x-text="role.icon || role.name.charAt(0)"></div>
                                                <span class="font-medium text-gray-900 dark:text-white text-sm" x-text="role.name"></span>
                                            </div>
                                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <button type="button" @click="openEdit(role)"
                                                        class="p-1.5 text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 rounded">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                    </svg>
                                                </button>
                                                <button type="button" @click="deleteRole(role.id)"
                                                        class="p-1.5 text-gray-400 hover:text-red-600 dark:hover:text-red-400 rounded">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                            <template x-if="roles.length === 0">
                                <div class="p-6 text-center">
                                    <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-gray-100 dark:bg-gray-600 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                    </div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Немає ролей. Додайте свою першу роль.</p>
                                </div>
                            </template>

                            <!-- Add Role Form -->
                            <div class="p-3 border-t border-gray-200 dark:border-gray-600">
                                <div class="flex flex-wrap gap-2">
                                    <input type="text" x-model="newName" placeholder="Назва ролі (напр. Камера, Звук)"
                                           @keydown.enter.prevent="addRole(newName, newIcon, newColor)"
                                           class="flex-1 min-w-[150px] px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                    <div class="relative w-14">
                                        <input type="text" x-model="newIcon" placeholder="🔧" maxlength="5"
                                               class="w-full px-2 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-center">
                                        <button type="button" x-show="newIcon" @click="newIcon = ''"
                                                class="absolute -right-1 -top-1 w-4 h-4 bg-gray-300 dark:bg-gray-500 hover:bg-red-500 text-white rounded-full flex items-center justify-center text-[10px] leading-none transition-colors"
                                                title="Очистити">×</button>
                                    </div>
                                    <input type="color" x-model="newColor"
                                           class="w-10 h-10 rounded-lg border border-gray-300 dark:border-gray-600 cursor-pointer">
                                    <button type="button" @click="addRole(newName, newIcon, newColor)"
                                            :disabled="!newName || loading"
                                            class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 disabled:opacity-50 transition-colors">
                                        <span x-show="!loading">Додати</span>
                                        <span x-show="loading">...</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Suggested Defaults -->
                        <template x-if="availableDefaults.length > 0">
                            <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
                                <h4 class="text-sm font-medium text-blue-800 dark:text-blue-300 mb-2">Швидке додавання:</h4>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="def in availableDefaults" :key="def.name">
                                        <button type="button" @click="addRole(def.name, def.icon, def.color)"
                                                :disabled="loading"
                                                class="px-2.5 py-1 text-xs bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-600 transition-colors disabled:opacity-50">
                                            <span x-text="def.icon + ' ' + def.name"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- Edit Role Modal -->
                        <div x-show="showEditModal" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="showEditModal = false" @keydown.escape.window="showEditModal = false">
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6 w-full max-w-md mx-4" @click.stop>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Редагувати роль</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Назва</label>
                                        <input type="text" x-model="editName" @keydown.enter.prevent="saveEdit()"
                                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Іконка</label>
                                            <div class="relative">
                                                <input type="text" x-model="editIcon" maxlength="5"
                                                       class="w-full px-3 py-2 pr-8 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-center">
                                                <button type="button" x-show="editIcon" @click="editIcon = ''"
                                                        class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 transition-colors"
                                                        title="Очистити іконку">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Колір</label>
                                            <input type="color" x-model="editColor"
                                                   class="w-full h-10 rounded-lg border border-gray-300 dark:border-gray-600 cursor-pointer">
                                        </div>
                                    </div>
                                </div>
                                <div class="flex justify-end gap-2 mt-6">
                                    <button type="button" @click="showEditModal = false" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                                        Скасувати
                                    </button>
                                    <button type="button" @click="saveEdit()" :disabled="!editName || loading"
                                            class="px-4 py-2 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 disabled:opacity-50 transition-colors">
                                        Зберегти
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="border-gray-200 dark:border-gray-700">

                    <!-- Danger Zone -->
                    <div class="p-6 border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/10 rounded-xl">
                        <h3 class="text-lg font-semibold text-red-600 dark:text-red-400 mb-2">Небезпечна зона</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            Видалення команди є незворотнім. Всі дані команди (цілі, задачі, ресурси) будуть втрачені.
                        </p>
                        <button type="button"
                                @click="ajaxDelete('{{ route('ministries.destroy', $ministry) }}', '{{ __('messages.confirm_delete_ministry_named', ['name' => $ministry->name]) }}', null, '{{ route('ministries.index') }}')"
                                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors">
                            Видалити команду
                        </button>
                    </div>
            </div>
            </div>
            @endcan

            <!-- Delete ministry handled via ajaxDelete -->
        </div>
    </div>

</div>

@push('styles')
<style>
    /* Song kanban drag-and-drop */
    .song-item .drag-handle { cursor: grab; }
    .song-item.sortable-chosen .drag-handle { cursor: grabbing; }
    .song-item.sortable-chosen { opacity: 0.8; }

    /* Custom document editor */
    .doc-toolbar { display: flex; flex-wrap: wrap; gap: 2px; padding: 6px 10px; border-bottom: 1px solid #e5e7eb; background: #f9fafb; position: sticky; top: 0; z-index: 10; }
    .dark .doc-toolbar { background: #1e293b; border-bottom-color: #374151; }
    .doc-toolbar .tb-btn { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 6px; border: none; background: none; cursor: pointer; color: #4b5563; font-size: 14px; transition: all .15s; }
    .doc-toolbar .tb-btn:hover { background: #e5e7eb; color: #111827; }
    .dark .doc-toolbar .tb-btn { color: #9ca3af; }
    .dark .doc-toolbar .tb-btn:hover { background: #374151; color: #f3f4f6; }
    .doc-toolbar .tb-btn.active { background: #dbeafe; color: #2563eb; }
    .dark .doc-toolbar .tb-btn.active { background: #1e3a5f; color: #60a5fa; }
    .doc-toolbar .tb-sep { width: 1px; height: 24px; background: #d1d5db; margin: 4px 4px; align-self: center; }
    .dark .doc-toolbar .tb-sep { background: #4b5563; }
    .doc-toolbar select { height: 32px; border-radius: 6px; border: 1px solid #d1d5db; background: #fff; color: #374151; font-size: 13px; padding: 0 6px; cursor: pointer; }
    .dark .doc-toolbar select { background: #1f2937; border-color: #4b5563; color: #d1d5db; }
    .doc-toolbar input[type="color"] { width: 32px; height: 32px; border: 1px solid #d1d5db; border-radius: 6px; padding: 2px; cursor: pointer; background: #fff; }

    .doc-editable { min-height: 400px; padding: 32px 40px; font-family: Arial, sans-serif; font-size: 15px; line-height: 1.7; color: #202124; outline: none; }
    .dark .doc-editable { color: #e2e8f0; }
    .doc-editable:empty::before { content: attr(data-placeholder); color: #9ca3af; pointer-events: none; }
    .doc-editable h1 { font-size: 26px; margin: 20px 0 8px; font-weight: 700; }
    .doc-editable h2 { font-size: 22px; margin: 18px 0 6px; font-weight: 600; }
    .doc-editable h3 { font-size: 18px; margin: 16px 0 4px; font-weight: 600; }
    .doc-editable p { margin: 0 0 8px; }
    .doc-editable blockquote { border-left: 4px solid #4285f4; padding-left: 16px; color: #5f6368; margin: 16px 0; }
    .doc-editable ul, .doc-editable ol { padding-left: 24px; margin: 8px 0; }
    .doc-editable table { border-collapse: collapse; width: 100%; margin: 12px 0; }
    .doc-editable td, .doc-editable th { border: 1px solid #dadce0; padding: 8px 12px; }
    .doc-editable a { color: #1a73e8; text-decoration: underline; }
    .doc-editable img { max-width: 100%; height: auto; border-radius: 4px; }
    @media (max-width: 640px) {
        .doc-editable { padding: 20px 16px; }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
function _getOrCreateResourceList() {
    var list = document.getElementById('ministry-resources-list');
    if (!list) {
        var empty = document.getElementById('ministry-resources-empty');
        list = document.createElement('div');
        list.id = 'ministry-resources-list';
        list.className = 'space-y-1.5';
        if (empty) {
            empty.parentNode.insertBefore(list, empty);
            empty.remove();
        } else {
            return null;
        }
    }
    return list;
}

function _addMinistryResourceFolder(ctx, data) {
    ctx.showCreateFolder = false;
    var list = _getOrCreateResourceList();
    if (!list) { window.location.reload(); return; }
    var name = ctx.$refs.createFolderForm.querySelector('[name="name"]').value;
    var safeName = name.replace(/&/g, '\x26amp;').replace(/\x3C/g, '\x26lt;').replace(/>/g, '\x26gt;');
    var today = new Date();
    var dateStr = String(today.getDate()).padStart(2,'0') + '.' + String(today.getMonth()+1).padStart(2,'0') + '.' + today.getFullYear();
    var el = document.createElement('div');
    el.className = 'group flex items-center gap-3 p-2.5 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer transition-colors';
    el.setAttribute('data-resource-id', data.id);
    el.onclick = function() { Livewire.navigate('/ministries/{{ $ministry->id }}/resources/folder/' + data.id); };
    el.innerHTML = '\x3Cdiv class="w-9 h-9 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center flex-shrink-0">\x3Csvg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="currentColor" viewBox="0 0 20 20">\x3Cpath d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>\x3C/svg>\x3C/div>\x3Cdiv class="flex-1 min-w-0">\x3Cp class="text-sm font-medium text-gray-900 dark:text-white truncate">' + safeName + '\x3C/p>\x3Cp class="text-xs text-gray-500 dark:text-gray-400">Папка \x3Cspan class="mx-1">\x26middot;\x3C/span> ' + dateStr + '\x3C/p>\x3C/div>';
    list.prepend(el);
    if (window.showGlobalToast) showGlobalToast('Папку створено', 'success');
}

function _addMinistryResourceFile(list, fileName) {
    var safeName = fileName.replace(/&/g, '\x26amp;').replace(/\x3C/g, '\x26lt;').replace(/>/g, '\x26gt;');
    var today = new Date();
    var dateStr = String(today.getDate()).padStart(2,'0') + '.' + String(today.getMonth()+1).padStart(2,'0') + '.' + today.getFullYear();
    var el = document.createElement('div');
    el.className = 'group flex items-center gap-3 p-2.5 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer transition-colors';
    el.innerHTML = '\x3Cdiv class="w-9 h-9 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center flex-shrink-0">\x3Csvg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">\x3Cpath stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>\x3C/svg>\x3C/div>\x3Cdiv class="flex-1 min-w-0">\x3Cp class="text-sm font-medium text-gray-900 dark:text-white truncate">' + safeName + '\x3C/p>\x3Cp class="text-xs text-gray-500 dark:text-gray-400">' + dateStr + '\x3C/p>\x3C/div>';
    list.appendChild(el);
}

function _updateMinistryHeader(ctx) {
    var form = ctx.$refs.settingsForm;
    if (!form) return;
    var name = form.querySelector('[name="name"]');
    var color = form.querySelector('[name="color"]');
    if (name) {
        var h = document.getElementById('ministry-name');
        if (h) h.textContent = name.value;
        document.title = name.value + ' | Ministrify';
    }
    if (color) {
        var dot = document.querySelector('.w-4.h-4.rounded-full[style*="background-color"]');
        if (dot) dot.style.backgroundColor = color.value;
    }
}

/* === Custom Document Editor (zero dependencies) === */
function docExec(cmd, val) { document.execCommand(cmd, false, val || null); document.getElementById('doc-editable')?.focus(); }
function docExecBlock(tag) { if (!tag) return; document.execCommand('formatBlock', false, tag); document.getElementById('doc-editable')?.focus(); }
function docExecFontSize(size) { if (!size) return; document.execCommand('fontSize', false, size); document.getElementById('doc-editable')?.focus(); }
function docExecColor(cmd, val) { document.execCommand(cmd, false, val); document.getElementById('doc-editable')?.focus(); }
function docInsertLink() {
    const url = prompt('URL посилання:', 'https://');
    if (url) document.execCommand('createLink', false, url);
    document.getElementById('doc-editable')?.focus();
}
function docInsertImage() {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';
    input.onchange = () => {
        const file = input.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = (e) => {
            document.execCommand('insertImage', false, e.target.result);
        };
        reader.readAsDataURL(file);
    };
    input.click();
}

function resourcesManager() {
    return {
        showCreateFolder: false,
        showRename: false,
        showDocEditor: false,
        renameName: '',
        menuOpen: false,
        menuX: 0,
        menuY: 0,
        selectedId: null,
        selectedName: '',
        previewFile: null,
        uploading: false,
        uploadError: '',
        docId: null,
        docName: '',
        docContent: '',
        docSaving: false,
        docSaved: false,
        _docCreated: false,

        openMenu(id, name, event) {
            event.stopPropagation();
            this.selectedId = id;
            this.selectedName = name;
            this.menuX = Math.min(event.clientX, window.innerWidth - 200);
            this.menuY = Math.min(event.clientY, window.innerHeight - 100);
            this.menuOpen = true;
        },

        showRenameModal() {
            this.menuOpen = false;
            this.renameName = this.selectedName;
            this.showRename = true;
            this.$nextTick(() => this.$refs.renameInput?.focus());
        },

        async submitRename() {
            if (!this.renameName.trim()) return;
            try {
                const response = await fetch(`/resources/${this.selectedId}/rename`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ name: this.renameName })
                });
                if (response.ok) {
                    this.showRename = false;
                    showToast('success', 'Перейменовано!');
                    const row = this.$el.querySelector(`[data-resource-id="${this.selectedId}"] .text-sm.font-medium`);
                    if (row) row.textContent = this.renameName;
                } else {
                    const err = await response.json().catch(() => ({}));
                    alert(err.message || 'Помилка перейменування');
                }
            } catch (error) {
                alert('Помилка перейменування');
            }
        },

        showPreview(file) {
            this.previewFile = file;
        },

        async uploadFile(event) {
            const files = event.target.files;
            if (!files.length) return;
            this.uploading = true;
            this.uploadError = '';

            for (const file of files) {
                const formData = new FormData();
                formData.append('file', file);
                formData.append('parent_id', '{{ $currentFolder?->id ?? "" }}');
                formData.append('_token', '{{ csrf_token() }}');

                try {
                    const response = await fetch('{{ route("ministries.resources.upload", $ministry) }}', {
                        method: 'POST',
                        body: formData
                    });
                    if (!response.ok) {
                        const data = await response.json().catch(() => ({}));
                        this.uploadError = data.message || 'Помилка завантаження';
                    }
                } catch (error) {
                    this.uploadError = 'Помилка завантаження';
                }
            }

            event.target.value = '';
            this.uploading = false;
            if (!this.uploadError) {
                var list = _getOrCreateResourceList();
                if (!list) { window.location.reload(); return; }
                for (var i = 0; i < files.length; i++) {
                    _addMinistryResourceFile(list, files[i].name);
                }
                if (window.showGlobalToast) showGlobalToast('Файл завантажено', 'success');
            }
        },

        deleteItem() {
            this.menuOpen = false;
            const id = this.selectedId;
            ajaxDelete(`/resources/${id}`, '{{ __('messages.confirm_delete_item') }}', () => {
                const row = this.$el.querySelector(`[data-resource-id="${id}"]`);
                if (row) row.remove();
            });
        },

        async createAndOpenDocument() {
            try {
                const response = await fetch('{{ route("ministries.resources.document.create", $ministry) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        name: 'Новий документ',
                        parent_id: '{{ $currentFolder?->id ?? "" }}' || null
                    })
                });

                if (response.ok) {
                    const data = await response.json().catch(() => ({}));
                    this._docCreated = true;
                    this.openDocument({ id: data.id, name: 'Новий документ', content: '' });
                } else {
                    const err = await response.json().catch(() => ({}));
                    alert(err.message || 'Помилка');
                }
            } catch (error) {
                alert('Помилка створення документа');
            }
        },

        async openDocument(doc) {
            this.docId = doc.id;
            this.docName = doc.name;
            this.docContent = doc.content || '';
            this.showDocEditor = true;
            this.docSaved = false;
            document.body.style.overflow = 'hidden';

            await this.$nextTick();

            const editable = document.getElementById('doc-editable');
            if (editable) {
                editable.innerHTML = this.docContent;
                editable.focus();
                // Ctrl+S
                editable.onkeydown = (e) => {
                    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                        e.preventDefault();
                        this.saveDocument();
                    }
                };
            }
        },

        async saveDocument() {
            const editable = document.getElementById('doc-editable');
            if (!editable || !this.docId) return;
            this.docSaving = true;
            this.docSaved = false;

            try {
                const content = editable.innerHTML;
                const response = await fetch(`/resources/${this.docId}/content`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ name: this.docName, content })
                });

                if (response.ok) {
                    this.docSaved = true;
                    setTimeout(() => this.docSaved = false, 3000);
                } else {
                    alert('Помилка збереження');
                }
            } catch (error) {
                alert('Помилка збереження');
            } finally {
                this.docSaving = false;
            }
        },

        closeDocEditor() {
            const editable = document.getElementById('doc-editable');
            if (editable) editable.innerHTML = '';
            document.body.style.overflow = '';
            this.showDocEditor = false;
            if (this._docCreated && this.docId) {
                this._docCreated = false;
                this._addDocToList(this.docId, this.docName);
            }
        },

        _addDocToList(id, name) {
            let list = document.getElementById('ministry-resources-list');
            if (!list) {
                list = _getOrCreateResourceList();
                if (!list) return;
            }
            const self = this;
            const today = new Date();
            const dd = String(today.getDate()).padStart(2, '0');
            const mm = String(today.getMonth() + 1).padStart(2, '0');
            const yyyy = today.getFullYear();
            const el = document.createElement('div');
            el.className = 'group flex items-center gap-3 p-2.5 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer transition-colors';
            el.onclick = () => self.openDocument({ id, name, content: '' });
            el.innerHTML = `
                <div class="w-9 h-9 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">${name}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Документ <span class="mx-1">&middot;</span> ${dd}.${mm}.${yyyy}</p>
                </div>`;
            list.prepend(el);
        }
    }
}

@php
    $allowedPeopleData = collect($ministry->allowed_person_ids ?? [])->map(function($id) {
        $p = \App\Models\Person::find($id);
        if (!$p) return ['id' => $id, 'name' => 'Unknown', 'photo' => null, 'initials' => '?'];
        return [
            'id' => $id,
            'name' => $p->full_name,
            'photo' => $p->photo ? \Illuminate\Support\Facades\Storage::url($p->photo) : null,
            'initials' => mb_substr($p->first_name, 0, 1) . mb_substr($p->last_name, 0, 1)
        ];
    })->values();

    $allPeopleData = $registeredUsers->map(fn($p) => [
        'id' => $p->id,
        'full_name' => $p->full_name,
        'photo' => $p->photo ? \Illuminate\Support\Facades\Storage::url($p->photo) : null,
        'initials' => mb_substr($p->first_name, 0, 1) . mb_substr($p->last_name, 0, 1)
    ])->values();
@endphp
function goalsManager() {
    return {
        editingVision: false,
        showGoalModal: false,
        showTaskModal: false,
        editingGoalId: null,
        editingTaskId: null,
        goalForm: { title: '', description: '', period: '', period_start: '', period_end: '', due_date: '', priority: 'medium', status: 'active' },
        taskForm: { title: '', description: '', goal_id: '', assigned_to: '', due_date: '', priority: 'medium', status: 'todo' },
        resetGoalForm() {
            this.editingGoalId = null;
            this.goalForm = { title: '', description: '', period: '', period_start: '', period_end: '', due_date: '', priority: 'medium', status: 'active' };
        },
        resetTaskForm() {
            this.editingTaskId = null;
            this.taskForm = { title: '', description: '', goal_id: '', assigned_to: '', due_date: '', priority: 'medium', status: 'todo' };
        },
        editGoal(id, data) {
            this.editingGoalId = id;
            this.goalForm = { ...data, period_start: '', period_end: '' };
            // Parse period string if exists (format: "dd.mm.yyyy – dd.mm.yyyy" or "dd.mm.yyyy")
            if (data.period) {
                const parts = data.period.split(' – ');
                if (parts.length === 2) {
                    // Convert dd.mm.yyyy to yyyy-mm-dd for date inputs
                    const parseDate = (str) => {
                        const [d, m, y] = str.split('.');
                        return `${y}-${m}-${d}`;
                    };
                    this.goalForm.period_start = parseDate(parts[0]);
                    this.goalForm.period_end = parseDate(parts[1]);
                }
            }
            this.showGoalModal = true;
        },
        editTask(id, data) {
            this.editingTaskId = id;
            this.taskForm = { ...data };
            this.showTaskModal = true;
        }
    }
}

@php
    $songsCollection = collect($songs ?? []);
    $songsData = $songsCollection->map(function($s) {
        return [
            'id' => $s->id,
            'title' => $s->title,
            'artist' => $s->artist,
            'key' => $s->key,
            'bpm' => $s->bpm,
            'lyrics' => $s->lyrics,
            'chords' => $s->chords,
            'ccli_number' => $s->ccli_number,
            'youtube_url' => $s->youtube_url,
            'spotify_url' => $s->spotify_url,
            'tags' => $s->tags ?? [],
            'notes' => $s->notes,
            'resource_links' => $s->resource_links ?? [],
            'times_used' => $s->times_used ?? 0,
            'created_at' => $s->created_at,
        ];
    });
    $allTagsData = $songsCollection->pluck('tags')->flatten()->filter()->unique()->sort()->values();
    $artistsData = $songsCollection->pluck('artist')->filter()->unique()->sort()->values();
    $boardTagsData = $songBoardTags ?? [];
    $songBoardTagsUrl = route('ministries.song-board-tags', $ministry);
@endphp
function songBoardTagsConfig() {
    return {
        tags: @json($boardTagsData),
        newTag: '',
        saving: false,
        async save() {
            this.saving = true;
            try {
                const resp = await fetch(@json($songBoardTagsUrl), {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ tags: this.tags })
                });
                const data = await resp.json();
                if (data.success) this.tags = data.tags;
            } catch(e) {}
            this.saving = false;
        },
        addTag() {
            const t = this.newTag.trim();
            if (!t || this.tags.includes(t)) return;
            this.tags.push(t);
            this.newTag = '';
            this.save();
        },
        removeTag(i) {
            this.tags.splice(i, 1);
            this.save();
        }
    }
}

function songsLibrary() {
    return {
        songs: @json($songsData),
        allTags: @json($allTagsData),
        artists: @json($artistsData),
        boardTags: @json($boardTagsData),
        search: '',
        filterKey: '',
        filterTag: '',
        filterArtist: '',
        filterBpm: '',
        filterUsage: '',
        filterContent: '',
        sortBy: 'title',
        sortDir: 'asc',
        keyQuery: '',
        keyDropdownOpen: false,
        songKeysMap: @js(\App\Models\Song::KEYS),

        init() {
            this.$nextTick(() => this.initSortable());
            this.$watch('search', () => this.$nextTick(() => this.initSortable()));
            this.$watch('filterTag', () => this.$nextTick(() => this.initSortable()));
            this.$watch('filterArtist', () => this.$nextTick(() => this.initSortable()));
            this.$watch('filterBpm', () => this.$nextTick(() => this.initSortable()));
            this.$watch('filterUsage', () => this.$nextTick(() => this.initSortable()));
            this.$watch('filterContent', () => this.$nextTick(() => this.initSortable()));
        },

        initSortable() {
            if (typeof Sortable === 'undefined') {
                setTimeout(() => this.initSortable(), 100);
                return;
            }
            document.querySelectorAll('.song-column').forEach(el => {
                if (el._sortable) el._sortable.destroy();
                el._sortable = new Sortable(el, {
                    group: 'songs',
                    handle: '.drag-handle',
                    animation: 200,
                    ghostClass: 'opacity-40',
                    chosenClass: 'shadow-lg',
                    draggable: '.song-item',
                    onEnd: (evt) => {
                        const songId = parseInt(evt.item.dataset.songId);
                        const fromTag = evt.from.dataset.tag || null;
                        const toTag = evt.to.dataset.tag || null;
                        // Revert DOM — Alpine перерендерить
                        if (evt.from !== evt.to) {
                            evt.from.insertBefore(evt.item, evt.from.children[evt.oldIndex] || null);
                        }
                        if (fromTag !== toTag) {
                            this.moveSong(songId, fromTag, toTag);
                        }
                    }
                });
            });
        },

        get filteredKeys() {
            if (!this.keyQuery) return Object.entries(this.songKeysMap);
            const q = this.keyQuery.toLowerCase();
            return Object.entries(this.songKeysMap).filter(([key, label]) =>
                key.toLowerCase().includes(q) || label.toLowerCase().includes(q)
            );
        },
        selectKey(key) {
            this.form.key = key;
            this.keyQuery = key;
            this.keyDropdownOpen = false;
        },
        expandedSong: null,
        showModal: false,
        editingId: null,
        saving: false,
        viewingSong: null,
        showViewModal: false,
        form: {
            title: '', artist: '', key: '', bpm: '', lyrics: '', chords: '',
            ccli_number: '', youtube_url: '', spotify_url: '', tags: [], new_tag: '', notes: '',
            resource_links: []
        },

        get filteredSongs() {
            let result = this.songs;
            if (this.search) {
                const s = this.search.toLowerCase();
                result = result.filter(song =>
                    song.title.toLowerCase().includes(s) ||
                    (song.artist && song.artist.toLowerCase().includes(s))
                );
            }
            if (this.filterKey) {
                result = result.filter(song => song.key === this.filterKey);
            }
            if (this.filterTag) {
                result = result.filter(song => song.tags && song.tags.includes(this.filterTag));
            }
            if (this.filterArtist) {
                result = result.filter(song => song.artist === this.filterArtist);
            }
            if (this.filterBpm) {
                result = result.filter(song => {
                    switch (this.filterBpm) {
                        case 'slow': return song.bpm && song.bpm < 80;
                        case 'medium': return song.bpm && song.bpm >= 80 && song.bpm <= 120;
                        case 'fast': return song.bpm && song.bpm > 120;
                        case 'none': return !song.bpm;
                        default: return true;
                    }
                });
            }
            if (this.filterUsage) {
                result = result.filter(song => {
                    const used = song.times_used || 0;
                    switch (this.filterUsage) {
                        case 'frequent': return used >= 5;
                        case 'moderate': return used >= 1 && used <= 4;
                        case 'never': return used === 0;
                        default: return true;
                    }
                });
            }
            if (this.filterContent) {
                result = result.filter(song => {
                    switch (this.filterContent) {
                        case 'has_lyrics': return !!song.lyrics;
                        case 'has_chords': return !!song.chords;
                        case 'has_youtube': return !!song.youtube_url;
                        case 'has_spotify': return !!song.spotify_url;
                        case 'no_lyrics': return !song.lyrics;
                        case 'no_chords': return !song.chords;
                        default: return true;
                    }
                });
            }
            const dir = this.sortDir === 'asc' ? 1 : -1;
            return [...result].sort((a, b) => {
                switch (this.sortBy) {
                    case 'artist': return dir * (a.artist || '').localeCompare(b.artist || '', 'uk');
                    case 'key': return dir * (a.key || '').localeCompare(b.key || '', 'uk');
                    case 'bpm': return dir * ((a.bpm || 0) - (b.bpm || 0));
                    case 'recent': return dir * (new Date(b.created_at) - new Date(a.created_at));
                    case 'popular': return dir * ((b.times_used || 0) - (a.times_used || 0));
                    default: return dir * a.title.localeCompare(b.title, 'uk');
                }
            });
        },

        toggleSort(col) {
            if (this.sortBy === col) {
                this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortBy = col;
                this.sortDir = (col === 'popular' || col === 'recent' || col === 'bpm') ? 'desc' : 'asc';
            }
        },

        toggleSong(id) {
            this.expandedSong = this.expandedSong === id ? null : id;
        },

        formatChords(chords) {
            if (!chords) return '';
            let html = chords.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            html = html.replace(/\[([A-G][#b]?m?(?:add|sus|dim|aug|maj|min)?[0-9]?(?:\/[A-G][#b]?)?)\]/g,
                '<span class="inline-block px-1 py-0.5 bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300 text-xs font-mono rounded mx-0.5">$1</span>');
            return html.replace(/\n/g, '<br>');
        },

        resetForm() {
            this.form = {
                title: '', artist: '', key: '', bpm: '', lyrics: '', chords: '',
                ccli_number: '', youtube_url: '', spotify_url: '', tags: [], new_tag: '', notes: '',
                resource_links: []
            };
            this.keyQuery = '';
            this.keyDropdownOpen = false;
            this.editingId = null;
        },

        openCreateModal() {
            this.resetForm();
            this.showModal = true;
        },

        openSongModal(song) {
            this.viewingSong = song;
            this.showViewModal = true;
        },

        openEditModal(song) {
            this.editingId = song.id;
            this.viewingSong = song; // Keep reference to return to view modal after save
            this.showViewModal = false;
            this.form = {
                title: song.title || '',
                artist: song.artist || '',
                key: song.key || '',
                bpm: song.bpm || '',
                lyrics: song.lyrics || '',
                chords: song.chords || '',
                ccli_number: song.ccli_number || '',
                youtube_url: song.youtube_url || '',
                spotify_url: song.spotify_url || '',
                tags: song.tags ? [...song.tags] : [],
                new_tag: '',
                notes: song.notes || '',
                resource_links: song.resource_links ? song.resource_links.map(l => ({...l})) : []
            };
            this.keyQuery = song.key || '';
            this.keyDropdownOpen = false;
            this.showModal = true;
        },

        async saveSong() {
            this.saving = true;
            const url = this.editingId ? `/songs/${this.editingId}` : '/songs';
            const method = this.editingId ? 'PUT' : 'POST';

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.form)
                });

                if (response.ok) {
                    const data = await response.json().catch(() => ({}));
                    const wasEditing = this.editingId;
                    if (this.editingId) {
                        const index = this.songs.findIndex(s => s.id === this.editingId);
                        if (index !== -1) {
                            this.songs[index] = { ...this.songs[index], ...data.song };
                            // Update viewingSong if it was being viewed
                            if (this.viewingSong && this.viewingSong.id === this.editingId) {
                                this.viewingSong = this.songs[index];
                            }
                        }
                    } else {
                        this.songs.push(data.song);
                    }
                    // Update tags list
                    this.allTags = [...new Set(this.songs.flatMap(s => s.tags || []))].sort();
                    this.artists = [...new Set(this.songs.map(s => s.artist).filter(Boolean))].sort();
                    this.showModal = false;
                    this.resetForm();
                    // Re-open view modal if we were editing
                    if (wasEditing && this.viewingSong) {
                        this.showViewModal = true;
                    }
                } else {
                    const err = await response.json().catch(() => ({}));
                    alert(err.message || 'Помилка збереження');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Помилка збереження');
            } finally {
                this.saving = false;
            }
        },

        async deleteSong(song) {
            if (!confirm('{{ __('messages.confirm_delete_song') }}')) return;

            try {
                const response = await fetch(`/songs/${song.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    this.songs = this.songs.filter(s => s.id !== song.id);
                    this.expandedSong = null;
                    this.allTags = [...new Set(this.songs.flatMap(s => s.tags || []))].sort();
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Помилка видалення');
            }
        },

        getSongsForColumn(tag) {
            return this.filteredSongs.filter(s => s.tags && s.tags.includes(tag));
        },

        getUntaggedSongs() {
            return this.filteredSongs.filter(s => {
                if (!s.tags || s.tags.length === 0) return true;
                return !s.tags.some(t => this.boardTags.includes(t));
            });
        },

        async moveSong(songId, fromTag, toTag) {
            const song = this.songs.find(s => s.id === songId);
            if (!song) return;

            const oldTags = [...(song.tags || [])];
            let newTags = [...oldTags];
            if (fromTag) newTags = newTags.filter(t => t !== fromTag);
            if (toTag && !newTags.includes(toTag)) newTags.push(toTag);
            song.tags = newTags.length > 0 ? newTags : [];

            try {
                const resp = await fetch(`/songs/${songId}/move-tag`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ from_tag: fromTag, to_tag: toTag })
                });
                const data = await resp.json();
                if (data.success) {
                    song.tags = data.tags;
                } else {
                    song.tags = oldTags;
                }
            } catch (e) {
                song.tags = oldTags;
            }
            this.allTags = [...new Set(this.songs.flatMap(s => s.tags || []))].sort();
        }
    }
}

/* === Ministry Roles Manager (for sunday service part) === */
function ministryRolesManager() {
    return {
        roles: [],
        defaultRoles: {!! $ministry->is_worship_ministry ? "
            [
                {icon: '🎤', name: 'Ведучий вокал', color: '#dc2626'},
                {icon: '🎤', name: 'Бек-вокал', color: '#f97316'},
                {icon: '🎸', name: 'Акустична гітара', color: '#84cc16'},
                {icon: '🎸', name: 'Електрогітара', color: '#22c55e'},
                {icon: '🎸', name: 'Бас', color: '#14b8a6'},
                {icon: '🥁', name: 'Барабани', color: '#06b6d4'},
                {icon: '🎹', name: 'Клавіші', color: '#8b5cf6'},
                {icon: '🎚', name: 'Звук', color: '#3b82f6'},
                {icon: '💻', name: 'Медіа', color: '#6366f1'},
            ]" : "
            [
                {icon: '📹', name: 'Камера 1', color: '#dc2626'},
                {icon: '📹', name: 'Камера 2', color: '#f97316'},
                {icon: '🎚', name: 'Звук', color: '#3b82f6'},
                {icon: '💻', name: 'Стрім', color: '#8b5cf6'},
                {icon: '📺', name: 'Презентація', color: '#22c55e'},
                {icon: '☕', name: 'Бариста', color: '#92400e'},
                {icon: '🙏', name: 'Привітання', color: '#ec4899'},
                {icon: '🎤', name: 'Ведучий', color: '#14b8a6'},
            ]"
        !!},
        storeUrl: '',
        baseUrl: '',
        newName: '',
        newIcon: '',
        newColor: '#3b82f6',
        loading: false,
        showEditModal: false,
        editId: null,
        editName: '',
        editIcon: '',
        editColor: '',

        init(roles, storeUrl, baseUrl) {
            this.roles = roles;
            this.storeUrl = storeUrl;
            this.baseUrl = baseUrl;
        },

        get availableDefaults() {
            return this.defaultRoles.filter(d => !this.roles.some(r => r.name === d.name));
        },

        async addRole(name, icon, color) {
            if (!name || this.loading) return;
            this.loading = true;

            const formData = new FormData();
            formData.append('name', name);
            formData.append('icon', icon || '');
            formData.append('color', color || '#3b82f6');

            try {
                const res = await fetch(this.storeUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                if (res.ok) {
                    const data = await res.json();
                    this.roles.push({id: data.id, name, icon: icon || '', color: color || '#3b82f6'});
                    this.newName = '';
                    this.newIcon = '';
                    this.newColor = '#3b82f6';
                }
            } catch (e) {
                alert('Помилка додавання');
            }

            this.loading = false;
        },

        openEdit(role) {
            this.editId = role.id;
            this.editName = role.name;
            this.editIcon = role.icon;
            this.editColor = role.color;
            this.showEditModal = true;
        },

        async saveEdit() {
            if (!this.editName || this.loading) return;
            this.loading = true;

            const formData = new FormData();
            formData.append('_method', 'PUT');
            formData.append('name', this.editName);
            formData.append('icon', this.editIcon || '');
            formData.append('color', this.editColor || '#3b82f6');

            try {
                const res = await fetch(this.baseUrl + '/' + this.editId, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                if (res.ok) {
                    const role = this.roles.find(r => r.id === this.editId);
                    if (role) {
                        role.name = this.editName;
                        role.icon = this.editIcon || '';
                        role.color = this.editColor || '#3b82f6';
                    }
                    this.showEditModal = false;
                }
            } catch (e) {
                alert('Помилка оновлення');
            }

            this.loading = false;
        },

        async deleteRole(id) {
            if (!confirm('{{ __('messages.confirm_delete_role') }}')) return;
            this.loading = true;

            try {
                const res = await fetch(this.baseUrl + '/' + id, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json'
                    }
                });

                if (res.ok) {
                    this.roles = this.roles.filter(r => r.id !== id);
                }
            } catch (e) {
                alert('Помилка видалення');
            }

            this.loading = false;
        }
    }
}

function settingsTab() {
    return {
        visibility: '{{ $ministry->visibility ?? "public" }}',
        allowedPeople: @json($allowedPeopleData),
        selectedPersonId: '',
        saved: false,
        allPeople: @json($allPeopleData),
        init() {},
        get availablePeopleFiltered() {
            const selectedIds = this.allowedPeople.map(p => p.id);
            return this.allPeople.filter(p => !selectedIds.includes(p.id));
        },
        addSelectedPerson() {
            if (!this.selectedPersonId) return;
            const person = this.allPeople.find(p => p.id == this.selectedPersonId);
            if (person && !this.allowedPeople.find(p => p.id === person.id)) {
                this.allowedPeople.push({ id: person.id, name: person.full_name, photo: person.photo, initials: person.initials });
                this.saveVisibility();
            }
            this.selectedPersonId = '';
        },
        removePerson(personId) {
            this.allowedPeople = this.allowedPeople.filter(p => p.id !== personId);
            this.saveVisibility();
        },
        async saveVisibility() {
            try {
                const response = await fetch('{{ route("ministries.update-visibility", $ministry) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        visibility: this.visibility,
                        allowed_person_ids: this.allowedPeople.map(p => p.id)
                    })
                });
                if (response.ok) {
                    this.saved = true;
                    setTimeout(() => this.saved = false, 2000);
                }
            } catch (error) {
                console.error('Error saving visibility:', error);
            }
        }
    }
}

function budgetPage() {
    return {
        // Shared state
        currentMonth: {{ $budgetData['month'] }},
        currentYear: {{ $budgetData['year'] }},
        ministryId: {{ $ministry->id }},
        monthNames: ['', 'Січень', 'Лютий', 'Березень', 'Квітень', 'Травень', 'Червень', 'Липень', 'Серпень', 'Вересень', 'Жовтень', 'Листопад', 'Грудень'],

        // Budget data (loaded via AJAX)
        budget: {
            budget_id: {{ $budgetData['budget']?->id ?? 'null' }},
            items: [],
            has_items: false,
            effective_budget: 0,
            total_spent: 0,
            total_income: {{ $budgetData['total_income'] ?? 0 }},
            total_allocated: {{ $budgetData['total_allocated'] ?? 0 }},
            unmatched_spent: 0,
        },
        budgetLoading: false,

        // Expenses data
        search: '',
        sortBy: 'date_desc',
        allTransactions: {!! Js::from($ministry->transactions->map(fn($t) => [
            'id' => $t->id,
            'amount' => $t->amount,
            'currency' => $t->currency ?? 'UAH',
            'direction' => $t->direction,
            'source_type' => $t->source_type,
            'description' => $t->description,
            'date' => $t->date->format('Y-m-d'),
            'month' => (int)$t->date->format('m'),
            'year' => (int)$t->date->format('Y'),
            'date_formatted' => $t->date->format('d.m.Y'),
            'category' => $t->category?->name,
            'category_id' => $t->category_id,
            'expense_type' => $t->expense_type,
            'payment_method' => $t->payment_method,
            'budget_item_id' => $t->budget_item_id,
            'notes' => $t->notes,
            'attachments' => $t->attachments->map(fn($a) => [
                'id' => $a->id,
                'url' => Storage::url($a->path),
                'is_image' => str_starts_with($a->mime_type, 'image/'),
                'original_name' => $a->original_name,
            ])
        ])) !!},

        // Budget item modal
        showItemModal: false,
        itemMode: 'create',
        itemModalTitle: '',
        itemEditId: null,
        itemSaving: false,
        itemForm: { name: '', planned_amount: '', planned_date: '', category_id: '', category_name: '', notes: '', person_ids: [] },

        // Expense modal
        showExpenseModal: false,
        expenseMode: 'create',
        expenseModalTitle: '',
        expenseEditId: null,
        expenseSaving: false,
        expenseForm: {
            amount: '', currency: 'UAH', date: '', description: '',
            category_id: '', category_name: '', expense_type: '', payment_method: '',
            budget_item_id: '', notes: '',
        },
        expenseFiles: [],
        expensePreviews: [],
        existingAttachments: [],
        deleteAttachmentIds: [],

        // Income modal
        showIncomeModal: false,
        incomeSaving: false,
        incomeForm: { amount: '', currency: 'UAH', date: '', description: '', notes: '' },

        // Computed
        get filteredTransactions() {
            let result = this.allTransactions.filter(t => t.direction === 'out' && t.source_type !== 'allocation' && t.month === this.currentMonth && t.year === this.currentYear);
            if (this.search) {
                const s = this.search.toLowerCase();
                result = result.filter(t =>
                    t.description?.toLowerCase().includes(s) ||
                    t.category?.toLowerCase().includes(s) ||
                    t.notes?.toLowerCase().includes(s)
                );
            }
            result = [...result].sort((a, b) => {
                if (this.sortBy === 'date_desc') return b.date.localeCompare(a.date);
                if (this.sortBy === 'date_asc') return a.date.localeCompare(b.date);
                if (this.sortBy === 'amount_desc') return b.amount - a.amount;
                if (this.sortBy === 'amount_asc') return a.amount - b.amount;
                return 0;
            });
            return result;
        },
        get filteredIncome() {
            return this.allTransactions
                .filter(t => t.direction === 'in' && t.source_type !== 'allocation' && t.month === this.currentMonth && t.year === this.currentYear)
                .sort((a, b) => b.date.localeCompare(a.date));
        },
        get totalIncome() {
            return this.filteredIncome.reduce((sum, t) => sum + parseFloat(t.amount), 0);
        },
        get balance() {
            return (this.budget.total_allocated || 0) + (this.budget.total_income || 0) - this.budget.total_spent;
        },
        get totalSum() {
            return this.filteredTransactions.reduce((sum, t) => sum + parseFloat(t.amount), 0);
        },
        get totalPlanned() {
            return this.budget.items.reduce((sum, i) => sum + i.planned_amount, 0);
        },
        get budgetRemaining() {
            return this.budget.effective_budget - this.budget.total_spent;
        },
        get budgetPct() {
            return this.budget.effective_budget > 0
                ? Math.round((this.budget.total_spent / this.budget.effective_budget) * 1000) / 10
                : 0;
        },

        // Format number
        fmt(n) {
            return new Intl.NumberFormat('uk-UA').format(Math.round(n));
        },

        // Month navigation (unified for budget + expenses)
        prevMonth() {
            this.currentMonth--;
            if (this.currentMonth < 1) { this.currentMonth = 12; this.currentYear--; }
            this.loadBudget();
        },
        nextMonth() {
            this.currentMonth++;
            if (this.currentMonth > 12) { this.currentMonth = 1; this.currentYear++; }
            this.loadBudget();
        },

        // Load budget data from API
        async loadBudget() {
            this.budgetLoading = true;
            try {
                const res = await fetch(`/ministries/${this.ministryId}/budget-data?year=${this.currentYear}&month=${this.currentMonth}`, {
                    headers: { 'Accept': 'application/json' },
                });
                if (res.ok) {
                    const data = await res.json();
                    this.budget = {
                        budget_id: data.budget_id,
                        items: data.items || [],
                        has_items: data.has_items,
                        effective_budget: data.effective_budget,
                        total_spent: data.total_spent,
                        total_income: data.total_income || 0,
                        total_allocated: data.total_allocated || 0,
                        unmatched_spent: data.unmatched_spent,
                    };
                }
            } catch (e) {
                console.error('Failed to load budget:', e);
            } finally {
                this.budgetLoading = false;
            }
        },

        // Ensure budget exists for current month
        async ensureBudget() {
            if (this.budget.budget_id) return true;
            try {
                const res = await fetch(`/ministries/${this.ministryId}/ensure-budget`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        year: this.currentYear,
                        month: this.currentMonth,
                    }),
                });
                if (res.ok) {
                    const data = await res.json();
                    await this.loadBudget();
                    return !!this.budget.budget_id;
                }
            } catch (e) {}
            return false;
        },

        // Open modal
        async openItemModal(mode, itemData) {
            this.itemMode = mode;
            this.itemModalTitle = mode === 'create' ? 'Нова стаття бюджету' : 'Редагувати статтю';

            if (mode === 'edit' && itemData) {
                this.itemEditId = itemData.id;
                this.itemForm = {
                    name: itemData.name,
                    planned_amount: itemData.planned_amount,
                    planned_date: itemData.planned_date || '',
                    category_id: itemData.category_id || '',
                    category_name: '',
                    notes: itemData.notes || '',
                    person_ids: (itemData.person_ids || []).map(String),
                };
            } else {
                this.itemEditId = null;
                this.itemForm = { name: '', planned_amount: '', planned_date: '', category_id: '', category_name: '', notes: '', person_ids: [] };
            }

            if (mode === 'create' && !this.budget.budget_id) {
                const ok = await this.ensureBudget();
                if (!ok) return;
            }

            this.showItemModal = true;
        },

        // Save item (create/update) — inline refresh, no reload
        async saveItem() {
            this.itemSaving = true;
            try {
                const url = this.itemMode === 'create'
                    ? `/ministries/${this.ministryId}/budget-items`
                    : `/ministries/budget-items/${this.itemEditId}`;
                const method = this.itemMode === 'create' ? 'POST' : 'PUT';

                const payload = {
                    name: this.itemForm.name,
                    planned_amount: this.itemForm.planned_amount,
                    planned_date: this.itemForm.planned_date || null,
                    category_id: (this.itemForm.category_id && this.itemForm.category_id !== '__custom__') ? this.itemForm.category_id : null,
                    category_name: (this.itemForm.category_id === '__custom__' && this.itemForm.category_name) ? this.itemForm.category_name : null,
                    notes: this.itemForm.notes || null,
                    person_ids: this.itemForm.person_ids.map(Number),
                };
                if (this.itemMode === 'create') payload.budget_id = this.budget.budget_id;

                const res = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(payload),
                });
                const data = await res.json().catch(() => ({}));
                if (res.ok && data.success) {
                    this.showItemModal = false;
                    if (typeof showToast === 'function') showToast('success', data.message);
                    await this.loadBudget();
                } else if (res.status === 422) {
                    const msgs = data.errors ? Object.values(data.errors).flat() : [data.message];
                    if (typeof showToast === 'function') showToast('error', msgs[0]);
                } else {
                    if (typeof showToast === 'function') showToast('error', data.message || 'Помилка');
                }
            } catch (e) {
                if (typeof showToast === 'function') showToast('error', 'Помилка збереження');
            } finally {
                this.itemSaving = false;
            }
        },

        // Delete item — inline refresh, no reload
        async deleteItem(itemId, itemName) {
            if (!confirm(`Видалити статтю "${itemName}"?`)) return;
            try {
                const res = await fetch(`/ministries/budget-items/${itemId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });
                const data = await res.json().catch(() => ({}));
                if (res.ok && data.success) {
                    if (typeof showToast === 'function') showToast('success', data.message);
                    await this.loadBudget();
                }
            } catch (e) {
                if (typeof showToast === 'function') showToast('error', 'Помилка видалення');
            }
        },

        // Copy budget to next month
        async copyBudget() {
            let toMonth = this.currentMonth + 1;
            let toYear = this.currentYear;
            if (toMonth > 12) { toMonth = 1; toYear++; }

            if (!confirm(`Копіювати бюджет на ${this.monthNames[toMonth]} ${toYear}?`)) return;

            try {
                const res = await fetch(`/ministries/${this.ministryId}/budget-copy`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        from_year: this.currentYear,
                        from_month: this.currentMonth,
                        to_year: toYear,
                        to_month: toMonth,
                    }),
                });
                const data = await res.json().catch(() => ({}));
                if (res.ok && data.success) {
                    if (typeof showToast === 'function') showToast('success', data.message);
                    // Navigate to the copied month
                    this.currentMonth = toMonth;
                    this.currentYear = toYear;
                    await this.loadBudget();
                } else {
                    if (typeof showToast === 'function') showToast('error', data.message || 'Помилка копіювання');
                }
            } catch (e) {
                if (typeof showToast === 'function') showToast('error', 'Помилка копіювання');
            }
        },

        // ==================
        // Expense Modal Methods
        // ==================

        async openExpenseModal(mode, transactionData) {
            this.expenseMode = mode;
            this.expenseModalTitle = mode === 'create' ? 'Нова витрата' : 'Редагувати витрату';
            this.expenseFiles = [];
            this.expensePreviews = [];
            this.deleteAttachmentIds = [];

            if (mode === 'edit' && transactionData) {
                this.expenseEditId = transactionData.id;
                // Fetch fresh data from server
                try {
                    const res = await fetch(`/ministries/expenses/${transactionData.id}/edit-data`, {
                        headers: { 'Accept': 'application/json' },
                    });
                    if (res.ok) {
                        const data = await res.json();
                        const t = data.transaction;
                        this.expenseForm = {
                            amount: t.amount,
                            currency: t.currency || 'UAH',
                            date: t.date,
                            description: t.description,
                            category_id: t.category_id || '',
                            category_name: '',
                            expense_type: t.expense_type || '',
                            payment_method: t.payment_method || '',
                            budget_item_id: t.budget_item_id || '',
                            notes: t.notes || '',
                        };
                        this.existingAttachments = t.attachments || [];
                    }
                } catch (e) {
                    if (typeof showToast === 'function') showToast('error', 'Помилка завантаження даних');
                    return;
                }
            } else {
                this.expenseEditId = null;
                this.expenseForm = {
                    amount: '', currency: 'UAH', date: new Date().toISOString().slice(0, 10),
                    description: '', category_id: '', category_name: '', expense_type: '', payment_method: '',
                    budget_item_id: '', notes: '',
                };
                this.existingAttachments = [];
            }

            this.showExpenseModal = true;
        },

        addExpenseFiles(event) {
            const files = Array.from(event.target.files);
            for (const file of files) {
                if (this.expenseFiles.length + this.existingAttachments.length - this.deleteAttachmentIds.length >= 10) break;
                this.expenseFiles.push(file);
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = (e) => this.expensePreviews.push({ name: file.name, url: e.target.result });
                    reader.readAsDataURL(file);
                } else {
                    this.expensePreviews.push({ name: file.name, url: null });
                }
            }
            event.target.value = '';
        },

        removeExpenseFile(index) {
            this.expenseFiles.splice(index, 1);
            this.expensePreviews.splice(index, 1);
        },

        removeExistingAttachment(attId) {
            this.deleteAttachmentIds.push(attId);
        },

        async saveExpense() {
            this.expenseSaving = true;
            try {
                const formData = new FormData();
                formData.append('amount', this.expenseForm.amount);
                formData.append('currency', this.expenseForm.currency);
                formData.append('date', this.expenseForm.date);
                formData.append('description', this.expenseForm.description);
                if (this.expenseForm.category_id && this.expenseForm.category_id !== '__custom__') formData.append('category_id', this.expenseForm.category_id);
                if (this.expenseForm.category_id === '__custom__' && this.expenseForm.category_name) formData.append('category_name', this.expenseForm.category_name);
                if (this.expenseForm.expense_type) formData.append('expense_type', this.expenseForm.expense_type);
                if (this.expenseForm.payment_method) formData.append('payment_method', this.expenseForm.payment_method);
                if (this.expenseForm.budget_item_id) formData.append('budget_item_id', this.expenseForm.budget_item_id);
                if (this.expenseForm.notes) formData.append('notes', this.expenseForm.notes);

                for (const file of this.expenseFiles) {
                    formData.append('receipts[]', file);
                }

                let url, method;
                if (this.expenseMode === 'create') {
                    url = `/ministries/${this.ministryId}/expenses`;
                    method = 'POST';
                } else {
                    url = `/ministries/expenses/${this.expenseEditId}`;
                    method = 'POST';
                    formData.append('_method', 'PUT');
                    for (const id of this.deleteAttachmentIds) {
                        formData.append('delete_attachments[]', id);
                    }
                }

                const res = await fetch(url, {
                    method,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                const data = await res.json().catch(() => ({}));
                if (res.ok && data.success) {
                    this.showExpenseModal = false;
                    if (typeof showToast === 'function') showToast('success', data.message);

                    // Update allTransactions inline
                    if (this.expenseMode === 'create') {
                        this.allTransactions.push(data.transaction);
                    } else {
                        const idx = this.allTransactions.findIndex(t => t.id === data.transaction.id);
                        if (idx !== -1) {
                            this.allTransactions[idx] = data.transaction;
                        }
                    }

                    // Refresh budget to update actual amounts
                    await this.loadBudget();
                } else if (res.status === 422) {
                    const msgs = data.errors ? Object.values(data.errors).flat() : [data.message];
                    if (typeof showToast === 'function') showToast('error', msgs[0]);
                } else {
                    if (typeof showToast === 'function') showToast('error', data.message || 'Помилка');
                }
            } catch (e) {
                console.error('Expense save error:', e);
                if (typeof showToast === 'function') showToast('error', 'Помилка збереження');
            } finally {
                this.expenseSaving = false;
            }
        },

        async deleteExpense(transactionId) {
            if (!confirm('Видалити цю витрату?')) return;
            try {
                const res = await fetch(`/ministries/expenses/${transactionId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });
                const data = await res.json().catch(() => ({}));
                if (res.ok && data.success) {
                    this.allTransactions = this.allTransactions.filter(t => t.id !== transactionId);
                    if (typeof showToast === 'function') showToast('success', data.message);
                    await this.loadBudget();
                }
            } catch (e) {
                if (typeof showToast === 'function') showToast('error', 'Помилка видалення');
            }
        },

        // ==================
        // Income Methods
        // ==================

        openIncomeModal() {
            this.incomeForm = {
                amount: '',
                currency: 'UAH',
                date: new Date().toISOString().slice(0, 10),
                description: '',
                notes: '',
            };
            this.showIncomeModal = true;
        },

        async saveIncome() {
            this.incomeSaving = true;
            try {
                const res = await fetch(`/ministries/${this.ministryId}/income`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(this.incomeForm),
                });
                const data = await res.json().catch(() => ({}));
                if (res.ok && data.success) {
                    this.showIncomeModal = false;
                    if (typeof showToast === 'function') showToast('success', data.message);
                    this.allTransactions.push(data.transaction);
                    await this.loadBudget();
                } else if (res.status === 422) {
                    const msgs = data.errors ? Object.values(data.errors).flat() : [data.message];
                    if (typeof showToast === 'function') showToast('error', msgs[0]);
                } else {
                    if (typeof showToast === 'function') showToast('error', data.message || 'Помилка');
                }
            } catch (e) {
                if (typeof showToast === 'function') showToast('error', 'Помилка збереження');
            } finally {
                this.incomeSaving = false;
            }
        },

        async deleteIncome(transactionId) {
            if (!confirm('Видалити це надходження?')) return;
            try {
                const res = await fetch(`/ministries/income/${transactionId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });
                const data = await res.json().catch(() => ({}));
                if (res.ok && data.success) {
                    this.allTransactions = this.allTransactions.filter(t => t.id !== transactionId);
                    if (typeof showToast === 'function') showToast('success', data.message);
                    await this.loadBudget();
                }
            } catch (e) {
                if (typeof showToast === 'function') showToast('error', 'Помилка видалення');
            }
        },
    }
}
</script>
@endpush
@endsection
