<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class IncomeExport implements FromCollection, WithHeadings, WithMapping
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Date',
            'Company',
            'Party Name',
            'Source',
            'Planned Amount (₹)',
            'Received Amount (₹)',
            'Balance (₹)',
            'Status',
            'Notes'
        ];
    }

    public function map($income): array
    {
        return [
            $income->income_date ?? $income->created_at->format('d-m-Y'),
            $income->company->name ?? 'N/A',
            $income->party_name ?? '-',
            ucfirst($income->source),
            number_format($income->amount ?? 0, 2),
            number_format($income->actual_amount ?? $income->received_amount ?? 0, 2),
            number_format(($income->amount ?? 0) - ($income->actual_amount ?? $income->received_amount ?? 0), 2),
            ucfirst($income->status),
            $income->notes ?? '-'
        ];
    }
}
