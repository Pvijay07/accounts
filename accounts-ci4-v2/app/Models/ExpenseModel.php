<?php

namespace App\Models;

use CodeIgniter\Model;

class ExpenseModel extends Model
{
    protected $table      = 'expenses';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'company_id',
        'purpose_comment',
        'planned_amount',
        'actual_amount',
        'due_date',
        'paid_date',
        'status',
        'party_name',
        'category_id',
        'source',
        'created_by',
        'expense_name',
        'frequency',
        'default_amount',
        'due_day',
        'reminder_days',
        'amount_mode',
        'tax_percentage',
        'tax_amount',
        'apply_tax',
        'mobile_number',
        'is_recurring',
        'is_split',
        'parent_id',
        'partial_paid',
        'tax_type',
        'is_active',
        'payment_mode',
        'bank_name',
        'upi_type',
        'upi_number',
        'schedule_amount',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
