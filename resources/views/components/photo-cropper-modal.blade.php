{{-- Photo Cropper Modal --}}
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
            <button type="button" @click="confirm()" :disabled="!ready || confirming"
                    class="px-5 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50">
                <span x-show="!confirming">{{ __('app.crop_confirm') }}</span>
                <span x-show="confirming" class="flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    ...
                </span>
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
        originalFile: null,
        ready: false,
        confirming: false,

        openCropper(detail) {
            this.callback = detail.callback;
            this.originalFile = detail.originalFile || null;
            this.ready = false;
            this.confirming = false;
            this.open = true;

            this.$nextTick(() => {
                const img = this.$refs.cropImage;
                if (!img) return;

                if (this.cropper) {
                    this.cropper.destroy();
                    this.cropper = null;
                }

                const self = this;
                const tempImg = new Image();
                tempImg.onload = function() {
                    img.src = tempImg.src;
                    self.cropper = new Cropper(img, {
                        aspectRatio: 1,
                        viewMode: 1,
                        dragMode: 'move',
                        autoCropArea: 1,
                        cropBoxResizable: true,
                        cropBoxMovable: true,
                        background: false,
                        responsive: true,
                        guides: true,
                        checkCrossOrigin: false,
                        checkOrientation: false,
                        ready: function() {
                            self.ready = true;
                        }
                    });
                };
                tempImg.src = detail.imageUrl;
            });
        },

        confirm() {
            if (!this.cropper || !this.ready || this.confirming) return;
            this.confirming = true;

            try {
                const canvas = this.cropper.getCroppedCanvas({
                    width: 800,
                    height: 800,
                });

                if (!canvas) {
                    showGlobalToast('Canvas error — null', 'error');
                    this.confirming = false;
                    return;
                }

                // Synchronous approach — no async toBlob
                const dataUrl = canvas.toDataURL('image/jpeg', 0.92);
                const byteString = atob(dataUrl.split(',')[1]);
                const ab = new ArrayBuffer(byteString.length);
                const ia = new Uint8Array(ab);
                for (let i = 0; i < byteString.length; i++) {
                    ia[i] = byteString.charCodeAt(i);
                }
                const blob = new Blob([ab], { type: 'image/jpeg' });

                if (this.callback) {
                    this.callback(blob, this.originalFile);
                }
                this.close();
            } catch (e) {
                console.error('Crop error:', e);
                showGlobalToast('Crop error: ' + e.message, 'error');
                this.confirming = false;
            }
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
            this.originalFile = null;
            this.ready = false;
            this.confirming = false;
        }
    };
}
</script>
