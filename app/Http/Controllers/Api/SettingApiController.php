<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SettingService;
use App\Models\Setting;
use Illuminate\Support\Facades\Validator;

/**
 * 网站设置API控制器
 * 
 * 处理网站设置相关的API请求
 */
class SettingApiController extends Controller
{
    /**
     * 设置服务
     *
     * @var SettingService
     */
    protected $settingService;
    
    /**
     * 构造函数
     *
     * @param SettingService $settingService
     */
    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
        $this->middleware("auth:api")->except(["getPublicSettings"]);
        $this->middleware("role:admin")->except(["getPublicSettings"]);
    }
    
    /**
     * 获取所有设置
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $settings = Setting::orderBy("group")->orderBy("key")->get();
        
        return response()->json([
            "status" => "success",
            "data" => $settings
        ]);
    }
    
    /**
     * 获取指定设置
     *
     * @param string $key
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($key)
    {
        $setting = Setting::where("key", $key)->first();
        
        if (!$setting) {
            return response()->json([
                "status" => "error",
                "message" => "设置不存在"
            ], 404);
        }
        
        return response()->json([
            "status" => "success",
            "data" => $setting
        ]);
    }
}
    /**
     * 更新设置
     *
     * @param Request $request
     * @param string $key
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $key)
    {
        $setting = Setting::where("key", $key)->first();
        
        if (!$setting) {
            return response()->json([
                "status" => "error",
                "message" => "设置不存在"
            ], 404);
        }
        
        // 验证请求
        $validator = Validator::make($request->all(), [
            "value" => "nullable",
            "group" => "sometimes|required|string|max:255",
            "type" => "sometimes|required|in:string,integer,float,boolean,array,json",
            "description" => "sometimes|nullable|string|max:255",
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                "status" => "error",
                "message" => "验证失败",
                "errors" => $validator->errors()
            ], 422);
        }
        
        // 处理值
        $value = $request->input("value");
        
        switch ($setting->type) {
            case "integer":
                $value = (int) $value;
                break;
            case "float":
                $value = (float) $value;
                break;
            case "boolean":
                $value = (bool) $value;
                break;
            case "array":
            case "json":
                if (is_string($value)) {
                    $value = json_decode($value, true) ?: [];
                }
                break;
        }
        
        // 更新设置
        $this->settingService->set(
            $key,
            $value,
            $request->input("group", $setting->group),
            $request->input("type", $setting->type),
            $request->input("description", $setting->description),
            $setting->is_system
        );
        
        return response()->json([
            "status" => "success",
            "message" => "设置更新成功",
            "data" => Setting::where("key", $key)->first()
        ]);
    }
    
    /**
     * 创建新设置
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // 验证请求
        $validator = Validator::make($request->all(), [
            "key" => "required|string|max:255|unique:settings,key",
            "value" => "nullable",
            "group" => "required|string|max:255",
            "type" => "required|in:string,integer,float,boolean,array,json",
            "description" => "nullable|string|max:255",
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                "status" => "error",
                "message" => "验证失败",
                "errors" => $validator->errors()
            ], 422);
        }
        
        // 处理值
        $value = $request->input("value");
        
        switch ($request->input("type")) {
            case "integer":
                $value = (int) $value;
                break;
            case "float":
                $value = (float) $value;
                break;
            case "boolean":
                $value = (bool) $value;
                break;
            case "array":
            case "json":
                if (is_string($value)) {
                    $value = json_decode($value, true) ?: [];
                }
                break;
        }
        
        // 创建设置
        $setting = $this->settingService->set(
            $request->input("key"),
            $value,
            $request->input("group"),
            $request->input("type"),
            $request->input("description"),
            false
        );
        
        return response()->json([
            "status" => "success",
            "message" => "设置创建成功",
            "data" => $setting
        ], 201);
    }
    
    /**
     * 删除设置
     *
     * @param string $key
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($key)
    {
        $setting = Setting::where("key", $key)->first();
        
        if (!$setting) {
            return response()->json([
                "status" => "error",
                "message" => "设置不存在"
            ], 404);
        }
        
        // 系统设置不允许删除
        if ($setting->is_system) {
            return response()->json([
                "status" => "error",
                "message" => "系统设置不允许删除"
            ], 403);
        }
        
        $this->settingService->delete($key);
        
        return response()->json([
            "status" => "success",
            "message" => "设置删除成功"
        ]);
    }
    
    /**
     * 获取分组设置
     *
     * @param string $group
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGroup($group)
    {
        $settings = Setting::where("group", $group)->orderBy("key")->get();
        
        if ($settings->isEmpty()) {
            return response()->json([
                "status" => "error",
                "message" => "设置分组不存在"
            ], 404);
        }
        
        return response()->json([
            "status" => "success",
            "data" => $settings
        ]);
    }
    
    /**
     * 更新分组设置
     *
     * @param Request $request
     * @param string $group
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateGroup(Request $request, $group)
    {
        $settings = Setting::where("group", $group)->get();
        
        if ($settings->isEmpty()) {
            return response()->json([
                "status" => "error",
                "message" => "设置分组不存在"
            ], 404);
        }
        
        $rules = [];
        
        // 构建验证规则
        foreach ($settings as $setting) {
            switch ($setting->type) {
                case "integer":
                    $rules[$setting->key] = "nullable|integer";
                    break;
                case "float":
                    $rules[$setting->key] = "nullable|numeric";
                    break;
                case "boolean":
                    $rules[$setting->key] = "nullable|boolean";
                    break;
                case "array":
                case "json":
                    $rules[$setting->key] = "nullable|array";
                    break;
                default:
                    $rules[$setting->key] = "nullable|string";
            }
        }
        
        // 验证请求
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            return response()->json([
                "status" => "error",
                "message" => "验证失败",
                "errors" => $validator->errors()
            ], 422);
        }
        
        // 保存设置
        $data = $request->only(array_keys($rules));
        $this->settingService->saveGroup($group, $data);
        
        return response()->json([
            "status" => "success",
            "message" => "设置保存成功",
            "data" => Setting::where("group", $group)->get()
        ]);
    }
    
    /**
     * 清除设置缓存
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function clearCache()
    {
        $this->settingService->clearCache();
        
        return response()->json([
            "status" => "success",
            "message" => "设置缓存已清除"
        ]);
    }
    
    /**
     * 初始化系统设置
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function initSystemSettings()
    {
        $this->settingService->initSystemSettings();
        
        return response()->json([
            "status" => "success",
            "message" => "系统设置已初始化"
        ]);
    }
    
    /**
     * 获取公共设置
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPublicSettings()
    {
        $publicGroups = ["general", "contact", "social"];
        $settings = Setting::whereIn("group", $publicGroups)->get();
        
        $result = [];
        foreach ($settings as $setting) {
            $result[$setting->key] = $setting->typed_value;
        }
        
        return response()->json([
            "status" => "success",
            "data" => $result
        ]);
    }
}
