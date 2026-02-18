<div x-data="{ open: false, src: '' }"
     @open-lightbox.window="open = true; src = $event.detail"
     @keydown.escape.window="if (open) { open = false }"
     x-show="open"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/90 p-4"
     style="display: none;"
     @click.self="open = false">

    <button @click="open = false"
            class="absolute top-4 right-4 z-10 p-2 text-white/70 hover:text-white transition-colors">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>

    <img :src="src" class="max-w-full max-h-full object-contain rounded-lg shadow-2xl" @click.stop>
</div>
