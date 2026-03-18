{{-- Testimonials Section --}}
@if(isset($testimonials) && $testimonials->count() > 0)
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900">{{ __('app.public_testimonials_title') }}</h2>
            <p class="text-gray-600 mt-2">{{ __('app.public_testimonials_subtitle') }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($testimonials as $testimonial)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <svg class="w-10 h-10 text-primary-200 mb-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/>
                    </svg>
                    <p class="text-gray-600 leading-relaxed mb-4">{{ $testimonial->content }}</p>
                    <div class="flex items-center gap-3">
                        @if($testimonial->author_photo)
                            <img src="{{ Storage::url($testimonial->author_photo) }}" alt="{{ $testimonial->author_name }}" class="w-10 h-10 rounded-full object-cover" loading="lazy">
                        @else
                            <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                                <span class="text-primary-600 font-semibold">{{ substr($testimonial->author_name, 0, 1) }}</span>
                            </div>
                        @endif
                        <div>
                            <p class="font-semibold text-gray-900">{{ $testimonial->author_name }}</p>
                            @if($testimonial->author_role)
                                <p class="text-sm text-gray-500">{{ $testimonial->author_role }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@else
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900">{{ __('app.public_testimonials_title') }}</h2>
            <p class="text-gray-600 mt-2">{{ __('app.public_testimonials_subtitle') }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach([
                ['content' => __('app.public_testimonial_1_content'), 'name' => __('app.public_testimonial_1_name'), 'role' => __('app.public_testimonial_1_role')],
                ['content' => __('app.public_testimonial_2_content'), 'name' => __('app.public_testimonial_2_name'), 'role' => __('app.public_testimonial_2_role')],
                ['content' => __('app.public_testimonial_3_content'), 'name' => __('app.public_testimonial_3_name'), 'role' => __('app.public_testimonial_3_role')],
            ] as $testimonial)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <svg class="w-10 h-10 text-primary-200 mb-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/>
                    </svg>
                    <p class="text-gray-600 leading-relaxed mb-4">{{ $testimonial['content'] }}</p>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                            <span class="text-primary-600 font-semibold">{{ mb_substr($testimonial['name'], 0, 1) }}</span>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">{{ $testimonial['name'] }}</p>
                            <p class="text-sm text-gray-500">{{ $testimonial['role'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
