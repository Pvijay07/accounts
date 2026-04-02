<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ExpenseTypeModel;
use App\Models\CompanyModel;
use CodeIgniter\API\ResponseTrait;

class ExpenseTypeController extends BaseController
{
    use ResponseTrait;

    protected $expenseTypeModel;

    public function __construct()
    {
        $this->expenseTypeModel = new ExpenseTypeModel();
    }

    public function index()
    {
        $types = $this->expenseTypeModel->orderBy('created_at', 'desc')->findAll();
        $companyModel = new CompanyModel();
        $companies = $companyModel->getActiveCompanies();

        return view('Admin/expense_types', [
            'expenseTypes' => $types,
            'companies'    => $companies,
        ]);
    }

    public function store()
    {
        $rules = [
            'name'     => 'required|max_length[255]',
            'category' => 'required',
        ];
        
        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $applicableCompanies = $this->request->getPost('applicable_companies');
        $inserted = $this->expenseTypeModel->insert([
            'name'                  => $this->request->getPost('name'),
            'category'              => $this->request->getPost('category'),
            'amount_type'           => $this->request->getPost('amount_type') ?? 'fixed',
            'default_amount'        => $this->request->getPost('default_amount') ?? 0,
            'reminder_days'         => $this->request->getPost('reminder_days') ?? 5,
            'applicable_companies'  => $applicableCompanies ? json_encode($applicableCompanies) : null,
            'status'                => 'active',
            'is_recurring'          => $this->request->getPost('is_recurring') ? 1 : 0,
        ]);

        if ($inserted) {
            return $this->respondCreated(['success' => true, 'message' => 'Expense type created successfully!']);
        }
        return $this->fail('Failed to create expense type');
    }

    public function edit($id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        $type = $this->expenseTypeModel->find($id);
        if (!$type) {
            return $this->response->setJSON(['success' => false, 'message' => 'Expense type not found']);
        }
        
        // Decode for view
        if (!empty($type['applicable_companies']) && is_string($type['applicable_companies'])) {
            $type['applicable_companies'] = json_decode($type['applicable_companies'], true) ?: [];
        }

        return $this->response->setJSON(['success' => true, 'expenseType' => $type]);
    }

    public function update($id = null)
    {
        $type = $this->expenseTypeModel->find($id);
        if (!$type) {
            return $this->fail('Expense type not found', 404);
        }

        $applicableCompanies = $this->request->getPost('applicable_companies');
        $updated = $this->expenseTypeModel->update($id, [
            'name'                 => $this->request->getPost('name'),
            'category'             => $this->request->getPost('category'),
            'amount_type'          => $this->request->getPost('amount_type') ?? 'fixed',
            'default_amount'       => $this->request->getPost('default_amount') ?? 0,
            'reminder_days'        => $this->request->getPost('reminder_days') ?? 5,
            'applicable_companies' => $applicableCompanies ? json_encode($applicableCompanies) : null,
            'is_recurring'         => $this->request->getPost('is_recurring') ? 1 : 0,
        ]);

        if ($updated) {
            return $this->respond(['success' => true, 'message' => 'Expense type updated!']);
        }
        return $this->fail('Update failed');
    }

    public function delete($id = null)
    {
        if ($this->expenseTypeModel->delete($id)) {
            return $this->respondDeleted(['success' => true, 'message' => 'Expense type deleted!']);
        }
        return $this->fail('Delete failed');
    }
}
