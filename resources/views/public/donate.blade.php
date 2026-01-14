@extends('public.layout')

@section('title', 'Пожертвування - ' . $church->name)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Header -->
    <div class="text-center mb-12">
        <div class="w-20 h-20 rounded-full bg-primary-100 flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
        </div>
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Підтримати церкву</h1>
        <p class="text-xl text-gray-600 max-w-2xl mx-auto">
            Ваша пожертва допомагає нам продовжувати працю та підтримувати громаду
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main donation form -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <form action="{{ route('public.donate.process', $church->slug) }}" method="POST" x-data="{
                    amount: {{ (int) request('amount', 100) }},
                    customAmount: false,
                    purpose: @json(request('campaign', ''))
                }">
                    @csrf

                    <!-- Amount selection -->
                    <div class="mb-8">
                        <label class="block text-sm font-medium text-gray-700 mb-3">Сума пожертви</label>
                        <div class="grid grid-cols-3 gap-3 mb-3">
                            @foreach([50, 100, 200, 500, 1000, 2000] as $preset)
                                <button type="button"
                                        @click="amount = {{ $preset }}; customAmount = false"
                                        :class="{ 'ring-2 ring-primary-500 border-primary-500 bg-primary-50': amount == {{ $preset }} && !customAmount }"
                                        class="py-3 px-4 border border-gray-300 rounded-xl text-center font-medium text-gray-700 hover:border-primary-400 transition-colors">
                                    {{ $preset }} грн
                                </button>
                            @endforeach
                        </div>
                        <div class="relative">
                            <input type="number" name="amount" x-model="amount" @focus="customAmount = true"
                                   :class="{ 'ring-2 ring-primary-500 border-primary-500': customAmount }"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-lg"
                                   placeholder="Інша сума" min="1">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500">грн</span>
                        </div>
                        @error('amount')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Campaign selection -->
                    @if($campaigns->count() > 0)
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-3">Цільовий збір (необов'язково)</label>
                        <select name="campaign_id"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Загальний фонд</option>
                            @foreach($campaigns as $campaign)
                                <option value="{{ $campaign->id }}">{{ $campaign->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <!-- Donor info -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-3">Ваші дані (необов'язково)</label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <input type="text" name="donor_name" value="{{ old('donor_name') }}"
                                       class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                       placeholder="Ваше ім'я">
                            </div>
                            <div>
                                <input type="email" name="donor_email" value="{{ old('donor_email') }}"
                                       class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                       placeholder="Email">
                            </div>
                            <div>
                                <input type="tel" name="donor_phone" value="{{ old('donor_phone') }}"
                                       class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                       placeholder="Телефон">
                            </div>
                        </div>
                    </div>

                    <!-- Message -->
                    <div class="mb-6">
                        <textarea name="message" rows="2"
                                  class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                  placeholder="Коментар до пожертви (необов'язково)">{{ old('message') }}</textarea>
                    </div>

                    <!-- Anonymous option -->
                    <div class="mb-6">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="is_anonymous" value="1"
                                   class="w-5 h-5 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                            <span class="text-gray-700">Зробити пожертву анонімною</span>
                        </label>
                    </div>

                    <!-- Payment method selection -->
                    @if(isset($paymentMethods) && ($paymentMethods['liqpay'] || $paymentMethods['monobank']))
                    <div class="mb-8">
                        <label class="block text-sm font-medium text-gray-700 mb-3">Спосіб оплати</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @if($paymentMethods['liqpay'])
                            <label class="relative flex items-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-primary-400 transition-colors has-[:checked]:border-primary-500 has-[:checked]:bg-primary-50">
                                <input type="radio" name="payment_method" value="liqpay" class="sr-only peer" checked>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">LiqPay</p>
                                        <p class="text-xs text-gray-500">Картка Visa/Mastercard</p>
                                    </div>
                                </div>
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 w-5 h-5 border-2 border-gray-300 rounded-full peer-checked:border-primary-500 peer-checked:bg-primary-500 peer-checked:after:content-[''] peer-checked:after:absolute peer-checked:after:inset-1 peer-checked:after:bg-white peer-checked:after:rounded-full"></div>
                            </label>
                            @endif

                            @if($paymentMethods['monobank'])
                            <label class="relative flex items-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-primary-400 transition-colors has-[:checked]:border-primary-500 has-[:checked]:bg-primary-50">
                                <input type="radio" name="payment_method" value="monobank" class="sr-only peer" {{ !$paymentMethods['liqpay'] ? 'checked' : '' }}>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-black rounded-lg flex items-center justify-center">
                                        <span class="text-white font-bold text-sm">mono</span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">Monobank</p>
                                        <p class="text-xs text-gray-500">Банка для збору</p>
                                    </div>
                                </div>
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 w-5 h-5 border-2 border-gray-300 rounded-full peer-checked:border-primary-500 peer-checked:bg-primary-500 peer-checked:after:content-[''] peer-checked:after:absolute peer-checked:after:inset-1 peer-checked:after:bg-white peer-checked:after:rounded-full"></div>
                            </label>
                            @endif
                        </div>
                    </div>
                    @else
                    <div class="mb-8 p-4 bg-amber-50 border border-amber-200 rounded-xl">
                        <p class="text-amber-800 text-sm">
                            Онлайн-оплата наразі недоступна. Зверніться до адміністрації церкви.
                        </p>
                    </div>
                    @endif

                    <!-- Submit -->
                    @if(isset($paymentMethods) && ($paymentMethods['liqpay'] || $paymentMethods['monobank']))
                    <button type="submit"
                            class="w-full py-4 bg-primary-600 hover:bg-primary-700 text-white text-lg font-semibold rounded-xl transition-colors shadow-lg hover:shadow-xl">
                        Пожертвувати <span x-text="amount"></span> грн
                    </button>
                    @endif

                    <p class="text-center text-sm text-gray-500 mt-4">
                        Безпечна оплата. Ваші дані захищені.
                    </p>
                </form>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Active campaigns -->
            @if($campaigns->count() > 0)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Активні збори</h3>
                    <div class="space-y-4">
                        @foreach($campaigns as $campaign)
                            <div class="p-4 bg-gray-50 rounded-xl">
                                <h4 class="font-medium text-gray-900 mb-1">{{ $campaign->name }}</h4>
                                @if($campaign->goal_amount)
                                    <div class="mt-2">
                                        <div class="flex justify-between text-xs mb-1">
                                            <span class="text-gray-500">{{ number_format($campaign->raised_amount, 0, ',', ' ') }} грн</span>
                                            <span class="text-gray-900 font-medium">{{ $campaign->progress_percent }}%</span>
                                        </div>
                                        <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                                            <div class="h-full bg-primary-500 rounded-full" style="width: {{ $campaign->progress_percent }}%"></div>
                                        </div>
                                    </div>
                                @endif
                                @if($campaign->days_remaining !== null)
                                    <p class="text-xs text-gray-500 mt-2">
                                        @if($campaign->days_remaining > 0)
                                            Залишилось {{ $campaign->days_remaining }} днів
                                        @else
                                            Збір завершено
                                        @endif
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Info box -->
            <div class="bg-primary-50 rounded-2xl p-6">
                <h3 class="font-semibold text-gray-900 mb-3">Як використовуються пожертви</h3>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-primary-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Підтримка команд та програм</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-primary-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Допомога нужденним</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-primary-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Утримання приміщення</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-primary-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Місіонерська діяльність</span>
                    </li>
                </ul>
            </div>

            <!-- Contact -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-semibold text-gray-900 mb-3">Питання?</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Якщо у вас є питання щодо пожертвувань, зв'яжіться з нами.
                </p>
                <a href="{{ route('public.contact', $church->slug) }}"
                   class="inline-flex items-center gap-2 text-primary-600 hover:text-primary-700 font-medium text-sm">
                    Зв'язатися
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
