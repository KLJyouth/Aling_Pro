<?php

declare(strict_types=1);

namespace AlingAi\Controllers\Frontend;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use AlingAi\Services\SecurityService;
use AlingAi\Services\RealTimeMonitoringService;

/**
 * ʵʱ��ȫ��ؿ�����
 * 
 * �ṩʵʱ��ȫ��ع��ܺͽ���
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
     * ʵʱ��ȫ�����ҳ
     */
    public function index(Request $request, Response $response): Response
    {
        $templateVars = [
            "page_title" => "ʵʱ��ȫ���",
            "monitoring_enabled" => true,
            "refresh_interval" => 5,
            "current_user" => isset($_SESSION["user"]) ? $_SESSION["user"] : null
        ];
        
        $view = $request->getAttribute("view");
        return $view->render($response, "real-time-security.twig", $templateVars);
    }
    
    /**
     * ��ȡʵʱ��ȫ����
     */
    public function getData(Request $request, Response $response): Response
    {
        // ��ȡʵʱ��ȫ����
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
     * ��ȡ��ȫ�澯
     */
    public function getAlerts(Request $request, Response $response): Response
    {
        // ��ȡ��ѯ����
        $params = $request->getQueryParams();
        $since = $params["since"] ?? null;
        $priority = $params["priority"] ?? null;
        
        // ��ȡ�澯����
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
