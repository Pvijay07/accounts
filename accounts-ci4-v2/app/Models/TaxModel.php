<?php

namespace App\Models;

use CodeIgniter\Model;

class TaxModel extends Model
{
    protected $table      = 'taxes';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'taxable_id',
        'taxable_type',
        'tax_type',
        'tax_percentage',
        'tax_amount',
        'amount_paid',
        'paid_date',
        'payment_status',
        'payment_notes',
        'payment_reference',
        'due_date',
        'direction',
        'tds_proof_path'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
