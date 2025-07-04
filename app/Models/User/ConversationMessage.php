<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class ConversationMessage extends Model
{
    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        "conversation_id",
        "role",
        "content",
        "metadata",
    ];

    /**
     * 应该被转换成原生类型的属性
     *
     * @var array
     */
    protected $casts = [
        "metadata" => "array",
    ];

    /**
     * 获取关联的对话
     */
    public function conversation()
    {
        return $this->belongsTo(UserConversation::class, "conversation_id");
    }

    /**
     * 获取令牌数量
     *
     * @return int|null
     */
    public function getTokenCountAttribute()
    {
        return $this->metadata["token_count"] ?? null;
    }

    /**
     * 获取延迟时间（毫秒）
     *
     * @return int|null
     */
    public function getLatencyAttribute()
    {
        return $this->metadata["latency"] ?? null;
    }

    /**
     * 获取格式化的延迟时间
     *
     * @return string|null
     */
    public function getFormattedLatencyAttribute()
    {
        if (!isset($this->metadata["latency"])) {
            return null;
        }
        
        $latency = $this->metadata["latency"];
        
        if ($latency < 1000) {
            return $latency . "ms";
        }
        
        return round($latency / 1000, 2) . "s";
    }

    /**
     * 作用域：按角色筛选
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $role
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByRole($query, $role)
    {
        return $query->where("role", $role);
    }

    /**
     * 作用域：用户消息
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUser($query)
    {
        return $query->where("role", "user");
    }

    /**
     * 作用域：助手消息
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAssistant($query)
    {
        return $query->where("role", "assistant");
    }

    /**
     * 作用域：系统消息
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSystem($query)
    {
        return $query->where("role", "system");
    }
}
