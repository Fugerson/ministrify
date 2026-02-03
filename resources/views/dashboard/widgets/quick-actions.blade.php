{{-- Quick Actions Widget --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-4 lg:px-5 py-4 border-b border-gray-200 dark:border-gray-700">
        <h2 class="font-semibold text-gray-900 dark:text-white">Швидкі дії</h2>
    </div>
    <div class="p-4">
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
            {{-- Додати людину --}}
            <a href="{{ route('people.create') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl bg-blue-50 dark:bg-blue-900/30 border border-blue-100 dark:border-blue-800 hover:bg-blue-100 dark:hover:bg-blue-900/50 hover:shadow-md transition-all group">
                <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </div>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300 text-center">Додати людину</span>
            </a>

            {{-- Створити подію --}}
            <a href="{{ route('events.create') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl bg-amber-50 dark:bg-amber-900/30 border border-amber-100 dark:border-amber-800 hover:bg-amber-100 dark:hover:bg-amber-900/50 hover:shadow-md transition-all group">
                <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11v4m0 0h-2m2 0h2"/>
                    </svg>
                </div>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300 text-center">Створити подію</span>
            </a>

            {{-- Нова транзакція (admin) --}}
            @admin
            <a href="{{ route('finances.incomes.create') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl bg-green-50 dark:bg-green-900/30 border border-green-100 dark:border-green-800 hover:bg-green-100 dark:hover:bg-green-900/50 hover:shadow-md transition-all group">
                <div class="w-10 h-10 rounded-xl bg-green-100 dark:bg-green-900 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300 text-center">Нова транзакція</span>
            </a>
            @endadmin

            {{-- Написати оголошення (admin) --}}
            @admin
            <a href="{{ route('announcements.create') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl bg-purple-50 dark:bg-purple-900/30 border border-purple-100 dark:border-purple-800 hover:bg-purple-100 dark:hover:bg-purple-900/50 hover:shadow-md transition-all group">
                <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-900 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                    </svg>
                </div>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300 text-center">Оголошення</span>
            </a>
            @endadmin

            {{-- Створити групу --}}
            <a href="{{ route('groups.create') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-100 dark:border-indigo-800 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 hover:shadow-md transition-all group">
                <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300 text-center">Створити групу</span>
            </a>

            {{-- Переглянути звіти (admin) --}}
            @admin
            <a href="{{ route('reports.index') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl bg-rose-50 dark:bg-rose-900/30 border border-rose-100 dark:border-rose-800 hover:bg-rose-100 dark:hover:bg-rose-900/50 hover:shadow-md transition-all group">
                <div class="w-10 h-10 rounded-xl bg-rose-100 dark:bg-rose-900 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300 text-center">Звіти</span>
            </a>
            @endadmin
        </div>
    </div>
</div>
