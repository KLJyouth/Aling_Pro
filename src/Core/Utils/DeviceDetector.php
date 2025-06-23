<?php

namespace AlingAi\Core\Utils;

/**
 * 设备检测器
 * 
 * 提供用户代理字符串解析，识别浏览器类型、操作系统和设备信息
 * 
 * @version 1.0.0
 * @author AlingAi Team
 */
class DeviceDetector
{
    /**
     * 检测设备信息
     * 
     * @param string $userAgent 用户代理字符串
     * @return array 设备信息
     */
    public function detect(string $userAgent): array
    {
        $result = [
            'device_type' => $this->getDeviceType($userAgent),
            'os' => $this->getOS($userAgent),
            'browser' => $this->getBrowser($userAgent),
            'device' => $this->getDeviceInfo($userAgent)
        ];
        
        return $result;
    }
    
    /**
     * 获取设备类型
     * 
     * @param string $userAgent 用户代理字符串
     * @return string 设备类型
     */
    private function getDeviceType(string $userAgent): string
    {
        $userAgent = strtolower($userAgent);
        
        // 检测是否为API调用
        if (strpos($userAgent, 'curl') !== false || 
            strpos($userAgent, 'wget') !== false ||
            strpos($userAgent, 'postman') !== false ||
            strpos($userAgent, 'insomnia') !== false ||
            strpos($userAgent, 'axios') !== false ||
            strpos($userAgent, 'okhttp') !== false ||
            strpos($userAgent, 'api') !== false) {
            return 'api';
        }
        
        // 检测移动设备
        if (strpos($userAgent, 'mobile') !== false || 
            strpos($userAgent, 'android') !== false ||
            strpos($userAgent, 'iphone') !== false) {
            return 'mobile';
        }
        
        // 检测平板设备
        if (strpos($userAgent, 'ipad') !== false || 
            strpos($userAgent, 'tablet') !== false) {
            return 'tablet';
        }
        
        // 默认为桌面设备
        return 'desktop';
    }
    
    /**
     * 获取操作系统信息
     * 
     * @param string $userAgent 用户代理字符串
     * @return string|null 操作系统信息
     */
    private function getOS(string $userAgent): ?string
    {
        $userAgent = strtolower($userAgent);
        
        $osPatterns = [
            'windows nt 10.0' => 'Windows 10',
            'windows nt 6.3' => 'Windows 8.1',
            'windows nt 6.2' => 'Windows 8',
            'windows nt 6.1' => 'Windows 7',
            'windows nt 6.0' => 'Windows Vista',
            'windows nt 5.2' => 'Windows Server 2003/XP x64',
            'windows nt 5.1' => 'Windows XP',
            'windows xp' => 'Windows XP',
            'windows nt 5.0' => 'Windows 2000',
            'windows me' => 'Windows ME',
            'win98' => 'Windows 98',
            'win95' => 'Windows 95',
            'win16' => 'Windows 3.11',
            'macintosh|mac os x' => 'Mac OS X',
            'mac_powerpc' => 'Mac OS 9',
            'linux' => 'Linux',
            'ubuntu' => 'Ubuntu',
            'iphone' => 'iOS',
            'ipod' => 'iOS',
            'ipad' => 'iOS',
            'android' => 'Android',
            'blackberry' => 'BlackBerry',
            'webos' => 'webOS',
            'cros' => 'Chrome OS'
        ];
        
        foreach ($osPatterns as $pattern => $name) {
            if (preg_match('/' . $pattern . '/i', $userAgent)) {
                // 获取Android版本
                if ($name === 'Android') {
                    preg_match('/android\s([0-9.]+)/i', $userAgent, $matches);
                    if (!empty($matches[1])) {
                        return 'Android ' . $matches[1];
                    }
                }
                
                // 获取iOS版本
                if ($name === 'iOS') {
                    preg_match('/os\s([0-9_]+)/i', $userAgent, $matches);
                    if (!empty($matches[1])) {
                        return 'iOS ' . str_replace('_', '.', $matches[1]);
                    }
                }
                
                return $name;
            }
        }
        
        return null;
    }
    
    /**
     * 获取浏览器信息
     * 
     * @param string $userAgent 用户代理字符串
     * @return string|null 浏览器信息
     */
    private function getBrowser(string $userAgent): ?string
    {
        $userAgent = strtolower($userAgent);
        
        $browserPatterns = [
            'edge' => 'Microsoft Edge',
            'msie' => 'Internet Explorer',
            'trident' => 'Internet Explorer',
            'firefox' => 'Mozilla Firefox',
            'chrome' => 'Google Chrome',
            'safari' => 'Safari',
            'opera' => 'Opera',
            'netscape' => 'Netscape',
            'maxthon' => 'Maxthon',
            'konqueror' => 'Konqueror',
            'mobile' => 'Mobile Browser',
            'curl' => 'cURL',
            'wget' => 'Wget',
            'postman' => 'Postman',
            'axios' => 'Axios',
            'okhttp' => 'OkHttp'
        ];
        
        foreach ($browserPatterns as $pattern => $name) {
            if (strpos($userAgent, $pattern) !== false) {
                return $name;
            }
        }
        
        return null;
    }
    
    /**
     * 获取设备具体信息
     * 
     * @param string $userAgent 用户代理字符串
     * @return array 设备具体信息
     */
    private function getDeviceInfo(string $userAgent): array
    {
        $deviceInfo = [];
        
        // 检测移动设备品牌
        $mobilePatterns = [
            'iphone' => 'Apple iPhone',
            'ipad' => 'Apple iPad',
            'samsung' => 'Samsung',
            'huawei' => 'Huawei',
            'xiaomi' => 'Xiaomi',
            'oppo' => 'OPPO',
            'vivo' => 'Vivo',
            'oneplus' => 'OnePlus',
            'nokia' => 'Nokia',
            'lg' => 'LG',
            'sony' => 'Sony',
            'motorola' => 'Motorola',
            'htc' => 'HTC'
        ];
        
        foreach ($mobilePatterns as $pattern => $brand) {
            if (stripos($userAgent, $pattern) !== false) {
                $deviceInfo['brand'] = $brand;
                break;
            }
        }
        
        // 获取屏幕信息(如果在user-agent中包含)
        if (preg_match('/(\d{3,4}x\d{3,4})/', $userAgent, $matches)) {
            $deviceInfo['screen'] = $matches[1];
        }
        
        return $deviceInfo;
    }
} 