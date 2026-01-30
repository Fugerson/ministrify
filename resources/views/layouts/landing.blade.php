<!DOCTYPE html>
<html lang="uk" class="scroll-smooth">
<head>
    <script>
        // Dark mode by default - prevent FOUC
        if (localStorage.getItem('theme') !== 'light') {
            document.documentElement.classList.add('dark');
        }
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    {{-- Primary Meta Tags --}}
    <title>@yield('title', 'Ministrify — Система управління церквою')</title>
    <meta name="title" content="@yield('title', 'Ministrify — Система управління церквою')">
    <meta name="description" content="@yield('description', 'Сучасна платформа для управління церквою: члени, події, пожертви, групи, команди. Українська мова, LiqPay та Monobank.')">
    <meta name="keywords" content="@yield('keywords', 'церква, управління церквою, church management, CRM для церкви, пожертви онлайн, облік членів церкви')">
    <meta name="author" content="Ministrify">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Open Graph / Facebook --}}
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('title', 'Ministrify — Система управління церквою')">
    <meta property="og:description" content="@yield('description', 'Сучасна платформа для управління церквою: члени, події, пожертви, групи, команди.')">
    <meta property="og:image" content="@yield('og_image', asset('icon-512x512.png'))">
    <meta property="og:locale" content="uk_UA">
    <meta property="og:site_name" content="Ministrify">

    {{-- Twitter --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ url()->current() }}">
    <meta name="twitter:title" content="@yield('title', 'Ministrify — Система управління церквою')">
    <meta name="twitter:description" content="@yield('description', 'Сучасна платформа для управління церквою.')">
    <meta name="twitter:image" content="@yield('og_image', asset('icon-512x512.png'))">

    {{-- Favicon --}}
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#3b82f6">

    {{-- Preconnect for performance --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Styles --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                            950: '#172554',
                        },
                    },
                },
            },
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        body { overflow-wrap: break-word; word-break: break-word; }
        .gradient-text {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 50%, #1d4ed8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .hero-gradient {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 50%, #bfdbfe 100%);
        }
        .dark .hero-gradient {
            background: linear-gradient(135deg, #172554 0%, #1e3a8a 50%, #1e40af 100%);
        }
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
    </style>

    {{-- Schema.org Markup --}}
    @yield('schema')
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Ministrify",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Church Management Software",
        "operatingSystem": "Web, iOS, Android",
        "description": "Сучасна українська платформа для управління церквою: члени, події, пожертви, групи, команди, Telegram-бот.",
        "url": "{{ url('/') }}",
        "inLanguage": "uk",
        "author": {
            "@type": "Organization",
            "name": "Ministrify",
            "url": "{{ url('/') }}",
            "logo": "{{ asset('icon-512x512.png') }}"
        },
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "UAH",
            "availability": "https://schema.org/InStock",
            "description": "Безкоштовно для всіх церков"
        },
        "aggregateRating": {
            "@type": "AggregateRating",
            "ratingValue": "4.9",
            "ratingCount": "50",
            "bestRating": "5",
            "worstRating": "1"
        },
        "featureList": "База членів церкви, Планування подій, Фінансовий облік, Telegram-бот, Домашні групи, Команди служіння"
    }
    </script>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "Ministrify",
        "url": "{{ url('/') }}",
        "logo": "{{ asset('icon-512x512.png') }}",
        "description": "Сучасна українська платформа для управління церквою",
        "foundingDate": "2024",
        "foundingLocation": "Ukraine",
        "areaServed": {
            "@type": "GeoCircle",
            "geoMidpoint": {
                "@type": "GeoCoordinates",
                "latitude": "48.3794",
                "longitude": "31.1656"
            },
            "geoRadius": "5000000"
        },
        "contactPoint": {
            "@type": "ContactPoint",
            "contactType": "customer support",
            "url": "{{ url('/contact') }}",
            "availableLanguage": ["Ukrainian", "Russian", "English"]
        },
        "sameAs": []
    }
    </script>

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @yield('head')
</head>
<body class="font-sans antialiased text-gray-900 bg-white dark:bg-gray-950 dark:text-gray-100" x-data="{ mobileMenu: false, darkMode: localStorage.getItem('theme') !== 'light' }" :class="{ 'dark': darkMode }">

    {{-- Navigation --}}
    <nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 dark:bg-gray-950/80 backdrop-blur-lg border-b border-gray-200/50 dark:border-gray-800/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                {{-- Logo --}}
                <a href="{{ url('/') }}" class="flex items-center space-x-2">
                    <div class="w-9 h-9 bg-gradient-to-br from-primary-500 to-primary-700 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-gray-900 dark:text-white">Ministrify</span>
                </a>

                {{-- Desktop Menu --}}
                <div class="hidden md:flex items-center space-x-8">
                    <a href="{{ url('/features') }}" class="text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors font-medium">Огляд функцій</a>
                    {{-- TODO: Розкоментувати після бета-тестування
                    <a href="{{ url('/pricing') }}" class="text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors font-medium">Ціни</a>
                    --}}
                    <a href="{{ url('/contact') }}" class="text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors font-medium">Контакти</a>
                </div>

                {{-- CTA Buttons --}}
                <div class="hidden md:flex items-center space-x-4">
                    {{-- Theme Toggle --}}
                    <div class="flex items-center bg-gray-200 dark:bg-gray-700 rounded-lg p-1">
                        <button @click="darkMode = false; localStorage.setItem('theme', 'light')"
                                :class="!darkMode ? 'bg-white dark:bg-gray-600 shadow' : ''"
                                class="p-1.5 rounded-md transition-all" title="Світла тема">
                            <svg class="w-4 h-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                        <button @click="darkMode = true; localStorage.setItem('theme', 'dark')"
                                :class="darkMode ? 'bg-white dark:bg-gray-600 shadow' : ''"
                                class="p-1.5 rounded-md transition-all" title="Темна тема">
                            <svg class="w-4 h-4 text-indigo-500" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>
                            </svg>
                        </button>
                    </div>
                    <a href="{{ route('login') }}" class="text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 font-medium transition-colors">Увійти</a>
                    <a href="{{ url('/register-church') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg transition-all shadow-lg shadow-primary-600/25 hover:shadow-primary-600/40">
                        Почати безкоштовно
                    </a>
                </div>

                {{-- Mobile menu button --}}
                <button @click="mobileMenu = !mobileMenu" class="md:hidden p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400">
                    <svg x-show="!mobileMenu" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg x-show="mobileMenu" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mobile Menu --}}
        <div x-show="mobileMenu" x-cloak x-transition class="md:hidden border-t border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950">
            <div class="px-4 py-4 space-y-3">
                <a href="{{ url('/features') }}" class="block px-3 py-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg font-medium">Огляд функцій</a>
                {{-- TODO: Розкоментувати після бета-тестування
                <a href="{{ url('/pricing') }}" class="block px-3 py-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg font-medium">Ціни</a>
                --}}
                <a href="{{ url('/contact') }}" class="block px-3 py-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg font-medium">Контакти</a>
                <hr class="border-gray-200 dark:border-gray-800">
                {{-- Theme Toggle --}}
                <div class="flex items-center justify-between px-3 py-2">
                    <span class="text-gray-600 dark:text-gray-300 font-medium">Тема</span>
                    <div class="flex items-center bg-gray-200 dark:bg-gray-700 rounded-lg p-1">
                        <button @click="darkMode = false; localStorage.setItem('theme', 'light')"
                                :class="!darkMode ? 'bg-white dark:bg-gray-600 shadow' : ''"
                                class="p-1.5 rounded-md transition-all">
                            <svg class="w-4 h-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                        <button @click="darkMode = true; localStorage.setItem('theme', 'dark')"
                                :class="darkMode ? 'bg-white dark:bg-gray-600 shadow' : ''"
                                class="p-1.5 rounded-md transition-all">
                            <svg class="w-4 h-4 text-indigo-500" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <hr class="border-gray-200 dark:border-gray-800">
                <a href="{{ route('login') }}" class="block px-3 py-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg font-medium">Увійти</a>
                <a href="{{ url('/register-church') }}" class="block px-3 py-2 bg-primary-600 text-white text-center rounded-lg font-semibold">Почати безкоштовно</a>
            </div>
        </div>
    </nav>

    {{-- Main Content --}}
    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                {{-- Brand --}}
                <div class="col-span-2 md:col-span-1">
                    <a href="{{ url('/') }}" class="flex items-center space-x-2 mb-4">
                        <div class="w-8 h-8 bg-gradient-to-br from-primary-500 to-primary-700 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <span class="text-lg font-bold text-gray-900 dark:text-white">Ministrify</span>
                    </a>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Сучасна платформа для управління церквою. Зроблено в Україні 🇺🇦
                    </p>
                </div>

                {{-- Product --}}
                <div>
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-4">Продукт</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ url('/features') }}" class="text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">Огляд функцій</a></li>
                        {{-- TODO: Розкоментувати після бета-тестування
                        <li><a href="{{ url('/pricing') }}" class="text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">Ціни</a></li>
                        --}}
                        <li><a href="{{ url('/register-church') }}" class="text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">Реєстрація</a></li>
                    </ul>
                </div>

                {{-- Support --}}
                <div>
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-4">Підтримка</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ url('/contact') }}" class="text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">Контакти</a></li>
                        <li><a href="{{ url('/docs') }}" class="text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">Документація</a></li>
                        <li><a href="{{ url('/faq') }}" class="text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">FAQ</a></li>
                    </ul>
                </div>

                {{-- Legal --}}
                <div>
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-4">Правова інформація</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ url('/terms') }}" class="text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">Умови використання</a></li>
                        <li><a href="{{ url('/privacy') }}" class="text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">Політика приватності</a></li>
                    </ul>
                </div>
            </div>

            <div class="mt-8 pt-8 border-t border-gray-200 dark:border-gray-800 flex flex-col md:flex-row items-center justify-between">
                <p class="text-gray-500 dark:text-gray-400 text-sm">
                    © {{ date('Y') }} Ministrify. Всі права захищені.
                </p>
{{-- Приховано: соціальні мережі
                <div class="flex items-center space-x-4 mt-4 md:mt-0">
                    <a href="#" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                    </a>
                </div>
                --}}
            </div>
        </div>
    </footer>

    @yield('scripts')
</body>
</html>
