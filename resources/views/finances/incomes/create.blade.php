@extends('layouts.app')

@section('title', __('app.add_income'))

@section('content')
<div class="max-w-2xl mx-auto" x-data="incomeCreateForm()">
    <a href="{{ route('finances.transactions', ['filter' => 'income']) }}" class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-sm mb-6">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        {{ __('app.back') }}
    </a>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('app.new_income') }}</h2>
        </div>

        <form @submit.prevent="submitForm" class="p-6 space-y-6" x-ref="form">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6" x-data="{ currency: '{{ old('currency', 'UAH') }}', exchangeRates: {{ json_encode($exchangeRates) }} }">
                <!-- Amount -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('app.amount_required') }} <span class="text-red-500">*</span>
                    </label>
                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <input type="number" name="amount" value="{{ old('amount') }}" step="0.01" min="0.01" required
                                   class="w-full px-4 py-2 border {{ $errors->has('amount') ? 'border-red-500 dark:border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                   placeholder="0.00">
                        </div>
                        @if(count($enabledCurrencies) > 1)
                        <select name="currency" x-model="currency"
                                class="w-24 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            @foreach($enabledCurrencies as $curr)
                                <option value="{{ $curr }}">{{ \App\Helpers\CurrencyHelper::symbol($curr) }} {{ $curr }}</option>
                            @endforeach
                        </select>
                        @else
                        <input type="hidden" name="currency" value="UAH">
                        <span class="inline-flex items-center px-3 py-2 text-gray-500 dark:text-gray-400">₴</span>
                        @endif
                    </div>
                    <template x-if="currency !== 'UAH' && exchangeRates[currency]">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ __('app.nbu_rate') }}: 1 <span x-text="currency"></span> = <span x-text="exchangeRates[currency]?.toFixed(2)"></span> ₴
                            @if(\App\Models\ExchangeRate::getLatestRateDate())
                                <span class="text-gray-400">({{ __('app.as_of') }} {{ \App\Models\ExchangeRate::getLatestRateDate() }})</span>
                            @endif
                        </p>
                    </template>
                    <template x-if="errors.amount">
                        <p class="mt-1 text-sm text-red-500" x-text="errors.amount[0]"></p>
                    </template>
                </div>

                <!-- Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('app.date_required') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="date" value="{{ old('date', now()->format('Y-m-d')) }}" required
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <template x-if="errors.date">
                        <p class="mt-1 text-sm text-red-500" x-text="errors.date[0]"></p>
                    </template>
                </div>
            </div>

            <!-- Category -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    {{ __('app.category') }} <span class="text-red-500">*</span>
                </label>
                @php
                    $categoriesWithIcon = $categories->map(function($c) {
                        $c->display_name = $c->name;
                        return $c;
                    });
                @endphp
                <x-searchable-select
                    name="category_id"
                    :items="$categoriesWithIcon"
                    :selected="old('category_id')"
                    labelKey="display_name"
                    valueKey="id"
                    colorKey="color"
                    :searchKeys="['name', 'display_name']"
                    placeholder="{{ __('app.search_category') }}"
                    nullText="{{ __('app.select_category') }}"
                    :nullable="false"
                    required
                />
                <template x-if="errors.category_id">
                    <p class="mt-1 text-sm text-red-500" x-text="errors.category_id[0]"></p>
                </template>
            </div>

            <!-- Payment Method -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    {{ __('app.payment_method_label') }} <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-2 gap-3">
                    @php $paymentMethods = ['cash' => __('app.cash_payment'), 'card' => __('app.card_payment')]; @endphp
                    @foreach($paymentMethods as $value => $label)
                        <label class="relative flex items-center justify-center px-4 py-3 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <input type="radio" name="payment_method" value="{{ $value }}" {{ old('payment_method', 'cash') == $value ? 'checked' : '' }} class="sr-only peer">
                            <span class="text-sm text-gray-700 dark:text-gray-300 peer-checked:text-primary-600 dark:peer-checked:text-primary-400 peer-checked:font-medium">{{ $label }}</span>
                            <div class="absolute inset-0 border-2 border-transparent peer-checked:border-primary-500 rounded-lg pointer-events-none"></div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Always anonymous -->
            <input type="hidden" name="is_anonymous" value="1">

            <!-- Notes -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    {{ __('app.notes_label_simple') }}
                </label>
                <textarea name="notes" rows="3"
                          class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                          placeholder="{{ __('app.internal_notes_placeholder') }}">{{ old('notes') }}</textarea>
            </div>

            <!-- Submit -->
            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 sm:gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('finances.transactions', ['filter' => 'income']) }}"
                   class="w-full sm:w-auto px-4 py-2 text-center text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                    {{ __('app.cancel') }}
                </a>
                <button type="submit" :disabled="saving"
                        class="w-full sm:w-auto px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50">
                    <span x-show="!saving">{{ __('app.save') }}</span>
                    <span x-show="saving" class="flex items-center justify-center gap-2">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        {{ __('app.saving') }}
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function incomeCreateForm() {
    return {
        saving: false,
        errors: {},
        async submitForm() {
            this.saving = true;
            this.errors = {};
            const formData = new FormData(this.$refs.form);
            try {
                const response = await fetch('{{ route("finances.incomes.store") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: formData,
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    if (response.status === 422 && data.errors) {
                        this.errors = data.errors;
                        showToast('error', @js(__('app.check_form_errors')));
                    } else {
                        showToast('error', data.message || @js(__('app.save_error')));
                    }
                    this.saving = false;
                    return;
                }
                showToast('success', data.message || @js(__('app.saved')));
                setTimeout(() => Livewire.navigate(data.redirect_url), 800);
            } catch (e) {
                showToast('error', @js(__('app.connection_error')));
                this.saving = false;
            }
        }
    }
}
</script>
@endsection
