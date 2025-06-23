<?php

declare(strict_types=1);

namespace AlingAi\Controllers\Frontend;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use AlingAi\Services\SecurityService;
use AlingAi\Services\VisualizationService;

/**
 * 3D威胁可视化控制器
 * 
 * 提供3D威胁数据的可视化展示
 */
class Enhanced3DThreatVisualizationController
{
    private $securityService;
    private $visualizationService;
    
    public function __construct(SecurityService $securityService, VisualizationService $visualizationService)
    {
        $this->securityService = $securityService;
        $this->visualizationService = $visualizationService;
    }
    
    /**
     * 3D威胁可视化首页
     */
    public function index(Request $request, Response $response): Response
    {
        $templateVars = [
            "page_title" => "3D威胁可视化",
            "visualization_enabled" => true,
            "use_3d" => true,
            "refresh_interval" => 30,
            "current_user" => isset($_SESSION["user"]) ? $_SESSION["user"] : null
        ];
        
        $view = $request->getAttribute("view");
        return $view->render($response, "threat-visualization-3d.twig", $templateVars);
    }
    
    /**
     * 获取3D威胁可视化数据
     */
    public function getData(Request $request, Response $response): Response
    {
        // 获取查询参数
        $params = $request->getQueryParams();
        $timeframe = $params["timeframe"] ?? "day";
        $type = $params["type"] ?? "all";
        $dimension = $params["dimension"] ?? "3d";
        
        // 获取威胁数据
        $threatData = $this->securityService->getThreatData($timeframe, $type);
        
        // 处理3D可视化
        $visualData = $this->visualizationService->formatThreatData($threatData);
        
        // 添加3D特定属性
        foreach ($visualData["nodes"] as &$node) {
            $node["z"] = rand(0, 100);  // 添加Z轴坐标
        }
        
        $response->getBody()->write(json_encode([
            "success" => true,
            "data" => $visualData
        ]));
        
        return $response
            ->withHeader("Content-Type", "application/json");
    }
}
