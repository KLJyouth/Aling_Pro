<?php

namespace AlingAi\Security;

use Psr\Log\LoggerInterface;
use AlingAi\Core\Container;
use AlingAi\AI\MachineLearning\PredictiveAnalytics;

/**
 * 全局态势感知系统
 * 
 * 实时监控和分析全局安全态势，提供预测性安全分析和威胁情报
 * 增强安全性：全局威胁感知、预测性分析和智能响应
 * 优化性能：分布式监控和智能聚合
 */
class GlobalSituationAwarenessSystem
{
    private $logger;
    private $container;
    private $config = [];
    private $predictiveAnalytics;
    private $threatIntelligence = [];
    private $securityEvents = [];
    private $systemMetrics = [];
    private $threatIndicators = [];
    private $riskAssessment = [];
    private $lastAnalysis = 0;
    private $analysisInterval = 300; // 5分钟分析一次

    /**
     * 构造函数
     * 
     * @param LoggerInterface $logger 日志接口
     * @param Container $container 容器
     */
    public function __construct(LoggerInterface $logger, Container $container)
    {
        $this->logger = $logger;
        $this->container = $container;
        
        $this->config = $this->loadConfiguration();
        $this->initializeComponents();
        $this->initializeThreatIntelligence();
    }
    
    /**
     * 加载配置
     * 
     * @return array
     */
    private function loadConfiguration(): array
    {
        return [
            'monitoring' => [
                'enabled' => env('GSA_MONITORING_ENABLED', true),
                'real_time' => env('GSA_REAL_TIME_MONITORING', true),
                'distributed' => env('GSA_DISTRIBUTED_MONITORING', true),
                'data_retention' => env('GSA_DATA_RETENTION', 604800) // 7天
            ],
            'analysis' => [
                'predictive_enabled' => env('GSA_PREDICTIVE_ENABLED', true),
                'correlation_enabled' => env('GSA_CORRELATION_ENABLED', true),
                'anomaly_detection' => env('GSA_ANOMALY_DETECTION', true),
                'trend_analysis' => env('GSA_TREND_ANALYSIS', true)
            ],
            'threat_intelligence' => [
                'sources' => explode(',', env('GSA_THREAT_SOURCES', 'internal,external,community')),
                'update_interval' => env('GSA_TI_UPDATE_INTERVAL', 3600), // 1小时
                'confidence_threshold' => env('GSA_TI_CONFIDENCE_THRESHOLD', 0.7),
                'sharing_enabled' => env('GSA_TI_SHARING_ENABLED', true)
            ],
            'risk_assessment' => [
                'dynamic_scoring' => env('GSA_DYNAMIC_SCORING', true),
                'risk_categories' => [
                    'critical' => ['threshold' => 0.9, 'color' => 'red'],
                    'high' => ['threshold' => 0.7, 'color' => 'orange'],
                    'medium' => ['threshold' => 0.5, 'color' => 'yellow'],
                    'low' => ['threshold' => 0.3, 'color' => 'green']
                ],
                'update_frequency' => env('GSA_RISK_UPDATE_FREQUENCY', 300) // 5分钟
            ],
            'alerting' => [
                'enabled' => env('GSA_ALERTING_ENABLED', true),
                'channels' => explode(',', env('GSA_ALERT_CHANNELS', 'email,webhook,dashboard')),
                'escalation_rules' => env('GSA_ESCALATION_RULES', true),
                'notification_threshold' => env('GSA_NOTIFICATION_THRESHOLD', 0.6)
            ]
        ];
    }
    
