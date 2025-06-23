<?php

namespace AlingAi\Core;

use Closure;

/**
 * AuthMiddleware
 *
 * @package AlingAi\Core
 */
class AuthMiddleware
{
    // 类属性和方法
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        // 初始化代码
    }
    /**
     * 处理传入的请求
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        // 请求前的处理逻辑
        
        $response = $next($request);
        
        // 请求后的处理逻辑
        
        return $response;
    }
}
