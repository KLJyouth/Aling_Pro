<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserDevice extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'device_id',
        'device_name',
        'device_type',
        'device_model',
        'os_type',
        'os_version',
        'app_version',
        'device_fingerprint',
        'phone_number',
        'imei',
        'mac_address',
        'is_verified',
        'last_active_at',
    ];
    
    /**
     * 应该转换为日期的属性
     *
     * @var array
     */
    protected $dates = [
        'last_active_at',
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
        'is_verified' => 'boolean',
    ];
    
    /**
     * 获取拥有此设备的用户
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * 获取设备的登录历史
     */
    public function loginHistory()
    {
        return $this->hasMany(SecurityLog::class, 'device_id', 'device_id')
            ->where('event_type', 'login');
    }
    
    /**
     * 检查设备是否为新设备（30天内首次使用）
     * 
     * @return bool
     */
    public function isNew()
    {
        return $this->created_at->diffInDays(now()) <= 30;
    }
    
    /**
     * 检查设备是否为活跃设备（7天内有活动）
     * 
     * @return bool
     */
    public function isActive()
    {
        return $this->last_active_at && $this->last_active_at->diffInDays(now()) <= 7;
    }
    
    /**
     * 获取设备的友好名称
     * 
     * @return string
     */
    public function getFriendlyName()
    {
        if ($this->device_name) {
            return $this->device_name;
        }
        
        $name = $this->device_model ?: $this->device_type;
        $os = $this->os_type . ($this->os_version ? " {$this->os_version}" : '');
        
        return "{$name} ({$os})";
    }
}
