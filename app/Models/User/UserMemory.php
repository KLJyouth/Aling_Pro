<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class UserMemory extends Model
{
    use SoftDeletes;

    /**
     * 表名
     *
     * @var string
     */
    protected $table = "user_memories";

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        "user_id",
        "key",
        "content",
        "type",
        "category",
        "importance",
        "last_accessed_at",
        "access_count",
        "metadata",
    ];

    /**
     * 应该被转换成原生类型的属性
     *
     * @var array
     */
    protected $casts = [
        "metadata" => "array",
        "importance" => "integer",
        "access_count" => "integer",
        "last_accessed_at" => "datetime",
    ];

    /**
     * 获取关联的用户
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 记录访问
     *
     * @return void
     */
    public function recordAccess()
    {
        $this->update([
            "last_accessed_at" => now(),
            "access_count" => $this->access_count + 1,
        ]);
    }

    /**
     * 获取记忆内容
     *
     * @return mixed
     */
    public function getContent()
    {
        if ($this->type === "json") {
            return json_decode($this->content, true);
        } elseif ($this->type === "embedding") {
            // 处理嵌入向量
            return unserialize($this->content);
        }
        
        return $this->content;
    }

    /**
     * 设置记忆内容
     *
     * @param mixed $content
     * @return void
     */
    public function setContent($content)
    {
        if ($this->type === "json") {
            $this->content = json_encode($content);
        } elseif ($this->type === "embedding") {
            // 处理嵌入向量
            $this->content = serialize($content);
        } else {
            $this->content = $content;
        }
        
        $this->save();
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
     * 作用域：按分类筛选
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $category
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where("category", $category);
    }

    /**
     * 作用域：按重要性筛选
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $minImportance
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByImportance($query, $minImportance)
    {
        return $query->where("importance", ">=", $minImportance);
    }

    /**
     * 作用域：按类型筛选
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByType($query, $type)
    {
        return $query->where("type", $type);
    }

    /**
     * 作用域：按键筛选
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $key
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByKey($query, $key)
    {
        return $query->where("key", $key);
    }
}
