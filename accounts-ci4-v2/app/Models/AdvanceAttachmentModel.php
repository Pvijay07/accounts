<?php

namespace App\Models;

use CodeIgniter\Model;

class AdvanceAttachmentModel extends Model
{
    protected $table      = 'advance_attachments';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'advance_id', 'file_name', 'file_path', 'file_type',
        'file_size', 'attachment_type'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
