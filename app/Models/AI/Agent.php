<?php

namespace App\Models\AI;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agent extends Model
{
    use SoftDeletes;

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = "ai_agents";

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        "provider_id",
        "model_id",
        "name",
        "type",
        "endpoint",
        "parameters",
        "description",
        "is_active",
    ];

    /**
     * 应该被转换成原生类型的属性
     *
     * @var array
     */
    protected $casts = [
        "is_active" => "boolean",
    ];

    /**
     * 获取关联的提供商
     */
    public function provider()
    {
        return $this->belongsTo(ModelProvider::class, "provider_id");
    }

    /**
     * 获取关联的模型
     */
    public function model()
    {
        return $this->belongsTo(AIModel::class, "model_id");
    }

    /**
     * 获取该智能体的显示样式
     */
    public function displayStyle()
    {
        return $this->morphOne(DisplayStyle::class, "styleable");
    }

    /**
     * 获取参数数组
     *
     * @return array
     */
    public function getParametersArrayAttribute()
    {
        return json_decode($this->parameters, true) ?: [];
    }

    /**
     * 获取格式化的名称（包含样式）
     *
     * @return string
     */
    public function getFormattedNameAttribute()
    {
        if (!$this->displayStyle) {
            return $this->name;
        }

        $style = $this->displayStyle->generateCss();
        $name = $this->name;
        $tooltip = $this->displayStyle->tooltip_content ? "data-toggle=\"tooltip\" title=\"{$this->displayStyle->tooltip_content}\"" : "";
        
        // 添加标签
        $badge = "";
        if ($this->displayStyle->badge_text) {
            $badgeColor = $this->displayStyle->badge_color ?: "primary";
            $badgeIcon = $this->displayStyle->badge_icon ? "<i class=\"{$this->displayStyle->badge_icon}\"></i> " : "";
            $badge = "<span class=\"badge badge-{$badgeColor} ml-1\">{$badgeIcon}{$this->displayStyle->badge_text}</span>";
        }
        
        // 添加提示图标
        $infoIcon = $this->displayStyle->tooltip_content ? "<i class=\"fas fa-info-circle ml-1 text-info\"></i>" : "";
        
        return "<span style=\"{$style}\" {$tooltip}>{$name}</span>{$badge}{$infoIcon}";
    }
}
