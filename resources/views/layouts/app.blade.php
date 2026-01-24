<!DOCTYPE html>
<html lang="uk" x-data="{
    darkMode: localStorage.getItem('theme') === 'dark' || (localStorage.getItem('theme') === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches),
    searchOpen: false,
    fabOpen: false
}" :class="{ 'dark': darkMode }">
<head>
    <script>
        // Apply dark mode immediately before any rendering to prevent FOUC
        (function() {
            const theme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (theme === 'dark' || (theme === 'auto' && prefersDark) || (!theme && prefersDark)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="{{ $currentChurch->primary_color ?? '#3b82f6' }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Ministrify">
    <meta name="application-name" content="Ministrify">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="msapplication-TileColor" content="#3b82f6">
    <meta name="msapplication-TileImage" content="/icons/icon-144x144.png">

    <!-- PWA Manifest -->
    <link rel="manifest" href="/manifest.json">

    <!-- Apple Touch Icons -->
    <link rel="apple-touch-icon" href="/icons/icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/icons/icon-192x192.png">
    <link rel="apple-touch-icon" sizes="167x167" href="/icons/icon-152x152.png">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">

    <title>{{ config('app.name', 'Ministrify') }} - @yield('title', 'Головна')</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '{{ $currentChurch->theme_colors["50"] ?? "#eff6ff" }}',
                            100: '{{ $currentChurch->theme_colors["100"] ?? "#dbeafe" }}',
                            200: '{{ $currentChurch->theme_colors["200"] ?? "#bfdbfe" }}',
                            300: '{{ $currentChurch->theme_colors["300"] ?? "#93c5fd" }}',
                            400: '{{ $currentChurch->theme_colors["400"] ?? "#60a5fa" }}',
                            500: '{{ $currentChurch->theme_colors["500"] ?? "#3b82f6" }}',
                            600: '{{ $currentChurch->theme_colors["600"] ?? "#2563eb" }}',
                            700: '{{ $currentChurch->theme_colors["700"] ?? "#1d4ed8" }}',
                            800: '{{ $currentChurch->theme_colors["800"] ?? "#1e40af" }}',
                            900: '{{ $currentChurch->theme_colors["900"] ?? "#1e3a8a" }}',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        .touch-target { min-height: 44px; min-width: 44px; }
        html { scroll-behavior: smooth; -webkit-overflow-scrolling: touch; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .safe-bottom { padding-bottom: env(safe-area-inset-bottom); }
        .safe-top { padding-top: env(safe-area-inset-top); }
        input:focus, select:focus, textarea:focus { outline: none; }
        @media screen and (max-width: 768px) {
            input, select, textarea { font-size: 16px !important; }
        }
        .dark body { background-color: #111827; }

        /* Mobile viewport fixes */
        @supports (height: 100dvh) {
            .h-dvh { height: 100dvh; }
            .min-h-dvh { min-height: 100dvh; }
        }

        /* Fix for mobile bottom nav spacing */
        @media (max-width: 1023px) {
            .mobile-safe-bottom { padding-bottom: calc(5rem + env(safe-area-inset-bottom, 0px)); }
        }

        /* Custom Scrollbars - Minimalistic Design */
        * {
            scrollbar-width: thin;
            scrollbar-color: rgba(156, 163, 175, 0.5) transparent;
        }
        .dark * {
            scrollbar-color: rgba(75, 85, 99, 0.6) transparent;
        }

        /* Webkit Scrollbars (Chrome, Safari, Edge) */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(156, 163, 175, 0.4);
            border-radius: 3px;
            transition: background 0.2s ease;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(107, 114, 128, 0.6);
        }
        ::-webkit-scrollbar-corner {
            background: transparent;
        }

        /* Dark mode scrollbars */
        .dark ::-webkit-scrollbar-thumb {
            background: rgba(75, 85, 99, 0.5);
        }
        .dark ::-webkit-scrollbar-thumb:hover {
            background: rgba(107, 114, 128, 0.7);
        }

        /* Scrollbar on hover only for cleaner look */
        .scroll-hover::-webkit-scrollbar-thumb {
            background: transparent;
        }
        .scroll-hover:hover::-webkit-scrollbar-thumb {
            background: rgba(156, 163, 175, 0.4);
        }
        .dark .scroll-hover:hover::-webkit-scrollbar-thumb {
            background: rgba(75, 85, 99, 0.5);
        }

        /* Thin scrollbar variant */
        .scrollbar-thin::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }
        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: rgba(156, 163, 175, 0.3);
            border-radius: 2px;
        }
        .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: rgba(107, 114, 128, 0.5);
        }
        .dark .scrollbar-thin::-webkit-scrollbar-thumb {
            background: rgba(75, 85, 99, 0.4);
        }
        .dark .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: rgba(107, 114, 128, 0.6);
        }

        /* Accent colored scrollbar */
        .scrollbar-accent::-webkit-scrollbar-thumb {
            background: rgba(59, 130, 246, 0.4);
        }
        .scrollbar-accent::-webkit-scrollbar-thumb:hover {
            background: rgba(59, 130, 246, 0.6);
        }

        /* ========================================
           UI/UX IMPROVEMENTS - Animations & Motion
           ======================================== */

        /* Respect user preference for reduced motion */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }
        }

        /* Enhanced focus states for accessibility */
        :focus-visible {
            outline: 2px solid currentColor;
            outline-offset: 2px;
        }
        .dark :focus-visible {
            outline-color: #60a5fa;
        }

        /* Smooth page transitions */
        .page-transition {
            animation: fadeInUp 0.3s ease-out;
        }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Stagger animation for lists */
        .stagger-item {
            opacity: 0;
            animation: staggerIn 0.4s ease-out forwards;
        }
        .stagger-item:nth-child(1) { animation-delay: 0.05s; }
        .stagger-item:nth-child(2) { animation-delay: 0.1s; }
        .stagger-item:nth-child(3) { animation-delay: 0.15s; }
        .stagger-item:nth-child(4) { animation-delay: 0.2s; }
        .stagger-item:nth-child(5) { animation-delay: 0.25s; }
        .stagger-item:nth-child(6) { animation-delay: 0.3s; }
        .stagger-item:nth-child(7) { animation-delay: 0.35s; }
        .stagger-item:nth-child(8) { animation-delay: 0.4s; }
        .stagger-item:nth-child(n+9) { animation-delay: 0.45s; }
        @keyframes staggerIn {
            from {
                opacity: 0;
                transform: translateY(15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Card hover effects */
        .card-hover {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
        }
        .dark .card-hover:hover {
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.4), 0 8px 10px -6px rgba(0, 0, 0, 0.3);
        }

        /* Button press effect */
        .btn-press {
            transition: transform 0.1s ease;
        }
        .btn-press:active {
            transform: scale(0.97);
        }

        /* Ripple effect for buttons */
        .ripple {
            position: relative;
            overflow: hidden;
        }
        .ripple::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            pointer-events: none;
            background-image: radial-gradient(circle, currentColor 10%, transparent 10.01%);
            background-repeat: no-repeat;
            background-position: 50%;
            transform: scale(10, 10);
            opacity: 0;
            transition: transform 0.4s, opacity 0.8s;
        }
        .ripple:active::after {
            transform: scale(0, 0);
            opacity: 0.2;
            transition: 0s;
        }

        /* Skeleton loading animation */
        .skeleton {
            background: linear-gradient(90deg, #e5e7eb 25%, #f3f4f6 50%, #e5e7eb 75%);
            background-size: 200% 100%;
            animation: skeleton-loading 1.5s ease-in-out infinite;
        }
        .dark .skeleton {
            background: linear-gradient(90deg, #374151 25%, #4b5563 50%, #374151 75%);
            background-size: 200% 100%;
        }
        @keyframes skeleton-loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* Pulse glow effect */
        .pulse-glow {
            animation: pulseGlow 2s ease-in-out infinite;
        }
        @keyframes pulseGlow {
            0%, 100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.4); }
            50% { box-shadow: 0 0 0 8px rgba(59, 130, 246, 0); }
        }

        /* Shake animation for errors */
        .shake {
            animation: shake 0.5s ease-in-out;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-5px); }
            40%, 80% { transform: translateX(5px); }
        }

        /* Success checkmark animation */
        .check-animate {
            animation: checkPop 0.3s ease-out;
        }
        @keyframes checkPop {
            0% { transform: scale(0); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }

        /* Slide in from right */
        .slide-in-right {
            animation: slideInRight 0.3s ease-out;
        }
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Bounce subtle */
        .bounce-subtle {
            animation: bounceSoft 0.5s ease;
        }
        @keyframes bounceSoft {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        /* Icon hover rotate */
        .icon-spin:hover svg {
            transition: transform 0.3s ease;
            transform: rotate(15deg);
        }

        /* Badge pulse for notifications */
        .badge-pulse::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border-radius: inherit;
            animation: badgePulse 1.5s ease-out infinite;
        }
        @keyframes badgePulse {
            0% {
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
            }
            70% {
                box-shadow: 0 0 0 6px rgba(239, 68, 68, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
            }
        }

        /* ========================================
           Mobile Improvements
           ======================================== */

        /* Better touch feedback */
        @media (hover: none) {
            .card-hover:active {
                transform: scale(0.98);
                transition: transform 0.1s ease;
            }
        }

        /* Improved mobile spacing */
        @media (max-width: 640px) {
            .mobile-compact {
                padding-left: 1rem;
                padding-right: 1rem;
            }
            .mobile-stack {
                flex-direction: column;
                gap: 0.75rem;
            }
            .mobile-full {
                width: 100%;
            }
        }

        /* Tablet optimizations */
        @media (min-width: 641px) and (max-width: 1023px) {
            .tablet-grid-2 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        /* Landscape mobile */
        @media (max-height: 500px) and (orientation: landscape) {
            .landscape-compact {
                padding-top: 0.5rem;
                padding-bottom: 0.5rem;
            }
            .landscape-hide {
                display: none;
            }
        }

        /* Pull-to-refresh indicator area */
        .ptr-area {
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            overscroll-behavior-y: contain;
        }

        /* ========================================
           Dark Mode Enhancements
           ======================================== */

        /* Softer dark backgrounds for less eye strain */
        .dark .bg-softer {
            background-color: #1a1f2e;
        }

        /* Glow effects for dark mode */
        .dark .glow-primary {
            box-shadow: 0 0 15px rgba(59, 130, 246, 0.3);
        }
        .dark .glow-success {
            box-shadow: 0 0 15px rgba(16, 185, 129, 0.3);
        }

        /* Better contrast borders in dark mode */
        .dark .border-subtle {
            border-color: rgba(255, 255, 255, 0.1);
        }

        /* Frosted glass effect */
        .glass {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        .dark .glass {
            background: rgba(17, 24, 39, 0.8);
        }

        /* ========================================
           Loading States
           ======================================== */

        /* Spinner */
        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid currentColor;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Page Loader */
        #page-loader {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f3f4f6;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }
        .dark #page-loader {
            background: #111827;
        }
        #page-loader.loaded {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }
        .loader-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid #e5e7eb;
            border-top-color: #3b82f6;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        .dark .loader-spinner {
            border-color: #374151;
            border-top-color: #60a5fa;
        }

        /* Hide content initially */
        .page-content {
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .page-content.visible {
            opacity: 1;
        }

        /* Dots loading */
        .loading-dots::after {
            content: '';
            animation: dots 1.5s steps(4, end) infinite;
        }
        @keyframes dots {
            0%, 20% { content: ''; }
            40% { content: '.'; }
            60% { content: '..'; }
            80%, 100% { content: '...'; }
        }

        /* Progress bar */
        .progress-bar {
            height: 3px;
            background: linear-gradient(90deg, transparent, currentColor, transparent);
            animation: progressSlide 1.5s ease-in-out infinite;
        }
        @keyframes progressSlide {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        /* ========================================
           Interactive Enhancements
           ======================================== */

        /* Smooth number counter */
        .counter {
            display: inline-block;
            transition: transform 0.2s ease;
        }
        .counter.updating {
            transform: scale(1.1);
        }

        /* Hover underline effect */
        .hover-underline {
            position: relative;
        }
        .hover-underline::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: currentColor;
            transition: width 0.3s ease;
        }
        .hover-underline:hover::after {
            width: 100%;
        }

        /* Tooltip fade */
        .tooltip-fade {
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.2s ease, visibility 0.2s ease;
        }
        .group:hover .tooltip-fade,
        .peer:hover ~ .tooltip-fade {
            opacity: 1;
            visibility: visible;
        }
    </style>

    @stack('styles')

    @include('partials.design-themes')
</head>
<body class="font-sans antialiased bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
    <!-- Page Loader -->
    <div id="page-loader">
        <div class="loader-spinner"></div>
    </div>
    <script>
        // Hide loader when page is ready
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                document.getElementById('page-loader').classList.add('loaded');
                document.querySelector('.page-content')?.classList.add('visible');
            }, 100);
        });
    </script>
    @if(session('impersonate_church_id') && auth()->user()->isSuperAdmin())
    <!-- Church context banner -->
    <div class="sticky top-0 z-50 bg-indigo-600 text-white text-center py-2 px-4 flex items-center justify-center gap-4">
        <span class="flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            Ви в контексті церкви: <strong>{{ $currentChurch->name }}</strong>
        </span>
        <form method="POST" action="{{ route('system.exit-church') }}" class="inline">
            @csrf
            <button type="submit" class="px-3 py-1 bg-white/20 hover:bg-white/30 rounded-lg text-sm font-medium transition-colors">
                ← Вийти
            </button>
        </form>
        <a href="{{ route('system.index') }}" class="px-3 py-1 bg-white/10 hover:bg-white/20 rounded-lg text-sm transition-colors">
            System Admin
        </a>
    </div>
    @endif

    @if(session('impersonating_from'))
    <!-- User impersonation banner -->
    <div class="sticky top-0 z-50 bg-orange-500 text-white text-center py-2 px-4 flex items-center justify-center gap-4">
        <span class="flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            Ви увійшли як <strong>{{ auth()->user()->name }}</strong>
        </span>
        <form method="POST" action="{{ route('stop-impersonating') }}" class="inline">
            @csrf
            <button type="submit" class="px-3 py-1 bg-white/20 hover:bg-white/30 rounded-lg text-sm font-medium transition-colors">
                ← Повернутись
            </button>
        </form>
    </div>
    @endif

    <div class="page-content">
    <div x-data="{ sidebarOpen: false }" class="min-h-screen flex"
         @keydown.window.prevent.cmd.k="searchOpen = true"
         @keydown.window.prevent.ctrl.k="searchOpen = true"
         @keydown.window.escape="searchOpen = false; fabOpen = false">

        <!-- Desktop Sidebar -->
        <aside class="hidden lg:flex lg:flex-col lg:w-64 lg:fixed lg:inset-y-0 z-30 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 {{ session('impersonating_from') || session('impersonate_church_id') ? 'pt-10' : '' }}">
            <div class="flex items-center h-16 px-6 border-b border-gray-200 dark:border-gray-700 flex-shrink-0">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
                    @if($currentChurch->logo)
                    <img src="/storage/{{ $currentChurch->logo }}" alt="{{ $currentChurch->name }}" class="w-8 h-8 rounded-lg object-contain">
                    @else
                    <span class="text-2xl">⛪</span>
                    @endif
                    <span class="text-lg font-bold text-gray-900 dark:text-white truncate">{{ $currentChurch->name ?? 'Ministrify' }}</span>
                </a>
            </div>

            <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto no-scrollbar">
                <a href="{{ route('dashboard') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl {{ request()->routeIs('dashboard') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Головна
                </a>
                @hasChurchRole
                @if(auth()->user()->canView('people'))
                <a href="{{ route('people.index') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl {{ request()->routeIs('people.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 24 24">
                        <path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM3.751 20.105a8.25 8.25 0 0116.498 0 .75.75 0 01-.437.695A18.683 18.683 0 0112 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 01-.437-.695z" clip-rule="evenodd"/>
                    </svg>
                    Люди
                </a>
                @endif
                @if(auth()->user()->canView('groups'))
                <a href="{{ route('groups.index') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl {{ request()->routeIs('groups.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Групи
                </a>
                @endif
                @if(auth()->user()->canView('ministries'))
                <a href="{{ route('ministries.index') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl {{ request()->routeIs('ministries.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    Команди
                </a>
                @endif
                @if(auth()->user()->canView('events'))
                <a href="{{ route('schedule') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl {{ request()->routeIs('schedule') || request()->routeIs('events.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Розклад
                </a>
                @endif
                @endhasChurchRole
                @hasChurchRole
                @if(auth()->user()->canView('announcements') && auth()->user()->church_id && auth()->user()->churchRole)
                <div x-data="pmBadge()" x-init="startPolling()" @pm-read.window="fetchCount()">
                    <a href="{{ route('announcements.index') }}" class="flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-xl {{ request()->routeIs('announcements.*') || request()->routeIs('pm.*') || request()->routeIs('messages.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            Комунікації
                        </span>
                        @php $initialPmCount = \App\Models\PrivateMessage::unreadCount(auth()->user()->church_id, auth()->id()); @endphp
                        @if($initialPmCount > 0)
                        <span x-cloak x-show="count > 0" x-text="count > 99 ? '99+' : count" class="px-2 py-0.5 text-xs font-bold bg-red-500 text-white rounded-full"></span>
                        @endif
                    </a>
                </div>
                @elseif(auth()->user()->canView('announcements'))
                <a href="{{ route('announcements.index') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl {{ request()->routeIs('announcements.*') || request()->routeIs('pm.*') || request()->routeIs('messages.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    Комунікації
                </a>
                @endif
                @endhasChurchRole
                @if(auth()->user()->canView('finances'))
                <a href="{{ route('finances.index') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl {{ request()->routeIs('finances.*') || request()->routeIs('expenses.*') || request()->routeIs('donations.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Фінанси
                </a>
                @endif
                @if(auth()->user()->canView('reports'))
                <a href="{{ route('reports.index') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl {{ request()->routeIs('reports.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Звіти
                </a>
                @endif
                @if(auth()->user()->canView('resources'))
                <a href="{{ route('resources.index') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl {{ request()->routeIs('resources.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                    </svg>
                    Ресурси
                </a>
                @endif
                @if(auth()->user()->canView('boards'))
                <a href="{{ route('boards.index') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl {{ request()->routeIs('boards.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    Завдання
                </a>
                @endif
                @hasChurchRole
                @else
                {{-- Pending approval notice for users without role --}}
                <div class="mt-4 mx-3 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl">
                    <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-amber-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-amber-800 dark:text-amber-200">Очікування підтвердження</p>
                            <p class="text-xs text-amber-600 dark:text-amber-400 mt-1">Адміністратор має надати вам доступ до системи.</p>
                        </div>
                    </div>
                </div>
                @endhasChurchRole

                @admin
                <div class="pt-4 mt-4 border-t border-gray-200 dark:border-gray-700">
                    <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Адміністрування</p>
                </div>
                <a href="{{ route('settings.index') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl {{ request()->routeIs('settings.*') || request()->routeIs('website-builder.*') || request()->routeIs('telegram.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Налаштування
                </a>
                <a href="{{ route('support.index') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl {{ request()->routeIs('support.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    Підтримка
                </a>
                @endadmin

                @if(auth()->user()->isSuperAdmin())
                <div class="pt-4 mt-4 border-t border-gray-200 dark:border-gray-700">
                    <p class="px-3 text-xs font-semibold text-red-400 uppercase tracking-wider">System Admin</p>
                </div>
                <a href="{{ route('system.index') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    System Admin
                </a>
                @endif
            </nav>

            <!-- Theme Toggle & User -->
            <div class="flex-shrink-0 p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <!-- Theme Toggle -->
                <div class="flex items-center justify-between mb-3 px-2">
                    <span class="text-xs text-gray-500 dark:text-gray-400">Тема</span>
                    <div class="flex items-center space-x-1 bg-gray-200 dark:bg-gray-700 rounded-lg p-1">
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
                </div>

            </div>
        </aside>

        <!-- Mobile Sidebar (overlay) -->
        <div x-show="sidebarOpen" x-cloak class="lg:hidden fixed inset-0 z-40 bg-black/50" @click="sidebarOpen = false"></div>
        <aside x-show="sidebarOpen" x-cloak
               x-transition:enter="transform transition ease-out duration-300"
               x-transition:enter-start="-translate-x-full"
               x-transition:enter-end="translate-x-0"
               x-transition:leave="transform transition ease-in duration-200"
               x-transition:leave-start="translate-x-0"
               x-transition:leave-end="-translate-x-full"
               class="lg:hidden fixed inset-y-0 left-0 z-50 w-[calc(100vw-3rem)] max-w-72 bg-white dark:bg-gray-800 shadow-xl flex flex-col">
            <div class="flex items-center justify-between h-16 px-6 border-b border-gray-200 dark:border-gray-700 flex-shrink-0">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
                    @if($currentChurch->logo)
                    <img src="/storage/{{ $currentChurch->logo }}" alt="{{ $currentChurch->name }}" class="w-8 h-8 rounded-lg object-contain">
                    @else
                    <span class="text-2xl">⛪</span>
                    @endif
                    <span class="text-lg font-bold text-gray-900 dark:text-white">Ministrify</span>
                </a>
                <button @click="sidebarOpen = false" class="p-2 -mr-2 text-gray-500 hover:text-gray-700 dark:text-gray-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto">
                <a href="{{ route('dashboard') }}" @click="sidebarOpen = false" class="flex items-center px-4 py-3 text-base font-medium rounded-xl {{ request()->routeIs('dashboard') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Головна
                </a>
                @hasChurchRole
                @if(auth()->user()->canView('people'))
                <a href="{{ route('people.index') }}" @click="sidebarOpen = false" class="flex items-center px-4 py-3 text-base font-medium rounded-xl {{ request()->routeIs('people.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-6 h-6 mr-3" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM3.751 20.105a8.25 8.25 0 0116.498 0 .75.75 0 01-.437.695A18.683 18.683 0 0112 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 01-.437-.695z" clip-rule="evenodd"/></svg>
                    Люди
                </a>
                @endif
                @if(auth()->user()->canView('groups'))
                <a href="{{ route('groups.index') }}" @click="sidebarOpen = false" class="flex items-center px-4 py-3 text-base font-medium rounded-xl {{ request()->routeIs('groups.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Групи
                </a>
                @endif
                @if(auth()->user()->canView('ministries'))
                <a href="{{ route('ministries.index') }}" @click="sidebarOpen = false" class="flex items-center px-4 py-3 text-base font-medium rounded-xl {{ request()->routeIs('ministries.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    Команди
                </a>
                @endif
                @if(auth()->user()->canView('events'))
                <a href="{{ route('schedule') }}" @click="sidebarOpen = false" class="flex items-center px-4 py-3 text-base font-medium rounded-xl {{ request()->routeIs('schedule') || request()->routeIs('events.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Розклад
                </a>
                @endif
                @endhasChurchRole
                @hasChurchRole
                @if(auth()->user()->canView('announcements') && auth()->user()->church_id && auth()->user()->churchRole)
                <div x-data="pmBadge()" x-init="startPolling()" @pm-read.window="fetchCount()">
                    <a href="{{ route('announcements.index') }}" @click="sidebarOpen = false" class="flex items-center justify-between px-4 py-3 text-base font-medium rounded-xl {{ request()->routeIs('announcements.*') || request()->routeIs('pm.*') || request()->routeIs('messages.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        <span class="flex items-center">
                            <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            Комунікації
                        </span>
                        <span x-cloak x-show="count > 0" x-text="count > 99 ? '99+' : count" class="px-2 py-0.5 text-xs font-bold bg-red-500 text-white rounded-full"></span>
                    </a>
                </div>
                @elseif(auth()->user()->canView('announcements'))
                <a href="{{ route('announcements.index') }}" @click="sidebarOpen = false" class="flex items-center px-4 py-3 text-base font-medium rounded-xl {{ request()->routeIs('announcements.*') || request()->routeIs('pm.*') || request()->routeIs('messages.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    Комунікації
                </a>
                @endif
                @endhasChurchRole
                @if(auth()->user()->canView('finances'))
                <a href="{{ route('finances.index') }}" @click="sidebarOpen = false" class="flex items-center px-4 py-3 text-base font-medium rounded-xl {{ request()->routeIs('finances.*') || request()->routeIs('expenses.*') || request()->routeIs('donations.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    Фінанси
                </a>
                @endif
                @if(auth()->user()->canView('reports'))
                <a href="{{ route('reports.index') }}" @click="sidebarOpen = false" class="flex items-center px-4 py-3 text-base font-medium rounded-xl {{ request()->routeIs('reports.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Звіти
                </a>
                @endif
                @if(auth()->user()->canView('resources'))
                <a href="{{ route('resources.index') }}" @click="sidebarOpen = false" class="flex items-center px-4 py-3 text-base font-medium rounded-xl {{ request()->routeIs('resources.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                    Ресурси
                </a>
                @endif
                @if(auth()->user()->canView('boards'))
                <a href="{{ route('boards.index') }}" @click="sidebarOpen = false" class="flex items-center px-4 py-3 text-base font-medium rounded-xl {{ request()->routeIs('boards.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    Завдання
                </a>
                @endif
                @hasChurchRole
                @else
                <div class="mt-4 mx-4 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl">
                    <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-amber-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-amber-800 dark:text-amber-200">Очікування</p>
                            <p class="text-xs text-amber-600 dark:text-amber-400 mt-1">Адміністратор має надати доступ.</p>
                        </div>
                    </div>
                </div>
                @endhasChurchRole
                @admin
                <div class="pt-4 mt-4 border-t border-gray-200 dark:border-gray-700"><p class="px-4 text-xs font-semibold text-gray-400 uppercase">Адміністрування</p></div>
                <a href="{{ route('settings.index') }}" @click="sidebarOpen = false" class="flex items-center px-4 py-3 text-base font-medium rounded-xl {{ request()->routeIs('settings.*') || request()->routeIs('website-builder.*') || request()->routeIs('telegram.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Налаштування
                </a>
                <a href="{{ route('support.index') }}" @click="sidebarOpen = false" class="flex items-center px-4 py-3 text-base font-medium rounded-xl {{ request()->routeIs('support.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    Підтримка
                </a>
                @endadmin
                @if(auth()->user()->isSuperAdmin())
                <div class="pt-4 mt-4 border-t border-gray-200 dark:border-gray-700"><p class="px-4 text-xs font-semibold text-red-400 uppercase">System Admin</p></div>
                <a href="{{ route('system.index') }}" @click="sidebarOpen = false" class="flex items-center px-4 py-3 text-base font-medium rounded-xl text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    System Admin
                </a>
                @endif
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 lg:pl-64 overflow-clip">
            <!-- Mobile Header -->
            <header class="lg:hidden sticky {{ session('impersonating_from') || session('impersonate_church_id') ? 'top-10' : 'top-0' }} z-30 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm safe-top">
                <div class="flex items-center justify-between h-14 px-3">
                    <button @click="sidebarOpen = true" class="w-11 h-11 flex items-center justify-center -ml-2 text-gray-600 dark:text-gray-300 active:bg-gray-100 dark:active:bg-gray-700 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <button @click="searchOpen = true" class="flex-1 mx-2 flex items-center justify-center space-x-2 h-10 px-3 bg-gray-100 dark:bg-gray-700 rounded-xl active:bg-gray-200 dark:active:bg-gray-600">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Пошук...</span>
                    </button>
                    <div class="flex items-center">
                        <button @click="$dispatch('open-page-help')" class="w-11 h-11 flex items-center justify-center text-gray-400 hover:text-primary-600 active:bg-gray-100 dark:active:bg-gray-700 rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </button>
                        <a href="{{ route('my-profile') }}" class="w-11 h-11 flex items-center justify-center">
                            <x-user-avatar size="md" />
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-11 h-11 flex items-center justify-center -mr-2 text-gray-400 active:bg-gray-100 dark:active:bg-gray-700 rounded-xl">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Desktop Header -->
            <header class="hidden lg:flex sticky {{ session('impersonating_from') || session('impersonate_church_id') ? 'top-10' : 'top-0' }} z-30 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 items-center justify-between h-16 px-6">
                <h1 class="text-lg font-semibold text-gray-900 dark:text-white">@yield('title', 'Головна')</h1>
                <div class="flex items-center space-x-4">
                    <!-- Help Button -->
                    <button @click="$dispatch('open-page-help')"
                            class="p-2 text-gray-400 hover:text-primary-600 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                            title="Довідка (?)">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </button>
                    <!-- Search Button -->
                    <button @click="searchOpen = true" class="flex items-center space-x-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-xl transition-colors">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Пошук...</span>
                        <kbd class="hidden sm:inline-flex items-center px-2 py-0.5 text-xs text-gray-400 bg-gray-200 dark:bg-gray-600 rounded">/</kbd>
                    </button>
                    @yield('actions')
                    <!-- Profile Link -->
                    <x-user-profile-link />
                </div>
            </header>

            <!-- Page Content -->
            <main class="p-4 lg:p-6 pb-24 lg:pb-6">
                @if(session('success'))
                <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 rounded-xl flex items-start">
                    <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
                @endif
                @if(session('error'))
                <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 rounded-xl flex items-start">
                    <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
                @endif
                @if($errors->any())
                <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 rounded-xl">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
                @endif
                @yield('content')
            </main>
        </div>

        <!-- Mobile Bottom Nav -->
        <nav class="lg:hidden fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 z-40 safe-bottom">
            <div class="flex items-center justify-around h-16">
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center justify-center flex-1 py-2 {{ request()->routeIs('dashboard') ? 'text-primary-600 dark:text-primary-400' : 'text-gray-500 dark:text-gray-400' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span class="text-xs mt-1">Головна</span>
                </a>
                <a href="{{ route('people.index') }}" class="flex flex-col items-center justify-center flex-1 py-2 {{ request()->routeIs('people.*') ? 'text-primary-600 dark:text-primary-400' : 'text-gray-500 dark:text-gray-400' }}">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM3.751 20.105a8.25 8.25 0 0116.498 0 .75.75 0 01-.437.695A18.683 18.683 0 0112 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 01-.437-.695z" clip-rule="evenodd"/></svg>
                    <span class="text-xs mt-1">Люди</span>
                </a>
                <a href="{{ route('schedule') }}" class="flex flex-col items-center justify-center flex-1 py-2 {{ request()->routeIs('schedule') || request()->routeIs('events.*') ? 'text-primary-600 dark:text-primary-400' : 'text-gray-500 dark:text-gray-400' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span class="text-xs mt-1">Розклад</span>
                </a>
                <a href="{{ route('groups.index') }}" class="flex flex-col items-center justify-center flex-1 py-2 {{ request()->routeIs('groups.*') ? 'text-primary-600 dark:text-primary-400' : 'text-gray-500 dark:text-gray-400' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span class="text-xs mt-1">Групи</span>
                </a>
                <a href="{{ route('my-profile') }}" class="flex flex-col items-center justify-center flex-1 py-2 {{ request()->routeIs('my-profile*') ? 'text-primary-600 dark:text-primary-400' : 'text-gray-500 dark:text-gray-400' }}">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM3.751 20.105a8.25 8.25 0 0116.498 0 .75.75 0 01-.437.695A18.683 18.683 0 0112 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 01-.437-.695z" clip-rule="evenodd"/></svg>
                    <span class="text-xs mt-1">Профіль</span>
                </a>
            </div>
        </nav>

        <!-- FAB (Quick Actions) -->
        @if(auth()->user()->canCreate('people') || auth()->user()->canCreate('events') || auth()->user()->canCreate('groups') || auth()->user()->canCreate('finances'))
        <div class="fixed right-4 bottom-20 lg:bottom-6 z-50" x-data="{ open: false }">
            <div x-show="open" x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="absolute bottom-16 right-0 mb-2 w-48 bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                @if(auth()->user()->canCreate('people'))
                <a href="{{ route('people.create') }}" class="flex items-center px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-5 h-5 mr-3 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    <span class="text-gray-700 dark:text-gray-200">Нова людина</span>
                </a>
                @endif
                @if(auth()->user()->canCreate('events'))
                <a href="{{ route('events.create') }}" class="flex items-center px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-5 h-5 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span class="text-gray-700 dark:text-gray-200">Нова подія</span>
                </a>
                @endif
                @if(auth()->user()->canCreate('groups'))
                <a href="{{ route('groups.create') }}" class="flex items-center px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-5 h-5 mr-3 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span class="text-gray-700 dark:text-gray-200">Нова група</span>
                </a>
                @endif
                @if(auth()->user()->canCreate('finances'))
                <a href="{{ route('expenses.create') }}" class="flex items-center px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-5 h-5 mr-3 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span class="text-gray-700 dark:text-gray-200">Нова витрата</span>
                </a>
                @endif
            </div>
            <button @click="open = !open"
                    class="w-14 h-14 bg-primary-600 hover:bg-primary-700 text-white rounded-full shadow-lg shadow-primary-500/30 flex items-center justify-center transition-all duration-200"
                    :class="{ 'rotate-45': open }">
                <svg class="w-6 h-6 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
            </button>
        </div>
        @endif

        <!-- Global Search Modal -->
        <div x-show="searchOpen" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto"
             @click.self="searchOpen = false">
            <div class="min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="searchOpen = false"></div>

                <div x-show="searchOpen"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="relative inline-block w-full max-w-xl mt-16 text-left align-middle transition-all transform"
                     x-data="globalSearch()"
                     @keydown.arrow-down.prevent="selectNext()"
                     @keydown.arrow-up.prevent="selectPrev()"
                     @keydown.enter.prevent="goToSelected()">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden">
                        <!-- Search Input -->
                        <div class="flex items-center px-4 border-b border-gray-200 dark:border-gray-700">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input type="text"
                                   x-ref="searchInput"
                                   x-model="query"
                                   @input.debounce.300ms="search()"
                                   x-init="$nextTick(() => $refs.searchInput.focus())"
                                   placeholder="Пошук людей, команд, подій..."
                                   class="w-full px-4 py-4 text-gray-900 dark:text-white bg-transparent border-0 focus:ring-0 focus:outline-none placeholder-gray-400">
                            <kbd class="hidden sm:inline-flex px-2 py-1 text-xs text-gray-400 bg-gray-100 dark:bg-gray-700 rounded">ESC</kbd>
                        </div>

                        <!-- Results -->
                        <div class="max-h-96 overflow-y-auto">
                            <template x-if="loading">
                                <div class="px-4 py-8 text-center text-gray-500">
                                    <svg class="w-6 h-6 mx-auto animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                            </template>

                            <template x-if="!loading && results.length === 0 && query.length >= 2">
                                <div class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    Нічого не знайдено
                                </div>
                            </template>

                            <template x-if="!loading && query.length < 2">
                                <div class="px-4 py-6">
                                    <p class="text-xs font-medium text-gray-400 uppercase mb-3">Швидкі дії</p>
                                    <div class="space-y-1">
                                        <a href="{{ route('people.create') }}" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                                            <span class="w-8 h-8 rounded-lg bg-primary-100 dark:bg-primary-900 flex items-center justify-center mr-3">
                                                <svg class="w-4 h-4 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                                            </span>
                                            <span class="text-gray-700 dark:text-gray-200">Додати людину</span>
                                            <kbd class="ml-auto px-2 py-0.5 text-xs text-gray-400 bg-gray-100 dark:bg-gray-700 rounded">N</kbd>
                                        </a>
                                        <a href="{{ route('events.create') }}" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                                            <span class="w-8 h-8 rounded-lg bg-green-100 dark:bg-green-900 flex items-center justify-center mr-3">
                                                <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            </span>
                                            <span class="text-gray-700 dark:text-gray-200">Створити подію</span>
                                            <kbd class="ml-auto px-2 py-0.5 text-xs text-gray-400 bg-gray-100 dark:bg-gray-700 rounded">E</kbd>
                                        </a>
                                        <a href="{{ route('groups.create') }}" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                                            <span class="w-8 h-8 rounded-lg bg-purple-100 dark:bg-purple-900 flex items-center justify-center mr-3">
                                                <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            </span>
                                            <span class="text-gray-700 dark:text-gray-200">Створити групу</span>
                                            <kbd class="ml-auto px-2 py-0.5 text-xs text-gray-400 bg-gray-100 dark:bg-gray-700 rounded">G</kbd>
                                        </a>
                                    </div>
                                </div>
                            </template>

                            <template x-if="!loading && results.length > 0">
                                <div class="py-2">
                                    <template x-for="(result, index) in results" :key="index">
                                        <a :href="result.url"
                                           class="flex items-center px-4 py-3 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                           :class="{ 'bg-gray-100 dark:bg-gray-700': selectedIndex === index }">
                                            <span class="w-10 h-10 rounded-xl flex items-center justify-center mr-3"
                                                  :class="{
                                                      'bg-primary-100 dark:bg-primary-900': result.type === 'person',
                                                      'bg-green-100 dark:bg-green-900': result.type === 'event',
                                                      'bg-purple-100 dark:bg-purple-900': result.type === 'ministry',
                                                      'bg-yellow-100 dark:bg-yellow-900': result.type === 'group'
                                                  }">
                                                <template x-if="result.type === 'person'">
                                                    <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM3.751 20.105a8.25 8.25 0 0116.498 0 .75.75 0 01-.437.695A18.683 18.683 0 0112 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 01-.437-.695z" clip-rule="evenodd"/></svg>
                                                </template>
                                                <template x-if="result.type === 'event'">
                                                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                </template>
                                                <template x-if="result.type === 'ministry'">
                                                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/></svg>
                                                </template>
                                                <template x-if="result.type === 'group'">
                                                    <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                </template>
                                            </span>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate" x-text="result.title"></p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate" x-text="result.subtitle"></p>
                                            </div>
                                        </a>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function globalSearch() {
            return {
                query: '',
                results: [],
                loading: false,
                selectedIndex: 0,

                async search() {
                    if (this.query.length < 2) {
                        this.results = [];
                        return;
                    }

                    this.loading = true;
                    try {
                        const response = await fetch(`/search?q=${encodeURIComponent(this.query)}`);
                        const data = await response.json();
                        this.results = data.results;
                        this.selectedIndex = 0;
                    } catch (error) {
                        console.error('Search error:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                selectNext() {
                    if (this.selectedIndex < this.results.length - 1) {
                        this.selectedIndex++;
                    }
                },

                selectPrev() {
                    if (this.selectedIndex > 0) {
                        this.selectedIndex--;
                    }
                },

                goToSelected() {
                    if (this.results[this.selectedIndex]) {
                        window.location.href = this.results[this.selectedIndex].url;
                    }
                }
            };
        }
    </script>

    @stack('scripts')

    <!-- Toast Notifications -->
    @include('components.toast')

    <!-- Page Help System -->
    @php
        $routeName = request()->route()?->getName() ?? 'dashboard';
        $pageKey = match(true) {
            str_starts_with($routeName, 'dashboard') => 'dashboard',
            str_starts_with($routeName, 'people') => 'people',
            str_starts_with($routeName, 'ministries') => 'ministries',
            str_starts_with($routeName, 'schedule') || str_starts_with($routeName, 'events.index') || str_starts_with($routeName, 'events.create') => 'schedule',
            $routeName === 'events.show' => 'events.show',
            str_starts_with($routeName, 'boards.show') => 'boards.show',
            str_starts_with($routeName, 'boards') => 'boards',
            str_starts_with($routeName, 'groups') => 'groups',
            str_starts_with($routeName, 'expenses') || str_starts_with($routeName, 'finances') => 'finances',
            str_starts_with($routeName, 'settings') => 'settings',
            str_starts_with($routeName, 'attendance') => 'attendance',
            str_starts_with($routeName, 'resources') => 'resources',
            str_starts_with($routeName, 'messages') => 'messages',
            str_starts_with($routeName, 'prayer-requests') => 'prayer-requests',
            str_starts_with($routeName, 'songs') => 'songs',
            str_starts_with($routeName, 'reports') => 'reports',
            str_starts_with($routeName, 'announcements') => 'announcements',
            str_starts_with($routeName, 'private-messages') || str_starts_with($routeName, 'pm.') => 'private-messages',
            str_starts_with($routeName, 'donations') => 'donations',
            str_starts_with($routeName, 'rotation') => 'rotation',
            str_starts_with($routeName, 'security') => 'security',
            default => null,
        };
    @endphp
    @if($pageKey)
        <x-page-help :page="$pageKey" />
    @endif

    <!-- Show session messages as toasts -->
    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                showToast('success', '{{ session('success') }}');
            });
        </script>
    @endif
    @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                showToast('error', '{{ session('error') }}');
            });
        </script>
    @endif
    @if(session('warning'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                showToast('warning', '{{ session('warning') }}');
            });
        </script>
    @endif

    <!-- PM Badge Polling -->
    <script>
        function pmBadge() {
            return {
                count: {{ \App\Models\PrivateMessage::unreadCount(auth()->user()->church_id, auth()->id()) }},
                interval: null,

                startPolling() {
                    this.fetchCount();
                    this.interval = setInterval(() => this.fetchCount(), 10000); // кожні 10 сек
                },

                async fetchCount() {
                    try {
                        const response = await fetch('{{ route("pm.unread-count") }}');
                        const data = await response.json();
                        this.count = data.count;
                    } catch (e) {}
                },

                destroy() {
                    if (this.interval) clearInterval(this.interval);
                }
            }
        }
    </script>

    <!-- PWA Service Worker Registration & Install Prompt -->
    <script>
        // Service Worker Registration
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(registration => {
                        console.log('SW registered:', registration.scope);

                        // Check for updates
                        registration.addEventListener('updatefound', () => {
                            const newWorker = registration.installing;
                            newWorker.addEventListener('statechange', () => {
                                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                    // New version available
                                    if (confirm('Доступна нова версія. Оновити?')) {
                                        newWorker.postMessage({ type: 'SKIP_WAITING' });
                                        window.location.reload();
                                    }
                                }
                            });
                        });
                    })
                    .catch(error => {
                        console.log('SW registration failed:', error);
                    });
            });
        }

        // PWA Install Prompt
        window.pwaInstallPrompt = null;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            window.pwaInstallPrompt = e;
            window.dispatchEvent(new CustomEvent('pwa-installable'));
        });

        window.addEventListener('appinstalled', () => {
            window.pwaInstallPrompt = null;
            localStorage.setItem('pwa-installed', 'true');
        });
    </script>

    <!-- PWA Install Banner -->
    @auth
    <div x-data="pwaInstallBanner()" x-show="showBanner" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-full"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0 transform translate-y-full"
         class="fixed bottom-0 left-0 right-0 z-50 p-4 bg-gradient-to-r from-primary-600 to-primary-700 text-white shadow-lg md:bottom-4 md:left-4 md:right-auto md:max-w-sm md:rounded-2xl">
        <div class="flex items-start gap-4">
            <div class="flex-shrink-0 w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                <img src="/icons/icon-72x72.png" alt="Ministrify" class="w-8 h-8">
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="font-semibold">Встановити додаток</h3>
                <p class="text-sm opacity-90 mt-1">Швидкий доступ з головного екрану вашого пристрою</p>
                <div class="flex gap-2 mt-3">
                    <button @click="install()"
                            class="px-4 py-2 bg-white text-primary-700 font-semibold rounded-lg hover:bg-white/90 transition-colors text-sm">
                        Встановити
                    </button>
                    <button @click="dismiss()"
                            class="px-4 py-2 text-white/80 hover:text-white transition-colors text-sm">
                        Не зараз
                    </button>
                </div>
            </div>
            <button @click="dismiss()" class="flex-shrink-0 p-1 text-white/60 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- iOS instructions --}}
        <div x-show="isIOS && !canInstall" class="mt-4 p-3 bg-white/10 rounded-xl text-sm">
            <p class="font-medium mb-2">Для iOS:</p>
            <ol class="list-decimal list-inside space-y-1 opacity-90">
                <li>Натисніть <svg class="inline w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L12 14M12 2L8 6M12 2L16 6M4 12V20H20V12"/></svg> (Поділитися)</li>
                <li>Виберіть "На Початковий екран"</li>
            </ol>
        </div>
    </div>

    <script>
    function pwaInstallBanner() {
        return {
            showBanner: false,
            canInstall: false,
            isIOS: /iPad|iPhone|iPod/.test(navigator.userAgent),

            init() {
                // Check if already installed or dismissed
                if (localStorage.getItem('pwa-installed') === 'true') return;
                if (localStorage.getItem('pwa-banner-dismissed')) {
                    const dismissed = new Date(localStorage.getItem('pwa-banner-dismissed'));
                    const daysSince = (Date.now() - dismissed.getTime()) / (1000 * 60 * 60 * 24);
                    if (daysSince < 7) return; // Don't show for 7 days after dismiss
                }

                // Check if running as PWA already
                if (window.matchMedia('(display-mode: standalone)').matches) return;

                // Listen for install prompt
                window.addEventListener('pwa-installable', () => {
                    this.canInstall = true;
                    this.showAfterDelay();
                });

                // Check if prompt already captured
                if (window.pwaInstallPrompt) {
                    this.canInstall = true;
                    this.showAfterDelay();
                }

                // For iOS, show instructions
                if (this.isIOS && !window.navigator.standalone) {
                    this.showAfterDelay();
                }
            },

            showAfterDelay() {
                // Show after 30 seconds on the page
                setTimeout(() => {
                    this.showBanner = true;
                }, 30000);
            },

            async install() {
                if (window.pwaInstallPrompt) {
                    window.pwaInstallPrompt.prompt();
                    const result = await window.pwaInstallPrompt.userChoice;
                    if (result.outcome === 'accepted') {
                        localStorage.setItem('pwa-installed', 'true');
                    }
                    window.pwaInstallPrompt = null;
                }
                this.showBanner = false;
            },

            dismiss() {
                localStorage.setItem('pwa-banner-dismissed', new Date().toISOString());
                this.showBanner = false;
            }
        };
    }
    </script>
    @endauth
    </div><!-- /.page-content -->
</body>
</html>
