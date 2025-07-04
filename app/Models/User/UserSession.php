<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class UserSession extends Model
{
    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        "user_id",
        "session_id",
        "ip_address",
        "user_agent",
        "device_type",
        "location",
        "is_current",
        "last_activity",
    ];

    /**
     * 应该被转换成原生类型的属性
     *
     * @var array
     */
    protected $casts = [
        "is_current" => "boolean",
        "last_activity" => "datetime",
    ];

    /**
     * 获取关联的用户
     */
    public function user()
    {
        return $this->belongsTo(User::class);
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
     * 获取操作系统名称
     *
     * @return string|null
     */
    public function getOsNameAttribute()
    {
        if (!$this->user_agent) {
            return null;
        }
        
        $systems = [
            "Windows" => "Windows",
            "Mac OS" => "macOS",
            "iPhone" => "iOS",
            "iPad" => "iOS",
            "Android" => "Android",
            "Linux" => "Linux",
        ];
        
        foreach ($systems as $key => $name) {
            if (strpos($this->user_agent, $key) !== false) {
                return $name;
            }
        }
        
        return "Unknown";
    }

    /**
     * 更新活动时间
     *
     * @return void
     */
    public function updateActivity()
    {
        $this->update([
            "last_activity" => now(),
        ]);
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
     * 作用域：当前会话
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCurrent($query)
    {
        return $query->where("is_current", true);
    }

    /**
     * 作用域：活跃会话
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $minutes
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query, $minutes = 15)
    {
        return $query->where("last_activity", ">=", now()->subMinutes($minutes));
    }
}
