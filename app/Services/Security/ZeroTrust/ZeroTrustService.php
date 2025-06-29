<?php

namespace App\Services\Security\ZeroTrust;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;
use App\Models\User;
use App\Models\UserDevice;
use App\Models\SecurityLog;
use App\Models\SecurityAlert;

class ZeroTrustService
{
    /**
     * 请求对象
     * 
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * 用户代理解析器
     * 
     * @var \Jenssegers\Agent\Agent
     */
    protected $agent;

    /**
     * 当前用户
     * 
     * @var \App\Models\User|null
     */
    protected $user;

    /**
     * 风险评分
     * 
     * @var int
     */
    protected $riskScore = 0;

    /**
     * 安全检查结果
     * 
     * @var array
     */
    protected $securityChecks = [];

    /**
     * 构造函数
     * 
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->agent = new Agent();
        $this->agent->setUserAgent($request->userAgent());
        $this->user = $request->user();
    }

    /**
     * 执行全面安全检查
     * 
     * @param string $context 上下文（如：login, payment, api）
     * @return array 安全检查结果
     */
    public function runFullSecurityCheck(string $context = '): array
    {
        // 重置风险评分
        $this->riskScore = 0;
        $this->securityChecks = [];
        
        // 检查设备信息
        $this->checkDeviceFingerprint();
        
        // 检查地理位置
        $this->checkGeolocation();
        
        // 检查网络环境
        $this->checkNetworkEnvironment();
        
        // 检查时间模式
        $this->checkTimePattern();
        
        // 检查行为模式
        $this->checkBehaviorPattern();
        
        // 检查API密钥安全
        if ($context === 'payment') {
            $this->checkPaymentApiSecurity();
        }
        
        // 检查服务器环境
        $this->checkServerEnvironment();
        
        // 检查浏览器安全
        $this->checkBrowserSecurity();
        
        // 记录安全检查结果
        $this->logSecurityCheck($context);
        
        // 返回安全检查结果
        return [
            'risk_score' => $this->riskScore,
            'checks' => $this->securityChecks,
            'is_suspicious' => $this->riskScore > 70,
            'is_dangerous' => $this->riskScore > 90,
            'requires_additional_verification' => $this->riskScore > 50,
        ];
    }

    /**
     * 检查设备指纹
     * 
     * @return void
     */
    protected function checkDeviceFingerprint(): void
    {
        $deviceFingerprint = $this->request->header('X-Device-Fingerprint');
        $deviceId = $this->request->header('X-Device-ID');
        $isKnownDevice = false;
        $deviceRisk = 0;
        
        // 检查是否为已知设备
        if ($this->user) {
            $knownDevices = UserDevice::where('user_id', $this->user->id)->get();
            
            foreach ($knownDevices as $device) {
                if ($device->device_fingerprint === $deviceFingerprint || $device->device_id === $deviceId) {
                    $isKnownDevice = true;
                    
                    // 更新设备最后活动时间
                    $device->last_active_at = now();
                    $device->save();
                    
                    break;
                }
            }
            
            if (!$isKnownDevice && ($deviceFingerprint || $deviceId)) {
                // 新设备风险评分增加
                $deviceRisk += 30;
            }
        }
        
        // 检查设备类型
        $deviceType = $this->agent->deviceType();
        $platform = $this->agent->platform();
        $browser = $this->agent->browser();
        
        // 收集设备信息
        $deviceInfo = [
            'device_type' => $deviceType,
            'platform' => $platform,
            'platform_version' => $this->agent->version($platform),
            'browser' => $browser,
            'browser_version' => $this->agent->version($browser),
            'is_mobile' => $this->agent->isMobile(),
            'is_tablet' => $this->agent->isTablet(),
            'is_desktop' => $this->agent->isDesktop(),
            'is_robot' => $this->agent->isRobot(),
            'is_known_device' => $isKnownDevice,
            'device_id' => $deviceId,
            'fingerprint' => substr($deviceFingerprint, 0, 16) . '...' // 截断指纹，仅用于日志
        ];
        
        // 检测机器人或异常设备
        if ($this->agent->isRobot()) {
            $deviceRisk += 50;
        }
        
        $this->securityChecks['device'] = [
            'status' => $deviceRisk < 30 ? 'safe' : ($deviceRisk < 60 ? 'suspicious' : 'dangerous'),
            'risk_score' => $deviceRisk,
            'info' => $deviceInfo
        ];
        
        $this->riskScore += $deviceRisk;
    }

    /**
     * 检查地理位置
     * 
     * @return void
     */
    protected function checkGeolocation(): void
    {
        $ip = $this->request->ip();
        $geoRisk = 0;
        
        // 获取地理位置信息
        $geoInfo = $this->getIpGeoInfo($ip);
        
        // 检查用户常用位置
        $isKnownLocation = false;
        $distanceFromKnownLocations = PHP_INT_MAX;
        
        if ($this->user) {
            // 获取用户最近的登录位置
            $recentLocations = SecurityLog::where('user_id', $this->user->id)
                ->where('event_type', 'login')
                ->where('created_at', '>', now()->subDays(30))
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
            
            foreach ($recentLocations as $location) {
                if (isset($location->metadata['geo'])) {
                    $prevLat = $location->metadata['geo']['latitude'] ?? null;
                    $prevLng = $location->metadata['geo']['longitude'] ?? null;
                    
                    if ($prevLat && $prevLng && isset($geoInfo['latitude']) && isset($geoInfo['longitude'])) {
                        // 计算距离（简化版本）
                        $distance = $this->calculateDistance(
                            $prevLat, 
                            $prevLng, 
                            $geoInfo['latitude'], 
                            $geoInfo['longitude']
                        );
                        
                        if ($distance < 50) { // 50公里内视为相同区域
                            $isKnownLocation = true;
                        }
                        
                        $distanceFromKnownLocations = min($distanceFromKnownLocations, $distance);
                    }
                    
                    // 如果国家相同也降低风险
                    if (isset($location->metadata['geo']['country']) && 
                        isset($geoInfo['country']) && 
                        $location->metadata['geo']['country'] === $geoInfo['country']) {
                        $isKnownLocation = true;
                    }
                }
            }
            
            if (!$isKnownLocation) {
                $geoRisk += 25;
                
                // 如果距离非常远，增加风险评分
                if ($distanceFromKnownLocations > 1000) { // 1000公里以上
                    $geoRisk += 15;
                }
            }
        }
        
        // 检查高风险国家/地区
        $highRiskCountries = ['North Korea', 'Iran', 'Syria', 'Sudan', 'Cuba'];
        if (isset($geoInfo['country']) && in_array($geoInfo['country'], $highRiskCountries)) {
            $geoRisk += 50;
        }
        
        // 检查VPN/代理使用
        if ($this->detectVpnOrProxy($ip)) {
            $geoRisk += 30;
        }
        
        $this->securityChecks['geolocation'] = [
            'status' => $geoRisk < 30 ? 'safe' : ($geoRisk < 60 ? 'suspicious' : 'dangerous'),
            'risk_score' => $geoRisk,
            'info' => [
                'ip' => $ip,
                'geo' => $geoInfo,
                'is_known_location' => $isKnownLocation,
                'distance_from_known' => $distanceFromKnownLocations < PHP_INT_MAX ? round($distanceFromKnownLocations) : null,
                'is_vpn_or_proxy' => $this->detectVpnOrProxy($ip)
            ]
        ];
        
        $this->riskScore += $geoRisk;
    }

    /**
     * 检查网络环境
     * 
     * @return void
     */
    protected function checkNetworkEnvironment(): void
    {
        $networkRisk = 0;
        
        // 检查连接类型
        $connectionType = $this->request->header('X-Connection-Type');
        $networkInfo = [
            'connection_type' => $connectionType,
            'is_secure' => $this->request->secure(),
            'protocol' => $this->request->server('SERVER_PROTOCOL'),
            'port' => $this->request->server('SERVER_PORT'),
        ];
        
        // 检查是否使用安全连接
        if (!$this->request->secure()) {
            $networkRisk += 40;
        }
        
        // 检查TLS版本
        $tlsVersion = $this->request->server('SSL_PROTOCOL') ?? 'unknown';
        $networkInfo['tls_version'] = $tlsVersion;
        
        if ($tlsVersion === 'TLSv1' || $tlsVersion === 'TLSv1.1') {
            $networkRisk += 30; // 较旧的TLS版本
        }
        
        // 检查HTTP请求头
        $headers = $this->request->headers->all();
        $suspiciousHeaders = [];
        
        // 检查可疑的请求头
        $knownProxyHeaders = [
            'via',
            'forwarded',
            'x-forwarded-for',
            'x-forwarded-host',
            'x-forwarded-proto',
            'x-real-ip',
            'client-ip',
            'cf-connecting-ip',
            'true-client-ip'
        ];
        
        foreach ($knownProxyHeaders as $header) {
            if (isset($headers[$header])) {
                $suspiciousHeaders[$header] = $headers[$header];
            }
        }
        
        if (!empty($suspiciousHeaders)) {
            $networkRisk += 15;
        }
        
        $networkInfo['suspicious_headers'] = $suspiciousHeaders;
        
        $this->securityChecks['network'] = [
            'status' => $networkRisk < 30 ? 'safe' : ($networkRisk < 60 ? 'suspicious' : 'dangerous'),
            'risk_score' => $networkRisk,
            'info' => $networkInfo
        ];
        
        $this->riskScore += $networkRisk;
    }

    /**
     * 检查时间模式
     * 
     * @return void
     */
    protected function checkTimePattern(): void
    {
        $timeRisk = 0;
        $currentHour = now()->hour;
        $isBusinessHours = ($currentHour >= 8 && $currentHour <= 18);
        $isWeekend = now()->isWeekend();
        
        $timeInfo = [
            'current_time' => now()->format('Y-m-d H:i:s'),
            'timezone' => config('app.timezone'),
            'is_business_hours' => $isBusinessHours,
            'is_weekend' => $isWeekend,
            'hour_of_day' => $currentHour
        ];
        
        // 检查用户的常规活动时间
        if ($this->user) {
            $userActiveTimes = SecurityLog::where('user_id', $this->user->id)
                ->where('created_at', '>', now()->subDays(30))
                ->get()
                ->groupBy(function ($date) {
                    return $date->created_at->format('H'); // 按小时分组
                });
            
            $activeHours = array_keys($userActiveTimes->toArray());
            $isUnusualTime = !in_array((string) $currentHour, $activeHours);
            
            if ($isUnusualTime && count($activeHours) > 5) {
                $timeRisk += 20;
                $timeInfo['is_unusual_time'] = true;
            } else {
                $timeInfo['is_unusual_time'] = false;
            }
            
            // 检查深夜活动
            if ($currentHour >= 0 && $currentHour <= 5) {
                $timeRisk += 15;
                $timeInfo['is_late_night'] = true;
            } else {
                $timeInfo['is_late_night'] = false;
            }
        }
        
        $this->securityChecks['time_pattern'] = [
            'status' => $timeRisk < 20 ? 'safe' : ($timeRisk < 40 ? 'suspicious' : 'dangerous'),
            'risk_score' => $timeRisk,
            'info' => $timeInfo
        ];
        
        $this->riskScore += $timeRisk;
    }

    /**
     * 检查行为模式
     * 
     * @return void
     */
    protected function checkBehaviorPattern(): void
    {
        $behaviorRisk = 0;
        
        $behaviorInfo = [
            'request_method' => $this->request->method(),
            'is_ajax' => $this->request->ajax(),
            'is_json' => $this->request->expectsJson(),
            'referrer' => $this->request->header('referer'),
            'user_agent' => $this->request->userAgent(),
            'request_frequency' => 0
        ];
        
        // 检查请求频率
        if ($this->user) {
            $userId = $this->user->id;
            $cacheKey = "user_requests:{$userId}";
            
            // 获取用户最近的请求次数
            $requestCount = Cache::get($cacheKey, 0);
            
            // 增加请求计数
            Cache::put($cacheKey, $requestCount + 1, now()->addMinutes(1));
            
            $behaviorInfo['request_frequency'] = $requestCount;
            
            // 检查请求频率是否异常
            if ($requestCount > 50) { // 每分钟50次以上请求
                $behaviorRisk += 30;
            } else if ($requestCount > 30) { // 每分钟30次以上请求
                $behaviorRisk += 15;
            }
        }
        
        // 检查请求方法
        if (!in_array($this->request->method(), ['GET', 'POST'])) {
            $behaviorRisk += 10;
        }
        
        // 检查Referer头
        $referer = $this->request->header('referer');
        if ($referer) {
            $refererHost = parse_url($referer, PHP_URL_HOST);
            $currentHost = $this->request->getHost();
            
            if ($refererHost !== $currentHost) {
                $behaviorRisk += 10;
                $behaviorInfo['external_referrer'] = true;
            }
        }
        
        $this->securityChecks['behavior'] = [
            'status' => $behaviorRisk < 20 ? 'safe' : ($behaviorRisk < 40 ? 'suspicious' : 'dangerous'),
            'risk_score' => $behaviorRisk,
            'info' => $behaviorInfo
        ];
        
        $this->riskScore += $behaviorRisk;
    }

    /**
     * 检查支付API安全
     * 
     * @return void
     */
    protected function checkPaymentApiSecurity(): void
    {
        $apiRisk = 0;
        
        $apiInfo = [
            'payment_method' => $this->request->input('payment_method'),
            'api_keys_secure' => true,
            'api_keys_rotated' => true,
            'api_permissions' => 'appropriate'
        ];
        
        // 检查支付方式
        $paymentMethod = $this->request->input('payment_method');
        
        if ($paymentMethod === 'alipay') {
            // 检查支付宝API密钥安全
            $alipayAppId = config('payment.alipay.app_id');
            $alipayPrivateKey = config('payment.alipay.app_secret_cert');
            
            // 检查配置是否存在
            if (empty($alipayAppId) || empty($alipayPrivateKey)) {
                $apiRisk += 50;
                $apiInfo['api_keys_secure'] = false;
            }
            
            // 检查密钥是否为测试密钥
            if ($alipayAppId && strpos($alipayAppId, '2088') !== 0) {
                $apiRisk += 20; // 非标准支付宝商户ID格式
            }
            
            // 检查沙箱模式
            if (config('payment.alipay.sandbox') === true) {
                $apiInfo['sandbox_mode'] = true;
                // 生产环境不应使用沙箱模式
                if (config('app.env') === 'production') {
                    $apiRisk += 70;
                }
            }
        } else if ($paymentMethod === 'wechat') {
            // 检查微信支付API密钥安全
            $wechatMchId = config('payment.wechat.mch_id');
            $wechatApiKey = config('payment.wechat.mch_secret_key');
            
            // 检查配置是否存在
            if (empty($wechatMchId) || empty($wechatApiKey)) {
                $apiRisk += 50;
                $apiInfo['api_keys_secure'] = false;
            }
        } else if ($paymentMethod === 'bank_card') {
            // 检查银行卡支付安全
            $apiInfo['pci_dss_compliant'] = true;
            $apiInfo['tokenization'] = true;
        }
        
        // 检查API密钥轮换
        $lastKeyRotation = Cache::get('last_api_key_rotation', null);
        if (!$lastKeyRotation || now()->diffInDays($lastKeyRotation) > 90) {
            $apiRisk += 30;
            $apiInfo['api_keys_rotated'] = false;
        }
        
        $this->securityChecks['payment_api'] = [
            'status' => $apiRisk < 30 ? 'safe' : ($apiRisk < 60 ? 'suspicious' : 'dangerous'),
            'risk_score' => $apiRisk,
            'info' => $apiInfo
        ];
        
        $this->riskScore += $apiRisk;
    }

    /**
     * 检查服务器环境
     * 
     * @return void
     */
    protected function checkServerEnvironment(): void
    {
        $serverRisk = 0;
        
        $serverInfo = [
            'environment' => config('app.env'),
            'server_software' => $this->request->server('SERVER_SOFTWARE'),
            'php_version' => PHP_VERSION,
            'database_status' => 'connected'
        ];
        
        // 检查环境
        if (config('app.env') === 'production') {
            // 检查调试模式
            if (config('app.debug') === true) {
                $serverRisk += 50;
                $serverInfo['debug_mode'] = true;
            } else {
                $serverInfo['debug_mode'] = false;
            }
        }
        
        // 检查数据库连接
        try {
            \DB::connection()->getPdo();
        } catch (\Exception $e) {
            $serverRisk += 80;
            $serverInfo['database_status'] = 'disconnected';
        }
        
        // 检查服务器负载
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            $serverInfo['server_load'] = $load;
            
            if ($load[0] > 5) { // 高负载可能表示DDoS攻击
                $serverRisk += 30;
            }
        }
        
        // 检查磁盘空间
        if (function_exists('disk_free_space') && function_exists('disk_total_space')) {
            $freeSpace = disk_free_space('/')/1024/1024/1024;
            $totalSpace = disk_total_space('/')/1024/1024/1024;
            $freePercentage = ($freeSpace / $totalSpace) * 100;
            
            $serverInfo['disk_free_gb'] = round($freeSpace, 2);
            $serverInfo['disk_total_gb'] = round($totalSpace, 2);
            $serverInfo['disk_free_percentage'] = round($freePercentage, 2);
            
            if ($freePercentage < 10) {
                $serverRisk += 40;
            } else if ($freePercentage < 20) {
                $serverRisk += 20;
            }
        }
        
        $this->securityChecks['server'] = [
            'status' => $serverRisk < 30 ? 'safe' : ($serverRisk < 60 ? 'suspicious' : 'dangerous'),
            'risk_score' => $serverRisk,
            'info' => $serverInfo
        ];
        
        $this->riskScore += $serverRisk;
    }

