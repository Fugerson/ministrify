{{-- Blog Section --}}
@if(isset($blogPosts) && $blogPosts->count() > 0)
<section class="py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900">Блог</h2>
            <p class="text-gray-600 mt-2">Статті та роздуми</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($blogPosts as $post)
                <article class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-shadow">
                    @if($post->featured_image)
                        <div class="h-48 overflow-hidden">
                            <img src="{{ Storage::url($post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover" loading="lazy">
                        </div>
                    @endif
                    <div class="p-5">
                        <p class="text-sm text-gray-500 mb-2">{{ $post->published_at->translatedFormat('d F Y') }}</p>
                        <h3 class="font-semibold text-gray-900 mb-2">{{ $post->title }}</h3>
                        @if($post->excerpt)
                            <p class="text-sm text-gray-600 line-clamp-3">{{ $post->excerpt }}</p>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif
