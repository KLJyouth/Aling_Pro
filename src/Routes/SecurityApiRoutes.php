<?php

namespace AlingAi\Routes;

use AlingAi\Controllers\Api\SecurityDashboardApiController;
use AlingAi\Controllers\Api\SecurityManagementApiController;
use AlingAi\Controllers\Api\ThreatIntelligenceApiController;
use AlingAi\Controllers\Api\IncidentResponseApiController;
use AlingAi\Controllers\Api\ComplianceApiController;
use AlingAi\Controllers\Api\ReportApiController;
use AlingAi\Controllers\Api\QuantumSecurityApiController;
use AlingAi\Controllers\Api\RedBlueTeamApiController;
use AlingAi\Core\Router;
use AlingAi\Middleware\SecurityMiddleware;
use AlingAi\Middleware\RateLimitMiddleware;
use AlingAi\Middleware\AuthenticationMiddleware;

/**
 * 安全API路由配置
 * 
 * 整合所有安全相关的API端点，提供统一的路由管理
 */
class SecurityApiRoutes
{
    private $router;
    private $container;
    private $securityMiddleware;
    private $rateLimitMiddleware;
    private $authMiddleware;

    public function __construct($router, $container)
    {
        $this->router = $router;
        $this->container = $container;
        $this->securityMiddleware = new SecurityMiddleware();
        $this->rateLimitMiddleware = new RateLimitMiddleware();
        $this->authMiddleware = new AuthenticationMiddleware();
        
        $this->registerRoutes();
    }

    /**
     * 注册所有安全API路由
     */
    public function registerRoutes(): void
    {
        $this->registerSecurityDashboardRoutes();
        $this->registerSecurityManagementRoutes();
        $this->registerIncidentResponseRoutes();
        $this->registerThreatIntelligenceRoutes();
        $this->registerComplianceRoutes();
        $this->registerReportRoutes();
        $this->registerQuantumSecurityRoutes();
        $this->registerRedBlueTeamRoutes();
    }

    /**
     * 注册安全仪表板路由
     */
    private function registerSecurityDashboardRoutes(): void
    {
        $controller = new SecurityDashboardApiController();

        // 仪表板概览
        $this->router->get('/api/security/dashboard/overview', [$controller, 'getDashboardOverview']);
        $this->router->get('/api/security/dashboard/metrics', [$controller, 'getSecurityMetrics']);
        $this->router->get('/api/security/dashboard/alerts', [$controller, 'getSecurityAlerts']);
        $this->router->get('/api/security/dashboard/events', [$controller, 'getSecurityEvents']);

        // 实时监控
        $this->router->get('/api/security/dashboard/monitoring/network', [$controller, 'getNetworkMonitoring']);
        $this->router->get('/api/security/dashboard/monitoring/system', [$controller, 'getSystemMonitoring']);
        $this->router->get('/api/security/dashboard/monitoring/application', [$controller, 'getApplicationMonitoring']);

        // 威胁分析
        $this->router->get('/api/security/dashboard/threats/analysis', [$controller, 'getThreatAnalysis']);
        $this->router->get('/api/security/dashboard/threats/trends', [$controller, 'getThreatTrends']);
        $this->router->get('/api/security/dashboard/threats/map', [$controller, 'getThreatMap']);

        // 安全评分
        $this->router->get('/api/security/dashboard/score', [$controller, 'getSecurityScore']);
        $this->router->post('/api/security/dashboard/score/calculate', [$controller, 'calculateSecurityScore']);
    }

