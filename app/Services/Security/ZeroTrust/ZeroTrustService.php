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
     * �������
     * 
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * �û����������
     * 
     * @var \Jenssegers\Agent\Agent
     */
    protected $agent;

    /**
     * ��ǰ�û�
     * 
     * @var \App\Models\User|null
     */
    protected $user;

    /**
     * ��������
     * 
     * @var int
     */
    protected $riskScore = 0;

    /**
     * ��ȫ�����
     * 
     * @var array
     */
    protected $securityChecks = [];

    /**
     * ���캯��
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
     * ִ��ȫ�氲ȫ���
     * 
     * @param string $context �����ģ��磺login, payment, api��
     * @return array ��ȫ�����
     */
    public function runFullSecurityCheck(string $context = '): array
    {
        // ���÷�������
        $this->riskScore = 0;
        $this->securityChecks = [];
        
        // ����豸��Ϣ
        $this->checkDeviceFingerprint();
        
        // ������λ��
        $this->checkGeolocation();
        
        // ������绷��
        $this->checkNetworkEnvironment();
        
        // ���ʱ��ģʽ
        $this->checkTimePattern();
        
        // �����Ϊģʽ
        $this->checkBehaviorPattern();
        
        // ���API��Կ��ȫ
        if ($context === 'payment') {
            $this->checkPaymentApiSecurity();
        }
        
        // ������������
        $this->checkServerEnvironment();
        
        // ����������ȫ
        $this->checkBrowserSecurity();
        
        // ��¼��ȫ�����
        $this->logSecurityCheck($context);
        
        // ���ذ�ȫ�����
        return [
            'risk_score' => $this->riskScore,
            'checks' => $this->securityChecks,
            'is_suspicious' => $this->riskScore > 70,
            'is_dangerous' => $this->riskScore > 90,
            'requires_additional_verification' => $this->riskScore > 50,
        ];
    }

    /**
     * ����豸ָ��
     * 
     * @return void
     */
    protected function checkDeviceFingerprint(): void
    {
        $deviceFingerprint = $this->request->header('X-Device-Fingerprint');
        $deviceId = $this->request->header('X-Device-ID');
        $isKnownDevice = false;
        $deviceRisk = 0;
        
        // ����Ƿ�Ϊ��֪�豸
        if ($this->user) {
            $knownDevices = UserDevice::where('user_id', $this->user->id)->get();
            
            foreach ($knownDevices as $device) {
                if ($device->device_fingerprint === $deviceFingerprint || $device->device_id === $deviceId) {
                    $isKnownDevice = true;
                    
                    // �����豸���ʱ��
                    $device->last_active_at = now();
                    $device->save();
                    
                    break;
                }
            }
            
            if (!$isKnownDevice && ($deviceFingerprint || $deviceId)) {
                // ���豸������������
                $deviceRisk += 30;
            }
        }
        
        // ����豸����
        $deviceType = $this->agent->deviceType();
        $platform = $this->agent->platform();
        $browser = $this->agent->browser();
        
        // �ռ��豸��Ϣ
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
            'fingerprint' => substr($deviceFingerprint, 0, 16) . '...' // �ض�ָ�ƣ���������־
        ];
        
        // �������˻��쳣�豸
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
     * ������λ��
     * 
     * @return void
     */
    protected function checkGeolocation(): void
    {
        $ip = $this->request->ip();
        $geoRisk = 0;
        
        // ��ȡ����λ����Ϣ
        $geoInfo = $this->getIpGeoInfo($ip);
        
        // ����û�����λ��
        $isKnownLocation = false;
        $distanceFromKnownLocations = PHP_INT_MAX;
        
        if ($this->user) {
            // ��ȡ�û�����ĵ�¼λ��
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
                        // ������루�򻯰汾��
                        $distance = $this->calculateDistance(
                            $prevLat, 
                            $prevLng, 
                            $geoInfo['latitude'], 
                            $geoInfo['longitude']
                        );
                        
                        if ($distance < 50) { // 50��������Ϊ��ͬ����
                            $isKnownLocation = true;
                        }
                        
                        $distanceFromKnownLocations = min($distanceFromKnownLocations, $distance);
                    }
                    
                    // ���������ͬҲ���ͷ���
                    if (isset($location->metadata['geo']['country']) && 
                        isset($geoInfo['country']) && 
                        $location->metadata['geo']['country'] === $geoInfo['country']) {
                        $isKnownLocation = true;
                    }
                }
            }
            
            if (!$isKnownLocation) {
                $geoRisk += 25;
                
                // �������ǳ�Զ�����ӷ�������
                if ($distanceFromKnownLocations > 1000) { // 1000��������
                    $geoRisk += 15;
                }
            }
        }
        
        // ���߷��չ���/����
        $highRiskCountries = ['North Korea', 'Iran', 'Syria', 'Sudan', 'Cuba'];
        if (isset($geoInfo['country']) && in_array($geoInfo['country'], $highRiskCountries)) {
            $geoRisk += 50;
        }
        
        // ���VPN/����ʹ��
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
     * ������绷��
     * 
     * @return void
     */
    protected function checkNetworkEnvironment(): void
    {
        $networkRisk = 0;
        
        // �����������
        $connectionType = $this->request->header('X-Connection-Type');
        $networkInfo = [
            'connection_type' => $connectionType,
            'is_secure' => $this->request->secure(),
            'protocol' => $this->request->server('SERVER_PROTOCOL'),
            'port' => $this->request->server('SERVER_PORT'),
        ];
        
        // ����Ƿ�ʹ�ð�ȫ����
        if (!$this->request->secure()) {
            $networkRisk += 40;
        }
        
        // ���TLS�汾
        $tlsVersion = $this->request->server('SSL_PROTOCOL') ?? 'unknown';
        $networkInfo['tls_version'] = $tlsVersion;
        
        if ($tlsVersion === 'TLSv1' || $tlsVersion === 'TLSv1.1') {
            $networkRisk += 30; // �Ͼɵ�TLS�汾
        }
        
        // ���HTTP����ͷ
        $headers = $this->request->headers->all();
        $suspiciousHeaders = [];
        
        // �����ɵ�����ͷ
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
     * ���ʱ��ģʽ
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
        
        // ����û��ĳ���ʱ��
        if ($this->user) {
            $userActiveTimes = SecurityLog::where('user_id', $this->user->id)
                ->where('created_at', '>', now()->subDays(30))
                ->get()
                ->groupBy(function ($date) {
                    return $date->created_at->format('H'); // ��Сʱ����
                });
            
            $activeHours = array_keys($userActiveTimes->toArray());
            $isUnusualTime = !in_array((string) $currentHour, $activeHours);
            
            if ($isUnusualTime && count($activeHours) > 5) {
                $timeRisk += 20;
                $timeInfo['is_unusual_time'] = true;
            } else {
                $timeInfo['is_unusual_time'] = false;
            }
            
            // �����ҹ�
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
     * �����Ϊģʽ
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
        
        // �������Ƶ��
        if ($this->user) {
            $userId = $this->user->id;
            $cacheKey = "user_requests:{$userId}";
            
            // ��ȡ�û�������������
            $requestCount = Cache::get($cacheKey, 0);
            
            // �����������
            Cache::put($cacheKey, $requestCount + 1, now()->addMinutes(1));
            
            $behaviorInfo['request_frequency'] = $requestCount;
            
            // �������Ƶ���Ƿ��쳣
            if ($requestCount > 50) { // ÿ����50����������
                $behaviorRisk += 30;
            } else if ($requestCount > 30) { // ÿ����30����������
                $behaviorRisk += 15;
            }
        }
        
        // ������󷽷�
        if (!in_array($this->request->method(), ['GET', 'POST'])) {
            $behaviorRisk += 10;
        }
        
        // ���Refererͷ
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
     * ���֧��API��ȫ
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
        
        // ���֧����ʽ
        $paymentMethod = $this->request->input('payment_method');
        
        if ($paymentMethod === 'alipay') {
            // ���֧����API��Կ��ȫ
            $alipayAppId = config('payment.alipay.app_id');
            $alipayPrivateKey = config('payment.alipay.app_secret_cert');
            
            // ��������Ƿ����
            if (empty($alipayAppId) || empty($alipayPrivateKey)) {
                $apiRisk += 50;
                $apiInfo['api_keys_secure'] = false;
            }
            
            // �����Կ�Ƿ�Ϊ������Կ
            if ($alipayAppId && strpos($alipayAppId, '2088') !== 0) {
                $apiRisk += 20; // �Ǳ�׼֧�����̻�ID��ʽ
            }
            
            // ���ɳ��ģʽ
            if (config('payment.alipay.sandbox') === true) {
                $apiInfo['sandbox_mode'] = true;
                // ����������Ӧʹ��ɳ��ģʽ
                if (config('app.env') === 'production') {
                    $apiRisk += 70;
                }
            }
        } else if ($paymentMethod === 'wechat') {
            // ���΢��֧��API��Կ��ȫ
            $wechatMchId = config('payment.wechat.mch_id');
            $wechatApiKey = config('payment.wechat.mch_secret_key');
            
            // ��������Ƿ����
            if (empty($wechatMchId) || empty($wechatApiKey)) {
                $apiRisk += 50;
                $apiInfo['api_keys_secure'] = false;
            }
        } else if ($paymentMethod === 'bank_card') {
            // ������п�֧����ȫ
            $apiInfo['pci_dss_compliant'] = true;
            $apiInfo['tokenization'] = true;
        }
        
        // ���API��Կ�ֻ�
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
     * ������������
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
        
        // ��黷��
        if (config('app.env') === 'production') {
            // ������ģʽ
            if (config('app.debug') === true) {
                $serverRisk += 50;
                $serverInfo['debug_mode'] = true;
            } else {
                $serverInfo['debug_mode'] = false;
            }
        }
        
        // ������ݿ�����
        try {
            \DB::connection()->getPdo();
        } catch (\Exception $e) {
            $serverRisk += 80;
            $serverInfo['database_status'] = 'disconnected';
        }
        
        // ������������
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            $serverInfo['server_load'] = $load;
            
            if ($load[0] > 5) { // �߸��ؿ��ܱ�ʾDDoS����
                $serverRisk += 30;
            }
        }
        
        // �����̿ռ�
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
     * ����������ȫ
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
        
        // ���������汾
        $browser = $this->agent->browser();
        $version = $this->agent->version($browser);
        
        // �����֪�Ĳ���ȫ������汾
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
        
        // ���JavaScript��Cookie����״̬
        if ($this->request->header('X-JavaScript-Enabled') !== 'true') {
            $browserRisk += 20;
        }
        
        if ($this->request->header('X-Cookies-Enabled') !== 'true') {
            $browserRisk += 15;
        }
        
        // ��������ָ��һ����
        $fingerprint = $this->request->header('X-Browser-Fingerprint');
        if ($fingerprint && $this->user) {
            $userFingerprints = Cache::get("user_browser_fingerprints:{$this->user->id}", []);
            
            if (!in_array($fingerprint, $userFingerprints)) {
                $browserRisk += 25;
                $browserInfo['new_fingerprint'] = true;
                
                // �����µ�ָ��
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
     * ��¼��ȫ�����
     * 
     * @param string $context ������
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
        
        // ����������ֺܸߣ�������ȫ����
        if ($this->riskScore > 80) {
            $alert = new SecurityAlert();
            $alert->user_id = $this->user ? $this->user->id : null;
            $alert->ip_address = $this->request->ip();
            $alert->alert_type = 'high_risk_activity';
            $alert->severity = $this->riskScore > 90 ? 'critical' : 'high';
            $alert->description = "�߷��ջ��⣺{$context}�������еķ�������Ϊ{$this->riskScore}";
            $alert->metadata = [
                'security_log_id' => $log->id,
                'risk_score' => $this->riskScore,
                'checks' => $this->securityChecks,
            ];
            $alert->save();
            
            // ��¼��ϵͳ��־
            Log::channel('security')->warning("�߷��ջ���", [
                'user_id' => $this->user ? $this->user->id : null,
                'ip' => $this->request->ip(),
                'risk_score' => $this->riskScore,
                'context' => $context
            ]);
        }
    }

    /**
     * ��ȡIP������Ϣ
     * 
     * @param string $ip IP��ַ
     * @return array ������Ϣ
     */
    protected function getIpGeoInfo(string $ip): array
    {
        // ���ȼ�黺��
        $cacheKey = "ip_geo:{$ip}";
        $geoInfo = Cache::get($cacheKey);
        
        if ($geoInfo) {
            return $geoInfo;
        }
        
        // ģ�����λ����Ϣ��ʵ��Ӧ����Ӧʹ����ʵ�ĵ���λ��API��
        try {
            // ʹ��IP-API�����ȡ����λ����Ϣ
            $response = Http::get("http://ip-api.com/json/{$ip}");
            
            if ($response->successful()) {
                $geoInfo = $response->json();
                
                // ��ʽ�����
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
                
                // ��������1�죩
                Cache::put($cacheKey, $result, now()->addDay());
                
                return $result;
            }
        } catch (\Exception $e) {
            Log::error("��ȡIP������Ϣʧ��", [
                'ip' => $ip,
                'error' => $e->getMessage()
            ]);
        }
        
        // Ĭ�Ϸ��ؿ���Ϣ
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
     * ���VPN�����
     * 
     * @param string $ip IP��ַ
     * @return bool �Ƿ�ΪVPN�����
     */
    protected function detectVpnOrProxy(string $ip): bool
    {
        // ���ȼ�黺��
        $cacheKey = "ip_proxy:{$ip}";
        $isProxy = Cache::get($cacheKey);
        
        if ($isProxy !== null) {
            return $isProxy;
        }
        
        // �򵥼�⣨ʵ��Ӧ����Ӧʹ��רҵ�Ĵ��������
        try {
            // ʹ�ô�����API
            $response = Http::get("https://proxycheck.io/v2/{$ip}?key=your_api_key&vpn=1");
            
            if ($response->successful()) {
                $data = $response->json();
                $isProxy = isset($data[$ip]['proxy']) && $data[$ip]['proxy'] === 'yes';
                
                // ��������1Сʱ��
                Cache::put($cacheKey, $isProxy, now()->addHour());
                
                return $isProxy;
            }
        } catch (\Exception $e) {
            Log::error("������ʧ��", [
                'ip' => $ip,
                'error' => $e->getMessage()
            ]);
        }
        
        // Ĭ�Ϸ���false
        return false;
    }

    /**
     * �������������֮��ľ��루���
     * 
     * @param float $lat1 ��һ�����γ��
     * @param float $lon1 ��һ����ľ���
     * @param float $lat2 �ڶ������γ��
     * @param float $lon2 �ڶ�����ľ���
     * @return float ���루���
     */
    protected function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // ����뾶�����
        
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
