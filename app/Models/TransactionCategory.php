<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransactionCategory extends Model
{
    use HasFactory;

    public const TYPE_INCOME = 'income';
    public const TYPE_EXPENSE = 'expense';
    public const TYPE_BOTH = 'both';

    protected $fillable = [
        'church_id',
        'name',
        'type',
        'icon',
        'color',
        'receipt_required',
        'is_tithe',
        'is_offering',
        'is_donation',
        'is_system',
        'sort_order',
    ];

    protected $casts = [
        'is_tithe' => 'boolean',
        'is_offering' => 'boolean',
        'is_donation' => 'boolean',
        'is_system' => 'boolean',
        'receipt_required' => 'boolean',
    ];

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'category_id');
    }

    public function scopeForIncome($query)
    {
        return $query->whereIn('type', [self::TYPE_INCOME, self::TYPE_BOTH]);
    }

    public function scopeForExpense($query)
    {
        return $query->whereIn('type', [self::TYPE_EXPENSE, self::TYPE_BOTH]);
    }

    public function getTotalThisMonthAttribute(): float
    {
        return $this->transactions()
            ->completed()
            ->thisMonth()
            ->sum('amount');
    }

    public static function createDefaults(Church $church): void
    {
        $incomeCategories = [
            ['name' => 'Десятина', 'is_tithe' => true, 'icon' => 'heart', 'color' => '#10b981'],
            ['name' => 'Пожертва', 'is_offering' => true, 'icon' => 'gift', 'color' => '#3b82f6'],
            ['name' => 'Донат', 'is_donation' => true, 'icon' => 'star', 'color' => '#8b5cf6'],
            ['name' => 'Інше', 'icon' => 'dots-horizontal', 'color' => '#6b7280'],
        ];

        $expenseCategories = [
            ['name' => 'Оренда', 'icon' => 'home', 'color' => '#ef4444'],
            ['name' => 'Комунальні послуги', 'icon' => 'lightning-bolt', 'color' => '#f59e0b'],
            ['name' => 'Обладнання', 'icon' => 'desktop-computer', 'color' => '#6366f1'],
            ['name' => 'Служіння', 'icon' => 'users', 'color' => '#ec4899'],
            ['name' => 'Транспорт', 'icon' => 'truck', 'color' => '#14b8a6'],
            ['name' => 'Інше', 'icon' => 'dots-horizontal', 'color' => '#6b7280'],
        ];

        foreach ($incomeCategories as $i => $cat) {
            self::create([
                'church_id' => $church->id,
                'name' => $cat['name'],
                'type' => self::TYPE_INCOME,
                'icon' => $cat['icon'] ?? null,
                'color' => $cat['color'] ?? null,
                'is_tithe' => $cat['is_tithe'] ?? false,
                'is_offering' => $cat['is_offering'] ?? false,
                'is_donation' => $cat['is_donation'] ?? false,
                'is_system' => true,
                'sort_order' => $i,
            ]);
        }

        foreach ($expenseCategories as $i => $cat) {
            self::create([
                'church_id' => $church->id,
                'name' => $cat['name'],
                'type' => self::TYPE_EXPENSE,
                'icon' => $cat['icon'] ?? null,
                'color' => $cat['color'] ?? null,
                'is_system' => true,
                'sort_order' => $i,
            ]);
        }
    }
}
