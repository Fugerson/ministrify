@extends('layouts.landing')

@section('title', 'FAQ — Ministrify')
@section('description', 'Часті питання про Ministrify - систему управління церквою.')

@section('content')
<section class="pt-32 pb-20 bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-950 min-h-screen">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <span class="inline-block px-4 py-1 rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 text-sm font-medium mb-4">FAQ</span>
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">Часті питання</h1>
            <p class="text-lg text-gray-600 dark:text-gray-400">Відповіді на найпопулярніші питання</p>
        </div>

        <div class="space-y-4" x-data="{ open: null }">
            {{-- FAQ Items --}}
            @php
                $faqs = [
                    [
                        'q' => 'Що таке Ministrify?',
                        'a' => 'Ministrify — це сучасна українська платформа для управління церквою. Вона допомагає вести облік членів, планувати події та служіння, керувати фінансами, організовувати групи та багато іншого.'
                    ],
                    [
                        'q' => 'Чи безкоштовний Ministrify?',
                        'a' => 'Так, наразі Ministrify повністю безкоштовний для всіх церков. Ми працюємо над преміум-функціями, але базовий функціонал залишиться безкоштовним.'
                    ],
                    [
                        'q' => 'Як додати членів церкви?',
                        'a' => 'Перейдіть в розділ "Люди" та натисніть "Додати людину". Також можна імпортувати список з Excel/CSV файлу.'
                    ],
                    [
                        'q' => 'Чи можна використовувати на телефоні?',
                        'a' => 'Так! Ministrify — це PWA (Progressive Web App). Ви можете додати його на головний екран телефону і користуватись як звичайним додатком.'
                    ],
                    [
                        'q' => 'Як налаштувати Telegram бота?',
                        'a' => 'Створіть бота через @BotFather в Telegram, отримайте токен та введіть його в Налаштування → Telegram. Після цього члени церкви зможуть отримувати сповіщення.'
                    ],
                    [
                        'q' => 'Чи безпечні мої дані?',
                        'a' => 'Так, ми серйозно ставимось до безпеки. Всі дані передаються через захищене з\'єднання (SSL), регулярно створюються резервні копії, доступ обмежений ролями користувачів.'
                    ],
                    [
                        'q' => 'Чи можна мати кілька церков?',
                        'a' => 'Так, один користувач може мати доступ до кількох церков. Просто попросіть адміністратора іншої церкви додати вас як користувача.'
                    ],
                    [
                        'q' => 'Як зв\'язатися з підтримкою?',
                        'a' => 'Напишіть нам через форму на сторінці Контакти або створіть тікет в системі підтримки всередині платформи.'
                    ],
                ];
            @endphp

            @foreach($faqs as $index => $faq)
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <button
                        @click="open = open === {{ $index }} ? null : {{ $index }}"
                        class="w-full px-6 py-5 text-left flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                    >
                        <span class="font-semibold text-gray-900 dark:text-white pr-4">{{ $faq['q'] }}</span>
                        <svg
                            class="w-5 h-5 text-gray-500 transition-transform duration-200 flex-shrink-0"
                            :class="{ 'rotate-180': open === {{ $index }} }"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div
                        x-show="open === {{ $index }}"
                        x-collapse
                        class="px-6 pb-5"
                    >
                        <p class="text-gray-600 dark:text-gray-400">{{ $faq['a'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- CTA --}}
        <div class="mt-12 text-center">
            <p class="text-gray-600 dark:text-gray-400 mb-4">Не знайшли відповідь на своє питання?</p>
            <a href="{{ url('/contact') }}" class="inline-flex items-center px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition-colors">
                Зв'язатися з нами
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </a>
        </div>
    </div>
</section>
@endsection
