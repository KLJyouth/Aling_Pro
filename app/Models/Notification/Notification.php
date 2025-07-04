<?php

namespace App\Models\Notification;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

/**
 * 通知模型
 * 
 * 用于管理系统通知
 */
class Notification extends Model
{
    use SoftDeletes;

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected  = 'notifications';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected  = [
        'title',           // 通知标题
        'content',         // 通知内容
        'type',            // 通知类型: system(系统通知), user(用户通知), email(邮件通知), api(API通知)
        'status',          // 状态: draft(草稿), sending(发送中), sent(已发送), failed(发送失败)
        'priority',        // 优先级: low, normal, high, urgent
        'sender_id',       // 发送者ID
        'template_id',     // 模板ID
        'scheduled_at',    // 计划发送时间
        'sent_at',         // 实际发送时间
        'metadata',        // 元数据(JSON)
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
        'scheduled_at',
        'sent_at',
    ];

    /**
     * 应该被转换为原生类型的属性
     *
     * @var array
     */
    protected  = [
        'metadata' => 'array',
    ];

    /**
     * 获取通知发送者
     */
    public function sender()
    {
        return ->belongsTo(User::class, 'sender_id');
    }

    /**
     * 获取通知使用的模板
     */
    public function template()
    {
        return ->belongsTo(NotificationTemplate::class, 'template_id');
    }

    /**
     * 获取通知的接收者
     */
    public function recipients()
    {
        return ->hasMany(NotificationRecipient::class, 'notification_id');
    }

    /**
     * 获取通知的附件
     */
    public function attachments()
    {
        return ->hasMany(NotificationAttachment::class, 'notification_id');
    }

    /**
     * 获取草稿通知
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function drafts()
    {
        return static::where('status', 'draft');
    }

    /**
     * 获取已发送的通知
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function sent()
    {
        return static::where('status', 'sent');
    }

    /**
     * 获取发送失败的通知
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function failed()
    {
        return static::where('status', 'failed');
    }

    /**
     * 获取待发送的通知
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function scheduled()
    {
        return static::where('status', 'draft')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now());
    }

    /**
     * 设置为发送中状态
     *
     * @return bool
     */
    public function setSending()
    {
        ->status = 'sending';
        return ->save();
    }

    /**
     * 设置为已发送状态
     *
     * @return bool
     */
    public function setSent()
    {
        ->status = 'sent';
        ->sent_at = now();
        return ->save();
    }

    /**
     * 设置为发送失败状态
     *
     * @param string|null 
     * @return bool
     */
    public function setFailed( = null)
    {
        ->status = 'failed';
        if () {
             = ->metadata ?: [];
            ['error_message'] = ;
            ->metadata = ;
        }
        return ->save();
    }

    /**
     * 是否为系统通知
     *
     * @return bool
     */
    public function isSystemNotification()
    {
        return ->type === 'system';
    }

    /**
     * 是否为用户通知
     *
     * @return bool
     */
    public function isUserNotification()
    {
        return ->type === 'user';
    }

    /**
     * 是否为邮件通知
     *
     * @return bool
     */
    public function isEmailNotification()
    {
        return ->type === 'email';
    }

    /**
     * 是否为API通知
     *
     * @return bool
     */
    public function isApiNotification()
    {
        return ->type === 'api';
    }
}
