@extends('layouts.app')

@section('title', 'Люди')

@section('actions')
<a href="{{ route('people.create') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-xl hover:bg-primary-700 transition-colors">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
    </svg>
    Додати
</a>
@endsection

@section('content')
<div x-data="peopleTable()" class="space-y-4">
    <!-- Search & Stats Bar -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
        <div class="flex flex-col sm:flex-row gap-4">
            <!-- Search -->
            <div class="flex-1 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" x-model="filters.search" placeholder="Пошук по всіх полях..."
                    class="w-full pl-10 pr-10 py-2.5 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500/20">
                <button x-show="filters.search" @click="filters.search = ''" x-cloak
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <!-- Stats -->
            <div class="flex flex-wrap items-center gap-4 text-sm">
                <div class="flex items-center gap-2">
                    <span class="text-gray-500 dark:text-gray-400">Всього:</span>
                    <span class="font-semibold text-gray-900 dark:text-white">{{ $stats['total'] }}</span>
                </div>
                <div class="w-px h-4 bg-gray-200 dark:bg-gray-700"></div>
                <div class="flex items-center gap-2">
                    <span class="text-gray-500 dark:text-gray-400">Показано:</span>
                    <span class="font-semibold text-primary-600" x-text="filteredCount"></span>
                </div>
                @if($stats['serving'] > 0)
                <div class="w-px h-4 bg-gray-200 dark:bg-gray-700"></div>
                <div class="flex items-center gap-2">
                    <span class="text-gray-500 dark:text-gray-400">Служать:</span>
                    <span class="font-semibold text-green-600">{{ $stats['serving'] }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Table Container -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-3 md:px-4 py-3 text-left">
                            <div class="space-y-2">
                                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ім'я</span>
                                <input type="text" x-model="filters.name" placeholder="Фільтр..."
                                    class="w-full px-2 py-1.5 text-sm bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                            </div>
                        </th>
                        <th class="px-3 md:px-4 py-3 text-left hidden sm:table-cell">
                            <div class="space-y-2">
                                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Телефон</span>
                                <input type="text" x-model="filters.phone" placeholder="Фільтр..."
                                    class="w-full px-2 py-1.5 text-sm bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                            </div>
                        </th>
                        <th class="px-3 md:px-4 py-3 text-left hidden md:table-cell">
                            <div class="space-y-2">
                                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email</span>
                                <input type="text" x-model="filters.email" placeholder="Фільтр..."
                                    class="w-full px-2 py-1.5 text-sm bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                            </div>
                        </th>
                        <th class="px-4 py-3 text-left hidden lg:table-cell">
                            <div class="space-y-2">
                                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Дата народження</span>
                                <div class="relative">
                                    <input type="text" x-ref="dateRange" x-model="filters.dateRangeDisplay" placeholder="Виберіть дати..." readonly
                                        class="w-full px-2 py-1.5 text-sm bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 cursor-pointer">
                                    <button type="button" x-show="filters.birth_from || filters.birth_to" @click="clearDateFilter()"
                                        class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">
                            <div class="space-y-2">
                                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Служіння</span>
                                <select x-model="filters.ministry"
                                    class="w-full px-2 py-1.5 text-sm bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                                    <option value="">Всі</option>
                                    @foreach($ministries as $ministry)
                                    <option value="{{ $ministry->name }}">{{ $ministry->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </th>
                        <th class="px-4 py-3 text-left hidden xl:table-cell">
                            <div class="space-y-2">
                                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Роль</span>
                                <select x-model="filters.role"
                                    class="w-full px-2 py-1.5 text-sm bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 text-gray-900 dark:text-white">
                                    <option value="" class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white">Всі</option>
                                    @foreach($churchRoles as $role)
                                    <option value="{{ $role->name }}" class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </th>
                        @if($church->shepherds_enabled)
                        <th class="px-4 py-3 text-left hidden xl:table-cell">
                            <div class="space-y-2">
                                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Опікун</span>
                                <select x-model="filters.shepherd"
                                    class="w-full px-2 py-1.5 text-sm bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 text-gray-900 dark:text-white">
                                    <option value="" class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white">Всі</option>
                                    @foreach($shepherds as $shepherd)
                                    <option value="{{ $shepherd->full_name }}" class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white">{{ $shepherd->full_name }}</option>
                                    @endforeach
                                    <option value="none" class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white">Без опікуна</option>
                                </select>
                            </div>
                        </th>
                        @endif
                        <th class="px-2 md:px-4 py-3 w-10">
                            <button @click="clearFilters()" x-show="hasFilters" x-cloak
                                class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                title="Очистити фільтри">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($people as $index => $person)
                    <tr x-show="shouldShowRow({{ $index }}, @js([
                            'name' => $person->full_name,
                            'phone' => $person->phone ?? '',
                            'email' => $person->email ?? '',
                            'birth_date' => $person->birth_date?->format('Y-m-d') ?? '',
                            'ministry' => $person->ministries->pluck('name')->join(', '),
                            'role' => $person->churchRoleRelation?->name ?? '',
                            'shepherd' => $person->shepherd?->full_name ?? '',
                        ]))"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        class="hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer group"
                        onclick="window.location='{{ route('people.show', $person) }}'">
                        <!-- Name -->
                        <td class="px-3 md:px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if($person->photo)
                                <img src="{{ Storage::url($person->photo) }}" alt=""
                                     class="w-9 h-9 rounded-lg object-cover flex-shrink-0">
                                @else
                                <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center flex-shrink-0">
                                    <span class="text-xs font-semibold text-white">{{ mb_substr($person->first_name, 0, 1) }}{{ mb_substr($person->last_name, 0, 1) }}</span>
                                </div>
                                @endif
                                <div class="min-w-0">
                                    <div class="font-medium text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors truncate">
                                        {{ $person->full_name ?: '—' }}
                                    </div>
                                    <!-- Mobile: show phone/email under name -->
                                    <div class="sm:hidden text-xs text-gray-500 dark:text-gray-400 truncate">
                                        {{ $person->phone ?: $person->email ?: '' }}
                                    </div>
                                    @if($person->telegram_username)
                                    <div class="text-xs text-gray-400 hidden sm:block">{{ $person->telegram_username }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <!-- Phone -->
                        <td class="px-3 md:px-4 py-3 hidden sm:table-cell">
                            <span class="text-gray-600 dark:text-gray-300">{{ $person->phone ?: '—' }}</span>
                        </td>
                        <!-- Email -->
                        <td class="px-3 md:px-4 py-3 hidden md:table-cell">
                            <span class="text-gray-600 dark:text-gray-300 text-sm">{{ $person->email ?: '—' }}</span>
                        </td>
                        <!-- Birth Date -->
                        <td class="px-4 py-3 hidden lg:table-cell">
                            <span class="text-gray-600 dark:text-gray-300">
                                {{ $person->birth_date?->format('d.m.Y') ?: '—' }}
                            </span>
                            @if($person->birth_date)
                            <span class="text-xs text-gray-400 ml-1">({{ $person->birth_date->age }} р.)</span>
                            @endif
                        </td>
                        <!-- Ministries -->
                        <td class="px-4 py-3 hidden md:table-cell">
                            @if($person->ministries->isNotEmpty())
                            <div class="flex flex-wrap gap-1">
                                @foreach($person->ministries->take(2) as $ministry)
                                <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-md"
                                      style="background-color: {{ $ministry->color ?? '#6366f1' }}20; color: {{ $ministry->color ?? '#6366f1' }}">
                                    {{ $ministry->name }}
                                </span>
                                @endforeach
                                @if($person->ministries->count() > 2)
                                <span class="text-xs text-gray-400">+{{ $person->ministries->count() - 2 }}</span>
                                @endif
                            </div>
                            @else
                            <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <!-- Role -->
                        <td class="px-4 py-3 hidden xl:table-cell">
                            @if($person->churchRoleRelation)
                            <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-md"
                                  style="background-color: {{ $person->churchRoleRelation->color }}20; color: {{ $person->churchRoleRelation->color }}">
                                {{ $person->churchRoleRelation->name }}
                            </span>
                            @else
                            <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        @if($church->shepherds_enabled)
                        <!-- Shepherd -->
                        <td class="px-4 py-3 hidden xl:table-cell">
                            @if($person->shepherd)
                            <div class="flex items-center gap-2">
                                @if($person->shepherd->photo)
                                <img src="{{ Storage::url($person->shepherd->photo) }}" alt="" class="w-6 h-6 rounded-full object-cover">
                                @else
                                <div class="w-6 h-6 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                                    <span class="text-xs font-medium text-green-600 dark:text-green-400">{{ mb_substr($person->shepherd->first_name, 0, 1) }}</span>
                                </div>
                                @endif
                                <span class="text-sm text-gray-600 dark:text-gray-300">{{ $person->shepherd->full_name }}</span>
                            </div>
                            @else
                            <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        @endif
                        <!-- Action -->
                        <td class="px-2 md:px-4 py-3">
                            <a href="{{ route('people.show', $person) }}"
                               class="p-1.5 text-gray-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-lg transition-colors inline-block"
                               onclick="event.stopPropagation()">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center">
                            <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-gray-700 rounded-xl flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Поки що нікого немає</h3>
                            <p class="text-gray-500 dark:text-gray-400 mb-4">Додайте першу людину або імпортуйте дані</p>
                            <div class="flex items-center justify-center gap-3">
                                <a href="{{ route('people.create') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg font-medium hover:bg-primary-700 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    Додати
                                </a>
                                <a href="{{ route('migration.planning-center') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                    </svg>
                                    Імпорт
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- No results message (when filters hide all) -->
        <div x-show="filteredCount === 0 && {{ $people->count() }} > 0" x-cloak class="px-4 py-12 text-center">
            <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Нічого не знайдено</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-4">Спробуйте змінити параметри фільтрів</p>
            <button @click="clearFilters()" class="text-primary-600 hover:text-primary-700 font-medium">
                Очистити фільтри
            </button>
        </div>

        <!-- Pagination -->
        <div class="px-3 md:px-4 py-3 border-t border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                <span class="hidden sm:inline">Показувати</span>
                <select x-model.number="perPage" @change="currentPage = 1"
                    class="px-2 py-1.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-primary-500/20">
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="0">Всі</option>
                </select>
                <span class="hidden sm:inline">записів</span>
            </div>

            <div class="flex items-center gap-0.5">
                <button @click="currentPage = 1" :disabled="currentPage === 1"
                    class="w-10 h-10 sm:w-9 sm:h-9 flex items-center justify-center rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed active:bg-gray-200 dark:active:bg-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                    </svg>
                </button>
                <button @click="currentPage--" :disabled="currentPage === 1"
                    class="w-10 h-10 sm:w-9 sm:h-9 flex items-center justify-center rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed active:bg-gray-200 dark:active:bg-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>

                <span class="px-2 sm:px-3 py-1 text-sm text-gray-700 dark:text-gray-300 min-w-[4rem] text-center">
                    <span x-text="currentPage"></span> / <span x-text="totalPages"></span>
                </span>

                <button @click="currentPage++" :disabled="currentPage >= totalPages"
                    class="w-10 h-10 sm:w-9 sm:h-9 flex items-center justify-center rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed active:bg-gray-200 dark:active:bg-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                <button @click="currentPage = totalPages" :disabled="currentPage >= totalPages"
                    class="w-10 h-10 sm:w-9 sm:h-9 flex items-center justify-center rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed active:bg-gray-200 dark:active:bg-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Export/Import -->
    @admin
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-3 md:p-4">
        <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
            <a href="{{ route('people.export') }}" class="inline-flex items-center justify-center px-4 py-2.5 sm:py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors text-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Експорт Excel
            </a>
            <a href="{{ route('migration.planning-center') }}" class="inline-flex items-center justify-center px-4 py-2.5 sm:py-2 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-lg font-medium hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors text-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Імпорт CSV
            </a>
        </div>
    </div>
    @endadmin
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/uk.js"></script>

<style>
/* Flatpickr base styles (inlined to avoid CSP issues) */
.flatpickr-calendar {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    border: 1px solid #e5e7eb;
    display: none;
    font-family: inherit;
    font-size: 14px;
    line-height: 24px;
    opacity: 0;
    padding: 0;
    position: absolute;
    text-align: center;
    touch-action: manipulation;
    visibility: hidden;
    width: 307.875px;
    z-index: 9999;
}
.flatpickr-calendar.open, .flatpickr-calendar.inline {
    opacity: 1;
    visibility: visible;
    display: block;
}
.flatpickr-calendar.animate.open {
    animation: fpFadeInDown 200ms ease-out;
}
@keyframes fpFadeInDown {
    from { opacity: 0; transform: translate3d(0, -10px, 0); }
    to { opacity: 1; transform: translate3d(0, 0, 0); }
}

.flatpickr-months {
    display: flex;
    align-items: center;
    padding: 8px 4px;
}
.flatpickr-months .flatpickr-month {
    flex: 1;
    height: 34px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.flatpickr-months .flatpickr-prev-month,
.flatpickr-months .flatpickr-next-month {
    cursor: pointer;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    transition: background 0.15s;
}
.flatpickr-months .flatpickr-prev-month:hover,
.flatpickr-months .flatpickr-next-month:hover {
    background: #f3f4f6;
}
.flatpickr-months .flatpickr-prev-month svg,
.flatpickr-months .flatpickr-next-month svg {
    width: 14px;
    height: 14px;
    fill: #6b7280;
}
.flatpickr-months .flatpickr-prev-month:hover svg,
.flatpickr-months .flatpickr-next-month:hover svg {
    fill: #111827;
}

.flatpickr-current-month {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
}
.flatpickr-current-month .flatpickr-monthDropdown-months {
    appearance: none;
    background: transparent;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    padding: 4px 8px;
    color: #111827;
}
.flatpickr-current-month .flatpickr-monthDropdown-months:hover {
    background: #f3f4f6;
}
.flatpickr-current-month .numInputWrapper {
    display: inline-flex;
    align-items: center;
}
.flatpickr-current-month input.cur-year {
    appearance: none;
    background: transparent;
    border: none;
    border-radius: 6px;
    cursor: text;
    font-size: 14px;
    font-weight: 600;
    padding: 4px 8px;
    width: 60px;
    color: #111827;
}
.flatpickr-current-month .numInputWrapper span {
    display: none;
}

.flatpickr-weekdays {
    display: flex;
    padding: 0 12px;
    height: 28px;
    align-items: center;
}
.flatpickr-weekdaycontainer {
    display: flex;
    flex: 1;
}
.flatpickr-weekday {
    flex: 1;
    font-size: 11px;
    font-weight: 600;
    color: #9ca3af;
    text-transform: uppercase;
}

.flatpickr-days {
    padding: 4px 12px 12px;
}
.dayContainer {
    display: flex;
    flex-wrap: wrap;
    width: 100%;
    gap: 2px;
}
.flatpickr-day {
    width: calc(100% / 7 - 2px);
    max-width: 39px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 500;
    color: #374151;
    border: 1px solid transparent;
    transition: all 0.15s;
}
.flatpickr-day:hover {
    background: #f3f4f6;
}
.flatpickr-day.today {
    border-color: #4f46e5;
}
.flatpickr-day.selected,
.flatpickr-day.startRange,
.flatpickr-day.endRange {
    background: #4f46e5;
    color: #fff;
    border-color: #4f46e5;
}
.flatpickr-day.inRange {
    background: #eef2ff;
    border-color: #eef2ff;
}
.flatpickr-day.prevMonthDay,
.flatpickr-day.nextMonthDay {
    color: #d1d5db;
}
.flatpickr-day.disabled {
    color: #d1d5db;
    cursor: not-allowed;
}

/* Dark mode */
.dark .flatpickr-calendar {
    background: #1f2937;
    border-color: #374151;
}
.dark .flatpickr-months .flatpickr-prev-month:hover,
.dark .flatpickr-months .flatpickr-next-month:hover {
    background: #374151;
}
.dark .flatpickr-months .flatpickr-prev-month svg,
.dark .flatpickr-months .flatpickr-next-month svg {
    fill: #9ca3af;
}
.dark .flatpickr-months .flatpickr-prev-month:hover svg,
.dark .flatpickr-months .flatpickr-next-month:hover svg {
    fill: #fff;
}
.dark .flatpickr-current-month .flatpickr-monthDropdown-months,
.dark .flatpickr-current-month input.cur-year {
    color: #fff;
}
.dark .flatpickr-current-month .flatpickr-monthDropdown-months:hover {
    background: #374151;
}
.dark .flatpickr-weekday {
    color: #6b7280;
}
.dark .flatpickr-day {
    color: #e5e7eb;
}
.dark .flatpickr-day:hover {
    background: #374151;
}
.dark .flatpickr-day.inRange {
    background: #312e81;
    border-color: #312e81;
}
.dark .flatpickr-day.prevMonthDay,
.dark .flatpickr-day.nextMonthDay {
    color: #6b7280;
}
</style>

<script>
function peopleTable() {
    return {
        filters: {
            search: '',
            name: '',
            phone: '',
            email: '',
            birth_from: '',
            birth_to: '',
            dateRangeDisplay: '',
            ministry: '',
            role: '',
            shepherd: ''
        },
        flatpickrInstance: null,
        filteredCount: {{ $people->count() }},
        perPage: 25,
        currentPage: 1,
        allPeople: @js($people->map(fn($p, $i) => ['index' => $i, 'name' => $p->full_name, 'phone' => $p->phone ?? '', 'email' => $p->email ?? '', 'birth_date' => $p->birth_date?->format('Y-m-d') ?? '', 'ministry' => $p->ministries->pluck('name')->join(', '), 'role' => $p->churchRoleRelation?->name ?? '', 'shepherd' => $p->shepherd?->full_name ?? ''])->values()),
        filteredIndices: [],

        init() {
            this.$nextTick(() => {
                this.initDatePicker();
                this.updateFilteredIndices();
            });

            this.$watch('filters', () => {
                this.updateFilteredIndices();
                this.currentPage = 1;
            }, { deep: true });
        },

        initDatePicker() {
            this.flatpickrInstance = flatpickr(this.$refs.dateRange, {
                mode: 'range',
                dateFormat: 'd.m.Y',
                locale: 'uk',
                allowInput: false,
                clickOpens: true,
                disableMobile: true,
                appendTo: document.body,
                onChange: (dates, dateStr) => {
                    if (dates.length === 2) {
                        this.filters.birth_from = dates[0].toISOString().split('T')[0];
                        this.filters.birth_to = dates[1].toISOString().split('T')[0];
                        this.filters.dateRangeDisplay = dateStr;
                    } else if (dates.length === 1) {
                        this.filters.birth_from = dates[0].toISOString().split('T')[0];
                        this.filters.birth_to = '';
                        this.filters.dateRangeDisplay = dateStr;
                    }
                }
            });
        },

        updateFilteredIndices() {
            this.filteredIndices = this.allPeople
                .filter(p => this.matchesFilters(p))
                .map(p => p.index);
            this.filteredCount = this.filteredIndices.length;
        },

        get totalPages() {
            if (this.perPage === 0) return 1;
            return Math.max(1, Math.ceil(this.filteredCount / this.perPage));
        },

        shouldShowRow(index, person) {
            if (!this.matchesFilters(person)) return false;

            if (this.perPage === 0) return true;

            const positionInFiltered = this.filteredIndices.indexOf(index);
            if (positionInFiltered === -1) return false;

            const start = (this.currentPage - 1) * this.perPage;
            const end = start + this.perPage;

            return positionInFiltered >= start && positionInFiltered < end;
        },

        clearDateFilter() {
            this.filters.birth_from = '';
            this.filters.birth_to = '';
            this.filters.dateRangeDisplay = '';
            if (this.flatpickrInstance) {
                this.flatpickrInstance.clear();
            }
        },

        get hasFilters() {
            return this.filters.search || this.filters.name || this.filters.phone || this.filters.email ||
                   this.filters.birth_from || this.filters.birth_to ||
                   this.filters.ministry || this.filters.role || this.filters.shepherd;
        },

        matchesFilters(person) {
            if (this.filters.search) {
                const searchLower = this.filters.search.toLowerCase();
                const allText = [person.name, person.phone, person.email, person.ministry, person.role, person.shepherd].join(' ').toLowerCase();
                if (!allText.includes(searchLower)) return false;
            }

            // Special handling for shepherd filter
            if (this.filters.shepherd) {
                if (this.filters.shepherd === 'none') {
                    if (person.shepherd) return false;
                } else {
                    if (!person.shepherd || !person.shepherd.toLowerCase().includes(this.filters.shepherd.toLowerCase())) return false;
                }
            }

            return (
                this.matchText(person.name, this.filters.name) &&
                this.matchText(person.phone, this.filters.phone) &&
                this.matchText(person.email, this.filters.email) &&
                this.matchDateRange(person.birth_date, this.filters.birth_from, this.filters.birth_to) &&
                this.matchText(person.ministry, this.filters.ministry) &&
                this.matchText(person.role, this.filters.role)
            );
        },

        matchText(value, filter) {
            if (!filter) return true;
            return value.toLowerCase().includes(filter.toLowerCase());
        },

        matchDateRange(dateStr, from, to) {
            if (!from && !to) return true;
            if (!dateStr) return false;

            const date = new Date(dateStr);
            if (from && date < new Date(from)) return false;
            if (to && date > new Date(to)) return false;
            return true;
        },

        clearFilters() {
            this.filters = {
                search: '',
                name: '',
                phone: '',
                email: '',
                birth_from: '',
                birth_to: '',
                dateRangeDisplay: '',
                ministry: '',
                role: '',
                shepherd: ''
            };
            this.currentPage = 1;
            if (this.flatpickrInstance) {
                this.flatpickrInstance.clear();
            }
        }
    };
}
</script>
@endsection
