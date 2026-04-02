<?php

namespace App\Controllers\Manager;

use App\Controllers\BaseController;
use App\Models\TaxModel;
use App\Models\ExpenseModel;
use App\Libraries\ManagesCompanies;
use CodeIgniter\API\ResponseTrait;

class TdsController extends BaseController
{
    use ResponseTrait;
    protected $companyHelper;

    public function __construct()
    {
        $this->companyHelper = new ManagesCompanies();
    }

    public function index()
    {
        $request = service('request');
        $companyIds = $this->companyHelper->getUserCompanyIds($request->getGet('company_id'));
        $period = $request->getGet('period') ?? date('Y-m');
        $selectedMonth = date('m', strtotime($period));
        $selectedYear  = date('Y', strtotime($period));

        $totalOutputTDS = 0;
        if (!empty($companyIds)) {
            $db = \Config\Database::connect();
            $result = $db->query("
                SELECT COALESCE(SUM(t.tax_amount), 0) as total
                FROM taxes t
                JOIN incomes i ON t.taxable_id = i.id AND t.taxable_type = 'App\\\\Models\\\\Income'
                WHERE t.tax_type = 'tds'
                AND MONTH(t.created_at) = ? AND YEAR(t.created_at) = ?
                AND i.company_id IN (" . implode(',', $companyIds) . ")
            ", [$selectedMonth, $selectedYear])->getRow();
            $totalOutputTDS = $result->total ?? 0;
        }

        $data = $this->companyHelper->getCommonViewData($selectedMonth, $selectedYear);
        $data['totalOutputTDS'] = $totalOutputTDS;
        $data['period'] = $period;
        $data['selectedMonth'] = $selectedMonth;
        $data['selectedYear'] = $selectedYear;

        return view('Manager/tds/index', $data);
    }

    public function tdsExpense()
    {
        $request = service('request');
        $companyIds = $this->companyHelper->getUserCompanyIds($request->getGet('company_id'));
        $period = $request->getGet('period') ?? date('Y-m');
        $selectedMonth = date('m', strtotime($period));
        $selectedYear  = date('Y', strtotime($period));

        $totalTDSAmount = 0;
        if (!empty($companyIds)) {
            $db = \Config\Database::connect();
            $result = $db->query("
                SELECT COALESCE(SUM(t.tax_amount), 0) as total
                FROM taxes t
                JOIN expenses e ON t.taxable_id = e.id AND t.taxable_type = 'App\\\\Models\\\\Expense'
                WHERE t.tax_type = 'tds' AND t.direction = 'expense'
                AND MONTH(t.created_at) = ? AND YEAR(t.created_at) = ?
                AND e.company_id IN (" . implode(',', $companyIds) . ")
            ", [$selectedMonth, $selectedYear])->getRow();
            $totalTDSAmount = $result->total ?? 0;
        }

        $data = $this->companyHelper->getCommonViewData($selectedMonth, $selectedYear);
        $data['totalTDSAmount'] = $totalTDSAmount;
        $data['selectedPeriod'] = $period;

        return view('Manager/tds/expense', $data);
    }

    public function attachTaxProof()
    {
        $taxModel = new TaxModel();
        $tax = $taxModel->find($this->request->getPost('tax_id'));
        if (!$tax) return $this->failNotFound('Tax record not found');

        $file = $this->request->getFile('tds_proof');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $tax['tax_type'] . '_proof_' . $tax['id'] . '_' . time() . '.' . $file->getClientExtension();
            $file->move(FCPATH . 'uploads/tds_proofs', $newName);
            $taxModel->update($tax['id'], [
                'tds_proof_path'  => 'uploads/tds_proofs/' . $newName,
                'payment_status'  => 'received',
            ]);
            return $this->respond(['success' => true, 'message' => 'TDS proof attached']);
        }
        return $this->fail('Invalid file');
    }

    public function downloadTdsProof($id = null)
    {
        $taxModel = new TaxModel();
        $tax = $taxModel->find($id);
        if (!$tax || !$tax['tds_proof_path']) {
            return redirect()->back()->with('error', 'No proof found');
        }
        $path = FCPATH . $tax['tds_proof_path'];
        if (!file_exists($path)) {
            return redirect()->back()->with('error', 'File not found');
        }
        return $this->response->download($path, null);
    }

    public function markTDSPaid($id = null)
    {
        $taxModel = new TaxModel();
        $taxModel->update($id, ['payment_status' => 'paid', 'paid_date' => date('Y-m-d')]);
        return $this->respond(['success' => true, 'message' => 'TDS marked as paid']);
    }

    public function exportData($type = 'csv') { return $this->respond(['success' => true]); }
    public function exportExpenseData($type = 'csv') { return $this->respond(['success' => true]); }
}
