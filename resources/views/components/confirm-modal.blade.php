<!-- Global Confirm Modal -->
<div x-data="confirmModal()" x-on:confirm-dialog.window="open($event.detail)" x-show="visible" x-cloak
     class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/50 dark:bg-black/70" @click="cancel()"></div>
    <!-- Modal -->
    <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-sm w-full p-6 border border-gray-200 dark:border-gray-700"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-4"
         @keydown.escape.window="cancel()">
        <!-- Icon -->
        <div class="flex justify-center mb-4">
            <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
        </div>
        <!-- Text -->
        <p class="text-center text-sm text-gray-700 dark:text-gray-300 mb-6" x-text="message"></p>
        <!-- Buttons -->
        <div class="flex gap-3">
            <button @click="cancel()"
                    class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                {{ __('common.cancel') }}
            </button>
            <button @click="accept()"
                    class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors">
                {{ __('common.confirm') }}
            </button>
        </div>
    </div>
</div>

<script>
function confirmModal() {
    return {
        visible: false,
        message: '',
        _resolve: null,

        open(detail) {
            this.message = detail.message || '';
            this.visible = true;
            this._resolve = detail.resolve;
        },

        accept() {
            this.visible = false;
            if (this._resolve) this._resolve(true);
            this._resolve = null;
        },

        cancel() {
            this.visible = false;
            if (this._resolve) this._resolve(false);
            this._resolve = null;
        }
    };
}

/**
 * Drop-in replacement for confirm() — returns a Promise<boolean>
 * Usage: if (await confirmDialog('Are you sure?')) { ... }
 */
window.confirmDialog = function(message) {
    return new Promise(function(resolve) {
        window.dispatchEvent(new CustomEvent('confirm-dialog', {
            detail: { message: message, resolve: resolve }
        }));
    });
};
</script>
