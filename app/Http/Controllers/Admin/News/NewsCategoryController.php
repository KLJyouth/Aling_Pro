<?php

namespace App\Http\Controllers\Admin\News;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\News\NewsCategory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * 新闻分类管理控制器
 * 
 * 处理后台新闻分类管理相关的请求
 */
class NewsCategoryController extends Controller
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->middleware(["auth", "role:admin,editor"]);
    }
    
    /**
     * 显示分类列表页面
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $categories = NewsCategory::with("parent")->orderBy("order")->get();
        
        return view("admin.news.categories.index", compact("categories"));
    }
    
    /**
     * 显示创建分类表单
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $categories = NewsCategory::orderBy("name")->get();
        
        return view("admin.news.categories.create", compact("categories"));
    }
    
    /**
     * 存储分类
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // 验证请求
        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:255",
            "slug" => "nullable|string|max:255|unique:news_categories,slug",
            "description" => "nullable|string",
            "parent_id" => "nullable|exists:news_categories,id",
            "order" => "nullable|integer|min:0",
            "is_active" => "nullable|boolean",
            "meta_keywords" => "nullable|string|max:255",
            "meta_description" => "nullable|string|max:255",
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
        while (NewsCategory::where("slug", $slug)->exists()) {
            $count++;
            $slug = $originalSlug . "-" . $count;
        }
        
        // 创建分类
        NewsCategory::create([
            "name" => $request->input("name"),
            "slug" => $slug,
            "description" => $request->input("description"),
            "parent_id" => $request->input("parent_id"),
            "order" => $request->input("order", 0),
            "is_active" => $request->boolean("is_active", true),
            "meta_keywords" => $request->input("meta_keywords"),
            "meta_description" => $request->input("meta_description"),
        ]);
        
        return redirect()->route("admin.news.categories.index")
            ->with("success", "分类创建成功");
    }
    
    /**
     * 显示编辑分类表单
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $category = NewsCategory::findOrFail($id);
        $categories = NewsCategory::where("id", "!=", $id)->orderBy("name")->get();
        
        return view("admin.news.categories.edit", compact("category", "categories"));
    }
    
    /**
     * 更新分类
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $category = NewsCategory::findOrFail($id);
        
        // 验证请求
        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:255",
            "slug" => "nullable|string|max:255|unique:news_categories,slug," . $id,
            "description" => "nullable|string",
            "parent_id" => "nullable|exists:news_categories,id",
            "order" => "nullable|integer|min:0",
            "is_active" => "nullable|boolean",
            "meta_keywords" => "nullable|string|max:255",
            "meta_description" => "nullable|string|max:255",
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // 检查父分类是否为自身或子分类
        $parentId = $request->input("parent_id");
        if ($parentId) {
            if ($parentId == $id) {
                return redirect()->back()
                    ->with("error", "父分类不能为自身")
                    ->withInput();
            }
            
            // 检查是否为子分类
            $childIds = NewsCategory::where("parent_id", $id)->pluck("id")->toArray();
            if (in_array($parentId, $childIds)) {
                return redirect()->back()
                    ->with("error", "父分类不能为其子分类")
                    ->withInput();
            }
        }
        
        // 处理别名
        $slug = $request->input("slug");
        if (empty($slug)) {
            $slug = Str::slug($request->input("name"));
        }
        
        // 确保别名唯一
        $count = 0;
        $originalSlug = $slug;
        while (NewsCategory::where("slug", $slug)->where("id", "!=", $id)->exists()) {
            $count++;
            $slug = $originalSlug . "-" . $count;
        }
        
        // 更新分类
        $category->update([
            "name" => $request->input("name"),
            "slug" => $slug,
            "description" => $request->input("description"),
            "parent_id" => $parentId,
            "order" => $request->input("order", $category->order),
            "is_active" => $request->boolean("is_active", $category->is_active),
            "meta_keywords" => $request->input("meta_keywords"),
            "meta_description" => $request->input("meta_description"),
        ]);
        
        return redirect()->route("admin.news.categories.index")
            ->with("success", "分类更新成功");
    }
    
    /**
     * 删除分类
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $category = NewsCategory::findOrFail($id);
        
        // 检查是否有子分类
        $hasChildren = NewsCategory::where("parent_id", $id)->exists();
        if ($hasChildren) {
            return redirect()->back()
                ->with("error", "无法删除分类，存在子分类");
        }
        
        // 检查是否有关联的新闻
        $hasNews = $category->news()->exists();
        if ($hasNews) {
            return redirect()->back()
                ->with("error", "无法删除分类，存在关联的新闻");
        }
        
        $category->delete();
        
        return redirect()->route("admin.news.categories.index")
            ->with("success", "分类删除成功");
    }
    
    /**
     * 更新分类状态
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleStatus($id)
    {
        $category = NewsCategory::findOrFail($id);
        $category->is_active = !$category->is_active;
        $category->save();
        
        $status = $category->is_active ? "激活" : "禁用";
        
        return redirect()->back()
            ->with("success", "分类已{$status}");
    }
}
