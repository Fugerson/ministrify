@extends('layouts.app')

@section('title', 'Історія платежів')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Історія платежів</h1>
        <a href="{{ route('billing.index') }}"
           class="text-primary-600 dark:text-primary-400 hover:underline">
            Назад до тарифів
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
        @if($payments->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-500 dark:text-gray-400">ID</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-500 dark:text-gray-400">Дата</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-500 dark:text-gray-400">Опис</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-500 dark:text-gray-400">План</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-500 dark:text-gray-400">Сума</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-500 dark:text-gray-400">Статус</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($payments as $payment)
                            <tr>
                                <td class="py-3 px-4 text-sm text-gray-500 dark:text-gray-400 font-mono">
                                    {{ $payment->order_id }}
                                </td>
                                <td class="py-3 px-4 text-sm text-gray-900 dark:text-white">
                                    {{ $payment->created_at->format('d.m.Y H:i') }}
                                </td>
                                <td class="py-3 px-4 text-sm text-gray-600 dark:text-gray-400">
                                    {{ $payment->description }}
                                </td>
                                <td class="py-3 px-4 text-sm text-gray-900 dark:text-white">
                                    {{ $payment->subscriptionPlan?->name ?? '-' }}
                                </td>
                                <td class="py-3 px-4 text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $payment->formatted_amount }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full
                                        @if($payment->status === 'success') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                                        @elseif($payment->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400
                                        @elseif($payment->status === 'failure') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400
                                        @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-400
                                        @endif">
                                        {{ $payment->status_label }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                {{ $payments->links() }}
            </div>
        @else
            <div class="p-12 text-center">
                <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Немає платежів</h3>
                <p class="text-gray-500 dark:text-gray-400">Історія платежів поки порожня</p>
            </div>
        @endif
    </div>
</div>
@endsection
