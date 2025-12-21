<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#3b82f6">

    <title>{{ config('app.name', 'ChurchHub') }} - @yield('title', 'Вхід')</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

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
        @media screen and (max-width: 768px) {
            input, select, textarea {
                font-size: 16px !important;
            }
        }
    </style>
</head>
<body class="font-sans antialiased bg-gradient-to-br from-primary-50 via-white to-blue-50 min-h-screen">
    <div class="min-h-screen flex flex-col justify-center px-4 py-8 sm:py-12">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <!-- Logo -->
            <div class="text-center mb-8">
                <a href="/" class="inline-flex items-center space-x-3">
                    <div class="w-14 h-14 bg-gradient-to-br from-primary-500 to-primary-700 rounded-2xl flex items-center justify-center shadow-lg shadow-primary-500/30">
                        <span class="text-3xl">⛪</span>
                    </div>
                </a>
                <h1 class="mt-4 text-2xl font-bold text-gray-900">ChurchHub</h1>
                <p class="mt-1 text-gray-500">Управління церквою просто</p>
            </div>

            <!-- Card -->
            <div class="bg-white/80 backdrop-blur-sm shadow-xl shadow-gray-200/50 rounded-3xl p-6 sm:p-8 border border-white">
                @if(session('status'))
                    <div class="mb-6 p-4 bg-green-50 border border-green-100 text-green-700 rounded-2xl text-sm flex items-start">
                        <svg class="w-5 h-5 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-6 p-4 bg-red-50 border border-red-100 text-red-700 rounded-2xl text-sm">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <ul class="space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                @yield('content')
            </div>

            <!-- Footer -->
            <p class="mt-8 text-center text-sm text-gray-400">
                &copy; {{ date('Y') }} ChurchHub. Для церков України
            </p>
        </div>
    </div>
</body>
</html>
