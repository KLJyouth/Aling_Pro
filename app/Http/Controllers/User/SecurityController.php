<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\User\SecurityService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class SecurityController extends Controller
{
    protected $securityService;
    
    /**
     * 构造函数
     *
     * @param SecurityService $securityService
     */
    public function __construct(SecurityService $securityService)
    {
        $this->securityService = $securityService;
        $this->middleware("auth");
    }
    
    /**
     * 显示安全设置页面
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        $hasTwoFactor = $this->securityService->hasTwoFactorEnabled($user->id);
        $credentials = $this->securityService->getUserCredentials($user->id);
        $sessions = $this->securityService->getUserSessions($user->id);
        
        return view("user.security.index", [
            "user" => $user,
            "hasTwoFactor" => $hasTwoFactor,
            "credentials" => $credentials,
            "sessions" => $sessions,
        ]);
    }
    
    /**
     * 显示双因素认证设置页面
     *
     * @return \Illuminate\Http\Response
     */
    public function twoFactorSetup()
    {
        $user = Auth::user();
        
        // 检查是否已设置
        if ($this->securityService->hasTwoFactorEnabled($user->id)) {
            return redirect()->route("user.security.index")
                ->with("info", "您已设置双因素认证");
        }
        
        $setupData = $this->securityService->setupTwoFactor($user->id);
        
        return view("user.security.two-factor-setup", [
            "setupData" => $setupData,
        ]);
    }
    
    /**
     * 激活双因素认证
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function twoFactorActivate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "code" => "required|string|size:6",
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $user = Auth::user();
        $code = $request->input("code");
        
        $result = $this->securityService->activateTwoFactor($user->id, $code);
        
        if ($result) {
            return redirect()->route("user.security.index")
                ->with("success", "双因素认证已成功激活");
        }
        
        return redirect()->back()
            ->with("error", "验证码无效，请重试");
    }
    
    /**
     * 显示禁用双因素认证页面
     *
     * @return \Illuminate\Http\Response
     */
    public function twoFactorDisableForm()
    {
        $user = Auth::user();
        
        // 检查是否已设置
        if (!$this->securityService->hasTwoFactorEnabled($user->id)) {
            return redirect()->route("user.security.index")
                ->with("info", "您尚未设置双因素认证");
        }
        
        return view("user.security.two-factor-disable");
    }
    
    /**
     * 禁用双因素认证
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function twoFactorDisable(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "password" => "required|string",
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $user = Auth::user();
        $password = $request->input("password");
        
        $result = $this->securityService->disableTwoFactor($user->id, $password);
        
        if ($result) {
            return redirect()->route("user.security.index")
                ->with("success", "双因素认证已禁用");
        }
        
        return redirect()->back()
            ->with("error", "密码错误，请重试");
    }
    
    /**
     * 显示安全日志
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function logs(Request $request)
    {
        $filters = $request->only([
            "action",
            "status",
            "sort_field",
            "sort_direction",
            "per_page",
        ]);
        
        $logs = $this->securityService->getUserSecurityLogs(Auth::id(), $filters);
        
        return view("user.security.logs", [
            "logs" => $logs,
            "filters" => $filters,
        ]);
    }
    
    /**
     * 显示会话管理页面
     *
     * @return \Illuminate\Http\Response
     */
    public function sessions()
    {
        $sessions = $this->securityService->getUserSessions(Auth::id());
        
        return view("user.security.sessions", [
            "sessions" => $sessions,
        ]);
    }
    
    /**
     * 撤销会话
     *
     * @param Request $request
     * @param string $sessionId
     * @return \Illuminate\Http\Response
     */
    public function revokeSession(Request $request, $sessionId)
    {
        $user = Auth::user();
        
        // 不能撤销当前会话
        if ($sessionId === session()->getId()) {
            return redirect()->back()
                ->with("error", "无法撤销当前会话");
        }
        
        $result = $this->securityService->revokeSession($user->id, $sessionId);
        
        if ($result) {
            return redirect()->back()
                ->with("success", "会话已撤销");
        }
        
        return redirect()->back()
            ->with("error", "会话撤销失败");
    }
    
    /**
     * 撤销所有其他会话
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function revokeOtherSessions(Request $request)
    {
        $user = Auth::user();
        $currentSessionId = session()->getId();
        
        $count = $this->securityService->revokeOtherSessions($user->id, $currentSessionId);
        
        return redirect()->back()
            ->with("success", "已撤销 {$count} 个其他会话");
    }
    
    /**
     * 显示修改密码表单
     *
     * @return \Illuminate\Http\Response
     */
    public function changePasswordForm()
    {
        return view("user.security.change-password");
    }
    
    /**
     * 修改密码
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "current_password" => "required|string",
            "password" => "required|string|min:8|confirmed",
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $user = Auth::user();
        
        // 验证当前密码
        if (!Hash::check($request->input("current_password"), $user->password)) {
            return redirect()->back()
                ->withErrors(["current_password" => "当前密码错误"])
                ->withInput();
        }
        
        // 更新密码
        $user->password = Hash::make($request->input("password"));
        $user->save();
        
        // 记录安全日志
        $this->securityService->securityLog($user->id, "password_change", "success");
        
        return redirect()->route("user.security.index")
            ->with("success", "密码修改成功");
    }
}
