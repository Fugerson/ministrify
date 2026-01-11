@extends('layouts.app')

@section('title', 'QR-код для пожертв')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <a href="{{ route('donations.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white flex items-center gap-1">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Назад до пожертв
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8">
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">QR-код для пожертв</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">Роздрукуйте або покажіть на екрані</p>
        </div>

        <!-- QR Code Display -->
        <div class="flex justify-center mb-8">
            <div class="bg-white p-6 rounded-2xl shadow-lg border-4 border-primary-100 dark:border-primary-900">
                <div id="qrcode" class="w-64 h-64"></div>
            </div>
        </div>

        <!-- URL Display -->
        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4 mb-6">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Посилання на сторінку пожертв</label>
            <div class="flex gap-2">
                <input type="text" readonly value="{{ $donateUrl }}" id="donateUrl"
                    class="flex-1 px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-white">
                <button type="button" onclick="copyUrl()"
                    class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-sm font-medium transition-colors">
                    Копіювати
                </button>
            </div>
        </div>

        <!-- Print Options -->
        <div class="grid sm:grid-cols-2 gap-4 mb-8">
            <button onclick="printQR()" class="btn-secondary flex items-center justify-center gap-2 py-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Друкувати QR-код
            </button>
            <button onclick="downloadQR()" class="btn-secondary flex items-center justify-center gap-2 py-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Завантажити PNG
            </button>
        </div>

        <!-- Tips -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 rounded-xl p-6">
            <h3 class="font-semibold text-blue-900 dark:text-blue-300 mb-3 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Як використовувати
            </h3>
            <ul class="space-y-2 text-sm text-blue-800 dark:text-blue-300">
                <li class="flex items-start gap-2">
                    <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Роздрукуйте QR-код та розмістіть у церкві
                </li>
                <li class="flex items-start gap-2">
                    <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Покажіть на екрані під час богослужіння
                </li>
                <li class="flex items-start gap-2">
                    <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Додайте до буклетів або листівок
                </li>
                <li class="flex items-start gap-2">
                    <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Поділіться посиланням у соціальних мережах
                </li>
            </ul>
        </div>
    </div>

    <!-- Print template (hidden) -->
    <div id="printTemplate" class="hidden">
        <div style="text-align: center; padding: 40px; font-family: Arial, sans-serif;">
            <h1 style="font-size: 24px; margin-bottom: 10px;">{{ $church->name }}</h1>
            <p style="font-size: 16px; color: #666; margin-bottom: 30px;">Підтримати церкву</p>
            <div id="qrPrint" style="display: inline-block; padding: 20px; border: 3px solid #e5e7eb; border-radius: 16px;"></div>
            <p style="margin-top: 20px; font-size: 12px; color: #999;">Скануйте QR-код камерою телефону</p>
            <p style="font-size: 10px; color: #ccc; margin-top: 10px;">{{ $donateUrl }}</p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
    // Generate QR code
    const qrcode = new QRCode(document.getElementById("qrcode"), {
        text: "{{ $donateUrl }}",
        width: 256,
        height: 256,
        colorDark: "#1f2937",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.H
    });

    function copyUrl() {
        const input = document.getElementById('donateUrl');
        input.select();
        document.execCommand('copy');

        const btn = event.target;
        const originalText = btn.textContent;
        btn.textContent = 'Скопійовано!';
        setTimeout(() => btn.textContent = originalText, 2000);
    }

    function downloadQR() {
        const canvas = document.querySelector('#qrcode canvas');
        if (canvas) {
            const link = document.createElement('a');
            link.download = 'donate-qr-{{ $church->slug }}.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
        }
    }

    function printQR() {
        const printContent = document.getElementById('printTemplate').innerHTML;
        const printWindow = window.open('', '', 'width=600,height=600');
        printWindow.document.write('<html><head><title>QR-код для пожертв</title></head><body>');
        printWindow.document.write(printContent);
        printWindow.document.write('</body></html>');

        // Generate QR in print window
        setTimeout(() => {
            const printQR = printWindow.document.getElementById('qrPrint');
            new QRCode(printQR, {
                text: "{{ $donateUrl }}",
                width: 200,
                height: 200,
                colorDark: "#1f2937",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });

            setTimeout(() => {
                printWindow.print();
                printWindow.close();
            }, 500);
        }, 100);
    }
</script>
@endsection
