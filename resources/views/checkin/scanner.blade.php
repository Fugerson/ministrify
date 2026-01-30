@extends('layouts.app')

@section('title', 'QR Сканер')

@section('content')
<div class="max-w-2xl mx-auto" x-data="qrScanner()" x-init="init()">
    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">QR Check-in</h1>
        <p class="text-gray-500 dark:text-gray-400">Скануйте QR-код для швидкої реєстрації</p>
    </div>

    {{-- Event selector --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Подія</label>
        <select x-model="selectedEventId"
                @change="selectEvent()"
                class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary-500 focus:border-primary-500">
            <option value="">Оберіть подію...</option>
            <template x-for="event in todayEvents" :key="event.id">
                <option :value="event.id" x-text="event.time + ' - ' + event.title"></option>
            </template>
        </select>

        <div x-show="selectedEvent" class="mt-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-medium text-gray-900 dark:text-white" x-text="selectedEvent?.title"></p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Зареєстровано: <span x-text="attendanceCount" class="font-semibold"></span>
                    </p>
                </div>
                <template x-if="selectedEvent?.qr_checkin_enabled">
                    <button @click="showQrCode = !showQrCode"
                            class="px-4 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition-colors">
                        <span x-text="showQrCode ? 'Сховати QR' : 'Показати QR'"></span>
                    </button>
                </template>
            </div>
        </div>
    </div>

    {{-- QR Code display --}}
    <div x-show="showQrCode && selectedEvent?.checkin_url" x-cloak
         class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6 text-center">
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Покажіть цей QR-код учасникам для самостійної реєстрації</p>
        <div class="inline-block p-4 bg-white rounded-xl">
            <div id="qr-code-display" class="w-48 h-48 sm:w-64 sm:h-64 mx-auto"></div>
        </div>
        <p class="mt-4 text-xs text-gray-400 break-all" x-text="selectedEvent?.checkin_url"></p>
    </div>

    {{-- Camera scanner --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h2 class="font-semibold text-gray-900 dark:text-white">Камера</h2>
            <button @click="toggleCamera()"
                    class="px-4 py-2 rounded-xl transition-colors"
                    :class="cameraActive ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'">
                <span x-text="cameraActive ? 'Вимкнути' : 'Увімкнути'"></span>
            </button>
        </div>

        <div class="relative">
            <video id="qr-video" class="w-full aspect-video bg-gray-900" x-show="cameraActive"></video>
            <div x-show="!cameraActive" class="w-full aspect-video bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                <div class="text-center text-gray-400">
                    <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <p>Натисніть "Увімкнути" для сканування</p>
                </div>
            </div>

            {{-- Scan overlay --}}
            <div x-show="cameraActive" class="absolute inset-0 pointer-events-none">
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="w-40 h-40 sm:w-48 sm:h-48 border-2 border-white/50 rounded-xl"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent check-ins --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="font-semibold text-gray-900 dark:text-white">Останні реєстрації</h2>
        </div>
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            <template x-for="checkin in recentCheckins" :key="checkin.id">
                <div class="p-4 flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="font-medium text-gray-900 dark:text-white" x-text="checkin.name"></p>
                        <p class="text-sm text-gray-500 dark:text-gray-400" x-text="checkin.time"></p>
                    </div>
                </div>
            </template>
            <template x-if="recentCheckins.length === 0">
                <div class="p-8 text-center text-gray-400">
                    Ще немає реєстрацій
                </div>
            </template>
        </div>
    </div>

    {{-- Success toast --}}
    <div x-show="showSuccess" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-4"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed bottom-6 left-1/2 -translate-x-1/2 bg-green-600 text-white px-6 py-4 rounded-xl shadow-lg flex items-center gap-3">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <span x-text="successMessage"></span>
    </div>
</div>

{{-- QR Scanner library --}}
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script src="https://unpkg.com/qrcode@1.5.1/build/qrcode.min.js"></script>

<script>
function qrScanner() {
    return {
        todayEvents: [],
        selectedEventId: '',
        selectedEvent: null,
        cameraActive: false,
        html5QrCode: null,
        showQrCode: false,
        recentCheckins: [],
        showSuccess: false,
        successMessage: '',
        attendanceCount: 0,

        async init() {
            await this.loadTodayEvents();
        },

        async loadTodayEvents() {
            try {
                const response = await fetch('/api/checkin/today-events', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();
                this.todayEvents = data.events;
            } catch (error) {
                console.error('Failed to load events:', error);
            }
        },

        selectEvent() {
            this.selectedEvent = this.todayEvents.find(e => e.id == this.selectedEventId);
            this.attendanceCount = this.selectedEvent?.attendance_count || 0;
            this.showQrCode = false;

            if (this.selectedEvent?.checkin_url) {
                this.$nextTick(() => this.generateQrCodeDisplay());
            }
        },

        generateQrCodeDisplay() {
            const container = document.getElementById('qr-code-display');
            if (!container || !this.selectedEvent?.checkin_url) return;

            container.innerHTML = '';
            new QRCode(container, {
                text: this.selectedEvent.checkin_url,
                width: 256,
                height: 256,
                colorDark: '#000000',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.M
            });
        },

        async toggleCamera() {
            if (this.cameraActive) {
                await this.stopCamera();
            } else {
                await this.startCamera();
            }
        },

        async startCamera() {
            try {
                this.html5QrCode = new Html5Qrcode("qr-video");

                await this.html5QrCode.start(
                    { facingMode: "environment" },
                    {
                        fps: 10,
                        qrbox: { width: 250, height: 250 }
                    },
                    (decodedText) => this.onQrCodeScanned(decodedText),
                    (errorMessage) => {} // Ignore scan errors
                );

                this.cameraActive = true;
            } catch (error) {
                console.error('Camera error:', error);
                alert('Не вдалося отримати доступ до камери. Перевірте дозволи.');
            }
        },

        async stopCamera() {
            if (this.html5QrCode) {
                await this.html5QrCode.stop();
                this.html5QrCode = null;
            }
            this.cameraActive = false;
        },

        async onQrCodeScanned(url) {
            // Extract token from URL
            const match = url.match(/\/checkin\/([a-f0-9]+)/);
            if (!match) return;

            const token = match[1];

            // Pause scanning briefly to avoid duplicates
            await this.stopCamera();

            try {
                const response = await fetch(`/api/checkin/${token}`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    }
                });

                const data = await response.json();

                if (data.success) {
                    this.showSuccessToast(data.already_checked_in
                        ? `${data.person?.name || 'Користувач'} вже зареєстрований`
                        : `${data.person?.name || 'Користувач'} зареєстровано!`
                    );

                    if (!data.already_checked_in) {
                        this.recentCheckins.unshift({
                            id: Date.now(),
                            name: data.person?.name || 'Невідомий',
                            time: data.checked_in_at
                        });
                        this.attendanceCount++;
                    }
                } else {
                    alert(data.message || 'Помилка реєстрації');
                }
            } catch (error) {
                console.error('Check-in error:', error);
                alert('Помилка з\'єднання');
            }

            // Resume scanning after a short delay
            setTimeout(() => this.startCamera(), 2000);
        },

        showSuccessToast(message) {
            this.successMessage = message;
            this.showSuccess = true;
            setTimeout(() => {
                this.showSuccess = false;
            }, 3000);
        }
    };
}
</script>
@endsection
