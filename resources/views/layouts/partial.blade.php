<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" x-data="{
    darkMode: localStorage.getItem('theme') !== 'light'
}" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Ministrify')</title>

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

    <style>
        [x-cloak] { display: none !important; }
        html { scroll-behavior: smooth; -webkit-overflow-scrolling: touch; }
        body { overflow-wrap: break-word; word-break: break-word; }
        .dark body { background-color: #111827; }
        input[type="number"]::-webkit-outer-spin-button,
        input[type="number"]::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        input[type="number"] { -moz-appearance: textfield; }
        @media screen and (max-width: 768px) {
            input, select, textarea { font-size: 16px !important; }
        }
        * { scrollbar-width: thin; scrollbar-color: rgba(156, 163, 175, 0.5) transparent; }
        .dark * { scrollbar-color: rgba(75, 85, 99, 0.6) transparent; }
    </style>

    @stack('styles')
    @include('partials.design-themes')
    @livewireStyles
</head>
<body class="font-sans antialiased bg-stone-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
    <div class="p-4 sm:p-6">
        @yield('content')
    </div>

    <script>
        window.onPageReady = function(fn) {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', fn, { once: true });
            } else {
                fn();
            }
        };
    </script>

    @stack('scripts')
    @livewireScripts

    <!-- Alpine.js Collapse plugin -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>

    @include('components.toast')
    @include('components.confirm-modal')
    @include('components.ajax-helpers')

    @if(session('success'))
        <script>onPageReady(function() { showToast('success', @js(session('success'))); });</script>
    @endif
    @if(session('error'))
        <script>onPageReady(function() { showToast('error', @js(session('error'))); });</script>
    @endif
</body>
</html>
