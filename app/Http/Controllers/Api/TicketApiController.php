<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Ticket\TicketService;
use App\Models\Ticket\Ticket;
use App\Models\Ticket\TicketReply;
use App\Models\Ticket\TicketAttachment;
use App\Models\Ticket\TicketDepartment;
use App\Models\Ticket\TicketCategory;
use App\Models\Ticket\TicketHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

/**
 * 工单系统API控制器
 * 
 * 处理工单相关的API请求
 */
class TicketApiController extends Controller
{
    /**
     * 工单服务
     *
     * @var TicketService
     */
    protected $ticketService;
    
    /**
     * 构造函数
     *
     * @param TicketService $ticketService
     */
    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
        $this->middleware("auth:api");
    }
    
    /**
     * 获取工单列表
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Ticket::query();
        
        // 如果不是管理员或支持人员，只能看到自己的工单
        if (!$user->hasRole(["admin", "support"])) {
            $query->where("user_id", $user->id);
        } else if ($request->has("assigned_to") && $request->input("assigned_to") == "me") {
            // 如果是管理员或支持人员，可以筛选分配给自己的工单
            $query->where("assigned_to", $user->id);
        }
        
        // 筛选条件
        if ($request->has("status")) {
            $query->where("status", $request->input("status"));
        }
        
        if ($request->has("priority")) {
            $query->where("priority", $request->input("priority"));
        }
        
        if ($request->has("department_id")) {
            $query->where("department_id", $request->input("department_id"));
        }
        
        if ($request->has("category_id")) {
            $query->where("category_id", $request->input("category_id"));
        }
        
        // 搜索
        if ($request->has("search")) {
            $search = $request->input("search");
            $query->where(function($q) use ($search) {
                $q->where("title", "like", "%{$search}%")
                  ->orWhere("content", "like", "%{$search}%");
            });
        }
        
        // 排序
        $sortField = $request->input("sort_field", "created_at");
        $sortDirection = $request->input("sort_direction", "desc");
        $query->orderBy($sortField, $sortDirection);
        
        // 分页
        $perPage = $request->input("per_page", 15);
        $tickets = $query->with(["user", "department", "category", "assignedUser"])->paginate($perPage);
        
        return response()->json([
            "status" => "success",
            "data" => $tickets
        ]);
    }
    
    /**
     * 创建工单
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // 验证请求
        $validator = Validator::make($request->all(), [
            "title" => "required|string|max:255",
            "content" => "required|string",
            "department_id" => "required|exists:ticket_departments,id",
            "category_id" => "required|exists:ticket_categories,id",
            "priority" => "required|in:low,medium,high,urgent",
            "attachments" => "nullable|array",
            "attachments.*" => "file|max:10240", // 最大10MB
            "is_public" => "nullable|boolean",
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                "status" => "error",
                "message" => "验证失败",
                "errors" => $validator->errors()
            ], 422);
        }
        
        // 创建工单
        try {
            $ticket = $this->ticketService->createTicket(
                $request->only(["title", "content", "department_id", "category_id", "priority", "is_public"]),
                Auth::id(),
                $request->file("attachments") ?: []
            );
            
            return response()->json([
                "status" => "success",
                "message" => "工单创建成功",
                "data" => $ticket->load(["user", "department", "category", "attachments"])
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                "status" => "error",
                "message" => "工单创建失败: " . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 获取工单详情
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $user = Auth::user();
        $ticket = Ticket::with([
            "user", 
            "department", 
            "category", 
            "assignedUser", 
            "replies" => function($query) use ($user) {
                // 如果不是管理员或支持人员，不显示内部回复
                if (!$user->hasRole(["admin", "support"])) {
                    $query->where("is_internal", false);
                }
                $query->orderBy("created_at", "asc");
            },
            "replies.user",
            "attachments",
            "history" => function($query) {
                $query->orderBy("created_at", "desc");
            },
            "history.user"
        ])->findOrFail($id);
        
        // 检查权限
        if (!$user->hasRole(["admin", "support"]) && $ticket->user_id != $user->id) {
            return response()->json([
                "status" => "error",
                "message" => "没有权限查看此工单"
            ], 403);
        }
        
        return response()->json([
            "status" => "success",
            "data" => $ticket
        ]);
    }
    
    /**
     * 更新工单
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $ticket = Ticket::findOrFail($id);
        
        // 检查权限
        if (!$user->hasRole(["admin", "support"]) && $ticket->user_id != $user->id) {
            return response()->json([
                "status" => "error",
                "message" => "没有权限更新此工单"
            ], 403);
        }
        
        // 验证请求
        $rules = [
            "title" => "sometimes|required|string|max:255",
            "content" => "sometimes|required|string",
            "priority" => "sometimes|required|in:low,medium,high,urgent",
        ];
        
        // 管理员和支持人员可以更新更多字段
        if ($user->hasRole(["admin", "support"])) {
            $rules = array_merge($rules, [
                "department_id" => "sometimes|required|exists:ticket_departments,id",
                "category_id" => "sometimes|required|exists:ticket_categories,id",
                "status" => "sometimes|required|in:open,pending,processing,resolved,closed",
                "assigned_to" => "sometimes|nullable|exists:users,id",
                "is_public" => "sometimes|boolean",
            ]);
        }
        
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            return response()->json([
                "status" => "error",
                "message" => "验证失败",
                "errors" => $validator->errors()
            ], 422);
        }
        
        // 更新工单
        try {
            $ticket = $this->ticketService->updateTicket(
                $id,
                $request->only(array_keys($rules)),
                Auth::id()
            );
            
            return response()->json([
                "status" => "success",
                "message" => "工单更新成功",
                "data" => $ticket->load(["user", "department", "category", "assignedUser"])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "status" => "error",
                "message" => "工单更新失败: " . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 删除工单（仅管理员）
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $ticket = Ticket::findOrFail($id);
        
        try {
            // 删除附件
            foreach ($ticket->attachments as $attachment) {
                Storage::disk("public")->delete($attachment->file_path);
            }
            
            // 删除工单
            $ticket->delete();
            
            return response()->json([
                "status" => "success",
                "message" => "工单删除成功"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "status" => "error",
                "message" => "工单删除失败: " . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 回复工单
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function reply(Request $request, $id)
    {
        $user = Auth::user();
        $ticket = Ticket::findOrFail($id);
        
        // 检查权限
        if (!$user->hasRole(["admin", "support"]) && $ticket->user_id != $user->id) {
            return response()->json([
                "status" => "error",
                "message" => "没有权限回复此工单"
            ], 403);
        }
        
        // 验证请求
        $validator = Validator::make($request->all(), [
            "content" => "required|string",
            "is_internal" => "nullable|boolean",
            "attachments" => "nullable|array",
            "attachments.*" => "file|max:10240", // 最大10MB
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                "status" => "error",
                "message" => "验证失败",
                "errors" => $validator->errors()
            ], 422);
        }
        
        // 非管理员和支持人员不能发送内部回复
        $isInternal = $request->input("is_internal", false);
        if ($isInternal && !$user->hasRole(["admin", "support"])) {
            $isInternal = false;
        }
        
        // 添加回复
        try {
            $reply = $this->ticketService->replyTicket(
                $id,
                $request->input("content"),
                Auth::id(),
                $isInternal,
                $request->file("attachments") ?: []
            );
            
            return response()->json([
                "status" => "success",
                "message" => "回复添加成功",
                "data" => $reply->load("user")
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                "status" => "error",
                "message" => "回复添加失败: " . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 获取工单回复
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getReplies(Request $request, $id)
    {
        $user = Auth::user();
        $ticket = Ticket::findOrFail($id);
        
        // 检查权限
        if (!$user->hasRole(["admin", "support"]) && $ticket->user_id != $user->id) {
            return response()->json([
                "status" => "error",
                "message" => "没有权限查看此工单的回复"
            ], 403);
        }
        
        $query = TicketReply::where("ticket_id", $id);
        
        // 如果不是管理员或支持人员，不显示内部回复
        if (!$user->hasRole(["admin", "support"])) {
            $query->where("is_internal", false);
        }
        
        $replies = $query->with("user")->orderBy("created_at", "asc")->get();
        
        return response()->json([
            "status" => "success",
            "data" => $replies
        ]);
    }
    
    /**
     * 分配工单
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function assign(Request $request, $id)
    {
        // 验证请求
        $validator = Validator::make($request->all(), [
            "assigned_to" => "required|exists:users,id",
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                "status" => "error",
                "message" => "验证失败",
                "errors" => $validator->errors()
            ], 422);
        }
        
        // 分配工单
        try {
            $ticket = $this->ticketService->assignTicket(
                $id,
                $request->input("assigned_to"),
                Auth::id()
            );
            
            return response()->json([
                "status" => "success",
                "message" => "工单分配成功",
                "data" => $ticket->load("assignedUser")
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "status" => "error",
                "message" => "工单分配失败: " . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 关闭工单
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function close(Request $request, $id)
    {
        $user = Auth::user();
        $ticket = Ticket::findOrFail($id);
        
        // 检查权限
        if (!$user->hasRole(["admin", "support"]) && $ticket->user_id != $user->id) {
            return response()->json([
                "status" => "error",
                "message" => "没有权限关闭此工单"
            ], 403);
        }
        
        // 验证请求
        $validator = Validator::make($request->all(), [
            "resolution" => "nullable|string",
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                "status" => "error",
                "message" => "验证失败",
                "errors" => $validator->errors()
            ], 422);
        }
        
        // 关闭工单
        try {
            $ticket = $this->ticketService->closeTicket(
                $id,
                Auth::id(),
                $request->input("resolution")
            );
            
            return response()->json([
                "status" => "success",
                "message" => "工单关闭成功",
                "data" => $ticket
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "status" => "error",
                "message" => "工单关闭失败: " . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 重新打开工单
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function reopen($id)
    {
        $user = Auth::user();
        $ticket = Ticket::findOrFail($id);
        
        // 检查权限
        if (!$user->hasRole(["admin", "support"]) && $ticket->user_id != $user->id) {
            return response()->json([
                "status" => "error",
                "message" => "没有权限重新打开此工单"
            ], 403);
        }
        
        // 重新打开工单
        try {
            $ticket = $this->ticketService->reopenTicket($id, Auth::id());
            
            return response()->json([
                "status" => "success",
                "message" => "工单已重新打开",
                "data" => $ticket
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "status" => "error",
                "message" => "重新打开工单失败: " . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 上传附件
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadAttachment(Request $request, $id)
    {
        $user = Auth::user();
        $ticket = Ticket::findOrFail($id);
        
        // 检查权限
        if (!$user->hasRole(["admin", "support"]) && $ticket->user_id != $user->id) {
            return response()->json([
                "status" => "error",
                "message" => "没有权限为此工单上传附件"
            ], 403);
        }
        
        // 验证请求
        $validator = Validator::make($request->all(), [
            "file" => "required|file|max:10240", // 最大10MB
            "reply_id" => "nullable|exists:ticket_replies,id",
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                "status" => "error",
                "message" => "验证失败",
                "errors" => $validator->errors()
            ], 422);
        }
        
        // 上传附件
        try {
            $attachment = $this->ticketService->addAttachment(
                $id,
                $request->input("reply_id"),
                Auth::id(),
                $request->file("file")
            );
            
            return response()->json([
                "status" => "success",
                "message" => "附件上传成功",
                "data" => $attachment
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                "status" => "error",
                "message" => "附件上传失败: " . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 删除附件
     *
     * @param int $id
     * @param int $attachmentId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteAttachment($id, $attachmentId)
    {
        $user = Auth::user();
        $ticket = Ticket::findOrFail($id);
        $attachment = TicketAttachment::where("ticket_id", $id)->where("id", $attachmentId)->firstOrFail();
        
        // 检查权限
        if (!$user->hasRole(["admin", "support"]) && $ticket->user_id != $user->id) {
            return response()->json([
                "status" => "error",
                "message" => "没有权限删除此附件"
            ], 403);
        }
        
        // 删除附件
        try {
            $this->ticketService->deleteAttachment($attachmentId, Auth::id());
            
            return response()->json([
                "status" => "success",
                "message" => "附件删除成功"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "status" => "error",
                "message" => "附件删除失败: " . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 获取附件列表
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAttachments($id)
    {
        $user = Auth::user();
        $ticket = Ticket::findOrFail($id);
        
        // 检查权限
        if (!$user->hasRole(["admin", "support"]) && $ticket->user_id != $user->id) {
            return response()->json([
                "status" => "error",
                "message" => "没有权限查看此工单的附件"
            ], 403);
        }
        
        $attachments = TicketAttachment::where("ticket_id", $id)->get();
        
        return response()->json([
            "status" => "success",
            "data" => $attachments
        ]);
    }
    
    /**
     * 获取工单统计信息
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatistics()
    {
        $user = Auth::user();
        
        // 获取统计信息
        $stats = $this->ticketService->getTicketStats($user->hasRole(["admin", "support"]) ? null : $user->id);
        
        return response()->json([
            "status" => "success",
            "data" => $stats
        ]);
    }
    
    /**
     * 获取部门列表
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDepartments()
    {
        $departments = TicketDepartment::orderBy("name")->get();
        
        return response()->json([
            "status" => "success",
            "data" => $departments
        ]);
    }
    
    /**
     * 创建部门
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createDepartment(Request $request)
    {
        // 验证请求
        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:255|unique:ticket_departments,name",
            "description" => "nullable|string",
            "is_active" => "nullable|boolean",
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                "status" => "error",
                "message" => "验证失败",
                "errors" => $validator->errors()
            ], 422);
        }
        
        // 创建部门
        $department = TicketDepartment::create([
            "name" => $request->input("name"),
            "description" => $request->input("description"),
            "is_active" => $request->input("is_active", true),
        ]);
        
        return response()->json([
            "status" => "success",
            "message" => "部门创建成功",
            "data" => $department
        ], 201);
    }
    
    /**
     * 获取部门详情
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function showDepartment($id)
    {
        $department = TicketDepartment::findOrFail($id);
        
        return response()->json([
            "status" => "success",
            "data" => $department
        ]);
    }
    
    /**
     * 更新部门
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateDepartment(Request $request, $id)
    {
        $department = TicketDepartment::findOrFail($id);
        
        // 验证请求
        $validator = Validator::make($request->all(), [
            "name" => "sometimes|required|string|max:255|unique:ticket_departments,name," . $id,
            "description" => "nullable|string",
            "is_active" => "nullable|boolean",
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                "status" => "error",
                "message" => "验证失败",
                "errors" => $validator->errors()
            ], 422);
        }
        
        // 更新部门
        $department->update($request->only(["name", "description", "is_active"]));
        
        return response()->json([
            "status" => "success",
            "message" => "部门更新成功",
            "data" => $department
        ]);
    }
    
    /**
     * 删除部门
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteDepartment($id)
    {
        $department = TicketDepartment::findOrFail($id);
        
        // 检查是否有关联的工单
        $hasTickets = Ticket::where("department_id", $id)->exists();
        if ($hasTickets) {
            return response()->json([
                "status" => "error",
                "message" => "无法删除部门，存在关联的工单"
            ], 400);
        }
        
        // 检查是否有关联的分类
        $hasCategories = TicketCategory::where("department_id", $id)->exists();
        if ($hasCategories) {
            return response()->json([
                "status" => "error",
                "message" => "无法删除部门，存在关联的分类"
            ], 400);
        }
        
        $department->delete();
        
        return response()->json([
            "status" => "success",
            "message" => "部门删除成功"
        ]);
    }
    
    /**
     * 获取分类列表
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCategories()
    {
        $categories = TicketCategory::with("department")->orderBy("name")->get();
        
        return response()->json([
            "status" => "success",
            "data" => $categories
        ]);
    }
    
    /**
     * 创建分类
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createCategory(Request $request)
    {
        // 验证请求
        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:255",
            "department_id" => "required|exists:ticket_departments,id",
            "description" => "nullable|string",
            "is_active" => "nullable|boolean",
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                "status" => "error",
                "message" => "验证失败",
                "errors" => $validator->errors()
            ], 422);
        }
        
        // 检查同一部门下是否有同名分类
        $exists = TicketCategory::where("name", $request->input("name"))
            ->where("department_id", $request->input("department_id"))
            ->exists();
            
        if ($exists) {
            return response()->json([
                "status" => "error",
                "message" => "同一部门下已存在同名分类"
            ], 422);
        }
        
        // 创建分类
        $category = TicketCategory::create([
            "name" => $request->input("name"),
            "department_id" => $request->input("department_id"),
            "description" => $request->input("description"),
            "is_active" => $request->input("is_active", true),
        ]);
        
        return response()->json([
            "status" => "success",
            "message" => "分类创建成功",
            "data" => $category
        ], 201);
    }
    
    /**
     * 获取分类详情
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function showCategory($id)
    {
        $category = TicketCategory::with("department")->findOrFail($id);
        
        return response()->json([
            "status" => "success",
            "data" => $category
        ]);
    }
    
    /**
     * 更新分类
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCategory(Request $request, $id)
    {
        $category = TicketCategory::findOrFail($id);
        
        // 验证请求
        $validator = Validator::make($request->all(), [
            "name" => "sometimes|required|string|max:255",
            "department_id" => "sometimes|required|exists:ticket_departments,id",
            "description" => "nullable|string",
            "is_active" => "nullable|boolean",
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                "status" => "error",
                "message" => "验证失败",
                "errors" => $validator->errors()
            ], 422);
        }
        
        // 检查同一部门下是否有同名分类
        if ($request->has("name") || $request->has("department_id")) {
            $departmentId = $request->input("department_id", $category->department_id);
            $name = $request->input("name", $category->name);
            
            $exists = TicketCategory::where("name", $name)
                ->where("department_id", $departmentId)
                ->where("id", "!=", $id)
                ->exists();
                
            if ($exists) {
                return response()->json([
                    "status" => "error",
                    "message" => "同一部门下已存在同名分类"
                ], 422);
            }
        }
        
        // 更新分类
        $category->update($request->only(["name", "department_id", "description", "is_active"]));
        
        return response()->json([
            "status" => "success",
            "message" => "分类更新成功",
            "data" => $category->load("department")
        ]);
    }
    
    /**
     * 删除分类
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteCategory($id)
    {
        $category = TicketCategory::findOrFail($id);
        
        // 检查是否有关联的工单
        $hasTickets = Ticket::where("category_id", $id)->exists();
        if ($hasTickets) {
            return response()->json([
                "status" => "error",
                "message" => "无法删除分类，存在关联的工单"
            ], 400);
        }
        
        $category->delete();
        
        return response()->json([
            "status" => "success",
            "message" => "分类删除成功"
        ]);
    }
    
    /**
     * 根据部门获取分类
     *
     * @param int $departmentId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCategoriesByDepartment($departmentId)
    {
        $categories = TicketCategory::where("department_id", $departmentId)
            ->where("is_active", true)
            ->orderBy("name")
            ->get();
        
        return response()->json([
            "status" => "success",
            "data" => $categories
        ]);
    }
}
