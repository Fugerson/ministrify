@extends('layouts.system-admin')

@section('title', 'Скріншоти')

@section('content')
<div x-data="screenshotManager()" class="space-y-6">
    {{-- Upload Zone --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <div
            @paste.window="handlePaste($event)"
            @dragover.prevent="dragOver = true"
            @dragleave="dragOver = false"
            @drop.prevent="handleDrop($event)"
            :class="dragOver ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' : 'border-gray-300 dark:border-gray-600'"
            class="border-2 border-dashed rounded-xl p-8 text-center transition-all cursor-pointer"
            @click="$refs.fileInput.click()"
        >
            <input type="file" x-ref="fileInput" class="hidden" accept="image/*" multiple @change="handleFiles($event.target.files)">

            <template x-if="!uploading">
                <div>
                    <svg class="w-10 h-10 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Ctrl+V, перетягніть або клікніть для завантаження</p>
                </div>
            </template>
            <template x-if="uploading">
                <div class="flex items-center justify-center gap-2 text-indigo-600">
                    <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span class="text-sm">Завантаження...</span>
                </div>
            </template>
        </div>
    </div>

    {{-- Screenshots Grid --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h3 class="font-semibold text-gray-900 dark:text-white">Всього: {{ count($screenshots) }}</h3>
        </div>

        @if(count($screenshots) === 0)
            <div class="p-12 text-center text-gray-500 dark:text-gray-400">
                <p>Скріншотів немає</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 p-4">
                @foreach($screenshots as $scr)
                <div class="group relative bg-gray-50 dark:bg-gray-900 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
                    <a href="{{ $scr['url'] }}" target="_blank">
                        <img src="{{ $scr['url'] }}" alt="{{ $scr['name'] }}" class="w-full h-48 object-cover hover:opacity-90 transition-opacity" loading="lazy">
                    </a>
                    <div class="p-2">
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $scr['name'] }}</p>
                        <div class="flex items-center justify-between mt-1">
                            <span class="text-xs text-gray-400">{{ $scr['date'] }} &middot; {{ round($scr['size'] / 1024) }} KB</span>
                            <div class="flex items-center gap-1">
                                <button
                                    @click="copyPath('{{ $scr['url'] }}')"
                                    class="p-1 text-gray-400 hover:text-indigo-600 transition-colors" title="Копіювати URL">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                                    </svg>
                                </button>
                                <form method="POST" action="{{ route('system.screenshots.destroy', $scr['name']) }}" onsubmit="return confirm('Видалити?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 text-gray-400 hover:text-red-600 transition-colors" title="Видалити">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function screenshotManager() {
    return {
        uploading: false,
        dragOver: false,

        async handlePaste(e) {
            const items = e.clipboardData?.items;
            if (!items) return;
            for (const item of items) {
                if (item.type.startsWith('image/')) {
                    await this.upload(item.getAsFile());
                }
            }
        },

        async handleDrop(e) {
            this.dragOver = false;
            const files = e.dataTransfer?.files;
            if (files) await this.handleFiles(files);
        },

        async handleFiles(files) {
            for (const file of files) {
                if (file.type.startsWith('image/')) {
                    await this.upload(file);
                }
            }
        },

        async upload(file) {
            this.uploading = true;
            const formData = new FormData();
            formData.append('screenshot', file);

            try {
                const res = await fetch('/api/screenshot-upload', { method: 'POST', body: formData });
                const data = await res.json();
                if (data.url) {
                    window.location.reload();
                }
            } catch (err) {
                alert('Помилка: ' + err.message);
            } finally {
                this.uploading = false;
            }
        },

        copyPath(url) {
            navigator.clipboard.writeText(url).then(() => {
                window.showToast?.('URL скопійовано', 'success');
            });
        }
    };
}
</script>
@endpush
@endsection
