<?php

namespace App\Models\OAuth;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;

class UserAccount extends Model
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = "oauth_user_accounts";

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        "user_id",
        "provider_id",
        "provider_user_id",
        "nickname",
        "name",
        "email",
        "avatar",
        "access_token",
        "refresh_token",
        "token_expires_at",
        "user_data",
    ];

    /**
     * 应该被转换成原生类型的属性
     *
     * @var array
     */
    protected $casts = [
        "token_expires_at" => "datetime",
        "user_data" => "array",
    ];

    /**
     * 应该被隐藏的属性
     *
     * @var array
     */
    protected $hidden = [
        "access_token",
        "refresh_token",
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
     * 设置访问令牌
     *
     * @param string $value
     * @return void
     */
    public function setAccessTokenAttribute($value)
    {
        if ($value) {
            $this->attributes["access_token"] = Crypt::encryptString($value);
        }
    }

    /**
     * 获取访问令牌
     *
     * @return string|null
     */
    public function getDecryptedAccessTokenAttribute()
    {
        if (!isset($this->attributes["access_token"])) {
            return null;
        }
        
        try {
            return Crypt::decryptString($this->attributes["access_token"]);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 设置刷新令牌
     *
     * @param string $value
     * @return void
     */
    public function setRefreshTokenAttribute($value)
    {
        if ($value) {
            $this->attributes["refresh_token"] = Crypt::encryptString($value);
        }
    }

    /**
     * 获取刷新令牌
     *
     * @return string|null
     */
    public function getDecryptedRefreshTokenAttribute()
    {
        if (!isset($this->attributes["refresh_token"])) {
            return null;
        }
        
        try {
            return Crypt::decryptString($this->attributes["refresh_token"]);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 令牌是否过期
     *
     * @return bool
     */
    public function isTokenExpired()
    {
        return $this->token_expires_at && $this->token_expires_at->isPast();
    }
}
