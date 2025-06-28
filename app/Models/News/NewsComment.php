<?php

namespace App\Models\News;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

/**
 * 新闻评论模型
 * 
 * 用于管理新闻评论
 */
class NewsComment extends Model
{
    use SoftDeletes;

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'news_comments';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'news_id',         // 新闻ID
        'user_id',         // 用户ID
        'parent_id',       // 父评论ID
        'content',         // 评论内容
        'status',          // 状态：pending, approved, rejected
        'ip_address',      // IP地址
        'user_agent',      // 用户代理
        'is_anonymous',    // 是否匿名
        'author_name',     // 匿名作者名称
        'author_email',    // 匿名作者邮箱
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
    ];

    /**
     * 应该被转换为原生类型的属性
     *
     * @var array
     */
    protected $casts = [
        'is_anonymous' => 'boolean',
    ];

    /**
     * 获取评论所属新闻
     */
    public function news()
    {
        return $this->belongsTo(News::class, 'news_id');
    }

    /**
     * 获取评论用户
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * 获取父评论
     */
    public function parent()
    {
        return $this->belongsTo(NewsComment::class, 'parent_id');
    }

    /**
     * 获取子评论
     */
    public function replies()
    {
        return $this->hasMany(NewsComment::class, 'parent_id')->orderBy('created_at', 'asc');
    }

    /**
     * 获取作者名称（匿名或用户名）
     *
     * @return string
     */
    public function getAuthorNameAttribute()
    {
        if ($this->is_anonymous) {
            return $this->attributes['author_name'] ?: '匿名用户';
        }
        
        return $this->user ? $this->user->name : '未知用户';
    }

    /**
     * 审核通过评论
     *
     * @return bool
     */
    public function approve()
    {
        $this->status = 'approved';
        return $this->save();
    }

    /**
     * 拒绝评论
     *
     * @return bool
     */
    public function reject()
    {
        $this->status = 'rejected';
        return $this->save();
    }
}
