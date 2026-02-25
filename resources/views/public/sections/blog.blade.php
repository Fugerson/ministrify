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
@else
<section class="py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900">Блог</h2>
            <p class="text-gray-600 mt-2">Статті та роздуми</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach([
                ['title' => 'Як молитва змінює наше життя', 'excerpt' => 'Молитва — це не просто слова, а живий діалог з Богом, який перетворює наше серце та розум.', 'date' => now()->subDays(3)->translatedFormat('d F Y'), 'color' => 'from-primary-100 to-primary-200'],
                ['title' => 'Сила спільноти віруючих', 'excerpt' => 'Бог створив нас для спільноти. Разом ми сильніші, і разом ми можемо більше для Його Царства.', 'date' => now()->subDays(10)->translatedFormat('d F Y'), 'color' => 'from-blue-100 to-blue-200'],
                ['title' => 'Щоденне читання Біблії', 'excerpt' => 'Практичні поради для тих, хто хоче зробити читання Божого Слова щоденною звичкою.', 'date' => now()->subDays(17)->translatedFormat('d F Y'), 'color' => 'from-green-100 to-green-200'],
            ] as $post)
                <article class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-shadow">
                    <div class="h-48 bg-gradient-to-br {{ $post['color'] }} flex items-center justify-center">
                        <svg class="w-16 h-16 text-primary-300/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                        </svg>
                    </div>
                    <div class="p-5">
                        <p class="text-sm text-gray-500 mb-2">{{ $post['date'] }}</p>
                        <h3 class="font-semibold text-gray-900 mb-2">{{ $post['title'] }}</h3>
                        <p class="text-sm text-gray-600 line-clamp-3">{{ $post['excerpt'] }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif
