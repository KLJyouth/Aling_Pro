<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Membership\ReferralService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /**
     * 显示注册表单
     *
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm()
    {
        return view("auth.register");
    }

    /**
     * 处理注册请求
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:255",
            "email" => "required|string|email|max:255|unique:users",
            "password" => "required|string|min:8|confirmed",
            "referral_code" => "nullable|string|exists:users,referral_code",
            "terms" => "required|accepted",
        ], [
            "terms.required" => "您必须同意服务条款和隐私政策",
            "terms.accepted" => "您必须同意服务条款和隐私政策",
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->except("password", "password_confirmation"));
        }

        try {
            // 创建用户
            $user = User::create([
                "name" => $request->name,
                "email" => $request->email,
                "password" => Hash::make($request->password),
                "role" => "user",
                "status" => "active",
            ]);

            // 处理推荐码
            if ($request->filled("referral_code")) {
                $referralService = app(ReferralService::class);
                $referralService->processReferral($user, $request->referral_code);
            }

            // 触发注册事件（用于发送验证邮件）
            event(new Registered($user));

            // 自动登录
            Auth::login($user);

            // 记录注册信息
            $user->recordLogin($request->ip());

            return redirect("/dashboard")->with('status', '注册成功！我们已向您的邮箱发送了一封验证邮件，请查收并验证您的邮箱。');
        } catch (\Exception $e) {
            Log::error("用户注册失败", [
                "error" => $e->getMessage(),
                "email" => $request->email,
                "ip" => $request->ip()
            ]);

            return redirect()->back()
                ->withErrors(["email" => "注册失败，请稍后再试"])
                ->withInput($request->except("password", "password_confirmation"));
        }
    }

    /**
     * 处理推荐注册
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processReferral(Request $request)
    {
        if (!$request->filled("ref")) {
            return redirect()->route("register");
        }

        $referralCode = $request->input("ref");
        
        // 检查推荐码是否存在
        $referrer = User::where("referral_code", $referralCode)->first();
        
        if (!$referrer) {
            return redirect()->route("register");
        }
        
        // 保存推荐码到session
        $request->session()->put("referral_code", $referralCode);
        
        return redirect()->route("register")->with("referral_code", $referralCode);
    }
    
    /**
     * 验证用户邮箱
     *
     * @param  string  $id
     * @param  string  $hash
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verifyEmail($id, $hash)
    {
        $user = User::findOrFail($id);
        
        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return redirect()->route('login')
                ->with('error', '验证链接无效');
        }
        
        if ($user->hasVerifiedEmail()) {
            return redirect()->route('dashboard')
                ->with('status', '您的邮箱已经验证过了');
        }
        
        $user->markEmailAsVerified();
        
        return redirect()->route('dashboard')
            ->with('status', '邮箱验证成功！');
    }
    
    /**
     * 重新发送验证邮件
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resendVerificationEmail(Request $request)
    {
        $user = Auth::user();
        
        if ($user->hasVerifiedEmail()) {
            return redirect()->route('dashboard')
                ->with('status', '您的邮箱已经验证过了');
        }
        
        $user->sendEmailVerificationNotification();
        
        return back()->with('status', '验证邮件已发送，请查收您的邮箱');
    }
}
