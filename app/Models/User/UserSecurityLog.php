<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class UserSecurityLog extends Model
{
    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        "user_id",
        "action",
        "status",
        "ip_address",
        "user_agent",
        "device_type",
        "location",
        "details",
    ];

    /**
     * 操作类型列表
     *
     * @var array
     */
    public static $actions = [
        "login" => "登录",
        "logout" => "登出",
        "register" => "注册",
        "password_change" => "修改密码",
        "password_reset" => "重置密码",
        "email_change" => "修改邮箱",
        "2fa_setup" => "设置双因素认证",
        "2fa_disable" => "禁用双因素认证",
        "2fa_challenge" => "双因素认证挑战",
        "recovery_code_use" => "使用恢复码",
        "account_lock" => "账号锁定",
        "account_unlock" => "账号解锁",
        "credential_add" => "添加安全凭证",
        "credential_remove" => "删除安全凭证",
        "session_revoke" => "撤销会话",
    ];

    /**
     * 获取关联的用户
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 获取操作名称
     *
     * @return string
     */
    public function getActionNameAttribute()
    {
        return self::$actions[$this->action] ?? $this->action;
    }

    /**
     * 获取状态标签HTML
     *
     * @return string
     */
    public function getStatusLabelAttribute()
    {
        $class = $this->status === "success" ? "success" : "danger";
        $text = $this->status === "success" ? "成功" : "失败";
        
        return "<span class=\"badge badge-{$class}\">{$text}</span>";
    }

    /**
     * 获取设备类型图标
     *
     * @return string
     */
    public function getDeviceIconAttribute()
    {
        $icons = [
            "desktop" => "fas fa-desktop",
            "laptop" => "fas fa-laptop",
            "tablet" => "fas fa-tablet-alt",
            "mobile" => "fas fa-mobile-alt",
            "other" => "fas fa-question-circle",
        ];
        
        return $icons[$this->device_type] ?? "fas fa-question-circle";
    }

    /**
     * 获取浏览器名称
     *
     * @return string|null
     */
    public function getBrowserNameAttribute()
    {
        if (!$this->user_agent) {
            return null;
        }
        
        $browsers = [
            "Chrome" => "Chrome",
            "Firefox" => "Firefox",
            "Safari" => "Safari",
            "Edge" => "Edge",
            "Opera" => "Opera",
            "MSIE" => "Internet Explorer",
        ];
        
        foreach ($browsers as $key => $name) {
            if (strpos($this->user_agent, $key) !== false) {
                return $name;
            }
        }
        
        return "Unknown";
    }

    /**
     * 记录安全日志
     *
     * @param int $userId
     * @param string $action
     * @param string $status
     * @param array $data
     * @return static
     */
    public static function log($userId, $action, $status = "success", $data = [])
    {
        $request = request();
        
        $deviceType = "other";
        if (strpos($request->userAgent(), "Mobile") !== false) {
            $deviceType = "mobile";
        } elseif (strpos($request->userAgent(), "Tablet") !== false) {
            $deviceType = "tablet";
        } elseif (strpos($request->userAgent(), "Windows") !== false || strpos($request->userAgent(), "Macintosh") !== false) {
            $deviceType = "desktop";
        }
        
        return self::create([
            "user_id" => $userId,
            "action" => $action,
            "status" => $status,
            "ip_address" => $request->ip(),
            "user_agent" => $request->userAgent(),
            "device_type" => $deviceType,
            "details" => !empty($data) ? json_encode($data) : null,
        ]);
    }

    /**
     * 记录成功日志
     *
     * @param int $userId
     * @param string $action
     * @param array $data
     * @return static
     */
    public static function success($userId, $action, $data = [])
    {
        return self::log($userId, $action, "success", $data);
    }

    /**
     * 记录失败日志
     *
     * @param int $userId
     * @param string $action
     * @param array $data
     * @return static
     */
    public static function failure($userId, $action, $data = [])
    {
        return self::log($userId, $action, "failed", $data);
    }

    /**
     * 作用域：按用户筛选
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where("user_id", $userId);
    }

    /**
     * 作用域：按操作筛选
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $action
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByAction($query, $action)
    {
        return $query->where("action", $action);
    }

    /**
     * 作用域：按状态筛选
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where("status", $status);
    }

    /**
     * 作用域：成功日志
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSuccessful($query)
    {
        return $query->where("status", "success");
    }

    /**
     * 作用域：失败日志
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFailed($query)
    {
        return $query->where("status", "failed");
    }
}
