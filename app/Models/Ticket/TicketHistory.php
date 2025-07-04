<?php

namespace App\Models\Ticket;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

/**
 * 工单历史记录模型
 * 
 * 用于记录工单的操作历史
 */
class TicketHistory extends Model
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = "ticket_history";

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        "ticket_id",     // 工单ID
        "user_id",       // 操作用户ID
        "action",        // 操作类型
        "data",          // 操作数据 (JSON)
    ];

    /**
     * 应该被转换为日期的属性
     *
     * @var array
     */
    protected $dates = [
        "created_at",
        "updated_at",
    ];

    /**
     * 应该被转换为原生类型的属性
     *
     * @var array
     */
    protected $casts = [
        "data" => "array",
    ];

    /**
     * 获取历史记录所属的工单
     */
    public function ticket()
    {
        return $this->belongsTo(Ticket::class, "ticket_id");
    }

    /**
     * 获取操作的用户
     */
    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }

    /**
     * 获取操作类型的可读文本
     *
     * @return string
     */
    public function getActionText()
    {
        $actions = [
            "create" => "创建工单",
            "update" => "更新工单",
            "assign" => "分配工单",
            "reply" => "回复工单",
            "close" => "关闭工单",
            "reopen" => "重新打开工单",
            "delete" => "删除工单",
            "restore" => "恢复工单",
            "attachment_add" => "添加附件",
            "attachment_remove" => "删除附件",
            "status_change" => "更改状态",
            "priority_change" => "更改优先级",
            "department_change" => "更改部门",
            "category_change" => "更改分类",
        ];

        return $actions[$this->action] ?? $this->action;
    }

    /**
     * 获取操作数据的摘要
     *
     * @return string
     */
    public function getDataSummary()
    {
        $data = $this->data;
        $summary = "";

        switch ($this->action) {
            case "update":
                if (isset($data["changes"])) {
                    $fields = [];
                    foreach ($data["changes"] as $field => $value) {
                        $fields[] = $field;
                    }
                    $summary = "更新了 " . implode(", ", $fields);
                }
                break;
            case "assign":
                if (isset($data["assigned_to"])) {
                    $summary = "分配给 ID:" . $data["assigned_to"];
                }
                break;
            case "status_change":
                if (isset($data["from"]) && isset($data["to"])) {
                    $summary = "状态从 {$data["from"]} 变更为 {$data["to"]}";
                }
                break;
            case "priority_change":
                if (isset($data["from"]) && isset($data["to"])) {
                    $summary = "优先级从 {$data["from"]} 变更为 {$data["to"]}";
                }
                break;
            default:
                if (isset($data["message"])) {
                    $summary = $data["message"];
                }
        }

        return $summary;
    }
}
