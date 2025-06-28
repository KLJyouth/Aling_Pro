<?php

namespace App\Http\Controllers\Admin\News;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\News\NewsComment;
use Illuminate\Support\Facades\Validator;

/**
 * �������۹��������
 * 
 * �����̨�������۹�����ص�����
 */
class NewsCommentController extends Controller
{
    /**
     * ���캯��
     */
    public function __construct()
    {
        $this->middleware(["auth", "role:admin,editor,moderator"]);
    }
    
    /**
     * ��ʾ�����б�ҳ��
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = NewsComment::query();
        
        // ɸѡ����
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
        
        // ����
        $sortField = $request->input("sort_field", "created_at");
        $sortDirection = $request->input("sort_direction", "desc");
        $query->orderBy($sortField, $sortDirection);
        
        // ��ҳ
        $comments = $query->with(["news", "user", "parent"])->paginate(20);
        
        return view("admin.news.comments.index", compact("comments"));
    }
    
    /**
     * ��ʾ��������
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
     * ���ͨ������
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approve($id)
    {
        $comment = NewsComment::findOrFail($id);
        $comment->approve();
        
        return redirect()->back()
            ->with("success", "���������ͨ��");
    }
    
    /**
     * �ܾ�����
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reject($id)
    {
        $comment = NewsComment::findOrFail($id);
        $comment->reject();
        
        return redirect()->back()
            ->with("success", "�����Ѿܾ�");
    }
    
    /**
     * �����������
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
                ->with("error", "����ʧ�ܣ�" . $validator->errors()->first());
        }
        
        $action = $request->input("action");
        $ids = $request->input("ids");
        
        switch ($action) {
            case "approve":
                NewsComment::whereIn("id", $ids)->update(["status" => "approved"]);
                $message = "���������ͨ������";
                break;
            case "reject":
                NewsComment::whereIn("id", $ids)->update(["status" => "rejected"]);
                $message = "�������ܾ�����";
                break;
            case "delete":
                NewsComment::whereIn("id", $ids)->delete();
                $message = "������ɾ������";
                break;
        }
        
        return redirect()->back()
            ->with("success", $message);
    }
    
    /**
     * �ظ�����
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
        
        // �����ظ�
        $reply = NewsComment::create([
            "news_id" => $comment->news_id,
            "user_id" => auth()->id(),
            "parent_id" => $id,
            "content" => $request->input("content"),
            "status" => "approved", // ����Ա�ظ��Զ����ͨ��
            "ip_address" => $request->ip(),
            "user_agent" => $request->userAgent(),
            "is_anonymous" => false,
        ]);
        
        return redirect()->back()
            ->with("success", "�ظ��ɹ�");
    }
    
    /**
     * ɾ������
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $comment = NewsComment::findOrFail($id);
        
        // ɾ��������
        $comment->replies()->delete();
        
        // ɾ������
        $comment->delete();
        
        return redirect()->route("admin.news.comments.index")
            ->with("success", "����ɾ���ɹ�");
    }
}
