{{-- Event Slide-Over Panel --}}
<div x-data="eventSlideOver()"
     x-on:open-event.window="openEvent($event.detail.id)"
     x-on:keydown.escape.window="close()"
     x-show="open" x-cloak
     class="fixed inset-0 z-[60]"
     style="display: none;">

    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"
         @click="close()"
         x-show="open"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
    </div>

    {{-- Panel --}}
    <div class="absolute right-0 top-0 bottom-0 w-full sm:w-[75%] md:w-[70%] lg:w-[60%] xl:w-[55%] bg-stone-100 dark:bg-gray-900 shadow-2xl flex flex-col"
         x-show="open"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full">

        {{-- Top bar with controls --}}
        <div class="flex items-center justify-between px-4 py-2.5 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shrink-0">
            <div class="flex items-center gap-2">
                <a :href="'/events/' + eventId"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                   title="{{ __('app.open_full_page') }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    <span class="hidden sm:inline">{{ __('app.open_full_page') }}</span>
                </a>
            </div>
            <button @click="close()"
                    class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Loading indicator --}}
        <div x-show="loading" class="flex-1 flex items-center justify-center">
            <div class="text-center">
                <svg class="animate-spin h-8 w-8 text-primary-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('app.loading') }}</p>
            </div>
        </div>

        {{-- Content iframe --}}
        <iframe x-ref="iframe"
                x-show="!loading"
                class="flex-1 w-full border-0"
                style="display: none;"
                sandbox="allow-same-origin allow-scripts allow-forms allow-popups allow-modals">
        </iframe>
    </div>
</div>

<script>
function eventSlideOver() {
    return {
        open: false,
        loading: false,
        eventId: null,

        openEvent(id) {
            this.eventId = id;
            this.open = true;
            this.loading = true;
            document.body.style.overflow = 'hidden';

            const iframe = this.$refs.iframe;
            iframe.src = '/events/' + id + '?partial=1';

            iframe.onload = () => {
                this.loading = false;

                // Sync dark mode to iframe
                try {
                    const isDark = document.documentElement.classList.contains('dark') ||
                                   localStorage.getItem('theme') !== 'light';
                    const iframeHtml = iframe.contentDocument.documentElement;
                    if (isDark) {
                        iframeHtml.classList.add('dark');
                    } else {
                        iframeHtml.classList.remove('dark');
                    }
                } catch (e) {
                    // Cross-origin or sandbox restriction — ignore
                }
            };
        },

        close() {
            this.open = false;
            document.body.style.overflow = '';

            // Clear iframe after transition
            setTimeout(() => {
                if (this.$refs.iframe) {
                    this.$refs.iframe.src = 'about:blank';
                }
            }, 300);
        }
    };
}
</script>
