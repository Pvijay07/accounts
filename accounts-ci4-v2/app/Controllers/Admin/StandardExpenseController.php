<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CategoryModel;
use App\Models\CompanyModel;
use App\Models\ExpenseModel;
use App\Models\TaxModel;
use CodeIgniter\API\ResponseTrait;

class StandardExpenseController extends BaseController
{
    use ResponseTrait;

    protected ExpenseModel $expenseModel;

    public function __construct()
    {
        $this->expenseModel = new ExpenseModel();
    }

    public function index()
    {
        $companyModel = new CompanyModel();
        $categoryModel = new CategoryModel();

        $expenses = $this->expenseModel
            ->select('expenses.*, companies.name as company_name, categories.name as category_name')
            ->join('companies', 'companies.id = expenses.company_id', 'left')
            ->join('categories', 'categories.id = expenses.category_id', 'left')
            ->where('expenses.source', 'standard')
            ->orderBy('expenses.created_at', 'desc')
            ->paginate(10);

        return view('Admin/standard_expenses', [
            'expenses' => $expenses,
            'pager' => $this->expenseModel->pager,
            'companies' => $companyModel->where('status', 'active')->orderBy('name', 'asc')->findAll(),
            'categories' => $categoryModel->where('main_type', 'expense')->where('is_active', 1)->orderBy('name', 'asc')->findAll(),
        ]);
    }

    public function store()
    {
        $rules = [
            'expense_name' => 'required|max_length[255]',
            'company_id' => 'required|integer',
            'category_id' => 'required|integer',
            'planned_amount' => 'required|numeric',
            'actual_amount' => 'permit_empty|numeric',
            'frequency' => 'required|in_list[monthly,quarterly,yearly]',
            'due_day' => 'required|integer|greater_than[0]|less_than[32]',
        ];

        if (! $this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data = $this->request->getPost();
        $data['actual_amount'] = $data['actual_amount'] !== '' ? $data['actual_amount'] : $data['planned_amount'];
        $data['source'] = 'standard';
        $data['is_recurring'] = 1;
        $data['created_by'] = session()->get('user_id');
        $data['tax_type'] = $this->buildTaxType();

        $db = \Config\Database::connect();
        $db->transStart();

        $this->expenseModel->insert($data);
        $expenseId = $this->expenseModel->getInsertID();

        $this->syncTaxes($expenseId);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->fail('Failed to create standard expense');
        }

        return $this->respondCreated([
            'success' => true,
            'message' => 'Standard expense template created successfully!',
        ]);
    }

    public function update($id = null)
    {
        $expense = $this->expenseModel->find($id);

        if (! $expense) {
            return $this->failNotFound('Template not found');
        }

        $rules = [
            'expense_name' => 'required|max_length[255]',
            'planned_amount' => 'required|numeric',
            'frequency' => 'required|in_list[monthly,quarterly,yearly]',
            'due_day' => 'required|integer|greater_than[0]|less_than[32]',
        ];

        if (! $this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data = $this->request->getPost();
        $data['tax_type'] = $this->buildTaxType();

        if (! $this->expenseModel->update($id, $data)) {
            return $this->failValidationErrors($this->expenseModel->errors() ?: ['error' => 'Failed to update template']);
        }

        $this->syncTaxes((int) $id);

        return $this->respond([
            'success' => true,
            'message' => 'Standard expense template updated successfully!',
        ]);
    }

    public function delete($id = null)
    {
        $expense = $this->expenseModel->find($id);

        if (! $expense) {
            return $this->failNotFound('Template not found');
        }

        $taxModel = new TaxModel();
        $taxModel->where('taxable_id', $id)->delete();
        $this->expenseModel->delete($id);

        return $this->respondDeleted([
            'success' => true,
            'message' => 'Template deleted successfully!',
        ]);
    }

    public function generateExpenses()
    {
        try {
            $templates = $this->expenseModel
                ->where('source', 'standard')
                ->where('is_recurring', 1)
                ->findAll();

            $count = 0;

            foreach ($templates as $template) {
                $exists = $this->expenseModel
                    ->where('source', 'standard')
                    ->where('parent_id', $template['id'])
                    ->where('MONTH(due_date)', date('m'))
                    ->where('YEAR(due_date)', date('Y'))
                    ->first();

                if ($exists) {
                    continue;
                }

                $this->expenseModel->insert([
                    'company_id' => $template['company_id'],
                    'expense_name' => $template['expense_name'],
                    'category_id' => $template['category_id'],
                    'planned_amount' => $template['planned_amount'],
                    'actual_amount' => $template['actual_amount'] ?? $template['planned_amount'],
                    'due_date' => date('Y-m-') . sprintf('%02d', (int) $template['due_day']),
                    'status' => 'pending',
                    'source' => 'standard',
                    'parent_id' => $template['id'],
                    'created_by' => session()->get('user_id'),
                ]);
                $count++;
            }

            return $this->respond([
                'success' => true,
                'message' => "Generated {$count} expenses.",
            ]);
        } catch (\Throwable $e) {
            return $this->fail('Generation failed: ' . $e->getMessage());
        }
    }

    public function getCategories()
    {
        $direction = $this->request->getPost('direction');
        $categoryModel = new CategoryModel();
        $categories = $categoryModel->where('main_type', $direction)->where('is_active', 1)->findAll();

        return $this->respond([
            'success' => true,
            'categories' => $categories,
        ]);
    }

    public function getTaxDetails($id)
    {
        $taxModel = new TaxModel();
        $taxes = $taxModel->where('taxable_id', $id)->findAll();

        return $this->respond([
            'success' => true,
            'taxes' => $taxes,
        ]);
    }

    public function show($id = null)
    {
        $expense = $this->expenseModel->find($id);

        if (! $expense) {
            return $this->failNotFound('Template not found');
        }

        return $this->respond($expense);
    }

    private function buildTaxType(): ?string
    {
        $taxTypes = [];

        if ($this->request->getPost('apply_gst') == '1') {
            $taxTypes[] = 'GST';
        }

        if ($this->request->getPost('apply_tds') == '1') {
            $taxTypes[] = 'TDS';
        }

        return $taxTypes ? implode('+', $taxTypes) : null;
    }

    private function syncTaxes(int $expenseId): void
    {
        $taxModel = new TaxModel();
        $taxModel->where('taxable_id', $expenseId)->delete();

        if ($this->request->getPost('apply_gst') == '1') {
            $taxModel->insert([
                'taxable_id' => $expenseId,
                'taxable_type' => 'expense',
                'tax_type' => 'gst',
                'tax_percentage' => $this->request->getPost('gst_percentage') ?: 18,
                'tax_amount' => $this->request->getPost('gst_amount') ?: 0,
                'payment_status' => 'not_received',
            ]);
        }

        if ($this->request->getPost('apply_tds') == '1') {
            $taxModel->insert([
                'taxable_id' => $expenseId,
                'taxable_type' => 'expense',
                'tax_type' => 'tds',
                'tax_percentage' => $this->request->getPost('tds_percentage') ?: 10,
                'tax_amount' => $this->request->getPost('tds_amount') ?: 0,
                'payment_status' => 'not_received',
            ]);
        }
    }
}
