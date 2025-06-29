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
     * �����������
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $context ��ȫ������ (login, payment, api)
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $context = '')
    {
        // ��������ι��ܱ����ã�ֱ��ͨ��
        if (!config('zero_trust.enabled', true)) {
            return $next($request);
        }
        
        // ���������η���
        $zeroTrustService = new ZeroTrustService($request);
        
        // ִ�а�ȫ���
        $securityResult = $zeroTrustService->runFullSecurityCheck($context);
        
        // ����ȫ����洢�������У��Ա���������Է���
        $request->attributes->set('security_result', $securityResult);
        
        // ��¼�߷��ջ
        if ($securityResult['risk_score'] > 70) {
            Log::channel('security')->warning("�߷���������", [
                'user_id' => Auth::id(),
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'risk_score' => $securityResult['risk_score'],
                'context' => $context
            ]);
        }
        
        // ����������ֳ�����ֵ�����������Ĳ�ȡ��ͬ���ж�
        if ($securityResult['is_dangerous']) {
            if ($context === 'payment') {
                // ֧�������£���ֹ�߷�������Ҫ�������֤
                Session::flash('security_warning', '��⵽�쳣�������������֤�����');
                return redirect()->route('security.verify', ['redirect_to' => $request->fullUrl()]);
            } elseif ($context === 'login') {
                // ��¼�����£���¼���ɵ�¼����
                Log::channel('security')->alert("���ɵ�¼����", [
                    'username' => $request->input('email'),
                    'ip' => $request->ip(),
                    'risk_score' => $securityResult['risk_score']
                ]);
                
                // ������ռ��ߣ�����ֱ����ֹ��¼
                if ($securityResult['risk_score'] > 90) {
                    return response()->json([
                        'success' => false,
                        'message' => '���ڰ�ȫԭ�����ĵ�¼���󱻾ܾ�������ϵ����Ա���Ժ����ԡ�'
                    ], 403);
                }
                
                // ���������֤��Ҫ��
                Session::put('requires_captcha', true);
            } elseif ($context === 'api') {
                // API�����£����Խ����������ƻ�Ҫ��������֤
                return response()->json([
                    'success' => false,
                    'message' => '��ȫ���ʧ�ܣ���������֤',
                    'code' => 'SECURITY_CHECK_FAILED'
                ], 403);
            }
        } elseif ($securityResult['is_suspicious']) {
            // ���ɵ���Σ�յ�������Ӷ����ػ�����
            if ($context === 'payment') {
                // �ڻỰ�б����Ҫ������֤
                Session::put('requires_additional_verification', true);
            }
        }
        
        return $next($request);
    }
}
