<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class AuthController extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function login()
    {
        if (session()->get('isLoggedIn')) {
            return $this->redirectByRole(session()->get('role'));
        }
        return view('auth/login');
    }

    public function attemptLogin()
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $user = $this->userModel->where('email', $email)->first();

        if (!$user) {
            return redirect()->back()->withInput()->with('error', 'Invalid email or password.');
        }

        // Check if user is active
        if (isset($user['status']) && $user['status'] !== 'active') {
            return redirect()->back()->withInput()->with('error', 'Your account has been deactivated. Please contact administrator.');
        }

        // Verify password
        if (!password_verify($password, $user['password'])) {
            return redirect()->back()->withInput()->with('error', 'Invalid email or password.');
        }

        // Set session data
        $sessionData = [
            'user_id'    => $user['id'],
            'user_name'  => $user['name'],
            'user_email' => $user['email'],
            'role'       => $user['role'],
            'isLoggedIn' => true,
        ];
        session()->set($sessionData);

        // Update last login
        $this->userModel->update($user['id'], ['last_login_at' => date('Y-m-d H:i:s')]);

        // Log login attempt
        log_message('info', 'User logged in: ' . $email . ' (ID: ' . $user['id'] . ')');

        return $this->redirectByRole($user['role']);
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('login'))->with('success', 'You have been logged out.');
    }

    private function redirectByRole($role)
    {
        switch ($role) {
            case 'admin':
                return redirect()->to(base_url('admin/dashboard'));
            case 'manager':
                return redirect()->to(base_url('manager/dashboard'));
            case 'ca':
                return redirect()->to(base_url('ca/dashboard'));
            default:
                return redirect()->to(base_url('admin/dashboard'));
        }
    }
}
