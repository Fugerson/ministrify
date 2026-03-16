@extends('layouts.app')

@section('title', $group->name)

@section('actions')
<div class="flex items-center gap-2">
    @can('update', $group)
    @if($currentChurch->attendance_enabled)
    <a href="{{ route('groups.attendance.checkin', $group) }}" class="inline-flex items-center whitespace-nowrap px-3 sm:px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-xl hover:bg-green-700 transition-colors">
        <svg class="w-4 h-4 mr-1.5 sm:mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ __('app.group_checkin') }}
    </a>
    @endif
    <a href="{{ route('groups.edit', $group) }}" class="inline-flex items-center whitespace-nowrap px-3 sm:px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
        <svg class="w-4 h-4 mr-1.5 sm:mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
        </svg>
        {{ __('app.edit') }}
    </a>
    @endcan
    @can('delete', $group)
    <button @click="ajaxDelete('{{ route('groups.destroy', $group) }}', @js(__('messages.confirm_delete_group')), null, '{{ route('groups.index') }}')"
            class="inline-flex items-center whitespace-nowrap px-3 sm:px-4 py-2 bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 text-sm font-medium rounded-xl hover:bg-red-100 dark:hover:bg-red-900/50 transition-colors">
        <svg class="w-4 h-4 mr-1.5 sm:mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
        </svg>
        {{ __('app.delete') }}
    </button>
    @endcan
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Group Info -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 sm:p-6">
        <div class="flex items-start gap-4">
            <div class="w-16 h-16 rounded-xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center flex-shrink-0">
                <svg class="w-8 h-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div class="flex-1">
                <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                    <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white">{{ $group->name }}</h2>
                    <span class="inline-flex items-center whitespace-nowrap gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium
                        @if($group->status === 'active') bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300
                        @elseif($group->status === 'paused') bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300
                        @else bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300
                        @endif">
                        <span class="w-1.5 h-1.5 rounded-full
                            @if($group->status === 'active') bg-green-500
                            @elseif($group->status === 'paused') bg-yellow-500
                            @else bg-blue-500
                            @endif"></span>
                        {{ $group->status_label }}
                    </span>
                </div>
                @if($group->description)
                <p class="text-gray-600 dark:text-gray-400 mt-1">{{ $group->description }}</p>
                @endif

                <div class="flex flex-wrap items-center gap-4 mt-4 text-sm text-gray-600 dark:text-gray-400">
                    @if($group->leader)
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-1.5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        {{ __('app.leader') }}: <a href="{{ route('people.show', $group->leader) }}" class="ml-1 text-primary-600 dark:text-primary-400 hover:underline">{{ $group->leader->full_name }}</a>
                    </div>
                    @endif
                    @if($group->meeting_day)
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        {{ ['monday' => __('app.monday'), 'tuesday' => __('app.tuesday'), 'wednesday' => __('app.wednesday'), 'thursday' => __('app.thursday'), 'friday' => __('app.friday'), 'saturday' => __('app.saturday'), 'sunday' => __('app.sunday')][$group->meeting_day] ?? $group->meeting_day }}
                        @if($group->meeting_time)
                        {{ __('app.group_at_time') }} {{ $group->meeting_time->format('H:i') }}
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $group->members->filter(fn($m) => $m->pivot->role !== 'guest')->count() }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.members') }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ $group->guests->count() }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.group_guests_list') }}</div>
        </div>
        @if($currentChurch->attendance_enabled)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $attendanceStats['total_meetings'] }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.meetings_count') }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center">
                <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ $attendanceStats['average_attendance'] }}</span>
                @if($attendanceStats['trend'] === 'up')
                <svg class="w-5 h-5 ml-1.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                </svg>
                @elseif($attendanceStats['trend'] === 'down')
                <svg class="w-5 h-5 ml-1.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                </svg>
                @endif
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.avg_attendance') }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            @if($attendanceStats['last_meeting'])
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $attendanceStats['last_meeting']->date->format('d.m') }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.last_meeting') }}</div>
            @else
            <div class="text-2xl font-bold text-gray-400">—</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.no_data') }}</div>
            @endif
        </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Members -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700"
             x-data="{ membersView: localStorage.getItem('group_members_view') || 'grid' }"
             x-init="$watch('membersView', v => localStorage.setItem('group_members_view', v))">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between gap-3">
                <h3 class="font-semibold text-gray-900 dark:text-white">{{ __('app.members') }} ({{ $group->members->count() }})</h3>
                <div class="flex items-center gap-2">
                    {{-- View switcher --}}
                    <div class="inline-flex items-center bg-gray-100 dark:bg-gray-700 rounded-lg p-0.5">
                        <button @click="membersView = 'grid'" type="button"
                                :class="membersView === 'grid' ? 'bg-white dark:bg-gray-600 shadow-sm text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                                class="p-2.5 rounded-md transition-all" title="{{ __('app.grid') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        </button>
                        <button @click="membersView = 'list'" type="button"
                                :class="membersView === 'list' ? 'bg-white dark:bg-gray-600 shadow-sm text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                                class="p-2.5 rounded-md transition-all" title="{{ __('app.list') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>
                        <button @click="membersView = 'compact'" type="button"
                                :class="membersView === 'compact' ? 'bg-white dark:bg-gray-600 shadow-sm text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                                class="p-2.5 rounded-md transition-all" title="{{ __('app.compact') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        </button>
                    </div>
                    @can('update', $group)
                    <button type="button" onclick="document.getElementById('addMemberModal').classList.remove('hidden')"
                            class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/30 rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        {{ __('app.add') }}
                    </button>
                    @endcan
                </div>
            </div>

            @php
                $sortedMembers = $group->members->filter(fn($m) => $m->pivot->role !== 'guest')->sortBy(fn($m) => match($m->pivot->role) { 'leader' => 0, 'assistant' => 1, 'member' => 2, default => 3 });
            @endphp

            @if($sortedMembers->count() > 0)

            {{-- ===== GRID VIEW ===== --}}
            <div x-show="membersView === 'grid'" class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($sortedMembers as $member)
                    @php $pivotRole = $member->pivot->role ?? 'member'; @endphp
                    <div class="relative bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl hover:shadow-md transition-shadow group/card"
                         x-data="{
                             role: '{{ $pivotRole }}',
                             open: false,
                             saving: false,
                             async setRole(newRole) {
                                 this.saving = true;
                                 try {
                                     const res = await fetch('{{ route('groups.members.role', [$group, $member]) }}', {
                                         method: 'PUT',
                                         headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
                                         body: JSON.stringify({ role: newRole })
                                     });
                                     if (res.ok) {
                                         this.role = newRole;
                                         if (window.showToast) showToast('success', @js(__('app.audit_action_role_changed')));
                                     }
                                 } catch (e) { console.error(e); }
                                 this.saving = false;
                                 this.open = false;
                             },
                             get accentClass() {
                                 if (this.role === 'leader' || this.role === 'assistant') return 'bg-gradient-to-r from-amber-400 to-amber-500';
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
                                        @can('update', $group)
                                        <div class="relative">
                                            <button type="button" @click="open = !open"
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium transition-colors"
                                                    :class="{
                                                        'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400 hover:bg-amber-200 dark:hover:bg-amber-900/50': role === 'leader',
                                                        'bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-300 hover:bg-amber-100 dark:hover:bg-amber-900/40': role === 'assistant',
                                                        'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-600': role === 'member'
                                                    }">
                                                <span x-text="role === 'leader' ? @js(__('app.leader')) : (role === 'assistant' ? @js(__('app.assistant_role')) : @js(__('app.member_role')))"></span>
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                            </button>
                                            <div x-show="open" @click.away="open = false" x-transition x-cloak
                                                 class="absolute left-0 mt-1 w-36 bg-white dark:bg-gray-700 rounded-lg shadow-lg z-20 border border-gray-200 dark:border-gray-600 py-1">
                                                <button type="button" @click="setRole('leader')" :disabled="saving"
                                                        class="w-full text-left px-3 py-1.5 text-xs hover:bg-gray-50 dark:hover:bg-gray-600 flex items-center gap-2"
                                                        :class="role === 'leader' ? 'text-amber-700 dark:text-amber-400 font-semibold' : 'text-gray-700 dark:text-gray-300'">
                                                    <span class="w-2 h-2 rounded-full bg-amber-400"></span> {{ __('app.leader') }}
                                                </button>
                                                <button type="button" @click="setRole('assistant')" :disabled="saving"
                                                        class="w-full text-left px-3 py-1.5 text-xs hover:bg-gray-50 dark:hover:bg-gray-600 flex items-center gap-2"
                                                        :class="role === 'assistant' ? 'text-amber-700 dark:text-amber-300 font-semibold' : 'text-gray-700 dark:text-gray-300'">
                                                    <span class="w-2 h-2 rounded-full bg-amber-300"></span> {{ __('app.assistant_role') }}
                                                </button>
                                                <button type="button" @click="setRole('member')" :disabled="saving"
                                                        class="w-full text-left px-3 py-1.5 text-xs hover:bg-gray-50 dark:hover:bg-gray-600 flex items-center gap-2"
                                                        :class="role === 'member' ? 'text-gray-900 dark:text-white font-semibold' : 'text-gray-700 dark:text-gray-300'">
                                                    <span class="w-2 h-2 rounded-full bg-gray-400"></span> {{ __('app.member_role') }}
                                                </button>
                                            </div>
                                        </div>
                                        @else
                                        <template x-if="role === 'leader'">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">{{ __('app.leader') }}</span>
                                        </template>
                                        <template x-if="role === 'assistant'">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-300">{{ __('app.assistant_role') }}</span>
                                        </template>
                                        @endcan
                                    </div>

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
                                        <span class="text-emerald-500 dark:text-emerald-400" title="{{ __('app.group_ministrify_user') }}">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                        </span>
                                        @endif
                                        @if($member->telegram_chat_id)
                                        <span class="text-emerald-500 dark:text-emerald-400" title="{{ __('app.group_telegram_connected') }}">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                        </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Remove button --}}
                                @can('update', $group)
                                @if($pivotRole !== 'leader')
                                <button @click="ajaxDelete('{{ route('groups.members.remove', [$group, $member]) }}', @js(__('messages.confirm_remove_member')),() => $el.closest('.group\\/card').remove())"
                                        class="shrink-0 p-1.5 text-gray-300 dark:text-gray-600 hover:text-red-500 dark:hover:text-red-400 opacity-0 group-hover/card:opacity-100 transition-opacity rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                                @endif
                                @endcan
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- ===== LIST VIEW ===== --}}
            <div x-show="membersView === 'list'" x-cloak>
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($sortedMembers as $member)
                        @php $pivotRole = $member->pivot->role ?? 'member'; @endphp
                        <div class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group/row"
                             x-data="{
                                 role: '{{ $pivotRole }}',
                                 open: false,
                                 saving: false,
                                 async setRole(newRole) {
                                     this.saving = true;
                                     try {
                                         const res = await fetch('{{ route('groups.members.role', [$group, $member]) }}', {
                                             method: 'PUT',
                                             headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
                                             body: JSON.stringify({ role: newRole })
                                         });
                                         if (res.ok) {
                                             this.role = newRole;
                                             if (window.showToast) showToast('success', @js(__('app.audit_action_role_changed')));
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
                                    @can('update', $group)
                                    <div class="relative">
                                        <button type="button" @click="open = !open"
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium transition-colors"
                                                :class="{
                                                    'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400': role === 'leader',
                                                    'bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-300': role === 'assistant',
                                                    'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400': role === 'member'
                                                }">
                                            <span x-text="role === 'leader' ? @js(__('app.leader')) : (role === 'assistant' ? @js(__('app.assistant_role')) : @js(__('app.member_role')))"></span>
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        </button>
                                        <div x-show="open" @click.away="open = false" x-transition x-cloak
                                             class="absolute left-0 mt-1 w-36 bg-white dark:bg-gray-700 rounded-lg shadow-lg z-20 border border-gray-200 dark:border-gray-600 py-1">
                                            <button type="button" @click="setRole('leader')" :disabled="saving" class="w-full text-left px-3 py-1.5 text-xs hover:bg-gray-50 dark:hover:bg-gray-600 flex items-center gap-2" :class="role === 'leader' ? 'text-amber-700 dark:text-amber-400 font-semibold' : 'text-gray-700 dark:text-gray-300'">
                                                <span class="w-2 h-2 rounded-full bg-amber-400"></span> {{ __('app.leader') }}
                                            </button>
                                            <button type="button" @click="setRole('assistant')" :disabled="saving" class="w-full text-left px-3 py-1.5 text-xs hover:bg-gray-50 dark:hover:bg-gray-600 flex items-center gap-2" :class="role === 'assistant' ? 'text-amber-700 dark:text-amber-300 font-semibold' : 'text-gray-700 dark:text-gray-300'">
                                                <span class="w-2 h-2 rounded-full bg-amber-300"></span> {{ __('app.assistant_role') }}
                                            </button>
                                            <button type="button" @click="setRole('member')" :disabled="saving" class="w-full text-left px-3 py-1.5 text-xs hover:bg-gray-50 dark:hover:bg-gray-600 flex items-center gap-2" :class="role === 'member' ? 'text-gray-900 dark:text-white font-semibold' : 'text-gray-700 dark:text-gray-300'">
                                                <span class="w-2 h-2 rounded-full bg-gray-400"></span> {{ __('app.member_role') }}
                                            </button>
                                        </div>
                                    </div>
                                    @else
                                    <template x-if="role === 'leader'">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">{{ __('app.leader') }}</span>
                                    </template>
                                    <template x-if="role === 'assistant'">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-300">{{ __('app.assistant_role') }}</span>
                                    </template>
                                    @endcan
                                </div>
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
                                <span class="text-emerald-500 dark:text-emerald-400" title="{{ __('app.group_ministrify_user') }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                </span>
                                @endif
                                @if($member->telegram_chat_id)
                                <span class="text-emerald-500 dark:text-emerald-400" title="{{ __('app.group_telegram_connected') }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                </span>
                                @endif
                            </div>

                            {{-- Phone text (mobile only) --}}
                            @if($member->phone)
                            <span class="sm:hidden text-xs text-gray-400 shrink-0">{{ $member->phone }}</span>
                            @endif

                            {{-- Remove button --}}
                            @can('update', $group)
                            @if($pivotRole !== 'leader')
                            <button @click="ajaxDelete('{{ route('groups.members.remove', [$group, $member]) }}', @js(__('messages.confirm_remove_member')),() => $el.closest('.group\\/row').remove())"
                                    class="shrink-0 p-1.5 text-gray-300 dark:text-gray-600 hover:text-red-500 dark:hover:text-red-400 opacity-0 group-hover/row:opacity-100 transition-opacity rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                            @endif
                            @endcan
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ===== COMPACT VIEW ===== --}}
            <div x-show="membersView === 'compact'" x-cloak>
                <div class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @foreach($sortedMembers as $member)
                        @php $pivotRole = $member->pivot->role ?? 'member'; @endphp
                        <div class="flex items-center gap-2 px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group/compact"
                             x-data="{ role: '{{ $pivotRole }}' }">
                            {{-- Color dot for role --}}
                            <span class="w-2 h-2 rounded-full shrink-0"
                                  :class="role === 'leader' ? 'bg-amber-400' : (role === 'assistant' ? 'bg-amber-300' : 'bg-gray-300 dark:bg-gray-600')"></span>

                            <a href="{{ route('people.show', $member) }}" class="text-sm text-gray-900 dark:text-white hover:text-primary-600 dark:hover:text-primary-400 truncate">
                                {{ $member->full_name }}
                            </a>

                            <span class="text-xs text-gray-400 dark:text-gray-500 shrink-0"
                                  x-text="role === 'leader' ? @js(__('app.leader')) : (role === 'assistant' ? @js(__('app.assistant_role')) : '')"></span>

                            <span class="flex-1"></span>

                            @if($member->phone)
                            <span class="hidden sm:inline text-xs text-gray-400">{{ $member->phone }}</span>
                            @endif
                            @if($member->user_id)
                            <span class="hidden sm:inline text-emerald-500 dark:text-emerald-400" title="{{ __('app.group_ministrify_user') }}">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </span>
                            @endif
                            @if($member->telegram_chat_id)
                            <span class="hidden sm:inline text-emerald-500 dark:text-emerald-400" title="{{ __('app.group_telegram_connected') }}">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            </span>
                            @endif

                            {{-- Remove button --}}
                            @can('update', $group)
                            @if($pivotRole !== 'leader')
                            <button @click="ajaxDelete('{{ route('groups.members.remove', [$group, $member]) }}', @js(__('messages.confirm_remove_member')),() => $el.closest('.group\\/compact').remove())"
                                    class="shrink-0 p-1 text-gray-300 dark:text-gray-600 hover:text-red-500 dark:hover:text-red-400 opacity-0 group-hover/compact:opacity-100 transition-opacity">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                            @endif
                            @endcan
                        </div>
                    @endforeach
                </div>
            </div>

            @else
            <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <p>{{ __('app.no_members') }}</p>
                @can('update', $group)
                <button type="button" onclick="document.getElementById('addMemberModal').classList.remove('hidden')"
                        class="mt-3 text-sm text-primary-600 dark:text-primary-400 hover:underline">
                    {{ __('app.add_first_member') }}
                </button>
                @endcan
            </div>
            @endif
        </div>

        <!-- Group Guests (separate from members) -->
        @if($group->guests->count() > 0 || auth()->user()->can('update', $group))
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ __('app.group_guests_list') }}</h3>
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 text-xs font-semibold">{{ $group->guests->count() }}</span>
                </div>
            </div>

            @if($group->guests->count() > 0)
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($group->guests->sortBy('first_name') as $guest)
                <div class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group/guest-row">
                    {{-- Avatar --}}
                    <div class="shrink-0" x-data="{ imgErr: false }">
                        @if($guest->photo)
                        <img x-show="!imgErr" x-on:error="imgErr = true" src="{{ Storage::url($guest->photo) }}" alt="" class="w-10 h-10 rounded-full object-cover" loading="lazy">
                        <div x-show="imgErr" x-cloak class="w-10 h-10 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center">
                            <span class="text-white text-sm font-semibold">{{ mb_substr($guest->first_name, 0, 1) }}{{ mb_substr($guest->last_name ?? '', 0, 1) }}</span>
                        </div>
                        @else
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center">
                            <span class="text-white text-sm font-semibold">{{ mb_substr($guest->first_name, 0, 1) }}{{ mb_substr($guest->last_name ?? '', 0, 1) }}</span>
                        </div>
                        @endif
                    </div>

                    {{-- Name --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="font-medium text-sm text-gray-900 dark:text-white truncate">{{ $guest->full_name }}</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-orange-50 text-orange-600 dark:bg-orange-900/20 dark:text-orange-400">{{ __('app.group_role_guest') }}</span>
                        </div>
                        @if($guest->notes)
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 truncate">{{ $guest->notes }}</p>
                        @endif
                    </div>

                    {{-- Birth date --}}
                    @if($guest->birth_date)
                    <span class="hidden sm:inline text-xs text-gray-400">{{ $guest->birth_date->format('d.m.Y') }}</span>
                    @endif

                    {{-- Delete button --}}
                    @can('update', $group)
                    <button @click="ajaxDelete('{{ route('groups.guests.destroy', [$group, $guest]) }}', @js(__('messages.confirm_remove_member')), () => $el.closest('.group\\/guest-row').remove())"
                            class="shrink-0 p-1.5 text-gray-300 dark:text-gray-600 hover:text-red-500 dark:hover:text-red-400 opacity-0 group-hover/guest-row:opacity-100 transition-opacity rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                    @endcan
                </div>
                @endforeach
            </div>
            @else
            <div class="p-6 text-center text-sm text-gray-500 dark:text-gray-400">
                {{ __('app.no_guests_yet') }}
            </div>
            @endif
        </div>
        @endif

        <!-- Recent Attendance -->
        @if($currentChurch->attendance_enabled)
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h3 class="font-semibold text-gray-900 dark:text-white">{{ __('app.attendance') }}</h3>
                <a href="{{ route('groups.attendance.index', $group) }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">
                    {{ __('app.all_records') }}
                </a>
            </div>
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($group->attendances->take(5) as $attendance)
                <a href="{{ route('groups.attendance.show', [$group, $attendance]) }}" class="p-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors block">
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $attendance->date->format('d.m.Y') }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            @if($attendance->time)
                            {{ $attendance->time->format('H:i') }}
                            @endif
                        </p>
                    </div>
                    <div class="text-right">
                        <div class="flex items-center gap-2">
                            <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ $attendance->members_present }}</span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">/ {{ $group->members->count() }}</span>
                        </div>
                        @if($attendance->guests_count > 0)
                        <p class="text-xs text-gray-500">+{{ $attendance->guests_count }} {{ __('app.group_guests') }}</p>
                        @endif
                    </div>
                </a>
                @empty
                <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                    <svg class="w-10 h-10 mx-auto mb-2 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    <p class="text-sm">{{ __('app.no_attendance_records') }}</p>
                    @can('update', $group)
                    <button type="button" onclick="document.getElementById('recordAttendanceModal').classList.remove('hidden')" class="mt-2 inline-block text-sm text-primary-600 dark:text-primary-400 hover:underline">
                        {{ __('app.record_first_meeting') }}
                    </button>
                    @endcan
                </div>
                @endforelse
            </div>
            @if($group->attendances->count() > 0)
            @can('update', $group)
            <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                <button type="button" onclick="document.getElementById('recordAttendanceModal').classList.remove('hidden')" class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    {{ __('app.record_meeting') }}
                </button>
            </div>
            @endcan
            @endif
        </div>
        @endif
    </div>
