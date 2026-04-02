<?php

namespace App\Models;

use CodeIgniter\Model;

class SystemSettingModel extends Model
{
    protected $table      = 'system_settings';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'key', 'value', 'type', 'description'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getSetting($key, $default = null)
    {
        $setting = $this->where('key', $key)->first();
        return $setting ? $setting['value'] : $default;
    }

    public function setSetting($key, $value, $type = 'string', $description = null)
    {
        $existing = $this->where('key', $key)->first();
        $data = [
            'key'   => $key,
            'value' => $value,
            'type'  => $type,
        ];
        if ($description) {
            $data['description'] = $description;
        }

        if ($existing) {
            return $this->update($existing['id'], $data);
        }
        return $this->insert($data);
    }
}
