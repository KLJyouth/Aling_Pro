<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User\UserConversation;
use App\Models\User\ConversationMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ConversationController extends Controller
{
    /**
     * 显示用户对话历史列表
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $conversations = UserConversation::where("user_id", $user->id)
            ->orderBy("updated_at", "desc")
            ->paginate(10);
        
        return view("user.conversations.index", compact("conversations"));
    }
    
    /**
     * 显示特定对话的详情
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = Auth::user();
        
        $conversation = UserConversation::where("user_id", $user->id)
            ->findOrFail($id);
        
        $messages = ConversationMessage::where("conversation_id", $conversation->id)
            ->orderBy("created_at", "asc")
            ->get();
        
        return view("user.conversations.show", compact("conversation", "messages"));
    }
    
    /**
     * 删除对话
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete(Request $request, $id)
    {
        $user = Auth::user();
        
        $conversation = UserConversation::where("user_id", $user->id)
            ->findOrFail($id);
        
        // 删除对话及其消息
        ConversationMessage::where("conversation_id", $conversation->id)->delete();
        $conversation->delete();
        
        return redirect()->route("user.conversations.index")
            ->with("success", "对话已成功删除");
    }

    
    /**
     * 导出对话记录
     *
     * @param Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(Request $request, $id)
    {
        $user = Auth::user();
        
        $conversation = UserConversation::where("user_id", $user->id)
            ->findOrFail($id);
        
        $messages = ConversationMessage::where("conversation_id", $conversation->id)
            ->orderBy("created_at", "asc")
            ->get();
        
        $format = $request->input("format", "txt");
        
        switch ($format) {
            case "json":
                return $this->exportAsJson($conversation, $messages);
            case "markdown":
                return $this->exportAsMarkdown($conversation, $messages);
            case "html":
                return $this->exportAsHtml($conversation, $messages);
            case "txt":
            default:
                return $this->exportAsTxt($conversation, $messages);
        }
    }
    
    /**
     * 将对话导出为TXT格式
     *
     * @param UserConversation $conversation
     * @param \Illuminate\Support\Collection $messages
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    protected function exportAsTxt($conversation, $messages)
    {
        $content = "对话标题: " . $conversation->title . "\n";
        $content .= "创建时间: " . $conversation->created_at . "\n";
        $content .= "更新时间: " . $conversation->updated_at . "\n";
        $content .= "----------------------------------------\n\n";
        
        foreach ($messages as $message) {
            $role = $message->role === "user" ? "用户" : "AI助手";
            $content .= "[" . $role . "] " . $message->created_at . "\n";
            $content .= $message->content . "\n\n";
        }
        
        $filename = "conversation_" . $conversation->id . "_" . date("Ymd_His") . ".txt";
        $tempPath = storage_path("app/temp/" . $filename);
        
        // 确保目录存在
        if (!file_exists(storage_path("app/temp"))) {
            mkdir(storage_path("app/temp"), 0755, true);
        }
        
        file_put_contents($tempPath, $content);
        
        return response()->download($tempPath, $filename, [
            "Content-Type" => "text/plain",
        ])->deleteFileAfterSend(true);
    }
    
    /**
     * 将对话导出为JSON格式
     *
     * @param UserConversation $conversation
     * @param \Illuminate\Support\Collection $messages
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    protected function exportAsJson($conversation, $messages)
    {
        $data = [
            "conversation" => [
                "id" => $conversation->id,
                "title" => $conversation->title,
                "created_at" => $conversation->created_at,
                "updated_at" => $conversation->updated_at,
            ],
            "messages" => []
        ];
        
        foreach ($messages as $message) {
            $data["messages"][] = [
                "role" => $message->role,
                "content" => $message->content,
                "created_at" => $message->created_at,
            ];
        }
        
        $filename = "conversation_" . $conversation->id . "_" . date("Ymd_His") . ".json";
        $tempPath = storage_path("app/temp/" . $filename);
        
        // 确保目录存在
        if (!file_exists(storage_path("app/temp"))) {
            mkdir(storage_path("app/temp"), 0755, true);
        }
        
        file_put_contents($tempPath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        return response()->download($tempPath, $filename, [
            "Content-Type" => "application/json",
        ])->deleteFileAfterSend(true);
    }

    
    /**
     * 将对话导出为Markdown格式
     *
     * @param UserConversation $conversation
     * @param \Illuminate\Support\Collection $messages
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    protected function exportAsMarkdown($conversation, $messages)
    {
        $content = "# " . $conversation->title . "\n\n";
        $content .= "- 创建时间: " . $conversation->created_at . "\n";
        $content .= "- 更新时间: " . $conversation->updated_at . "\n\n";
        $content .= "---\n\n";
        
        foreach ($messages as $message) {
            $role = $message->role === "user" ? "用户" : "AI助手";
            $content .= "### " . $role . " (" . $message->created_at . ")\n\n";
            $content .= $message->content . "\n\n";
            $content .= "---\n\n";
        }
        
        $filename = "conversation_" . $conversation->id . "_" . date("Ymd_His") . ".md";
        $tempPath = storage_path("app/temp/" . $filename);
        
        // 确保目录存在
        if (!file_exists(storage_path("app/temp"))) {
            mkdir(storage_path("app/temp"), 0755, true);
        }
        
        file_put_contents($tempPath, $content);
        
        return response()->download($tempPath, $filename, [
            "Content-Type" => "text/markdown",
        ])->deleteFileAfterSend(true);
    }
    
    /**
     * 将对话导出为HTML格式
     *
     * @param UserConversation $conversation
     * @param \Illuminate\Support\Collection $messages
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    protected function exportAsHtml($conversation, $messages)
    {
        $content = "<!DOCTYPE html>\n";
        $content .= "<html lang=\"zh-CN\">\n";
        $content .= "<head>\n";
        $content .= "    <meta charset=\"UTF-8\">\n";
        $content .= "    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n";
        $content .= "    <title>" . htmlspecialchars($conversation->title) . " - 对话记录</title>\n";
        $content .= "    <style>\n";
        $content .= "        body { font-family: Arial, sans-serif; line-height: 1.6; max-width: 800px; margin: 0 auto; padding: 20px; }\n";
        $content .= "        h1 { color: #333; }\n";
        $content .= "        .meta { color: #666; margin-bottom: 20px; }\n";
        $content .= "        .message { margin-bottom: 20px; padding: 15px; border-radius: 5px; }\n";
        $content .= "        .user { background-color: #f0f0f0; }\n";
        $content .= "        .assistant { background-color: #e6f7ff; }\n";
        $content .= "        .message-header { font-weight: bold; margin-bottom: 5px; }\n";
        $content .= "        .message-time { font-size: 0.8em; color: #666; }\n";
        $content .= "        .message-content { white-space: pre-wrap; }\n";
        $content .= "    </style>\n";
        $content .= "</head>\n";
        $content .= "<body>\n";
        $content .= "    <h1>" . htmlspecialchars($conversation->title) . "</h1>\n";
        $content .= "    <div class=\"meta\">\n";
        $content .= "        <div>创建时间: " . $conversation->created_at . "</div>\n";
        $content .= "        <div>更新时间: " . $conversation->updated_at . "</div>\n";
        $content .= "    </div>\n";
        
        foreach ($messages as $message) {
            $role = $message->role === "user" ? "用户" : "AI助手";
            $cssClass = $message->role === "user" ? "user" : "assistant";
            
            $content .= "    <div class=\"message " . $cssClass . "\">\n";
            $content .= "        <div class=\"message-header\">" . $role . " <span class=\"message-time\">" . $message->created_at . "</span></div>\n";
            $content .= "        <div class=\"message-content\">" . nl2br(htmlspecialchars($message->content)) . "</div>\n";
            $content .= "    </div>\n";
        }
        
        $content .= "</body>\n";
        $content .= "</html>";
        
        $filename = "conversation_" . $conversation->id . "_" . date("Ymd_His") . ".html";
        $tempPath = storage_path("app/temp/" . $filename);
        
        // 确保目录存在
        if (!file_exists(storage_path("app/temp"))) {
            mkdir(storage_path("app/temp"), 0755, true);
        }
        
        file_put_contents($tempPath, $content);
        
        return response()->download($tempPath, $filename, [
            "Content-Type" => "text/html",
        ])->deleteFileAfterSend(true);
    }
}
