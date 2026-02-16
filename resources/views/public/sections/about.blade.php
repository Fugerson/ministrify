{{-- About Section --}}
@php $aboutContent = $church->about_content; @endphp
@if($aboutContent['mission'] || $aboutContent['vision'] || !empty($aboutContent['values']))
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900">Про нас</h2>
            <p class="text-gray-600 mt-2">Дізнайтесь більше про нашу церкву</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @if($aboutContent['mission'])
            <div class="text-center p-6">
                <div class="w-14 h-14 rounded-xl bg-primary-100 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-900 text-lg mb-2">Місія</h3>
                <p class="text-gray-600 leading-relaxed">{{ $aboutContent['mission'] }}</p>
            </div>
            @endif

            @if($aboutContent['vision'])
            <div class="text-center p-6">
                <div class="w-14 h-14 rounded-xl bg-primary-100 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-900 text-lg mb-2">Візія</h3>
                <p class="text-gray-600 leading-relaxed">{{ $aboutContent['vision'] }}</p>
            </div>
            @endif

            @if(!empty($aboutContent['values']))
            <div class="text-center p-6">
                <div class="w-14 h-14 rounded-xl bg-primary-100 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-900 text-lg mb-2">Цінності</h3>
                <ul class="text-gray-600 space-y-1">
                    @foreach((array)$aboutContent['values'] as $value)
                        <li>{{ $value }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>

        @if($aboutContent['history'] ?? null)
        <div class="mt-12 max-w-3xl mx-auto text-center">
            <h3 class="font-semibold text-gray-900 text-lg mb-4">Наша історія</h3>
            <p class="text-gray-600 leading-relaxed">{{ $aboutContent['history'] }}</p>
        </div>
        @endif
    </div>
</section>
@endif
