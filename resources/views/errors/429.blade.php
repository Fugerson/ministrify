<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" x-data="{ darkMode: localStorage.getItem('theme') === 'dark' || (localStorage.getItem('theme') === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches) }" x-bind:class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>429 - {{ __('app.err_429_title') }} | Ministrify</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: { 50: '#eff6ff', 100: '#dbeafe', 200: '#bfdbfe', 300: '#93c5fd', 400: '#60a5fa', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8' }
                    },
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] }
                }
            }
        }
    </script>
</head>
<body class="font-sans antialiased bg-gradient-to-br from-orange-50 via-white to-amber-50 dark:from-gray-900 dark:via-gray-900 dark:to-gray-800 min-h-screen">
    <div class="min-h-screen flex flex-col items-center justify-center px-4 py-8">
        <div class="max-w-md w-full text-center">
            {{-- Icon --}}
            <div class="mx-auto w-24 h-24 bg-orange-100 dark:bg-orange-900/30 rounded-full flex items-center justify-center mb-6 shadow-lg shadow-orange-200/50 dark:shadow-orange-900/30">
                <svg class="w-12 h-12 text-orange-500 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/>
                </svg>
            </div>

            {{-- Error code --}}
            <p class="text-7xl font-bold text-orange-500/20 dark:text-orange-400/20 mb-2">429</p>

            {{-- Title --}}
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">
                {{ __('app.err_429_title') }}
            </h1>

            {{-- Description --}}
            <p class="text-gray-500 dark:text-gray-400 mb-8 leading-relaxed">
                {{ __('app.err_429_desc') }}
            </p>

            {{-- Actions --}}
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="javascript:history.back()"
                   class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    {{ __('app.err_back') }}
                </a>
                <a href="javascript:location.reload()"
                   class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 rounded-xl transition-all shadow-md shadow-primary-500/30">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    {{ __('app.err_try_again') }}
                </a>
            </div>
        </div>

        {{-- Footer --}}
        <p class="mt-12 text-sm text-gray-400 dark:text-gray-500">&copy; {{ date('Y') }} Ministrify</p>
    </div>
</body>
</html>
