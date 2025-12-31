@extends('layouts.app')

@section('title', 'Шаблони дизайну')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('website-builder.index') }}" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Шаблони дизайну</h1>
                <p class="text-gray-600 dark:text-gray-400">Оберіть стиль для вашого сайту</p>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-400 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <!-- Templates Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($templates as $key => $template)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border-2 {{ $currentTemplate === $key ? 'border-primary-500' : 'border-gray-200 dark:border-gray-700' }} overflow-hidden group">
                <!-- Preview Image -->
                <div class="aspect-video bg-gradient-to-br {{ $key === 'dark' ? 'from-gray-900 to-gray-800' : ($key === 'warm' ? 'from-amber-100 to-orange-100' : ($key === 'bold' ? 'from-indigo-600 to-purple-600' : ($key === 'classic' ? 'from-stone-100 to-stone-200' : ($key === 'minimal' ? 'from-gray-50 to-white' : 'from-blue-50 to-indigo-100')))) }} relative">
                    <div class="absolute inset-0 flex items-center justify-center">
                        <!-- Template Preview Placeholder -->
                        <div class="w-4/5 h-4/5 bg-white/90 dark:bg-gray-900/90 rounded-lg shadow-lg overflow-hidden">
                            <div class="h-2 bg-{{ $key === 'dark' ? 'gray-700' : ($key === 'warm' ? 'amber-500' : ($key === 'bold' ? 'purple-600' : ($key === 'classic' ? 'stone-700' : 'primary-500'))) }}"></div>
                            <div class="p-3 space-y-2">
                                <div class="w-2/3 h-2 bg-gray-200 dark:bg-gray-700 rounded"></div>
                                <div class="w-1/2 h-1.5 bg-gray-100 dark:bg-gray-800 rounded"></div>
                                <div class="flex gap-2 mt-3">
                                    <div class="w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded"></div>
                                    <div class="w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded"></div>
                                    <div class="w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if($currentTemplate === $key)
                        <div class="absolute top-3 right-3">
                            <span class="px-2 py-1 bg-primary-500 text-white text-xs font-medium rounded-full">
                                Активний
                            </span>
                        </div>
                    @endif
                </div>

                <!-- Info -->
                <div class="p-4">
                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ $template['name'] }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $template['description'] }}</p>

                    <!-- Font Info -->
                    <div class="flex items-center gap-2 mt-3 text-xs text-gray-400 dark:text-gray-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        <span>{{ $template['fonts']['heading'] }} + {{ $template['fonts']['body'] }}</span>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-2 mt-4">
                        @if($currentTemplate !== $key)
                            <form action="{{ route('website-builder.templates.apply', $key) }}" method="POST" class="flex-1">
                                @csrf
                                <button type="submit" class="w-full px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                                    Застосувати
                                </button>
                            </form>
                        @else
                            <button disabled class="flex-1 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 font-medium rounded-lg cursor-not-allowed">
                                Активний
                            </button>
                        @endif
                        <a href="{{ route('public.church', $church->slug) }}?preview_template={{ $key }}" target="_blank"
                           class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Info -->
    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800 p-4">
        <div class="flex gap-3">
            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="text-sm text-blue-700 dark:text-blue-300">
                <p class="font-medium">Про шаблони</p>
                <p class="mt-1">Кожен шаблон має свій унікальний стиль, шрифти та оформлення. Після вибору шаблону ви можете додатково налаштувати кольори та інші параметри в розділі "Кольори та стиль".</p>
            </div>
        </div>
    </div>
</div>
@endsection