</div>

<!-- Add Member Modal -->
@can('update', $group)
<div id="addMemberModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="min-h-screen px-4 flex items-center justify-center">
        <div class="fixed inset-0 bg-black/50" onclick="document.getElementById('addMemberModal').classList.add('hidden')"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full p-4 sm:p-6"
             x-data="{ role: 'member', ...ajaxForm({url: '{{ route('groups.members.add', $group) }}', method: 'POST', stayOnPage: true, onSuccess() { Livewire.navigate(window.location.href); }}) }">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('app.add_member_title') }}</h3>
            <form @submit.prevent="submit($refs.addMemberForm)" x-ref="addMemberForm" enctype="multipart/form-data">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.role_label') }}</label>
                        <select name="role" x-model="role" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl dark:text-white focus:ring-2 focus:ring-primary-500">
                            <option value="member">{{ __('app.member_role') }}</option>
                            <option value="assistant">{{ __('app.assistant_role') }}</option>
                            <option value="guest">{{ __('app.group_role_guest') }}</option>
                        </select>
                    </div>

                    <!-- Existing person select (for member/assistant) -->
                    <div x-show="role !== 'guest'" x-transition>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.person_label') }}</label>
                        <x-person-select name="person_id" :people="$availablePeople" :required="false" :nullable="false" :placeholder="__('app.group_search_person')" />
                    </div>

                    <!-- Guest form fields (accordion) -->
                    <template x-if="role === 'guest'">
                        <div class="space-y-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.group_guest_first_name') }} *</label>
                                <input type="text" name="first_name" required
                                       class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl dark:text-white focus:ring-2 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.group_guest_last_name') }}</label>
                                <input type="text" name="last_name"
                                       class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl dark:text-white focus:ring-2 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.date_of_birth') }}</label>
                                <input type="date" name="birth_date" max="{{ now()->format('Y-m-d') }}"
                                       class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl dark:text-white focus:ring-2 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.group_guest_photo') }}</label>
                                <input type="file" name="photo" accept="image/*"
                                       class="w-full text-sm text-gray-500 dark:text-gray-400 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-600 dark:file:bg-primary-900/30 dark:file:text-primary-400 hover:file:bg-primary-100">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.group_guest_notes') }}</label>
                                <textarea name="notes" rows="2"
                                          class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl dark:text-white focus:ring-2 focus:ring-primary-500"></textarea>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 sm:gap-3 mt-6">
                    <button type="button" onclick="document.getElementById('addMemberModal').classList.add('hidden')"
                            class="w-full sm:w-auto px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900">
                        {{ __('app.cancel') }}
                    </button>
                    <button type="submit" :disabled="saving" class="w-full sm:w-auto px-4 py-2 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700 disabled:opacity-50">
                        <span x-show="!saving">{{ __('app.add') }}</span>
                        <span x-show="saving" class="inline-flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            {{ __('app.saving') }}
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

