@props([
    'type' => 'text', // text, avatar, card, table-row, stat
    'lines' => 3,
    'class' => ''
])

@switch($type)
    @case('avatar')
        <div {{ $attributes->merge(['class' => 'skeleton rounded-full w-10 h-10 ' . $class]) }}></div>
        @break

    @case('avatar-lg')
        <div {{ $attributes->merge(['class' => 'skeleton rounded-full w-16 h-16 ' . $class]) }}></div>
        @break

    @case('card')
        <div {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-800 rounded-2xl p-6 ' . $class]) }}>
            <div class="flex items-center gap-4 mb-4">
                <div class="skeleton rounded-xl w-12 h-12"></div>
                <div class="flex-1 space-y-2">
                    <div class="skeleton h-4 w-3/4 rounded"></div>
                    <div class="skeleton h-3 w-1/2 rounded"></div>
                </div>
            </div>
            <div class="space-y-2">
                <div class="skeleton h-3 w-full rounded"></div>
                <div class="skeleton h-3 w-5/6 rounded"></div>
                <div class="skeleton h-3 w-4/6 rounded"></div>
            </div>
        </div>
        @break

    @case('stat')
        <div {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-800 rounded-2xl p-6 ' . $class]) }}>
            <div class="flex items-center justify-between mb-4">
                <div class="skeleton h-4 w-24 rounded"></div>
                <div class="skeleton rounded-xl w-10 h-10"></div>
            </div>
            <div class="skeleton h-8 w-20 rounded mb-2"></div>
            <div class="skeleton h-3 w-32 rounded"></div>
        </div>
        @break

    @case('table-row')
        <div {{ $attributes->merge(['class' => 'flex items-center gap-4 p-4 ' . $class]) }}>
            <div class="skeleton rounded-full w-10 h-10"></div>
            <div class="flex-1 space-y-2">
                <div class="skeleton h-4 w-48 rounded"></div>
                <div class="skeleton h-3 w-32 rounded"></div>
            </div>
            <div class="skeleton h-6 w-20 rounded-full"></div>
        </div>
        @break

    @case('list')
        <div {{ $attributes->merge(['class' => 'space-y-3 ' . $class]) }}>
            @for($i = 0; $i < $lines; $i++)
                <div class="flex items-center gap-3">
                    <div class="skeleton rounded-full w-8 h-8"></div>
                    <div class="flex-1">
                        <div class="skeleton h-4 w-{{ ['3/4', '2/3', '1/2'][$i % 3] }} rounded"></div>
                    </div>
                </div>
            @endfor
        </div>
        @break

    @case('image')
        <div {{ $attributes->merge(['class' => 'skeleton rounded-xl aspect-video ' . $class]) }}></div>
        @break

    @default
        <div {{ $attributes->merge(['class' => 'space-y-2 ' . $class]) }}>
            @for($i = 0; $i < $lines; $i++)
                <div class="skeleton h-4 rounded" style="width: {{ 100 - ($i * 15) }}%"></div>
            @endfor
        </div>
@endswitch
