@extends('layouts.app')

@section('title', __('app.prayer_index_title'))

@section('actions')
<div class="flex items-center space-x-2">
    <a href="{{ route('prayer-requests.wall') }}"
       class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
        </svg>
        {{ __('app.prayer_wall') }}
    </a>
    <button onclick="openCreatePrayerRequestModal()"
       class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        {{ __('app.prayer_new_request') }}
    </button>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Stats -->
    <div class="grid grid-cols-3 gap-2 md:gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-3 md:p-6">
            <div class="flex flex-col sm:flex-row items-center sm:items-start">
                <div class="w-10 h-10 md:w-12 md:h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 md:w-6 md:h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                    </svg>
                </div>
                <div class="sm:ml-3 md:ml-4 mt-2 sm:mt-0 text-center sm:text-left">
                    <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400">{{ __('app.prayer_stat_active') }}</p>
                    <p class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['active'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-3 md:p-6">
            <div class="flex flex-col sm:flex-row items-center sm:items-start">
                <div class="w-10 h-10 md:w-12 md:h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 md:w-6 md:h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="sm:ml-3 md:ml-4 mt-2 sm:mt-0 text-center sm:text-left">
                    <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400">{{ __('app.prayer_stat_answers') }}</p>
                    <p class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['answered'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-3 md:p-6">
            <div class="flex flex-col sm:flex-row items-center sm:items-start">
                <div class="w-10 h-10 md:w-12 md:h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 md:w-6 md:h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                </div>
                <div class="sm:ml-3 md:ml-4 mt-2 sm:mt-0 text-center sm:text-left">
                    <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400">{{ __('app.prayer_stat_prayers') }}</p>
                    <p class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total_prayers'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-3 md:p-4 overflow-x-auto">
        <div class="flex gap-1 sm:gap-2 min-w-max">
            <a href="{{ route('prayer-requests.index') }}"
               class="px-3 md:px-4 py-2 rounded-lg text-xs sm:text-sm font-medium transition-colors whitespace-nowrap {{ !request('status') ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                {{ __('app.prayer_filter_active') }}
            </a>
            <a href="{{ route('prayer-requests.index', ['status' => 'answered']) }}"
               class="px-3 md:px-4 py-2 rounded-lg text-xs sm:text-sm font-medium transition-colors whitespace-nowrap {{ request('status') === 'answered' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                {{ __('app.prayer_filter_answers') }}
            </a>
            <a href="{{ route('prayer-requests.index', ['status' => 'closed']) }}"
               class="px-3 md:px-4 py-2 rounded-lg text-xs sm:text-sm font-medium transition-colors whitespace-nowrap {{ request('status') === 'closed' ? 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                {{ __('app.prayer_filter_closed') }}
            </a>
        </div>
    </div>

    <!-- Prayer Requests List -->
    <div class="space-y-4">
        @forelse($prayerRequests as $request)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 md:p-6 hover:shadow-md transition-shadow">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-1.5 md:gap-2 mb-2">
                            @if($request->is_urgent)
                                <span class="px-2 py-1 bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 text-xs font-medium rounded-full">
                                    {{ __('app.prayer_badge_urgent') }}
                                </span>
                            @endif
                            @if(!$request->is_public)
                                <span class="px-2 py-1 bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 text-xs font-medium rounded-full">
                                    {{ __('app.prayer_badge_private') }}
                                </span>
                            @endif
                            <span class="px-2 py-1 bg-{{ $request->status_color }}-100 text-{{ $request->status_color }}-700 dark:bg-{{ $request->status_color }}-900/30 dark:text-{{ $request->status_color }}-400 text-xs font-medium rounded-full">
                                {{ $request->status_label }}
                            </span>
                        </div>

                        <a href="{{ route('prayer-requests.show', $request) }}" class="block group">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400">
                                {{ $request->title }}
                            </h3>
                        </a>

                        <p class="mt-2 text-gray-600 dark:text-gray-400 line-clamp-2">
                            {{ Str::limit($request->description, 200) }}
                        </p>

                        <div class="mt-4 flex items-center text-sm text-gray-500 dark:text-gray-400">
                            <span>{{ $request->author_name }}</span>
                            <span class="mx-2">•</span>
                            <span>{{ $request->created_at->diffForHumans() }}</span>
                            <span class="mx-2">•</span>
                            <span class="flex items-center">
                                {{ $request->prayer_count }}
                            </span>
                        </div>
                    </div>

                    @if($request->status === 'active')
                        <div class="sm:ml-4 w-full sm:w-auto" x-data="{ prayed: {{ $request->hasPrayed(auth()->user()) ? 'true' : 'false' }}, prayerCount: {{ $request->prayer_count }} }">
                            <button type="button"
                                    @click="if(!prayed) { ajaxAction('{{ route('prayer-requests.pray', $request) }}', 'POST').then(() => { prayed = true; prayerCount++; }).catch(() => {}) }"
                                    :disabled="prayed"
                                    class="w-full sm:w-auto px-4 py-2 bg-primary-50 hover:bg-primary-100 dark:bg-primary-900/20 dark:hover:bg-primary-900/40 text-primary-600 dark:text-primary-400 rounded-lg font-medium text-sm transition-colors"
                                    :class="{ 'opacity-50': prayed }">
                                <span x-text="prayed ? @json(__('app.prayer_prayed')) : @json(__('app.prayer_praying'))"></span>
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-12 text-center">
                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">{{ __('app.prayer_no_requests') }}</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-4">{{ __('app.prayer_no_requests_desc') }}</p>
                <button onclick="openCreatePrayerRequestModal()"
                   class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                    {{ __('app.prayer_add_request') }}
                </button>
            </div>
        @endforelse
    </div>

    {{ $prayerRequests->links() }}
</div>

{{-- Create Prayer Request Modal --}}
<div id="createPrayerRequestModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-start justify-center min-h-screen pt-4 px-4 pb-20 sm:p-0">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" onclick="closeCreatePrayerRequestModal()"></div>
        <div class="relative w-full max-w-lg mx-auto mt-8 sm:mt-16 bg-white dark:bg-gray-800 rounded-2xl shadow-xl z-10" x-data="prayerRequestCreateForm()">
            <form x-ref="form" @submit.prevent="submitForm">
                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                                                {{ __('app.prayer_new_title') }}
                    </h2>
                    <button type="button" onclick="closeCreatePrayerRequestModal()" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Body --}}
                <div class="px-6 py-4 space-y-5 max-h-[70vh] overflow-y-auto">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('app.prayer_title_label') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="title" required maxlength="255"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                               placeholder="{{ __('app.prayer_title_placeholder') }}">
                        <template x-if="errors.title">
                            <p class="mt-1 text-sm text-red-500" x-text="errors.title[0]"></p>
                        </template>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('app.prayer_description_label') }} <span class="text-red-500">*</span>
                        </label>
                        <textarea name="description" rows="5" required maxlength="2000"
                                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                  placeholder="{{ __('app.prayer_description_placeholder') }}"></textarea>
                        <template x-if="errors.description">
                            <p class="mt-1 text-sm text-red-500" x-text="errors.description[0]"></p>
                        </template>
                    </div>

                    <div class="space-y-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <label class="flex items-center space-x-3 cursor-pointer">
                            <input type="checkbox" name="is_urgent" value="1"
                                   class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                            <div>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('app.prayer_urgent') }}</span>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('app.prayer_urgent_desc') }}</p>
                            </div>
                        </label>

                        <label class="flex items-center space-x-3 cursor-pointer">
                            <input type="checkbox" name="is_public" value="1" checked
                                   class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                            <div>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('app.prayer_public') }}</span>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('app.prayer_public_desc') }}</p>
                            </div>
                        </label>

                        <label class="flex items-center space-x-3 cursor-pointer">
                            <input type="checkbox" name="is_anonymous" value="1"
                                   class="w-4 h-4 text-gray-600 border-gray-300 rounded focus:ring-gray-500">
                            <div>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('app.prayer_anonymous') }}</span>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('app.prayer_anonymous_desc') }}</p>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 sm:gap-3 px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" onclick="closeCreatePrayerRequestModal()"
                            class="w-full sm:w-auto px-4 py-2 text-center text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                        {{ __('app.prayer_cancel') }}
                    </button>
                    <button type="submit" :disabled="saving"
                            class="w-full sm:w-auto px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50">
                        <span x-show="!saving">{{ __('app.prayer_submit') }}</span>
                        <span x-show="saving" class="flex items-center justify-center gap-2">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            {{ __('app.prayer_saving') }}
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openCreatePrayerRequestModal() {
    document.getElementById('createPrayerRequestModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closeCreatePrayerRequestModal() {
    document.getElementById('createPrayerRequestModal').classList.add('hidden');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !document.getElementById('createPrayerRequestModal').classList.contains('hidden')) {
        closeCreatePrayerRequestModal();
    }
});

function prayerRequestCreateForm() {
    return {
        saving: false,
        errors: {},
        i18n: {
            validationError: @json(__('app.prayer_validation_error')),
            saveError: @json(__('app.prayer_save_error')),
            added: @json(__('app.prayer_added')),
            connectionError: @json(__('app.prayer_connection_error')),
        },
        async submitForm() {
            this.saving = true;
            this.errors = {};
            const formData = new FormData(this.$refs.form);
            try {
                const response = await fetch('{{ route("prayer-requests.store") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: formData,
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    if (response.status === 422 && data.errors) { this.errors = data.errors; showToast('error', this.i18n.validationError); }
                    else { showToast('error', data.message || this.i18n.saveError); }
                    this.saving = false; return;
                }
                showToast('success', data.message || this.i18n.added);
                closeCreatePrayerRequestModal();
                setTimeout(() => Livewire.navigate(window.location.href), 600);
            } catch (e) { showToast('error', this.i18n.connectionError); this.saving = false; }
        }
    }
}
</script>
@endsection
