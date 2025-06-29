<?php

namespace App\Models\Security\ApiControl;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiInterfaceResponse extends Model
{
    use HasFactory;

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'api_interface_responses';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'api_interface_id',
        'name',
        'description',
        'status_code',
        'content_type',
        'schema',
        'example',
    ];

    /**
     * 应该被转换成原生类型的属性
     *
     * @var array
     */
    protected $casts = [
        'schema' => 'json',
    ];

    /**
     * 获取所属接口
     */
    public function interface()
    {
        return $this->belongsTo(ApiInterface::class, 'api_interface_id');
    }
    
    /**
     * 获取状态码类型
     *
     * @return string
     */
    public function getStatusTypeAttribute()
    {
        if ($this->status_code >= 200 && $this->status_code < 300) {
            return 'success';
        } elseif ($this->status_code >= 300 && $this->status_code < 400) {
            return 'redirect';
        } elseif ($this->status_code >= 400 && $this->status_code < 500) {
            return 'client_error';
        } else {
            return 'server_error';
        }
    }
    
    /**
     * 获取格式化的示例
     *
     * @return mixed
     */
    public function getFormattedExampleAttribute()
    {
        if ($this->content_type == 'application/json') {
            return json_decode($this->example, true);
        }
        
        return $this->example;
    }
} 