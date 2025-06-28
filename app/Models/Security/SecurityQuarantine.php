<?php

namespace App\Models\Security;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

/**
 * 安全隔离区模型
 * 
 * 用于存储和管理异常请求和文件
 */
class SecurityQuarantine extends Model
{
    use SoftDeletes;

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'security_quarantines';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'type', // 类型: request(请求), file(文件)
        'source', // 来源: IP地址或文件路径
        'content_hash', // 内容哈希
        'risk_level', // 风险等级: high(高), medium(中), low(低)
        'category', // 异常类别: malware(恶意软件), injection(注入攻击), dos(拒绝服务), unauthorized(未授权访问), etc
        'details', // 详细信息 (JSON)
        'status', // 状态: pending(待处理), analyzing(分析中), quarantined(已隔离), resolved(已解决), false_positive(误报)
        'ai_analysis', // AI分析结果 (JSON)
        'admin_notes', // 管理员备注
        'reviewed_by', // 审核人ID
        'resolved_at', // 解决时间
    ];

    /**
     * 应该被转换为日期的属性
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'resolved_at',
    ];

    /**
     * 应该被转换为原生类型的属性
     *
     * @var array
     */
    protected $casts = [
        'details' => 'array',
        'ai_analysis' => 'array',
    ];

    /**
     * 获取审核此异常的管理员
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * 获取相关的IP封禁记录
     */
    public function ipBans()
    {
        return $this->hasMany(SecurityIpBan::class, 'quarantine_id');
    }

    /**
     * 判断是否为高风险异常
     *
     * @return bool
     */
    public function isHighRisk()
    {
        return $this->risk_level === 'high';
    }

    /**
     * 判断是否为请求类型
     *
     * @return bool
     */
    public function isRequest()
    {
        return $this->type === 'request';
    }

    /**
     * 判断是否为文件类型
     *
     * @return bool
     */
    public function isFile()
    {
        return $this->type === 'file';
    }

    /**
     * 判断是否已解决
     *
     * @return bool
     */
    public function isResolved()
    {
        return $this->status === 'resolved' || $this->status === 'false_positive';
    }

    /**
     * 判断是否需要人工审核
     *
     * @return bool
     */
    public function needsReview()
    {
        return $this->status === 'pending' || $this->status === 'quarantined';
    }
} 