<?php

namespace App\Models\Ticket;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

/**
 * 工单回复模型
 * 
 * 用于管理工单的回复
 */
class TicketReply extends Model
{
    use SoftDeletes;

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = "ticket_replies";

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        "ticket_id",     // 工单ID
        "user_id",       // 回复用户ID
        "content",       // 回复内容
        "is_internal",   // 是否为内部回复
        "meta_data",     // 元数据 (JSON)
    ];

    /**
     * 应该被转换为日期的属性
     *
     * @var array
     */
    protected $dates = [
        "created_at",
        "updated_at",
        "deleted_at",
    ];

    /**
     * 应该被转换为原生类型的属性
     *
     * @var array
     */
    protected $casts = [
        "meta_data" => "array",
        "is_internal" => "boolean",
    ];

    /**
     * 获取回复所属的工单
     */
    public function ticket()
    {
        return $this->belongsTo(Ticket::class, "ticket_id");
    }

    /**
     * 获取回复的用户
     */
    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }

    /**
     * 获取回复的附件
     */
    public function attachments()
    {
        return $this->hasMany(TicketAttachment::class, "reply_id");
    }

    /**
     * 判断是否为内部回复
     *
     * @return bool
     */
    public function isInternal()
    {
        return $this->is_internal;
    }

    /**
     * 判断是否为客服回复
     *
     * @return bool
     */
    public function isStaffReply()
    {
        if ($this->user && $this->user->hasRole(["admin", "support"])) {
            return true;
        }
        
        return false;
    }
}
