<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CategoryModel;
use CodeIgniter\API\ResponseTrait;

class CategoryController extends BaseController
{
    use ResponseTrait;

    protected $categoryModel;

    public function __construct()
    {
        $this->categoryModel = new CategoryModel();
    }

    public function index()
    {
        $data['categories'] = $this->categoryModel->findAll();
        return view('Admin/categories', $data);
    }

    public function store()
    {
        $rules = [
            'name'      => 'required|min_length[3]',
            'main_type' => 'required|in_list[income,expense]'
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data = $this->request->getPost();
        if ($this->categoryModel->insert($data)) {
            return $this->respondCreated(['success' => true, 'message' => 'Category created!']);
        }
        return $this->fail('Failed to create category');
    }

    public function update($id = null)
    {
        $data = $this->request->getPost();
        if ($this->categoryModel->update($id, $data)) {
            return $this->respond(['success' => true, 'message' => 'Category updated!']);
        }
        return $this->fail('Update failed');
    }

    public function delete($id = null)
    {
        if ($this->categoryModel->delete($id)) {
            return $this->respondDeleted(['success' => true, 'message' => 'Category deleted!']);
        }
        return $this->fail('Delete failed');
    }
}
