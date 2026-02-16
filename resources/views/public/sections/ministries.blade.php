{{-- Ministries Section --}}
@if(isset($ministries) && $ministries->count() > 0)
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900">Наші команди</h2>
            <p class="text-gray-600 mt-2">Знайдіть своє місце в команді</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($ministries as $ministry)
                <a href="{{ route('public.ministry', [$church->slug, $ministry->slug]) }}"
                   class="group bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-xl hover:border-primary-200 transition-all duration-300">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 rounded-xl flex items-center justify-center flex-shrink-0"
                             style="background-color: {{ $ministry->color }}30;">
                            <svg class="w-7 h-7" style="color: {{ $ministry->color }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-gray-900 group-hover:text-primary-600 transition-colors">{{ $ministry->name }}</h3>
                            @if($ministry->public_description)
                                <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ $ministry->public_description }}</p>
                            @elseif($ministry->description)
                                <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ $ministry->description }}</p>
                            @endif
                            <p class="text-xs text-gray-400 mt-2">{{ $ministry->members_count }} учасників</p>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif
