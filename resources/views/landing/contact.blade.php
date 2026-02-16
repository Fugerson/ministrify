@extends('layouts.landing')

@section('title', 'Зв\'язатися з нами - Ministrify')
@section('description', 'Маєте питання про Ministrify? Напишіть нам і ми відповімо протягом 24 годин. Безкоштовна консультація для церков.')
@section('keywords', 'контакти Ministrify, підтримка, допомога, консультація для церков')

@section('schema')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "ContactPage",
    "name": "Контакти Ministrify",
    "description": "Зв'яжіться з командою Ministrify",
    "mainEntity": {
        "@type": "Organization",
        "name": "Ministrify",
        "email": "hello@ministrify.one",
        "areaServed": "UA",
        "availableLanguage": ["uk", "ru", "en"]
    }
}
</script>
@endsection

@section('content')
<!-- Hero Section -->
<section class="relative py-20 bg-gradient-to-br from-indigo-600 via-purple-600 to-indigo-800 overflow-hidden">
    <div class="absolute inset-0 bg-grid-white/10"></div>
    <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-white/20 to-transparent"></div>

    <div class="container mx-auto px-4 relative z-10">
        <div class="text-center max-w-3xl mx-auto">
            <h1 class="text-4xl md:text-5xl font-bold text-white mb-6">
                Зв'яжіться з нами
            </h1>
            <p class="text-xl text-indigo-100">
                Маєте питання або пропозиції? Ми завжди раді допомогти!
            </p>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section class="py-20 bg-white dark:bg-gray-900">
    <div class="container mx-auto px-4">
        <div class="grid lg:grid-cols-2 gap-16 max-w-6xl mx-auto">

            <!-- Contact Form -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                    Напишіть нам
                </h2>

                @if(session('success'))
                    <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl text-green-700 dark:text-green-400">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            {{ session('success') }}
                        </div>
                    </div>
                @endif

                <form action="{{ route('landing.contact.send') }}" method="POST" class="space-y-6"
                      x-data="{ submitting: false }"
                      x-on:submit.prevent="
                          submitting = true;
                          if (typeof grecaptcha !== 'undefined') {
                              grecaptcha.ready(function() {
                                  grecaptcha.execute('{{ config('services.recaptcha.site_key') }}', {action: 'contact'}).then(function(token) {
                                      $refs.recaptchaToken.value = token;
                                      $el.submit();
                                  });
                              });
                          } else { $el.submit(); }
                      ">
                    @csrf
                    <x-spam-protection action="contact" />

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Ваше ім'я *
                        </label>
                        <input type="text" id="name" name="name" required
                               value="{{ old('name') }}"
                               class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('name') border-red-500 @enderror"
                               placeholder="Ваше ім'я">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Email *
                        </label>
                        <input type="email" id="email" name="email" required
                               value="{{ old('email') }}"
                               class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('email') border-red-500 @enderror"
                               >
                        @error('email')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="church" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Назва церкви
                        </label>
                        <input type="text" id="church" name="church"
                               value="{{ old('church') }}"
                               class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                               placeholder="Назва вашої церкви (необов'язково)">
                    </div>

                    <div>
                        <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Повідомлення *
                        </label>
                        <textarea id="message" name="message" rows="5" required
                                  class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all resize-none @error('message') border-red-500 @enderror"
                                  placeholder="Опишіть ваше питання або пропозицію...">{{ old('message') }}</textarea>
                        @error('message')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" :disabled="submitting"
                            class="w-full py-4 px-6 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 disabled:opacity-50">
                        <span x-show="!submitting">Надіслати повідомлення</span>
                        <span x-show="submitting" x-cloak>Надсилання...</span>
                    </button>
                </form>
            </div>

            <!-- Contact Info -->
            <div class="lg:pl-8">
                {{-- Приховано: Інші способи зв'язку
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                    Інші способи зв'язку
                </h2>

                <div class="space-y-6">
                    <!-- Email -->
                    <div class="flex items-start space-x-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-xl">
                        <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white">Email</h3>
                            <p class="text-gray-600 dark:text-gray-400">hello@ministrify.one</p>
                            <p class="text-sm text-gray-500 dark:text-gray-500 mt-1">Відповідаємо протягом 24 годин</p>
                        </div>
                    </div>

                    <!-- Telegram -->
                    <div class="flex items-start space-x-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-xl">
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-blue-500" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white">Telegram</h3>
                            <p class="text-gray-600 dark:text-gray-400">@ministrify_support</p>
                            <p class="text-sm text-gray-500 dark:text-gray-500 mt-1">Швидкі відповіді у робочий час</p>
                        </div>
                    </div>

                    <!-- Schedule -->
                    <div class="flex items-start space-x-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-xl">
                        <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white">Графік підтримки</h3>
                            <p class="text-gray-600 dark:text-gray-400">Пн-Пт: 9:00 - 18:00</p>
                            <p class="text-sm text-gray-500 dark:text-gray-500 mt-1">За київським часом (UTC+2)</p>
                        </div>
                    </div>
                </div>
                --}}

                <!-- FAQ Link -->
                <div class="p-6 bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-2xl border border-indigo-100 dark:border-indigo-800">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">
                        Часті питання
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">
                        Можливо, відповідь на ваше питання вже є у розділі FAQ.
                    </p>
                    <a href="{{ route('landing.faq') }}"
                       class="inline-flex items-center text-indigo-600 dark:text-indigo-400 font-medium hover:text-indigo-700 dark:hover:text-indigo-300">
                        Переглянути FAQ
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>

                <!-- Demo Request -->
                <div class="mt-6 p-6 bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 rounded-2xl border border-amber-100 dark:border-amber-800">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">
                        Хочете побачити демо?
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">
                        Запишіться на безкоштовну 30-хвилинну демонстрацію системи.
                    </p>
                    <a href="{{ route('landing.register') }}"
                       class="inline-flex items-center px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-medium rounded-lg transition-colors">
                        Спробувати безкоштовно
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Map Section (Optional visual) -->
<section class="py-16 bg-gray-50 dark:bg-gray-800">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-indigo-100 dark:bg-indigo-900/30 rounded-full mb-6">
                <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                Працюємо для церков по всій Україні
            </h2>
            <p class="text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                Ministrify — це хмарний сервіс, доступний з будь-якої точки світу.
                Ми допомагаємо церквам України та української діаспори ефективно управляти громадою.
            </p>

            <!-- Stats -->
            <div class="grid grid-cols-3 gap-8 mt-12 max-w-lg mx-auto">
                <div>
                    <div class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">24/7</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Доступність</div>
                </div>
                <div>
                    <div class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">&lt;24г</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Час відповіді</div>
                </div>
                <div>
                    <div class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">UA</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Підтримка</div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
