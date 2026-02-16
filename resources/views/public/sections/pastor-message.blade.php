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
@endif
