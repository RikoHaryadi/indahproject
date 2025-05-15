<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\login;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

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

  // Cari user di tabel "login"
    $user = Login::where('username', $username)->first();

    // Jika user ditemukan dan password cocok
    if ($user && Hash::check($password, $user->password)) {
        // Simpan status login di session
        $request->session()->put('logged_in', true);
        $request->session()->put('user_id',     $user->id);
        $request->session()->put('username',    $user->username);
        $request->session()->put('user_level',  $user->level);

        // Redirect ke halaman home (atau dashboard)
        return redirect()->route('home');
    }

    return back()->with('error', 'Username atau password salah.');
}

    public function logout(Request $request)
    {
        $request->session()->flush();
        return redirect()->route('login');
    }

     public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|string|alpha_dash|unique:login,username',
            'password' => 'required|string|min:6|confirmed',
            'level'    => ['required', Rule::in([1,2,3])]
        ]);

        login::create([
            'username' => $data['username'],
            'password' => Hash::make($data['password']),
            'level'    => $data['level'],
        ]);

        return redirect()->route('login')
                         ->with('success','Akun berhasil dibuat, silakan login.');
    }
}
