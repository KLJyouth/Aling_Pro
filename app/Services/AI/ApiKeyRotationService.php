<?php

namespace App\Services\AI;

use App\Models\AI\ApiKey;
use App\Models\AI\AdvancedSetting;
use App\Models\AI\ApiLog;
use Illuminate\Support\Collection;

class ApiKeyRotationService
{
    /**
     * 获取可用的API密钥
     *
     * @param int $providerId 提供商ID
     * @return ApiKey|null
     */
    public function getAvailableApiKey($providerId)
    {
        // 检查是否启用API密钥轮换
        $rotationEnabled = AdvancedSetting::getValue("enable_api_key_rotation", false);
        
        if (!$rotationEnabled) {
            // 如果未启用轮换，则返回第一个可用的API密钥
            return ApiKey::where("provider_id", $providerId)
                ->where("is_active", true)
                ->whereRaw("(quota_limit = 0 OR usage_count < quota_limit)")
                ->orderBy("usage_count")
                ->first();
        }
        
        // 获取轮换策略
        $strategy = AdvancedSetting::getValue("rotation_strategy", "round_robin");
        
        // 获取所有可用的API密钥
        $availableKeys = ApiKey::where("provider_id", $providerId)
            ->where("is_active", true)
            ->whereRaw("(quota_limit = 0 OR usage_count < quota_limit)")
            ->get();
        
        if ($availableKeys->isEmpty()) {
            return null;
        }
        
        // 根据策略选择API密钥
        switch ($strategy) {
            case "round_robin":
                return $this->roundRobinStrategy($availableKeys);
            
            case "random":
                return $this->randomStrategy($availableKeys);
            
            case "weighted":
                return $this->weightedStrategy($availableKeys);
            
            default:
                return $availableKeys->first();
        }
    }
    
    /**
     * 轮询策略
     *
     * @param Collection $keys
     * @return ApiKey
     */
    protected function roundRobinStrategy(Collection $keys)
    {
        // 按最后使用时间排序，选择最久未使用的密钥
        return $keys->sortBy("last_used_at")->first();
    }
    
    /**
     * 随机策略
     *
     * @param Collection $keys
     * @return ApiKey
     */
    protected function randomStrategy(Collection $keys)
    {
        return $keys->random();
    }
    
    /**
     * 加权策略
     *
     * @param Collection $keys
     * @return ApiKey
     */
    protected function weightedStrategy(Collection $keys)
    {
        // 根据使用次数的倒数作为权重，使用次数越少权重越高
        $totalWeight = 0;
        $weights = [];
        
        foreach ($keys as $key) {
            $usage = $key->usage_count + 1; // 避免除以0
            $weight = 1 / $usage;
            $weights[$key->id] = $weight;
            $totalWeight += $weight;
        }
        
        $random = mt_rand(0, mt_getrandmax()) / mt_getrandmax() * $totalWeight;
        $currentWeight = 0;
        
        foreach ($keys as $key) {
            $currentWeight += $weights[$key->id];
            if ($currentWeight >= $random) {
                return $key;
            }
        }
        
        return $keys->first();
    }
    
    /**
     * 负载均衡
     *
     * @param int $providerId 提供商ID
     * @return ApiKey|null
     */
    public function loadBalance($providerId)
    {
        // 检查是否启用负载均衡
        $loadBalancingEnabled = AdvancedSetting::getValue("enable_load_balancing", false);
        
        if (!$loadBalancingEnabled) {
            // 如果未启用负载均衡，则使用轮换策略
            return $this->getAvailableApiKey($providerId);
        }
        
        // 获取负载均衡策略
        $strategy = AdvancedSetting::getValue("load_balancing_strategy", "least_used");
        
        // 获取所有可用的API密钥
        $availableKeys = ApiKey::where("provider_id", $providerId)
            ->where("is_active", true)
            ->whereRaw("(quota_limit = 0 OR usage_count < quota_limit)")
            ->get();
        
        if ($availableKeys->isEmpty()) {
            return null;
        }
        
        // 根据策略选择API密钥
        switch ($strategy) {
            case "least_used":
                return $availableKeys->sortBy("usage_count")->first();
            
            case "percentage":
                return $this->percentageStrategy($availableKeys);
            
            default:
                return $availableKeys->sortBy("usage_count")->first();
        }
    }
    
    /**
     * 百分比策略
     *
     * @param Collection $keys
     * @return ApiKey
     */
    protected function percentageStrategy(Collection $keys)
    {
        $totalUsage = $keys->sum("usage_count") ?: 1; // 避免除以0
        $availablePercentage = [];
        
        foreach ($keys as $key) {
            $usagePercentage = $key->usage_count / $totalUsage;
            $availablePercentage[$key->id] = 1 - $usagePercentage; // 使用百分比越低，可用性越高
        }
        
        // 按可用性百分比排序，选择可用性最高的密钥
        $keyId = array_search(max($availablePercentage), $availablePercentage);
        return $keys->firstWhere("id", $keyId);
    }
    
    /**
     * 记录API调用
     *
     * @param ApiKey $apiKey
     * @param array $data
     * @return ApiLog
     */
    public function logApiCall(ApiKey $apiKey, array $data)
    {
        // 增加API密钥使用次数
        $apiKey->incrementUsage();
        
        // 创建API调用日志
        return ApiLog::create(array_merge([
            "provider_id" => $apiKey->provider_id,
            "api_key_id" => $apiKey->id,
        ], $data));
    }
}
