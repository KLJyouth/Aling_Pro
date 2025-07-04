<?php

namespace App\Models\OAuth;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class Provider extends Model
{
    use SoftDeletes;

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = "oauth_providers";

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        "name",
        "identifier",
        "icon",
        "description",
        "is_active",
        "client_id",
        "client_secret",
        "redirect_url",
        "auth_url",
        "token_url",
        "user_info_url",
        "scopes",
        "config",
    ];

    /**
     * 应该被转换成原生类型的属性
     *
     * @var array
     */
    protected $casts = [
        "is_active" => "boolean",
        "scopes" => "array",
        "config" => "array",
    ];

    /**
     * 应该被隐藏的属性
     *
     * @var array
     */
    protected $hidden = [
        "client_secret",
    ];

    /**
     * 获取该提供商的用户账号
     */
    public function userAccounts()
    {
        return $this->hasMany(UserAccount::class, "provider_id");
    }

    /**
     * 获取该提供商的日志
     */
    public function logs()
    {
        return $this->hasMany(OAuthLog::class, "provider_id");
    }

    /**
     * 设置客户端密钥
     *
     * @param string $value
     * @return void
     */
    public function setClientSecretAttribute($value)
    {
        if ($value) {
            $this->attributes["client_secret"] = Crypt::encryptString($value);
        }
    }

    /**
     * 获取客户端密钥
     *
     * @return string|null
     */
    public function getDecryptedClientSecretAttribute()
    {
        if (!isset($this->attributes["client_secret"])) {
            return null;
        }
        
        try {
            return Crypt::decryptString($this->attributes["client_secret"]);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 获取授权URL
     *
     * @param array $params 额外参数
     * @return string
     */
    public function getAuthorizationUrl(array $params = [])
    {
        $defaultParams = [
            "client_id" => $this->client_id,
            "redirect_uri" => $this->redirect_url,
            "response_type" => "code",
            "scope" => implode(",", $this->scopes ?: []),
            "state" => csrf_token(),
        ];

        $queryParams = array_merge($defaultParams, $params);
        $queryString = http_build_query($queryParams);

        return $this->auth_url . "?" . $queryString;
    }
}
