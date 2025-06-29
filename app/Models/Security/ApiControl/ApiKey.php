<?php

namespace App\Models\Security\ApiControl;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApiKey extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * 与模型关联的数据表
     *
     * @var string
     */
    protected $table = 'api_keys';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'name',
        'key_prefix',
        'key_hash',
        'expiration_date',
        'rate_limit',
        'ip_restrictions',
        'permissions',
        'status',
        'last_used_at',
    ];

    /**
     * 应该被转换为日期的属性
     *
     * @var array
     */
    protected $dates = [
        'expiration_date',
        'last_used_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * 应该被转换为原生类型的属性
     *
     * @var array
     */
    protected $casts = [
        'permissions' => 'json',
    ];

    /**
     * 获取关联的用户
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 获取关联的API密钥日志
     */
    public function logs()
    {
        return $this->hasMany(ApiKeyLog::class);
    }

    /**
     * 获取关联的API请求日志
     */
    public function requestLogs()
    {
        return $this->hasMany(ApiRequestLog::class);
    }

    /**
     * 检查API密钥是否已过期
     *
     * @return bool
     */
    public function isExpired()
    {
        if (!$this->expiration_date) {
            return false;
        }
        
        return now()->gt($this->expiration_date);
    }

    /**
     * 检查API密钥是否处于活动状态
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->status === 'active' && !$this->isExpired();
    }

    /**
     * 检查IP地址是否在允许的范围内
     *
     * @param string $ip
     * @return bool
     */
    public function isIpAllowed($ip)
    {
        if (empty($this->ip_restrictions)) {
            return true;
        }

        $allowedIps = array_map('trim', explode(',', $this->ip_restrictions));
        
        foreach ($allowedIps as $allowedIp) {
            if ($allowedIp === $ip) {
                return true;
            }
            
            // 检查CIDR格式
            if (strpos($allowedIp, '/') !== false) {
                if ($this->ipInCidr($ip, $allowedIp)) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * 检查IP是否在CIDR范围内
     *
     * @param string $ip
     * @param string $cidr
     * @return bool
     */
    private function ipInCidr($ip, $cidr)
    {
        list($subnet, $mask) = explode('/', $cidr);
        
        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);
        $maskLong = ~((1 << (32 - $mask)) - 1);
        
        return ($ipLong & $maskLong) === ($subnetLong & $maskLong);
    }

    /**
     * 检查是否有指定的权限
     *
     * @param string $permission
     * @return bool
     */
    public function hasPermission($permission)
    {
        if (empty($this->permissions)) {
            return false;
        }
        
        return in_array($permission, $this->permissions) || in_array('*', $this->permissions);
    }

    /**
     * 更新最后使用时间
     *
     * @return void
     */
    public function updateLastUsed()
    {
        $this->last_used_at = now();
        $this->save();
    }
} 