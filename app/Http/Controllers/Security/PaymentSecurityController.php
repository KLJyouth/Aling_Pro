<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Security\ZeroTrust\ZeroTrustService;
use App\Services\Security\MultiFactorAuthService;
use App\Services\Security\DeviceBindingService;
use App\Services\Payment\PaymentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PaymentSecurityController extends Controller
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
     * ֧������
     * 
     * @var \App\Services\Payment\PaymentService
     */
    protected $paymentService;
    
    /**
     * ���캯��
     * 
     * @param \App\Services\Security\MultiFactorAuthService $mfaService
     * @param \App\Services\Security\DeviceBindingService $deviceBindingService
     * @param \App\Services\Payment\PaymentService $paymentService
     * @return void
     */
    public function __construct(
        MultiFactorAuthService $mfaService,
        DeviceBindingService $deviceBindingService,
        PaymentService $paymentService
    ) {
        $this->mfaService = $mfaService;
        $this->deviceBindingService = $deviceBindingService;
        $this->paymentService = $paymentService;
    }
    
    /**
     * ��ʾ֧����ȫ��֤ҳ��
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function showPaymentVerifyPage(Request $request)
    {
        $orderId = $request->input('order_id');
        $paymentMethod = $request->input('payment_method');
        
        // ��鶩���Ƿ����
        $order = $this->paymentService->getOrderById($orderId);
        
        if (!$order) {
            return redirect()->route('home')
                ->with('error', '����������');
        }
        
        $user = Auth::user();
        $mfaMethods = $this->mfaService->getUserMfaMethods($user)['methods'];
        
        return view('security.payment_verify', [
            'order' => $order,
            'payment_method' => $paymentMethod,
            'mfa_methods' => $mfaMethods
        ]);
    }
    
    /**
     * ����֧����ȫ��֤
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verifyPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|string',
            'payment_method' => 'required|string',
            'method' => 'required|string',
            'code' => 'required|string'
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $user = Auth::user();
        $orderId = $request->input('order_id');
        $paymentMethod = $request->input('payment_method');
        $method = $request->input('method');
        $code = $request->input('code');
        
        // ��鶩���Ƿ����
        $order = $this->paymentService->getOrderById($orderId);
        
        if (!$order) {
            return redirect()->back()
                ->with('error', '����������');
        }
        
        // ��鶩���Ƿ����ڵ�ǰ�û�
        if ($order->user_id !== $user->id) {
            return redirect()->back()
                ->with('error', '��Ȩ�����˶���');
        }
        
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
            Session::put('payment_verified', true);
            Session::put('payment_verified_at', now()->timestamp);
            Session::put('payment_verified_order', $orderId);
            
            // �ض���֧��ҳ��
            return redirect()->route('payment.process', [
                'order_id' => $orderId,
                'payment_method' => $paymentMethod
            ])->with('success', '��֤�ɹ�����������֧��');
        } else {
            return redirect()->back()
                ->with('error', $result['message'])
                ->withInput();
        }
    }
    
    /**
     * ��ʾ���ն�������ҳ��
     * 
     * @param Request $request
     * @param string $orderId
     * @return \Illuminate\View\View
     */
    public function showRiskOrderDetails(Request $request, string $orderId)
    {
        $user = Auth::user();
        
        // ��鶩���Ƿ����
        $order = $this->paymentService->getOrderById($orderId);
        
        if (!$order) {
            return redirect()->route('home')
                ->with('error', '����������');
        }
        
        // ��鶩���Ƿ����ڵ�ǰ�û����û��Ƿ�Ϊ����Ա
        if ($order->user_id !== $user->id && !$user->hasRole('admin')) {
            return redirect()->route('home')
                ->with('error', '��Ȩ�鿴�˶���');
        }
        
        // ��ȡ������������
        $riskAssessment = $order->risk_assessment ?? [];
        
        return view('security.risk_order_details', [
            'order' => $order,
            'risk_assessment' => $riskAssessment
        ]);
    }
    
    /**
     * ��ʾ֧��API��ȫҳ�棨����Ա��
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function showPaymentApiSecurity(Request $request)
    {
        $user = Auth::user();
        
        // ����û��Ƿ�Ϊ����Ա
        if (!$user->hasRole('admin')) {
            return redirect()->route('home')
                ->with('error', '��Ȩ���ʴ�ҳ��');
        }
        
        // ��ȡ֧��API����
        $alipayConfig = [
            'app_id' => config('payment.alipay.app_id'),
            'sandbox' => config('payment.alipay.sandbox'),
            'last_key_rotation' => Session::get('last_api_key_rotation'),
        ];
        
        $wechatConfig = [
            'app_id' => config('payment.wechat.app_id'),
            'mch_id' => config('payment.wechat.mch_id'),
            'sandbox' => config('payment.wechat.sandbox'),
            'last_key_rotation' => Session::get('last_api_key_rotation'),
        ];
        
        return view('admin.security.payment_api', [
            'alipay_config' => $alipayConfig,
            'wechat_config' => $wechatConfig
        ]);
    }
    
    /**
     * �ֻ�API��Կ������Ա��
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function rotateApiKeys(Request $request)
    {
        $user = Auth::user();
        
        // ����û��Ƿ�Ϊ����Ա
        if (!$user->hasRole('admin')) {
            return redirect()->route('home')
                ->with('error', '��Ȩִ�д˲���');
        }
        
        // ģ��API��Կ�ֻ�
        // ʵ��Ӧ����Ӧ����֧��ƽ̨API������Կ����
        Session::put('last_api_key_rotation', now());
        
        Log::channel('security')->info("֧��API��Կ���ֻ�", [
            'admin_id' => $user->id,
            'timestamp' => now()->timestamp
        ]);
        
        return redirect()->back()
            ->with('success', 'API��Կ�ѳɹ��ֻ�');
    }
    
    /**
     * ��ʾ֧�����ռ��ҳ�棨����Ա��
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function showPaymentRiskMonitoring(Request $request)
    {
        $user = Auth::user();
        
        // ����û��Ƿ�Ϊ����Ա
        if (!$user->hasRole('admin')) {
            return redirect()->route('home')
                ->with('error', '��Ȩ���ʴ�ҳ��');
        }
        
        // ��ȡ�߷��ն���
        $highRiskOrders = \App\Models\Order::where('risk_score', '>', 70)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('admin.security.payment_risk_monitoring', [
            'high_risk_orders' => $highRiskOrders
        ]);
    }
}
