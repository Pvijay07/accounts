<?php

namespace App\Models;

use CodeIgniter\Model;

class InvoiceModel extends Model
{
    protected $table      = 'invoices';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'company_id',
        'type',
        'invoice_number',
        'status',
        'client_details',
        'line_items',
        'subtotal',
        'tax_amount',
        'total_amount',
        'is_taxable',
        'issue_date',
        'due_date',
        'paid_date',
        'terms_conditions',
        'created_by',
        'frequency',
        'due_day',
        'reminder_days',
        'is_recurring',
        'is_settled',
        'writeoff_reason',
        'tax_percentage',
        'currency',
        'conversion_rate',
        'converted_amount',
        'received_amount',
        'tax_type',
        'original_currency_amount',
        'conversion_cost'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
