<!DOCTYPE html>
<html lang="uk" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    {{-- Primary Meta Tags --}}
    <title>@yield('title', 'ChurchHub — Система управління церквою')</title>
    <meta name="title" content="@yield('title', 'ChurchHub — Система управління церквою')">
    <meta name="description" content="@yield('description', 'Сучасна платформа для управління церквою: члени, події, пожертви, групи, служіння. Українська мова, LiqPay та Monobank.')">
    <meta name="keywords" content="@yield('keywords', 'церква, управління церквою, church management, CRM для церкви, пожертви онлайн, облік членів церкви')">
    <meta name="author" content="ChurchHub">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Open Graph / Facebook --}}
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('title', 'ChurchHub — Система управління церквою')">
    <meta property="og:description" content="@yield('description', 'Сучасна платформа для управління церквою: члени, події, пожертви, групи, служіння.')">
    <meta property="og:image" content="@yield('og_image', asset('images/og-image.jpg'))">
    <meta property="og:locale" content="uk_UA">
    <meta property="og:site_name" content="ChurchHub">

    {{-- Twitter --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ url()->current() }}">
    <meta name="twitter:title" content="@yield('title', 'ChurchHub — Система управління церквою')">
    <meta name="twitter:description" content="@yield('description', 'Сучасна платформа для управління церквою.')">
    <meta name="twitter:image" content="@yield('og_image', asset('images/og-image.jpg'))">

    {{-- Favicon --}}
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#4F46E5">

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
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                            950: '#1e1b4b',
                        },
                    },
                },
            },
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        .gradient-text {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #d946ef 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .hero-gradient {
            background: linear-gradient(135deg, #eef2ff 0%, #faf5ff 50%, #fdf4ff 100%);
        }
        .dark .hero-gradient {
            background: linear-gradient(135deg, #1e1b4b 0%, #2e1065 50%, #3b0764 100%);
        }
    </style>

    {{-- Schema.org Markup --}}
    @yield('schema')
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "ChurchHub",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web",
        "description": "Сучасна платформа для управління церквою: члени, події, пожертви, групи, служіння.",
        "url": "{{ url('/') }}",
        "author": {
            "@type": "Organization",
            "name": "ChurchHub"
        },
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "UAH",
            "description": "Безкоштовний план до 50 членів"
        },
        "aggregateRating": {
            "@type": "AggregateRating",
            "ratingValue": "4.9",
            "ratingCount": "50"
        }
    }
    </script>

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @yield('head')
</head>
<body class="font-sans antialiased text-gray-900 bg-white dark:bg-gray-950 dark:text-gray-100" x-data="{ mobileMenu: false, darkMode: localStorage.getItem('darkMode') === 'true' }" :class="{ 'dark': darkMode }">

    {{-- Navigation --}}
    <nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 dark:bg-gray-950/80 backdrop-blur-lg border-b border-gray-200/50 dark:border-gray-800/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                {{-- Logo --}}
                <a href="{{ url('/') }}" class="flex items-center space-x-2">
                    <div class="w-9 h-9 bg-gradient-to-br from-primary-500 to-purple-600 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-gray-900 dark:text-white">ChurchHub</span>
                </a>

                {{-- Desktop Menu --}}
                <div class="hidden md:flex items-center space-x-8">
                    <a href="{{ url('/features') }}" class="text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors font-medium">Можливості</a>
                    <a href="{{ url('/pricing') }}" class="text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors font-medium">Ціни</a>
                    <a href="{{ url('/contact') }}" class="text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors font-medium">Контакти</a>
                </div>

                {{-- CTA Buttons --}}
                <div class="hidden md:flex items-center space-x-4">
                    <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)" class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
                        <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                        <svg x-show="darkMode" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </button>
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
                <a href="{{ url('/features') }}" class="block px-3 py-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg font-medium">Можливості</a>
                <a href="{{ url('/pricing') }}" class="block px-3 py-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg font-medium">Ціни</a>
                <a href="{{ url('/contact') }}" class="block px-3 py-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg font-medium">Контакти</a>
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
                        <div class="w-8 h-8 bg-gradient-to-br from-primary-500 to-purple-600 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <span class="text-lg font-bold text-gray-900 dark:text-white">ChurchHub</span>
                    </a>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Сучасна платформа для управління церквою. Зроблено в Україні 🇺🇦
                    </p>
                </div>

                {{-- Product --}}
                <div>
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-4">Продукт</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ url('/features') }}" class="text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">Можливості</a></li>
                        <li><a href="{{ url('/pricing') }}" class="text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">Ціни</a></li>
                        <li><a href="{{ url('/register-church') }}" class="text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">Реєстрація</a></li>
                    </ul>
                </div>

                {{-- Support --}}
                <div>
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-4">Підтримка</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ url('/contact') }}" class="text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">Контакти</a></li>
                        <li><a href="#" class="text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">Документація</a></li>
                        <li><a href="#" class="text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">FAQ</a></li>
                    </ul>
                </div>

                {{-- Legal --}}
                <div>
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-4">Правова інформація</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">Умови використання</a></li>
                        <li><a href="#" class="text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">Політика приватності</a></li>
                    </ul>
                </div>
            </div>

            <div class="mt-8 pt-8 border-t border-gray-200 dark:border-gray-800 flex flex-col md:flex-row items-center justify-between">
                <p class="text-gray-500 dark:text-gray-400 text-sm">
                    © {{ date('Y') }} ChurchHub. Всі права захищені.
                </p>
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
            </div>
        </div>
    </footer>

    @yield('scripts')
</body>
</html>
