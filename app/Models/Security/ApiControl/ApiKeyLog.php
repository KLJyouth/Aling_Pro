<?php

namespace App\Models\Security\ApiControl;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiKeyLog extends Model
{
    use HasFactory;

    /**
     * 与模型关联的数据表
     *
     * @var string
     */
    protected $table = 'api_key_logs';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'api_key_id',
        'action',
        'description',
        'ip_address',
        'user_agent',
    ];

    /**
     * 获取关联的API密钥
     */
    public function apiKey()
    {
        return $this->belongsTo(ApiKey::class);
    }
} 