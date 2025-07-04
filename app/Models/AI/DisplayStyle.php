<?php

namespace App\Models\AI;

use Illuminate\Database\Eloquent\Model;

class DisplayStyle extends Model
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = "ai_display_styles";

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        "font_family",
        "font_color",
        "gradient_start",
        "gradient_end",
        "is_animated",
        "badge_text",
        "badge_color",
        "badge_icon",
        "tooltip_content",
    ];

    /**
     * 应该被转换成原生类型的属性
     *
     * @var array
     */
    protected $casts = [
        "is_animated" => "boolean",
    ];

    /**
     * 获取拥有此样式的模型
     */
    public function styleable()
    {
        return $this->morphTo();
    }

    /**
     * 生成CSS样式
     *
     * @return string
     */
    public function generateCss()
    {
        $css = [];

        if ($this->font_family) {
            $css[] = "font-family: {$this->font_family}";
        }

        if ($this->font_color) {
            $css[] = "color: {$this->font_color}";
        }

        if ($this->gradient_start && $this->gradient_end) {
            $css[] = "background: linear-gradient(135deg, {$this->gradient_start} 0%, {$this->gradient_end} 100%)";
            $css[] = "-webkit-background-clip: text";
            $css[] = "-webkit-text-fill-color: transparent";
        }

        if ($this->is_animated) {
            $css[] = "animation: pulse 2s infinite";
        }

        return implode("; ", $css);
    }
}
