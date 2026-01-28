@extends('layouts.landing')

@section('title', 'Документація — Ministrify')
@section('description', 'Документація та інструкції з використання Ministrify - системи управління церквою.')

@section('content')
<section class="pt-32 pb-20 bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-950 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <span class="inline-block px-4 py-1 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-sm font-medium mb-4">Документація</span>
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">Як користуватись Ministrify</h1>
            <p class="text-lg text-gray-600 dark:text-gray-400">Покрокові інструкції для початку роботи</p>
        </div>

        <div class="space-y-8">
            {{-- Початок роботи --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center mr-4">
                        <span class="text-blue-600 dark:text-blue-400 font-bold">1</span>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Початок роботи</h2>
                </div>
                <div class="prose dark:prose-invert max-w-none text-gray-600 dark:text-gray-400">
                    <ol class="space-y-2">
                        <li>Зареєструйте церкву на сторінці <a href="{{ url('/register-church') }}" class="text-primary-600 hover:underline">реєстрації</a></li>
                        <li>Підтвердіть email адресу</li>
                        <li>Увійдіть в систему та пройдіть початкове налаштування</li>
                        <li>Додайте перших членів церкви</li>
                    </ol>
                </div>
            </div>

            {{-- Люди --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center mr-4">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Управління людьми</h2>
                </div>
                <div class="text-gray-600 dark:text-gray-400 space-y-3">
                    <p><strong class="text-gray-900 dark:text-white">Додавання людей:</strong> Меню "Люди" → "Додати людину". Заповніть ім'я, контакти, теги.</p>
                    <p><strong class="text-gray-900 dark:text-white">Імпорт:</strong> Завантажте Excel/CSV файл зі списком членів церкви.</p>
                    <p><strong class="text-gray-900 dark:text-white">Фільтрація:</strong> Використовуйте теги та фільтри для швидкого пошуку.</p>
                </div>
            </div>

            {{-- Команди --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center mr-4">
                        <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Команди та події</h2>
                </div>
                <div class="text-gray-600 dark:text-gray-400 space-y-3">
                    <p><strong class="text-gray-900 dark:text-white">Створення команди:</strong> Налаштування → Команди. Вкажіть назву, колір, лідера.</p>
                    <p><strong class="text-gray-900 dark:text-white">Позиції:</strong> Додайте позиції (вокал, звук, камера тощо) для кожної команди.</p>
                    <p><strong class="text-gray-900 dark:text-white">Планування:</strong> Створіть подію в календарі та призначте волонтерів на позиції.</p>
                </div>
            </div>

            {{-- Telegram --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-sky-100 dark:bg-sky-900/30 rounded-xl flex items-center justify-center mr-4">
                        <svg class="w-5 h-5 text-sky-600 dark:text-sky-400" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Налаштування Telegram</h2>
                </div>
                <div class="text-gray-600 dark:text-gray-400 space-y-3">
                    <p><strong class="text-gray-900 dark:text-white">Крок 1:</strong> Створіть бота через @BotFather в Telegram</p>
                    <p><strong class="text-gray-900 dark:text-white">Крок 2:</strong> Скопіюйте токен та вставте в Налаштування → Telegram</p>
                    <p><strong class="text-gray-900 dark:text-white">Крок 3:</strong> Члени церкви можуть прив'язати Telegram в своєму профілі</p>
                </div>
            </div>

            {{-- Підтримка --}}
            <div class="bg-primary-50 dark:bg-primary-900/20 rounded-2xl p-8 border border-primary-100 dark:border-primary-800">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Потрібна допомога?</h2>
                <p class="text-gray-600 dark:text-gray-400 mb-4">Якщо у вас виникли питання або проблеми, зв'яжіться з нами.</p>
                <a href="{{ url('/contact') }}" class="inline-flex items-center px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition-colors">
                    Написати в підтримку
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
