<?php

namespace App\Controllers\Manager;

use App\Controllers\BaseController;
use App\Models\IncomeModel;
use App\Models\CompanyModel;
use App\Models\TaxModel;
use App\Models\ReceiptModel;
use App\Libraries\ManagesCompanies;
use CodeIgniter\API\ResponseTrait;

class IncomeController extends BaseController
{
    use ResponseTrait;

    protected $incomeModel;
    protected $companyHelper;

    public function __construct()
    {
        $this->incomeModel   = new IncomeModel();
        $this->companyHelper = new ManagesCompanies();
    }

    public function index()
    {
        $request   = service('request');
        $companyId = $request->getGet('company');
        $dateRange = $request->getGet('date_range') ?? 'month';
        $status    = $request->getGet('status') ?? 'all';
        $category  = $request->getGet('category') ?? 'all';

        $companyIds  = $this->companyHelper->getUserCompanyIds();
        $dateFilters = $this->companyHelper->getDateRange($dateRange);

        $builder = $this->incomeModel;
        if (!empty($companyIds)) $builder = $builder->whereIn('company_id', $companyIds);
        if ($companyId) $builder = $builder->where('company_id', $companyId);
        if ($category === 'standard') $builder = $builder->where('income_type', 'standard');
        elseif ($category === 'non-standard') $builder = $builder->where('income_type', 'non-standard');
        if ($status && $status !== 'all') $builder = $builder->where('status', $status);
        $builder = $builder->where('created_at >=', $dateFilters['start'])->where('created_at <=', $dateFilters['end']);

        $incomes = $builder->orderBy('created_at', 'desc')->paginate(20);
        $pager   = $this->incomeModel->pager;

        // Stats
        $statsBuilder = new IncomeModel();
        if (!empty($companyIds)) $statsBuilder = $statsBuilder->whereIn('company_id', $companyIds);
        if ($companyId) $statsBuilder = $statsBuilder->where('company_id', $companyId);
        $statsBuilder = $statsBuilder->where('created_at >=', $dateFilters['start'])->where('created_at <=', $dateFilters['end']);
        $allForStats = $statsBuilder->findAll();

        $stats = [
            'totalPayments' => array_sum(array_column($allForStats, 'amount')),
            'paymentItems'  => count($allForStats),
            'totalReceived' => array_sum(array_column(array_filter($allForStats, fn($i) => $i['status'] === 'received'), 'actual_amount')),
            'receivedItems' => count(array_filter($allForStats, fn($i) => $i['status'] === 'received')),
            'totalPending'  => array_sum(array_column(array_filter($allForStats, fn($i) => $i['status'] === 'pending'), 'amount')),
            'pendingItems'  => count(array_filter($allForStats, fn($i) => $i['status'] === 'pending')),
            'totalOverdue'  => array_sum(array_column(array_filter($allForStats, fn($i) => $i['status'] === 'overdue'), 'amount')),
            'overdueItems'  => count(array_filter($allForStats, fn($i) => $i['status'] === 'overdue')),
            'allTimeOverdue' => 0, 'allTimeOverdueItems' => 0,
        ];

        // All-time overdue
        $overdueBuilder = new IncomeModel();
        if (!empty($companyIds)) $overdueBuilder = $overdueBuilder->whereIn('company_id', $companyIds);
        $stats['allTimeOverdue'] = $overdueBuilder->where('status', 'overdue')->selectSum('amount')->get()->getRow()->amount ?? 0;
        $overdueBuilder2 = new IncomeModel();
        if (!empty($companyIds)) $overdueBuilder2 = $overdueBuilder2->whereIn('company_id', $companyIds);
        $stats['allTimeOverdueItems'] = $overdueBuilder2->where('status', 'overdue')->countAllResults();

        $dateRangeTitle = match($dateRange) {
            'today' => 'Today', 'week' => 'This Week', 'month' => date('F Y'),
            'quarter' => 'Q' . ceil(date('n')/3) . ' ' . date('Y'),
            'year' => date('Y'), default => date('F Y')
        };

        $companies = $this->companyHelper->getUserCompanies();
        $companyMap = [];
        foreach ($companies as $company) {
            $companyMap[$company['id']] = $company;
        }
        foreach ($incomes as &$income) {
            $income['company_name'] = $companyMap[$income['company_id']]['name'] ?? 'N/A';
        }
        unset($income);

        return view('Manager/income/index', [
            'incomes'        => $incomes,
            'pager'          => $pager,
            'companies'      => $companies,
            'statuses'       => ['pending', 'received', 'overdue', 'upcoming'],
            'stats'          => $stats,
            'dateRangeTitle' => $dateRangeTitle,
            'dateRange'      => $dateRange,
            'companyId'      => $companyId,
            'category'       => $category,
            'status'         => $status,
        ]);
    }

