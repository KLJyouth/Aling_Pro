<?php

namespace App\Http\Controllers\Admin\News;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\News\NewsTag;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * ���ű�ǩ���������
 * 
 * �����̨���ű�ǩ������ص�����
 */
class NewsTagController extends Controller
{
    /**
     * ���캯��
     */
    public function __construct()
    {
        $this->middleware(["auth", "role:admin,editor"]);
    }
    
    /**
     * ��ʾ��ǩ�б�ҳ��
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $tags = NewsTag::withCount("news")->orderBy("name")->get();
        
        return view("admin.news.tags.index", compact("tags"));
    }
    
    /**
     * ��ʾ������ǩ��
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view("admin.news.tags.create");
    }
    
    /**
     * �洢��ǩ
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // ��֤����
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
        
        // �������
        $slug = $request->input("slug");
        if (empty($slug)) {
            $slug = Str::slug($request->input("name"));
        }
        
        // ȷ������Ψһ
        $count = 0;
        $originalSlug = $slug;
        while (NewsTag::where("slug", $slug)->exists()) {
            $count++;
            $slug = $originalSlug . "-" . $count;
        }
        
        // ������ǩ
        NewsTag::create([
            "name" => $request->input("name"),
            "slug" => $slug,
            "description" => $request->input("description"),
            "is_active" => $request->boolean("is_active", true),
        ]);
        
        return redirect()->route("admin.news.tags.index")
            ->with("success", "��ǩ�����ɹ�");
    }
    
    /**
     * ��ʾ�༭��ǩ��
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
     * ���±�ǩ
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $tag = NewsTag::findOrFail($id);
        
        // ��֤����
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
        
        // �������
        $slug = $request->input("slug");
        if (empty($slug)) {
            $slug = Str::slug($request->input("name"));
        }
        
        // ȷ������Ψһ
        $count = 0;
        $originalSlug = $slug;
        while (NewsTag::where("slug", $slug)->where("id", "!=", $id)->exists()) {
            $count++;
            $slug = $originalSlug . "-" . $count;
        }
        
        // ���±�ǩ
        $tag->update([
            "name" => $request->input("name"),
            "slug" => $slug,
            "description" => $request->input("description"),
            "is_active" => $request->boolean("is_active", $tag->is_active),
        ]);
        
        return redirect()->route("admin.news.tags.index")
            ->with("success", "��ǩ���³ɹ�");
    }
    
    /**
     * ɾ����ǩ
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $tag = NewsTag::findOrFail($id);
        
        // ����Ƿ��й���������
        $hasNews = $tag->news()->exists();
        if ($hasNews) {
            return redirect()->back()
                ->with("error", "�޷�ɾ����ǩ�����ڹ���������");
        }
        
        $tag->delete();
        
        return redirect()->route("admin.news.tags.index")
            ->with("success", "��ǩɾ���ɹ�");
    }
    
    /**
     * ���±�ǩ״̬
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleStatus($id)
    {
        $tag = NewsTag::findOrFail($id);
        $tag->is_active = !$tag->is_active;
        $tag->save();
        
        $status = $tag->is_active ? "����" : "����";
        
        return redirect()->back()
            ->with("success", "��ǩ��{$status}");
    }
}
