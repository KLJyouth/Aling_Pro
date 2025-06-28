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
 * ����ϵͳAPI������
 * 
 * ��������ص�API����
 */
class TicketApiController extends Controller
{
    /**
     * ��������
     *
     * @var TicketService
     */
    protected $ticketService;
    
    /**
     * ���캯��
     *
     * @param TicketService $ticketService
     */
    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
        $this->middleware("auth:api");
    }
    
    /**
     * ��ȡ�����б�
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Ticket::query();
        
        // ������ǹ���Ա��֧����Ա��ֻ�ܿ����Լ��Ĺ���
        if (!$user->hasRole(["admin", "support"])) {
            $query->where("user_id", $user->id);
        } else if ($request->has("assigned_to") && $request->input("assigned_to") == "me") {
            // ����ǹ���Ա��֧����Ա������ɸѡ������Լ��Ĺ���
            $query->where("assigned_to", $user->id);
        }
        
        // ɸѡ����
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
        
        // ����
        if ($request->has("search")) {
            $search = $request->input("search");
            $query->where(function($q) use ($search) {
                $q->where("title", "like", "%{$search}%")
                  ->orWhere("content", "like", "%{$search}%");
            });
        }
        
        // ����
        $sortField = $request->input("sort_field", "created_at");
        $sortDirection = $request->input("sort_direction", "desc");
        $query->orderBy($sortField, $sortDirection);
        
        // ��ҳ
        $perPage = $request->input("per_page", 15);
        $tickets = $query->with(["user", "department", "category", "assignedUser"])->paginate($perPage);
        
        return response()->json([
            "status" => "success",
            "data" => $tickets
        ]);
    }
    
    /**
     * ��������
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // ��֤����
        $validator = Validator::make($request->all(), [
            "title" => "required|string|max:255",
            "content" => "required|string",
            "department_id" => "required|exists:ticket_departments,id",
            "category_id" => "required|exists:ticket_categories,id",
            "priority" => "required|in:low,medium,high,urgent",
            "attachments" => "nullable|array",
            "attachments.*" => "file|max:10240", // ���10MB
            "is_public" => "nullable|boolean",
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                "status" => "error",
                "message" => "��֤ʧ��",
                "errors" => $validator->errors()
            ], 422);
        }
        
        // ��������
        try {
            $ticket = $this->ticketService->createTicket(
                $request->only(["title", "content", "department_id", "category_id", "priority", "is_public"]),
                Auth::id(),
                $request->file("attachments") ?: []
            );
            
            return response()->json([
                "status" => "success",
                "message" => "���������ɹ�",
                "data" => $ticket->load(["user", "department", "category", "attachments"])
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                "status" => "error",
                "message" => "��������ʧ��: " . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * ��ȡ��������
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
                // ������ǹ���Ա��֧����Ա������ʾ�ڲ��ظ�
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
        
        // ���Ȩ��
        if (!$user->hasRole(["admin", "support"]) && $ticket->user_id != $user->id) {
            return response()->json([
                "status" => "error",
                "message" => "û��Ȩ�޲鿴�˹���"
            ], 403);
        }
        
        return response()->json([
            "status" => "success",
            "data" => $ticket
        ]);
    }
    
    /**
     * ���¹���
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $ticket = Ticket::findOrFail($id);
        
        // ���Ȩ��
        if (!$user->hasRole(["admin", "support"]) && $ticket->user_id != $user->id) {
            return response()->json([
                "status" => "error",
                "message" => "û��Ȩ�޸��´˹���"
            ], 403);
        }
        
        // ��֤����
        $rules = [
            "title" => "sometimes|required|string|max:255",
            "content" => "sometimes|required|string",
            "priority" => "sometimes|required|in:low,medium,high,urgent",
        ];
        
        // ����Ա��֧����Ա���Ը��¸����ֶ�
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
                "message" => "��֤ʧ��",
                "errors" => $validator->errors()
            ], 422);
        }
        
        // ���¹���
        try {
            $ticket = $this->ticketService->updateTicket(
                $id,
                $request->only(array_keys($rules)),
                Auth::id()
            );
            
            return response()->json([
                "status" => "success",
                "message" => "�������³ɹ�",
                "data" => $ticket->load(["user", "department", "category", "assignedUser"])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "status" => "error",
                "message" => "��������ʧ��: " . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * ɾ��������������Ա��
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $ticket = Ticket::findOrFail($id);
        
        try {
            // ɾ������
            foreach ($ticket->attachments as $attachment) {
                Storage::disk("public")->delete($attachment->file_path);
            }
            
            // ɾ������
            $ticket->delete();
            
            return response()->json([
                "status" => "success",
                "message" => "����ɾ���ɹ�"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "status" => "error",
                "message" => "����ɾ��ʧ��: " . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * �ظ�����
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function reply(Request $request, $id)
    {
        $user = Auth::user();
        $ticket = Ticket::findOrFail($id);
        
        // ���Ȩ��
        if (!$user->hasRole(["admin", "support"]) && $ticket->user_id != $user->id) {
            return response()->json([
                "status" => "error",
                "message" => "û��Ȩ�޻ظ��˹���"
            ], 403);
        }
        
        // ��֤����
        $validator = Validator::make($request->all(), [
            "content" => "required|string",
            "is_internal" => "nullable|boolean",
            "attachments" => "nullable|array",
            "attachments.*" => "file|max:10240", // ���10MB
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                "status" => "error",
                "message" => "��֤ʧ��",
                "errors" => $validator->errors()
            ], 422);
        }
        
        // �ǹ���Ա��֧����Ա���ܷ����ڲ��ظ�
        $isInternal = $request->input("is_internal", false);
        if ($isInternal && !$user->hasRole(["admin", "support"])) {
            $isInternal = false;
        }
        
        // ��ӻظ�
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
                "message" => "�ظ���ӳɹ�",
                "data" => $reply->load("user")
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                "status" => "error",
                "message" => "�ظ����ʧ��: " . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * ��ȡ�����ظ�
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getReplies(Request $request, $id)
    {
        $user = Auth::user();
        $ticket = Ticket::findOrFail($id);
        
        // ���Ȩ��
        if (!$user->hasRole(["admin", "support"]) && $ticket->user_id != $user->id) {
            return response()->json([
                "status" => "error",
                "message" => "û��Ȩ�޲鿴�˹����Ļظ�"
            ], 403);
        }
        
        $query = TicketReply::where("ticket_id", $id);
        
        // ������ǹ���Ա��֧����Ա������ʾ�ڲ��ظ�
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
     * ���乤��
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function assign(Request $request, $id)
    {
        // ��֤����
        $validator = Validator::make($request->all(), [
            "assigned_to" => "required|exists:users,id",
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                "status" => "error",
                "message" => "��֤ʧ��",
                "errors" => $validator->errors()
            ], 422);
        }
        
        // ���乤��
        try {
            $ticket = $this->ticketService->assignTicket(
                $id,
                $request->input("assigned_to"),
                Auth::id()
            );
            
            return response()->json([
                "status" => "success",
                "message" => "��������ɹ�",
                "data" => $ticket->load("assignedUser")
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "status" => "error",
                "message" => "��������ʧ��: " . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * �رչ���
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function close(Request $request, $id)
    {
        $user = Auth::user();
        $ticket = Ticket::findOrFail($id);
        
        // ���Ȩ��
        if (!$user->hasRole(["admin", "support"]) && $ticket->user_id != $user->id) {
            return response()->json([
                "status" => "error",
                "message" => "û��Ȩ�޹رմ˹���"
            ], 403);
        }
        
        // ��֤����
        $validator = Validator::make($request->all(), [
            "resolution" => "nullable|string",
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                "status" => "error",
                "message" => "��֤ʧ��",
                "errors" => $validator->errors()
            ], 422);
        }
        
        // �رչ���
        try {
            $ticket = $this->ticketService->closeTicket(
                $id,
                Auth::id(),
                $request->input("resolution")
            );
            
            return response()->json([
                "status" => "success",
                "message" => "�����رճɹ�",
                "data" => $ticket
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "status" => "error",
                "message" => "�����ر�ʧ��: " . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * ���´򿪹���
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function reopen($id)
    {
        $user = Auth::user();
        $ticket = Ticket::findOrFail($id);
        
        // ���Ȩ��
        if (!$user->hasRole(["admin", "support"]) && $ticket->user_id != $user->id) {
            return response()->json([
                "status" => "error",
                "message" => "û��Ȩ�����´򿪴˹���"
            ], 403);
        }
        
        // ���´򿪹���
        try {
            $ticket = $this->ticketService->reopenTicket($id, Auth::id());
            
            return response()->json([
                "status" => "success",
                "message" => "���������´�",
                "data" => $ticket
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "status" => "error",
                "message" => "���´򿪹���ʧ��: " . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * �ϴ�����
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadAttachment(Request $request, $id)
    {
        $user = Auth::user();
        $ticket = Ticket::findOrFail($id);
        
        // ���Ȩ��
        if (!$user->hasRole(["admin", "support"]) && $ticket->user_id != $user->id) {
            return response()->json([
                "status" => "error",
                "message" => "û��Ȩ��Ϊ�˹����ϴ�����"
            ], 403);
        }
        
        // ��֤����
        $validator = Validator::make($request->all(), [
            "file" => "required|file|max:10240", // ���10MB
            "reply_id" => "nullable|exists:ticket_replies,id",
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                "status" => "error",
                "message" => "��֤ʧ��",
                "errors" => $validator->errors()
            ], 422);
        }
        
        // �ϴ�����
        try {
            $attachment = $this->ticketService->addAttachment(
                $id,
                $request->input("reply_id"),
                Auth::id(),
                $request->file("file")
            );
            
            return response()->json([
                "status" => "success",
                "message" => "�����ϴ��ɹ�",
                "data" => $attachment
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                "status" => "error",
                "message" => "�����ϴ�ʧ��: " . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * ɾ������
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
        
        // ���Ȩ��
        if (!$user->hasRole(["admin", "support"]) && $ticket->user_id != $user->id) {
            return response()->json([
                "status" => "error",
                "message" => "û��Ȩ��ɾ���˸���"
            ], 403);
        }
        
        // ɾ������
        try {
            $this->ticketService->deleteAttachment($attachmentId, Auth::id());
            
            return response()->json([
                "status" => "success",
                "message" => "����ɾ���ɹ�"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "status" => "error",
                "message" => "����ɾ��ʧ��: " . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * ��ȡ�����б�
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAttachments($id)
    {
        $user = Auth::user();
        $ticket = Ticket::findOrFail($id);
        
        // ���Ȩ��
        if (!$user->hasRole(["admin", "support"]) && $ticket->user_id != $user->id) {
            return response()->json([
                "status" => "error",
                "message" => "û��Ȩ�޲鿴�˹����ĸ���"
            ], 403);
        }
        
        $attachments = TicketAttachment::where("ticket_id", $id)->get();
        
        return response()->json([
            "status" => "success",
            "data" => $attachments
        ]);
    }
    
    /**
     * ��ȡ����ͳ����Ϣ
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatistics()
    {
        $user = Auth::user();
        
        // ��ȡͳ����Ϣ
        $stats = $this->ticketService->getTicketStats($user->hasRole(["admin", "support"]) ? null : $user->id);
        
        return response()->json([
            "status" => "success",
            "data" => $stats
        ]);
    }
    
    /**
     * ��ȡ�����б�
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
     * ��������
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createDepartment(Request $request)
    {
        // ��֤����
        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:255|unique:ticket_departments,name",
            "description" => "nullable|string",
            "is_active" => "nullable|boolean",
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                "status" => "error",
                "message" => "��֤ʧ��",
                "errors" => $validator->errors()
            ], 422);
        }
        
        // ��������
        $department = TicketDepartment::create([
            "name" => $request->input("name"),
            "description" => $request->input("description"),
            "is_active" => $request->input("is_active", true),
        ]);
        
        return response()->json([
            "status" => "success",
            "message" => "���Ŵ����ɹ�",
            "data" => $department
        ], 201);
    }
    
    /**
     * ��ȡ��������
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
     * ���²���
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateDepartment(Request $request, $id)
    {
        $department = TicketDepartment::findOrFail($id);
        
        // ��֤����
        $validator = Validator::make($request->all(), [
            "name" => "sometimes|required|string|max:255|unique:ticket_departments,name," . $id,
            "description" => "nullable|string",
            "is_active" => "nullable|boolean",
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                "status" => "error",
                "message" => "��֤ʧ��",
                "errors" => $validator->errors()
            ], 422);
        }
        
        // ���²���
        $department->update($request->only(["name", "description", "is_active"]));
        
        return response()->json([
            "status" => "success",
            "message" => "���Ÿ��³ɹ�",
            "data" => $department
        ]);
    }
    
    /**
     * ɾ������
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteDepartment($id)
    {
        $department = TicketDepartment::findOrFail($id);
        
        // ����Ƿ��й����Ĺ���
        $hasTickets = Ticket::where("department_id", $id)->exists();
        if ($hasTickets) {
            return response()->json([
                "status" => "error",
                "message" => "�޷�ɾ�����ţ����ڹ����Ĺ���"
            ], 400);
        }
        
        // ����Ƿ��й����ķ���
        $hasCategories = TicketCategory::where("department_id", $id)->exists();
        if ($hasCategories) {
            return response()->json([
                "status" => "error",
                "message" => "�޷�ɾ�����ţ����ڹ����ķ���"
            ], 400);
        }
        
        $department->delete();
        
        return response()->json([
            "status" => "success",
            "message" => "����ɾ���ɹ�"
        ]);
    }
    
    /**
     * ��ȡ�����б�
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
     * ��������
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createCategory(Request $request)
    {
        // ��֤����
        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:255",
            "department_id" => "required|exists:ticket_departments,id",
            "description" => "nullable|string",
            "is_active" => "nullable|boolean",
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                "status" => "error",
                "message" => "��֤ʧ��",
                "errors" => $validator->errors()
            ], 422);
        }
        
        // ���ͬһ�������Ƿ���ͬ������
        $exists = TicketCategory::where("name", $request->input("name"))
            ->where("department_id", $request->input("department_id"))
            ->exists();
            
        if ($exists) {
            return response()->json([
                "status" => "error",
                "message" => "ͬһ�������Ѵ���ͬ������"
            ], 422);
        }
        
        // ��������
        $category = TicketCategory::create([
            "name" => $request->input("name"),
            "department_id" => $request->input("department_id"),
            "description" => $request->input("description"),
            "is_active" => $request->input("is_active", true),
        ]);
        
        return response()->json([
            "status" => "success",
            "message" => "���ഴ���ɹ�",
            "data" => $category
        ], 201);
    }
    
    /**
     * ��ȡ��������
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
     * ���·���
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCategory(Request $request, $id)
    {
        $category = TicketCategory::findOrFail($id);
        
        // ��֤����
        $validator = Validator::make($request->all(), [
            "name" => "sometimes|required|string|max:255",
            "department_id" => "sometimes|required|exists:ticket_departments,id",
            "description" => "nullable|string",
            "is_active" => "nullable|boolean",
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                "status" => "error",
                "message" => "��֤ʧ��",
                "errors" => $validator->errors()
            ], 422);
        }
        
        // ���ͬһ�������Ƿ���ͬ������
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
                    "message" => "ͬһ�������Ѵ���ͬ������"
                ], 422);
            }
        }
        
        // ���·���
        $category->update($request->only(["name", "department_id", "description", "is_active"]));
        
        return response()->json([
            "status" => "success",
            "message" => "������³ɹ�",
            "data" => $category->load("department")
        ]);
    }
    
    /**
     * ɾ������
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteCategory($id)
    {
        $category = TicketCategory::findOrFail($id);
        
        // ����Ƿ��й����Ĺ���
        $hasTickets = Ticket::where("category_id", $id)->exists();
        if ($hasTickets) {
            return response()->json([
                "status" => "error",
                "message" => "�޷�ɾ�����࣬���ڹ����Ĺ���"
            ], 400);
        }
        
        $category->delete();
        
        return response()->json([
            "status" => "success",
            "message" => "����ɾ���ɹ�"
        ]);
    }
    
    /**
     * ���ݲ��Ż�ȡ����
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
