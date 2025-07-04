<?php

namespace App\Models\AI;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ApiLog extends Model
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = "ai_api_logs";

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        "provider_id",
        "model_id",
        "agent_id",
        "api_key_id",
        "user_id",
        "request_id",
        "ip_address",
        "endpoint",
        "request_data",
        "response_data",
        "response_time",
        "input_tokens",
        "output_tokens",
        "status",
        "error_message",
        "cost",
        "session_id",
    ];

    /**
     * 应该被转换成原生类型的属性
     *
     * @var array
     */
    protected $casts = [
        "response_time" => "integer",
        "input_tokens" => "integer",
        "output_tokens" => "integer",
        "cost" => "float",
    ];

    /**
     * 获取关联的提供商
     */
    public function provider()
    {
        return $this->belongsTo(ModelProvider::class, "provider_id");
    }

    /**
     * 获取关联的模型
     */
    public function model()
    {
        return $this->belongsTo(Model::class, "model_id");
    }

    /**
     * 获取关联的智能体
     */
    public function agent()
    {
        return $this->belongsTo(Agent::class, "agent_id");
    }

    /**
     * 获取关联的API密钥
     */
    public function apiKey()
    {
        return $this->belongsTo(ApiKey::class, "api_key_id");
    }

    /**
     * 获取关联的用户
     */
    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }

    /**
     * 获取总标记数
     *
     * @return int
     */
    public function getTotalTokensAttribute()
    {
        return $this->input_tokens + $this->output_tokens;
    }

    /**
     * 获取请求数据数组
     *
     * @return array|null
     */
    public function getRequestDataArrayAttribute()
    {
        return $this->request_data ? json_decode($this->request_data, true) : null;
    }

    /**
     * 获取响应数据数组
     *
     * @return array|null
     */
    public function getResponseDataArrayAttribute()
    {
        return $this->response_data ? json_decode($this->response_data, true) : null;
    }
}
