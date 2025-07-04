<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class UserVerification extends Model
{
    use SoftDeletes;

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        "user_id",
        "type",
        "status",
        "name",
        "identifier",
        "contact_name",
        "contact_phone",
        "contact_email",
        "description",
        "documents",
        "rejection_reason",
        "verified_by",
        "verified_at",
    ];

    /**
     * 应该被转换成原生类型的属性
     *
     * @var array
     */
    protected $casts = [
        "documents" => "array",
        "verified_at" => "datetime",
    ];

    /**
     * 认证类型列表
     *
     * @var array
     */
    public static $types = [
        "personal" => "个人认证",
        "business" => "企业认证",
        "team" => "团队认证",
        "government" => "政府机构认证",
        "education" => "教育机构认证",
    ];

    /**
     * 状态列表
     *
     * @var array
     */
    public static $statuses = [
        "pending" => "待审核",
        "approved" => "已通过",
        "rejected" => "已拒绝",
    ];

    /**
     * 获取关联的用户
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 获取关联的审核人
     */
    public function verifier()
    {
        return $this->belongsTo(User::class, "verified_by");
    }

    /**
     * 获取认证文件
     */
    public function verificationDocuments()
    {
        return $this->hasMany(VerificationDocument::class, "verification_id");
    }

    /**
     * 获取认证类型名称
     *
     * @return string
     */
    public function getTypeNameAttribute()
    {
        return self::$types[$this->type] ?? $this->type;
    }

    /**
     * 获取状态名称
     *
     * @return string
     */
    public function getStatusNameAttribute()
    {
        return self::$statuses[$this->status] ?? $this->status;
    }

    /**
     * 获取状态标签HTML
     *
     * @return string
     */
    public function getStatusLabelAttribute()
    {
        $class = [
            "pending" => "warning",
            "approved" => "success",
            "rejected" => "danger",
        ][$this->status] ?? "secondary";
        
        return "<span class=\"badge badge-{$class}\">{$this->status_name}</span>";
    }

    /**
     * 获取认证图标
     *
     * @return string
     */
    public function getIconAttribute()
    {
        $icons = [
            "personal" => "fas fa-user",
            "business" => "fas fa-building",
            "team" => "fas fa-users",
            "government" => "fas fa-landmark",
            "education" => "fas fa-graduation-cap",
        ];
        
        return $icons[$this->type] ?? "fas fa-certificate";
    }

    /**
     * 审核通过
     *
     * @param int $verifierId
     * @return bool
     */
    public function approve($verifierId)
    {
        return $this->update([
            "status" => "approved",
            "verified_by" => $verifierId,
            "verified_at" => now(),
            "rejection_reason" => null,
        ]);
    }

    /**
     * 审核拒绝
     *
     * @param int $verifierId
     * @param string $reason
     * @return bool
     */
    public function reject($verifierId, $reason)
    {
        return $this->update([
            "status" => "rejected",
            "verified_by" => $verifierId,
            "verified_at" => now(),
            "rejection_reason" => $reason,
        ]);
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
     * 作用域：按类型筛选
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
     * 作用域：按状态筛选
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where("status", $status);
    }

    /**
     * 作用域：待审核
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where("status", "pending");
    }

    /**
     * 作用域：已通过
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApproved($query)
    {
        return $query->where("status", "approved");
    }

    /**
     * 作用域：已拒绝
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRejected($query)
    {
        return $query->where("status", "rejected");
    }
}
