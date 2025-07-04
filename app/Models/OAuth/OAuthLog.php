<?php

namespace App\Models\OAuth;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class OAuthLog extends Model
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = "oauth_logs";

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        "provider_id",
        "user_id",
        "action",
        "status",
        "ip_address",
        "user_agent",
        "error_message",
        "request_data",
        "response_data",
    ];

    /**
     * 应该被转换成原生类型的属性
     *
     * @var array
     */
    protected $casts = [
        "request_data" => "array",
        "response_data" => "array",
    ];

    /**
     * 获取关联的用户
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 获取关联的提供商
     */
    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    /**
     * 记录成功日志
     *
     * @param string $action
     * @param int|null $providerId
     * @param int|null $userId
     * @param array $requestData
     * @param array $responseData
     * @return static
     */
    public static function success($action, $providerId = null, $userId = null, $requestData = [], $responseData = [])
    {
        return static::create([
            "provider_id" => $providerId,
            "user_id" => $userId,
            "action" => $action,
            "status" => "success",
            "ip_address" => request()->ip(),
            "user_agent" => request()->userAgent(),
            "request_data" => $requestData,
            "response_data" => $responseData,
        ]);
    }

    /**
     * 记录失败日志
     *
     * @param string $action
     * @param string $errorMessage
     * @param int|null $providerId
     * @param int|null $userId
     * @param array $requestData
     * @param array $responseData
     * @return static
     */
    public static function failure($action, $errorMessage, $providerId = null, $userId = null, $requestData = [], $responseData = [])
    {
        return static::create([
            "provider_id" => $providerId,
            "user_id" => $userId,
            "action" => $action,
            "status" => "failed",
            "ip_address" => request()->ip(),
            "user_agent" => request()->userAgent(),
            "error_message" => $errorMessage,
            "request_data" => $requestData,
            "response_data" => $responseData,
        ]);
    }
}
