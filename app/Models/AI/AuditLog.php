<?php

namespace App\Models\AI;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class AuditLog extends Model
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = "ai_audit_logs";

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        "user_id",
        "action",
        "resource_type",
        "resource_id",
        "old_values",
        "new_values",
        "ip_address",
        "user_agent",
    ];

    /**
     * 获取关联的用户
     */
    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }

    /**
     * 获取旧值数组
     *
     * @return array|null
     */
    public function getOldValuesArrayAttribute()
    {
        return $this->old_values ? json_decode($this->old_values, true) : null;
    }

    /**
     * 获取新值数组
     *
     * @return array|null
     */
    public function getNewValuesArrayAttribute()
    {
        return $this->new_values ? json_decode($this->new_values, true) : null;
    }

    /**
     * 获取变更的字段
     *
     * @return array
     */
    public function getChangedFieldsAttribute()
    {
        $oldValues = $this->old_values_array;
        $newValues = $this->new_values_array;
        
        if (!$oldValues || !$newValues) {
            return [];
        }
        
        $changedFields = [];
        
        foreach ($newValues as $key => $value) {
            if (!isset($oldValues[$key]) || $oldValues[$key] !== $value) {
                $changedFields[$key] = [
                    "old" => $oldValues[$key] ?? null,
                    "new" => $value
                ];
            }
        }
        
        return $changedFields;
    }
}
