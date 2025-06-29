<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Services\Security\DeviceBindingService;

class VerifyDeviceMiddleware
{
    /**
     * 设备绑定服务
     * 
     * @var \App\Services\Security\DeviceBindingService
     */
    protected $deviceBindingService;
    
    /**
     * 构造函数
     * 
     * @param \App\Services\Security\DeviceBindingService $deviceBindingService
     * @return void
     */
    public function __construct(DeviceBindingService $deviceBindingService)
    {
        $this->deviceBindingService = $deviceBindingService;
    }
    
    /**
     * 处理传入的请求
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  bool  $requireVerified 是否要求设备已验证
     * @return mixed
     */
    public function handle(Request $request, Closure $next, bool $requireVerified = true)
    {
        $user = Auth::user();
        
        // 如果用户未登录，直接通过
        if (!$user) {
            return $next($request);
        }
        
        // 验证设备
        $result = $this->deviceBindingService->verifyDevice($user, $request);
        
        // 如果设备未绑定或未验证
        if (!$result['success'] && $requireVerified) {
            // 保存当前URL以便验证后重定向回来
            Session::put('device_redirect_url', $request->fullUrl());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'code' => $result['requires_binding'] ? 'DEVICE_BINDING_REQUIRED' : 'DEVICE_VERIFICATION_REQUIRED',
                    'redirect' => $result['requires_binding'] 
                        ? route('auth.device.bind') 
                        : route('auth.device.verify')
                ], 403);
            }
            
            if ($result['requires_binding']) {
                return redirect()->route('auth.device.bind');
            } else {
                return redirect()->route('auth.device.verify');
            }
        }
        
        // 将设备信息存储在请求中
        if ($result['success'] && isset($result['device'])) {
            $request->attributes->set('device', $result['device']);
        }
        
        return $next($request);
    }
}
