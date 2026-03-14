@extends('layouts.app')

@section('title', __('app.group_edit_record') . ': ' . $attendance->date->format('d.m.Y'))

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">{{ __('app.group_edit_record') }}</h2>

        <form @submit.prevent="submit($refs.grpAttEditForm)" x-ref="grpAttEditForm" x-data="{ ...ajaxForm({ url: '{{ route('groups.attendance.update', [$group, $attendance]) }}', method: 'PUT' }) }" class="space-y-6">

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.date') }} *</label>
                    <input type="date" name="date" id="date" value="{{ old('date', $attendance->date->format('Y-m-d')) }}" required
                           class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                </div>
                <div>
                    <label for="time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.group_time') }}</label>
                    <input type="time" name="time" id="time" value="{{ old('time', $attendance->time?->format('H:i')) }}"
                           class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                </div>
            </div>

            <div>
                <label for="location" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.group_location') }}</label>
                <input type="text" name="location" id="location" value="{{ old('location', $attendance->location) }}"
                       placeholder="{{ __('app.group_location_name_placeholder') }}"
                       class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
            </div>

            <!-- Members Checklist -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{ __('app.group_present_members') }}</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach($group->members->sortBy('first_name') as $member)
                    <label class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-xl cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                        <input type="checkbox" name="present[]" value="{{ $member->id }}"
                               {{ in_array($member->id, $presentIds) ? 'checked' : '' }}
                               class="w-5 h-5 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        <span class="ml-3 text-gray-900 dark:text-white">{{ $member->full_name }}</span>
                        @if($member->pivot->role !== 'member')
                        <span class="ml-auto text-xs text-gray-500 dark:text-gray-400">
                            {{ $member->pivot->role === 'leader' ? __('app.leader') : __('app.assistant_role') }}
                        </span>
                        @endif
                    </label>
                    @endforeach
                </div>
            </div>

            <!-- Guests Checklist -->
            @if($group->guests->count() > 0)
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{ __('app.group_present_guests') }}</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach($group->guests->sortBy('first_name') as $guest)
                    <label class="flex items-center p-3 bg-orange-50 dark:bg-orange-900/10 rounded-xl cursor-pointer hover:bg-orange-100 dark:hover:bg-orange-900/20 transition-colors">
                        <input type="checkbox" name="guests_present[]" value="{{ $guest->id }}"
                               {{ in_array($guest->id, $guestsPresentIds) ? 'checked' : '' }}
                               class="w-5 h-5 rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                        <div class="ml-3 flex items-center gap-2">
                            @if($guest->photo)
                            <img src="{{ Storage::url($guest->photo) }}" alt="" class="w-6 h-6 rounded-full object-cover">
                            @endif
                            <span class="text-gray-900 dark:text-white">{{ $guest->full_name }}</span>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>
            @endif

            <div>
                <label for="guests_count" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.group_guests_count') }}</label>
                <input type="number" name="guests_count" id="guests_count" value="{{ old('guests_count', $attendance->anonymous_guests_count ?? 0) }}" min="0"
                       class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
            </div>

            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.notes') }}</label>
                <textarea name="notes" id="notes" rows="3"
                          placeholder="{{ __('app.group_notes_placeholder') }}"
                          class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">{{ old('notes', $attendance->notes) }}</textarea>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                <div></div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('groups.attendance.show', [$group, $attendance]) }}" class="px-5 py-2.5 text-gray-700 dark:text-gray-300 hover:text-gray-900 font-medium">
                        {{ __('app.cancel') }}
                    </a>
                    <button type="submit" :disabled="saving" class="px-5 py-2.5 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700 transition-colors disabled:opacity-50">
                        <span x-text="saving ? @js(__('app.saving')) : @js(__('app.save'))"></span>
                    </button>
                </div>
            </div>
        </form>

        <!-- Delete button -->
        @can('update', $group)
        <div class="mt-4 text-center">
            <button type="button"
                    @click="ajaxDelete('{{ route('groups.attendance.destroy', [$group, $attendance]) }}', @js(__('messages.confirm_delete_record')), null, '{{ route('groups.show', $group) }}')"
                    class="text-red-600 hover:text-red-700 text-sm font-medium hover:underline">
                {{ __('app.group_delete_record') }}
            </button>
        </div>
        @endcan
    </div>
</div>
@endsection
