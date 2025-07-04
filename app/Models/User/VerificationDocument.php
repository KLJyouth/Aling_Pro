<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class VerificationDocument extends Model
{
    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        "verification_id",
        "name",
        "path",
        "type",
        "mime_type",
        "size",
        "notes",
    ];

    /**
     * 应该被转换成原生类型的属性
     *
     * @var array
     */
    protected $casts = [
        "size" => "integer",
    ];

    /**
     * 文件类型列表
     *
     * @var array
     */
    public static $types = [
        "id_card" => "身份证",
        "business_license" => "营业执照",
        "organization_code" => "组织机构代码证",
        "tax_registration" => "税务登记证",
        "qualification_certificate" => "资质证书",
        "authorization_letter" => "授权书",
        "other" => "其他",
    ];

    /**
     * 获取关联的认证
     */
    public function verification()
    {
        return $this->belongsTo(UserVerification::class, "verification_id");
    }

    /**
     * 获取文件类型名称
     *
     * @return string
     */
    public function getTypeNameAttribute()
    {
        return self::$types[$this->type] ?? $this->type;
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
        return route("admin.users.verifications.documents.download", $this->id);
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
            "jpg" => "fa-file-image",
            "jpeg" => "fa-file-image",
            "png" => "fa-file-image",
        ];
        
        return isset($iconMap[$extension]) ? "fas " . $iconMap[$extension] : "fas fa-file";
    }
}