    /**
     * 初始化组件
     */
    private function initializeComponents(): void
    {
        // 初始化预测分析器
        $this->predictiveAnalytics = new PredictiveAnalytics([
            'time_series_analysis' => true,
            'pattern_recognition' => true,
            'forecasting_horizon' => 24, // 24小时
            'confidence_interval' => 0.95
        ]);
        
        // 初始化威胁情报
        $this->threatIntelligence = [
            'internal' => [],
            'external' => [],
            'community' => [],
            'correlated' => [],
            'last_update' => time()
        ];
        
        // 初始化安全事件
        $this->securityEvents = [
            'recent' => [],
            'critical' => [],
            'trending' => [],
            'correlated' => []
        ];
        
        // 初始化系统指标
        $this->systemMetrics = [
            'performance' => [],
            'security' => [],
            'network' => [],
            'application' => []
        ];
        
        // 初始化威胁指标
        $this->threatIndicators = [
            'active_threats' => [],
            'emerging_threats' => [],
            'threat_trends' => [],
            'vulnerability_alerts' => []
        ];
        
        // 初始化风险评估
        $this->riskAssessment = [
            'current_risk' => 0.0,
            'risk_history' => [],
            'risk_factors' => [],
            'mitigation_actions' => []
        ];
    }
    
    /**
     * 初始化威胁情报
     */
    private function initializeThreatIntelligence(): void
    {
        // 初始化内部威胁情报
        $this->threatIntelligence['internal'] = [
            'threats' => [],
            'indicators' => [],
            'patterns' => [],
            'last_analysis' => time()
        ];
        
        // 初始化外部威胁情报
        $this->threatIntelligence['external'] = [
            'feeds' => [],
            'threats' => [],
            'indicators' => [],
            'last_update' => time()
        ];
        
        // 初始化社区威胁情报
        $this->threatIntelligence['community'] = [
            'shared_threats' => [],
            'collaborative_analysis' => [],
            'last_sync' => time()
        ];
    }
    
    /**
     * 更新安全态势
     * 
     * @param array $eventData 事件数据
     * @return array 更新结果
     */
    public function updateSituation(array $eventData): array
    {
        $this->logger->debug('更新安全态势', [
            'event_type' => $eventData['type'] ?? 'unknown',
            'severity' => $eventData['severity'] ?? 'low'
        ]);
        
        $updateResult = [
            'updated' => false,
            'impact_level' => 'none',
            'risk_change' => 0.0,
            'actions_triggered' => []
        ];
        
        // 记录安全事件
        $this->recordSecurityEvent($eventData);
        
        // 更新威胁指标
        $this->updateThreatIndicators($eventData);
        
        // 更新系统指标
        $this->updateSystemMetrics($eventData);
        
        // 执行态势分析
        $situationAnalysis = $this->analyzeSituation();
        
        // 更新风险评估
        $riskChange = $this->updateRiskAssessment($eventData, $situationAnalysis);
        
        // 检查是否需要触发行动
        $actions = $this->checkActionTriggers($situationAnalysis, $riskChange);
        
        $updateResult['updated'] = true;
        $updateResult['impact_level'] = $situationAnalysis['impact_level'];
        $updateResult['risk_change'] = $riskChange;
        $updateResult['actions_triggered'] = $actions;
        
        $this->logger->info('安全态势更新完成', [
            'impact_level' => $updateResult['impact_level'],
            'risk_change' => $updateResult['risk_change'],
            'actions_count' => count($updateResult['actions_triggered'])
        ]);
        
        return $updateResult;
    }
    
    /**
     * 记录安全事件
     * 
     * @param array $eventData 事件数据
     */
    private function recordSecurityEvent(array $eventData): void
    {
        $event = [
            'id' => uniqid('event_', true),
            'timestamp' => time(),
            'type' => $eventData['type'] ?? 'unknown',
            'severity' => $eventData['severity'] ?? 'low',
            'source' => $eventData['source'] ?? 'unknown',
            'data' => $eventData['data'] ?? [],
            'impact_score' => $this->calculateImpactScore($eventData)
        ];
        
        $this->securityEvents['recent'][] = $event;
        
        // 限制最近事件数量
        if (count($this->securityEvents['recent']) > 1000) {
            array_shift($this->securityEvents['recent']);
        }
        
        // 如果是关键事件，添加到关键事件列表
        if ($event['severity'] === 'critical' || $event['impact_score'] > 0.8) {
            $this->securityEvents['critical'][] = $event;
            
            // 限制关键事件数量
            if (count($this->securityEvents['critical']) > 100) {
                array_shift($this->securityEvents['critical']);
            }
        }
    }
    
