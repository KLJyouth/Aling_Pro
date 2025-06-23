<?php

namespace AlingAi\Models;

use AlingAi\Models\BaseModel;
use AlingAi\Models\User;
use DateTime;

class Conversation extends BaseModel
{
    protected $table = 'conversations';
    
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'type',
        'status',
        'context',
        'messages',
        'metadata',
        'settings',
        'tags',
        'is_favorite',
        'is_public',
        'share_token',
        'view_count',
        'rating'
    ];
    
    protected $casts = [
        'context' => 'array',
        'messages' => 'array',
        'metadata' => 'array',
        'settings' => 'array',
        'tags' => 'array',
        'is_favorite' => 'boolean',
        'is_public' => 'boolean',
        'view_count' => 'integer',
        'rating' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];
    
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    
    // 默认属性值
    protected $attributes = [
        'type' => 'chat',
        'status' => 'active',
        'context' => '{}',
        'messages' => '[]',
        'metadata' => '{}',
        'settings' => '{}',
        'tags' => '[]',
        'is_favorite' => false,
        'is_public' => false,
        'view_count' => 0,
        'rating' => 0
    ];
    
    // 对话类型常量
    const TYPE_CHAT = 'chat';
    const TYPE_DOCUMENT = 'document';
    const TYPE_CODE = 'code';
    const TYPE_CREATIVE = 'creative';
    const TYPE_ANALYSIS = 'analysis';
    
    // 对话状态常量
    const STATUS_ACTIVE = 'active';
    const STATUS_ARCHIVED = 'archived';
    const STATUS_PAUSED = 'paused';
    const STATUS_COMPLETED = 'completed';
      /**
     * 获取关联的用户
     */
    public function user(): ?User
    {
        return User::find($this->user_id);
    }
      /**
     * 获取消息记录
     */
    public function messages(): array
    {
        // 如果有消息存储在 messages 字段中，返回该数组
        return $this->messages ?? [];
    }    /**
     * 获取关联的文档
     */
    public function documents(): array
    {
        try {
            // 从关联表获取文档ID
            $sql = "SELECT document_id FROM conversation_documents WHERE conversation_id = ?";
            $documentIds = self::$databaseService->query($sql, [$this->id]);
            
            if (empty($documentIds)) {
                return [];
            }
            
            // 提取文档ID
            $ids = array_column($documentIds, 'document_id');
            
            if (empty($ids)) {
                return [];
            }
            
            // 获取文档
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $sql = "SELECT * FROM documents WHERE id IN ($placeholders)";
            return self::$databaseService->query($sql, $ids);
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * 获取对话摘要
     */
    public function getSummaryAttribute(): string
    {
        if (!empty($this->description)) {
            return $this->description;
        }
        
        $messages = $this->messages ?? [];
        if (empty($messages)) {
            return 'Empty conversation';
        }
        
        $firstMessage = reset($messages);
        return substr($firstMessage['content'] ?? '', 0, 100) . '...';
    }
    
    /**
     * 获取消息数量
     */
    public function getMessageCountAttribute(): int
    {
        return count($this->messages ?? []);
    }
    
    /**
     * 获取最后一条消息
     */
    public function getLastMessageAttribute(): ?array
    {
        $messages = $this->messages ?? [];
        return empty($messages) ? null : end($messages);
    }
    
    /**
     * 获取对话时长（估算）
     */
    public function getDurationAttribute(): int
    {
        $messages = $this->messages ?? [];
        if (count($messages) < 2) {
            return 0;
        }
        
        $firstMessage = reset($messages);
        $lastMessage = end($messages);
        
        $startTime = strtotime($firstMessage['timestamp'] ?? $this->created_at);
        $endTime = strtotime($lastMessage['timestamp'] ?? $this->updated_at);
        
        return max(0, $endTime - $startTime);
    }
    
    /**
     * 检查用户是否有访问权限
     */
    public function canAccess(User $user): bool
    {
        // 对话所有者
        if ($this->user_id === $user->id) {
            return true;
        }
        
        // 公开对话
        if ($this->is_public) {
            return true;
        }
        
        // 管理员权限
        if ($user->isAdmin()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * 检查用户是否可以编辑
     */
    public function canEdit(User $user): bool
    {
        // 对话所有者
        if ($this->user_id === $user->id) {
            return true;
        }
        
        // 管理员权限
        if ($user->isAdmin()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * 添加消息
     */
    public function addMessage(array $message): void
    {
        $messages = $this->messages ?? [];
        
        // 添加时间戳和ID
        $message['id'] = uniqid('msg_', true);
        $message['timestamp'] = date('c');
          $messages[] = $message;
        
        $this->messages = $messages;
        $this->save();
    }

    /**
     * 更新消息
     */
    public function updateMessage(string $messageId, array $updates): bool
    {
        $messages = $this->messages ?? [];
        
        foreach ($messages as &$message) {
            if ($message['id'] === $messageId) {
                $message = array_merge($message, $updates);
                $message['updated_at'] = date('c');
                $this->messages = $messages;
                $this->save();
                return true;
            }
        }
        
        return false;
    }
      /**
     * 删除消息
     */
    public function deleteMessage(string $messageId): bool
    {
        $messages = $this->messages ?? [];
        
        foreach ($messages as $index => $message) {
            if ($message['id'] === $messageId) {
                unset($messages[$index]);
                $this->messages = array_values($messages);
                $this->save();
                return true;
            }
        }
        
        return false;
    }
      /**
     * 生成分享令牌
     */
    public function generateShareToken(): string
    {
        $token = bin2hex(random_bytes(16));
        $this->share_token = $token;
        $this->save();
        return $token;
    }
      /**
     * 增加查看次数
     */
    public function incrementViewCount(): void
    {
        $this->view_count = ($this->view_count ?? 0) + 1;
        $this->save();
    }
      /**
     * 设置评分
     */
    public function setRating(int $rating): void
    {
        $rating = max(1, min(5, $rating));
        $this->rating = $rating;
        $this->save();
    }
      /**
     * 添加标签
     */
    public function addTag(string $tag): void
    {
        $tags = $this->tags ?? [];
        if (!in_array($tag, $tags)) {
            $tags[] = $tag;
            $this->tags = $tags;
            $this->save();
        }
    }
      /**
     * 移除标签
     */
    public function removeTag(string $tag): void
    {
        $tags = $this->tags ?? [];
        $index = array_search($tag, $tags);
        if ($index !== false) {
            unset($tags[$index]);
            $this->tags = array_values($tags);
            $this->save();
        }
    }
      /**
     * 归档对话
     */
    public function archive(): void
    {
        $this->status = self::STATUS_ARCHIVED;
        $this->save();
    }
      /**
     * 恢复对话
     */
    public function restore(): void
    {
        $this->status = self::STATUS_ACTIVE;
        $this->save();
    }
      /**
     * 设置为收藏
     */
    public function favorite(): void
    {
        $this->is_favorite = true;
        $this->save();
    }
      /**
     * 取消收藏
     */
    public function unfavorite(): void
    {
        $this->is_favorite = false;
        $this->save();
    }
      /**
     * 设置为公开
     */
    public function makePublic(): void
    {
        $this->is_public = true;
        $this->save();
        if (!$this->share_token) {
            $this->generateShareToken();
        }
    }
      /**
     * 设置为私有
     */
    public function makePrivate(): void
    {
        $this->is_public = false;
        $this->share_token = null;
        $this->save();
    }
      /**
     * 查询作用域：活跃对话
     */
    public static function active()
    {
        return (new static())->where('status', self::STATUS_ACTIVE);
    }
    
    /**
     * 查询作用域：已归档对话
     */
    public static function archived()
    {
        return (new static())->where('status', self::STATUS_ARCHIVED);
    }
    
    /**
     * 查询作用域：收藏对话
     */
    public static function favorited()
    {
        return (new static())->where('is_favorite', true);
    }
    
    /**
     * 查询作用域：公开对话
     */
    public static function publicConversations()
    {
        return (new static())->where('is_public', true);
    }
    
    /**
     * 查询作用域：按类型筛选
     */
    public static function byType(string $type)
    {
        return (new static())->where('type', $type);
    }
    
    /**
     * 查询作用域：按标签筛选
     */
    public static function withTag(string $tag)
    {
        $qb = new static();
        // 使用 JSON 搜索模拟 whereJsonContains
        return $qb->where('tags', 'LIKE', '%"' . $tag . '"%');
    }
    
    /**
     * 查询作用域：搜索对话
     */
    public static function search(string $search)
    {
        $qb = new static();
        return $qb->where(function ($builder) use ($search) {
            $builder->where('title', 'LIKE', "%{$search}%")
                   ->orWhere('description', 'LIKE', "%{$search}%")
                   ->orWhere('tags', 'LIKE', '%"' . $search . '"%');
        });
    }
    
    /**
     * 序列化为数组
     */
    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'status' => $this->status,
            'summary' => $this->summary,
            'message_count' => $this->message_count,
            'duration' => $this->duration,
            'tags' => $this->tags,
            'is_favorite' => $this->is_favorite,
            'is_public' => $this->is_public,
            'view_count' => $this->view_count,
            'rating' => $this->rating,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
    
    /**
     * 序列化为详细数组
     */
    public function toDetailedArray(): array
    {
        return array_merge($this->toPublicArray(), [
            'user_id' => $this->user_id,
            'context' => $this->context,
            'messages' => $this->messages,
            'metadata' => $this->metadata,
            'settings' => $this->settings,
            'share_token' => $this->share_token,
            'last_message' => $this->last_message
        ]);
    }
}
