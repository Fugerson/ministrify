<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use App\Traits\Auditable;

class Church extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected static function booted(): void
    {
        static::created(function (Church $church) {
            ChurchRole::createDefaultsForChurch($church->id);
        });
    }

    protected $fillable = [
        'name',
        'slug',
        'city',
        'address',
        'logo',
        'primary_color',
        'design_theme',
        'menu_position',
        'telegram_bot_token',
        'settings',
        'payment_settings',
        'calendar_token',
        'public_site_enabled',
        'public_template',
        'public_site_settings',
        'public_description',
        'public_email',
        'public_phone',
        'website_url',
        'facebook_url',
        'instagram_url',
        'youtube_url',
        'service_times',
        'cover_image',
        'pastor_name',
        'pastor_photo',
        'pastor_message',
        'shepherds_enabled',
        'attendance_enabled',
        'initial_balance',
        'initial_balance_date',
        'initial_balances',
        'monobank_token',
        'monobank_account_id',
        'monobank_auto_sync',
        'monobank_last_sync',
        'monobank_webhook_secret',
        'privatbank_merchant_id',
        'privatbank_password',
        'privatbank_card_number',
        'privatbank_auto_sync',
        'privatbank_last_sync',
        'enabled_currencies',
    ];

    protected $casts = [
        'settings' => 'array',
        'payment_settings' => 'array',
        'public_site_settings' => 'array',
        'public_site_enabled' => 'boolean',
        'shepherds_enabled' => 'boolean',
        'attendance_enabled' => 'boolean',
        'initial_balance' => 'decimal:2',
        'initial_balance_date' => 'date',
        'initial_balances' => 'array',
        'monobank_auto_sync' => 'boolean',
        'monobank_last_sync' => 'datetime',
        'privatbank_auto_sync' => 'boolean',
        'privatbank_last_sync' => 'datetime',
        'enabled_currencies' => 'array',
    ];

    /**
     * Get telegram bot token (with decryption error handling)
     */
    public function getTelegramBotTokenAttribute($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException $e) {
            // Token was encrypted with different key, clear it
            $this->attributes['telegram_bot_token'] = null;
            $this->saveQuietly();
            return null;
        }
    }

    /**
     * Set telegram bot token (encrypted)
     */
    public function setTelegramBotTokenAttribute($value): void
    {
        $this->attributes['telegram_bot_token'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Get monobank token (with decryption error handling)
     */
    public function getMonobankTokenAttribute($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException $e) {
            $this->attributes['monobank_token'] = null;
            $this->saveQuietly();
            return null;
        }
    }

    /**
     * Set monobank token (encrypted)
     */
    public function setMonobankTokenAttribute($value): void
    {
        $this->attributes['monobank_token'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Get privatbank password (with decryption error handling)
     */
    public function getPrivatbankPasswordAttribute($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException $e) {
            $this->attributes['privatbank_password'] = null;
            $this->saveQuietly();
            return null;
        }
    }

    /**
     * Set privatbank password (encrypted)
     */
    public function setPrivatbankPasswordAttribute($value): void
    {
        $this->attributes['privatbank_password'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Fields that should be hidden from serialization
     */
    protected $hidden = [
        'telegram_bot_token',
        'calendar_token',
        'monobank_token',
        'privatbank_merchant_id',
        'privatbank_password',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function people(): HasMany
    {
        return $this->hasMany(Person::class);
    }

    public function ministries(): HasMany
    {
        return $this->hasMany(Ministry::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class);
    }

    public function expenseCategories(): HasMany
    {
        return $this->hasMany(ExpenseCategory::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }

    public function checklistTemplates(): HasMany
    {
        return $this->hasMany(ChecklistTemplate::class);
    }

    public function messageTemplates(): HasMany
    {
        return $this->hasMany(MessageTemplate::class);
    }

    public function boards(): HasMany
    {
        return $this->hasMany(Board::class);
    }

    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }

    public function donationCampaigns(): HasMany
    {
        return $this->hasMany(DonationCampaign::class);
    }

    public function ministryTypes(): HasMany
    {
        return $this->hasMany(MinistryType::class)->orderBy('sort_order');
    }

    public function eventRegistrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    // Website Builder relationships
    public function staffMembers(): HasMany
    {
        return $this->hasMany(StaffMember::class)->orderBy('sort_order');
    }

    public function sermons(): HasMany
    {
        return $this->hasMany(Sermon::class);
    }

    public function sermonSeries(): HasMany
    {
        return $this->hasMany(SermonSeries::class);
    }

    public function galleries(): HasMany
    {
        return $this->hasMany(Gallery::class);
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(Faq::class);
    }

    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class);
    }

    public function blogPosts(): HasMany
    {
        return $this->hasMany(BlogPost::class);
    }

    public function blogCategories(): HasMany
    {
        return $this->hasMany(BlogCategory::class);
    }

    public function churchRoles(): HasMany
    {
        return $this->hasMany(ChurchRole::class);
    }

    /**
     * Get cached ministries (TTL 600s / 10 min).
     * Call Church::clearMinistriesCache($churchId) on ministry CRUD.
     */
    public function getCachedMinistries(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember(
            "church:{$this->id}:ministries",
            600,
            fn () => $this->ministries()->orderBy('name')->get()
        );
    }

    /**
     * Clear the cached ministries for a given church.
     */
    public static function clearMinistriesCache(int $churchId): void
    {
        Cache::forget("church:{$churchId}:ministries");
    }

    public function getPublicEventsAttribute()
    {
        return $this->events()
            ->where('is_public', true)
            ->where('date', '>=', now()->startOfDay())
            ->orderBy('date')
            ->get();
    }

    public function getPublicMinistriesAttribute()
    {
        return $this->ministries()
            ->where('is_public', true)
            ->get();
    }

    public function getPublicGroupsAttribute()
    {
        return $this->groups()
            ->where('is_public', true)
            ->get();
    }

    public function getPublicUrlAttribute(): string
    {
        return route('public.church', $this->slug);
    }

    public static function findBySlug(string $slug)
    {
        return static::where('slug', $slug)
            ->where('public_site_enabled', true)
            ->first();
    }

    public function getThemeColorsAttribute(): array
    {
        $color = $this->primary_color ?? '#3b82f6';
        return [
            '50' => $this->adjustBrightness($color, 0.95),
            '100' => $this->adjustBrightness($color, 0.9),
            '200' => $this->adjustBrightness($color, 0.75),
            '300' => $this->adjustBrightness($color, 0.6),
            '400' => $this->adjustBrightness($color, 0.3),
            '500' => $color,
            '600' => $this->adjustBrightness($color, -0.1),
            '700' => $this->adjustBrightness($color, -0.25),
            '800' => $this->adjustBrightness($color, -0.4),
            '900' => $this->adjustBrightness($color, -0.55),
        ];
    }

    private function adjustBrightness(string $hex, float $percent): string
    {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        if ($percent > 0) {
            $r = $r + (255 - $r) * $percent;
            $g = $g + (255 - $g) * $percent;
            $b = $b + (255 - $b) * $percent;
        } else {
            $r = $r * (1 + $percent);
            $g = $g * (1 + $percent);
            $b = $b * (1 + $percent);
        }

        return sprintf('#%02x%02x%02x', max(0, min(255, $r)), max(0, min(255, $g)), max(0, min(255, $b)));
    }

    public function getSetting(string $key, $default = null)
    {
        return data_get($this->settings, $key, $default);
    }

    public function setSetting(string $key, $value): void
    {
        $settings = $this->settings ?? [];
        data_set($settings, $key, $value);
        $this->settings = $settings;
        $this->save();
    }

    /**
     * Get or generate calendar token
     */
    public function getCalendarToken(): string
    {
        if (!$this->calendar_token) {
            $this->calendar_token = \Illuminate\Support\Str::random(32);
            $this->save();
        }
        return $this->calendar_token;
    }

    /**
     * Get calendar feed URL
     */
    public function getCalendarFeedUrlAttribute(): string
    {
        return route('api.calendar.feed', ['token' => $this->getCalendarToken()]);
    }

    /**
     * Regenerate calendar token
     */
    public function regenerateCalendarToken(): string
    {
        $this->calendar_token = \Illuminate\Support\Str::random(32);
        $this->save();
        return $this->calendar_token;
    }

    /**
     * Get total income from transactions
     */
    public function getTotalIncomeAttribute(): float
    {
        return (float) Transaction::where('church_id', $this->id)
            ->incoming()
            ->completed()
            ->sum('amount');
    }

    /**
     * Get total expenses from transactions
     */
    public function getTotalExpenseAttribute(): float
    {
        return (float) Transaction::where('church_id', $this->id)
            ->outgoing()
            ->completed()
            ->sum('amount');
    }

    /**
     * Get current balance (initial + income - expenses)
     */
    public function getCurrentBalanceAttribute(): float
    {
        return (float) $this->initial_balance + $this->total_income - $this->total_expense;
    }

    /**
     * Get balance breakdown
     */
    public function getBalanceBreakdown(): array
    {
        return [
            'initial_balance' => (float) $this->initial_balance,
            'initial_balance_date' => $this->initial_balance_date,
            'total_income' => $this->total_income,
            'total_expense' => $this->total_expense,
            'current_balance' => $this->current_balance,
        ];
    }

    /**
     * Get initial balance for a specific currency
     */
    public function getInitialBalanceForCurrency(string $currency): float
    {
        // Try new JSON field first
        if ($this->initial_balances && isset($this->initial_balances[$currency])) {
            return (float) $this->initial_balances[$currency];
        }

        // Fallback to old field for UAH (backwards compatibility)
        if ($currency === 'UAH' && $this->initial_balance) {
            return (float) $this->initial_balance;
        }

        return 0.0;
    }

    /**
     * Get all initial balances (multi-currency)
     */
    public function getAllInitialBalances(): array
    {
        // If new field exists, use it
        if ($this->initial_balances) {
            return $this->initial_balances;
        }

        // Fallback to old field
        if ($this->initial_balance) {
            return ['UAH' => (float) $this->initial_balance];
        }

        return [];
    }

    // ========== Website Builder Public Getters ==========

    public function getPublicStaffAttribute()
    {
        return $this->staffMembers()
            ->where('is_public', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function getPublicSermonsAttribute()
    {
        return $this->sermons()
            ->where('is_public', true)
            ->orderByDesc('sermon_date')
            ->limit(6)
            ->get();
    }

    public function getPublicGalleriesAttribute()
    {
        return $this->galleries()
            ->where('is_public', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function getPublicFaqsAttribute()
    {
        return $this->faqs()
            ->where('is_public', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function getPublicTestimonialsAttribute()
    {
        return $this->testimonials()
            ->where('is_public', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function getPublicBlogPostsAttribute()
    {
        return $this->blogPosts()
            ->published()
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();
    }

    // ========== Public Site Settings Helpers ==========

    public function getPublicSiteSetting(string $key, $default = null)
    {
        return data_get($this->public_site_settings, $key, $default);
    }

    public function setPublicSiteSetting(string $key, $value): void
    {
        $settings = $this->public_site_settings ?? [];
        data_set($settings, $key, $value);
        $this->public_site_settings = $settings;
        $this->save();
    }

    public function getActiveTemplateAttribute(): string
    {
        return $this->public_template ?? 'modern';
    }

    public function getEnabledSectionsAttribute(): array
    {
        $sections = $this->getPublicSiteSetting('sections', $this->getDefaultSections());
        return collect($sections)
            ->filter(fn($s) => $s['enabled'] ?? false)
            ->sortBy('order')
            ->values()
            ->all();
    }

    public function getDefaultSections(): array
    {
        return [
            ['id' => 'hero', 'enabled' => true, 'order' => 0],
            ['id' => 'service_times', 'enabled' => true, 'order' => 1],
            ['id' => 'about', 'enabled' => false, 'order' => 2],
            ['id' => 'pastor_message', 'enabled' => true, 'order' => 3],
            ['id' => 'leadership', 'enabled' => false, 'order' => 4],
            ['id' => 'events', 'enabled' => true, 'order' => 5],
            ['id' => 'sermons', 'enabled' => false, 'order' => 6],
            ['id' => 'ministries', 'enabled' => true, 'order' => 7],
            ['id' => 'groups', 'enabled' => true, 'order' => 8],
            ['id' => 'gallery', 'enabled' => false, 'order' => 9],
            ['id' => 'testimonials', 'enabled' => false, 'order' => 10],
            ['id' => 'blog', 'enabled' => false, 'order' => 11],
            ['id' => 'faq', 'enabled' => false, 'order' => 12],
            ['id' => 'donations', 'enabled' => true, 'order' => 13],
            ['id' => 'contact', 'enabled' => true, 'order' => 14],
        ];
    }

    public function getTemplateConfig(): array
    {
        return config("public_site_templates.templates.{$this->active_template}", []);
    }

    public function getSiteColorsAttribute(): array
    {
        $defaultColors = [
            'primary' => $this->primary_color ?? '#3b82f6',
            'secondary' => '#10b981',
            'accent' => '#f59e0b',
            'background' => '#ffffff',
            'text' => '#1f2937',
            'heading' => '#111827',
        ];

        return array_merge($defaultColors, $this->getPublicSiteSetting('colors', []));
    }

    public function getSiteFontsAttribute(): array
    {
        $templateConfig = $this->getTemplateConfig();
        $defaultFonts = $templateConfig['fonts'] ?? ['heading' => 'Inter', 'body' => 'Inter'];

        return array_merge($defaultFonts, $this->getPublicSiteSetting('fonts', []));
    }

    public function getHeroSettingsAttribute(): array
    {
        $templateConfig = $this->getTemplateConfig();
        $defaults = [
            'type' => $templateConfig['hero_default'] ?? 'image',
            'overlay_opacity' => 70,
            'text_alignment' => 'left',
            'show_cta' => true,
        ];

        return array_merge($defaults, $this->getPublicSiteSetting('hero', []));
    }

    public function getAboutContentAttribute(): array
    {
        return $this->getPublicSiteSetting('about', [
            'mission' => null,
            'vision' => null,
            'values' => [],
            'history' => null,
        ]);
    }

    public function getCustomCssAttribute(): ?string
    {
        $css = $this->getPublicSiteSetting('custom_css');
        if (!$css) return null;

        // Comprehensive CSS sanitization to prevent XSS attacks
        $dangerousPatterns = [
            // @import and vendor prefixes
            '/@-?(?:webkit-|moz-|ms-|o-)?import\b/i',
            // javascript: protocol (with possible obfuscation)
            '/j\s*a\s*v\s*a\s*s\s*c\s*r\s*i\s*p\s*t\s*:/i',
            // vbscript: protocol
            '/v\s*b\s*s\s*c\s*r\s*i\s*p\s*t\s*:/i',
            // expression() - IE specific
            '/expression\s*\(/i',
            // behavior: - IE specific
            '/behavior\s*:/i',
            // -moz-binding - Firefox specific
            '/-moz-binding\s*:/i',
            // data: URLs (can contain scripts)
            '/url\s*\(\s*["\']?\s*data\s*:/i',
        ];

        foreach ($dangerousPatterns as $pattern) {
            $css = preg_replace($pattern, '/* blocked */', $css);
        }

        return $css;
    }
}
