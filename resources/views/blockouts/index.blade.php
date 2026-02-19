@extends('layouts.app')

@section('title', 'Мої періоди недоступності')

@section('actions')
<a href="{{ route('blockouts.create') }}"
   class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-xl transition-colors">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Додати період
</a>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Header with back link -->
    <div class="flex items-center gap-4">
        <a href="{{ route('my-profile') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Періоди недоступності</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Вкажіть дати, коли ви не можете служити</p>
        </div>
    </div>

    <!-- Active Blockouts -->
    @if(isset($blockouts['active']) && $blockouts['active']->count() > 0)
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-4 py-3 bg-red-50 dark:bg-red-900/20 border-b border-red-100 dark:border-red-800">
            <h2 class="font-medium text-red-800 dark:text-red-200 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                </svg>
                Активні періоди ({{ $blockouts['active']->count() }})
            </h2>
        </div>
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            @foreach($blockouts['active'] as $blockout)
            <div class="px-4 py-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50">
                <div class="flex-1">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                            @switch($blockout->reason)
                                @case('vacation')
                                    <span class="text-xl">🏖️</span>
                                    @break
                                @case('travel')
                                    <span class="text-xl">✈️</span>
                                    @break
                                @case('sick')
                                    <span class="text-xl">🏥</span>
                                    @break
                                @case('family')
                                    <span class="text-xl">👨‍👩‍👧</span>
                                    @break
                                @case('work')
                                    <span class="text-xl">💼</span>
                                    @break
                                @default
                                    <span class="text-xl">📅</span>
                            @endswitch
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-medium text-gray-900 dark:text-white">{{ $blockout->date_range }}</span>
                                @if($blockout->recurrence !== 'none')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                                    🔄 {{ \App\Models\BlockoutDate::RECURRENCE_OPTIONS[$blockout->recurrence] }}
                                </span>
                                @endif
                            </div>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ $blockout->reason_label }}</span>
                                @if($blockout->reason_note)
                                <span class="text-sm text-gray-500 dark:text-gray-500">— {{ $blockout->reason_note }}</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-2 mt-1 text-xs text-gray-500">
                                <span>{{ $blockout->time_range }}</span>
                                @if(!$blockout->applies_to_all)
                                <span>•</span>
                                <span>{{ $blockout->ministries->pluck('name')->join(', ') }}</span>
                                @else
                                <span>•</span>
                                <span>Всі команди</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('blockouts.edit', $blockout) }}"
                       class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                        </svg>
                    </a>
                    <form action="{{ route('blockouts.destroy', $blockout) }}" method="POST" class="inline"
                          onsubmit="return confirm('{{ __('messages.confirm_delete_blockout') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="p-2 text-gray-400 hover:text-red-600 dark:hover:text-red-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Expired Blockouts -->
    @if(isset($blockouts['expired']) && $blockouts['expired']->count() > 0)
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-600">
            <h2 class="font-medium text-gray-600 dark:text-gray-300 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Минулі періоди ({{ $blockouts['expired']->count() }})
            </h2>
        </div>
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            @foreach($blockouts['expired']->take(5) as $blockout)
            <div class="px-4 py-3 flex items-center justify-between opacity-60">
                <div class="flex items-center gap-3">
                    <span class="text-gray-400">{{ $blockout->date_range }}</span>
                    <span class="text-sm text-gray-500">{{ $blockout->reason_label }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Empty State -->
    @if((!isset($blockouts['active']) || $blockouts['active']->count() === 0) && (!isset($blockouts['expired']) || $blockouts['expired']->count() === 0))
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Немає періодів недоступності</h3>
        <p class="text-gray-500 dark:text-gray-400 mb-6">Вкажіть дати, коли ви не зможете служити</p>
        <a href="{{ route('blockouts.create') }}"
           class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-xl transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Додати період
        </a>
    </div>
    @endif

    <!-- Quick Reference -->
    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4">
        <h3 class="font-medium text-blue-800 dark:text-blue-200 mb-2">Як це працює?</h3>
        <ul class="text-sm text-blue-700 dark:text-blue-300 space-y-1">
            <li>• Вкажіть дати, коли ви недоступні для подій</li>
            <li>• Система автоматично врахує це при плануванні</li>
            <li>• Лідери побачать попередження, якщо спробують вас призначити</li>
            <li>• Незатверджені призначення на ці дати будуть автоматично відхилені</li>
        </ul>
    </div>
</div>
@endsection
