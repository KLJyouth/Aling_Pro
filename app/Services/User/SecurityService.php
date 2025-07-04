<?php

namespace App\Services\User;

use App\Models\User;
use App\Models\User\UserCredential;
use App\Models\User\UserSession;
use App\Models\User\UserSecurityLog;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Str;

class SecurityService
{
    /**
     * 设置双因素认证
     *
     * @param int $userId
     * @return array
     */
    public function setupTwoFactor($userId)
    {
        $user = User::findOrFail($userId);
        
        // 检查是否已设置
        $existingCredential = UserCredential::byUser($userId)
            ->byType("totp")
            ->active()
            ->first();
            
        if ($existingCredential) {
            throw new \Exception("您已设置双因素认证");
        }
        
        // 生成密钥
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();
        
        // 创建凭证
        $credential = UserCredential::create([
            "user_id" => $userId,
            "type" => "totp",
            "identifier" => "Google Authenticator",
            "secret" => $secret,
            "is_primary" => true,
            "is_active" => false,
        ]);
        
        // 生成二维码URL
        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config("app.name"),
            $user->email,
            $secret
        );
        
        // 生成恢复码
        $recoveryCodes = $this->generateRecoveryCodes($userId);
        
        // 记录安全日志
        UserSecurityLog::success($userId, "2fa_setup");
        
