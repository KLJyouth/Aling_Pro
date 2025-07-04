<?php

namespace App\Models\Notification;

use Illuminate\Database\Eloquent\Model;

/**
 * 通知附件模型
 * 
 * 用于管理通知的附件
 */
class NotificationAttachment extends Model
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected  = 'notification_attachments';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected  = [
        'notification_id',  // 通知ID
        'file_name',        // 文件名
        'file_path',        // 文件路径
        'file_size',        // 文件大小（字节）
        'file_type',        // 文件类型（MIME类型）
        'description',      // 文件描述
    ];

    /**
     * 获取关联的通知
     */
    public function notification()
    {
        return ->belongsTo(Notification::class, 'notification_id');
    }

    /**
     * 获取文件URL
     *
     * @return string
     */
    public function getFileUrl()
    {
        return asset(->file_path);
    }

    /**
     * 获取格式化的文件大小
     *
     * @return string
     */
    public function getFormattedFileSize()
    {
         = ->file_size;
         = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        
        for ( = 0;  > 1024; ++) {
             /= 1024;
        }
        
        return round(, 2) . ' ' . [];
    }

    /**
     * 检查文件是否为图片
     *
     * @return bool
     */
    public function isImage()
    {
        return strpos(->file_type, 'image/') === 0;
    }

    /**
     * 检查文件是否为PDF
     *
     * @return bool
     */
    public function isPdf()
    {
        return ->file_type === 'application/pdf';
    }

    /**
     * 检查文件是否为文档
     *
     * @return bool
     */
    public function isDocument()
    {
         = [
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain',
        ];
        
        return in_array(->file_type, );
    }
}
