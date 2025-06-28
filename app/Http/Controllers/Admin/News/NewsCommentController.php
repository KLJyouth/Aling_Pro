<?php

namespace App\Http\Controllers\Admin\News;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\News\NewsComment;
use Illuminate\Support\Facades\Validator;

/**
 * 新闻评论管理控制器
 * 
 * 处理后台新闻评论管理相关的请求
 */
class NewsCommentController extends Controller
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->middleware(["auth", "role:admin,editor,moderator"]);
    }
    
    /**
     * 显示评论列表页面
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = NewsComment::query();
        
        // 筛选条件
        if ($request->has("status")) {
            $query->where("status", $request->input("status"));
        }
        
        if ($request->has("news_id")) {
            $query->where("news_id", $request->input("news_id"));
        }
        
        if ($request->has("search")) {
            $search = $request->input("search");
            $query->where(function($q) use ($search) {
                $q->where("content", "like", "%{$search}%")
                  ->orWhere("author_name", "like", "%{$search}%")
                  ->orWhere("author_email", "like", "%{$search}%");
            });
        }
        
        // 排序
        $sortField = $request->input("sort_field", "created_at");
        $sortDirection = $request->input("sort_direction", "desc");
        $query->orderBy($sortField, $sortDirection);
        
        // 分页
        $comments = $query->with(["news", "user", "parent"])->paginate(20);
        
        return view("admin.news.comments.index", compact("comments"));
    }
    
    /**
     * 显示评论详情
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $comment = NewsComment::with(["news", "user", "parent", "replies"])->findOrFail($id);
        
        return view("admin.news.comments.show", compact("comment"));
    }
    
    /**
     * 审核通过评论
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approve($id)
    {
        $comment = NewsComment::findOrFail($id);
        $comment->approve();
        
        return redirect()->back()
            ->with("success", "评论已审核通过");
    }
    
    /**
     * 拒绝评论
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reject($id)
    {
        $comment = NewsComment::findOrFail($id);
        $comment->reject();
        
        return redirect()->back()
            ->with("success", "评论已拒绝");
    }
    
    /**
     * 批量审核评论
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function batchAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "action" => "required|in:approve,reject,delete",
            "ids" => "required|array",
            "ids.*" => "exists:news_comments,id",
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->with("error", "操作失败：" . $validator->errors()->first());
        }
        
        $action = $request->input("action");
        $ids = $request->input("ids");
        
        switch ($action) {
            case "approve":
                NewsComment::whereIn("id", $ids)->update(["status" => "approved"]);
                $message = "已批量审核通过评论";
                break;
            case "reject":
                NewsComment::whereIn("id", $ids)->update(["status" => "rejected"]);
                $message = "已批量拒绝评论";
                break;
            case "delete":
                NewsComment::whereIn("id", $ids)->delete();
                $message = "已批量删除评论";
                break;
        }
        
        return redirect()->back()
            ->with("success", $message);
    }
    
    /**
     * 回复评论
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reply(Request $request, $id)
    {
        $comment = NewsComment::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            "content" => "required|string|max:1000",
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // 创建回复
        $reply = NewsComment::create([
            "news_id" => $comment->news_id,
            "user_id" => auth()->id(),
            "parent_id" => $id,
            "content" => $request->input("content"),
            "status" => "approved", // 管理员回复自动审核通过
            "ip_address" => $request->ip(),
            "user_agent" => $request->userAgent(),
            "is_anonymous" => false,
        ]);
        
        return redirect()->back()
            ->with("success", "回复成功");
    }
    
    /**
     * 删除评论
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $comment = NewsComment::findOrFail($id);
        
        // 删除子评论
        $comment->replies()->delete();
        
        // 删除评论
        $comment->delete();
        
        return redirect()->route("admin.news.comments.index")
            ->with("success", "评论删除成功");
    }
}
