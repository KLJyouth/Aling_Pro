<?php

namespace App\Models\Ticket;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

/**
 * 工单模型
 * 
 * 用于管理用户提交的工单
 */
class Ticket extends Model
{
    use SoftDeletes;

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'tickets';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'title',               // 工单标题
        'content',             // 工单内容
        'user_id',             // 提交用户ID
        'assigned_to',         // 分配给谁处理
        'department_id',       // 部门ID
        'category_id',         // 分类ID
        'priority',            // 优先级: low, medium, high, urgent
        'status',              // 状态: open, pending, processing, resolved, closed
        'closed_at',           // 关闭时间
        'closed_by',           // 关闭人ID
        'resolution',          // 解决方案
        'satisfaction_rating', // 满意度评分
        'feedback',            // 用户反馈
        'is_public',           // 是否公开
        'meta_data',           // 元数据 (JSON)
    ];

    /**
     * 应该被转换为日期的属性
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'closed_at',
    ];

    /**
     * 应该被转换为原生类型的属性
     *
     * @var array
     */
    protected $casts = [
        'meta_data' => 'array',
        'is_public' => 'boolean',
    ];

    /**
     * 获取提交工单的用户
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * 获取处理工单的用户
     */
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * 获取关闭工单的用户
     */
    public function closedByUser()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    /**
     * 获取工单所属部门
     */
    public function department()
    {
        return $this->belongsTo(TicketDepartment::class, 'department_id');
    }

    /**
     * 获取工单分类
     */
    public function category()
    {
        return $this->belongsTo(TicketCategory::class, 'category_id');
    }

    /**
     * 获取工单回复
     */
    public function replies()
    {
        return $this->hasMany(TicketReply::class, 'ticket_id');
    }

    /**
     * 获取工单附件
     */
    public function attachments()
    {
        return $this->hasMany(TicketAttachment::class, 'ticket_id');
    }

    /**
     * 获取工单历史记录
     */
    public function history()
    {
        return $this->hasMany(TicketHistory::class, 'ticket_id');
    }

    /**
     * 判断工单是否已关闭
     *
     * @return bool
     */
    public function isClosed()
    {
        return in_array($this->status, ['resolved', 'closed']);
    }

    /**
     * 判断工单是否待处理
     *
     * @return bool
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * 判断工单是否正在处理
     *
     * @return bool
     */
    public function isProcessing()
    {
        return $this->status === 'processing';
    }

    /**
     * 判断工单是否为高优先级
     *
     * @return bool
     */
    public function isHighPriority()
    {
        return in_array($this->priority, ['high', 'urgent']);
    }

    /**
     * 关闭工单
     *
     * @param int $userId 关闭人ID
     * @param string $resolution 解决方案
     * @return bool
     */
    public function closeTicket($userId, $resolution = null)
    {
        $this->status = 'closed';
        $this->closed_at = now();
        $this->closed_by = $userId;
        
        if ($resolution) {
            $this->resolution = $resolution;
        }
        
        return $this->save();
    }

    /**
     * 重新打开工单
     *
     * @return bool
     */
    public function reopenTicket()
    {
        $this->status = 'open';
        $this->closed_at = null;
        $this->closed_by = null;
        
        return $this->save();
    }

    /**
     * 分配工单
     *
     * @param int $userId 分配给的用户ID
     * @return bool
     */
    public function assignTo($userId)
    {
        $this->assigned_to = $userId;
        $this->status = 'processing';
        
        return $this->save();
    }

    /**
     * 添加工单历史记录
     *
     * @param string $action 操作类型
     * @param int $userId 操作人ID
     * @param array $data 附加数据
     * @return TicketHistory
     */
    public function addHistory($action, $userId, $data = [])
    {
        return TicketHistory::create([
            'ticket_id' => $this->id,
            'user_id' => $userId,
            'action' => $action,
            'data' => $data,
        ]);
    }
} 