<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

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
    
    /**
     * 重定向到社交登录提供商
     *
     * @param  string  $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectToProvider($provider)
    {
        if (!in_array($provider, ['google', 'github'])) {
            return redirect()->route('login')->with('error', '不支持的登录方式');
        }
        
        return Socialite::driver($provider)->redirect();
    }

    /**
     * 处理从社交登录提供商返回的回调
     *
     * @param  string  $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleProviderCallback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
            
            // 查找或创建用户
            $user = User::where('email', $socialUser->getEmail())->first();
            
            if (!$user) {
                // 创建新用户
                $user = User::create([
                    'name' => $socialUser->getName(),
                    'email' => $socialUser->getEmail(),
                    'password' => Hash::make(Str::random(16)),
                    'role' => 'user',
                    'status' => 'active',
                ]);
            }
            
            // 登录用户
            Auth::login($user, true);
            
            // 记录登录信息
            $user->recordLogin(request()->ip());
            
            return redirect()->intended('/dashboard');
            
        } catch (\Exception $e) {
            Log::error('社交登录失败', [
                'provider' => $provider,
                'error' => $e->getMessage(),
                'ip' => request()->ip()
            ]);
            
            return redirect()->route('login')
                ->with('error', '社交登录失败，请稍后再试或使用其他登录方式');
        }
    }
}
