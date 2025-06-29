<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecurityAlert extends Model
{
    use HasFactory;
    
    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'ip_address',
        'alert_type',
        'severity',
        'description',
        'metadata',
        'is_resolved',
        'resolved_by',
        'resolved_at',
        'resolution_notes',
    ];
    
    /**
     * 应该转换为日期的属性
     *
     * @var array
     */
    protected $dates = [
        'resolved_at',
        'created_at',
        'updated_at',
    ];
    
    /**
     * 应该转换为原生类型的属性
     *
     * @var array
     */
    protected $casts = [
        'metadata' => 'array',
        'is_resolved' => 'boolean',
    ];
    
    /**
     * 获取相关的用户
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * 获取解决此警报的用户
     */
    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
    
    /**
     * 获取警报类型的友好名称
     * 
     * @return string
     */
    public function getAlertTypeName()
    {
        $alertTypes = [
            'suspicious_login' => '可疑登录',
            'failed_login_attempts' => '多次登录失败',
            'password_brute_force' => '密码暴力破解尝试',
            'unusual_location' => '异常地理位置',
            'vpn_proxy_detected' => '检测到VPN或代理',
            'high_risk_activity' => '高风险活动',
            'unusual_payment' => '异常支付',
            'api_abuse' => 'API滥用',
            'unusual_traffic' => '异常流量',
            'data_leak' => '数据泄露',
        ];
        
        return $alertTypes[$this->alert_type] ?? $this->alert_type;
    }
    
    /**
     * 获取严重程度的友好名称
     * 
     * @return string
     */
    public function getSeverityName()
    {
        $severities = [
            'low' => '低',
            'medium' => '中',
            'high' => '高',
            'critical' => '严重',
        ];
        
        return $severities[$this->severity] ?? $this->severity;
    }
    
    /**
     * 获取严重程度的颜色
     * 
     * @return string
     */
    public function getSeverityColor()
    {
        $colors = [
            'low' => 'info',
            'medium' => 'primary',
            'high' => 'warning',
            'critical' => 'danger',
        ];
        
        return $colors[$this->severity] ?? 'secondary';
    }
    
    /**
     * 解决警报
     * 
     * @param int $userId 解决警报的用户ID
     * @param string $notes 解决说明
     * @return bool
     */
    public function resolve(int $userId, string $notes = '')
    {
        $this->is_resolved = true;
        $this->resolved_by = $userId;
        $this->resolved_at = now();
        $this->resolution_notes = $notes;
        
        return $this->save();
    }
}
