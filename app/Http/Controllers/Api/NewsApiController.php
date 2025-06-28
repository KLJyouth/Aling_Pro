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
 * ����API������
 * 
 * ����������ص�API����
 */
class NewsApiController extends Controller
{
    /**
     * ���ŷ���
     *
     * @var NewsService
     */
    protected $newsService;
    
    /**
     * ���캯��
     *
     * @param NewsService $newsService
     */
    public function __construct(NewsService $newsService)
    {
        $this->newsService = $newsService;
        $this->middleware("auth:api")->except(["index", "show", "getCategories", "getTags", "getByCategory", "getByTag", "getComments"]);
    }
    
    /**
     * ��ȡ�����б�
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = News::where("status", "published")
            ->where("published_at", "<=", now());
        
        // ����ɸѡ
        if ($request->has("category_id")) {
            $query->where("category_id", $request->input("category_id"));
        }
        
        // ��ǩɸѡ
        if ($request->has("tag_id")) {
            $tagId = $request->input("tag_id");
            $query->whereHas("tags", function($q) use ($tagId) {
                $q->where("news_tags.id", $tagId);
            });
        }
        
        // �Ƽ�ɸѡ
        if ($request->has("featured")) {
            $query->where("featured", $request->boolean("featured"));
        }
        
        // ����
        if ($request->has("search")) {
            $search = $request->input("search");
            $query->where(function($q) use ($search) {
                $q->where("title", "like", "%{$search}%")
                  ->orWhere("content", "like", "%{$search}%")
                  ->orWhere("summary", "like", "%{$search}%");
            });
        }
        
        // ����
        $sortField = $request->input("sort_field", "published_at");
        $sortDirection = $request->input("sort_direction", "desc");
        $query->orderBy($sortField, $sortDirection);
        
        // ��ҳ
        $perPage = $request->input("per_page", 10);
        $news = $query->with(["author", "category", "tags"])
            ->paginate($perPage);
        
        return response()->json([
            "status" => "success",
            "data" => $news
        ]);
    }
    
    /**
     * ��ȡ��������
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
        
        // �����������
        $news->incrementViewCount();
        
        return response()->json([
            "status" => "success",
            "data" => $news
        ]);
    }
    
    /**
     * ��ȡ���ŷ����б�
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
     * ��ȡ���ű�ǩ�б�
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
     * ���ݷ����ȡ�����б�
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
        
        // ����
        $sortField = $request->input("sort_field", "published_at");
        $sortDirection = $request->input("sort_direction", "desc");
        $query->orderBy($sortField, $sortDirection);
        
        // ��ҳ
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
     * ���ݱ�ǩ��ȡ�����б�
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
        
        // ����
        $sortField = $request->input("sort_field", "published_at");
        $sortDirection = $request->input("sort_direction", "desc");
        $query->orderBy($sortField, $sortDirection);
        
        // ��ҳ
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
     * ��ȡ��������
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
     * �����������
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
        
        // ��֤����
        $rules = [
            "content" => "required|string|max:1000",
            "parent_id" => "nullable|exists:news_comments,id",
            "is_anonymous" => "nullable|boolean",
        ];
        
        // �����������Ҫ������֤
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
                "message" => "��֤ʧ��",
                "errors" => $validator->errors()
            ], 422);
        }
        
        // �������
        try {
            $comment = $this->newsService->addComment(
                $news->id,
                $request->all(),
                Auth::id()
            );
            
            return response()->json([
                "status" => "success",
                "message" => "�����ύ�ɹ����ȴ����",
                "data" => $comment
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                "status" => "error",
                "message" => "�����ύʧ��: " . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * ��ȡ�Ƽ�����
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
