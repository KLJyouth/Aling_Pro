<?php

namespace App\Http\Controllers;

use App\Models\MembershipLevel;
use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * 显示功能页面
     *
     * @return \Illuminate\View\View
     */
    public function features()
    {
        return view("pages.features");
    }
    
    /**
     * 显示价格页面
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
     * 显示API文档页面
     *
     * @return \Illuminate\View\View
     */
    public function apiDocs()
    {
        return view("pages.api-docs");
    }
    
    /**
     * 显示博客列表页面
     *
     * @return \Illuminate\View\View
     */
    public function blog()
    {
        // 这里应该从数据库获取博客文章
        $posts = [];
        
        return view("pages.blog", compact("posts"));
    }
    
    /**
     * 显示单篇博客文章页面
     *
     * @param string $slug
     * @return \Illuminate\View\View
     */
    public function blogPost($slug)
    {
        // 这里应该从数据库获取博客文章
        $post = null;
        
        return view("pages.blog-post", compact("post"));
    }
    
    /**
     * 显示联系我们页面
     *
     * @return \Illuminate\View\View
     */
    public function contact()
    {
        return view("pages.contact");
    }
    
    /**
     * 处理联系表单提交
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
        
        // 处理联系表单提交
        // 可以发送邮件或保存到数据库
        
        return redirect()->route("contact")->with("success", "您的消息已成功发送，我们会尽快回复您。");
    }
    
    /**
     * 显示关于我们页面
     *
     * @return \Illuminate\View\View
     */
    public function about()
    {
        return view("pages.about");
    }
    
    /**
     * 显示团队页面
     *
     * @return \Illuminate\View\View
     */
    public function team()
    {
        return view("pages.team");
    }
    
    /**
     * 显示招聘页面
     *
     * @return \Illuminate\View\View
     */
    public function careers()
    {
        return view("pages.careers");
    }
    
    /**
     * 显示示例页面
     *
     * @return \Illuminate\View\View
     */
    public function examples()
    {
        return view("pages.examples");
    }
    
    /**
     * 显示教程页面
     *
     * @return \Illuminate\View\View
     */
    public function tutorials()
    {
        return view("pages.tutorials");
    }
    
    /**
     * 显示常见问题页面
     *
     * @return \Illuminate\View\View
     */
    public function faq()
    {
        return view("pages.faq");
    }
    
    /**
     * 显示支持页面
     *
     * @return \Illuminate\View\View
     */
    public function support()
    {
        return view("pages.support");
    }
    
    /**
     * 显示服务条款页面
     *
     * @return \Illuminate\View\View
     */
    public function terms()
    {
        return view("pages.terms");
    }
    
    /**
     * 显示隐私政策页面
     *
     * @return \Illuminate\View\View
     */
    public function privacy()
    {
        return view("pages.privacy");
    }
    
    /**
     * 显示安全页面
     *
     * @return \Illuminate\View\View
     */
    public function security()
    {
        return view("pages.security");
    }
}
