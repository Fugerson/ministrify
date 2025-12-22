@extends('layouts.app')

@section('title', 'Служіння')

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
<x-page-help page="ministries" />

<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($ministries as $ministry)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden border border-gray-100 dark:border-gray-700">
            <div class="p-6">
                <div class="flex items-start justify-between">
                    <div class="flex items-center">
                        <span class="text-3xl">{{ $ministry->icon }}</span>
                        <div class="ml-3">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $ministry->name }}</h3>
                            @if($ministry->leader)
                                <p class="text-sm text-gray-500 dark:text-gray-400">Лідер: {{ $ministry->leader->full_name }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Учасників: {{ $ministry->members->count() }}</p>
                </div>

                @if($ministry->monthly_budget)
                    <div class="mt-4">
                        <div class="flex items-center justify-between text-sm mb-1">
                            <span class="text-gray-500 dark:text-gray-400">Бюджет</span>
                            <span class="text-gray-900 dark:text-white">{{ number_format($ministry->spent_this_month, 0, ',', ' ') }} / {{ number_format($ministry->monthly_budget, 0, ',', ' ') }} ₴</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="h-2 rounded-full {{ $ministry->budget_usage_percent > 90 ? 'bg-red-500' : ($ministry->budget_usage_percent > 70 ? 'bg-yellow-500' : 'bg-green-500') }}"
                                 style="width: {{ min(100, $ministry->budget_usage_percent) }}%"></div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="px-6 py-3 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-100 dark:border-gray-700">
                <a href="{{ route('ministries.show', $ministry) }}"
                   class="text-primary-600 dark:text-primary-400 hover:text-primary-500 text-sm font-medium">
                    Відкрити →
                </a>
            </div>
        </div>
    @empty
        <div class="col-span-full text-center py-12">
            <p class="text-gray-500 dark:text-gray-400">Ще немає служінь.</p>
            @admin
            <a href="{{ route('ministries.create') }}" class="mt-2 inline-block text-primary-600 dark:text-primary-400 hover:text-primary-500">
                Створити перше служіння
            </a>
            @endadmin
        </div>
    @endforelse
    </div>
</div>
@endsection
