@props([
    'action',
    'title' => 'Видалити',
    'message' => 'Ви впевнені, що хочете видалити цей запис? Цю дію неможливо скасувати.',
    'buttonClass' => 'text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300',
    'buttonText' => 'Видалити',
    'icon' => true,
    'method' => 'DELETE',
    'ajax' => false,
    'redirect' => null,
])

<div x-data="{
    open: false,
    loading: false,
    async submitDelete() {
        this.loading = true;
        try {
            const response = await fetch('{{ $action }}', {
                method: '{{ $method }}',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await response.json();
            if (response.ok && data.success) {
                this.open = false;
                if (typeof showToast === 'function') {
                    showToast('success', data.message || 'Видалено!');
                }
                @if($redirect)
                    setTimeout(() => window.location.href = '{{ $redirect }}', 500);
                @else
                    setTimeout(() => window.location.href = '{{ url()->previous() }}', 500);
                @endif
            } else {
                if (typeof showToast === 'function') {
                    showToast('error', data.message || 'Помилка видалення');
                }
            }
        } catch (e) {
            if (typeof showToast === 'function') {
                showToast('error', 'Помилка з\'єднання');
            }
        } finally {
            this.loading = false;
        }
    }
}" class="inline">
    {{-- Trigger Button --}}
    <button type="button" @click="open = true" {{ $attributes->merge(['class' => $buttonClass]) }}>
        @if($slot->isNotEmpty())
            {{ $slot }}
        @else
            @if($icon)
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            @endif
            {{ $buttonText }}
        @endif
    </button>

    {{-- Modal --}}
    <template x-teleport="body">
        <div x-show="open"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto"
             role="dialog"
             aria-modal="true"
             aria-labelledby="delete-dialog-title"
             @keydown.escape.window="open = false"
             style="display: none;">

            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-black/50 dark:bg-black/70" @click="open = false" aria-hidden="true"></div>

            {{-- Modal Content --}}
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="open"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     @click.stop
                     class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6"
                     x-trap.inert.noscroll="open">

                    {{-- Warning Icon --}}
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                        <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>

                    {{-- Title --}}
                    <h3 id="delete-dialog-title" class="mt-4 text-lg font-semibold text-gray-900 dark:text-white text-center">
                        {{ $title }}
                    </h3>

                    {{-- Message --}}
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 text-center">
                        {{ $message }}
                    </p>

                    {{-- Actions --}}
                    <div class="mt-6 flex justify-center space-x-3">
                        <button type="button" @click="open = false"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                            Скасувати
                        </button>
                        @if($ajax)
                            <button type="button" @click="submitDelete()" :disabled="loading"
                                    class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors disabled:opacity-50">
                                <span x-show="!loading">{{ $buttonText }}</span>
                                <span x-show="loading">Видалення...</span>
                            </button>
                        @else
                            <form method="POST" action="{{ $action }}" class="inline">
                                @csrf
                                @method($method)
                                <button type="submit"
                                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors">
                                    {{ $buttonText }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
