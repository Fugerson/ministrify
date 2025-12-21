<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Church extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'city',
        'address',
        'logo',
        'primary_color',
        'theme',
        'telegram_bot_token',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
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
}
