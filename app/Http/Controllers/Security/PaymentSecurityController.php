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
     * 支付服务
     * 
     * @var \App\Services\Payment\PaymentService
     */
    protected $paymentService;
    
    /**
     * 构造函数
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
     * 显示支付安全验证页面
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function showPaymentVerifyPage(Request $request)
    {
        $orderId = $request->input('order_id');
        $paymentMethod = $request->input('payment_method');
        
        // 检查订单是否存在
        $order = $this->paymentService->getOrderById($orderId);
        
        if (!$order) {
            return redirect()->route('home')
                ->with('error', '订单不存在');
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
     * 处理支付安全验证
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
        
        // 检查订单是否存在
        $order = $this->paymentService->getOrderById($orderId);
        
        if (!$order) {
            return redirect()->back()
                ->with('error', '订单不存在');
        }
        
        // 检查订单是否属于当前用户
        if ($order->user_id !== $user->id) {
            return redirect()->back()
                ->with('error', '无权操作此订单');
        }
        
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
            Session::put('payment_verified', true);
            Session::put('payment_verified_at', now()->timestamp);
            Session::put('payment_verified_order', $orderId);
            
            // 重定向到支付页面
            return redirect()->route('payment.process', [
                'order_id' => $orderId,
                'payment_method' => $paymentMethod
            ])->with('success', '验证成功，请继续完成支付');
        } else {
            return redirect()->back()
                ->with('error', $result['message'])
                ->withInput();
        }
    }
    
    /**
     * 显示风险订单详情页面
     * 
     * @param Request $request
     * @param string $orderId
     * @return \Illuminate\View\View
     */
    public function showRiskOrderDetails(Request $request, string $orderId)
    {
        $user = Auth::user();
        
        // 检查订单是否存在
        $order = $this->paymentService->getOrderById($orderId);
        
        if (!$order) {
            return redirect()->route('home')
                ->with('error', '订单不存在');
        }
        
        // 检查订单是否属于当前用户或用户是否为管理员
        if ($order->user_id !== $user->id && !$user->hasRole('admin')) {
            return redirect()->route('home')
                ->with('error', '无权查看此订单');
        }
        
        // 获取订单风险评估
        $riskAssessment = $order->risk_assessment ?? [];
        
        return view('security.risk_order_details', [
            'order' => $order,
            'risk_assessment' => $riskAssessment
        ]);
    }
    
    /**
     * 显示支付API安全页面（管理员）
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function showPaymentApiSecurity(Request $request)
    {
        $user = Auth::user();
        
        // 检查用户是否为管理员
        if (!$user->hasRole('admin')) {
            return redirect()->route('home')
                ->with('error', '无权访问此页面');
        }
        
        // 获取支付API配置
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
     * 轮换API密钥（管理员）
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function rotateApiKeys(Request $request)
    {
        $user = Auth::user();
        
        // 检查用户是否为管理员
        if (!$user->hasRole('admin')) {
            return redirect()->route('home')
                ->with('error', '无权执行此操作');
        }
        
        // 模拟API密钥轮换
        // 实际应用中应调用支付平台API进行密钥更新
        Session::put('last_api_key_rotation', now());
        
        Log::channel('security')->info("支付API密钥已轮换", [
            'admin_id' => $user->id,
            'timestamp' => now()->timestamp
        ]);
        
        return redirect()->back()
            ->with('success', 'API密钥已成功轮换');
    }
    
    /**
     * 显示支付风险监控页面（管理员）
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function showPaymentRiskMonitoring(Request $request)
    {
        $user = Auth::user();
        
        // 检查用户是否为管理员
        if (!$user->hasRole('admin')) {
            return redirect()->route('home')
                ->with('error', '无权访问此页面');
        }
        
        // 获取高风险订单
        $highRiskOrders = \App\Models\Order::where('risk_score', '>', 70)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('admin.security.payment_risk_monitoring', [
            'high_risk_orders' => $highRiskOrders
        ]);
    }
}
