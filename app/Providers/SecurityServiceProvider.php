<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\PasswordReset;

class SecurityServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // 注册零信任服务
        $this->app->singleton('zero_trust', function ($app) {
            return new \App\Services\Security\ZeroTrust\ZeroTrustService($app['request']);
        });
        
        // 注册多因素认证服务
        $this->app->singleton('mfa', function ($app) {
            return new \App\Services\Security\MultiFactorAuthService();
        });
        
        // 注册设备绑定服务
        $this->app->singleton('device_binding', function ($app) {
            return new \App\Services\Security\DeviceBindingService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // 配置安全日志通道
        Config::set('logging.channels.security', [
            'driver' => 'daily',
            'path' => storage_path('logs/security.log'),
            'level' => 'debug',
            'days' => 30,
        ]);
        
        // 监听认证事件
        $this->registerAuthEventListeners();
    }
    
    /**
     * 注册认证事件监听器
     * 
     * @return void
     */
    protected function registerAuthEventListeners(): void
    {
        // 登录成功事件
        Event::listen(Login::class, function (Login $event) {
            $user = $event->user;
            $ip = request()->ip();
            
            // 记录登录
            $user->recordLogin($ip);
            
            Log::channel('security')->info("用户登录成功", [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $ip,
                'user_agent' => request()->userAgent(),
            ]);
        });
        
        // 登出事件
        Event::listen(Logout::class, function (Logout $event) {
            $user = $event->user;
            
            if ($user) {
                // 创建安全日志
                \App\Models\SecurityLog::create([
                    'user_id' => $user->id,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'event_type' => 'logout',
                    'context' => 'auth',
                    'metadata' => [
                        'timestamp' => now()->timestamp,
                    ],
                ]);
                
                Log::channel('security')->info("用户登出", [
                    'user_id' => $user->id,
                    'ip' => request()->ip(),
                ]);
            }
        });
        
        // 登录失败事件
        Event::listen(Failed::class, function (Failed $event) {
            $credentials = $event->credentials;
            
            // 创建安全日志
            \App\Models\SecurityLog::create([
                'user_id' => null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'event_type' => 'login_failed',
                'context' => 'auth',
                'risk_score' => 50,
                'metadata' => [
                    'email' => $credentials['email'] ?? 'unknown',
                    'timestamp' => now()->timestamp,
                ],
            ]);
            
            Log::channel('security')->warning("登录失败", [
                'email' => $credentials['email'] ?? 'unknown',
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
            
            // 检查是否需要创建警报
            $this->checkLoginFailureAlert($credentials['email'] ?? 'unknown', request()->ip());
        });
        
        // 注册事件
        Event::listen(Registered::class, function (Registered $event) {
            $user = $event->user;
            
            // 创建安全日志
            \App\Models\SecurityLog::create([
                'user_id' => $user->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'event_type' => 'register',
                'context' => 'auth',
                'metadata' => [
                    'email' => $user->email,
                    'timestamp' => now()->timestamp,
                ],
            ]);
            
            Log::channel('security')->info("用户注册", [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => request()->ip(),
            ]);
        });
        
        // 密码重置事件
        Event::listen(PasswordReset::class, function (PasswordReset $event) {
            $user = $event->user;
            
            // 创建安全日志
            \App\Models\SecurityLog::create([
                'user_id' => $user->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'event_type' => 'password_reset',
                'context' => 'auth',
                'metadata' => [
                    'email' => $user->email,
                    'timestamp' => now()->timestamp,
                ],
            ]);
            
            Log::channel('security')->info("密码重置", [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => request()->ip(),
            ]);
        });
    }
    
    /**
     * 检查登录失败警报
     * 
     * @param string $email 邮箱
     * @param string $ip IP地址
     * @return void
     */
    protected function checkLoginFailureAlert(string $email, string $ip): void
    {
        // 检查最近30分钟内的登录失败次数
        $failedCount = \App\Models\SecurityLog::where('ip_address', $ip)
            ->where('event_type', 'login_failed')
            ->where('created_at', '>', now()->subMinutes(30))
            ->count();
        
        // 如果失败次数超过5次，创建警报
        if ($failedCount >= 5) {
            $existingAlert = \App\Models\SecurityAlert::where('ip_address', $ip)
                ->where('alert_type', 'failed_login_attempts')
                ->where('is_resolved', false)
                ->where('created_at', '>', now()->subHours(1))
                ->exists();
            
            if (!$existingAlert) {
                \App\Models\SecurityAlert::create([
                    'ip_address' => $ip,
                    'alert_type' => 'failed_login_attempts',
                    'severity' => $failedCount >= 10 ? 'high' : 'medium',
                    'description' => "检测到多次登录失败尝试，可能是暴力破解攻击",
                    'metadata' => [
                        'email' => $email,
                        'failed_count' => $failedCount,
                        'timestamp' => now()->timestamp,
                    ],
                ]);
                
                Log::channel('security')->alert("多次登录失败警报", [
                    'email' => $email,
                    'ip' => $ip,
                    'failed_count' => $failedCount,
                ]);
            }
        }
    }
}
