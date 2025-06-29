<?php

namespace App\Models\Security\ApiControl;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiSdkVersion extends Model
{
    use HasFactory;

    /**
     * 与模型关联的数据表
     *
     * @var string
     */
    protected $table = 'api_sdk_versions';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'api_sdk_id',
        'version',
        'file_path',
        'changelog',
        'is_current',
        'download_count',
    ];

    /**
     * 应该被转换为原生类型的属性
     *
     * @var array
     */
    protected $casts = [
        'is_current' => 'boolean',
    ];

    /**
     * 获取关联的SDK
     */
    public function sdk()
    {
        return $this->belongsTo(ApiSdk::class, 'api_sdk_id');
    }

    /**
     * 增加下载计数
     */
    public function incrementDownloadCount()
    {
        $this->increment('download_count');
    }
} 