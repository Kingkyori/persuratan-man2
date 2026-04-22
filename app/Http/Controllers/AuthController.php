<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
     * Handle login request - Connected to Database Tabel Login
     */
    public function login(Request $request)
    {
        $username = trim($request->input('username'));
        $password = trim($request->input('password'));

        // Validasi
        if (empty($username) || empty($password)) {
            return back()->withErrors([
                'username' => 'Username dan password harus diisi.',
            ])->onlyInput('username');
        }

        // Cari user dari tabel login di database
        $user = DB::table('login')
                  ->where('username', $username)
                  ->first();

        // Check user dan password (case-insensitive comparison)
        if ($user && strtoupper(trim($user->password)) === strtoupper($password)) {
            // Login berhasil - simpan ke session
            session(['user' => [
                'id' => $user->id,
                'username' => $user->username
            ]]);
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

    

