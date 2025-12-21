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

    public function __construct(int $churchId)
    {
        $this->churchId = $churchId;
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
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
