<?php

namespace App\Models;

use CodeIgniter\Model;

class AdvanceModel extends Model
{
    protected $table      = 'advances';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'transaction_type', 'direction', 'party_id', 'party_type',
        'reference_number', 'amount', 'recovered_amount', 'outstanding_amount',
        'transaction_date', 'expected_recovery_date', 'status',
        'purpose', 'comments', 'created_by', 'company_id', 'linked_advance_id'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
