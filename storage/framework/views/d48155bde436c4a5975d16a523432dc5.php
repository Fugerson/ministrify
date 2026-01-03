<?php
    $designTheme = $currentChurch->design_theme ?? 'modern';
?>

<style>
/* ========================================
   DESIGN THEME: MODERN (Default)
   - Rounded corners, soft shadows, gradients
   ======================================== */
<?php if($designTheme === 'modern'): ?>
    :root {
        --card-radius: 1rem;
        --btn-radius: 0.75rem;
        --input-radius: 0.75rem;
        --card-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
        --card-shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
    }
    .card-custom { @apply rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800; }
    .btn-custom { @apply rounded-xl; }
    .input-custom { @apply rounded-xl; }
    .sidebar-custom { @apply bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700; }
    .header-custom { @apply bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700; }
    .nav-item-active { @apply bg-primary-50 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300 rounded-xl; }
    .nav-item-hover { @apply hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl; }


/* ========================================
   DESIGN THEME: MINIMAL
   - Sharp edges, no shadows, clean lines
   ======================================== */
<?php elseif($designTheme === 'minimal'): ?>
    body { font-weight: 400; }
    .bg-white { background-color: #fafafa !important; }
    .dark .bg-white, .dark .dark\:bg-gray-800 { background-color: #0f0f0f !important; }
    .dark .dark\:bg-gray-900, .dark body { background-color: #000 !important; }

    /* Remove all shadows */
    .shadow, .shadow-sm, .shadow-md, .shadow-lg, .shadow-xl { box-shadow: none !important; }

    /* Sharp corners */
    .rounded-xl, .rounded-2xl, .rounded-3xl { border-radius: 0.25rem !important; }
    .rounded-lg { border-radius: 0.125rem !important; }
    .rounded-full { border-radius: 0.25rem !important; }

    /* Thinner borders */
    .border { border-width: 1px !important; border-color: #e5e5e5 !important; }
    .dark .border { border-color: #262626 !important; }

    /* Buttons - flat style */
    button, .btn, [type="submit"] {
        border-radius: 0.25rem !important;
        font-weight: 500 !important;
        text-transform: uppercase !important;
        font-size: 0.75rem !important;
        letter-spacing: 0.05em !important;
    }

    /* Cards - minimal */
    .bg-white.rounded-2xl, .dark\:bg-gray-800.rounded-2xl {
        border-radius: 0.25rem !important;
        border: 1px solid #e5e5e5 !important;
    }
    .dark .bg-white.rounded-2xl, .dark .dark\:bg-gray-800.rounded-2xl {
        border-color: #262626 !important;
    }


/* ========================================
   DESIGN THEME: BRUTALIST
   - Bold, raw, high contrast
   ======================================== */
<?php elseif($designTheme === 'brutalist'): ?>
    body { font-family: 'Courier New', monospace !important; }

    /* Black borders */
    .border, .border-gray-200, .border-gray-300 {
        border-color: #000 !important;
        border-width: 2px !important;
    }
    .dark .border, .dark .border-gray-700 {
        border-color: #fff !important;
    }

    /* No rounded corners */
    .rounded, .rounded-md, .rounded-lg, .rounded-xl, .rounded-2xl, .rounded-3xl, .rounded-full {
        border-radius: 0 !important;
    }

    /* High contrast shadows */
    .shadow, .shadow-sm, .shadow-md {
        box-shadow: 4px 4px 0 #000 !important;
    }
    .dark .shadow, .dark .shadow-sm, .dark .shadow-md {
        box-shadow: 4px 4px 0 #fff !important;
    }

    /* Bold buttons */
    button, .btn, [type="submit"] {
        border: 2px solid #000 !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
    }
    .dark button, .dark .btn, .dark [type="submit"] {
        border-color: #fff !important;
    }

    /* Background patterns */
    .bg-gray-50, .bg-gray-100 {
        background: repeating-linear-gradient(
            45deg,
            transparent,
            transparent 2px,
            rgba(0,0,0,0.03) 2px,
            rgba(0,0,0,0.03) 4px
        ) !important;
    }


/* ========================================
   DESIGN THEME: GLASS (Glassmorphism)
   - Transparent, blur, frosted glass effect
   ======================================== */
<?php elseif($designTheme === 'glass'): ?>
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        min-height: 100vh;
    }
    .dark body {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%) !important;
    }

    /* Glass cards */
    .bg-white, .dark\:bg-gray-800 {
        background: rgba(255, 255, 255, 0.15) !important;
        backdrop-filter: blur(20px) !important;
        -webkit-backdrop-filter: blur(20px) !important;
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
    }
    .dark .bg-white, .dark .dark\:bg-gray-800 {
        background: rgba(0, 0, 0, 0.3) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
    }

    /* Text adjustments */
    .text-gray-900, .text-gray-800, .text-gray-700 { color: #fff !important; }
    .text-gray-600, .text-gray-500, .text-gray-400 { color: rgba(255,255,255,0.7) !important; }
    .dark .text-gray-100, .dark .text-white { color: #fff !important; }

    /* Sidebar glass */
    aside, .sidebar {
        background: rgba(255, 255, 255, 0.1) !important;
        backdrop-filter: blur(20px) !important;
    }
    .dark aside, .dark .sidebar {
        background: rgba(0, 0, 0, 0.4) !important;
    }

    /* Glowing buttons */
    .bg-primary-600, .bg-primary-500 {
        box-shadow: 0 0 20px rgba(99, 102, 241, 0.5) !important;
    }

    /* Inputs */
    input, select, textarea {
        background: rgba(255, 255, 255, 0.1) !important;
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
        color: #fff !important;
    }
    input::placeholder, textarea::placeholder { color: rgba(255,255,255,0.5) !important; }


/* ========================================
   DESIGN THEME: NEUMORPHISM
   - Soft UI, extruded shapes, subtle shadows
   ======================================== */
<?php elseif($designTheme === 'neumorphism'): ?>
    body, .bg-gray-100, .dark\:bg-gray-900 {
        background: #e0e5ec !important;
    }
    .dark body, .dark .bg-gray-100, .dark .dark\:bg-gray-900 {
        background: #1a1a2e !important;
    }

    /* Neumorphic cards */
    .bg-white, .dark\:bg-gray-800 {
        background: #e0e5ec !important;
        border: none !important;
        box-shadow:
            9px 9px 16px rgba(163, 177, 198, 0.6),
            -9px -9px 16px rgba(255, 255, 255, 0.8) !important;
    }
    .dark .bg-white, .dark .dark\:bg-gray-800 {
        background: #1a1a2e !important;
        box-shadow:
            9px 9px 16px rgba(0, 0, 0, 0.4),
            -9px -9px 16px rgba(56, 56, 82, 0.4) !important;
    }

    /* Neumorphic buttons - pressed look on hover */
    button, .btn, [type="submit"] {
        background: #e0e5ec !important;
        border: none !important;
        box-shadow:
            5px 5px 10px rgba(163, 177, 198, 0.5),
            -5px -5px 10px rgba(255, 255, 255, 0.8) !important;
        transition: all 0.2s ease !important;
    }
    button:hover, .btn:hover, [type="submit"]:hover {
        box-shadow:
            inset 3px 3px 6px rgba(163, 177, 198, 0.5),
            inset -3px -3px 6px rgba(255, 255, 255, 0.8) !important;
    }
    .dark button, .dark .btn, .dark [type="submit"] {
        background: #1a1a2e !important;
        box-shadow:
            5px 5px 10px rgba(0, 0, 0, 0.4),
            -5px -5px 10px rgba(56, 56, 82, 0.4) !important;
    }

    /* Primary buttons keep color */
    .bg-primary-600, .bg-primary-500 {
        box-shadow:
            5px 5px 10px rgba(163, 177, 198, 0.5),
            -5px -5px 10px rgba(255, 255, 255, 0.8) !important;
    }

    /* Inputs - inset */
    input, select, textarea {
        background: #e0e5ec !important;
        border: none !important;
        box-shadow:
            inset 3px 3px 6px rgba(163, 177, 198, 0.5),
            inset -3px -3px 6px rgba(255, 255, 255, 0.8) !important;
    }
    .dark input, .dark select, .dark textarea {
        background: #1a1a2e !important;
        box-shadow:
            inset 3px 3px 6px rgba(0, 0, 0, 0.4),
            inset -3px -3px 6px rgba(56, 56, 82, 0.4) !important;
    }


/* ========================================
   DESIGN THEME: CORPORATE
   - Professional, traditional, subtle
   ======================================== */
<?php elseif($designTheme === 'corporate'): ?>
    body { font-family: 'Georgia', serif !important; }

    /* Subtle borders */
    .border-gray-200 { border-color: #d1d5db !important; }
    .dark .border-gray-700 { border-color: #4b5563 !important; }

    /* Smaller radius */
    .rounded-xl, .rounded-2xl { border-radius: 0.5rem !important; }
    .rounded-lg { border-radius: 0.375rem !important; }

    /* Subtle shadows */
    .shadow-sm { box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important; }
    .shadow { box-shadow: 0 2px 4px rgba(0,0,0,0.06) !important; }

    /* Headers with bottom border accent */
    h1, h2, .text-lg.font-semibold, .text-xl.font-bold {
        padding-bottom: 0.5rem;
        border-bottom: 2px solid var(--tw-text-opacity, 1) !important;
        border-color: inherit;
        display: inline-block;
    }

    /* Professional button styling */
    button, .btn, [type="submit"] {
        font-weight: 600 !important;
        letter-spacing: 0.025em !important;
    }

    /* Muted backgrounds */
    .bg-primary-50 { background-color: #f8fafc !important; }
    .bg-primary-100 { background-color: #f1f5f9 !important; }


/* ========================================
   DESIGN THEME: PLAYFUL
   - Colorful, fun, rounded, bouncy
   ======================================== */
<?php elseif($designTheme === 'playful'): ?>
    body {
        background: linear-gradient(180deg, #fef3c7 0%, #fce7f3 50%, #ddd6fe 100%) !important;
        font-family: 'Comic Sans MS', 'Chalkboard SE', sans-serif !important;
    }
    .dark body {
        background: linear-gradient(180deg, #1e1b4b 0%, #3b0764 50%, #4c1d95 100%) !important;
    }

    /* Extra rounded */
    .rounded-lg, .rounded-xl { border-radius: 1.5rem !important; }
    .rounded-2xl { border-radius: 2rem !important; }
    .rounded-full { border-radius: 9999px !important; }

    /* Colorful cards */
    .bg-white, .dark\:bg-gray-800 {
        background: rgba(255, 255, 255, 0.9) !important;
        border: 3px solid #a855f7 !important;
        box-shadow: 0 10px 25px rgba(168, 85, 247, 0.2) !important;
    }
    .dark .bg-white, .dark .dark\:bg-gray-800 {
        background: rgba(30, 27, 75, 0.9) !important;
        border: 3px solid #c084fc !important;
    }

    /* Fun shadows */
    .shadow, .shadow-sm, .shadow-md {
        box-shadow:
            0 10px 25px rgba(236, 72, 153, 0.15),
            0 4px 6px rgba(168, 85, 247, 0.1) !important;
    }

    /* Bouncy buttons */
    button, .btn, [type="submit"] {
        border-radius: 9999px !important;
        font-weight: 700 !important;
        transition: transform 0.15s ease !important;
    }
    button:hover, .btn:hover, [type="submit"]:hover {
        transform: scale(1.05) !important;
    }
    button:active, .btn:active, [type="submit"]:active {
        transform: scale(0.95) !important;
    }

<?php endif; ?>
</style>
<?php /**PATH /var/www/html/resources/views/partials/design-themes.blade.php ENDPATH**/ ?>