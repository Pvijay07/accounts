<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CompanyModel;
use App\Models\UserModel;
use CodeIgniter\API\ResponseTrait;

class UserController extends BaseController
{
    use ResponseTrait;

    protected UserModel $userModel;
    protected CompanyModel $companyModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->companyModel = new CompanyModel();
    }

    public function index()
    {
        $search = trim((string) $this->request->getGet('search'));
        $role = (string) $this->request->getGet('role');
        $status = (string) $this->request->getGet('status');
        $companyId = (string) $this->request->getGet('company');

        $builder = $this->userModel
            ->select('users.*, companies.name as company_name')
            ->join('companies', 'companies.id = users.company_id', 'left');

        if ($search !== '') {
            $builder = $builder->groupStart()
                ->like('users.name', $search)
                ->orLike('users.email', $search)
                ->groupEnd();
        }

        if (in_array($role, ['admin', 'manager', 'ca', 'user'], true)) {
            $builder = $builder->where('users.role', $role);
        }

        if (in_array($status, ['active', 'inactive'], true)) {
            $builder = $builder->where('users.status', $status);
        }

        if ($companyId !== '' && ctype_digit($companyId)) {
            $builder = $builder->where('users.company_id', (int) $companyId);
        }

        $users = $builder
            ->orderBy('users.created_at', 'desc')
            ->paginate(10);

        return view('Admin/users', [
            'users' => $users,
            'pager' => $this->userModel->pager,
            'companies' => $this->companyModel->where('status', 'active')->orderBy('name', 'asc')->findAll(),
            'roles' => ['admin', 'manager', 'ca', 'user'],
            'filters' => [
                'search' => $search,
                'role' => $role,
                'status' => $status,
                'company' => $companyId,
            ],
        ]);
    }

    public function edit($id = null)
    {
        $user = $this->userModel->find($id);

        if (! $user) {
            return $this->failNotFound('User not found');
        }

        return $this->respond([
            'success' => true,
            'user' => $user,
        ]);
    }

    public function store()
    {
        $rules = [
            'name' => 'required|min_length[3]|max_length[255]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[8]',
            'role' => 'required|in_list[admin,manager,ca,user]',
            'status' => 'required|in_list[active,inactive]',
            'company_id' => 'permit_empty|integer',
        ];

        if (! $this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data = $this->normalizePayload($this->request->getPost());

        if (! $this->userModel->insert($data)) {
            return $this->failValidationErrors($this->userModel->errors() ?: ['error' => 'Failed to create user']);
        }

        return $this->respondCreated([
            'success' => true,
            'message' => 'User created successfully!',
        ]);
    }

    public function update($id = null)
    {
        $user = $this->userModel->find($id);

        if (! $user) {
            return $this->failNotFound('User not found');
        }

        $rules = [
            'name' => 'required|min_length[3]|max_length[255]',
            'email' => "required|valid_email|is_unique[users.email,id,{$id}]",
            'password' => 'permit_empty|min_length[8]',
            'role' => 'required|in_list[admin,manager,ca,user]',
            'status' => 'required|in_list[active,inactive]',
            'company_id' => 'permit_empty|integer',
        ];

        if (! $this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data = $this->normalizePayload($this->request->getPost());

        if (empty($data['password'])) {
            unset($data['password']);
        }

        if (! $this->userModel->update($id, $data)) {
            return $this->failValidationErrors($this->userModel->errors() ?: ['error' => 'Failed to update user']);
        }

        return $this->respond([
            'success' => true,
            'message' => 'User updated successfully!',
        ]);
    }

    public function updateStatus($id = null)
    {
        $user = $this->userModel->find($id);

        if (! $user) {
            return $this->failNotFound('User not found');
        }

        $status = (string) $this->request->getPost('status');

        if (! in_array($status, ['active', 'inactive'], true)) {
            return $this->failValidationErrors(['status' => 'Invalid status provided']);
        }

        if (! $this->userModel->update($id, ['status' => $status])) {
            return $this->fail('Failed to update user status');
        }

        return $this->respond([
            'success' => true,
            'message' => 'User status updated successfully!',
        ]);
    }

    public function delete($id = null)
    {
        if ((int) $id === (int) session()->get('user_id')) {
            return $this->failForbidden('You cannot delete your own account.');
        }

        $user = $this->userModel->find($id);

        if (! $user) {
            return $this->failNotFound('User not found');
        }

        if (! $this->userModel->delete($id)) {
            return $this->fail('Failed to delete user');
        }

        return $this->respondDeleted([
            'success' => true,
            'message' => 'User deleted successfully!',
        ]);
    }

    private function normalizePayload(array $data): array
    {
        if (array_key_exists('company_id', $data) && $data['company_id'] === '') {
            $data['company_id'] = null;
        }

        return $data;
    }
}
