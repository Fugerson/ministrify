<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
    ];

    protected $casts = [
        'settings' => 'array',
        'payment_settings' => 'array',
        'public_site_enabled' => 'boolean',
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
}