    public function store()
    {
        $rules = [
            'company_id'  => 'required',
            'client_name' => 'required|max_length[255]',
            'amount'      => 'required|numeric',
            'grand_total' => 'required|numeric',
            'status'      => 'required|in_list[pending,received,overdue]',
            'income_date' => 'required|valid_date',
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $plannedAmount  = $this->request->getPost('grand_total');
            $receivedAmount = $this->request->getPost('received_amount') ?? 0;
            $baseAmount     = $this->request->getPost('amount');
            $status         = $this->request->getPost('status');

            $isSplitPayment = $status === 'received' && $receivedAmount > 0 && $receivedAmount < $plannedAmount;
            $balanceAmount  = $plannedAmount - $receivedAmount;
            $proportion     = $plannedAmount > 0 ? ($receivedAmount / $plannedAmount) : 0;
            $paidBaseAmount = $baseAmount * $proportion;

            $incomeData = [
                'company_id'    => $this->request->getPost('company_id'),
                'party_name'    => $this->request->getPost('client_name'),
                'amount'        => $isSplitPayment ? $receivedAmount : $plannedAmount,
                'frequency'     => $this->request->getPost('frequency'),
                'actual_amount' => $isSplitPayment ? $paidBaseAmount : $baseAmount,
                'balance_amount' => $isSplitPayment ? 0 : $balanceAmount,
                'due_day'       => $this->request->getPost('due_day'),
                'status'        => $isSplitPayment ? 'received' : $status,
                'income_date'   => $this->request->getPost('income_date'),
                'mail_status'   => $this->request->getPost('mail_status') ?? 0,
                'notes'         => $this->request->getPost('notes'),
                'income_type'   => 'non-standard',
                'source'        => 'manual',
                'created_by'    => session()->get('user_id'),
                'schedule_amount' => $baseAmount,
            ];

            $this->incomeModel->insert($incomeData);
            $incomeId = $this->incomeModel->getInsertID();

            // Handle GST
            if ($this->request->getPost('apply_gst')) {
                $gstAmount = $this->request->getPost('gst_amount') ?? 0;
                if ($isSplitPayment && $gstAmount > 0 && $plannedAmount > 0) {
                    $gstAmount = ($receivedAmount / $plannedAmount) * $gstAmount;
                }
                $taxModel = new TaxModel();
                $taxModel->insert([
                    'taxable_id' => $incomeId, 'taxable_type' => 'App\Models\Income',
                    'tax_type' => 'gst', 'tax_percentage' => $this->request->getPost('gst_percentage') ?? 0,
                    'tax_amount' => $gstAmount, 'payment_status' => 'received', 'direction' => 'income',
                ]);
            }

            // Handle TDS
            if ($this->request->getPost('apply_tds')) {
                $tdsAmount = $this->request->getPost('tds_amount') ?? 0;
                if ($isSplitPayment && $tdsAmount > 0 && $plannedAmount > 0) {
                    $tdsAmount = ($receivedAmount / $plannedAmount) * $tdsAmount;
                }
                $tdsProofPath = '';
                $tdsFile = $this->request->getFile('tds_receipt');
                if ($tdsFile && $tdsFile->isValid() && !$tdsFile->hasMoved()) {
                    $newName = $tdsFile->getRandomName();
                    $tdsFile->move(FCPATH . 'uploads/receipts', $newName);
                    $tdsProofPath = 'uploads/receipts/' . $newName;
                }
                $taxModel = new TaxModel();
                $taxModel->insert([
                    'taxable_id' => $incomeId, 'taxable_type' => 'App\Models\Income',
                    'tax_type' => 'tds', 'tax_percentage' => $this->request->getPost('tds_percentage') ?? 0,
                    'tax_amount' => $tdsAmount,
                    'payment_status' => $this->request->getPost('tds_status') ?? 'not_received',
                    'direction' => 'income', 'tds_proof_path' => $tdsProofPath,
                ]);
            }

            // Create balance income for split payment
            if ($isSplitPayment && $balanceAmount > 0) {
                $balanceBaseAmount = $baseAmount - $paidBaseAmount;
                $balanceData = $incomeData;
                $balanceData['party_name']     = $incomeData['party_name'] . ' - Balance';
                $balanceData['amount']         = $balanceAmount;
                $balanceData['actual_amount']  = $balanceBaseAmount;
                $balanceData['balance_amount'] = $balanceAmount;
                $balanceData['status']         = 'pending';
                $balanceData['income_date']    = $this->request->getPost('new_due_date') ?? date('Y-m-d', strtotime('+30 days'));
                $balanceData['parent_id']      = $incomeId;
                $balanceData['schedule_amount'] = $baseAmount;
                $this->incomeModel->insert($balanceData);
            }

            // Handle receipts
            $files = $this->request->getFiles();
            if (isset($files['receipts'])) {
                $receiptModel = new ReceiptModel();
                foreach ($files['receipts'] as $file) {
                    if ($file->isValid() && !$file->hasMoved()) {
                        $newName = $file->getRandomName();
                        $file->move(FCPATH . 'uploads/receipts', $newName);
                        $receiptModel->insert([
                            'income_id' => $incomeId,
                            'file_name' => $file->getClientName(),
                            'file_path' => 'uploads/receipts/' . $newName,
                            'file_type' => $file->getClientExtension(),
                            'file_size' => $this->companyHelper->formatBytes($file->getSize()),
                        ]);
                    }
                }
            }

            $db->transComplete();
            if ($db->transStatus() === false) {
                return $this->fail('Failed to create income');
            }

            return $this->respondCreated([
                'success' => true,
                'message' => $isSplitPayment ? 'Income created with partial payment.' : 'Income saved successfully!',
            ]);
        } catch (\Exception $e) {
            $db->transRollback();
            return $this->fail('Error: ' . $e->getMessage());
        }
    }

