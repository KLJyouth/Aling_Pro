<?php

declare(strict_types=1);

namespace AlingAi\Controllers\Frontend;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use AlingAi\Services\SecurityService;
use AlingAi\Services\RealTimeMonitoringService;

/**
 * 实时安全监控控制器
 * 
 * 提供实时安全监控功能和界面
 */
class RealTimeSecurityController
{
    private $securityService;
    private $monitoringService;
    
    public function __construct(SecurityService $securityService, RealTimeMonitoringService $monitoringService)
    {
        $this->securityService = $securityService;
        $this->monitoringService = $monitoringService;
    }
    
    /**
     * 实时安全监控首页
     */
    public function index(Request $request, Response $response): Response
    {
        $templateVars = [
            "page_title" => "实时安全监控",
            "monitoring_enabled" => true,
            "refresh_interval" => 5,
            "current_user" => isset($_SESSION["user"]) ? $_SESSION["user"] : null
        ];
        
        $view = $request->getAttribute("view");
        return $view->render($response, "real-time-security.twig", $templateVars);
    }
    
    /**
     * 获取实时安全数据
     */
    public function getData(Request $request, Response $response): Response
    {
        // 获取实时安全数据
        $securityData = $this->monitoringService->getRealtimeSecurityData();
        
        $response->getBody()->write(json_encode([
            "success" => true,
            "timestamp" => time(),
            "data" => $securityData
        ]));
        
        return $response
            ->withHeader("Content-Type", "application/json");
    }
    
    /**
     * 获取安全告警
     */
    public function getAlerts(Request $request, Response $response): Response
    {
        // 获取查询参数
        $params = $request->getQueryParams();
        $since = $params["since"] ?? null;
        $priority = $params["priority"] ?? null;
        
        // 获取告警数据
        $alerts = $this->monitoringService->getSecurityAlerts($since, $priority);
        
        $response->getBody()->write(json_encode([
            "success" => true,
            "count" => count($alerts),
            "data" => $alerts
        ]));
        
        return $response
            ->withHeader("Content-Type", "application/json");
    }
}
