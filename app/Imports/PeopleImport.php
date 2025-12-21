<?php

namespace App\Imports;

use App\Models\Person;
use App\Models\Tag;
use App\Models\Ministry;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Carbon\Carbon;

class PeopleImport implements ToModel, WithHeadingRow, WithValidation
{
    protected int $churchId;
    protected array $tagCache = [];
    protected array $ministryCache = [];

    public function __construct(int $churchId)
    {
        $this->churchId = $churchId;
        $this->loadCache();
    }

    protected function loadCache(): void
    {
        $this->tagCache = Tag::where('church_id', $this->churchId)
            ->pluck('id', 'name')
            ->toArray();

        $this->ministryCache = Ministry::where('church_id', $this->churchId)
            ->pluck('id', 'name')
            ->toArray();
    }

    public function model(array $row)
    {
        $person = Person::updateOrCreate(
            [
                'church_id' => $this->churchId,
                'first_name' => $row['imia'] ?? $row["im'ia"] ?? $row['first_name'] ?? '',
                'last_name' => $row['prizvyshche'] ?? $row['last_name'] ?? '',
            ],
            [
                'phone' => $row['telefon'] ?? $row['phone'] ?? null,
                'email' => $row['email'] ?? null,
                'telegram_username' => $row['telegram'] ?? null,
                'address' => $row['adresa'] ?? $row['address'] ?? null,
                'birth_date' => $this->parseDate($row['data_narodzhennia'] ?? $row['birth_date'] ?? null),
                'joined_date' => $this->parseDate($row['v_tserkvi_z'] ?? $row['joined_date'] ?? null),
                'notes' => $row['notatky'] ?? $row['notes'] ?? null,
            ]
        );

        // Sync tags
        if (!empty($row['tehy']) || !empty($row['tags'])) {
            $tagNames = array_map('trim', explode(',', $row['tehy'] ?? $row['tags'] ?? ''));
            $tagIds = [];
            foreach ($tagNames as $tagName) {
                if (!empty($tagName) && isset($this->tagCache[$tagName])) {
                    $tagIds[] = $this->tagCache[$tagName];
                }
            }
            $person->tags()->sync($tagIds);
        }

        // Sync ministries
        if (!empty($row['sluzhinnia']) || !empty($row['ministries'])) {
            $ministryNames = array_map('trim', explode(',', $row['sluzhinnia'] ?? $row['ministries'] ?? ''));
            $ministryIds = [];
            foreach ($ministryNames as $ministryName) {
                if (!empty($ministryName) && isset($this->ministryCache[$ministryName])) {
                    $ministryIds[] = $this->ministryCache[$ministryName];
                }
            }
            $person->ministries()->syncWithoutDetaching($ministryIds);
        }

        return $person;
    }

    protected function parseDate(?string $value): ?Carbon
    {
        if (empty($value)) {
            return null;
        }

        try {
            if (preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $value)) {
                return Carbon::createFromFormat('d.m.Y', $value);
            }
            return Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function rules(): array
    {
        return [
            'imia' => 'nullable|string|max:255',
            "im'ia" => 'nullable|string|max:255',
            'first_name' => 'nullable|string|max:255',
        ];
    }
}
