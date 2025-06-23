<?php
namespace AlingAi\Controllers\Admin;

use AlingAi\Monitoring\Storage\MetricsStorageInterface;
use AlingAi\Monitoring\Config\GatewayConfig;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Exception;

/**
 * 监控管理控制器 - 处理API监控系统的Web界面请求
 */
class MonitoringController
{
    /**
     * @var MetricsStorageInterface
     */
    private $metricsStorage;
    
    /**
     * @var GatewayConfig
     */
    private $gatewayConfig;
    
    /**
     * @var LoggerInterface
     */
    private $logger;
    
    /**
     * @var \Twig\Environment
     */
    private $twig;

    /**
     * 构造函数
     */
    public function __construct(
        MetricsStorageInterface $metricsStorage,
        GatewayConfig $gatewayConfig,
        LoggerInterface $logger,
        \Twig\Environment $twig
    ) {
        $this->metricsStorage = $metricsStorage;
        $this->gatewayConfig = $gatewayConfig;
        $this->logger = $logger;
        $this->twig = $twig;
    }

    /**
     * 显示仪表盘页面
     */
    public function dashboard(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            // 获取所有API名称
            $apiNames = $this->metricsStorage->getAllApiNames();
            
            // 按类型分组API
            $internalApis = [];
            $externalApis = [];
            $incomingApis = [];
            
            foreach ($apiNames as $apiName) {
                // 根据命名约定确定API类型
                if (strpos($apiName, 'internal:') === 0) {
                    $internalApis[] = $apiName;
                } elseif (strpos($apiName, 'incoming:') === 0) {
                    $incomingApis[] = $apiName;
                } else {
                    $externalApis[] = $apiName;
                }
            }
            
            // 获取每个API的基本指标
            $apiMetrics = [];
            
            foreach ($apiNames as $apiName) {
                // 获取过去24小时的指标
                $startTime = time() - 86400;
                
                $apiMetrics[$apiName] = [
                    'name' => $apiName,
                    'avg_response_time' => $this->metricsStorage->getAverageResponseTime($apiName, $startTime) ?? 0,
                    'error_rate' => $this->metricsStorage->getErrorRate($apiName, $startTime) ?? 0,
                    'availability' => $this->metricsStorage->getAvailabilityPercentage($apiName, $startTime) ?? 100,
                    'type' => $this->getApiType($apiName),
                ];
            }
            
            // 渲染模板
            $html = $this->twig->render('admin/monitoring/dashboard.twig', [
                'api_metrics' => $apiMetrics,
                'internal_apis' => $internalApis,
                'external_apis' => $externalApis,
                'incoming_apis' => $incomingApis,
            ]);
            
            $response->getBody()->write($html);
            return $response;
        } catch (Exception $e) {
            $this->logger->error("加载监控仪表盘失败", [
                'error' => $e->getMessage(),
            ]);
            
            $response->getBody()->write("加载监控仪表盘失败: " . $e->getMessage());
            return $response->withStatus(500);
        }
    }

    /**
     * 显示API详情页面
     */
    public function apiDetail(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $apiName = $args['name'] ?? null;
            
            if (!$apiName) {
                return $response->withStatus(400)->withHeader('Location', '/admin/monitoring');
            }
            
            // 获取时间范围
            $queryParams = $request->getQueryParams();
            $timeRange = $queryParams['time_range'] ?? '24h';
            
            $startTime = $this->getStartTimeFromRange($timeRange);
            $endTime = time();
            
            // 获取API详细指标
            $metrics = $this->metricsStorage->getMetricsByTimeRange($apiName, $startTime, $endTime);
            
            // 计算聚合指标
            $avgResponseTime = $this->metricsStorage->getAverageResponseTime($apiName, $startTime, $endTime) ?? 0;
            $errorRate = $this->metricsStorage->getErrorRate($apiName, $startTime, $endTime) ?? 0;
            $availability = $this->metricsStorage->getAvailabilityPercentage($apiName, $startTime, $endTime) ?? 100;
            
            // 准备图表数据
            $chartData = $this->prepareChartData($metrics, $startTime, $endTime);
            
            // 获取最近的错误
            $recentErrors = array_filter($metrics, function($metric) {
                return !$metric['success'];
            });
            
            // 按时间排序
            usort($recentErrors, function($a, $b) {
                return $b['timestamp'] <=> $a['timestamp'];
            });
            
            // 限制错误数量
            $recentErrors = array_slice($recentErrors, 0, 10);
            
            // 渲染模板
            $html = $this->twig->render('admin/monitoring/api_detail.twig', [
                'api_name' => $apiName,
                'api_type' => $this->getApiType($apiName),
                'time_range' => $timeRange,
                'avg_response_time' => $avgResponseTime,
                'error_rate' => $errorRate,
                'availability' => $availability,
                'chart_data' => json_encode($chartData),
                'recent_errors' => $recentErrors,
                'metrics_count' => count($metrics),
            ]);
            
            $response->getBody()->write($html);
            return $response;
        } catch (Exception $e) {
            $this->logger->error("加载API详情页面失败", [
                'error' => $e->getMessage(),
                'api_name' => $args['name'] ?? null,
            ]);
            
            $response->getBody()->write("加载API详情页面失败: " . $e->getMessage());
            return $response->withStatus(500);
        }
    }

    /**
     * 获取API配置页面
     */
    public function apiConfig(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            // 获取所有配置的API
            $providers = $this->gatewayConfig->getAllProviders();
            
            // 渲染模板
            $html = $this->twig->render('admin/monitoring/api_config.twig', [
                'providers' => $providers,
            ]);
            
            $response->getBody()->write($html);
            return $response;
        } catch (Exception $e) {
            $this->logger->error("加载API配置页面失败", [
                'error' => $e->getMessage(),
            ]);
            
            $response->getBody()->write("加载API配置页面失败: " . $e->getMessage());
            return $response->withStatus(500);
        }
    }

    /**
     * 保存API配置
     */
    public function saveApiConfig(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $data = $request->getParsedBody();
            
            // 验证提交的数据
            if (!isset($data['provider_name']) || empty($data['provider_name'])) {
                throw new Exception("API提供者名称不能为空");
            }
            
            if (!isset($data['base_url']) || empty($data['base_url'])) {
                throw new Exception("基础URL不能为空");
            }
            
            // 创建或更新API提供者配置
            $providerName = $data['provider_name'];
            $config = [
                'base_url' => $data['base_url'],
                'timeout' => (int) ($data['timeout'] ?? 30),
                'default_headers' => $this->parseHeaders($data['default_headers'] ?? ''),
                'auth_type' => $data['auth_type'] ?? 'none',
            ];
            
            // 根据认证类型添加认证信息
            if ($config['auth_type'] === 'basic') {
                $config['auth'] = [
                    'username' => $data['auth_username'] ?? '',
                    'password' => $data['auth_password'] ?? '',
                ];
            } elseif ($config['auth_type'] === 'bearer') {
                $config['auth'] = [
                    'token' => $data['auth_token'] ?? '',
                ];
            } elseif ($config['auth_type'] === 'api_key') {
                $config['auth'] = [
                    'key_name' => $data['auth_key_name'] ?? '',
                    'key_value' => $data['auth_key_value'] ?? '',
                    'key_in' => $data['auth_key_in'] ?? 'header',
                ];
            }
            
            $this->gatewayConfig->setProviderConfig($providerName, $config);
            
            // 重定向回配置页面
            return $response->withStatus(302)->withHeader('Location', '/admin/monitoring/config?success=1');
        } catch (Exception $e) {
            $this->logger->error("保存API配置失败", [
                'error' => $e->getMessage(),
                'data' => $request->getParsedBody(),
            ]);
            
            // 重定向回配置页面，带错误信息
            return $response->withStatus(302)->withHeader('Location', '/admin/monitoring/config?error=' . urlencode($e->getMessage()));
        }
    }

    /**
     * 获取告警页面
     */
    public function alerts(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            // 这里我们需要一个告警存储服务来获取告警历史
            // 由于这超出了当前实现范围，我们将展示一个简单的示例页面
            
            // 渲染模板
            $html = $this->twig->render('admin/monitoring/alerts.twig', [
                'alerts' => [], // 实际应用中，从告警存储中获取
            ]);
            
            $response->getBody()->write($html);
            return $response;
        } catch (Exception $e) {
            $this->logger->error("加载告警页面失败", [
                'error' => $e->getMessage(),
            ]);
            
            $response->getBody()->write("加载告警页面失败: " . $e->getMessage());
            return $response->withStatus(500);
        }
    }

    /**
     * 获取API健康检查页面
     */
    public function healthChecks(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            // 渲染模板
            $html = $this->twig->render('admin/monitoring/health_checks.twig', [
                'health_checks' => [], // 实际应用中，从健康检查服务中获取
            ]);
            
            $response->getBody()->write($html);
            return $response;
        } catch (Exception $e) {
            $this->logger->error("加载健康检查页面失败", [
                'error' => $e->getMessage(),
            ]);
            
            $response->getBody()->write("加载健康检查页面失败: " . $e->getMessage());
            return $response->withStatus(500);
        }
    }

    /**
     * 从时间范围获取开始时间
     */
    private function getStartTimeFromRange(string $range): int
    {
        $now = time();
        
        switch ($range) {
            case '1h':
                return $now - 3600;
            case '6h':
                return $now - 21600;
            case '12h':
                return $now - 43200;
            case '24h':
                return $now - 86400;
            case '7d':
                return $now - 604800;
            case '30d':
                return $now - 2592000;
            default:
                return $now - 86400; // 默认24小时
        }
    }

    /**
     * 准备图表数据
     */
    private function prepareChartData(array $metrics, int $startTime, int $endTime): array
    {
        // 确定时间间隔
        $totalSeconds = $endTime - $startTime;
        $interval = $this->getChartInterval($totalSeconds);
        
        // 初始化数据点
        $responseTimeSeries = [];
        $errorRateSeries = [];
        
        // 按时间间隔分组数据
        $groupedMetrics = [];
        
        foreach ($metrics as $metric) {
            $timestamp = $metric['timestamp'];
            $bucket = floor($timestamp / $interval) * $interval;
            
            if (!isset($groupedMetrics[$bucket])) {
                $groupedMetrics[$bucket] = [
                    'total' => 0,
                    'errors' => 0,
                    'duration_sum' => 0,
                ];
            }
            
            $groupedMetrics[$bucket]['total']++;
            
            if (!$metric['success']) {
                $groupedMetrics[$bucket]['errors']++;
            }
            
            $groupedMetrics[$bucket]['duration_sum'] += $metric['duration'];
        }
        
        // 填充数据点
        for ($bucket = floor($startTime / $interval) * $interval; $bucket <= $endTime; $bucket += $interval) {
            $time = $bucket * 1000; // 转换为毫秒，用于前端图表
            
            // 响应时间数据点
            $avgDuration = 0;
            if (isset($groupedMetrics[$bucket]) && $groupedMetrics[$bucket]['total'] > 0) {
                $avgDuration = $groupedMetrics[$bucket]['duration_sum'] / $groupedMetrics[$bucket]['total'];
            }
            
            $responseTimeSeries[] = [$time, round($avgDuration, 3)];
            
            // 错误率数据点
            $errorRate = 0;
            if (isset($groupedMetrics[$bucket]) && $groupedMetrics[$bucket]['total'] > 0) {
                $errorRate = $groupedMetrics[$bucket]['errors'] / $groupedMetrics[$bucket]['total'] * 100;
            }
            
            $errorRateSeries[] = [$time, round($errorRate, 2)];
        }
        
        return [
            'response_time' => $responseTimeSeries,
            'error_rate' => $errorRateSeries,
        ];
    }

    /**
     * 获取图表时间间隔
     */
    private function getChartInterval(int $totalSeconds): int
    {
        if ($totalSeconds <= 3600) { // 1小时
            return 60; // 1分钟
        } elseif ($totalSeconds <= 43200) { // 12小时
            return 300; // 5分钟
        } elseif ($totalSeconds <= 86400) { // 24小时
            return 900; // 15分钟
        } elseif ($totalSeconds <= 604800) { // 7天
            return 3600; // 1小时
        } else {
            return 86400; // 1天
        }
    }

    /**
     * 解析HTTP头
     */
    private function parseHeaders(string $headersStr): array
    {
        $headers = [];
        $lines = explode("\n", trim($headersStr));
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }
            
            $parts = explode(':', $line, 2);
            if (count($parts) !== 2) {
                continue;
            }
            
            $name = trim($parts[0]);
            $value = trim($parts[1]);
            
            if (!empty($name)) {
                $headers[$name] = $value;
            }
        }
        
        return $headers;
    }

    /**
     * 获取API类型
     */
    private function getApiType(string $apiName): string
    {
        if (strpos($apiName, 'internal:') === 0) {
            return 'internal';
        } elseif (strpos($apiName, 'incoming:') === 0) {
            return 'incoming';
        } else {
            return 'external';
        }
    }
} 