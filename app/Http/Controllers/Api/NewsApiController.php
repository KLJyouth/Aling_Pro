<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\News\News;
use App\Models\News\NewsCategory;
use App\Models\News\NewsTag;
use App\Models\News\NewsComment;
use App\Services\News\NewsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * 新闻API控制器
 * 
 * 处理新闻相关的API请求
 */
class NewsApiController extends Controller
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
        $this->middleware("auth:api")->except(["index", "show", "getCategories", "getTags", "getByCategory", "getByTag", "getComments"]);
    }
    
    /**
     * 获取新闻列表
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = News::where("status", "published")
            ->where("published_at", "<=", now());
        
        // 分类筛选
        if ($request->has("category_id")) {
            $query->where("category_id", $request->input("category_id"));
        }
        
        // 标签筛选
        if ($request->has("tag_id")) {
            $tagId = $request->input("tag_id");
            $query->whereHas("tags", function($q) use ($tagId) {
                $q->where("news_tags.id", $tagId);
            });
        }
        
        // 推荐筛选
        if ($request->has("featured")) {
            $query->where("featured", $request->boolean("featured"));
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
        $sortField = $request->input("sort_field", "published_at");
        $sortDirection = $request->input("sort_direction", "desc");
        $query->orderBy($sortField, $sortDirection);
        
        // 分页
        $perPage = $request->input("per_page", 10);
        $news = $query->with(["author", "category", "tags"])
            ->paginate($perPage);
        
        return response()->json([
            "status" => "success",
            "data" => $news
        ]);
    }
    
    /**
     * 获取新闻详情
     *
     * @param string $slug
     * @return \Illuminate\Http\JsonResponse
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
        
        return response()->json([
            "status" => "success",
            "data" => $news
        ]);
    }
    
    /**
     * 获取新闻分类列表
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCategories()
    {
        $categories = NewsCategory::where("is_active", true)
            ->orderBy("order")
            ->get();
        
        return response()->json([
            "status" => "success",
            "data" => $categories
        ]);
    }
    
    /**
     * 获取新闻标签列表
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTags()
    {
        $tags = NewsTag::where("is_active", true)
            ->withCount("news")
            ->orderBy("name")
            ->get();
        
        return response()->json([
            "status" => "success",
            "data" => $tags
        ]);
    }
    
    /**
     * 根据分类获取新闻列表
     *
     * @param string $slug
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByCategory($slug, Request $request)
    {
        $category = NewsCategory::where("slug", $slug)
            ->where("is_active", true)
            ->firstOrFail();
        
        $query = News::where("category_id", $category->id)
            ->where("status", "published")
            ->where("published_at", "<=", now());
        
        // 排序
        $sortField = $request->input("sort_field", "published_at");
        $sortDirection = $request->input("sort_direction", "desc");
        $query->orderBy($sortField, $sortDirection);
        
        // 分页
        $perPage = $request->input("per_page", 10);
        $news = $query->with(["author", "tags"])
            ->paginate($perPage);
        
        return response()->json([
            "status" => "success",
            "category" => $category,
            "data" => $news
        ]);
    }
    
    /**
     * 根据标签获取新闻列表
     *
     * @param string $slug
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByTag($slug, Request $request)
    {
        $tag = NewsTag::where("slug", $slug)
            ->where("is_active", true)
            ->firstOrFail();
        
        $query = $tag->news()
            ->where("status", "published")
            ->where("published_at", "<=", now());
        
        // 排序
        $sortField = $request->input("sort_field", "published_at");
        $sortDirection = $request->input("sort_direction", "desc");
        $query->orderBy($sortField, $sortDirection);
        
        // 分页
        $perPage = $request->input("per_page", 10);
        $news = $query->with(["author", "category"])
            ->paginate($perPage);
        
        return response()->json([
            "status" => "success",
            "tag" => $tag,
            "data" => $news
        ]);
    }
    
    /**
     * 获取新闻评论
     *
     * @param string $slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function getComments($slug)
    {
        $news = News::where("slug", $slug)
            ->where("status", "published")
            ->where("published_at", "<=", now())
            ->firstOrFail();
        
        $comments = $news->comments()
            ->where("status", "approved")
            ->whereNull("parent_id")
            ->with(["user", "replies" => function($query) {
                $query->where("status", "approved")->with("user");
            }])
            ->orderBy("created_at", "desc")
            ->get();
        
        return response()->json([
            "status" => "success",
            "data" => $comments
        ]);
    }
    
    /**
     * 添加新闻评论
     *
     * @param Request $request
     * @param string $slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function addComment(Request $request, $slug)
    {
        $news = News::where("slug", $slug)
            ->where("status", "published")
            ->where("published_at", "<=", now())
            ->firstOrFail();
        
        // 验证规则
        $rules = [
            "content" => "required|string|max:1000",
            "parent_id" => "nullable|exists:news_comments,id",
            "is_anonymous" => "nullable|boolean",
        ];
        
        // 如果匿名，需要额外验证
        if ($request->boolean("is_anonymous", false)) {
            $rules = array_merge($rules, [
                "author_name" => "required|string|max:50",
                "author_email" => "required|email|max:100",
            ]);
        }
        
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            return response()->json([
                "status" => "error",
                "message" => "验证失败",
                "errors" => $validator->errors()
            ], 422);
        }
        
        // 添加评论
        try {
            $comment = $this->newsService->addComment(
                $news->id,
                $request->all(),
                Auth::id()
            );
            
            return response()->json([
                "status" => "success",
                "message" => "评论提交成功，等待审核",
                "data" => $comment
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                "status" => "error",
                "message" => "评论提交失败: " . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 获取推荐新闻
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFeatured(Request $request)
    {
        $limit = $request->input("limit", 5);
        
        $featuredNews = News::where("status", "published")
            ->where("published_at", "<=", now())
            ->where("featured", true)
            ->with(["author", "category"])
            ->orderBy("published_at", "desc")
            ->take($limit)
            ->get();
        
        return response()->json([
            "status" => "success",
            "data" => $featuredNews
        ]);
    }
}
