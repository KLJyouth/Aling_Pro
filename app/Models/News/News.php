<?php

namespace App\Models\News;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

/**
 * 新闻模型
 * 
 * 用于管理网站的新闻内容
 */
class News extends Model
{
    use SoftDeletes;

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'news';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'title',           // 新闻标题
        'slug',            // URL友好的别名
        'content',         // 新闻内容
        'summary',         // 新闻摘要
        'cover_image',     // 封面图片
        'author_id',       // 作者ID
        'category_id',     // 分类ID
        'status',          // 状态：draft, published, archived
        'published_at',    // 发布时间
        'featured',        // 是否推荐
        'view_count',      // 浏览次数
        'meta_keywords',   // SEO关键词
        'meta_description',// SEO描述
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
        'published_at',
    ];

    /**
     * 应该被转换为原生类型的属性
     *
     * @var array
     */
    protected $casts = [
        'featured' => 'boolean',
    ];

    /**
     * 获取新闻作者
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * 获取新闻分类
     */
    public function category()
    {
        return $this->belongsTo(NewsCategory::class, 'category_id');
    }

    /**
     * 获取新闻标签
     */
    public function tags()
    {
        return $this->belongsToMany(NewsTag::class, 'news_tag', 'news_id', 'tag_id');
    }

    /**
     * 获取新闻评论
     */
    public function comments()
    {
        return $this->hasMany(NewsComment::class, 'news_id');
    }

    /**
     * 判断新闻是否已发布
     *
     * @return bool
     */
    public function isPublished()
    {
        return $this->status === 'published' && $this->published_at <= now();
    }

    /**
     * 增加浏览次数
     *
     * @param int $count
     * @return bool
     */
    public function incrementViewCount($count = 1)
    {
        $this->view_count += $count;
        return $this->save();
    }

    /**
     * 设置为推荐
     *
     * @return bool
     */
    public function setFeatured()
    {
        $this->featured = true;
        return $this->save();
    }

    /**
     * 取消推荐
     *
     * @return bool
     */
    public function unsetFeatured()
    {
        $this->featured = false;
        return $this->save();
    }

    /**
     * 发布新闻
     *
     * @param \DateTime|null $publishedAt
     * @return bool
     */
    public function publish($publishedAt = null)
    {
        $this->status = 'published';
        $this->published_at = $publishedAt ?: now();
        return $this->save();
    }

    /**
     * 设置新闻为草稿
     *
     * @return bool
     */
    public function setDraft()
    {
        $this->status = 'draft';
        return $this->save();
    }

    /**
     * 将新闻归档
     *
     * @return bool
     */
    public function archive()
    {
        $this->status = 'archived';
        return $this->save();
    }
    
    /**
     * 获取热门新闻
     *
     * @param int $limit 返回的新闻数量
     * @param int $days 统计的天数范围，默认为30天
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getPopularNews($limit = 5, $days = 30)
    {
        return static::where('status', 'published')
            ->where('published_at', '<=', now())
            ->where('published_at', '>=', now()->subDays($days))
            ->orderBy('view_count', 'desc')
            ->with(['author', 'category'])
            ->take($limit)
            ->get();
    }
}
