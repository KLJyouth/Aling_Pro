<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\Security\ZeroTrust\ZeroTrustService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ZeroTrustMiddleware
{
    /**
     * 处理传入的请求
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $context 安全上下文 (login, payment, api)
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $context = '')
    {
        // 如果零信任功能被禁用，直接通过
        if (!config('zero_trust.enabled', true)) {
            return $next($request);
        }
        
        // 创建零信任服务
        $zeroTrustService = new ZeroTrustService($request);
        
        // 执行安全检查
        $securityResult = $zeroTrustService->runFullSecurityCheck($context);
        
        // 将安全结果存储在请求中，以便控制器可以访问
        $request->attributes->set('security_result', $securityResult);
        
        // 记录高风险活动
        if ($securityResult['risk_score'] > 70) {
            Log::channel('security')->warning("高风险请求检测", [
                'user_id' => Auth::id(),
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'risk_score' => $securityResult['risk_score'],
                'context' => $context
            ]);
        }
        
        // 如果风险评分超过阈值，根据上下文采取不同的行动
        if ($securityResult['is_dangerous']) {
            if ($context === 'payment') {
                // 支付场景下，阻止高风险请求并要求额外验证
                Session::flash('security_warning', '检测到异常活动，请进行身份验证后继续');
                return redirect()->route('security.verify', ['redirect_to' => $request->fullUrl()]);
            } elseif ($context === 'login') {
                // 登录场景下，记录可疑登录尝试
                Log::channel('security')->alert("可疑登录尝试", [
                    'username' => $request->input('email'),
                    'ip' => $request->ip(),
                    'risk_score' => $securityResult['risk_score']
                ]);
                
                // 如果风险极高，可以直接阻止登录
                if ($securityResult['risk_score'] > 90) {
                    return response()->json([
                        'success' => false,
                        'message' => '出于安全原因，您的登录请求被拒绝。请联系管理员或稍后再试。'
                    ], 403);
                }
                
                // 否则添加验证码要求
                Session::put('requires_captcha', true);
            } elseif ($context === 'api') {
                // API场景下，可以降低速率限制或要求重新认证
                return response()->json([
                    'success' => false,
                    'message' => '安全检查失败，请重新认证',
                    'code' => 'SECURITY_CHECK_FAILED'
                ], 403);
            }
        } elseif ($securityResult['is_suspicious']) {
            // 可疑但不危险的请求，添加额外监控或限制
            if ($context === 'payment') {
                // 在会话中标记需要额外验证
                Session::put('requires_additional_verification', true);
            }
        }
        
        return $next($request);
    }
}
