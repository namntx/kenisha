<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    // Chỉ cho phép đăng nhập cho Admin và Agent
    protected function authenticated(Request $request, $user)
    {
        if ($user->isCustomer()) {
            // Đăng xuất nếu là khách hàng
            $this->guard()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect()->route('login')
                ->with('error', 'Khách hàng không được phép đăng nhập vào hệ thống. Vui lòng liên hệ đại lý của bạn.');
        }
        
        return redirect()->intended($this->redirectPath());
    }
}