<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\User\MemoryService;
use App\Models\User\UserMemory;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class MemoryController extends Controller
{
    protected $memoryService;
    
    /**
     * 构造函数
     *
     * @param MemoryService $memoryService
     */
    public function __construct(MemoryService $memoryService)
    {
        $this->memoryService = $memoryService;
        $this->middleware("auth:admin");
    }
    
    /**
     * 显示用户记忆列表
     *
     * @param Request $request
     * @param int $userId
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        
        $filters = $request->only([
            "category",
            "type",
            "min_importance",
            "search",
            "sort_field",
            "sort_direction",
            "per_page",
        ]);
        
        $memories = $this->memoryService->getUserMemories($userId, $filters);
        $categories = $this->memoryService->getUserMemoryCategories($userId);
        
        return view("admin.users.memories.index", [
            "user" => $user,
            "memories" => $memories,
            "categories" => $categories,
            "filters" => $filters,
        ]);
    }
    
    /**
     * 显示创建记忆表单
     *
     * @param int $userId
     * @return \Illuminate\Http\Response
     */
    public function create($userId)
    {
        $user = User::findOrFail($userId);
        $categories = $this->memoryService->getUserMemoryCategories($userId);
        
        return view("admin.users.memories.create", [
            "user" => $user,
            "categories" => $categories,
        ]);
    }
    
    /**
     * 存储新记忆
     *
     * @param Request $request
     * @param int $userId
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $userId)
    {
        $validator = Validator::make($request->all(), [
            "key" => "required|string|max:255",
            "content" => "required|string",
            "type" => "required|in:text,json,embedding",
            "category" => "nullable|string|max:255",
            "importance" => "required|integer|min:1|max:10",
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $content = $request->input("content");
        
        // 如果类型是JSON，尝试解析
        if ($request->input("type") === "json") {
            try {
                $content = json_decode($content, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return redirect()->back()
                        ->withErrors(["content" => "无效的JSON格式"])
                        ->withInput();
                }
            } catch (\Exception $e) {
                return redirect()->back()
                    ->withErrors(["content" => "无效的JSON格式"])
                    ->withInput();
            }
        }
        
        $data = [
            "type" => $request->input("type"),
            "category" => $request->input("category"),
            "importance" => $request->input("importance"),
        ];
        
        $memory = $this->memoryService->remember(
            $userId,
            $request->input("key"),
            $content,
            $data
        );
        
        return redirect()->route("admin.users.memories.show", [$userId, $memory->id])
            ->with("success", "记忆创建成功");
    }
    
    /**
     * 显示记忆详情
     *
     * @param int $userId
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($userId, $id)
    {
        $user = User::findOrFail($userId);
        $memory = UserMemory::where("id", $id)
            ->where("user_id", $userId)
            ->firstOrFail();
            
        return view("admin.users.memories.show", [
            "user" => $user,
            "memory" => $memory,
        ]);
    }
    
    /**
     * 显示编辑记忆表单
     *
     * @param int $userId
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($userId, $id)
    {
        $user = User::findOrFail($userId);
        $memory = UserMemory::where("id", $id)
            ->where("user_id", $userId)
            ->firstOrFail();
            
        $categories = $this->memoryService->getUserMemoryCategories($userId);
        
        return view("admin.users.memories.edit", [
            "user" => $user,
            "memory" => $memory,
            "categories" => $categories,
        ]);
    }
    
    /**
     * 更新记忆
     *
     * @param Request $request
     * @param int $userId
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $userId, $id)
    {
        $validator = Validator::make($request->all(), [
            "key" => "required|string|max:255",
            "content" => "required|string",
            "type" => "required|in:text,json,embedding",
            "category" => "nullable|string|max:255",
            "importance" => "required|integer|min:1|max:10",
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $memory = UserMemory::where("id", $id)
            ->where("user_id", $userId)
            ->firstOrFail();
            
        $content = $request->input("content");
        
        // 如果类型是JSON，尝试解析
        if ($request->input("type") === "json") {
            try {
                $content = json_decode($content, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return redirect()->back()
                        ->withErrors(["content" => "无效的JSON格式"])
                        ->withInput();
                }
            } catch (\Exception $e) {
                return redirect()->back()
                    ->withErrors(["content" => "无效的JSON格式"])
                    ->withInput();
            }
        }
        
        $data = [
            "type" => $request->input("type"),
            "category" => $request->input("category"),
            "importance" => $request->input("importance"),
        ];
        
        $this->memoryService->remember(
            $userId,
            $request->input("key"),
            $content,
            $data
        );
        
        return redirect()->route("admin.users.memories.show", [$userId, $id])
            ->with("success", "记忆更新成功");
    }
    
    /**
     * 删除记忆
     *
     * @param int $userId
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($userId, $id)
    {
        $memory = UserMemory::where("id", $id)
            ->where("user_id", $userId)
            ->firstOrFail();
            
        $memory->delete();
        
        return redirect()->route("admin.users.memories.index", $userId)
            ->with("success", "记忆删除成功");
    }
}
