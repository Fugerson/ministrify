@extends('layouts.app')

@section('title', 'Моя карта')

@section('content')
<div class="space-y-6">
    <!-- Bank Selection Tabs -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">
        <div class="border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
            <nav class="flex -mb-px" x-data="{ activeTab: '{{ request('tab', 'monobank') }}' }">
                <a href="{{ route('finances.cards', ['tab' => 'monobank']) }}"
                   class="flex-1 px-6 py-4 text-center text-sm font-medium border-b-2 transition-colors {{ request('tab', 'monobank') === 'monobank' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <div class="flex items-center justify-center gap-2">
                        <div class="w-8 h-8 bg-black rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-sm">M</span>
                        </div>
                        <span>Monobank</span>
                        @if($monobankConnected)
                            <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                        @endif
                    </div>
                </a>
                <a href="{{ route('finances.cards', ['tab' => 'privatbank']) }}"
                   class="flex-1 px-6 py-4 text-center text-sm font-medium border-b-2 transition-colors {{ request('tab') === 'privatbank' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <div class="flex items-center justify-center gap-2">
                        <div class="w-8 h-8 bg-green-600 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-sm">P</span>
                        </div>
                        <span>PrivatBank</span>
                        @if($privatbankConnected)
                            <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                        @endif
                    </div>
                </a>
            </nav>
        </div>
    </div>

    <!-- Tab Content -->
    @if(request('tab') === 'privatbank')
        @include('finances.cards.privatbank-content')
    @else
        @include('finances.cards.monobank-content')
    @endif
</div>
@endsection