    /**
     * 计算影响评分
     * 
     * @param array $eventData 事件数据
     * @return float 影响评分
     */
    private function calculateImpactScore(array $eventData): float
    {
        $score = 0.0;
        
        // 基于严重性评分
        $severityScores = [
            'critical' => 1.0,
            'high' => 0.8,
            'medium' => 0.5,
            'low' => 0.2
        ];
        
        $severity = $eventData['severity'] ?? 'low';
        $score += $severityScores[$severity] ?? 0.2;
        
        // 基于事件类型评分
        $typeScores = [
            'intrusion' => 0.9,
            'data_breach' => 1.0,
            'ddos_attack' => 0.8,
            'malware_detection' => 0.7,
            'unauthorized_access' => 0.6,
            'configuration_change' => 0.3
        ];
        
        $type = $eventData['type'] ?? 'unknown';
        $score += $typeScores[$type] ?? 0.1;
        
        // 基于数据影响评分
        if (isset($eventData['data']['affected_systems'])) {
            $affectedSystems = count($eventData['data']['affected_systems']);
            $score += min($affectedSystems * 0.1, 0.3);
        }
        
        return min($score, 1.0);
    }
    
    /**
     * 更新威胁指标
     * 
     * @param array $eventData 事件数据
     */
    private function updateThreatIndicators(array $eventData): void
    {
        $eventType = $eventData['type'] ?? '';
        $severity = $eventData['severity'] ?? 'low';
        
        // 更新活跃威胁
        if ($severity === 'high' || $severity === 'critical') {
            $threatIndicator = [
                'type' => $eventType,
                'severity' => $severity,
                'first_seen' => time(),
                'last_seen' => time(),
                'occurrence_count' => 1,
                'sources' => [$eventData['source'] ?? 'unknown']
            ];
            
            $this->threatIndicators['active_threats'][$eventType] = $threatIndicator;
        }
        
        // 更新威胁趋势
        $this->updateThreatTrends($eventData);
        
        // 检查新兴威胁
        $this->detectEmergingThreats($eventData);
    }
    
    /**
     * 更新威胁趋势
     * 
     * @param array $eventData 事件数据
     */
    private function updateThreatTrends(array $eventData): void
    {
        $eventType = $eventData['type'] ?? '';
        $currentTime = time();
        
        if (!isset($this->threatIndicators['threat_trends'][$eventType])) {
            $this->threatIndicators['threat_trends'][$eventType] = [
                'hourly' => array_fill(0, 24, 0),
                'daily' => array_fill(0, 7, 0),
                'last_update' => $currentTime
            ];
        }
        
        $trend = &$this->threatIndicators['threat_trends'][$eventType];
        
        // 更新小时统计
        $hour = (int)date('H', $currentTime);
        $trend['hourly'][$hour]++;
        
        // 更新日统计
        $dayOfWeek = (int)date('w', $currentTime);
        $trend['daily'][$dayOfWeek]++;
        
        $trend['last_update'] = $currentTime;
    }
    
    /**
     * 检测新兴威胁
     * 
     * @param array $eventData 事件数据
     */
    private function detectEmergingThreats(array $eventData): void
    {
        $eventType = $eventData['type'] ?? '';
        $currentTime = time();
        
        // 检查是否是新威胁类型
        if (!isset($this->threatIndicators['active_threats'][$eventType])) {
            $emergingThreat = [
                'type' => $eventType,
                'first_detected' => $currentTime,
                'severity' => $eventData['severity'] ?? 'low',
                'source' => $eventData['source'] ?? 'unknown',
                'confidence' => 0.5
            ];
            
            $this->threatIndicators['emerging_threats'][$eventType] = $emergingThreat;
            
            $this->logger->warning('检测到新兴威胁', [
                'type' => $eventType,
                'severity' => $eventData['severity']
            ]);
        }
    }
    
