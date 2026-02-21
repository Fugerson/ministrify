@extends('layouts.app')

@section('title', 'Люди')

@section('actions')
<div class="flex items-center gap-2">
    @if(auth()->user()->isAdmin())
    <div x-data="{ count: null, loading: true }" x-init="
        fetch('{{ route('people.duplicates') }}', { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } })
            .then(r => r.json())
            .then(d => { count = (d.pairs || []).length; loading = false; window.__duplicatesPreloaded = d.pairs || []; })
            .catch(() => { loading = false; })
    " class="relative inline-flex">
        <button @click="document.dispatchEvent(new CustomEvent('open-duplicates'))"
                class="inline-flex items-center px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-xl transition-colors" title="{{ __('app.find_duplicates') }}">
            <svg class="w-4 h-4 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
            </svg>
            <span class="hidden sm:inline">{{ __('app.duplicates') }}</span>
        </button>
        <span x-show="!loading && count > 0" x-cloak
              class="absolute -top-2 -right-2 inline-flex items-center justify-center w-5 h-5 text-[10px] font-bold text-white bg-red-500 rounded-full"
              x-text="count"></span>
    </div>
    @endif
    @if(auth()->user()->canEdit('people'))
    <a href="{{ route('people.quick-edit') }}" class="inline-flex items-center px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium rounded-xl transition-colors" title="{{ __('app.quick_edit') }}">
        <svg class="w-4 h-4 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
        </svg>
        <span class="hidden sm:inline">{{ __('app.quick_edit') }}</span>
    </a>
    @endif
    @if(auth()->user()->canCreate('people'))
    <a href="{{ route('people.create') }}" id="people-add-btn" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-xl hover:bg-primary-700 transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        Додати
    </a>
    @endif
</div>
@endsection

