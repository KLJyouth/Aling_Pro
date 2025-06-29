<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class RequireMfaMiddleware
{
    /**
     * 处理传入的请求
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        
        // 如果用户未登录，直接通过
        if (!$user) {
            return $next($request);
        }
        
        // 检查用户是否启用了MFA
        if ($user->has_mfa) {
            // 检查用户是否已完成MFA验证
            if (!Session::has('mfa_verified') || Session::get('mfa_verified') !== true) {
                // 保存当前URL以便验证后重定向回来
                Session::put('mfa_redirect_url', $request->fullUrl());
                
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => '需要多因素认证',
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
