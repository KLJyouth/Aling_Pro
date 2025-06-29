<?php

namespace App\Models\Security\ApiControl;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiRequestLog extends Model
{
    use HasFactory;

    /**
     * 与模型关联的数据表
     *
     * @var string
     */
    protected $table = 'api_request_logs';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'api_key_id',
        'api_interface_id',
        'method',
        'path',
        'query_params',
        'request_body',
        'response_body',
        'status_code',
        'response_time',
        'ip_address',
        'user_agent',
        'error_message',
    ];

    /**
     * 应该被转换为原生类型的属性
     *
     * @var array
     */
    protected $casts = [
        'query_params' => 'json',
        'request_body' => 'json',
        'response_body' => 'json',
    ];

    /**
     * 获取关联的API密钥
     */
    public function apiKey()
    {
        return $this->belongsTo(ApiKey::class);
    }

    /**
     * 获取关联的API接口
     */
    public function apiInterface()
    {
        return $this->belongsTo(ApiInterface::class);
    }

    /**
     * 请求是否成功
     *
     * @return bool
     */
    public function isSuccess()
    {
        return $this->status_code >= 200 && $this->status_code < 400;
    }
} 