    public function edit($id = null)
    {
        $income = $this->incomeModel->find($id);
        if (!$income) return $this->failNotFound('Income not found');

        $taxModel = new TaxModel();
        $gstTax = $taxModel->where('taxable_id', $id)->where('taxable_type', 'App\Models\Income')
            ->where('tax_type', 'gst')->where('direction', 'income')->first();
        $tdsTax = $taxModel->where('taxable_id', $id)->where('taxable_type', 'App\Models\Income')
            ->where('tax_type', 'tds')->where('direction', 'income')->first();

        $gstAmount = $gstTax['tax_amount'] ?? 0;
        $tdsAmount = $tdsTax['tax_amount'] ?? 0;

        return $this->respond([
            'success' => true,
            'income'  => [
                'id'             => $income['id'],
                'company_id'     => $income['company_id'],
                'client_name'    => $income['party_name'],
                'actual_amount'  => $income['actual_amount'],
                'planned_amount' => $income['amount'],
                'frequency'      => $income['frequency'],
                'due_day'        => $income['due_day'],
                'status'         => $income['status'],
                'income_date'    => $income['income_date'],
                'mail_status'    => $income['mail_status'] ? 1 : 0,
                'notes'          => $income['notes'],
                'gst_percentage' => $gstTax['tax_percentage'] ?? 18,
                'gst_amount'     => $gstAmount,
                'tds_percentage' => $tdsTax['tax_percentage'] ?? 10,
                'tds_amount'     => $tdsAmount,
                'amount_after_tds' => $income['amount'] - $tdsAmount,
                'grand_total'    => $income['amount'] + $gstAmount - $tdsAmount,
                'tds_status'     => $tdsTax['payment_status'] ?? 'not_received',
                'original_total_base' => $income['schedule_amount'] ?? 0,
                'source'         => $income['source'],
            ],
        ]);
    }

