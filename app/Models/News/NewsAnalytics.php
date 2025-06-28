<?php

namespace App\Models\News;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

/**
 * 新闻统计分析模型
 * 
 * 用于记录和分析新闻的访问数据
 */
class NewsAnalytics extends Model
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'news_analytics';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'news_id',      // 新闻ID
        'user_id',      // 用户ID（如果已登录）
        'ip_address',   // IP地址
        'user_agent',   // 用户代理
        'referrer',     // 来源URL
        'device_type',  // 设备类型（desktop, mobile, tablet）
        'visited_at',   // 访问时间
    ];

    /**
     * 应该被转换为日期的属性
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'visited_at',
    ];

    /**
     * 获取关联的新闻
     */
    public function news()
    {
        return $this->belongsTo(News::class, 'news_id');
    }

    /**
     * 获取关联的用户
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * 按设备类型统计访问量
     *
     * @param int $newsId
     * @param \Carbon\Carbon|null $startDate
     * @return \Illuminate\Support\Collection
     */
    public static function getDeviceStats($newsId = null, $startDate = null)
    {
        $query = static::query();
        
        if ($newsId) {
            $query->where('news_id', $newsId);
        }
        
        if ($startDate) {
            $query->where('visited_at', '>=', $startDate);
        }
        
        return $query->selectRaw('device_type, count(*) as count')
            ->groupBy('device_type')
            ->get();
    }

    /**
     * 按来源统计访问量
     *
     * @param int $newsId
     * @param \Carbon\Carbon|null $startDate
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public static function getReferrerStats($newsId = null, $startDate = null, $limit = 10)
    {
        $query = static::query();
        
        if ($newsId) {
            $query->where('news_id', $newsId);
        }
        
        if ($startDate) {
            $query->where('visited_at', '>=', $startDate);
        }
        
        return $query->selectRaw('referrer, count(*) as count')
            ->whereNotNull('referrer')
            ->groupBy('referrer')
            ->orderBy('count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * 获取每日访问趋势
     *
     * @param int $newsId
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return \Illuminate\Support\Collection
     */
    public static function getDailyTrends($newsId = null, $startDate = null, $endDate = null)
    {
        $query = static::query();
        
        if ($newsId) {
            $query->where('news_id', $newsId);
        }
        
        if ($startDate) {
            $query->where('visited_at', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('visited_at', '<=', $endDate);
        }
        
        return $query->selectRaw('DATE(visited_at) as date, count(*) as views')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }
}