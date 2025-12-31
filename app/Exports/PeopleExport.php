<?php

namespace App\Exports;

use App\Models\Person;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PeopleExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected int $churchId;
    protected array $householdCache = [];

    public function __construct(int $churchId)
    {
        $this->churchId = $churchId;
        $this->buildHouseholdCache();
    }

    /**
     * Build a cache of person_id -> household_name based on family relationships
     */
    protected function buildHouseholdCache(): void
    {
        $people = Person::where('church_id', $this->churchId)
            ->with(['familyRelationships.relatedPerson', 'inverseFamilyRelationships.person'])
            ->get();

        $processed = [];

        foreach ($people as $person) {
            if (isset($processed[$person->id])) {
                continue;
            }

            // Get all family members (including this person)
            $familyMemberIds = [$person->id];

            // Direct relationships
            foreach ($person->familyRelationships as $rel) {
                if (!in_array($rel->related_person_id, $familyMemberIds)) {
                    $familyMemberIds[] = $rel->related_person_id;
                }
            }

            // Inverse relationships
            foreach ($person->inverseFamilyRelationships as $rel) {
                if (!in_array($rel->person_id, $familyMemberIds)) {
                    $familyMemberIds[] = $rel->person_id;
                }
            }

            if (count($familyMemberIds) > 1) {
                // Generate household name from last name
                $householdName = "Сім'я " . $person->last_name;

                foreach ($familyMemberIds as $memberId) {
                    $this->householdCache[$memberId] = $householdName;
                    $processed[$memberId] = true;
                }
            }
        }
    }

    public function collection()
    {
        return Person::where('church_id', $this->churchId)
            ->with(['tags', 'ministries'])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
    }

    public function headings(): array
    {
        return [
            "Ім'я",
            'Прізвище',
            'Телефон',
            'Email',
            'Telegram',
            'Адреса',
            'Дата народження',
            'В церкві з',
            'Служіння',
            'Теги',
            'Нотатки',
            'Household Name',
        ];
    }

    public function map($person): array
    {
        return [
            $person->first_name,
            $person->last_name,
            $person->phone,
            $person->email,
            $person->telegram_username,
            $person->address,
            $person->birth_date?->format('d.m.Y'),
            $person->joined_date?->format('d.m.Y'),
            $person->ministries->pluck('name')->implode(', '),
            $person->tags->pluck('name')->implode(', '),
            $person->notes,
            $this->householdCache[$person->id] ?? '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