    /**
     * 注册安全管理路由
     */
    private function registerSecurityManagementRoutes(): void
    {
        $controller = new SecurityManagementApiController();

        // 安全策略管理
        $this->router->get('/api/security/policies', [$controller, 'getSecurityPolicies']);
        $this->router->post('/api/security/policies', [$controller, 'createSecurityPolicy']);
        $this->router->put('/api/security/policies/{id}', [$controller, 'updateSecurityPolicy']);
        $this->router->delete('/api/security/policies/{id}', [$controller, 'deleteSecurityPolicy']);

        // 安全规则管理
        $this->router->get('/api/security/rules', [$controller, 'getSecurityRules']);
        $this->router->post('/api/security/rules', [$controller, 'createSecurityRule']);
        $this->router->put('/api/security/rules/{id}', [$controller, 'updateSecurityRule']);
        $this->router->delete('/api/security/rules/{id}', [$controller, 'deleteSecurityRule']);

        // 白名单管理
        $this->router->get('/api/security/whitelist', [$controller, 'getWhitelist']);
        $this->router->post('/api/security/whitelist', [$controller, 'addToWhitelist']);
        $this->router->delete('/api/security/whitelist/{id}', [$controller, 'removeFromWhitelist']);

        // 黑名单管理
        $this->router->get('/api/security/blacklist', [$controller, 'getBlacklist']);
        $this->router->post('/api/security/blacklist', [$controller, 'addToBlacklist']);
        $this->router->delete('/api/security/blacklist/{id}', [$controller, 'removeFromBlacklist']);

        // 安全配置管理
        $this->router->get('/api/security/config', [$controller, 'getSecurityConfig']);
        $this->router->put('/api/security/config', [$controller, 'updateSecurityConfig']);
        $this->router->post('/api/security/config/backup', [$controller, 'backupSecurityConfig']);
        $this->router->post('/api/security/config/restore', [$controller, 'restoreSecurityConfig']);
    }

    /**
     * 注册事件响应路由
     */
    private function registerIncidentResponseRoutes(): void
    {
        $controller = new IncidentResponseApiController();

        // 事件管理
        $this->router->get('/api/incidents', [$controller, 'getIncidents']);
        $this->router->get('/api/incidents/{id}', [$controller, 'getIncident']);
        $this->router->post('/api/incidents', [$controller, 'createIncident']);
        $this->router->put('/api/incidents/{id}', [$controller, 'updateIncident']);
        $this->router->delete('/api/incidents/{id}', [$controller, 'deleteIncident']);

        // 事件响应
        $this->router->post('/api/incidents/{id}/respond', [$controller, 'respondToIncident']);
        $this->router->post('/api/incidents/{id}/escalate', [$controller, 'escalateIncident']);
        $this->router->post('/api/incidents/{id}/resolve', [$controller, 'resolveIncident']);

        // 事件分类
        $this->router->get('/api/incidents/categories', [$controller, 'getIncidentCategories']);
        $this->router->post('/api/incidents/categories', [$controller, 'createIncidentCategory']);

        // 响应流程
        $this->router->get('/api/incidents/playbooks', [$controller, 'getResponsePlaybooks']);
        $this->router->post('/api/incidents/playbooks', [$controller, 'createResponsePlaybook']);
        $this->router->put('/api/incidents/playbooks/{id}', [$controller, 'updateResponsePlaybook']);
    }

    /**
     * 注册威胁情报路由
     */
    private function registerThreatIntelligenceRoutes(): void
    {
        $controller = new ThreatIntelligenceApiController();

        // 威胁情报
        $this->router->get('/api/threat-intelligence/feeds', [$controller, 'getThreatFeeds']);
        $this->router->post('/api/threat-intelligence/feeds', [$controller, 'addThreatFeed']);
        $this->router->put('/api/threat-intelligence/feeds/{id}', [$controller, 'updateThreatFeed']);
        $this->router->delete('/api/threat-intelligence/feeds/{id}', [$controller, 'deleteThreatFeed']);

        // 威胁指标
        $this->router->get('/api/threat-intelligence/indicators', [$controller, 'getThreatIndicators']);
        $this->router->post('/api/threat-intelligence/indicators', [$controller, 'addThreatIndicator']);
        $this->router->put('/api/threat-intelligence/indicators/{id}', [$controller, 'updateThreatIndicator']);
        $this->router->delete('/api/threat-intelligence/indicators/{id}', [$controller, 'deleteThreatIndicator']);

        // 威胁分析
        $this->router->get('/api/threat-intelligence/analysis', [$controller, 'getThreatAnalysis']);
        $this->router->post('/api/threat-intelligence/analyze', [$controller, 'analyzeThreat']);
        $this->router->get('/api/threat-intelligence/trends', [$controller, 'getThreatTrends']);

        // 情报共享
        $this->router->get('/api/threat-intelligence/sharing', [$controller, 'getSharingConfig']);
        $this->router->post('/api/threat-intelligence/share', [$controller, 'shareThreatIntelligence']);
        $this->router->get('/api/threat-intelligence/received', [$controller, 'getReceivedIntelligence']);
    }

