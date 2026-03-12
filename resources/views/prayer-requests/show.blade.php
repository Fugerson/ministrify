@extends('layouts.app')

@section('title', $prayerRequest->title)

@section('content')
<div class="max-w-3xl mx-auto">
    <a href="{{ route('prayer-requests.index') }}" class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-sm mb-6">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        {{ __('app.prayer_back_to_list') }}
    </a>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-start justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        @if($prayerRequest->is_urgent)
                            <span class="px-2 py-1 bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 text-xs font-medium rounded-full">
                                {{ __('app.prayer_badge_urgent') }}
                            </span>
                        @endif
                        @if(!$prayerRequest->is_public)
                            <span class="px-2 py-1 bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 text-xs font-medium rounded-full">
                                {{ __('app.prayer_badge_private') }}
                            </span>
                        @endif
                        <span id="prayer-status-badge" class="px-2 py-1 bg-{{ $prayerRequest->status_color }}-100 text-{{ $prayerRequest->status_color }}-700 dark:bg-{{ $prayerRequest->status_color }}-900/30 dark:text-{{ $prayerRequest->status_color }}-400 text-xs font-medium rounded-full">
                            {{ $prayerRequest->status_label }}
                        </span>
                    </div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $prayerRequest->title }}</h1>
                </div>

                @if($prayerRequest->user_id === auth()->id() || auth()->user()->hasRole(['admin', 'leader']))
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('prayer-requests.edit', $prayerRequest) }}"
                           class="px-3 py-1 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-sm">
                            {{ __('app.prayer_edit') }}
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Content -->
        <div class="p-6">
            <div class="prose dark:prose-invert max-w-none">
                <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $prayerRequest->description }}</p>
            </div>

            <div class="mt-6 flex items-center text-sm text-gray-500 dark:text-gray-400">
                <div class="w-8 h-8 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center mr-3">
                    <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                </div>
                <div>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $prayerRequest->author_name }}</p>
                    <p>{{ $prayerRequest->created_at->format('d.m.Y H:i') }}</p>
                </div>
            </div>
        </div>

        <!-- Answer Testimony -->
        @if($prayerRequest->status === 'answered' && $prayerRequest->answer_testimony)
            <div class="px-6 py-4 bg-green-50 dark:bg-green-900/20 border-t border-green-200 dark:border-green-800">
                <h3 class="font-semibold text-green-800 dark:text-green-300 mb-2 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ __('app.prayer_answer_testimony') }}
                </h3>
                <p class="text-green-700 dark:text-green-400 whitespace-pre-wrap">{{ $prayerRequest->answer_testimony }}</p>
                <p class="text-sm text-green-600 dark:text-green-500 mt-2">{{ $prayerRequest->answered_at?->format('d.m.Y') }}</p>
            </div>
        @endif

        <!-- Actions -->
        <div class="px-4 sm:px-6 py-3 sm:py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                                        <span>{{ trans_choice(__('app.prayer_people_praying'), $prayerRequest->prayer_count, ['count' => $prayerRequest->prayer_count]) }}</span>
                </div>

                <div id="prayer-actions" class="flex items-center space-x-3" x-data="{ prayed: {{ $hasPrayed ? 'true' : 'false' }}, prayerCount: {{ $prayerRequest->prayer_count }} }">
                    @if($prayerRequest->status === 'active')
                        <button type="button"
                                @click="if(!prayed) { ajaxAction('{{ route('prayer-requests.pray', $prayerRequest) }}', 'POST').then(() => { prayed = true; prayerCount++; }).catch(() => {}) }"
                                :disabled="prayed"
                                class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-medium text-sm transition-colors"
                                :class="{ 'opacity-50 cursor-not-allowed': prayed }">
                            <span x-text="prayed ? @json(__('app.prayer_already_prayed')) : @json(__('app.prayer_pray_for_this'))"></span>
                        </button>

                        @if($prayerRequest->user_id === auth()->id() || auth()->user()->hasRole(['admin', 'leader']))
                            <button type="button"
                                    onclick="document.getElementById('answerModal').classList.remove('hidden')"
                                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium text-sm transition-colors">
                                {{ __('app.prayer_answer_received') }}
                            </button>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Answer Modal -->
<div id="answerModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50"
     x-data="{ ...ajaxForm({ url: '{{ route('prayer-requests.mark-answered', $prayerRequest) }}', method: 'POST', onSuccess() { _markPrayerAnswered(this); } }) }">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-lg w-full mx-4">
        <form @submit.prevent="submit($refs.answerForm)" x-ref="answerForm">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('app.prayer_praise_god') }}</h3>
            </div>
            <div class="p-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('app.prayer_share_testimony') }}
                </label>
                <textarea name="answer_testimony" rows="4"
                          class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                          placeholder="{{ __('app.prayer_testimony_placeholder') }}"></textarea>
            </div>
            <div class="px-4 sm:px-6 py-3 sm:py-4 border-t border-gray-200 dark:border-gray-700 flex flex-col-reverse sm:flex-row sm:justify-end gap-2 sm:gap-3">
                <button type="button" onclick="document.getElementById('answerModal').classList.add('hidden')"
                        class="w-full sm:w-auto px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                    {{ __('app.prayer_cancel') }}
                </button>
                <button type="submit" :disabled="saving"
                        class="w-full sm:w-auto px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                    {{ __('app.prayer_confirm') }}
                </button>
            </div>
        </form>
    </div>
</div>
<script>
const _prayerI18n = {
    answerReceived: @json(__('app.prayer_answer_received')),
    answerTestimony: @json(__('app.prayer_answer_testimony')),
};
function _markPrayerAnswered(ctx) {
    document.getElementById('answerModal').classList.add('hidden');
    var badge = document.getElementById('prayer-status-badge');
    if (badge) {
        badge.className = 'px-2 py-1 bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 text-xs font-medium rounded-full';
        badge.textContent = _prayerI18n.answerReceived;
    }
    var actions = document.getElementById('prayer-actions');
    if (actions) actions.innerHTML = '';
    var testimony = ctx.$refs.answerForm ? ctx.$refs.answerForm.querySelector('textarea') : null;
    if (testimony && testimony.value.trim()) {
        var section = document.createElement('div');
        section.className = 'px-6 py-4 bg-green-50 dark:bg-green-900/20 border-t border-green-200 dark:border-green-800';
        var now = new Date();
        var dd = String(now.getDate()).padStart(2, '0') + '.' + String(now.getMonth() + 1).padStart(2, '0') + '.' + now.getFullYear();
        var safe = testimony.value.replace(/&/g, '\x26amp;').replace(/</g, '\x26lt;').replace(/>/g, '\x26gt;');
        section.innerHTML = '\x3Ch3 class="font-semibold text-green-800 dark:text-green-300 mb-2 flex items-center">\x3Csvg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">\x3Cpath stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>\x3C/svg>' + _prayerI18n.answerTestimony + '\x3C/h3>\x3Cp class="text-green-700 dark:text-green-400 whitespace-pre-wrap">' + safe + '\x3C/p>\x3Cp class="text-sm text-green-600 dark:text-green-500 mt-2">' + dd + '\x3C/p>';
        var content = document.querySelector('.bg-white.dark\\:bg-gray-800.rounded-xl');
        if (content) content.appendChild(section);
    }
}
</script>
@endsection
