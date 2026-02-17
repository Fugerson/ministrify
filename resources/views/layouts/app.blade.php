<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" x-data="{
    darkMode: localStorage.getItem('theme') !== 'light',
    searchOpen: false,
    fabOpen: false
}" :class="{ 'dark': darkMode }">
<head>
    <script>
        // Apply dark mode immediately before any rendering to prevent FOUC
        // Dark is default, only light if explicitly set
        (function() {
            const theme = localStorage.getItem('theme');
            if (theme !== 'light') {
                document.documentElement.classList.add('dark');
            }
        })();

        // Handle back/forward cache - check if user is still authenticated
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                fetch('/api/auth-check', { credentials: 'same-origin' })
                    .then(r => r.json())
                    .then(data => {
                        if (!data.authenticated) {
                            window.location.href = '/login';
                        }
                    })
                    .catch(() => window.location.reload());
            }
        });

        // Global fetch interceptor — auto-reload on expired session (401/419)
        (function() {
            const originalFetch = window.fetch;
            let reloading = false;
            window.fetch = function(...args) {
                return originalFetch.apply(this, args).then(response => {
                    if ((response.status === 401 || response.status === 419) && !reloading) {
                        reloading = true;
                        window.location.reload();
                    }
                    return response;
                });
            };
        })();

        // Periodic session check for idle tabs (every 5 min)
        setInterval(function() {
            if (document.hidden) return;
            fetch('/api/auth-check', { credentials: 'same-origin' })
                .then(r => r.json())
                .then(data => {
                    if (!data.authenticated) window.location.href = '/login';
                })
                .catch(() => {});
        }, 5 * 60 * 1000);
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

    <title>{{ config('app.name', 'Ministrify') }} - @yield('title', __('Головна'))</title>

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

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>

    <!-- Driver.js - Guided Tour -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.css">
    <script src="https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.js.iife.js"></script>

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

        /* Prevent text overflow — wrap long words instead of breaking layout */
        body { overflow-wrap: break-word; word-break: break-word; }
        .break-anywhere { overflow-wrap: anywhere; }

        /* Mobile table horizontal scroll */
        .overflow-x-auto > table { min-width: 640px; }
        td, th { white-space: nowrap; }
        td.wrap-cell, th.wrap-cell { white-space: normal; overflow-wrap: break-word; }

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

        /* Page Loader - Professional */
        #page-loader {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 24px;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            transition: opacity 0.4s cubic-bezier(0.4, 0, 0.2, 1), visibility 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .dark #page-loader {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        }
        #page-loader.loaded {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        /* Logo container with pulse */
        .loader-logo {
            position: relative;
            width: 72px;
            height: 72px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .loader-logo::before {
            content: '';
            position: absolute;
            inset: -8px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(147, 51, 234, 0.2));
            animation: logoPulse 2s ease-in-out infinite;
        }
        .dark .loader-logo::before {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(147, 51, 234, 0.15));
        }
        @keyframes logoPulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.15); opacity: 0.7; }
        }
        .loader-logo-inner {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            animation: logoFloat 3s ease-in-out infinite;
            box-shadow: 0 10px 40px -10px rgba(59, 130, 246, 0.5);
        }
        .loader-logo-inner img {
            width: 40px;
            height: 40px;
            object-fit: contain;
            border-radius: 8px;
        }
        @keyframes logoFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }

        /* Modern spinner ring */
        .loader-ring {
            position: relative;
            width: 48px;
            height: 48px;
        }
        .loader-ring::before,
        .loader-ring::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 50%;
            border: 3px solid transparent;
        }
        .loader-ring::before {
            border-top-color: #3b82f6;
            border-right-color: #3b82f6;
            animation: ringRotate 1s cubic-bezier(0.5, 0, 0.5, 1) infinite;
        }
        .loader-ring::after {
            border-bottom-color: #8b5cf6;
            border-left-color: #8b5cf6;
            animation: ringRotate 1s cubic-bezier(0.5, 0, 0.5, 1) infinite reverse;
            animation-delay: -0.5s;
        }
        @keyframes ringRotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Loading text */
        .loader-text {
            font-size: 13px;
            font-weight: 500;
            color: #64748b;
            letter-spacing: 0.5px;
        }
        .dark .loader-text {
            color: #94a3b8;
        }

        /* Progress bar */
        .loader-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: rgba(59, 130, 246, 0.1);
            overflow: hidden;
        }
        .loader-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #3b82f6, #8b5cf6);
            animation: progressLoad 1.5s ease-in-out infinite;
            border-radius: 0 3px 3px 0;
        }
        @keyframes progressLoad {
            0% { width: 0%; transform: translateX(0); }
            50% { width: 70%; }
            100% { width: 100%; transform: translateX(0); }
        }

        /* Hide content initially - NO transform to preserve fixed positioning */
        .page-content {
            opacity: 0;
            transition: opacity 0.4s cubic-bezier(0.4, 0, 0.2, 1);
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
<body class="font-sans antialiased bg-stone-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100">

    <!-- Page Loader -->
    <div id="page-loader">
        <div class="loader-logo">
            <div class="loader-logo-inner">
                @if(isset($currentChurch) && $currentChurch->logo)
                    <img src="/storage/{{ $currentChurch->logo }}" alt="">
                @else
                    <span>⛪</span>
                @endif
            </div>
        </div>
        <div class="loader-ring"></div>
        <div class="loader-progress">
            <div class="loader-progress-bar"></div>
        </div>
    </div>
    <script>
        // Hide loader when page is fully ready
        (function() {
            var hideLoader = function() {
                var loader = document.getElementById('page-loader');
                var content = document.querySelector('.page-content');
                if (loader) {
                    loader.classList.add('loaded');
                }
                if (content) {
                    setTimeout(function() {
                        content.classList.add('visible');
                    }, 100);
                }
            };

            // Wait for everything to be ready
            if (document.readyState === 'complete') {
                setTimeout(hideLoader, 200);
            } else {
                window.addEventListener('load', function() {
                    setTimeout(hideLoader, 200);
                });
            }
        })();
    </script>
    <div class="page-content">
    @php
        $userMenuPosition = auth()->check() ? (auth()->user()->settings['menu_position'] ?? '') : '';
        $menuPosition = $userMenuPosition ?: ($currentChurch->menu_position ?? 'left');
    @endphp
    <div x-data="{ sidebarOpen: false }" class="min-h-screen flex max-w-[100vw] overflow-x-clip menu-position-wrapper"
         @keydown.window.prevent.cmd.k="searchOpen = true"
         @keydown.window.prevent.ctrl.k="searchOpen = true"
         @keydown.window.escape="searchOpen = false; fabOpen = false">

        @if($menuPosition === 'top')
        <!-- Top Navigation Bar -->
        <nav class="top-nav-bar hidden lg:flex fixed top-0 left-0 right-0 z-40 h-16 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 items-center justify-between">
            <div class="flex items-center gap-6">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2 flex-shrink-0">
                    @if($currentChurch->logo)
                    <img src="/storage/{{ $currentChurch->logo }}" alt="{{ $currentChurch->name }}" class="w-8 h-8 rounded-lg object-contain">
                    @else
                    <span class="text-xl">⛪</span>
                    @endif
                    <span class="font-bold text-gray-900 dark:text-white">{{ $currentChurch->name ?? 'Ministrify' }}</span>
                </a>
                <div class="flex items-center gap-1 flex-wrap">
                    <a href="{{ route('dashboard') }}" class="px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('dashboard') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">{{ __('Головна') }}</a>
                    @hasChurchRole
                    @if(auth()->user()->canView('people'))<a href="{{ route('people.index') }}" class="px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('people.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">{{ __('Люди') }}</a>@endif
                    @if(auth()->user()->canView('groups'))<a href="{{ route('groups.index') }}" class="px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('groups.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">{{ __('Групи') }}</a>@endif
                    @if(auth()->user()->canView('ministries'))<a href="{{ route('ministries.index') }}" class="px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('ministries.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">{{ __('Команди') }}</a>@endif
                    @if(auth()->user()->canView('events'))<a href="{{ route('schedule') }}" class="px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('schedule') || request()->routeIs('events.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">{{ __('Розклад') }}</a>@endif
                    @if(auth()->user()->canView('finances'))<a href="{{ route('finances.index') }}" class="px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('finances.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">{{ __('Фінанси') }}</a>@endif
                    @if(auth()->user()->canView('announcements'))<a href="{{ route('announcements.index') }}" class="px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('announcements.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">{{ __('Комунікації') }}</a>@endif
                    @if(auth()->user()->canView('reports'))<a href="{{ route('reports.index') }}" class="px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('reports.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">{{ __('Звіти') }}</a>@endif
                    @if(auth()->user()->canView('resources'))<a href="{{ route('resources.index') }}" class="px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('resources.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">{{ __('Ресурси') }}</a>@endif
                    @if(auth()->user()->canView('boards'))<a href="{{ route('boards.index') }}" class="px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('boards.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">{{ __('Завдання') }}</a>@endif
                    @endhasChurchRole
                    @if(auth()->user()->canView('settings'))
                    <a href="{{ route('settings.index') }}" class="px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('settings.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">{{ __('Налаштування') }}</a>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <button @click="searchOpen = true" class="p-2 text-gray-400 hover:text-primary-600 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg></button>
                <button onclick="toggleTheme()" class="p-2 text-gray-400 hover:text-primary-600 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg"><svg class="w-5 h-5 text-yellow-500 dark:hidden" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/></svg><svg class="w-5 h-5 text-indigo-400 hidden dark:block" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/></svg></button>
                <x-user-profile-link />
            </div>
        </nav>
        @endif

        @if($menuPosition === 'bottom')
        <!-- Bottom Dock Navigation -->
        <nav class="bottom-dock-nav hidden lg:flex fixed bottom-0 left-0 right-0 z-40 h-16 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 px-4 items-center justify-center gap-1">
            <a href="{{ route('dashboard') }}" class="flex flex-col items-center px-2 py-2 rounded-xl {{ request()->routeIs('dashboard') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-600' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span class="text-[10px] mt-0.5">{{ __('Головна') }}</span>
            </a>
            @hasChurchRole
            @if(auth()->user()->canView('people'))
            <a href="{{ route('people.index') }}" class="flex flex-col items-center px-2 py-2 rounded-xl {{ request()->routeIs('people.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-600' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM3.751 20.105a8.25 8.25 0 0116.498 0 .75.75 0 01-.437.695A18.683 18.683 0 0112 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 01-.437-.695z" clip-rule="evenodd"/></svg>
                <span class="text-[10px] mt-0.5">{{ __('Люди') }}</span>
            </a>
            @endif
            @if(auth()->user()->canView('groups'))
            <a href="{{ route('groups.index') }}" class="flex flex-col items-center px-2 py-2 rounded-xl {{ request()->routeIs('groups.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-600' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span class="text-[10px] mt-0.5">{{ __('Групи') }}</span>
            </a>
            @endif
            @if(auth()->user()->canView('ministries'))
            <a href="{{ route('ministries.index') }}" class="flex flex-col items-center px-2 py-2 rounded-xl {{ request()->routeIs('ministries.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-600' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <span class="text-[10px] mt-0.5">{{ __('Команди') }}</span>
            </a>
            @endif
            @if(auth()->user()->canView('events'))
            <a href="{{ route('schedule') }}" class="flex flex-col items-center px-2 py-2 rounded-xl {{ request()->routeIs('schedule') || request()->routeIs('events.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-600' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span class="text-[10px] mt-0.5">{{ __('Розклад') }}</span>
            </a>
            @endif
            @if(auth()->user()->canView('finances'))
            <a href="{{ route('finances.index') }}" class="flex flex-col items-center px-2 py-2 rounded-xl {{ request()->routeIs('finances.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-600' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span class="text-[10px] mt-0.5">{{ __('Фінанси') }}</span>
            </a>
            @endif
            @if(auth()->user()->canView('announcements'))
            <a href="{{ route('announcements.index') }}" class="flex flex-col items-center px-2 py-2 rounded-xl {{ request()->routeIs('announcements.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-600' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                <span class="text-[10px] mt-0.5">{{ __('Чат') }}</span>
            </a>
            @endif
            @if(auth()->user()->canView('reports'))
            <a href="{{ route('reports.index') }}" class="flex flex-col items-center px-2 py-2 rounded-xl {{ request()->routeIs('reports.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-600' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span class="text-[10px] mt-0.5">{{ __('Звіти') }}</span>
            </a>
            @endif
            @if(auth()->user()->canView('resources'))
            <a href="{{ route('resources.index') }}" class="flex flex-col items-center px-2 py-2 rounded-xl {{ request()->routeIs('resources.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-600' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                <span class="text-[10px] mt-0.5">{{ __('Ресурси') }}</span>
            </a>
            @endif
            @if(auth()->user()->canView('boards'))
            <a href="{{ route('boards.index') }}" class="flex flex-col items-center px-2 py-2 rounded-xl {{ request()->routeIs('boards.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-600' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                <span class="text-[10px] mt-0.5">{{ __('Завдання') }}</span>
            </a>
            @endif
            @endhasChurchRole
            @if(auth()->user()->canView('settings'))
            <a href="{{ route('settings.index') }}" class="flex flex-col items-center px-2 py-2 rounded-xl {{ request()->routeIs('settings.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-600' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span class="text-[10px] mt-0.5">{{ __('Налашт.') }}</span>
            </a>
            @endif
            <button @click="searchOpen = true" class="flex flex-col items-center px-2 py-2 rounded-xl text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <span class="text-[10px] mt-0.5">{{ __('Пошук') }}</span>
            </button>
            <a href="{{ route('my-profile') }}" class="flex flex-col items-center px-2 py-2 rounded-xl text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700">
                <x-user-avatar size="sm" />
                <span class="text-[10px] mt-0.5">{{ __('Профіль') }}</span>
            </a>
        </nav>
        @endif

        <!-- Desktop Sidebar -->
        <aside x-data="{ collapsed: localStorage.getItem('sidebar_collapsed') === 'true' }"
               x-init="$watch('collapsed', val => { localStorage.setItem('sidebar_collapsed', val); window.dispatchEvent(new CustomEvent('sidebar-toggle', { detail: val })) })"
               :class="collapsed ? 'lg:w-16' : 'lg:w-64'"
               class="desktop-sidebar hidden lg:flex lg:flex-col lg:fixed lg:inset-y-0 z-40 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 transition-all duration-300 overflow-visible">

            <!-- Toggle button on sidebar edge -->
            <button @click="collapsed = !collapsed"
                    class="absolute top-20 z-50 w-6 h-6 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-full shadow-md flex items-center justify-center text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 hover:border-primary-400 dark:hover:border-primary-500 hover:shadow-lg transition-all cursor-pointer {{ $menuPosition === 'right' ? '-left-3' : '-right-3' }}"
                    :title="collapsed ? '{{ __("Розгорнути") }}' : '{{ __("Згорнути") }}'">
                @if($menuPosition === 'right')
                <svg x-show="!collapsed" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                <svg x-show="collapsed" x-cloak class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                @else
                <svg x-show="!collapsed" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                <svg x-show="collapsed" x-cloak class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                @endif
            </button>

            <div class="flex items-center h-16 border-b border-gray-200 dark:border-gray-700 flex-shrink-0" :class="collapsed ? 'justify-center px-2' : 'px-6'">
                <a href="{{ route('dashboard') }}" class="flex items-center flex-shrink-0 min-w-0" :class="collapsed ? '' : 'space-x-2 overflow-hidden'">
                    @if($currentChurch->logo)
                    <img src="/storage/{{ $currentChurch->logo }}" alt="{{ $currentChurch->name }}" class="w-8 h-8 rounded-lg object-contain flex-shrink-0">
                    @else
                    <span class="text-2xl flex-shrink-0">⛪</span>
                    @endif
                    <span x-show="!collapsed" x-cloak class="text-lg font-bold text-gray-900 dark:text-white truncate">{{ $currentChurch->name ?? 'Ministrify' }}</span>
                </a>
            </div>

            <nav id="sidebar-nav" class="flex-1 py-4 space-y-1 overflow-y-auto no-scrollbar" :class="collapsed ? 'px-2' : 'px-4'">
                <a href="{{ route('dashboard') }}" id="nav-dashboard" class="flex items-center py-2.5 text-sm font-medium rounded-xl {{ request()->routeIs('dashboard') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}" :class="collapsed ? 'justify-center px-2' : 'px-3'" :title="collapsed ? '{{ __("Головна") }}' : ''">
                    <svg class="w-5 h-5 flex-shrink-0" :class="collapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span x-show="!collapsed" x-cloak>{{ __('Головна') }}</span>
                </a>
                @hasChurchRole
                @if(auth()->user()->canView('people'))
                <a href="{{ route('people.index') }}" id="nav-people" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl {{ request()->routeIs('people.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}" :title="collapsed ? '{{ __("Люди") }}' : ''">
                    <svg class="w-5 h-5 sidebar-icon flex-shrink-0" :class="collapsed ? '' : 'mr-3'" fill="currentColor" viewBox="0 0 24 24">
                        <path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM3.751 20.105a8.25 8.25 0 0116.498 0 .75.75 0 01-.437.695A18.683 18.683 0 0112 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 01-.437-.695z" clip-rule="evenodd"/>
                    </svg>
                    <span x-show="!collapsed" class="sidebar-text">{{ __('Люди') }}</span>
                </a>
                @endif
                @if(auth()->user()->canView('groups'))
                <a href="{{ route('groups.index') }}" id="nav-groups" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl {{ request()->routeIs('groups.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}" :title="collapsed ? '{{ __("Групи") }}' : ''">
                    <svg class="w-5 h-5 sidebar-icon flex-shrink-0" :class="collapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span x-show="!collapsed" class="sidebar-text">{{ __('Групи') }}</span>
                </a>
                @endif
                @if(auth()->user()->canView('ministries'))
                <a href="{{ route('ministries.index') }}" id="nav-ministries" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl {{ request()->routeIs('ministries.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}" :title="collapsed ? '{{ __("Команди") }}' : ''">
                    <svg class="w-5 h-5 sidebar-icon flex-shrink-0" :class="collapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span x-show="!collapsed" class="sidebar-text">{{ __('Команди') }}</span>
                </a>
                @endif
                @if(auth()->user()->canView('events'))
                <a href="{{ route('schedule') }}" id="nav-schedule" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl {{ request()->routeIs('schedule') || request()->routeIs('events.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}" :title="collapsed ? '{{ __("Розклад") }}' : ''">
                    <svg class="w-5 h-5 sidebar-icon flex-shrink-0" :class="collapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span x-show="!collapsed" class="sidebar-text">{{ __('Розклад') }}</span>
                </a>
                @endif
                @endhasChurchRole
                @hasChurchRole
                @if(auth()->user()->canView('announcements') && auth()->user()->church_id && auth()->user()->churchRole)
                <div x-data="pmBadge()" x-init="startPolling()" @pm-read.window="fetchCount()" class="relative">
                    <a href="{{ route('announcements.index') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl {{ request()->routeIs('announcements.*') || request()->routeIs('pm.*') || request()->routeIs('messages.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}" :title="collapsed ? '{{ __("Комунікації") }}' : ''">
                        <svg class="w-5 h-5 sidebar-icon flex-shrink-0" :class="collapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <span x-show="!collapsed" class="sidebar-text flex-1">{{ __('Комунікації') }}</span>
                        @php $initialPmCount = \App\Models\PrivateMessage::unreadCount(auth()->user()->church_id, auth()->id()); @endphp
                        @if($initialPmCount > 0)
                        <span x-cloak x-show="count > 0" x-text="count > 99 ? '99+' : count" class="sidebar-badge px-2 py-0.5 text-xs font-bold bg-red-500 text-white rounded-full"></span>
                        @endif
                    </a>
                </div>
                @elseif(auth()->user()->canView('announcements'))
                <a href="{{ route('announcements.index') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl {{ request()->routeIs('announcements.*') || request()->routeIs('pm.*') || request()->routeIs('messages.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}" :title="collapsed ? '{{ __("Комунікації") }}' : ''">
                    <svg class="w-5 h-5 sidebar-icon flex-shrink-0" :class="collapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <span x-show="!collapsed" class="sidebar-text">{{ __('Комунікації') }}</span>
                </a>
                @endif
                @endhasChurchRole
                @if(auth()->user()->canView('finances'))
                <a href="{{ route('finances.index') }}" id="nav-finances" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl {{ request()->routeIs('finances.*') || request()->routeIs('expenses.*') || request()->routeIs('donations.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}" :title="collapsed ? '{{ __("Фінанси") }}' : ''">
                    <svg class="w-5 h-5 sidebar-icon flex-shrink-0" :class="collapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span x-show="!collapsed" class="sidebar-text">{{ __('Фінанси') }}</span>
                </a>
                @endif
                @if(auth()->user()->canView('reports'))
                <a href="{{ route('reports.index') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl {{ request()->routeIs('reports.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}" :title="collapsed ? '{{ __("Звіти") }}' : ''">
                    <svg class="w-5 h-5 sidebar-icon flex-shrink-0" :class="collapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span x-show="!collapsed" class="sidebar-text">{{ __('Звіти') }}</span>
                </a>
                @endif
                @if(auth()->user()->canView('resources'))
                <a href="{{ route('resources.index') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl {{ request()->routeIs('resources.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}" :title="collapsed ? '{{ __("Ресурси") }}' : ''">
                    <svg class="w-5 h-5 sidebar-icon flex-shrink-0" :class="collapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                    </svg>
                    <span x-show="!collapsed" class="sidebar-text">{{ __('Ресурси') }}</span>
                </a>
                @endif
                @if(auth()->user()->canView('boards'))
                <a href="{{ route('boards.index') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl {{ request()->routeIs('boards.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}" :title="collapsed ? '{{ __("Завдання") }}' : ''">
                    <svg class="w-5 h-5 sidebar-icon flex-shrink-0" :class="collapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    <span x-show="!collapsed" class="sidebar-text">{{ __('Завдання') }}</span>
                </a>
                @endif
                @hasChurchRole
                @else
                {{-- Pending approval notice for users without role --}}
                <div x-show="!collapsed" class="mt-4 mx-3 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl">
                    <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-amber-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-amber-800 dark:text-amber-200">{{ __('Очікування підтвердження') }}</p>
                            <p class="text-xs text-amber-600 dark:text-amber-400 mt-1">{{ __('Адміністратор має надати вам доступ до системи.') }}</p>
                        </div>
                    </div>
                </div>
                @endhasChurchRole

                @if(auth()->user()->canView('settings'))
                <div class="pt-4 mt-4 border-t border-gray-200 dark:border-gray-700" x-show="!collapsed">
                    <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider sidebar-divider-text">{{ __('Адміністрування') }}</p>
                </div>
                <div x-show="collapsed" class="pt-2 mt-2 border-t border-gray-200 dark:border-gray-700"></div>
                <a href="{{ route('settings.index') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl {{ request()->routeIs('settings.*') || request()->routeIs('website-builder.*') || request()->routeIs('telegram.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}" :title="collapsed ? '{{ __("Налаштування") }}' : ''">
                    <svg class="w-5 h-5 sidebar-icon flex-shrink-0" :class="collapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span x-show="!collapsed" class="sidebar-text">{{ __('Налаштування') }}</span>
                </a>
                @endif

                <a href="{{ route('support.index') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl {{ request()->routeIs('support.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}" :title="collapsed ? '{{ __("Підтримка") }}' : ''">
                    <svg class="w-5 h-5 sidebar-icon flex-shrink-0" :class="collapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span x-show="!collapsed" class="sidebar-text">{{ __('Підтримка') }}</span>
                </a>

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

            <x-church-switcher />

            @if(session('impersonate_church_id') && auth()->user()->isSuperAdmin())
            <div class="flex-shrink-0 px-3 py-3 border-t border-indigo-200 dark:border-indigo-800 bg-indigo-50 dark:bg-indigo-900/30">
                <div class="flex items-center gap-2 text-xs text-indigo-700 dark:text-indigo-300 mb-2">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span class="truncate">{{ __('Церква') }}: <strong>{{ $currentChurch->name }}</strong></span>
                </div>
                <div class="flex gap-1">
                    <form method="POST" action="{{ route('system.exit-church') }}" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full px-2 py-1.5 text-xs font-medium text-indigo-700 dark:text-indigo-300 bg-indigo-100 dark:bg-indigo-800/50 hover:bg-indigo-200 dark:hover:bg-indigo-800 rounded-lg transition-colors">
                            &larr; {{ __('Вийти') }}
                        </button>
                    </form>
                    <a href="{{ route('system.index') }}" class="px-2 py-1.5 text-xs text-indigo-600 dark:text-indigo-400 bg-indigo-100 dark:bg-indigo-800/50 hover:bg-indigo-200 dark:hover:bg-indigo-800 rounded-lg transition-colors">
                        Admin
                    </a>
                </div>
            </div>
            @endif

            @if(session('impersonating_from'))
            <div class="flex-shrink-0 px-3 py-3 border-t border-orange-200 dark:border-orange-800 bg-orange-50 dark:bg-orange-900/30">
                <div class="flex items-center gap-2 text-xs text-orange-700 dark:text-orange-300 mb-2">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <span class="truncate">{{ __('Ви увійшли як') }} <strong>{{ auth()->user()->name }}</strong></span>
                </div>
                <form method="POST" action="{{ route('stop-impersonating') }}">
                    @csrf
                    <button type="submit" class="w-full px-2 py-1.5 text-xs font-medium text-orange-700 dark:text-orange-300 bg-orange-100 dark:bg-orange-800/50 hover:bg-orange-200 dark:hover:bg-orange-800 rounded-lg transition-colors">
                        &larr; {{ __('Повернутись') }}
                    </button>
                </form>
            </div>
            @endif

        </aside>

        <!-- Mobile Sidebar (overlay) -->
        <div x-show="sidebarOpen" x-cloak class="lg:hidden fixed inset-0 z-40 bg-black/50" @click="sidebarOpen = false"></div>
        <aside x-show="sidebarOpen" x-cloak
               x-transition:enter="transform transition ease-out duration-300"
               x-transition:enter-start="{{ $menuPosition === 'right' ? 'translate-x-full' : '-translate-x-full' }}"
               x-transition:enter-end="translate-x-0"
               x-transition:leave="transform transition ease-in duration-200"
               x-transition:leave-start="translate-x-0"
               x-transition:leave-end="{{ $menuPosition === 'right' ? 'translate-x-full' : '-translate-x-full' }}"
               class="lg:hidden fixed inset-y-0 {{ $menuPosition === 'right' ? 'right-0' : 'left-0' }} z-50 w-[calc(100vw-3rem)] max-w-72 bg-white dark:bg-gray-800 shadow-xl flex flex-col">
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
                    {{ __('Головна') }}
                </a>
                @hasChurchRole
                @if(auth()->user()->canView('people'))
                <a href="{{ route('people.index') }}" @click="sidebarOpen = false" class="flex items-center px-4 py-3 text-base font-medium rounded-xl {{ request()->routeIs('people.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-6 h-6 mr-3" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM3.751 20.105a8.25 8.25 0 0116.498 0 .75.75 0 01-.437.695A18.683 18.683 0 0112 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 01-.437-.695z" clip-rule="evenodd"/></svg>
                    {{ __('Люди') }}
                </a>
                @endif
                @if(auth()->user()->canView('groups'))
                <a href="{{ route('groups.index') }}" @click="sidebarOpen = false" class="flex items-center px-4 py-3 text-base font-medium rounded-xl {{ request()->routeIs('groups.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    {{ __('Групи') }}
                </a>
                @endif
                @if(auth()->user()->canView('ministries'))
                <a href="{{ route('ministries.index') }}" @click="sidebarOpen = false" class="flex items-center px-4 py-3 text-base font-medium rounded-xl {{ request()->routeIs('ministries.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    {{ __('Команди') }}
                </a>
                @endif
                @if(auth()->user()->canView('events'))
                <a href="{{ route('schedule') }}" @click="sidebarOpen = false" class="flex items-center px-4 py-3 text-base font-medium rounded-xl {{ request()->routeIs('schedule') || request()->routeIs('events.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    {{ __('Розклад') }}
                </a>
                @endif
                @endhasChurchRole
                @hasChurchRole
                @if(auth()->user()->canView('announcements') && auth()->user()->church_id && auth()->user()->churchRole)
                <div x-data="pmBadge()" x-init="startPolling()" @pm-read.window="fetchCount()">
                    <a href="{{ route('announcements.index') }}" @click="sidebarOpen = false" class="flex items-center justify-between px-4 py-3 text-base font-medium rounded-xl {{ request()->routeIs('announcements.*') || request()->routeIs('pm.*') || request()->routeIs('messages.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        <span class="flex items-center">
                            <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            {{ __('Комунікації') }}
                        </span>
                        <span x-cloak x-show="count > 0" x-text="count > 99 ? '99+' : count" class="px-2 py-0.5 text-xs font-bold bg-red-500 text-white rounded-full"></span>
                    </a>
                </div>
                @elseif(auth()->user()->canView('announcements'))
                <a href="{{ route('announcements.index') }}" @click="sidebarOpen = false" class="flex items-center px-4 py-3 text-base font-medium rounded-xl {{ request()->routeIs('announcements.*') || request()->routeIs('pm.*') || request()->routeIs('messages.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    {{ __('Комунікації') }}
                </a>
                @endif
                @endhasChurchRole
                @if(auth()->user()->canView('finances'))
                <a href="{{ route('finances.index') }}" @click="sidebarOpen = false" class="flex items-center px-4 py-3 text-base font-medium rounded-xl {{ request()->routeIs('finances.*') || request()->routeIs('expenses.*') || request()->routeIs('donations.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    {{ __('Фінанси') }}
                </a>
                @endif
                @if(auth()->user()->canView('reports'))
                <a href="{{ route('reports.index') }}" @click="sidebarOpen = false" class="flex items-center px-4 py-3 text-base font-medium rounded-xl {{ request()->routeIs('reports.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    {{ __('Звіти') }}
                </a>
                @endif
                @if(auth()->user()->canView('resources'))
                <a href="{{ route('resources.index') }}" @click="sidebarOpen = false" class="flex items-center px-4 py-3 text-base font-medium rounded-xl {{ request()->routeIs('resources.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                    {{ __('Ресурси') }}
                </a>
                @endif
                @if(auth()->user()->canView('boards'))
                <a href="{{ route('boards.index') }}" @click="sidebarOpen = false" class="flex items-center px-4 py-3 text-base font-medium rounded-xl {{ request()->routeIs('boards.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    {{ __('Завдання') }}
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
                            <p class="text-sm font-medium text-amber-800 dark:text-amber-200">{{ __('Очікування') }}</p>
                            <p class="text-xs text-amber-600 dark:text-amber-400 mt-1">{{ __('Адміністратор має надати доступ.') }}</p>
                        </div>
                    </div>
                </div>
                @endhasChurchRole
                @if(auth()->user()->canView('settings'))
                <div class="pt-4 mt-4 border-t border-gray-200 dark:border-gray-700"><p class="px-4 text-xs font-semibold text-gray-400 uppercase">{{ __('Адміністрування') }}</p></div>
                <a href="{{ route('settings.index') }}" @click="sidebarOpen = false" class="flex items-center px-4 py-3 text-base font-medium rounded-xl {{ request()->routeIs('settings.*') || request()->routeIs('website-builder.*') || request()->routeIs('telegram.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    {{ __('Налаштування') }}
                </a>
                @endif

                <a href="{{ route('support.index') }}" @click="sidebarOpen = false" class="flex items-center px-4 py-3 text-base font-medium rounded-xl {{ request()->routeIs('support.*') ? 'bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    {{ __('Підтримка') }}
                </a>
                @if(auth()->user()->isSuperAdmin())
                <div class="pt-4 mt-4 border-t border-gray-200 dark:border-gray-700"><p class="px-4 text-xs font-semibold text-red-400 uppercase">System Admin</p></div>
                <a href="{{ route('system.index') }}" @click="sidebarOpen = false" class="flex items-center px-4 py-3 text-base font-medium rounded-xl text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    System Admin
                </a>
                @endif
            </nav>

            <x-church-switcher />

            @if(session('impersonate_church_id') && auth()->user()->isSuperAdmin())
            <div class="flex-shrink-0 px-3 py-3 border-t border-indigo-200 dark:border-indigo-800 bg-indigo-50 dark:bg-indigo-900/30">
                <div class="flex items-center gap-2 text-xs text-indigo-700 dark:text-indigo-300 mb-2">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span class="truncate">Церква: <strong>{{ $currentChurch->name }}</strong></span>
                </div>
                <div class="flex gap-1">
                    <form method="POST" action="{{ route('system.exit-church') }}" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full px-2 py-1.5 text-xs font-medium text-indigo-700 dark:text-indigo-300 bg-indigo-100 dark:bg-indigo-800/50 hover:bg-indigo-200 dark:hover:bg-indigo-800 rounded-lg transition-colors">
                            ← Вийти
                        </button>
                    </form>
                    <a href="{{ route('system.index') }}" class="px-2 py-1.5 text-xs text-indigo-600 dark:text-indigo-400 bg-indigo-100 dark:bg-indigo-800/50 hover:bg-indigo-200 dark:hover:bg-indigo-800 rounded-lg transition-colors">
                        Admin
                    </a>
                </div>
            </div>
            @endif

            @if(session('impersonating_from'))
            <div class="flex-shrink-0 px-3 py-3 border-t border-orange-200 dark:border-orange-800 bg-orange-50 dark:bg-orange-900/30">
                <div class="flex items-center gap-2 text-xs text-orange-700 dark:text-orange-300 mb-2">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <span class="truncate">Ви увійшли як <strong>{{ auth()->user()->name }}</strong></span>
                </div>
                <form method="POST" action="{{ route('stop-impersonating') }}">
                    @csrf
                    <button type="submit" class="w-full px-2 py-1.5 text-xs font-medium text-orange-700 dark:text-orange-300 bg-orange-100 dark:bg-orange-800/50 hover:bg-orange-200 dark:hover:bg-orange-800 rounded-lg transition-colors">
                        ← Повернутись
                    </button>
                </form>
            </div>
            @endif

        </aside>

        <!-- Main Content -->
        <div id="main-content" class="main-content-area flex-1 min-w-0 {{ $menuPosition === 'top' ? 'lg:pt-16' : '' }} {{ $menuPosition === 'bottom' ? 'lg:pb-20' : '' }}"
             x-data="{ sidebarCollapsed: localStorage.getItem('sidebar_collapsed') === 'true' }"
             x-on:sidebar-toggle.window="sidebarCollapsed = $event.detail"
             :class="sidebarCollapsed ? '{{ $menuPosition === 'right' ? 'lg:pr-16' : 'lg:pl-16' }}' : '{{ $menuPosition === 'right' ? 'lg:pr-64' : 'lg:pl-64' }}'">
            <!-- Mobile Header -->
            <header class="lg:hidden sticky top-0 z-30 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm safe-top">
                <div class="flex items-center justify-between h-14 px-3 {{ $menuPosition === 'right' ? 'flex-row-reverse' : '' }}">
                    <button @click="sidebarOpen = true" class="w-11 h-11 flex items-center justify-center {{ $menuPosition === 'right' ? '-mr-2' : '-ml-2' }} text-gray-600 dark:text-gray-300 active:bg-gray-100 dark:active:bg-gray-700 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <button @click="searchOpen = true" class="flex-1 mx-2 flex items-center justify-center space-x-2 h-10 px-3 bg-gray-100 dark:bg-gray-700 rounded-xl active:bg-gray-200 dark:active:bg-gray-600">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Пошук...</span>
                    </button>
                    <div class="flex items-center">
                        <button onclick="toggleTheme()" id="theme-toggle-mobile" class="w-11 h-11 flex items-center justify-center text-gray-400 hover:text-primary-600 active:bg-gray-100 dark:active:bg-gray-700 rounded-xl">
                            <svg id="theme-sun-mobile" class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"/>
                            </svg>
                            <svg id="theme-moon-mobile" class="w-5 h-5 text-indigo-400 hidden" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>
                            </svg>
                        </button>
                        <a href="{{ route('my-schedule') }}" class="w-11 h-11 flex items-center justify-center text-gray-400 hover:text-primary-600 active:bg-gray-100 dark:active:bg-gray-700 rounded-xl" title="Мій розклад">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </a>
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
            <header class="hidden lg:flex sticky top-0 z-30 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 items-center justify-between h-16 px-6">
                <h1 class="text-lg font-semibold text-gray-900 dark:text-white">@yield('title', 'Головна')</h1>
                <div class="flex items-center space-x-4">
                    <!-- Theme Toggle -->
                    <button onclick="toggleTheme()" id="theme-toggle-desktop"
                            class="p-2 text-gray-400 hover:text-primary-600 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                            title="Змінити тему">
                        <svg id="theme-sun-desktop" class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"/>
                        </svg>
                        <svg id="theme-moon-desktop" class="w-5 h-5 text-indigo-400 hidden" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>
                        </svg>
                    </button>
                    <!-- Search Button -->
                    <button @click="searchOpen = true" class="flex items-center space-x-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-xl transition-colors">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Пошук...</span>
                        <kbd class="hidden sm:inline-flex items-center px-2 py-0.5 text-xs text-gray-400 bg-gray-200 dark:bg-gray-600 rounded">/</kbd>
                    </button>
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
                @hasSection('actions')
                <div class="flex flex-wrap items-center gap-2 mb-4">
                    @yield('actions')
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
        @if(auth()->user()->canCreate('people') || auth()->user()->can('create', \App\Models\Event::class) || auth()->user()->canCreate('groups') || auth()->user()->canCreate('finances'))
        <div id="fab-button" class="fixed right-4 bottom-20 lg:bottom-6 z-50" x-data="{ open: false }">
            <div x-show="open" x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="absolute bottom-16 right-0 mb-2 w-48 max-w-[calc(100vw-2rem)] bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                @if(auth()->user()->canCreate('people'))
                <a href="{{ route('people.create') }}" class="flex items-center px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-5 h-5 mr-3 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    <span class="text-gray-700 dark:text-gray-200">Нова людина</span>
                </a>
                @endif
                @can('create', \App\Models\Event::class)
                <a href="{{ route('events.create') }}" class="flex items-center px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-5 h-5 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span class="text-gray-700 dark:text-gray-200">Нова подія</span>
                </a>
                @endcan
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
                                        @if(auth()->user()->canCreate('people'))
                                        <a href="{{ route('people.create') }}" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                                            <span class="w-8 h-8 rounded-lg bg-primary-100 dark:bg-primary-900 flex items-center justify-center mr-3">
                                                <svg class="w-4 h-4 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                                            </span>
                                            <span class="text-gray-700 dark:text-gray-200">Додати людину</span>
                                            <kbd class="ml-auto px-2 py-0.5 text-xs text-gray-400 bg-gray-100 dark:bg-gray-700 rounded">N</kbd>
                                        </a>
                                        @endif
                                        @if(auth()->user()->canCreate('events'))
                                        <a href="{{ route('events.create') }}" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                                            <span class="w-8 h-8 rounded-lg bg-green-100 dark:bg-green-900 flex items-center justify-center mr-3">
                                                <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            </span>
                                            <span class="text-gray-700 dark:text-gray-200">Створити подію</span>
                                            <kbd class="ml-auto px-2 py-0.5 text-xs text-gray-400 bg-gray-100 dark:bg-gray-700 rounded">E</kbd>
                                        </a>
                                        @endif
                                        @if(auth()->user()->canCreate('groups'))
                                        <a href="{{ route('groups.create') }}" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                                            <span class="w-8 h-8 rounded-lg bg-purple-100 dark:bg-purple-900 flex items-center justify-center mr-3">
                                                <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            </span>
                                            <span class="text-gray-700 dark:text-gray-200">Створити групу</span>
                                            <kbd class="ml-auto px-2 py-0.5 text-xs text-gray-400 bg-gray-100 dark:bg-gray-700 rounded">G</kbd>
                                        </a>
                                        @endif
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

    <!-- Alpine.js - loaded after page scripts to ensure x-data functions are defined -->
    <script src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Toast Notifications -->
    @include('components.toast')


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
        // Service Worker Registration with proper update flow
        if ('serviceWorker' in navigator) {
            let refreshing = false;

            // Auto-reload when new SW takes control
            navigator.serviceWorker.addEventListener('controllerchange', () => {
                if (!refreshing) {
                    refreshing = true;
                    window.location.reload();
                }
            });

            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(registration => {
                        // Auto-update: if SW already waiting, activate immediately
                        if (registration.waiting) {
                            registration.waiting.postMessage({ type: 'SKIP_WAITING' });
                        }

                        // Listen for new SW installing → auto-activate
                        registration.addEventListener('updatefound', () => {
                            const newWorker = registration.installing;
                            newWorker.addEventListener('statechange', () => {
                                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                    newWorker.postMessage({ type: 'SKIP_WAITING' });
                                }
                            });
                        });

                        // Periodic update check — every 60 minutes
                        setInterval(() => {
                            registration.update();
                        }, 60 * 60 * 1000);
                    })
                    .catch(() => {});
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

    <script>
        function toggleTheme() {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            updateThemeIcons();
        }
        function updateThemeIcons() {
            const isDark = document.documentElement.classList.contains('dark');
            // Desktop icons
            const sunDesktop = document.getElementById('theme-sun-desktop');
            const moonDesktop = document.getElementById('theme-moon-desktop');
            if (sunDesktop) sunDesktop.classList.toggle('hidden', isDark);
            if (moonDesktop) moonDesktop.classList.toggle('hidden', !isDark);
            // Mobile icons
            const sunMobile = document.getElementById('theme-sun-mobile');
            const moonMobile = document.getElementById('theme-moon-mobile');
            if (sunMobile) sunMobile.classList.toggle('hidden', isDark);
            if (moonMobile) moonMobile.classList.toggle('hidden', !isDark);
        }
        updateThemeIcons();
    </script>

    <!-- Driver.js Dark Mode + Mobile Styles -->
    <style>
        .driver-popover {
            min-width: 280px;
            max-width: calc(100vw - 2rem);
            word-break: normal;
            overflow-wrap: break-word;
        }
        .driver-popover .driver-popover-title {
            font-size: 16px;
            font-weight: 600;
            white-space: normal;
        }
        .driver-popover .driver-popover-description {
            font-size: 14px;
            white-space: normal;
            word-break: normal;
        }
        .driver-popover .driver-popover-footer {
            flex-wrap: wrap;
            gap: 8px;
        }
        .driver-popover .driver-popover-footer button {
            white-space: nowrap;
        }
        .driver-popover button.driver-popover-next-btn,
        .driver-popover button.driver-popover-close-btn-text {
            background-color: var(--driver-primary, #2563eb);
            text-shadow: none;
        }
        @media (max-width: 640px) {
            .driver-popover {
                min-width: 260px;
                max-width: calc(100vw - 1.5rem);
                font-size: 13px;
            }
            .driver-popover .driver-popover-title {
                font-size: 15px;
            }
            .driver-popover .driver-popover-description {
                font-size: 13px;
            }
        }
        .dark .driver-popover {
            background-color: #1f2937;
            color: #f3f4f6;
        }
        .dark .driver-popover .driver-popover-title {
            color: #f9fafb;
        }
        .dark .driver-popover .driver-popover-description {
            color: #d1d5db;
        }
        .dark .driver-popover .driver-popover-progress-text {
            color: #9ca3af;
        }
        .dark .driver-popover button.driver-popover-prev-btn {
            color: #d1d5db;
            border-color: #4b5563;
        }
        .dark .driver-popover button.driver-popover-prev-btn:hover {
            background-color: #374151;
        }
    </style>

    <!-- Guided Tour Script -->
    <script>
        var __tourCompleted = {{ json_encode((auth()->user()->preferences['tour_completed'] ?? false) ? true : false) }};

        function buildTourSteps() {
            var steps = [];

            function isVisible(el) {
                if (!el) return false;
                var rect = el.getBoundingClientRect();
                if (rect.width === 0 && rect.height === 0) return false;
                var style = window.getComputedStyle(el);
                return style.display !== 'none' && style.visibility !== 'hidden' && style.opacity !== '0';
            }

            function add(id, title, desc, side, align) {
                var el = document.getElementById(id);
                if (el && isVisible(el)) {
                    steps.push({ element: '#' + id, popover: { title: title, description: desc, side: side || 'bottom', align: align || 'start' } });
                }
            }

            // === Dashboard ===
            add('sidebar-nav', 'Навігація', 'Це головне меню. Звідси ви потрапите до будь-якого розділу системи.', 'right', 'start');
            add('stats-grid', 'Статистика', 'Ключові показники вашої церкви: люди, служіння, групи та події. Натисніть на картку, щоб перейти до розділу.', 'bottom', 'center');
            add('stat-people', 'Картка "Люди"', 'Загальна кількість людей, тренд за 3 місяці та розподіл за віком.', 'bottom', 'start');
            add('stat-events', 'Картка "Події"', 'Скільки подій заплановано та проведено цього місяця.', 'bottom', 'end');

            // === Sidebar nav items ===
            add('nav-people', 'Люди', 'База людей та контактів. Додавайте, редагуйте, фільтруйте за різними критеріями.', 'right', 'start');
            add('nav-groups', 'Групи', 'Домашні групи та малі групи. Відстежуйте учасників, лідерів та зустрічі.', 'right', 'start');
            add('nav-ministries', 'Команди', 'Служіння та команди волонтерів. Організовуйте людей за напрямками роботи.', 'right', 'start');
            add('nav-schedule', 'Розклад', 'Календар подій та зустрічей. Плануйте, запрошуйте та відстежуйте відвідуваність.', 'right', 'start');
            add('nav-finances', 'Фінанси', 'Облік доходів та витрат, баланс каси, фінансові звіти по категоріях.', 'right', 'start');

            // === People page ===
            add('people-add-btn', 'Додати людину', 'Натисніть, щоб додати нову людину до бази. Вкажіть ім\'я, контакти, день народження тощо.', 'bottom', 'end');
            add('people-search-bar', 'Пошук та фільтри', 'Шукайте за ім\'ям, телефоном чи email. Використовуйте фільтри для вибірки за статтю, служінням, роллю та іншими критеріями.', 'bottom', 'center');
            add('people-filter-btn', 'Розширені фільтри', 'Відкрийте панель фільтрів: стать, сімейний стан, служіння, роль, пастор, дата народження.', 'bottom', 'start');

            // === Groups page ===
            add('groups-stats', 'Статистика груп', 'Загальна кількість груп, учасників та середній розмір групи.', 'bottom', 'center');
            add('groups-table', 'Таблиця груп', 'Список усіх груп з лідерами, статусом та кількістю учасників. Натисніть на групу, щоб переглянути деталі.', 'top', 'center');

            // === Ministries page ===
            add('ministries-grid', 'Картки команд', 'Кожна картка — окреме служіння з лідером та кількістю учасників. Натисніть "Відкрити", щоб побачити деталі.', 'bottom', 'center');

            // === Schedule page ===
            add('schedule-options', 'Перегляд подій', 'Перемикайте між списком і календарем. Фільтруйте події за командою.', 'bottom', 'center');
            add('events-list', 'Список подій', 'Усі заплановані та минулі події. Натисніть на подію, щоб побачити деталі та відвідуваність.', 'top', 'center');

            // === Finances page ===
            add('finance-actions', 'Фінансові операції', 'Три кнопки: зелена — додати надходження, червона — витрату, жовта — обмін валют.', 'bottom', 'end');
            add('finance-period', 'Вибір періоду', 'Оберіть рік та місяць для перегляду фінансів. Можна подивитися за весь рік або конкретний місяць.', 'bottom', 'start');
            add('finance-balance', 'Баланс каси', 'Поточний залишок по кожній валюті. Синій — додатний, помаранчевий — від\'ємний баланс.', 'bottom', 'center');

            // === Settings page ===
            add('settings-tabs', 'Розділи налаштувань', 'Загальні, тема, сайт, інтеграції, категорії, фінанси, користувачі, права доступу та журнал дій — все тут.', 'bottom', 'center');

            // === FAB & Tour restart ===
            add('fab-button', 'Швидке створення', 'Натисніть "+", щоб швидко додати людину, подію, групу або витрату з будь-якої сторінки.', 'left', 'end');
            add('tour-restart-btn', 'Повторити тур', 'Ви завжди можете запустити цей тур знову, натиснувши тут.', 'right', 'start');

            return steps;
        }

        function saveTourCompleted() {
            fetch('{{ route("preferences.update") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ tour_completed: true })
            }).catch(function() {});
        }

        function startGuidedTour() {
            var steps = buildTourSteps();
            if (steps.length === 0) return;

            var driver = window.driver.js.driver({
                showProgress: true,
                animate: true,
                overlayColor: 'rgba(0, 0, 0, 0.6)',
                stagePadding: 8,
                stageRadius: 12,
                popoverOffset: 12,
                showButtons: ['next', 'previous', 'close'],
                nextBtnText: 'Далі',
                prevBtnText: 'Назад',
                doneBtnText: 'Готово',
                progressText: '@{{current}} з @{{total}}',
                steps: steps,
                onDestroyStarted: function() {
                    if (!driver.hasNextStep()) {
                        saveTourCompleted();
                    }
                    driver.destroy();
                }
            });

            driver.drive();
        }

        // Auto-start tour — temporarily disabled
        // document.addEventListener('DOMContentLoaded', function() {
        //     if (!__tourCompleted) {
        //         setTimeout(function() { startGuidedTour(); }, 800);
        //     }
        // });
    </script>
</body>
</html>
