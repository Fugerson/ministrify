<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExpensesExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected int $churchId;
    protected ?int $month;
    protected ?int $year;

    public function __construct(int $churchId, ?int $month = null, ?int $year = null)
    {
        $this->churchId = $churchId;
        $this->month = $month;
        $this->year = $year;
    }

    public function collection()
    {
        $query = Transaction::where('church_id', $this->churchId)
            ->outgoing()
            ->with(['ministry', 'category', 'recorder']);

        if ($this->month && $this->year) {
            $query->forMonth($this->year, $this->month);
        }

        return $query->orderBy('date', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Дата',
            'Служіння',
            'Категорія',
            'Опис',
            'Сума',
            'Хто вніс',
            'Нотатки',
        ];
    }

    public function map($expense): array
    {
        return [
            $expense->date->format('d.m.Y'),
            $expense->ministry?->name ?? '-',
            $expense->category?->name ?? '-',
            $expense->description,
            $expense->amount,
            $expense->recorder?->name ?? '-',
            $expense->notes,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
