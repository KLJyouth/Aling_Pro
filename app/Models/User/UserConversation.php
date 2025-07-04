<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\AI\Agent;

class UserConversation extends Model
{
    use SoftDeletes;

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        "user_id",
        "title",
        "model",
        "agent_id",
        "system_prompt",
        "metadata",
        "last_message_at",
        "message_count",
        "is_pinned",
        "is_archived",
    ];

    /**
     * 应该被转换成原生类型的属性
     *
     * @var array
     */
    protected $casts = [
        "system_prompt" => "array",
        "metadata" => "array",
        "last_message_at" => "datetime",
        "message_count" => "integer",
        "is_pinned" => "boolean",
        "is_archived" => "boolean",
    ];

    /**
     * 获取关联的用户
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 获取关联的智能体
     */
    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    /**
     * 获取对话消息
     */
    public function messages()
    {
        return $this->hasMany(ConversationMessage::class, "conversation_id");
    }

    /**
     * 添加消息
     *
     * @param string $role
     * @param string $content
     * @param array $metadata
     * @return ConversationMessage
     */
    public function addMessage($role, $content, $metadata = [])
    {
        $message = $this->messages()->create([
            "role" => $role,
            "content" => $content,
            "metadata" => $metadata,
        ]);
        
        $this->update([
            "last_message_at" => now(),
            "message_count" => $this->message_count + 1,
        ]);
        
        // 如果没有标题，使用用户的第一条消息作为标题
        if (!$this->title && $role === "user" && $this->message_count === 1) {
            $this->update([
                "title" => strlen($content) > 50 ? substr($content, 0, 47) . "..." : $content,
            ]);
        }
        
        return $message;
    }

    /**
     * 获取最后一条消息
     *
     * @return ConversationMessage|null
     */
    public function getLastMessage()
    {
        return $this->messages()->latest()->first();
    }

    /**
     * 获取对话历史
     *
     * @param int $limit 消息数量限制
     * @return array
     */
    public function getHistory($limit = null)
    {
        $query = $this->messages()->orderBy("id");
        
        if ($limit) {
            $query->take($limit);
        }
        
        $messages = $query->get();
        
        return $messages->map(function ($message) {
            return [
                "role" => $message->role,
                "content" => $message->content,
            ];
        })->toArray();
    }

    /**
     * 清空对话
     *
     * @return void
     */
    public function clearMessages()
    {
        $this->messages()->delete();
        
        $this->update([
            "message_count" => 0,
            "last_message_at" => null,
        ]);
    }

    /**
     * 作用域：按用户筛选
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where("user_id", $userId);
    }

    /**
     * 作用域：按智能体筛选
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $agentId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByAgent($query, $agentId)
    {
        return $query->where("agent_id", $agentId);
    }

    /**
     * 作用域：按模型筛选
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByModel($query, $model)
    {
        return $query->where("model", $model);
    }

    /**
     * 作用域：置顶对话
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePinned($query)
    {
        return $query->where("is_pinned", true);
    }

    /**
     * 作用域：归档对话
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeArchived($query)
    {
        return $query->where("is_archived", true);
    }

    /**
     * 作用域：活跃对话（未归档）
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where("is_archived", false);
    }
}
