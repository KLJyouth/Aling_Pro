<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Exception;

/**
 * AI模型服务类
 * 
 * 用于管理AI模型提供商、模型和API密钥
 */
class AIModelService
{
    /**
     * 获取所有模型提供商
     *
     * @param bool $onlyActive 是否只返回激活的提供商
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllProviders($onlyActive = false)
    {
        $query = DB::table('ai_model_providers')
            ->whereNull('deleted_at')
            ->orderBy('sort_order', 'asc');

        if ($onlyActive) {
            $query->where('is_active', true);
        }

        return $query->get();
    }

    /**
     * 获取单个模型提供商
     *
     * @param int|string $id 提供商ID或代码
     * @return object|null
     */
    public function getProvider($id)
    {
        if (is_numeric($id)) {
            return DB::table('ai_model_providers')
                ->where('id', $id)
                ->whereNull('deleted_at')
                ->first();
        } else {
            return DB::table('ai_model_providers')
                ->where('code', $id)
                ->whereNull('deleted_at')
                ->first();
        }
    }

    /**
     * 创建模型提供商
     *
     * @param array $data 提供商数据
     * @return int 新创建的提供商ID
     */
    public function createProvider(array $data)
    {
        // 处理JSON字段
        foreach (['capabilities', 'config_schema', 'config'] as $field) {
            if (isset($data[$field]) && is_array($data[$field])) {
                $data[$field] = json_encode($data[$field]);
            }
        }

        $data['created_at'] = now();
        $data['updated_at'] = now();

        return DB::table('ai_model_providers')->insertGetId($data);
    }

    /**
     * 更新模型提供商
     *
     * @param int $id 提供商ID
     * @param array $data 更新数据
     * @return bool
     */
    public function updateProvider($id, array $data)
    {
        // 处理JSON字段
        foreach (['capabilities', 'config_schema', 'config'] as $field) {
            if (isset($data[$field]) && is_array($data[$field])) {
                $data[$field] = json_encode($data[$field]);
            }
        }

        $data['updated_at'] = now();

        return DB::table('ai_model_providers')
            ->where('id', $id)
            ->update($data);
    }

    /**
     * 删除模型提供商（软删除）
     *
     * @param int $id 提供商ID
     * @return bool
     */
    public function deleteProvider($id)
    {
        return DB::table('ai_model_providers')
            ->where('id', $id)
            ->update([
                'deleted_at' => now(),
                'updated_at' => now()
            ]);
    }

    /**
     * 激活/停用模型提供商
     *
     * @param int $id 提供商ID
     * @param bool $active 是否激活
     * @return bool
     */
    public function toggleProvider($id, $active = true)
    {
        return DB::table('ai_model_providers')
            ->where('id', $id)
            ->update([
                'is_active' => $active,
                'updated_at' => now()
            ]);
    }

    /**
     * 获取提供商的所有模型
     *
     * @param int $providerId 提供商ID
     * @param bool $onlyActive 是否只返回激活的模型
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getProviderModels($providerId, $onlyActive = false)
    {
        $query = DB::table('ai_models')
            ->where('provider_id', $providerId)
            ->whereNull('deleted_at')
            ->orderBy('sort_order', 'asc');

        if ($onlyActive) {
            $query->where('is_active', true);
        }

        return $query->get();
    }

    /**
     * 获取单个模型
     *
     * @param int $id 模型ID
     * @return object|null
     */
    public function getModel($id)
    {
        return DB::table('ai_models')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();
    }

    /**
     * 创建模型
     *
     * @param array $data 模型数据
     * @return int 新创建的模型ID
     */
    public function createModel(array $data)
    {
        // 处理JSON字段
        foreach (['capabilities', 'parameters', 'limits'] as $field) {
            if (isset($data[$field]) && is_array($data[$field])) {
                $data[$field] = json_encode($data[$field]);
            }
        }

        $data['created_at'] = now();
        $data['updated_at'] = now();

        return DB::table('ai_models')->insertGetId($data);
    }

