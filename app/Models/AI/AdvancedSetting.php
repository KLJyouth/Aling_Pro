<?php

namespace App\Models\AI;

use Illuminate\Database\Eloquent\Model;

class AdvancedSetting extends Model
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = "ai_advanced_settings";

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        "key",
        "value",
        "group",
        "description",
    ];

    /**
     * 获取设置值（自动转换类型）
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getValue($key, $default = null)
    {
        $setting = static::where("key", $key)->first();
        
        if (!$setting) {
            return $default;
        }
        
        $value = $setting->value;
        
        // 自动转换类型
        if (is_numeric($value)) {
            return strpos($value, ".") !== false ? (float)$value : (int)$value;
        } elseif ($value === "true" || $value === "false") {
            return $value === "true";
        } elseif ($value === "null") {
            return null;
        } elseif (substr($value, 0, 1) === "{" || substr($value, 0, 1) === "[") {
            // 尝试解析JSON
            $decoded = json_decode($value, true);
            return $decoded !== null ? $decoded : $value;
        }
        
        return $value;
    }

    /**
     * 设置设置值
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public static function setValue($key, $value)
    {
        // 将值转换为字符串
        if (is_array($value) || is_object($value)) {
            $value = json_encode($value);
        } elseif (is_bool($value)) {
            $value = $value ? "true" : "false";
        } elseif ($value === null) {
            $value = "null";
        } else {
            $value = (string)$value;
        }
        
        return static::updateOrCreate(
            ["key" => $key],
            ["value" => $value]
        ) ? true : false;
    }

    /**
     * 获取分组设置
     *
     * @param string $group
     * @return array
     */
    public static function getGroupSettings($group)
    {
        $settings = static::where("group", $group)->get();
        $result = [];
        
        foreach ($settings as $setting) {
            $result[$setting->key] = static::getValue($setting->key);
        }
        
        return $result;
    }
}
