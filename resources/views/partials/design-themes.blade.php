@php
    // User theme: null = not set (use church default), '' = explicitly chose Класична
    $userTheme = auth()->check() ? (auth()->user()->settings['design_theme'] ?? null) : null;
    $designTheme = $userTheme !== null ? $userTheme : ($currentChurch->design_theme ?? 'classic');
    $userMenuPosition = auth()->check() ? (auth()->user()->settings['menu_position'] ?? '') : '';
    $menuPosition = $userMenuPosition ?: ($currentChurch->menu_position ?? 'left');
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
    /* Top Navigation Layout - desktop only */
    @media (min-width: 1024px) {
        .desktop-sidebar {
            display: none !important;
        }
        .main-content-area {
            padding-left: 0 !important;
        }
        .top-nav-bar {
            display: flex !important;
        }
        .main-content-area > header.hidden.lg\:flex {
            display: none !important;
        }
    }
@elseif($menuPosition === 'bottom')
    /* Bottom Dock Layout - desktop only */
    @media (min-width: 1024px) {
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
   SHARED: Button consistency across themes
   - All themes override .bg-primary-600/.bg-primary-500
   - These shared rules ensure hover, focus, disabled states
   ======================================== */
.bg-primary-600:focus, .bg-primary-500:focus {
    outline: 2px solid transparent;
    outline-offset: 2px;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.4);
}
.bg-primary-600:disabled, .bg-primary-500:disabled,
.bg-primary-600.opacity-50, .bg-primary-500.opacity-50 {
    opacity: 0.5 !important;
    cursor: not-allowed !important;
    filter: grayscale(30%) !important;
    box-shadow: none !important;
}
.bg-primary-600:active, .bg-primary-500:active {
    transform: scale(0.98);
}


/* ========================================
   DESIGN THEME: КЛАСИЧНА (Classic) — Default
   Clean, professional look with blue accent
   Glass effects + gradient blobs in dark mode
   ======================================== */
@if($designTheme === '' || $designTheme === 'classic' || $designTheme === null)
    /* --- Light mode --- */
    body {
        background-color: #f8fafc;
        background-image:
            radial-gradient(ellipse at 15% 5%, rgba(59, 130, 246, 0.08) 0%, transparent 45%),
            radial-gradient(ellipse at 85% 80%, rgba(99, 102, 241, 0.06) 0%, transparent 45%);
        background-attachment: fixed;
    }

    /* Light cards — clean white with subtle blue tint */
    main .bg-white,
    main [class*="dark:bg-gray-800"] {
        background: rgba(255, 255, 255, 0.92) !important;
        border: 1px solid rgba(59, 130, 246, 0.1) !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05), 0 1px 2px rgba(0, 0, 0, 0.03) !important;
    }

    /* Light sidebar */
    aside {
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%) !important;
        border-right: 1px solid rgba(59, 130, 246, 0.12) !important;
    }

    /* Blue accent buttons */
    .bg-primary-600, .bg-primary-500 {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important;
        box-shadow: 0 4px 14px rgba(59, 130, 246, 0.25) !important;
    }
    .bg-primary-600:hover, .bg-primary-500:hover {
        background: linear-gradient(135deg, #60a5fa 0%, #3b82f6 100%) !important;
        box-shadow: 0 6px 20px rgba(59, 130, 246, 0.35) !important;
    }
    .bg-primary-600:focus, .bg-primary-500:focus {
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.4) !important;
    }

    /* Hover states */
    .hover\:bg-gray-100:hover { background: rgba(239, 246, 255, 0.7) !important; }

    /* Active nav */
    .bg-primary-50, .bg-primary-100 { background: rgba(219, 234, 254, 0.6) !important; }

    /* --- Dark mode: glass + gradient blobs --- */
    .dark body {
        background-color: #020617;
        background-image:
            radial-gradient(ellipse at 15% 5%, rgba(30, 58, 138, 0.35) 0%, transparent 45%),
            radial-gradient(ellipse at 85% 80%, rgba(59, 130, 246, 0.15) 0%, transparent 45%),
            radial-gradient(ellipse at 50% 50%, rgba(99, 102, 241, 0.06) 0%, transparent 60%);
        background-attachment: fixed;
    }

    /* Dark cards — glass-like effect WITHOUT backdrop-filter (avoids stacking context issues with dropdowns) */
    .dark main .bg-white,
    .dark main [class*="dark:bg-gray-800"] {
        background: rgba(15, 23, 42, 0.85) !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.04) !important;
    }

    /* Dark sidebar — glass */
    .dark aside {
        background: linear-gradient(180deg, rgba(15, 23, 42, 0.95) 0%, rgba(2, 6, 23, 0.98) 100%) !important;
        backdrop-filter: blur(20px) !important;
        -webkit-backdrop-filter: blur(20px) !important;
        border-right: 1px solid rgba(255, 255, 255, 0.06) !important;
    }

    /* Dark hover */
    .dark .dark\:hover\:bg-gray-700:hover { background: rgba(30, 41, 59, 0.6) !important; }

    /* Dark active nav */
    .dark .dark\:bg-primary-900\/50 { background: rgba(30, 58, 138, 0.3) !important; }


