@extends('layouts.app')

@section('title', 'Люди')

@section('actions')
<div class="flex items-center gap-2">
    @admin
    <a href="{{ route('people.quick-edit') }}" class="inline-flex items-center px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium rounded-xl transition-colors" title="Швидке редагування">
        <svg class="w-4 h-4 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
        </svg>
        <span class="hidden sm:inline">Швидке редагування</span>
    </a>
    @endadmin
    <a href="{{ route('people.create') }}" id="people-add-btn" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-xl hover:bg-primary-700 transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        Додати
    </a>
</div>
@endsection

@section('content')
<div x-data="peopleTable()" class="space-y-4">
    <!-- Bulk Actions Toolbar -->
    @admin
    <div x-show="selectedIds.length > 0" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="sticky top-16 z-40 bg-primary-600 text-white rounded-xl shadow-lg p-4">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <button @click="clearSelection()" class="p-1.5 hover:bg-white/20 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                <span class="font-medium">
                    Вибрано: <span x-text="selectedIds.length"></span>
                </span>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <!-- Add to Ministry -->
                <button @click="bulkAction = 'ministry'; showBulkModal = true"
                        class="inline-flex items-center gap-2 px-3 py-2 bg-white/20 hover:bg-white/30 rounded-lg text-sm font-medium transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Команда
                </button>
                <!-- Add Tag -->
                <button @click="bulkAction = 'tag'; showBulkModal = true"
                        class="inline-flex items-center gap-2 px-3 py-2 bg-white/20 hover:bg-white/30 rounded-lg text-sm font-medium transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    Тег
                </button>
                <!-- Send Message -->
                <button @click="bulkAction = 'message'; showBulkModal = true"
                        class="inline-flex items-center gap-2 px-3 py-2 bg-white/20 hover:bg-white/30 rounded-lg text-sm font-medium transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    Telegram
                </button>
                <!-- Grant Access -->
                <button @click="bulkAction = 'grant_access'; showBulkModal = true"
                        class="inline-flex items-center gap-2 px-3 py-2 bg-white/20 hover:bg-white/30 rounded-lg text-sm font-medium transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                    Доступ
                </button>
                <!-- Export Selected -->
                <button @click="exportSelected()"
                        class="inline-flex items-center gap-2 px-3 py-2 bg-white/20 hover:bg-white/30 rounded-lg text-sm font-medium transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Excel
                </button>
                <!-- Delete -->
                <button @click="bulkAction = 'delete'; showBulkModal = true"
                        class="inline-flex items-center gap-2 px-3 py-2 bg-red-500 hover:bg-red-600 rounded-lg text-sm font-medium transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Видалити
                </button>
            </div>
        </div>
    </div>
    @endadmin
    <!-- Search & Filter Bar -->
    <div id="people-search-bar" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <div class="flex flex-col sm:flex-row gap-4">
            <!-- Search -->
            <div class="flex-1 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" x-model="filters.search" placeholder="Пошук за ім'ям, телефоном, email..."
                    class="w-full pl-10 pr-10 py-2.5 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500/20">
                <button x-show="filters.search" @click="filters.search = ''" x-cloak
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Filter Button -->
            <button id="people-filter-btn" @click="showFilters = !showFilters"
                :class="{'bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 border-primary-200 dark:border-primary-800': hasFilters || showFilters, 'bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-600': !hasFilters && !showFilters}"
                class="inline-flex items-center px-4 py-2.5 border rounded-xl font-medium transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                Фільтри
                <span x-show="activeFilterCount > 0" x-text="activeFilterCount"
                    class="ml-2 px-2 py-0.5 text-xs font-semibold bg-primary-600 text-white rounded-full"></span>
            </button>

            <!-- Stats -->
            <div class="flex flex-wrap items-center gap-4 text-sm">
                <div class="flex items-center gap-2">
                    <span class="font-semibold text-gray-900 dark:text-white" x-text="filteredCount"></span>
                    <span class="text-gray-500 dark:text-gray-400">з {{ $stats['total'] }}</span>
                </div>
            </div>
        </div>

        <!-- Active Filters Chips -->
        <div x-show="hasFilters" x-cloak class="flex flex-wrap gap-2 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
            <template x-if="filters.gender">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-lg text-sm">
                    <span x-text="filters.gender === 'male' ? 'Чоловіки' : 'Жінки'"></span>
                    <button @click="filters.gender = ''" class="hover:text-blue-900 dark:hover:text-blue-100">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </span>
            </template>
            <template x-if="filters.marital_status">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-pink-50 dark:bg-pink-900/30 text-pink-700 dark:text-pink-300 rounded-lg text-sm">
                    <span x-text="maritalStatusLabels[filters.marital_status]"></span>
                    <button @click="filters.marital_status = ''" class="hover:text-pink-900 dark:hover:text-pink-100">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </span>
            </template>
            <template x-if="filters.ministry">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded-lg text-sm">
                    <span x-text="filters.ministry"></span>
                    <button @click="filters.ministry = ''" class="hover:text-green-900 dark:hover:text-green-100">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </span>
            </template>
            <template x-if="filters.role">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 rounded-lg text-sm">
                    <span x-text="filters.role"></span>
                    <button @click="filters.role = ''" class="hover:text-purple-900 dark:hover:text-purple-100">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </span>
            </template>
            @if($church->shepherds_enabled)
            <template x-if="filters.shepherd">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 rounded-lg text-sm">
                    <span x-text="filters.shepherd === 'none' ? 'Без опікуна' : filters.shepherd"></span>
                    <button @click="filters.shepherd = ''" class="hover:text-amber-900 dark:hover:text-amber-100">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </span>
            </template>
            @endif
            <template x-if="filters.birth_from || filters.birth_to">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-orange-50 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 rounded-lg text-sm">
                    <span x-text="filters.dateRangeDisplay || 'Дата народження'"></span>
                    <button @click="clearDateFilter()" class="hover:text-orange-900 dark:hover:text-orange-100">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </span>
            </template>

            <button @click="clearFilters()" class="text-sm text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 font-medium">
                Очистити все
            </button>
        </div>
    </div>

    <!-- Filter Panel -->
    <div x-show="showFilters" x-cloak x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">

            <!-- Gender -->
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Стать</label>
                <div class="flex flex-wrap gap-2">
                    <button @click="filters.gender = filters.gender === 'male' ? '' : 'male'"
                        :class="filters.gender === 'male' ? 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 border-blue-300 dark:border-blue-700' : 'bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500'"
                        class="px-3 py-1.5 text-sm font-medium border rounded-lg transition-colors">
                        Чоловіки
                    </button>
                    <button @click="filters.gender = filters.gender === 'female' ? '' : 'female'"
                        :class="filters.gender === 'female' ? 'bg-pink-100 dark:bg-pink-900/40 text-pink-700 dark:text-pink-300 border-pink-300 dark:border-pink-700' : 'bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500'"
                        class="px-3 py-1.5 text-sm font-medium border rounded-lg transition-colors">
                        Жінки
                    </button>
                </div>
            </div>

            <!-- Marital Status -->
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Сімейний стан</label>
                <select x-model="filters.marital_status"
                    class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                    <option value="">Всі</option>
                    @foreach(\App\Models\Person::MARITAL_STATUSES as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Ministry -->
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Команда</label>
                <select x-model="filters.ministry"
                    class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                    <option value="">Всі</option>
                    @foreach($ministries as $ministry)
                    <option value="{{ $ministry->name }}">{{ $ministry->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Role -->
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Роль в церкві</label>
                <select x-model="filters.role"
                    class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                    <option value="">Всі</option>
                    @foreach($churchRoles as $role)
                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>

            @if($church->shepherds_enabled)
            <!-- Shepherd -->
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Опікун</label>
                <select x-model="filters.shepherd"
                    class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                    <option value="">Всі</option>
                    @foreach($shepherds as $shepherd)
                    <option value="{{ $shepherd->full_name }}">{{ $shepherd->full_name }}</option>
                    @endforeach
                    <option value="none">Без опікуна</option>
                </select>
            </div>
            @endif

            <!-- Birth Date Range -->
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Дата народження</label>
                <div class="relative">
                    <input type="text" x-ref="dateRange" x-model="filters.dateRangeDisplay" placeholder="Виберіть діапазон..." readonly
                        @click="openDatePicker()"
                        class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500/20 cursor-pointer">
                    <button type="button" x-show="filters.birth_from || filters.birth_to" @click="clearDateFilter()"
                        class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Container -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        @admin
                        <th class="px-4 py-3 w-10">
                            <input type="checkbox"
                                   @change="toggleSelectAll($event.target.checked)"
                                   :checked="isAllSelected"
                                   :indeterminate.prop="isPartiallySelected"
                                   class="w-4 h-4 text-primary-600 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-primary-500 cursor-pointer">
                        </th>
                        @endadmin
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Ім'я
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden sm:table-cell">
                            Контакти
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell">
                            Дата народження
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden md:table-cell">
                            Команда
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden xl:table-cell">
                            Роль
                        </th>
                        @if($church->shepherds_enabled)
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden xl:table-cell">
                            Опікун
                        </th>
                        @endif
                        <th class="px-4 py-3 w-10"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($people as $index => $person)
                    <tr x-show="shouldShowRow({{ $index }}, @js([
                            'name' => $person->full_name,
                            'phone' => $person->phone ?? '',
                            'email' => $person->email ?? '',
                            'birth_date' => $person->birth_date?->format('Y-m-d') ?? '',
                            'ministry' => $person->ministries->pluck('name')->join(', '),
                            'gender' => $person->gender ?? '',
                            'marital_status' => $person->marital_status ?? '',
                            'role' => $person->churchRoleRelation?->name ?? '',
                            'shepherd' => $person->shepherd?->full_name ?? '',
                        ]))"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        class="hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer group"
                        :class="{ 'bg-primary-50 dark:bg-primary-900/20': selectedIds.includes({{ $person->id }}) }"
                        onclick="window.location='{{ route('people.show', $person) }}'">
                        @admin
                        <!-- Checkbox -->
                        <td class="px-4 py-3" onclick="event.stopPropagation()">
                            <input type="checkbox"
                                   value="{{ $person->id }}"
                                   @change="toggleSelect({{ $person->id }})"
                                   :checked="selectedIds.includes({{ $person->id }})"
                                   class="w-4 h-4 text-primary-600 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-primary-500 cursor-pointer">
                        </td>
                        @endadmin
                        <!-- Name -->
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if($person->photo)
                                <div class="flex-shrink-0" x-data="{ hover: false, r: {} }" @mouseenter="hover = true; r = $el.getBoundingClientRect()" @mouseleave="hover = false">
                                    <img src="{{ Storage::url($person->photo) }}" alt=""
                                         class="w-10 h-10 rounded-xl object-cover"
                                         loading="lazy">
                                    <div class="fixed z-[100] pointer-events-none" :style="`left:${r.left+r.width/2}px;top:${r.top-8}px;transform:translate(-50%,-100%)`">
                                        <img src="{{ Storage::url($person->photo) }}" :class="hover ? 'opacity-100 scale-100' : 'opacity-0 scale-75'" class="w-32 h-32 rounded-xl object-cover shadow-xl ring-2 ring-white dark:ring-gray-800 transition-all duration-200 ease-out origin-bottom">
                                    </div>
                                </div>
                                @else
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center flex-shrink-0">
                                    <span class="text-sm font-semibold text-white">{{ mb_substr($person->first_name, 0, 1) }}{{ mb_substr($person->last_name, 0, 1) }}</span>
                                </div>
                                @endif
                                <div class="min-w-0">
                                    <div class="font-medium text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                                        {{ $person->full_name ?: '—' }}
                                    </div>
                                    <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                        @if($person->gender)
                                        <span>{{ $person->gender_label }}</span>
                                        @endif
                                        @if($person->gender && $person->marital_status)
                                        <span>•</span>
                                        @endif
                                        @if($person->marital_status)
                                        <span>{{ $person->marital_status_label }}</span>
                                        @endif
                                    </div>
                                    <!-- Mobile: show phone under name -->
                                    <div class="sm:hidden text-xs text-gray-500 dark:text-gray-400 truncate mt-0.5">
                                        {{ $person->phone ?: $person->email ?: '' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <!-- Contacts -->
                        <td class="px-4 py-3 hidden sm:table-cell">
                            <div class="space-y-0.5">
                                @if($person->phone)
                                <div class="text-sm text-gray-900 dark:text-white">{{ $person->phone }}</div>
                                @endif
                                @if($person->email)
                                <div class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-[200px]">{{ $person->email }}</div>
                                @endif
                                @if(!$person->phone && !$person->email)
                                <span class="text-gray-400">—</span>
                                @endif
                            </div>
                        </td>
                        <!-- Birth Date -->
                        <td class="px-4 py-3 hidden lg:table-cell">
                            @if($person->birth_date)
                            <div class="text-sm text-gray-900 dark:text-white">
                                {{ $person->birth_date->format('d.m.Y') }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $person->birth_date->age }} років
                            </div>
                            @else
                            <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <!-- Ministries -->
                        <td class="px-4 py-3 hidden md:table-cell">
                            @if($person->ministries->isNotEmpty())
                            <div class="flex flex-wrap gap-1">
                                @foreach($person->ministries->take(2) as $ministry)
                                <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-md"
                                      style="background-color: {{ $ministry->color ?? '#6366f1' }}30; color: {{ $ministry->color ?? '#6366f1' }}">
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
                                  style="background-color: {{ $person->churchRoleRelation->color }}30; color: {{ $person->churchRoleRelation->color }}">
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
                                <img src="{{ Storage::url($person->shepherd->photo) }}" alt="" class="w-6 h-6 rounded-full object-cover" loading="lazy">
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
                        <td class="px-4 py-3">
                            <a href="{{ route('people.show', $person) }}"
                               class="p-2 text-gray-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-lg transition-colors inline-block"
                               onclick="event.stopPropagation()">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="px-4 py-12 text-center">
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
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row items-center justify-between gap-3">
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
                    class="w-10 h-10 sm:w-9 sm:h-9 flex items-center justify-center rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                    </svg>
                </button>
                <button @click="currentPage--" :disabled="currentPage === 1"
                    class="w-10 h-10 sm:w-9 sm:h-9 flex items-center justify-center rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>

                <span class="px-3 py-1 text-sm text-gray-700 dark:text-gray-300 min-w-[4rem] text-center">
                    <span x-text="currentPage"></span> / <span x-text="totalPages"></span>
                </span>

                <button @click="currentPage++" :disabled="currentPage >= totalPages"
                    class="w-10 h-10 sm:w-9 sm:h-9 flex items-center justify-center rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                <button @click="currentPage = totalPages" :disabled="currentPage >= totalPages"
                    class="w-10 h-10 sm:w-9 sm:h-9 flex items-center justify-center rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Export/Import -->
    @admin
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('people.export') }}" class="inline-flex items-center justify-center px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors text-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Експорт Excel
            </a>
            <a href="{{ route('migration.planning-center') }}" class="inline-flex items-center justify-center px-4 py-2 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-lg font-medium hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors text-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Імпорт CSV
            </a>
        </div>
    </div>
    @endadmin

    <!-- Bulk Action Modal -->
    @admin
    <div x-show="showBulkModal" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         @keydown.escape.window="showBulkModal = false">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div x-show="showBulkModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80 transition-opacity"
                 @click="showBulkModal = false"></div>

            <div x-show="showBulkModal" x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full">

                <!-- Ministry Selection -->
                <template x-if="bulkAction === 'ministry'">
                    <div>
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Додати до команди</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                Вибрано людей: <span x-text="selectedIds.length"></span>
                            </p>
                        </div>
                        <div class="p-6">
                            <div class="space-y-2 max-h-64 overflow-y-auto">
                                @foreach($ministries as $ministry)
                                <label class="flex items-center p-3 border border-gray-200 dark:border-gray-700 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <input type="radio" name="bulk_ministry" value="{{ $ministry->id }}" x-model="bulkValue"
                                           class="w-4 h-4 text-primary-600 border-gray-300 dark:border-gray-600 focus:ring-primary-500">
                                    <span class="ml-3 flex items-center gap-2">
                                        @if($ministry->color)
                                        <span class="w-3 h-3 rounded-full" style="background-color: {{ $ministry->color }}"></span>
                                        @endif
                                        <span class="text-gray-900 dark:text-white">{{ $ministry->name }}</span>
                                    </span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 flex justify-end gap-3">
                            <button @click="showBulkModal = false" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-lg transition-colors">
                                Скасувати
                            </button>
                            <button @click="executeBulkAction()" :disabled="!bulkValue || bulkLoading"
                                    class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors disabled:opacity-50">
                                <span x-show="!bulkLoading">Додати</span>
                                <span x-show="bulkLoading" class="flex items-center gap-2">
                                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                    Зачекайте...
                                </span>
                            </button>
                        </div>
                    </div>
                </template>

                <!-- Tag Selection -->
                <template x-if="bulkAction === 'tag'">
                    <div>
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Додати тег</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                Вибрано людей: <span x-text="selectedIds.length"></span>
                            </p>
                        </div>
                        <div class="p-6">
                            <div class="space-y-2 max-h-64 overflow-y-auto">
                                @foreach($tags ?? [] as $tag)
                                <label class="flex items-center p-3 border border-gray-200 dark:border-gray-700 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <input type="radio" name="bulk_tag" value="{{ $tag->id }}" x-model="bulkValue"
                                           class="w-4 h-4 text-primary-600 border-gray-300 dark:border-gray-600 focus:ring-primary-500">
                                    <span class="ml-3 text-gray-900 dark:text-white">{{ $tag->name }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 flex justify-end gap-3">
                            <button @click="showBulkModal = false" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-lg transition-colors">
                                Скасувати
                            </button>
                            <button @click="executeBulkAction()" :disabled="!bulkValue || bulkLoading"
                                    class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors disabled:opacity-50">
                                <span x-show="!bulkLoading">Додати</span>
                                <span x-show="bulkLoading">Зачекайте...</span>
                            </button>
                        </div>
                    </div>
                </template>

                <!-- Telegram Message -->
                <template x-if="bulkAction === 'message'">
                    <div>
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Надіслати Telegram</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                Вибрано людей: <span x-text="selectedIds.length"></span>
                            </p>
                        </div>
                        <div class="p-6">
                            <textarea x-model="bulkMessage" rows="4" placeholder="Введіть повідомлення..."
                                      class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500"></textarea>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                Повідомлення отримають тільки ті, хто підключив Telegram
                            </p>
                        </div>
                        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 flex justify-end gap-3">
                            <button @click="showBulkModal = false" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-lg transition-colors">
                                Скасувати
                            </button>
                            <button @click="executeBulkAction()" :disabled="!bulkMessage || bulkLoading"
                                    class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors disabled:opacity-50">
                                <span x-show="!bulkLoading">Надіслати</span>
                                <span x-show="bulkLoading">Зачекайте...</span>
                            </button>
                        </div>
                    </div>
                </template>

                <!-- Delete Confirmation -->
                <template x-if="bulkAction === 'delete'">
                    <div>
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-red-600">Видалити людей</h3>
                        </div>
                        <div class="p-6">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0 w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-gray-900 dark:text-white">
                                        Ви впевнені, що хочете видалити <strong x-text="selectedIds.length"></strong> людей?
                                    </p>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        Цю дію можна буде скасувати через архів.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 flex justify-end gap-3">
                            <button @click="showBulkModal = false" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-lg transition-colors">
                                Скасувати
                            </button>
                            <button @click="executeBulkAction()" :disabled="bulkLoading"
                                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors disabled:opacity-50">
                                <span x-show="!bulkLoading">Видалити</span>
                                <span x-show="bulkLoading">Зачекайте...</span>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
    @endadmin
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/uk.js"></script>

<style>
.flatpickr-calendar {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    border: 1px solid #e5e7eb;
    font-family: inherit;
    z-index: 9999;
}
.flatpickr-calendar.open { opacity: 1; visibility: visible; display: block; }
.flatpickr-calendar.animate.open { animation: fpFadeInDown 200ms ease-out; }
@keyframes fpFadeInDown {
    from { opacity: 0; transform: translate3d(0, -10px, 0); }
    to { opacity: 1; transform: translate3d(0, 0, 0); }
}
.flatpickr-months { display: flex; align-items: center; padding: 8px 4px; }
.flatpickr-months .flatpickr-month { flex: 1; height: 34px; display: flex; align-items: center; justify-content: center; }
.flatpickr-months .flatpickr-prev-month, .flatpickr-months .flatpickr-next-month {
    cursor: pointer; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 8px;
}
.flatpickr-months .flatpickr-prev-month:hover, .flatpickr-months .flatpickr-next-month:hover { background: #f3f4f6; }
.flatpickr-months .flatpickr-prev-month svg, .flatpickr-months .flatpickr-next-month svg { width: 14px; height: 14px; fill: #6b7280; }
.flatpickr-current-month { display: flex; align-items: center; justify-content: center; gap: 4px; }
.flatpickr-current-month .flatpickr-monthDropdown-months {
    appearance: none; background: transparent; border: none; border-radius: 6px; cursor: pointer;
    font-size: 14px; font-weight: 600; padding: 4px 8px; color: #111827;
}
.flatpickr-current-month input.cur-year {
    appearance: none; background: transparent; border: none; border-radius: 6px; cursor: text;
    font-size: 14px; font-weight: 600; padding: 4px 8px; width: 60px; color: #111827;
}
.flatpickr-current-month .numInputWrapper span { display: none; }
.flatpickr-weekdays { display: flex; padding: 0 12px; height: 28px; align-items: center; }
.flatpickr-weekdaycontainer { display: flex; flex: 1; }
.flatpickr-weekday { flex: 1; font-size: 11px; font-weight: 600; color: #9ca3af; text-transform: uppercase; }
.flatpickr-days { padding: 4px 12px 12px; }
.dayContainer { display: flex; flex-wrap: wrap; width: 100%; gap: 2px; }
.flatpickr-day {
    width: calc(100% / 7 - 2px); max-width: 39px; height: 36px; display: flex; align-items: center; justify-content: center;
    border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 500; color: #374151; border: 1px solid transparent;
}
.flatpickr-day:hover { background: #f3f4f6; }
.flatpickr-day.today { border-color: #4f46e5; }
.flatpickr-day.selected, .flatpickr-day.startRange, .flatpickr-day.endRange { background: #4f46e5; color: #fff; border-color: #4f46e5; }
.flatpickr-day.inRange { background: #eef2ff; border-color: #eef2ff; }
.flatpickr-day.prevMonthDay, .flatpickr-day.nextMonthDay { color: #d1d5db; }
.dark .flatpickr-calendar { background: #1f2937; border-color: #374151; }
.dark .flatpickr-months .flatpickr-prev-month:hover, .dark .flatpickr-months .flatpickr-next-month:hover { background: #374151; }
.dark .flatpickr-months .flatpickr-prev-month svg, .dark .flatpickr-months .flatpickr-next-month svg { fill: #9ca3af; }
.dark .flatpickr-current-month .flatpickr-monthDropdown-months, .dark .flatpickr-current-month input.cur-year { color: #fff; }
.dark .flatpickr-weekday { color: #6b7280; }
.dark .flatpickr-day { color: #e5e7eb; }
.dark .flatpickr-day:hover { background: #374151; }
.dark .flatpickr-day.inRange { background: #312e81; border-color: #312e81; }
.dark .flatpickr-day.prevMonthDay, .dark .flatpickr-day.nextMonthDay { color: #6b7280; }
</style>

<script>
function peopleTable() {
    return {
        showFilters: false,
        filters: {
            search: '',
            birth_from: '',
            birth_to: '',
            dateRangeDisplay: '',
            ministry: '',
            gender: '',
            marital_status: '',
            role: '',
            shepherd: ''
        },
        maritalStatusLabels: @js(\App\Models\Person::MARITAL_STATUSES),
        flatpickrInstance: null,
        filteredCount: {{ $people->count() }},
        perPage: 25,
        currentPage: 1,
        allPeople: @js($people->map(fn($p, $i) => ['index' => $i, 'id' => $p->id, 'name' => $p->full_name, 'phone' => $p->phone ?? '', 'email' => $p->email ?? '', 'birth_date' => $p->birth_date?->format('Y-m-d') ?? '', 'ministry' => $p->ministries->pluck('name')->join(', '), 'gender' => $p->gender ?? '', 'marital_status' => $p->marital_status ?? '', 'role' => $p->churchRoleRelation?->name ?? '', 'shepherd' => $p->shepherd?->full_name ?? ''])->values()),
        filteredIndices: [],

        // Bulk selection state
        selectedIds: [],
        showBulkModal: false,
        bulkAction: '',
        bulkValue: '',
        bulkMessage: '',
        bulkLoading: false,

        init() {
            this.$nextTick(() => {
                this.updateFilteredIndices();
            });

            this.$watch('filters', () => {
                this.updateFilteredIndices();
                this.currentPage = 1;
            }, { deep: true });
        },

        openDatePicker() {
            const input = this.$refs.dateRange;
            if (!input) {
                console.error('Date input not found');
                return;
            }

            if (typeof flatpickr === 'undefined') {
                console.error('Flatpickr not loaded');
                return;
            }

            if (!this.flatpickrInstance) {
                const self = this;
                this.flatpickrInstance = flatpickr(input, {
                    mode: 'range',
                    dateFormat: 'd.m.Y',
                    locale: 'uk',
                    allowInput: false,
                    clickOpens: false,
                    disableMobile: true,
                    appendTo: document.body,
                    onReady: function() {
                        this.open();
                    },
                    onChange: function(dates, dateStr) {
                        if (dates.length === 2) {
                            self.filters.birth_from = dates[0].toISOString().split('T')[0];
                            self.filters.birth_to = dates[1].toISOString().split('T')[0];
                            self.filters.dateRangeDisplay = dateStr;
                        } else if (dates.length === 1) {
                            self.filters.birth_from = dates[0].toISOString().split('T')[0];
                            self.filters.birth_to = '';
                            self.filters.dateRangeDisplay = dateStr;
                        }
                    }
                });
            } else {
                this.flatpickrInstance.open();
            }
        },

        updateFilteredIndices() {
            this.filteredIndices = this.allPeople
                .filter(p => this.matchesFilters(p))
                .map(p => p.index);
            this.filteredCount = this.filteredIndices.length;
        },

        get activeFilterCount() {
            let count = 0;
            if (this.filters.gender) count++;
            if (this.filters.marital_status) count++;
            if (this.filters.ministry) count++;
            if (this.filters.role) count++;
            if (this.filters.shepherd) count++;
            if (this.filters.birth_from || this.filters.birth_to) count++;
            return count;
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
            return this.filters.search || this.filters.birth_from || this.filters.birth_to ||
                   this.filters.ministry || this.filters.gender || this.filters.marital_status ||
                   this.filters.role || this.filters.shepherd;
        },

        matchesFilters(person) {
            if (this.filters.search) {
                const searchLower = this.filters.search.toLowerCase();
                const allText = [person.name, person.phone, person.email, person.ministry, person.role, person.shepherd].join(' ').toLowerCase();
                if (!allText.includes(searchLower)) return false;
            }

            if (this.filters.shepherd) {
                if (this.filters.shepherd === 'none') {
                    if (person.shepherd) return false;
                } else {
                    if (!person.shepherd || !person.shepherd.toLowerCase().includes(this.filters.shepherd.toLowerCase())) return false;
                }
            }

            return (
                this.matchDateRange(person.birth_date, this.filters.birth_from, this.filters.birth_to) &&
                this.matchText(person.ministry, this.filters.ministry) &&
                this.matchExact(person.gender, this.filters.gender) &&
                this.matchExact(person.marital_status, this.filters.marital_status) &&
                this.matchText(person.role, this.filters.role)
            );
        },

        matchExact(value, filter) {
            if (!filter) return true;
            return value === filter;
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
                birth_from: '',
                birth_to: '',
                dateRangeDisplay: '',
                ministry: '',
                gender: '',
                marital_status: '',
                role: '',
                shepherd: ''
            };
            this.currentPage = 1;
            if (this.flatpickrInstance) {
                this.flatpickrInstance.clear();
            }
        },

        // Bulk selection methods
        get visibleIds() {
            return this.allPeople
                .filter(p => this.filteredIndices.includes(p.index))
                .map(p => p.id);
        },

        get isAllSelected() {
            return this.visibleIds.length > 0 && this.visibleIds.every(id => this.selectedIds.includes(id));
        },

        get isPartiallySelected() {
            return this.selectedIds.length > 0 && !this.isAllSelected && this.visibleIds.some(id => this.selectedIds.includes(id));
        },

        toggleSelect(id) {
            const index = this.selectedIds.indexOf(id);
            if (index === -1) {
                this.selectedIds.push(id);
            } else {
                this.selectedIds.splice(index, 1);
            }
        },

        toggleSelectAll(checked) {
            if (checked) {
                this.visibleIds.forEach(id => {
                    if (!this.selectedIds.includes(id)) {
                        this.selectedIds.push(id);
                    }
                });
            } else {
                this.selectedIds = this.selectedIds.filter(id => !this.visibleIds.includes(id));
            }
        },

        clearSelection() {
            this.selectedIds = [];
        },

        exportSelected() {
            const ids = this.selectedIds.join(',');
            window.location.href = `{{ route('people.export') }}?ids=${ids}`;
        },

        async executeBulkAction() {
            this.bulkLoading = true;
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            try {
                const response = await fetch('{{ route("people.bulk-action") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        action: this.bulkAction,
                        ids: this.selectedIds,
                        value: this.bulkValue,
                        message: this.bulkMessage
                    })
                });

                const data = await response.json();

                if (data.success) {
                    this.showBulkModal = false;
                    this.clearSelection();
                    this.bulkValue = '';
                    this.bulkMessage = '';

                    // Show success message and optionally reload
                    if (data.reload) {
                        window.location.reload();
                    } else {
                        alert(data.message || 'Операція виконана успішно');
                    }
                } else {
                    alert(data.message || 'Сталася помилка');
                }
            } catch (error) {
                console.error('Bulk action error:', error);
                alert('Сталася помилка при виконанні операції');
            } finally {
                this.bulkLoading = false;
            }
        }
    };
}
</script>
@endsection
