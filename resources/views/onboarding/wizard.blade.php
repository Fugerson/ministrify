<!DOCTYPE html>
<html lang="uk" x-data="themeManager()" :class="{ 'dark': darkMode }">
<head>
    <script>
        (function() {
            const theme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (theme === 'dark' || (!theme && prefersDark)) {
                document.documentElement.classList.add('dark');
            }
            // Apply saved color theme
            const colorTheme = localStorage.getItem('colorTheme') || 'forest';
            document.documentElement.setAttribute('data-theme', colorTheme);
        })();
    </script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ChurchHub - Налаштування</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }

        /* ==================== THEME SYSTEM ==================== */
        :root {
            /* Default: Forest (Planning Center style) */
            --theme-50: #f0fdf4;
            --theme-100: #dcfce7;
            --theme-200: #bbf7d0;
            --theme-300: #86efac;
            --theme-400: #4ade80;
            --theme-500: #22c55e;
            --theme-600: #16a34a;
            --theme-700: #15803d;
            --theme-800: #166534;
            --theme-900: #14532d;
            --theme-gradient-start: #dcfce7;
            --theme-gradient-mid: #bbf7d0;
            --theme-gradient-end: #86efac;
            --theme-dark-start: #14532d;
            --theme-dark-mid: #166534;
            --theme-dark-end: #15803d;
            --theme-pattern-color: 34, 197, 94;
            --theme-confetti: #22c55e, #16a34a, #4ade80;
        }

        /* Ocean Theme */
        [data-theme="ocean"] {
            --theme-50: #eff6ff;
            --theme-100: #dbeafe;
            --theme-200: #bfdbfe;
            --theme-300: #93c5fd;
            --theme-400: #60a5fa;
            --theme-500: #3b82f6;
            --theme-600: #2563eb;
            --theme-700: #1d4ed8;
            --theme-800: #1e40af;
            --theme-900: #1e3a8a;
            --theme-gradient-start: #dbeafe;
            --theme-gradient-mid: #bfdbfe;
            --theme-gradient-end: #93c5fd;
            --theme-dark-start: #1e3a8a;
            --theme-dark-mid: #1e40af;
            --theme-dark-end: #1d4ed8;
            --theme-pattern-color: 59, 130, 246;
            --theme-confetti: #3b82f6, #2563eb, #60a5fa;
        }

        /* Sunset Theme */
        [data-theme="sunset"] {
            --theme-50: #fff7ed;
            --theme-100: #ffedd5;
            --theme-200: #fed7aa;
            --theme-300: #fdba74;
            --theme-400: #fb923c;
            --theme-500: #f97316;
            --theme-600: #ea580c;
            --theme-700: #c2410c;
            --theme-800: #9a3412;
            --theme-900: #7c2d12;
            --theme-gradient-start: #ffedd5;
            --theme-gradient-mid: #fed7aa;
            --theme-gradient-end: #fdba74;
            --theme-dark-start: #7c2d12;
            --theme-dark-mid: #9a3412;
            --theme-dark-end: #c2410c;
            --theme-pattern-color: 249, 115, 22;
            --theme-confetti: #f97316, #ea580c, #fb923c;
        }

        /* Lavender Theme */
        [data-theme="lavender"] {
            --theme-50: #faf5ff;
            --theme-100: #f3e8ff;
            --theme-200: #e9d5ff;
            --theme-300: #d8b4fe;
            --theme-400: #c084fc;
            --theme-500: #a855f7;
            --theme-600: #9333ea;
            --theme-700: #7e22ce;
            --theme-800: #6b21a8;
            --theme-900: #581c87;
            --theme-gradient-start: #f3e8ff;
            --theme-gradient-mid: #e9d5ff;
            --theme-gradient-end: #d8b4fe;
            --theme-dark-start: #581c87;
            --theme-dark-mid: #6b21a8;
            --theme-dark-end: #7e22ce;
            --theme-pattern-color: 168, 85, 247;
            --theme-confetti: #a855f7, #9333ea, #c084fc;
        }

        /* Rose Theme */
        [data-theme="rose"] {
            --theme-50: #fff1f2;
            --theme-100: #ffe4e6;
            --theme-200: #fecdd3;
            --theme-300: #fda4af;
            --theme-400: #fb7185;
            --theme-500: #f43f5e;
            --theme-600: #e11d48;
            --theme-700: #be123c;
            --theme-800: #9f1239;
            --theme-900: #881337;
            --theme-gradient-start: #ffe4e6;
            --theme-gradient-mid: #fecdd3;
            --theme-gradient-end: #fda4af;
            --theme-dark-start: #881337;
            --theme-dark-mid: #9f1239;
            --theme-dark-end: #be123c;
            --theme-pattern-color: 244, 63, 94;
            --theme-confetti: #f43f5e, #e11d48, #fb7185;
        }

        /* Teal Theme */
        [data-theme="teal"] {
            --theme-50: #f0fdfa;
            --theme-100: #ccfbf1;
            --theme-200: #99f6e4;
            --theme-300: #5eead4;
            --theme-400: #2dd4bf;
            --theme-500: #14b8a6;
            --theme-600: #0d9488;
            --theme-700: #0f766e;
            --theme-800: #115e59;
            --theme-900: #134e4a;
            --theme-gradient-start: #ccfbf1;
            --theme-gradient-mid: #99f6e4;
            --theme-gradient-end: #5eead4;
            --theme-dark-start: #134e4a;
            --theme-dark-mid: #115e59;
            --theme-dark-end: #0f766e;
            --theme-pattern-color: 20, 184, 166;
            --theme-confetti: #14b8a6, #0d9488, #2dd4bf;
        }

        /* Slate/Minimal Theme */
        [data-theme="slate"] {
            --theme-50: #f8fafc;
            --theme-100: #f1f5f9;
            --theme-200: #e2e8f0;
            --theme-300: #cbd5e1;
            --theme-400: #94a3b8;
            --theme-500: #64748b;
            --theme-600: #475569;
            --theme-700: #334155;
            --theme-800: #1e293b;
            --theme-900: #0f172a;
            --theme-gradient-start: #f1f5f9;
            --theme-gradient-mid: #e2e8f0;
            --theme-gradient-end: #cbd5e1;
            --theme-dark-start: #0f172a;
            --theme-dark-mid: #1e293b;
            --theme-dark-end: #334155;
            --theme-pattern-color: 100, 116, 139;
            --theme-confetti: #64748b, #475569, #94a3b8;
        }

        /* ==================== FESTIVE THEMES ==================== */

        /* Christmas Theme */
        [data-theme="christmas"] {
            --theme-50: #fef2f2;
            --theme-100: #fee2e2;
            --theme-200: #fecaca;
            --theme-300: #fca5a5;
            --theme-400: #f87171;
            --theme-500: #dc2626;
            --theme-600: #b91c1c;
            --theme-700: #991b1b;
            --theme-800: #7f1d1d;
            --theme-900: #450a0a;
            --theme-gradient-start: #fee2e2;
            --theme-gradient-mid: #dcfce7;
            --theme-gradient-end: #fee2e2;
            --theme-dark-start: #450a0a;
            --theme-dark-mid: #14532d;
            --theme-dark-end: #450a0a;
            --theme-pattern-color: 220, 38, 38;
            --theme-confetti: #dc2626, #16a34a, #fbbf24, #ffffff;
        }

        /* Easter Theme */
        [data-theme="easter"] {
            --theme-50: #fdf4ff;
            --theme-100: #fae8ff;
            --theme-200: #f5d0fe;
            --theme-300: #f0abfc;
            --theme-400: #e879f9;
            --theme-500: #d946ef;
            --theme-600: #c026d3;
            --theme-700: #a21caf;
            --theme-800: #86198f;
            --theme-900: #701a75;
            --theme-gradient-start: #fae8ff;
            --theme-gradient-mid: #dbeafe;
            --theme-gradient-end: #dcfce7;
            --theme-dark-start: #701a75;
            --theme-dark-mid: #1e3a8a;
            --theme-dark-end: #14532d;
            --theme-pattern-color: 217, 70, 239;
            --theme-confetti: #d946ef, #60a5fa, #4ade80, #fbbf24, #fb7185;
        }

        /* New Year Theme */
        [data-theme="newyear"] {
            --theme-50: #fefce8;
            --theme-100: #fef9c3;
            --theme-200: #fef08a;
            --theme-300: #fde047;
            --theme-400: #facc15;
            --theme-500: #eab308;
            --theme-600: #ca8a04;
            --theme-700: #a16207;
            --theme-800: #854d0e;
            --theme-900: #713f12;
            --theme-gradient-start: #1e1b4b;
            --theme-gradient-mid: #312e81;
            --theme-gradient-end: #1e1b4b;
            --theme-dark-start: #1e1b4b;
            --theme-dark-mid: #312e81;
            --theme-dark-end: #3730a3;
            --theme-pattern-color: 234, 179, 8;
            --theme-confetti: #eab308, #fde047, #ffffff, #c084fc, #60a5fa;
        }

        /* ==================== APPLY THEME COLORS ==================== */
        body {
            background: linear-gradient(135deg, var(--theme-gradient-start) 0%, var(--theme-gradient-mid) 50%, var(--theme-gradient-end) 100%);
            min-height: 100vh;
        }

        .dark body {
            background: linear-gradient(135deg, var(--theme-dark-start) 0%, var(--theme-dark-mid) 50%, var(--theme-dark-end) 100%);
        }

        /* New Year special gradient */
        [data-theme="newyear"] body {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 30%, #1e1b4b 50%, #312e81 70%, #1e1b4b 100%);
        }

        /* Topo pattern with theme color */
        .topo-pattern {
            position: fixed;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='rgba(var(--theme-pattern-color), 0.08)' fill-rule='evenodd'/%3E%3C/svg%3E");
            pointer-events: none;
            z-index: 0;
        }

        /* Christmas snowflakes */
        [data-theme="christmas"] .topo-pattern {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'%3E%3Ctext x='10' y='30' font-size='20' fill='%23ffffff30'%3E❄%3C/text%3E%3Ctext x='60' y='70' font-size='15' fill='%23ffffff20'%3E❄%3C/text%3E%3Ctext x='30' y='90' font-size='12' fill='%23ffffff25'%3E❄%3C/text%3E%3Ctext x='80' y='20' font-size='18' fill='%23ffffff15'%3E❄%3C/text%3E%3C/svg%3E");
        }

        /* New Year stars */
        [data-theme="newyear"] .topo-pattern {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'%3E%3Ctext x='10' y='30' font-size='14' fill='%23eab30840'%3E✨%3C/text%3E%3Ctext x='60' y='70' font-size='10' fill='%23eab30830'%3E⭐%3C/text%3E%3Ctext x='30' y='90' font-size='8' fill='%23eab30835'%3E🎆%3C/text%3E%3Ctext x='80' y='20' font-size='12' fill='%23eab30825'%3E🎇%3C/text%3E%3Ctext x='45' y='50' font-size='16' fill='%23eab30820'%3E✨%3C/text%3E%3C/svg%3E");
        }

        /* Easter - Christian symbols */
        [data-theme="easter"] .topo-pattern {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'%3E%3Ctext x='10' y='30' font-size='16' fill='%23d946ef30'%3E✝%3C/text%3E%3Ctext x='60' y='70' font-size='12' fill='%23d946ef20'%3E🕊%3C/text%3E%3Ctext x='30' y='90' font-size='10' fill='%23d946ef25'%3E☀%3C/text%3E%3Ctext x='80' y='20' font-size='14' fill='%23d946ef15'%3E✝%3C/text%3E%3Ctext x='45' y='55' font-size='12' fill='%23d946ef18'%3E🕊%3C/text%3E%3C/svg%3E");
        }

        /* Dynamic primary colors using CSS variables */
        .theme-primary { color: var(--theme-500); }
        .theme-primary-dark { color: var(--theme-600); }
        .theme-primary-light { color: var(--theme-400); }
        .theme-bg-50 { background-color: var(--theme-50); }
        .theme-bg-100 { background-color: var(--theme-100); }
        .theme-bg-500 { background-color: var(--theme-500); }
        .theme-border { border-color: var(--theme-200); }

        .theme-gradient {
            background: linear-gradient(135deg, var(--theme-500) 0%, var(--theme-700) 100%);
        }

        .theme-gradient-light {
            background: linear-gradient(135deg, var(--theme-400) 0%, var(--theme-600) 100%);
        }

        .card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
        }

        .dark .card {
            background: rgba(30, 41, 59, 0.98);
        }

        /* New Year dark card */
        [data-theme="newyear"] .card {
            background: rgba(30, 27, 75, 0.95);
            border: 1px solid rgba(234, 179, 8, 0.2);
        }

        .step-enter {
            animation: stepEnter 0.5s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }

        @keyframes stepEnter {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .progress-bar {
            background: linear-gradient(90deg, var(--theme-500) 0%, var(--theme-600) 50%, var(--theme-700) 100%);
            transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 999px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--theme-500) 0%, var(--theme-600) 100%);
            transition: all 0.2s ease;
            box-shadow: 0 4px 14px -3px rgba(var(--theme-pattern-color), 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 25px -5px rgba(var(--theme-pattern-color), 0.5);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .step-item {
            transition: all 0.2s ease;
        }

        .step-item:hover:not(:disabled) {
            background: rgba(var(--theme-pattern-color), 0.08);
        }

        .step-item.active {
            background: rgba(var(--theme-pattern-color), 0.12);
        }

        .step-number {
            transition: all 0.3s ease;
        }

        .step-number.active {
            background: linear-gradient(135deg, var(--theme-500) 0%, var(--theme-600) 100%);
            color: white;
            box-shadow: 0 4px 12px -2px rgba(var(--theme-pattern-color), 0.4);
        }

        .step-number.completed {
            background: linear-gradient(135deg, var(--theme-500) 0%, var(--theme-600) 100%);
            color: white;
        }

        .step-number.skipped {
            background: #f59e0b;
            color: white;
        }

        .input-field:focus {
            box-shadow: 0 0 0 3px rgba(var(--theme-pattern-color), 0.2);
        }

        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(var(--theme-pattern-color), 0.25); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(var(--theme-pattern-color), 0.4); }

        /* Theme switcher */
        .theme-btn {
            transition: all 0.2s ease;
        }
        .theme-btn:hover {
            transform: scale(1.1);
        }
        .theme-btn.active {
            ring: 2px solid var(--theme-500);
            transform: scale(1.15);
        }

        /* Festive animations */
        @keyframes snow {
            0% { transform: translateY(-10px) rotate(0deg); opacity: 1; }
            100% { transform: translateY(100vh) rotate(360deg); opacity: 0.3; }
        }

        @keyframes sparkle {
            0%, 100% { opacity: 0.3; transform: scale(0.8); }
            50% { opacity: 1; transform: scale(1.2); }
        }

        .animate-snow { animation: snow 10s linear infinite; }
        .animate-sparkle { animation: sparkle 2s ease-in-out infinite; }
    </style>
