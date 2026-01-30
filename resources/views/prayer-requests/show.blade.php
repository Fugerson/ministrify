@extends('layouts.app')

@section('title', $prayerRequest->title)

@section('content')
<div class="max-w-3xl mx-auto">
    <a href="{{ route('prayer-requests.index') }}" class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-sm mb-6">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Назад до списку
    </a>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-start justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        @if($prayerRequest->is_urgent)
                            <span class="px-2 py-1 bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 text-xs font-medium rounded-full">
                                🔥 Терміново
                            </span>
                        @endif
                        @if(!$prayerRequest->is_public)
                            <span class="px-2 py-1 bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 text-xs font-medium rounded-full">
                                🔒 Приватне
                            </span>
                        @endif
                        <span class="px-2 py-1 bg-{{ $prayerRequest->status_color }}-100 text-{{ $prayerRequest->status_color }}-700 dark:bg-{{ $prayerRequest->status_color }}-900/30 dark:text-{{ $prayerRequest->status_color }}-400 text-xs font-medium rounded-full">
                            {{ $prayerRequest->status_label }}
                        </span>
                    </div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $prayerRequest->title }}</h1>
                </div>

                @if($prayerRequest->user_id === auth()->id() || auth()->user()->hasRole(['admin', 'leader']))
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('prayer-requests.edit', $prayerRequest) }}"
                           class="px-3 py-1 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-sm">
                            Редагувати
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Content -->
        <div class="p-6">
            <div class="prose dark:prose-invert max-w-none">
                <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $prayerRequest->description }}</p>
            </div>

            <div class="mt-6 flex items-center text-sm text-gray-500 dark:text-gray-400">
                <div class="w-8 h-8 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center mr-3">
                    <span>🙏</span>
                </div>
                <div>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $prayerRequest->author_name }}</p>
                    <p>{{ $prayerRequest->created_at->format('d.m.Y H:i') }}</p>
                </div>
            </div>
        </div>

        <!-- Answer Testimony -->
        @if($prayerRequest->status === 'answered' && $prayerRequest->answer_testimony)
            <div class="px-6 py-4 bg-green-50 dark:bg-green-900/20 border-t border-green-200 dark:border-green-800">
                <h3 class="font-semibold text-green-800 dark:text-green-300 mb-2 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Свідчення відповіді
                </h3>
                <p class="text-green-700 dark:text-green-400 whitespace-pre-wrap">{{ $prayerRequest->answer_testimony }}</p>
                <p class="text-sm text-green-600 dark:text-green-500 mt-2">{{ $prayerRequest->answered_at?->format('d.m.Y') }}</p>
            </div>
        @endif

        <!-- Actions -->
        <div class="px-4 sm:px-6 py-3 sm:py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                    <span class="text-xl mr-2">🙏</span>
                    <span>{{ $prayerRequest->prayer_count }} {{ trans_choice('людина молиться|людини моляться|людей моляться', $prayerRequest->prayer_count) }}</span>
                </div>

                <div class="flex items-center space-x-3">
                    @if($prayerRequest->status === 'active')
                        <form action="{{ route('prayer-requests.pray', $prayerRequest) }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-medium text-sm transition-colors {{ $hasPrayed ? 'opacity-50 cursor-not-allowed' : '' }}"
                                    {{ $hasPrayed ? 'disabled' : '' }}>
                                🙏 {{ $hasPrayed ? 'Ви вже помолилися' : 'Молюсь за це' }}
                            </button>
                        </form>

                        @if($prayerRequest->user_id === auth()->id() || auth()->user()->hasRole(['admin', 'leader']))
                            <button type="button"
                                    onclick="document.getElementById('answerModal').classList.remove('hidden')"
                                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium text-sm transition-colors">
                                ✓ Відповідь отримано
                            </button>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Answer Modal -->
<div id="answerModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-lg w-full mx-4">
        <form action="{{ route('prayer-requests.mark-answered', $prayerRequest) }}" method="POST">
            @csrf
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Слава Богу!</h3>
            </div>
            <div class="p-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Поділіться свідченням (необов'язково)
                </label>
                <textarea name="answer_testimony" rows="4"
                          class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                          placeholder="Розкажіть як Бог відповів на молитву..."></textarea>
            </div>
            <div class="px-4 sm:px-6 py-3 sm:py-4 border-t border-gray-200 dark:border-gray-700 flex flex-col-reverse sm:flex-row sm:justify-end gap-2 sm:gap-3">
                <button type="button" onclick="document.getElementById('answerModal').classList.add('hidden')"
                        class="w-full sm:w-auto px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                    Скасувати
                </button>
                <button type="submit"
                        class="w-full sm:w-auto px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                    Підтвердити
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
