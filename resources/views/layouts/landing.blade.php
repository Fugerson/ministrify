<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="scroll-smooth">
<head>
    <script>
        // Dark mode by default - prevent FOUC
        if (localStorage.getItem('theme') !== 'light') {
            document.documentElement.classList.add('dark');
        }
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    {{-- Primary Meta Tags --}}
    <title>@yield('title', __('landing.home_title'))</title>
    <meta name="title" content="@yield('title', __('landing.home_title'))">
    <meta name="description" content="@yield('description', __('landing.home_meta'))">
    <meta name="keywords" content="@yield('keywords', __('landing.home_keywords'))">
    <meta name="author" content="Ministrify">
    <meta name="robots" content="index, follow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Hreflang for multilingual SEO --}}
    <link rel="alternate" hreflang="uk" href="{{ url()->current() }}">
    <link rel="alternate" hreflang="en" href="{{ url()->current() }}">
    <link rel="alternate" hreflang="x-default" href="{{ url()->current() }}">

    {{-- Open Graph / Facebook --}}
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('title', __('landing.home_title'))">
    <meta property="og:description" content="@yield('description', __('landing.home_meta'))">
    <meta property="og:image" content="@yield('og_image', asset('og-image.jpg'))">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:locale" content="{{ app()->getLocale() === 'en' ? 'en_US' : 'uk_UA' }}">
    <meta property="og:locale:alternate" content="{{ app()->getLocale() === 'en' ? 'uk_UA' : 'en_US' }}">
    <meta property="og:site_name" content="Ministrify">

    {{-- Twitter --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ url()->current() }}">
    <meta name="twitter:title" content="@yield('title', __('landing.home_title'))">
    <meta name="twitter:description" content="@yield('description', __('landing.home_meta'))">
    <meta name="twitter:image" content="@yield('og_image', asset('og-image.jpg'))">

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

        /* Gradient text */
        .gradient-text {
            background: linear-gradient(135deg, #60a5fa 0%, #3b82f6 40%, #818cf8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Hero gradient */
        .hero-gradient {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 50%, #e0e7ff 100%);
        }
        .dark .hero-gradient {
            background: radial-gradient(ellipse at 20% 50%, #1e3a8a 0%, #0f172a 50%, #020617 100%);
        }

        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
        .scrollbar-hide::-webkit-scrollbar { display: none; }

        /* Floating blobs */
        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.4;
            pointer-events: none;
        }
        .dark .blob { opacity: 0.15; }

        @keyframes blob-float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            25% { transform: translate(30px, -40px) scale(1.1); }
            50% { transform: translate(-20px, 20px) scale(0.95); }
            75% { transform: translate(15px, 30px) scale(1.05); }
        }
        @keyframes float-slow {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }
        @keyframes float-medium {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-15px) rotate(3deg); }
        }
        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 20px rgba(59, 130, 246, 0.3); }
            50% { box-shadow: 0 0 40px rgba(59, 130, 246, 0.6), 0 0 80px rgba(59, 130, 246, 0.2); }
        }
        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        @keyframes gradient-shift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        @keyframes fade-in-up {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes count-up {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Scroll reveal */
        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.8s cubic-bezier(0.16, 1, 0.3, 1), transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .reveal.revealed {
            opacity: 1;
            transform: translateY(0);
        }
        .reveal-delay-1 { transition-delay: 0.1s; }
        .reveal-delay-2 { transition-delay: 0.2s; }
        .reveal-delay-3 { transition-delay: 0.3s; }
        .reveal-delay-4 { transition-delay: 0.4s; }
        .reveal-delay-5 { transition-delay: 0.5s; }

        /* Glass card */
        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .dark .glass-card {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        /* Glow button */
        .glow-btn {
            position: relative;
            overflow: hidden;
        }
        .glow-btn::before {
            content: '';
            position: absolute;
            top: -2px; left: -2px; right: -2px; bottom: -2px;
            background: linear-gradient(135deg, #3b82f6, #818cf8, #3b82f6);
            background-size: 200% 200%;
            animation: gradient-shift 3s ease infinite;
            border-radius: inherit;
            z-index: -1;
            opacity: 0.7;
            filter: blur(8px);
            transition: opacity 0.3s;
        }
        .glow-btn:hover::before { opacity: 1; }

        /* Grid pattern */
        .grid-pattern {
            background-image:
                linear-gradient(rgba(59, 130, 246, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(59, 130, 246, 0.03) 1px, transparent 1px);
            background-size: 60px 60px;
        }
        .dark .grid-pattern {
            background-image:
                linear-gradient(rgba(59, 130, 246, 0.06) 1px, transparent 1px),
                linear-gradient(90deg, rgba(59, 130, 246, 0.06) 1px, transparent 1px);
        }

        /* Animated border gradient */
        .border-gradient {
            position: relative;
        }
        .border-gradient::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            padding: 1px;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.5), rgba(129, 140, 248, 0.5), rgba(59, 130, 246, 0.5));
            background-size: 200% 200%;
            animation: gradient-shift 4s ease infinite;
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            pointer-events: none;
        }

        /* Stat counter */
        .stat-number {
            background: linear-gradient(135deg, #fff 0%, #bfdbfe 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>

    {{-- Schema.org Markup --}}
    @yield('schema')
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'SoftwareApplication',
        'name' => 'Ministrify',
        'applicationCategory' => 'BusinessApplication',
        'applicationSubCategory' => 'Church Management Software',
        'operatingSystem' => 'Web, iOS, Android',
        'description' => __('landing.schema_description'),
        'url' => url('/'),
        'inLanguage' => ['uk', 'en'],
        'author' => [
            '@type' => 'Organization',
            'name' => 'Ministrify',
            'url' => url('/'),
            'logo' => asset('icon-512x512.png'),
        ],
        'offers' => [
            '@type' => 'Offer',
            'price' => '0',
            'priceCurrency' => 'USD',
            'availability' => 'https://schema.org/InStock',
            'description' => __('landing.schema_available'),
        ],
        'aggregateRating' => [
            '@type' => 'AggregateRating',
            'ratingValue' => '4.9',
            'ratingCount' => '50',
            'bestRating' => '5',
            'worstRating' => '1',
        ],
        'featureList' => array_map('trim', explode(',', __('landing.schema_features'))),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => 'Ministrify',
        'url' => url('/'),
        'logo' => asset('icon-512x512.png'),
        'description' => __('landing.schema_org_description'),
        'foundingDate' => '2024',
        'foundingLocation' => 'Ukraine',
        'areaServed' => [
            '@type' => 'GeoCircle',
            'geoMidpoint' => [
                '@type' => 'GeoCoordinates',
                'latitude' => 48.3794,
                'longitude' => 31.1656,
            ],
            'geoRadius' => '5000 km',
        ],
        'contactPoint' => [
            '@type' => 'ContactPoint',
            'contactType' => 'customer support',
            'url' => url('/contact'),
            'availableLanguage' => ['Ukrainian', 'Russian', 'English'],
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>

    {{-- Alpine.js + Collapse plugin --}}
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- reCAPTCHA v3 --}}
    <x-recaptcha-script />

    {{-- Locale Switcher Function --}}
    <script>
    window.switchLocale = function(locale) {
        console.log('Switching locale to:', locale);

        // Set HTTP cookie immediately (1 year expiry)
        const maxAge = 365 * 24 * 60 * 60;
        document.cookie = 'locale=' + locale + '; path=/; max-age=' + maxAge + '; SameSite=Lax; Secure';

        // Get CSRF token from meta tag
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

        // Send to server to save user preference
        const formData = new FormData();
        formData.append('_token', csrfToken);

        fetch('/locale/' + locale, {
            method: 'POST',
            credentials: 'include',
            body: formData
        })
        .then(r => r.json())
        .catch(err => console.error('Locale switch error:', err));

        // Reload after brief delay
        setTimeout(() => { window.location.href = window.location.href; }, 200);
    };
    </script>

    @yield('head')

    {{-- Google Analytics --}}
    @if(config('services.google_analytics.id'))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.google_analytics.id') }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ config('services.google_analytics.id') }}');
    </script>
    @endif
</head>
<body class="font-sans antialiased text-gray-900 bg-white dark:bg-gray-950 dark:text-gray-100" x-data="{ mobileMenu: false, darkMode: localStorage.getItem('theme') !== 'light' }" :class="{ 'dark': darkMode }" x-effect="darkMode ? document.documentElement.classList.add('dark') : document.documentElement.classList.remove('dark')">

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
                    <a href="{{ url('/features') }}" class="text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors font-medium">{{ __('landing.features_overview') }}</a>
                    <a href="{{ url('/contact') }}" class="text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors font-medium">{{ __('landing.contacts') }}</a>
                </div>

                {{-- CTA Buttons --}}
                <div class="hidden md:flex items-center space-x-4">
                    {{-- Theme Toggle --}}
                    <div class="flex items-center bg-gray-200 dark:bg-gray-700 rounded-lg p-1">
                        <button @click="darkMode = false; localStorage.setItem('theme', 'light'); document.documentElement.classList.remove('dark')"
                                :class="!darkMode ? 'bg-white dark:bg-gray-600 shadow' : ''"
                                class="p-1.5 rounded-md transition-all" title="{{ __('landing.light_theme_title') }}">
                            <svg class="w-4 h-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                        <button @click="darkMode = true; localStorage.setItem('theme', 'dark'); document.documentElement.classList.add('dark')"
                                :class="darkMode ? 'bg-white dark:bg-gray-600 shadow' : ''"
                                class="p-1.5 rounded-md transition-all" title="{{ __('landing.dark_theme_title') }}">
                            <svg class="w-4 h-4 text-indigo-500" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>
                            </svg>
                        </button>
                    </div>
                    <x-locale-switcher />
                    <a href="{{ route('login') }}" class="text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 font-medium transition-colors">{{ __('landing.login') }}</a>
                    <a href="{{ url('/register-church') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg transition-all shadow-lg shadow-primary-600/25 hover:shadow-primary-600/40">
                        {{ __('landing.sign_up') }}
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
                <a href="{{ url('/features') }}" class="block px-3 py-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg font-medium">{{ __('landing.features_overview') }}</a>
                <a href="{{ url('/contact') }}" class="block px-3 py-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg font-medium">{{ __('landing.contacts') }}</a>
                <hr class="border-gray-200 dark:border-gray-800">
                {{-- Theme Toggle --}}
                <div class="flex items-center justify-between px-3 py-2">
                    <span class="text-gray-600 dark:text-gray-300 font-medium">{{ __('landing.theme') }}</span>
                    <div class="flex items-center bg-gray-200 dark:bg-gray-700 rounded-lg p-1">
                        <button @click="darkMode = false; localStorage.setItem('theme', 'light'); document.documentElement.classList.remove('dark')"
                                :class="!darkMode ? 'bg-white dark:bg-gray-600 shadow' : ''"
                                class="p-1.5 rounded-md transition-all">
                            <svg class="w-4 h-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                        <button @click="darkMode = true; localStorage.setItem('theme', 'dark'); document.documentElement.classList.add('dark')"
                                :class="darkMode ? 'bg-white dark:bg-gray-600 shadow' : ''"
                                class="p-1.5 rounded-md transition-all">
                            <svg class="w-4 h-4 text-indigo-500" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <hr class="border-gray-200 dark:border-gray-800">
                <x-locale-switcher />
                <a href="{{ route('login') }}" class="block px-3 py-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg font-medium">{{ __('landing.login') }}</a>
                <a href="{{ url('/register-church') }}" class="block px-3 py-2 bg-primary-600 text-white text-center rounded-lg font-semibold">{{ __('landing.sign_up') }}</a>
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
                        {{ __('landing.footer_modern_platform') }}
                    </p>
                </div>

                {{-- Product --}}
                <div>
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-4">{{ __('landing.footer_product') }}</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ url('/features') }}" class="text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">{{ __('landing.footer_features_overview') }}</a></li>
                        {{-- TODO: Розкоментувати після бета-тестування
                        <li><a href="{{ url('/pricing') }}" class="text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">{{ __('landing.pricing') }}</a></li>
                        --}}
                        <li><a href="{{ url('/register-church') }}" class="text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">{{ __('landing.footer_registration') }}</a></li>
                    </ul>
                </div>

                {{-- Support --}}
                <div>
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-4">{{ __('landing.footer_support') }}</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ url('/contact') }}" class="text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">{{ __('landing.contacts') }}</a></li>
                        <li><a href="{{ url('/docs') }}" class="text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">{{ __('landing.footer_documentation') }}</a></li>
                        <li><a href="{{ url('/faq') }}" class="text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">FAQ</a></li>
                    </ul>
                </div>

                {{-- Legal --}}
                <div>
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-4">{{ __('landing.footer_legal_info') }}</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ url('/terms') }}" class="text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">{{ __('landing.footer_terms') }}</a></li>
                        <li><a href="{{ url('/privacy') }}" class="text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">{{ __('landing.footer_privacy') }}</a></li>
                    </ul>
                </div>
            </div>

            <div class="mt-8 pt-8 border-t border-gray-200 dark:border-gray-800 flex flex-col md:flex-row items-center justify-between">
                <p class="text-gray-500 dark:text-gray-400 text-sm">
                    © {{ date('Y') }} Ministrify. {{ __('landing.footer_all_rights') }}
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

    {{-- Scroll Reveal Observer --}}
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

        document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

        // Animated counters
        document.querySelectorAll('[data-count]').forEach(el => {
            const countObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const target = parseInt(el.dataset.count);
                        const suffix = el.dataset.suffix || '';
                        const duration = 2000;
                        const start = performance.now();
                        const animate = (now) => {
                            const progress = Math.min((now - start) / duration, 1);
                            const eased = 1 - Math.pow(1 - progress, 4);
                            el.textContent = Math.floor(target * eased).toLocaleString() + suffix;
                            if (progress < 1) requestAnimationFrame(animate);
                        };
                        requestAnimationFrame(animate);
                        countObserver.unobserve(el);
                    }
                });
            }, { threshold: 0.5 });
            countObserver.observe(el);
        });
    });
    </script>

    @yield('scripts')
</body>
</html>
