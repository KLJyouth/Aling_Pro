<?php

namespace AlingAi\Controllers\Api;

use AlingAi\Services\SecurityService;
use AlingAi\Services\MonitoringService;
use AlingAi\Services\ThreatDetectionService;
use AlingAi\Utils\SystemInfo;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

/**
 * 安全监控API控制器
 * 
 * 提供系统安全监控、威胁检测、安全事件管理等功能
 * 优化性能：实时监控、智能告警、数据聚合
 * 增强安全性：多层防护、异常检测、安全审计
 */
class SecurityMonitoringApiController extends BaseApiController
{
    private SecurityService $securityService;
    private MonitoringService $monitoringService;
    private ThreatDetectionService $threatDetectionService;
    private LoggerInterface $logger;
    
    public function __construct(
        SecurityService $securityService,
        MonitoringService $monitoringService,
        ThreatDetectionService $threatDetectionService,
        LoggerInterface $logger
    ) {
        parent::__construct();
        $this->securityService = $securityService;
        $this->monitoringService = $monitoringService;
        $this->threatDetectionService = $threatDetectionService;
        $this->logger = $logger;
    }

    /**
     * 获取系统安全状态
     */
    public function getSecurityStatus(ServerRequestInterface $request): array
    {
        try {
            if (!$this->requireAuth()) {
                return $this->sendErrorResponse('未授权访问', 401);
            }

            $userId = $this->getCurrentUserId();
            
            // 检查管理员权限
            if (!$this->isAdmin($userId)) {
                return $this->sendErrorResponse('需要管理员权限', 403);
            }

            $status = [
                'overall_status' => 'healthy',
                'last_updated' => date('Y-m-d H:i:s'),
                'components' => []
            ];

            // 检查各个安全组件状态
            $components = [
                'firewall' => $this->securityService->getFirewallStatus(),
                'intrusion_detection' => $this->securityService->getIntrusionDetectionStatus(),
                'malware_protection' => $this->securityService->getMalwareProtectionStatus(),
                'encryption' => $this->securityService->getEncryptionStatus(),
                'access_control' => $this->securityService->getAccessControlStatus()
            ];

            foreach ($components as $name => $componentStatus) {
                $status['components'][$name] = $componentStatus;
                if ($componentStatus['status'] !== 'healthy') {
                    $status['overall_status'] = 'warning';
                }
            }

            // 获取威胁统计
            $threatStats = $this->threatDetectionService->getThreatStatistics();
            $status['threat_statistics'] = $threatStats;

            return $this->sendSuccessResponse($status, '安全状态获取成功');

        } catch (\Exception $e) {
            $this->logger->error('获取安全状态失败', [
                'error' => $e->getMessage()
            ]);

            return $this->sendErrorResponse('获取安全状态失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 获取安全事件列表
     */
    public function getSecurityEvents(ServerRequestInterface $request): array
    {
        try {
            if (!$this->requireAuth()) {
                return $this->sendErrorResponse('未授权访问', 401);
            }

            $userId = $this->getCurrentUserId();
            
            if (!$this->isAdmin($userId)) {
                return $this->sendErrorResponse('需要管理员权限', 403);
            }

            $params = $request->getQueryParams();
            $page = (int)($params['page'] ?? 1);
            $limit = min((int)($params['limit'] ?? 20), 100);
            $severity = $params['severity'] ?? '';
            $type = $params['type'] ?? '';
            $dateFrom = $params['date_from'] ?? '';
            $dateTo = $params['date_to'] ?? '';

            $events = $this->securityService->getSecurityEvents([
                'page' => $page,
                'limit' => $limit,
                'severity' => $severity,
                'type' => $type,
                'date_from' => $dateFrom,
                'date_to' => $dateTo
            ]);

            return $this->sendSuccessResponse($events, '安全事件列表获取成功');

        } catch (\Exception $e) {
            $this->logger->error('获取安全事件列表失败', [
                'error' => $e->getMessage()
            ]);

            return $this->sendErrorResponse('获取安全事件列表失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 获取威胁检测报告
     */
    public function getThreatReport(ServerRequestInterface $request): array
    {
        try {
            if (!$this->requireAuth()) {
                return $this->sendErrorResponse('未授权访问', 401);
            }

            $userId = $this->getCurrentUserId();
            
            if (!$this->isAdmin($userId)) {
                return $this->sendErrorResponse('需要管理员权限', 403);
            }

            $params = $request->getQueryParams();
            $period = $params['period'] ?? '24h'; // 24h, 7d, 30d, 90d
            $type = $params['type'] ?? 'all'; // all, malware, intrusion, ddos

            $report = $this->threatDetectionService->generateThreatReport($period, $type);

            return $this->sendSuccessResponse($report, '威胁检测报告生成成功');

        } catch (\Exception $e) {
            $this->logger->error('生成威胁检测报告失败', [
                'error' => $e->getMessage()
            ]);

            return $this->sendErrorResponse('生成威胁检测报告失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 获取系统性能监控数据
     */
    public function getSystemMetrics(ServerRequestInterface $request): array
    {
        try {
            if (!$this->requireAuth()) {
                return $this->sendErrorResponse('未授权访问', 401);
            }

            $userId = $this->getCurrentUserId();
            
            if (!$this->isAdmin($userId)) {
                return $this->sendErrorResponse('需要管理员权限', 403);
            }

            $params = $request->getQueryParams();
            $metric = $params['metric'] ?? 'all'; // cpu, memory, disk, network, all
            $duration = $params['duration'] ?? '1h'; // 1h, 6h, 24h, 7d

            $metrics = $this->monitoringService->getSystemMetrics($metric, $duration);

            return $this->sendSuccessResponse($metrics, '系统性能数据获取成功');

        } catch (\Exception $e) {
            $this->logger->error('获取系统性能数据失败', [
                'error' => $e->getMessage()
            ]);

            return $this->sendErrorResponse('获取系统性能数据失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 获取网络流量监控
     */
    public function getNetworkTraffic(ServerRequestInterface $request): array
    {
        try {
            if (!$this->requireAuth()) {
                return $this->sendErrorResponse('未授权访问', 401);
            }

            $userId = $this->getCurrentUserId();
            
            if (!$this->isAdmin($userId)) {
                return $this->sendErrorResponse('需要管理员权限', 403);
            }

            $params = $request->getQueryParams();
            $interface = $params['interface'] ?? 'all';
            $duration = $params['duration'] ?? '1h';

            $traffic = $this->monitoringService->getNetworkTraffic($interface, $duration);

            return $this->sendSuccessResponse($traffic, '网络流量数据获取成功');

        } catch (\Exception $e) {
            $this->logger->error('获取网络流量数据失败', [
                'error' => $e->getMessage()
            ]);

            return $this->sendErrorResponse('获取网络流量数据失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 获取活跃连接
     */
    public function getActiveConnections(ServerRequestInterface $request): array
    {
        try {
            if (!$this->requireAuth()) {
                return $this->sendErrorResponse('未授权访问', 401);
            }

            $userId = $this->getCurrentUserId();
            
            if (!$this->isAdmin($userId)) {
                return $this->sendErrorResponse('需要管理员权限', 403);
            }

            $connections = $this->monitoringService->getActiveConnections();

            return $this->sendSuccessResponse($connections, '活跃连接列表获取成功');

        } catch (\Exception $e) {
            $this->logger->error('获取活跃连接失败', [
                'error' => $e->getMessage()
            ]);

            return $this->sendErrorResponse('获取活跃连接失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 获取安全告警
     */
    public function getSecurityAlerts(ServerRequestInterface $request): array
    {
        try {
            if (!$this->requireAuth()) {
                return $this->sendErrorResponse('未授权访问', 401);
            }

            $userId = $this->getCurrentUserId();
            
            if (!$this->isAdmin($userId)) {
                return $this->sendErrorResponse('需要管理员权限', 403);
            }

            $params = $request->getQueryParams();
            $status = $params['status'] ?? 'active'; // active, resolved, all
            $severity = $params['severity'] ?? '';
            $page = (int)($params['page'] ?? 1);
            $limit = min((int)($params['limit'] ?? 20), 100);

            $alerts = $this->securityService->getSecurityAlerts([
                'status' => $status,
                'severity' => $severity,
                'page' => $page,
                'limit' => $limit
            ]);

            return $this->sendSuccessResponse($alerts, '安全告警列表获取成功');

        } catch (\Exception $e) {
            $this->logger->error('获取安全告警失败', [
                'error' => $e->getMessage()
            ]);

            return $this->sendErrorResponse('获取安全告警失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 处理安全告警
     */
    public function handleSecurityAlert(ServerRequestInterface $request): array
    {
        try {
            if (!$this->requireAuth()) {
                return $this->sendErrorResponse('未授权访问', 401);
            }

            $userId = $this->getCurrentUserId();
            
            if (!$this->isAdmin($userId)) {
                return $this->sendErrorResponse('需要管理员权限', 403);
            }

            $alertId = $request->getAttribute('id');
            $data = $this->getJsonData($request);
            
            $action = $data['action'] ?? ''; // resolve, ignore, escalate
            $notes = $data['notes'] ?? '';

            $result = $this->securityService->handleSecurityAlert($alertId, $action, $notes, $userId);

            if (!$result['success']) {
                return $this->sendErrorResponse($result['message'], 400);
            }

            return $this->sendSuccessResponse($result['alert'], '安全告警处理成功');

        } catch (\Exception $e) {
            $this->logger->error('处理安全告警失败', [
                'alert_id' => $alertId ?? null,
                'error' => $e->getMessage()
            ]);

            return $this->sendErrorResponse('处理安全告警失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 获取安全配置
     */
    public function getSecurityConfig(ServerRequestInterface $request): array
    {
        try {
            if (!$this->requireAuth()) {
                return $this->sendErrorResponse('未授权访问', 401);
            }

            $userId = $this->getCurrentUserId();
            
            if (!$this->isAdmin($userId)) {
                return $this->sendErrorResponse('需要管理员权限', 403);
            }

            $config = $this->securityService->getSecurityConfiguration();

            return $this->sendSuccessResponse($config, '安全配置获取成功');

        } catch (\Exception $e) {
            $this->logger->error('获取安全配置失败', [
                'error' => $e->getMessage()
            ]);

            return $this->sendErrorResponse('获取安全配置失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 更新安全配置
     */
    public function updateSecurityConfig(ServerRequestInterface $request): array
    {
        try {
            if (!$this->requireAuth()) {
                return $this->sendErrorResponse('未授权访问', 401);
            }

            $userId = $this->getCurrentUserId();
            
            if (!$this->isAdmin($userId)) {
                return $this->sendErrorResponse('需要管理员权限', 403);
            }

            $data = $this->getJsonData($request);
            
            $result = $this->securityService->updateSecurityConfiguration($data, $userId);

            if (!$result['success']) {
                return $this->sendErrorResponse($result['message'], 400);
            }

            return $this->sendSuccessResponse($result['config'], '安全配置更新成功');

        } catch (\Exception $e) {
            $this->logger->error('更新安全配置失败', [
                'error' => $e->getMessage()
            ]);

            return $this->sendErrorResponse('更新安全配置失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 获取系统健康检查
     */
    public function getHealthCheck(ServerRequestInterface $request): array
    {
        try {
            if (!$this->requireAuth()) {
                return $this->sendErrorResponse('未授权访问', 401);
            }

            $userId = $this->getCurrentUserId();
            
            if (!$this->isAdmin($userId)) {
                return $this->sendErrorResponse('需要管理员权限', 403);
            }

            $health = SystemInfo::healthCheck();

            return $this->sendSuccessResponse($health, '系统健康检查完成');

        } catch (\Exception $e) {
            $this->logger->error('系统健康检查失败', [
                'error' => $e->getMessage()
            ]);

            return $this->sendErrorResponse('系统健康检查失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 获取实时监控数据
     */
    public function getRealTimeMetrics(ServerRequestInterface $request): array
    {
        try {
            if (!$this->requireAuth()) {
                return $this->sendErrorResponse('未授权访问', 401);
            }

            $userId = $this->getCurrentUserId();
            
            if (!$this->isAdmin($userId)) {
                return $this->sendErrorResponse('需要管理员权限', 403);
            }

            $metrics = $this->monitoringService->getRealTimeMetrics();

            return $this->sendSuccessResponse($metrics, '实时监控数据获取成功');

        } catch (\Exception $e) {
            $this->logger->error('获取实时监控数据失败', [
                'error' => $e->getMessage()
            ]);

            return $this->sendErrorResponse('获取实时监控数据失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 检查是否为管理员
     */
    private function isAdmin(int $userId): bool
    {
        // 这里应该检查用户角色
        // 简化实现，实际应该查询数据库
        return $userId === 1; // 假设用户ID为1是管理员
    }
}
