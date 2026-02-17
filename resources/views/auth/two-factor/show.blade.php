@extends('layouts.app')

@section('title', __('Двофакторна аутентифікація'))

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ __('Двофакторна аутентифікація') }}</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ __('Додатковий захист для вашого облікового запису') }}
            </p>
        </div>

        <div class="p-6">
            @if($enabled)
                <!-- 2FA Enabled -->
                <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-xl p-4 mb-6">
                    <div class="flex">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-green-800 dark:text-green-300">{{ __('2FA увімкнено') }}</h3>
                            <p class="mt-1 text-sm text-green-700 dark:text-green-400">
                                {{ __('Ваш обліковий запис захищений двофакторною аутентифікацією.') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Recovery Codes -->
                @if(count($recoveryCodes) > 0)
                    <div class="mb-6">
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">{{ __('Коди відновлення') }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                            {{ __('Збережіть ці коди в безпечному місці. Вони знадобляться, якщо втратите доступ до телефону.') }}
                        </p>
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 font-mono text-sm grid grid-cols-2 gap-2">
                            @foreach($recoveryCodes as $code)
                                <div class="text-gray-900 dark:text-white">{{ $code }}</div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Actions -->
                <div class="space-y-4">
                    <form action="{{ route('two-factor.regenerate') }}" method="POST" class="inline">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('Пароль для підтвердження') }}
                            </label>
                            <input type="password" name="password" required
                                   class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg">
                        </div>
                        <button type="submit"
                                class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600">
                            {{ __('Згенерувати нові коди відновлення') }}
                        </button>
                    </form>

                    <form action="{{ route('two-factor.disable') }}" method="POST"
                          onsubmit="return confirm('{{ __('Ви впевнені, що хочете вимкнути 2FA?') }}')">
                        @csrf
                        @method('DELETE')
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('Пароль для вимкнення') }}
                            </label>
                            <input type="password" name="password" required
                                   class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg">
                        </div>
                        <button type="submit"
                                class="px-4 py-2 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 rounded-lg hover:bg-red-200 dark:hover:bg-red-900/50">
                            {{ __('Вимкнути 2FA') }}
                        </button>
                    </form>
                </div>
            @else
                <!-- 2FA Disabled -->
                <div class="text-center py-8">
                    <div class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">{{ __('2FA не увімкнено') }}</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-sm mx-auto">
                        {{ __('Увімкніть двофакторну аутентифікацію для додаткового захисту вашого облікового запису.') }}
                    </p>
                    <a href="{{ route('two-factor.enable') }}"
                       class="inline-flex items-center px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        {{ __('Увімкнути 2FA') }}
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection