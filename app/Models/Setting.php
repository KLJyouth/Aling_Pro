<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 网站设置模型
 * 
 * 用于管理网站的各种设置
 */
class Setting extends Model
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'settings';

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'key',        // 设置键名
        'value',      // 设置值
        'group',      // 设置分组
        'type',       // 值类型：string, integer, boolean, array, json
        'description', // 设置描述
        'is_system',  // 是否为系统设置
    ];

    /**
     * 应该被转换为原生类型的属性
     *
     * @var array
     */
    protected $casts = [
        'is_system' => 'boolean',
    ];

    /**
     * 获取设置值（根据类型自动转换）
     *
     * @return mixed
     */
    public function getTypedValueAttribute()
    {
        switch ($this->type) {
            case 'boolean':
                return (bool) $this->value;
            case 'integer':
                return (int) $this->value;
            case 'float':
                return (float) $this->value;
            case 'array':
            case 'json':
                return json_decode($this->value, true);
            default:
                return $this->value;
        }
    }

    /**
     * 设置值（根据类型自动转换）
     *
     * @param mixed $value
     */
    public function setTypedValueAttribute($value)
    {
        switch ($this->type) {
            case 'array':
            case 'json':
                $this->attributes['value'] = json_encode($value);
                break;
            default:
                $this->attributes['value'] = (string) $value;
        }
    }

    /**
     * 根据键名获取设置
     *
     * @param string $key
     * @return Setting|null
     */
    public static function getByKey($key)
    {
        return static::where('key', $key)->first();
    }

    /**
     * 根据键名获取设置值
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getValue($key, $default = null)
    {
        $setting = static::getByKey($key);
        
        if (!$setting) {
            return $default;
        }
        
        return $setting->typed_value;
    }

    /**
     * 设置值
     *
     * @param string $key
     * @param mixed $value
     * @param string|null $group
     * @param string $type
     * @param string|null $description
     * @param bool $isSystem
     * @return Setting
     */
    public static function setValue($key, $value, $group = null, $type = 'string', $description = null, $isSystem = false)
    {
        $setting = static::getByKey($key);
        
        if (!$setting) {
            $setting = new static([
                'key' => $key,
                'group' => $group,
                'type' => $type,
                'description' => $description,
                'is_system' => $isSystem,
            ]);
        }
        
        $setting->typed_value = $value;
        $setting->save();
        
        return $setting;
    }

    /**
     * 根据分组获取设置
     *
     * @param string $group
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getByGroup($group)
    {
        return static::where('group', $group)->get();
    }

    /**
     * 获取所有分组
     *
     * @return array
     */
    public static function getAllGroups()
    {
        return static::distinct()->pluck('group')->toArray();
    }
} 