    /**
     * 检查浏览器安全
     * 
     * @return void
     */
    protected function checkBrowserSecurity(): void
    {
        $browserRisk = 0;
        
        $browserInfo = [
            'browser' => $this->agent->browser(),
            'browser_version' => $this->agent->version($this->agent->browser()),
            'accept_language' => $this->request->header('accept-language'),
            'do_not_track' => $this->request->header('dnt'),
            'content_language' => $this->request->header('content-language'),
            'has_js_enabled' => $this->request->header('X-JavaScript-Enabled') === 'true',
            'has_cookies_enabled' => $this->request->header('X-Cookies-Enabled') === 'true',
            'screen_resolution' => $this->request->header('X-Screen-Resolution'),
            'color_depth' => $this->request->header('X-Color-Depth'),
            'timezone_offset' => $this->request->header('X-Timezone-Offset'),
            'webgl_vendor' => $this->request->header('X-WebGL-Vendor'),
            'webgl_renderer' => $this->request->header('X-WebGL-Renderer'),
        ];
        
        // 检查浏览器版本
        $browser = $this->agent->browser();
        $version = $this->agent->version($browser);
        
        // 检查已知的不安全浏览器版本
        $insecureBrowsers = [
            'Internet Explorer' => 11,
            'Edge' => 18,
            'Chrome' => 80,
            'Firefox' => 72,
            'Safari' => 12
        ];
        
        if (isset($insecureBrowsers[$browser]) && version_compare($version, $insecureBrowsers[$browser], '<')) {
            $browserRisk += 30;
            $browserInfo['outdated_browser'] = true;
        } else {
            $browserInfo['outdated_browser'] = false;
        }
        
        // 检查JavaScript和Cookie启用状态
        if ($this->request->header('X-JavaScript-Enabled') !== 'true') {
            $browserRisk += 20;
        }
        
        if ($this->request->header('X-Cookies-Enabled') !== 'true') {
            $browserRisk += 15;
        }
        
        // 检查浏览器指纹一致性
        $fingerprint = $this->request->header('X-Browser-Fingerprint');
        if ($fingerprint && $this->user) {
            $userFingerprints = Cache::get("user_browser_fingerprints:{$this->user->id}", []);
            
            if (!in_array($fingerprint, $userFingerprints)) {
                $browserRisk += 25;
                $browserInfo['new_fingerprint'] = true;
                
                // 缓存新的指纹
                $userFingerprints[] = $fingerprint;
                Cache::put("user_browser_fingerprints:{$this->user->id}", $userFingerprints, now()->addDays(30));
            } else {
                $browserInfo['new_fingerprint'] = false;
            }
        }
        
        $this->securityChecks['browser'] = [
            'status' => $browserRisk < 20 ? 'safe' : ($browserRisk < 40 ? 'suspicious' : 'dangerous'),
            'risk_score' => $browserRisk,
            'info' => $browserInfo
        ];
        
        $this->riskScore += $browserRisk;
    }

