<?php

namespace App\Models\Ticket;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

/**
 * 工单附件模型
 * 
 * 用于管理工单和回复的附件
 */
class TicketAttachment extends Model
{
    use SoftDeletes;

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'ticket_attachments';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'ticket_id',     // 工单ID
        'reply_id',      // 回复ID
        'user_id',       // 上传用户ID
        'file_name',     // 文件名
        'file_path',     // 文件路径
        'file_size',     // 文件大小
        'file_type',     // 文件类型
        'is_image',      // 是否为图片
        'meta_data',     // 元数据 (JSON)
    ];

    /**
     * 应该被转换为日期的属性
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * 应该被转换为原生类型的属性
     *
     * @var array
     */
    protected $casts = [
        'meta_data' => 'array',
        'is_image' => 'boolean',
    ];

    /**
     * 获取附件所属的工单
     */
    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    /**
     * 获取附件所属的回复
     */
    public function reply()
    {
        return $this->belongsTo(TicketReply::class, 'reply_id');
    }

    /**
     * 获取上传附件的用户
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * 判断是否为图片
     *
     * @return bool
     */
    public function isImage()
    {
        return $this->is_image;
    }

    /**
     * 获取文件URL
     *
     * @return string
     */
    public function getFileUrl()
    {
        return asset('storage/' . $this->file_path);
    }

    /**
     * 获取文件大小的可读格式
     *
     * @return string
     */
    public function getReadableFileSize()
    {
        $size = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        
        while ($size >= 1024 && $i < 4) {
            $size /= 1024;
            $i++;
        }
        
        return round($size, 2) . ' ' . $units[$i];
    }
}
