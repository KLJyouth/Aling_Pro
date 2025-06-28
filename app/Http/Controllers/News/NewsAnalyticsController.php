<?php

namespace App\Http\Controllers\News;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\News\News;
use App\Models\News\NewsCategory;
use App\Models\News\NewsTag;
use App\Models\News\NewsAnalytics;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * 新闻统计和分析控制器
 * 
 * 处理新闻相关的统计和分析功能
 */
class NewsAnalyticsController extends Controller
{
    /**
     * 记录页面访问
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function recordPageview(Request $request)
    {
        try {
            // 验证请求
            $request->validate([
                'url' => 'required|string',
                'title' => 'required|string',
            ]);
            
            // 解析URL获取新闻ID或Slug
            $url = $request->input('url');
            $slug = $this->extractSlugFromUrl($url);
            
            if ($slug) {
                // 查找对应的新闻
                $news = News::where('slug', $slug)->first();
                
                if ($news) {
                    // 增加浏览次数
                    $news->incrementViewCount();
                    
                    // 记录详细的访问信息
                    NewsAnalytics::create([
                        'news_id' => $news->id,
                        'user_id' => auth()->id(), // 如果用户已登录
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'referrer' => $request->header('referer'),
                        'device_type' => $this->detectDeviceType($request->userAgent()),
                        'visited_at' => now(),
                    ]);
                    
                    return response()->json(['success' => true]);
                }
            }
            
            // 如果不是新闻页面，只记录基本信息
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '记录访问失败: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * 获取新闻统计概览（管理员用）
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatisticsOverview(Request $request)
    {
        // 验证管理员权限
        if (!auth()->user()->hasRole(['admin', 'editor'])) {
            return response()->json(['error' => '没有权限访问此资源'], 403);
        }
        
        // 获取时间范围
        $period = $request->input('period', 'week');
        $startDate = $this->getStartDate($period);
        
        // 获取基本统计数据
        $totalNews = News::count();
        $publishedNews = News::where('status', 'published')->count();
        $totalViews = News::sum('view_count');
        
        // 获取时间段内的新发布新闻数
        $newPublished = News::where('published_at', '>=', $startDate)
            ->where('status', 'published')
            ->count();
        
        // 获取时间段内的总浏览量
        $periodViews = NewsAnalytics::where('visited_at', '>=', $startDate)
            ->count();
        
        // 获取热门新闻
        $popularNews = News::where('status', 'published')
            ->where('published_at', '>=', $startDate)
            ->orderBy('view_count', 'desc')
            ->take(5)
            ->with('category')
            ->get(['id', 'title', 'slug', 'view_count', 'category_id', 'published_at']);
        
        // 获取分类统计
        $categoryStats = NewsCategory::withCount(['news' => function($query) {
                $query->where('status', 'published');
            }])
            ->orderBy('news_count', 'desc')
            ->take(10)
            ->get(['id', 'name', 'news_count']);
        
        // 获取标签统计
        $tagStats = NewsTag::withCount(['news' => function($query) {
                $query->where('status', 'published');
            }])
            ->orderBy('news_count', 'desc')
            ->take(10)
            ->get(['id', 'name', 'news_count']);
        
        // 获取每日访问趋势
        $dailyTrends = $this->getDailyTrends($startDate);
        
        return response()->json([
            'success' => true,
            'data' => [
                'total_news' => $totalNews,
                'published_news' => $publishedNews,
                'total_views' => $totalViews,
                'new_published' => $newPublished,
                'period_views' => $periodViews,
                'popular_news' => $popularNews,
                'category_stats' => $categoryStats,
                'tag_stats' => $tagStats,
                'daily_trends' => $dailyTrends,
                'period' => $period
            ]
        ]);
    }
    
    /**
     * 获取单个新闻的详细统计信息
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNewsStatistics(Request $request, $id)
    {
        // 验证管理员权限
        if (!auth()->user()->hasRole(['admin', 'editor'])) {
            return response()->json(['error' => '没有权限访问此资源'], 403);
        }
        
        // 查找新闻
        $news = News::findOrFail($id);
        
        // 获取时间范围
        $period = $request->input('period', 'week');
        $startDate = $this->getStartDate($period);
        
        // 获取访问趋势
        $viewTrends = $this->getNewsViewTrends($news->id, $startDate);
        
        // 获取设备类型统计
        $deviceStats = NewsAnalytics::where('news_id', $news->id)
            ->where('visited_at', '>=', $startDate)
            ->select('device_type', DB::raw('count(*) as count'))
            ->groupBy('device_type')
            ->get();
        
        // 获取来源统计
        $referrerStats = NewsAnalytics::where('news_id', $news->id)
            ->where('visited_at', '>=', $startDate)
            ->select('referrer', DB::raw('count(*) as count'))
            ->groupBy('referrer')
            ->orderBy('count', 'desc')
            ->take(10)
            ->get();
        
        // 获取相关新闻
        $relatedNews = News::where('category_id', $news->category_id)
            ->where('id', '!=', $news->id)
            ->where('status', 'published')
            ->orderBy('view_count', 'desc')
            ->take(5)
            ->get(['id', 'title', 'slug', 'view_count']);
        
        return response()->json([
            'success' => true,
            'data' => [
                'news' => $news,
                'view_trends' => $viewTrends,
                'device_stats' => $deviceStats,
                'referrer_stats' => $referrerStats,
                'related_news' => $relatedNews,
                'period' => $period
            ]
        ]);
    }
    
    /**
     * 从URL中提取新闻Slug
     *
     * @param string $url
     * @return string|null
     */
    protected function extractSlugFromUrl($url)
    {
        // 示例URL: https://example.com/news/my-news-title
        $pattern = '/\/news\/([a-zA-Z0-9\-_]+)/';
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }
        return null;
    }
    
    /**
     * 检测设备类型
     *
     * @param string $userAgent
     * @return string
     */
    protected function detectDeviceType($userAgent)
    {
        $userAgent = strtolower($userAgent);
        
        if (strpos($userAgent, 'mobile') !== false || strpos($userAgent, 'android') !== false) {
            return 'mobile';
        } elseif (strpos($userAgent, 'tablet') !== false || strpos($userAgent, 'ipad') !== false) {
            return 'tablet';
        } else {
            return 'desktop';
        }
    }
    
    /**
     * 根据时间段获取起始日期
     *
     * @param string $period
     * @return \Carbon\Carbon
     */
    protected function getStartDate($period)
    {
        switch ($period) {
            case 'today':
                return Carbon::today();
            case 'yesterday':
                return Carbon::yesterday();
            case 'week':
                return Carbon::now()->subWeek();
            case 'month':
                return Carbon::now()->subMonth();
            case 'quarter':
                return Carbon::now()->subQuarter();
            case 'year':
                return Carbon::now()->subYear();
            default:
                return Carbon::now()->subWeek();
        }
    }
    
    /**
     * 获取每日访问趋势
     *
     * @param \Carbon\Carbon $startDate
     * @return array
     */
    protected function getDailyTrends($startDate)
    {
        $trends = NewsAnalytics::where('visited_at', '>=', $startDate)
            ->select(DB::raw('DATE(visited_at) as date'), DB::raw('count(*) as views'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // 填充没有数据的日期
        $result = [];
        $currentDate = $startDate->copy();
        $endDate = Carbon::now();
        
        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->format('Y-m-d');
            $found = false;
            
            foreach ($trends as $trend) {
                if ($trend->date == $dateStr) {
                    $result[] = [
                        'date' => $dateStr,
                        'views' => $trend->views
                    ];
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                $result[] = [
                    'date' => $dateStr,
                    'views' => 0
                ];
            }
            
            $currentDate->addDay();
        }
        
        return $result;
    }
    
    /**
     * 获取单个新闻的访问趋势
     *
     * @param int $newsId
     * @param \Carbon\Carbon $startDate
     * @return array
     */
    protected function getNewsViewTrends($newsId, $startDate)
    {
        $trends = NewsAnalytics::where('news_id', $newsId)
            ->where('visited_at', '>=', $startDate)
            ->select(DB::raw('DATE(visited_at) as date'), DB::raw('count(*) as views'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // 填充没有数据的日期
        $result = [];
        $currentDate = $startDate->copy();
        $endDate = Carbon::now();
        
        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->format('Y-m-d');
            $found = false;
            
            foreach ($trends as $trend) {
                if ($trend->date == $dateStr) {
                    $result[] = [
                        'date' => $dateStr,
                        'views' => $trend->views
                    ];
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                $result[] = [
                    'date' => $dateStr,
                    'views' => 0
                ];
            }
            
            $currentDate->addDay();
        }
        
        return $result;
    }
}