<?php

namespace AlingAi\Core\Utils;

/**
 * IP地理位置定位器
 * 
 * 提供IP地址地理位置信息查询功能
 * 
 * @version 1.0.0
 * @author AlingAi Team
 */
class IPGeolocator
{
    /**
     * @var string 缓存目录路径
     */
    private $cacheDir;
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->cacheDir = __DIR__ . '/../../../storage/cache/geoip';
        
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }
    
    /**
     * 定位IP地址地理位置
     * 
     * @param string $ipAddress IP地址
     * @return array 包含国家、地区、城市等信息的数组
     */
    public function locate(string $ipAddress): array
    {
        // 检查IP是否有效
        if (!filter_var($ipAddress, FILTER_VALIDATE_IP)) {
            return [
                'country' => '未知',
                'region' => '未知',
                'city' => '未知'
            ];
        }
        
        // 检查本地缓存
        $cacheResult = $this->getFromCache($ipAddress);
        if ($cacheResult !== null) {
            return $cacheResult;
        }
        
        // 如果安装了GeoIP扩展，使用扩展
        if (function_exists('geoip_country_name_by_name')) {
            return $this->locateWithGeoipExtension($ipAddress);
        }
        
        // 使用免费API服务
        $apiResult = $this->locateWithApi($ipAddress);
        if ($apiResult !== null) {
            // 缓存结果
            $this->saveToCache($ipAddress, $apiResult);
            return $apiResult;
        }
        
        // 使用本地数据库（如果存在）
        $dbResult = $this->locateWithLocalDatabase($ipAddress);
        if ($dbResult !== null) {
            return $dbResult;
        }
        
        // 如果所有方法都失败，返回未知
        return [
            'country' => '未知',
            'region' => '未知',
            'city' => '未知'
        ];
    }
    
    /**
     * 从缓存获取IP地理信息
     * 
     * @param string $ipAddress IP地址
     * @return array|null 地理信息数组或null
     */
    private function getFromCache(string $ipAddress): ?array
    {
        $cacheFile = $this->cacheDir . '/' . md5($ipAddress) . '.json';
        
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < 86400 * 30)) { // 30天缓存
            $data = file_get_contents($cacheFile);
            return json_decode($data, true);
        }
        
        return null;
    }
    
    /**
     * 保存IP地理信息到缓存
     * 
     * @param string $ipAddress IP地址
     * @param array $geoData 地理信息数组
     */
    private function saveToCache(string $ipAddress, array $geoData): void
    {
        $cacheFile = $this->cacheDir . '/' . md5($ipAddress) . '.json';
        file_put_contents($cacheFile, json_encode($geoData, JSON_UNESCAPED_UNICODE));
    }
    
    /**
     * 使用GeoIP扩展定位IP地址
     * 
     * @param string $ipAddress IP地址
     * @return array 地理信息数组
     */
    private function locateWithGeoipExtension(string $ipAddress): array
    {
        $country = @geoip_country_name_by_name($ipAddress) ?: '未知';
        $region = @geoip_region_by_name($ipAddress);
        $region = $region ? $region['region'] : '未知';
        $city = function_exists('geoip_city_by_name') ? @geoip_city_by_name($ipAddress) : '未知';
        $city = is_array($city) ? $city['city'] : '未知';
        
        return [
            'country' => $country,
            'region' => $region,
            'city' => $city
        ];
    }
    
    /**
     * 使用免费API服务定位IP地址
     * 
     * @param string $ipAddress IP地址
     * @return array|null 地理信息数组或null
     */
    private function locateWithApi(string $ipAddress): ?array
    {
        // 如果是本地IP，返回本地信息
        if ($this->isLocalIP($ipAddress)) {
            return [
                'country' => '本地网络',
                'region' => '本地网络',
                'city' => '本地网络'
            ];
        }
        
        // 尝试使用IP-API.com服务
        try {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 2 // 2秒超时
                ]
            ]);
            
            $apiUrl = "http://ip-api.com/json/{$ipAddress}?lang=zh-CN&fields=country,regionName,city,status";
            $response = @file_get_contents($apiUrl, false, $context);
            
            if ($response !== false) {
                $data = json_decode($response, true);
                
                if ($data && isset($data['status']) && $data['status'] === 'success') {
                    return [
                        'country' => $data['country'] ?? '未知',
                        'region' => $data['regionName'] ?? '未知',
                        'city' => $data['city'] ?? '未知'
                    ];
                }
            }
        } catch (\Exception $e) {
            // 忽略API错误，继续使用其他方法
        }
        
        return null;
    }
    
    /**
     * 使用本地数据库定位IP地址
     * 
     * @param string $ipAddress IP地址
     * @return array|null 地理信息数组或null
     */
    private function locateWithLocalDatabase(string $ipAddress): ?array
    {
        // IP地址分类
        if ($this->isLocalIP($ipAddress)) {
            return [
                'country' => '本地网络',
                'region' => '本地网络',
                'city' => '本地网络'
            ];
        }
        
        // 基于IP前缀的简单国家判断
        $ipLong = ip2long($ipAddress);
        
        // 中国IP段简单判断（这只是示例，实际应使用完整的IP数据库）
        $cnRanges = [
            ['1.0.0.0', '1.255.255.255'],
            ['14.0.0.0', '14.255.255.255'],
            ['27.0.0.0', '27.255.255.255'],
            ['36.0.0.0', '36.255.255.255'],
            ['39.0.0.0', '39.255.255.255'],
            ['42.0.0.0', '42.255.255.255'],
            ['49.0.0.0', '49.255.255.255'],
            ['58.0.0.0', '58.255.255.255'],
            ['59.0.0.0', '59.255.255.255'],
            ['60.0.0.0', '60.255.255.255'],
            ['101.0.0.0', '101.255.255.255'],
            ['103.0.0.0', '103.255.255.255'],
            ['106.0.0.0', '106.255.255.255'],
            ['110.0.0.0', '110.255.255.255'],
            ['111.0.0.0', '111.255.255.255'],
            ['112.0.0.0', '112.255.255.255'],
            ['113.0.0.0', '113.255.255.255'],
            ['114.0.0.0', '114.255.255.255'],
            ['115.0.0.0', '115.255.255.255'],
            ['116.0.0.0', '116.255.255.255'],
            ['117.0.0.0', '117.255.255.255'],
            ['118.0.0.0', '118.255.255.255'],
            ['119.0.0.0', '119.255.255.255'],
            ['120.0.0.0', '120.255.255.255'],
            ['121.0.0.0', '121.255.255.255'],
            ['122.0.0.0', '122.255.255.255'],
            ['123.0.0.0', '123.255.255.255'],
            ['124.0.0.0', '124.255.255.255'],
            ['125.0.0.0', '125.255.255.255'],
            ['126.0.0.0', '126.255.255.255'],
            ['175.0.0.0', '175.255.255.255'],
            ['180.0.0.0', '180.255.255.255'],
            ['182.0.0.0', '182.255.255.255'],
            ['183.0.0.0', '183.255.255.255'],
            ['202.0.0.0', '202.255.255.255'],
            ['203.0.0.0', '203.255.255.255'],
            ['210.0.0.0', '210.255.255.255'],
            ['211.0.0.0', '211.255.255.255'],
            ['218.0.0.0', '218.255.255.255'],
            ['220.0.0.0', '220.255.255.255'],
            ['221.0.0.0', '221.255.255.255'],
            ['222.0.0.0', '222.255.255.255'],
            ['223.0.0.0', '223.255.255.255']
        ];
        
        foreach ($cnRanges as $range) {
            $startIp = ip2long($range[0]);
            $endIp = ip2long($range[1]);
            
            if ($ipLong >= $startIp && $ipLong <= $endIp) {
                return [
                    'country' => '中国',
                    'region' => '未知',
                    'city' => '未知'
                ];
            }
        }
        
        return null;
    }
    
    /**
     * 检查是否为本地IP地址
     * 
     * @param string $ipAddress IP地址
     * @return bool 是否为本地IP
     */
    private function isLocalIP(string $ipAddress): bool
    {
        // 检查私有IP范围
        if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return true;
        }
        
        // 常见本地IP地址
        return in_array($ipAddress, [
            '127.0.0.1',
            '::1',
            'localhost',
            '0.0.0.0'
        ]);
    }
} 