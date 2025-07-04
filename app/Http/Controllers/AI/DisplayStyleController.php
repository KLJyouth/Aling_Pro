<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AI\DisplayStyleService;
use App\Services\AI\AuditService;
use App\Models\AI\ModelProvider;
use App\Models\AI\AIModel;
use App\Models\AI\Agent;

class DisplayStyleController extends Controller
{
    protected $styleService;
    protected $auditService;
    
    /**
     * 构造函数
     *
     * @param DisplayStyleService $styleService
     * @param AuditService $auditService
     */
    public function __construct(DisplayStyleService $styleService, AuditService $auditService)
    {
        $this->styleService = $styleService;
        $this->auditService = $auditService;
    }
    
    /**
     * 获取预定义样式列表
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPredefinedStyles()
    {
        $styles = $this->styleService->getPredefinedStyles();
        return response()->json(["success" => true, "data" => $styles]);
    }
    
    /**
     * 更新提供商显示样式
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProviderStyle(Request $request, $id)
    {
        $provider = ModelProvider::findOrFail($id);
        
        $validated = $request->validate([
            "font_family" => "nullable|string|max:255",
            "font_color" => "nullable|string|max:50",
            "gradient_start" => "nullable|string|max:50",
            "gradient_end" => "nullable|string|max:50",
            "is_animated" => "boolean",
            "badge_text" => "nullable|string|max:50",
            "badge_color" => "nullable|string|max:50",
            "badge_icon" => "nullable|string|max:50",
            "tooltip_content" => "nullable|string",
            "predefined_style" => "nullable|string",
        ]);
        
        // 如果选择了预定义样式
        if (!empty($validated["predefined_style"])) {
            $style = $this->styleService->applyPredefinedStyle($provider, $validated["predefined_style"]);
            
            // 如果提供了tooltip_content，则更新它
            if (isset($validated["tooltip_content"])) {
                $style->tooltip_content = $validated["tooltip_content"];
                $style->save();
            }
        } else {
            // 移除predefined_style字段
            if (isset($validated["predefined_style"])) {
                unset($validated["predefined_style"]);
            }
            
            $style = $this->styleService->updateOrCreateStyle($provider, $validated);
        }
        
        // 记录审计日志
        $this->auditService->logUpdate("provider_style", $provider->id, 
            $provider->displayStyle ? $provider->displayStyle->toArray() : [], 
            $style->toArray()
        );
        
        return response()->json([
            "success" => true,
            "message" => "提供商显示样式已更新",
            "data" => $style
        ]);
    }
    
    /**
     * 更新模型显示样式
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateModelStyle(Request $request, $id)
    {
        $model = AIModel::findOrFail($id);
        
        $validated = $request->validate([
            "font_family" => "nullable|string|max:255",
            "font_color" => "nullable|string|max:50",
            "gradient_start" => "nullable|string|max:50",
            "gradient_end" => "nullable|string|max:50",
            "is_animated" => "boolean",
            "badge_text" => "nullable|string|max:50",
            "badge_color" => "nullable|string|max:50",
            "badge_icon" => "nullable|string|max:50",
            "tooltip_content" => "nullable|string",
            "predefined_style" => "nullable|string",
        ]);
        
        // 如果选择了预定义样式
        if (!empty($validated["predefined_style"])) {
            $style = $this->styleService->applyPredefinedStyle($model, $validated["predefined_style"]);
            
            // 如果提供了tooltip_content，则更新它
            if (isset($validated["tooltip_content"])) {
                $style->tooltip_content = $validated["tooltip_content"];
                $style->save();
            }
        } else {
            // 移除predefined_style字段
            if (isset($validated["predefined_style"])) {
                unset($validated["predefined_style"]);
            }
            
            $style = $this->styleService->updateOrCreateStyle($model, $validated);
        }
        
        // 记录审计日志
        $this->auditService->logUpdate("model_style", $model->id, 
            $model->displayStyle ? $model->displayStyle->toArray() : [], 
            $style->toArray()
        );
        
        return response()->json([
            "success" => true,
            "message" => "模型显示样式已更新",
            "data" => $style
        ]);
    }
    
    /**
     * 更新智能体显示样式
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAgentStyle(Request $request, $id)
    {
        $agent = Agent::findOrFail($id);
        
        $validated = $request->validate([
            "font_family" => "nullable|string|max:255",
            "font_color" => "nullable|string|max:50",
            "gradient_start" => "nullable|string|max:50",
            "gradient_end" => "nullable|string|max:50",
            "is_animated" => "boolean",
            "badge_text" => "nullable|string|max:50",
            "badge_color" => "nullable|string|max:50",
            "badge_icon" => "nullable|string|max:50",
            "tooltip_content" => "nullable|string",
            "predefined_style" => "nullable|string",
        ]);
        
        // 如果选择了预定义样式
        if (!empty($validated["predefined_style"])) {
            $style = $this->styleService->applyPredefinedStyle($agent, $validated["predefined_style"]);
            
            // 如果提供了tooltip_content，则更新它
            if (isset($validated["tooltip_content"])) {
                $style->tooltip_content = $validated["tooltip_content"];
                $style->save();
            }
        } else {
            // 移除predefined_style字段
            if (isset($validated["predefined_style"])) {
                unset($validated["predefined_style"]);
            }
            
            $style = $this->styleService->updateOrCreateStyle($agent, $validated);
        }
        
        // 记录审计日志
        $this->auditService->logUpdate("agent_style", $agent->id, 
            $agent->displayStyle ? $agent->displayStyle->toArray() : [], 
            $style->toArray()
        );
        
        return response()->json([
            "success" => true,
            "message" => "智能体显示样式已更新",
            "data" => $style
        ]);
    }
    
    /**
     * 删除显示样式
     *
     * @param Request $request
     * @param string $type
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteStyle(Request $request, $type, $id)
    {
        $model = null;
        
        switch ($type) {
            case "provider":
                $model = ModelProvider::findOrFail($id);
                break;
            case "model":
                $model = AIModel::findOrFail($id);
                break;
            case "agent":
                $model = Agent::findOrFail($id);
                break;
            default:
                return response()->json([
                    "success" => false,
                    "message" => "无效的类型"
                ], 400);
        }
        
        if ($model->displayStyle) {
            $oldStyle = $model->displayStyle->toArray();
            $model->displayStyle->delete();
            
            // 记录审计日志
            $this->auditService->logDelete("{$type}_style", $id, $oldStyle);
            
            return response()->json([
                "success" => true,
                "message" => "显示样式已删除"
            ]);
        }
        
        return response()->json([
            "success" => false,
            "message" => "未找到显示样式"
        ], 404);
    }
}
