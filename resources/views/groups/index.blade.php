@extends('layouts.app')

@section('title', __('app.groups'))

@section('actions')
@can('create', App\Models\Group::class)
<button type="button" onclick="openCreateGroupModal()" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-xl hover:bg-primary-700 transition-colors">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
    </svg>
    {{ __('app.group_create') }}
</button>
@endcan
@endsection

@section('content')
<div class="space-y-8">
    @if($groups->isEmpty())
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
        <div class="w-20 h-20 mx-auto mb-4 bg-gradient-to-br from-purple-100 to-purple-50 dark:from-purple-900 dark:to-purple-800 rounded-2xl flex items-center justify-center">
            <svg class="w-10 h-10 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ __('app.group_no_groups') }}</h3>
        <p class="text-gray-500 dark:text-gray-400 mb-6">{{ __('app.group_create_hint') }}</p>
        @can('create', App\Models\Group::class)
        <button type="button" onclick="openCreateGroupModal()" class="inline-flex items-center px-5 py-2.5 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700 transition-all">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            {{ __('app.group_create') }}
        </button>
        @endcan
    </div>
    @else
    <!-- Stats -->
    <div id="groups-stats" class="grid grid-cols-3 gap-2 sm:gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $groups->count() }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.group_total_groups') }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $groups->sum('members_count') }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.group_total_members') }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $groups->count() > 0 ? round($groups->sum('members_count') / $groups->count(), 1) : 0 }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.group_avg_size') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Groups Table -->
    <div id="groups-table" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('app.group') }}</th>
                        <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden md:table-cell">{{ __('app.leader') }}</th>
                        <th class="px-3 md:px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden sm:table-cell">{{ __('app.status') }}</th>
                        <th class="px-3 md:px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('app.members') }}</th>
                        <th class="px-3 md:px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($groups as $group)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="px-3 md:px-6 py-3 md:py-4">
                            <a href="{{ route('groups.show', $group) }}" class="flex items-center group">
                                <div class="w-9 h-9 md:w-10 md:h-10 rounded-xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 md:w-5 md:h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <div class="ml-3 min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 truncate">{{ $group->name }}</p>
                                    @if($group->description)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-[150px] sm:max-w-xs hidden sm:block">{{ Str::limit($group->description, 50) }}</p>
                                    @endif
                                    <!-- Mobile: show leader under name -->
                                    <p class="md:hidden text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ $group->leader?->full_name ?? '' }}</p>
                                </div>
                            </a>
                        </td>
                        <td class="px-3 md:px-6 py-3 md:py-4 hidden md:table-cell">
                            @if($group->leader)
                            <a href="{{ route('people.show', $group->leader) }}" class="text-sm text-gray-900 dark:text-white hover:text-primary-600 dark:hover:text-primary-400">
                                {{ $group->leader->full_name }}
                            </a>
                            @else
                            <span class="text-sm text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-3 md:px-6 py-3 md:py-4 whitespace-nowrap text-center hidden sm:table-cell">
                            <span class="inline-flex items-center gap-1.5 px-2 md:px-2.5 py-1 rounded-lg text-xs font-medium
                                @if($group->status === 'active') bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300
                                @elseif($group->status === 'paused') bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300
                                @else bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300
                                @endif">
                                <span class="w-1.5 h-1.5 rounded-full
                                    @if($group->status === 'active') bg-green-500
                                    @elseif($group->status === 'paused') bg-yellow-500
                                    @else bg-blue-500
                                    @endif"></span>
                                <span class="hidden md:inline">{{ $group->status_label }}</span>
                            </span>
                        </td>
                        <td class="px-3 md:px-6 py-3 md:py-4 whitespace-nowrap text-center">
                            <span class="inline-flex items-center px-2 md:px-2.5 py-1 rounded-lg text-sm font-medium bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300">
                                {{ $group->members_count }}
                            </span>
                        </td>
                        <td class="px-3 md:px-6 py-3 md:py-4 whitespace-nowrap text-right">
                            <a href="{{ route('groups.show', $group) }}" class="p-2 inline-flex text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

