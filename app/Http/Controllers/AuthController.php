<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function showLoginForm(Request $request)
    {
        // Jika sudah login, redirect ke home
        if ($request->session()->has('logged_in')) {
            return redirect()->route('home');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'user' => 'required',
            'password' => 'required',
        ]);

        $username = $request->input('user');
        $password = $request->input('password');

        // Cek kredensial (contoh sederhana)
        if ($username === 'rikoharyadi' && $password === '12345678') {
            // Simpan status login di session
            $request->session()->put('logged_in', true);
            $request->session()->put('username', $username);

            // Redirect ke halaman home
            return redirect()->route('home');
        }

        return back()->with('error', 'Username atau password salah.');
    }

    public function logout(Request $request)
    {
        $request->session()->flush();
        return redirect()->route('login');
    }
}
