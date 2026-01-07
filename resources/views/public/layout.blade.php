<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $church->name)</title>
    <meta name="description" content="{{ $church->public_description ?? $church->name }}">

    <!-- Favicon -->
    @if($church->logo)
        <link rel="icon" type="image/png" href="{{ Storage::url($church->logo) }}">
    @endif

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet">

    <!-- Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '{{ $church->theme_colors["50"] }}',
                            100: '{{ $church->theme_colors["100"] }}',
                            200: '{{ $church->theme_colors["200"] }}',
                            300: '{{ $church->theme_colors["300"] }}',
                            400: '{{ $church->theme_colors["400"] }}',
                            500: '{{ $church->theme_colors["500"] }}',
                            600: '{{ $church->theme_colors["600"] }}',
                            700: '{{ $church->theme_colors["700"] }}',
                            800: '{{ $church->theme_colors["800"] }}',
                            900: '{{ $church->theme_colors["900"] }}',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        .gradient-text {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        :root {
            --primary-color: {{ $church->primary_color ?? '#3b82f6' }};
            --primary-dark: {{ $church->theme_colors["700"] }};
        }

        /* Custom Scrollbars - Theme colored */
        * {
            scrollbar-width: thin;
            scrollbar-color: var(--primary-color) transparent;
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
            background: var(--primary-color);
            opacity: 0.5;
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            opacity: 0.7;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm sticky top-0 z-50" x-data="{ mobileOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('public.church', $church->slug) }}" class="flex items-center gap-3">
                        @if($church->logo)
                            <img src="{{ Storage::url($church->logo) }}" alt="{{ $church->name }}" class="h-10 w-10 rounded-lg object-cover">
                        @else
                            <div class="h-10 w-10 rounded-lg bg-primary-500 flex items-center justify-center">
                                <span class="text-white font-bold text-lg">{{ substr($church->name, 0, 1) }}</span>
                            </div>
                        @endif
                        <span class="text-xl font-bold text-gray-900">{{ $church->name }}</span>
                    </a>
                </div>

                <!-- Desktop nav -->
                <div class="hidden md:flex items-center gap-1">
                    <a href="{{ route('public.church', $church->slug) }}"
                       class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-colors">
                        Головна
                    </a>
                    <a href="{{ route('public.events', $church->slug) }}"
                       class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-colors">
                        Події
                    </a>
                    {{-- TODO: Розкоментувати після бета-тестування
                    <a href="{{ route('public.donate', $church->slug) }}"
                       class="ml-2 px-5 py-2.5 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition-colors shadow-sm">
                        Пожертвувати
                    </a>
                    --}}
                </div>

                <!-- Mobile menu button -->
                <div class="flex items-center md:hidden">
                    <button @click="mobileOpen = !mobileOpen" class="p-2 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path x-show="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            <path x-show="mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        <div x-show="mobileOpen" x-cloak class="md:hidden border-t border-gray-100">
            <div class="px-4 py-3 space-y-1">
                <a href="{{ route('public.church', $church->slug) }}" class="block px-4 py-2.5 text-sm font-medium text-gray-700 hover:text-primary-600 hover:bg-primary-50 rounded-lg">Головна</a>
                <a href="{{ route('public.events', $church->slug) }}" class="block px-4 py-2.5 text-sm font-medium text-gray-700 hover:text-primary-600 hover:bg-primary-50 rounded-lg">Події</a>
                {{-- TODO: Розкоментувати після бета-тестування
                <a href="{{ route('public.donate', $church->slug) }}" class="block px-4 py-2.5 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-lg text-center mt-3">Пожертвувати</a>
                --}}
            </div>
        </div>
    </nav>

    <!-- Flash messages -->
    @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl p-4 flex items-center gap-3">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl p-4 flex items-center gap-3">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                {{ session('error') }}
            </div>
        </div>
    @endif

    <!-- Main content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        @if($church->logo)
                            <img src="{{ Storage::url($church->logo) }}" alt="{{ $church->name }}" class="h-12 w-12 rounded-lg object-cover">
                        @endif
                        <span class="text-xl font-bold">{{ $church->name }}</span>
                    </div>
                    @if($church->public_description)
                        <p class="text-gray-400 text-sm leading-relaxed">{{ Str::limit($church->public_description, 150) }}</p>
                    @endif
                </div>

                <div>
                    <h4 class="font-semibold mb-4">Контакти</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        @if($church->address)
                            <li class="flex items-start gap-2">
                                <svg class="w-4 h-4 mt-0.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                {{ $church->address }}@if($church->city), {{ $church->city }}@endif
                            </li>
                        @endif
                        @if($church->public_email)
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <a href="mailto:{{ $church->public_email }}" class="hover:text-white transition-colors">{{ $church->public_email }}</a>
                            </li>
                        @endif
                        @if($church->public_phone)
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                <a href="tel:{{ $church->public_phone }}" class="hover:text-white transition-colors">{{ $church->public_phone }}</a>
                            </li>
                        @endif
                    </ul>
                </div>

            </div>

            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-sm text-gray-500">
                <p>&copy; {{ date('Y') }} {{ $church->name }}. Powered by Ministrify</p>
            </div>
        </div>
    </footer>
</body>
</html>
