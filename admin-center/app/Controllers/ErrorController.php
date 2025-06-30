<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Core\Logger;

/**
 * 错误处理控制器
 * 负责处理系统错误和异常
 */
class ErrorController extends Controller
{
    /**
     * 处理404错误
     */
    public function notFound()
    {
        http_response_code(404);
        
        View::display('errors.404', [
            'pageTitle' => '404 页面未找到 - IT运维中心',
            'currentPage' => 'error'
        ]);
    }
    
    /**
     * 处理500错误
     * 
     * @param \Exception $exception 异常对象
     */
    public function serverError($exception = null)
    {
        http_response_code(500);
        
        // 记录错误日志
        if ($exception) {
            Logger::error('服务器错误: ' . $exception->getMessage(), [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ]);
        }
        
        View::display('errors.500', [
            'pageTitle' => '500 服务器错误 - IT运维中心',
            'currentPage' => 'error',
            'exception' => $exception,
            'isDebug' => getenv('APP_DEBUG') === 'true'
        ]);
    }
    
    /**
     * 处理维护模式
     */
    public function maintenance()
    {
        http_response_code(503);
        
        View::display('errors.maintenance', [
            'pageTitle' => '系统维护中 - IT运维中心',
            'currentPage' => 'error'
        ]);
    }
    
    /**
     * 处理无权限错误
     */
    public function forbidden()
    {
        http_response_code(403);
        
        View::display('errors.403', [
            'pageTitle' => '403 禁止访问 - IT运维中心',
            'currentPage' => 'error'
        ]);
    }
} 