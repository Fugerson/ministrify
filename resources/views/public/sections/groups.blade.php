{{-- Groups Section --}}
@if(isset($groups) && $groups->count() > 0)
<section class="py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900">Малі групи</h2>
            <p class="text-gray-600 mt-2">Спільнота, яка росте разом</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($groups as $group)
                <a href="{{ route('public.group', [$church->slug, $group->slug]) }}"
                   class="group bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl hover:border-primary-200 transition-all duration-300">
                    @if($group->cover_image)
                        <div class="h-40 overflow-hidden">
                            <img src="{{ Storage::url($group->cover_image) }}" alt="{{ $group->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">
                        </div>
                    @else
                        <div class="h-40 flex items-center justify-center" style="background: linear-gradient(135deg, {{ $group->color ?? '#6366f1' }} 0%, {{ $group->color ?? '#6366f1' }}99 100%);">
                            <svg class="w-12 h-12 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                    @endif
                    <div class="p-5">
                        <h3 class="font-semibold text-gray-900 group-hover:text-primary-600 transition-colors">{{ $group->name }}</h3>
                        @if($group->meeting_schedule || ($group->meeting_day && $group->meeting_time))
                            <p class="text-sm text-gray-500 mt-1">
                                @if($group->meeting_schedule)
                                    {{ $group->meeting_schedule }}
                                @else
                                    {{ $group->meeting_day_name }}@if($group->meeting_time), {{ $group->meeting_time?->format('H:i') }}@endif
                                @endif
                            </p>
                        @endif
                        <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100">
                            <span class="text-xs text-gray-400">{{ $group->members_count }} учасників</span>
                            @if($group->allow_join_requests)
                                <span class="text-xs text-primary-600 font-medium">Приєднатися</span>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif
