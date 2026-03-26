@props([
    'label' => null,
    'name' => null,
    'required' => false,
    'hint' => null,
])

<div {{ $attributes->merge(['class' => '']) }}>
    @if($label)
        <label @if($name) for="{{ $name }}" @endif class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    {{ $slot }}

    @if($hint)
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $hint }}</p>
    @endif

    @if($name)
        @error($name)
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    @endif
</div>
