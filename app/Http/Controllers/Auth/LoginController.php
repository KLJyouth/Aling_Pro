<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * 显示登录表单
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view("auth.login");
    }

    /**
     * 处理登录请求
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $request->validate([
            "email" => "required|email",
            "password" => "required|string",
        ]);

        // 尝试登录
        if (Auth::attempt([
            "email" => $request->email,
            "password" => $request->password,
            "status" => "active"
        ], $request->filled("remember"))) {
            $request->session()->regenerate();
            
            // 记录登录信息
            $user = Auth::user();
            $user->recordLogin($request->ip());
            
            // 根据用户角色重定向
            if ($user->role === "admin") {
                return redirect()->intended("/admin/dashboard");
            }
            
            return redirect()->intended("/dashboard");
        }

        // 登录失败
        Log::warning("登录失败", [
            "email" => $request->email,
            "ip" => $request->ip(),
            "user_agent" => $request->userAgent()
        ]);

        throw ValidationException::withMessages([
            "email" => [trans("auth.failed")],
        ]);
    }

    /**
     * 处理用户登出
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect("/");
    }
}
