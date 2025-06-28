<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * 设置服务类
 * 
 * 提供网站设置的业务逻辑处理
 */
class SettingService
{
    /**
     * 缓存前缀
     *
     * @var string
     */
    protected $cachePrefix = 'settings:';
    
    /**
     * 缓存过期时间（秒）
     *
     * @var int
     */
    protected $cacheExpiration = 86400; // 24小时
    
    /**
     * 获取设置值
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        // 尝试从缓存获取
        $cacheKey = $this->cachePrefix . $key;
        
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        
        // 从数据库获取
        $value = Setting::getValue($key, $default);
        
        // 存入缓存
        Cache::put($cacheKey, $value, $this->cacheExpiration);
        
        return $value;
    }
    
    /**
     * 设置值
     *
     * @param string $key
     * @param mixed $value
     * @param string|null $group
     * @param string $type
     * @param string|null $description
     * @param bool $isSystem
     * @return Setting
     */
    public function set($key, $value, $group = null, $type = 'string', $description = null, $isSystem = false)
    {
        // 更新数据库
        $setting = Setting::setValue($key, $value, $group, $type, $description, $isSystem);
        
        // 更新缓存
        $cacheKey = $this->cachePrefix . $key;
        Cache::put($cacheKey, $setting->typed_value, $this->cacheExpiration);
        
        return $setting;
    }
    
    /**
     * 删除设置
     *
     * @param string $key
     * @return bool
     */
    public function delete($key)
    {
        // 从数据库删除
        $deleted = Setting::where('key', $key)->delete();
        
        // 从缓存删除
        $cacheKey = $this->cachePrefix . $key;
        Cache::forget($cacheKey);
        
        return $deleted > 0;
    }
    
    /**
     * 获取分组设置
     *
     * @param string $group
     * @return array
     */
    public function getGroup($group)
    {
        $settings = Setting::getByGroup($group);
        $result = [];
        
        foreach ($settings as $setting) {
            $result[$setting->key] = $setting->typed_value;
        }
        
        return $result;
    }
    
    /**
     * 保存分组设置
     *
     * @param string $group
     * @param array $data
     * @return bool
     */
    public function saveGroup($group, array $data)
    {
        $settings = Setting::getByGroup($group);
        $settingsMap = [];
        
        // 创建设置映射
        foreach ($settings as $setting) {
            $settingsMap[$setting->key] = $setting;
        }
        
        // 更新设置
        foreach ($data as $key => $value) {
            if (isset($settingsMap[$key])) {
                $setting = $settingsMap[$key];
                $setting->typed_value = $value;
                $setting->save();
                
                // 更新缓存
                $cacheKey = $this->cachePrefix . $key;
                Cache::put($cacheKey, $setting->typed_value, $this->cacheExpiration);
            }
        }
        
        return true;
    }
    
    /**
     * 清除设置缓存
     *
     * @return void
     */
    public function clearCache()
    {
        $settings = Setting::all();
        
        foreach ($settings as $setting) {
            $cacheKey = $this->cachePrefix . $setting->key;
            Cache::forget($cacheKey);
        }
    }
    
    /**
     * 初始化系统设置
     *
     * @return void
     */
    public function initSystemSettings()
    {
        $settings = [
            // 基本设置
            'site_name' => ['值', 'general', 'string', '网站名称', true],
            'site_description' => ['AI专业解决方案提供商', 'general', 'string', '网站描述', true],
            'site_keywords' => ['AI,人工智能,机器学习,深度学习', 'general', 'string', '网站关键词', true],
            'site_logo' => ['/assets/images/logo.png', 'general', 'string', '网站Logo', true],
            'site_favicon' => ['/favicon.ico', 'general', 'string', '网站图标', true],
            'site_icp' => ['', 'general', 'string', 'ICP备案号', true],
            'site_copyright' => ['© ' . date('Y') . ' AlingAi. All rights reserved.', 'general', 'string', '版权信息', true],
            
            // 联系方式
            'contact_email' => ['contact@alingai.com', 'contact', 'string', '联系邮箱', true],
            'contact_phone' => ['', 'contact', 'string', '联系电话', true],
            'contact_address' => ['', 'contact', 'string', '联系地址', true],
            
            // 社交媒体
            'social_weixin' => ['', 'social', 'string', '微信公众号', true],
            'social_weibo' => ['', 'social', 'string', '微博', true],
            'social_qq' => ['', 'social', 'string', 'QQ', true],
            
            // 邮件设置
            'mail_driver' => ['smtp', 'mail', 'string', '邮件驱动', true],
            'mail_host' => ['smtp.example.com', 'mail', 'string', '邮件服务器', true],
            'mail_port' => ['587', 'mail', 'integer', '邮件端口', true],
            'mail_username' => ['', 'mail', 'string', '邮件用户名', true],
            'mail_password' => ['', 'mail', 'string', '邮件密码', true],
            'mail_encryption' => ['tls', 'mail', 'string', '邮件加密方式', true],
            'mail_from_address' => ['noreply@example.com', 'mail', 'string', '发件人地址', true],
            'mail_from_name' => ['AlingAi', 'mail', 'string', '发件人名称', true],
            
            // 安全设置
            'security_login_captcha' => [true, 'security', 'boolean', '登录验证码', true],
            'security_register_captcha' => [true, 'security', 'boolean', '注册验证码', true],
            'security_login_attempts' => [5, 'security', 'integer', '最大登录尝试次数', true],
            'security_login_lockout_time' => [10, 'security', 'integer', '登录锁定时间（分钟）', true],
            
            // 注册设置
            'register_enabled' => [true, 'register', 'boolean', '开启注册', true],
            'register_verification' => [true, 'register', 'boolean', '邮箱验证', true],
            'register_default_role' => ['user', 'register', 'string', '默认角色', true],
            
            // 上传设置
            'upload_max_size' => [10, 'upload', 'integer', '最大上传大小（MB）', true],
            'upload_allowed_types' => ['jpg,jpeg,png,gif,doc,docx,pdf,xls,xlsx,zip,rar', 'upload', 'string', '允许的文件类型', true],
            'upload_disk' => ['public', 'upload', 'string', '上传磁盘', true],
            
            // API设置
            'api_throttle_enabled' => [true, 'api', 'boolean', '开启API限流', true],
            'api_throttle_attempts' => [60, 'api', 'integer', 'API限流次数', true],
            'api_throttle_time' => [1, 'api', 'integer', 'API限流时间（分钟）', true],
        ];
        
        foreach ($settings as $key => $setting) {
            list($value, $group, $type, $description, $isSystem) = $setting;
            
            if (!Setting::getByKey($key)) {
                $this->set($key, $value, $group, $type, $description, $isSystem);
            }
        }
    }
} 