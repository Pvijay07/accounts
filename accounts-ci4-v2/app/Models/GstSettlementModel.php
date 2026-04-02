<?php

namespace App\Models;

use CodeIgniter\Model;

class GstSettlementModel extends Model
{
    protected $table      = 'gst_settlements';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'company_id', 'tax_period', 'amount', 'payment_date',
        'payment_mode', 'challan_number', 'utr_number',
        'status', 'purpose_comment', 'created_by'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
