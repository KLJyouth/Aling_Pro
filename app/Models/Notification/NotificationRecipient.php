<?php

namespace App\Models\Notification;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

/**
 * 通知接收者模型
 * 
 * 用于管理通知的接收者信息
 */
class NotificationRecipient extends Model
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected  = 'notification_recipients';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected  = [
        'notification_id',  // 通知ID
        'user_id',          // 用户ID（可为空，用于外部接收者）
        'email',            // 邮箱地址（用于邮件通知）
        'phone',            // 手机号码（用于短信通知）
        'api_endpoint',     // API端点（用于API通知）
        'status',           // 状态：pending(待处理), sent(已发送), read(已读), failed(发送失败)
        'sent_at',          // 发送时间
        'read_at',          // 阅读时间
        'error_message',    // 错误信息
    ];

    /**
     * 应该被转换为日期的属性
     *
     * @var array
     */
    protected  = [
        'created_at',
        'updated_at',
        'sent_at',
        'read_at',
    ];

    /**
     * 获取关联的通知
     */
    public function notification()
    {
        return ->belongsTo(Notification::class, 'notification_id');
    }

    /**
     * 获取关联的用户
     */
    public function user()
    {
        return ->belongsTo(User::class, 'user_id');
    }

    /**
     * 标记为已发送
     *
     * @return bool
     */
    public function markAsSent()
    {
        ->status = 'sent';
        ->sent_at = now();
        return ->save();
    }

    /**
     * 标记为已读
     *
     * @return bool
     */
    public function markAsRead()
    {
        ->status = 'read';
        ->read_at = now();
        return ->save();
    }

    /**
     * 标记为发送失败
     *
     * @param string|null 
     * @return bool
     */
    public function markAsFailed( = null)
    {
        ->status = 'failed';
        if () {
            ->error_message = ;
        }
        return ->save();
    }

    /**
     * 获取待处理的通知接收者
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function pending()
    {
        return static::where('status', 'pending');
    }

    /**
     * 获取已发送的通知接收者
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function sent()
    {
        return static::where('status', 'sent');
    }

    /**
     * 获取已读的通知接收者
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function read()
    {
        return static::where('status', 'read');
    }

    /**
     * 获取发送失败的通知接收者
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function failed()
    {
        return static::where('status', 'failed');
    }
}