/* ========================================
   DESIGN THEME: РАНОК (Morning)
   Warm peach/coral sunrise — distinctly warm & cozy
   ======================================== */
@elseif($designTheme === 'modern')
    body {
        background: linear-gradient(135deg, #fef3e2 0%, #fce7d6 50%, #fdf2f8 100%) !important;
        background-attachment: fixed !important;
    }
    .dark body {
        background-color: #18120d !important;
        background-image:
            radial-gradient(ellipse at 20% 10%, rgba(251, 146, 60, 0.08) 0%, transparent 50%),
            radial-gradient(ellipse at 80% 80%, rgba(234, 88, 12, 0.06) 0%, transparent 50%),
            radial-gradient(ellipse at 50% 50%, rgba(180, 83, 9, 0.04) 0%, transparent 60%) !important;
        background-attachment: fixed !important;
    }

    /* Warm creamy cards with visible peach border */
    main .bg-white,
    main [class*="dark:bg-gray-800"] {
        background: rgba(255, 252, 247, 0.95) !important;
        border: 1px solid rgba(251, 146, 60, 0.2) !important;
        box-shadow: 0 2px 15px rgba(251, 146, 60, 0.08), 0 1px 3px rgba(0,0,0,0.04) !important;
    }
    .dark main .bg-white,
    .dark main [class*="dark:bg-gray-800"] {
        background: rgba(36, 24, 16, 0.92) !important;
        border: 1px solid rgba(251, 146, 60, 0.15) !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4), 0 0 12px rgba(251, 146, 60, 0.04), inset 0 1px 0 rgba(251, 146, 60, 0.06) !important;
    }

    /* Sidebar — warm cream gradient */
    aside {
        background: linear-gradient(180deg, #fff8f0 0%, #fef3e2 100%) !important;
        border-right: 1px solid rgba(251, 146, 60, 0.2) !important;
    }
    .dark aside {
        background: linear-gradient(180deg, rgba(36, 24, 16, 0.98) 0%, rgba(24, 18, 13, 0.99) 100%) !important;
        border-right: 1px solid rgba(251, 146, 60, 0.15) !important;
    }

    /* Orange accent buttons */
    .bg-primary-600, .bg-primary-500 {
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%) !important;
        box-shadow: 0 4px 15px rgba(249, 115, 22, 0.3) !important;
    }
    .bg-primary-600:hover, .bg-primary-500:hover {
        background: linear-gradient(135deg, #fb923c 0%, #f97316 100%) !important;
        box-shadow: 0 6px 20px rgba(249, 115, 22, 0.4) !important;
    }
    .bg-primary-600:focus, .bg-primary-500:focus {
        box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.4) !important;
    }

    /* Warm orange text accents */
    .text-primary-700 { color: #c2410c !important; }
    .text-primary-600 { color: #ea580c !important; }
    .dark .dark\:text-primary-300, .dark .dark\:text-primary-400 { color: #fdba74 !important; }

    /* Warm hover */
    .hover\:bg-gray-100:hover { background: rgba(254, 235, 210, 0.7) !important; }
    .dark .dark\:hover\:bg-gray-700:hover { background: rgba(68, 45, 25, 0.6) !important; }

    /* Dark mode subtitle text contrast — warm readable tones */
    .dark main .text-gray-500 { color: #d6b99a !important; }
    .dark main .text-gray-400 { color: #e8d5c0 !important; }

    /* Active nav — warm peach tint */
    .bg-primary-50, .bg-primary-100 { background: rgba(254, 215, 170, 0.5) !important; }
    .dark .dark\:bg-primary-900\/50 { background: rgba(120, 90, 70, 0.35) !important; }


/* ========================================
   DESIGN THEME: ВЕЧІР (Evening)
   Deep navy, gold accents, luxurious night feel.

   NOTE: Both modes use dark backgrounds, but they are NOT identical:
   - Light mode: lighter navy (#1e293b-based), brighter gold accents, lighter cards
   - Dark mode: deepest navy (#020617-based), muted amber accents, darker cards
   The light-mode text color overrides below are INTENTIONAL (no .dark prefix)
   to ensure readability. They are scoped to `main` to avoid affecting
   modals, dropdowns, and other portal elements.
   ======================================== */
@elseif($designTheme === 'glass')
    /* Light mode: lighter navy — noticeably different from dark */
    body {
        background: linear-gradient(135deg, #1e293b 0%, #2e2760 50%, #1e3a5f 100%) !important;
    }
    /* Dark mode: deepest navy */
    .dark body {
        background: linear-gradient(135deg, #020617 0%, #0c0a1d 50%, #0a1628 100%) !important;
    }

    /* Light mode - still use dark elegant background, hide default light bg */
    .bg-stone-100 { background: transparent !important; }

    /* Light mode cards — slightly lighter with brighter gold (no backdrop-filter to avoid stacking context) */
    main .bg-white,
    main [class*="dark:bg-gray-800"] {
        background: rgba(40, 52, 72, 0.92) !important;
        border: 1px solid rgba(251, 191, 36, 0.2) !important;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25), inset 0 1px 0 rgba(251, 191, 36, 0.12) !important;
    }
    /* Dark mode cards — deeper, more muted gold */
    .dark main .bg-white,
    .dark main [class*="dark:bg-gray-800"] {
        background: rgba(15, 23, 42, 0.9) !important;
        border: 1px solid rgba(251, 191, 36, 0.08) !important;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4), inset 0 1px 0 rgba(251, 191, 36, 0.04) !important;
    }

    /*
     * Text color overrides — scoped to `main` so they only affect
     * page content, NOT modals/dropdowns/portals which render outside main.
     * No `.dark` prefix because this theme is always dark.
     */
    main .text-gray-900,
    main .text-gray-800,
    main .text-gray-700 { color: #f1f5f9 !important; }
    main .text-gray-600,
    main .text-gray-500 { color: #94a3b8 !important; }
    main .text-gray-400 { color: #64748b !important; }

    .dark .dark\:text-white { color: #ffffff !important; }
    .dark .dark\:text-gray-100, .dark .dark\:text-gray-200 { color: #e2e8f0 !important; }
    .dark .dark\:text-gray-300, .dark .dark\:text-gray-400 { color: #94a3b8 !important; }

    /* Sidebar text — also needs light text on dark bg */
    aside .text-gray-900,
    aside .text-gray-800,
    aside .text-gray-700 { color: #e2e8f0 !important; }
    aside .text-gray-600,
    aside .text-gray-500 { color: #94a3b8 !important; }

    /* Header — light mode: slightly lighter bg */
    header .text-gray-900,
    header .text-gray-800,
    header .text-gray-700 { color: #f1f5f9 !important; }
    header .text-gray-600,
    header .text-gray-500 { color: #94a3b8 !important; }
    header {
        background: linear-gradient(180deg, rgba(40, 52, 72, 0.95) 0%, rgba(30, 41, 59, 0.98) 100%) !important;
        border-bottom-color: rgba(251, 191, 36, 0.18) !important;
    }
    .dark header {
        background: linear-gradient(180deg, rgba(15, 23, 42, 0.95) 0%, rgba(8, 15, 30, 0.98) 100%) !important;
        border-bottom-color: rgba(251, 191, 36, 0.1) !important;
    }

    /* Override bg-white elements within main (toolbar buttons etc.) */
    main .bg-white { color: #e2e8f0 !important; }

    /* Sidebar — light mode: slightly lighter navy with brighter gold trim */
    aside {
        background: linear-gradient(180deg, rgba(40, 52, 72, 0.95) 0%, rgba(30, 41, 59, 0.98) 100%) !important;
        border-right: 1px solid rgba(251, 191, 36, 0.25) !important;
    }
    /* Sidebar — dark mode: deepest navy, muted gold */
    .dark aside {
        background: linear-gradient(180deg, rgba(15, 23, 42, 0.98) 0%, rgba(2, 6, 23, 0.99) 100%) !important;
        border-right: 1px solid rgba(251, 191, 36, 0.12) !important;
    }

    /* Gold accent buttons — light mode: brighter gold */
    .bg-primary-600, .bg-primary-500 {
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%) !important;
        box-shadow: 0 4px 15px rgba(251, 191, 36, 0.35) !important;
        color: #1e293b !important;
    }
    .bg-primary-600:hover, .bg-primary-500:hover {
        background: linear-gradient(135deg, #fcd34d 0%, #fbbf24 100%) !important;
        box-shadow: 0 6px 20px rgba(251, 191, 36, 0.45) !important;
    }
    /* Gold accent buttons — dark mode: more muted amber */
    .dark .bg-primary-600, .dark .bg-primary-500 {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
        box-shadow: 0 4px 15px rgba(245, 158, 11, 0.25) !important;
        color: #ffffff !important;
    }
    .dark .bg-primary-600:hover, .dark .bg-primary-500:hover {
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%) !important;
        box-shadow: 0 6px 20px rgba(245, 158, 11, 0.35) !important;
    }
    .bg-primary-600:focus, .bg-primary-500:focus {
        box-shadow: 0 0 0 3px rgba(251, 191, 36, 0.4) !important;
    }

    /* Inputs — light mode: slightly lighter bg */
    main input, main select, main textarea {
        background: rgba(40, 52, 72, 0.8) !important;
        border: 1px solid rgba(100, 116, 139, 0.35) !important;
        color: #e2e8f0 !important;
    }
    .dark main input, .dark main select, .dark main textarea {
        background: rgba(15, 23, 42, 0.8) !important;
        border: 1px solid rgba(100, 116, 139, 0.25) !important;
    }
    main input::placeholder, main textarea::placeholder { color: #64748b !important; }
    main input:focus, main select:focus, main textarea:focus {
        border-color: rgba(251, 191, 36, 0.5) !important;
        box-shadow: 0 0 0 2px rgba(251, 191, 36, 0.15) !important;
    }

    /* Hover states */
    .hover\:bg-gray-100:hover, .hover\:bg-gray-50:hover { background: rgba(51, 65, 85, 0.5) !important; }
    .dark .dark\:hover\:bg-gray-700:hover { background: rgba(51, 65, 85, 0.6) !important; }

    /* Active nav — light mode: brighter gold tint */
    .bg-primary-50, .bg-primary-100 { background: rgba(251, 191, 36, 0.18) !important; }
    .dark .dark\:bg-primary-900\/50 { background: rgba(251, 191, 36, 0.08) !important; }
    .text-primary-700 { color: #fcd34d !important; }
    .dark .text-primary-700 { color: #fbbf24 !important; }
    .dark .dark\:text-primary-300, .dark .dark\:text-primary-400 { color: #fcd34d !important; }

    /* Border colors */
    main .border-gray-200, main .border-gray-300 { border-color: rgba(100, 116, 139, 0.3) !important; }
    .dark .dark\:border-gray-700, .dark .dark\:border-gray-600 { border-color: rgba(71, 85, 105, 0.4) !important; }

    /* Light gray backgrounds in main — light mode: slightly lighter slate */
    main .bg-gray-50,
    main .bg-gray-100,
    main .bg-gray-200 {
        background: rgba(40, 52, 72, 0.65) !important;
        color: #e2e8f0 !important;
    }
    .dark main .bg-gray-50,
    .dark main .bg-gray-100,
    .dark main .bg-gray-200 {
        background: rgba(15, 23, 42, 0.7) !important;
        color: #e2e8f0 !important;
    }

    /* Amber/orange status blocks — "Очікує підтвердження" heading visibility */
    main .bg-amber-100,
    main .bg-yellow-100,
    main .bg-orange-100,
    main [class*="bg-amber-"],
    main [class*="bg-yellow-"] {
        color: #1e293b !important;
    }
    main .bg-amber-100 h2, main .bg-amber-100 h3, main .bg-amber-100 h4,
    main .bg-amber-100 p, main .bg-amber-100 span,
    main .bg-yellow-100 h2, main .bg-yellow-100 h3, main .bg-yellow-100 h4,
    main .bg-yellow-100 p, main .bg-yellow-100 span,
    main [class*="bg-amber-"] h2, main [class*="bg-amber-"] h3, main [class*="bg-amber-"] h4,
    main [class*="bg-amber-"] p, main [class*="bg-amber-"] span {
        color: #1e293b !important;
    }

    /* Financial values "0 грн" — ensure visible on dark background */
    main .text-green-600,
    main .text-green-500 { color: #4ade80 !important; }
    main .text-red-600,
    main .text-red-500 { color: #f87171 !important; }
    main .text-blue-600,
    main .text-blue-500 { color: #60a5fa !important; }

    /* Onboarding banner gradient — override light gradients to dark */
    main .from-primary-50,
    main [class*="from-primary-50"],
    main [class*="via-blue-50"],
    main [class*="to-indigo-50"] {
        background: linear-gradient(135deg, rgba(40, 52, 72, 0.85) 0%, rgba(30, 40, 90, 0.7) 50%, rgba(35, 32, 80, 0.6) 100%) !important;
    }
    .dark main .from-primary-50,
    .dark main [class*="from-primary-50"],
    .dark main [class*="via-blue-50"],
    .dark main [class*="to-indigo-50"] {
        background: linear-gradient(135deg, rgba(15, 23, 42, 0.85) 0%, rgba(12, 20, 50, 0.7) 50%, rgba(15, 14, 40, 0.6) 100%) !important;
    }
    main .border-primary-200 { border-color: rgba(251, 191, 36, 0.2) !important; }


/* ========================================
   DESIGN THEME: ПРИРОДА (Nature)
   Forest greens, earthy tones, organic feel
   Text contrast meets WCAG AA (4.5:1 ratio)
   ======================================== */
@elseif($designTheme === 'corporate')
    body {
        background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 50%, #dcfce7 100%) !important;
    }
    .dark body {
        background: linear-gradient(135deg, #022c22 0%, #052e16 50%, #14532d 100%) !important;
    }

    /* Organic cards */
    main .bg-white,
    main [class*="dark:bg-gray-800"] {
        background: rgba(255, 255, 255, 0.94) !important;
        border: 1px solid rgba(34, 197, 94, 0.18) !important;
        box-shadow: 0 2px 12px rgba(34, 197, 94, 0.06), 0 1px 3px rgba(0,0,0,0.04) !important;
    }
    .dark main .bg-white,
    .dark main [class*="dark:bg-gray-800"] {
        background: rgba(5, 46, 22, 0.88) !important;
        border: 1px solid rgba(34, 197, 94, 0.15) !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4) !important;
    }

    /* Sidebar - leafy */
    aside {
        background: linear-gradient(180deg, #f0fdf4 0%, #ffffff 100%) !important;
        border-right: 1px solid rgba(34, 197, 94, 0.2) !important;
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
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.35) !important;
    }
    .bg-primary-600:focus, .bg-primary-500:focus {
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.4) !important;
    }

    /* Nature accents */
    .bg-primary-50, .bg-primary-100 { background: rgba(209, 250, 229, 0.6) !important; }
    .dark .dark\:bg-primary-900\/50 { background: rgba(6, 78, 59, 0.5) !important; }
    /* WCAG AA: #047857 on white = 4.9:1 contrast ratio */
    .text-primary-700 { color: #047857 !important; }
    .text-primary-600 { color: #059669 !important; }
    /* WCAG AA: #6ee7b7 on #052e16 = 7.2:1 contrast ratio */
    .dark .dark\:text-primary-300, .dark .dark\:text-primary-400 { color: #6ee7b7 !important; }

    /* Soft hover */
    .hover\:bg-gray-100:hover { background: rgba(209, 250, 229, 0.4) !important; }
    .dark .dark\:hover\:bg-gray-700:hover { background: rgba(6, 78, 59, 0.4) !important; }

    /* Improve small text contrast in dark mode */
    .dark main .text-gray-500 { color: #9ca3af !important; }
    .dark main .text-gray-400 { color: #d1d5db !important; }

    /* Rounded like leaves */
    .rounded-xl { border-radius: 1rem !important; }
    .rounded-2xl { border-radius: 1.25rem !important; }

    /* Border colors */
    main .border-gray-200, main .border-gray-300 { border-color: rgba(34, 197, 94, 0.18) !important; }
    .dark .dark\:border-gray-700, .dark .dark\:border-gray-600 { border-color: rgba(34, 197, 94, 0.15) !important; }


/* ========================================
   DESIGN THEME: ОКЕАН (Ocean)
   Deep blue, calming waves, sea vibes
   Good dark mode contrast ensured
   ======================================== */
@elseif($designTheme === 'ocean')
    body {
        background: linear-gradient(135deg, #ecfeff 0%, #e0f2fe 50%, #c7d2fe 100%) !important;
    }
    .dark body {
        background: linear-gradient(135deg, #083344 0%, #0c4a6e 50%, #1e3a5f 100%) !important;
    }

    /* Ocean cards */
    main .bg-white,
    main [class*="dark:bg-gray-800"] {
        background: rgba(255, 255, 255, 0.94) !important;
        border: 1px solid rgba(6, 182, 212, 0.18) !important;
        box-shadow: 0 2px 12px rgba(6, 182, 212, 0.08), 0 1px 3px rgba(0,0,0,0.04) !important;
    }
    .dark main .bg-white,
    .dark main [class*="dark:bg-gray-800"] {
        background: rgba(8, 51, 68, 0.92) !important;
        border: 1px solid rgba(6, 182, 212, 0.2) !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4) !important;
    }

    /* Sidebar - wave gradient */
    aside {
        background: linear-gradient(180deg, #ecfeff 0%, #ffffff 100%) !important;
        border-right: 1px solid rgba(6, 182, 212, 0.2) !important;
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
        box-shadow: 0 6px 20px rgba(6, 182, 212, 0.4) !important;
    }
    .bg-primary-600:focus, .bg-primary-500:focus {
        box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.4) !important;
    }

    /* Ocean accents */
    .bg-primary-50, .bg-primary-100 { background: rgba(207, 250, 254, 0.6) !important; }
    .dark .dark\:bg-primary-900\/50 { background: rgba(8, 51, 68, 0.6) !important; }
    .text-primary-700 { color: #0e7490 !important; }
    .text-primary-600 { color: #0891b2 !important; }
    /* Ensure enough contrast: #67e8f9 on #083344 = 7.5:1 */
    .dark .dark\:text-primary-300, .dark .dark\:text-primary-400 { color: #67e8f9 !important; }

    /* Soft hover */
    .hover\:bg-gray-100:hover { background: rgba(207, 250, 254, 0.4) !important; }
    .dark .dark\:hover\:bg-gray-700:hover { background: rgba(14, 116, 144, 0.3) !important; }

    /* Border colors */
    main .border-gray-200, main .border-gray-300 { border-color: rgba(6, 182, 212, 0.18) !important; }
    .dark .dark\:border-gray-700, .dark .dark\:border-gray-600 { border-color: rgba(6, 182, 212, 0.2) !important; }


/* ========================================
   DESIGN THEME: ЗАХІД (Sunset)
   Warm purple/pink sunset, romantic vibes
   Softened purple borders in light mode
   ======================================== */
@elseif($designTheme === 'sunset')
    body {
        background: linear-gradient(135deg, #fce7f3 0%, #f3e8ff 50%, #e0e7ff 100%) !important;
    }
    .dark body {
        background: linear-gradient(135deg, #1a0a20 0%, #150825 50%, #12102a 100%) !important;
    }

    /* Sunset cards — softer purple border in light mode */
    main .bg-white,
    main [class*="dark:bg-gray-800"] {
        background: rgba(255, 255, 255, 0.94) !important;
        border: 1px solid rgba(168, 85, 247, 0.12) !important;
        box-shadow: 0 2px 12px rgba(168, 85, 247, 0.06), 0 1px 3px rgba(0,0,0,0.04) !important;
    }
    .dark main .bg-white,
    .dark main [class*="dark:bg-gray-800"] {
        background: rgba(30, 12, 45, 0.92) !important;
        border: 1px solid rgba(168, 85, 247, 0.2) !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4) !important;
    }

    /* Sidebar - sunset gradient */
    aside {
        background: linear-gradient(180deg, #fdf2f8 0%, #ffffff 100%) !important;
        border-right: 1px solid rgba(168, 85, 247, 0.15) !important;
    }
    .dark aside {
        background: linear-gradient(180deg, rgba(25, 10, 38, 0.97) 0%, rgba(30, 14, 42, 0.98) 100%) !important;
        border-right: 1px solid rgba(168, 85, 247, 0.2) !important;
    }

    /* Sunset purple buttons */
    .bg-primary-600, .bg-primary-500 {
        background: linear-gradient(135deg, #ec4899 0%, #a855f7 100%) !important;
        box-shadow: 0 4px 15px rgba(168, 85, 247, 0.25) !important;
    }
    .bg-primary-600:hover, .bg-primary-500:hover {
        background: linear-gradient(135deg, #f472b6 0%, #c084fc 100%) !important;
        box-shadow: 0 6px 20px rgba(168, 85, 247, 0.35) !important;
    }
    .bg-primary-600:focus, .bg-primary-500:focus {
        box-shadow: 0 0 0 3px rgba(168, 85, 247, 0.4) !important;
    }

    /* Sunset accents */
    .bg-primary-50, .bg-primary-100 { background: rgba(250, 232, 255, 0.6) !important; }
    .dark .dark\:bg-primary-900\/50 { background: rgba(45, 18, 70, 0.5) !important; }
    .text-primary-700 { color: #7e22ce !important; }
    .text-primary-600 { color: #9333ea !important; }
    .dark .dark\:text-primary-300, .dark .dark\:text-primary-400 { color: #d8b4fe !important; }

    /* Soft hover */
    .hover\:bg-gray-100:hover { background: rgba(250, 232, 255, 0.4) !important; }
    .dark .dark\:hover\:bg-gray-700:hover { background: rgba(80, 30, 120, 0.25) !important; }

    /* Border colors — softer in light mode */
    main .border-gray-200, main .border-gray-300 { border-color: rgba(168, 85, 247, 0.12) !important; }
    .dark .dark\:border-gray-700, .dark .dark\:border-gray-600 { border-color: rgba(168, 85, 247, 0.18) !important; }

@endif
</style>
