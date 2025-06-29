<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserMfaMethod extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'method',
        'is_primary',
        'metadata',
        'last_used_at',
    ];
    
    /**
     * 应该转换为日期的属性
     *
     * @var array
     */
    protected $dates = [
        'last_used_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    
    /**
     * 应该转换为原生类型的属性
     *
     * @var array
     */
    protected $casts = [
        'is_primary' => 'boolean',
        'metadata' => 'array',
    ];
    
    /**
     * 获取拥有此MFA方法的用户
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * 获取MFA方法的友好名称
     * 
     * @return string
     */
    public function getFriendlyName()
    {
        switch ($this->method) {
            case 'app':
                return '认证应用';
            case 'sms':
                $phone = $this->metadata['phone_number'] ?? '';
                return '短信验证 (' . substr($phone, 0, 3) . '****' . substr($phone, -4) . ')';
            case 'email':
                $email = $this->metadata['email'] ?? '';
                return '邮箱验证 (' . substr($email, 0, 3) . '***' . strstr($email, '@') . ')';
            case 'fingerprint':
                return '指纹验证';
            default:
                return '未知方法';
        }
    }
    
    /**
     * 获取MFA方法的图标
     * 
     * @return string
     */
    public function getIcon()
    {
        switch ($this->method) {
            case 'app':
                return 'fa-mobile-alt';
            case 'sms':
                return 'fa-sms';
            case 'email':
                return 'fa-envelope';
            case 'fingerprint':
                return 'fa-fingerprint';
            default:
                return 'fa-shield-alt';
        }
    }
    
    /**
     * 检查MFA方法是否最近使用过（7天内）
     * 
     * @return bool
     */
    public function isRecentlyUsed()
    {
        return $this->last_used_at && $this->last_used_at->diffInDays(now()) <= 7;
    }
}
