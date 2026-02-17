<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Двофакторна аутентифікація') }} - Ministrify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">{{ __('Двофакторна аутентифікація') }}</h1>
            <p class="text-gray-500 mt-2">{{ __('Введіть код з вашого додатку аутентифікації') }}</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                </div>
            @endif

            <form action="{{ route('two-factor.verify') }}" method="POST">
                @csrf
                <div class="mb-6">
                    <input type="text" name="code" required autofocus
                           placeholder="000000"
                           maxlength="10"
                           class="w-full px-4 py-4 bg-gray-50 border border-gray-200 rounded-xl text-center text-2xl tracking-widest font-mono focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <p class="text-xs text-gray-400 mt-2 text-center">
                        {{ __('Або введіть код відновлення (xxxx-xxxx)') }}
                    </p>
                </div>

                <button type="submit"
                        class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-colors">
                    {{ __('Підтвердити') }}
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="{{ route('login') }}" class="text-sm text-gray-500 hover:text-gray-700">
                    {{ __('Повернутися до входу') }}
                </a>
            </div>
        </div>
    </div>
</body>
</html>