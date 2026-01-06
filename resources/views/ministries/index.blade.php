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
@include('partials.section-tabs', ['tabs' => [
    ['route' => 'people.index', 'label' => 'Люди', 'active' => 'people.index', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"/></svg>'],
    ['route' => 'groups.index', 'label' => 'Групи', 'active' => 'groups.*', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>'],
    ['route' => 'ministries.index', 'label' => 'Служіння', 'active' => 'ministries.*', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>'],
]])

<div class="space-y-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
    @forelse($ministries as $ministry)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden border border-gray-100 dark:border-gray-700">
            <div class="p-4 md:p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $ministry->name }}</h3>
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