<!-- Record Attendance Modal -->
@can('update', $group)
<div id="recordAttendanceModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="min-h-screen px-4 flex items-start justify-center pt-10 pb-20">
        <div class="fixed inset-0 bg-black/50" onclick="document.getElementById('recordAttendanceModal').classList.add('hidden')"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-lg w-full p-4 sm:p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('app.group_record_meeting') }}</h3>
                <button type="button" onclick="document.getElementById('recordAttendanceModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            @if($existingToday ?? false)
            <div class="mb-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-3">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <p class="text-amber-800 dark:text-amber-200 text-sm">{{ __('app.group_today_record_exists') }}</p>
                </div>
            </div>
            @endif

            <form @submit.prevent="submit($refs.grpAttModalForm)" x-ref="grpAttModalForm"
                  x-data="{ ...ajaxForm({ url: '{{ route('groups.attendance.store', $group) }}', method: 'POST', onSuccess() { Livewire.navigate(window.location.href); } }) }" class="space-y-4">

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="modal_att_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.date') }} *</label>
                        <input type="date" name="date" id="modal_att_date" value="{{ now()->format('Y-m-d') }}" required
                               class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white text-sm">
                    </div>
                    <div>
                        <label for="modal_att_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.group_time') }}</label>
                        <input type="time" name="time" id="modal_att_time" value="{{ $group->meeting_time?->format('H:i') }}"
                               class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="modal_att_location" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.group_location') }}</label>
                        <input type="text" name="location" id="modal_att_location" value="{{ $group->location }}"
                               class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white text-sm">
                    </div>
                    <div>
                        <label for="modal_att_guests" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.group_guests_count') }}</label>
                        <input type="number" name="guests_count" id="modal_att_guests" value="0" min="0"
                               class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white text-sm">
                    </div>
                </div>

                <!-- Members & Guests Checklist -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.group_present_members') }}</label>
                    <div class="max-h-60 overflow-y-auto space-y-1.5 pr-1">
                        @foreach($group->members->filter(fn($m) => $m->pivot->role !== 'guest')->sortBy('first_name') as $member)
                        <label class="flex items-center p-2.5 bg-gray-50 dark:bg-gray-700 rounded-xl cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                            <input type="checkbox" name="present[]" value="{{ $member->id }}"
                                   class="w-5 h-5 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                            <span class="ml-3 text-sm text-gray-900 dark:text-white">{{ $member->full_name }}</span>
                            @if($member->pivot->role !== 'member')
                            <span class="ml-auto text-xs text-gray-500 dark:text-gray-400">
                                @if($member->pivot->role === 'leader') {{ __('app.leader') }}
                                @elseif($member->pivot->role === 'assistant') {{ __('app.assistant_role') }}
                                @endif
                            </span>
                            @endif
                        </label>
                        @endforeach
                        @if($group->guests->count() > 0)
                        <div class="pt-2 mt-1 border-t border-gray-200 dark:border-gray-600">
                            <p class="text-xs font-medium text-orange-600 dark:text-orange-400 mb-1.5 px-1">{{ __('app.group_guests_list') }}</p>
                            @foreach($group->guests->sortBy('first_name') as $guest)
                            <label class="flex items-center p-2.5 bg-orange-50/50 dark:bg-orange-900/10 rounded-xl cursor-pointer hover:bg-orange-100/50 dark:hover:bg-orange-900/20 transition-colors">
                                <input type="checkbox" name="guests_present[]" value="{{ $guest->id }}"
                                       class="w-5 h-5 rounded border-gray-300 text-orange-500 focus:ring-orange-500">
                                <span class="ml-3 text-sm text-gray-900 dark:text-white">{{ $guest->full_name }}</span>
                                <span class="ml-auto text-xs text-orange-500 dark:text-orange-400">{{ __('app.group_role_guest') }}</span>
                            </label>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>

                <div>
                    <label for="modal_att_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.notes') }}</label>
                    <textarea name="notes" id="modal_att_notes" rows="2"
                              placeholder="{{ __('app.group_notes_placeholder') }}"
                              class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white text-sm"></textarea>
                </div>

                <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 sm:gap-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" onclick="document.getElementById('recordAttendanceModal').classList.add('hidden')"
                            class="w-full sm:w-auto px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 font-medium text-sm">
                        {{ __('app.cancel') }}
                    </button>
                    <button type="submit" :disabled="saving" class="w-full sm:w-auto px-5 py-2.5 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700 transition-colors disabled:opacity-50 text-sm">
                        <span x-show="!saving">{{ __('app.save') }}</span>
                        <span x-show="saving" class="inline-flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            {{ __('app.saving') }}
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan


@endsection
