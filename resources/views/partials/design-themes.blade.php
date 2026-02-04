@php
    $userTheme = auth()->check() ? (auth()->user()->settings['design_theme'] ?? '') : '';
    $designTheme = $userTheme ?: ($currentChurch->design_theme ?? 'modern');
    $menuPosition = $currentChurch->menu_position ?? 'left';
@endphp

<style>
/* ========================================
   MENU POSITION STYLES
   ======================================== */
@if($menuPosition === 'right')
    /* Right Sidebar Layout */
    .menu-position-wrapper {
        flex-direction: row-reverse !important;
    }
    .desktop-sidebar {
        left: auto !important;
        right: 0 !important;
        border-right: none !important;
        border-left: 1px solid var(--border-color, rgba(229, 231, 235, 1)) !important;
    }
    .dark .desktop-sidebar {
        border-left-color: rgba(55, 65, 81, 1) !important;
    }
    .main-content-area {
        padding-left: 0 !important;
    }
    @media (max-width: 1023px) {
        .main-content-area {
            padding-right: 0 !important;
        }
    }
@elseif($menuPosition === 'top')
    /* Top Navigation Layout */
    .desktop-sidebar {
        display: none !important;
    }
    .main-content-area {
        padding-left: 0 !important;
    }
    .top-nav-bar {
        display: flex !important;
    }
    /* Hide desktop header since top nav has everything */
    @media (min-width: 1024px) {
        .main-content-area > header.hidden.lg\\:flex {
            display: none !important;
        }
        .mobile-bottom-nav {
            display: none !important;
        }
    }
@elseif($menuPosition === 'bottom')
    /* Bottom Dock Layout */
    .desktop-sidebar {
        display: none !important;
    }
    .main-content-area {
        padding-left: 0 !important;
        padding-bottom: 5rem !important;
    }
    .bottom-dock-nav {
        display: flex !important;
    }
    @media (min-width: 1024px) {
        .mobile-bottom-nav {
            display: none !important;
        }
    }
@endif

/* ========================================
   COLLAPSIBLE SIDEBAR STYLES
   ======================================== */
.desktop-sidebar.lg\:w-16 .sidebar-text { display: none !important; }
.desktop-sidebar.lg\:w-16 .sidebar-icon { margin-right: 0 !important; }
.desktop-sidebar.lg\:w-16 nav a,
.desktop-sidebar.lg\:w-16 nav button { justify-content: center !important; padding-left: 0.5rem !important; padding-right: 0.5rem !important; }
.desktop-sidebar.lg\:w-16 nav a svg,
.desktop-sidebar.lg\:w-16 nav button svg { margin-right: 0 !important; }
.desktop-sidebar.lg\:w-16 .sidebar-divider-text { display: none !important; }
.desktop-sidebar.lg\:w-16 .sidebar-badge { position: absolute !important; top: -4px !important; right: -4px !important; }
.desktop-sidebar.lg\:w-16 nav > div:has(.sidebar-badge) { position: relative !important; }
.desktop-sidebar.lg\:w-16 nav > div a { justify-content: center !important; padding-left: 0.5rem !important; padding-right: 0.5rem !important; }
.desktop-sidebar.lg\:w-16 nav > div a svg { margin-right: 0 !important; }

/* Main content padding adjustment for collapsed sidebar */
.main-content-area { transition: padding-left 0.3s ease, padding-right 0.3s ease; }

/* ========================================
   DESIGN THEME: РАНОК (Morning) - Default
   Fresh, light, peach/coral sunrise tones
   ======================================== */