</head>
<body class="min-h-screen font-sans antialiased">
    <!-- Topo pattern overlay -->
    <div class="topo-pattern"></div>

    <!-- Festive decorations -->
    <div x-show="colorTheme === 'christmas'" class="fixed inset-0 pointer-events-none z-[1] overflow-hidden">
        <template x-for="i in 20" :key="i">
            <div class="absolute text-white/20 animate-snow"
                 :style="'left: ' + (Math.random() * 100) + '%; animation-delay: ' + (Math.random() * 10) + 's; font-size: ' + (10 + Math.random() * 15) + 'px;'">❄</div>
        </template>
    </div>

    <div x-show="colorTheme === 'newyear'" class="fixed inset-0 pointer-events-none z-[1] overflow-hidden">
        <template x-for="i in 15" :key="i">
            <div class="absolute text-yellow-400/30 animate-sparkle"
                 :style="'left: ' + (Math.random() * 100) + '%; top: ' + (Math.random() * 100) + '%; animation-delay: ' + (Math.random() * 2) + 's; font-size: ' + (8 + Math.random() * 12) + 'px;'">✨</div>
        </template>
    </div>

    <div x-data="onboardingWizard()" class="relative z-10 min-h-screen flex items-center justify-center p-4 lg:p-8">
        <div class="w-full max-w-5xl card overflow-hidden flex flex-col lg:flex-row">
            <!-- Sidebar -->
            <aside class="w-full lg:w-80 p-6 lg:p-8 flex-shrink-0 relative"
                   :class="colorTheme === 'newyear' ? 'bg-black/30' : 'bg-gray-50/80 dark:bg-slate-800/80'">
                <!-- Logo & Brand -->
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-14 h-14 rounded-2xl theme-gradient flex items-center justify-center shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold" :class="colorTheme === 'newyear' ? 'text-white' : 'text-gray-900 dark:text-white'">ChurchHub</h1>
                        <p class="text-sm font-medium theme-primary">Налаштування</p>
                    </div>
                </div>

                <!-- Theme Switcher -->
                <div class="mb-6">
                    <button @click="showThemePicker = !showThemePicker"
                            class="w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all"
                            :class="colorTheme === 'newyear' ? 'bg-white/10 hover:bg-white/20 text-white' : 'bg-white dark:bg-slate-700 hover:bg-gray-50 dark:hover:bg-slate-600 text-gray-700 dark:text-gray-200'">
                        <span class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded-full theme-gradient"></span>
                            <span class="text-sm font-medium" x-text="themes[colorTheme]?.name || 'Тема'"></span>
                        </span>
                        <svg class="w-5 h-5 transition-transform" :class="showThemePicker ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <!-- Theme Picker Dropdown -->
                    <div x-show="showThemePicker"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         @click.away="showThemePicker = false"
                         class="mt-2 p-4 rounded-2xl shadow-xl border"
                         :class="colorTheme === 'newyear' ? 'bg-black/80 border-white/10' : 'bg-white dark:bg-slate-800 border-gray-100 dark:border-slate-700'">

                        <!-- Regular Themes -->
                        <p class="text-xs font-bold uppercase tracking-wider mb-3"
                           :class="colorTheme === 'newyear' ? 'text-white/60' : 'text-gray-400 dark:text-gray-500'">Основні теми</p>
                        <div class="grid grid-cols-4 gap-2 mb-4">
                            <template x-for="(theme, key) in regularThemes" :key="key">
                                <button @click="setTheme(key)"
                                        class="group relative w-10 h-10 rounded-xl transition-all hover:scale-110"
                                        :class="colorTheme === key ? 'ring-2 ring-offset-2 ring-gray-400 scale-110' : ''"
                                        :style="'background: linear-gradient(135deg, ' + theme.colors[0] + ', ' + theme.colors[1] + ')'">
                                    <span class="absolute -bottom-6 left-1/2 -translate-x-1/2 text-[10px] font-medium whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity"
                                          :class="colorTheme === 'newyear' ? 'text-white/70' : 'text-gray-500 dark:text-gray-400'"
                                          x-text="theme.name"></span>
                                </button>
                            </template>
                        </div>

                        <!-- Festive Themes -->
                        <p class="text-xs font-bold uppercase tracking-wider mb-3 mt-6"
                           :class="colorTheme === 'newyear' ? 'text-white/60' : 'text-gray-400 dark:text-gray-500'">Святкові теми</p>
                        <div class="grid grid-cols-4 gap-2">
                            <template x-for="(theme, key) in festiveThemes" :key="key">
                                <button @click="setTheme(key)"
                                        class="group relative w-10 h-10 rounded-xl transition-all hover:scale-110 flex items-center justify-center text-lg"
                                        :class="colorTheme === key ? 'ring-2 ring-offset-2 ring-gray-400 scale-110' : ''"
                                        :style="'background: linear-gradient(135deg, ' + theme.colors[0] + ', ' + theme.colors[1] + ')'">
                                    <span x-text="theme.icon"></span>
                                    <span class="absolute -bottom-6 left-1/2 -translate-x-1/2 text-[10px] font-medium whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity"
                                          :class="colorTheme === 'newyear' ? 'text-white/70' : 'text-gray-500 dark:text-gray-400'"
                                          x-text="theme.name"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Steps Navigation -->
                <nav class="space-y-1 mb-8">
                    <p class="text-[10px] font-bold uppercase tracking-wider mb-3 px-4"
                       :class="colorTheme === 'newyear' ? 'text-white/40' : 'text-gray-400 dark:text-gray-500'">КРОКИ НАЛАШТУВАННЯ</p>
                    @foreach($steps as $stepKey => $stepConfig)
                        <button
                            @click="goToStep('{{ $stepKey }}')"
                            :disabled="!canNavigateTo('{{ $stepKey }}')"
                            class="step-item w-full flex items-center gap-4 px-4 py-3 rounded-xl text-left"
                            :class="{
                                'active': currentStep === '{{ $stepKey }}',
                                'opacity-40 cursor-not-allowed': !canNavigateTo('{{ $stepKey }}')
                            }"
                        >
                            <span class="step-number flex-shrink-0 w-9 h-9 rounded-xl flex items-center justify-center text-sm font-bold"
                                  :class="{
                                      'active': currentStep === '{{ $stepKey }}',
                                      'completed': stepsState['{{ $stepKey }}']?.completed,
                                      'skipped': stepsState['{{ $stepKey }}']?.skipped,
                                      'bg-gray-200 dark:bg-slate-700 text-gray-600 dark:text-gray-400': !stepsState['{{ $stepKey }}']?.completed && !stepsState['{{ $stepKey }}']?.skipped && currentStep !== '{{ $stepKey }}'
                                  }">
                                <template x-if="stepsState['{{ $stepKey }}']?.completed">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </template>
                                <template x-if="stepsState['{{ $stepKey }}']?.skipped">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                                    </svg>
                                </template>
                                <template x-if="!stepsState['{{ $stepKey }}']?.completed && !stepsState['{{ $stepKey }}']?.skipped">
                                    <span>{{ $stepConfig['order'] }}</span>
                                </template>
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-sm"
                                   :class="{
                                       'theme-primary': currentStep === '{{ $stepKey }}',
                                       'text-white': (colorTheme === 'newyear') && currentStep !== '{{ $stepKey }}',
                                       'text-gray-900 dark:text-white': colorTheme !== 'halloween' && colorTheme !== 'newyear' && currentStep !== '{{ $stepKey }}'
                                   }">{{ $stepConfig['title'] }}</p>
                                @if(!$stepConfig['required'])
                                    <p class="text-xs" :class="colorTheme === 'newyear' ? 'text-white/40' : 'text-gray-400 dark:text-gray-500'">опційно</p>
                                @endif
                            </div>
                            <svg x-show="currentStep === '{{ $stepKey }}'" class="w-5 h-5 theme-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    @endforeach
                </nav>

                <!-- Progress Card -->
                <div class="rounded-2xl p-5 shadow-sm border"
                     :class="colorTheme === 'newyear' ? 'bg-white/10 border-white/10' : 'bg-white dark:bg-slate-700/50 border-gray-100 dark:border-slate-600/50'">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider"
                               :class="colorTheme === 'newyear' ? 'text-white/40' : 'text-gray-400 dark:text-gray-500'">ПРОГРЕС</p>
                            <p class="text-2xl font-bold mt-1"
                               :class="colorTheme === 'newyear' ? 'text-white' : 'text-gray-900 dark:text-white'"
                               x-text="progress.percentage + '%'"></p>
                        </div>
                        <div class="w-14 h-14 rounded-2xl flex items-center justify-center"
                             :class="colorTheme === 'newyear' ? 'bg-white/10' : 'theme-bg-50 dark:bg-opacity-30'">
                            <svg class="w-7 h-7 theme-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="h-2.5 rounded-full overflow-hidden"
                         :class="colorTheme === 'newyear' ? 'bg-white/10' : 'bg-gray-100 dark:bg-slate-600'">
                        <div class="h-full progress-bar" :style="'width: ' + progress.percentage + '%'"></div>
                    </div>
                    <p class="mt-3 text-xs"
                       :class="colorTheme === 'newyear' ? 'text-white/50' : 'text-gray-500 dark:text-gray-400'">
                        <span class="font-semibold theme-primary" x-text="progress.completed"></span> з <span x-text="progress.total"></span> кроків завершено
                    </p>
                </div>

                <!-- Dark Mode Toggle -->
                <button @click="darkMode = !darkMode; localStorage.setItem('theme', darkMode ? 'dark' : 'light')"
                        class="mt-4 w-full flex items-center justify-center gap-2 px-4 py-3 text-sm rounded-xl transition-all border border-transparent"
                        :class="colorTheme === 'newyear' ? 'text-white/60 hover:text-white hover:bg-white/10' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-white dark:hover:bg-slate-700 hover:border-gray-200 dark:hover:border-slate-600'">
                    <template x-if="!darkMode">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                    </template>
                    <template x-if="darkMode">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </template>
                    <span x-text="darkMode ? 'Світла тема' : 'Темна тема'"></span>
                </button>
            </aside>

            <!-- Main Content -->
            <main class="flex-1 p-6 lg:p-10 overflow-y-auto max-h-[85vh]"
                  :class="colorTheme === 'newyear' ? 'bg-[#1e1b4b]' : 'bg-white dark:bg-slate-800'">
                <!-- Step Content Container -->
                <div x-show="!loading" class="step-enter">
                    <div id="step-content"></div>
                </div>

                <!-- Loading State -->
                <div x-show="loading" class="flex flex-col items-center justify-center py-20">
                    <div class="w-16 h-16 rounded-full border-4 border-t-4 animate-spin"
                         :style="'border-color: var(--theme-100); border-top-color: var(--theme-500);'"></div>
                    <p class="mt-4" :class="colorTheme === 'newyear' ? 'text-white/50' : 'text-gray-500 dark:text-gray-400'">Завантаження...</p>
                </div>

                <!-- Navigation -->
                <div x-show="!loading" class="flex items-center justify-between mt-10 pt-6 border-t"
                     :class="colorTheme === 'newyear' ? 'border-white/10' : 'border-gray-100 dark:border-slate-700'">
                    <button @click="previousStep()"
                            x-show="canGoPrevious()"
                            class="flex items-center gap-2 px-5 py-2.5 rounded-xl transition-all"
                            :class="colorTheme === 'newyear' ? 'text-white/60 hover:text-white hover:bg-white/10' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-slate-700'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Назад
                    </button>
                    <div x-show="!canGoPrevious()"></div>

                    <div class="flex items-center gap-3">
                        <button x-show="canSkipCurrent()"
                                @click="skipStep()"
                                :disabled="saving"
                                class="px-5 py-2.5 rounded-xl transition-all font-medium"
                                :class="colorTheme === 'newyear' ? 'text-white/50 hover:text-white/80 hover:bg-white/10' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-slate-700'">
                            Пропустити
                        </button>

                        <button @click="saveAndNext()"
                                :disabled="saving"
                                class="btn-primary flex items-center gap-2 px-8 py-3 text-white rounded-xl font-semibold disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
                            <span x-show="!saving" x-text="isLastStep() ? 'Завершити' : 'Продовжити'"></span>
                            <span x-show="saving" class="flex items-center gap-2">
                                <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Збереження...
                            </span>
                            <svg x-show="!saving" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </main>
        </div>

        <!-- Toast Notifications -->
        <div class="fixed bottom-6 right-6 z-50 space-y-3">
            <!-- Error Toast -->
            <div x-show="serverError"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform translate-y-4"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="max-w-sm">
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border-l-4 border-red-500 p-5 flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-red-50 dark:bg-red-900/30 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="font-bold text-gray-900 dark:text-white" x-text="serverError?.title"></h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1" x-text="serverError?.message"></p>
                        <ul x-show="serverError?.errors?.length" class="mt-2 text-sm text-red-600 dark:text-red-400 space-y-1">
                            <template x-for="error in serverError?.errors || []" :key="error">
                                <li class="flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                    <span x-text="error"></span>
                                </li>
                            </template>
                        </ul>
                    </div>
                    <button @click="serverError = null" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Success Toast -->
            <div x-show="successMessage"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform translate-y-4"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border-l-4 p-5 flex items-center gap-4"
                     :style="'border-color: var(--theme-500);'">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center theme-bg-50">
                        <svg class="w-5 h-5 theme-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <p class="font-semibold text-gray-900 dark:text-white" x-text="successMessage"></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function themeManager() {
            return {
                darkMode: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches),
                colorTheme: localStorage.getItem('colorTheme') || 'forest',
                showThemePicker: false,

                regularThemes: {
                    forest: { name: 'Ліс', colors: ['#22c55e', '#15803d'] },
                    ocean: { name: 'Океан', colors: ['#3b82f6', '#1d4ed8'] },
                    sunset: { name: 'Захід', colors: ['#f97316', '#c2410c'] },
                    lavender: { name: 'Лаванда', colors: ['#a855f7', '#7e22ce'] },
                    rose: { name: 'Троянда', colors: ['#f43f5e', '#be123c'] },
                    teal: { name: 'Бірюза', colors: ['#14b8a6', '#0f766e'] },
                    slate: { name: 'Мінімал', colors: ['#64748b', '#334155'] },
                },

                festiveThemes: {
                    christmas: { name: 'Різдво', colors: ['#dc2626', '#15803d'], icon: '🎄' },
                    easter: { name: 'Великдень', colors: ['#d946ef', '#60a5fa'], icon: '✝️' },
                    newyear: { name: 'Новий рік', colors: ['#eab308', '#312e81'], icon: '🎆' },
                },

                get themes() {
                    return { ...this.regularThemes, ...this.festiveThemes };
                },

                setTheme(theme) {
                    this.colorTheme = theme;
                    localStorage.setItem('colorTheme', theme);
                    document.documentElement.setAttribute('data-theme', theme);
                    this.showThemePicker = false;
                },

                init() {
                    // Auto-detect festive season
                    const today = new Date();
                    const month = today.getMonth();
                    const day = today.getDate();

                    // Check if user hasn't manually set a theme
                    if (!localStorage.getItem('colorTheme')) {
                        // Christmas: Dec 15 - Jan 7
                        if ((month === 11 && day >= 15) || (month === 0 && day <= 7)) {
                            this.setTheme('christmas');
                        }
                        // Easter: April (simplified - around Orthodox/Catholic Easter)
                        else if (month === 3) {
                            this.setTheme('easter');
                        }
                        // New Year: Dec 25 - Jan 15
                        else if ((month === 11 && day >= 25) || (month === 0 && day <= 15)) {
                            this.setTheme('newyear');
                        }
                    }
                }
            }
        }

        function onboardingWizard() {
            return {
                currentStep: '{{ $currentStep }}',
                steps: @json($steps),
                stepsState: @json($stepsState),
                progress: @json($progress),
                loading: true,
                saving: false,
                serverError: null,
                successMessage: null,

                async init() {
                    await this.loadStepContent();
                },

                getStepKeys() {
                    return Object.keys(this.steps);
                },

                getCurrentStepIndex() {
                    return this.getStepKeys().indexOf(this.currentStep);
                },

                canNavigateTo(step) {
                    const stepKeys = this.getStepKeys();
                    const targetIndex = stepKeys.indexOf(step);
                    const currentIndex = this.getCurrentStepIndex();

                    if (targetIndex <= currentIndex) return true;

                    for (let i = currentIndex; i < targetIndex; i++) {
                        const stepKey = stepKeys[i];
                        if (!this.stepsState[stepKey]?.completed && !this.stepsState[stepKey]?.skipped) {
                            return false;
                        }
                    }
                    return true;
                },

                canGoPrevious() {
                    return this.getCurrentStepIndex() > 0;
                },

                canSkipCurrent() {
                    return !this.steps[this.currentStep]?.required;
                },

                isLastStep() {
                    const stepKeys = this.getStepKeys();
                    return this.currentStep === stepKeys[stepKeys.length - 1];
                },

                async goToStep(step) {
                    if (!this.canNavigateTo(step) || this.currentStep === step) return;
                    this.currentStep = step;
                    await this.loadStepContent();
                },

                async loadStepContent() {
                    this.loading = true;
                    try {
                        const response = await fetch(`/onboarding/step/${this.currentStep}`);
                        const html = await response.text();
                        document.getElementById('step-content').innerHTML = html;
                    } catch (error) {
                        console.error('Failed to load step:', error);
                    }
                    this.loading = false;
                },

                async previousStep() {
                    const stepKeys = this.getStepKeys();
                    const currentIndex = this.getCurrentStepIndex();
                    if (currentIndex > 0) {
                        this.currentStep = stepKeys[currentIndex - 1];
                        await this.loadStepContent();
                    }
                },

                async saveAndNext() {
                    this.saving = true;
                    this.serverError = null;

                    try {
                        const { formData, hasFiles } = this.collectFormData();

                        const headers = {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        };

                        let body;
                        if (hasFiles) {
                            body = formData;
                        } else {
                            headers['Content-Type'] = 'application/json';
                            body = JSON.stringify(this.formDataToObject(formData));
                        }

                        const response = await fetch(`/onboarding/step/${this.currentStep}`, {
                            method: 'POST',
                            headers: headers,
                            body: body
                        });

                        if (!response.ok) {
                            const errorData = await response.json().catch(() => ({}));
                            this.serverError = {
                                title: 'Помилка валідації',
                                message: errorData.message || 'Перевірте правильність даних',
                                errors: errorData.errors ? Object.values(errorData.errors).flat() : null
                            };
                            setTimeout(() => this.serverError = null, 8000);
                            this.saving = false;
                            return;
                        }

                        const data = await response.json();

                        if (data.success) {
                            this.stepsState[this.currentStep] = {
                                completed: true,
                                skipped: false,
                                completed_at: new Date().toISOString()
                            };
                            this.progress = data.progress || this.progress;

                            if (data.completed) {
                                this.successMessage = 'Налаштування завершено!';
                                this.celebrate();
                                setTimeout(() => {
                                    window.location.href = data.redirect;
                                }, 2500);
                            } else if (data.nextStep) {
                                this.successMessage = 'Збережено';
                                setTimeout(() => this.successMessage = null, 1500);
                                this.currentStep = data.nextStep;
                                await this.loadStepContent();
                            }
                        }
                    } catch (error) {
                        this.serverError = {
                            title: 'Помилка з\'єднання',
                            message: 'Перевірте інтернет-з\'єднання'
                        };
                        setTimeout(() => this.serverError = null, 5000);
                    }
                    this.saving = false;
                },

                async skipStep() {
                    this.saving = true;
                    try {
                        const response = await fetch(`/onboarding/step/${this.currentStep}/skip`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.stepsState[this.currentStep] = { completed: false, skipped: true };
                            this.progress = data.progress || this.progress;

                            if (data.nextStep) {
                                this.currentStep = data.nextStep;
                                await this.loadStepContent();
                            }
                        }
                    } catch (error) {
                        console.error('Failed to skip step:', error);
                    }
                    this.saving = false;
                },

                collectFormData() {
                    const form = document.getElementById('step-content').querySelector('form');
                    if (!form) return { formData: new FormData(), hasFiles: false };

                    const formData = new FormData(form);
                    let hasFiles = false;

                    form.querySelectorAll('input[type="file"]').forEach(input => {
                        if (input.files.length > 0) hasFiles = true;
                    });

                    return { formData, hasFiles };
                },

                formDataToObject(formData) {
                    const data = {};
                    for (const [key, value] of formData.entries()) {
                        if (value instanceof File) continue;
                        if (key.includes('[')) {
                            const matches = key.match(/([^\[]+)\[(\d+)\]\[([^\]]+)\]/);
                            if (matches) {
                                const [, arrayName, index, field] = matches;
                                if (!data[arrayName]) data[arrayName] = [];
                                if (!data[arrayName][index]) data[arrayName][index] = {};
                                data[arrayName][index][field] = value;
                            }
                        } else {
                            data[key] = value;
                        }
                    }
                    return data;
                },

                celebrate() {
                    const duration = 3000;
                    const end = Date.now() + duration;

                    // Get theme colors for confetti
                    const style = getComputedStyle(document.documentElement);
                    const themeColor = style.getPropertyValue('--theme-500').trim() || '#22c55e';
                    const themeColorDark = style.getPropertyValue('--theme-600').trim() || '#16a34a';
                    const themeColorLight = style.getPropertyValue('--theme-400').trim() || '#4ade80';

                    const frame = () => {
                        confetti({
                            particleCount: 3,
                            angle: 60,
                            spread: 55,
                            origin: { x: 0, y: 0.8 },
                            colors: [themeColor, themeColorDark, themeColorLight, '#fbbf24', '#ffffff']
                        });
                        confetti({
                            particleCount: 3,
                            angle: 120,
                            spread: 55,
                            origin: { x: 1, y: 0.8 },
                            colors: [themeColor, themeColorDark, themeColorLight, '#fbbf24', '#ffffff']
                        });

                        if (Date.now() < end) requestAnimationFrame(frame);
                    };
                    frame();
                }
            }
        }
    </script>
</body>
</html>