    /**
     * 注册合规管理路由
     */
    private function registerComplianceRoutes(): void
    {
        $controller = new ComplianceApiController();

        // 合规框架
        $this->router->get('/api/compliance/frameworks', [$controller, 'getComplianceFrameworks']);
        $this->router->post('/api/compliance/frameworks', [$controller, 'createComplianceFramework']);
        $this->router->put('/api/compliance/frameworks/{id}', [$controller, 'updateComplianceFramework']);
        $this->router->delete('/api/compliance/frameworks/{id}', [$controller, 'deleteComplianceFramework']);

        // 合规要求
        $this->router->get('/api/compliance/requirements', [$controller, 'getComplianceRequirements']);
        $this->router->post('/api/compliance/requirements', [$controller, 'createComplianceRequirement']);
        $this->router->put('/api/compliance/requirements/{id}', [$controller, 'updateComplianceRequirement']);
        $this->router->delete('/api/compliance/requirements/{id}', [$controller, 'deleteComplianceRequirement']);

        // 合规评估
        $this->router->get('/api/compliance/assessments', [$controller, 'getComplianceAssessments']);
        $this->router->post('/api/compliance/assessments', [$controller, 'createComplianceAssessment']);
        $this->router->get('/api/compliance/assessments/{id}', [$controller, 'getComplianceAssessment']);
        $this->router->put('/api/compliance/assessments/{id}', [$controller, 'updateComplianceAssessment']);

        // 合规监控
        $this->router->get('/api/compliance/monitoring', [$controller, 'getComplianceMonitoring']);
        $this->router->post('/api/compliance/monitoring/check', [$controller, 'checkCompliance']);
        $this->router->get('/api/compliance/violations', [$controller, 'getComplianceViolations']);
    }

    /**
     * 注册报告管理路由
     */
    private function registerReportRoutes(): void
    {
        $controller = new ReportApiController();

        // 安全报告
        $this->router->get('/api/reports/security', [$controller, 'getSecurityReports']);
        $this->router->post('/api/reports/security', [$controller, 'generateSecurityReport']);
        $this->router->get('/api/reports/security/{id}', [$controller, 'getSecurityReport']);
        $this->router->delete('/api/reports/security/{id}', [$controller, 'deleteSecurityReport']);

        // 合规报告
        $this->router->get('/api/reports/compliance', [$controller, 'getComplianceReports']);
        $this->router->post('/api/reports/compliance', [$controller, 'generateComplianceReport']);
        $this->router->get('/api/reports/compliance/{id}', [$controller, 'getComplianceReport']);

        // 事件报告
        $this->router->get('/api/reports/incidents', [$controller, 'getIncidentReports']);
        $this->router->post('/api/reports/incidents', [$controller, 'generateIncidentReport']);
        $this->router->get('/api/reports/incidents/{id}', [$controller, 'getIncidentReport']);

        // 威胁报告
        $this->router->get('/api/reports/threats', [$controller, 'getThreatReports']);
        $this->router->post('/api/reports/threats', [$controller, 'generateThreatReport']);
        $this->router->get('/api/reports/threats/{id}', [$controller, 'getThreatReport']);

        // 报告导出
        $this->router->post('/api/reports/export', [$controller, 'exportReport']);
        $this->router->get('/api/reports/templates', [$controller, 'getReportTemplates']);
        $this->router->post('/api/reports/schedule', [$controller, 'scheduleReport']);
    }

    /**
     * 注册量子安全路由
     */
    private function registerQuantumSecurityRoutes(): void
    {
        $controller = new QuantumSecurityApiController();

        // 量子加密
        $this->router->post('/api/quantum/encrypt', [$controller, 'quantumEncrypt']);
        $this->router->post('/api/quantum/decrypt', [$controller, 'quantumDecrypt']);
        $this->router->get('/api/quantum/keys', [$controller, 'getQuantumKeys']);
        $this->router->post('/api/quantum/keys/generate', [$controller, 'generateQuantumKey']);

        // 量子密钥分发
        $this->router->post('/api/quantum/key-distribution', [$controller, 'distributeQuantumKey']);
        $this->router->get('/api/quantum/key-distribution/status', [$controller, 'getKeyDistributionStatus']);

        // 量子安全通信
        $this->router->post('/api/quantum/secure-communication', [$controller, 'establishSecureCommunication']);
        $this->router->get('/api/quantum/communication/status', [$controller, 'getCommunicationStatus']);

        // 量子威胁检测
        $this->router->get('/api/quantum/threats', [$controller, 'getQuantumThreats']);
        $this->router->post('/api/quantum/threats/detect', [$controller, 'detectQuantumThreats']);

        // 量子安全配置
        $this->router->get('/api/quantum/config', [$controller, 'getQuantumConfig']);
        $this->router->put('/api/quantum/config', [$controller, 'updateQuantumConfig']);
    }

