<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInitialTables extends Migration
{
    public function up()
    {
        // Companies Table
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'email'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'manager_id'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'status'      => ['type' => 'ENUM', 'constraint' => ['active', 'inactive'], 'default' => 'active'],
            'currency'    => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'INR'],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('companies');

        // Users Table
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name'          => ['type' => 'VARCHAR', 'constraint' => 255],
            'email'         => ['type' => 'VARCHAR', 'constraint' => 255],
            'password'      => ['type' => 'VARCHAR', 'constraint' => 255],
            'role'          => ['type' => 'VARCHAR', 'constraint' => 50],
            'company_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'status'        => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('email');
        $this->forge->createTable('users');

        // Categories Table
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name'          => ['type' => 'VARCHAR', 'constraint' => 255],
            'main_type'     => ['type' => 'ENUM', 'constraint' => ['income', 'expense']],
            'category_type' => ['type' => 'VARCHAR', 'constraint' => 50],
            'is_active'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'is_default'    => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('categories');

        // Invoices Table
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'company_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'invoice_number' => ['type' => 'VARCHAR', 'constraint' => 100],
            'type'           => ['type' => 'VARCHAR', 'constraint' => 50],
            'status'         => ['type' => 'VARCHAR', 'constraint' => 50],
            'client_details' => ['type' => 'TEXT', 'null' => true],
            'line_items'     => ['type' => 'TEXT', 'null' => true],
            'total_amount'   => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00],
            'issue_date'     => ['type' => 'DATE', 'null' => true],
            'due_date'       => ['type' => 'DATE', 'null' => true],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('invoices');

        // Incomes Table
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'company_id'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'invoice_id'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'party_name'  => ['type' => 'VARCHAR', 'constraint' => 255],
            'amount'      => ['type' => 'DECIMAL', 'constraint' => '15,2'],
            'status'      => ['type' => 'VARCHAR', 'constraint' => 50],
            'income_date' => ['type' => 'DATE', 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('incomes');

        // Expenses Table
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'company_id'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'expense_name'    => ['type' => 'VARCHAR', 'constraint' => 255],
            'category_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'planned_amount'  => ['type' => 'DECIMAL', 'constraint' => '15,2'],
            'actual_amount'   => ['type' => 'DECIMAL', 'constraint' => '15,2', 'null' => true],
            'status'          => ['type' => 'VARCHAR', 'constraint' => 50],
            'source'          => ['type' => 'VARCHAR', 'constraint' => 50],
            'frequency'       => ['type' => 'VARCHAR', 'constraint' => 50],
            'due_day'         => ['type' => 'INT', 'constraint' => 2, 'null' => true],
            'due_date'        => ['type' => 'DATE', 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('expenses');
    }

    public function down()
    {
        $this->forge->dropTable('expenses');
        $this->forge->dropTable('incomes');
        $this->forge->dropTable('invoices');
        $this->forge->dropTable('categories');
        $this->forge->dropTable('users');
        $this->forge->dropTable('companies');
    }
}
