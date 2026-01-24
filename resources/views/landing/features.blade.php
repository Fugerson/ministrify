@extends('layouts.landing')

@section('title', 'Можливості — Ministrify | Функції системи управління церквою')
@section('description', 'Повний список можливостей Ministrify: управління членами, події, пожертви, групи, команди, Telegram бот, звіти та багато іншого.')

@section('content')
{{-- Hero --}}
<section class="pt-32 pb-16 bg-gradient-to-b from-primary-50 to-white dark:from-gray-900 dark:to-gray-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <span class="inline-block px-4 py-1 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 text-sm font-medium mb-4">Можливості</span>
            <h1 class="text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-6">
                Все необхідне для вашої церкви
            </h1>
            <p class="text-xl text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                Потужні інструменти, прості у використанні. Створено спеціально для потреб українських церков.
            </p>
        </div>
    </div>
</section>

{{-- Dashboard Preview --}}
<section class="py-16 bg-gray-50 dark:bg-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Зручний дашборд</h2>
            <p class="text-lg text-gray-600 dark:text-gray-400">Вся важлива інформація на одному екрані</p>
        </div>
        <div class="relative max-w-5xl mx-auto">
            <div class="absolute inset-0 bg-gradient-to-r from-primary-500 to-indigo-600 rounded-2xl transform rotate-1 opacity-20 dark:opacity-30"></div>
            <div class="relative bg-gradient-to-br from-primary-500 to-indigo-600 rounded-2xl p-1 shadow-2xl">
                <img src="/icons/demo/Screenshot_7.jpg" alt="Ministrify Dashboard" class="rounded-xl w-full">
            </div>
        </div>
    </div>
</section>

