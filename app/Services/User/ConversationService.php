<?php

namespace App\Services\User;

use App\Models\User\UserConversation;
use App\Models\User\ConversationMessage;

class ConversationService
{
    /**
     * 创建新对话
     *
     * @param int $userId
     * @param array $data
     * @return UserConversation
     */
    public function createConversation($userId, array $data = [])
    {
        return UserConversation::create([
            "user_id" => $userId,
            "title" => $data["title"] ?? null,
            "model" => $data["model"] ?? null,
            "agent_id" => $data["agent_id"] ?? null,
            "system_prompt" => $data["system_prompt"] ?? null,
            "metadata" => $data["metadata"] ?? null,
        ]);
    }
    
    /**
     * 添加消息到对话
     *
     * @param int $conversationId
     * @param string $role
     * @param string $content
     * @param array $metadata
     * @return ConversationMessage
     */
    public function addMessage($conversationId, $role, $content, array $metadata = [])
    {
        $conversation = UserConversation::findOrFail($conversationId);
        
        return $conversation->addMessage($role, $content, $metadata);
    }
    
    /**
     * 获取对话历史
     *
     * @param int $conversationId
     * @param int|null $limit
     * @return array
     */
    public function getConversationHistory($conversationId, $limit = null)
    {
        $conversation = UserConversation::findOrFail($conversationId);
        
        return $conversation->getHistory($limit);
    }
    
    /**
     * 更新对话信息
     *
     * @param int $conversationId
     * @param array $data
     * @return UserConversation
     */
    public function updateConversation($conversationId, array $data)
    {
        $conversation = UserConversation::findOrFail($conversationId);
        
        $conversation->update($data);
        
        return $conversation;
    }
    
    /**
     * 删除对话
     *
     * @param int $conversationId
     * @return bool
     */
    public function deleteConversation($conversationId)
    {
        $conversation = UserConversation::findOrFail($conversationId);
        
        // 删除所有消息
        $conversation->messages()->delete();
        
        // 删除对话
        return $conversation->delete();
    }
    
    /**
     * 清空对话消息
     *
     * @param int $conversationId
     * @return void
     */
    public function clearConversation($conversationId)
    {
        $conversation = UserConversation::findOrFail($conversationId);
        
        $conversation->clearMessages();
    }
    
    /**
     * 获取用户对话列表
     *
     * @param int $userId
     * @param array $filters
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getUserConversations($userId, array $filters = [])
    {
        $query = UserConversation::byUser($userId);
        
        // 应用过滤器
        if (isset($filters["agent_id"])) {
            $query->byAgent($filters["agent_id"]);
        }
        
        if (isset($filters["model"])) {
            $query->byModel($filters["model"]);
        }
        
        if (isset($filters["is_archived"])) {
            if ($filters["is_archived"]) {
                $query->archived();
            } else {
                $query->active();
            }
        } else {
            // 默认只显示活跃对话
            $query->active();
        }
        
        if (isset($filters["search"])) {
            $search = $filters["search"];
            $query->where("title", "like", "%{$search}%");
        }
        
        // 置顶对话排在前面
        $query->orderBy("is_pinned", "desc");
        
        // 排序
        $sortField = $filters["sort_field"] ?? "last_message_at";
        $sortDirection = $filters["sort_direction"] ?? "desc";
        
        $query->orderBy($sortField, $sortDirection);
        
        // 分页
        $perPage = $filters["per_page"] ?? 15;
        
        return $query->paginate($perPage);
    }
    
    /**
     * 切换对话置顶状态
     *
     * @param int $conversationId
     * @return bool
     */
    public function togglePin($conversationId)
    {
        $conversation = UserConversation::findOrFail($conversationId);
        
        $conversation->is_pinned = !$conversation->is_pinned;
        
        return $conversation->save();
    }
    
    /**
     * 切换对话归档状态
     *
     * @param int $conversationId
     * @return bool
     */
    public function toggleArchive($conversationId)
    {
        $conversation = UserConversation::findOrFail($conversationId);
        
        $conversation->is_archived = !$conversation->is_archived;
        
        return $conversation->save();
    }
    
    /**
     * 获取对话统计信息
     *
     * @param int $userId
     * @return array
     */
    public function getConversationStats($userId)
    {
        return [
            "total" => UserConversation::byUser($userId)->count(),
            "active" => UserConversation::byUser($userId)->active()->count(),
            "archived" => UserConversation::byUser($userId)->archived()->count(),
            "pinned" => UserConversation::byUser($userId)->pinned()->count(),
            "total_messages" => UserConversation::byUser($userId)->sum("message_count"),
        ];
    }
}
