<?php

namespace App\Imports;

use App\Models\FamilyRelationship;
use App\Models\Ministry;
use App\Models\Person;
use App\Models\Tag;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Events\AfterImport;

class PeopleImport implements ToModel, WithEvents, WithHeadingRow, WithValidation
{
    protected int $churchId;

    protected array $tagCache = [];

    protected array $ministryCache = [];

    protected array $households = []; // Track household -> person_ids

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

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function (AfterImport $event) {
                $this->createFamilyRelationships();
            },
        ];
    }

    public function model(array $row)
    {
        $firstName = $row['imia'] ?? $row["im'ia"] ?? $row['first_name'] ?? '';
        $lastName = $row['prizvyshche'] ?? $row['last_name'] ?? '';

        // Skip rows with empty names
        if (empty(trim($firstName)) && empty(trim($lastName))) {
            return null;
        }

        // Build update data — only set non-null values to avoid overwriting existing data
        $updateData = array_filter([
            'phone' => $row['telefon'] ?? $row['phone'] ?? null,
            'email' => $row['email'] ?? null,
            'telegram_username' => $row['telegram'] ?? null,
            'address' => $row['adresa'] ?? $row['address'] ?? null,
            'birth_date' => $this->parseDate($row['data_narodzhennia'] ?? $row['birth_date'] ?? null),
            'joined_date' => $this->parseDate($row['v_tserkvi_z'] ?? $row['joined_date'] ?? null),
            'notes' => $row['notatky'] ?? $row['notes'] ?? null,
        ], fn ($v) => $v !== null && $v !== '');

        $person = Person::updateOrCreate(
            [
                'church_id' => $this->churchId,
                'first_name' => $firstName,
                'last_name' => $lastName,
            ],
            $updateData
        );

        // Track household for family relationships
        $householdName = $row['household_name'] ?? $row['household'] ?? $row['simia'] ?? $row["sim'ia"] ?? $row['rodyna'] ?? null;
        if (! empty($householdName)) {
            $householdName = trim($householdName);
            if (! isset($this->households[$householdName])) {
                $this->households[$householdName] = [];
            }
            $this->households[$householdName][] = $person->id;
        }

        // Sync tags
        if (! empty($row['tehy']) || ! empty($row['tags'])) {
            $tagNames = array_map('trim', explode(',', $row['tehy'] ?? $row['tags'] ?? ''));
            $tagIds = [];
            foreach ($tagNames as $tagName) {
                if (! empty($tagName) && isset($this->tagCache[$tagName])) {
                    $tagIds[] = $this->tagCache[$tagName];
                }
            }
            if (! empty($tagIds)) {
                $person->tags()->syncWithoutDetaching($tagIds);
            }
        }

        // Sync ministries
        if (! empty($row['sluzhinnia']) || ! empty($row['ministries'])) {
            $ministryNames = array_map('trim', explode(',', $row['sluzhinnia'] ?? $row['ministries'] ?? ''));
            $ministryIds = [];
            foreach ($ministryNames as $ministryName) {
                if (! empty($ministryName) && isset($this->ministryCache[$ministryName])) {
                    $ministryIds[] = $this->ministryCache[$ministryName];
                }
            }
            $person->ministries()->syncWithoutDetaching($ministryIds);
        }

        return $person;
    }

    /**
     * Create family relationships for people in the same household
     * All members with the same household name are linked as "family" (siblings by default)
     */
    protected function createFamilyRelationships(): void
    {
        foreach ($this->households as $householdName => $personIds) {
            if (count($personIds) < 2) {
                continue; // Need at least 2 people to form a family
            }

            // Create sibling relationships between all members of the household
            // (User can later change to spouse/child/parent in the UI)
            for ($i = 0; $i < count($personIds); $i++) {
                for ($j = $i + 1; $j < count($personIds); $j++) {
                    // Family relationships are stored one-directionally by design;
                    // inverseFamilyRelationships() handles the reverse lookup.
                    // Check both directions to avoid logical duplicates (A→B or B→A).
                    $exists = FamilyRelationship::where('church_id', $this->churchId)
                        ->where(function ($q) use ($personIds, $i, $j) {
                            $q->where(function ($q2) use ($personIds, $i, $j) {
                                $q2->where('person_id', $personIds[$i])
                                    ->where('related_person_id', $personIds[$j]);
                            })->orWhere(function ($q2) use ($personIds, $i, $j) {
                                $q2->where('person_id', $personIds[$j])
                                    ->where('related_person_id', $personIds[$i]);
                            });
                        })
                        ->exists();

                    if (! $exists) {
                        FamilyRelationship::create([
                            'church_id' => $this->churchId,
                            'person_id' => $personIds[$i],
                            'related_person_id' => $personIds[$j],
                            'relationship_type' => FamilyRelationship::TYPE_SIBLING,
                        ]);
                    }
                }
            }
        }
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
