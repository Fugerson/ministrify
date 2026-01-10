<?php return array (
  'app' => 
  array (
    'name' => 'Ministrify',
    'env' => 'local',
    'debug' => true,
    'url' => 'http://localhost',
    'asset_url' => NULL,
    'timezone' => 'Europe/Kiev',
    'locale' => 'uk',
    'fallback_locale' => 'en',
    'faker_locale' => 'uk_UA',
    'key' => 'base64:z7AU8395WdbKE2HguymDqVzyRsJcwJu7M0SsjAuJXFE=',
    'cipher' => 'AES-256-CBC',
    'maintenance' => 
    array (
      'driver' => 'file',
    ),
    'providers' => 
    array (
      0 => 'Illuminate\\Auth\\AuthServiceProvider',
      1 => 'Illuminate\\Broadcasting\\BroadcastServiceProvider',
      2 => 'Illuminate\\Bus\\BusServiceProvider',
      3 => 'Illuminate\\Cache\\CacheServiceProvider',
      4 => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
      5 => 'Illuminate\\Cookie\\CookieServiceProvider',
      6 => 'Illuminate\\Database\\DatabaseServiceProvider',
      7 => 'Illuminate\\Encryption\\EncryptionServiceProvider',
      8 => 'Illuminate\\Filesystem\\FilesystemServiceProvider',
      9 => 'Illuminate\\Foundation\\Providers\\FoundationServiceProvider',
      10 => 'Illuminate\\Hashing\\HashServiceProvider',
      11 => 'Illuminate\\Mail\\MailServiceProvider',
      12 => 'Illuminate\\Notifications\\NotificationServiceProvider',
      13 => 'Illuminate\\Pagination\\PaginationServiceProvider',
      14 => 'Illuminate\\Auth\\Passwords\\PasswordResetServiceProvider',
      15 => 'Illuminate\\Pipeline\\PipelineServiceProvider',
      16 => 'Illuminate\\Queue\\QueueServiceProvider',
      17 => 'Illuminate\\Redis\\RedisServiceProvider',
      18 => 'Illuminate\\Session\\SessionServiceProvider',
      19 => 'Illuminate\\Translation\\TranslationServiceProvider',
      20 => 'Illuminate\\Validation\\ValidationServiceProvider',
      21 => 'Illuminate\\View\\ViewServiceProvider',
      22 => 'App\\Providers\\AppServiceProvider',
      23 => 'App\\Providers\\AuthServiceProvider',
      24 => 'App\\Providers\\EventServiceProvider',
      25 => 'App\\Providers\\RouteServiceProvider',
    ),
    'aliases' => 
    array (
      'App' => 'Illuminate\\Support\\Facades\\App',
      'Arr' => 'Illuminate\\Support\\Arr',
      'Artisan' => 'Illuminate\\Support\\Facades\\Artisan',
      'Auth' => 'Illuminate\\Support\\Facades\\Auth',
      'Blade' => 'Illuminate\\Support\\Facades\\Blade',
      'Broadcast' => 'Illuminate\\Support\\Facades\\Broadcast',
      'Bus' => 'Illuminate\\Support\\Facades\\Bus',
      'Cache' => 'Illuminate\\Support\\Facades\\Cache',
      'Config' => 'Illuminate\\Support\\Facades\\Config',
      'Cookie' => 'Illuminate\\Support\\Facades\\Cookie',
      'Crypt' => 'Illuminate\\Support\\Facades\\Crypt',
      'Date' => 'Illuminate\\Support\\Facades\\Date',
      'DB' => 'Illuminate\\Support\\Facades\\DB',
      'Eloquent' => 'Illuminate\\Database\\Eloquent\\Model',
      'Event' => 'Illuminate\\Support\\Facades\\Event',
      'File' => 'Illuminate\\Support\\Facades\\File',
      'Gate' => 'Illuminate\\Support\\Facades\\Gate',
      'Hash' => 'Illuminate\\Support\\Facades\\Hash',
      'Http' => 'Illuminate\\Support\\Facades\\Http',
      'Js' => 'Illuminate\\Support\\Js',
      'Lang' => 'Illuminate\\Support\\Facades\\Lang',
      'Log' => 'Illuminate\\Support\\Facades\\Log',
      'Mail' => 'Illuminate\\Support\\Facades\\Mail',
      'Notification' => 'Illuminate\\Support\\Facades\\Notification',
      'Number' => 'Illuminate\\Support\\Number',
      'Password' => 'Illuminate\\Support\\Facades\\Password',
      'Process' => 'Illuminate\\Support\\Facades\\Process',
      'Queue' => 'Illuminate\\Support\\Facades\\Queue',
      'RateLimiter' => 'Illuminate\\Support\\Facades\\RateLimiter',
      'Redirect' => 'Illuminate\\Support\\Facades\\Redirect',
      'Request' => 'Illuminate\\Support\\Facades\\Request',
      'Response' => 'Illuminate\\Support\\Facades\\Response',
      'Route' => 'Illuminate\\Support\\Facades\\Route',
      'Schema' => 'Illuminate\\Support\\Facades\\Schema',
      'Session' => 'Illuminate\\Support\\Facades\\Session',
      'Storage' => 'Illuminate\\Support\\Facades\\Storage',
      'Str' => 'Illuminate\\Support\\Str',
      'URL' => 'Illuminate\\Support\\Facades\\URL',
      'Validator' => 'Illuminate\\Support\\Facades\\Validator',
      'View' => 'Illuminate\\Support\\Facades\\View',
      'Vite' => 'Illuminate\\Support\\Facades\\Vite',
    ),
  ),
  'auth' => 
  array (
    'defaults' => 
    array (
      'guard' => 'web',
      'passwords' => 'users',
    ),
    'guards' => 
    array (
      'web' => 
      array (
        'driver' => 'session',
        'provider' => 'users',
      ),
      'sanctum' => 
      array (
        'driver' => 'sanctum',
        'provider' => NULL,
      ),
    ),
    'providers' => 
    array (
      'users' => 
      array (
        'driver' => 'eloquent',
        'model' => 'App\\Models\\User',
      ),
    ),
    'passwords' => 
    array (
      'users' => 
      array (
        'provider' => 'users',
        'table' => 'password_reset_tokens',
        'expire' => 60,
        'throttle' => 60,
      ),
    ),
    'password_timeout' => 10800,
  ),
  'cache' => 
  array (
    'default' => 'redis',
    'stores' => 
    array (
      'array' => 
      array (
        'driver' => 'array',
        'serialize' => false,
      ),
      'database' => 
      array (
        'driver' => 'database',
        'table' => 'cache',
        'connection' => NULL,
        'lock_connection' => NULL,
      ),
      'file' => 
      array (
        'driver' => 'file',
        'path' => '/var/www/html/storage/framework/cache/data',
        'lock_path' => '/var/www/html/storage/framework/cache/data',
      ),
      'redis' => 
      array (
        'driver' => 'redis',
        'connection' => 'cache',
        'lock_connection' => 'default',
      ),
    ),
    'prefix' => 'ministrify_cache_',
  ),
  'cors' => 
  array (
    'paths' => 
    array (
      0 => 'api/*',
      1 => 'sanctum/csrf-cookie',
    ),
    'allowed_methods' => 
    array (
      0 => '*',
    ),
    'allowed_origins' => 
    array (
      0 => '*',
    ),
    'allowed_origins_patterns' => 
    array (
    ),
    'allowed_headers' => 
    array (
      0 => '*',
    ),
    'exposed_headers' => 
    array (
    ),
    'max_age' => 0,
    'supports_credentials' => false,
  ),
  'database' => 
  array (
    'default' => 'mysql',
    'connections' => 
    array (
      'sqlite' => 
      array (
        'driver' => 'sqlite',
        'url' => NULL,
        'database' => 'ministrify',
        'prefix' => '',
        'foreign_key_constraints' => true,
      ),
      'mysql' => 
      array (
        'driver' => 'mysql',
        'url' => NULL,
        'host' => 'mysql',
        'port' => '3306',
        'database' => 'ministrify',
        'username' => 'ministrify',
        'password' => 'secret',
        'unix_socket' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'prefix_indexes' => true,
        'strict' => true,
        'engine' => NULL,
        'options' => 
        array (
        ),
      ),
    ),
    'migrations' => 'migrations',
    'redis' => 
    array (
      'client' => 'phpredis',
      'options' => 
      array (
        'cluster' => 'redis',
        'prefix' => 'ministrify_database_',
      ),
      'default' => 
      array (
        'url' => NULL,
        'host' => 'redis',
        'username' => NULL,
        'password' => NULL,
        'port' => '6379',
        'database' => '0',
      ),
      'cache' => 
      array (
        'url' => NULL,
        'host' => 'redis',
        'username' => NULL,
        'password' => NULL,
        'port' => '6379',
        'database' => '1',
      ),
    ),
  ),
  'filesystems' => 
  array (
    'default' => 'local',
    'disks' => 
    array (
      'local' => 
      array (
        'driver' => 'local',
        'root' => '/var/www/html/storage/app',
        'throw' => false,
      ),
      'public' => 
      array (
        'driver' => 'local',
        'root' => '/var/www/html/storage/app/public',
        'url' => 'http://localhost/storage',
        'visibility' => 'public',
        'throw' => false,
      ),
    ),
    'links' => 
    array (
      '/var/www/html/public/storage' => '/var/www/html/storage/app/public',
    ),
  ),
  'hashing' => 
  array (
    'driver' => 'bcrypt',
    'bcrypt' => 
    array (
      'rounds' => 12,
      'verify' => true,
    ),
    'argon' => 
    array (
      'memory' => 65536,
      'threads' => 1,
      'time' => 4,
      'verify' => true,
    ),
    'rehash_on_login' => true,
  ),
  'logging' => 
  array (
    'default' => 'stack',
    'deprecations' => 
    array (
      'channel' => NULL,
      'trace' => false,
    ),
    'channels' => 
    array (
      'stack' => 
      array (
        'driver' => 'stack',
        'channels' => 
        array (
          0 => 'single',
        ),
        'ignore_exceptions' => false,
      ),
      'single' => 
      array (
        'driver' => 'single',
        'path' => '/var/www/html/storage/logs/laravel.log',
        'level' => 'debug',
        'replace_placeholders' => true,
      ),
      'daily' => 
      array (
        'driver' => 'daily',
        'path' => '/var/www/html/storage/logs/laravel.log',
        'level' => 'debug',
        'days' => 14,
        'replace_placeholders' => true,
      ),
      'stderr' => 
      array (
        'driver' => 'monolog',
        'level' => 'debug',
        'handler' => 'Monolog\\Handler\\StreamHandler',
        'formatter' => NULL,
        'with' => 
        array (
          'stream' => 'php://stderr',
        ),
        'processors' => 
        array (
          0 => 'Monolog\\Processor\\PsrLogMessageProcessor',
        ),
      ),
      'null' => 
      array (
        'driver' => 'monolog',
        'handler' => 'Monolog\\Handler\\NullHandler',
      ),
      'emergency' => 
      array (
        'path' => '/var/www/html/storage/logs/laravel.log',
      ),
      'security' => 
      array (
        'driver' => 'daily',
        'path' => '/var/www/html/storage/logs/security.log',
        'level' => 'info',
        'days' => 90,
        'replace_placeholders' => true,
      ),
    ),
  ),
  'mail' => 
  array (
    'default' => 'smtp',
    'mailers' => 
    array (
      'smtp' => 
      array (
        'transport' => 'smtp',
        'url' => NULL,
        'host' => 'mailpit',
        'port' => '1025',
        'encryption' => NULL,
        'username' => NULL,
        'password' => NULL,
        'timeout' => NULL,
        'local_domain' => NULL,
      ),
      'log' => 
      array (
        'transport' => 'log',
        'channel' => NULL,
      ),
      'array' => 
      array (
        'transport' => 'array',
      ),
    ),
    'from' => 
    array (
      'address' => 'hello@ministrify.app',
      'name' => 'Ministrify',
    ),
  ),
  'public_site_templates' => 
  array (
    'templates' => 
    array (
      'modern' => 
      array (
        'name' => 'Сучасний',
        'description' => 'Чистий, мінімалістичний дизайн з градієнтами та м\'якими тінями',
        'preview' => '/images/templates/modern-preview.png',
        'fonts' => 
        array (
          'heading' => 'Inter',
          'body' => 'Inter',
        ),
        'font_url' => 'https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap',
        'css_class' => 'template-modern',
        'hero_default' => 'gradient',
        'navigation_default' => 'transparent',
        'footer_default' => 'multi-column',
        'button_style' => 'rounded',
        'card_style' => 'shadow',
      ),
      'classic' => 
      array (
        'name' => 'Класичний',
        'description' => 'Традиційний, елегантний дизайн з засечками',
        'preview' => '/images/templates/classic-preview.png',
        'fonts' => 
        array (
          'heading' => 'Playfair Display',
          'body' => 'Lora',
        ),
        'font_url' => 'https://fonts.bunny.net/css?family=playfair-display:400,700&lora:400,500,600&display=swap',
        'css_class' => 'template-classic',
        'hero_default' => 'full-image',
        'navigation_default' => 'solid',
        'footer_default' => 'multi-column',
        'button_style' => 'sharp',
        'card_style' => 'border',
      ),
      'bold' => 
      array (
        'name' => 'Яскравий',
        'description' => 'Сміливі кольори, великі заголовки, високий контраст',
        'preview' => '/images/templates/bold-preview.png',
        'fonts' => 
        array (
          'heading' => 'Oswald',
          'body' => 'Open Sans',
        ),
        'font_url' => 'https://fonts.bunny.net/css?family=oswald:400,500,700&open-sans:400,600&display=swap',
        'css_class' => 'template-bold',
        'hero_default' => 'full-image',
        'navigation_default' => 'solid',
        'footer_default' => 'simple',
        'button_style' => 'pill',
        'card_style' => 'shadow-lg',
      ),
      'minimal' => 
      array (
        'name' => 'Мінімалізм',
        'description' => 'Максимальна простота, чистота та функціональність',
        'preview' => '/images/templates/minimal-preview.png',
        'fonts' => 
        array (
          'heading' => 'DM Sans',
          'body' => 'DM Sans',
        ),
        'font_url' => 'https://fonts.bunny.net/css?family=dm-sans:400,500,700&display=swap',
        'css_class' => 'template-minimal',
        'hero_default' => 'split',
        'navigation_default' => 'minimal',
        'footer_default' => 'centered',
        'button_style' => 'square',
        'card_style' => 'flat',
      ),
      'dark' => 
      array (
        'name' => 'Темний',
        'description' => 'Елегантний темний режим з сучасним виглядом',
        'preview' => '/images/templates/dark-preview.png',
        'fonts' => 
        array (
          'heading' => 'Space Grotesk',
          'body' => 'Inter',
        ),
        'font_url' => 'https://fonts.bunny.net/css?family=space-grotesk:400,500,700&inter:400,500&display=swap',
        'css_class' => 'template-dark',
        'hero_default' => 'gradient',
        'navigation_default' => 'transparent',
        'footer_default' => 'simple',
        'button_style' => 'rounded',
        'card_style' => 'glass',
        'dark_mode' => true,
        'colors' => 
        array (
          'background' => '#0f172a',
          'text' => '#e2e8f0',
          'heading' => '#f1f5f9',
        ),
      ),
      'warm' => 
      array (
        'name' => 'Теплий',
        'description' => 'М\'які кольори, заокруглені форми, привітний вигляд',
        'preview' => '/images/templates/warm-preview.png',
        'fonts' => 
        array (
          'heading' => 'Nunito',
          'body' => 'Nunito',
        ),
        'font_url' => 'https://fonts.bunny.net/css?family=nunito:400,500,600,700,800&display=swap',
        'css_class' => 'template-warm',
        'hero_default' => 'slideshow',
        'navigation_default' => 'solid',
        'footer_default' => 'multi-column',
        'button_style' => 'pill',
        'card_style' => 'soft',
      ),
    ),
    'fonts' => 
    array (
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
    ),
    'button_styles' => 
    array (
      'rounded' => 'Заокруглені',
      'pill' => 'Овальні',
      'square' => 'Квадратні',
      'sharp' => 'Гострі кути',
    ),
    'hero_types' => 
    array (
      'image' => 
      array (
        'name' => 'Зображення',
        'description' => 'Фонове зображення з накладенням',
      ),
      'video' => 
      array (
        'name' => 'Відео',
        'description' => 'YouTube або Vimeo відео на фоні',
      ),
      'gradient' => 
      array (
        'name' => 'Градієнт',
        'description' => 'Динамічний градієнт з основного кольору',
      ),
      'slideshow' => 
      array (
        'name' => 'Слайд-шоу',
        'description' => 'Декілька зображень, що змінюються',
      ),
      'split' => 
      array (
        'name' => 'Розділений',
        'description' => '50/50 текст і зображення',
      ),
    ),
    'navigation_styles' => 
    array (
      'transparent' => 'Прозора (поверх hero)',
      'solid' => 'Суцільна',
      'minimal' => 'Мінімальна',
    ),
    'sections' => 
    array (
      'hero' => 
      array (
        'name' => 'Hero секція',
        'icon' => 'heroicons-image',
        'description' => 'Головна секція з заголовком та CTA',
      ),
      'service_times' => 
      array (
        'name' => 'Розклад служінь',
        'icon' => 'heroicons-clock',
        'description' => 'Час та місце богослужінь',
      ),
      'about' => 
      array (
        'name' => 'Про нас',
        'icon' => 'heroicons-information-circle',
        'description' => 'Місія, візія, цінності, історія',
      ),
      'pastor_message' => 
      array (
        'name' => 'Слово пастора',
        'icon' => 'heroicons-chat-bubble-left-right',
        'description' => 'Привітання від пастора',
      ),
      'leadership' => 
      array (
        'name' => 'Команда лідерів',
        'icon' => 'heroicons-user-group',
        'description' => 'Пастори, лідери, персонал',
      ),
      'events' => 
      array (
        'name' => 'Події',
        'icon' => 'heroicons-calendar',
        'description' => 'Найближчі події церкви',
      ),
      'sermons' => 
      array (
        'name' => 'Проповіді',
        'icon' => 'heroicons-play-circle',
        'description' => 'Відео та аудіо проповіді',
      ),
      'ministries' => 
      array (
        'name' => 'Служіння',
        'icon' => 'heroicons-heart',
        'description' => 'Напрямки служінь церкви',
      ),
      'groups' => 
      array (
        'name' => 'Малі групи',
        'icon' => 'heroicons-users',
        'description' => 'Домашні групи та спілкування',
      ),
      'gallery' => 
      array (
        'name' => 'Галерея',
        'icon' => 'heroicons-photo',
        'description' => 'Фотоальбоми з життя церкви',
      ),
      'testimonials' => 
      array (
        'name' => 'Свідчення',
        'icon' => 'heroicons-chat-bubble-bottom-center-text',
        'description' => 'Історії членів церкви',
      ),
      'blog' => 
      array (
        'name' => 'Блог',
        'icon' => 'heroicons-newspaper',
        'description' => 'Новини та статті',
      ),
      'faq' => 
      array (
        'name' => 'FAQ',
        'icon' => 'heroicons-question-mark-circle',
        'description' => 'Часті питання',
      ),
      'donations' => 
      array (
        'name' => 'Пожертви',
        'icon' => 'heroicons-currency-dollar',
        'description' => 'Збори та пожертви',
      ),
      'contact' => 
      array (
        'name' => 'Контакти',
        'icon' => 'heroicons-map-pin',
        'description' => 'Адреса, телефон, карта',
      ),
    ),
  ),
  'queue' => 
  array (
    'default' => 'redis',
    'connections' => 
    array (
      'sync' => 
      array (
        'driver' => 'sync',
      ),
      'database' => 
      array (
        'driver' => 'database',
        'table' => 'jobs',
        'queue' => 'default',
        'retry_after' => 90,
        'after_commit' => false,
      ),
      'redis' => 
      array (
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => 'default',
        'retry_after' => 90,
        'block_for' => NULL,
        'after_commit' => false,
      ),
    ),
    'batching' => 
    array (
      'database' => 'mysql',
      'table' => 'job_batches',
    ),
    'failed' => 
    array (
      'driver' => 'database-uuids',
      'database' => 'mysql',
      'table' => 'failed_jobs',
    ),
  ),
  'security' => 
  array (
    'rate_limits' => 
    array (
      'login' => 
      array (
        'max_attempts' => 5,
        'decay_minutes' => 1,
      ),
      'register' => 
      array (
        'max_attempts' => 5,
        'decay_minutes' => 1,
      ),
      'password_reset' => 
      array (
        'max_attempts' => 3,
        'decay_minutes' => 1,
      ),
      'api' => 
      array (
        'max_attempts' => 120,
        'decay_minutes' => 1,
      ),
      'public_forms' => 
      array (
        'max_attempts' => 10,
        'decay_minutes' => 1,
      ),
    ),
    'password' => 
    array (
      'min_length' => 10,
      'require_uppercase' => true,
      'require_lowercase' => true,
      'require_numbers' => true,
      'require_special' => false,
      'check_common_passwords' => true,
    ),
    'session' => 
    array (
      'regenerate_on_login' => true,
      'invalidate_on_logout' => true,
      'lifetime_minutes' => '120',
      'expire_on_close' => false,
      'encrypt' => true,
      'same_site' => 'lax',
    ),
    'headers' => 
    array (
      'x_frame_options' => 'SAMEORIGIN',
      'x_content_type_options' => 'nosniff',
      'x_xss_protection' => '1; mode=block',
      'referrer_policy' => 'strict-origin-when-cross-origin',
      'hsts_max_age' => 31536000,
      'hsts_include_subdomains' => true,
    ),
    'uploads' => 
    array (
      'allowed_image_types' => 
      array (
        0 => 'jpg',
        1 => 'jpeg',
        2 => 'png',
        3 => 'gif',
        4 => 'webp',
      ),
      'allowed_document_types' => 
      array (
        0 => 'pdf',
        1 => 'doc',
        2 => 'docx',
        3 => 'xls',
        4 => 'xlsx',
        5 => 'csv',
      ),
      'max_image_size_kb' => 2048,
      'max_document_size_kb' => 10240,
      'sanitize_filenames' => true,
    ),
    'encrypted_fields' => 
    array (
      'churches' => 
      array (
        0 => 'telegram_bot_token',
      ),
    ),
    'audit' => 
    array (
      'log_logins' => true,
      'log_logouts' => true,
      'log_failed_logins' => true,
      'log_password_changes' => true,
      'log_permission_changes' => true,
      'log_sensitive_data_access' => true,
    ),
    'allowed_hosts' => 
    array (
      0 => 'http://localhost',
    ),
    'debug_warning' => NULL,
  ),
  'services' => 
  array (
    'telegram' => 
    array (
      'bot_token' => '',
    ),
    'vapid' => 
    array (
      'public_key' => NULL,
      'private_key' => NULL,
      'subject' => 'mailto:admin@ministrify.app',
    ),
    'liqpay' => 
    array (
      'public_key' => NULL,
      'private_key' => NULL,
      'sandbox' => false,
    ),
    'twilio' => 
    array (
      'sid' => NULL,
      'token' => NULL,
      'from' => NULL,
    ),
    'google' => 
    array (
      'client_id' => NULL,
      'client_secret' => NULL,
      'redirect_uri' => NULL,
    ),
  ),
  'session' => 
  array (
    'driver' => 'redis',
    'lifetime' => '120',
    'expire_on_close' => false,
    'encrypt' => false,
    'files' => '/var/www/html/storage/framework/sessions',
    'connection' => NULL,
    'table' => 'sessions',
    'store' => NULL,
    'lottery' => 
    array (
      0 => 2,
      1 => 100,
    ),
    'cookie' => 'ministrify_session',
    'path' => '/',
    'domain' => NULL,
    'secure' => NULL,
    'http_only' => true,
    'same_site' => 'lax',
    'partitioned' => false,
  ),
  'view' => 
  array (
    'paths' => 
    array (
      0 => '/var/www/html/resources/views',
    ),
    'compiled' => '/var/www/html/storage/framework/views',
  ),
  'sanctum' => 
  array (
    'stateful' => 
    array (
      0 => 'localhost',
      1 => 'localhost:3000',
      2 => '127.0.0.1',
      3 => '127.0.0.1:8000',
      4 => '::1',
      5 => 'localhost',
    ),
    'guard' => 
    array (
      0 => 'web',
    ),
    'expiration' => NULL,
    'token_prefix' => '',
    'middleware' => 
    array (
      'authenticate_session' => 'Laravel\\Sanctum\\Http\\Middleware\\AuthenticateSession',
      'encrypt_cookies' => 'App\\Http\\Middleware\\EncryptCookies',
      'verify_csrf_token' => 'App\\Http\\Middleware\\VerifyCsrfToken',
    ),
  ),
  'livewire' => 
  array (
    'class_namespace' => 'App\\Livewire',
    'view_path' => '/var/www/html/resources/views/livewire',
    'layout' => 'components.layouts.app',
    'lazy_placeholder' => NULL,
    'temporary_file_upload' => 
    array (
      'disk' => NULL,
      'rules' => NULL,
      'directory' => NULL,
      'middleware' => NULL,
      'preview_mimes' => 
      array (
        0 => 'png',
        1 => 'gif',
        2 => 'bmp',
        3 => 'svg',
        4 => 'wav',
        5 => 'mp4',
        6 => 'mov',
        7 => 'avi',
        8 => 'wmv',
        9 => 'mp3',
        10 => 'm4a',
        11 => 'jpg',
        12 => 'jpeg',
        13 => 'mpga',
        14 => 'webp',
        15 => 'wma',
      ),
      'max_upload_time' => 5,
      'cleanup' => true,
    ),
    'render_on_redirect' => false,
    'legacy_model_binding' => false,
    'inject_assets' => true,
    'navigate' => 
    array (
      'show_progress_bar' => true,
      'progress_bar_color' => '#2299dd',
    ),
    'inject_morph_markers' => true,
    'smart_wire_keys' => false,
    'pagination_theme' => 'tailwind',
    'release_token' => 'a',
  ),
  'excel' => 
  array (
    'exports' => 
    array (
      'chunk_size' => 1000,
      'pre_calculate_formulas' => false,
      'strict_null_comparison' => false,
      'csv' => 
      array (
        'delimiter' => ',',
        'enclosure' => '"',
        'line_ending' => '
',
        'use_bom' => false,
        'include_separator_line' => false,
        'excel_compatibility' => false,
        'output_encoding' => '',
        'test_auto_detect' => true,
      ),
      'properties' => 
      array (
        'creator' => '',
        'lastModifiedBy' => '',
        'title' => '',
        'description' => '',
        'subject' => '',
        'keywords' => '',
        'category' => '',
        'manager' => '',
        'company' => '',
      ),
    ),
    'imports' => 
    array (
      'read_only' => true,
      'ignore_empty' => false,
      'heading_row' => 
      array (
        'formatter' => 'slug',
      ),
      'csv' => 
      array (
        'delimiter' => NULL,
        'enclosure' => '"',
        'escape_character' => '\\',
        'contiguous' => false,
        'input_encoding' => 'guess',
      ),
      'properties' => 
      array (
        'creator' => '',
        'lastModifiedBy' => '',
        'title' => '',
        'description' => '',
        'subject' => '',
        'keywords' => '',
        'category' => '',
        'manager' => '',
        'company' => '',
      ),
      'cells' => 
      array (
        'middleware' => 
        array (
        ),
      ),
    ),
    'extension_detector' => 
    array (
      'xlsx' => 'Xlsx',
      'xlsm' => 'Xlsx',
      'xltx' => 'Xlsx',
      'xltm' => 'Xlsx',
      'xls' => 'Xls',
      'xlt' => 'Xls',
      'ods' => 'Ods',
      'ots' => 'Ods',
      'slk' => 'Slk',
      'xml' => 'Xml',
      'gnumeric' => 'Gnumeric',
      'htm' => 'Html',
      'html' => 'Html',
      'csv' => 'Csv',
      'tsv' => 'Csv',
      'pdf' => 'Dompdf',
    ),
    'value_binder' => 
    array (
      'default' => 'Maatwebsite\\Excel\\DefaultValueBinder',
    ),
    'cache' => 
    array (
      'driver' => 'memory',
      'batch' => 
      array (
        'memory_limit' => 60000,
      ),
      'illuminate' => 
      array (
        'store' => NULL,
      ),
      'default_ttl' => 10800,
    ),
    'transactions' => 
    array (
      'handler' => 'db',
      'db' => 
      array (
        'connection' => NULL,
      ),
    ),
    'temporary_files' => 
    array (
      'local_path' => '/var/www/html/storage/framework/cache/laravel-excel',
      'local_permissions' => 
      array (
      ),
      'remote_disk' => NULL,
      'remote_prefix' => NULL,
      'force_resync_remote' => NULL,
    ),
  ),
  'flare' => 
  array (
    'key' => NULL,
    'flare_middleware' => 
    array (
      0 => 'Spatie\\FlareClient\\FlareMiddleware\\RemoveRequestIp',
      1 => 'Spatie\\FlareClient\\FlareMiddleware\\AddGitInformation',
      2 => 'Spatie\\LaravelIgnition\\FlareMiddleware\\AddNotifierName',
      3 => 'Spatie\\LaravelIgnition\\FlareMiddleware\\AddEnvironmentInformation',
      4 => 'Spatie\\LaravelIgnition\\FlareMiddleware\\AddExceptionInformation',
      5 => 'Spatie\\LaravelIgnition\\FlareMiddleware\\AddDumps',
      'Spatie\\LaravelIgnition\\FlareMiddleware\\AddLogs' => 
      array (
        'maximum_number_of_collected_logs' => 200,
      ),
      'Spatie\\LaravelIgnition\\FlareMiddleware\\AddQueries' => 
      array (
        'maximum_number_of_collected_queries' => 200,
        'report_query_bindings' => true,
      ),
      'Spatie\\LaravelIgnition\\FlareMiddleware\\AddJobs' => 
      array (
        'max_chained_job_reporting_depth' => 5,
      ),
      6 => 'Spatie\\LaravelIgnition\\FlareMiddleware\\AddContext',
      7 => 'Spatie\\LaravelIgnition\\FlareMiddleware\\AddExceptionHandledStatus',
      'Spatie\\FlareClient\\FlareMiddleware\\CensorRequestBodyFields' => 
      array (
        'censor_fields' => 
        array (
          0 => 'password',
          1 => 'password_confirmation',
        ),
      ),
      'Spatie\\FlareClient\\FlareMiddleware\\CensorRequestHeaders' => 
      array (
        'headers' => 
        array (
          0 => 'API-KEY',
          1 => 'Authorization',
          2 => 'Cookie',
          3 => 'Set-Cookie',
          4 => 'X-CSRF-TOKEN',
          5 => 'X-XSRF-TOKEN',
        ),
      ),
    ),
    'send_logs_as_events' => true,
  ),
  'ignition' => 
  array (
    'editor' => 'phpstorm',
    'theme' => 'auto',
    'enable_share_button' => true,
    'register_commands' => false,
    'solution_providers' => 
    array (
      0 => 'Spatie\\Ignition\\Solutions\\SolutionProviders\\BadMethodCallSolutionProvider',
      1 => 'Spatie\\Ignition\\Solutions\\SolutionProviders\\MergeConflictSolutionProvider',
      2 => 'Spatie\\Ignition\\Solutions\\SolutionProviders\\UndefinedPropertySolutionProvider',
      3 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\IncorrectValetDbCredentialsSolutionProvider',
      4 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\MissingAppKeySolutionProvider',
      5 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\DefaultDbNameSolutionProvider',
      6 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\TableNotFoundSolutionProvider',
      7 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\MissingImportSolutionProvider',
      8 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\InvalidRouteActionSolutionProvider',
      9 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\ViewNotFoundSolutionProvider',
      10 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\RunningLaravelDuskInProductionProvider',
      11 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\MissingColumnSolutionProvider',
      12 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\UnknownValidationSolutionProvider',
      13 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\MissingMixManifestSolutionProvider',
      14 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\MissingViteManifestSolutionProvider',
      15 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\MissingLivewireComponentSolutionProvider',
      16 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\UndefinedViewVariableSolutionProvider',
      17 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\GenericLaravelExceptionSolutionProvider',
      18 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\OpenAiSolutionProvider',
      19 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\SailNetworkSolutionProvider',
      20 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\UnknownMysql8CollationSolutionProvider',
      21 => 'Spatie\\LaravelIgnition\\Solutions\\SolutionProviders\\UnknownMariadbCollationSolutionProvider',
    ),
    'ignored_solution_providers' => 
    array (
    ),
    'enable_runnable_solutions' => NULL,
    'remote_sites_path' => '/var/www/html',
    'local_sites_path' => '',
    'housekeeping_endpoint_prefix' => '_ignition',
    'settings_file_path' => '',
    'recorders' => 
    array (
      0 => 'Spatie\\LaravelIgnition\\Recorders\\DumpRecorder\\DumpRecorder',
      1 => 'Spatie\\LaravelIgnition\\Recorders\\JobRecorder\\JobRecorder',
      2 => 'Spatie\\LaravelIgnition\\Recorders\\LogRecorder\\LogRecorder',
      3 => 'Spatie\\LaravelIgnition\\Recorders\\QueryRecorder\\QueryRecorder',
    ),
    'open_ai_key' => NULL,
    'with_stack_frame_arguments' => true,
    'argument_reducers' => 
    array (
      0 => 'Spatie\\Backtrace\\Arguments\\Reducers\\BaseTypeArgumentReducer',
      1 => 'Spatie\\Backtrace\\Arguments\\Reducers\\ArrayArgumentReducer',
      2 => 'Spatie\\Backtrace\\Arguments\\Reducers\\StdClassArgumentReducer',
      3 => 'Spatie\\Backtrace\\Arguments\\Reducers\\EnumArgumentReducer',
      4 => 'Spatie\\Backtrace\\Arguments\\Reducers\\ClosureArgumentReducer',
      5 => 'Spatie\\Backtrace\\Arguments\\Reducers\\DateTimeArgumentReducer',
      6 => 'Spatie\\Backtrace\\Arguments\\Reducers\\DateTimeZoneArgumentReducer',
      7 => 'Spatie\\Backtrace\\Arguments\\Reducers\\SymphonyRequestArgumentReducer',
      8 => 'Spatie\\LaravelIgnition\\ArgumentReducers\\ModelArgumentReducer',
      9 => 'Spatie\\LaravelIgnition\\ArgumentReducers\\CollectionArgumentReducer',
      10 => 'Spatie\\Backtrace\\Arguments\\Reducers\\StringableArgumentReducer',
    ),
  ),
  'tinker' => 
  array (
    'commands' => 
    array (
    ),
    'alias' => 
    array (
    ),
    'dont_alias' => 
    array (
      0 => 'App\\Nova',
    ),
  ),
);
