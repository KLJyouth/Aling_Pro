<?php

declare(strict_types=1);

namespace AlingAi\Controllers\Frontend;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use AlingAi\Services\SecurityService;
use AlingAi\Services\VisualizationService;

/**
 * ��в���ӻ�������
 * 
 * �ṩ��в���ݵĿ��ӻ�չʾ
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
     * ��в���ӻ���ҳ
     */
    public function index(Request $request, Response $response): Response
    {
        $templateVars = [
            "page_title" => "��в���ӻ�",
            "visualization_enabled" => true,
            "refresh_interval" => 30,
            "current_user" => isset($_SESSION["user"]) ? $_SESSION["user"] : null
        ];
        
        $view = $request->getAttribute("view");
        return $view->render($response, "threat-visualization.twig", $templateVars);
    }
    
    /**
     * ��ȡ��в���ӻ�����
     */
    public function getData(Request $request, Response $response): Response
    {
        // ��ȡ��ѯ����
        $params = $request->getQueryParams();
        $timeframe = $params["timeframe"] ?? "day";
        $type = $params["type"] ?? "all";
        
        // ��ȡ��в����
        $threatData = $this->securityService->getThreatData($timeframe, $type);
        
        // ������ӻ�
        $visualData = $this->visualizationService->formatThreatData($threatData);
        
        $response->getBody()->write(json_encode([
            "success" => true,
            "data" => $visualData
        ]));
        
        return $response
            ->withHeader("Content-Type", "application/json");
    }
}
