<?php

namespace App\Http\Controllers\Admin\News;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\News\NewsTag;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * 新闻标签管理控制器
 * 
 * 处理后台新闻标签管理相关的请求
 */
class NewsTagController extends Controller
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->middleware(["auth", "role:admin,editor"]);
    }
    
    /**
     * 显示标签列表页面
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $tags = NewsTag::withCount("news")->orderBy("name")->get();
        
        return view("admin.news.tags.index", compact("tags"));
    }
    
    /**
     * 显示创建标签表单
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view("admin.news.tags.create");
    }
    
    /**
     * 存储标签
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // 验证请求
        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:255",
            "slug" => "nullable|string|max:255|unique:news_tags,slug",
            "description" => "nullable|string",
            "is_active" => "nullable|boolean",
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // 处理别名
        $slug = $request->input("slug");
        if (empty($slug)) {
            $slug = Str::slug($request->input("name"));
        }
        
        // 确保别名唯一
        $count = 0;
        $originalSlug = $slug;
        while (NewsTag::where("slug", $slug)->exists()) {
            $count++;
            $slug = $originalSlug . "-" . $count;
        }
        
        // 创建标签
        NewsTag::create([
            "name" => $request->input("name"),
            "slug" => $slug,
            "description" => $request->input("description"),
            "is_active" => $request->boolean("is_active", true),
        ]);
        
        return redirect()->route("admin.news.tags.index")
            ->with("success", "标签创建成功");
    }
    
    /**
     * 显示编辑标签表单
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $tag = NewsTag::findOrFail($id);
        
        return view("admin.news.tags.edit", compact("tag"));
    }
    
    /**
     * 更新标签
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $tag = NewsTag::findOrFail($id);
        
        // 验证请求
        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:255",
            "slug" => "nullable|string|max:255|unique:news_tags,slug," . $id,
            "description" => "nullable|string",
            "is_active" => "nullable|boolean",
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // 处理别名
        $slug = $request->input("slug");
        if (empty($slug)) {
            $slug = Str::slug($request->input("name"));
        }
        
        // 确保别名唯一
        $count = 0;
        $originalSlug = $slug;
        while (NewsTag::where("slug", $slug)->where("id", "!=", $id)->exists()) {
            $count++;
            $slug = $originalSlug . "-" . $count;
        }
        
        // 更新标签
        $tag->update([
            "name" => $request->input("name"),
            "slug" => $slug,
            "description" => $request->input("description"),
            "is_active" => $request->boolean("is_active", $tag->is_active),
        ]);
        
        return redirect()->route("admin.news.tags.index")
            ->with("success", "标签更新成功");
    }
    
    /**
     * 删除标签
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $tag = NewsTag::findOrFail($id);
        
        // 检查是否有关联的新闻
        $hasNews = $tag->news()->exists();
        if ($hasNews) {
            return redirect()->back()
                ->with("error", "无法删除标签，存在关联的新闻");
        }
        
        $tag->delete();
        
        return redirect()->route("admin.news.tags.index")
            ->with("success", "标签删除成功");
    }
    
    /**
     * 更新标签状态
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleStatus($id)
    {
        $tag = NewsTag::findOrFail($id);
        $tag->is_active = !$tag->is_active;
        $tag->save();
        
        $status = $tag->is_active ? "激活" : "禁用";
        
        return redirect()->back()
            ->with("success", "标签已{$status}");
    }
}
