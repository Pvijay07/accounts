<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TdsExport implements FromCollection, WithHeadings, WithMapping
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
            'Vendor/Client',
            'Taxable Amount (₹)',
            'TDS Percentage (%)',
            'TDS Amount (₹)',
            'Status',
            'Payment Reference'
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
        } elseif ($tax->taxable_type === 'App\Models\Invoice') {
             $clientDetails = is_string($taxable->client_details) ? json_decode($taxable->client_details, true) : $taxable->client_details;
             $partyName = $clientDetails['name'] ?? '-';
        }

        return [
            $tax->created_at->format('d-m-Y'),
            $taxable->company->name ?? 'N/A',
            $partyName,
            number_format($taxable->amount ?? 0, 2),
            $tax->tax_percentage . '%',
            number_format($tax->tax_amount ?? 0, 2),
            ucfirst($tax->payment_status),
            $tax->payment_reference ?? '-'
        ];
    }
}
