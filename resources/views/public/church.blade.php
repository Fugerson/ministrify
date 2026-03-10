@extends('public.layout')

@section('title', $church->name)

@section('content')
@php
    $isAdmin = auth()->check() && auth()->user()->church_id === ($church->id ?? null) && auth()->user()->canEdit('website');
    $sectionLabels = [
        'hero' => __('app.sb_section_hero'),
        'service_times' => __('app.sb_section_service_times'),
        'about' => __('app.sb_section_about'),
        'pastor_message' => __('app.sb_section_pastor_message'),
        'leadership' => __('app.sb_section_leadership'),
        'events' => __('app.sb_section_events'),
        'sermons' => __('app.sb_section_sermons'),
        'ministries' => __('app.sb_section_ministries'),
        'groups' => __('app.sb_section_groups'),
        'gallery' => __('app.sb_section_gallery'),
        'testimonials' => __('app.sb_section_testimonials'),
        'blog' => __('app.sb_section_blog'),
        'faq' => __('app.sb_section_faq'),
        'donations' => __('app.sb_section_donations'),
        'contact' => __('app.sb_section_contact'),
    ];
    $sectionSettings = ($church->public_site_settings ?? [])['section_settings'] ?? [];
@endphp

@php $sectionsContainerId = $isAdmin ? 'id="sections-container"' : ''; @endphp
<div {!! $sectionsContainerId !!} class="flex flex-wrap items-stretch">

@foreach($enabledSections as $section)
    @php
        $secLayout = $section['layout'] ?? 'full';
        $widthClass = $secLayout === 'half' ? 'w-full md:w-1/2' : 'w-full';
    @endphp

    @if($isAdmin)
    @php $secBg = $sectionSettings[$section['id']]['bg_color'] ?? ''; @endphp
    <div class="section-wrapper {{ $widthClass }}" data-section-id="{{ $section['id'] }}" data-layout="{{ $secLayout }}">
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
                <button onclick="window.__sectionMoveUp(this)" class="section-toolbar-btn" title="{{ __('app.sb_move_up') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 15l7-7 7 7"/></svg>
                </button>
                <button onclick="window.__sectionMoveDown(this)" class="section-toolbar-btn" title="{{ __('app.sb_move_down') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 9l-7 7-7-7"/></svg>
                </button>
                <span class="section-toolbar-sep"></span>
                <button onclick="window.__sectionToggleLayout(this)" class="section-toolbar-btn" title="{{ $secLayout === 'half' ? __('app.sb_full_width') : __('app.sb_half_width') }}">
                    @if($secLayout === 'half')
                    {{-- Currently half → show "expand to full" icon --}}
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/></svg>
                    @else
                    {{-- Currently full → show "split to half" icon --}}
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="12" y1="3" x2="12" y2="21"/></svg>
                    @endif
                </button>
                <label class="section-toolbar-btn section-color-label" title="{{ __('app.sb_section_bg_color') }}">
                    <input type="color" value="{{ $secBg ?: '#ffffff' }}" onchange="window.__sectionChangeBg(this)" class="section-color-input">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                </label>
                @if($secBg)
                <button onclick="window.__sectionResetBg(this)" class="section-toolbar-btn" title="{{ __('app.sb_reset_color') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                @endif
                <button onclick="window.__sectionEdit('{{ $section['id'] }}')" class="section-toolbar-btn" title="{{ __('app.sb_edit_section') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </button>
            </div>
        </div>
    @else
    <div class="{{ $widthClass }}">
    @endif

    @include('public.sections.' . Str::replace('_', '-', $section['id']), ['church' => $church])

    </div>
@endforeach

</div>

@include('public.sections.cta', ['church' => $church])

@if($isAdmin)
<script>
// Toggle section layout between full and half width
window.__sectionToggleLayout = function(btn) {
    const wrapper = btn.closest('.section-wrapper');
    if (!wrapper) return;
    const current = wrapper.dataset.layout || 'full';
    const next = current === 'full' ? 'half' : 'full';
    wrapper.dataset.layout = next;

    // Update width class
    wrapper.classList.remove('w-full', 'md:w-1/2');
    if (next === 'half') {
        wrapper.classList.add('w-full', 'md:w-1/2');
    } else {
        wrapper.classList.add('w-full');
    }

    // Update the icon inside the button
    if (next === 'half') {
        btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/></svg>';
        btn.title = @js(__('app.sb_full_width'));
    } else {
        btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="12" y1="3" x2="12" y2="21"/></svg>';
        btn.title = @js(__('app.sb_half_width'));
    }

    // Show toast
    const sidebar = window.__adminSidebar;
    if (sidebar) {
        sidebar.showPageToast(next === 'half' ? @js(__('app.sb_half_toast')) : @js(__('app.sb_full_toast')));
    }
};

