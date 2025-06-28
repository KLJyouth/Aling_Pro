<?php

namespace App\Models\Security;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 安全威胁模型
 */
class SecurityThreat extends Model
{
    use HasFactory;
    
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'security_threats';
    
    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'threat_type',
        'severity',
        'confidence',
        'details',
        'source_ip',
        'target',
        'status',
        'response_actions',
        'resolved_at',
    ];
    
    /**
     * 应该被转换为日期的属性
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'resolved_at',
    ];
    
    /**
     * 应该被转换为原生类型的属性
     *
     * @var array
     */
    protected $casts = [
        'confidence' => 'float',
        'response_actions' => 'array',
    ];
    
    /**
     * 威胁类型常量
     */
    const TYPE_MALWARE = 'malware';
    const TYPE_INTRUSION = 'intrusion';
    const TYPE_DATA_EXFILTRATION = 'data_exfiltration';
    const TYPE_DENIAL_OF_SERVICE = 'denial_of_service';
    const TYPE_INSIDER_THREAT = 'insider_threat';
    const TYPE_UNKNOWN = 'unknown';
    
    /**
     * 严重程度常量
     */
    const SEVERITY_LOW = 'low';
    const SEVERITY_MEDIUM = 'medium';
    const SEVERITY_HIGH = 'high';
    
    /**
     * 状态常量
     */
    const STATUS_DETECTED = 'detected';
    const STATUS_ANALYZING = 'analyzing';
    const STATUS_RESPONDING = 'responding';
    const STATUS_CONTAINED = 'contained';
    const STATUS_RESOLVED = 'resolved';
    const STATUS_FALSE_POSITIVE = 'false_positive';
    
    /**
     * 获取威胁类型列表
     * 
     * @return array 威胁类型列表
     */
    public static function getThreatTypes(): array
    {
        return [
            self::TYPE_MALWARE => '恶意软件',
            self::TYPE_INTRUSION => '入侵',
            self::TYPE_DATA_EXFILTRATION => '数据泄露',
            self::TYPE_DENIAL_OF_SERVICE => '拒绝服务',
            self::TYPE_INSIDER_THREAT => '内部威胁',
            self::TYPE_UNKNOWN => '未知威胁',
        ];
    }
    
    /**
     * 获取严重程度列表
     * 
     * @return array 严重程度列表
     */
    public static function getSeverityLevels(): array
    {
        return [
            self::SEVERITY_LOW => '低',
            self::SEVERITY_MEDIUM => '中',
            self::SEVERITY_HIGH => '高',
        ];
    }
    
    /**
     * 获取状态列表
     * 
     * @return array 状态列表
     */
    public static function getStatusList(): array
    {
        return [
            self::STATUS_DETECTED => '已检测',
            self::STATUS_ANALYZING => '分析中',
            self::STATUS_RESPONDING => '响应中',
            self::STATUS_CONTAINED => '已控制',
            self::STATUS_RESOLVED => '已解决',
            self::STATUS_FALSE_POSITIVE => '误报',
        ];
    }
    
    /**
     * 获取威胁类型中文名称
     * 
     * @return string 威胁类型中文名称
     */
    public function getThreatTypeNameAttribute(): string
    {
        $types = self::getThreatTypes();
        return $types[$this->threat_type] ?? $types[self::TYPE_UNKNOWN];
    }
    
    /**
     * 获取严重程度中文名称
     * 
     * @return string 严重程度中文名称
     */
    public function getSeverityNameAttribute(): string
    {
        $levels = self::getSeverityLevels();
        return $levels[$this->severity] ?? $levels[self::SEVERITY_LOW];
    }
    
    /**
     * 获取状态中文名称
     * 
     * @return string 状态中文名称
     */
    public function getStatusNameAttribute(): string
    {
        $statuses = self::getStatusList();
        return $statuses[$this->status] ?? $statuses[self::STATUS_DETECTED];
    }
    
    /**
     * 获取严重程度颜色
     * 
     * @return string 严重程度颜色
     */
    public function getSeverityColorAttribute(): string
    {
        switch ($this->severity) {
            case self::SEVERITY_HIGH:
                return 'danger';
            case self::SEVERITY_MEDIUM:
                return 'warning';
            case self::SEVERITY_LOW:
                return 'info';
            default:
                return 'secondary';
        }
    }
    
    /**
     * 获取状态颜色
     * 
     * @return string 状态颜色
     */
    public function getStatusColorAttribute(): string
    {
        switch ($this->status) {
            case self::STATUS_DETECTED:
                return 'warning';
            case self::STATUS_ANALYZING:
                return 'info';
            case self::STATUS_RESPONDING:
                return 'primary';
            case self::STATUS_CONTAINED:
                return 'success';
            case self::STATUS_RESOLVED:
                return 'success';
            case self::STATUS_FALSE_POSITIVE:
                return 'secondary';
            default:
                return 'secondary';
        }
    }
    
    /**
     * 标记为已解决
     * 
     * @return bool 是否成功
     */
    public function markAsResolved(): bool
    {
        $this->status = self::STATUS_RESOLVED;
        $this->resolved_at = now();
        return $this->save();
    }
    
    /**
     * 标记为误报
     * 
     * @return bool 是否成功
     */
    public function markAsFalsePositive(): bool
    {
        $this->status = self::STATUS_FALSE_POSITIVE;
        $this->resolved_at = now();
        return $this->save();
    }
    
    /**
     * 添加响应操作
     * 
     * @param array $action 响应操作
     * @return bool 是否成功
     */
    public function addResponseAction(array $action): bool
    {
        $actions = $this->response_actions ?? [];
        $action['timestamp'] = now()->toDateTimeString();
        $actions[] = $action;
        $this->response_actions = $actions;
        return $this->save();
    }
    
    /**
     * 范围查询：按威胁类型
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type 威胁类型
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('threat_type', $type);
    }
    
    /**
     * 范围查询：按严重程度
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $severity 严重程度
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfSeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }
    
    /**
     * 范围查询：按状态
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status 状态
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfStatus($query, $status)
    {
        return $query->where('status', $status);
    }
    
    /**
     * 范围查询：活跃威胁
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [self::STATUS_RESOLVED, self::STATUS_FALSE_POSITIVE]);
    }
    
    /**
     * 范围查询：已解决威胁
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeResolved($query)
    {
        return $query->whereIn('status', [self::STATUS_RESOLVED, self::STATUS_FALSE_POSITIVE]);
    }
    
    /**
     * 范围查询：高危威胁
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHighRisk($query)
    {
        return $query->where('severity', self::SEVERITY_HIGH);
    }
} 