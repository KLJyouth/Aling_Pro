<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class RequireMfaMiddleware
{
    /**
     * �����������
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        
        // ����û�δ��¼��ֱ��ͨ��
        if (!$user) {
            return $next($request);
        }
        
        // ����û��Ƿ�������MFA
        if ($user->has_mfa) {
            // ����û��Ƿ������MFA��֤
            if (!Session::has('mfa_verified') || Session::get('mfa_verified') !== true) {
                // ���浱ǰURL�Ա���֤���ض������
                Session::put('mfa_redirect_url', $request->fullUrl());
                
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => '��Ҫ��������֤',
                        'code' => 'MFA_REQUIRED',
                        'redirect' => route('auth.mfa.verify')
                    ], 403);
                }
                
                return redirect()->route('auth.mfa.verify');
            }
        }
        
        return $next($request);
    }
}
