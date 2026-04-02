<?php

namespace App\Models;

use CodeIgniter\Model;

class CompanyModel extends Model
{
    protected $table      = 'companies';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'code',
        'name',
        'manager_id',
        'financial_year_start',
        'default_currency',
        'invoice_prefix',
        'tax_percentage',
        'logo_path',
        'address',
        'contact_details',
        'status',
        'email',
        'website',
        'currency'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules    = [
        'name' => 'required|min_length[3]|max_length[255]',
        'email' => 'permit_empty|valid_email',
        'status' => 'required|in_list[active,inactive]'
    ];
    protected $validationMessages = [];
    protected $skipValidation     = false;

    public function getActiveCompanies()
    {
        return $this->where('status', 'active')->findAll();
    }
}