    /**
     * 记录安全检查结果
     * 
     * @param string $context 上下文
     * @return void
     */
    protected function logSecurityCheck(string $context): void
    {
        $log = new SecurityLog();
        $log->user_id = $this->user ? $this->user->id : null;
        $log->ip_address = $this->request->ip();
        $log->user_agent = $this->request->userAgent();
        $log->event_type = 'security_check';
        $log->context = $context;
        $log->risk_score = $this->riskScore;
        $log->metadata = [
            'checks' => $this->securityChecks,
            'url' => $this->request->fullUrl(),
            'method' => $this->request->method(),
            'session_id' => $this->request->session()->getId(),
        ];
        $log->save();
        
        // 如果风险评分很高，创建安全警报
        if ($this->riskScore > 80) {
            $alert = new SecurityAlert();
            $alert->user_id = $this->user ? $this->user->id : null;
            $alert->ip_address = $this->request->ip();
            $alert->alert_type = 'high_risk_activity';
            $alert->severity = $this->riskScore > 90 ? 'critical' : 'high';
            $alert->description = "高风险活动检测：{$context}上下文中的风险评分为{$this->riskScore}";
            $alert->metadata = [
                'security_log_id' => $log->id,
                'risk_score' => $this->riskScore,
                'checks' => $this->securityChecks,
            ];
            $alert->save();
            
            // 记录到系统日志
            Log::channel('security')->warning("高风险活动检测", [
                'user_id' => $this->user ? $this->user->id : null,
                'ip' => $this->request->ip(),
                'risk_score' => $this->riskScore,
                'context' => $context
            ]);
        }
    }

