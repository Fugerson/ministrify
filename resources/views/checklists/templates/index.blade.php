@extends('layouts.app')

@section('title', 'Шаблони чеклистів')

@section('actions')
<a href="{{ route('checklists.templates.create') }}"
   class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-xl transition-colors">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Новий шаблон
</a>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Info -->
    <div class="bg-blue-50 dark:bg-blue-900/30 rounded-xl p-4 flex items-start gap-3">
        <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
            <p class="text-sm text-blue-700 dark:text-blue-300">
                Шаблони чеклистів допомагають організувати підготовку до подій. Створіть шаблон один раз і використовуйте для будь-якої події.
            </p>
        </div>
    </div>

    <!-- Templates Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($templates as $template)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition-shadow">
                <div class="p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white">{{ $template->name }}</h3>
                            @if($template->description)
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ Str::limit($template->description, 80) }}</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-1">
                            <a href="{{ route('checklists.templates.edit', $template) }}"
                               class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <form method="POST" action="{{ route('checklists.templates.destroy', $template) }}" onsubmit="return confirm('{{ __('messages.confirm_delete_template') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-gray-400 hover:text-red-500 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="mt-4 space-y-2">
                        @foreach($template->items->take(4) as $item)
                            <div class="flex items-center gap-2 text-sm">
                                <div class="w-4 h-4 rounded border-2 border-gray-300 dark:border-gray-600 flex-shrink-0"></div>
                                <span class="text-gray-700 dark:text-gray-300 truncate">{{ $item->title }}</span>
                            </div>
                        @endforeach
                        @if($template->items->count() > 4)
                            <p class="text-xs text-gray-400 dark:text-gray-500 pl-6">+ ще {{ $template->items->count() - 4 }} пунктів</p>
                        @endif
                    </div>
                </div>

                <div class="px-5 py-3 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">{{ $template->items->count() }} пунктів</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
                <div class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <h3 class="font-medium text-gray-900 dark:text-white mb-2">Немає шаблонів</h3>
                <p class="text-gray-500 dark:text-gray-400 text-sm mb-4">Створіть перший шаблон чеклиста для подій</p>
                <a href="{{ route('checklists.templates.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-xl transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Створити шаблон
                </a>
            </div>
        @endforelse
    </div>
</div>
@endsection
