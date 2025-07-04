<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\User\ConversationService;
use App\Models\User\UserConversation;
use App\Models\User;
use App\Models\AI\Agent;
use Illuminate\Support\Facades\Validator;

class ConversationController extends Controller
{
    protected $conversationService;
    
    /**
     * 构造函数
     *
     * @param ConversationService $conversationService
     */
    public function __construct(ConversationService $conversationService)
    {
        $this->conversationService = $conversationService;
        $this->middleware("auth:admin");
    }
    
    /**
     * 显示用户对话列表
     *
     * @param Request $request
     * @param int $userId
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        
        $filters = $request->only([
            "agent_id",
            "model",
            "is_archived",
            "search",
            "sort_field",
            "sort_direction",
            "per_page",
        ]);
        
        $conversations = $this->conversationService->getUserConversations($userId, $filters);
        $stats = $this->conversationService->getConversationStats($userId);
        
        // 获取可用的智能体列表
        $agents = Agent::where("is_active", true)->get();
        
        return view("admin.users.conversations.index", [
            "user" => $user,
            "conversations" => $conversations,
            "agents" => $agents,
            "stats" => $stats,
            "filters" => $filters,
        ]);
    }
    
    /**
     * 显示对话详情
     *
     * @param int $userId
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($userId, $id)
    {
        $user = User::findOrFail($userId);
        $conversation = UserConversation::where("id", $id)
            ->where("user_id", $userId)
            ->firstOrFail();
            
        $messages = $conversation->messages()->orderBy("id")->get();
        
        return view("admin.users.conversations.show", [
            "user" => $user,
            "conversation" => $conversation,
            "messages" => $messages,
        ]);
    }
    
    /**
     * 显示编辑对话表单
     *
     * @param int $userId
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($userId, $id)
    {
        $user = User::findOrFail($userId);
        $conversation = UserConversation::where("id", $id)
            ->where("user_id", $userId)
            ->firstOrFail();
            
        // 获取可用的智能体列表
        $agents = Agent::where("is_active", true)->get();
        
        return view("admin.users.conversations.edit", [
            "user" => $user,
            "conversation" => $conversation,
            "agents" => $agents,
        ]);
    }
    
    /**
     * 更新对话
     *
     * @param Request $request
     * @param int $userId
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $userId, $id)
    {
        $validator = Validator::make($request->all(), [
            "title" => "required|string|max:255",
            "model" => "nullable|string|max:255",
            "agent_id" => "nullable|exists:agents,id",
            "system_prompt" => "nullable|string",
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $conversation = UserConversation::where("id", $id)
            ->where("user_id", $userId)
            ->firstOrFail();
            
        $data = $request->only([
            "title",
            "model",
            "agent_id",
        ]);
        
        // 处理系统提示词
        if ($request->has("system_prompt")) {
            $data["system_prompt"] = [
                "content" => $request->input("system_prompt"),
            ];
        }
        
        $this->conversationService->updateConversation($id, $data);
        
        return redirect()->route("admin.users.conversations.show", [$userId, $id])
            ->with("success", "对话更新成功");
    }
    
    /**
     * 删除对话
     *
     * @param int $userId
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($userId, $id)
    {
        $conversation = UserConversation::where("id", $id)
            ->where("user_id", $userId)
            ->firstOrFail();
            
        $this->conversationService->deleteConversation($id);
        
        return redirect()->route("admin.users.conversations.index", $userId)
            ->with("success", "对话删除成功");
    }
    
    /**
     * 清空对话消息
     *
     * @param int $userId
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function clear($userId, $id)
    {
        $conversation = UserConversation::where("id", $id)
            ->where("user_id", $userId)
            ->firstOrFail();
            
        $this->conversationService->clearConversation($id);
        
        return redirect()->route("admin.users.conversations.show", [$userId, $id])
            ->with("success", "对话消息已清空");
    }
    
    /**
     * 切换对话置顶状态
     *
     * @param int $userId
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function togglePin($userId, $id)
    {
        $conversation = UserConversation::where("id", $id)
            ->where("user_id", $userId)
            ->firstOrFail();
            
        $this->conversationService->togglePin($id);
        
        return redirect()->back()
            ->with("success", $conversation->is_pinned ? "对话已取消置顶" : "对话已置顶");
    }
    
    /**
     * 切换对话归档状态
     *
     * @param int $userId
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function toggleArchive($userId, $id)
    {
        $conversation = UserConversation::where("id", $id)
            ->where("user_id", $userId)
            ->firstOrFail();
            
        $this->conversationService->toggleArchive($id);
        
        return redirect()->back()
            ->with("success", $conversation->is_archived ? "对话已取消归档" : "对话已归档");
    }
    
    /**
     * 导出对话历史
     *
     * @param int $userId
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function export($userId, $id)
    {
        $conversation = UserConversation::where("id", $id)
            ->where("user_id", $userId)
            ->firstOrFail();
            
        $messages = $conversation->messages()->orderBy("id")->get();
        
        $filename = "conversation-" . $id . "-" . date("Y-m-d") . ".json";
        
        $data = [
            "id" => $conversation->id,
            "user_id" => $conversation->user_id,
            "title" => $conversation->title,
            "model" => $conversation->model,
            "created_at" => $conversation->created_at->toIso8601String(),
            "messages" => $messages->map(function ($message) {
                return [
                    "role" => $message->role,
                    "content" => $message->content,
                    "created_at" => $message->created_at->toIso8601String(),
                ];
            }),
        ];
        
        return response()->json($data)
            ->header("Content-Disposition", "attachment; filename={$filename}");
    }
}