    /**
     * 注册红蓝队演练路由
     */
    private function registerRedBlueTeamRoutes(): void
    {
        $controller = new RedBlueTeamApiController();

        // 演练场景
        $this->router->get('/api/red-blue-team/scenarios', [$controller, 'getExerciseScenarios']);
        $this->router->post('/api/red-blue-team/scenarios', [$controller, 'createExerciseScenario']);
        $this->router->get('/api/red-blue-team/scenarios/{id}', [$controller, 'getExerciseScenario']);

        // 演练管理
        $this->router->post('/api/red-blue-team/start', [$controller, 'startExercise']);
        $this->router->get('/api/red-blue-team/status/{exerciseId}', [$controller, 'getExerciseStatus']);
        $this->router->post('/api/red-blue-team/stop/{exerciseId}', [$controller, 'stopExercise']);

        // 红队攻击
        $this->router->post('/api/red-blue-team/red-team/attack', [$controller, 'executeRedTeamAttack']);
        $this->router->post('/api/red-blue-team/red-team/vulnerability', [$controller, 'executeVulnerabilityExploitation']);
        $this->router->post('/api/red-blue-team/red-team/social-engineering', [$controller, 'executeSocialEngineeringAttack']);

        // 蓝队防御
        $this->router->post('/api/red-blue-team/blue-team/defense', [$controller, 'executeBlueTeamDefense']);
        $this->router->post('/api/red-blue-team/blue-team/threat-detection', [$controller, 'performThreatDetection']);
        $this->router->post('/api/red-blue-team/blue-team/incident-response', [$controller, 'executeIncidentResponse']);

        // 演练结果
        $this->router->get('/api/red-blue-team/results/{exerciseId}', [$controller, 'getExerciseResults']);
        $this->router->post('/api/red-blue-team/report/{exerciseId}', [$controller, 'generateExerciseReport']);
        $this->router->get('/api/red-blue-team/history', [$controller, 'getExerciseHistory']);
        $this->router->get('/api/red-blue-team/statistics', [$controller, 'getExerciseStatistics']);
    }

