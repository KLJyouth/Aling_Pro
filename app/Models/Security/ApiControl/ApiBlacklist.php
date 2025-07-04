<?php

namespace App\Models\Security\ApiControl;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Admin\Management\AdminUser;

class ApiBlacklist extends Model
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected \ = 'api_blacklists';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected \ = [
        'list_type',
        'value',
        'reason',
        'source',
        'expires_at',
        'status',
        'created_by',
    ];

    /**
     * 应该被转换为原生类型的属性
     *
     * @var array
     */
    protected \ = [
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 获取创建该黑名单的管理员
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return \->belongsTo(AdminUser::class, 'created_by');
    }

    /**
     * 获取活跃黑名单的查询作用域
     *
     * @param \Illuminate\Database\Eloquent\Builder \
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive(\)
    {
        return \->where('status', 'active');
    }

    /**
     * 获取指定类型黑名单的查询作用域
     *
     * @param \Illuminate\Database\Eloquent\Builder \
     * @param string \
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeType(\, string \)
    {
        return \->where('list_type', \);
    }

    /**
     * 获取未过期黑名单的查询作用域
     *
     * @param \Illuminate\Database\Eloquent\Builder \
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotExpired(\)
    {
        return \->where(function(\) {
            \->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * 检查黑名单是否活跃
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return \->status === 'active';
    }

    /**
     * 检查黑名单是否已过期
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return \->expires_at && \->expires_at->isPast();
    }

    /**
     * 检查值是否在黑名单中
     *
     * @param string \
     * @param string \
     * @return bool
     */
    public static function isBlacklisted(string \, string \): bool
    {
        return self::active()
            ->notExpired()
            ->where('list_type', \)
            ->where('value', \)
            ->exists();
    }

    /**
     * 添加到黑名单
     *
     * @param string \
     * @param string \
     * @param string|null \
     * @param string \
     * @param \DateTime|null \
     * @param int|null \
     * @return self
     */
    public static function addToBlacklist(
        string \,
        string \,
        ?string \ = null,
        string \ = 'manual',
        ?\DateTime \ = null,
        ?int \ = null
    ): self {
        // 检查是否已存在
        \ = self::where('list_type', \)
            ->where('value', \)
            ->first();
        
        if (\) {
            // 更新现有记录
            \->reason = \ ?? \->reason;
            \->source = \;
            \->expires_at = \;
            \->status = 'active';
            \->created_by = \ ?? \->created_by;
            \->save();
            
            return \;
        }
        
        // 创建新记录
        return self::create([
            'list_type' => \,
            'value' => \,
            'reason' => \,
            'source' => \,
            'expires_at' => \,
            'status' => 'active',
            'created_by' => \,
        ]);
    }

    /**
     * 从黑名单中移除
     *
     * @param string \
     * @param string \
     * @return bool
     */
    public static function removeFromBlacklist(string \, string \): bool
    {
        \ = self::where('list_type', \)
            ->where('value', \)
            ->first();
        
        if (\) {
            \->status = 'removed';
            return \->save();
        }
        
        return false;
    }
}