    /**
     * 更新系统指标
     * 
     * @param array $eventData 事件数据
     */
    private function updateSystemMetrics(array $eventData): void
    {
        $currentTime = time();
        
        // 更新安全指标
        $this->systemMetrics['security'][] = [
            'timestamp' => $currentTime,
            'event_count' => 1,
            'severity_distribution' => [
                'critical' => $eventData['severity'] === 'critical' ? 1 : 0,
                'high' => $eventData['severity'] === 'high' ? 1 : 0,
                'medium' => $eventData['severity'] === 'medium' ? 1 : 0,
                'low' => $eventData['severity'] === 'low' ? 1 : 0
            ]
        ];
        
        // 限制指标历史
        if (count($this->systemMetrics['security']) > 1000) {
            array_shift($this->systemMetrics['security']);
        }
    }
    
    /**
     * 分析安全态势
     * 
     * @return array 分析结果
     */
    private function analyzeSituation(): array
    {
        $analysis = [
            'overall_risk' => 0.0,
            'threat_level' => 'low',
            'trend' => 'stable',
            'impact_level' => 'none',
            'key_indicators' => [],
            'predictions' => [],
            'recommendations' => []
        ];
        
        // 计算整体风险
        $analysis['overall_risk'] = $this->calculateOverallRisk();
        
        // 确定威胁级别
        $analysis['threat_level'] = $this->determineThreatLevel($analysis['overall_risk']);
        
        // 分析趋势
        $analysis['trend'] = $this->analyzeTrend();
        
        // 确定影响级别
        $analysis['impact_level'] = $this->determineImpactLevel();
        
        // 识别关键指标
        $analysis['key_indicators'] = $this->identifyKeyIndicators();
        
        // 生成预测
        if ($this->config['analysis']['predictive_enabled']) {
            $analysis['predictions'] = $this->generatePredictions();
        }
        
        // 生成建议
        $analysis['recommendations'] = $this->generateRecommendations($analysis);
        
        return $analysis;
    }
    
    /**
     * 计算整体风险
     * 
     * @return float 整体风险评分
     */
    private function calculateOverallRisk(): float
    {
        $riskScore = 0.0;
        
        // 基于活跃威胁数量
        $activeThreatsCount = count($this->threatIndicators['active_threats']);
        $riskScore += min($activeThreatsCount * 0.1, 0.3);
        
        // 基于关键事件数量
        $criticalEventsCount = count($this->securityEvents['critical']);
        $riskScore += min($criticalEventsCount * 0.05, 0.2);
        
        // 基于新兴威胁数量
        $emergingThreatsCount = count($this->threatIndicators['emerging_threats']);
        $riskScore += min($emergingThreatsCount * 0.15, 0.3);
        
        // 基于最近事件频率
        $recentEvents = array_filter($this->securityEvents['recent'], function($event) {
            return time() - $event['timestamp'] < 3600; // 1小时内
        });
        
        $recentEventCount = count($recentEvents);
        $riskScore += min($recentEventCount * 0.02, 0.2);
        
        return min($riskScore, 1.0);
    }
    
    /**
     * 确定威胁级别
     * 
     * @param float $riskScore 风险评分
     * @return string 威胁级别
     */
    private function determineThreatLevel(float $riskScore): string
    {
        if ($riskScore >= 0.8) {
            return 'critical';
        } elseif ($riskScore >= 0.6) {
            return 'high';
        } elseif ($riskScore >= 0.4) {
            return 'medium';
        } else {
            return 'low';
        }
    }
    
