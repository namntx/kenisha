<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Hiển thị form đăng nhập
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Xử lý đăng nhập
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Thử đăng nhập
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            // Đăng nhập thành công, tạo lại session
            $request->session()->regenerate();

            // Chuyển hướng đến trang dashboard
            return redirect()->intended('dashboard');
        }

        // Đăng nhập thất bại
        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }

    /**
     * Đăng xuất
     */
    public function logout(Request $request)
    {
        Auth::logout();

        // Hủy session
        $request->session()->invalidate();
        
        // Tạo lại token CSRF
        $request->session()->regenerateToken();

        // Chuyển hướng về trang chủ
        return redirect('/');
    }
}