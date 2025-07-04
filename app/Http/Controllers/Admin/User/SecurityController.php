<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\User\SecurityService;
use App\Models\User\UserSecurityLog;
use App\Models\User\UserSession;
use App\Models\User\UserCredential;
use App\Models\User;
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
        $this->middleware("auth:admin");
    }
    
    /**
     * 显示用户安全信息
     *
     * @param int $userId
     * @return \Illuminate\Http\Response
     */
    public function index($userId)
    {
        $user = User::findOrFail($userId);
        $hasTwoFactor = $this->securityService->hasTwoFactorEnabled($userId);
        $credentials = $this->securityService->getUserCredentials($userId);
        $sessions = $this->securityService->getUserSessions($userId);
        
        return view("admin.users.security.index", [
            "user" => $user,
            "hasTwoFactor" => $hasTwoFactor,
            "credentials" => $credentials,
            "sessions" => $sessions,
        ]);
    }
    
    /**
     * 显示用户安全日志
     *
     * @param Request $request
     * @param int $userId
     * @return \Illuminate\Http\Response
     */
    public function logs(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        
        $filters = $request->only([
            "action",
            "status",
            "sort_field",
            "sort_direction",
            "per_page",
        ]);
        
        $logs = $this->securityService->getUserSecurityLogs($userId, $filters);
        
        return view("admin.users.security.logs", [
            "user" => $user,
            "logs" => $logs,
            "filters" => $filters,
            "actions" => UserSecurityLog::$actions,
        ]);
    }
    
    /**
     * 显示用户会话管理页面
     *
     * @param int $userId
     * @return \Illuminate\Http\Response
     */
    public function sessions($userId)
    {
        $user = User::findOrFail($userId);
        $sessions = $this->securityService->getUserSessions($userId);
        
        return view("admin.users.security.sessions", [
            "user" => $user,
            "sessions" => $sessions,
        ]);
    }
    
    /**
     * 撤销用户会话
     *
     * @param int $userId
     * @param string $sessionId
     * @return \Illuminate\Http\Response
     */
    public function revokeSession($userId, $sessionId)
    {
        $user = User::findOrFail($userId);
        $session = UserSession::where("user_id", $userId)
            ->where("session_id", $sessionId)
            ->firstOrFail();
            
        $result = $this->securityService->revokeSession($userId, $sessionId);
        
        if ($result) {
            return redirect()->back()
                ->with("success", "会话已撤销");
        }
        
        return redirect()->back()
            ->with("error", "会话撤销失败");
    }
    
    /**
     * 撤销用户所有会话
     *
     * @param int $userId
     * @return \Illuminate\Http\Response
     */
    public function revokeAllSessions($userId)
    {
        $user = User::findOrFail($userId);
        $sessions = UserSession::where("user_id", $userId)->get();
        
        foreach ($sessions as $session) {
            $this->securityService->revokeSession($userId, $session->session_id);
        }
        
        return redirect()->back()
            ->with("success", "所有会话已撤销");
    }
    
    /**
     * 显示用户凭证管理页面
     *
     * @param int $userId
     * @return \Illuminate\Http\Response
     */
    public function credentials($userId)
    {
        $user = User::findOrFail($userId);
        $credentials = $this->securityService->getUserCredentials($userId);
        
        return view("admin.users.security.credentials", [
            "user" => $user,
            "credentials" => $credentials,
            "types" => UserCredential::$types,
        ]);
    }
    
    /**
     * 禁用用户凭证
     *
     * @param int $userId
     * @param int $credentialId
     * @return \Illuminate\Http\Response
     */
    public function disableCredential($userId, $credentialId)
    {
        $user = User::findOrFail($userId);
        $credential = UserCredential::where("id", $credentialId)
            ->where("user_id", $userId)
            ->firstOrFail();
            
        $credential->update([
            "is_active" => false,
        ]);
        
        return redirect()->back()
            ->with("success", "凭证已禁用");
    }
    
    /**
     * 启用用户凭证
     *
     * @param int $userId
     * @param int $credentialId
     * @return \Illuminate\Http\Response
     */
    public function enableCredential($userId, $credentialId)
    {
        $user = User::findOrFail($userId);
        $credential = UserCredential::where("id", $credentialId)
            ->where("user_id", $userId)
            ->firstOrFail();
            
        $credential->update([
            "is_active" => true,
        ]);
        
        return redirect()->back()
            ->with("success", "凭证已启用");
    }
    
    /**
     * 删除用户凭证
     *
     * @param int $userId
     * @param int $credentialId
     * @return \Illuminate\Http\Response
     */
    public function deleteCredential($userId, $credentialId)
    {
        $user = User::findOrFail($userId);
        $credential = UserCredential::where("id", $credentialId)
            ->where("user_id", $userId)
            ->firstOrFail();
            
        $credential->delete();
        
        return redirect()->back()
            ->with("success", "凭证已删除");
    }
    
    /**
     * 重置用户双因素认证
     *
     * @param int $userId
     * @return \Illuminate\Http\Response
     */
    public function resetTwoFactor($userId)
    {
        $user = User::findOrFail($userId);
        
        // 删除所有TOTP凭证
        UserCredential::where("user_id", $userId)
            ->where("type", "totp")
            ->delete();
            
        // 删除恢复码
        UserCredential::where("user_id", $userId)
            ->where("type", "recovery_code")
            ->delete();
            
        return redirect()->back()
            ->with("success", "用户双因素认证已重置");
    }
    
    /**
     * 锁定用户账号
     *
     * @param int $userId
     * @return \Illuminate\Http\Response
     */
    public function lockAccount($userId)
    {
        $user = User::findOrFail($userId);
        
        $user->update([
            "is_locked" => true,
        ]);
        
        // 记录安全日志
        UserSecurityLog::success($userId, "account_lock", [
            "locked_by" => auth()->id(),
        ]);
        
        // 撤销所有会话
        $sessions = UserSession::where("user_id", $userId)->get();
        
        foreach ($sessions as $session) {
            $this->securityService->revokeSession($userId, $session->session_id);
        }
        
        return redirect()->back()
            ->with("success", "用户账号已锁定");
    }
    
    /**
     * 解锁用户账号
     *
     * @param int $userId
     * @return \Illuminate\Http\Response
     */
    public function unlockAccount($userId)
    {
        $user = User::findOrFail($userId);
        
        $user->update([
            "is_locked" => false,
        ]);
        
        // 记录安全日志
        UserSecurityLog::success($userId, "account_unlock", [
            "unlocked_by" => auth()->id(),
        ]);
        
        return redirect()->back()
            ->with("success", "用户账号已解锁");
    }
}
