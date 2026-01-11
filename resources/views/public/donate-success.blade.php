@extends('public.layout')

@section('title', 'Дякуємо за пожертву - ' . $church->name)

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
    <div class="text-center">
        <div class="w-24 h-24 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-8">
            <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>

        <h1 class="text-4xl font-bold text-gray-900 mb-4">Дякуємо за вашу щедрість!</h1>

        @if(session('donation_amount'))
            <p class="text-2xl text-primary-600 font-semibold mb-6">{{ session('donation_amount') }}</p>
        @endif

        <p class="text-xl text-gray-600 mb-8 max-w-lg mx-auto">
            Ваша пожертва допоможе нам продовжувати працю та підтримувати громаду. Нехай Бог благословить вас!
        </p>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 mb-8">
            <h3 class="font-semibold text-gray-900 mb-4">Що далі?</h3>
            <ul class="space-y-3 text-left text-gray-600">
                <li class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-primary-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Ви отримаєте підтвердження на email (якщо ви вказали адресу)</span>
                </li>
                <li class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-primary-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Ваша пожертва буде використана відповідно до вказаного призначення</span>
                </li>
                <li class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-primary-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Приєднуйтесь до наших подій та команд</span>
                </li>
            </ul>
        </div>

        <div class="flex flex-wrap justify-center gap-4">
            <a href="{{ route('public.church', $church->slug) }}"
               class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors">
                Повернутись на головну
            </a>
            <a href="{{ route('public.events', $church->slug) }}"
               class="px-6 py-3 border border-gray-300 hover:border-gray-400 text-gray-700 font-medium rounded-xl transition-colors">
                Переглянути події
            </a>
        </div>
    </div>
</div>
@endsection
