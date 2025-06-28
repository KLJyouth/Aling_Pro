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
 * ���Ź��������
 * 
 * �����̨���Ź�����ص�����
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
        $this->middleware(["auth", "role:admin,editor"]);
    }
    
    /**
     * ��ʾ�����б�ҳ��
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = News::query();
        
        // ɸѡ����
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
        
        // ����
        $sortField = $request->input("sort_field", "created_at");
        $sortDirection = $request->input("sort_direction", "desc");
        $query->orderBy($sortField, $sortDirection);
        
        // ��ҳ
        $news = $query->with(["author", "category"])->paginate(15);
        
        // ��ȡ�����б�
        $categories = NewsCategory::getActiveCategories();
        
        return view("admin.news.index", compact("news", "categories"));
    }
    
    /**
     * ��ʾ�������ű�
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
     * �洢����
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // ��֤����
        $validator = Validator::make($request->all(), [
            "title" => "required|string|max:255",
            "slug" => "nullable|string|max:255|unique:news,slug",
            "content" => "required|string",
            "summary" => "nullable|string|max:500",
            "category_id" => "required|exists:news_categories,id",
            "status" => "required|in:draft,published,archived",
            "published_at" => "nullable|date",
            "featured" => "nullable|boolean",
            "cover_image" => "nullable|image|max:2048", // ���2MB
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
            // ��������
            $news = $this->newsService->createNews(
                $request->except(["cover_image", "tags"]),
                Auth::id(),
                $request->file("cover_image"),
                $request->input("tags", [])
            );
            
            return redirect()->route("admin.news.index")
                ->with("success", "���Ŵ����ɹ�");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with("error", "���Ŵ���ʧ��: " . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * ��ʾ�༭���ű�
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
     * ��������
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        // ��֤����
        $validator = Validator::make($request->all(), [
            "title" => "required|string|max:255",
            "slug" => "nullable|string|max:255|unique:news,slug," . $id,
            "content" => "required|string",
            "summary" => "nullable|string|max:500",
            "category_id" => "required|exists:news_categories,id",
            "status" => "required|in:draft,published,archived",
            "published_at" => "nullable|date",
            "featured" => "nullable|boolean",
            "cover_image" => "nullable|image|max:2048", // ���2MB
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
            // ��������
            $news = $this->newsService->updateNews(
                $id,
                $request->except(["cover_image", "tags"]),
                $request->file("cover_image"),
                $request->input("tags", [])
            );
            
            return redirect()->route("admin.news.index")
                ->with("success", "���Ÿ��³ɹ�");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with("error", "���Ÿ���ʧ��: " . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * ɾ������
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            $this->newsService->deleteNews($id);
            
            return redirect()->route("admin.news.index")
                ->with("success", "����ɾ���ɹ�");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with("error", "����ɾ��ʧ��: " . $e->getMessage());
        }
    }
    
    /**
     * ����/ȡ���Ƽ�
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleFeatured($id)
    {
        $news = News::findOrFail($id);
        
        if ($news->featured) {
            $news->unsetFeatured();
            $message = "ȡ���Ƽ��ɹ�";
        } else {
            $news->setFeatured();
            $message = "�����Ƽ��ɹ�";
        }
        
        return redirect()->back()->with("success", $message);
    }
    
    /**
     * ��������
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function publish($id)
    {
        $news = News::findOrFail($id);
        $news->publish();
        
        return redirect()->back()->with("success", "�����ѷ���");
    }
    
    /**
     * ��Ϊ�ݸ�
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function draft($id)
    {
        $news = News::findOrFail($id);
        $news->setDraft();
        
        return redirect()->back()->with("success", "��������Ϊ�ݸ�");
    }
    
    /**
     * �鵵����
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function archive($id)
    {
        $news = News::findOrFail($id);
        $news->archive();
        
        return redirect()->back()->with("success", "�����ѹ鵵");
    }
}
