<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class UserFile extends Model
{
    use SoftDeletes;

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        "user_id",
        "name",
        "path",
        "type",
        "mime_type",
        "size",
        "category",
        "description",
        "metadata",
        "is_public",
        "download_count",
    ];

    /**
     * 应该被转换成原生类型的属性
     *
     * @var array
     */
    protected $casts = [
        "metadata" => "array",
        "is_public" => "boolean",
        "size" => "integer",
        "download_count" => "integer",
    ];

    /**
     * 获取关联的用户
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 获取文件分类
     */
    public function fileCategory()
    {
        return $this->belongsTo(UserFileCategory::class, "category", "name")
            ->where("user_id", $this->user_id);
    }

    /**
     * 获取格式化的文件大小
     *
     * @return string
     */
    public function getFormattedSizeAttribute()
    {
        $bytes = $this->size;
        $units = ["B", "KB", "MB", "GB", "TB", "PB"];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . " " . $units[$i];
    }

    /**
     * 获取文件URL
     *
     * @return string
     */
    public function getUrlAttribute()
    {
        if ($this->is_public) {
            return url("storage/" . $this->path);
        }
        
        return route("user.files.download", $this->id);
    }

    /**
     * 获取文件图标
     *
     * @return string
     */
    public function getIconAttribute()
    {
        $extension = pathinfo($this->name, PATHINFO_EXTENSION);
        
        $iconMap = [
            "pdf" => "fa-file-pdf",
            "doc" => "fa-file-word",
            "docx" => "fa-file-word",
            "xls" => "fa-file-excel",
            "xlsx" => "fa-file-excel",
            "ppt" => "fa-file-powerpoint",
            "pptx" => "fa-file-powerpoint",
            "txt" => "fa-file-alt",
            "zip" => "fa-file-archive",
            "rar" => "fa-file-archive",
            "jpg" => "fa-file-image",
            "jpeg" => "fa-file-image",
            "png" => "fa-file-image",
            "gif" => "fa-file-image",
            "mp3" => "fa-file-audio",
            "wav" => "fa-file-audio",
            "mp4" => "fa-file-video",
            "avi" => "fa-file-video",
            "mov" => "fa-file-video",
        ];
        
        return isset($iconMap[$extension]) ? "fas " . $iconMap[$extension] : "fas fa-file";
    }

    /**
     * 增加下载次数
     *
     * @return void
     */
    public function incrementDownloadCount()
    {
        $this->increment("download_count");
    }

    /**
     * 作用域：按用户筛选
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where("user_id", $userId);
    }

    /**
     * 作用域：按分类筛选
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $category
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where("category", $category);
    }

    /**
     * 作用域：按文件类型筛选
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByType($query, $type)
    {
        return $query->where("type", $type);
    }

    /**
     * 作用域：公开文件
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublic($query)
    {
        return $query->where("is_public", true);
    }
}
