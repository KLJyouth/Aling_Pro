<?php

declare(strict_types=1);

namespace AlingAi\Controllers\Frontend;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use AlingAi\Services\SecurityService;
use AlingAi\Services\VisualizationService;

/**
 * 3D��в���ӻ�������
 * 
 * �ṩ3D��в���ݵĿ��ӻ�չʾ
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
     * 3D��в���ӻ���ҳ
     */
    public function index(Request $request, Response $response): Response
    {
        $templateVars = [
            "page_title" => "3D��в���ӻ�",
            "visualization_enabled" => true,
            "use_3d" => true,
            "refresh_interval" => 30,
            "current_user" => isset($_SESSION["user"]) ? $_SESSION["user"] : null
        ];
        
        $view = $request->getAttribute("view");
        return $view->render($response, "threat-visualization-3d.twig", $templateVars);
    }
    
    /**
     * ��ȡ3D��в���ӻ�����
     */
    public function getData(Request $request, Response $response): Response
    {
        // ��ȡ��ѯ����
        $params = $request->getQueryParams();
        $timeframe = $params["timeframe"] ?? "day";
        $type = $params["type"] ?? "all";
        $dimension = $params["dimension"] ?? "3d";
        
        // ��ȡ��в����
        $threatData = $this->securityService->getThreatData($timeframe, $type);
        
        // ����3D���ӻ�
        $visualData = $this->visualizationService->formatThreatData($threatData);
        
        // ���3D�ض�����
        foreach ($visualData["nodes"] as &$node) {
            $node["z"] = rand(0, 100);  // ���Z������
        }
        
        $response->getBody()->write(json_encode([
            "success" => true,
            "data" => $visualData
        ]));
        
        return $response
            ->withHeader("Content-Type", "application/json");
    }
}
