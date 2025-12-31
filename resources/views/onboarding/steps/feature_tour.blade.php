<div x-data="{ currentSlide: 0, autoPlay: null }"
     x-init="autoPlay = setInterval(() => { currentSlide = (currentSlide + 1) % 5 }, 5000)"
     @mouseenter="clearInterval(autoPlay)"
     @mouseleave="autoPlay = setInterval(() => { currentSlide = (currentSlide + 1) % 5 }, 5000)">

    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center shadow-lg shadow-primary-500/30">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/>
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-primary-600 dark:text-primary-400 mb-0.5">КРОК 6 - ФІНАЛ</p>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Огляд функцій</h2>
            </div>
        </div>
    </div>

    <!-- Slideshow -->
    <div class="relative bg-gradient-to-br from-gray-50 to-gray-100/50 dark:from-slate-800/50 dark:to-slate-900/50 rounded-3xl overflow-hidden mb-6 border border-gray-200/50 dark:border-slate-700/50 shadow-xl">
        <!-- Slides -->
        <div class="relative h-80 md:h-96">
            <!-- Slide 1: Dashboard -->
            <div x-show="currentSlide === 0"
                 x-transition:enter="transition ease-out duration-500"
                 x-transition:enter-start="opacity-0 transform translate-x-8"
                 x-transition:enter-end="opacity-100 transform translate-x-0"
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="opacity-100 transform translate-x-0"
                 x-transition:leave-end="opacity-0 transform -translate-x-8"
                 class="absolute inset-0 p-8 flex flex-col items-center justify-center text-center">
                <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-3xl flex items-center justify-center mb-5 shadow-2xl">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Dashboard</h3>
                <p class="text-gray-600 dark:text-gray-400 max-w-md leading-relaxed">
                    Головна панель з оглядом статистики, найближчих подій, та важливих сповіщень. Все в одному місці.
                </p>
            </div>

            <!-- Slide 2: Calendar -->
            <div x-show="currentSlide === 1"
                 x-transition:enter="transition ease-out duration-500"
                 x-transition:enter-start="opacity-0 transform translate-x-8"
                 x-transition:enter-end="opacity-100 transform translate-x-0"
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="opacity-100 transform translate-x-0"
                 x-transition:leave-end="opacity-0 transform -translate-x-8"
                 class="absolute inset-0 p-8 flex flex-col items-center justify-center text-center">
                <div class="w-20 h-20 bg-gradient-to-br from-green-500 to-emerald-600 rounded-3xl flex items-center justify-center mb-5 shadow-2xl">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Календар та події</h3>
                <p class="text-gray-600 dark:text-gray-400 max-w-md leading-relaxed">
                    Плануйте богослужіння, зустрічі та заходи. Автоматичні нагадування для служителів та синхронізація.
                </p>
            </div>

            <!-- Slide 3: People -->
            <div x-show="currentSlide === 2"
                 x-transition:enter="transition ease-out duration-500"
                 x-transition:enter-start="opacity-0 transform translate-x-8"
                 x-transition:enter-end="opacity-100 transform translate-x-0"
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="opacity-100 transform translate-x-0"
                 x-transition:leave-end="opacity-0 transform -translate-x-8"
                 class="absolute inset-0 p-8 flex flex-col items-center justify-center text-center">
                <div class="w-20 h-20 bg-gradient-to-br from-cyan-500 to-blue-600 rounded-3xl flex items-center justify-center mb-5 shadow-2xl">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Управління людьми</h3>
                <p class="text-gray-600 dark:text-gray-400 max-w-md leading-relaxed">
                    База даних членів церкви з тегами, групами та відстеженням відвідуваності. Легкий пошук та фільтрація.
                </p>
            </div>

            <!-- Slide 4: Finances -->
            <div x-show="currentSlide === 3"
                 x-transition:enter="transition ease-out duration-500"
                 x-transition:enter-start="opacity-0 transform translate-x-8"
                 x-transition:enter-end="opacity-100 transform translate-x-0"
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="opacity-100 transform translate-x-0"
                 x-transition:leave-end="opacity-0 transform -translate-x-8"
                 class="absolute inset-0 p-8 flex flex-col items-center justify-center text-center">
                <div class="w-20 h-20 bg-gradient-to-br from-amber-500 to-orange-600 rounded-3xl flex items-center justify-center mb-5 shadow-2xl">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Фінанси</h3>
                <p class="text-gray-600 dark:text-gray-400 max-w-md leading-relaxed">
                    Облік доходів та витрат з категоріями, графіками та звітами. Інтеграція з платіжними системами.
                </p>
            </div>

            <!-- Slide 5: Communication -->
            <div x-show="currentSlide === 4"
                 x-transition:enter="transition ease-out duration-500"
                 x-transition:enter-start="opacity-0 transform translate-x-8"
                 x-transition:enter-end="opacity-100 transform translate-x-0"
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="opacity-100 transform translate-x-0"
                 x-transition:leave-end="opacity-0 transform -translate-x-8"
                 class="absolute inset-0 p-8 flex flex-col items-center justify-center text-center">
                <div class="w-20 h-20 bg-gradient-to-br from-cyan-500 to-teal-600 rounded-3xl flex items-center justify-center mb-5 shadow-2xl">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Комунікація</h3>
                <p class="text-gray-600 dark:text-gray-400 max-w-md leading-relaxed">
                    Telegram-бот для сповіщень, масові розсилки, особисті повідомлення та оголошення для всієї церкви.
                </p>
            </div>
        </div>

        <!-- Navigation Dots -->
        <div class="flex justify-center gap-3 py-5">
            <template x-for="(_, i) in 5" :key="i">
                <button @click="currentSlide = i"
                        class="w-3 h-3 rounded-full transition-all duration-300"
                        :class="currentSlide === i ? 'bg-gradient-to-r from-primary-500 to-emerald-600 w-8 shadow-lg' : 'bg-gray-300 dark:bg-slate-600 hover:bg-gray-400 dark:hover:bg-slate-500'">
                </button>
            </template>
        </div>

        <!-- Arrow Navigation -->
        <button @click="currentSlide = (currentSlide - 1 + 5) % 5"
                class="absolute left-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-white/90 dark:bg-slate-700/90 backdrop-blur-sm rounded-2xl shadow-xl flex items-center justify-center text-gray-600 dark:text-gray-300 hover:bg-white dark:hover:bg-slate-600 transition-all hover:scale-110">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>
        <button @click="currentSlide = (currentSlide + 1) % 5"
                class="absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-white/90 dark:bg-slate-700/90 backdrop-blur-sm rounded-2xl shadow-xl flex items-center justify-center text-gray-600 dark:text-gray-300 hover:bg-white dark:hover:bg-slate-600 transition-all hover:scale-110">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </button>
    </div>

    <!-- Completion Message -->
    <div class="bg-gradient-to-r from-primary-500/10 via-emerald-500/10 to-teal-500/10 dark:from-primary-500/20 dark:via-emerald-500/20 dark:to-teal-500/20 rounded-2xl p-8 text-center border border-primary-200/50 dark:border-primary-700/30">
        <div class="w-16 h-16 mx-auto mb-4 bg-gradient-to-br from-primary-500 to-emerald-600 rounded-2xl flex items-center justify-center shadow-2xl">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
            </svg>
        </div>
        <h4 class="font-bold text-gray-900 dark:text-white text-xl mb-2">Вітаємо!</h4>
        <p class="text-gray-600 dark:text-gray-400 max-w-md mx-auto">
            Ви готові почати користуватися Ministrify. Натисніть "Завершити" щоб перейти до панелі управління.
        </p>
    </div>

    <form class="hidden">
        <input type="hidden" name="tour_completed" value="1">
    </form>
</div>
