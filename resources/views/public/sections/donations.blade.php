{{-- Donations Section --}}
@if(isset($campaigns) && $campaigns->count() > 0)
<section class="py-16 bg-gradient-to-br from-primary-50 to-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900">Підтримайте нас</h2>
            <p class="text-gray-600 mt-2">Ваша пожертва допомагає нам служити громаді</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl mx-auto">
            @foreach($campaigns as $campaign)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-semibold text-gray-900 text-lg">{{ $campaign->name }}</h3>
                    @if($campaign->description)
                        <p class="text-sm text-gray-500 mt-2">{{ $campaign->description }}</p>
                    @endif
                    @if($campaign->goal_amount)
                        <div class="mt-4">
                            <div class="flex justify-between text-sm mb-2">
                                <span class="text-gray-500">Зібрано</span>
                                <span class="font-medium text-gray-900">{{ number_format($campaign->raised_amount, 0, ',', ' ') }} / {{ number_format($campaign->goal_amount, 0, ',', ' ') }} грн</span>
                            </div>
                            <div class="w-full h-3 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-primary-500 rounded-full transition-all duration-500" style="width: {{ $campaign->progress_percent }}%"></div>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
