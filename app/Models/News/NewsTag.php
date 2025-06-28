<?php

namespace App\Models\News;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 新闻标签模型
 * 
 * 用于管理新闻标签
 */
class NewsTag extends Model
{
    use SoftDeletes;

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'news_tags';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'name',            // 标签名称
        'slug',            // URL友好的别名
        'description',     // 标签描述
        'is_active',       // 是否激活
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
        'is_active' => 'boolean',
    ];

    /**
     * 获取标签关联的新闻
     */
    public function news()
    {
        return $this->belongsToMany(News::class, 'news_tag', 'tag_id', 'news_id');
    }

    /**
     * 获取所有激活的标签
     *
     * @param bool $withCount 是否包含新闻计数
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getActiveTags($withCount = true)
    {
        $query = static::where('is_active', true);
        
        if ($withCount) {
            $query->withCount(['news' => function($query) {
                $query->where('status', 'published')
                      ->where('published_at', '<=', now());
            }]);
        }
        
        return $query->orderBy('name')->get();
    }

    /**
     * 根据名称获取标签，如不存在则创建
     *
     * @param string $name
     * @return NewsTag
     */
    public static function getOrCreate($name)
    {
        $slug = \Str::slug($name);
        
        $tag = static::where('slug', $slug)->first();
        
        if (!$tag) {
            $tag = static::create([
                'name' => $name,
                'slug' => $slug,
                'is_active' => true,
            ]);
        }
        
        return $tag;
    }
}
