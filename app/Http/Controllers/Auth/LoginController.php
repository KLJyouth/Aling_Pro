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
     * ��ʾ��¼��
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view("auth.login");
    }

    /**
     * �����¼����
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

        // ���Ե�¼
        if (Auth::attempt([
            "email" => $request->email,
            "password" => $request->password,
            "status" => "active"
        ], $request->filled("remember"))) {
            $request->session()->regenerate();
            
            // ��¼��¼��Ϣ
            $user = Auth::user();
            $user->recordLogin($request->ip());
            
            // �����û���ɫ�ض���
            if ($user->role === "admin") {
                return redirect()->intended("/admin/dashboard");
            }
            
            return redirect()->intended("/dashboard");
        }

        // ��¼ʧ��
        Log::warning("��¼ʧ��", [
            "email" => $request->email,
            "ip" => $request->ip(),
            "user_agent" => $request->userAgent()
        ]);

        throw ValidationException::withMessages([
            "email" => [trans("auth.failed")],
        ]);
    }

    /**
     * �����û��ǳ�
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
