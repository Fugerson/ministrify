<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonobankSenderMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'church_id',
        'sender_iban',
        'sender_name',
        'category_id',
        'person_id',
        'times_used',
    ];

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TransactionCategory::class, 'category_id');
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    /**
     * Find or create mapping for a sender
     */
    public static function findForSender(int $churchId, ?string $iban, ?string $name): ?self
    {
        if ($iban) {
            $mapping = self::where('church_id', $churchId)
                ->where('sender_iban', $iban)
                ->first();
            if ($mapping) return $mapping;
        }

        if ($name) {
            return self::where('church_id', $churchId)
                ->where('sender_name', $name)
                ->first();
        }

        return null;
    }

    /**
     * Update or create mapping when transaction is imported
     */
    public static function updateFromImport(
        int $churchId,
        ?string $iban,
        ?string $name,
        int $categoryId,
        ?int $personId = null
    ): self {
        if (!$iban && !$name) {
            // Cannot create mapping without identifier — skip
            return new self([
                'church_id' => $churchId,
                'category_id' => $categoryId,
                'person_id' => $personId,
            ]);
        }

        $mapping = self::where('church_id', $churchId)
            ->where(function ($q) use ($iban, $name) {
                if ($iban) $q->where('sender_iban', $iban);
                else $q->where('sender_name', $name);
            })
            ->first();

        if ($mapping) {
            $mapping->increment('times_used');
            $mapping->update([
                'category_id' => $categoryId,
                'person_id' => $personId,
            ]);
            return $mapping;
        }

        return self::create([
            'church_id' => $churchId,
            'sender_iban' => $iban,
            'sender_name' => $name,
            'category_id' => $categoryId,
            'person_id' => $personId,
        ]);
    }
}
