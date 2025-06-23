<?php

declare(strict_types=1);

namespace AlingAi\Utils;

/**
 * 设备检测器
 * 用于解析用户代理字符串，识别设备类型、浏览器、操作系统等信息
 */
class DeviceDetector
{
    private array $browserPatterns;
    private array $osPatterns;
    private array $devicePatterns;
    
    public function __construct()
    {
        $this->initializePatterns();
    }
    
    /**
     * 检测设备信息
     */
    public function detect(string $userAgent): array
    {
        if (empty($userAgent)) {
            return $this->getDefaultInfo();
        }
        
        return [
            'browser' => $this->detectBrowser($userAgent),
            'operating_system' => $this->detectOperatingSystem($userAgent),
            'device_type' => $this->detectDeviceType($userAgent),
            'is_mobile' => $this->isMobile($userAgent),
            'is_tablet' => $this->isTablet($userAgent),
            'is_desktop' => $this->isDesktop($userAgent),
            'is_bot' => $this->isBot($userAgent),
            'raw_user_agent' => $userAgent
        ];
    }
    
    /**
     * 检测浏览器信息
     */
    private function detectBrowser(string $userAgent): array
    {
        $browser = [
            'name' => 'Unknown',
            'version' => 'Unknown',
            'engine' => 'Unknown'
        ];
        
        foreach ($this->browserPatterns as $pattern => $info) {
            if (preg_match($pattern, $userAgent, $matches)) {
                $browser['name'] = $info['name'];
                $browser['version'] = $matches[1] ?? 'Unknown';
                $browser['engine'] = $info['engine'] ?? 'Unknown';
                break;
            }
        }
        
        return $browser;
    }
    
    /**
     * 检测操作系统信息
     */
    private function detectOperatingSystem(string $userAgent): array
    {
        $os = [
            'name' => 'Unknown',
            'version' => 'Unknown',
            'architecture' => 'Unknown'
        ];
        
        foreach ($this->osPatterns as $pattern => $info) {
            if (preg_match($pattern, $userAgent, $matches)) {
                $os['name'] = $info['name'];
                $os['version'] = $matches[1] ?? 'Unknown';
                $os['architecture'] = $info['architecture'] ?? 'Unknown';
                break;
            }
        }
        
        return $os;
    }
    
    /**
     * 检测设备类型
     */
    private function detectDeviceType(string $userAgent): string
    {
        foreach ($this->devicePatterns as $pattern => $type) {
            if (preg_match($pattern, $userAgent)) {
                return $type;
            }
        }
        
        return 'Desktop';
    }
    
    /**
     * 判断是否为移动设备
     */
    private function isMobile(string $userAgent): bool
    {
        $mobilePatterns = [
            '/Mobile|Android|iPhone|iPad|Windows Phone/i',
            '/Opera Mini/i',
            '/IEMobile/i'
        ];
        
        foreach ($mobilePatterns as $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * 判断是否为平板设备
     */
    private function isTablet(string $userAgent): bool
    {
        $tabletPatterns = [
            '/iPad/i',
            '/Android.*Tablet/i',
            '/Tablet PC/i'
        ];
        
        foreach ($tabletPatterns as $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * 判断是否为桌面设备
     */
    private function isDesktop(string $userAgent): bool
    {
        return !$this->isMobile($userAgent) && !$this->isTablet($userAgent);
    }
    
    /**
     * 判断是否为机器人
     */
    private function isBot(string $userAgent): bool
    {
        $botPatterns = [
            '/bot|crawler|spider|crawling/i',
            '/Googlebot/i',
            '/Bingbot/i',
            '/YandexBot/i',
            '/Baiduspider/i',
            '/Sogou/i',
            '/360Spider/i'
        ];
        
        foreach ($botPatterns as $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * 获取默认设备信息
     */
    private function getDefaultInfo(): array
    {
        return [
            'browser' => [
                'name' => 'Unknown',
                'version' => 'Unknown',
                'engine' => 'Unknown'
            ],
            'operating_system' => [
                'name' => 'Unknown',
                'version' => 'Unknown',
                'architecture' => 'Unknown'
            ],
            'device_type' => 'Desktop',
            'is_mobile' => false,
            'is_tablet' => false,
            'is_desktop' => true,
            'is_bot' => false,
            'raw_user_agent' => ''
        ];
    }
    
    /**
     * 初始化检测模式
     */
    private function initializePatterns(): void
    {
        // 浏览器检测模式
        $this->browserPatterns = [
            '/Chrome\/([0-9.]+)/i' => ['name' => 'Chrome', 'engine' => 'Blink'],
            '/Firefox\/([0-9.]+)/i' => ['name' => 'Firefox', 'engine' => 'Gecko'],
            '/Safari\/([0-9.]+)/i' => ['name' => 'Safari', 'engine' => 'WebKit'],
            '/Edge\/([0-9.]+)/i' => ['name' => 'Edge', 'engine' => 'EdgeHTML'],
            '/MSIE\s([0-9.]+)/i' => ['name' => 'Internet Explorer', 'engine' => 'Trident'],
            '/Opera\/([0-9.]+)/i' => ['name' => 'Opera', 'engine' => 'Blink'],
            '/OPR\/([0-9.]+)/i' => ['name' => 'Opera', 'engine' => 'Blink']
        ];
        
        // 操作系统检测模式
        $this->osPatterns = [
            '/Windows NT ([0-9.]+)/i' => ['name' => 'Windows', 'architecture' => 'x64'],
            '/Mac OS X ([0-9._]+)/i' => ['name' => 'macOS', 'architecture' => 'x64'],
            '/Linux/i' => ['name' => 'Linux', 'architecture' => 'x64'],
            '/Android ([0-9.]+)/i' => ['name' => 'Android', 'architecture' => 'ARM'],
            '/iPhone OS ([0-9._]+)/i' => ['name' => 'iOS', 'architecture' => 'ARM'],
            '/iPad.*OS ([0-9._]+)/i' => ['name' => 'iOS', 'architecture' => 'ARM']
        ];
        
        // 设备类型检测模式
        $this->devicePatterns = [
            '/iPhone/i' => 'Smartphone',
            '/iPad/i' => 'Tablet',
            '/Android.*Mobile/i' => 'Smartphone',
            '/Android.*Tablet/i' => 'Tablet',
            '/Windows Phone/i' => 'Smartphone',
            '/BlackBerry/i' => 'Smartphone',
            '/PlayBook/i' => 'Tablet'
        ];
    }
} 