<?php

namespace App\Exports;

use App\Models\Person;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VolunteersExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected int $churchId;
    protected int $year;

    public function __construct(int $churchId, int $year)
    {
        $this->churchId = $churchId;
        $this->year = $year;
    }

    public function collection()
    {
        return Person::where('church_id', $this->churchId)
            ->whereHas('ministries')
            ->withCount(['assignments' => fn($q) => $q->whereHas('event', fn($e) => $e->whereYear('date', $this->year))])
            ->with(['ministries', 'assignments' => fn($q) => $q->whereHas('event', fn($e) => $e->whereYear('date', $this->year))->with('event')])
            ->orderByDesc('assignments_count')
            ->get();
    }

    public function headings(): array
    {
        return [
            "Ім'я",
            'Прізвище',
            'Телефон',
            'Email',
            'Служіння',
            'Призначень за ' . $this->year,
            'Останнє служіння',
        ];
    }

    public function map($person): array
    {
        $lastAssignment = $person->assignments
            ->sortByDesc(fn($a) => $a->event?->date)
            ->first();

        return [
            $person->first_name,
            $person->last_name,
            $person->phone,
            $person->email,
            $person->ministries->pluck('name')->implode(', '),
            $person->assignments_count,
            $lastAssignment?->event?->date?->format('d.m.Y') ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
