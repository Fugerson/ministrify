@extends('layouts.app')

@section('title', 'Люди')

@section('actions')
<a href="{{ route('people.create') }}" class="hidden lg:inline-flex items-center px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-xl hover:bg-primary-700 transition-colors">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
    </svg>
    Додати
</a>
@endsection

@section('content')
<x-page-help page="people" />

<div class="space-y-4 lg:space-y-6">
    <!-- Mobile Header -->
    <div class="lg:hidden flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Люди</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $people->total() }} {{ trans_choice('людина|людини|людей', $people->total()) }}</p>
        </div>
        <a href="{{ route('people.create') }}" class="w-10 h-10 bg-primary-600 text-white rounded-xl flex items-center justify-center shadow-lg shadow-primary-500/30">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
        </a>
    </div>

    <!-- Statistics -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 lg:p-6">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6">
            <!-- Age Categories -->
            <div>
                <h4 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">За віком</h4>
                <div class="space-y-1.5">
                    @foreach($stats['age'] as $key => $age)
                    @if($age['count'] > 0)
                    <a href="{{ route('people.index', array_merge(request()->except('page'), ['age' => $key])) }}"
                       class="flex items-center justify-between text-sm hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg px-2 py-1 -mx-2 transition-colors {{ request('age') === $key ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                        <span style="color: {{ $age['color'] }}" class="font-medium">{{ $age['label'] }}</span>
                        <span class="text-gray-500 dark:text-gray-400">{{ $age['count'] }}</span>
                    </a>
                    @endif
                    @endforeach
                </div>
            </div>

            <!-- Church Roles -->
            <div>
                <h4 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">За роллю</h4>
                <div class="space-y-1.5">
                    @foreach($stats['roles'] as $key => $role)
                    @if($role['count'] > 0)
                    <a href="{{ route('people.index', array_merge(request()->except('page'), ['role' => $key])) }}"
                       class="flex items-center justify-between text-sm hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg px-2 py-1 -mx-2 transition-colors {{ request('role') === $key ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                        <span class="text-gray-700 dark:text-gray-300">{{ $role['label'] }}</span>
                        <span class="text-gray-500 dark:text-gray-400">{{ $role['count'] }}</span>
                    </a>
                    @endif
                    @endforeach
                </div>
            </div>

            <!-- Quick Stats -->
            <div>
                <h4 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Статистика</h4>
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between text-sm px-2 py-1 -mx-2">
                        <span class="text-gray-700 dark:text-gray-300">Всього</span>
                        <span class="font-semibold text-gray-900 dark:text-white">{{ $stats['total'] }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm px-2 py-1 -mx-2">
                        <span class="text-gray-700 dark:text-gray-300">Служать</span>
                        <span class="font-semibold text-primary-600 dark:text-primary-400">{{ $stats['serving'] }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm px-2 py-1 -mx-2">
                        <span class="text-gray-700 dark:text-gray-300">Нові (місяць)</span>
                        <span class="font-semibold text-green-600 dark:text-green-400">{{ $stats['new_this_month'] }}</span>
                    </div>
                </div>
            </div>

            <!-- Visual Bar -->
            <div class="hidden lg:block">
                <h4 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Розподіл за віком</h4>
                @if($stats['total'] > 0)
                <div class="flex h-4 rounded-full overflow-hidden bg-gray-100 dark:bg-gray-700">
                    @foreach($stats['age'] as $key => $age)
                    @if($age['count'] > 0 && $key !== 'unknown')
                    <div style="width: {{ ($age['count'] / $stats['total']) * 100 }}%; background-color: {{ $age['color'] }}"
                         title="{{ $age['label'] }}: {{ $age['count'] }}"
                         class="transition-all hover:opacity-80"></div>
                    @endif
                    @endforeach
                </div>
                <div class="flex flex-wrap gap-x-3 gap-y-1 mt-2">
                    @foreach($stats['age'] as $key => $age)
                    @if($age['count'] > 0 && $key !== 'unknown')
                    <div class="flex items-center text-xs">
                        <span class="w-2 h-2 rounded-full mr-1" style="background-color: {{ $age['color'] }}"></span>
                        <span class="text-gray-500 dark:text-gray-400">{{ $age['label'] }}</span>
                    </div>
                    @endif
                    @endforeach
                </div>
                @else
                <p class="text-sm text-gray-400">Немає даних</p>
                @endif
            </div>
        </div>

        @if(request()->hasAny(['age', 'role']))
        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
            <a href="{{ route('people.index', request()->except(['age', 'role', 'page'])) }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">
                Скинути фільтри статистики
            </a>
        </div>
        @endif
    </div>

    <!-- Search and Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
        <form method="GET" class="space-y-3 lg:space-y-0 lg:flex lg:items-center lg:gap-3">
            <!-- Search -->
            <div class="flex-1 relative">
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Пошук..."
                    class="w-full pl-11 pr-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:bg-white dark:focus:bg-gray-600 focus:ring-2 focus:ring-primary-500/20 transition-all">
            </div>

            <!-- Filters -->
            <div class="flex gap-2 overflow-x-auto no-scrollbar -mx-4 px-4 lg:mx-0 lg:px-0">
                <select name="tag" class="flex-shrink-0 px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500/20 text-gray-700 dark:text-gray-300">
                    <option value="">Теги</option>
                    @foreach($tags as $tag)
                        <option value="{{ $tag->id }}" {{ request('tag') == $tag->id ? 'selected' : '' }}>{{ $tag->name }}</option>
                    @endforeach
                </select>

                <select name="ministry" class="flex-shrink-0 px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500/20 text-gray-700 dark:text-gray-300">
                    <option value="">Служіння</option>
                    @foreach($ministries as $ministry)
                        <option value="{{ $ministry->id }}" {{ request('ministry') == $ministry->id ? 'selected' : '' }}>{{ $ministry->name }}</option>
                    @endforeach
                </select>

                <button type="submit" class="flex-shrink-0 px-5 py-3 bg-gray-900 dark:bg-gray-600 text-white rounded-xl font-medium hover:bg-gray-800 dark:hover:bg-gray-500 transition-colors">
                    Знайти
                </button>

                @if(request()->hasAny(['search', 'tag', 'ministry']))
                <a href="{{ route('people.index') }}" class="flex-shrink-0 px-4 py-3 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                    Скинути
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- People Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 lg:gap-4">
        @forelse($people as $person)
        <a href="{{ route('people.show', $person) }}"
           class="group bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 hover:shadow-lg hover:border-primary-100 dark:hover:border-primary-900 hover:-translate-y-0.5 transition-all duration-200">
            <div class="flex items-start gap-3">
                <!-- Avatar -->
                @if($person->photo)
                <img src="{{ Storage::url($person->photo) }}" alt="{{ $person->full_name }}"
                     class="w-12 h-12 rounded-xl object-cover flex-shrink-0">
                @else
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center flex-shrink-0">
                    <span class="text-base font-semibold text-white">{{ mb_substr($person->first_name, 0, 1) }}{{ mb_substr($person->last_name, 0, 1) }}</span>
                </div>
                @endif

                <!-- Info -->
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors truncate">
                        {{ $person->full_name }}
                    </h3>

                    @if($person->phone)
                    <p class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ $person->phone }}</p>
                    @elseif($person->telegram_username)
                    <p class="text-sm text-primary-600 dark:text-primary-400 truncate">{{ $person->telegram_username }}</p>
                    @endif

                    <!-- Ministries -->
                    @if($person->ministries->isNotEmpty())
                    <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 mt-1.5">
                        @foreach($person->ministries->take(2) as $ministry)
                        <span class="text-xs font-medium" style="color: {{ $ministry->color ?? '#3b82f6' }}">{{ $ministry->name }}</span>
                        @endforeach
                        @if($person->ministries->count() > 2)
                        <span class="text-xs text-gray-400 dark:text-gray-500">+{{ $person->ministries->count() - 2 }}</span>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            <!-- Tags -->
            @if($person->tags->isNotEmpty())
            <div class="flex flex-wrap gap-1.5 mt-3 pt-3 border-t border-gray-50 dark:border-gray-700">
                @foreach($person->tags->take(2) as $tag)
                <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-md"
                      style="background-color: {{ $tag->color ?? '#3b82f6' }}15; color: {{ $tag->color ?? '#3b82f6' }}">
                    {{ $tag->name }}
                </span>
                @endforeach
                @if($person->tags->count() > 2)
                <span class="text-xs text-gray-400 dark:text-gray-500">+{{ $person->tags->count() - 2 }}</span>
                @endif
            </div>
            @endif
        </a>
        @empty
        <div class="col-span-full">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-12 text-center">
                <div class="w-20 h-20 mx-auto mb-4 bg-gradient-to-br from-gray-100 to-gray-50 dark:from-gray-700 dark:to-gray-800 rounded-2xl flex items-center justify-center">
                    <svg class="w-10 h-10 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                    @if(request()->hasAny(['search', 'tag', 'ministry']))
                        Нікого не знайдено
                    @else
                        Поки що нікого немає
                    @endif
                </h3>
                <p class="text-gray-500 dark:text-gray-400 mb-6">
                    @if(request()->hasAny(['search', 'tag', 'ministry']))
                        Спробуйте змінити параметри пошуку
                    @else
                        Додайте першу людину до бази
                    @endif
                </p>
                @if(!request()->hasAny(['search', 'tag', 'ministry']))
                <a href="{{ route('people.create') }}" class="inline-flex items-center px-5 py-2.5 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700 shadow-lg shadow-primary-500/30 transition-all">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Додати людину
                </a>
                @endif
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($people->hasPages())
    <div class="mt-6 mb-4">
        {{ $people->links() }}
    </div>
    @endif

    <!-- Export/Import -->
    @admin
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Експорт / Імпорт</h3>
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('people.export') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Завантажити Excel
            </a>
            <form action="{{ route('people.import') }}" method="POST" enctype="multipart/form-data" class="flex-1 flex flex-col sm:flex-row gap-2">
                @csrf
                <label class="flex-1 flex items-center justify-center px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border-2 border-dashed border-gray-200 dark:border-gray-600 rounded-xl cursor-pointer hover:border-primary-300 dark:hover:border-primary-600 hover:bg-primary-50/50 dark:hover:bg-primary-900/20 transition-colors">
                    <svg class="w-4 h-4 mr-2 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Оберіть файл</span>
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" class="hidden">
                </label>
                <button type="submit" class="px-4 py-2.5 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700 transition-colors">
                    Імпорт
                </button>
            </form>
        </div>
    </div>
    @endadmin
</div>
@endsection
