@extends('layouts.app')

@section('title', 'Чек-ін: ' . $group->name)

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $group->name }}</h2>
                <p class="text-gray-500 dark:text-gray-400">{{ $attendance->date->format('d.m.Y') }} о {{ now()->format('H:i') }}</p>
            </div>
            <div class="text-right">
                <div class="text-3xl font-bold text-primary-600 dark:text-primary-400" id="presentCount">{{ $attendance->members_present }}</div>
                <div class="text-sm text-gray-500">з {{ $group->members->count() }}</div>
            </div>
        </div>
    </div>

    <!-- Members List -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
        <div class="p-4 border-b border-gray-100 dark:border-gray-700">
            <h3 class="font-semibold text-gray-900 dark:text-white">Відмітити присутніх</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Натисніть на учасника щоб відмітити</p>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-gray-700">
            @foreach($group->members->sortBy('first_name') as $member)
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
                    <img src="{{ Storage::url($member->photo) }}" class="w-12 h-12 rounded-full object-cover mr-4">
                    @else
                    <div class="w-12 h-12 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center mr-4">
                        <span class="text-lg font-medium text-primary-600 dark:text-primary-400">{{ mb_substr($member->first_name, 0, 1) }}{{ mb_substr($member->last_name, 0, 1) }}</span>
                    </div>
                    @endif
                    <div class="text-left">
                        <p class="font-medium text-gray-900 dark:text-white">{{ $member->full_name }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            @if($member->pivot->role === 'leader')
                            Лідер
                            @elseif($member->pivot->role === 'assistant')
                            Помічник
                            @else
                            Учасник
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
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="flex gap-3">
        <button type="button" onclick="markAllPresent()" class="flex-1 px-4 py-3 bg-green-600 text-white rounded-xl font-medium hover:bg-green-700 transition-colors">
            Усі присутні
        </button>
        <a href="{{ route('groups.show', $group) }}" class="flex-1 px-4 py-3 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-xl font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors text-center">
            Готово
        </a>
    </div>
</div>

<script>
const groupId = {{ $group->id }};
const attendanceId = {{ $attendance->id }};
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

        const data = await response.json();

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
    const buttons = document.querySelectorAll('[data-person-id]');
    for (const button of buttons) {
        const indicator = button.querySelector('.check-indicator');
        if (indicator.classList.contains('opacity-0')) {
            await togglePresence(button.dataset.personId, button);
            await new Promise(r => setTimeout(r, 100)); // Small delay for UX
        }
    }
}
</script>
@endsection
