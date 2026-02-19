@extends('layouts.app')

@section('title', 'Запити на молитву')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Запити на молитву</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Запити від відвідувачів публічного сайту</p>
        </div>
        <a href="{{ route('website-builder.index') }}" class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Назад до конструктора
        </a>
    </div>

    <!-- Prayer Requests List -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        @if($prayerRequests->isEmpty())
            <div class="p-12 text-center">
                <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Немає запитів</h3>
                <p class="text-gray-500 dark:text-gray-400">Запити на молитву з публічного сайту з'являться тут</p>
            </div>
        @else
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($prayerRequests as $request)
                    <div class="p-6 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-2">
                                    @if($request->is_urgent)
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                            Терміново
                                        </span>
                                    @endif
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $request->status_color }}-100 text-{{ $request->status_color }}-800 dark:bg-{{ $request->status_color }}-900/30 dark:text-{{ $request->status_color }}-400">
                                        {{ $request->status_label }}
                                    </span>
                                </div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">{{ $request->title }}</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 line-clamp-2">{{ $request->description }}</p>
                                <div class="flex items-center gap-4 mt-3 text-sm text-gray-500 dark:text-gray-400">
                                    <span>{{ $request->is_anonymous ? 'Анонімно' : ($request->submitter_name ?? 'Невідомо') }}</span>
                                    <span>{{ $request->created_at->diffForHumans() }}</span>
                                    @if($request->submitter_email)
                                        <span>{{ $request->submitter_email }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <form action="{{ route('website-builder.prayer-inbox.status', $request) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PUT')
                                    <select name="status" onchange="this.form.submit()" class="text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg focus:ring-primary-500 focus:border-primary-500">
                                        <option value="active" {{ $request->status === 'active' ? 'selected' : '' }}>Активне</option>
                                        <option value="answered" {{ $request->status === 'answered' ? 'selected' : '' }}>Відповідь отримано</option>
                                        <option value="closed" {{ $request->status === 'closed' ? 'selected' : '' }}>Закрито</option>
                                    </select>
                                </form>
                                <form action="{{ route('website-builder.prayer-inbox.destroy', $request) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('messages.confirm_delete_prayer_request') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($prayerRequests->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $prayerRequests->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
