<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;

class UserCredential extends Model
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
        "identifier",
        "secret",
        "metadata",
        "is_primary",
        "is_active",
        "last_used_at",
    ];

    /**
     * 应该被转换成原生类型的属性
     *
     * @var array
     */
    protected $casts = [
        "metadata" => "array",
        "is_primary" => "boolean",
        "is_active" => "boolean",
        "last_used_at" => "datetime",
    ];

    /**
     * 应该被隐藏的属性
     *
     * @var array
     */
    protected $hidden = [
        "secret",
    ];

    /**
     * 凭证类型列表
     *
     * @var array
     */
    public static $types = [
        "totp" => "TOTP双因素认证",
        "webauthn" => "WebAuthn认证器",
        "recovery_code" => "恢复码",
        "trusted_device" => "受信任设备",
    ];

    /**
     * 获取关联的用户
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 获取凭证类型名称
     *
     * @return string
     */
    public function getTypeNameAttribute()
    {
        return self::$types[$this->type] ?? $this->type;
    }

    /**
     * 设置密钥
     *
     * @param string $value
     * @return void
     */
    public function setSecretAttribute($value)
    {
        if ($value) {
            $this->attributes["secret"] = Crypt::encryptString($value);
        }
    }

    /**
     * 获取解密的密钥
     *
     * @return string|null
     */
    public function getDecryptedSecretAttribute()
    {
        if (!isset($this->attributes["secret"])) {
            return null;
        }
        
        try {
            return Crypt::decryptString($this->attributes["secret"]);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 获取凭证图标
     *
     * @return string
     */
    public function getIconAttribute()
    {
        $icons = [
            "totp" => "fas fa-mobile-alt",
            "webauthn" => "fas fa-key",
            "recovery_code" => "fas fa-life-ring",
            "trusted_device" => "fas fa-laptop",
        ];
        
        return $icons[$this->type] ?? "fas fa-shield-alt";
    }

    /**
     * 记录使用
     *
     * @return void
     */
    public function recordUsage()
    {
        $this->update([
            "last_used_at" => now(),
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
     * 作用域：活跃凭证
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where("is_active", true);
    }

    /**
     * 作用域：主要凭证
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePrimary($query)
    {
        return $query->where("is_primary", true);
    }
}
