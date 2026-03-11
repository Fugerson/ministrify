{{-- Photo Cropper Modal --}}
{{-- Usage: include once on page, then call window.openPhotoCropper(file, callback) --}}
<div x-data="photoCropperModal()" x-show="open" x-cloak
     class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
     @photo-cropper-open.window="openCropper($event.detail)"
     @keydown.escape.window="cancel()">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/70" @click="cancel()"></div>

    <!-- Modal -->
    <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden" @click.stop>
        <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('app.crop_photo_title') }}</h3>
            <button type="button" @click="cancel()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="p-4">
            <div class="w-full" style="max-height: 60vh;">
                <img x-ref="cropImage" src="" style="max-width: 100%; display: block;">
            </div>
        </div>

        <div class="px-5 py-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-end gap-3">
            <button type="button" @click="cancel()"
                    class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                {{ __('app.back') }}
            </button>
            <button type="button" @click="confirm()"
                    class="px-5 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                {{ __('app.crop_confirm') }}
            </button>
        </div>
    </div>
</div>

<script>
function photoCropperModal() {
    return {
        open: false,
        cropper: null,
        callback: null,

        openCropper(detail) {
            this.callback = detail.callback;
            this.open = true;

            this.$nextTick(() => {
                const img = this.$refs.cropImage;

                // Destroy previous instance
                if (this.cropper) {
                    this.cropper.destroy();
                    this.cropper = null;
                }

                const initCropper = () => {
                    this.cropper = new Cropper(img, {
                        aspectRatio: 1,
                        viewMode: 1,
                        dragMode: 'move',
                        autoCropArea: 1,
                        cropBoxResizable: true,
                        cropBoxMovable: true,
                        background: false,
                        responsive: true,
                        guides: true,
                    });
                };

                // Set onload BEFORE src to avoid race condition with data URLs
                img.onload = () => initCropper();
                img.src = detail.imageUrl;

                // Fallback: if image already complete (cached/data URL loaded sync)
                if (img.complete && img.naturalWidth > 0) {
                    img.onload = null;
                    initCropper();
                }
            });
        },

        confirm() {
            if (!this.cropper) return;

            const canvas = this.cropper.getCroppedCanvas({
                width: 800,
                height: 800,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high',
            });

            canvas.toBlob((blob) => {
                if (this.callback) {
                    this.callback(blob);
                }
                this.close();
            }, 'image/jpeg', 0.92);
        },

        cancel() {
            this.close();
        },

        close() {
            if (this.cropper) {
                this.cropper.destroy();
                this.cropper = null;
            }
            this.open = false;
            this.callback = null;
        }
    };
}
</script>
