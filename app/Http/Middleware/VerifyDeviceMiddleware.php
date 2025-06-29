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
     * �豸�󶨷���
     * 
     * @var \App\Services\Security\DeviceBindingService
     */
    protected $deviceBindingService;
    
    /**
     * ���캯��
     * 
     * @param \App\Services\Security\DeviceBindingService $deviceBindingService
     * @return void
     */
    public function __construct(DeviceBindingService $deviceBindingService)
    {
        $this->deviceBindingService = $deviceBindingService;
    }
    
    /**
     * �����������
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  bool  $requireVerified �Ƿ�Ҫ���豸����֤
     * @return mixed
     */
    public function handle(Request $request, Closure $next, bool $requireVerified = true)
    {
        $user = Auth::user();
        
        // ����û�δ��¼��ֱ��ͨ��
        if (!$user) {
            return $next($request);
        }
        
        // ��֤�豸
        $result = $this->deviceBindingService->verifyDevice($user, $request);
        
        // ����豸δ�󶨻�δ��֤
        if (!$result['success'] && $requireVerified) {
            // ���浱ǰURL�Ա���֤���ض������
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
        
        // ���豸��Ϣ�洢��������
        if ($result['success'] && isset($result['device'])) {
            $request->attributes->set('device', $result['device']);
        }
        
        return $next($request);
    }
}