@if($designTheme === 'modern')
    body {
        background: linear-gradient(135deg, #fef7f0 0%, #fdf2f8 50%, #fef3c7 100%) !important;
    }
    .dark body {
        background: linear-gradient(135deg, #1c1917 0%, #292524 50%, #1c1917 100%) !important;
    }

    /* Warm cards */
    .bg-white, [class*="dark:bg-gray-800"] {
        background: rgba(255, 255, 255, 0.95) !important;
        border: 1px solid rgba(251, 191, 171, 0.3) !important;
        box-shadow: 0 4px 20px rgba(251, 146, 60, 0.08), 0 1px 3px rgba(0,0,0,0.05) !important;
    }
    .dark .bg-white, .dark [class*="dark:bg-gray-800"] {
        background: rgba(41, 37, 36, 0.95) !important;
        border: 1px solid rgba(120, 90, 70, 0.3) !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3) !important;
    }

    /* Sidebar */
    aside {
        background: linear-gradient(180deg, #fff7ed 0%, #ffffff 100%) !important;
        border-right: 1px solid rgba(251, 191, 171, 0.4) !important;
    }
    .dark aside {
        background: linear-gradient(180deg, #292524 0%, #1c1917 100%) !important;
        border-right: 1px solid rgba(120, 90, 70, 0.3) !important;
    }

    /* Accent hover */
    .hover\:bg-gray-100:hover { background: rgba(254, 243, 235, 0.8) !important; }
    .dark .dark\:hover\:bg-gray-700:hover { background: rgba(68, 64, 60, 0.6) !important; }

    /* Rounded warmth */
    .rounded-xl, .rounded-2xl { border-radius: 1rem !important; }

    /* Active nav */
    .bg-primary-50, .bg-primary-100 { background: rgba(254, 215, 170, 0.5) !important; }
    .dark .dark\:bg-primary-900\/50 { background: rgba(120, 90, 70, 0.4) !important; }


/* ========================================
   DESIGN THEME: ВЕЧІР (Evening)
   Deep navy, gold accents, luxurious night
   ======================================== */
@elseif($designTheme === 'glass')
    body {
        background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #172554 100%) !important;
    }
    .dark body {
        background: linear-gradient(135deg, #020617 0%, #0c0a1d 50%, #0a1628 100%) !important;
    }

    /* Light mode - still use dark elegant background */
    .bg-stone-100 { background: transparent !important; }

    /* Elegant cards with gold accent */
    .bg-white, [class*="dark:bg-gray-800"] {
        background: rgba(30, 41, 59, 0.85) !important;
        backdrop-filter: blur(12px) !important;
        -webkit-backdrop-filter: blur(12px) !important;
        border: 1px solid rgba(251, 191, 36, 0.15) !important;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(251, 191, 36, 0.1) !important;
    }
    .dark .bg-white, .dark [class*="dark:bg-gray-800"] {
        background: rgba(15, 23, 42, 0.9) !important;
        border: 1px solid rgba(251, 191, 36, 0.1) !important;
    }

    /* Text colors for dark background */
    .text-gray-900, .text-gray-800, .text-gray-700 { color: #f1f5f9 !important; }
    .text-gray-600, .text-gray-500 { color: #94a3b8 !important; }
    .text-gray-400 { color: #64748b !important; }
    .dark .dark\:text-white { color: #ffffff !important; }
    .dark .dark\:text-gray-100, .dark .dark\:text-gray-200 { color: #e2e8f0 !important; }
    .dark .dark\:text-gray-300, .dark .dark\:text-gray-400 { color: #94a3b8 !important; }

    /* Sidebar - deep with gold trim */
    aside {
        background: linear-gradient(180deg, rgba(30, 41, 59, 0.95) 0%, rgba(15, 23, 42, 0.98) 100%) !important;
        border-right: 1px solid rgba(251, 191, 36, 0.2) !important;
    }
    .dark aside {
        background: linear-gradient(180deg, rgba(15, 23, 42, 0.98) 0%, rgba(2, 6, 23, 0.99) 100%) !important;
    }

    /* Gold accents on buttons */
    .bg-primary-600, .bg-primary-500 {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
        box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3) !important;
    }
    .bg-primary-600:hover, .bg-primary-500:hover {
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%) !important;
    }

    /* Inputs */
    input, select, textarea {
        background: rgba(30, 41, 59, 0.8) !important;
        border: 1px solid rgba(100, 116, 139, 0.3) !important;
        color: #e2e8f0 !important;
    }
    input::placeholder, textarea::placeholder { color: #64748b !important; }
    input:focus, select:focus, textarea:focus {
        border-color: rgba(251, 191, 36, 0.5) !important;
        box-shadow: 0 0 0 2px rgba(251, 191, 36, 0.1) !important;
    }

    /* Hover states */
    .hover\:bg-gray-100:hover, .hover\:bg-gray-50:hover { background: rgba(51, 65, 85, 0.5) !important; }
    .dark .dark\:hover\:bg-gray-700:hover { background: rgba(51, 65, 85, 0.6) !important; }

    /* Active nav - gold tint */
    .bg-primary-50, .bg-primary-100 { background: rgba(251, 191, 36, 0.15) !important; }
    .dark .dark\:bg-primary-900\/50 { background: rgba(251, 191, 36, 0.1) !important; }
    .text-primary-700 { color: #fbbf24 !important; }
    .dark .dark\:text-primary-300, .dark .dark\:text-primary-400 { color: #fcd34d !important; }

    /* Border colors */
    .border-gray-200, .border-gray-300 { border-color: rgba(100, 116, 139, 0.3) !important; }
    .dark .dark\:border-gray-700, .dark .dark\:border-gray-600 { border-color: rgba(71, 85, 105, 0.4) !important; }


/* ========================================
   DESIGN THEME: ПРИРОДА (Nature)
   Forest greens, earthy tones, organic feel
   ======================================== */
@elseif($designTheme === 'corporate')
    body {
        background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 50%, #dcfce7 100%) !important;
    }
    .dark body {
        background: linear-gradient(135deg, #022c22 0%, #052e16 50%, #14532d 100%) !important;
    }

    /* Organic cards */
    .bg-white, [class*="dark:bg-gray-800"] {
        background: rgba(255, 255, 255, 0.92) !important;
        border: 1px solid rgba(34, 197, 94, 0.2) !important;
        box-shadow: 0 4px 20px rgba(34, 197, 94, 0.08), 0 1px 3px rgba(0,0,0,0.04) !important;
    }
    .dark .bg-white, .dark [class*="dark:bg-gray-800"] {
        background: rgba(5, 46, 22, 0.85) !important;
        border: 1px solid rgba(34, 197, 94, 0.15) !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4) !important;
    }

    /* Sidebar - leafy */
    aside {
        background: linear-gradient(180deg, #f0fdf4 0%, #ffffff 100%) !important;
        border-right: 1px solid rgba(34, 197, 94, 0.25) !important;
    }
    .dark aside {
        background: linear-gradient(180deg, rgba(5, 46, 22, 0.95) 0%, rgba(2, 44, 34, 0.98) 100%) !important;
        border-right: 1px solid rgba(34, 197, 94, 0.2) !important;
    }

    /* Natural green buttons */
    .bg-primary-600, .bg-primary-500 {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.25) !important;
    }
    .bg-primary-600:hover, .bg-primary-500:hover {
        background: linear-gradient(135deg, #34d399 0%, #10b981 100%) !important;
    }

    /* Nature accents */
    .bg-primary-50, .bg-primary-100 { background: rgba(209, 250, 229, 0.7) !important; }
    .dark .dark\:bg-primary-900\/50 { background: rgba(6, 78, 59, 0.5) !important; }
    .text-primary-700 { color: #047857 !important; }
    .text-primary-600 { color: #059669 !important; }
    .dark .dark\:text-primary-300, .dark .dark\:text-primary-400 { color: #6ee7b7 !important; }

    /* Soft hover */
    .hover\:bg-gray-100:hover { background: rgba(209, 250, 229, 0.5) !important; }
    .dark .dark\:hover\:bg-gray-700:hover { background: rgba(6, 78, 59, 0.4) !important; }

    /* Rounded like leaves */
    .rounded-xl { border-radius: 1rem !important; }
    .rounded-2xl { border-radius: 1.25rem !important; }

    /* Border colors */
    .border-gray-200, .border-gray-300 { border-color: rgba(34, 197, 94, 0.2) !important; }
    .dark .dark\:border-gray-700, .dark .dark\:border-gray-600 { border-color: rgba(34, 197, 94, 0.15) !important; }


/* ========================================
   DESIGN THEME: ОКЕАН (Ocean)
   Deep blue, calming waves, sea vibes
   ======================================== */
@elseif($designTheme === 'ocean')
    body {
        background: linear-gradient(135deg, #ecfeff 0%, #e0f2fe 50%, #c7d2fe 100%) !important;
    }
    .dark body {
        background: linear-gradient(135deg, #083344 0%, #0c4a6e 50%, #1e3a5f 100%) !important;
    }

    /* Ocean cards */
    .bg-white, [class*="dark:bg-gray-800"] {
        background: rgba(255, 255, 255, 0.92) !important;
        border: 1px solid rgba(6, 182, 212, 0.2) !important;
        box-shadow: 0 4px 20px rgba(6, 182, 212, 0.1), 0 1px 3px rgba(0,0,0,0.04) !important;
    }
    .dark .bg-white, .dark [class*="dark:bg-gray-800"] {
        background: rgba(8, 51, 68, 0.9) !important;
        border: 1px solid rgba(6, 182, 212, 0.2) !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4) !important;
    }

    /* Sidebar - wave gradient */
    aside {
        background: linear-gradient(180deg, #ecfeff 0%, #ffffff 100%) !important;
        border-right: 1px solid rgba(6, 182, 212, 0.25) !important;
    }
    .dark aside {
        background: linear-gradient(180deg, rgba(8, 51, 68, 0.95) 0%, rgba(12, 74, 110, 0.98) 100%) !important;
        border-right: 1px solid rgba(6, 182, 212, 0.2) !important;
    }

    /* Ocean blue buttons */
    .bg-primary-600, .bg-primary-500 {
        background: linear-gradient(135deg, #06b6d4 0%, #0284c7 100%) !important;
        box-shadow: 0 4px 15px rgba(6, 182, 212, 0.3) !important;
    }
    .bg-primary-600:hover, .bg-primary-500:hover {
        background: linear-gradient(135deg, #22d3ee 0%, #06b6d4 100%) !important;
    }

    /* Ocean accents */
    .bg-primary-50, .bg-primary-100 { background: rgba(207, 250, 254, 0.7) !important; }
    .dark .dark\:bg-primary-900\/50 { background: rgba(8, 51, 68, 0.6) !important; }
    .text-primary-700 { color: #0e7490 !important; }
    .text-primary-600 { color: #0891b2 !important; }
    .dark .dark\:text-primary-300, .dark .dark\:text-primary-400 { color: #67e8f9 !important; }

    /* Soft hover */
    .hover\:bg-gray-100:hover { background: rgba(207, 250, 254, 0.5) !important; }
    .dark .dark\:hover\:bg-gray-700:hover { background: rgba(14, 116, 144, 0.3) !important; }

    /* Border colors */
    .border-gray-200, .border-gray-300 { border-color: rgba(6, 182, 212, 0.2) !important; }
    .dark .dark\:border-gray-700, .dark .dark\:border-gray-600 { border-color: rgba(6, 182, 212, 0.2) !important; }


/* ========================================
   DESIGN THEME: ЗАХІД (Sunset)
   Warm purple/pink sunset, romantic vibes
   ======================================== */
@elseif($designTheme === 'sunset')
    body {
        background: linear-gradient(135deg, #fce7f3 0%, #f3e8ff 50%, #e0e7ff 100%) !important;
    }
    .dark body {
        background: linear-gradient(135deg, #4a1d4e 0%, #3b0d60 50%, #312e81 100%) !important;
    }

    /* Sunset cards */
    .bg-white, [class*="dark:bg-gray-800"] {
        background: rgba(255, 255, 255, 0.92) !important;
        border: 1px solid rgba(168, 85, 247, 0.2) !important;
        box-shadow: 0 4px 20px rgba(168, 85, 247, 0.1), 0 1px 3px rgba(0,0,0,0.04) !important;
    }
    .dark .bg-white, .dark [class*="dark:bg-gray-800"] {
        background: rgba(59, 13, 96, 0.85) !important;
        border: 1px solid rgba(168, 85, 247, 0.25) !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4) !important;
    }

    /* Sidebar - sunset gradient */
    aside {
        background: linear-gradient(180deg, #fdf2f8 0%, #ffffff 100%) !important;
        border-right: 1px solid rgba(168, 85, 247, 0.2) !important;
    }
    .dark aside {
        background: linear-gradient(180deg, rgba(59, 13, 96, 0.95) 0%, rgba(74, 29, 78, 0.98) 100%) !important;
        border-right: 1px solid rgba(168, 85, 247, 0.25) !important;
    }

    /* Sunset purple buttons */
    .bg-primary-600, .bg-primary-500 {
        background: linear-gradient(135deg, #ec4899 0%, #a855f7 100%) !important;
        box-shadow: 0 4px 15px rgba(168, 85, 247, 0.3) !important;
    }
    .bg-primary-600:hover, .bg-primary-500:hover {
        background: linear-gradient(135deg, #f472b6 0%, #c084fc 100%) !important;
    }

    /* Sunset accents */
    .bg-primary-50, .bg-primary-100 { background: rgba(250, 232, 255, 0.7) !important; }
    .dark .dark\:bg-primary-900\/50 { background: rgba(88, 28, 135, 0.5) !important; }
    .text-primary-700 { color: #7e22ce !important; }
    .text-primary-600 { color: #9333ea !important; }
    .dark .dark\:text-primary-300, .dark .dark\:text-primary-400 { color: #d8b4fe !important; }

    /* Soft hover */
    .hover\:bg-gray-100:hover { background: rgba(250, 232, 255, 0.5) !important; }
    .dark .dark\:hover\:bg-gray-700:hover { background: rgba(126, 34, 206, 0.3) !important; }

    /* Border colors */
    .border-gray-200, .border-gray-300 { border-color: rgba(168, 85, 247, 0.2) !important; }
    .dark .dark\:border-gray-700, .dark .dark\:border-gray-600 { border-color: rgba(168, 85, 247, 0.2) !important; }

@endif
</style>
