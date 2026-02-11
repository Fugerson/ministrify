@if(isset($userChurches) && $userChurches->count() > 1)
<div x-data="{ open: false }" class="relative px-3 py-2 border-b border-gray-200 dark:border-gray-700">
    <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
        <span class="flex items-center gap-2 truncate">
            @if($currentChurch->logo)
                <img src="/storage/{{ $currentChurch->logo }}" alt="" class="w-5 h-5 rounded object-contain flex-shrink-0">
            @else
                <span class="text-sm flex-shrink-0">⛪</span>
            @endif
            <span class="truncate">{{ $currentChurch->name }}</span>
        </span>
        <svg class="w-4 h-4 flex-shrink-0 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <div x-show="open" x-cloak @click.outside="open = false"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="mt-1 py-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg z-50">
        @foreach($userChurches as $church)
            @if($church->id !== $currentChurch->id)
            <form method="POST" action="{{ route('church.switch') }}">
                @csrf
                <input type="hidden" name="church_id" value="{{ $church->id }}">
                <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-left">
                    @if($church->logo)
                        <img src="/storage/{{ $church->logo }}" alt="" class="w-5 h-5 rounded object-contain flex-shrink-0">
                    @else
                        <span class="text-sm flex-shrink-0">⛪</span>
                    @endif
                    <span class="truncate">{{ $church->name }}</span>
                </button>
            </form>
            @endif
        @endforeach
    </div>
</div>
@endif