        return [
            "credential_id" => $credential->id,
            "secret" => $secret,
            "qr_code_url" => $qrCodeUrl,
            "recovery_codes" => $recoveryCodes,
        ];
    }
    
    /**
     * 验证双因素认证
     *
     * @param int $userId
     * @param string $code
     * @return bool
     */
    public function verifyTwoFactor($userId, $code)
    {
        $credential = UserCredential::byUser($userId)
            ->byType("totp")
            ->active()
            ->first();
            
        if (!$credential) {
            UserSecurityLog::failure($userId, "2fa_challenge", [
                "reason" => "No active TOTP credential found",
            ]);
            return false;
        }
        
        $google2fa = new Google2FA();
        
        $valid = $google2fa->verifyKey(
            $credential->decrypted_secret,
            $code
        );
        
        if ($valid) {
            // 记录使用
            $credential->recordUsage();
            
            // 记录安全日志
            UserSecurityLog::success($userId, "2fa_challenge");
        } else {
            // 记录安全日志
            UserSecurityLog::failure($userId, "2fa_challenge", [
                "reason" => "Invalid code",
            ]);
        }
        
        return $valid;
    }
    
    /**
     * 激活双因素认证
     *
     * @param int $userId
     * @param string $code
     * @return bool
     */
    public function activateTwoFactor($userId, $code)
    {
        $credential = UserCredential::byUser($userId)
            ->byType("totp")
            ->where("is_active", false)
            ->first();
            
        if (!$credential) {
            UserSecurityLog::failure($userId, "2fa_setup", [
                "reason" => "No pending TOTP credential found",
            ]);
            return false;
        }
        
        $google2fa = new Google2FA();
        
        $valid = $google2fa->verifyKey(
            $credential->decrypted_secret,
            $code
        );
        
        if ($valid) {
            // 激活凭证
            $credential->update([
                "is_active" => true,
                "last_used_at" => now(),
            ]);
            
            // 记录安全日志
            UserSecurityLog::success($userId, "2fa_setup");
            
            return true;
        } else {
            // 记录安全日志
            UserSecurityLog::failure($userId, "2fa_setup", [
                "reason" => "Invalid code",
            ]);
            
            return false;
        }
    }
    
    /**
     * 禁用双因素认证
     *
     * @param int $userId
     * @param string $password
     * @return bool
     */
    public function disableTwoFactor($userId, $password)
    {
        $user = User::findOrFail($userId);
        
        // 验证密码
        if (!Hash::check($password, $user->password)) {
            UserSecurityLog::failure($userId, "2fa_disable", [
                "reason" => "Invalid password",
            ]);
            return false;
        }
        
        // 删除所有TOTP凭证
        UserCredential::byUser($userId)
            ->byType("totp")
            ->delete();
            
        // 删除恢复码
        UserCredential::byUser($userId)
            ->byType("recovery_code")
            ->delete();
            
        // 记录安全日志
        UserSecurityLog::success($userId, "2fa_disable");
        
        return true;
    }
    
    /**
     * 生成恢复码
     *
     * @param int $userId
     * @param int $count
     * @return array
     */
    public function generateRecoveryCodes($userId, $count = 8)
    {
        // 删除现有恢复码
        UserCredential::byUser($userId)
            ->byType("recovery_code")
            ->delete();
            
        $codes = [];
        
        for ($i = 0; $i < $count; $i++) {
            $code = Str::random(10);
            $codes[] = $code;
            
            UserCredential::create([
                "user_id" => $userId,
                "type" => "recovery_code",
                "identifier" => "Recovery Code " . ($i + 1),
                "secret" => $code,
                "is_active" => true,
            ]);
        }
        
        return $codes;
    }
    
    /**
     * 使用恢复码
     *
     * @param int $userId
     * @param string $code
     * @return bool
     */
    public function useRecoveryCode($userId, $code)
    {
        $credentials = UserCredential::byUser($userId)
            ->byType("recovery_code")
            ->active()
            ->get();
            
        foreach ($credentials as $credential) {
            if (Hash::check($code, $credential->decrypted_secret)) {
                // 使用后删除
                $credential->delete();
                
                // 记录安全日志
                UserSecurityLog::success($userId, "recovery_code_use");
                
                return true;
            }
        }
        
        // 记录安全日志
        UserSecurityLog::failure($userId, "recovery_code_use", [
            "reason" => "Invalid recovery code",
        ]);
        
        return false;
    }
    
    /**
     * 添加受信任设备
     *
     * @param int $userId
     * @param string $userAgent
     * @param string $ipAddress
     * @return UserCredential
     */
    public function addTrustedDevice($userId, $userAgent, $ipAddress)
    {
        $deviceId = md5($userAgent . $ipAddress . time());
        
        $deviceType = "other";
        if (strpos($userAgent, "Mobile") !== false) {
            $deviceType = "mobile";
        } elseif (strpos($userAgent, "Tablet") !== false) {
            $deviceType = "tablet";
        } elseif (strpos($userAgent, "Windows") !== false || strpos($userAgent, "Macintosh") !== false) {
            $deviceType = "desktop";
        }
        
        $credential = UserCredential::create([
            "user_id" => $userId,
            "type" => "trusted_device",
            "identifier" => $deviceType,
            "secret" => $deviceId,
            "metadata" => [
                "user_agent" => $userAgent,
                "ip_address" => $ipAddress,
                "device_type" => $deviceType,
            ],
            "is_active" => true,
        ]);
        
        // 记录安全日志
        UserSecurityLog::success($userId, "credential_add", [
            "type" => "trusted_device",
        ]);
        
        return $credential;
    }
    
    /**
     * 验证受信任设备
     *
     * @param int $userId
     * @param string $deviceId
     * @return bool
     */
    public function verifyTrustedDevice($userId, $deviceId)
    {
        $credential = UserCredential::byUser($userId)
            ->byType("trusted_device")
            ->active()
            ->where("secret", $deviceId)
            ->first();
            
        if ($credential) {
            // 记录使用
            $credential->recordUsage();
            
            return true;
        }
        
        return false;
    }
    
    /**
     * 删除受信任设备
     *
     * @param int $userId
     * @param int $credentialId
     * @return bool
     */
    public function removeTrustedDevice($userId, $credentialId)
    {
        $credential = UserCredential::where("id", $credentialId)
            ->where("user_id", $userId)
            ->byType("trusted_device")
            ->first();
            
        if (!$credential) {
            return false;
        }
        
        $credential->delete();
        
        // 记录安全日志
        UserSecurityLog::success($userId, "credential_remove", [
            "type" => "trusted_device",
        ]);
        
        return true;
    }
    
    /**
     * 记录用户会话
     *
     * @param int $userId
     * @param string $sessionId
     * @param string $userAgent
     * @param string $ipAddress
     * @return UserSession
     */
    public function recordSession($userId, $sessionId, $userAgent, $ipAddress)
    {
        // 设置之前的会话为非当前
        UserSession::where("user_id", $userId)
            ->where("is_current", true)
            ->update(["is_current" => false]);
            
        $deviceType = "other";
        if (strpos($userAgent, "Mobile") !== false) {
            $deviceType = "mobile";
        } elseif (strpos($userAgent, "Tablet") !== false) {
            $deviceType = "tablet";
        } elseif (strpos($userAgent, "Windows") !== false || strpos($userAgent, "Macintosh") !== false) {
            $deviceType = "desktop";
        }
        
        // 创建新会话
        return UserSession::create([
            "user_id" => $userId,
            "session_id" => $sessionId,
            "ip_address" => $ipAddress,
            "user_agent" => $userAgent,
            "device_type" => $deviceType,
            "is_current" => true,
            "last_activity" => now(),
        ]);
    }
    
    /**
     * 更新会话活动
     *
     * @param string $sessionId
     * @return bool
     */
    public function updateSessionActivity($sessionId)
    {
        $session = UserSession::where("session_id", $sessionId)->first();
        
        if ($session) {
            $session->updateActivity();
            return true;
        }
        
        return false;
    }
    
    /**
     * 撤销会话
     *
     * @param int $userId
     * @param string $sessionId
     * @return bool
     */
    public function revokeSession($userId, $sessionId)
    {
        $session = UserSession::where("user_id", $userId)
            ->where("session_id", $sessionId)
            ->first();
            
        if ($session) {
            $session->delete();
            
            // 记录安全日志
            UserSecurityLog::success($userId, "session_revoke");
            
            return true;
        }
        
        return false;
    }
    
    /**
     * 撤销所有其他会话
     *
     * @param int $userId
     * @param string $currentSessionId
     * @return int
     */
    public function revokeOtherSessions($userId, $currentSessionId)
    {
        $count = UserSession::where("user_id", $userId)
            ->where("session_id", "!=", $currentSessionId)
            ->count();
            
        UserSession::where("user_id", $userId)
            ->where("session_id", "!=", $currentSessionId)
            ->delete();
            
        // 记录安全日志
        UserSecurityLog::success($userId, "session_revoke", [
            "count" => $count,
            "type" => "other_sessions",
        ]);
        
        return $count;
    }
    
    /**
     * 获取用户会话列表
     *
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserSessions($userId)
    {
        return UserSession::byUser($userId)
            ->orderBy("is_current", "desc")
            ->orderBy("last_activity", "desc")
            ->get();
    }
    
    /**
     * 获取用户安全日志
     *
     * @param int $userId
     * @param array $filters
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getUserSecurityLogs($userId, array $filters = [])
    {
        $query = UserSecurityLog::byUser($userId);
        
        // 应用过滤器
        if (isset($filters["action"])) {
            $query->byAction($filters["action"]);
        }
        
        if (isset($filters["status"])) {
            $query->byStatus($filters["status"]);
        }
        
        // 排序
        $sortField = $filters["sort_field"] ?? "created_at";
        $sortDirection = $filters["sort_direction"] ?? "desc";
        
        $query->orderBy($sortField, $sortDirection);
        
        // 分页
        $perPage = $filters["per_page"] ?? 15;
        
        return $query->paginate($perPage);
    }
    
    /**
     * 获取用户凭证列表
     *
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserCredentials($userId)
    {
        return UserCredential::byUser($userId)
            ->active()
            ->orderBy("type")
            ->orderBy("created_at", "desc")
            ->get();
    }
    
    /**
     * 检查用户是否启用了双因素认证
     *
     * @param int $userId
     * @return bool
     */
    public function hasTwoFactorEnabled($userId)
    {
        return UserCredential::byUser($userId)
            ->byType("totp")
            ->active()
            ->exists();
    }
}
