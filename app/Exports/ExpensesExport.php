<?php

namespace App\Exports;

use App\Models\Expense;
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
        $query = Expense::where('church_id', $this->churchId)
            ->with(['ministry', 'category', 'user']);

        if ($this->month && $this->year) {
            $query->whereMonth('date', $this->month)
                ->whereYear('date', $this->year);
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
            $expense->ministry->name,
            $expense->category?->name ?? '-',
            $expense->description,
            $expense->amount,
            $expense->user->name,
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
