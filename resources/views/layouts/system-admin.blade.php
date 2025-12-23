<!DOCTYPE html>
<html lang="uk" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>System Admin - @yield('title', 'Dashboard')</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        [x-cloak] { display: none !important; }

        /* Custom Scrollbars - Dark theme */
        * {
            scrollbar-width: thin;
            scrollbar-color: rgba(75, 85, 99, 0.6) transparent;
        }
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(75, 85, 99, 0.5);
            border-radius: 3px;
            transition: background 0.2s ease;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(107, 114, 128, 0.7);
        }
        ::-webkit-scrollbar-corner {
            background: transparent;
        }
    </style>
</head>
<body class="font-sans antialiased bg-gray-900 text-gray-100">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-800 border-r border-gray-700 flex flex-col">
            <div class="h-16 flex items-center px-6 border-b border-gray-700">
                <span class="text-xl font-bold text-white flex items-center gap-2">
                    <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    System Admin
                </span>
            </div>

            <nav class="flex-1 px-4 py-6 space-y-1">
                <a href="{{ route('system.index') }}"
                   class="flex items-center px-4 py-3 rounded-xl {{ request()->routeIs('system.index') ? 'bg-red-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Dashboard
                </a>

                <a href="{{ route('system.churches.index') }}"
                   class="flex items-center px-4 py-3 rounded-xl {{ request()->routeIs('system.churches.*') ? 'bg-red-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    Церкви
                </a>

                <a href="{{ route('system.users.index') }}"
                   class="flex items-center px-4 py-3 rounded-xl {{ request()->routeIs('system.users.*') ? 'bg-red-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    Користувачі
                </a>

                <a href="{{ route('system.audit-logs') }}"
                   class="flex items-center px-4 py-3 rounded-xl {{ request()->routeIs('system.audit-logs') ? 'bg-red-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Журнал дій
                </a>

                <div class="pt-6 mt-6 border-t border-gray-700">
                    <a href="{{ route('dashboard') }}"
                       class="flex items-center px-4 py-3 rounded-xl text-gray-300 hover:bg-gray-700">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/>
                        </svg>
                        До ChurchHub
                    </a>
                </div>
            </nav>

            <div class="p-4 border-t border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-red-600 flex items-center justify-center">
                        <span class="text-white font-bold">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-400">Super Admin</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="p-2 text-gray-400 hover:text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            <header class="h-16 bg-gray-800 border-b border-gray-700 flex items-center justify-between px-6">
                <h1 class="text-xl font-semibold text-white">@yield('title', 'Dashboard')</h1>
                @yield('actions')
            </header>

            <main class="flex-1 p-6 overflow-y-auto">
                @if(session('success'))
                <div class="mb-4 p-4 bg-green-900/50 border border-green-700 text-green-300 rounded-xl">
                    {{ session('success') }}
                </div>
                @endif

                @if(session('error'))
                <div class="mb-4 p-4 bg-red-900/50 border border-red-700 text-red-300 rounded-xl">
                    {{ session('error') }}
                </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
