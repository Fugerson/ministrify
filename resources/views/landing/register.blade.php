@extends('layouts.landing')

@section('title', 'Зареєструвати церкву - Ministrify')
@section('description', 'Зареєструйте вашу церкву в Ministrify за 2 хвилини. Безкоштовний старт, без кредитної картки. Управління громадою стало простіше!')
@section('keywords', 'реєстрація церкви, Ministrify реєстрація, управління церквою безкоштовно')

@section('schema')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "WebPage",
    "name": "Реєстрація церкви в Ministrify",
    "description": "Швидка реєстрація церкви для доступу до всіх функцій управління громадою",
    "potentialAction": {
        "@type": "RegisterAction",
        "target": {
            "@type": "EntryPoint",
            "urlTemplate": "{{ url('/register-church') }}"
        }
    }
}
</script>
@endsection

@section('content')
<div class="relative min-h-screen bg-gradient-to-br from-indigo-600 via-purple-600 to-indigo-800 flex items-center justify-center pt-24 pb-12 px-4 overflow-hidden" x-data="{ showPassword: false }">
    <!-- Background Pattern -->
    <div class="absolute inset-0 bg-grid-white/10 pointer-events-none"></div>
    <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent pointer-events-none"></div>

    <!-- Floating Elements -->
    <div class="absolute top-20 left-10 w-72 h-72 bg-purple-500 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob pointer-events-none"></div>
    <div class="absolute top-40 right-10 w-72 h-72 bg-indigo-500 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob animation-delay-2000 pointer-events-none"></div>
    <div class="absolute bottom-20 left-1/2 w-72 h-72 bg-pink-500 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob animation-delay-4000 pointer-events-none"></div>

    <div class="relative z-10 w-full max-w-lg">
        <!-- Logo -->
        <div class="text-center mb-8">
            <a href="{{ route('landing.home') }}" class="inline-flex items-center space-x-2">
                <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center shadow-lg">
                    <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <span class="text-2xl font-bold text-white">Ministrify</span>
            </a>
        </div>

        <!-- Registration Card -->
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl p-8">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                    Зареєструйте вашу церкву
                </h1>
                <p class="text-gray-600 dark:text-gray-400">
                    Безкоштовний старт за 2 хвилини
                </p>
            </div>

            @if($errors->any())
                <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl text-red-700 dark:text-red-400">
                    <ul class="list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('landing.register.process') }}" method="POST" class="space-y-5">
                @csrf

                <!-- Church Info Section -->
                <div class="space-y-4">
                    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Інформація про церкву
                    </h3>

                    <div>
                        <label for="church_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Назва церкви *
                        </label>
                        <input type="text" id="church_name" name="church_name" required
                               value="{{ old('church_name') }}"
                               class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                               placeholder="Церква 'Благодать'">
                    </div>

                    <div>
                        <label for="city" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Місто *
                        </label>
                        <input type="text" id="city" name="city" required
                               value="{{ old('city') }}"
                               class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                               placeholder="Київ">
                    </div>
                </div>

                <!-- Admin Info Section -->
                <div class="space-y-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Адміністратор церкви
                    </h3>

                    <div>
                        <label for="admin_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Ваше ім'я *
                        </label>
                        <input type="text" id="admin_name" name="admin_name" required
                               value="{{ old('admin_name') }}"
                               class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                               placeholder="Іван Петренко">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Email *
                        </label>
                        <input type="email" id="email" name="email" required
                               value="{{ old('email') }}"
                               class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                               placeholder="admin@church.ua">
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Телефон
                        </label>
                        <input type="tel" id="phone" name="phone"
                               value="{{ old('phone') }}"
                               class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                               placeholder="+380 XX XXX XX XX">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Пароль *
                        </label>
                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'" id="password" name="password" required minlength="8"
                                   class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all pr-12"
                                   placeholder="Мінімум 8 символів">
                            <button type="button" @click="showPassword = !showPassword"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Підтвердження пароля *
                        </label>
                        <input :type="showPassword ? 'text' : 'password'" id="password_confirmation" name="password_confirmation" required
                               class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                               placeholder="Повторіть пароль">
                    </div>
                </div>

                <!-- Terms -->
                <div class="flex items-start space-x-3 pt-2">
                    <input type="checkbox" id="terms" name="terms" required
                           class="mt-1 w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <label for="terms" class="text-sm text-gray-600 dark:text-gray-400">
                        Я погоджуюсь з <a href="#" class="text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">умовами використання</a>
                        та <a href="#" class="text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">політикою конфіденційності</a>
                    </label>
                </div>

                <!-- Submit -->
                <button type="submit"
                        class="w-full py-4 px-6 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                    Зареєструвати церкву
                </button>
            </form>

            <!-- Login Link -->
            <p class="mt-6 text-center text-gray-600 dark:text-gray-400">
                Вже маєте акаунт?
                <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 font-medium">
                    Увійти
                </a>
            </p>
        </div>

        <!-- Features List -->
        <div class="mt-8 grid grid-cols-3 gap-4 text-center text-white/80 text-sm">
            <div class="flex flex-col items-center">
                <svg class="w-6 h-6 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span>Безкоштовно</span>
            </div>
            <div class="flex flex-col items-center">
                <svg class="w-6 h-6 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <span>Безпечно</span>
            </div>
            <div class="flex flex-col items-center">
                <svg class="w-6 h-6 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <span>2 хвилини</span>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes blob {
        0% { transform: translate(0px, 0px) scale(1); }
        33% { transform: translate(30px, -50px) scale(1.1); }
        66% { transform: translate(-20px, 20px) scale(0.9); }
        100% { transform: translate(0px, 0px) scale(1); }
    }
    .animate-blob {
        animation: blob 7s infinite;
    }
    .animation-delay-2000 {
        animation-delay: 2s;
    }
    .animation-delay-4000 {
        animation-delay: 4s;
    }
</style>
@endsection
