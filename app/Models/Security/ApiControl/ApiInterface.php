<?php

namespace App\Models\Security\ApiControl;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApiInterface extends Model
{
    use HasFactory;

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'api_interfaces';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'path',
        'method',
        'group',
        'description',
        'status',
        'version',
        'rate_limit',
        'auth_required',
        'sort_order',
        'options',
    ];

    /**
     * 应该被转换成原生类型的属性
     *
     * @var array
     */
    protected $casts = [
        'auth_required' => 'boolean',
        'options' => 'json',
    ];

    /**
     * 获取关联的访问控制规则
     *
     * @return HasMany
     */
    public function accessControls(): HasMany
    {
        return $this->hasMany(ApiAccessControl::class, 'interface_id');
    }

    /**
     * 获取关联的安全审计记录
     *
     * @return HasMany
     */
    public function securityAudits(): HasMany
    {
        return $this->hasMany(ApiSecurityAudit::class, 'interface_id');
    }

    /**
     * 获取关联的异常检测配置
     *
     * @return HasMany
     */
    public function anomalyConfigs(): HasMany
    {
        return $this->hasMany(ApiAnomalyConfig::class, 'interface_id');
    }

    /**
     * 获取关联的异常事件
     *
     * @return HasMany
     */
    public function anomalyEvents(): HasMany
    {
        return $this->hasMany(ApiAnomalyEvent::class, 'interface_id');
    }

    /**
     * 获取活跃接口的查询作用域
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * 获取指定分类的查询作用域
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $category
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * 获取需要认证的接口的查询作用域
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRequiresAuth($query)
    {
        return $query->where('auth_required', true);
    }

    /**
     * 获取接口的完整路径（包含版本）
     *
     * @return string
     */
    public function getFullPathAttribute(): string
    {
        return '/' . $this->version . $this->path;
    }

    /**
     * 检查接口是否活跃
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * 按路径和方法查找接口
     *
     * @param string $path
     * @param string $method
     * @param string $version
     * @return self|null
     */
    public static function findByPathAndMethod(string $path, string $method, string $version = 'v1'): ?self
    {
        return self::where('path', $path)
            ->where('method', $method)
            ->where('version', $version)
            ->first();
    }

    /**
     * 获取接口参数
     */
    public function parameters()
    {
        return $this->hasMany(ApiInterfaceParameter::class);
    }

    /**
     * 获取接口响应
     */
    public function responses()
    {
        return $this->hasMany(ApiInterfaceResponse::class);
    }

    /**
     * 获取使用此接口的SDK
     */
    public function sdks()
    {
        return $this->belongsToMany(ApiSdk::class, 'api_sdk_interfaces');
    }

    /**
     * 获取接口请求日志
     */
    public function requestLogs()
    {
        return $this->hasMany(ApiRequestLog::class);
    }
    
    /**
     * 获取方法名称
     *
     * @return string
     */
    public function getMethodNameAttribute()
    {
        $methods = [
            'GET' => 'GET',
            'POST' => 'POST',
            'PUT' => 'PUT',
            'DELETE' => 'DELETE',
            'PATCH' => 'PATCH',
            'OPTIONS' => 'OPTIONS',
            'HEAD' => 'HEAD',
        ];
        
        return $methods[$this->method] ?? $this->method;
    }
    
    /**
     * 获取状态名称
     *
     * @return string
     */
    public function getStatusNameAttribute()
    {
        $statuses = [
            'active' => '启用',
            'inactive' => '禁用',
            'deprecated' => '已弃用',
            'development' => '开发中',
        ];
        
        return $statuses[$this->status] ?? $this->status;
    }
    
    /**
     * 获取接口完整URL
     *
     * @return string
     */
    public function getFullUrlAttribute()
    {
        return config('app.url') . $this->path;
    }
}