    /**
     * 分析趋势
     * 
     * @return string 趋势
     */
    private function analyzeTrend(): string
    {
        // 获取最近的事件
        $recentEvents = array_slice($this->securityEvents['recent'], -100);
        
        if (count($recentEvents) < 10) {
            return 'stable';
        }
        
        // 按时间分组
        $hourlyCounts = [];
        foreach ($recentEvents as $event) {
            $hour = date('H', $event['timestamp']);
            $hourlyCounts[$hour] = ($hourlyCounts[$hour] ?? 0) + 1;
        }
        
        // 计算趋势
        $values = array_values($hourlyCounts);
        if (count($values) < 2) {
            return 'stable';
        }
        
        $trend = 0;
        for ($i = 1; $i < count($values); $i++) {
            $trend += $values[$i] - $values[$i - 1];
        }
        
        $avgTrend = $trend / (count($values) - 1);
        
        if ($avgTrend > 0.5) {
            return 'increasing';
        } elseif ($avgTrend < -0.5) {
            return 'decreasing';
        } else {
            return 'stable';
        }
    }
    
    /**
     * 确定影响级别
     * 
     * @return string 影响级别
     */
    private function determineImpactLevel(): string
    {
        $criticalEvents = count($this->securityEvents['critical']);
        $highSeverityEvents = 0;
        
        foreach ($this->securityEvents['recent'] as $event) {
            if ($event['severity'] === 'high') {
                $highSeverityEvents++;
            }
        }
        
        if ($criticalEvents > 0) {
            return 'critical';
        } elseif ($highSeverityEvents > 5) {
            return 'high';
        } elseif ($highSeverityEvents > 0) {
            return 'medium';
        } else {
            return 'low';
        }
    }
    
    /**
     * 识别关键指标
     * 
     * @return array 关键指标
     */
    private function identifyKeyIndicators(): array
    {
        $indicators = [];
        
        // 活跃威胁指标
        $activeThreats = count($this->threatIndicators['active_threats']);
        if ($activeThreats > 0) {
            $indicators[] = [
                'type' => 'active_threats',
                'value' => $activeThreats,
                'status' => $activeThreats > 5 ? 'critical' : ($activeThreats > 2 ? 'warning' : 'normal')
            ];
        }
        
        // 新兴威胁指标
        $emergingThreats = count($this->threatIndicators['emerging_threats']);
        if ($emergingThreats > 0) {
            $indicators[] = [
                'type' => 'emerging_threats',
                'value' => $emergingThreats,
                'status' => 'warning'
            ];
        }
        
        // 事件频率指标
        $recentEvents = array_filter($this->securityEvents['recent'], function($event) {
            return time() - $event['timestamp'] < 3600; // 1小时内
        });
        
        $eventFrequency = count($recentEvents);
        if ($eventFrequency > 10) {
            $indicators[] = [
                'type' => 'event_frequency',
                'value' => $eventFrequency,
                'status' => $eventFrequency > 50 ? 'critical' : 'warning'
            ];
        }
        
        return $indicators;
    }
    
    /**
     * 生成预测
     * 
     * @return array 预测结果
     */
    private function generatePredictions(): array
    {
        $predictions = [];
        
        // 基于历史数据预测事件趋势
        $eventTrend = $this->predictEventTrend();
        if ($eventTrend) {
            $predictions[] = $eventTrend;
        }
        
        // 预测风险变化
        $riskPrediction = $this->predictRiskChange();
        if ($riskPrediction) {
            $predictions[] = $riskPrediction;
        }
        
        // 预测威胁演变
        $threatPrediction = $this->predictThreatEvolution();
        if ($threatPrediction) {
            $predictions[] = $threatPrediction;
        }
        
        return $predictions;
    }
    
