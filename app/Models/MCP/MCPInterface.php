<?php

namespace App\Models\MCP;

use Illuminate\Database\Eloquent\Model;

class MCPInterface extends Model
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = "mcp_interfaces";

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        "name",
        "endpoint",
        "description",
        "method",
        "parameters",
        "response_format",
        "is_active",
        "requires_auth",
        "rate_limit",
        "created_at",
        "updated_at"
    ];

    /**
     * 应该被转换为原生类型的属性
     *
     * @var array
     */
    protected $casts = [
        "parameters" => "json",
        "response_format" => "json",
        "is_active" => "boolean",
        "requires_auth" => "boolean",
        "created_at" => "datetime",
        "updated_at" => "datetime"
    ];

    /**
     * 获取接口的日志记录
     */
    public function logs()
    {
        return $this->hasMany(MCPLog::class, "interface_id");
    }
}