    /**
     * 更新模型
     *
     * @param int $id 模型ID
     * @param array $data 更新数据
     * @return bool
     */
    public function updateModel($id, array $data)
    {
        // 处理JSON字段
        foreach (['capabilities', 'parameters', 'limits'] as $field) {
            if (isset($data[$field]) && is_array($data[$field])) {
                $data[$field] = json_encode($data[$field]);
            }
        }

        $data['updated_at'] = now();

        return DB::table('ai_models')
            ->where('id', $id)
            ->update($data);
    }

    /**
     * 删除模型（软删除）
     *
     * @param int $id 模型ID
     * @return bool
     */
    public function deleteModel($id)
    {
        return DB::table('ai_models')
            ->where('id', $id)
            ->update([
                'deleted_at' => now(),
                'updated_at' => now()
            ]);
    }

    /**
     * 激活/停用模型
     *
     * @param int $id 模型ID
     * @param bool $active 是否激活
     * @return bool
     */
    public function toggleModel($id, $active = true)
    {
        return DB::table('ai_models')
            ->where('id', $id)
            ->update([
                'is_active' => $active,
                'updated_at' => now()
            ]);
    }

    /**
     * 获取所有API密钥
     *
     * @param int|null $providerId 提供商ID
     * @param bool $onlyActive 是否只返回激活的密钥
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllApiKeys($providerId = null, $onlyActive = false)
    {
        $query = DB::table('ai_api_keys')
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc');

        if ($providerId) {
            $query->where('provider_id', $providerId);
        }

        if ($onlyActive) {
            $query->where('is_active', true);
        }

        return $query->get();
    }

    /**
     * 获取单个API密钥
     *
     * @param int $id 密钥ID
     * @return object|null
     */
    public function getApiKey($id)
    {
        return DB::table('ai_api_keys')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();
    }

    /**
     * 创建API密钥
     *
     * @param array $data 密钥数据
     * @return int 新创建的密钥ID
     */
    public function createApiKey(array $data)
    {
        // 处理JSON字段
        if (isset($data['permissions']) && is_array($data['permissions'])) {
            $data['permissions'] = json_encode($data['permissions']);
        }

        $data['created_at'] = now();
        $data['updated_at'] = now();

        return DB::table('ai_api_keys')->insertGetId($data);
    }

    /**
     * 更新API密钥
     *
     * @param int $id 密钥ID
     * @param array $data 更新数据
     * @return bool
     */
    public function updateApiKey($id, array $data)
    {
        // 处理JSON字段
        if (isset($data['permissions']) && is_array($data['permissions'])) {
            $data['permissions'] = json_encode($data['permissions']);
        }

        $data['updated_at'] = now();

        return DB::table('ai_api_keys')
            ->where('id', $id)
            ->update($data);
    }

    /**
     * 删除API密钥（软删除）
     *
     * @param int $id 密钥ID
     * @return bool
     */
    public function deleteApiKey($id)
    {
        return DB::table('ai_api_keys')
            ->where('id', $id)
            ->update([
                'deleted_at' => now(),
                'updated_at' => now()
            ]);
    }

    /**
     * 激活/停用API密钥
     *
     * @param int $id 密钥ID
     * @param bool $active 是否激活
     * @return bool
     */
    public function toggleApiKey($id, $active = true)
    {
        return DB::table('ai_api_keys')
            ->where('id', $id)
            ->update([
                'is_active' => $active,
                'updated_at' => now()
            ]);
    }

    /**
     * 记录API使用日志
     *
     * @param array $data 日志数据
     * @return int 日志ID
     */
    public function logApiUsage(array $data)
    {
        // 处理JSON字段
        foreach (['request_data', 'response_data'] as $field) {
            if (isset($data[$field]) && is_array($data[$field])) {
                $data[$field] = json_encode($data[$field]);
            }
        }

        $data['created_at'] = now();
        $data['updated_at'] = now();

        return DB::table('ai_usage_logs')->insertGetId($data);
    }

