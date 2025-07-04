<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User\UserMemory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MemoryController extends Controller
{
    /**
     * 显示用户记忆列表
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = UserMemory::where("user_id", $user->id);
        
        // 过滤分类
        if ($request->has("category")) {
            $query->where("category", $request->input("category"));
        }
        
        // 过滤类型
        if ($request->has("type")) {
            $query->where("type", $request->input("type"));
        }
        
        // 过滤重要性
        if ($request->has("importance")) {
            $query->where("importance", ">=", $request->input("importance"));
        }
        
        // 排序
        $sortBy = $request->input("sort", "updated_at");
        $sortDir = $request->input("dir", "desc");
        
        $allowedSortFields = ["key", "category", "importance", "last_accessed_at", "access_count", "created_at", "updated_at"];
        
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->orderBy("updated_at", "desc");
        }
        
        $memories = $query->paginate(20);
        
        // 获取所有分类
        $categories = UserMemory::where("user_id", $user->id)
            ->select("category")
            ->distinct()
            ->pluck("category")
            ->filter()
            ->values();
        
        return view("user.memories.index", compact("memories", "categories"));
    }
    
    /**
     * 显示特定记忆的详情
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = Auth::user();
        
        $memory = UserMemory::where("user_id", $user->id)
            ->findOrFail($id);
        
        // 记录访问
        $memory->recordAccess();
        
        return view("user.memories.show", compact("memory"));
    }

    
    /**
     * 显示创建记忆的表单
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = Auth::user();
        
        // 获取所有分类
        $categories = UserMemory::where("user_id", $user->id)
            ->select("category")
            ->distinct()
            ->pluck("category")
            ->filter()
            ->values();
        
        return view("user.memories.create", compact("categories"));
    }
    
    /**
     * 存储新记忆
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // 验证输入
        $validator = Validator::make($request->all(), [
            "key" => "required|string|max:100",
            "content" => "required|string|max:10000",
            "type" => "required|in:text,json,embedding",
            "category" => "nullable|string|max:50",
            "importance" => "required|integer|min:1|max:10",
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // 检查键是否已存在
        $existingMemory = UserMemory::where("user_id", $user->id)
            ->where("key", $request->input("key"))
            ->first();
        
        if ($existingMemory) {
            return redirect()->back()
                ->withErrors(["key" => "记忆键已存在，请使用其他键"])
                ->withInput();
        }
        
        // 创建新记忆
        $memory = new UserMemory();
        $memory->user_id = $user->id;
        $memory->key = $request->input("key");
        $memory->content = $request->input("content");
        $memory->type = $request->input("type");
        $memory->category = $request->input("category");
        $memory->importance = $request->input("importance");
        $memory->metadata = [
            "source" => "user_created",
            "created_from" => "web_interface"
        ];
        $memory->save();
        
        return redirect()->route("user.memories.show", $memory->id)
            ->with("success", "记忆已成功创建");
    }
    
    /**
     * 显示编辑记忆的表单
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = Auth::user();
        
        $memory = UserMemory::where("user_id", $user->id)
            ->findOrFail($id);
        
        // 获取所有分类
        $categories = UserMemory::where("user_id", $user->id)
            ->select("category")
            ->distinct()
            ->pluck("category")
            ->filter()
            ->values();
        
        return view("user.memories.edit", compact("memory", "categories"));
    }
    
    /**
     * 更新记忆
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        
        $memory = UserMemory::where("user_id", $user->id)
            ->findOrFail($id);
        
        // 验证输入
        $validator = Validator::make($request->all(), [
            "key" => "required|string|max:100",
            "content" => "required|string|max:10000",
            "type" => "required|in:text,json,embedding",
            "category" => "nullable|string|max:50",
            "importance" => "required|integer|min:1|max:10",
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // 检查键是否已存在（排除当前记忆）
        $existingMemory = UserMemory::where("user_id", $user->id)
            ->where("key", $request->input("key"))
            ->where("id", "!=", $id)
            ->first();
        
        if ($existingMemory) {
            return redirect()->back()
                ->withErrors(["key" => "记忆键已存在，请使用其他键"])
                ->withInput();
        }
        
        // 更新记忆
        $memory->key = $request->input("key");
        $memory->content = $request->input("content");
        $memory->type = $request->input("type");
        $memory->category = $request->input("category");
        $memory->importance = $request->input("importance");
        
        // 添加更新元数据
        $metadata = $memory->metadata ?? [];
        $metadata["last_updated_by"] = "user";
        $metadata["last_updated_from"] = "web_interface";
        $memory->metadata = $metadata;
        
        $memory->save();
        
        return redirect()->route("user.memories.show", $memory->id)
            ->with("success", "记忆已成功更新");
    }

    
    /**
     * 删除记忆
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $user = Auth::user();
        
        $memory = UserMemory::where("user_id", $user->id)
            ->findOrFail($id);
        
        $memory->delete();
        
        return redirect()->route("user.memories.index")
            ->with("success", "记忆已成功删除");
    }
    
    /**
     * 导出所有记忆
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        
        $query = UserMemory::where("user_id", $user->id);
        
        // 过滤分类
        if ($request->has("category")) {
            $query->where("category", $request->input("category"));
        }
        
        // 过滤类型
        if ($request->has("type")) {
            $query->where("type", $request->input("type"));
        }
        
        // 过滤重要性
        if ($request->has("importance")) {
            $query->where("importance", ">=", $request->input("importance"));
        }
        
        $memories = $query->get();
        
        $format = $request->input("format", "json");
        
        switch ($format) {
            case "csv":
                return $this->exportAsCsv($memories);
            case "json":
            default:
                return $this->exportAsJson($memories);
        }
    }
    
    /**
     * 将记忆导出为JSON格式
     *
     * @param \Illuminate\Support\Collection $memories
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    protected function exportAsJson($memories)
    {
        $data = [];
        
        foreach ($memories as $memory) {
            $data[] = [
                "key" => $memory->key,
                "content" => $memory->getContent(),
                "type" => $memory->type,
                "category" => $memory->category,
                "importance" => $memory->importance,
                "created_at" => $memory->created_at,
                "updated_at" => $memory->updated_at,
                "last_accessed_at" => $memory->last_accessed_at,
                "access_count" => $memory->access_count,
                "metadata" => $memory->metadata,
            ];
        }
        
        $filename = "memories_" . date("Ymd_His") . ".json";
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
     * 将记忆导出为CSV格式
     *
     * @param \Illuminate\Support\Collection $memories
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    protected function exportAsCsv($memories)
    {
        $filename = "memories_" . date("Ymd_His") . ".csv";
        $tempPath = storage_path("app/temp/" . $filename);
        
        // 确保目录存在
        if (!file_exists(storage_path("app/temp"))) {
            mkdir(storage_path("app/temp"), 0755, true);
        }
        
        $file = fopen($tempPath, "w");
        
        // 添加BOM以支持中文
        fputs($file, "\xEF\xBB\xBF");
        
        // 写入标题行
        fputcsv($file, [
            "键",
            "内容",
            "类型",
            "分类",
            "重要性",
            "创建时间",
            "更新时间",
            "最后访问时间",
            "访问次数"
        ]);
        
        // 写入数据行
        foreach ($memories as $memory) {
            $content = $memory->type === "json" ? json_encode($memory->getContent(), JSON_UNESCAPED_UNICODE) : $memory->content;
            
            fputcsv($file, [
                $memory->key,
                $content,
                $memory->type,
                $memory->category,
                $memory->importance,
                $memory->created_at,
                $memory->updated_at,
                $memory->last_accessed_at,
                $memory->access_count
            ]);
        }
        
        fclose($file);
        
        return response()->download($tempPath, $filename, [
            "Content-Type" => "text/csv",
        ])->deleteFileAfterSend(true);
    }
}
