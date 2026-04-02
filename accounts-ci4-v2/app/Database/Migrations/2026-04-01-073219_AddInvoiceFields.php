<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddInvoiceFields extends Migration
{
    public function up()
    {
        $fields = [
            'client_name'       => ['type' => 'VARCHAR', 'constraint' => 255, 'after' => 'client_details'],
            'client_email'      => ['type' => 'VARCHAR', 'constraint' => 255, 'after' => 'client_name'],
            'mobile_number'     => ['type' => 'VARCHAR', 'constraint' => 20, 'after' => 'client_email'],
            'client_gstin'      => ['type' => 'VARCHAR', 'constraint' => 50, 'after' => 'mobile_number'],
            'billing_address'   => ['type' => 'TEXT', 'after' => 'client_gstin'],
            'currency'          => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'INR', 'after' => 'total_amount'],
            'gst_percentage'    => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0.00, 'after' => 'currency'],
            'gst_amount'        => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00, 'after' => 'gst_percentage'],
            'tds_percentage'    => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0.00, 'after' => 'gst_amount'],
            'tds_amount'        => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00, 'after' => 'tds_percentage'],
            'receivable_amount' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00, 'after' => 'tds_amount'],
            'purpose_comment'   => ['type' => 'TEXT', 'null' => true, 'after' => 'receivable_amount'],
            'terms_conditions'  => ['type' => 'TEXT', 'null' => true, 'after' => 'purpose_comment'],
        ];
        $this->forge->addColumn('invoices', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('invoices', [
            'client_name', 'client_email', 'mobile_number', 'client_gstin', 
            'billing_address', 'currency', 'gst_percentage', 'gst_amount', 
            'tds_percentage', 'tds_amount', 'receivable_amount', 
            'purpose_comment', 'terms_conditions'
        ]);
    }
}