    /**
     * 预测事件趋势
     * 
     * @return array|null 预测结果
     */
    private function predictEventTrend(): ?array
    {
        if (count($this->securityEvents['recent']) < 20) {
            return null;
        }
        
        // 简化的趋势预测
        $recentEvents = array_slice($this->securityEvents['recent'], -20);
        $eventCounts = [];
        
        foreach ($recentEvents as $event) {
            $hour = date('H', $event['timestamp']);
            $eventCounts[$hour] = ($eventCounts[$hour] ?? 0) + 1;
        }
        
        $avgEventsPerHour = array_sum($eventCounts) / count($eventCounts);
        
        return [
            'type' => 'event_trend',
            'prediction' => $avgEventsPerHour > 5 ? 'increasing' : 'stable',
            'confidence' => 0.7,
            'timeframe' => 'next_24_hours'
        ];
    }
    
    /**
     * 预测风险变化
     * 
     * @return array|null 预测结果
     */
    private function predictRiskChange(): ?array
    {
        $currentRisk = $this->calculateOverallRisk();
        
        // 基于趋势预测风险变化
        $trend = $this->analyzeTrend();
        
        $predictedRisk = $currentRisk;
        if ($trend === 'increasing') {
            $predictedRisk = min($currentRisk + 0.1, 1.0);
        } elseif ($trend === 'decreasing') {
            $predictedRisk = max($currentRisk - 0.1, 0.0);
        }
        
        return [
            'type' => 'risk_change',
            'current_risk' => $currentRisk,
            'predicted_risk' => $predictedRisk,
            'change' => $predictedRisk - $currentRisk,
            'confidence' => 0.6,
            'timeframe' => 'next_6_hours'
        ];
    }
    
    /**
     * 预测威胁演变
     * 
     * @return array|null 预测结果
     */
    private function predictThreatEvolution(): ?array
    {
        $emergingThreats = count($this->threatIndicators['emerging_threats']);
        
        if ($emergingThreats === 0) {
            return null;
        }
        
        return [
            'type' => 'threat_evolution',
            'emerging_threats' => $emergingThreats,
            'prediction' => $emergingThreats > 3 ? 'threat_escalation' : 'stable',
            'confidence' => 0.5,
            'timeframe' => 'next_12_hours'
        ];
    }
    
    /**
     * 生成建议
     * 
     * @param array $analysis 分析结果
     * @return array 建议列表
     */
    private function generateRecommendations(array $analysis): array
    {
        $recommendations = [];
        
        // 基于威胁级别生成建议
        if ($analysis['threat_level'] === 'critical') {
            $recommendations[] = [
                'priority' => 'immediate',
                'action' => 'activate_emergency_response',
                'description' => '立即激活紧急响应程序'
            ];
            $recommendations[] = [
                'priority' => 'high',
                'action' => 'isolate_affected_systems',
                'description' => '隔离受影响的系统'
            ];
        }
        
        // 基于活跃威胁生成建议
        $activeThreats = count($this->threatIndicators['active_threats']);
        if ($activeThreats > 5) {
            $recommendations[] = [
                'priority' => 'high',
                'action' => 'increase_monitoring',
                'description' => '增加监控频率和深度'
            ];
        }
        
        // 基于新兴威胁生成建议
        $emergingThreats = count($this->threatIndicators['emerging_threats']);
        if ($emergingThreats > 0) {
            $recommendations[] = [
                'priority' => 'medium',
                'action' => 'investigate_new_threats',
                'description' => '调查新兴威胁模式'
            ];
        }
        
        // 基于趋势生成建议
        if ($analysis['trend'] === 'increasing') {
            $recommendations[] = [
                'priority' => 'medium',
                'action' => 'review_security_policies',
                'description' => '审查和更新安全策略'
            ];
        }
        
        return $recommendations;
    }
    