    /**
     * 获取IP地理信息
     * 
     * @param string $ip IP地址
     * @return array 地理信息
     */
    protected function getIpGeoInfo(string $ip): array
    {
        // 首先检查缓存
        $cacheKey = "ip_geo:{$ip}";
        $geoInfo = Cache::get($cacheKey);
        
        if ($geoInfo) {
            return $geoInfo;
        }
        
        // 模拟地理位置信息（实际应用中应使用真实的地理位置API）
        try {
            // 使用IP-API服务获取地理位置信息
            $response = Http::get("http://ip-api.com/json/{$ip}");
            
            if ($response->successful()) {
                $geoInfo = $response->json();
                
                // 格式化结果
                $result = [
                    'ip' => $ip,
                    'country' => $geoInfo['country'] ?? 'Unknown',
                    'country_code' => $geoInfo['countryCode'] ?? 'XX',
                    'region' => $geoInfo['regionName'] ?? 'Unknown',
                    'city' => $geoInfo['city'] ?? 'Unknown',
                    'latitude' => $geoInfo['lat'] ?? 0,
                    'longitude' => $geoInfo['lon'] ?? 0,
                    'isp' => $geoInfo['isp'] ?? 'Unknown',
                    'org' => $geoInfo['org'] ?? 'Unknown',
                    'as' => $geoInfo['as'] ?? 'Unknown',
                ];
                
                // 缓存结果（1天）
                Cache::put($cacheKey, $result, now()->addDay());
                
                return $result;
            }
        } catch (\Exception $e) {
            Log::error("获取IP地理信息失败", [
                'ip' => $ip,
                'error' => $e->getMessage()
            ]);
        }
        
        // 默认返回空信息
        return [
            'ip' => $ip,
            'country' => 'Unknown',
            'country_code' => 'XX',
            'region' => 'Unknown',
            'city' => 'Unknown',
            'latitude' => 0,
            'longitude' => 0,
        ];
    }

