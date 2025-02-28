<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /**
     * Hiển thị form đăng ký
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Xử lý đăng ký
     */
    public function register(Request $request)
    {
        // Validate dữ liệu đầu vào
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // Tìm role "Agent"
        $agentRole = Role::where('name', 'Agent')->first();
        
        if (!$agentRole) {
            // Fallback nếu không tìm thấy
            return back()->with('error', 'Không tìm thấy role "Agent" trong hệ thống');
        }
        
        // Tạo user mới
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'phone' => $validatedData['phone'] ?? null,
            'password' => Hash::make($validatedData['password']),
            'role_id' => $agentRole->id, // Đăng ký mặc định sẽ là Agent
            'balance' => 0,
        ]);

        // Đăng nhập user ngay sau khi đăng ký
        auth()->login($user);

        // Chuyển hướng đến dashboard
        return redirect()->route('dashboard')
            ->with('success', 'Đăng ký tài khoản thành công');
    }
}