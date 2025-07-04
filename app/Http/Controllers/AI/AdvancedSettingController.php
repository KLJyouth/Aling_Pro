<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AI\AdvancedSetting;
use App\Models\AI\ModelProvider;
use App\Services\AI\AuditService;

class AdvancedSettingController extends Controller
{
    protected $auditService;
    
    /**
     * 构造函数
     *
     * @param AuditService $auditService
     */
    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }
    
    /**
     * 显示高级设置页面
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // 获取分组设置
        $apiKeySettings = AdvancedSetting::getGroupSettings("api_keys");
        $cachingSettings = AdvancedSetting::getGroupSettings("caching");
        $fallbackSettings = AdvancedSetting::getGroupSettings("fallback");
        $loggingSettings = AdvancedSetting::getGroupSettings("logging");
        
        return view("admin.ai.advanced_settings.index", [
            "apiKeySettings" => $apiKeySettings,
            "cachingSettings" => $cachingSettings,
            "fallbackSettings" => $fallbackSettings,
            "loggingSettings" => $loggingSettings,
            "providers" => ModelProvider::where("is_active", true)->get(),
        ]);
    }
    
    /**
     * 更新高级设置
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            // API密钥设置
            "enable_api_key_rotation" => "boolean",
            "rotation_strategy" => "required|in:round_robin,random,weighted",
            "enable_load_balancing" => "boolean",
            "load_balancing_strategy" => "required|in:least_used,percentage",
            
            // 缓存设置
            "enable_request_caching" => "boolean",
            "request_cache_ttl" => "required|integer|min:1",
            
            // 故障转移设置
            "enable_fallback" => "boolean",
            "fallback_provider" => "nullable|exists:ai_model_providers,id",
            
            // 日志设置
            "enable_detailed_logging" => "boolean",
            "log_retention_days" => "required|integer|min:1",
            "enable_audit_logging" => "boolean",
        ]);
        
        // 保存旧值用于审计
        $oldValues = [];
        foreach ($validated as $key => $value) {
            $oldValues[$key] = AdvancedSetting::getValue($key);
        }
        
        // 更新设置
        foreach ($validated as $key => $value) {
            AdvancedSetting::setValue($key, $value);
        }
        
        // 记录审计日志
        $this->auditService->logUpdate("advanced_settings", null, $oldValues, $validated);
        
        return redirect()->route("admin.ai.advanced-settings.index")
            ->with("success", "高级设置已更新");
    }
    
    /**
     * 重置高级设置为默认值
     *
     * @return \Illuminate\Http\Response
     */
    public function reset()
    {
        // 保存旧值用于审计
        $oldValues = [
            "enable_api_key_rotation" => AdvancedSetting::getValue("enable_api_key_rotation"),
            "rotation_strategy" => AdvancedSetting::getValue("rotation_strategy"),
            "enable_load_balancing" => AdvancedSetting::getValue("enable_load_balancing"),
            "load_balancing_strategy" => AdvancedSetting::getValue("load_balancing_strategy"),
            "enable_request_caching" => AdvancedSetting::getValue("enable_request_caching"),
            "request_cache_ttl" => AdvancedSetting::getValue("request_cache_ttl"),
            "enable_fallback" => AdvancedSetting::getValue("enable_fallback"),
            "fallback_provider" => AdvancedSetting::getValue("fallback_provider"),
            "enable_detailed_logging" => AdvancedSetting::getValue("enable_detailed_logging"),
            "log_retention_days" => AdvancedSetting::getValue("log_retention_days"),
            "enable_audit_logging" => AdvancedSetting::getValue("enable_audit_logging"),
        ];
        
        // 重置为默认值
        AdvancedSetting::setValue("enable_api_key_rotation", 0);
        AdvancedSetting::setValue("rotation_strategy", "round_robin");
        AdvancedSetting::setValue("enable_load_balancing", 0);
        AdvancedSetting::setValue("load_balancing_strategy", "least_used");
        AdvancedSetting::setValue("enable_request_caching", 0);
        AdvancedSetting::setValue("request_cache_ttl", 60);
        AdvancedSetting::setValue("enable_fallback", 0);
        AdvancedSetting::setValue("fallback_provider", "");
        AdvancedSetting::setValue("enable_detailed_logging", 1);
        AdvancedSetting::setValue("log_retention_days", 90);
        AdvancedSetting::setValue("enable_audit_logging", 1);
        
        // 获取新值用于审计
        $newValues = [
            "enable_api_key_rotation" => 0,
            "rotation_strategy" => "round_robin",
            "enable_load_balancing" => 0,
            "load_balancing_strategy" => "least_used",
            "enable_request_caching" => 0,
            "request_cache_ttl" => 60,
            "enable_fallback" => 0,
            "fallback_provider" => "",
            "enable_detailed_logging" => 1,
            "log_retention_days" => 90,
            "enable_audit_logging" => 1,
        ];
        
        // 记录审计日志
        $this->auditService->logUpdate("advanced_settings", null, $oldValues, $newValues);
        
        return redirect()->route("admin.ai.advanced-settings.index")
            ->with("success", "高级设置已重置为默认值");
    }
}
