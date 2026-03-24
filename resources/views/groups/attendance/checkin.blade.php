@extends('layouts.app')

@section('title', __('app.group_checkin') . ': ' . $group->name)

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $group->name }}</h2>
                <p class="text-gray-500 dark:text-gray-400">{{ $attendance->date->format('d.m.Y') }} {{ __('app.group_at_time') }} {{ now()->format('H:i') }}</p>
            </div>
            <div class="text-right">
                <div class="text-3xl font-bold text-primary-600 dark:text-primary-400" id="presentCount">{{ $attendance->members_present }}</div>
                <div class="text-sm text-gray-500">{{ __('app.group_attendance_of_total', ['total' => $group->members->count()]) }}</div>
            </div>
        </div>
    </div>

    <!-- Members & Guests List -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="font-semibold text-gray-900 dark:text-white">{{ __('app.group_mark_present') }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.group_tap_to_mark') }}</p>
        </div>
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            @foreach($group->members->filter(fn($m) => $m->pivot->role !== 'guest')->sortBy('first_name') as $member)
            @php
                $record = $attendance->records->firstWhere('person_id', $member->id);
                $isPresent = $record ? $record->present : false;
            @endphp
            <button type="button"
                    onclick="togglePresence({{ $member->id }}, this)"
                    data-person-id="{{ $member->id }}"
                    class="w-full p-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-all {{ $isPresent ? 'bg-green-50 dark:bg-green-900/20' : '' }}">
                <div class="flex items-center">
                    @if($member->photo)
                    <div class="mr-4" x-data="{ hover: false, r: {} }" @mouseenter="hover = true; r = $el.getBoundingClientRect()" @mouseleave="hover = false">
                        <img src="{{ Storage::url($member->photo) }}" alt="{{ $member->full_name }}" class="w-12 h-12 rounded-full object-cover" loading="lazy">
                        <div class="fixed z-[100] pointer-events-none" :style="`left:${r.left+r.width/2}px;top:${r.top-8}px;transform:translate(-50%,-100%)`">
                            <img src="{{ Storage::url($member->photo) }}" :class="hover ? 'opacity-100 scale-100' : 'opacity-0 scale-75'" class="w-32 h-32 rounded-xl object-cover shadow-xl ring-2 ring-white dark:ring-gray-800 transition-all duration-200 ease-out origin-bottom">
                        </div>
                    </div>
                    @else
                    <div class="w-12 h-12 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center mr-4">
                        <span class="text-lg font-medium text-primary-600 dark:text-primary-400">{{ mb_substr($member->first_name, 0, 1) }}{{ mb_substr($member->last_name ?? '', 0, 1) }}</span>
                    </div>
                    @endif
                    <div class="text-left">
                        <p class="font-medium text-gray-900 dark:text-white">{{ $member->full_name }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            @if($member->pivot->role === 'leader')
                            {{ __('app.leader') }}
                            @elseif($member->pivot->role === 'assistant')
                            {{ __('app.assistant_role') }}
                            @else
                            {{ __('app.member_role') }}
                            @endif
                        </p>
                    </div>
                </div>
                <div class="check-indicator {{ $isPresent ? '' : 'opacity-0' }} transition-opacity">
                    <div class="w-10 h-10 rounded-full bg-green-500 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </div>
            </button>
            @endforeach

            @if($group->guests->count() > 0)
            <div class="border-t-2 border-orange-200 dark:border-orange-800">
                <div class="px-4 py-2 bg-orange-50 dark:bg-orange-900/20">
                    <p class="text-xs font-medium text-orange-600 dark:text-orange-400">{{ __('app.group_guests_list') }}</p>
                </div>
                @foreach($group->guests->sortBy('first_name') as $guest)
                @php
                    $guestRecord = \DB::table('group_guest_attendance')
                        ->where('group_guest_id', $guest->id)
                        ->where('attendance_id', $attendance->id)
                        ->first();
                    $isGuestPresent = $guestRecord ? $guestRecord->present : false;
                @endphp
                <button type="button"
                        onclick="toggleGuestPresence({{ $guest->id }}, this)"
                        data-guest-id="{{ $guest->id }}"
                        class="w-full p-4 flex items-center justify-between hover:bg-orange-50 dark:hover:bg-orange-900/10 transition-all {{ $isGuestPresent ? 'bg-green-50 dark:bg-green-900/20' : '' }}">
                    <div class="flex items-center">
                        @if($guest->photo)
                        <img src="{{ Storage::url($guest->photo) }}" alt="" class="w-12 h-12 rounded-full object-cover mr-4" loading="lazy">
                        @else
                        <div class="w-12 h-12 rounded-full bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center mr-4">
                            <span class="text-lg font-medium text-orange-600 dark:text-orange-400">{{ mb_substr($guest->first_name, 0, 1) }}{{ mb_substr($guest->last_name ?? '', 0, 1) }}</span>
                        </div>
                        @endif
                        <div class="text-left">
                            <p class="font-medium text-gray-900 dark:text-white">{{ $guest->full_name }}</p>
                            <p class="text-sm text-orange-500 dark:text-orange-400">{{ __('app.group_role_guest') }}</p>
                        </div>
                    </div>
                    <div class="check-indicator {{ $isGuestPresent ? '' : 'opacity-0' }} transition-opacity">
                        <div class="w-10 h-10 rounded-full bg-green-500 flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                    </div>
                </button>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="flex gap-3">
        <button type="button" onclick="markAllPresent()" class="flex-1 px-4 py-3 bg-green-600 text-white rounded-xl font-medium hover:bg-green-700 transition-colors">
            {{ __('app.group_all_present') }}
        </button>
        <a href="{{ route('groups.show', $group) }}" class="flex-1 px-4 py-3 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-xl font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors text-center">
            {{ __('app.group_done') }}
        </a>
    </div>
</div>

<script>
const toggleUrl = '{{ route("groups.attendance.toggle", [$group, $attendance]) }}';
const csrfToken = '{{ csrf_token() }}';

async function togglePresence(personId, button) {
    try {
        const response = await fetch(toggleUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ person_id: personId })
        });

        const data = await response.json().catch(() => ({}));

        if (data.success) {
            const indicator = button.querySelector('.check-indicator');
            if (data.present) {
                button.classList.add('bg-green-50', 'dark:bg-green-900/20');
                indicator.classList.remove('opacity-0');
            } else {
                button.classList.remove('bg-green-50', 'dark:bg-green-900/20');
                indicator.classList.add('opacity-0');
            }
            document.getElementById('presentCount').textContent = data.members_present;
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

async function toggleGuestPresence(guestId, button) {
    try {
        const response = await fetch(toggleUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ guest_id: guestId })
        });

        const data = await response.json().catch(() => ({}));

        if (data.success) {
            const indicator = button.querySelector('.check-indicator');
            if (data.present) {
                button.classList.add('bg-green-50', 'dark:bg-green-900/20');
                indicator.classList.remove('opacity-0');
            } else {
                button.classList.remove('bg-green-50', 'dark:bg-green-900/20');
                indicator.classList.add('opacity-0');
            }
            document.getElementById('presentCount').textContent = data.members_present;
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

async function markAllPresent() {
    try {
        const response = await fetch('{{ route("groups.attendance.markAll", [$group, $attendance]) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ include_guests: true })
        });

        const data = await response.json().catch(() => ({}));

        if (data.success) {
            // Update all member buttons visually
            document.querySelectorAll('[data-person-id]').forEach(button => {
                button.classList.add('bg-green-50', 'dark:bg-green-900/20');
                const indicator = button.querySelector('.check-indicator');
                if (indicator) indicator.classList.remove('opacity-0');
            });

            // Update all guest buttons visually
            document.querySelectorAll('[data-guest-id]').forEach(button => {
                button.classList.add('bg-green-50', 'dark:bg-green-900/20');
                const indicator = button.querySelector('.check-indicator');
                if (indicator) indicator.classList.remove('opacity-0');
            });

            document.getElementById('presentCount').textContent = data.members_present;
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Real-time attendance sync via Laravel Reverb
if (window.Echo) {
    window.Echo.private('church.{{ $group->church_id }}.attendance.{{ $attendance->id }}')
        .listen('.attendance.updated', function(e) {
            // Update present count from other user's action
            document.getElementById('presentCount').textContent = e.present_count;

            // If a specific person was toggled, update their button visually
            if (e.person_id) {
                var btn = document.querySelector('[data-person-id="' + e.person_id + '"]');
                if (btn) {
                    var indicator = btn.querySelector('.check-indicator');
                    if (e.present) {
                        btn.classList.add('bg-green-50', 'dark:bg-green-900/20');
                        if (indicator) indicator.classList.remove('opacity-0');
                    } else {
                        btn.classList.remove('bg-green-50', 'dark:bg-green-900/20');
                        if (indicator) indicator.classList.add('opacity-0');
                    }
                }
            } else {
                // Bulk update (markAll) — mark all visible buttons as present
                document.querySelectorAll('[data-person-id], [data-guest-id]').forEach(function(btn) {
                    btn.classList.add('bg-green-50', 'dark:bg-green-900/20');
                    var indicator = btn.querySelector('.check-indicator');
                    if (indicator) indicator.classList.remove('opacity-0');
                });
            }
        });
}
</script>
@endsection
