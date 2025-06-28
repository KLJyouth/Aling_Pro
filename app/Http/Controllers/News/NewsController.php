<?php

namespace App\Http\Controllers\News;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\News\News;
use App\Models\News\NewsCategory;
use App\Models\News\NewsTag;
use App\Services\News\NewsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * 前台新闻控制器
 * 
 * 处理前台新闻相关的请求
 */
class NewsController extends Controller
{
    /**
     * 新闻服务
     *
     * @var NewsService
     */
    protected $newsService;
    
    /**
     * 构造函数
     *
     * @param NewsService $newsService
     */
    public function __construct(NewsService $newsService)
    {
        $this->newsService = $newsService;
    }
    
    /**
     * 显示新闻列表页面
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = News::where("status", "published")
            ->where("published_at", "<=", now());
        
        // 分类筛选
        if ($request->has("category")) {
            $category = NewsCategory::where("slug", $request->input("category"))->firstOrFail();
            $query->where("category_id", $category->id);
        }
        
        // 标签筛选
        if ($request->has("tag")) {
            $tag = NewsTag::where("slug", $request->input("tag"))->firstOrFail();
            $query->whereHas("tags", function($q) use ($tag) {
                $q->where("news_tags.id", $tag->id);
            });
        }
        
        // 搜索
        if ($request->has("search")) {
            $search = $request->input("search");
            $query->where(function($q) use ($search) {
                $q->where("title", "like", "%{$search}%")
                  ->orWhere("content", "like", "%{$search}%")
                  ->orWhere("summary", "like", "%{$search}%");
            });
        }
        
        // 排序
        $sortBy = $request->input("sort", "latest");
        switch ($sortBy) {
            case "popular":
                $query->orderBy("view_count", "desc");
                break;
            case "oldest":
                $query->orderBy("published_at", "asc");
                break;
            case "latest":
            default:
                $query->orderBy("published_at", "desc");
                break;
        }
        
        // 分页
        $news = $query->with(["author", "category", "tags"])
            ->paginate(12);
        
        // 获取分类列表
        $categories = NewsCategory::getActiveCategories();
        
        // 获取标签列表
        $tags = NewsTag::getActiveTags();
        
        // 获取推荐新闻
        $featuredNews = News::where("status", "published")
            ->where("published_at", "<=", now())
            ->where("featured", true)
            ->orderBy("published_at", "desc")
            ->take(5)
            ->get();
        
        return view("news.index", compact("news", "categories", "tags", "featuredNews"));
    }
    
    /**
     * 显示新闻详情页面
     *
     * @param string $slug
     * @return \Illuminate\View\View
     */
    public function show($slug)
    {
        $news = News::where("slug", $slug)
            ->where("status", "published")
            ->where("published_at", "<=", now())
            ->with(["author", "category", "tags"])
            ->firstOrFail();
        
        // 增加浏览次数
        $news->incrementViewCount();
        
        // 获取评论
        $comments = $news->comments()
            ->where("status", "approved")
            ->whereNull("parent_id")
            ->with(["user", "replies" => function($query) {
                $query->where("status", "approved")->with("user");
            }])
            ->orderBy("created_at", "desc")
            ->get();
        
        // 获取相关新闻
        $relatedNews = News::where("id", "!=", $news->id)
            ->where("status", "published")
            ->where("published_at", "<=", now())
            ->where(function($query) use ($news) {
                $query->where("category_id", $news->category_id)
                      ->orWhereHas("tags", function($q) use ($news) {
                          $q->whereIn("news_tags.id", $news->tags->pluck("id"));
                      });
            })
            ->orderBy("published_at", "desc")
            ->take(4)
            ->get();
        
        return view("news.show", compact("news", "comments", "relatedNews"));
    }
    
    /**
     * 显示分类新闻列表页面
     *
     * @param string $slug
     * @return \Illuminate\View\View
     */
    public function category($slug)
    {
        $category = NewsCategory::where("slug", $slug)
            ->where("is_active", true)
            ->firstOrFail();
        
        $news = News::where("category_id", $category->id)
            ->where("status", "published")
            ->where("published_at", "<=", now())
            ->with(["author", "category", "tags"])
            ->orderBy("published_at", "desc")
            ->paginate(12);
        
        // 获取分类列表
        $categories = NewsCategory::getActiveCategories();
        
        // 获取标签列表
        $tags = NewsTag::getActiveTags();
        
        return view("news.category", compact("category", "news", "categories", "tags"));
    }
    
    /**
     * 显示标签新闻列表页面
     *
     * @param string $slug
     * @return \Illuminate\View\View
     */
    public function tag($slug)
    {
        $tag = NewsTag::where("slug", $slug)
            ->where("is_active", true)
            ->firstOrFail();
        
        $news = $tag->news()
            ->where("status", "published")
            ->where("published_at", "<=", now())
            ->with(["author", "category", "tags"])
            ->orderBy("published_at", "desc")
            ->paginate(12);
        
        // 获取分类列表
        $categories = NewsCategory::getActiveCategories();
        
        // 获取标签列表
        $tags = NewsTag::getActiveTags();
        
        return view("news.tag", compact("tag", "news", "categories", "tags"));
    }
    
    /**
     * 提交评论
     *
     * @param Request $request
     * @param string $slug
     * @return \Illuminate\Http\RedirectResponse
     */
    public function comment(Request $request, $slug)
    {
        $news = News::where("slug", $slug)
            ->where("status", "published")
            ->where("published_at", "<=", now())
            ->firstOrFail();
        
        // 验证规则
        $rules = [
            "content" => "required|string|max:1000",
            "parent_id" => "nullable|exists:news_comments,id",
        ];
        
        // 如果未登录，需要额外验证
        if (!Auth::check()) {
            $rules = array_merge($rules, [
                "author_name" => "required|string|max:50",
                "author_email" => "required|email|max:100",
            ]);
        }
        
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // 添加评论
        try {
            $this->newsService->addComment(
                $news->id,
                $request->all(),
                Auth::id()
            );
            
            return redirect()->back()
                ->with("success", "评论提交成功，等待审核");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with("error", "评论提交失败: " . $e->getMessage())
                ->withInput();
        }
    }
}
