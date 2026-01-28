@extends('layouts.landing')

@section('title', 'Політика приватності — Ministrify')
@section('description', 'Політика приватності та захисту персональних даних Ministrify.')

@section('content')
<section class="pt-32 pb-20 bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-950 min-h-screen">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">Політика приватності</h1>
            <p class="text-gray-600 dark:text-gray-400">Останнє оновлення: {{ now()->format('d.m.Y') }}</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="prose dark:prose-invert max-w-none text-gray-600 dark:text-gray-400">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mt-0">1. Вступ</h2>
                <p>Ministrify поважає вашу приватність та захищає ваші персональні дані. Ця політика пояснює, як ми збираємо, використовуємо та захищаємо вашу інформацію.</p>

                <h2 class="text-xl font-bold text-gray-900 dark:text-white">2. Які дані ми збираємо</h2>
                <p>Ми можемо збирати наступну інформацію:</p>
                <ul>
                    <li><strong>Реєстраційні дані:</strong> ім'я, email, назва церкви</li>
                    <li><strong>Дані членів церкви:</strong> імена, контакти, дати народження (вводяться вами)</li>
                    <li><strong>Технічні дані:</strong> IP-адреса, тип браузера, час відвідування</li>
                    <li><strong>Дані використання:</strong> які функції ви використовуєте</li>
                </ul>

                <h2 class="text-xl font-bold text-gray-900 dark:text-white">3. Як ми використовуємо дані</h2>
                <p>Ваші дані використовуються для:</p>
                <ul>
                    <li>Надання послуг платформи</li>
                    <li>Покращення функціональності сервісу</li>
                    <li>Технічної підтримки</li>
                    <li>Надсилання важливих сповіщень</li>
                </ul>

                <h2 class="text-xl font-bold text-gray-900 dark:text-white">4. Захист даних</h2>
                <p>Ми вживаємо заходів для захисту ваших даних:</p>
                <ul>
                    <li>SSL-шифрування всіх з'єднань</li>
                    <li>Хешування паролів</li>
                    <li>Регулярні резервні копії</li>
                    <li>Обмежений доступ до серверів</li>
                    <li>Моніторинг безпеки</li>
                </ul>

                <h2 class="text-xl font-bold text-gray-900 dark:text-white">5. Передача даних третім особам</h2>
                <p>Ми не продаємо і не передаємо ваші персональні дані третім особам, окрім випадків:</p>
                <ul>
                    <li>Коли це необхідно для надання послуг (хостинг, email)</li>
                    <li>На вимогу закону</li>
                    <li>За вашою явною згодою</li>
                </ul>

                <h2 class="text-xl font-bold text-gray-900 dark:text-white">6. Cookies</h2>
                <p>Ми використовуємо cookies для:</p>
                <ul>
                    <li>Підтримки вашої сесії (авторизація)</li>
                    <li>Збереження налаштувань (тема, мова)</li>
                    <li>Аналітики використання сервісу</li>
                </ul>

                <h2 class="text-xl font-bold text-gray-900 dark:text-white">7. Ваші права</h2>
                <p>Ви маєте право:</p>
                <ul>
                    <li>Отримати копію своїх даних</li>
                    <li>Виправити неточні дані</li>
                    <li>Видалити свої дані</li>
                    <li>Обмежити обробку даних</li>
                    <li>Відкликати згоду на обробку</li>
                </ul>

                <h2 class="text-xl font-bold text-gray-900 dark:text-white">8. Зберігання даних</h2>
                <p>Ми зберігаємо ваші дані протягом часу використання сервісу. Після видалення облікового запису дані видаляються протягом 30 днів, окрім резервних копій, які зберігаються до 90 днів.</p>

                <h2 class="text-xl font-bold text-gray-900 dark:text-white">9. Зміни політики</h2>
                <p>Ми можемо оновлювати цю політику. Про суттєві зміни ми повідомимо через email або сповіщення в системі.</p>

                <h2 class="text-xl font-bold text-gray-900 dark:text-white">10. Контакти</h2>
                <p>З питань приватності звертайтесь через <a href="{{ url('/contact') }}" class="text-primary-600 hover:underline">форму зворотного зв'язку</a>.</p>
            </div>
        </div>
    </div>
</section>
@endsection
