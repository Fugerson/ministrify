@extends('layouts.app')

@section('title', __('app.faq'))

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{ showModal: false, editingFaq: null }">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('website-builder.index') }}" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('app.wb_faq_title') }}</h1>
                <p class="text-gray-600 dark:text-gray-400">{{ __('app.wb_faq_subtitle') }}</p>
            </div>
        </div>
        @if(auth()->user()->canEdit('website'))
        <button @click="showModal = true; editingFaq = null" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            {{ __('app.wb_add_faq') }}
        </button>
        @endif
    </div>


    @if($faqs->isEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
            <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('app.wb_no_faq_yet') }}</h3>
            <p class="text-gray-500 dark:text-gray-400 mt-1">{{ __('app.wb_add_faq_answers') }}</p>
        </div>
    @else
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <ul id="faq-list" class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($faqs as $faq)
                    <li class="faq-item" data-id="{{ $faq->id }}">
                        <div class="flex items-start gap-4 px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <div class="cursor-grab text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 drag-handle mt-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-900 dark:text-white">{{ $faq->question }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">{{ $faq->answer }}</p>
                                @if($faq->category)
                                    <span class="inline-block mt-2 px-2 py-0.5 text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded">{{ $faq->category }}</span>
                                @endif
                            </div>
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $faq->is_public ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                                {{ $faq->is_public ? __('app.wb_public') : __('app.wb_hidden') }}
                            </span>
                            @if(auth()->user()->canEdit('website'))
                            <div class="flex gap-1">
                                <button type="button" @click="editingFaq = @json($faq); showModal = true" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button type="button" @click="ajaxDelete('{{ route('website-builder.faq.destroy', $faq) }}', @js( __('messages.confirm_delete_question') ), () => $el.closest('.faq-item').remove())" class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @keydown.escape.window="showModal = false">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-black/50" @click="showModal = false"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-lg w-full p-6"
                 x-data="{ saving: false, errors: {},
                     async submitFaq(formEl) {
                         if (this.saving) return;
                         this.saving = true; this.errors = {};
                         const url = editingFaq ? '{{ url('website-builder/faq') }}/' + editingFaq.id : '{{ route('website-builder.faq.store') }}';
                         const formData = new FormData(formEl);
                         if (editingFaq) formData.append('_method', 'PUT');
                         try {
                             const resp = await fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' }, body: formData });
                             const data = await resp.json().catch(() => ({}));
                             if (!resp.ok) { if (resp.status === 422 && data.errors) this.errors = data.errors; showToast('error', data.message || @js(__('app.wb_error'))); this.saving = false; return; }
                             showToast('success', data.message || @js(__('app.wb_saved')));
                             setTimeout(() => Livewire.navigate(window.location.href), 600);
                         } catch(e) { showToast('error', @js(__('app.wb_connection_error'))); this.saving = false; }
                     }
                 }">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4" x-text="editingFaq ? @js(__('app.wb_edit_faq')) : @js(__('app.wb_add_faq'))"></h3>
                <form @submit.prevent="submitFaq($refs.faqForm)" x-ref="faqForm">

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.wb_question') }} *</label>
                            <input type="text" name="question" :value="editingFaq?.question || ''" required
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.wb_answer') }} *</label>
                            <textarea name="answer" rows="4" required
                                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" x-text="editingFaq?.answer || ''"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.category') }}</label>
                            <input type="text" name="category" :value="editingFaq?.category || ''"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>

                        <label class="flex items-center">
                            <input type="checkbox" name="is_public" value="1" :checked="editingFaq?.is_public ?? true"
                                   class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ __('app.wb_show_on_site') }}</span>
                        </label>
                    </div>

                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" @click="showModal = false" class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition-colors">
                            {{ __('app.cancel') }}
                        </button>
                        <button type="submit" :disabled="saving" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50">
                            <span x-show="!saving">{{ __('app.save') }}</span>
                            <span x-show="saving" x-cloak class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                {{ __('app.saving') }}
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    onPageReady(function() {
        const list = document.getElementById('faq-list');
        if (list) {
            new Sortable(list, {
                animation: 150,
                handle: '.drag-handle',
                onEnd: async function() {
                    const items = document.querySelectorAll('.faq-item');
                    const order = Array.from(items).map(item => parseInt(item.dataset.id));

                    await fetch('{{ route("website-builder.faq.reorder") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ order })
                    });
                }
            });
        }
    });
</script>
@endsection
