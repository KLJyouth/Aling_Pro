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
     * ��������֤����
     * 
     * @var \App\Services\Security\MultiFactorAuthService
     */
    protected $mfaService;
    
    /**
     * �豸�󶨷���
     * 
     * @var \App\Services\Security\DeviceBindingService
     */
    protected $deviceBindingService;
    
    /**
     * ���캯��
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
     * ��ʾ��ȫ��֤ҳ��
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
     * ����ȫ��֤
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
        
        // ������֤�뵽�û�������ֻ���ģ�⣩
        if ($request->has('send_code')) {
            // �����û�����ѡ���ͷ�ʽ
            if ($user->phone_number) {
                // ���Ͷ�����֤��
                $result = $this->mfaService->sendMfaCode($user, 'sms');
            } else {
                // ����������֤��
                $result = $this->mfaService->sendMfaCode($user, 'email');
            }
            
            if ($result['success']) {
                return redirect()->back()->with('success', $result['message']);
            } else {
                return redirect()->back()->with('error', $result['message']);
            }
        }
        
        // ��֤����
        $method = Session::get('verification_method', $user->phone_number ? 'sms' : 'email');
        $result = $this->mfaService->verifyMfa($user, $method, $verificationCode);
        
        if ($result['success']) {
            // ���Ϊ����֤
            Session::put('security_verified', true);
            Session::put('security_verified_at', now()->timestamp);
            
            return redirect($redirectTo)->with('success', '��֤�ɹ�');
        } else {
            return redirect()->back()
                ->with('error', $result['message'])
                ->withInput();
        }
    }
    
    /**
     * ��ʾ��������֤��֤ҳ��
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
     * �����������֤��֤
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
        
        // ������֤��
        if ($request->has('send_code')) {
            $result = $this->mfaService->sendMfaCode($user, $method);
            
            if ($result['success']) {
                return redirect()->back()->with('success', $result['message']);
            } else {
                return redirect()->back()->with('error', $result['message']);
            }
        }
        
        // ��֤����
        $result = $this->mfaService->verifyMfa($user, $method, $code);
        
        if ($result['success']) {
            // ���Ϊ����֤
            Session::put('mfa_verified', true);
            Session::put('mfa_verified_at', now()->timestamp);
            
            // �ض����ԭʼURL
            $redirectUrl = Session::pull('mfa_redirect_url', route('home'));
            return redirect($redirectUrl)->with('success', '��֤�ɹ�');
        } else {
            return redirect()->back()
                ->with('error', $result['message'])
                ->withInput();
        }
    }
    
    /**
     * ��ʾ�豸��ҳ��
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
     * �����豸��
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
                'message' => '��֤ʧ��',
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
     * ��ʾ�豸��֤ҳ��
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function showDeviceVerifyPage(Request $request)
    {
        return view('security.device_verify');
    }
    
    /**
     * �����豸��֤
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyDevice(Request $request)
    {
        $user = Auth::user();
        $result = $this->deviceBindingService->verifyDevice($user, $request);
        
        if ($result['success']) {
            // ���Ϊ����֤
            Session::put('device_verified', true);
            Session::put('device_verified_at', now()->timestamp);
            
            // �ض����ԭʼURL
            $redirectUrl = Session::pull('device_redirect_url', route('home'));
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'redirect' => $redirectUrl
                ]);
            }
            
            return redirect($redirectUrl)->with('success', '�豸��֤�ɹ�');
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
     * ��ʾ��ȫ����ҳ��
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
     * ���ö�������֤
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
                // ��Ҫ��֤�������ݴ���Ự
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
     * ��ʾ��������֤������֤ҳ��
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
                ->with('error', '���������ѹ��ڣ������¿�ʼ');
        }
        
        return view('security.mfa_setup_verify', [
            'setup_data' => $setupData,
            'method' => $method
        ]);
    }
    
    /**
     * ��֤��������֤����
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
                ->with('error', '���������ѹ��ڣ������¿�ʼ');
        }
        
        $result = $this->mfaService->verifyMfaSetup($user, $method, $code);
        
        if ($result['success']) {
            // ����Ự����
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
     * ���ö�������֤
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
        
        // ��ȡ��������
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
                ->with('error', 'δ�ҵ�ָ���Ķ�������֤����');
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
     * ������Ҫ��������֤����
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
     * ���ɻָ�����
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
     * ����豸
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
