<?php

namespace App\Models\MCP;

use Illuminate\Database\Eloquent\Model;

class MCPLog extends Model
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = "mcp_logs";

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        "interface_id",
        "method",
        "endpoint",
        "request_data",
        "status_code",
        "response_data",
        "response_time",
        "ip_address",
        "user_agent",
        "user_id",
        "created_at"
    ];

    /**
     * 应该被转换为原生类型的属性
     *
     * @var array
     */
    protected $casts = [
        "request_data" => "json",
        "response_data" => "json",
        "created_at" => "datetime"
    ];

    /**
     * 表明模型是否应该被打上时间戳
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * 获取关联的接口
     */
    public function interface()
    {
        return $this->belongsTo(MCPInterface::class, "interface_id");
    }

    /**
     * 获取关联的用户
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, "user_id");
    }
}