    public function update($id = null)
    {
        $db = \Config\Database::connect();
        $db->transStart();
        try {
            $income = $this->incomeModel->find($id);
            if (!$income) return $this->failNotFound('Income not found');

            $data = $this->request->getPost();

            $this->incomeModel->update($id, [
                'company_id'    => $data['company_id'] ?? $income['company_id'],
                'party_name'    => $data['client_name'] ?? $income['party_name'],
                'amount'        => $data['grand_total'] ?? $income['amount'],
                'actual_amount' => $data['amount'] ?? $income['actual_amount'],
                'status'        => ($data['status'] ?? 'pending') === 'settle' ? 'received' : ($data['status'] ?? $income['status']),
                'notes'         => $data['notes'] ?? $income['notes'],
                'income_date'   => $data['income_date'] ?? $income['income_date'],
            ]);

            $taxModel = new TaxModel();
            $taxModel->where('taxable_id', $id)
                ->where('taxable_type', 'App\Models\Income')
                ->delete();

            if (($data['apply_gst'] ?? null) == '1') {
                $taxModel->insert([
                    'taxable_id' => $id,
                    'taxable_type' => 'App\Models\Income',
                    'tax_type' => 'gst',
                    'tax_percentage' => $data['gst_percentage'] ?? 18,
                    'tax_amount' => $data['gst_amount'] ?? 0,
                    'payment_status' => 'received',
                    'direction' => 'income',
                ]);
            }

            if (($data['apply_tds'] ?? null) == '1') {
                $taxModel->insert([
                    'taxable_id' => $id,
                    'taxable_type' => 'App\Models\Income',
                    'tax_type' => 'tds',
                    'tax_percentage' => $data['tds_percentage'] ?? 10,
                    'tax_amount' => $data['tds_amount'] ?? 0,
                    'payment_status' => $data['tds_status'] ?? 'not_received',
                    'direction' => 'income',
                ]);
            }

            $db->transComplete();
            return $this->respond(['success' => true, 'message' => 'Income updated successfully!']);
        } catch (\Exception $e) {
            $db->transRollback();
            return $this->fail('Error: ' . $e->getMessage());
        }
    }

    public function settle($id = null)
    {
        $income = $this->incomeModel->find($id);
        if (!$income) return $this->failNotFound('Income not found');

        $this->incomeModel->update($id, ['status' => 'received', 'income_date' => date('Y-m-d')]);
        return $this->respond(['success' => true, 'message' => 'Income settled!']);
    }

    public function delete($id = null)
    {
        $income = $this->incomeModel->find($id);

        if (! $income) {
            return $this->failNotFound('Income not found');
        }

        $taxModel = new TaxModel();
        $taxModel->where('taxable_id', $id)->delete();

        $receiptModel = new ReceiptModel();
        $receiptModel->where('income_id', $id)->delete();

        if (! $this->incomeModel->delete($id)) {
            return $this->fail('Failed to delete income');
        }

        return $this->respondDeleted([
            'success' => true,
            'message' => 'Income deleted successfully!',
        ]);
    }

    public function splitHistory($id = null)
    {
        $income = $this->incomeModel->find($id);
        if (!$income) return $this->failNotFound('Income not found');

        $children = $this->incomeModel->where('parent_id', $id)->orderBy('created_at', 'desc')->findAll();
        $parent = null;
        if ($income['parent_id']) {
            $parent = $this->incomeModel->find($income['parent_id']);
            $children = $this->incomeModel->where('parent_id', $income['parent_id'])->orderBy('created_at', 'desc')->findAll();
        }

        return $this->respond([
            'success' => true,
            'current_expense' => $income,
            'parent_expense' => $parent,
            'children' => $children,
            'summary' => [
                'original_amount' => $income['schedule_amount'] ?? $income['amount'],
                'total_paid' => array_sum(array_map(fn($c) => $c['status'] === 'received' ? $c['amount'] : 0, $children)),
                'total_balance' => array_sum(array_map(fn($c) => $c['status'] !== 'received' ? $c['amount'] : 0, $children)),
                'split_count' => count($children),
            ],
        ]);
    }

    public function getIncomeDetails($id = null)
    {
        $income = $this->incomeModel->find($id);
        if (!$income) return $this->failNotFound('Income not found');
        return $this->respond(['success' => true, 'income' => $income]);
    }

    public function export()
    {
        $companyIds = $this->companyHelper->getUserCompanyIds();
        $incomes = $this->incomeModel->whereIn('company_id', $companyIds)->orderBy('created_at', 'desc')->findAll();
        $filename = 'income_' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Party', 'Amount', 'Status', 'Date', 'Company']);
        foreach ($incomes as $inc) {
            fputcsv($output, [$inc['id'], $inc['party_name'], $inc['amount'], $inc['status'], $inc['income_date'], $inc['company_id']]);
        }
        fclose($output);
        exit;
    }
}
