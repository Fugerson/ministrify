<!-- Toast Notifications Container -->
<div x-data="toastNotifications()"
     x-on:toast.window="add($event.detail)"
     class="fixed bottom-4 right-4 z-50 flex flex-col gap-3 pointer-events-none">
    <template x-for="toast in toasts" :key="toast.id">
        <div x-show="toast.visible"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-2 scale-95"
             class="pointer-events-auto flex items-start gap-3 p-4 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 max-w-sm"
             :class="{
                 'border-l-4 border-l-green-500': toast.type === 'success',
                 'border-l-4 border-l-red-500': toast.type === 'error',
                 'border-l-4 border-l-yellow-500': toast.type === 'warning',
                 'border-l-4 border-l-blue-500': toast.type === 'info'
             }">
            <!-- Icon -->
            <div class="flex-shrink-0">
                <template x-if="toast.type === 'success'">
                    <div class="w-8 h-8 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </template>
                <template x-if="toast.type === 'error'">
                    <div class="w-8 h-8 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                </template>
                <template x-if="toast.type === 'warning'">
                    <div class="w-8 h-8 rounded-full bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center">
                        <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                </template>
                <template x-if="toast.type === 'info'">
                    <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </template>
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="toast.title"></p>
                <p x-show="toast.message" class="text-sm text-gray-500 dark:text-gray-400 mt-0.5" x-text="toast.message"></p>
            </div>

            <!-- Close button -->
            <button @click="remove(toast.id)" class="flex-shrink-0 p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </template>
</div>

<script>
function toastNotifications() {
    return {
        toasts: [],

        add(toast) {
            const id = Date.now();
            this.toasts.push({
                id,
                type: toast.type || 'info',
                title: toast.title || '',
                message: toast.message || '',
                visible: true
            });

            setTimeout(() => this.remove(id), toast.duration || 5000);
        },

        remove(id) {
            const index = this.toasts.findIndex(t => t.id === id);
            if (index > -1) {
                this.toasts[index].visible = false;
                setTimeout(() => {
                    this.toasts = this.toasts.filter(t => t.id !== id);
                }, 300);
            }
        }
    }
}

// Helper function to show toast
function showToast(type, title, message = '', duration = 5000) {
    window.dispatchEvent(new CustomEvent('toast', {
        detail: { type, title, message, duration }
    }));
}
</script>
<?php /**PATH /var/www/html/resources/views/components/toast.blade.php ENDPATH**/ ?>