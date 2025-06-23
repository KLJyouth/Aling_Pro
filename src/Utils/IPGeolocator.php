<?php

declare(strict_types=1);

namespace AlingAi\Utils;

/**
 * IP地理定位器
 * 用于获取IP地址的地理位置信息
 */
class IPGeolocator
{
    private array $config;
    private array $cache = [];
    
    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'enable_cache' => true,
            'cache_ttl' => 3600, // 1小时
            'api_key' => '',
            'use_local_db' => true,
            'fallback_service' => 'ipapi'
        ], $config);
    }
    
    /**
     * 定位IP地址
     */
    public function locate(string $ip): array
    {
        if (empty($ip) || $ip === '0.0.0.0' || $ip === '127.0.0.1') {
            return $this->getDefaultLocation();
        }
        
        // 检查缓存
        if ($this->config['enable_cache'] && isset($this->cache[$ip])) {
            $cached = $this->cache[$ip];
            if (time() - $cached['timestamp'] < $this->config['cache_ttl']) {
                return $cached['data'];
            }
        }
        
        // 尝试本地数据库
        if ($this->config['use_local_db']) {
            $location = $this->locateFromLocalDB($ip);
            if ($location) {
                $this->cacheResult($ip, $location);
                return $location;
            }
        }
        
        // 尝试外部API
        $location = $this->locateFromAPI($ip);
        if ($location) {
            $this->cacheResult($ip, $location);
            return $location;
        }
        
        // 返回默认信息
        $default = $this->getDefaultLocation();
        $this->cacheResult($ip, $default);
        return $default;
    }
    
    /**
     * 从本地数据库定位
     */
    private function locateFromLocalDB(string $ip): ?array
    {
        // 这里可以集成MaxMind GeoIP2或其他本地IP数据库
        // 简化实现，实际应该使用真实的IP数据库
        return null;
    }
    
    /**
     * 从外部API定位
     */
    private function locateFromAPI(string $ip): ?array
    {
        $services = [
            'ipapi' => [$this, 'queryIPAPI'],
            'ipinfo' => [$this, 'queryIPInfo'],
            'freegeoip' => [$this, 'queryFreeGeoIP']
        ];
        
        $service = $services[$this->config['fallback_service']] ?? $services['ipapi'];
        
        try {
            return $service($ip);
        } catch (\Exception $e) {
            // 记录错误但不抛出异常
            error_log("IP定位失败: {$ip} - " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * 查询ipapi.co服务
     */
    private function queryIPAPI(string $ip): ?array
    {
        $url = "http://ip-api.com/json/{$ip}";
        $response = $this->makeRequest($url);
        
        if (!$response) {
            return null;
        }
        
        $data = json_decode($response, true);
        if (!$data || $data['status'] !== 'success') {
            return null;
        }
        
        return [
            'country' => $data['country'] ?? 'Unknown',
            'country_code' => $data['countryCode'] ?? 'Unknown',
            'region' => $data['regionName'] ?? 'Unknown',
            'region_code' => $data['region'] ?? 'Unknown',
            'city' => $data['city'] ?? 'Unknown',
            'zip_code' => $data['zip'] ?? 'Unknown',
            'latitude' => $data['lat'] ?? 0,
            'longitude' => $data['lon'] ?? 0,
            'timezone' => $data['timezone'] ?? 'Unknown',
            'isp' => $data['isp'] ?? 'Unknown',
            'organization' => $data['org'] ?? 'Unknown',
            'as_number' => $data['as'] ?? 'Unknown'
        ];
    }
    
    /**
     * 查询ipinfo.io服务
     */
    private function queryIPInfo(string $ip): ?array
    {
        $url = "https://ipinfo.io/{$ip}/json";
        if ($this->config['api_key']) {
            $url .= "?token=" . $this->config['api_key'];
        }
        
        $response = $this->makeRequest($url);
        if (!$response) {
            return null;
        }
        
        $data = json_decode($response, true);
        if (!$data || isset($data['error'])) {
            return null;
        }
        
        $location = explode(',', $data['loc'] ?? '0,0');
        
        return [
            'country' => $data['country'] ?? 'Unknown',
            'country_code' => $data['country'] ?? 'Unknown',
            'region' => $data['region'] ?? 'Unknown',
            'region_code' => $data['region'] ?? 'Unknown',
            'city' => $data['city'] ?? 'Unknown',
            'zip_code' => $data['postal'] ?? 'Unknown',
            'latitude' => (float) ($location[0] ?? 0),
            'longitude' => (float) ($location[1] ?? 0),
            'timezone' => $data['timezone'] ?? 'Unknown',
            'isp' => $data['org'] ?? 'Unknown',
            'organization' => $data['org'] ?? 'Unknown',
            'as_number' => 'Unknown'
        ];
    }
    
    /**
     * 查询FreeGeoIP服务
     */
    private function queryFreeGeoIP(string $ip): ?array
    {
        $url = "https://freegeoip.app/json/{$ip}";
        $response = $this->makeRequest($url);
        
        if (!$response) {
            return null;
        }
        
        $data = json_decode($response, true);
        if (!$data) {
            return null;
        }
        
        return [
            'country' => $data['country_name'] ?? 'Unknown',
            'country_code' => $data['country_code'] ?? 'Unknown',
            'region' => $data['region_name'] ?? 'Unknown',
            'region_code' => $data['region_code'] ?? 'Unknown',
            'city' => $data['city'] ?? 'Unknown',
            'zip_code' => $data['zip_code'] ?? 'Unknown',
            'latitude' => $data['latitude'] ?? 0,
            'longitude' => $data['longitude'] ?? 0,
            'timezone' => $data['time_zone'] ?? 'Unknown',
            'isp' => $data['isp'] ?? 'Unknown',
            'organization' => $data['organization'] ?? 'Unknown',
            'as_number' => 'Unknown'
        ];
    }
    
    /**
     * 发送HTTP请求
     */
    private function makeRequest(string $url): ?string
    {
        $context = stream_context_create([
            'http' => [
                'timeout' => 5,
                'user_agent' => 'AlingAi-IPGeolocator/1.0'
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        return $response ?: null;
    }
    
    /**
     * 缓存结果
     */
    private function cacheResult(string $ip, array $location): void
    {
        if ($this->config['enable_cache']) {
            $this->cache[$ip] = [
                'data' => $location,
                'timestamp' => time()
            ];
        }
    }
    
    /**
     * 获取默认位置信息
     */
    private function getDefaultLocation(): array
    {
        return [
            'country' => 'Unknown',
            'country_code' => 'Unknown',
            'region' => 'Unknown',
            'region_code' => 'Unknown',
            'city' => 'Unknown',
            'zip_code' => 'Unknown',
            'latitude' => 0,
            'longitude' => 0,
            'timezone' => 'Unknown',
            'isp' => 'Unknown',
            'organization' => 'Unknown',
            'as_number' => 'Unknown'
        ];
    }
    
    /**
     * 批量定位IP地址
     */
    public function locateBatch(array $ips): array
    {
        $results = [];
        
        foreach ($ips as $ip) {
            $results[$ip] = $this->locate($ip);
        }
        
        return $results;
    }
    
    /**
     * 清除缓存
     */
    public function clearCache(): void
    {
        $this->cache = [];
    }
    
    /**
     * 获取缓存统计
     */
    public function getCacheStats(): array
    {
        return [
            'cache_enabled' => $this->config['enable_cache'],
            'cache_size' => count($this->cache),
            'cache_ttl' => $this->config['cache_ttl']
        ];
    }
} 