    /**
     * 获取所有安全API路由
     * 
     * @return array
     */
    public function getAllRoutes(): array
    {
        return [
            'security_dashboard' => [
                'GET /api/security/dashboard/overview',
                'GET /api/security/dashboard/metrics',
                'GET /api/security/dashboard/alerts',
                'GET /api/security/dashboard/events',
                'GET /api/security/dashboard/monitoring/network',
                'GET /api/security/dashboard/monitoring/system',
                'GET /api/security/dashboard/monitoring/application',
                'GET /api/security/dashboard/threats/analysis',
                'GET /api/security/dashboard/threats/trends',
                'GET /api/security/dashboard/threats/map',
                'GET /api/security/dashboard/score',
                'POST /api/security/dashboard/score/calculate'
            ],
            'security_management' => [
                'GET /api/security/policies',
                'POST /api/security/policies',
                'PUT /api/security/policies/{id}',
                'DELETE /api/security/policies/{id}',
                'GET /api/security/rules',
                'POST /api/security/rules',
                'PUT /api/security/rules/{id}',
                'DELETE /api/security/rules/{id}',
                'GET /api/security/whitelist',
                'POST /api/security/whitelist',
                'DELETE /api/security/whitelist/{id}',
                'GET /api/security/blacklist',
                'POST /api/security/blacklist',
                'DELETE /api/security/blacklist/{id}',
                'GET /api/security/config',
                'PUT /api/security/config',
                'POST /api/security/config/backup',
                'POST /api/security/config/restore'
            ],
            'incident_response' => [
                'GET /api/incidents',
                'GET /api/incidents/{id}',
                'POST /api/incidents',
                'PUT /api/incidents/{id}',
                'DELETE /api/incidents/{id}',
                'POST /api/incidents/{id}/respond',
                'POST /api/incidents/{id}/escalate',
                'POST /api/incidents/{id}/resolve',
                'GET /api/incidents/categories',
                'POST /api/incidents/categories',
                'GET /api/incidents/playbooks',
                'POST /api/incidents/playbooks',
                'PUT /api/incidents/playbooks/{id}'
            ],
            'threat_intelligence' => [
                'GET /api/threat-intelligence/feeds',
                'POST /api/threat-intelligence/feeds',
                'PUT /api/threat-intelligence/feeds/{id}',
                'DELETE /api/threat-intelligence/feeds/{id}',
                'GET /api/threat-intelligence/indicators',
                'POST /api/threat-intelligence/indicators',
                'PUT /api/threat-intelligence/indicators/{id}',
                'DELETE /api/threat-intelligence/indicators/{id}',
                'GET /api/threat-intelligence/analysis',
                'POST /api/threat-intelligence/analyze',
                'GET /api/threat-intelligence/trends',
                'GET /api/threat-intelligence/sharing',
                'POST /api/threat-intelligence/share',
                'GET /api/threat-intelligence/received'
            ],
            'compliance' => [
                'GET /api/compliance/frameworks',
                'POST /api/compliance/frameworks',
                'PUT /api/compliance/frameworks/{id}',
                'DELETE /api/compliance/frameworks/{id}',
                'GET /api/compliance/requirements',
                'POST /api/compliance/requirements',
                'PUT /api/compliance/requirements/{id}',
                'DELETE /api/compliance/requirements/{id}',
                'GET /api/compliance/assessments',
                'POST /api/compliance/assessments',
                'GET /api/compliance/assessments/{id}',
                'PUT /api/compliance/assessments/{id}',
                'GET /api/compliance/monitoring',
                'POST /api/compliance/monitoring/check',
                'GET /api/compliance/violations'
            ],
            'reports' => [
                'GET /api/reports/security',
                'POST /api/reports/security',
                'GET /api/reports/security/{id}',
                'DELETE /api/reports/security/{id}',
                'GET /api/reports/compliance',
                'POST /api/reports/compliance',
                'GET /api/reports/compliance/{id}',
                'GET /api/reports/incidents',
                'POST /api/reports/incidents',
                'GET /api/reports/incidents/{id}',
                'GET /api/reports/threats',
                'POST /api/reports/threats',
                'GET /api/reports/threats/{id}',
                'POST /api/reports/export',
                'GET /api/reports/templates',
                'POST /api/reports/schedule'
            ],
            'quantum_security' => [
                'POST /api/quantum/encrypt',
                'POST /api/quantum/decrypt',
                'GET /api/quantum/keys',
                'POST /api/quantum/keys/generate',
                'POST /api/quantum/key-distribution',
                'GET /api/quantum/key-distribution/status',
                'POST /api/quantum/secure-communication',
                'GET /api/quantum/communication/status',
                'GET /api/quantum/threats',
                'POST /api/quantum/threats/detect',
                'GET /api/quantum/config',
                'PUT /api/quantum/config'
            ],
            'red_blue_team' => [
                'GET /api/red-blue-team/scenarios',
                'POST /api/red-blue-team/scenarios',
                'GET /api/red-blue-team/scenarios/{id}',
                'POST /api/red-blue-team/start',
                'GET /api/red-blue-team/status/{exerciseId}',
                'POST /api/red-blue-team/stop/{exerciseId}',
                'POST /api/red-blue-team/red-team/attack',
                'POST /api/red-blue-team/red-team/vulnerability',
                'POST /api/red-blue-team/red-team/social-engineering',
                'POST /api/red-blue-team/blue-team/defense',
                'POST /api/red-blue-team/blue-team/threat-detection',
                'POST /api/red-blue-team/blue-team/incident-response',
                'GET /api/red-blue-team/results/{exerciseId}',
                'POST /api/red-blue-team/report/{exerciseId}',
                'GET /api/red-blue-team/history',
                'GET /api/red-blue-team/statistics'
            ]
        ];
    }
} 