<?php

namespace App\Http\Controllers\Admin\News;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\News\NewsService;
use App\Models\News\News;
use App\Models\News\NewsCategory;
use App\Models\News\NewsTag;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * 新闻管理控制器
 * 
 * 处理后台新闻管理相关的请求
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
        $this->middleware(["auth", "role:admin,editor"]);
    }
    
    /**
     * 显示新闻列表页面
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = News::query();
        
        // 筛选条件
        if ($request->has("status")) {
            $query->where("status", $request->input("status"));
        }
        
        if ($request->has("category_id")) {
            $query->where("category_id", $request->input("category_id"));
        }
        
        if ($request->has("featured")) {
            $query->where("featured", $request->boolean("featured"));
        }
        
        if ($request->has("search")) {
            $search = $request->input("search");
            $query->where(function($q) use ($search) {
                $q->where("title", "like", "%{$search}%")
                  ->orWhere("content", "like", "%{$search}%")
                  ->orWhere("summary", "like", "%{$search}%");
            });
        }
        
        // 排序
        $sortField = $request->input("sort_field", "created_at");
        $sortDirection = $request->input("sort_direction", "desc");
        $query->orderBy($sortField, $sortDirection);
        
        // 分页
        $news = $query->with(["author", "category"])->paginate(15);
        
        // 获取分类列表
        $categories = NewsCategory::getActiveCategories();
        
        return view("admin.news.index", compact("news", "categories"));
    }
    
    /**
     * 显示创建新闻表单
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $categories = NewsCategory::getActiveCategories();
        $tags = NewsTag::getActiveTags();
        
        return view("admin.news.create", compact("categories", "tags"));
    }
    
    /**
     * 存储新闻
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // 验证请求
        $validator = Validator::make($request->all(), [
            "title" => "required|string|max:255",
            "slug" => "nullable|string|max:255|unique:news,slug",
            "content" => "required|string",
            "summary" => "nullable|string|max:500",
            "category_id" => "required|exists:news_categories,id",
            "status" => "required|in:draft,published,archived",
            "published_at" => "nullable|date",
            "featured" => "nullable|boolean",
            "cover_image" => "nullable|image|max:2048", // 最大2MB
            "meta_keywords" => "nullable|string|max:255",
            "meta_description" => "nullable|string|max:255",
            "tags" => "nullable|array",
            "tags.*" => "string|max:50",
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        try {
            // 创建新闻
            $news = $this->newsService->createNews(
                $request->except(["cover_image", "tags"]),
                Auth::id(),
                $request->file("cover_image"),
                $request->input("tags", [])
            );
            
            return redirect()->route("admin.news.index")
                ->with("success", "新闻创建成功");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with("error", "新闻创建失败: " . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * 显示编辑新闻表单
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $news = News::with("tags")->findOrFail($id);
        $categories = NewsCategory::getActiveCategories();
        $tags = NewsTag::getActiveTags();
        $selectedTags = $news->tags->pluck("name")->toArray();
        
        return view("admin.news.edit", compact("news", "categories", "tags", "selectedTags"));
    }
    
    /**
     * 更新新闻
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        // 验证请求
        $validator = Validator::make($request->all(), [
            "title" => "required|string|max:255",
            "slug" => "nullable|string|max:255|unique:news,slug," . $id,
            "content" => "required|string",
            "summary" => "nullable|string|max:500",
            "category_id" => "required|exists:news_categories,id",
            "status" => "required|in:draft,published,archived",
            "published_at" => "nullable|date",
            "featured" => "nullable|boolean",
            "cover_image" => "nullable|image|max:2048", // 最大2MB
            "meta_keywords" => "nullable|string|max:255",
            "meta_description" => "nullable|string|max:255",
            "tags" => "nullable|array",
            "tags.*" => "string|max:50",
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        try {
            // 更新新闻
            $news = $this->newsService->updateNews(
                $id,
                $request->except(["cover_image", "tags"]),
                $request->file("cover_image"),
                $request->input("tags", [])
            );
            
            return redirect()->route("admin.news.index")
                ->with("success", "新闻更新成功");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with("error", "新闻更新失败: " . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * 删除新闻
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            $this->newsService->deleteNews($id);
            
            return redirect()->route("admin.news.index")
                ->with("success", "新闻删除成功");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with("error", "新闻删除失败: " . $e->getMessage());
        }
    }
    
    /**
     * 设置/取消推荐
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleFeatured($id)
    {
        $news = News::findOrFail($id);
        
        if ($news->featured) {
            $news->unsetFeatured();
            $message = "取消推荐成功";
        } else {
            $news->setFeatured();
            $message = "设置推荐成功";
        }
        
        return redirect()->back()->with("success", $message);
    }
    
    /**
     * 发布新闻
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function publish($id)
    {
        $news = News::findOrFail($id);
        $news->publish();
        
        return redirect()->back()->with("success", "新闻已发布");
    }
    
    /**
     * 设为草稿
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function draft($id)
    {
        $news = News::findOrFail($id);
        $news->setDraft();
        
        return redirect()->back()->with("success", "新闻已设为草稿");
    }
    
    /**
     * 归档新闻
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function archive($id)
    {
        $news = News::findOrFail($id);
        $news->archive();
        
        return redirect()->back()->with("success", "新闻已归档");
    }
}
