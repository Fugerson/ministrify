<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#3b82f6">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <title>{{ config('app.name', 'ChurchHub') }} - @yield('title', 'Dashboard')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
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
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }

        /* Better touch targets */
        .touch-target {
            min-height: 44px;
            min-width: 44px;
        }

        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
        }

        /* Hide scrollbar but keep functionality */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* Safe area for notch devices */
        .safe-bottom {
            padding-bottom: env(safe-area-inset-bottom);
        }

        /* Input focus styles for mobile */
        input:focus, select:focus, textarea:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
        }

        /* Prevent zoom on input focus (iOS) */
        @media screen and (max-width: 768px) {
            input, select, textarea {
                font-size: 16px !important;
            }
        }
    </style>

    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-100">
    <div x-data="{ sidebarOpen: false }" class="min-h-screen flex flex-col">

        <!-- Mobile Header -->
        <header class="lg:hidden sticky top-0 z-40 bg-white border-b shadow-sm">
            <div class="flex items-center justify-between h-14 px-4">
                <button @click="sidebarOpen = true" class="p-2 -ml-2 text-gray-600 hover:text-gray-900 touch-target">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <div class="flex items-center space-x-2">
                    <span class="text-xl">⛪</span>
                    <span class="font-bold text-gray-900">ChurchHub</span>
                </div>

                <a href="{{ route('my-profile') }}" class="p-2 -mr-2 touch-target">
                    <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center">
                        <span class="text-sm font-medium text-primary-600">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
                    </div>
                </a>
            </div>
        </header>

        <!-- Mobile sidebar backdrop -->
        <div x-show="sidebarOpen"
             x-transition:enter="transition-opacity ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             x-cloak
             class="fixed inset-0 z-40 bg-black/50 lg:hidden"
             @click="sidebarOpen = false"></div>

        <!-- Sidebar -->
        <aside x-show="sidebarOpen || window.innerWidth >= 1024"
               x-transition:enter="transition ease-out duration-300"
               x-transition:enter-start="-translate-x-full"
               x-transition:enter-end="translate-x-0"
               x-transition:leave="transition ease-in duration-200"
               x-transition:leave-start="translate-x-0"
               x-transition:leave-end="-translate-x-full"
               x-cloak
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
               class="fixed inset-y-0 left-0 z-50 w-72 lg:w-64 bg-white shadow-xl lg:shadow-lg flex flex-col lg:static">

            <!-- Sidebar Header -->
            <div class="flex items-center justify-between h-16 px-6 border-b flex-shrink-0">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
                    <span class="text-2xl">⛪</span>
                    <span class="text-lg font-bold text-gray-900">ChurchHub</span>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden p-2 -mr-2 text-gray-500 hover:text-gray-700 touch-target">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto no-scrollbar">
                <a href="{{ route('dashboard') }}" @click="sidebarOpen = false"
                   class="flex items-center px-4 py-3 text-base lg:text-sm font-medium rounded-xl touch-target {{ request()->routeIs('dashboard') ? 'bg-primary-50 text-primary-700' : 'text-gray-700 hover:bg-gray-100 active:bg-gray-200' }}">
                    <svg class="w-6 h-6 lg:w-5 lg:h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Dashboard
                </a>

                <a href="{{ route('people.index') }}" @click="sidebarOpen = false"
                   class="flex items-center px-4 py-3 text-base lg:text-sm font-medium rounded-xl touch-target {{ request()->routeIs('people.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-700 hover:bg-gray-100 active:bg-gray-200' }}">
                    <svg class="w-6 h-6 lg:w-5 lg:h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                    </svg>
                    Люди
                </a>

                <a href="{{ route('ministries.index') }}" @click="sidebarOpen = false"
                   class="flex items-center px-4 py-3 text-base lg:text-sm font-medium rounded-xl touch-target {{ request()->routeIs('ministries.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-700 hover:bg-gray-100 active:bg-gray-200' }}">
                    <svg class="w-6 h-6 lg:w-5 lg:h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    Служіння
                </a>

                <a href="{{ route('schedule') }}" @click="sidebarOpen = false"
                   class="flex items-center px-4 py-3 text-base lg:text-sm font-medium rounded-xl touch-target {{ request()->routeIs('schedule') || request()->routeIs('events.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-700 hover:bg-gray-100 active:bg-gray-200' }}">
                    <svg class="w-6 h-6 lg:w-5 lg:h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Розклад
                </a>

                @leader
                <a href="{{ route('expenses.index') }}" @click="sidebarOpen = false"
                   class="flex items-center px-4 py-3 text-base lg:text-sm font-medium rounded-xl touch-target {{ request()->routeIs('expenses.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-700 hover:bg-gray-100 active:bg-gray-200' }}">
                    <svg class="w-6 h-6 lg:w-5 lg:h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Витрати
                </a>
                @endleader

                <a href="{{ route('attendance.index') }}" @click="sidebarOpen = false"
                   class="flex items-center px-4 py-3 text-base lg:text-sm font-medium rounded-xl touch-target {{ request()->routeIs('attendance.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-700 hover:bg-gray-100 active:bg-gray-200' }}">
                    <svg class="w-6 h-6 lg:w-5 lg:h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Відвідуваність
                </a>

                <a href="{{ route('my-profile') }}" @click="sidebarOpen = false"
                   class="flex items-center px-4 py-3 text-base lg:text-sm font-medium rounded-xl touch-target {{ request()->routeIs('my-profile*') ? 'bg-primary-50 text-primary-700' : 'text-gray-700 hover:bg-gray-100 active:bg-gray-200' }}">
                    <svg class="w-6 h-6 lg:w-5 lg:h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Мій профіль
                </a>

                @admin
                <div class="pt-4 mt-4 border-t">
                    <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Адміністрування</p>
                </div>

                <a href="{{ route('settings.index') }}" @click="sidebarOpen = false"
                   class="flex items-center px-4 py-3 text-base lg:text-sm font-medium rounded-xl touch-target {{ request()->routeIs('settings.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-700 hover:bg-gray-100 active:bg-gray-200' }}">
                    <svg class="w-6 h-6 lg:w-5 lg:h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Налаштування
                </a>
                @endadmin
            </nav>

            <!-- User section at bottom -->
            <div class="flex-shrink-0 p-4 border-t bg-gray-50 safe-bottom">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                            <span class="text-sm font-medium text-primary-600">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ $currentChurch->name ?? '' }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="p-2 text-gray-400 hover:text-gray-600 touch-target">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex-1 lg:ml-64 flex flex-col min-h-screen lg:min-h-0">
            <!-- Desktop Top bar -->
            <header class="hidden lg:flex sticky top-0 z-30 bg-white border-b items-center justify-between h-16 px-6">
                <h1 class="text-lg font-semibold text-gray-900">@yield('title', 'Dashboard')</h1>
                <div class="flex items-center space-x-4">
                    @yield('actions')
                </div>
            </header>

            <!-- Page content -->
            <main class="flex-1 p-4 lg:p-6 pb-20 lg:pb-6">
                <!-- Flash messages -->
                @if(session('success'))
                    <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl flex items-start">
                        <svg class="w-5 h-5 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-start">
                        <svg class="w-5 h-5 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @if(session('info'))
                    <div class="mb-4 p-4 bg-blue-50 border border-blue-200 text-blue-700 rounded-xl flex items-start">
                        <svg class="w-5 h-5 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ session('info') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <ul class="list-disc list-inside space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>

        <!-- Mobile Bottom Navigation -->
        <nav class="lg:hidden fixed bottom-0 left-0 right-0 bg-white border-t z-40 safe-bottom">
            <div class="flex items-center justify-around h-16">
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center justify-center flex-1 py-2 {{ request()->routeIs('dashboard') ? 'text-primary-600' : 'text-gray-500' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span class="text-xs mt-1">Головна</span>
                </a>
                <a href="{{ route('people.index') }}" class="flex flex-col items-center justify-center flex-1 py-2 {{ request()->routeIs('people.*') ? 'text-primary-600' : 'text-gray-500' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                    </svg>
                    <span class="text-xs mt-1">Люди</span>
                </a>
                <a href="{{ route('schedule') }}" class="flex flex-col items-center justify-center flex-1 py-2 {{ request()->routeIs('schedule') || request()->routeIs('events.*') ? 'text-primary-600' : 'text-gray-500' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span class="text-xs mt-1">Розклад</span>
                </a>
                <a href="{{ route('ministries.index') }}" class="flex flex-col items-center justify-center flex-1 py-2 {{ request()->routeIs('ministries.*') ? 'text-primary-600' : 'text-gray-500' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span class="text-xs mt-1">Служіння</span>
                </a>
                <a href="{{ route('my-profile') }}" class="flex flex-col items-center justify-center flex-1 py-2 {{ request()->routeIs('my-profile*') ? 'text-primary-600' : 'text-gray-500' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span class="text-xs mt-1">Профіль</span>
                </a>
            </div>
        </nav>
    </div>

    @stack('scripts')
</body>
</html>
