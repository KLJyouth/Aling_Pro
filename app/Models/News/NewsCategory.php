<?php

namespace App\Models\News;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 新闻分类模型
 * 
 * 用于管理新闻分类
 */
class NewsCategory extends Model
{
    use SoftDeletes;

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'news_categories';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'name',            // 分类名称
        'slug',            // URL友好的别名
        'description',     // 分类描述
        'parent_id',       // 父分类ID
        'order',           // 排序
        'is_active',       // 是否激活
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
     * 获取分类下的新闻
     */
    public function news()
    {
        return $this->hasMany(News::class, 'category_id');
    }

    /**
     * 获取父分类
     */
    public function parent()
    {
        return $this->belongsTo(NewsCategory::class, 'parent_id');
    }

    /**
     * 获取子分类
     */
    public function children()
    {
        return $this->hasMany(NewsCategory::class, 'parent_id');
    }

    /**
     * 获取所有激活的分类
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getActiveCategories()
    {
        return static::where('is_active', true)->orderBy('order')->get();
    }
    
    /**
     * 获取主要分类（顶级分类，没有父分类）
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getMainCategories()
    {
        return static::where('is_active', true)
            ->whereNull('parent_id')
            ->orderBy('order')
            ->get();
    }

    /**
     * 获取分类层级结构
     *
     * @return array
     */
    public static function getCategoryTree()
    {
        $categories = static::where('is_active', true)->orderBy('order')->get();
        return static::buildTree($categories);
    }

    /**
     * 构建分类结构
     *
     * @param \Illuminate\Database\Eloquent\Collection $categories
     * @param int|null $parentId
     * @return array
     */
    protected static function buildTree($categories, $parentId = null)
    {
        $branch = [];

        foreach ($categories as $category) {
            if ($category->parent_id == $parentId) {
                $children = static::buildTree($categories, $category->id);

                if ($children) {
                    $category->children = $children;
                }

                $branch[] = $category;
            }
        }

        return $branch;
    }
}
