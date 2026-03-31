<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ExpenseExport implements FromCollection, WithHeadings, WithMapping
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
            'Expense Name',
            'Party Name',
            'Category',
            'Planned Amount (₹)',
            'Actual Amount (₹)',
            'Status',
            'Payment Mode'
        ];
    }

    public function map($expense): array
    {
        return [
            $expense->payment_date ?? $expense->created_at->format('d-m-Y'),
            $expense->company->name ?? 'N/A',
            $expense->expense_name,
            $expense->party_name ?? '-',
            $expense->categoryRelation->name ?? 'Uncategorized',
            number_format($expense->planned_amount ?? 0, 2),
            number_format($expense->actual_amount ?? 0, 2),
            ucfirst($expense->status),
            ucfirst($expense->payment_mode ?? 'Cash')
        ];
    }
}
