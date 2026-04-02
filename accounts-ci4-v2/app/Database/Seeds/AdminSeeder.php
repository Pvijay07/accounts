<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run()
    {
        $data = [
            'name'     => 'Administrator',
            'email'    => 'admin@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'role'     => 'admin',
            'status'   => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Simple query
        $this->db->table('users')->insert($data);
    }
}
