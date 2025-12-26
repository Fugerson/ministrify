@extends('layouts.app')

@section('title', 'Молитовна стіна')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">🙏 Молитовна стіна</h1>
        <p class="text-gray-600 dark:text-gray-400">Підтримайте братів і сестер у молитві</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Active Requests -->
        <div class="lg:col-span-2 space-y-4">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                <span class="w-3 h-3 bg-blue-500 rounded-full mr-2 animate-pulse"></span>
                Активні прохання
            </h2>

            @forelse($requests as $request)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 hover:shadow-md transition-shadow {{ $request->is_urgent ? 'border-l-4 border-red-500' : '' }}">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            @if($request->is_urgent)
                                <span class="inline-block px-2 py-1 bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 text-xs font-medium rounded-full mb-2">
                                    🔥 Терміново
                                </span>
                            @endif
                            <h3 class="font-semibold text-gray-900 dark:text-white">{{ $request->title }}</h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 line-clamp-3">{{ $request->description }}</p>
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-500">
                                {{ $request->author_name }} • {{ $request->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <form action="{{ route('prayer-requests.pray', $request) }}" method="POST" class="ml-4">
                            @csrf
                            <button type="submit"
                                    class="flex flex-col items-center px-3 py-2 bg-primary-50 hover:bg-primary-100 dark:bg-primary-900/20 dark:hover:bg-primary-900/40 text-primary-600 dark:text-primary-400 rounded-lg transition-colors">
                                <span class="text-2xl">🙏</span>
                                <span class="text-xs font-medium">{{ $request->prayer_count }}</span>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-8 text-center">
                    <p class="text-gray-500 dark:text-gray-400">Немає активних прохань</p>
                </div>
            @endforelse

            <div class="text-center">
                <a href="{{ route('prayer-requests.create') }}"
                   class="inline-flex items-center px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Додати прохання
                </a>
            </div>
        </div>

        <!-- Answered Prayers (Testimonies) -->
        <div class="space-y-4">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Свідчення
            </h2>

            @forelse($answered as $testimony)
                <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl p-4 border border-green-200 dark:border-green-800">
                    <h4 class="font-medium text-green-800 dark:text-green-300">{{ $testimony->title }}</h4>
                    <p class="mt-2 text-sm text-green-700 dark:text-green-400 line-clamp-3">{{ $testimony->answer_testimony }}</p>
                    <p class="mt-2 text-xs text-green-600 dark:text-green-500">
                        {{ $testimony->author_name }} • {{ $testimony->answered_at->diffForHumans() }}
                    </p>
                </div>
            @empty
                <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4 text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Поки немає свідчень</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
