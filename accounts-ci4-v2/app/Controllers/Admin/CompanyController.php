<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CompanyModel;
use App\Models\UserModel;
use CodeIgniter\API\ResponseTrait;

class CompanyController extends BaseController
{
    use ResponseTrait;

    protected CompanyModel $companyModel;
    protected UserModel $userModel;

    public function __construct()
    {
        $this->companyModel = new CompanyModel();
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $search = trim((string) $this->request->getGet('search'));
        $status = (string) $this->request->getGet('status');
        $sortBy = (string) ($this->request->getGet('sort_by') ?: 'created_at');
        $sortOrder = strtolower((string) ($this->request->getGet('sort_order') ?: 'desc')) === 'asc' ? 'asc' : 'desc';

        $sortable = ['name', 'status', 'created_at'];
        if (! in_array($sortBy, $sortable, true)) {
            $sortBy = 'created_at';
        }

        $builder = $this->companyModel
            ->select('companies.*, users.name as manager_name')
            ->join('users', 'users.id = companies.manager_id', 'left');

        if ($search !== '') {
            $builder = $builder->groupStart()
                ->like('companies.name', $search)
                ->orLike('companies.email', $search)
                ->orLike('users.name', $search)
                ->groupEnd();
        }

        if (in_array($status, ['active', 'inactive'], true)) {
            $builder = $builder->where('companies.status', $status);
        }

        $companies = $builder
            ->orderBy("companies.{$sortBy}", $sortOrder)
            ->paginate(10);

        return view('Admin/companies', [
            'companies' => $companies,
            'pager' => $this->companyModel->pager,
            'managers' => $this->userModel->where('role', 'manager')->orderBy('name', 'asc')->findAll(),
            'filters' => [
                'search' => $search,
                'status' => $status,
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
            ],
            'counts' => [
                'all' => (new CompanyModel())->countAllResults(),
                'active' => (new CompanyModel())->where('status', 'active')->countAllResults(),
                'inactive' => (new CompanyModel())->where('status', 'inactive')->countAllResults(),
            ],
        ]);
    }

    public function store()
    {
        $rules = [
            'name' => 'required|min_length[3]|max_length[255]',
            'email' => 'permit_empty|valid_email',
            'currency' => 'required|max_length[10]',
            'status' => 'required|in_list[active,inactive]',
            'manager_id' => 'permit_empty|integer',
            'website' => 'permit_empty|max_length[255]',
            'address' => 'permit_empty|max_length[1000]',
        ];

        if (! $this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data = $this->normalizePayload($this->request->getPost());
        $this->handleLogoUpload($data);

        if (! $this->companyModel->insert($data)) {
            return $this->failValidationErrors($this->companyModel->errors() ?: ['error' => 'Failed to create company']);
        }

        return $this->respondCreated([
            'success' => true,
            'message' => 'Company created successfully!',
        ]);
    }

    public function edit($id = null)
    {
        $company = $this->companyModel->find($id);

        if (! $company) {
            return $this->failNotFound('Company not found');
        }

        return $this->respond([
            'success' => true,
            'company' => $company,
        ]);
    }

    public function update($id = null)
    {
        $company = $this->companyModel->find($id);

        if (! $company) {
            return $this->failNotFound('Company not found');
        }

        $rules = [
            'name' => 'required|min_length[3]|max_length[255]',
            'email' => 'permit_empty|valid_email',
            'currency' => 'required|max_length[10]',
            'status' => 'required|in_list[active,inactive]',
            'manager_id' => 'permit_empty|integer',
            'website' => 'permit_empty|max_length[255]',
            'address' => 'permit_empty|max_length[1000]',
        ];

        if (! $this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data = $this->normalizePayload($this->request->getPost());
        unset($data['_method']);

        $this->handleLogoUpload($data);

        if (! $this->companyModel->update($id, $data)) {
            return $this->failValidationErrors($this->companyModel->errors() ?: ['error' => 'Failed to update company']);
        }

        return $this->respond([
            'success' => true,
            'message' => 'Company updated successfully!',
        ]);
    }

    public function delete($id = null)
    {
        $company = $this->companyModel->find($id);

        if (! $company) {
            return $this->failNotFound('Company not found');
        }

        if (! $this->companyModel->delete($id)) {
            return $this->fail('Failed to delete company');
        }

        return $this->respondDeleted([
            'success' => true,
            'message' => 'Company deleted successfully!',
        ]);
    }

    private function normalizePayload(array $data): array
    {
        if (array_key_exists('manager_id', $data) && $data['manager_id'] === '') {
            $data['manager_id'] = null;
        }

        if (array_key_exists('email', $data) && $data['email'] === '') {
            $data['email'] = null;
        }

        return $data;
    }

    private function handleLogoUpload(array &$data): void
    {
        $logo = $this->request->getFile('logo');

        if (! $logo || ! $logo->isValid() || $logo->hasMoved()) {
            return;
        }

        $target = FCPATH . 'uploads/company-logos';
        if (! is_dir($target)) {
            mkdir($target, 0777, true);
        }

        $newName = $logo->getRandomName();
        $logo->move($target, $newName);
        $data['logo_path'] = 'uploads/company-logos/' . $newName;
    }
}
