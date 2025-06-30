<?php

namespace App\Http\Controllers;

use App\Models\MembershipLevel;
use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * ��ʾ����ҳ��
     *
     * @return \Illuminate\View\View
     */
    public function features()
    {
        return view("pages.features");
    }
    
    /**
     * ��ʾ�۸�ҳ��
     *
     * @return \Illuminate\View\View
     */
    public function pricing()
    {
        $membershipLevels = MembershipLevel::where("status", "active")
            ->orderBy("sort_order")
            ->get();
            
        return view("pages.pricing", compact("membershipLevels"));
    }
    
    /**
     * ��ʾAPI�ĵ�ҳ��
     *
     * @return \Illuminate\View\View
     */
    public function apiDocs()
    {
        return view("pages.api-docs");
    }
    
    /**
     * ��ʾ�����б�ҳ��
     *
     * @return \Illuminate\View\View
     */
    public function blog()
    {
        // ����Ӧ�ô����ݿ��ȡ��������
        $posts = [];
        
        return view("pages.blog", compact("posts"));
    }
    
    /**
     * ��ʾ��ƪ��������ҳ��
     *
     * @param string $slug
     * @return \Illuminate\View\View
     */
    public function blogPost($slug)
    {
        // ����Ӧ�ô����ݿ��ȡ��������
        $post = null;
        
        return view("pages.blog-post", compact("post"));
    }
    
    /**
     * ��ʾ��ϵ����ҳ��
     *
     * @return \Illuminate\View\View
     */
    public function contact()
    {
        return view("pages.contact");
    }
    
    /**
     * ������ϵ���ύ
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submitContact(Request $request)
    {
        $request->validate([
            "name" => "required|string|max:255",
            "email" => "required|email|max:255",
            "subject" => "required|string|max:255",
            "message" => "required|string",
        ]);
        
        // ������ϵ���ύ
        // ���Է����ʼ��򱣴浽���ݿ�
        
        return redirect()->route("contact")->with("success", "������Ϣ�ѳɹ����ͣ����ǻᾡ��ظ�����");
    }
    
    /**
     * ��ʾ��������ҳ��
     *
     * @return \Illuminate\View\View
     */
    public function about()
    {
        return view("pages.about");
    }
    
    /**
     * ��ʾ�Ŷ�ҳ��
     *
     * @return \Illuminate\View\View
     */
    public function team()
    {
        return view("pages.team");
    }
    
    /**
     * ��ʾ��Ƹҳ��
     *
     * @return \Illuminate\View\View
     */
    public function careers()
    {
        return view("pages.careers");
    }
    
    /**
     * ��ʾʾ��ҳ��
     *
     * @return \Illuminate\View\View
     */
    public function examples()
    {
        return view("pages.examples");
    }
    
    /**
     * ��ʾ�̳�ҳ��
     *
     * @return \Illuminate\View\View
     */
    public function tutorials()
    {
        return view("pages.tutorials");
    }
    
    /**
     * ��ʾ��������ҳ��
     *
     * @return \Illuminate\View\View
     */
    public function faq()
    {
        return view("pages.faq");
    }
    
    /**
     * ��ʾ֧��ҳ��
     *
     * @return \Illuminate\View\View
     */
    public function support()
    {
        return view("pages.support");
    }
    
    /**
     * ��ʾ��������ҳ��
     *
     * @return \Illuminate\View\View
     */
    public function terms()
    {
        return view("pages.terms");
    }
    
    /**
     * ��ʾ��˽����ҳ��
     *
     * @return \Illuminate\View\View
     */
    public function privacy()
    {
        return view("pages.privacy");
    }
    
    /**
     * ��ʾ��ȫҳ��
     *
     * @return \Illuminate\View\View
     */
    public function security()
    {
        return view("pages.security");
    }
}
