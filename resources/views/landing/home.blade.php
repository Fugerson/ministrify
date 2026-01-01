@extends('layouts.landing')

@section('title', 'Ministrify — Система управління церквою | Українська CRM для церков')
@section('description', 'Безкоштовна українська платформа для управління церквою: члени, події, пожертви, групи, служіння. Інтеграція з LiqPay, Monobank та Telegram.')
@section('keywords', 'церква, управління церквою, church management, CRM для церкви, пожертви онлайн, облік членів церкви, українська CRM, безкоштовно')

@section('content')
{{-- Hero Section --}}
<section class="hero-gradient pt-24 pb-16 md:pt-32 md:pb-24 overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            {{-- Left: Text --}}
            <div class="text-center lg:text-left">
                <div class="inline-flex items-center px-3 py-1 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 text-sm font-medium mb-6">
                    <span class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                    Безкоштовно до 50 членів
                </div>

                <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-gray-900 dark:text-white leading-tight mb-6">
                    Керуйте церквою
                    <span class="gradient-text">просто та ефективно</span>
                </h1>

                <p class="text-lg md:text-xl text-gray-600 dark:text-gray-300 mb-8 max-w-2xl mx-auto lg:mx-0">
                    Сучасна українська платформа для управління церквою: члени, події, пожертви, групи, служіння — все в одному місці.
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                    <a href="{{ url('/register-church') }}" class="inline-flex items-center justify-center px-8 py-4 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition-all shadow-lg shadow-primary-600/25 hover:shadow-primary-600/40 hover:scale-105">
                        <span>Почати безкоштовно</span>
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                    <a href="{{ url('/features') }}" class="inline-flex items-center justify-center px-8 py-4 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 font-semibold rounded-xl border border-gray-200 dark:border-gray-700 hover:border-primary-300 dark:hover:border-primary-600 transition-all hover:scale-105">
                        <svg class="w-5 h-5 mr-2 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Дивитись демо
                    </a>
                </div>

                {{-- Trust badges --}}
                <div class="mt-10 flex flex-wrap items-center justify-center lg:justify-start gap-6 text-sm text-gray-500 dark:text-gray-400">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        SSL захист
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/>
                        </svg>
                        Хмарне рішення
                    </div>
                    <div class="flex items-center">
                        <span class="text-xl mr-2">🇺🇦</span>
                        Українська мова
                    </div>
                </div>
            </div>

            {{-- Right: Hero Image / Dashboard Preview --}}
            <div class="relative">
                <div class="relative rounded-2xl overflow-hidden shadow-2xl shadow-primary-600/10 border border-gray-200 dark:border-gray-700">
                    <div class="bg-gray-100 dark:bg-gray-800 px-4 py-3 flex items-center space-x-2 border-b border-gray-200 dark:border-gray-700">
                        <div class="w-3 h-3 bg-red-400 rounded-full"></div>
                        <div class="w-3 h-3 bg-yellow-400 rounded-full"></div>
                        <div class="w-3 h-3 bg-green-400 rounded-full"></div>
                        <span class="ml-4 text-sm text-gray-500 dark:text-gray-400">ministrify.one/dashboard</span>
                    </div>
                    <div class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 p-6 aspect-video">
                        {{-- Mock dashboard with demo data --}}
                        <div class="grid grid-cols-3 gap-4 mb-4">
                            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm">
                                <div class="text-2xl font-bold text-primary-600">247</div>
                                <div class="text-xs text-gray-500">Людей</div>
                            </div>
                            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm">
                                <div class="text-2xl font-bold text-green-600">18</div>
                                <div class="text-xs text-gray-500">Подій</div>
                            </div>
                            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm">
                                <div class="text-2xl font-bold text-purple-600">8</div>
                                <div class="text-xs text-gray-500">Служінь</div>
                            </div>
                        </div>
                        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm h-32">
                            <div class="flex items-center justify-between mb-3">
                                <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Відвідуваність</div>
                                <div class="text-xs text-green-500">+12%</div>
                            </div>
                            <div class="flex items-end space-x-1 h-16">
                                @foreach([40, 55, 45, 60, 75, 65, 80, 70, 85, 78, 90, 88] as $h)
                                    <div class="flex-1 bg-primary-500/80 rounded-t" style="height: {{ $h }}%"></div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Floating elements --}}
                <div class="absolute -top-4 -right-4 bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-xl border border-gray-100 dark:border-gray-700 animate-bounce" style="animation-duration: 3s">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-white">+₴2,500</div>
                            <div class="text-xs text-gray-500">Нова пожертва</div>
                        </div>
                    </div>
                </div>

                <div class="absolute -bottom-4 -left-4 bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-xl border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-white">Telegram</div>
                            <div class="text-xs text-gray-500">Сповіщення</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Logos / Trust Section --}}
