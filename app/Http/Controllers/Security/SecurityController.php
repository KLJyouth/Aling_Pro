<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Security\ZeroTrust\ZeroTrustService;
use App\Services\Security\MultiFactorAuthService;
use App\Services\Security\DeviceBindingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SecurityController extends Controller
{
    /**
     * 多因素认证服务
     * 
     * @var \App\Services\Security\MultiFactorAuthService
     */
    protected $mfaService;
    
    /**
     * 设备绑定服务
     * 
     * @var \App\Services\Security\DeviceBindingService
     */
    protected $deviceBindingService;
    
    /**
     * 构造函数
     * 
     * @param \App\Services\Security\MultiFactorAuthService $mfaService
     * @param \App\Services\Security\DeviceBindingService $deviceBindingService
     * @return void
     */
    public function __construct(
        MultiFactorAuthService $mfaService,
        DeviceBindingService $deviceBindingService
    ) {
        $this->mfaService = $mfaService;
        $this->deviceBindingService = $deviceBindingService;
    }
    
    /**
     * 显示安全验证页面
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function showVerifyPage(Request $request)
    {
        $redirectTo = $request->input('redirect_to', route('home'));
        $securityResult = $request->attributes->get('security_result', []);
        
        return view('security.verify', [
            'redirect_to' => $redirectTo,
            'security_result' => $securityResult
        ]);
    }
    
    /**
     * 处理安全验证
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'verification_code' => 'required|string',
            'redirect_to' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $user = Auth::user();
        $verificationCode = $request->input('verification_code');
        $redirectTo = $request->input('redirect_to', route('home'));
        
        // 发送验证码到用户邮箱或手机（模拟）
        if ($request->has('send_code')) {
            // 根据用户设置选择发送方式
            if ($user->phone_number) {
                // 发送短信验证码
                $result = $this->mfaService->sendMfaCode($user, 'sms');
            } else {
                // 发送邮箱验证码
                $result = $this->mfaService->sendMfaCode($user, 'email');
            }
            
            if ($result['success']) {
                return redirect()->back()->with('success', $result['message']);
            } else {
                return redirect()->back()->with('error', $result['message']);
            }
        }
        
        // 验证代码
        $method = Session::get('verification_method', $user->phone_number ? 'sms' : 'email');
        $result = $this->mfaService->verifyMfa($user, $method, $verificationCode);
        
        if ($result['success']) {
            // 标记为已验证
            Session::put('security_verified', true);
            Session::put('security_verified_at', now()->timestamp);
            
            return redirect($redirectTo)->with('success', '验证成功');
        } else {
            return redirect()->back()
                ->with('error', $result['message'])
                ->withInput();
        }
    }
    
    /**
     * 显示多因素认证验证页面
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function showMfaVerifyPage(Request $request)
    {
        $user = Auth::user();
        $methods = $this->mfaService->getUserMfaMethods($user)['methods'];
        
        return view('security.mfa_verify', [
            'methods' => $methods
        ]);
    }
    
    /**
     * 处理多因素认证验证
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verifyMfa(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'method' => 'required|string',
            'code' => 'required|string'
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $user = Auth::user();
        $method = $request->input('method');
        $code = $request->input('code');
        
        // 发送验证码
        if ($request->has('send_code')) {
            $result = $this->mfaService->sendMfaCode($user, $method);
            
            if ($result['success']) {
                return redirect()->back()->with('success', $result['message']);
            } else {
                return redirect()->back()->with('error', $result['message']);
            }
        }
        
        // 验证代码
        $result = $this->mfaService->verifyMfa($user, $method, $code);
        
        if ($result['success']) {
            // 标记为已验证
            Session::put('mfa_verified', true);
            Session::put('mfa_verified_at', now()->timestamp);
            
            // 重定向回原始URL
            $redirectUrl = Session::pull('mfa_redirect_url', route('home'));
            return redirect($redirectUrl)->with('success', '验证成功');
        } else {
            return redirect()->back()
                ->with('error', $result['message'])
                ->withInput();
        }
    }
    
    /**
     * 显示设备绑定页面
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function showDeviceBindPage(Request $request)
    {
        $user = Auth::user();
        $bindingData = $this->deviceBindingService->generateBindingQrCode($user);
        
        return view('security.device_bind', [
            'binding_data' => $bindingData
        ]);
    }
    
    /**
     * 处理设备绑定
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bindDevice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'binding_code' => 'required|string',
            'device_id' => 'required|string',
            'device_name' => 'required|string',
            'device_type' => 'required|string',
            'device_model' => 'nullable|string',
            'os_type' => 'required|string',
            'os_version' => 'nullable|string',
            'app_version' => 'nullable|string',
            'device_fingerprint' => 'nullable|string',
            'phone_number' => 'nullable|string',
            'imei' => 'nullable|string',
            'mac_address' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => '验证失败',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $bindingCode = $request->input('binding_code');
        $deviceInfo = $request->only([
            'device_id',
            'device_name',
            'device_type',
            'device_model',
            'os_type',
            'os_version',
            'app_version',
            'device_fingerprint',
            'phone_number',
            'imei',
            'mac_address'
        ]);
        
        $result = $this->deviceBindingService->bindDevice($bindingCode, $deviceInfo);
        
        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'device' => $result['device']
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 400);
        }
    }
    
    /**
     * 显示设备验证页面
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function showDeviceVerifyPage(Request $request)
    {
        return view('security.device_verify');
    }
    
    /**
     * 处理设备验证
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyDevice(Request $request)
    {
        $user = Auth::user();
        $result = $this->deviceBindingService->verifyDevice($user, $request);
        
        if ($result['success']) {
            // 标记为已验证
            Session::put('device_verified', true);
            Session::put('device_verified_at', now()->timestamp);
            
            // 重定向回原始URL
            $redirectUrl = Session::pull('device_redirect_url', route('home'));
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'redirect' => $redirectUrl
                ]);
            }
            
            return redirect($redirectUrl)->with('success', '设备验证成功');
        } else {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }
            
            return redirect()->back()
                ->with('error', $result['message'])
                ->withInput();
        }
    }
    
    /**
     * 显示安全设置页面
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function showSecuritySettings(Request $request)
    {
        $user = Auth::user();
        $mfaMethods = $this->mfaService->getUserMfaMethods($user)['methods'];
        $devices = $this->deviceBindingService->getUserDevices($user)['devices'];
        
        return view('security.settings', [
            'mfa_methods' => $mfaMethods,
            'devices' => $devices
        ]);
    }
    
    /**
     * 启用多因素认证
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function enableMfa(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'method' => 'required|string|in:app,sms,email,fingerprint'
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $user = Auth::user();
        $method = $request->input('method');
        
        $result = $this->mfaService->enableMfa($user, $method);
        
        if ($result['success']) {
            if (isset($result['data']['requires_verification']) && $result['data']['requires_verification']) {
                // 需要验证，将数据存入会话
                Session::put('mfa_setup_data', $result['data']);
                Session::put('mfa_setup_method', $method);
                
                return redirect()->route('security.mfa.setup.verify')
                    ->with('success', $result['message']);
            }
            
            return redirect()->back()->with('success', $result['message']);
        } else {
            return redirect()->back()
                ->with('error', $result['message'])
                ->withInput();
        }
    }
    
    /**
     * 显示多因素认证设置验证页面
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function showMfaSetupVerifyPage(Request $request)
    {
        $setupData = Session::get('mfa_setup_data');
        $method = Session::get('mfa_setup_method');
        
        if (!$setupData || !$method) {
            return redirect()->route('security.settings')
                ->with('error', '设置数据已过期，请重新开始');
        }
        
        return view('security.mfa_setup_verify', [
            'setup_data' => $setupData,
            'method' => $method
        ]);
    }
    
    /**
     * 验证多因素认证设置
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verifyMfaSetup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string'
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $user = Auth::user();
        $method = Session::get('mfa_setup_method');
        $code = $request->input('code');
        
        if (!$method) {
            return redirect()->route('security.settings')
                ->with('error', '设置数据已过期，请重新开始');
        }
        
        $result = $this->mfaService->verifyMfaSetup($user, $method, $code);
        
        if ($result['success']) {
            // 清除会话数据
            Session::forget('mfa_setup_data');
            Session::forget('mfa_setup_method');
            
            return redirect()->route('security.settings')
                ->with('success', $result['message']);
        } else {
            return redirect()->back()
                ->with('error', $result['message'])
                ->withInput();
        }
    }
    
    /**
     * 禁用多因素认证
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function disableMfa(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'method_id' => 'required|integer'
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $user = Auth::user();
        $methodId = $request->input('method_id');
        
        // 获取方法详情
        $methods = $this->mfaService->getUserMfaMethods($user)['methods'];
        $method = null;
        
        foreach ($methods as $m) {
            if ($m['id'] == $methodId) {
                $method = $m['method'];
                break;
            }
        }
        
        if (!$method) {
            return redirect()->back()
                ->with('error', '未找到指定的多因素认证方法');
        }
        
        $result = $this->mfaService->disableMfa($user, $method);
        
        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        } else {
            return redirect()->back()
                ->with('error', $result['message'])
                ->withInput();
        }
    }
    
    /**
     * 设置主要多因素认证方法
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function setPrimaryMfaMethod(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'method_id' => 'required|integer'
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $user = Auth::user();
        $methodId = $request->input('method_id');
        
        $result = $this->mfaService->setPrimaryMfaMethod($user, $methodId);
        
        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        } else {
            return redirect()->back()
                ->with('error', $result['message'])
                ->withInput();
        }
    }
    
    /**
     * 生成恢复代码
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function generateRecoveryCodes(Request $request)
    {
        $user = Auth::user();
        $result = $this->mfaService->generateRecoveryCodes($user);
        
        if ($result['success']) {
            return view('security.recovery_codes', [
                'recovery_codes' => $result['data']['recovery_codes']
            ]);
        } else {
            return redirect()->back()
                ->with('error', $result['message']);
        }
    }
    
    /**
     * 解绑设备
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function unbindDevice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|integer'
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $user = Auth::user();
        $deviceId = $request->input('device_id');
        
        $result = $this->deviceBindingService->unbindDevice($user, $deviceId);
        
        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        } else {
            return redirect()->back()
                ->with('error', $result['message'])
                ->withInput();
        }
    }
}
