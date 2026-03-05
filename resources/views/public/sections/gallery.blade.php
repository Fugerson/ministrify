{{-- Gallery Section --}}
@if(isset($galleries) && $galleries->count() > 0)
<section class="py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900">Галерея</h2>
            <p class="text-gray-600 mt-2">Моменти з життя нашої церкви</p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($galleries as $gallery)
                @if($gallery->photos->count() > 0)
                    @foreach($gallery->photos->take(4) as $photo)
                        <div class="aspect-square rounded-xl overflow-hidden">
                            <img src="{{ Storage::url($photo->file_path) }}" alt="{{ $gallery->title }}" class="w-full h-full object-cover hover:scale-105 transition-transform duration-300 cursor-pointer hover:opacity-80 transition-opacity" loading="lazy"
                                 @click="$dispatch('open-lightbox', '{{ Storage::url($photo->file_path) }}')">
                        </div>
                    @endforeach
                @endif
            @endforeach
        </div>
    </div>
</section>
@elseif($isAdmin ?? false)
<section class="py-16 opacity-60">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-gray-900">Галерея</h2>
            <p class="text-sm text-amber-600 mt-2">Додайте фото через Конструктор сайту → Галерея</p>
        </div>
    </div>
</section>
@endif
