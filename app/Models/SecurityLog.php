<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecurityLog extends Model
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
        'user_agent',
        'event_type',
        'context',
        'risk_score',
        'metadata',
    ];
    
    /**
     * 应该转换为原生类型的属性
     *
     * @var array
     */
    protected $casts = [
        'metadata' => 'array',
        'risk_score' => 'integer',
    ];
    
    /**
     * 获取相关的用户
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * 获取事件类型的友好名称
     * 
     * @return string
     */
    public function getEventName()
    {
        $eventTypes = [
            'login' => '登录',
            'logout' => '登出',
            'register' => '注册',
            'password_reset' => '重置密码',
            'password_change' => '修改密码',
            'mfa_enabled' => '启用多因素认证',
            'mfa_disabled' => '禁用多因素认证',
            'mfa_verification_success' => '多因素认证成功',
            'mfa_verification_failed' => '多因素认证失败',
            'mfa_primary_changed' => '修改主要多因素认证方法',
            'recovery_codes_generated' => '生成恢复代码',
            'recovery_code_used' => '使用恢复代码',
            'recovery_code_verification_failed' => '恢复代码验证失败',
            'device_binding' => '设备绑定',
            'device_unbinding' => '设备解绑',
            'payment_attempt' => '支付尝试',
            'payment_success' => '支付成功',
            'payment_failed' => '支付失败',
            'security_check' => '安全检查',
            'high_risk_activity' => '高风险活动',
            'api_key_rotation' => 'API密钥轮换',
        ];
        
        return $eventTypes[$this->event_type] ?? $this->event_type;
    }
    
    /**
     * 获取上下文的友好名称
     * 
     * @return string
     */
    public function getContextName()
    {
        $contexts = [
            'auth' => '认证',
            'mfa' => '多因素认证',
            'device' => '设备管理',
            'payment' => '支付',
            'api' => 'API',
            'admin' => '管理',
        ];
        
        return $contexts[$this->context] ?? $this->context;
    }
    
    /**
     * 检查日志是否为高风险事件
     * 
     * @return bool
     */
    public function isHighRisk()
    {
        return $this->risk_score > 70;
    }
    
    /**
     * 获取风险等级描述
     * 
     * @return string
     */
    public function getRiskLevel()
    {
        if ($this->risk_score > 90) {
            return '危险';
        } elseif ($this->risk_score > 70) {
            return '高风险';
        } elseif ($this->risk_score > 50) {
            return '中风险';
        } elseif ($this->risk_score > 30) {
            return '低风险';
        } else {
            return '安全';
        }
    }
    
    /**
     * 获取风险等级颜色
     * 
     * @return string
     */
    public function getRiskColor()
    {
        if ($this->risk_score > 90) {
            return 'danger';
        } elseif ($this->risk_score > 70) {
            return 'warning';
        } elseif ($this->risk_score > 50) {
            return 'primary';
        } elseif ($this->risk_score > 30) {
            return 'info';
        } else {
            return 'success';
        }
    }
}
