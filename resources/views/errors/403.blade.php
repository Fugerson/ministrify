<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <script>
        (function(){
            var t = localStorage.getItem('theme');
            if (t === 'dark' || (t === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 - {{ __('app.access_denied') ?? 'Доступ заборонено' }} | Ministrify</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
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
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            33% { transform: translateY(-8px) rotate(2deg); }
            66% { transform: translateY(4px) rotate(-1deg); }
        }
        @keyframes pulse-ring {
            0% { transform: scale(1); opacity: 0.4; }
            50% { transform: scale(1.15); opacity: 0; }
            100% { transform: scale(1); opacity: 0; }
        }
        @keyframes fade-in-up {
            0% { opacity: 0; transform: translateY(20px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        @keyframes shimmer {
            0% { background-position: -200% center; }
            100% { background-position: 200% center; }
        }
        @keyframes orbit1 {
            0% { transform: rotate(0deg) translateX(80px) rotate(0deg); }
            100% { transform: rotate(360deg) translateX(80px) rotate(-360deg); }
        }
        @keyframes orbit2 {
            0% { transform: rotate(120deg) translateX(100px) rotate(-120deg); }
            100% { transform: rotate(480deg) translateX(100px) rotate(-480deg); }
        }
        @keyframes orbit3 {
            0% { transform: rotate(240deg) translateX(65px) rotate(-240deg); }
            100% { transform: rotate(600deg) translateX(65px) rotate(-600deg); }
        }
        .animate-float { animation: float 4s ease-in-out infinite; }
        .animate-pulse-ring { animation: pulse-ring 2.5s ease-out infinite; }
        .animate-fade-in-up { animation: fade-in-up 0.6s ease-out both; }
        .delay-100 { animation-delay: 0.1s; }
        .delay-200 { animation-delay: 0.2s; }
        .delay-300 { animation-delay: 0.3s; }
        .delay-400 { animation-delay: 0.4s; }
        .animate-shimmer {
            background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.08) 50%, transparent 100%);
            background-size: 200% 100%;
            animation: shimmer 3s ease-in-out infinite;
        }
        .animate-orbit1 { animation: orbit1 12s linear infinite; }
        .animate-orbit2 { animation: orbit2 18s linear infinite; }
        .animate-orbit3 { animation: orbit3 10s linear infinite; }
    </style>
</head>
<body class="font-sans antialiased min-h-screen overflow-hidden">
    {{-- Background --}}
    <div class="fixed inset-0 bg-gradient-to-br from-rose-50 via-white to-orange-50 dark:from-gray-950 dark:via-gray-900 dark:to-gray-950"></div>

    {{-- Decorative bg blobs --}}
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-32 -right-32 w-96 h-96 bg-red-200/30 dark:bg-red-900/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-32 -left-32 w-96 h-96 bg-orange-200/30 dark:bg-orange-900/10 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-rose-100/20 dark:bg-rose-900/5 rounded-full blur-3xl"></div>
    </div>

    {{-- Grid pattern overlay --}}
    <div class="fixed inset-0 opacity-[0.03] dark:opacity-[0.05] pointer-events-none"
         style="background-image: radial-gradient(circle, currentColor 1px, transparent 1px); background-size: 32px 32px;"></div>

    <div class="relative min-h-screen flex flex-col items-center justify-center px-4 py-8">
        <div class="max-w-lg w-full text-center">

            {{-- Shield icon with orbiting elements --}}
            <div class="relative mx-auto w-40 h-40 mb-8 animate-fade-in-up">
                {{-- Pulse ring --}}
                <div class="absolute inset-0 rounded-full bg-red-400/20 dark:bg-red-500/10 animate-pulse-ring"></div>

                {{-- Orbiting particles --}}
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="animate-orbit1">
                        <div class="w-2 h-2 rounded-full bg-red-300 dark:bg-red-500/60"></div>
                    </div>
                </div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="animate-orbit2">
                        <div class="w-1.5 h-1.5 rounded-full bg-orange-300 dark:bg-orange-500/50"></div>
                    </div>
                </div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="animate-orbit3">
                        <div class="w-1 h-1 rounded-full bg-rose-400 dark:bg-rose-400/60"></div>
                    </div>
                </div>

                {{-- Main icon --}}
                <div class="absolute inset-0 flex items-center justify-center animate-float">
                    <div class="w-28 h-28 bg-gradient-to-br from-red-500 to-rose-600 dark:from-red-600 dark:to-rose-700 rounded-3xl shadow-2xl shadow-red-500/30 dark:shadow-red-900/40 flex items-center justify-center rotate-3">
                        <svg class="w-14 h-14 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v.01M9 17h6M12 2l7 4v5c0 5.25-3.5 9.74-7 11-3.5-1.26-7-5.75-7-11V6l7-4z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Error code --}}
            <div class="animate-fade-in-up delay-100">
                <p class="text-8xl sm:text-9xl font-extrabold text-transparent bg-clip-text bg-gradient-to-b from-red-300/40 to-red-500/10 dark:from-red-400/30 dark:to-red-600/5 select-none leading-none mb-1">
                    403
                </p>
            </div>

            {{-- Glass card --}}
            <div class="animate-fade-in-up delay-200 relative mx-auto max-w-sm -mt-6">
                <div class="relative bg-white/70 dark:bg-gray-800/60 backdrop-blur-xl rounded-2xl border border-white/80 dark:border-gray-700/50 shadow-xl shadow-gray-200/50 dark:shadow-black/20 p-6 overflow-hidden">
                    {{-- Shimmer effect --}}
                    <div class="absolute inset-0 animate-shimmer"></div>

                    <div class="relative">
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                            Доступ заборонено
                        </h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                            У вас немає дозволу для перегляду цієї сторінки. Зверніться до адміністратора, якщо вважаєте, що це помилка.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="animate-fade-in-up delay-300 flex flex-col sm:flex-row gap-3 justify-center mt-8">
                <a href="javascript:history.back()"
                   class="group inline-flex items-center justify-center px-6 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm border border-gray-200/80 dark:border-gray-700/80 rounded-xl hover:bg-white dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 transition-all shadow-sm hover:shadow-md">
                    <svg class="w-4 h-4 mr-2 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Назад
                </a>
                <a href="{{ url('/dashboard') }}"
                   class="group inline-flex items-center justify-center px-6 py-3 text-sm font-medium text-white bg-gradient-to-r from-red-500 to-rose-600 hover:from-red-600 hover:to-rose-700 rounded-xl transition-all shadow-lg shadow-red-500/25 hover:shadow-red-500/40 hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-4 h-4 mr-2 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    На головну
                </a>
            </div>
        </div>

        {{-- Footer --}}
        <p class="animate-fade-in-up delay-400 mt-16 text-xs text-gray-400 dark:text-gray-600">&copy; {{ date('Y') }} Ministrify</p>
    </div>
</body>
</html>
