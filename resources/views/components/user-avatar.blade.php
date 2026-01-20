@props(['size' => 'md'])

@php
    $sizeClasses = match($size) {
        'sm' => 'w-8 h-8 text-sm',
        'md' => 'w-9 h-9 text-sm',
        'lg' => 'w-10 h-10 text-sm',
        default => 'w-9 h-9 text-sm',
    };
@endphp

@if(auth()->user()->person?->photo)
    <img src="{{ Storage::url(auth()->user()->person->photo) }}" alt="" class="{{ $sizeClasses }} rounded-full object-cover">
@else
    <div class="{{ $sizeClasses }} rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center">
        <span class="font-medium text-primary-600 dark:text-primary-300">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
    </div>
@endif
