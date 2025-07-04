<?php

namespace App\Services\AI;

use App\Models\AI\DisplayStyle;

class DisplayStyleService
{
    /**
     * 创建或更新显示样式
     *
     * @param mixed $model 模型实例
     * @param array $data 样式数据
     * @return DisplayStyle
     */
    public function updateOrCreateStyle($model, array $data)
    {
        if ($model->displayStyle) {
            $model->displayStyle->update($data);
            return $model->displayStyle;
        } else {
            return $model->displayStyle()->create($data);
        }
    }
    
    /**
     * 获取预定义样式列表
     *
     * @return array
     */
    public function getPredefinedStyles()
    {
        return [
            "default" => [
                "name" => "默认",
                "font_family" => "",
                "font_color" => "",
                "gradient_start" => "",
                "gradient_end" => "",
                "is_animated" => false,
                "badge_text" => "",
                "badge_color" => "",
                "badge_icon" => "",
            ],
            "pro" => [
                "name" => "专业版",
                "font_family" => "\"Segoe UI\", Arial, sans-serif",
                "font_color" => "",
                "gradient_start" => "#4b6cb7",
                "gradient_end" => "#182848",
                "is_animated" => false,
                "badge_text" => "Pro",
                "badge_color" => "primary",
                "badge_icon" => "fas fa-star",
            ],
            "max" => [
                "name" => "MAX",
                "font_family" => "\"Segoe UI\", Arial, sans-serif",
                "font_color" => "",
                "gradient_start" => "#FF416C",
                "gradient_end" => "#FF4B2B",
                "is_animated" => true,
                "badge_text" => "MAX",
                "badge_color" => "danger",
                "badge_icon" => "fas fa-bolt",
            ],
            "plus" => [
                "name" => "Plus",
                "font_family" => "\"Segoe UI\", Arial, sans-serif",
                "font_color" => "",
                "gradient_start" => "#11998e",
                "gradient_end" => "#38ef7d",
                "is_animated" => false,
                "badge_text" => "Plus",
                "badge_color" => "success",
                "badge_icon" => "fas fa-plus",
            ],
            "lite" => [
                "name" => "轻量版",
                "font_family" => "\"Segoe UI\", Arial, sans-serif",
                "font_color" => "",
                "gradient_start" => "#3498db",
                "gradient_end" => "#2980b9",
                "is_animated" => false,
                "badge_text" => "Lite",
                "badge_color" => "info",
                "badge_icon" => "fas fa-feather",
            ],
            "beta" => [
                "name" => "测试版",
                "font_family" => "\"Segoe UI\", Arial, sans-serif",
                "font_color" => "",
                "gradient_start" => "#9b59b6",
                "gradient_end" => "#8e44ad",
                "is_animated" => false,
                "badge_text" => "Beta",
                "badge_color" => "warning",
                "badge_icon" => "fas fa-flask",
            ],
            "experimental" => [
                "name" => "实验性",
                "font_family" => "\"Segoe UI\", Arial, sans-serif",
                "font_color" => "",
                "gradient_start" => "#f39c12",
                "gradient_end" => "#e67e22",
                "is_animated" => true,
                "badge_text" => "实验性",
                "badge_color" => "warning",
                "badge_icon" => "fas fa-vial",
            ],
        ];
    }
    
    /**
     * 应用预定义样式
     *
     * @param mixed $model 模型实例
     * @param string $styleName 样式名称
     * @return DisplayStyle|null
     */
    public function applyPredefinedStyle($model, $styleName)
    {
        $predefinedStyles = $this->getPredefinedStyles();
        
        if (!isset($predefinedStyles[$styleName])) {
            return null;
        }
        
        $styleData = $predefinedStyles[$styleName];
        unset($styleData["name"]); // 移除名称，只保留样式属性
        
        return $this->updateOrCreateStyle($model, $styleData);
    }
}
