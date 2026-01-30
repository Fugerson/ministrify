<!DOCTYPE html>
<html lang="uk" x-data="{ darkMode: localStorage.getItem('theme') === 'dark' || (localStorage.getItem('theme') === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches) }" :class="{ 'dark': darkMode }">
<head>
    <script>
        (function() {
            const theme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (theme === 'dark' || (theme === 'auto' && prefersDark)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ministrify - Налаштування</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
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
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }

        /* Primary color scheme - Indigo */
        :root {
            --theme-50: #eef2ff;
            --theme-100: #e0e7ff;
            --theme-200: #c7d2fe;
            --theme-300: #a5b4fc;
            --theme-400: #818cf8;
            --theme-500: #6366f1;
            --theme-600: #4f46e5;
            --theme-700: #4338ca;
            --theme-800: #3730a3;
            --theme-900: #312e81;
            --theme-gradient-start: #e0e7ff;
            --theme-gradient-mid: #c7d2fe;
            --theme-gradient-end: #a5b4fc;
            --theme-dark-start: #312e81;
            --theme-dark-mid: #3730a3;
            --theme-dark-end: #4338ca;
            --theme-pattern-color: 99, 102, 241;
            --theme-confetti: #6366f1, #4f46e5, #818cf8;
        }

        /* ==================== APPLY THEME COLORS ==================== */
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 50%, #e0e7ff 100%);
            min-height: 100vh;
        }

        .dark body {
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #1e293b 100%);
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
    <div x-data="onboardingWizard()" class="relative z-10 min-h-screen flex items-center justify-center p-4 lg:p-8">
        <div class="w-full max-w-5xl card overflow-hidden flex flex-col lg:flex-row">
            <!-- Sidebar -->
            <aside class="w-full lg:w-80 p-6 lg:p-8 flex-shrink-0 relative bg-gray-50/80 dark:bg-slate-800/80">
                <!-- Logo & Brand -->
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-14 h-14 rounded-2xl theme-gradient flex items-center justify-center shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Ministrify</h1>
                        <p class="text-sm font-medium theme-primary">Налаштування</p>
                    </div>
                </div>

                <!-- Steps Navigation -->
                <nav class="space-y-1 mb-8">
                    <p class="text-[10px] font-bold uppercase tracking-wider mb-3 px-4 text-gray-400 dark:text-gray-500">КРОКИ НАЛАШТУВАННЯ</p>
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
                                       'text-gray-900 dark:text-white': currentStep !== '{{ $stepKey }}'
                                   }">{{ $stepConfig['title'] }}</p>
                                @if(!$stepConfig['required'])
                                    <p class="text-xs text-gray-400 dark:text-gray-500">опційно</p>
                                @endif
                            </div>
                            <svg x-show="currentStep === '{{ $stepKey }}'" class="w-5 h-5 theme-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    @endforeach
                </nav>

                <!-- Progress Card -->
                <div class="rounded-2xl p-5 shadow-sm border bg-white dark:bg-slate-700/50 border-gray-100 dark:border-slate-600/50">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">ПРОГРЕС</p>
                            <p class="text-2xl font-bold mt-1 text-gray-900 dark:text-white" x-text="progress.percentage + '%'"></p>
                        </div>
                        <div class="w-14 h-14 rounded-2xl flex items-center justify-center theme-bg-50 dark:bg-opacity-30">
                            <svg class="w-7 h-7 theme-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="h-2.5 rounded-full overflow-hidden bg-gray-100 dark:bg-slate-600">
                        <div class="h-full progress-bar" :style="'width: ' + progress.percentage + '%'"></div>
                    </div>
                    <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                        <span class="font-semibold theme-primary" x-text="progress.completed"></span> з <span x-text="progress.total"></span> кроків завершено
                    </p>
                </div>

                <!-- Dark Mode Toggle -->
                <button @click="darkMode = !darkMode; localStorage.setItem('theme', darkMode ? 'dark' : 'light')"
                        class="mt-4 w-full flex items-center justify-center gap-2 px-4 py-3 text-sm rounded-xl transition-all border border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-white dark:hover:bg-slate-700 hover:border-gray-200 dark:hover:border-slate-600">
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
            <main class="flex-1 p-6 lg:p-10 overflow-y-auto max-h-[85vh] bg-white dark:bg-slate-800">
                <!-- Step Content Container -->
                <div x-show="!loading" class="step-enter">
                    <div id="step-content"></div>
                </div>

                <!-- Loading State -->
                <div x-show="loading" class="flex flex-col items-center justify-center py-20">
                    <div class="w-16 h-16 rounded-full border-4 border-t-4 animate-spin"
                         :style="'border-color: var(--theme-100); border-top-color: var(--theme-500);'"></div>
                    <p class="mt-4 text-gray-500 dark:text-gray-400">Завантаження...</p>
                </div>

                <!-- Navigation -->
                <div x-show="!loading" class="flex items-center justify-between mt-10 pt-6 border-t border-gray-100 dark:border-slate-700">
                    <button @click="previousStep()"
                            x-show="canGoPrevious()"
                            class="flex items-center gap-2 px-5 py-2.5 rounded-xl transition-all text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-slate-700">
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
                                class="px-5 py-2.5 rounded-xl transition-all font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-slate-700">
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
