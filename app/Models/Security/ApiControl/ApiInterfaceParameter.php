<?php

namespace App\Models\Security\ApiControl;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiInterfaceParameter extends Model
{
    use HasFactory;

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'api_interface_parameters';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'api_interface_id',
        'name',
        'type',
        'in',
        'description',
        'required',
        'default',
        'example',
        'enum',
        'format',
        'pattern',
        'min_length',
        'max_length',
        'min_value',
        'max_value',
    ];

    /**
     * 应该被转换成原生类型的属性
     *
     * @var array
     */
    protected $casts = [
        'required' => 'boolean',
        'enum' => 'json',
    ];

    /**
     * 获取所属接口
     */
    public function interface()
    {
        return $this->belongsTo(ApiInterface::class, 'api_interface_id');
    }
    
    /**
     * 获取参数位置名称
     *
     * @return string
     */
    public function getInNameAttribute()
    {
        $locations = [
            'query' => '查询参数',
            'path' => '路径参数',
            'body' => '请求体',
            'header' => '请求头',
            'cookie' => 'Cookie',
        ];
        
        return $locations[$this->in] ?? $this->in;
    }
    
    /**
     * 获取参数类型名称
     *
     * @return string
     */
    public function getTypeNameAttribute()
    {
        $types = [
            'string' => '字符串',
            'integer' => '整数',
            'number' => '数字',
            'boolean' => '布尔值',
            'array' => '数组',
            'object' => '对象',
            'file' => '文件',
        ];
        
        return $types[$this->type] ?? $this->type;
    }
} 