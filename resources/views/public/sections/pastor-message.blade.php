{{-- Pastor's Message Section --}}
@if($church->pastor_name && $church->pastor_message)
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-3xl shadow-xl overflow-hidden">
            <div class="md:flex">
                @if($church->pastor_photo)
                <div class="md:w-1/3">
                    <img src="{{ Storage::url($church->pastor_photo) }}" alt="{{ $church->pastor_name }}" class="w-full h-64 md:h-full object-cover">
                </div>
                @endif
                <div class="p-8 md:p-12 {{ $church->pastor_photo ? 'md:w-2/3' : '' }}">
                    <div class="flex items-center gap-2 text-primary-600 mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                        <span class="text-sm font-medium">Слово пастора</span>
                    </div>
                    <blockquote class="text-xl md:text-2xl text-gray-700 leading-relaxed mb-6 italic">
                        "{{ $church->pastor_message }}"
                    </blockquote>
                    <p class="font-semibold text-gray-900">{{ $church->pastor_name }}</p>
                    <p class="text-sm text-gray-500">Пастор церкви</p>
                </div>
            </div>
        </div>
    </div>
</section>
@else
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-3xl shadow-xl overflow-hidden">
            <div class="md:flex">
                <div class="md:w-1/3 bg-gradient-to-br from-primary-100 to-primary-200 flex items-center justify-center min-h-[250px]">
                    <svg class="w-24 h-24 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div class="p-8 md:p-12 md:w-2/3">
                    <div class="flex items-center gap-2 text-primary-600 mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                        <span class="text-sm font-medium">Слово пастора</span>
                    </div>
                    <blockquote class="text-xl md:text-2xl text-gray-700 leading-relaxed mb-6 italic">
                        "Ми раді вітати кожного у нашій церкві. Наші двері завжди відкриті для тих, хто шукає Бога, спільноту та підтримку. Приходьте такими, якими ви є."
                    </blockquote>
                    <p class="font-semibold text-gray-900">Пастор церкви</p>
                    <p class="text-sm text-gray-500">{{ $church->name }}</p>
                </div>
            </div>
        </div>
    </div>
</section>
@endif
