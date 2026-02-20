@extends('layouts.app')

@section('title', 'Редагувати групу')

@section('content')
<div class="max-w-2xl" x-data="groupEditForm()">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <form @submit.prevent="submitForm" class="space-y-6" x-ref="form">

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.group_name') }}</label>
                <input type="text" name="name" id="name" value="{{ old('name', $group->name) }}" required
                       class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                <template x-if="errors.name">
                    <p class="mt-1 text-sm text-red-600" x-text="errors.name[0]"></p>
                </template>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Опис</label>
                <textarea name="description" id="description" rows="3"
                          class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">{{ old('description', $group->description) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.group_leader') }}</label>
                <x-person-select
                    name="leader_id"
                    :people="$people"
                    :selected="old('leader_id', $group->leader_id)"
                    :placeholder="__('app.leader_placeholder')"
                    :null-text="__('app.without_leader')"
                />
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Статус</label>
                <select name="status" id="status"
                        class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                    @foreach(\App\Models\Group::getStatuses() as $value => $label)
                    <option value="{{ $value }}" {{ old('status', $group->status) == $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {{ __('app.status_help') }}
                </p>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                @can('delete', $group)
                <button type="button"
                        onclick="if(confirm('{{ __('messages.confirm_delete_group') }}')) { document.getElementById('delete-group-form').submit(); }"
                        class="text-red-600 hover:text-red-700 text-sm font-medium">
                    {{ __('app.delete_group') }}
                </button>
                @endcan

                <div class="flex flex-col-reverse sm:flex-row sm:items-center gap-2 sm:gap-3">
                    <a href="{{ route('groups.show', $group) }}" class="w-full sm:w-auto px-5 py-2.5 text-center text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white font-medium">
                        {{ __('app.cancel') }}
                    </a>
                    <button type="submit" :disabled="saving"
                            class="w-full sm:w-auto px-5 py-2.5 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700 transition-colors disabled:opacity-50">
                        <span x-show="!saving">{{ __('app.save') }}</span>
                        <span x-show="saving" class="flex items-center justify-center gap-2">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            {{ __('app.saving') }}
                        </span>
                    </button>
                </div>
            </div>
        </form>

        @can('delete', $group)
        <form id="delete-group-form" method="POST" action="{{ route('groups.destroy', $group) }}" class="hidden">
            @csrf
            @method('DELETE')
        </form>
        @endcan
    </div>
</div>

<script>
function groupEditForm() {
    return {
        saving: false,
        errors: {},
        async submitForm() {
            this.saving = true;
            this.errors = {};
            const formData = new FormData(this.$refs.form);
            formData.append('_method', 'PUT');
            try {
                const response = await fetch('{{ route("groups.update", $group) }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: formData,
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    if (response.status === 422 && data.errors) { this.errors = data.errors; showToast('error', '{{ __('app.form_check_error') }}'); }
                    else { showToast('error', data.message || '{{ __('app.save_error') }}'); }
                    this.saving = false; return;
                }
                showToast('success', data.message || '{{ __('app.saved_msg') }}');
            } catch (e) { showToast('error', "{{ __('app.server_error') }}"); }
            this.saving = false;
        }
    }
}
</script>
@endsection