{{-- Create Group Modal --}}
@can('create', App\Models\Group::class)
<div id="createGroupModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-start justify-center min-h-screen pt-4 px-4 pb-20 sm:p-0">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" onclick="closeCreateGroupModal()"></div>
        <div class="relative w-full max-w-lg mx-auto mt-8 sm:mt-16 bg-white dark:bg-gray-800 rounded-2xl shadow-xl z-10" x-data="groupCreateForm()">
            <form x-ref="form" @submit.prevent="submitForm">
                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('app.group_new') }}</h3>
                    <button type="button" onclick="closeCreateGroupModal()" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Body --}}
                <div class="px-6 py-5 space-y-5 max-h-[70vh] overflow-y-auto">
                    {{-- Name --}}
                    <div>
                        <label for="modal_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.group_name') }}</label>
                        <input type="text" name="name" id="modal_name" required
                               placeholder="{{ __('app.group_name_placeholder') }}"
                               :class="errors.name ? 'ring-2 ring-red-500' : ''"
                               class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                        <template x-if="errors.name">
                            <p class="mt-1 text-sm text-red-600" x-text="errors.name[0]"></p>
                        </template>
                    </div>

                    {{-- Description --}}
                    <div>
                        <label for="modal_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.description') }}</label>
                        <textarea name="description" id="modal_description" rows="3"
                                  placeholder="{{ __('app.group_desc_placeholder') }}"
                                  class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white"></textarea>
                    </div>

                    {{-- Leader --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.group_leader') }}</label>
                        <x-person-select
                            name="leader_id"
                            :people="$people"
                            :placeholder="__('app.leader_placeholder')"
                            :null-text="__('app.without_leader')"
                        />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('app.group_leader_auto_member') }}</p>
                    </div>

                    {{-- Meeting details --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label for="modal_meeting_day" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.group_meeting_day') }}</label>
                            <select name="meeting_day" id="modal_meeting_day"
                                    class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                                <option value="">{{ __('app.group_not_specified') }}</option>
                                @foreach(['monday' => __('app.monday'), 'tuesday' => __('app.tuesday'), 'wednesday' => __('app.wednesday'), 'thursday' => __('app.thursday'), 'friday' => __('app.friday'), 'saturday' => __('app.saturday'), 'sunday' => __('app.sunday')] as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="modal_meeting_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.group_meeting_time') }}</label>
                            <input type="time" name="meeting_time" id="modal_meeting_time"
                                   class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                        </div>
                        <div>
                            <label for="modal_location" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.group_meeting_location') }}</label>
                            <input type="text" name="location" id="modal_location"
                                   placeholder="{{ __('app.group_location_placeholder') }}"
                                   class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                        </div>
                    </div>

                    {{-- Status --}}
                    <div>
                        <label for="modal_status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.status') }}</label>
                        <select name="status" id="modal_status"
                                class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                            @foreach(\App\Models\Group::getStatuses() as $value => $label)
                            <option value="{{ $value }}" {{ $value === 'active' ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 sm:gap-3 px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" onclick="closeCreateGroupModal()"
                            class="w-full sm:w-auto px-5 py-2.5 text-center text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white font-medium">
                        {{ __('app.cancel') }}
                    </button>
                    <button type="submit" :disabled="saving"
                            class="w-full sm:w-auto px-5 py-2.5 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700 transition-colors disabled:opacity-50">
                        <span x-show="!saving">{{ __('app.group_create') }}</span>
                        <span x-show="saving" class="flex items-center justify-center gap-2">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
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

@push('scripts')
<script>
function openCreateGroupModal() {
    document.getElementById('createGroupModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closeCreateGroupModal() {
    document.getElementById('createGroupModal').classList.add('hidden');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !document.getElementById('createGroupModal').classList.contains('hidden')) {
        closeCreateGroupModal();
    }
});

function groupCreateForm() {
    return {
        saving: false,
        errors: {},
        async submitForm() {
            this.saving = true;
            this.errors = {};
            const formData = new FormData(this.$refs.form);
            try {
                const response = await fetch('{{ route("groups.store") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: formData,
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    if (response.status === 422 && data.errors) {
                        this.errors = data.errors;
                        showToast('error', @js( __('app.form_check_error') ));
                    } else {
                        showToast('error', data.message || @js( __('app.save_error') ));
                    }
                    this.saving = false;
                    return;
                }
                showToast('success', data.message || @js( __('app.saved_msg') ));
                closeCreateGroupModal();
                setTimeout(() => Livewire.navigate(window.location.href), 600);
            } catch (e) {
                showToast('error', @js( __('app.server_error') ));
                this.saving = false;
            }
        }
    }
}
</script>
@endpush
