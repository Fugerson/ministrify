<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Public Site Templates
    |--------------------------------------------------------------------------
    |
    | Define the available templates for public church websites.
    | Each template has its own visual style, fonts, and default settings.
    |
    */

    'templates' => [
        'modern' => [
            'name' => 'Сучасний',
            'description' => 'Чистий, мінімалістичний дизайн з градієнтами та м\'якими тінями',
            'preview' => '/images/templates/modern-preview.png',
            'fonts' => [
                'heading' => 'Inter',
                'body' => 'Inter',
            ],
            'font_url' => 'https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap',
            'css_class' => 'template-modern',
            'hero_default' => 'gradient',
            'navigation_default' => 'transparent',
            'footer_default' => 'multi-column',
            'button_style' => 'rounded',
            'card_style' => 'shadow',
        ],

        'classic' => [
            'name' => 'Класичний',
            'description' => 'Традиційний, елегантний дизайн з засечками',
            'preview' => '/images/templates/classic-preview.png',
            'fonts' => [
                'heading' => 'Playfair Display',
                'body' => 'Lora',
            ],
            'font_url' => 'https://fonts.bunny.net/css?family=playfair-display:400,700&lora:400,500,600&display=swap',
            'css_class' => 'template-classic',
            'hero_default' => 'full-image',
            'navigation_default' => 'solid',
            'footer_default' => 'multi-column',
            'button_style' => 'sharp',
            'card_style' => 'border',
        ],

        'bold' => [
            'name' => 'Яскравий',
            'description' => 'Сміливі кольори, великі заголовки, високий контраст',
            'preview' => '/images/templates/bold-preview.png',
            'fonts' => [
                'heading' => 'Oswald',
                'body' => 'Open Sans',
            ],
            'font_url' => 'https://fonts.bunny.net/css?family=oswald:400,500,700&open-sans:400,600&display=swap',
            'css_class' => 'template-bold',
            'hero_default' => 'full-image',
            'navigation_default' => 'solid',
            'footer_default' => 'simple',
            'button_style' => 'pill',
            'card_style' => 'shadow-lg',
        ],

        'minimal' => [
            'name' => 'Мінімалізм',
            'description' => 'Максимальна простота, чистота та функціональність',
            'preview' => '/images/templates/minimal-preview.png',
            'fonts' => [
                'heading' => 'DM Sans',
                'body' => 'DM Sans',
            ],
            'font_url' => 'https://fonts.bunny.net/css?family=dm-sans:400,500,700&display=swap',
            'css_class' => 'template-minimal',
            'hero_default' => 'split',
            'navigation_default' => 'minimal',
            'footer_default' => 'centered',
            'button_style' => 'square',
            'card_style' => 'flat',
        ],

        'dark' => [
            'name' => 'Темний',
            'description' => 'Елегантний темний режим з сучасним виглядом',
            'preview' => '/images/templates/dark-preview.png',
            'fonts' => [
                'heading' => 'Space Grotesk',
                'body' => 'Inter',
            ],
            'font_url' => 'https://fonts.bunny.net/css?family=space-grotesk:400,500,700&inter:400,500&display=swap',
            'css_class' => 'template-dark',
            'hero_default' => 'gradient',
            'navigation_default' => 'transparent',
            'footer_default' => 'simple',
            'button_style' => 'rounded',
            'card_style' => 'glass',
            'dark_mode' => true,
            'colors' => [
                'background' => '#0f172a',
                'text' => '#e2e8f0',
                'heading' => '#f1f5f9',
            ],
        ],

        'warm' => [
            'name' => 'Теплий',
            'description' => 'М\'які кольори, заокруглені форми, привітний вигляд',
            'preview' => '/images/templates/warm-preview.png',
            'fonts' => [
                'heading' => 'Nunito',
                'body' => 'Nunito',
            ],
            'font_url' => 'https://fonts.bunny.net/css?family=nunito:400,500,600,700,800&display=swap',
            'css_class' => 'template-warm',
            'hero_default' => 'slideshow',
            'navigation_default' => 'solid',
            'footer_default' => 'multi-column',
            'button_style' => 'pill',
            'card_style' => 'soft',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Available Fonts
    |--------------------------------------------------------------------------
    */

    'fonts' => [
        'Inter' => 'Inter',
        'Poppins' => 'Poppins',
        'Open Sans' => 'Open Sans',
        'Roboto' => 'Roboto',
        'Lato' => 'Lato',
        'Montserrat' => 'Montserrat',
        'Playfair Display' => 'Playfair Display',
        'Lora' => 'Lora',
        'DM Sans' => 'DM Sans',
        'Source Sans Pro' => 'Source Sans Pro',
        'Oswald' => 'Oswald',
        'Nunito' => 'Nunito',
        'Space Grotesk' => 'Space Grotesk',
        'Merriweather' => 'Merriweather',
        'PT Serif' => 'PT Serif',
    ],

    /*
    |--------------------------------------------------------------------------
    | Button Styles
    |--------------------------------------------------------------------------
    */

    'button_styles' => [
        'rounded' => 'Заокруглені',
        'pill' => 'Овальні',
        'square' => 'Квадратні',
        'sharp' => 'Гострі кути',
    ],

    /*
    |--------------------------------------------------------------------------
    | Hero Types
    |--------------------------------------------------------------------------
    */

    'hero_types' => [
        'image' => [
            'name' => 'Зображення',
            'description' => 'Фонове зображення з накладенням',
        ],
        'video' => [
            'name' => 'Відео',
            'description' => 'YouTube або Vimeo відео на фоні',
        ],
        'gradient' => [
            'name' => 'Градієнт',
            'description' => 'Динамічний градієнт з основного кольору',
        ],
        'slideshow' => [
            'name' => 'Слайд-шоу',
            'description' => 'Декілька зображень, що змінюються',
        ],
        'split' => [
            'name' => 'Розділений',
            'description' => '50/50 текст і зображення',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Navigation Styles
    |--------------------------------------------------------------------------
    */

    'navigation_styles' => [
        'transparent' => 'Прозора (поверх hero)',
        'solid' => 'Суцільна',
        'minimal' => 'Мінімальна',
    ],

    /*
    |--------------------------------------------------------------------------
    | Sections
    |--------------------------------------------------------------------------
    */

    'sections' => [
        'hero' => [
            'name' => 'Hero секція',
            'icon' => 'heroicons-image',
            'description' => 'Головна секція з заголовком та CTA',
        ],
        'service_times' => [
            'name' => 'Розклад служінь',
            'icon' => 'heroicons-clock',
            'description' => 'Час та місце богослужінь',
        ],
        'about' => [
            'name' => 'Про нас',
            'icon' => 'heroicons-information-circle',
            'description' => 'Місія, візія, цінності, історія',
        ],
        'pastor_message' => [
            'name' => 'Слово пастора',
            'icon' => 'heroicons-chat-bubble-left-right',
            'description' => 'Привітання від пастора',
        ],
        'leadership' => [
            'name' => 'Команда лідерів',
            'icon' => 'heroicons-user-group',
            'description' => 'Пастори, лідери, персонал',
        ],
        'events' => [
            'name' => 'Події',
            'icon' => 'heroicons-calendar',
            'description' => 'Найближчі події церкви',
        ],
        'sermons' => [
            'name' => 'Проповіді',
            'icon' => 'heroicons-play-circle',
            'description' => 'Відео та аудіо проповіді',
        ],
        'ministries' => [
            'name' => 'Служіння',
            'icon' => 'heroicons-heart',
            'description' => 'Напрямки служінь церкви',
        ],
        'groups' => [
            'name' => 'Малі групи',
            'icon' => 'heroicons-users',
            'description' => 'Домашні групи та спілкування',
        ],
        'gallery' => [
            'name' => 'Галерея',
            'icon' => 'heroicons-photo',
            'description' => 'Фотоальбоми з життя церкви',
        ],
        'testimonials' => [
            'name' => 'Свідчення',
            'icon' => 'heroicons-chat-bubble-bottom-center-text',
            'description' => 'Історії членів церкви',
        ],
        'blog' => [
            'name' => 'Блог',
            'icon' => 'heroicons-newspaper',
            'description' => 'Новини та статті',
        ],
        'faq' => [
            'name' => 'FAQ',
            'icon' => 'heroicons-question-mark-circle',
            'description' => 'Часті питання',
        ],
        'donations' => [
            'name' => 'Пожертви',
            'icon' => 'heroicons-currency-dollar',
            'description' => 'Збори та пожертви',
        ],
        'contact' => [
            'name' => 'Контакти',
            'icon' => 'heroicons-map-pin',
            'description' => 'Адреса, телефон, карта',
        ],
    ],
];
