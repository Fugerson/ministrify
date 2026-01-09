@extends('layouts.landing')

@section('title', 'Умови використання — Ministrify')
@section('description', 'Умови використання сервісу Ministrify.')

@section('content')
<section class="pt-32 pb-20 bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-950 min-h-screen">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">Умови використання</h1>
            <p class="text-gray-600 dark:text-gray-400">Останнє оновлення: {{ now()->format('d.m.Y') }}</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="prose dark:prose-invert max-w-none text-gray-600 dark:text-gray-400">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mt-0">1. Загальні положення</h2>
                <p>Ці Умови використання регулюють відносини між користувачами та сервісом Ministrify. Використовуючи наш сервіс, ви погоджуєтесь з цими умовами.</p>

                <h2 class="text-xl font-bold text-gray-900 dark:text-white">2. Опис сервісу</h2>
                <p>Ministrify — це онлайн-платформа для управління церквою, яка надає інструменти для:</p>
                <ul>
                    <li>Ведення бази членів церкви</li>
                    <li>Планування подій та служінь</li>
                    <li>Обліку фінансів</li>
                    <li>Організації груп та команд</li>
                    <li>Комунікації через Telegram</li>
                </ul>

                <h2 class="text-xl font-bold text-gray-900 dark:text-white">3. Реєстрація та обліковий запис</h2>
                <p>Для використання сервісу необхідно зареєструвати церкву та створити обліковий запис. Ви несете відповідальність за:</p>
                <ul>
                    <li>Достовірність наданої інформації</li>
                    <li>Безпеку вашого паролю</li>
                    <li>Всі дії, здійснені з вашого облікового запису</li>
                </ul>

                <h2 class="text-xl font-bold text-gray-900 dark:text-white">4. Правила використання</h2>
                <p>Використовуючи Ministrify, ви зобов'язуєтесь:</p>
                <ul>
                    <li>Не порушувати права інших користувачів</li>
                    <li>Не використовувати сервіс для незаконних цілей</li>
                    <li>Не завантажувати шкідливий контент</li>
                    <li>Дотримуватись законодавства України</li>
                </ul>

                <h2 class="text-xl font-bold text-gray-900 dark:text-white">5. Інтелектуальна власність</h2>
                <p>Всі права на програмне забезпечення, дизайн та контент Ministrify належать розробникам сервісу. Ви отримуєте лише право використання сервісу згідно з цими умовами.</p>

                <h2 class="text-xl font-bold text-gray-900 dark:text-white">6. Обмеження відповідальності</h2>
                <p>Ministrify надається "як є". Ми докладаємо зусиль для забезпечення безперебійної роботи, але не гарантуємо відсутність помилок чи перебоїв у роботі сервісу.</p>

                <h2 class="text-xl font-bold text-gray-900 dark:text-white">7. Зміни умов</h2>
                <p>Ми залишаємо за собою право змінювати ці умови. Про суттєві зміни ми повідомимо користувачів через email або сповіщення в системі.</p>

                <h2 class="text-xl font-bold text-gray-900 dark:text-white">8. Контакти</h2>
                <p>З питань щодо цих умов звертайтесь через <a href="{{ url('/contact') }}" class="text-primary-600 hover:underline">форму зворотного зв'язку</a>.</p>
            </div>
        </div>
    </div>
</section>
@endsection
