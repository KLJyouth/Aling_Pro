<?php

namespace App\Models\Notification;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

/**
 * 邮件发送接口模型
 * 
 * 用于管理邮件发送接口配置
 */
class EmailProvider extends Model
{
    use SoftDeletes;

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected  = 'email_providers';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected  = [
        'name',            // 接口名称
        'provider_type',   // 接口类型: smtp, sendgrid, mailgun, ses, etc.
        'host',            // SMTP主机
        'port',            // SMTP端口
        'username',        // 用户名/账号
        'password',        // 密码/密钥
        'encryption',      // 加密方式: tls, ssl, null
        'api_key',         // API密钥（用于API类型的邮件服务）
        'api_secret',      // API密钥（用于API类型的邮件服务）
        'region',          // 区域（用于AWS SES等）
        'from_email',      // 默认发件人邮箱
        'from_name',       // 默认发件人名称
        'reply_to_email',  // 默认回复邮箱
        'status',          // 状态: active, inactive
        'is_default',      // 是否为默认接口
        'daily_limit',     // 每日发送限制
        'creator_id',      // 创建者ID
        'settings',        // 其他设置（JSON）
    ];

    /**
     * 应该被隐藏的属性
     *
     * @var array
     */
    protected  = [
        'password',
        'api_key',
        'api_secret',
    ];

    /**
     * 应该被转换为原生类型的属性
     *
     * @var array
     */
    protected  = [
        'settings' => 'array',
        'is_default' => 'boolean',
    ];

    /**
     * 应该被转换为日期的属性
     *
     * @var array
     */
    protected  = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * 获取创建者
     */
    public function creator()
    {
        return ->belongsTo(User::class, 'creator_id');
    }

    /**
     * 获取活动的邮件接口
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function active()
    {
        return static::where('status', 'active');
    }

    /**
     * 获取默认邮件接口
     *
     * @return EmailProvider|null
     */
    public static function getDefault()
    {
        return static::where('is_default', true)
            ->where('status', 'active')
            ->first();
    }

    /**
     * 设置为默认接口
     *
     * @return bool
     */
    public function setAsDefault()
    {
        // 先将所有接口设为非默认
        static::query()->update(['is_default' => false]);
        
        // 将当前接口设为默认
        ->is_default = true;
        return ->save();
    }

    /**
     * 获取邮件驱动配置
     *
     * @return array
     */
    public function getMailConfig()
    {
        switch (->provider_type) {
            case 'smtp':
                return [
                    'transport' => 'smtp',
                    'host' => ->host,
                    'port' => ->port,
                    'encryption' => ->encryption,
                    'username' => ->username,
                    'password' => ->password,
                    'from' => [
                        'address' => ->from_email,
                        'name' => ->from_name,
                    ],
                ];
            
            case 'sendgrid':
                return [
                    'transport' => 'sendgrid',
                    'api_key' => ->api_key,
                    'from' => [
                        'address' => ->from_email,
                        'name' => ->from_name,
                    ],
                ];
                
            case 'mailgun':
                return [
                    'transport' => 'mailgun',
                    'domain' => ->settings['domain'] ?? '',
                    'secret' => ->api_key,
                    'endpoint' => ->settings['endpoint'] ?? 'api.mailgun.net',
                    'from' => [
                        'address' => ->from_email,
                        'name' => ->from_name,
                    ],
                ];
                
            case 'ses':
                return [
                    'transport' => 'ses',
                    'key' => ->api_key,
                    'secret' => ->api_secret,
                    'region' => ->region,
                    'from' => [
                        'address' => ->from_email,
                        'name' => ->from_name,
                    ],
                ];
                
            default:
                return [];
        }
    }
}
