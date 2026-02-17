@extends('layouts.app')

@section('title', __('Налаштування 2FA'))

@section('content')
<div class="max-w-xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ __('Налаштування 2FA') }}</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ __('Скануйте QR-код за допомогою Google Authenticator або Authy') }}
            </p>
        </div>

        <div class="p-6">
            <!-- QR Code -->
            <div class="text-center mb-6">
                <div class="inline-block p-4 bg-white rounded-xl shadow-sm">
                    {!! $qrCodeSvg !!}
                </div>
            </div>

            <!-- Manual Entry -->
            <div class="mb-6">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                    {{ __('Або введіть код вручну:') }}
                </p>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 font-mono text-center text-lg tracking-widest text-gray-900 dark:text-white">
                    {{ $secret }}
                </div>
            </div>

            <!-- Verification Form -->
            <form action="{{ route('two-factor.confirm') }}" method="POST">
                @csrf
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('Введіть 6-значний код з додатку') }}
                    </label>
                    <input type="text" name="code" required autofocus
                           maxlength="6" pattern="[0-9]{6}"
                           placeholder="000000"
                           class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-center text-2xl tracking-widest font-mono">
                    @error('code')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex gap-4">
                    <a href="{{ route('two-factor.show') }}"
                       class="flex-1 px-4 py-3 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium rounded-xl text-center hover:bg-gray-200 dark:hover:bg-gray-600">
                        {{ __('Скасувати') }}
                    </a>
                    <button type="submit"
                            class="flex-1 px-4 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl">
                        {{ __('Підтвердити') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection