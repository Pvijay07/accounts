<?php

namespace App\Controllers\Manager;

use App\Controllers\BaseController;
use App\Models\ExpenseModel;
use App\Models\CategoryModel;
use App\Models\CompanyModel;
use App\Models\TaxModel;
use App\Models\ReceiptModel;
use App\Libraries\ManagesCompanies;
use CodeIgniter\API\ResponseTrait;

class ExpensesController extends BaseController
{
    use ResponseTrait;

    protected $expenseModel;
    protected $companyHelper;

    public function __construct()
    {
        $this->expenseModel  = new ExpenseModel();
        $this->companyHelper = new ManagesCompanies();
    }

    public function index()
    {
        $request    = service('request');
        $companyId  = $request->getGet('company');
        $categoryId = $request->getGet('category');
        $status     = $request->getGet('status') ?? 'all';
        $type       = $request->getGet('type') ?? 'all';
        $dateRange  = $request->getGet('date_range') ?? 'month';

        $companyIds = $this->companyHelper->getUserCompanyIds();
        $dateFilters = $this->companyHelper->getDateRange($dateRange);

        // Build query
        $builder = $this->expenseModel;

        if (!empty($companyIds)) {
            $builder = $builder->whereIn('company_id', $companyIds);
        }
        if ($companyId) {
            $builder = $builder->where('company_id', $companyId);
        }
        if ($type === 'standard') {
            $builder = $builder->where('source', 'standard');
        } elseif ($type === 'non-standard') {
            $builder = $builder->where('source', 'manual');
        }
        if ($categoryId && $categoryId !== 'all') {
            $builder = $builder->where('category_id', $categoryId);
        }
        if ($status && $status !== 'all') {
            $builder = $builder->where('status', $status);
        }

        $builder = $builder->where('created_at >=', $dateFilters['start'])
                           ->where('created_at <=', $dateFilters['end']);

        $allExpenses = $builder->orderBy('created_at', 'desc')->paginate(10);
        $pager = $this->expenseModel->pager;

        // Stats - fresh query
        $statsBuilder = new ExpenseModel();
        if (!empty($companyIds)) $statsBuilder = $statsBuilder->whereIn('company_id', $companyIds);
        if ($companyId) $statsBuilder = $statsBuilder->where('company_id', $companyId);
        if ($type === 'standard') $statsBuilder = $statsBuilder->where('source', 'standard');
        elseif ($type === 'non-standard') $statsBuilder = $statsBuilder->where('source', 'manual');
        $statsBuilder = $statsBuilder->where('created_at >=', $dateFilters['start'])->where('created_at <=', $dateFilters['end']);
        $allForStats = $statsBuilder->findAll();

        $totalPayments = array_sum(array_column($allForStats, 'planned_amount'));
        $totalItems    = count($allForStats);
        $paidItems     = array_filter($allForStats, fn($e) => $e['status'] === 'paid');
        $paidAmount    = array_sum(array_column($paidItems, 'actual_amount'));
        $paidCount     = count($paidItems);
        $pendingItems  = array_filter($allForStats, fn($e) => $e['status'] === 'pending');
        $pendingAmount = array_sum(array_column($pendingItems, 'planned_amount'));
        $pendingCount  = count($pendingItems);
        $overdueItems  = array_filter($allForStats, fn($e) => $e['status'] === 'overdue');
        $overdueAmount = array_sum(array_column($overdueItems, 'planned_amount'));
        $overdueCount  = count($overdueItems);

        $companies  = $this->companyHelper->getUserCompanies();
        $catModel   = new CategoryModel();
        $categories = $catModel->where('is_active', 1)->findAll();
        $companyMap = [];
        foreach ($companies as $company) {
            $companyMap[$company['id']] = $company;
        }
        $categoryMap = [];
        foreach ($categories as $category) {
            $categoryMap[$category['id']] = $category;
        }
        foreach ($allExpenses as &$expense) {
            $expense['company_name'] = $companyMap[$expense['company_id']]['name'] ?? 'All Companies';
            $expense['category_name'] = $categoryMap[$expense['category_id']]['name'] ?? 'N/A';
        }
        unset($expense);

        $dateRangeTitle = match($dateRange) {
            'today' => 'Today', 'week' => 'This Week',
            'month' => date('M-Y'), 'quarter' => 'Q' . ceil(date('n')/3) . ' ' . date('Y'),
            'year' => date('Y'), default => date('M-Y')
        };

        return view('Manager/expenses/index', [
            'allExpenses'      => $allExpenses,
            'pager'            => $pager,
            'companies'        => $companies,
            'categories'       => $categories,
            'companyId'        => $companyId,
            'categoryId'       => $categoryId,
            'status'           => $status,
            'type'             => $type,
            'dateRange'        => $dateRange,
            'dateRangeTitle'   => $dateRangeTitle,
            'totalPayments'    => $totalPayments,
            'totalItems'       => $totalItems,
            'paidAmount'       => $paidAmount,
            'paidCount'        => $paidCount,
            'pendingAmount'    => $pendingAmount,
            'pendingCount'     => $pendingCount,
            'overdueAmount'    => $overdueAmount,
            'overdueCount'     => $overdueCount,
            'totalOverdueAmount' => $overdueAmount,
            'totalOverdueCount'  => $overdueCount,
            'next7DaysAmount'  => 0,
            'next7DaysCount'   => 0,
        ]);
    }

    public function store()
    {
        $rules = [
            'expense_name'  => 'required|max_length[255]',
            'company_id'    => 'required|integer',
            'category_id'   => 'required',
            'actual_amount' => 'required|numeric',
            'status'        => 'required|in_list[upcoming,pending,paid]',
            'grand_total'   => 'required|numeric',
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $plannedAmount = $this->request->getPost('grand_total');
            $paidAmount    = $this->request->getPost('paid_amount') ?? 0;
            $actualAmount  = $this->request->getPost('actual_amount') ?? 0;
            $status        = $this->request->getPost('status');

            $paidAmount = $paidAmount > 0 ? $paidAmount : ($status === 'paid' ? $actualAmount : 0);

            $isSplitPayment = $status === 'paid' && $paidAmount > 0 && $paidAmount < $plannedAmount
                && ($this->request->getPost('split_payment') || $this->request->getPost('create_new_for_balance'));

            $balanceAmount = $plannedAmount - $paidAmount;
            $proportion = $plannedAmount > 0 ? ($paidAmount / $plannedAmount) : 0;
            $paidBaseAmount = $actualAmount * $proportion;

            $expenseData = [
                'expense_name'   => $this->request->getPost('expense_name'),
                'company_id'     => $this->request->getPost('company_id'),
                'category_id'    => $this->request->getPost('category_id'),
                'actual_amount'  => $isSplitPayment ? $paidBaseAmount : ($status === 'paid' ? $actualAmount : 0),
                'planned_amount' => $isSplitPayment ? $paidAmount : $plannedAmount,
                'status'         => $isSplitPayment ? 'paid' : $status,
                'source'         => 'manual',
                'payment_mode'   => $this->request->getPost('payment_mode') ?? 'cash',
                'bank_name'      => $this->request->getPost('bank_name'),
                'upi_type'       => $this->request->getPost('upi_type'),
                'upi_number'     => $this->request->getPost('upi_number'),
                'party_name'     => $this->request->getPost('party_name'),
                'mobile_number'  => $this->request->getPost('mobile_number'),
                'created_by'     => session()->get('user_id'),
                'is_split'       => $isSplitPayment ? 1 : 0,
                'schedule_amount' => $actualAmount,
            ];

            if ($status === 'paid' || $isSplitPayment) {
                $expenseData['paid_date'] = $this->request->getPost('payment_date') ?? date('Y-m-d');
            }

            $this->expenseModel->insert($expenseData);
            $expenseId = $this->expenseModel->getInsertID();

            // Handle GST
            if ($this->request->getPost('apply_gst') == '1') {
                $gstAmount = $this->request->getPost('gst_amount') ?? 0;
                if ($isSplitPayment && $gstAmount > 0 && $plannedAmount > 0) {
                    $gstAmount = ($paidAmount / $plannedAmount) * $gstAmount;
                }
                $taxModel = new TaxModel();
                $taxModel->insert([
                    'taxable_id'     => $expenseId,
                    'taxable_type'   => 'App\Models\Expense',
                    'tax_type'       => 'gst',
                    'tax_percentage' => $this->request->getPost('gst_percentage') ?? 0,
                    'tax_amount'     => $gstAmount,
                    'amount_paid'    => 0,
                    'payment_status' => 'not_received',
                    'direction'      => 'expense',
                ]);
            }

            // Handle TDS
            if ($this->request->getPost('apply_tds') == '1') {
                $tdsAmount = $this->request->getPost('tds_amount') ?? 0;
                if ($isSplitPayment && $tdsAmount > 0 && $plannedAmount > 0) {
                    $tdsAmount = ($paidAmount / $plannedAmount) * $tdsAmount;
                }
                $taxModel = new TaxModel();
                $taxModel->insert([
                    'taxable_id'     => $expenseId,
                    'taxable_type'   => 'App\Models\Expense',
                    'tax_type'       => 'tds',
                    'tax_percentage' => $this->request->getPost('tds_percentage') ?? 0,
                    'tax_amount'     => $tdsAmount,
                    'amount_paid'    => $this->request->getPost('tds_status') == 'paid' ? $tdsAmount : 0,
                    'payment_status' => $this->request->getPost('tds_status') == 'paid' ? 'paid' : 'not_received',
                    'direction'      => 'expense',
                ]);
            }

            // Create balance expense for split payment
            if ($isSplitPayment && $balanceAmount > 0) {
                $balanceBaseAmount = $actualAmount - $paidBaseAmount;
                $balanceData = $expenseData;
                $balanceData['expense_name']   = $expenseData['expense_name'] . ' - Balance';
                $balanceData['planned_amount'] = $balanceAmount;
                $balanceData['actual_amount']  = $balanceBaseAmount;
                $balanceData['status']         = 'pending';
                $balanceData['due_date']       = $this->request->getPost('new_due_date') ?? date('Y-m-d', strtotime('+30 days'));
                $balanceData['paid_date']      = null;
                $balanceData['is_split']       = 1;
                $balanceData['parent_id']      = $expenseId;
                $this->expenseModel->insert($balanceData);
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
                            'expense_id' => $expenseId,
                            'file_name'  => $file->getClientName(),
                            'file_path'  => 'uploads/receipts/' . $newName,
                            'file_type'  => $file->getClientExtension(),
                            'file_size'  => $this->companyHelper->formatBytes($file->getSize()),
                        ]);
                    }
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                return $this->fail('Failed to create expense');
            }

            return $this->respondCreated([
                'success' => true,
                'message' => $isSplitPayment ? 'Expense created with split payment.' : 'Expense saved successfully!',
            ]);
        } catch (\Exception $e) {
            $db->transRollback();
            return $this->fail('Error: ' . $e->getMessage());
        }
    }

    public function edit($id = null)
    {
        $expense = $this->expenseModel->find($id);
        if (!$expense) {
            return $this->failNotFound('Expense not found');
        }

        $taxModel = new TaxModel();
        $gstTax = $taxModel->where('taxable_id', $id)->where('taxable_type', 'App\Models\Expense')
            ->where('tax_type', 'gst')->where('direction', 'expense')->first();
        $tdsTax = $taxModel->where('taxable_id', $id)->where('taxable_type', 'App\Models\Expense')
            ->where('tax_type', 'tds')->where('direction', 'expense')->first();

        return $this->respond([
            'success' => true,
            'expense' => array_merge($expense, [
                'gst_percentage' => $gstTax['tax_percentage'] ?? 18,
                'gst_amount'     => $gstTax['tax_amount'] ?? 0,
                'tds_percentage' => $tdsTax['tax_percentage'] ?? 10,
                'tds_amount'     => $tdsTax['tax_amount'] ?? 0,
                'tds_status'     => $tdsTax['payment_status'] ?? 'not_received',
            ]),
        ]);
    }

    public function update($id = null)
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $expense = $this->expenseModel->find($id);
            if (!$expense) return $this->failNotFound('Expense not found');

            $data = $this->request->getPost();
            unset($data['_method']);

            $this->expenseModel->update($id, [
                'expense_name'   => $data['expense_name'] ?? $expense['expense_name'],
                'company_id'     => $data['company_id'] ?? $expense['company_id'],
                'category_id'    => $data['category_id'] ?? $expense['category_id'],
                'actual_amount'  => $data['actual_amount'] ?? $expense['actual_amount'],
                'planned_amount' => $data['grand_total'] ?? $expense['planned_amount'],
                'status'         => $data['status'] ?? $expense['status'],
                'payment_mode'   => $data['payment_mode'] ?? $expense['payment_mode'],
                'party_name'     => $data['party_name'] ?? $expense['party_name'],
                'mobile_number'  => $data['mobile_number'] ?? $expense['mobile_number'],
            ]);

            $taxModel = new TaxModel();
            $taxModel->where('taxable_id', $id)
                ->where('taxable_type', 'App\Models\Expense')
                ->delete();

            if (isset($data['apply_gst']) && $data['apply_gst'] == '1') {
                $taxModel->insert([
                    'taxable_id' => $id,
                    'taxable_type' => 'App\Models\Expense',
                    'tax_type' => 'gst',
                    'tax_percentage' => $data['gst_percentage'] ?? 0,
                    'tax_amount' => $data['gst_amount'] ?? 0,
                    'direction' => 'expense',
                    'payment_status' => 'not_received',
                ]);
            }

            if (isset($data['apply_tds']) && $data['apply_tds'] == '1') {
                $taxModel->insert([
                    'taxable_id' => $id,
                    'taxable_type' => 'App\Models\Expense',
                    'tax_type' => 'tds',
                    'tax_percentage' => $data['tds_percentage'] ?? 0,
                    'tax_amount' => $data['tds_amount'] ?? 0,
                    'direction' => 'expense',
                    'payment_status' => $data['tds_status'] ?? 'not_received',
                ]);
            }

            $db->transComplete();

            return $this->respond(['success' => true, 'message' => 'Expense updated successfully!']);
        } catch (\Exception $e) {
            $db->transRollback();
            return $this->fail('Error: ' . $e->getMessage());
        }
    }

    public function delete($id = null)
    {
        $expense = $this->expenseModel->find($id);

        if (! $expense) {
            return $this->failNotFound('Expense not found');
        }

        $taxModel = new TaxModel();
        $taxModel->where('taxable_id', $id)->delete();

        $receiptModel = new ReceiptModel();
        $receiptModel->where('expense_id', $id)->delete();

        if (! $this->expenseModel->delete($id)) {
            return $this->fail('Failed to delete expense');
        }

        return $this->respondDeleted([
            'success' => true,
            'message' => 'Expense deleted successfully!',
        ]);
    }

    public function markPaid($id = null)
    {
        $expense = $this->expenseModel->find($id);
        if (!$expense) return $this->failNotFound('Expense not found');

        $this->expenseModel->update($id, [
            'status'    => 'paid',
            'paid_date' => date('Y-m-d'),
        ]);

        return $this->respond(['success' => true, 'message' => 'Expense marked as paid!']);
    }

    public function splitHistory($id = null)
    {
        $expense = $this->expenseModel->find($id);
        if (!$expense) return $this->failNotFound('Expense not found');

        $children = $this->expenseModel->where('parent_id', $id)->orderBy('created_at', 'desc')->findAll();
        $parent = null;
        if ($expense['parent_id']) {
            $parent = $this->expenseModel->find($expense['parent_id']);
            $children = $this->expenseModel->where('parent_id', $expense['parent_id'])->orderBy('created_at', 'desc')->findAll();
        }

        $totalPaid = array_sum(array_map(fn($c) => $c['status'] === 'paid' ? $c['planned_amount'] : 0, $children));
        $totalBalance = array_sum(array_map(fn($c) => $c['status'] !== 'paid' ? $c['planned_amount'] : 0, $children));

        return $this->respond([
            'success'        => true,
            'current_expense' => $expense,
            'parent_expense' => $parent,
            'children'       => $children,
            'summary'        => [
                'original_amount' => $expense['schedule_amount'] ?? $expense['planned_amount'],
                'total_paid'      => $totalPaid,
                'total_balance'   => $totalBalance,
                'split_count'     => count($children),
            ],
        ]);
    }

    public function getSummary()
    {
        return $this->respond(['success' => true, 'message' => 'Summary endpoint']);
    }

    public function getTable()
    {
        return $this->respond(['success' => true]);
    }

    public function export()
    {
        // CSV export
        $companyIds = $this->companyHelper->getUserCompanyIds();
        $expenses = $this->expenseModel->whereIn('company_id', $companyIds)->orderBy('created_at', 'desc')->findAll();

        $filename = 'expenses_' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Name', 'Amount', 'Status', 'Date', 'Company']);
        foreach ($expenses as $exp) {
            fputcsv($output, [$exp['id'], $exp['expense_name'], $exp['actual_amount'], $exp['status'], $exp['created_at'], $exp['company_id']]);
        }
        fclose($output);
        exit;
    }
}