{{-- Features --}}
<section class="py-20 bg-white dark:bg-gray-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="space-y-32">
            {{-- Members --}}
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div>
                    <div class="inline-flex items-center px-3 py-1 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Члени церкви
                    </div>
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Повна база ваших людей</h2>
                    <p class="text-lg text-gray-600 dark:text-gray-400 mb-6">
                        Зберігайте всю інформацію про членів церкви в одному місці. Контакти, сім'ї, дні народження, участь у командах.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-center text-gray-700 dark:text-gray-300">
                            <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Детальні профілі з фото
                        </li>
                        <li class="flex items-center text-gray-700 dark:text-gray-300">
                            <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Зв'язки сімей та опікуни
                        </li>
                        <li class="flex items-center text-gray-700 dark:text-gray-300">
                            <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Теги, фільтрація та пошук
                        </li>
                        <li class="flex items-center text-gray-700 dark:text-gray-300">
                            <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Імпорт з Excel/CSV
                        </li>
                    </ul>
                </div>
                <div class="relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-2xl transform rotate-3 opacity-20 dark:opacity-30"></div>
                    <div class="relative bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl p-1 shadow-xl">
                        <img src="/icons/demo/Screenshot_2.jpg" alt="Управління членами церкви" class="rounded-xl w-full">
                    </div>
                </div>
            </div>

            {{-- Finance --}}
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div class="order-2 lg:order-1 relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-green-500 to-emerald-600 rounded-2xl transform -rotate-3 opacity-20 dark:opacity-30"></div>
                    <div class="relative bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl p-1 shadow-xl">
                        <img src="/icons/demo/Screenshot_3.jpg" alt="Фінансовий облік церкви" class="rounded-xl w-full">
                    </div>
                </div>
                <div class="order-1 lg:order-2">
                    <div class="inline-flex items-center px-3 py-1 rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Фінанси
                    </div>
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Повний фінансовий облік</h2>
                    <p class="text-lg text-gray-600 dark:text-gray-400 mb-6">
                        Ведіть облік пожертв, десятин та витрат. Детальні звіти по категоріях та командах. Бюджетування та аналітика.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-center text-gray-700 dark:text-gray-300">
                            <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Десятини та пожертви
                        </li>
                        <li class="flex items-center text-gray-700 dark:text-gray-300">
                            <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Витрати по командах
                        </li>
                        <li class="flex items-center text-gray-700 dark:text-gray-300">
                            <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Графіки та динаміка
                        </li>
                        <li class="flex items-center text-gray-700 dark:text-gray-300">
                            <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Експорт в CSV/Excel
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Events --}}
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div>
                    <div class="inline-flex items-center px-3 py-1 rounded-full bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Календар
                    </div>
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Події та розклад</h2>
                    <p class="text-lg text-gray-600 dark:text-gray-400 mb-6">
                        Плануйте богослужіння, зустрічі груп та заходи. Автоматичні нагадування та синхронізація з Google Calendar.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-center text-gray-700 dark:text-gray-300">
                            <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Візуальний календар
                        </li>
                        <li class="flex items-center text-gray-700 dark:text-gray-300">
                            <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Призначення волонтерів
                        </li>
                        <li class="flex items-center text-gray-700 dark:text-gray-300">
                            <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Google Calendar sync
                        </li>
                        <li class="flex items-center text-gray-700 dark:text-gray-300">
                            <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Облік відвідуваності
                        </li>
                    </ul>
                </div>
                <div class="relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-purple-500 to-pink-600 rounded-2xl transform rotate-3 opacity-20 dark:opacity-30"></div>
                    <div class="relative bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl p-1 shadow-xl">
                        <img src="/icons/demo/Screenshot_4.jpg" alt="Календар подій церкви" class="rounded-xl w-full">
                    </div>
                </div>
            </div>

            {{-- Teams --}}
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div class="order-2 lg:order-1 relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-orange-500 to-amber-600 rounded-2xl transform -rotate-3 opacity-20 dark:opacity-30"></div>
                    <div class="relative bg-gradient-to-br from-orange-500 to-amber-600 rounded-2xl p-1 shadow-xl">
                        <img src="/icons/demo/Screenshot_5.jpg" alt="Команди служіння" class="rounded-xl w-full">
                    </div>
                </div>
                <div class="order-1 lg:order-2">
                    <div class="inline-flex items-center px-3 py-1 rounded-full bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        Команди
                    </div>
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Команди служіння</h2>
                    <p class="text-lg text-gray-600 dark:text-gray-400 mb-6">
                        Організуйте команди служіння, призначайте лідерів та учасників. Відстежуйте активність та плануйте ротацію.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-center text-gray-700 dark:text-gray-300">
                            <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Позиції та ролі
                        </li>
                        <li class="flex items-center text-gray-700 dark:text-gray-300">
                            <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Лідери команд
                        </li>
                        <li class="flex items-center text-gray-700 dark:text-gray-300">
                            <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Цілі та завдання
                        </li>
                        <li class="flex items-center text-gray-700 dark:text-gray-300">
                            <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Бюджет команди
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Tasks --}}
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div>
                    <div class="inline-flex items-center px-3 py-1 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        Завдання
                    </div>
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Трекер завдань</h2>
                    <p class="text-lg text-gray-600 dark:text-gray-400 mb-6">
                        Kanban-дошка для планування та відстеження завдань команд. Пріоритети, дедлайни, виконавці.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-center text-gray-700 dark:text-gray-300">
                            <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Kanban-дошка
                        </li>
                        <li class="flex items-center text-gray-700 dark:text-gray-300">
                            <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Пріоритети та дедлайни
                        </li>
                        <li class="flex items-center text-gray-700 dark:text-gray-300">
                            <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Фільтри по командах
                        </li>
                        <li class="flex items-center text-gray-700 dark:text-gray-300">
                            <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Коментарі та вкладення
                        </li>
                    </ul>
                </div>
                <div class="relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-indigo-500 to-violet-600 rounded-2xl transform rotate-3 opacity-20 dark:opacity-30"></div>
                    <div class="relative bg-gradient-to-br from-indigo-500 to-violet-600 rounded-2xl p-1 shadow-xl">
                        <img src="/icons/demo/Screenshot_6.jpg" alt="Трекер завдань" class="rounded-xl w-full">
                    </div>
                </div>
            </div>

            {{-- Telegram --}}
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div class="order-2 lg:order-1 relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-sky-500 to-blue-600 rounded-2xl transform -rotate-3 opacity-20 dark:opacity-30"></div>
                    <div class="relative bg-gradient-to-br from-sky-500 to-blue-600 rounded-2xl p-1">
                        <div class="bg-white dark:bg-gray-900 rounded-xl p-8 aspect-video flex items-center justify-center">
                            <div class="text-center">
                                <div class="w-24 h-24 mx-auto mb-6 bg-sky-100 dark:bg-sky-900/50 rounded-full flex items-center justify-center">
                                    <svg class="w-14 h-14 text-sky-600 dark:text-sky-400" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Telegram бот</h3>
                                <p class="text-gray-500 dark:text-gray-400">Сповіщення та комунікація</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="order-1 lg:order-2">
                    <div class="inline-flex items-center px-3 py-1 rounded-full bg-sky-100 dark:bg-sky-900/30 text-sky-700 dark:text-sky-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4 mr-2" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                        </svg>
                        Telegram
                    </div>
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Telegram бот для церкви</h2>
                    <p class="text-lg text-gray-600 dark:text-gray-400 mb-6">
                        Автоматичні сповіщення волонтерам, нагадування про події, масові розсилки та індивідуальні повідомлення.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-center text-gray-700 dark:text-gray-300">
                            <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Власний бот церкви
                        </li>
                        <li class="flex items-center text-gray-700 dark:text-gray-300">
                            <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Нагадування про події
                        </li>
                        <li class="flex items-center text-gray-700 dark:text-gray-300">
                            <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Масові розсилки
                        </li>
                        <li class="flex items-center text-gray-700 dark:text-gray-300">
                            <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Підтвердження участі
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- More features grid --}}
<section class="py-20 bg-gray-50 dark:bg-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">І ще багато іншого</h2>
            <p class="text-lg text-gray-600 dark:text-gray-400">Все що потрібно для ефективного управління церквою</p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            {{-- Малі групи --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1 border border-gray-100 dark:border-gray-700">
                <div class="w-14 h-14 bg-teal-100 dark:bg-teal-900/30 rounded-xl flex items-center justify-center mb-6">
                    <svg class="w-7 h-7 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Малі групи</h3>
                <p class="text-gray-600 dark:text-gray-400">Домашні групи, лідери, відвідуваність та статистика</p>
            </div>

            {{-- Відвідуваність --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1 border border-gray-100 dark:border-gray-700">
                <div class="w-14 h-14 bg-rose-100 dark:bg-rose-900/30 rounded-xl flex items-center justify-center mb-6">
                    <svg class="w-7 h-7 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Відвідуваність</h3>
                <p class="text-gray-600 dark:text-gray-400">Облік відвідуваності богослужінь та груп з аналітикою</p>
            </div>

            {{-- Звіти --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1 border border-gray-100 dark:border-gray-700">
                <div class="w-14 h-14 bg-cyan-100 dark:bg-cyan-900/30 rounded-xl flex items-center justify-center mb-6">
                    <svg class="w-7 h-7 text-cyan-600 dark:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Звіти</h3>
                <p class="text-gray-600 dark:text-gray-400">Детальна аналітика, графіки та експорт даних</p>
            </div>

            {{-- Ресурси --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1 border border-gray-100 dark:border-gray-700">
                <div class="w-14 h-14 bg-amber-100 dark:bg-amber-900/30 rounded-xl flex items-center justify-center mb-6">
                    <svg class="w-7 h-7 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Ресурси</h3>
                <p class="text-gray-600 dark:text-gray-400">Файлове сховище для документів, презентацій та медіа</p>
            </div>

            {{-- Комунікації --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1 border border-gray-100 dark:border-gray-700">
                <div class="w-14 h-14 bg-pink-100 dark:bg-pink-900/30 rounded-xl flex items-center justify-center mb-6">
                    <svg class="w-7 h-7 text-pink-600 dark:text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Комунікації</h3>
                <p class="text-gray-600 dark:text-gray-400">Оголошення, приватні повідомлення та масові розсилки</p>
            </div>

            {{-- Мобільна версія --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1 border border-gray-100 dark:border-gray-700">
                <div class="w-14 h-14 bg-violet-100 dark:bg-violet-900/30 rounded-xl flex items-center justify-center mb-6">
                    <svg class="w-7 h-7 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Мобільна версія</h3>
                <p class="text-gray-600 dark:text-gray-400">PWA додаток — працює на телефоні як нативний застосунок</p>
            </div>
        </div>
    </div>
</section>

{{-- Settings Preview --}}
<section class="py-16 bg-white dark:bg-gray-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <div class="inline-flex items-center px-3 py-1 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-sm font-medium mb-4">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Налаштування
                </div>
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Гнучкі налаштування</h2>
                <p class="text-lg text-gray-600 dark:text-gray-400 mb-6">
                    Повний контроль над системою: права доступу, категорії, інтеграції та персоналізація під вашу церкву.
                </p>
                <ul class="space-y-3">
                    <li class="flex items-center text-gray-700 dark:text-gray-300">
                        <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Права доступу по ролях
                    </li>
                    <li class="flex items-center text-gray-700 dark:text-gray-300">
                        <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Церковні ролі та опікуни
                    </li>
                    <li class="flex items-center text-gray-700 dark:text-gray-300">
                        <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Категорії фінансів
                    </li>
                    <li class="flex items-center text-gray-700 dark:text-gray-300">
                        <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Інтеграція з Google та Telegram
                    </li>
                </ul>
            </div>
            <div class="relative">
                <div class="absolute inset-0 bg-gradient-to-r from-gray-500 to-slate-600 rounded-2xl transform rotate-3 opacity-20 dark:opacity-30"></div>
                <div class="relative bg-gradient-to-br from-gray-500 to-slate-600 rounded-2xl p-1 shadow-xl">
                    <img src="/icons/demo/Screenshot_8.jpg" alt="Налаштування системи" class="rounded-xl w-full">
                </div>
            </div>
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="py-20 bg-gradient-to-br from-primary-600 to-primary-700">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">Готові почати?</h2>
        <p class="text-xl text-primary-100 mb-8">Спробуйте безкоштовно — без зобов'язань та обмежень на час.</p>
        <a href="{{ url('/register-church') }}" class="inline-flex items-center px-8 py-4 bg-white text-primary-600 font-semibold rounded-xl hover:bg-primary-50 transition-all shadow-lg hover:shadow-xl hover:scale-105">
            Почати безкоштовно
            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
        </a>
    </div>
</section>
@endsection