    /**
     * 获取使用日志
     *
     * @param array $filters 过滤条件
     * @param int $limit 限制数量
     * @param int $offset 偏移量
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUsageLogs(array $filters = [], $limit = 50, $offset = 0)
    {
        $query = DB::table('ai_usage_logs')
            ->orderBy('created_at', 'desc')
            ->offset($offset)
            ->limit($limit);

        foreach ($filters as $field => $value) {
            if ($value !== null) {
                $query->where($field, $value);
            }
        }

        return $query->get();
    }

    /**
     * 获取使用统计
     *
     * @param string $period 周期 (day, week, month, year)
     * @param int|null $providerId 提供商ID
     * @return array
     */
    public function getUsageStatistics($period = 'month', $providerId = null)
    {
        $dateFormat = 'Y-m-d';
        $groupBy = 'date';
        
        switch ($period) {
            case 'day':
                $startDate = now()->startOfDay();
                $dateFormat = 'Y-m-d H:i';
                $groupBy = 'hour';
                break;
            case 'week':
                $startDate = now()->startOfWeek();
                break;
            case 'month':
                $startDate = now()->startOfMonth();
                break;
            case 'year':
                $startDate = now()->startOfYear();
                $dateFormat = 'Y-m';
                $groupBy = 'month';
                break;
            default:
                $startDate = now()->startOfMonth();
        }

        $query = DB::table('ai_usage_logs')
            ->where('created_at', '>=', $startDate);

        if ($providerId) {
            $query->where('provider_id', $providerId);
        }

        if ($groupBy === 'hour') {
            $query->selectRaw('DATE_FORMAT(created_at, "%Y-%m-%d %H:00") as date');
        } elseif ($groupBy === 'month') {
            $query->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as date');
        } else {
            $query->selectRaw('DATE(created_at) as date');
        }

        $results = $query->selectRaw('
                COUNT(*) as request_count,
                SUM(total_tokens) as total_tokens,
                SUM(cost) as total_cost,
                AVG(latency_ms) as avg_latency,
                COUNT(CASE WHEN is_success = 0 THEN 1 END) as error_count
            ')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return $results;
    }

    /**
     * 获取AI接口设置
     *
     * @param string $key 设置键
     * @param mixed $default 默认值
     * @return mixed
     */
    public function getSetting($key, $default = null)
    {
        $cacheKey = "ai_interface_setting:{$key}";
        
        // 尝试从缓存获取
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        
        // 从数据库获取
        $setting = DB::table('ai_interface_settings')
            ->where('key', $key)
            ->first();
        
        $value = $setting ? $setting->value : $default;
        
        // 缓存结果
        Cache::put($cacheKey, $value, 3600); // 缓存1小时
        
        return $value;
    }

    /**
     * 更新AI接口设置
     *
     * @param string $key 设置键
     * @param mixed $value 设置值
     * @param string $group 分组
     * @param string|null $description 描述
     * @return bool
     */
    public function updateSetting($key, $value, $group = 'general', $description = null)
    {
        // 检查设置是否存在
        $exists = DB::table('ai_interface_settings')
            ->where('key', $key)
            ->exists();
        
        if ($exists) {
            // 更新设置
            $result = DB::table('ai_interface_settings')
                ->where('key', $key)
                ->update([
                    'value' => $value,
                    'updated_at' => now()
                ]);
        } else {
            // 创建设置
            $result = DB::table('ai_interface_settings')->insert([
                'key' => $key,
                'value' => $value,
                'group' => $group,
                'description' => $description,
                'is_system' => false,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
        
        // 更新缓存
        Cache::put("ai_interface_setting:{$key}", $value, 3600);
        
        return $result;
    }

    /**
     * 获取分组设置
     *
     * @param string $group 分组名称
     * @return \Illuminate\Support\Collection
     */
    public function getSettingsByGroup($group)
    {
        return DB::table('ai_interface_settings')
            ->where('group', $group)
            ->get();
    }

    /**
     * 获取所有设置
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllSettings()
    {
        return DB::table('ai_interface_settings')
            ->orderBy('group')
            ->orderBy('key')
            ->get();
    }

    /**
     * 清除设置缓存
     *
     * @param string|null $key 特定设置键，为null则清除所有
     * @return void
     */
    public function clearSettingsCache($key = null)
    {
        if ($key) {
            Cache::forget("ai_interface_setting:{$key}");
        } else {
            // 获取所有设置键
            $keys = DB::table('ai_interface_settings')
                ->pluck('key')
                ->toArray();
                
            foreach ($keys as $k) {
                Cache::forget("ai_interface_setting:{$k}");
            }
        }
    }

    /**
     * 测试API连接
     *
     * @param string $provider 提供商代码
     * @param array $config 配置
     * @return array 测试结果
     */
    public function testApiConnection($provider, array $config)
    {
        try {
            switch ($provider) {
                case 'openai':
                    return $this->testOpenAI($config);
                case 'anthropic':
                    return $this->testAnthropic($config);
                case 'zhipu':
                    return $this->testZhipu($config);
                case 'baidu':
                    return $this->testBaidu($config);
                case 'aliyun':
                    return $this->testAliyun($config);
                case 'huawei':
                    return $this->testHuawei($config);
                default:
                    return [
                        'success' => false,
                        'message' => '不支持的提供商'
                    ];
            }
        } catch (Exception $e) {
            Log::error('AI API测试失败', [
                'provider' => $provider,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => '测试失败: ' . $e->getMessage()
            ];
        }
    }

    /**
     * 测试OpenAI API
     *
     * @param array $config 配置
     * @return array 测试结果
     */
    private function testOpenAI(array $config)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $config['api_key'],
            'Content-Type' => 'application/json'
        ])->get('https://api.openai.com/v1/models');

        if ($response->successful()) {
            return [
                'success' => true,
                'message' => '连接成功',
                'data' => $response->json()
            ];
        }

        return [
            'success' => false,
            'message' => '连接失败: ' . $response->body(),
            'status' => $response->status()
        ];
    }

    /**
     * 测试Anthropic API
     *
     * @param array $config 配置
     * @return array 测试结果
     */
    private function testAnthropic(array $config)
    {
        $response = Http::withHeaders([
            'x-api-key' => $config['api_key'],
            'Content-Type' => 'application/json',
            'anthropic-version' => '2023-06-01'
        ])->post('https://api.anthropic.com/v1/messages', [
            'model' => 'claude-3-haiku-20240307',
            'max_tokens' => 10,
            'messages' => [
                ['role' => 'user', 'content' => 'Hello']
            ]
        ]);

        if ($response->successful()) {
            return [
                'success' => true,
                'message' => '连接成功',
                'data' => $response->json()
            ];
        }

        return [
            'success' => false,
            'message' => '连接失败: ' . $response->body(),
            'status' => $response->status()
        ];
    }

    /**
     * 测试智谱API
     *
     * @param array $config 配置
     * @return array 测试结果
     */
    private function testZhipu(array $config)
    {
        // 智谱AI接口测试实现
        // 此处需要根据智谱AI的API文档实现
        return [
            'success' => true,
            'message' => '连接成功（模拟）'
        ];
    }

    /**
     * 测试百度文心API
     *
     * @param array $config 配置
     * @return array 测试结果
     */
    private function testBaidu(array $config)
    {
        // 百度文心接口测试实现
        // 此处需要根据百度文心的API文档实现
        return [
            'success' => true,
            'message' => '连接成功（模拟）'
        ];
    }

    /**
     * 测试阿里通义API
     *
     * @param array $config 配置
     * @return array 测试结果
     */
    private function testAliyun(array $config)
    {
        // 阿里通义接口测试实现
        // 此处需要根据阿里通义的API文档实现
        return [
            'success' => true,
            'message' => '连接成功（模拟）'
        ];
    }

    /**
     * 测试华为盘古API
     *
     * @param array $config 配置
     * @return array 测试结果
     */
    private function testHuawei(array $config)
    {
        // 华为盘古接口测试实现
        // 此处需要根据华为盘古的API文档实现
        return [
            'success' => true,
            'message' => '连接成功（模拟）'
        ];
    }
} 