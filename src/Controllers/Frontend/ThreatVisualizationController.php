<?php

declare(strict_types=1);

namespace AlingAi\Controllers\Frontend;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use AlingAi\Services\SecurityService;
use AlingAi\Services\VisualizationService;

/**
 * 威胁可视化控制器
 * 
 * 提供威胁数据的可视化展示
 */
class ThreatVisualizationController
{
    private $securityService;
    private $visualizationService;
    
    public function __construct(SecurityService $securityService, VisualizationService $visualizationService)
    {
        $this->securityService = $securityService;
        $this->visualizationService = $visualizationService;
    }
    
    /**
     * 威胁可视化首页
     */
    public function index(Request $request, Response $response): Response
    {
        $templateVars = [
            "page_title" => "威胁可视化",
            "visualization_enabled" => true,
            "refresh_interval" => 30,
            "current_user" => isset($_SESSION["user"]) ? $_SESSION["user"] : null
        ];
        
        $view = $request->getAttribute("view");
        return $view->render($response, "threat-visualization.twig", $templateVars);
    }
    
    /**
     * 获取威胁可视化数据
     */
    public function getData(Request $request, Response $response): Response
    {
        // 获取查询参数
        $params = $request->getQueryParams();
        $timeframe = $params["timeframe"] ?? "day";
        $type = $params["type"] ?? "all";
        
        // 获取威胁数据
        $threatData = $this->securityService->getThreatData($timeframe, $type);
        
        // 处理可视化
        $visualData = $this->visualizationService->formatThreatData($threatData);
        
        $response->getBody()->write(json_encode([
            "success" => true,
            "data" => $visualData
        ]));
        
        return $response
            ->withHeader("Content-Type", "application/json");
    }
}
