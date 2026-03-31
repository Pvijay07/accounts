<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember', false);

        // Check if user exists
        $user = User::where('email', $credentials['email'])->first();
        if (!$user) {
            $this->logLoginAttempt($request, 'user_not_found');
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        // Check if user is active (if you have status field)
        if (array_key_exists('status', $user->getAttributes()) && $user->status !== 'active') {
            $this->logLoginAttempt($request, 'account_inactive');
            throw ValidationException::withMessages([
                'email' => __('Your account has been deactivated. Please contact administrator.'),
            ]);
        }

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            // Update last login
            $user->update(['last_login_at' => now()]);

            // Log successful login
            $this->logLoginAttempt($request, 'success', $user);
            // Redirect based on role
            return $this->authenticated($request, $user);
        }

        // Log failed attempt
        $this->logLoginAttempt($request, 'invalid_credentials', $user);

        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }

    protected function authenticated(Request $request, $user)
    {
        // Log the activity (if you have activity logging)
        if (class_exists('Spatie\Activitylog\ActivitylogServiceProvider')) {
            activity()
                ->causedBy($user)
                ->log('User logged in');
        }

        // Redirect based on user role
        switch ($user->role) {
            case 'admin':
                return redirect()->intended('/admin/dashboard');
            case 'manager':
                return redirect()->intended('/manager/dashboard');
            case 'ca':
                return redirect()->intended('/ca/dashboard');
            case 'user':
                return redirect()->intended('/ca/dashboard');
            default:
                return redirect()->intended('/dashboard');
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    protected function logLoginAttempt(Request $request, $status, $user = null)
    {
        // Log login attempts (you can save this to a database table)
        \Log::info('Login attempt', [
            'email' => $request->email,
            'status' => $status,
            'user_id' => $user ? $user->id : null,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now(),
        ]);
    }
}
