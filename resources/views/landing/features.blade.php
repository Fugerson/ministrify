@extends('layouts.landing')

@section('title', 'Можливості — Ministrify | Функції системи управління церквою')
@section('description', 'Повний список можливостей Ministrify: управління членами, події, пожертви, групи, служіння, Telegram бот, звіти та багато іншого.')

@section('content')
<section class="pt-32 pb-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h1 class="text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-4">
                Все необхідне для вашої церкви
            </h1>
            <p class="text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                Потужні інструменти, прості у використанні. Створено спеціально для потреб українських церков.
            </p>
        </div>

        {{-- Feature sections --}}
        <div class="space-y-24">
            {{-- Members --}}
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div>
                    <div class="inline-flex items-center px-3 py-1 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Члени церкви
                    </div>
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Повна база ваших людей</h2>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        Зберігайте всю інформацію про членів церкви в одному місці. Контакти, сім'ї, дні народження, участь у служіннях.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-center text-gray-600 dark:text-gray-300">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Детальні профілі з фото
                        </li>
                        <li class="flex items-center text-gray-600 dark:text-gray-300">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Зв'язки сімей
                        </li>
                        <li class="flex items-center text-gray-600 dark:text-gray-300">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Теги та фільтрація
                        </li>
                        <li class="flex items-center text-gray-600 dark:text-gray-300">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Імпорт з Excel/CSV
                        </li>
                    </ul>
                </div>
                <div class="bg-gray-100 dark:bg-gray-800 rounded-2xl p-8 aspect-video flex items-center justify-center">
                    <div class="text-center text-gray-400">
                        <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Скріншот інтерфейсу
                    </div>
                </div>
            </div>

            {{-- Donations --}}
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div class="order-2 lg:order-1 bg-gray-100 dark:bg-gray-800 rounded-2xl p-8 aspect-video flex items-center justify-center">
                    <div class="text-center text-gray-400">
                        <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Скріншот пожертв
                    </div>
                </div>
                <div class="order-1 lg:order-2">
                    <div class="inline-flex items-center px-3 py-1 rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Пожертви
                    </div>
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Онлайн-пожертви без комісій</h2>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        Приймайте пожертви через LiqPay та Monobank. Ми не беремо комісію — ви платите тільки платіжній системі.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-center text-gray-600 dark:text-gray-300">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            LiqPay та Monobank
                        </li>
                        <li class="flex items-center text-gray-600 dark:text-gray-300">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Регулярні пожертви
                        </li>
                        <li class="flex items-center text-gray-600 dark:text-gray-300">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Автоматичні подяки
                        </li>
                        <li class="flex items-center text-gray-600 dark:text-gray-300">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Детальні звіти
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Events --}}
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div>
                    <div class="inline-flex items-center px-3 py-1 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Календар
                    </div>
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Події та розклад служінь</h2>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        Плануйте богослужіння, зустрічі груп та заходи. Автоматичні нагадування та синхронізація з Google Calendar.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-center text-gray-600 dark:text-gray-300">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Візуальний календар
                        </li>
                        <li class="flex items-center text-gray-600 dark:text-gray-300">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Повторювані події
                        </li>
                        <li class="flex items-center text-gray-600 dark:text-gray-300">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Google Calendar sync
                        </li>
                        <li class="flex items-center text-gray-600 dark:text-gray-300">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Планування служіння
                        </li>
                    </ul>
                </div>
                <div class="bg-gray-100 dark:bg-gray-800 rounded-2xl p-8 aspect-video flex items-center justify-center">
                    <div class="text-center text-gray-400">
                        <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Скріншот календаря
                    </div>
                </div>
            </div>

            {{-- More features grid --}}
            <div>
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white text-center mb-12">І ще багато іншого</h2>
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @php
                        $moreFeatures = [
                            ['icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10', 'title' => 'Служіння', 'desc' => 'Організуйте служіння та призначайте служителів'],
                            ['icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'title' => 'Групи', 'desc' => 'Домашні групи та малі спільноти'],
                            ['icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01', 'title' => 'Відвідуваність', 'desc' => 'Облік відвідуваності з аналітикою'],
                            ['icon' => 'M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3', 'title' => 'Пісні', 'desc' => 'Бібліотека пісень з акордами'],
                            ['icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z', 'title' => 'Молитви', 'desc' => 'Молитовні потреби та стіна молитов'],
                            ['icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'title' => 'Звіти', 'desc' => 'Детальна аналітика та експорт'],
                            ['icon' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9', 'title' => 'Telegram', 'desc' => 'Бот для сповіщень та комунікації'],
                            ['icon' => 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z', 'title' => 'PWA', 'desc' => 'Мобільний додаток без встановлення'],
                            ['icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'title' => 'Безпека', 'desc' => 'SSL, бекапи, GDPR'],
                        ];
                    @endphp

                    @foreach($moreFeatures as $feature)
                        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-6 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                            <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900/30 rounded-xl flex items-center justify-center mb-4">
                                <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $feature['icon'] }}"/>
                                </svg>
                            </div>
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-2">{{ $feature['title'] }}</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-sm">{{ $feature['desc'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- CTA --}}
        <div class="mt-20 text-center">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Готові почати?</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-8">Спробуйте безкоштовно — без зобов'язань.</p>
            <a href="{{ url('/register-church') }}" class="inline-flex items-center px-8 py-4 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition-all shadow-lg shadow-primary-600/25">
                Почати безкоштовно
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </a>
        </div>
    </div>
</section>
@endsection