    /**
     * 检测VPN或代理
     * 
     * @param string $ip IP地址
     * @return bool 是否为VPN或代理
     */
    protected function detectVpnOrProxy(string $ip): bool
    {
        // 首先检查缓存
        $cacheKey = "ip_proxy:{$ip}";
        $isProxy = Cache::get($cacheKey);
        
        if ($isProxy !== null) {
            return $isProxy;
        }
        
        // 简单检测（实际应用中应使用专业的代理检测服务）
        try {
            // 使用代理检测API
            $response = Http::get("https://proxycheck.io/v2/{$ip}?key=your_api_key&vpn=1");
            
            if ($response->successful()) {
                $data = $response->json();
                $isProxy = isset($data[$ip]['proxy']) && $data[$ip]['proxy'] === 'yes';
                
                // 缓存结果（1小时）
                Cache::put($cacheKey, $isProxy, now()->addHour());
                
                return $isProxy;
            }
        } catch (\Exception $e) {
            Log::error("代理检测失败", [
                'ip' => $ip,
                'error' => $e->getMessage()
            ]);
        }
        
        // 默认返回false
        return false;
    }

    /**
     * 计算两个坐标点之间的距离（公里）
     * 
     * @param float $lat1 第一个点的纬度
     * @param float $lon1 第一个点的经度
     * @param float $lat2 第二个点的纬度
     * @param float $lon2 第二个点的经度
     * @return float 距离（公里）
     */
    protected function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // 地球半径（公里）
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * 
             sin($dLon/2) * sin($dLon/2);
             
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $distance = $earthRadius * $c;
        
        return $distance;
    }
}
