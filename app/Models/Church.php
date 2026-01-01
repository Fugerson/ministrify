<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

class Church extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'city',
        'address',
        'logo',
        'primary_color',
        'theme',
        'design_theme',
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
        'initial_balance',
        'initial_balance_date',
    ];

    protected $casts = [
        'settings' => 'array',
        'payment_settings' => 'array',
        'public_site_settings' => 'array',
        'public_site_enabled' => 'boolean',
        'shepherds_enabled' => 'boolean',
        'initial_balance' => 'decimal:2',
        'initial_balance_date' => 'date',
        'subscription_ends_at' => 'datetime',
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
     * Fields that should be hidden from serialization
     */
    protected $hidden = [
        'telegram_bot_token',
        'calendar_token',
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

        // Sanitize: remove @import, javascript:, expression()
        $css = preg_replace('/@import\s+/i', '', $css);
        $css = preg_replace('/javascript\s*:/i', '', $css);
        $css = preg_replace('/expression\s*\(/i', '', $css);

        return $css;
    }

    // ========== Subscription Methods ==========

    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get current plan (or free plan as fallback)
     */
    public function getPlanAttribute(): ?SubscriptionPlan
    {
        return $this->subscriptionPlan ?? SubscriptionPlan::free();
    }

    /**
     * Check if subscription is active
     */
    public function hasActiveSubscription(): bool
    {
        // Free plan is always active
        if (!$this->subscriptionPlan || $this->subscriptionPlan->isFree()) {
            return true;
        }

        return $this->subscription_ends_at && $this->subscription_ends_at->isFuture();
    }

    /**
     * Check if subscription is expiring soon (within 7 days)
     */
    public function isSubscriptionExpiringSoon(): bool
    {
        if (!$this->subscription_ends_at) {
            return false;
        }

        return $this->subscription_ends_at->isBetween(now(), now()->addDays(7));
    }

    /**
     * Check if subscription has expired
     */
    public function isSubscriptionExpired(): bool
    {
        if (!$this->subscriptionPlan || $this->subscriptionPlan->isFree()) {
            return false;
        }

        return $this->subscription_ends_at && $this->subscription_ends_at->isPast();
    }

    /**
     * Days left in subscription
     */
    public function getSubscriptionDaysLeftAttribute(): ?int
    {
        if (!$this->subscription_ends_at) {
            return null;
        }

        return max(0, now()->diffInDays($this->subscription_ends_at, false));
    }

    /**
     * Check if church has access to a feature
     */
    public function hasFeature(string $feature): bool
    {
        $plan = $this->plan;
        if (!$plan) {
            return false;
        }

        // If subscription expired, use free plan features
        if ($this->isSubscriptionExpired()) {
            $plan = SubscriptionPlan::free();
        }

        return $plan ? $plan->hasFeature($feature) : false;
    }

    /**
     * Get limit for a resource
     */
    public function getLimit(string $resource): int
    {
        $plan = $this->plan;
        if (!$plan) {
            return 0;
        }

        // If subscription expired, use free plan limits
        if ($this->isSubscriptionExpired()) {
            $plan = SubscriptionPlan::free();
        }

        return $plan ? $plan->getLimit($resource) : 0;
    }

    /**
     * Check if church can add more of a resource
     */
    public function canAdd(string $resource): bool
    {
        $limit = $this->getLimit($resource);

        // 0 means unlimited
        if ($limit === 0) {
            return true;
        }

        $current = match($resource) {
            'people' => $this->people()->count(),
            'ministries' => $this->ministries()->count(),
            'users' => $this->users()->count(),
            'events_per_month' => $this->events()->whereMonth('date', now()->month)->count(),
            default => 0,
        };

        return $current < $limit;
    }

    /**
     * Get usage stats for billing page
     */
    public function getUsageStats(): array
    {
        $plan = $this->plan;

        return [
            'people' => [
                'current' => $this->people()->count(),
                'limit' => $plan ? $plan->max_people : 0,
            ],
            'ministries' => [
                'current' => $this->ministries()->count(),
                'limit' => $plan ? $plan->max_ministries : 0,
            ],
            'users' => [
                'current' => $this->users()->count(),
                'limit' => $plan ? $plan->max_users : 0,
            ],
            'events_per_month' => [
                'current' => $this->events()->whereMonth('date', now()->month)->count(),
                'limit' => $plan ? $plan->max_events_per_month : 0,
            ],
        ];
    }

    /**
     * Extend subscription by a period
     */
    public function extendSubscription(string $period = 'monthly'): void
    {
        $days = $period === 'yearly' ? 365 : 30;

        $startFrom = $this->subscription_ends_at && $this->subscription_ends_at->isFuture()
            ? $this->subscription_ends_at
            : now();

        $this->update([
            'subscription_ends_at' => $startFrom->addDays($days),
            'billing_period' => $period,
        ]);
    }

    /**
     * Upgrade to a new plan
     */
    public function upgradeToPlan(SubscriptionPlan $plan, string $period = 'monthly'): void
    {
        $this->update([
            'subscription_plan_id' => $plan->id,
            'billing_period' => $period,
        ]);

        if (!$plan->isFree()) {
            $this->extendSubscription($period);
        }
    }
}
