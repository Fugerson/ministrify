@extends('layouts.app')

@section('title', 'Команди')

@section('actions')
@admin
<a href="{{ route('ministries.create') }}"
   class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Додати
</a>
@endadmin
@endsection

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
    @forelse($ministries as $ministry)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden border border-gray-100 dark:border-gray-700">
            <div class="p-4 md:p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $ministry->name }}</h3>
                            @if($ministry->is_private)
                                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" title="Приватна команда">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            @endif
                        </div>
                        @if($ministry->leader)
                            <p class="text-sm text-gray-500 dark:text-gray-400">Лідер: {{ $ministry->leader->full_name }}</p>
                        @endif
                    </div>
                    @if($ministry->color)
                        <div class="w-3 h-3 rounded-full" style="background-color: {{ $ministry->color }}"></div>
                    @endif
                </div>

                <div class="mt-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Учасників: {{ $ministry->members->count() }}</p>
                </div>
            </div>

            <div class="px-4 md:px-6 py-3 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-100 dark:border-gray-700">
                <a href="{{ route('ministries.show', $ministry) }}"
                   class="text-primary-600 dark:text-primary-400 hover:text-primary-500 text-sm font-medium flex items-center">
                    Відкрити
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>
    @empty
        <div class="col-span-full text-center py-8 md:py-12">
            <p class="text-gray-500 dark:text-gray-400">Ще немає команд.</p>
            @admin
            <a href="{{ route('ministries.create') }}" class="mt-2 inline-block text-primary-600 dark:text-primary-400 hover:text-primary-500">
                Створити першу команду
            </a>
            @endadmin
        </div>
    @endforelse
    </div>
</div>
@endsection
