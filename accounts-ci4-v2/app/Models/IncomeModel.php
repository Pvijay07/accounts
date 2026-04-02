<?php

namespace App\Models;

use CodeIgniter\Model;

class IncomeModel extends Model
{
    protected $table      = 'incomes';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'company_id',
        'source',
        'income_type',
        'description',
        'amount',
        'invoice_id',
        'import_method',
        'income_date',
        'due_date',
        'status',
        'tax_type',
        'actual_amount',
        'balance_amount',
        'party_name',
        'frequency',
        'due_day',
        'mail_status',
        'created_by',
        'notes',
        'conversion_cost',
        'conversion_rate_percentage',
        'client_details',
        'line_items',
        'parent_id',
        'invoice_number',
        'schedule_amount'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
