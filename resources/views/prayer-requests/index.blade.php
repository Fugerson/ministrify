@extends('layouts.app')

@section('title', 'Молитовні прохання')

@section('actions')
<div class="flex items-center space-x-2">
    <a href="{{ route('prayer-requests.wall') }}"
       class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
        </svg>
        Молитовна стіна
    </a>
    <a href="{{ route('prayer-requests.create') }}"
       class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Нове прохання
    </a>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Stats -->
    <div class="grid grid-cols-3 gap-2 md:gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-3 md:p-6">
            <div class="flex flex-col sm:flex-row items-center sm:items-start">
                <div class="w-10 h-10 md:w-12 md:h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 md:w-6 md:h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                    </svg>
                </div>
                <div class="sm:ml-3 md:ml-4 mt-2 sm:mt-0 text-center sm:text-left">
                    <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400">Активні</p>
                    <p class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['active'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-3 md:p-6">
            <div class="flex flex-col sm:flex-row items-center sm:items-start">
                <div class="w-10 h-10 md:w-12 md:h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 md:w-6 md:h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="sm:ml-3 md:ml-4 mt-2 sm:mt-0 text-center sm:text-left">
                    <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400">Відповіді</p>
                    <p class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['answered'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-3 md:p-6">
            <div class="flex flex-col sm:flex-row items-center sm:items-start">
                <div class="w-10 h-10 md:w-12 md:h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                    <span class="text-lg md:text-2xl">🙏</span>
                </div>
                <div class="sm:ml-3 md:ml-4 mt-2 sm:mt-0 text-center sm:text-left">
                    <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400">Молитов</p>
                    <p class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total_prayers'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-3 md:p-4 overflow-x-auto">
        <div class="flex gap-1 sm:gap-2 min-w-max">
            <a href="{{ route('prayer-requests.index') }}"
               class="px-3 md:px-4 py-2 rounded-lg text-xs sm:text-sm font-medium transition-colors whitespace-nowrap {{ !request('status') ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                Активні
            </a>
            <a href="{{ route('prayer-requests.index', ['status' => 'answered']) }}"
               class="px-3 md:px-4 py-2 rounded-lg text-xs sm:text-sm font-medium transition-colors whitespace-nowrap {{ request('status') === 'answered' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                Відповіді
            </a>
            <a href="{{ route('prayer-requests.index', ['status' => 'closed']) }}"
               class="px-3 md:px-4 py-2 rounded-lg text-xs sm:text-sm font-medium transition-colors whitespace-nowrap {{ request('status') === 'closed' ? 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                Закриті
            </a>
        </div>
    </div>

    <!-- Prayer Requests List -->
    <div class="space-y-4">
        @forelse($prayerRequests as $request)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 md:p-6 hover:shadow-md transition-shadow">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-1.5 md:gap-2 mb-2">
                            @if($request->is_urgent)
                                <span class="px-2 py-1 bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 text-xs font-medium rounded-full">
                                    🔥 Терміново
                                </span>
                            @endif
                            @if(!$request->is_public)
                                <span class="px-2 py-1 bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 text-xs font-medium rounded-full">
                                    🔒 Приватне
                                </span>
                            @endif
                            <span class="px-2 py-1 bg-{{ $request->status_color }}-100 text-{{ $request->status_color }}-700 dark:bg-{{ $request->status_color }}-900/30 dark:text-{{ $request->status_color }}-400 text-xs font-medium rounded-full">
                                {{ $request->status_label }}
                            </span>
                        </div>

                        <a href="{{ route('prayer-requests.show', $request) }}" class="block group">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400">
                                {{ $request->title }}
                            </h3>
                        </a>

                        <p class="mt-2 text-gray-600 dark:text-gray-400 line-clamp-2">
                            {{ Str::limit($request->description, 200) }}
                        </p>

                        <div class="mt-4 flex items-center text-sm text-gray-500 dark:text-gray-400">
                            <span>{{ $request->author_name }}</span>
                            <span class="mx-2">•</span>
                            <span>{{ $request->created_at->diffForHumans() }}</span>
                            <span class="mx-2">•</span>
                            <span class="flex items-center">
                                🙏 {{ $request->prayer_count }}
                            </span>
                        </div>
                    </div>

                    @if($request->status === 'active')
                        <form action="{{ route('prayer-requests.pray', $request) }}" method="POST" class="sm:ml-4 w-full sm:w-auto">
                            @csrf
                            <button type="submit"
                                    class="w-full sm:w-auto px-4 py-2 bg-primary-50 hover:bg-primary-100 dark:bg-primary-900/20 dark:hover:bg-primary-900/40 text-primary-600 dark:text-primary-400 rounded-lg font-medium text-sm transition-colors {{ $request->hasPrayed(auth()->user()) ? 'opacity-50' : '' }}"
                                    {{ $request->hasPrayed(auth()->user()) ? 'disabled' : '' }}>
                                🙏 {{ $request->hasPrayed(auth()->user()) ? 'Помолився' : 'Молюсь' }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-12 text-center">
                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="text-3xl">🙏</span>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Немає прохань</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-4">Поділіться своїм молитовним проханням з церквою</p>
                <a href="{{ route('prayer-requests.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                    Додати прохання
                </a>
            </div>
        @endforelse
    </div>

    {{ $prayerRequests->links() }}
</div>
@endsection
