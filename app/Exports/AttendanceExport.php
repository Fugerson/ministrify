<?php

namespace App\Exports;

use App\Models\AttendanceRecord;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping, WithStyles
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
        return AttendanceRecord::whereHas('attendance', fn($q) => $q
            ->where('church_id', $this->churchId)
            ->whereYear('date', $this->year))
            ->with(['person', 'attendance.attendable'])
            ->orderBy('created_at')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Дата',
            'Подія/Група',
            'Тип',
            'Особа',
            'Статус',
        ];
    }

    public function map($record): array
    {
        $attendance = $record->attendance;

        return [
            $attendance?->date?->format('d.m.Y') ?? '-',
            $attendance?->entity_name ?? '-',
            $attendance?->type_label ?? '-',
            $record->person?->full_name ?? '-',
            $record->present ? 'Присутній' : 'Відсутній',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
