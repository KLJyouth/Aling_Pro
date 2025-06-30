<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ApiKeyController extends Controller
{
    /**
     * 创建新的控制器实例
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware("auth");
    }

    /**
     * 显示API密钥管理页面
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        
        // 获取用户的API密钥
        $apiKeys = $user->apiKeys()->orderBy("created_at", "desc")->get();
        
        // 获取API使用统计
        $apiUsageStats = $this->getApiUsageStats($user);
        
        return view("api.index", compact("user", "apiKeys", "apiUsageStats"));
    }

    /**
     * 创建新的API密钥
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:100",
            "expires_at" => "nullable|date|after:today",
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $user = Auth::user();
        
        try {
            // 生成API密钥
            $apiKey = new ApiKey();
            $apiKey->user_id = $user->id;
            $apiKey->name = $request->name;
            $apiKey->api_key = $this->generateApiKey();
            $apiKey->status = "active";
            $apiKey->expires_at = $request->filled("expires_at") ? $request->expires_at : null;
            $apiKey->save();
            
            // 记录日志
            Log::info("API密钥创建成功", [
                "user_id" => $user->id,
                "api_key_id" => $apiKey->id
            ]);
            
            return redirect()->route("api-keys")->with([
                "success" => "API密钥创建成功",
                "new_api_key" => $apiKey->api_key
            ]);
        } catch (\Exception $e) {
            Log::error("API密钥创建失败", [
                "error" => $e->getMessage(),
                "user_id" => $user->id
            ]);
            
            return redirect()->back()->with("error", "API密钥创建失败，请稍后再试。");
        }
    }

    /**
     * 更新API密钥
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:100",
            "status" => "required|in:active,inactive",
            "expires_at" => "nullable|date|after:today",
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $user = Auth::user();
        
        // 获取API密钥
        $apiKey = ApiKey::where("id", $id)
            ->where("user_id", $user->id)
            ->first();
        
        if (!$apiKey) {
            return redirect()->back()->with("error", "未找到API密钥。");
        }
        
        try {
            // 更新API密钥
            $apiKey->name = $request->name;
            $apiKey->status = $request->status;
            $apiKey->expires_at = $request->filled("expires_at") ? $request->expires_at : null;
            $apiKey->save();
            
            // 记录日志
            Log::info("API密钥更新成功", [
                "user_id" => $user->id,
                "api_key_id" => $apiKey->id
            ]);
            
            return redirect()->route("api-keys")->with("success", "API密钥更新成功。");
        } catch (\Exception $e) {
            Log::error("API密钥更新失败", [
                "error" => $e->getMessage(),
                "user_id" => $user->id,
                "api_key_id" => $id
            ]);
            
            return redirect()->back()->with("error", "API密钥更新失败，请稍后再试。");
        }
    }

    /**
     * 删除API密钥
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $user = Auth::user();
        
        // 获取API密钥
        $apiKey = ApiKey::where("id", $id)
            ->where("user_id", $user->id)
            ->first();
        
        if (!$apiKey) {
            return redirect()->back()->with("error", "未找到API密钥。");
        }
        
        try {
            // 删除API密钥
            $apiKey->delete();
            
            // 记录日志
            Log::info("API密钥删除成功", [
                "user_id" => $user->id,
                "api_key_id" => $id
            ]);
            
            return redirect()->route("api-keys")->with("success", "API密钥删除成功。");
        } catch (\Exception $e) {
            Log::error("API密钥删除失败", [
                "error" => $e->getMessage(),
                "user_id" => $user->id,
                "api_key_id" => $id
            ]);
            
            return redirect()->back()->with("error", "API密钥删除失败，请稍后再试。");
        }
    }

    /**
     * 生成唯一API密钥
     *
     * @return string
     */
    protected function generateApiKey()
    {
        $prefix = "ak_";
        $key = $prefix . Str::random(32);
        
        // 确保密钥唯一
        while (ApiKey::where("api_key", $key)->exists()) {
            $key = $prefix . Str::random(32);
        }
        
        return $key;
    }

    /**
     * 获取API使用统计
     *
     * @param  \App\Models\User  $user
     * @return array
     */
    protected function getApiUsageStats($user)
    {
        $apiKeys = $user->apiKeys;
        
        // 今日API调用次数
        $todayCalls = 0;
        
        // 本月API调用次数
        $monthCalls = 0;
        
        // 总API调用次数
        $totalCalls = 0;
        
        // 按状态码统计
        $statusStats = [
            "success" => 0,
            "error" => 0
        ];
        
        // 按端点统计
        $endpointStats = [];
        
        foreach ($apiKeys as $apiKey) {
            // 获取API日志
            $logs = $apiKey->logs;
            
            foreach ($logs as $log) {
                $totalCalls++;
                
                // 今日调用
                if ($log->created_at->isToday()) {
                    $todayCalls++;
                }
                
                // 本月调用
                if ($log->created_at->isCurrentMonth()) {
                    $monthCalls++;
                }
                
                // 状态统计
                if ($log->status_code >= 200 && $log->status_code < 300) {
                    $statusStats["success"]++;
                } else {
                    $statusStats["error"]++;
                }
                
                // 端点统计
                $endpoint = $log->endpoint;
                if (!isset($endpointStats[$endpoint])) {
                    $endpointStats[$endpoint] = 0;
                }
                $endpointStats[$endpoint]++;
            }
        }
        
        // 按调用次数排序端点
        arsort($endpointStats);
        
        // 只保留前5个端点
        $endpointStats = array_slice($endpointStats, 0, 5, true);
        
        return [
            "today" => $todayCalls,
            "month" => $monthCalls,
            "total" => $totalCalls,
            "status" => $statusStats,
            "endpoints" => $endpointStats
        ];
    }
}
