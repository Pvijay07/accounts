<?php

namespace App\Models;

use CodeIgniter\Model;

class GstTaskModel extends Model
{
    protected $table      = 'gst_tasks';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'company_id', 'task_name', 'task_type', 'due_date',
        'status', 'assigned_to', 'notes', 'created_by'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
