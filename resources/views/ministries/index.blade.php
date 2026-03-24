@extends('layouts.app')

@section('title', __('app.ministries'))

@section('actions')
@if(auth()->user()->canCreate('ministries'))
<button type="button" onclick="openCreateMinistryModal()"
   class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    {{ __('app.add') }}
</button>
@endif
@endsection

@section('content')
<div class="space-y-6">
    <div id="ministries-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
    @forelse($ministries as $ministry)
        @php
            $canAccess = $ministry->canAccess();
            $visibility = $ministry->visibility ?? 'public';
            $isLocked = $visibility !== 'public' && !$canAccess;
        @endphp
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden border border-gray-200 dark:border-gray-700 flex flex-col {{ $isLocked ? 'opacity-60' : '' }}">
            <div class="p-4 md:p-6 flex-1">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-semibold {{ $isLocked ? 'text-gray-500 dark:text-gray-500' : 'text-gray-900 dark:text-white' }}">{{ $ministry->name }}</h3>
                            @if($visibility !== 'public')
                                <svg class="w-4 h-4 {{ $canAccess ? 'text-amber-500' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" title="{{ $visibility === 'members' ? __('app.members_only') : __('app.leaders_only') }}">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            @endif
                        </div>
                        @if($ministry->leader)
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.leader') }}: {{ $ministry->leader->full_name }}</p>
                        @endif
                    </div>
                    @if($ministry->color)
                        <div class="w-3 h-3 rounded-full {{ $isLocked ? 'opacity-50' : '' }}" style="background-color: {{ $ministry->color }}"></div>
                    @endif
                </div>

                <div class="mt-4">
                    <p class="text-sm {{ $isLocked ? 'text-gray-400 dark:text-gray-500' : 'text-gray-600 dark:text-gray-400' }}">
                        {{ __('app.members') }}: {{ $ministry->members->count() }}
                    </p>
                </div>
            </div>

            <div class="px-4 md:px-6 py-3 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700">
                @if($isLocked)
                    <span class="text-gray-400 dark:text-gray-500 text-sm font-medium flex items-center cursor-not-allowed">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        {{ __('app.access_denied') }}
                    </span>
                @else
                    <a href="{{ route('ministries.show', $ministry) }}"
                       class="text-primary-600 dark:text-primary-400 hover:text-primary-500 text-sm font-medium flex items-center">
                        {{ __('app.open_action') }}
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                @endif
            </div>
        </div>
    @empty
        <div class="col-span-full text-center py-8 md:py-12">
            <p class="text-gray-500 dark:text-gray-400">{{ __('app.no_ministries_yet') }}</p>
            @if(auth()->user()->canCreate('ministries'))
            <button type="button" onclick="openCreateMinistryModal()" class="mt-2 inline-block text-primary-600 dark:text-primary-400 hover:text-primary-500">
                {{ __('app.create_first_ministry') }}
            </button>
            @endif
        </div>
    @endforelse
    </div>
</div>

{{-- Create Ministry Modal --}}
@if(auth()->user()->canCreate('ministries'))
<div id="createMinistryModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-start justify-center min-h-screen pt-4 px-4 pb-20 sm:p-0">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" onclick="closeCreateMinistryModal()"></div>
        <div class="relative w-full max-w-lg mx-auto mt-8 sm:mt-16 bg-white dark:bg-gray-800 rounded-2xl shadow-xl z-10" x-data="ministryCreateForm()">
            <form x-ref="form" @submit.prevent="submitForm">
                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('app.add_ministry') }}</h2>
                    <button type="button" onclick="closeCreateMinistryModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Body --}}
                <div class="px-6 py-4 space-y-4 max-h-[70vh] overflow-y-auto">
                    <div>
                        <label for="modal_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.name_required') }}</label>
                        <input type="text" name="name" id="modal_name" required
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                               placeholder="{{ __('app.ministry_name_placeholder') }}">
                        <template x-if="errors.name">
                            <p class="mt-1 text-sm text-red-500" x-text="errors.name[0]"></p>
                        </template>
                    </div>

                    <div>
                        <label for="modal_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.description') }}</label>
                        <textarea name="description" id="modal_description" rows="2"
                                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.leader') }}</label>
                        <x-person-select name="leader_id" :people="$people" :placeholder="__('app.search_leader')" />
                    </div>

                    <div>
                        <label for="modal_color" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.color') }}</label>
                        <input type="color" name="color" id="modal_color" value="#3b82f6"
                               class="w-16 h-10 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer">
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 sm:gap-3 px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" onclick="closeCreateMinistryModal()"
                            class="w-full sm:w-auto px-4 py-2 text-center text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                        {{ __('app.cancel') }}
                    </button>
                    <button type="submit" :disabled="saving"
                            class="w-full sm:w-auto px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50">
                        <span x-show="!saving">{{ __('app.create') }}</span>
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

<script>
function openCreateMinistryModal() {
    document.getElementById('createMinistryModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    setTimeout(() => {
        const nameInput = document.getElementById('modal_name');
        if (nameInput) nameInput.focus();
    }, 100);
}

function closeCreateMinistryModal() {
    document.getElementById('createMinistryModal').classList.add('hidden');
    document.body.style.overflow = '';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !document.getElementById('createMinistryModal').classList.contains('hidden')) {
        closeCreateMinistryModal();
    }
}, { signal: pageSignal() });

function ministryCreateForm() {
    return {
        saving: false,
        errors: {},
        async submitForm() {
            this.saving = true;
            this.errors = {};
            const formData = new FormData(this.$refs.form);
            try {
                const response = await fetch('{{ route("ministries.store") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: formData,
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    if (response.status === 422 && data.errors) {
                        this.errors = data.errors;
                        showToast('error', @js(__('app.form_check_error')));
                    } else {
                        showToast('error', data.message || @js(__('app.save_error')));
                    }
                    this.saving = false;
                    return;
                }
                showToast('success', data.message || @js(__('app.created')));
                closeCreateMinistryModal();
                if (data.redirect_url) {
                    setTimeout(() => window.location.href = data.redirect_url, 400);
                } else {
                    setTimeout(() => window.location.reload(), 400);
                }
            } catch (e) {
                showToast('error', @js(__('app.server_error')));
                this.saving = false;
            }
        }
    }
}
</script>
@endif

<x-realtime-banner channel="ministries" />
@endsection
