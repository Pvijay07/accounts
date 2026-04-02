<?php

namespace App\Models;

use CodeIgniter\Model;

class ExpenseTypeModel extends Model
{
    protected $table      = 'expense_types';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'name', 'category', 'amount_type', 'default_amount',
        'reminder_days', 'applicable_companies', 'status',
        'is_recurring'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
