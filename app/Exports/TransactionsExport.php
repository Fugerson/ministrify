<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class TransactionsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnFormatting
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
        return Transaction::where('church_id', $this->churchId)
            ->completed()
            ->whereYear('date', $this->year)
            ->with(['category', 'ministry', 'person'])
            ->orderBy('date')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Дата',
            'Тип',
            'Категорія',
            'Сума',
            'Валюта',
            'Опис',
            'Служіння',
            'Особа',
            'Примітки',
        ];
    }

    public function map($transaction): array
    {
        return [
            $transaction->date->format('d.m.Y'),
            $transaction->direction === 'in' ? 'Надходження' : 'Витрата',
            $transaction->category?->name ?? '-',
            $transaction->amount,
            $transaction->currency ?? 'UAH',
            $transaction->description ?? '-',
            $transaction->ministry?->name ?? '-',
            $transaction->person?->full_name ?? ($transaction->is_anonymous ? 'Анонімно' : '-'),
            $transaction->notes ?? '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }
}
