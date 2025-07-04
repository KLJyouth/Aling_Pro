<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Mail\EmailVerification;
use App\Models\User\UserVerification;

class EmailController extends Controller
{
    /**
     * 显示邮箱绑定页面
     *
     * @return \Illuminate\Http\Response
     */
    public function showBindForm()
    {
        $user = Auth::user();
        
        return view("user.email.bind", compact("user"));
    }
    
    /**
     * 处理邮箱绑定请求
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bind(Request $request)
    {
        $user = Auth::user();
        
        // 验证输入
        $validator = Validator::make($request->all(), [
            "email" => "required|string|email|max:255|unique:users,email," . $user->id,
            "password" => "required|string",
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // 验证密码
        if (!Hash::check($request->input("password"), $user->password)) {
            return redirect()->back()
                ->withErrors(["password" => "密码不正确"])
                ->withInput();
        }
        
        $email = $request->input("email");
        
        // 如果邮箱未变更，直接返回
        if ($user->email === $email && $user->email_verified_at) {
            return redirect()->back()
                ->with("success", "邮箱已绑定");
        }
        
        // 更新邮箱
        $user->email = $email;
        $user->email_verified_at = null;
        $user->save();
        
        // 生成验证码
        $code = Str::random(6);
        $token = Str::random(64);
        
        // 保存验证记录
        UserVerification::where("user_id", $user->id)
            ->where("type", "email")
            ->delete();
        
        UserVerification::create([
            "user_id" => $user->id,
            "type" => "email",
            "token" => $token,
            "code" => $code,
            "expires_at" => now()->addHours(24),
        ]);
        
        // 发送验证邮件
        Mail::to($email)->send(new EmailVerification($user, $code, $token));
        
        return redirect()->route("user.email.verify")
            ->with("success", "验证邮件已发送，请查收并完成验证");
    }

    
    /**
     * 显示邮箱验证页面
     *
     * @return \Illuminate\Http\Response
     */
    public function showVerifyForm()
    {
        $user = Auth::user();
        
        if ($user->email_verified_at) {
            return redirect()->route("user.profile")
                ->with("success", "邮箱已验证");
        }
        
        return view("user.email.verify", compact("user"));
    }
    
    /**
     * 处理邮箱验证请求
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verify(Request $request)
    {
        $user = Auth::user();
        
        // 验证输入
        $validator = Validator::make($request->all(), [
            "code" => "required|string|size:6",
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $code = $request->input("code");
        
        // 查找验证记录
        $verification = UserVerification::where("user_id", $user->id)
            ->where("type", "email")
            ->where("code", $code)
            ->where("expires_at", ">", now())
            ->first();
        
        if (!$verification) {
            return redirect()->back()
                ->withErrors(["code" => "验证码无效或已过期"])
                ->withInput();
        }
        
        // 更新用户邮箱验证状态
        $user->email_verified_at = now();
        $user->save();
        
        // 删除验证记录
        $verification->delete();
        
        return redirect()->route("user.profile")
            ->with("success", "邮箱验证成功");
    }
    
    /**
     * 重新发送验证邮件
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resend()
    {
        $user = Auth::user();
        
        if ($user->email_verified_at) {
            return redirect()->route("user.profile")
                ->with("success", "邮箱已验证");
        }
        
        if (!$user->email) {
            return redirect()->route("user.email.bind")
                ->with("error", "请先绑定邮箱");
        }
        
        // 检查是否在短时间内多次请求
        $lastVerification = UserVerification::where("user_id", $user->id)
            ->where("type", "email")
            ->orderBy("created_at", "desc")
            ->first();
        
        if ($lastVerification && $lastVerification->created_at->diffInMinutes(now()) < 1) {
            return redirect()->back()
                ->with("error", "请求过于频繁，请稍后再试");
        }
        
        // 生成验证码
        $code = Str::random(6);
        $token = Str::random(64);
        
        // 保存验证记录
        UserVerification::where("user_id", $user->id)
            ->where("type", "email")
            ->delete();
        
        UserVerification::create([
            "user_id" => $user->id,
            "type" => "email",
            "token" => $token,
            "code" => $code,
            "expires_at" => now()->addHours(24),
        ]);
        
        // 发送验证邮件
        Mail::to($user->email)->send(new EmailVerification($user, $code, $token));
        
        return redirect()->route("user.email.verify")
            ->with("success", "验证邮件已重新发送，请查收并完成验证");
    }
    
    /**
     * 通过链接验证邮箱
     *
     * @param string $token
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verifyByToken($token)
    {
        // 查找验证记录
        $verification = UserVerification::where("token", $token)
            ->where("type", "email")
            ->where("expires_at", ">", now())
            ->first();
        
        if (!$verification) {
            return redirect()->route("user.email.verify")
                ->with("error", "验证链接无效或已过期");
        }
        
        $user = User::find($verification->user_id);
        
        if (!$user) {
            return redirect()->route("login")
                ->with("error", "用户不存在");
        }
        
        // 更新用户邮箱验证状态
        $user->email_verified_at = now();
        $user->save();
        
        // 删除验证记录
        $verification->delete();
        
        // 如果用户已登录，直接跳转到个人资料页
        if (Auth::id() === $user->id) {
            return redirect()->route("user.profile")
                ->with("success", "邮箱验证成功");
        }
        
        // 否则跳转到登录页
        return redirect()->route("login")
            ->with("success", "邮箱验证成功，请登录");
    }
}
