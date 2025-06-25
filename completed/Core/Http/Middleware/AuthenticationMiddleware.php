<?php

namespace AlingAi\Core\Http\Middleware;

use Closure;

/**
 * AuthenticationMiddleware
 *
 * @package AlingAi\Core\Http\Middleware
 */
class AuthenticationMiddleware
{
    // 类属性和方法
    
    /**
     * 构造函�?     */
    public function __construct()
    {
        // 初始化代�?    }
    /**
     * 处理传入的请�?
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        // 请求前的处理逻辑
        
        $response = $next($request];
        
        // 请求后的处理逻辑
        
        return $response;
    }
}