// Apply saved section background colors + detect empty sections
document.addEventListener('DOMContentLoaded', function() {
    const bgColors = @json(collect($sectionSettings)->mapWithKeys(fn($s, $id) => [$id => $s['bg_color'] ?? null])->filter());
    Object.entries(bgColors).forEach(function([id, color]) {
        const wrapper = document.querySelector('[data-section-id="' + id + '"]');
        if (wrapper) {
            const section = wrapper.querySelector('section');
            if (section) section.style.backgroundColor = color;
        }
    });

    // Mark empty sections + add placeholder for admin
    const sectionHints = {
        hero: @js(__('app.sb_hint_hero')),
        service_times: @js(__('app.sb_hint_service_times')),
        about: @js(__('app.sb_hint_about')),
        pastor_message: @js(__('app.sb_hint_pastor_message')),
        leadership: @js(__('app.sb_hint_leadership')),
        events: @js(__('app.sb_hint_events')),
        sermons: @js(__('app.sb_hint_sermons')),
        ministries: @js(__('app.sb_hint_ministries')),
        groups: @js(__('app.sb_hint_groups')),
        gallery: @js(__('app.sb_hint_gallery')),
        testimonials: @js(__('app.sb_hint_testimonials')),
        blog: @js(__('app.sb_hint_blog')),
        faq: @js(__('app.sb_hint_faq')),
        donations: @js(__('app.sb_hint_donations')),
        contact: @js(__('app.sb_hint_contact'))
    };
    document.querySelectorAll('.section-wrapper').forEach(function(wrapper) {
        const section = wrapper.querySelector('section');
        const contentHeight = section ? section.offsetHeight : 0;
        if (contentHeight < 10) {
            wrapper.classList.add('section-empty');
            const sectionId = wrapper.dataset.sectionId;
            const hint = sectionHints[sectionId] || @js(__('app.sb_hint_default'));
            const placeholder = document.createElement('div');
            placeholder.className = 'section-empty-placeholder';
            placeholder.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>'
                + '<span>' + hint + '</span>';
            wrapper.appendChild(placeholder);
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
    border: 2px dashed transparent;
    pointer-events: none;
    z-index: 50;
    transition: border-color 0.2s, background 0.2s;
}
/* In edit mode: min height for empty sections + visible borders */
#sections-container.edit-active .section-wrapper {
    min-height: 56px;
}
#sections-container.edit-active .section-wrapper::before {
    border-color: rgba(59, 130, 246, 0.25);
}
#sections-container.edit-active .section-wrapper:hover::before {
    border-color: rgba(59, 130, 246, 0.6);
}
/* Empty sections (no visible content): always show toolbar + placeholder */
#sections-container.edit-active .section-wrapper.section-empty .section-admin-toolbar {
    opacity: 1;
    pointer-events: auto;
    position: relative;
    top: auto;
    left: auto;
    transform: none;
    display: flex;
    justify-content: center;
    padding: 12px 0 4px;
}
.section-empty-placeholder {
    display: none;
}
#sections-container.edit-active .section-empty-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 8px 16px 16px;
    color: #9ca3af;
    font-size: 13px;
}
#sections-container.edit-active .section-empty-placeholder svg {
    width: 18px;
    height: 18px;
    flex-shrink: 0;
}
.section-wrapper.sortable-chosen::before {
    border-color: rgba(59, 130, 246, 0.8) !important;
    border-style: solid !important;
    background: rgba(59, 130, 246, 0.05);
}
.section-wrapper.sortable-ghost {
    opacity: 0.4;
}

/* Toolbar hidden by default, shown on hover only in edit mode */
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
#sections-container.edit-active .section-wrapper:hover .section-admin-toolbar,
#sections-container.edit-active .section-admin-toolbar:focus-within {
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
