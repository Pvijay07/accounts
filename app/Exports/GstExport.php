<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class GstExport implements FromCollection, WithHeadings, WithMapping
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
            'Bill No',
            'Taxable Amount (₹)',
            'GST %',
            'GST Amount (₹)',
            'Status',
            'Type'
        ];
    }

    public function map($tax): array
    {
        $taxable = $tax->taxable;
        $partyName = '-';
        if ($tax->taxable_type === 'App\Models\Income') {
            $partyName = $taxable->party_name ?? $taxable->client_name ?? '-';
        } elseif ($tax->taxable_type === 'App\Models\Expense') {
            $partyName = $taxable->vendor_name ?? $taxable->party_name ?? '-';
        }

        return [
            $tax->created_at->format('d-m-Y'),
            $taxable->company->name ?? 'N/A',
            $partyName,
            $taxable->bill_no ?? $taxable->invoice_number ?? '-',
            number_format($taxable->amount ?? 0, 2),
            $tax->tax_percentage . '%',
            number_format($tax->tax_amount ?? 0, 2),
            ucfirst($tax->payment_status),
            ucfirst($tax->direction)
        ];
    }
}
