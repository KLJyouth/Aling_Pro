<?php

namespace App\Services\Ticket;

use App\Models\Ticket\Ticket;
use App\Models\Ticket\TicketReply;
use App\Models\Ticket\TicketAttachment;
use App\Models\Ticket\TicketHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Exception;

/**
 * 工单服务类
 * 
 * 提供工单相关的业务逻辑处理
 */
class TicketService
{
    /**
     * 创建新工单
     *
     * @param array $data 工单数据
     * @param int $userId 用户ID
     * @param array $files 上传的文件
     * @return Ticket
     */
    public function createTicket(array $data, int $userId, array $files = [])
    {
        DB::beginTransaction();
        
        try {
            // 创建工单
            $ticket = Ticket::create([
                "title" => $data["title"],
                "content" => $data["content"],
                "user_id" => $userId,
                "department_id" => $data["department_id"] ?? null,
                "category_id" => $data["category_id"] ?? null,
                "priority" => $data["priority"] ?? "medium",
                "status" => "open",
                "is_public" => $data["is_public"] ?? false,
                "meta_data" => $data["meta_data"] ?? [],
            ]);
            
            // 记录历史
            $ticket->addHistory("create", $userId, [
                "message" => "创建了工单"
            ]);
            
            // 处理附件
            if (!empty($files)) {
                foreach ($files as $file) {
                    $this->addAttachment($ticket->id, null, $userId, $file);
                }
            }
            
            // 自动分配
            $this->autoAssignTicket($ticket);
            
            DB::commit();
            return $ticket;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * 更新工单
     *
     * @param int $ticketId 工单ID
     * @param array $data 更新数据
     * @param int $userId 操作用户ID
     * @return Ticket
     */
    public function updateTicket(int $ticketId, array $data, int $userId)
    {
        DB::beginTransaction();
        
        try {
            $ticket = Ticket::findOrFail($ticketId);
            $changes = [];
            
            // 记录变更
            foreach ($data as $key => $value) {
                if (in_array($key, $ticket->getFillable()) && $ticket->{$key} != $value) {
                    $changes[$key] = [
                        "from" => $ticket->{$key},
                        "to" => $value
                    ];
                }
            }
            
            // 更新工单
            $ticket->update($data);
            
            // 记录历史
            if (!empty($changes)) {
                $ticket->addHistory("update", $userId, [
                    "changes" => $changes,
                    "message" => "更新了工单"
                ]);
            }
            
            // 处理状态变更
            if (isset($data["status"]) && $ticket->status != $data["status"]) {
                $ticket->addHistory("status_change", $userId, [
                    "from" => $ticket->getOriginal("status"),
                    "to" => $data["status"],
                    "message" => "更改了工单状态"
                ]);
                
                // 如果关闭工单
                if (in_array($data["status"], ["resolved", "closed"])) {
                    $ticket->closed_at = now();
                    $ticket->closed_by = $userId;
                    $ticket->save();
                }
                
                // 如果重新打开工单
                if ($data["status"] == "open" && $ticket->getOriginal("status") != "open") {
                    $ticket->closed_at = null;
                    $ticket->closed_by = null;
                    $ticket->save();
                }
            }
            
            DB::commit();
            return $ticket;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * 回复工单
     *
     * @param int $ticketId 工单ID
     * @param string $content 回复内容
     * @param int $userId 用户ID
     * @param bool $isInternal 是否为内部回复
     * @param array $files 上传的文件
     * @return TicketReply
     */
    public function replyTicket(int $ticketId, string $content, int $userId, bool $isInternal = false, array $files = [])
    {
        DB::beginTransaction();
        
        try {
            $ticket = Ticket::findOrFail($ticketId);
            
            // 创建回复
            $reply = TicketReply::create([
                'ticket_id' => $ticketId,
                'user_id' => $userId,
                'content' => $content,
                'is_internal' => $isInternal,
            ]);
            
            // 更新工单状态
            $user = User::find($userId);
            $isStaff = $user && $user->hasRole(['admin', 'support']);
            
            if ($isStaff) {
                // 如果是工作人员回复，将工单状态设为待处理
                $ticket->status = 'pending';
            } else {
                // 如果是用户回复，将工单状态设为处理中
                $ticket->status = 'processing';
            }
            
            $ticket->save();
            
            // 记录历史
            $ticket->addHistory('reply', $userId, [
                'reply_id' => $reply->id,
                'is_internal' => $isInternal,
                'message' => ($isInternal ? '添加了内部回复' : '回复了工单')
            ]);
            
            // 处理附件
            if (!empty($files)) {
                foreach ($files as $file) {
                    $this->addAttachment($ticketId, $reply->id, $userId, $file);
                }
            }
            
            DB::commit();
            return $reply;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * 分配工单
     *
     * @param int $ticketId 工单ID
     * @param int $assignedTo 分配给的用户ID
     * @param int $userId 操作用户ID
     * @return Ticket
     */
    public function assignTicket(int $ticketId, int $assignedTo, int $userId)
    {
        DB::beginTransaction();
        
        try {
            $ticket = Ticket::findOrFail($ticketId);
            $oldAssignedTo = $ticket->assigned_to;
            
            // 更新工单
            $ticket->assigned_to = $assignedTo;
            $ticket->status = 'processing';
            $ticket->save();
            
            // 记录历史
            $ticket->addHistory('assign', $userId, [
                'from' => $oldAssignedTo,
                'to' => $assignedTo,
                'message' => '将工单分配给了用户 ID:' . $assignedTo
            ]);
            
            DB::commit();
            return $ticket;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * 关闭工单
     *
     * @param int $ticketId 工单ID
     * @param int $userId 操作用户ID
     * @param string|null $resolution 解决方案
     * @return Ticket
     */
    public function closeTicket(int $ticketId, int $userId, ?string $resolution = null)
    {
        DB::beginTransaction();
        
        try {
            $ticket = Ticket::findOrFail($ticketId);
            
            // 更新工单
            $ticket->status = 'closed';
            $ticket->closed_at = now();
            $ticket->closed_by = $userId;
            
            if ($resolution) {
                $ticket->resolution = $resolution;
            }
            
            $ticket->save();
            
            // 记录历史
            $ticket->addHistory('close', $userId, [
                'resolution' => $resolution,
                'message' => '关闭了工单'
            ]);
            
            DB::commit();
            return $ticket;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * 重新打开工单
     *
     * @param int $ticketId 工单ID
     * @param int $userId 操作用户ID
     * @return Ticket
     */
    public function reopenTicket(int $ticketId, int $userId)
    {
        DB::beginTransaction();
        
        try {
            $ticket = Ticket::findOrFail($ticketId);
            
            // 更新工单
            $ticket->status = 'open';
            $ticket->closed_at = null;
            $ticket->closed_by = null;
            $ticket->save();
            
            // 记录历史
            $ticket->addHistory('reopen', $userId, [
                'message' => '重新打开了工单'
            ]);
            
            DB::commit();
            return $ticket;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * 添加附件
     *
     * @param int $ticketId 工单ID
     * @param int|null $replyId 回复ID
     * @param int $userId 用户ID
     * @param UploadedFile $file 上传的文件
     * @return TicketAttachment
     */
    public function addAttachment(int $ticketId, ?int $replyId, int $userId, UploadedFile $file)
    {
        // 生成文件名
        $fileName = $file->getClientOriginalName();
        $fileExtension = $file->getClientOriginalExtension();
        $storageName = Str::uuid() . '.' . $fileExtension;
        
        // 存储文件
        $path = $file->storeAs('tickets/' . $ticketId, $storageName, 'public');
        
        // 判断是否为图片
        $isImage = in_array(strtolower($fileExtension), ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp']);
        
        // 创建附件记录
        $attachment = TicketAttachment::create([
            'ticket_id' => $ticketId,
            'reply_id' => $replyId,
            'user_id' => $userId,
            'file_name' => $fileName,
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'file_type' => $file->getMimeType(),
            'is_image' => $isImage,
        ]);
        
        // 记录历史
        $ticket = Ticket::find($ticketId);
        $ticket->addHistory('attachment_add', $userId, [
            'attachment_id' => $attachment->id,
            'file_name' => $fileName,
            'message' => '添加了附件: ' . $fileName
        ]);
        
        return $attachment;
    }
    
    /**
     * 删除附件
     *
     * @param int $attachmentId 附件ID
     * @param int $userId 操作用户ID
     * @return bool
     */
    public function deleteAttachment(int $attachmentId, int $userId)
    {
        DB::beginTransaction();
        
        try {
            $attachment = TicketAttachment::findOrFail($attachmentId);
            $ticketId = $attachment->ticket_id;
            $fileName = $attachment->file_name;
            
            // 删除文件
            Storage::disk('public')->delete($attachment->file_path);
            
            // 删除记录
            $attachment->delete();
            
            // 记录历史
            $ticket = Ticket::find($ticketId);
            $ticket->addHistory('attachment_remove', $userId, [
                'file_name' => $fileName,
                'message' => '删除了附件: ' . $fileName
            ]);
            
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * 自动分配工单
     *
     * @param Ticket $ticket 工单
     * @return Ticket
     */
    protected function autoAssignTicket(Ticket $ticket)
    {
        // 如果已经分配，则跳过
        if ($ticket->assigned_to) {
            return $ticket;
        }
        
        $assignedTo = null;
        
        // 优先根据分类自动分配
        if ($ticket->category_id) {
            $category = $ticket->category;
            if ($category && $category->auto_assign_to) {
                $assignedTo = $category->auto_assign_to;
            }
        }
        
        // 如果分类没有自动分配，则根据部门自动分配
        if (!$assignedTo && $ticket->department_id) {
            $department = $ticket->department;
            if ($department && $department->auto_assign_to) {
                $assignedTo = $department->auto_assign_to;
            }
        }
        
        // 如果有自动分配的用户，则更新工单
        if ($assignedTo) {
            $ticket->assigned_to = $assignedTo;
            $ticket->status = 'processing';
            $ticket->save();
            
            // 记录历史
            $ticket->addHistory('assign', $assignedTo, [
                'from' => null,
                'to' => $assignedTo,
                'message' => '系统自动分配给了用户 ID:' . $assignedTo
            ]);
        }
        
        return $ticket;
    }
    
    /**
     * 获取工单统计信息
     *
     * @param int|null $userId 用户ID，如果提供则只统计该用户的工单
     * @return array
     */
    public function getTicketStats(?int $userId = null)
    {
        $query = Ticket::query();
        
        if ($userId) {
            $query->where(function($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere('assigned_to', $userId);
            });
        }
        
        $total = $query->count();
        
        $open = (clone $query)->where('status', 'open')->count();
        $pending = (clone $query)->where('status', 'pending')->count();
        $processing = (clone $query)->where('status', 'processing')->count();
        $resolved = (clone $query)->where('status', 'resolved')->count();
        $closed = (clone $query)->where('status', 'closed')->count();
        
        $highPriority = (clone $query)->whereIn('priority', ['high', 'urgent'])->count();
        $unassigned = (clone $query)->whereNull('assigned_to')->count();
        
        return [
            'total' => $total,
            'open' => $open,
            'pending' => $pending,
            'processing' => $processing,
            'resolved' => $resolved,
            'closed' => $closed,
            'high_priority' => $highPriority,
            'unassigned' => $unassigned,
        ];
    }
}
