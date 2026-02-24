@extends('public.layout')

@section('title', $church->name)

@section('content')
@php
    $isAdmin = auth()->check() && auth()->user()->church_id === ($church->id ?? null) && auth()->user()->canEdit('website');
    $sectionLabels = [
        'hero' => 'Hero секція',
        'service_times' => 'Розклад служінь',
        'about' => 'Про нас',
        'pastor_message' => 'Слово пастора',
        'leadership' => 'Команда лідерів',
        'events' => 'Події',
        'sermons' => 'Проповіді',
        'ministries' => 'Служіння',
        'groups' => 'Малі групи',
        'gallery' => 'Галерея',
        'testimonials' => 'Свідчення',
        'blog' => 'Блог',
        'faq' => 'FAQ',
        'donations' => 'Пожертви',
        'contact' => 'Контакти',
    ];
    $sectionSettings = ($church->public_site_settings ?? [])['section_settings'] ?? [];
@endphp

@if($isAdmin)<div id="sections-container">@endif

@foreach($enabledSections as $section)
    @if($isAdmin)
    @php $secBg = $sectionSettings[$section['id']]['bg_color'] ?? ''; @endphp
    <div class="section-wrapper" data-section-id="{{ $section['id'] }}">
        {{-- Admin toolbar --}}
        <div class="section-admin-toolbar">
            <div class="section-toolbar-pill">
                <svg class="section-drag-handle" viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
                    <circle cx="9" cy="6" r="1.5"/><circle cx="15" cy="6" r="1.5"/>
                    <circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/>
                    <circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/>
                </svg>
                <span class="section-toolbar-label">{{ $sectionLabels[$section['id']] ?? $section['id'] }}</span>
                <span class="section-toolbar-sep"></span>
                <button onclick="window.__sectionMoveUp(this)" class="section-toolbar-btn" title="Вгору">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 15l7-7 7 7"/></svg>
                </button>
                <button onclick="window.__sectionMoveDown(this)" class="section-toolbar-btn" title="Вниз">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 9l-7 7-7-7"/></svg>
                </button>
                <span class="section-toolbar-sep"></span>
                <label class="section-toolbar-btn section-color-label" title="Колір фону секції">
                    <input type="color" value="{{ $secBg ?: '#ffffff' }}" onchange="window.__sectionChangeBg(this)" class="section-color-input">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                </label>
                @if($secBg)
                <button onclick="window.__sectionResetBg(this)" class="section-toolbar-btn" title="Скинути колір">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                @endif
                <button onclick="window.__sectionEdit('{{ $section['id'] }}')" class="section-toolbar-btn" title="Редагувати">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </button>
            </div>
        </div>
    @endif

    @include('public.sections.' . Str::replace('_', '-', $section['id']), ['church' => $church])

    @if($isAdmin)
    </div>
    @endif
@endforeach

@if($isAdmin)</div>@endif

@include('public.sections.cta', ['church' => $church])

@if($isAdmin)
<script>
// Apply saved section background colors on load
document.addEventListener('DOMContentLoaded', function() {
    const bgColors = @json(collect($sectionSettings)->mapWithKeys(fn($s, $id) => [$id => $s['bg_color'] ?? null])->filter());
    Object.entries(bgColors).forEach(function([id, color]) {
        const wrapper = document.querySelector('[data-section-id="' + id + '"]');
        if (wrapper) {
            const section = wrapper.querySelector('section');
            if (section) section.style.backgroundColor = color;
        }
    });
});
</script>
<style>
.section-wrapper {
    position: relative;
}
.section-wrapper::before {
    content: '';
    position: absolute;
    inset: 0;
    border: 2px solid transparent;
    pointer-events: none;
    z-index: 50;
    transition: border-color 0.2s;
}
.section-wrapper:hover::before {
    border-color: rgba(59, 130, 246, 0.5);
    border-style: dashed;
}
.section-wrapper.sortable-chosen::before {
    border-color: rgba(59, 130, 246, 0.8);
    border-style: solid;
    background: rgba(59, 130, 246, 0.05);
}
.section-wrapper.sortable-ghost {
    opacity: 0.4;
}

.section-admin-toolbar {
    position: absolute;
    top: 8px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 51;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s;
}
.section-wrapper:hover .section-admin-toolbar,
.section-admin-toolbar:focus-within {
    opacity: 1;
    pointer-events: auto;
}
.section-toolbar-pill {
    display: flex;
    align-items: center;
    gap: 4px;
    background: rgba(17, 24, 39, 0.88);
    backdrop-filter: blur(8px);
    color: white;
    border-radius: 9999px;
    padding: 6px 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    font-size: 13px;
    white-space: nowrap;
}
.section-drag-handle {
    width: 18px;
    height: 18px;
    cursor: grab;
    color: rgba(255,255,255,0.6);
    transition: color 0.15s;
    flex-shrink: 0;
}
.section-drag-handle:hover { color: white; }
.section-drag-handle:active { cursor: grabbing; }
.section-toolbar-label {
    font-weight: 500;
    padding: 0 4px;
}
.section-toolbar-sep {
    width: 1px;
    height: 16px;
    background: rgba(255,255,255,0.2);
    flex-shrink: 0;
}
.section-toolbar-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    transition: background 0.15s;
    cursor: pointer;
    position: relative;
    border: none;
    background: none;
    color: white;
    padding: 0;
}
.section-toolbar-btn:hover {
    background: rgba(255,255,255,0.2);
}
.section-toolbar-btn svg {
    width: 16px;
    height: 16px;
}
.section-color-label {
    cursor: pointer;
}
.section-color-input {
    position: absolute;
    width: 0;
    height: 0;
    opacity: 0;
    border: 0;
    padding: 0;
    pointer-events: none;
}
</style>
@endif

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
@endsection
