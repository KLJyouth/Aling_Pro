<?php

namespace App\Models\AI;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class ApiKey extends Model
{
    use SoftDeletes;

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = "ai_api_keys";

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        "provider_id",
        "name",
        "key",
        "key_mask",
        "usage_count",
        "quota_limit",
        "description",
        "is_active",
        "last_used_at",
    ];

    /**
     * 应该被转换成原生类型的属性
     *
     * @var array
     */
    protected $casts = [
        "is_active" => "boolean",
        "usage_count" => "integer",
        "quota_limit" => "integer",
        "last_used_at" => "datetime",
    ];

    /**
     * 应该隐藏的属性
     *
     * @var array
     */
    protected $hidden = [
        "key",
    ];

    /**
     * 获取关联的提供商
     */
    public function provider()
    {
        return $this->belongsTo(ModelProvider::class, "provider_id");
    }

    /**
     * 获取API密钥的使用记录
     */
    public function usageRecords()
    {
        return $this->hasMany(ApiLog::class, "api_key_id");
    }

    /**
     * 设置API密钥
     *
     * @param string $value
     * @return void
     */
    public function setKeyAttribute($value)
    {
        if ($value) {
            $this->attributes["key"] = Crypt::encryptString($value);
            $this->attributes["key_mask"] = substr($value, 0, 3) . "..." . substr($value, -4);
        }
    }

    /**
     * 获取API密钥
     *
     * @return string|null
     */
    public function getDecryptedKeyAttribute()
    {
        if (!isset($this->attributes["key"])) {
            return null;
        }
        
        try {
            return Crypt::decryptString($this->attributes["key"]);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 增加使用次数
     *
     * @return bool
     */
    public function incrementUsage()
    {
        $this->usage_count++;
        $this->last_used_at = now();
        return $this->save();
    }

    /**
     * 检查是否超出配额
     *
     * @return bool
     */
    public function isQuotaExceeded()
    {
        return $this->quota_limit > 0 && $this->usage_count >= $this->quota_limit;
    }

    /**
     * 重置使用配额
     *
     * @return bool
     */
    public function resetQuota()
    {
        $this->usage_count = 0;
        return $this->save();
    }
}
