<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Check-in - {{ $event->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-primary-600 to-primary-800 flex items-center justify-center p-4"
      x-data="checkinPage()"
      x-init="init()">

    <div class="w-full max-w-md">
        {{-- Event card --}}
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
            {{-- Header --}}
            <div class="bg-primary-600 text-white p-6 text-center">
                <div class="w-16 h-16 mx-auto mb-4 bg-white/20 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold">{{ $event->title }}</h1>
                <p class="mt-2 opacity-90">
                    {{ $event->date->translatedFormat('d F Y') }}
                    @if($event->time)
                        о {{ $event->time->format('H:i') }}
                    @endif
                </p>
                @if($event->location)
                    <p class="mt-1 text-sm opacity-75">{{ $event->location }}</p>
                @endif
            </div>

            {{-- Content --}}
            <div class="p-6">
                @guest
                    {{-- Not logged in --}}
                    <div class="text-center">
                        <div class="w-20 h-20 mx-auto mb-4 bg-yellow-100 rounded-full flex items-center justify-center">
                            <svg class="w-10 h-10 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <h2 class="text-xl font-semibold text-gray-900 mb-2">Увійдіть для реєстрації</h2>
                        <p class="text-gray-500 mb-6">Щоб зареєструватися на подію, увійдіть у свій акаунт</p>
                        <a href="{{ route('login') }}?redirect={{ urlencode(request()->fullUrl()) }}"
                           class="inline-flex items-center justify-center w-full px-6 py-3 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition-colors">
                            Увійти
                        </a>
                    </div>
                @else
                    @if($person)
                        {{-- Logged in with profile --}}
                        <div x-show="!checkedIn && !loading" class="text-center">
                            <div class="w-20 h-20 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center overflow-hidden">
                                @if($person->avatar)
                                    <img src="{{ $person->avatar_url }}" alt="{{ $person->full_name }}" class="w-full h-full object-cover">
                                @else
                                    <span class="text-2xl font-bold text-gray-400">{{ substr($person->first_name, 0, 1) }}</span>
                                @endif
                            </div>
                            <h2 class="text-xl font-semibold text-gray-900 mb-1">{{ $person->full_name }}</h2>
                            <p class="text-gray-500 mb-6">Натисніть кнопку для реєстрації</p>
                            <button @click="doCheckin()"
                                    class="w-full px-6 py-4 bg-green-600 text-white text-lg font-semibold rounded-xl hover:bg-green-700 transition-all transform hover:scale-105 active:scale-95">
                                Зареєструватися
                            </button>
                        </div>

                        {{-- Loading --}}
                        <div x-show="loading" x-cloak class="text-center py-8">
                            <svg class="w-12 h-12 mx-auto mb-4 text-primary-600 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <p class="text-gray-500">Реєстрація...</p>
                        </div>

                        {{-- Success --}}
                        <div x-show="checkedIn" x-cloak class="text-center">
                            <div class="w-24 h-24 mx-auto mb-4 bg-green-100 rounded-full flex items-center justify-center">
                                <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-900 mb-2">Успішно!</h2>
                            <p class="text-gray-500 mb-2" x-text="successMessage"></p>
                            <p class="text-sm text-gray-400" x-text="'Час: ' + checkinTime"></p>
                        </div>

                        {{-- Error --}}
                        <div x-show="error" x-cloak class="text-center">
                            <div class="w-20 h-20 mx-auto mb-4 bg-red-100 rounded-full flex items-center justify-center">
                                <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </div>
                            <h2 class="text-xl font-semibold text-gray-900 mb-2">Помилка</h2>
                            <p class="text-gray-500" x-text="errorMessage"></p>
                        </div>
                    @else
                        {{-- Logged in but no profile --}}
                        <div class="text-center">
                            <div class="w-20 h-20 mx-auto mb-4 bg-yellow-100 rounded-full flex items-center justify-center">
                                <svg class="w-10 h-10 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <h2 class="text-xl font-semibold text-gray-900 mb-2">Профіль не знайдено</h2>
                            <p class="text-gray-500 mb-6">Зверніться до адміністратора церкви для створення профілю</p>
                        </div>
                    @endif
                @endguest
            </div>

            {{-- Footer --}}
            <div class="px-6 pb-6 text-center">
                <p class="text-xs text-gray-400">
                    Powered by <span class="font-semibold">Ministrify</span>
                </p>
            </div>
        </div>
    </div>

    <script>
    function checkinPage() {
        return {
            loading: false,
            checkedIn: false,
            error: false,
            successMessage: '',
            errorMessage: '',
            checkinTime: '',

            init() {
                // Check if already checked in from session
            },

            async doCheckin() {
                this.loading = true;
                this.error = false;

                try {
                    const response = await fetch('/api/checkin/{{ $event->checkin_token }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.checkedIn = true;
                        this.successMessage = data.already_checked_in
                            ? 'Ви вже зареєстровані на цю подію'
                            : 'Вас зареєстровано на подію';
                        this.checkinTime = data.checked_in_at || new Date().toLocaleTimeString('uk-UA', { hour: '2-digit', minute: '2-digit' });
                    } else {
                        this.error = true;
                        this.errorMessage = data.message || 'Не вдалося зареєструватися';
                    }
                } catch (err) {
                    this.error = true;
                    this.errorMessage = 'Помилка з\'єднання. Спробуйте ще раз.';
                }

                this.loading = false;
            }
        };
    }
    </script>

    <style>
        .bg-primary-600 { background-color: #3b82f6; }
        .bg-primary-700 { background-color: #2563eb; }
        .bg-primary-800 { background-color: #1d4ed8; }
        .text-primary-600 { color: #3b82f6; }
        .from-primary-600 { --tw-gradient-from: #3b82f6; }
        .to-primary-800 { --tw-gradient-to: #1d4ed8; }
    </style>
</body>
</html>
