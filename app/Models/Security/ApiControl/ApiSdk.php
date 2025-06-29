<?php

namespace App\Models\Security\ApiControl;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApiSdk extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * 与模型关联的数据表
     *
     * @var string
     */
    protected $table = 'api_sdks';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
        'language',
        'description',
        'options',
        'status',
    ];

    /**
     * 应该被转换为原生类型的属性
     *
     * @var array
     */
    protected $casts = [
        'options' => 'json',
    ];

    /**
     * 获取关联的API接口
     */
    public function interfaces()
    {
        return $this->belongsToMany(
            ApiInterface::class,
            'api_sdk_interfaces',
            'api_sdk_id',
            'api_interface_id'
        );
    }

    /**
     * 获取关联的SDK版本
     */
    public function versions()
    {
        return $this->hasMany(ApiSdkVersion::class, 'api_sdk_id');
    }

    /**
     * 获取当前版本
     */
    public function currentVersion()
    {
        return $this->hasOne(ApiSdkVersion::class, 'api_sdk_id')
            ->where('is_current', true);
    }

    /**
     * 获取语言显示名称
     *
     * @return string
     */
    public function getLanguageNameAttribute()
    {
        $languages = [
            'php' => 'PHP',
            'python' => 'Python',
            'javascript' => 'JavaScript',
            'java' => 'Java',
            'csharp' => 'C#',
            'go' => 'Go',
        ];

        return $languages[$this->language] ?? $this->language;
    }

    /**
     * 获取状态显示名称
     *
     * @return string
     */
    public function getStatusNameAttribute()
    {
        $statuses = [
            'active' => '启用',
            'inactive' => '禁用',
        ];

        return $statuses[$this->status] ?? $this->status;
    }
} 