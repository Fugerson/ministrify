@extends('layouts.app')

@section('title', __('app.new_user_title'))

@section('content')
<div class="max-w-2xl mx-auto" x-data="userCreateForm()" @person-selected.window="personSelected = $event.detail.person">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('app.new_user_title') }}</h1>
    </div>

    <form @submit.prevent="submitForm" x-ref="form"
          class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-6">

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('app.link_to_person_label') }}</label>
            <div class="mt-1">
                <x-person-select
                    name="person_id"
                    :people="$people"
                    :selected="old('person_id')"
                    :placeholder="__('app.start_typing_name')"
                    :null-text="__('app.create_without_link')"
                />
            </div>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('app.link_person_hint') }}</p>
        </div>

        <!-- Show selected person info -->
        <div x-show="personSelected && personSelected?.email" x-cloak class="bg-green-50 dark:bg-green-900/20 rounded-xl p-4">
            <p class="text-sm text-green-700 dark:text-green-300">
                <span class="font-medium" x-text="personSelected?.full_name"></span>
                <span class="text-green-600 dark:text-green-400">
                    (<span x-text="personSelected?.email"></span>)
                </span>
            </p>
        </div>

        <!-- Warning when person has no email -->
        <div x-show="personSelected && !personSelected?.email" x-cloak class="bg-amber-50 dark:bg-amber-900/20 rounded-xl p-4">
            <p class="text-sm text-amber-700 dark:text-amber-300">
                <span class="font-medium" x-text="personSelected?.full_name"></span>
                {{ __('app.no_email_warning') }}
            </p>
        </div>

        <!-- Show name/email fields only when no person selected -->
        <div x-show="!personSelected" x-cloak>
            <div class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('app.first_name') }}</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" x-bind:required="!personSelected"
                        class="mt-1 block w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white"
                        :class="errors.name ? 'border-red-500 bg-red-50 dark:bg-red-900/20' : 'border-transparent'">
                    <template x-if="errors.name">
                        <p class="mt-1 text-sm text-red-600" x-text="errors.name[0]"></p>
                    </template>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('app.email') }}</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" x-bind:required="!personSelected"
                        class="mt-1 block w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white"
                        :class="errors.email ? 'border-red-500 bg-red-50 dark:bg-red-900/20' : 'border-transparent'">
                    <template x-if="errors.email">
                        <p class="mt-1 text-sm text-red-600" x-text="errors.email[0]"></p>
                    </template>
                </div>
            </div>
        </div>

        <div>
            <label for="church_role_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('app.role') }}</label>
            <select name="church_role_id" id="church_role_id" required
                class="mt-1 block w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white"
                :class="errors.church_role_id ? 'border-red-500 bg-red-50 dark:bg-red-900/20' : 'border-transparent'">
                @foreach($churchRoles as $role)
                <option value="{{ $role->id }}" {{ old('church_role_id') == $role->id ? 'selected' : '' }}>
                    {{ $role->name }}
                </option>
                @endforeach
            </select>
            <template x-if="errors.church_role_id">
                <p class="mt-1 text-sm text-red-600" x-text="errors.church_role_id[0]"></p>
            </template>
        </div>

        <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 sm:gap-3">
            <a href="{{ route('settings.users.index') }}" class="w-full sm:w-auto px-4 py-2 text-center text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">{{ __('app.cancel') }}</a>
            <button type="submit" :disabled="saving"
                    class="w-full sm:w-auto px-4 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700 disabled:opacity-50">
                <span x-show="!saving">{{ __('app.create') }}</span>
                <span x-show="saving" class="flex items-center justify-center gap-2">
                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    {{ __('app.saving') }}
                </span>
            </button>
        </div>
    </form>
</div>

<script>
function userCreateForm() {
    return {
        saving: false,
        errors: {},
        personSelected: null,
        async submitForm() {
            this.saving = true;
            this.errors = {};
            const formData = new FormData(this.$refs.form);
            try {
                const response = await fetch('{{ route("settings.users.store") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: formData,
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    if (response.status === 422 && data.errors) { this.errors = data.errors; showToast('error', @js(__('app.check_form_errors'))); }
                    else { showToast('error', data.message || @js(__('app.save_error'))); }
                    this.saving = false; return;
                }
                showToast('success', data.message || @js(__('app.saved_toast')));
                setTimeout(() => Livewire.navigate(data.redirect_url || '{{ route("settings.users.index") }}'), 800);
            } catch (e) { showToast('error', @js(__('app.server_connection_error'))); this.saving = false; }
        }
    }
}
</script>
@endsection
