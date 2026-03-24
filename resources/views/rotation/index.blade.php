@extends('layouts.app')

@section('title', __('app.rotation_title'))

@section('content')
<div x-data="{ search: '' }" class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('app.rotation_title') }}</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">{{ __('app.rotation_subtitle') }}</p>
        </div>
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" x-model="search" placeholder="{{ __('app.search') }}..."
                   class="pl-10 pr-4 py-2 w-full sm:w-64 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
        </div>
    </div>

    <!-- Info Card -->
    <div class="bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/30 dark:to-purple-900/30 rounded-2xl border border-indigo-100 dark:border-indigo-800 p-6">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </div>
            <div>
                <h3 class="font-semibold text-gray-900 dark:text-white">{{ __('app.rotation_how_works') }}</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    {{ __('app.rotation_how_works_desc') }}
                </p>
                <div class="flex flex-wrap gap-4 mt-3">
                    <div class="flex items-center gap-2 text-sm">
                        <span class="w-2 h-2 rounded-full bg-green-500"></span>
                        <span class="text-gray-600 dark:text-gray-400">{{ __('app.rotation_workload_balance') }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        <span class="text-gray-600 dark:text-gray-400">{{ __('app.rotation_skill_matching') }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm">
                        <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                        <span class="text-gray-600 dark:text-gray-400">{{ __('app.rotation_availability') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ministries Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($ministries as $ministry)
        <div x-show="!search || {{ Js::from(mb_strtolower($ministry->name)) }}.includes(search.toLowerCase())"
             class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-5">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background-color: {{ $ministry->color ?? '#3b82f6' }}30;">
                        <div class="w-4 h-4 rounded-full" style="background-color: {{ $ministry->color ?? '#3b82f6' }}"></div>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">{{ $ministry->name }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.rotation_members_count', ['count' => $ministry->members_count]) }}</p>
                    </div>
                </div>

                <!-- Upcoming Events Preview -->
                @if(isset($upcomingEvents[$ministry->id]) && $upcomingEvents[$ministry->id]->count() > 0)
                <div class="space-y-2 mb-4">
                    @foreach($upcomingEvents[$ministry->id]->take(3) as $event)
                    <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $event->title }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $event->date->format('d.m') }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            @php
                                $filled = $event->assignments->count();
                                $total = $event->ministry ? ($event->ministry->positions->sum('max_per_event') ?: $event->ministry->positions->count()) : 0;
                                $percentage = $total > 0 ? ($filled / $total) * 100 : 0;
                            @endphp
                            <div class="w-16 h-2 bg-gray-200 dark:bg-gray-600 rounded-full overflow-hidden">
                                <div class="h-full rounded-full {{ $percentage >= 100 ? 'bg-green-500' : ($percentage >= 50 ? 'bg-amber-500' : 'bg-red-500') }}"
                                     style="width: {{ min(100, $percentage) }}%"></div>
                            </div>
                            <span class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $filled }}/{{ $total }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ __('app.rotation_no_scheduled_events') }}</p>
                @endif

                <div class="flex gap-2">
                    <a href="{{ route('rotation.ministry', $ministry) }}"
                       class="flex-1 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors text-center text-sm">
                        {{ __('app.rotation_details') }}
                    </a>
                    @if(isset($upcomingEvents[$ministry->id]) && $upcomingEvents[$ministry->id]->count() > 0)
                    <button type="button"
                            onclick="autoAssignMinistry({{ $ministry->id }}, @js($ministry->name))"
                            class="flex-1 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors text-center text-sm">
                        {{ __('app.rotation_auto_assign') }}
                    </button>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-8 text-center">
                <div class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('app.rotation_no_teams') }}</h3>
                <p class="text-gray-500 dark:text-gray-400 mt-1">{{ __('app.rotation_no_teams_desc') }}</p>
                <a href="{{ route('ministries.create') }}" class="inline-flex items-center gap-2 mt-4 px-4 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('app.rotation_create_team') }}
                </a>
            </div>
        </div>
        @endforelse
    </div>
</div>

<!-- Auto-Assign Modal -->
<div id="autoAssignModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeAutoAssignModal()"></div>
    <div class="absolute inset-4 md:inset-auto md:top-1/2 md:left-1/2 md:-translate-x-1/2 md:-translate-y-1/2 md:w-full md:max-w-lg bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white" id="modalTitle">{{ __('app.rotation_auto_assign') }}</h3>
            <button onclick="closeAutoAssignModal()" class="p-2 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="p-6">
            <div id="modalContent">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentMinistryId = null;
