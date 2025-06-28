<?php

namespace App\Http\Controllers\Admin\News;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\News\NewsCategory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * ���ŷ�����������
 * 
 * �����̨���ŷ��������ص�����
 */
class NewsCategoryController extends Controller
{
    /**
     * ���캯��
     */
    public function __construct()
    {
        $this->middleware(["auth", "role:admin,editor"]);
    }
    
    /**
     * ��ʾ�����б�ҳ��
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $categories = NewsCategory::with("parent")->orderBy("order")->get();
        
        return view("admin.news.categories.index", compact("categories"));
    }
    
    /**
     * ��ʾ���������
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $categories = NewsCategory::orderBy("name")->get();
        
        return view("admin.news.categories.create", compact("categories"));
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
        
        // �������
        $slug = $request->input("slug");
        if (empty($slug)) {
            $slug = Str::slug($request->input("name"));
        }
        
        // ȷ������Ψһ
        $count = 0;
        $originalSlug = $slug;
        while (NewsCategory::where("slug", $slug)->exists()) {
            $count++;
            $slug = $originalSlug . "-" . $count;
        }
        
        // ��������
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
            ->with("success", "���ഴ���ɹ�");
    }
    
    /**
     * ��ʾ�༭�����
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
     * ���·���
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $category = NewsCategory::findOrFail($id);
        
        // ��֤����
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
        
        // ��鸸�����Ƿ�Ϊ������ӷ���
        $parentId = $request->input("parent_id");
        if ($parentId) {
            if ($parentId == $id) {
                return redirect()->back()
                    ->with("error", "�����಻��Ϊ����")
                    ->withInput();
            }
            
            // ����Ƿ�Ϊ�ӷ���
            $childIds = NewsCategory::where("parent_id", $id)->pluck("id")->toArray();
            if (in_array($parentId, $childIds)) {
                return redirect()->back()
                    ->with("error", "�����಻��Ϊ���ӷ���")
                    ->withInput();
            }
        }
        
        // �������
        $slug = $request->input("slug");
        if (empty($slug)) {
            $slug = Str::slug($request->input("name"));
        }
        
        // ȷ������Ψһ
        $count = 0;
        $originalSlug = $slug;
        while (NewsCategory::where("slug", $slug)->where("id", "!=", $id)->exists()) {
            $count++;
            $slug = $originalSlug . "-" . $count;
        }
        
        // ���·���
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
            ->with("success", "������³ɹ�");
    }
    
    /**
     * ɾ������
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $category = NewsCategory::findOrFail($id);
        
        // ����Ƿ����ӷ���
        $hasChildren = NewsCategory::where("parent_id", $id)->exists();
        if ($hasChildren) {
            return redirect()->back()
                ->with("error", "�޷�ɾ�����࣬�����ӷ���");
        }
        
        // ����Ƿ��й���������
        $hasNews = $category->news()->exists();
        if ($hasNews) {
            return redirect()->back()
                ->with("error", "�޷�ɾ�����࣬���ڹ���������");
        }
        
        $category->delete();
        
        return redirect()->route("admin.news.categories.index")
            ->with("success", "����ɾ���ɹ�");
    }
    
    /**
     * ���·���״̬
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleStatus($id)
    {
        $category = NewsCategory::findOrFail($id);
        $category->is_active = !$category->is_active;
        $category->save();
        
        $status = $category->is_active ? "����" : "����";
        
        return redirect()->back()
            ->with("success", "������{$status}");
    }
}
