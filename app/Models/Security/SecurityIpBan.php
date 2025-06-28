<?php

namespace App\Models\Security;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

/**
 * IP封禁模型
 * 
 * 用于管理被封禁的IP地址
 */
class SecurityIpBan extends Model
{
    use SoftDeletes;

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'security_ip_bans';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'ip_address', // IP地址
        'reason', // 封禁原因
        'quarantine_id', // 关联的隔离区记录ID
        'banned_until', // 封禁截止时间 (null表示永久封禁)
        'banned_by', // 封禁操作人ID
        'details', // 详细信息 (JSON)
        'status', // 状态: active(生效中), expired(已过期), revoked(已撤销)
        'revoked_by', // 撤销操作人ID
        'revoked_at', // 撤销时间
        'revoke_reason', // 撤销原因
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
        'banned_until',
        'revoked_at',
    ];

    /**
     * 应该被转换为原生类型的属性
     *
     * @var array
     */
    protected $casts = [
        'details' => 'array',
    ];

    /**
     * 获取封禁此IP的管理员
     */
    public function bannedByUser()
    {
        return $this->belongsTo(User::class, 'banned_by');
    }

    /**
     * 获取撤销封禁的管理员
     */
    public function revokedByUser()
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }

    /**
     * 获取关联的隔离区记录
     */
    public function quarantine()
    {
        return $this->belongsTo(SecurityQuarantine::class, 'quarantine_id');
    }

    /**
     * 判断封禁是否已过期
     *
     * @return bool
     */
    public function isExpired()
    {
        if ($this->banned_until === null) {
            return false; // 永久封禁不会过期
        }
        
        return $this->banned_until->isPast();
    }

    /**
     * 判断封禁是否已撤销
     *
     * @return bool
     */
    public function isRevoked()
    {
        return $this->status === 'revoked';
    }

    /**
     * 判断封禁是否有效
     *
     * @return bool
     */
    public function isActive()
    {
        if ($this->isRevoked()) {
            return false;
        }
        
        if ($this->isExpired()) {
            // 自动更新状态为已过期
            if ($this->status !== 'expired') {
                $this->status = 'expired';
                $this->save();
            }
            return false;
        }
        
        return true;
    }

    /**
     * 撤销封禁
     *
     * @param int $userId 操作人ID
     * @param string $reason 撤销原因
     * @return bool
     */
    public function revoke($userId, $reason)
    {
        $this->status = 'revoked';
        $this->revoked_by = $userId;
        $this->revoked_at = now();
        $this->revoke_reason = $reason;
        
        return $this->save();
    }
} 