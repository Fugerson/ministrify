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
                @if($gallery->images && count($gallery->images) > 0)
                    @foreach(array_slice($gallery->images, 0, 4) as $image)
                        <div class="aspect-square rounded-xl overflow-hidden">
                            <img src="{{ Storage::url($image) }}" alt="{{ $gallery->title }}" class="w-full h-full object-cover hover:scale-105 transition-transform duration-300" loading="lazy">
                        </div>
                    @endforeach
                @endif
            @endforeach
        </div>
    </div>
</section>
@endif