const i18n = {
    autoAssignFor: @json(__('app.rotation_auto_assign_for')),
    autoAssignDesc: @json(__('app.rotation_auto_assign_desc')),
    period: @json(__('app.rotation_period')),
    week1: @json(__('app.rotation_1_week')),
    weeks2: @json(__('app.rotation_2_weeks')),
    weeks4: @json(__('app.rotation_4_weeks')),
    weeks8: @json(__('app.rotation_8_weeks')),
    cancel: @json(__('app.rotation_cancel')),
    run: @json(__('app.rotation_run')),
    running: @json(__('app.rotation_running')),
    error: @json(__('app.rotation_error')),
    close: @json(__('app.rotation_close')),
    connectionError: @json(__('app.rotation_connection_error')),
    complete: @json(__('app.rotation_complete')),
    eventsLabel: @json(__('app.rotation_events_label')),
    assignedLabel: @json(__('app.rotation_assigned_label')),
    unfilledLabel: @json(__('app.rotation_unfilled_label')),
    refreshPage: @json(__('app.rotation_refresh_page')),
};

function autoAssignMinistry(ministryId, ministryName) {
    currentMinistryId = ministryId;
    document.getElementById('modalTitle').textContent = i18n.autoAssignFor + ministryName;
    document.getElementById('modalContent').innerHTML = `
        <div class="space-y-4">
            <p class="text-gray-600 dark:text-gray-400">
                ${i18n.autoAssignDesc}
            </p>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">${i18n.period}</label>
                <select id="weeksSelect" class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl">
                    <option value="1">${i18n.week1}</option>
                    <option value="2">${i18n.weeks2}</option>
                    <option value="4" selected>${i18n.weeks4}</option>
                    <option value="8">${i18n.weeks8}</option>
                </select>
            </div>
            <div class="flex gap-3">
                <button onclick="closeAutoAssignModal()"
                        class="flex-1 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                    ${i18n.cancel}
                </button>
                <button onclick="runAutoAssign()"
                        class="flex-1 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors">
                    ${i18n.run}
                </button>
            </div>
        </div>
    `;
    document.getElementById('autoAssignModal').classList.remove('hidden');
}

function closeAutoAssignModal() {
    document.getElementById('autoAssignModal').classList.add('hidden');
}

async function runAutoAssign() {
    const weeks = document.getElementById('weeksSelect').value;
    document.getElementById('modalContent').innerHTML = `
        <div class="flex flex-col items-center justify-center py-8">
            <svg class="animate-spin h-10 w-10 text-primary-500 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="text-gray-600 dark:text-gray-400">${i18n.running}</p>
        </div>
    `;

    try {
        const response = await fetch(`/rotation/ministry/${currentMinistryId}/auto-assign`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ weeks: parseInt(weeks) }),
        });

        const data = await response.json().catch(() => ({}));

        if (data.success) {
            displayResults(data);
        } else {
            document.getElementById('modalContent').innerHTML = `
                <div class="text-center py-4">
                    <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                    <p class="text-red-600 dark:text-red-400">${i18n.error}</p>
                    <button onclick="closeAutoAssignModal()" class="mt-4 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl">
                        ${i18n.close}
                    </button>
                </div>
            `;
        }
    } catch (error) {
        console.error(error);
        document.getElementById('modalContent').innerHTML = `
            <div class="text-center py-4">
                <p class="text-red-600 dark:text-red-400">${i18n.connectionError}</p>
                <button onclick="closeAutoAssignModal()" class="mt-4 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl">
                    ${i18n.close}
                </button>
            </div>
        `;
    }
}

function displayResults(data) {
    const summary = data.summary;
    let html = `
        <div class="text-center mb-6">
            <div class="w-16 h-16 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h4 class="text-lg font-semibold text-gray-900 dark:text-white">${i18n.complete}</h4>
        </div>
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="text-center p-3 bg-gray-50 dark:bg-gray-700 rounded-xl">
                <p class="text-2xl font-bold text-gray-900 dark:text-white">${summary.events}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">${i18n.eventsLabel}</p>
            </div>
            <div class="text-center p-3 bg-green-50 dark:bg-green-900/30 rounded-xl">
                <p class="text-2xl font-bold text-green-600 dark:text-green-400">${summary.assigned}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">${i18n.assignedLabel}</p>
            </div>
            <div class="text-center p-3 bg-amber-50 dark:bg-amber-900/30 rounded-xl">
                <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">${summary.unassigned}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">${i18n.unfilledLabel}</p>
            </div>
        </div>
        <!-- SPA reload: assignment counts and progress bars need fresh server data -->
        <button onclick="Livewire.navigate(window.location.href)" class="w-full px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors">
            ${i18n.refreshPage}
        </button>
    `;
    document.getElementById('modalContent').innerHTML = html;
}
</script>
@endpush
@endsection
