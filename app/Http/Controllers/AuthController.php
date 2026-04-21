<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Show the login form
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Handle login request - Hardcoded untuk prototype
     */
    public function login(Request $request)
    {
        $username = $request->input('username');
        $password = $request->input('password');

        // Hardcoded credentials untuk prototype
        $ADMIN_USERNAME = 'admin';
        $ADMIN_PASSWORD = 'admin';

        // Validasi
        if (empty($username) || empty($password)) {
            return back()->withErrors([
                'username' => 'Username dan password harus diisi.',
            ])->onlyInput('username');
        }

        // Check credentials
        if ($username === $ADMIN_USERNAME && $password === $ADMIN_PASSWORD) {
            // Login berhasil - simpan ke session
            session(['user' => ['name' => 'Administrator', 'username' => $username]]);
            return redirect()->intended('/dashboard');
        }

        // Login gagal
        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ])->onlyInput('username');
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        session()->forget('user');
        session()->invalidate();
        session()->regenerateToken();

        return redirect('/login');
    }
}

    