    /**
     * 更新风险评估
     * 
     * @param array $eventData 事件数据
     * @param array $situationAnalysis 态势分析
     * @return float 风险变化
     */
    private function updateRiskAssessment(array $eventData, array $situationAnalysis): float
    {
        $previousRisk = $this->riskAssessment['current_risk'];
        $newRisk = $situationAnalysis['overall_risk'];
        
        $this->riskAssessment['current_risk'] = $newRisk;
        $this->riskAssessment['risk_history'][] = [
            'timestamp' => time(),
            'risk_level' => $newRisk,
            'threat_level' => $situationAnalysis['threat_level'],
            'trend' => $situationAnalysis['trend']
        ];
        
        // 限制历史记录
        if (count($this->riskAssessment['risk_history']) > 1000) {
            array_shift($this->riskAssessment['risk_history']);
        }
        
        return $newRisk - $previousRisk;
    }
    
    /**
     * 检查行动触发器
     * 
     * @param array $situationAnalysis 态势分析
     * @param float $riskChange 风险变化
     * @return array 触发的行动
     */
    private function checkActionTriggers(array $situationAnalysis, float $riskChange): array
    {
        $actions = [];
        
        // 检查风险阈值
        if ($situationAnalysis['overall_risk'] > 0.8) {
            $actions[] = 'activate_emergency_response';
        }
        
        // 检查风险变化
        if ($riskChange > 0.2) {
            $actions[] = 'increase_monitoring';
        }
        
        // 检查威胁级别
        if ($situationAnalysis['threat_level'] === 'critical') {
            $actions[] = 'notify_stakeholders';
        }
        
        // 检查新兴威胁
        if (count($this->threatIndicators['emerging_threats']) > 3) {
            $actions[] = 'threat_intelligence_update';
        }
        
        return $actions;
    }
    
    /**
     * 获取态势报告
     * 
     * @return array 态势报告
     */
    public function getSituationReport(): array
    {
        $this->performCleanup();
        
        $situationAnalysis = $this->analyzeSituation();
        
        return [
            'timestamp' => time(),
            'overall_risk' => $situationAnalysis['overall_risk'],
            'threat_level' => $situationAnalysis['threat_level'],
            'trend' => $situationAnalysis['trend'],
            'impact_level' => $situationAnalysis['impact_level'],
            'key_indicators' => $situationAnalysis['key_indicators'],
            'predictions' => $situationAnalysis['predictions'],
            'recommendations' => $situationAnalysis['recommendations'],
            'statistics' => [
                'total_events' => count($this->securityEvents['recent']),
                'critical_events' => count($this->securityEvents['critical']),
                'active_threats' => count($this->threatIndicators['active_threats']),
                'emerging_threats' => count($this->threatIndicators['emerging_threats'])
            ]
        ];
    }
    
    /**
     * 获取威胁情报摘要
     * 
     * @return array 威胁情报摘要
     */
    public function getThreatIntelligenceSummary(): array
    {
        return [
            'internal_threats' => count($this->threatIntelligence['internal']['threats']),
            'external_threats' => count($this->threatIntelligence['external']['threats']),
            'community_threats' => count($this->threatIntelligence['community']['shared_threats']),
            'correlated_threats' => count($this->threatIntelligence['correlated']),
            'last_update' => $this->threatIntelligence['last_update']
        ];
    }
    
    /**
     * 执行清理
     */
    private function performCleanup(): void
    {
        $currentTime = time();
        if ($currentTime - $this->lastAnalysis < $this->analysisInterval) {
            return;
        }
        
        // 清理过期事件
        $retentionTime = $this->config['monitoring']['data_retention'];
        
        $this->securityEvents['recent'] = array_filter(
            $this->securityEvents['recent'],
            function($event) use ($currentTime, $retentionTime) {
                return $currentTime - $event['timestamp'] < $retentionTime;
            }
        );
        
        // 清理过期的威胁指标
        foreach ($this->threatIndicators['active_threats'] as $type => $threat) {
            if ($currentTime - $threat['last_seen'] > 86400) { // 24小时无活动
                unset($this->threatIndicators['active_threats'][$type]);
            }
        }
        
        $this->lastAnalysis = $currentTime;
    }
}