<section class="py-12 bg-white dark:bg-gray-900 border-y border-gray-100 dark:border-gray-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <p class="text-center text-sm text-gray-500 dark:text-gray-400 mb-8">Інтегровано з популярними сервісами</p>
        <div class="flex flex-wrap items-center justify-center gap-8 md:gap-16 opacity-60 grayscale hover:grayscale-0 hover:opacity-100 transition-all duration-500">
            <div class="flex items-center space-x-2 text-gray-700 dark:text-gray-300">
                <svg class="w-8 h-8" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                </svg>
                <span class="font-medium">Google Calendar</span>
            </div>
            <div class="flex items-center space-x-2 text-gray-700 dark:text-gray-300">
                <svg class="w-8 h-8" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                </svg>
                <span class="font-medium">Telegram</span>
            </div>
            <div class="flex items-center space-x-2 text-gray-700 dark:text-gray-300">
                <span class="text-2xl font-bold text-green-600">LiqPay</span>
            </div>
            <div class="flex items-center space-x-2 text-gray-700 dark:text-gray-300">
                <span class="text-2xl font-bold">monobank</span>
            </div>
        </div>
    </div>
</section>

{{-- Features Section --}}
<section id="features" class="py-20 bg-white dark:bg-gray-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <span class="inline-block px-4 py-1 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 text-sm font-medium mb-4">Можливості</span>
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                Все для ефективного управління церквою
            </h2>
            <p class="text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                Від обліку членів до онлайн-пожертв — один інструмент для всіх потреб вашої церкви.
            </p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            {{-- Feature 1 --}}
            <div class="group bg-gray-50 dark:bg-gray-900 rounded-2xl p-8 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                <div class="w-14 h-14 bg-primary-100 dark:bg-primary-900/50 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Управління членами</h3>
                <p class="text-gray-600 dark:text-gray-400">
                    Повна база членів церкви з профілями, контактами, сім'ями та історією участі. Швидкий пошук та фільтрація.
                </p>
            </div>

            {{-- Feature 2 --}}
            <div class="group bg-gray-50 dark:bg-gray-900 rounded-2xl p-8 hover:bg-green-50 dark:hover:bg-green-900/20 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                <div class="w-14 h-14 bg-green-100 dark:bg-green-900/50 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Онлайн-пожертви</h3>
                <p class="text-gray-600 dark:text-gray-400">
                    Приймайте пожертви через LiqPay та Monobank. Автоматичний облік, звіти та подяки донорам.
                </p>
            </div>

            {{-- Feature 3 --}}
            <div class="group bg-gray-50 dark:bg-gray-900 rounded-2xl p-8 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                <div class="w-14 h-14 bg-blue-100 dark:bg-blue-900/50 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Події та календар</h3>
                <p class="text-gray-600 dark:text-gray-400">
                    Плануйте богослужіння, зустрічі груп та заходи. Синхронізація з Google Calendar та нагадування.
                </p>
            </div>

            {{-- Feature 4 --}}
            <div class="group bg-gray-50 dark:bg-gray-900 rounded-2xl p-8 hover:bg-purple-50 dark:hover:bg-purple-900/20 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                <div class="w-14 h-14 bg-purple-100 dark:bg-purple-900/50 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Служіння та групи</h3>
                <p class="text-gray-600 dark:text-gray-400">
                    Організуйте служіння, домашні групи та команди. Відстежуйте участь та координуйте служителів.
                </p>
            </div>

            {{-- Feature 5 --}}
            <div class="group bg-gray-50 dark:bg-gray-900 rounded-2xl p-8 hover:bg-orange-50 dark:hover:bg-orange-900/20 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                <div class="w-14 h-14 bg-orange-100 dark:bg-orange-900/50 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Бібліотека пісень</h3>
                <p class="text-gray-600 dark:text-gray-400">
                    Зберігайте тексти пісень з акордами. Створюйте плейлисти для богослужінь та презентації.
                </p>
            </div>

            {{-- Feature 6 --}}
            <div class="group bg-gray-50 dark:bg-gray-900 rounded-2xl p-8 hover:bg-pink-50 dark:hover:bg-pink-900/20 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                <div class="w-14 h-14 bg-pink-100 dark:bg-pink-900/50 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Молитовні потреби</h3>
                <p class="text-gray-600 dark:text-gray-400">
                    Збирайте та відстежуйте молитовні потреби. Публічна стіна молитов та приватні запити.
                </p>
            </div>
        </div>

        <div class="text-center mt-12">
            <a href="{{ url('/features') }}" class="inline-flex items-center text-primary-600 dark:text-primary-400 font-semibold hover:text-primary-700 dark:hover:text-primary-300">
                Всі можливості
                <svg class="w-5 h-5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
    </div>
