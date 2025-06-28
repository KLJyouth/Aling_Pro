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
 * ǰ̨���ſ�����
 * 
 * ����ǰ̨������ص�����
 */
class NewsController extends Controller
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
    }
    
    /**
     * ��ʾ�����б�ҳ��
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = News::where("status", "published")
            ->where("published_at", "<=", now());
        
        // ����ɸѡ
        if ($request->has("category")) {
            $category = NewsCategory::where("slug", $request->input("category"))->firstOrFail();
            $query->where("category_id", $category->id);
        }
        
        // ��ǩɸѡ
        if ($request->has("tag")) {
            $tag = NewsTag::where("slug", $request->input("tag"))->firstOrFail();
            $query->whereHas("tags", function($q) use ($tag) {
                $q->where("news_tags.id", $tag->id);
            });
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
        
        // ��ҳ
        $news = $query->with(["author", "category", "tags"])
            ->paginate(12);
        
        // ��ȡ�����б�
        $categories = NewsCategory::getActiveCategories();
        
        // ��ȡ��ǩ�б�
        $tags = NewsTag::getActiveTags();
        
        // ��ȡ�Ƽ�����
        $featuredNews = News::where("status", "published")
            ->where("published_at", "<=", now())
            ->where("featured", true)
            ->orderBy("published_at", "desc")
            ->take(5)
            ->get();
        
        return view("news.index", compact("news", "categories", "tags", "featuredNews"));
    }
    
    /**
     * ��ʾ��������ҳ��
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
        
        // �����������
        $news->incrementViewCount();
        
        // ��ȡ����
        $comments = $news->comments()
            ->where("status", "approved")
            ->whereNull("parent_id")
            ->with(["user", "replies" => function($query) {
                $query->where("status", "approved")->with("user");
            }])
            ->orderBy("created_at", "desc")
            ->get();
        
        // ��ȡ�������
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
     * ��ʾ���������б�ҳ��
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
        
        // ��ȡ�����б�
        $categories = NewsCategory::getActiveCategories();
        
        // ��ȡ��ǩ�б�
        $tags = NewsTag::getActiveTags();
        
        return view("news.category", compact("category", "news", "categories", "tags"));
    }
    
    /**
     * ��ʾ��ǩ�����б�ҳ��
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
        
        // ��ȡ�����б�
        $categories = NewsCategory::getActiveCategories();
        
        // ��ȡ��ǩ�б�
        $tags = NewsTag::getActiveTags();
        
        return view("news.tag", compact("tag", "news", "categories", "tags"));
    }
    
    /**
     * �ύ����
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
        
        // ��֤����
        $rules = [
            "content" => "required|string|max:1000",
            "parent_id" => "nullable|exists:news_comments,id",
        ];
        
        // ���δ��¼����Ҫ������֤
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
        
        // �������
        try {
            $this->newsService->addComment(
                $news->id,
                $request->all(),
                Auth::id()
            );
            
            return redirect()->back()
                ->with("success", "�����ύ�ɹ����ȴ����");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with("error", "�����ύʧ��: " . $e->getMessage())
                ->withInput();
        }
    }
}
