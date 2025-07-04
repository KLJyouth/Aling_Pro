<?php

namespace App\Services\User;

use App\Models\User\UserMemory;
use Illuminate\Support\Facades\DB;

class MemoryService
{
    /**
     * 创建或更新记忆
     *
     * @param int $userId
     * @param string $key
     * @param mixed $content
     * @param array $data
     * @return UserMemory
     */
    public function remember($userId, $key, $content, array $data = [])
    {
        // 确定记忆类型
        $type = $data["type"] ?? $this->determineType($content);
        
        // 查找现有记忆
        $memory = UserMemory::byUser($userId)->byKey($key)->first();
        
        if ($memory) {
            // 更新现有记忆
            $memory->update([
                "content" => $this->formatContent($content, $type),
                "type" => $type,
                "category" => $data["category"] ?? $memory->category,
                "importance" => $data["importance"] ?? $memory->importance,
                "metadata" => $data["metadata"] ?? $memory->metadata,
            ]);
        } else {
            // 创建新记忆
            $memory = UserMemory::create([
                "user_id" => $userId,
                "key" => $key,
                "content" => $this->formatContent($content, $type),
                "type" => $type,
                "category" => $data["category"] ?? null,
                "importance" => $data["importance"] ?? 5,
                "metadata" => $data["metadata"] ?? null,
            ]);
        }
        
        return $memory;
    }
    
    /**
     * 检索记忆
     *
     * @param int $userId
     * @param string $key
     * @return mixed|null
     */
    public function recall($userId, $key)
    {
        $memory = UserMemory::byUser($userId)->byKey($key)->first();
        
        if (!$memory) {
            return null;
        }
        
        // 记录访问
        $memory->recordAccess();
        
        return $memory->getContent();
    }
    
    /**
     * 删除记忆
     *
     * @param int $userId
     * @param string $key
     * @return bool
     */
    public function forget($userId, $key)
    {
        return UserMemory::byUser($userId)->byKey($key)->delete();
    }
    
    /**
     * 获取用户记忆列表
     *
     * @param int $userId
     * @param array $filters
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getUserMemories($userId, array $filters = [])
    {
        $query = UserMemory::byUser($userId);
        
        // 应用过滤器
        if (isset($filters["category"])) {
            $query->byCategory($filters["category"]);
        }
        
        if (isset($filters["type"])) {
            $query->byType($filters["type"]);
        }
        
        if (isset($filters["min_importance"])) {
            $query->byImportance($filters["min_importance"]);
        }
        
        if (isset($filters["search"])) {
            $search = $filters["search"];
            $query->where(function ($q) use ($search) {
                $q->where("key", "like", "%{$search}%")
                  ->orWhere("content", "like", "%{$search}%");
            });
        }
        
        // 排序
        $sortField = $filters["sort_field"] ?? "created_at";
        $sortDirection = $filters["sort_direction"] ?? "desc";
        
        $query->orderBy($sortField, $sortDirection);
        
        // 分页
        $perPage = $filters["per_page"] ?? 15;
        
        return $query->paginate($perPage);
    }
    
    /**
     * 获取用户记忆分类列表
     *
     * @param int $userId
     * @return array
     */
    public function getUserMemoryCategories($userId)
    {
        return DB::table("user_memories")
            ->where("user_id", $userId)
            ->whereNotNull("category")
            ->select("category")
            ->distinct()
            ->pluck("category")
            ->toArray();
    }
    
    /**
     * 获取语义相似的记忆
     *
     * @param int $userId
     * @param string $query
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSimilarMemories($userId, $query, $limit = 5)
    {
        // 这里应该使用向量数据库或嵌入搜索
        // 简化版实现：基于关键词匹配
        $keywords = preg_split("/\s+/", $query);
        
        $query = UserMemory::byUser($userId);
        
        foreach ($keywords as $keyword) {
            if (strlen($keyword) > 2) {
                $query->where("content", "like", "%{$keyword}%");
            }
        }
        
        return $query->orderBy("importance", "desc")
            ->limit($limit)
            ->get();
    }
    
    /**
     * 确定内容类型
     *
     * @param mixed $content
     * @return string
     */
    protected function determineType($content)
    {
        if (is_array($content) || is_object($content)) {
            return "json";
        }
        
        return "text";
    }
    
    /**
     * 格式化内容
     *
     * @param mixed $content
     * @param string $type
     * @return string
     */
    protected function formatContent($content, $type)
    {
        if ($type === "json" && (is_array($content) || is_object($content))) {
            return json_encode($content);
        } elseif ($type === "embedding" && (is_array($content) || is_object($content))) {
            return serialize($content);
        }
        
        return (string) $content;
    }
}
