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
        // ע�������η���
        $this->app->singleton('zero_trust', function ($app) {
            return new \App\Services\Security\ZeroTrust\ZeroTrustService($app['request']);
        });
        
        // ע���������֤����
        $this->app->singleton('mfa', function ($app) {
            return new \App\Services\Security\MultiFactorAuthService();
        });
        
        // ע���豸�󶨷���
        $this->app->singleton('device_binding', function ($app) {
            return new \App\Services\Security\DeviceBindingService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // ���ð�ȫ��־ͨ��
        Config::set('logging.channels.security', [
            'driver' => 'daily',
            'path' => storage_path('logs/security.log'),
            'level' => 'debug',
            'days' => 30,
        ]);
        
        // ������֤�¼�
        $this->registerAuthEventListeners();
    }
    
    /**
     * ע����֤�¼�������
     * 
     * @return void
     */
    protected function registerAuthEventListeners(): void
    {
        // ��¼�ɹ��¼�
        Event::listen(Login::class, function (Login $event) {
            $user = $event->user;
            $ip = request()->ip();
            
            // ��¼��¼
            $user->recordLogin($ip);
            
            Log::channel('security')->info("�û���¼�ɹ�", [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $ip,
                'user_agent' => request()->userAgent(),
            ]);
        });
        
        // �ǳ��¼�
        Event::listen(Logout::class, function (Logout $event) {
            $user = $event->user;
            
            if ($user) {
                // ������ȫ��־
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
                
                Log::channel('security')->info("�û��ǳ�", [
                    'user_id' => $user->id,
                    'ip' => request()->ip(),
                ]);
            }
        });
        
        // ��¼ʧ���¼�
        Event::listen(Failed::class, function (Failed $event) {
            $credentials = $event->credentials;
            
            // ������ȫ��־
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
            
            Log::channel('security')->warning("��¼ʧ��", [
                'email' => $credentials['email'] ?? 'unknown',
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
            
            // ����Ƿ���Ҫ��������
            $this->checkLoginFailureAlert($credentials['email'] ?? 'unknown', request()->ip());
        });
        
        // ע���¼�
        Event::listen(Registered::class, function (Registered $event) {
            $user = $event->user;
            
            // ������ȫ��־
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
            
            Log::channel('security')->info("�û�ע��", [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => request()->ip(),
            ]);
        });
        
        // ���������¼�
        Event::listen(PasswordReset::class, function (PasswordReset $event) {
            $user = $event->user;
            
            // ������ȫ��־
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
            
            Log::channel('security')->info("��������", [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => request()->ip(),
            ]);
        });
    }
    
    /**
     * ����¼ʧ�ܾ���
     * 
     * @param string $email ����
     * @param string $ip IP��ַ
     * @return void
     */
    protected function checkLoginFailureAlert(string $email, string $ip): void
    {
        // ������30�����ڵĵ�¼ʧ�ܴ���
        $failedCount = \App\Models\SecurityLog::where('ip_address', $ip)
            ->where('event_type', 'login_failed')
            ->where('created_at', '>', now()->subMinutes(30))
            ->count();
        
        // ���ʧ�ܴ�������5�Σ���������
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
                    'description' => "��⵽��ε�¼ʧ�ܳ��ԣ������Ǳ����ƽ⹥��",
                    'metadata' => [
                        'email' => $email,
                        'failed_count' => $failedCount,
                        'timestamp' => now()->timestamp,
                    ],
                ]);
                
                Log::channel('security')->alert("��ε�¼ʧ�ܾ���", [
                    'email' => $email,
                    'ip' => $ip,
                    'failed_count' => $failedCount,
                ]);
            }
        }
    }
}