@section('content')
<div x-data="peopleTable()" class="space-y-4">
    <!-- Bulk Actions Toolbar -->
    @if(auth()->user()->canEdit('people'))
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
                    {{ __('app.tag') }}
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
                    {{ __('app.access') }}
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
    @endif
    <!-- Search & Filter Bar -->
    <div id="people-search-bar" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <div class="flex flex-col sm:flex-row gap-4">
            <!-- Search -->
            <div class="flex-1 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" x-model="filters.search" placeholder="{{ __('app.search') }}..."
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
                    <span x-text="filters.gender === 'male' ? '{{ __('app.male') }}' : '{{ __('app.female') }}'"></span>
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
            <template x-if="filters.tag">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-teal-50 dark:bg-teal-900/30 text-teal-700 dark:text-teal-300 rounded-lg text-sm">
                    <span x-text="filters.tag"></span>
                    <button @click="filters.tag = ''" class="hover:text-teal-900 dark:hover:text-teal-100">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </span>
            </template>
            @if($church->shepherds_enabled)
            <template x-if="filters.shepherd">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 rounded-lg text-sm">
                    <span x-text="filters.shepherd === 'none' ? '{{ __('app.no_shepherd') }}' : filters.shepherd"></span>
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
                {{ __('app.clear_all') }}
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
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">{{ __('app.gender') }}</label>
                <div class="flex flex-wrap gap-2">
                    <button @click="filters.gender = filters.gender === 'male' ? '' : 'male'"
                        :class="filters.gender === 'male' ? 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 border-blue-300 dark:border-blue-700' : 'bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500'"
                        class="px-3 py-1.5 text-sm font-medium border rounded-lg transition-colors">
                        {{ __('app.male') }}
                    </button>
                    <button @click="filters.gender = filters.gender === 'female' ? '' : 'female'"
                        :class="filters.gender === 'female' ? 'bg-pink-100 dark:bg-pink-900/40 text-pink-700 dark:text-pink-300 border-pink-300 dark:border-pink-700' : 'bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500'"
                        class="px-3 py-1.5 text-sm font-medium border rounded-lg transition-colors">
                        {{ __('app.female') }}
                    </button>
                </div>
            </div>

            <!-- Marital Status -->
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">{{ __('app.family_status') }}</label>
                <select x-model="filters.marital_status"
                    class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                    <option value="">{{ __('app.all') }}</option>
                    @foreach(\App\Models\Person::getMaritalStatuses() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Ministry -->
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Команда</label>
                <select x-model="filters.ministry"
                    class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                    <option value="">{{ __('app.all') }}</option>
                    @foreach($ministries as $ministry)
                    <option value="{{ $ministry->name }}">{{ $ministry->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Role -->
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">{{ __('app.church_role') }}</label>
                <select x-model="filters.role"
                    class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                    <option value="">{{ __('app.all') }}</option>
                    @foreach($churchRoles as $role)
                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>

            @if($church->shepherds_enabled)
            <!-- Shepherd -->
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">{{ __('app.shepherd') }}</label>
                <select x-model="filters.shepherd"
                    class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                    <option value="">{{ __('app.all') }}</option>
                    @foreach($shepherds as $shepherd)
                    <option value="{{ $shepherd->full_name }}">{{ $shepherd->full_name }}</option>
                    @endforeach
                    <option value="none">{{ __('app.no_shepherd') }}</option>
                </select>
            </div>
            @endif

            <!-- Tag -->
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">{{ __('app.tag') }}</label>
                <select x-model="filters.tag"
                    class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                    <option value="">{{ __('app.all') }}</option>
                    @foreach($tags as $tag)
                    <option value="{{ $tag->name }}">{{ $tag->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Birth Date Range -->
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Дата народження</label>
                <div class="relative">
                    <input type="text" x-ref="dateRange" x-model="filters.dateRangeDisplay" placeholder="{{ __('app.select_range') }}" readonly
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

    @if($peopleLimited)
    <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-3 text-amber-800 dark:text-amber-200 text-sm">
        {{ __('app.limited_notice') }}
    </div>
    @endif

    <!-- Table Container -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        @if(auth()->user()->canEdit('people'))
                        <th class="px-4 py-3 w-10">
                            <input type="checkbox"
                                   @change="toggleSelectAll($event.target.checked)"
                                   :checked="isAllSelected"
                                   :indeterminate.prop="isPartiallySelected"
                                   class="w-4 h-4 text-primary-600 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-primary-500 cursor-pointer">
                        </th>
                        @endif
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Ім'я
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden sm:table-cell">
                            {{ __('app.contacts') }}
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
                            {{ __('app.shepherd') }}
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
                            'tags' => $person->tags->pluck('name')->join(', '),
                            'shepherd' => $person->shepherd?->full_name ?? '',
                        ]))"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        class="hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer group"
                        :class="{ 'bg-primary-50 dark:bg-primary-900/20': selectedIds.includes({{ $person->id }}) }"
                        onclick="window.location='{{ route('people.show', $person) }}'">
                        @if(auth()->user()->canEdit('people'))
                        <!-- Checkbox -->
                        <td class="px-4 py-3" onclick="event.stopPropagation()">
                            <input type="checkbox"
                                   value="{{ $person->id }}"
                                   @change="toggleSelect({{ $person->id }})"
                                   :checked="selectedIds.includes({{ $person->id }})"
                                   class="w-4 h-4 text-primary-600 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-primary-500 cursor-pointer">
                        </td>
                        @endif
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
                                {{ $person->birth_date->age }} {{ trans_choice('рік|роки|років', $person->birth_date->age) }}
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
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ __('app.empty_people_title') }}</h3>
                            <p class="text-gray-500 dark:text-gray-400 mb-4">{{ __('app.empty_people_desc') }}</p>
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
            <p class="text-gray-500 dark:text-gray-400 mb-4">{{ __('app.no_results_desc') }}</p>
            <button @click="clearFilters()" class="text-primary-600 hover:text-primary-700 font-medium">
                {{ __('app.clear_filters') }}
            </button>
        </div>

        <!-- Pagination -->
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                <span class="hidden sm:inline">{{ __('app.show_per_page') }}</span>
                <select x-model.number="perPage" @change="currentPage = 1"
                    class="px-2 py-1.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-primary-500/20">
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="0">{{ __('app.all') }}</option>
                </select>
                <span class="hidden sm:inline">{{ __('app.records') }}</span>
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
    @if(auth()->user()->canEdit('people'))
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('people.export') }}" class="inline-flex items-center justify-center px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors text-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                {{ __('app.export_excel') }}
            </a>
            <a href="{{ route('migration.planning-center') }}" class="inline-flex items-center justify-center px-4 py-2 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-lg font-medium hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors text-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                {{ __('app.import_csv') }}
            </a>
        </div>
    </div>
    @endif

    <!-- Bulk Action Modal -->
    @if(auth()->user()->canEdit('people'))
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
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('app.add_to_ministry') }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                {{ __('app.selected_people') }} <span x-text="selectedIds.length"></span>
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
                                    {{ __('app.please_wait') }}
                                </span>
                            </button>
                        </div>
                    </div>
                </template>

                <!-- Tag Selection -->
                <template x-if="bulkAction === 'tag'">
                    <div>
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('app.add_tag') }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                {{ __('app.selected_people') }} <span x-text="selectedIds.length"></span>
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
                                <span x-show="bulkLoading">{{ __('app.please_wait') }}</span>
                            </button>
                        </div>
                    </div>
                </template>

                <!-- Telegram Message -->
                <template x-if="bulkAction === 'message'">
                    <div>
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('app.send_telegram') }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                {{ __('app.selected_people') }} <span x-text="selectedIds.length"></span>
                            </p>
                        </div>
                        <div class="p-6">
                            <textarea x-model="bulkMessage" rows="4" placeholder="{{ __('app.enter_message') }}"
                                      class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500"></textarea>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                {{ __('app.telegram_notice') }}
                            </p>
                        </div>
                        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 flex justify-end gap-3">
                            <button @click="showBulkModal = false" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-lg transition-colors">
                                Скасувати
                            </button>
                            <button @click="executeBulkAction()" :disabled="!bulkMessage || bulkLoading"
                                    class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors disabled:opacity-50">
                                <span x-show="!bulkLoading">Надіслати</span>
                                <span x-show="bulkLoading">{{ __('app.please_wait') }}</span>
                            </button>
                        </div>
                    </div>
                </template>

                <!-- Grant Access -->
                <template x-if="bulkAction === 'grant_access'">
                    <div>
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('app.grant_access') }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                {{ __('app.selected_people') }} <span x-text="selectedIds.length"></span>
                            </p>
                        </div>
                        <div class="p-6">
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">{{ __('app.select_access_level') }}</p>
                            <div class="space-y-2 max-h-64 overflow-y-auto">
                                @foreach($churchRoles as $role)
                                <label class="flex items-center p-3 border border-gray-200 dark:border-gray-700 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <input type="radio" name="bulk_role" value="{{ $role->id }}" x-model="bulkValue"
                                           class="w-4 h-4 text-primary-600 border-gray-300 dark:border-gray-600 focus:ring-primary-500">
                                    <span class="ml-3 text-gray-900 dark:text-white">{{ $role->name }}</span>
                                </label>
                                @endforeach
                            </div>
                            <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                                {{ __('app.grant_access_notice') }}
                            </p>
                        </div>
                        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 flex justify-end gap-3">
                            <button @click="showBulkModal = false" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-lg transition-colors">
                                {{ __('app.cancel') }}
                            </button>
                            <button @click="executeBulkAction()" :disabled="!bulkValue || bulkLoading"
                                    class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors disabled:opacity-50">
                                <span x-show="!bulkLoading">{{ __('app.grant') }}</span>
                                <span x-show="bulkLoading">{{ __('app.please_wait') }}</span>
                            </button>
                        </div>
                    </div>
                </template>

                <!-- Delete Confirmation -->
                <template x-if="bulkAction === 'delete'">
                    <div>
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-red-600">{{ __('app.delete_people') }}</h3>
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
                                        {{ __('app.delete_can_undo') }}
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
                                <span x-show="bulkLoading">{{ __('app.please_wait') }}</span>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
    @endif

    <!-- Duplicates Modal -->
    @if(auth()->user()->isAdmin())
    <div x-show="showDuplicatesModal" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         @keydown.escape.window="if(showDuplicatesModal && !mergeState) showDuplicatesModal = false; if(mergeState) mergeState = null;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div x-show="showDuplicatesModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80 transition-opacity"
                 @click="if(!mergeState) showDuplicatesModal = false; else mergeState = null;"></div>

            <div x-show="showDuplicatesModal" x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:w-full max-h-[90vh] flex flex-col"
                 :class="mergeState ? 'sm:max-w-6xl' : 'sm:max-w-4xl'">

                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between flex-shrink-0">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white" x-text="mergeState ? '{{ __('app.merge_manager_title') }}' : '{{ __('app.find_duplicates') }}'"></h3>
                        <p x-show="!mergeState && duplicatePairs.length > 0" class="text-sm text-gray-500 dark:text-gray-400 mt-0.5"
                           x-text="'{{ __('app.duplicates_pair') }}'.replace(':current', currentDuplicateIndex + 1).replace(':total', duplicatePairs.length)"></p>
                    </div>
                    <button @click="if(mergeState) mergeState = null; else showDuplicatesModal = false;" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Content -->
                <div class="flex-1 overflow-y-auto p-6">
                    <!-- Loading -->
                    <div x-show="duplicatesLoading" class="flex flex-col items-center justify-center py-12">
                        <svg class="animate-spin w-8 h-8 text-primary-600 mb-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <p class="text-gray-500 dark:text-gray-400">{{ __('app.duplicates_loading') }}</p>
                    </div>

                    <!-- No duplicates -->
                    <div x-show="!duplicatesLoading && duplicatePairs.length === 0 && !mergeState" class="flex flex-col items-center justify-center py-12">
                        <div class="w-16 h-16 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">{{ __('app.duplicates_not_found') }}</h4>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">{{ __('app.duplicates_not_found_desc') }}</p>
                    </div>

                    <!-- ========== 3-COLUMN MERGE MANAGER ========== -->
                    <template x-if="mergeState">
                        <div>
                            <!-- 3-column grid header: Person A | Result | Person B -->
                            <div class="grid grid-cols-[1fr_auto_1fr] gap-0 mb-3">
                                <!-- Person A header -->
                                <div class="flex items-center gap-2 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
                                    <template x-if="mergeState.personA.photo_url">
                                        <img :src="mergeState.personA.photo_url" class="w-10 h-10 rounded-lg object-cover flex-shrink-0">
                                    </template>
                                    <template x-if="!mergeState.personA.photo_url">
                                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center flex-shrink-0">
                                            <span class="text-sm font-semibold text-white" x-text="(mergeState.personA.first_name?.[0] || '') + (mergeState.personA.last_name?.[0] || '')"></span>
                                        </div>
                                    </template>
                                    <div class="min-w-0">
                                        <a :href="mergeState.personA.url" target="_blank" class="text-sm font-semibold text-gray-900 dark:text-white hover:text-blue-600 truncate block" x-text="mergeState.personA.full_name"></a>
                                        <div class="text-xs text-blue-600 dark:text-blue-400 font-medium">A</div>
                                    </div>
                                </div>

                                <!-- Result column header -->
                                <div class="flex items-center justify-center px-4">
                                    <div class="text-center">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('app.merge_result_column') }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ __('app.merge_conflict') }}</div>
                                    </div>
                                </div>

                                <!-- Person B header -->
                                <div class="flex items-center gap-2 p-3 bg-purple-50 dark:bg-purple-900/20 rounded-xl">
                                    <template x-if="mergeState.personB.photo_url">
                                        <img :src="mergeState.personB.photo_url" class="w-10 h-10 rounded-lg object-cover flex-shrink-0">
                                    </template>
                                    <template x-if="!mergeState.personB.photo_url">
                                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center flex-shrink-0">
                                            <span class="text-sm font-semibold text-white" x-text="(mergeState.personB.first_name?.[0] || '') + (mergeState.personB.last_name?.[0] || '')"></span>
                                        </div>
                                    </template>
                                    <div class="min-w-0">
                                        <a :href="mergeState.personB.url" target="_blank" class="text-sm font-semibold text-gray-900 dark:text-white hover:text-purple-600 truncate block" x-text="mergeState.personB.full_name"></a>
                                        <div class="text-xs text-purple-600 dark:text-purple-400 font-medium">B</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Field rows -->
                            <div class="space-y-1">
                                <template x-for="field in mergeState.fields" :key="field.key">
                                    <div class="grid grid-cols-[1fr_auto_1fr] gap-0 items-center text-sm"
                                         :class="{
                                             'bg-gray-50 dark:bg-gray-700/30 rounded-lg': field.type === 'same',
                                         }">
                                        <!-- Person A value -->
                                        <div class="px-3 py-2 min-w-0"
                                             :class="{
                                                 'cursor-pointer hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-l-lg': field.type === 'conflict',
                                                 'opacity-40': field.type === 'auto' && !field.valueA,
                                             }"
                                             @click="if(field.type === 'conflict') toggleField(field.key, 'A')">
                                            <div class="flex items-center gap-2">
                                                <template x-if="field.type === 'conflict'">
                                                    <span class="flex-shrink-0 w-4 h-4 rounded-full border-2 flex items-center justify-center"
                                                          :class="mergeState.selectedValues[field.key] === 'A' ? 'border-blue-500 bg-blue-500' : 'border-gray-300 dark:border-gray-600'">
                                                        <svg x-show="mergeState.selectedValues[field.key] === 'A'" class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                    </span>
                                                </template>
                                                <template x-if="field.key === 'photo_url' && field.valueA">
                                                    <img :src="field.valueA" class="w-8 h-8 rounded-lg object-cover">
                                                </template>
                                                <template x-if="field.key !== 'photo_url'">
                                                    <span class="truncate" :class="field.valueA ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500'" x-text="field.displayA || '—'"></span>
                                                </template>
                                            </div>
                                        </div>

                                        <!-- Result (center column) -->
                                        <div class="px-3 py-2 min-w-[140px] text-center border-x border-gray-200 dark:border-gray-700">
                                            <div class="flex flex-col items-center gap-0.5">
                                                <span class="text-[10px] font-semibold uppercase tracking-wider"
                                                      :class="{
                                                          'text-green-600 dark:text-green-400': field.type === 'auto',
                                                          'text-blue-600 dark:text-blue-400': field.type === 'conflict' && mergeState.selectedValues[field.key] === 'A',
                                                          'text-purple-600 dark:text-purple-400': field.type === 'conflict' && mergeState.selectedValues[field.key] === 'B',
                                                          'text-gray-400 dark:text-gray-500': field.type === 'same',
                                                      }"
                                                      x-text="field.label"></span>
                                                <template x-if="field.key === 'photo_url' && getMergeResultValue(field)">
                                                    <img :src="getMergeResultValue(field)" class="w-8 h-8 rounded-lg object-cover">
                                                </template>
                                                <template x-if="field.key !== 'photo_url'">
                                                    <span class="text-xs font-medium truncate max-w-[130px]"
                                                          :class="{
                                                              'text-green-700 dark:text-green-300': field.type === 'auto',
                                                              'text-blue-700 dark:text-blue-300': field.type === 'conflict' && mergeState.selectedValues[field.key] === 'A',
                                                              'text-purple-700 dark:text-purple-300': field.type === 'conflict' && mergeState.selectedValues[field.key] === 'B',
                                                              'text-gray-500 dark:text-gray-400': field.type === 'same',
                                                          }"
                                                          x-text="getMergeResultDisplay(field) || '—'"></span>
                                                </template>
                                                <span class="text-[9px] px-1.5 py-0.5 rounded-full"
                                                      :class="{
                                                          'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400': field.type === 'auto',
                                                          'bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400': field.type === 'conflict',
                                                          'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400': field.type === 'same',
                                                      }"
                                                      x-text="field.type === 'auto' ? '{{ __('app.merge_auto_selected') }}' : (field.type === 'conflict' ? '{{ __('app.merge_conflict') }}' : '{{ __('app.merge_same_value') }}')"></span>
                                            </div>
                                        </div>

                                        <!-- Person B value -->
                                        <div class="px-3 py-2 min-w-0"
                                             :class="{
                                                 'cursor-pointer hover:bg-purple-50 dark:hover:bg-purple-900/20 rounded-r-lg': field.type === 'conflict',
                                                 'opacity-40': field.type === 'auto' && !field.valueB,
                                             }"
                                             @click="if(field.type === 'conflict') toggleField(field.key, 'B')">
                                            <div class="flex items-center justify-end gap-2">
                                                <template x-if="field.key === 'photo_url' && field.valueB">
                                                    <img :src="field.valueB" class="w-8 h-8 rounded-lg object-cover">
                                                </template>
                                                <template x-if="field.key !== 'photo_url'">
                                                    <span class="truncate text-right" :class="field.valueB ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500'" x-text="field.displayB || '—'"></span>
                                                </template>
                                                <template x-if="field.type === 'conflict'">
                                                    <span class="flex-shrink-0 w-4 h-4 rounded-full border-2 flex items-center justify-center"
                                                          :class="mergeState.selectedValues[field.key] === 'B' ? 'border-purple-500 bg-purple-500' : 'border-gray-300 dark:border-gray-600'">
                                                        <svg x-show="mergeState.selectedValues[field.key] === 'B'" class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                    </span>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- Merged collections: ministries + tags (always union) -->
                            <template x-if="mergeState.mergedMinistries.length > 0 || mergeState.mergedTags.length > 0">
                                <div class="mt-4 pt-3 border-t border-gray-200 dark:border-gray-700">
                                    <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">{{ __('app.merge_union_note') }}</div>
                                    <template x-if="mergeState.mergedMinistries.length > 0">
                                        <div class="mb-2">
                                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ __('app.ministries') }}</div>
                                            <div class="flex flex-wrap gap-1">
                                                <template x-for="m in mergeState.mergedMinistries" :key="m.name">
                                                    <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-md"
                                                          :class="m.from === 'B' ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 ring-1 ring-purple-300 dark:ring-purple-700' : 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300'"
                                                          x-text="m.name"></span>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="mergeState.mergedTags.length > 0">
                                        <div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ __('app.tags') }}</div>
                                            <div class="flex flex-wrap gap-1">
                                                <template x-for="t in mergeState.mergedTags" :key="t.name">
                                                    <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-md"
                                                          :class="t.from === 'B' ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 ring-1 ring-purple-300 dark:ring-purple-700' : 'bg-teal-100 dark:bg-teal-900/30 text-teal-700 dark:text-teal-300'"
                                                          x-text="t.name"></span>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <!-- Account warning -->
                            <template x-if="mergeState.accountNote">
                                <div class="mt-3 p-3 rounded-xl text-sm"
                                     :class="mergeState.bothHaveAccounts ? 'bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 text-amber-700 dark:text-amber-300' : 'bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300'">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        <span x-text="mergeState.accountNote"></span>
                                    </div>
                                </div>
                            </template>

                            <!-- Will be deleted notice -->
                            <div class="mt-3 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl text-sm text-red-700 dark:text-red-300">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    <span>{{ __('app.duplicates_will_delete') }}: <strong x-text="mergeState.personB.full_name"></strong></span>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- ========== PAIR COMPARISON (select base record) ========== -->
                    <template x-if="!duplicatesLoading && duplicatePairs.length > 0 && !mergeState">
                        <div>
                            <!-- Reason badges -->
                            <div class="flex items-center gap-2 mb-4">
                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.duplicate_found') }}:</span>
                                <template x-for="reason in currentPair.reasons" :key="reason">
                                    <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full"
                                          :class="{
                                              'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300': reason === 'phone',
                                              'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300': reason === 'email',
                                              'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300': reason === 'name'
                                          }"
                                          x-text="reason === 'phone' ? '{{ __('app.duplicates_reason_phone') }}' : (reason === 'email' ? '{{ __('app.duplicates_reason_email') }}' : '{{ __('app.duplicates_reason_name') }}')">
                                    </span>
                                </template>
                            </div>

                            <!-- Info -->
                            <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl text-sm text-blue-800 dark:text-blue-200">
                                <div class="flex items-start gap-2">
                                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>{{ __('app.merge_select_base_desc') }}</span>
                                </div>
                            </div>

                            <!-- Side-by-side comparison -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Person A -->
                                <div class="border-2 border-gray-200 dark:border-gray-700 rounded-xl p-4 hover:border-blue-300 dark:hover:border-blue-700 transition-colors">
                                    <div class="flex items-center gap-3 mb-4">
                                        <template x-if="currentPair.personA.photo_url">
                                            <img :src="currentPair.personA.photo_url" class="w-12 h-12 rounded-xl object-cover">
                                        </template>
                                        <template x-if="!currentPair.personA.photo_url">
                                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center">
                                                <span class="text-sm font-semibold text-white" x-text="(currentPair.personA.first_name?.[0] || '') + (currentPair.personA.last_name?.[0] || '')"></span>
                                            </div>
                                        </template>
                                        <div class="min-w-0">
                                            <a :href="currentPair.personA.url" target="_blank" class="font-semibold text-gray-900 dark:text-white hover:text-primary-600 dark:hover:text-primary-400" x-text="currentPair.personA.full_name"></a>
                                            <div x-show="currentPair.personA.has_user" class="text-xs text-green-600 dark:text-green-400 font-medium">{{ __('app.duplicates_has_account') }}</div>
                                        </div>
                                    </div>

                                    <dl class="space-y-2 text-sm">
                                        <template x-for="f in mergeFields" :key="f.key">
                                            <div class="flex justify-between gap-2" x-show="f.key !== 'photo_url'">
                                                <dt class="text-gray-500 dark:text-gray-400 flex-shrink-0" x-text="f.label"></dt>
                                                <dd class="text-gray-900 dark:text-white font-medium truncate text-right" x-text="getMergeFieldDisplay(currentPair.personA, f.key) || '—'"></dd>
                                            </div>
                                        </template>
                                        <div x-show="(currentPair.personA.ministries || []).length > 0">
                                            <dt class="text-gray-500 dark:text-gray-400 mb-1">{{ __('app.ministries') }}</dt>
                                            <dd class="flex flex-wrap gap-1">
                                                <template x-for="m in currentPair.personA.ministries || []" :key="m">
                                                    <span class="inline-flex px-2 py-0.5 text-xs font-medium bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded-md" x-text="m"></span>
                                                </template>
                                            </dd>
                                        </div>
                                        <div x-show="(currentPair.personA.tags || []).length > 0">
                                            <dt class="text-gray-500 dark:text-gray-400 mb-1">{{ __('app.tags') }}</dt>
                                            <dd class="flex flex-wrap gap-1">
                                                <template x-for="t in currentPair.personA.tags || []" :key="t">
                                                    <span class="inline-flex px-2 py-0.5 text-xs font-medium bg-teal-100 dark:bg-teal-900/30 text-teal-700 dark:text-teal-300 rounded-md" x-text="t"></span>
                                                </template>
                                            </dd>
                                        </div>
                                    </dl>

                                    <button @click="startMerge(currentPair.personA, currentPair.personB)"
                                            class="mt-4 w-full inline-flex items-center justify-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition-colors">
                                        {{ __('app.duplicates_keep_record') }}
                                    </button>
                                </div>

                                <!-- Person B -->
                                <div class="border-2 border-gray-200 dark:border-gray-700 rounded-xl p-4 hover:border-purple-300 dark:hover:border-purple-700 transition-colors">
                                    <div class="flex items-center gap-3 mb-4">
                                        <template x-if="currentPair.personB.photo_url">
                                            <img :src="currentPair.personB.photo_url" class="w-12 h-12 rounded-xl object-cover">
                                        </template>
                                        <template x-if="!currentPair.personB.photo_url">
                                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center">
                                                <span class="text-sm font-semibold text-white" x-text="(currentPair.personB.first_name?.[0] || '') + (currentPair.personB.last_name?.[0] || '')"></span>
                                            </div>
                                        </template>
                                        <div class="min-w-0">
                                            <a :href="currentPair.personB.url" target="_blank" class="font-semibold text-gray-900 dark:text-white hover:text-primary-600 dark:hover:text-primary-400" x-text="currentPair.personB.full_name"></a>
                                            <div x-show="currentPair.personB.has_user" class="text-xs text-green-600 dark:text-green-400 font-medium">{{ __('app.duplicates_has_account') }}</div>
                                        </div>
                                    </div>

                                    <dl class="space-y-2 text-sm">
                                        <template x-for="f in mergeFields" :key="f.key">
                                            <div class="flex justify-between gap-2" x-show="f.key !== 'photo_url'">
                                                <dt class="text-gray-500 dark:text-gray-400 flex-shrink-0" x-text="f.label"></dt>
                                                <dd class="text-gray-900 dark:text-white font-medium truncate text-right" x-text="getMergeFieldDisplay(currentPair.personB, f.key) || '—'"></dd>
                                            </div>
                                        </template>
                                        <div x-show="(currentPair.personB.ministries || []).length > 0">
                                            <dt class="text-gray-500 dark:text-gray-400 mb-1">{{ __('app.ministries') }}</dt>
                                            <dd class="flex flex-wrap gap-1">
                                                <template x-for="m in currentPair.personB.ministries || []" :key="m">
                                                    <span class="inline-flex px-2 py-0.5 text-xs font-medium bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded-md" x-text="m"></span>
                                                </template>
                                            </dd>
                                        </div>
                                        <div x-show="(currentPair.personB.tags || []).length > 0">
                                            <dt class="text-gray-500 dark:text-gray-400 mb-1">{{ __('app.tags') }}</dt>
                                            <dd class="flex flex-wrap gap-1">
                                                <template x-for="t in currentPair.personB.tags || []" :key="t">
                                                    <span class="inline-flex px-2 py-0.5 text-xs font-medium bg-teal-100 dark:bg-teal-900/30 text-teal-700 dark:text-teal-300 rounded-md" x-text="t"></span>
                                                </template>
                                            </dd>
                                        </div>
                                    </dl>

                                    <button @click="startMerge(currentPair.personB, currentPair.personA)"
                                            class="mt-4 w-full inline-flex items-center justify-center px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-xl transition-colors">
                                        {{ __('app.duplicates_keep_record') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between flex-shrink-0">
                    <!-- Merge manager mode: Back + Confirm -->
                    <template x-if="mergeState">
                        <div class="flex items-center justify-between w-full">
                            <button @click="mergeState = null"
                                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-lg transition-colors">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                                {{ __('app.previous') }}
                            </button>
                            <button @click="confirmMerge()"
                                    :disabled="mergingId !== null"
                                    class="inline-flex items-center px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl transition-colors disabled:opacity-50">
                                <template x-if="mergingId">
                                    <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                </template>
                                {{ __('app.duplicates_confirm_merge') }}
                            </button>
                        </div>
                    </template>
                    <!-- Compare mode: navigation -->
                    <template x-if="!mergeState && !duplicatesLoading && duplicatePairs.length > 1">
                        <div class="flex items-center justify-between w-full">
                            <button @click="currentDuplicateIndex = Math.max(0, currentDuplicateIndex - 1)"
                                    :disabled="currentDuplicateIndex === 0"
                                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-lg transition-colors disabled:opacity-40">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                                {{ __('app.previous') }}
                            </button>
                            <span class="text-sm text-gray-500 dark:text-gray-400" x-text="(currentDuplicateIndex + 1) + ' / ' + duplicatePairs.length"></span>
                            <button @click="currentDuplicateIndex = Math.min(duplicatePairs.length - 1, currentDuplicateIndex + 1)"
                                    :disabled="currentDuplicateIndex >= duplicatePairs.length - 1"
                                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-lg transition-colors disabled:opacity-40">
                                {{ __('app.next') }}
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                        </div>
                    </template>
                    <template x-if="!mergeState && (duplicatesLoading || duplicatePairs.length <= 1)">
                        <div></div>
                    </template>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/uk.js"></script>

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
            tag: '',
            shepherd: ''
        },
        maritalStatusLabels: @js(\App\Models\Person::getMaritalStatuses()),
        flatpickrInstance: null,
        filteredCount: {{ $people->count() }},
        perPage: 25,
        currentPage: 1,
        allPeople: @js($people->map(fn($p, $i) => ['index' => $i, 'id' => $p->id, 'name' => $p->full_name, 'phone' => $p->phone ?? '', 'email' => $p->email ?? '', 'birth_date' => $p->birth_date?->format('Y-m-d') ?? '', 'ministry' => $p->ministries->pluck('name')->join(', '), 'gender' => $p->gender ?? '', 'marital_status' => $p->marital_status ?? '', 'role' => $p->churchRoleRelation?->name ?? '', 'tags' => $p->tags->pluck('name')->join(', '), 'shepherd' => $p->shepherd?->full_name ?? ''])->values()),
        filteredIndices: [],

        // Bulk selection state
        selectedIds: [],
        showBulkModal: false,
        bulkAction: '',
        bulkValue: '',
        bulkMessage: '',
        bulkLoading: false,

        // Duplicates state
        showDuplicatesModal: false,
        duplicatesLoading: false,
        duplicatePairs: [],
        currentDuplicateIndex: 0,
        mergingId: null,
        mergeState: null, // { personA, personB, fields: [...], selectedValues: {key: 'A'|'B'}, mergedMinistries, mergedTags, accountNote, bothHaveAccounts }

        mergeFields: [
            { key: 'photo_url', label: '{{ __("app.photo") }}', type: 'photo' },
            { key: 'phone', label: '{{ __("app.phone") }}' },
            { key: 'email', label: 'Email' },
            { key: 'birth_date', label: '{{ __("app.date_of_birth") }}' },
            { key: 'gender', label: '{{ __("app.gender") }}' },
            { key: 'membership_status', label: '{{ __("app.membership_status") }}' },
            { key: 'telegram_username', label: 'Telegram' },
            { key: 'address', label: '{{ __("app.address") }}' },
            { key: 'notes', label: '{{ __("app.notes") }}' },
        ],

        get currentPair() {
            return this.duplicatePairs[this.currentDuplicateIndex] || { personA: {}, personB: {}, reasons: [] };
        },

        init() {
            document.addEventListener('open-duplicates', () => this.openDuplicates());
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
            if (this.filters.tag) count++;
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
                   this.filters.role || this.filters.tag || this.filters.shepherd;
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
                this.matchText(person.role, this.filters.role) &&
                this.matchText(person.tags, this.filters.tag)
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
                tag: '',
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

        async openDuplicates() {
            this.showDuplicatesModal = true;
            this.currentDuplicateIndex = 0;
            this.mergingId = null;
            this.mergeState = null;

            // Use preloaded data if available (from button badge)
            if (window.__duplicatesPreloaded) {
                this.duplicatePairs = window.__duplicatesPreloaded;
                window.__duplicatesPreloaded = null;
                this.duplicatesLoading = false;
                return;
            }

            this.duplicatesLoading = true;
            this.duplicatePairs = [];

            try {
                const response = await fetch('{{ route("people.duplicates") }}', {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                });
                const data = await response.json();
                this.duplicatePairs = data.pairs || [];
            } catch (error) {
                console.error('Failed to load duplicates:', error);
                this.duplicatePairs = [];
            } finally {
                this.duplicatesLoading = false;
            }
        },

        getMergeFieldDisplay(person, key) {
            if (!person) return '';
            if (key === 'gender') {
                const map = { 'male': '{{ __("app.gender_male") }}', 'female': '{{ __("app.gender_female") }}' };
                return map[person.gender] || person.gender || '';
            }
            if (key === 'membership_status') {
                const map = { 'guest': '{{ __("app.guest") }}', 'newcomer': '{{ __("app.newcomer") }}', 'member': '{{ __("app.member") }}', 'active': '{{ __("app.active") }}' };
                return map[person.membership_status] || person.membership_status || '';
            }
            if (key === 'photo_url') return person.photo_url || '';
            return person[key] || '';
        },

        startMerge(personA, personB) {
            const fields = [];
            const selectedValues = {};

            for (const f of this.mergeFields) {
                const valA = this.getMergeFieldDisplay(personA, f.key);
                const rawA = personA[f.key] || '';
                const valB = this.getMergeFieldDisplay(personB, f.key);
                const rawB = personB[f.key] || '';

                // Skip if both empty
                if (!rawA && !rawB) continue;

                let type;
                if (!rawA && rawB) {
                    type = 'auto';
                    selectedValues[f.key] = 'B';
                } else if (rawA && !rawB) {
                    type = 'auto';
                    selectedValues[f.key] = 'A';
                } else if (rawA === rawB) {
                    type = 'same';
                    selectedValues[f.key] = 'A';
                } else {
                    type = 'conflict';
                    selectedValues[f.key] = 'A'; // default to A
                }

                fields.push({
                    key: f.key,
                    label: f.label,
                    type: type,
                    valueA: f.key === 'photo_url' ? personA.photo_url : rawA,
                    valueB: f.key === 'photo_url' ? personB.photo_url : rawB,
                    displayA: valA || '—',
                    displayB: valB || '—',
                });
            }

            // Merge ministries (union)
            const aMinistries = (personA.ministries || []).map(n => ({ name: n, from: 'A' }));
            const bMinistries = (personB.ministries || []).filter(n => !(personA.ministries || []).includes(n)).map(n => ({ name: n, from: 'B' }));

            // Merge tags (union)
            const aTags = (personA.tags || []).map(n => ({ name: n, from: 'A' }));
            const bTags = (personB.tags || []).filter(n => !(personA.tags || []).includes(n)).map(n => ({ name: n, from: 'B' }));

            // Account info
            let accountNote = null;
            let bothHaveAccounts = false;
            if (personA.has_user && personB.has_user) {
                accountNote = '{{ __("app.merge_both_have_accounts") }}';
                bothHaveAccounts = true;
            } else if (!personA.has_user && personB.has_user) {
                accountNote = '{{ __("app.duplicates_has_account") }} ← ' + personB.full_name;
            } else if (personA.has_user) {
                accountNote = '{{ __("app.duplicates_has_account") }}: ' + personA.full_name;
            }

            this.mergeState = {
                personA: personA,
                personB: personB,
                fields: fields,
                selectedValues: selectedValues,
                mergedMinistries: [...aMinistries, ...bMinistries],
                mergedTags: [...aTags, ...bTags],
                accountNote: accountNote,
                bothHaveAccounts: bothHaveAccounts,
            };
        },

        toggleField(key, side) {
            if (!this.mergeState) return;
            this.mergeState.selectedValues[key] = side;
        },

        getMergeResultValue(field) {
            if (!this.mergeState) return '';
            const sel = this.mergeState.selectedValues[field.key];
            return sel === 'B' ? field.valueB : field.valueA;
        },

        getMergeResultDisplay(field) {
            if (!this.mergeState) return '';
            const sel = this.mergeState.selectedValues[field.key];
            return sel === 'B' ? field.displayB : field.displayA;
        },

        async confirmMerge() {
            if (!this.mergeState) return;

            this.mergingId = this.mergeState.personA.id;
            const secondaryId = this.mergeState.personB.id;

            // Build field_selections from mergeState
            const fieldSelections = {};
            for (const field of this.mergeState.fields) {
                fieldSelections[field.key] = this.mergeState.selectedValues[field.key] || 'A';
            }

            try {
                const response = await fetch('{{ route("people.merge") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        primary_id: this.mergeState.personA.id,
                        secondary_id: secondaryId,
                        field_selections: fieldSelections
                    })
                });

                const data = await response.json();

                if (data.success) {
                    this.mergeState = null;

                    // Remove this pair from the list
                    this.duplicatePairs.splice(this.currentDuplicateIndex, 1);

                    // Also remove any other pairs involving the secondary person
                    this.duplicatePairs = this.duplicatePairs.filter(p =>
                        p.personA.id !== secondaryId && p.personB.id !== secondaryId
                    );

                    // Adjust index if needed
                    if (this.currentDuplicateIndex >= this.duplicatePairs.length) {
                        this.currentDuplicateIndex = Math.max(0, this.duplicatePairs.length - 1);
                    }

                    // If no more pairs, close after a short delay
                    if (this.duplicatePairs.length === 0) {
                        setTimeout(() => {
                            this.showDuplicatesModal = false;
                            window.location.reload();
                        }, 1000);
                    }
                } else {
                    alert(data.message || 'Error');
                }
            } catch (error) {
                console.error('Merge failed:', error);
                alert('Error merging records');
            } finally {
                this.mergingId = null;
            }
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
                        message: this.bulkMessage,
                        church_role_id: this.bulkAction === 'grant_access' ? this.bulkValue : null
                    })
                });

                const data = await response.json().catch(() => ({}));

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
