<?php

namespace App\Http\Controllers\Ticket;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Ticket\TicketService;
use App\Models\Ticket\Ticket;
use App\Models\Ticket\TicketDepartment;
use App\Models\Ticket\TicketCategory;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * 工单控制器
 * 
 * 处理工单相关的请求
 */
class TicketController extends Controller
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
        $this->middleware("auth");
    }
    
    /**
     * 显示工单列表
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // 获取查询参数
        $status = $request->input("status");
        $priority = $request->input("priority");
        $department = $request->input("department");
        $category = $request->input("category");
        $search = $request->input("search");
        $assignedTo = $request->input("assigned_to");
        $perPage = $request->input("per_page", 15);
        
        // 构建查询
        $query = Ticket::with(["user", "department", "category", "assignedUser"]);
        
        // 根据用户角色过滤
        $user = Auth::user();
        if (!$user->hasRole(["admin", "support"])) {
            // 普通用户只能看到自己的工单
            $query->where("user_id", $user->id);
        } elseif ($user->hasRole("support") && !$user->hasPermissionTo("view all tickets")) {
            // 客服人员只能看到分配给自己的工单或未分配的工单
            $query->where(function($q) use ($user) {
                $q->where("assigned_to", $user->id)
                  ->orWhereNull("assigned_to");
            });
        }
        
        // 应用过滤条件
        if ($status) {
            $query->where("status", $status);
        }
        
        if ($priority) {
            $query->where("priority", $priority);
        }
        
        if ($department) {
            $query->where("department_id", $department);
        }
        
        if ($category) {
            $query->where("category_id", $category);
        }
        
        if ($assignedTo) {
            if ($assignedTo == "unassigned") {
                $query->whereNull("assigned_to");
            } else {
                $query->where("assigned_to", $assignedTo);
            }
        }
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where("title", "like", "%" . $search . "%")
                  ->orWhere("content", "like", "%" . $search . "%")
                  ->orWhereHas("user", function($q) use ($search) {
                      $q->where("name", "like", "%" . $search . "%")
                        ->orWhere("email", "like", "%" . $search . "%");
                  });
            });
        }
        
        // 排序
        $query->orderBy("updated_at", "desc");
        
        // 分页
        $tickets = $query->paginate($perPage);
        
        // 获取部门和分类列表
        $departments = TicketDepartment::active()->sorted()->get();
        $categories = TicketCategory::active()->sorted()->get();
        
        // 获取客服人员列表
        $staff = User::role(["admin", "support"])->get();
        
        // 获取统计信息
        $stats = $this->ticketService->getTicketStats();
        
        return view("ticket.index", compact(
            "tickets", 
            "departments", 
            "categories", 
            "staff", 
            "stats",
            "status",
            "priority",
            "department",
            "category",
            "search",
            "assignedTo"
        ));
    }
    
    /**
     * 显示创建工单表单
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $departments = TicketDepartment::active()->sorted()->get();
        $categories = TicketCategory::active()->sorted()->get();
        
        return view("ticket.create", compact("departments", "categories"));
    }
    
    /**
     * 存储新工单
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // 验证请求
        $validator = Validator::make($request->all(), [
            "title" => "required|string|max:255",
            "content" => "required|string",
            "department_id" => "nullable|exists:ticket_departments,id",
            "category_id" => "nullable|exists:ticket_categories,id",
            "priority" => "required|in:low,medium,high,urgent",
            "attachments.*" => "nullable|file|max:10240", // 最大10MB
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // 创建工单
        try {
            $ticket = $this->ticketService->createTicket(
                $request->all(),
                Auth::id(),
                $request->file("attachments") ?? []
            );
            
            return redirect()->route("tickets.show", $ticket->id)
                ->with("success", "工单创建成功");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with("error", "工单创建失败: " . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * 显示工单详情
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $ticket = Ticket::with([
            "user", 
            "department", 
            "category", 
            "assignedUser", 
            "closedByUser",
            "replies" => function($query) {
                $query->orderBy("created_at", "asc");
            },
            "replies.user",
            "replies.attachments",
            "attachments",
            "history" => function($query) {
                $query->orderBy("created_at", "desc");
            },
            "history.user"
        ])->findOrFail($id);
        
        // 检查权限
        $user = Auth::user();
        if (!$user->hasRole(["admin", "support"]) && $user->id != $ticket->user_id) {
            abort(403, "您无权查看此工单");
        }
        
        // 获取部门和分类列表
        $departments = TicketDepartment::active()->sorted()->get();
        $categories = TicketCategory::active()->sorted()->get();
        
        // 获取客服人员列表
        $staff = User::role(["admin", "support"])->get();
        
        return view("ticket.show", compact("ticket", "departments", "categories", "staff"));
    }
    
    /**
     * 更新工单
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        // 验证请求
        $validator = Validator::make($request->all(), [
            "title" => "sometimes|required|string|max:255",
            "content" => "sometimes|required|string",
            "department_id" => "nullable|exists:ticket_departments,id",
            "category_id" => "nullable|exists:ticket_categories,id",
            "priority" => "sometimes|required|in:low,medium,high,urgent",
            "status" => "sometimes|required|in:open,pending,processing,resolved,closed",
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // 更新工单
        try {
            $this->ticketService->updateTicket(
                $id,
                $request->all(),
                Auth::id()
            );
            
            return redirect()->route("tickets.show", $id)
                ->with("success", "工单更新成功");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with("error", "工单更新失败: " . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * 回复工单
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reply(Request $request, $id)
    {
        // 验证请求
        $validator = Validator::make($request->all(), [
            "content" => "required|string",
            "is_internal" => "sometimes|boolean",
            "attachments.*" => "nullable|file|max:10240", // 最大10MB
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // 回复工单
        try {
            $isInternal = $request->has("is_internal") && $request->input("is_internal") && Auth::user()->hasRole(["admin", "support"]);
            
            $this->ticketService->replyTicket(
                $id,
                $request->input("content"),
                Auth::id(),
                $isInternal,
                $request->file("attachments") ?? []
            );
            
            return redirect()->route("tickets.show", $id)
                ->with("success", "回复已添加");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with("error", "回复失败: " . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * 分配工单
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function assign(Request $request, $id)
    {
        // 验证请求
        $validator = Validator::make($request->all(), [
            "assigned_to" => "required|exists:users,id",
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // 分配工单
        try {
            $this->ticketService->assignTicket(
                $id,
                $request->input("assigned_to"),
                Auth::id()
            );
            
            return redirect()->route("tickets.show", $id)
                ->with("success", "工单已分配");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with("error", "分配失败: " . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * 关闭工单
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function close(Request $request, $id)
    {
        // 验证请求
        $validator = Validator::make($request->all(), [
            "resolution" => "nullable|string",
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // 关闭工单
        try {
            $this->ticketService->closeTicket(
                $id,
                Auth::id(),
                $request->input("resolution")
            );
            
            return redirect()->route("tickets.show", $id)
                ->with("success", "工单已关闭");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with("error", "关闭失败: " . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * 重新打开工单
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reopen($id)
    {
        // 重新打开工单
        try {
            $this->ticketService->reopenTicket(
                $id,
                Auth::id()
            );
            
            return redirect()->route("tickets.show", $id)
                ->with("success", "工单已重新打开");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with("error", "重新打开失败: " . $e->getMessage());
        }
    }
    
    /**
     * 删除附件
     *
     * @param int $id
     * @param int $attachmentId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteAttachment($id, $attachmentId)
    {
        try {
            $this->ticketService->deleteAttachment(
                $attachmentId,
                Auth::id()
            );
            
            return redirect()->route("tickets.show", $id)
                ->with("success", "附件已删除");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with("error", "删除附件失败: " . $e->getMessage());
        }
    }
}