</section>

{{-- Stats Section --}}
<section class="py-16 bg-gradient-to-r from-primary-600 to-purple-600">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <div>
                <div class="text-4xl md:text-5xl font-bold text-white mb-2">{{ $stats['churches'] ?? '10' }}+</div>
                <div class="text-primary-100">Церков</div>
            </div>
            <div>
                <div class="text-4xl md:text-5xl font-bold text-white mb-2">{{ number_format($stats['members'] ?? 500) }}+</div>
                <div class="text-primary-100">Членів</div>
            </div>
            <div>
                <div class="text-4xl md:text-5xl font-bold text-white mb-2">{{ $stats['events'] ?? '100' }}+</div>
                <div class="text-primary-100">Подій</div>
            </div>
            <div>
                <div class="text-4xl md:text-5xl font-bold text-white mb-2">99.9%</div>
                <div class="text-primary-100">Uptime</div>
            </div>
        </div>
    </div>
</section>

{{-- Pricing Preview --}}
<section id="pricing" class="py-20 bg-gray-50 dark:bg-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <span class="inline-block px-4 py-1 rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 text-sm font-medium mb-4">Тарифи</span>
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                Простa та прозорa ціна
            </h2>
            <p class="text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                Почніть безкоштовно. Масштабуйтесь коли будете готові.
            </p>
        </div>

        <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">
            {{-- Free --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 border border-gray-200 dark:border-gray-700 hover:border-primary-300 dark:hover:border-primary-600 transition-all hover:shadow-lg">
                <div class="text-center mb-8">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Free</h3>
                    <div class="text-4xl font-bold text-gray-900 dark:text-white">₴0</div>
                    <div class="text-gray-500 dark:text-gray-400">назавжди</div>
                </div>
                <ul class="space-y-3 mb-8">
                    <li class="flex items-center text-gray-600 dark:text-gray-300">
                        <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        До 50 людей
                    </li>
                    <li class="flex items-center text-gray-600 dark:text-gray-300">
                        <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        До 3 служінь
                    </li>
                    <li class="flex items-center text-gray-600 dark:text-gray-300">
                        <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Календар подій
                    </li>
                    <li class="flex items-center text-gray-600 dark:text-gray-300">
                        <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Форми
                    </li>
                </ul>
                <a href="{{ url('/register-church') }}" class="block w-full py-3 px-4 text-center bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 font-semibold rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                    Почати безкоштовно
                </a>
            </div>

            {{-- Basic - Featured --}}
            <div class="bg-primary-600 rounded-2xl p-8 relative transform md:scale-105 shadow-xl shadow-primary-600/25">
                <div class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 px-4 py-1 bg-yellow-400 text-yellow-900 text-sm font-bold rounded-full">
                    Популярний
                </div>
                <div class="text-center mb-8">
                    <h3 class="text-xl font-bold text-white mb-2">Basic</h3>
                    <div class="text-4xl font-bold text-white">₴99</div>
                    <div class="text-primary-200">/місяць</div>
                </div>
                <ul class="space-y-3 mb-8">
                    <li class="flex items-center text-white font-medium">
                        <svg class="w-5 h-5 text-yellow-300 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Все з Free +
                    </li>
                    <li class="flex items-center text-primary-100">
                        <svg class="w-5 h-5 text-white mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        До 200 людей
                    </li>
                    <li class="flex items-center text-primary-100">
                        <svg class="w-5 h-5 text-white mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        До 10 служінь
                    </li>
                    <li class="flex items-center text-primary-100">
                        <svg class="w-5 h-5 text-white mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Telegram бот
                    </li>
                    <li class="flex items-center text-primary-100">
                        <svg class="w-5 h-5 text-white mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Фінанси та облік
                    </li>
                </ul>
                <a href="{{ url('/register-church') }}" class="block w-full py-3 px-4 text-center bg-white text-primary-600 font-semibold rounded-xl hover:bg-primary-50 transition-colors">
                    Обрати план
                </a>
            </div>

            {{-- Pro --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 border border-gray-200 dark:border-gray-700 hover:border-primary-300 dark:hover:border-primary-600 transition-all hover:shadow-lg">
                <div class="text-center mb-8">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Pro</h3>
                    <div class="text-4xl font-bold text-gray-900 dark:text-white">₴249</div>
                    <div class="text-gray-500 dark:text-gray-400">/місяць</div>
                </div>
                <ul class="space-y-3 mb-8">
                    <li class="flex items-center text-primary-600 dark:text-primary-400 font-medium">
                        <svg class="w-5 h-5 text-primary-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Все з Basic +
                    </li>
                    <li class="flex items-center text-gray-600 dark:text-gray-300">
                        <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Без обмежень людей
                    </li>
                    <li class="flex items-center text-gray-600 dark:text-gray-300">
                        <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Конструктор сайту
                    </li>
                    <li class="flex items-center text-gray-600 dark:text-gray-300">
                        <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Власний домен
                    </li>
                    <li class="flex items-center text-gray-600 dark:text-gray-300">
                        <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        API доступ
                    </li>
                </ul>
                <a href="{{ url('/register-church') }}" class="block w-full py-3 px-4 text-center bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 font-semibold rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                    Обрати Pro
                </a>
            </div>
        </div>
    </div>
</section>

{{-- CTA Section --}}
<section class="py-20 bg-white dark:bg-gray-950">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-6">
            Готові спростити управління церквою?
        </h2>
        <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">
            Приєднуйтесь до церков, які вже використовують Ministrify. Безкоштовно до 50 членів.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ url('/register-church') }}" class="inline-flex items-center justify-center px-8 py-4 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition-all shadow-lg shadow-primary-600/25 hover:shadow-primary-600/40">
                Почати безкоштовно
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </a>
            <a href="{{ url('/contact') }}" class="inline-flex items-center justify-center px-8 py-4 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 font-semibold rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700 transition-all">
                Запитати демо
            </a>
        </div>
    </div>
</section>
@endsection
