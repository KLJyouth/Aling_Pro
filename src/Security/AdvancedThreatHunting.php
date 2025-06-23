<?php

namespace AlingAi\Security;

use Psr\Log\LoggerInterface;
use AlingAi\Core\Container;
use AlingAi\AI\MachineLearning\DeepLearningModel;
use AlingAi\AI\MachineLearning\AnomalyDetector;

/**
 * 高级威胁狩猎系统
 * 
 * 主动搜寻和识别潜在的安全威胁，包括APT和零日攻击
 * 增强安全性：主动威胁发现、高级威胁情报和智能分析
 * 优化性能：分布式扫描和优先级处理
 */
class AdvancedThreatHunting
{
    private $logger;
    private $container;
    private $config = [];
    private $deepLearningModel;
    private $anomalyDetector;
    private $threatIntelligence;
    private $huntingRules = [];
    private $threatIndicators = [];
    private $huntingResults = [];
    private $lastHunt = 0;
    private $huntInterval = 3600; // 1小时执行一次

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
        $this->loadHuntingRules();
    }
    
    /**
     * 加载配置
     * 
     * @return array
     */
    private function loadConfiguration(): array
    {
        return [
            'hunting_modes' => [
                'scheduled' => env('THREAT_HUNT_SCHEDULED', true),
                'event_triggered' => env('THREAT_HUNT_EVENT_TRIGGERED', true),
                'continuous' => env('THREAT_HUNT_CONTINUOUS', false)
            ],
            'scan_areas' => [
                'network_traffic' => env('HUNT_NETWORK_TRAFFIC', true),
                'system_logs' => env('HUNT_SYSTEM_LOGS', true),
                'application_logs' => env('HUNT_APP_LOGS', true),
                'user_behavior' => env('HUNT_USER_BEHAVIOR', true),
                'file_system' => env('HUNT_FILE_SYSTEM', true),
                'memory' => env('HUNT_MEMORY', false)
            ],
            'detection_methods' => [
                'behavior_analysis' => env('HUNT_BEHAVIOR_ANALYSIS', true),
                'anomaly_detection' => env('HUNT_ANOMALY_DETECTION', true),
                'pattern_matching' => env('HUNT_PATTERN_MATCHING', true),
                'threat_intelligence' => env('HUNT_THREAT_INTEL', true),
                'machine_learning' => env('HUNT_ML', true)
            ],
            'performance' => [
                'max_threads' => env('HUNT_MAX_THREADS', 4),
                'resource_limit' => env('HUNT_RESOURCE_LIMIT', 0.5), // 最大使用50%的系统资源
                'priority_scheduling' => env('HUNT_PRIORITY_SCHEDULING', true),
                'batch_size' => env('HUNT_BATCH_SIZE', 1000)
            ],
            'alert_thresholds' => [
                'low' => env('HUNT_THRESHOLD_LOW', 0.3),
                'medium' => env('HUNT_THRESHOLD_MEDIUM', 0.6),
                'high' => env('HUNT_THRESHOLD_HIGH', 0.8)
            ],
            'retention' => [
                'results_days' => env('HUNT_RESULTS_RETENTION', 30),
                'indicators_days' => env('HUNT_INDICATORS_RETENTION', 90)
            ]
        ];
    }
    
    /**
     * 初始化组件
     */
    private function initializeComponents(): void
    {
        // 初始化深度学习模型
        $this->deepLearningModel = new DeepLearningModel([
            'model_type' => 'threat_detection',
            'input_shape' => [1, 1024],
            'layers' => [
                ['type' => 'dense', 'units' => 512, 'activation' => 'relu'],
                ['type' => 'dropout', 'rate' => 0.3],
                ['type' => 'dense', 'units' => 256, 'activation' => 'relu'],
                ['type' => 'dropout', 'rate' => 0.3],
                ['type' => 'dense', 'units' => 128, 'activation' => 'relu'],
                ['type' => 'dense', 'units' => 8, 'activation' => 'softmax']
            ],
            'learning_rate' => 0.001
        ]);
        
        // 初始化异常检测器
        $this->anomalyDetector = new AnomalyDetector([
            'algorithm' => 'isolation_forest',
            'contamination' => 0.01,
            'n_estimators' => 100
        ]);
        
        // 获取威胁情报服务
        $this->threatIntelligence = $this->container->get(GlobalThreatIntelligence::class);
        
        // 初始化狩猎结果
        $this->huntingResults = [
            'recent_hunts' => [],
            'detected_threats' => [],
            'potential_threats' => [],
            'false_positives' => []
        ];
    }
    
    /**
     * 加载狩猎规则
     */
    private function loadHuntingRules(): void
    {
        $this->huntingRules = [
            'network' => [
                'unusual_connections' => [
                    'description' => '检测异常网络连接',
                    'indicators' => [
                        'connections_to_unusual_ports',
                        'connections_to_unusual_destinations',
                        'high_volume_data_transfer',
                        'encrypted_traffic_to_uncommon_destinations'
                    ],
                    'severity' => 'medium',
                    'ttl' => 86400 // 24小时
                ],
                'command_and_control' => [
                    'description' => '检测C2通信',
                    'indicators' => [
                        'beaconing_activity',
                        'dns_tunneling',
                        'domain_generation_algorithms',
                        'tor_exit_nodes'
                    ],
                    'severity' => 'high',
                    'ttl' => 172800 // 48小时
                ]
            ],
            'system' => [
                'persistence_mechanisms' => [
                    'description' => '检测持久化机制',
                    'indicators' => [
                        'registry_modifications',
                        'startup_folder_changes',
                        'scheduled_task_creation',
                        'service_installation'
                    ],
                    'severity' => 'high',
                    'ttl' => 172800 // 48小时
                ],
                'privilege_escalation' => [
                    'description' => '检测权限提升',
                    'indicators' => [
                        'sudo_usage',
                        'setuid_binaries',
                        'kernel_exploits',
                        'admin_group_changes'
                    ],
                    'severity' => 'high',
                    'ttl' => 86400 // 24小时
                ]
            ],
            'user' => [
                'account_manipulation' => [
                    'description' => '检测账户操作',
                    'indicators' => [
                        'password_changes',
                        'group_membership_changes',
                        'new_account_creation',
                        'account_lockouts'
                    ],
                    'severity' => 'medium',
                    'ttl' => 86400 // 24小时
                ],
                'unusual_behavior' => [
                    'description' => '检测异常用户行为',
                    'indicators' => [
                        'off_hours_activity',
                        'unusual_access_patterns',
                        'multiple_failed_logins',
                        'access_to_sensitive_data'
                    ],
                    'severity' => 'medium',
                    'ttl' => 43200 // 12小时
                ]
            ],
            'application' => [
                'web_attacks' => [
                    'description' => '检测Web应用攻击',
                    'indicators' => [
                        'sql_injection_attempts',
                        'xss_attempts',
                        'file_inclusion_attempts',
                        'command_injection_attempts'
                    ],
                    'severity' => 'high',
                    'ttl' => 43200 // 12小时
                ],
                'api_abuse' => [
                    'description' => '检测API滥用',
                    'indicators' => [
                        'high_rate_of_requests',
                        'unusual_api_calls',
                        'sequential_resource_access',
                        'parameter_tampering'
                    ],
                    'severity' => 'medium',
                    'ttl' => 43200 // 12小时
                ]
            ],
            'data' => [
                'data_exfiltration' => [
                    'description' => '检测数据泄露',
                    'indicators' => [
                        'large_file_transfers',
                        'unusual_email_attachments',
                        'sensitive_data_access',
                        'database_dumps'
                    ],
                    'severity' => 'high',
                    'ttl' => 86400 // 24小时
                ],
                'data_manipulation' => [
                    'description' => '检测数据篡改',
                    'indicators' => [
                        'unusual_database_modifications',
                        'configuration_file_changes',
                        'log_tampering',
                        'timestamp_manipulation'
                    ],
                    'severity' => 'high',
                    'ttl' => 86400 // 24小时
                ]
            ]
        ];
    }
    
    /**
     * 执行威胁狩猎
     * 
     * @param array $options 狩猎选项
     * @return array 狩猎结果
     */
    public function hunt(array $options = []): array
    {
        $startTime = microtime(true);
        
        $this->logger->info('开始高级威胁狩猎', [
            'mode' => $options['mode'] ?? 'scheduled',
            'scan_areas' => $options['scan_areas'] ?? array_keys(array_filter($this->config['scan_areas']))
        ]);
        
        // 合并选项与默认配置
        $huntOptions = array_merge([
            'mode' => 'scheduled',
            'scan_areas' => array_keys(array_filter($this->config['scan_areas'])),
            'priority' => 'normal',
            'depth' => 'standard',
            'max_results' => 100
        ], $options);
        
        // 创建狩猎会话
        $huntSession = [
            'id' => uniqid('hunt_', true),
            'start_time' => time(),
            'end_time' => null,
            'mode' => $huntOptions['mode'],
            'scan_areas' => $huntOptions['scan_areas'],
            'priority' => $huntOptions['priority'],
            'depth' => $huntOptions['depth'],
            'status' => 'running',
            'results' => [
                'detected_threats' => [],
                'potential_threats' => [],
                'false_positives' => [],
                'indicators_found' => 0
            ]
        ];
        
        // 添加到最近的狩猎结果
        $this->huntingResults['recent_hunts'][] = $huntSession;
        if (count($this->huntingResults['recent_hunts']) > 10) {
            array_shift($this->huntingResults['recent_hunts']);
        }
        
        // 执行各区域的狩猎
        foreach ($huntOptions['scan_areas'] as $area) {
            if (!isset($this->config['scan_areas'][$area]) || !$this->config['scan_areas'][$area]) {
                continue;
            }
            
            $areaResults = $this->huntArea($area, $huntOptions);
            
            // 合并结果
            $huntSession['results']['detected_threats'] = array_merge(
                $huntSession['results']['detected_threats'],
                $areaResults['detected_threats']
            );
            
            $huntSession['results']['potential_threats'] = array_merge(
                $huntSession['results']['potential_threats'],
                $areaResults['potential_threats']
            );
            
            $huntSession['results']['false_positives'] = array_merge(
                $huntSession['results']['false_positives'],
                $areaResults['false_positives']
            );
            
            $huntSession['results']['indicators_found'] += $areaResults['indicators_found'];
        }
        
        // 关联分析
        $correlationResults = $this->performCorrelationAnalysis($huntSession['results']);
        $huntSession['results']['correlated_threats'] = $correlationResults['correlated_threats'];
        $huntSession['results']['threat_campaigns'] = $correlationResults['threat_campaigns'];
        
        // 更新全局威胁指标
        $this->updateThreatIndicators($huntSession['results']);
        
        // 完成狩猎会话
        $huntSession['end_time'] = time();
        $huntSession['duration'] = $huntSession['end_time'] - $huntSession['start_time'];
        $huntSession['status'] = 'completed';
        
        // 更新最近的狩猎结果
        foreach ($this->huntingResults['recent_hunts'] as &$hunt) {
            if ($hunt['id'] === $huntSession['id']) {
                $hunt = $huntSession;
                break;
            }
        }
        
        // 更新最后狩猎时间
        $this->lastHunt = time();
        
        $this->logger->info('完成高级威胁狩猎', [
            'hunt_id' => $huntSession['id'],
            'duration' => $huntSession['duration'],
            'detected_threats' => count($huntSession['results']['detected_threats']),
            'potential_threats' => count($huntSession['results']['potential_threats']),
            'indicators_found' => $huntSession['results']['indicators_found']
        ]);
        
        return [
            'hunt_id' => $huntSession['id'],
            'duration' => microtime(true) - $startTime,
            'detected_threats' => $huntSession['results']['detected_threats'],
            'potential_threats' => $huntSession['results']['potential_threats'],
            'correlated_threats' => $huntSession['results']['correlated_threats'],
            'threat_campaigns' => $huntSession['results']['threat_campaigns'],
            'indicators_found' => $huntSession['results']['indicators_found']
        ];
    }
    
    /**
     * 狩猎特定区域
     * 
     * @param string $area 区域
     * @param array $options 选项
     * @return array 结果
     */
    private function huntArea(string $area, array $options): array
    {
        $this->logger->debug('开始狩猎区域', [
            'area' => $area,
            'depth' => $options['depth']
        ]);
        
        $results = [
            'detected_threats' => [],
            'potential_threats' => [],
            'false_positives' => [],
            'indicators_found' => 0
        ];
        
        switch ($area) {
            case 'network_traffic':
                $results = $this->huntNetworkTraffic($options);
                break;
                
            case 'system_logs':
                $results = $this->huntSystemLogs($options);
                break;
                
            case 'application_logs':
                $results = $this->huntApplicationLogs($options);
                break;
                
            case 'user_behavior':
                $results = $this->huntUserBehavior($options);
                break;
                
            case 'file_system':
                $results = $this->huntFileSystem($options);
                break;
                
            case 'memory':
                $results = $this->huntMemory($options);
                break;
        }
        
        return $results;
    }
    
    /**
     * 狩猎网络流量
     * 
     * @param array $options 选项
     * @return array 结果
     */
    private function huntNetworkTraffic(array $options): array
    {
        $results = [
            'detected_threats' => [],
            'potential_threats' => [],
            'false_positives' => [],
            'indicators_found' => 0
        ];
        
        // 获取网络流量数据
        $networkData = $this->getNetworkData($options);
        
        // 应用狩猎规则
        foreach ($this->huntingRules['network'] as $ruleId => $rule) {
            $ruleResults = $this->applyNetworkRule($networkData, $ruleId, $rule, $options);
            
            $results['detected_threats'] = array_merge($results['detected_threats'], $ruleResults['detected_threats']);
            $results['potential_threats'] = array_merge($results['potential_threats'], $ruleResults['potential_threats']);
            $results['false_positives'] = array_merge($results['false_positives'], $ruleResults['false_positives']);
            $results['indicators_found'] += $ruleResults['indicators_found'];
        }
        
        // 应用机器学习检测
        if ($this->config['detection_methods']['machine_learning']) {
            $mlResults = $this->applyMachineLearningToNetwork($networkData, $options);
            
            $results['detected_threats'] = array_merge($results['detected_threats'], $mlResults['detected_threats']);
            $results['potential_threats'] = array_merge($results['potential_threats'], $mlResults['potential_threats']);
            $results['indicators_found'] += $mlResults['indicators_found'];
        }
        
        // 应用异常检测
        if ($this->config['detection_methods']['anomaly_detection']) {
            $anomalyResults = $this->applyAnomalyDetectionToNetwork($networkData, $options);
            
            $results['detected_threats'] = array_merge($results['detected_threats'], $anomalyResults['detected_threats']);
            $results['potential_threats'] = array_merge($results['potential_threats'], $anomalyResults['potential_threats']);
            $results['indicators_found'] += $anomalyResults['indicators_found'];
        }
        
        return $results;
    }
    
    /**
     * 获取网络数据
     * 
     * @param array $options 选项
     * @return array 网络数据
     */
    private function getNetworkData(array $options): array
    {
        try {
            // 实际实现：从网络监控系统获取数据
            $this->logger->info('开始获取网络数据', ['options' => $options]);
            
            $networkData = [
                'connections' => [],
                'traffic' => [],
                'dns_queries' => [],
                'http_requests' => [],
                'metadata' => [
                    'timestamp' => time(),
                    'data_source' => 'network_monitor',
                    'collection_duration' => $options['duration'] ?? 300
                ]
            ];
            
            // 获取网络连接数据
            $connections = $this->collectNetworkConnections($options);
            $networkData['connections'] = $connections;
            
            // 获取流量数据
            $traffic = $this->collectNetworkTraffic($options);
            $networkData['traffic'] = $traffic;
            
            // 获取DNS查询数据
            $dnsQueries = $this->collectDNSQueries($options);
            $networkData['dns_queries'] = $dnsQueries;
            
            // 获取HTTP请求数据
            $httpRequests = $this->collectHTTPRequests($options);
            $networkData['http_requests'] = $httpRequests;
            
            $this->logger->info('网络数据获取完成', [
                'connections_count' => count($connections),
                'traffic_records' => count($traffic),
                'dns_queries_count' => count($dnsQueries),
                'http_requests_count' => count($httpRequests)
            ]);
            
            return $networkData;
            
        } catch (\Exception $e) {
            $this->logger->error('网络数据获取失败', ['error' => $e->getMessage()]);
            
            return [
                'connections' => [],
                'traffic' => [],
                'dns_queries' => [],
                'http_requests' => [],
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 应用网络规则
     * 
     * @param array $networkData 网络数据
     * @param string $ruleId 规则ID
     * @param array $rule 规则
     * @param array $options 选项
     * @return array 结果
     */
    private function applyNetworkRule(array $networkData, string $ruleId, array $rule, array $options): array
    {
        try {
            $this->logger->info('开始应用网络规则', [
                'rule_id' => $ruleId,
                'rule_type' => $rule['type'] ?? 'unknown'
            ]);
            
            $results = [
                'detected_threats' => [],
                'potential_threats' => [],
                'false_positives' => [],
                'indicators_found' => 0,
                'rule_id' => $ruleId,
                'rule_type' => $rule['type'] ?? 'unknown'
            ];
            
            // 根据规则类型应用不同的逻辑
            switch ($rule['type'] ?? 'unknown') {
                case 'pattern_match':
                    $ruleResults = $this->applyPatternMatchRule($networkData, $rule, $options);
                    break;
                    
                case 'threshold':
                    $ruleResults = $this->applyThresholdRule($networkData, $rule, $options);
                    break;
                    
                case 'behavioral':
                    $ruleResults = $this->applyBehavioralRule($networkData, $rule, $options);
                    break;
                    
                case 'signature':
                    $ruleResults = $this->applySignatureRule($networkData, $rule, $options);
                    break;
                    
                default:
                    $ruleResults = $this->applyGenericRule($networkData, $rule, $options);
            }
            
            // 合并结果
            $results['detected_threats'] = $ruleResults['detected_threats'] ?? [];
            $results['potential_threats'] = $ruleResults['potential_threats'] ?? [];
            $results['false_positives'] = $ruleResults['false_positives'] ?? [];
            $results['indicators_found'] = count($results['detected_threats']) + count($results['potential_threats']);
            
            $this->logger->info('网络规则应用完成', [
                'rule_id' => $ruleId,
                'indicators_found' => $results['indicators_found']
            ]);
            
            return $results;
            
        } catch (\Exception $e) {
            $this->logger->error('网络规则应用失败', [
                'rule_id' => $ruleId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'detected_threats' => [],
                'potential_threats' => [],
                'false_positives' => [],
                'indicators_found' => 0,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 应用机器学习到网络数据
     * 
     * @param array $networkData 网络数据
     * @param array $options 选项
     * @return array 结果
     */
    private function applyMachineLearningToNetwork(array $networkData, array $options): array
    {
        try {
            $this->logger->info('开始应用机器学习到网络数据', ['options' => $options]);
            
            $results = [
                'detected_threats' => [],
                'potential_threats' => [],
                'indicators_found' => 0,
                'model_performance' => []
            ];
            
            // 特征提取
            $features = $this->extractNetworkFeatures($networkData);
            
            // 应用不同的机器学习模型
            $models = [
                'anomaly_detection' => $this->applyAnomalyDetectionModel($features, $options),
                'classification' => $this->applyClassificationModel($features, $options),
                'clustering' => $this->applyClusteringModel($features, $options),
                'regression' => $this->applyRegressionModel($features, $options)
            ];
            
            // 合并模型结果
            foreach ($models as $modelType => $modelResults) {
                $results['detected_threats'] = array_merge(
                    $results['detected_threats'],
                    $modelResults['detected_threats'] ?? []
                );
                $results['potential_threats'] = array_merge(
                    $results['potential_threats'],
                    $modelResults['potential_threats'] ?? []
                );
                $results['model_performance'][$modelType] = $modelResults['performance'] ?? [];
            }
            
            $results['indicators_found'] = count($results['detected_threats']) + count($results['potential_threats']);
            
            $this->logger->info('机器学习应用完成', [
                'indicators_found' => $results['indicators_found'],
                'models_applied' => array_keys($models)
            ]);
            
            return $results;
            
        } catch (\Exception $e) {
            $this->logger->error('机器学习应用失败', ['error' => $e->getMessage()]);
            
            return [
                'detected_threats' => [],
                'potential_threats' => [],
                'indicators_found' => 0,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 应用异常检测到网络数据
     * 
     * @param array $networkData 网络数据
     * @param array $options 选项
     * @return array 结果
     */
    private function applyAnomalyDetectionToNetwork(array $networkData, array $options): array
    {
        try {
            $this->logger->info('开始应用异常检测到网络数据', ['options' => $options]);
            
            $results = [
                'detected_threats' => [],
                'potential_threats' => [],
                'indicators_found' => 0,
                'anomaly_scores' => []
            ];
            
            // 流量异常检测
            $trafficAnomalies = $this->detectTrafficAnomalies($networkData['traffic'], $options);
            $results['detected_threats'] = array_merge($results['detected_threats'], $trafficAnomalies['threats']);
            $results['anomaly_scores']['traffic'] = $trafficAnomalies['scores'];
            
            // 连接异常检测
            $connectionAnomalies = $this->detectConnectionAnomalies($networkData['connections'], $options);
            $results['detected_threats'] = array_merge($results['detected_threats'], $connectionAnomalies['threats']);
            $results['anomaly_scores']['connections'] = $connectionAnomalies['scores'];
            
            // DNS异常检测
            $dnsAnomalies = $this->detectDNSAnomalies($networkData['dns_queries'], $options);
            $results['detected_threats'] = array_merge($results['detected_threats'], $dnsAnomalies['threats']);
            $results['anomaly_scores']['dns'] = $dnsAnomalies['scores'];
            
            // HTTP异常检测
            $httpAnomalies = $this->detectHTTPAnomalies($networkData['http_requests'], $options);
            $results['detected_threats'] = array_merge($results['detected_threats'], $httpAnomalies['threats']);
            $results['anomaly_scores']['http'] = $httpAnomalies['scores'];
            
            $results['indicators_found'] = count($results['detected_threats']);
            
            $this->logger->info('异常检测完成', [
                'indicators_found' => $results['indicators_found'],
                'anomaly_types' => array_keys($results['anomaly_scores'])
            ]);
            
            return $results;
            
        } catch (\Exception $e) {
            $this->logger->error('异常检测失败', ['error' => $e->getMessage()]);
            
            return [
                'detected_threats' => [],
                'potential_threats' => [],
                'indicators_found' => 0,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 执行关联分析
     * 
     * @param array $results 结果
     * @return array 关联分析结果
     */
    private function performCorrelationAnalysis(array $results): array
    {
        try {
            $this->logger->info('开始执行关联分析', ['result_count' => count($results)]);
            
            $correlationResults = [
                'correlated_threats' => [],
                'threat_campaigns' => [],
                'attack_patterns' => [],
                'temporal_correlations' => [],
                'spatial_correlations' => []
            ];
            
            // 时间关联分析
            $temporalCorrelations = $this->analyzeTemporalCorrelations($results);
            $correlationResults['temporal_correlations'] = $temporalCorrelations;
            
            // 空间关联分析
            $spatialCorrelations = $this->analyzeSpatialCorrelations($results);
            $correlationResults['spatial_correlations'] = $spatialCorrelations;
            
            // 威胁关联分析
            $threatCorrelations = $this->analyzeThreatCorrelations($results);
            $correlationResults['correlated_threats'] = $threatCorrelations;
            
            // 攻击模式识别
            $attackPatterns = $this->identifyAttackPatterns($results);
            $correlationResults['attack_patterns'] = $attackPatterns;
            
            // 威胁活动识别
            $threatCampaigns = $this->identifyThreatCampaigns($results);
            $correlationResults['threat_campaigns'] = $threatCampaigns;
            
            $this->logger->info('关联分析完成', [
                'correlated_threats' => count($threatCorrelations),
                'attack_patterns' => count($attackPatterns),
                'threat_campaigns' => count($threatCampaigns)
            ]);
            
            return $correlationResults;
            
        } catch (\Exception $e) {
            $this->logger->error('关联分析失败', ['error' => $e->getMessage()]);
            
            return [
                'correlated_threats' => [],
                'threat_campaigns' => [],
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 更新威胁指标
     * 
     * @param array $results 结果
     */
    private function updateThreatIndicators(array $results): void
    {
        // 在实际实现中，这里会更新全局威胁指标
        try {
            // 实际实现：更新安全视图
            $this->updateSecurityView($data);
            
            $this->logger->info('安全视图更新完成');
        } catch (\Exception $e) {
            $this->logger->error('安全视图更新失败', ['error' => $e->getMessage()]);
        }
    }
    
    /**
     * 获取威胁指标
     * 
     * @param string $type 类型
     * @return array 威胁指标
     */
    public function getThreatIndicators(string $type = 'all'): array
    {
        if ($type === 'all') {
            return $this->threatIndicators;
        }
        
        return $this->threatIndicators[$type] ?? [];
    }
    
    /**
     * 获取狩猎结果
     * 
     * @param string $huntId 狩猎ID
     * @return array|null 狩猎结果
     */
    public function getHuntResults(string $huntId = null): ?array
    {
        if ($huntId === null) {
            return $this->huntingResults;
        }
        
        foreach ($this->huntingResults['recent_hunts'] as $hunt) {
            if ($hunt['id'] === $huntId) {
                return $hunt;
            }
        }
        
        return null;
    }
    
    /**
     * 获取状态
     * 
     * @return array 状态
     */
    public function getStatus(): array
    {
        return [
            'last_hunt' => $this->lastHunt,
            'hunt_interval' => $this->huntInterval,
            'recent_hunts' => count($this->huntingResults['recent_hunts']),
            'detected_threats' => count($this->huntingResults['detected_threats']),
            'potential_threats' => count($this->huntingResults['potential_threats']),
            'false_positives' => count($this->huntingResults['false_positives'])
        ];
    }
    
    /**
     * 清理过期数据
     */
    private function cleanupExpiredData(): void
    {
        $now = time();
        
        // 清理过期的威胁指标
        foreach ($this->threatIndicators as $type => &$indicators) {
            foreach ($indicators as $id => $indicator) {
                if (isset($indicator['expiry']) && $indicator['expiry'] < $now) {
                    unset($indicators[$id]);
                }
            }
        }
        
        // 清理过期的狩猎结果
        $resultsRetention = $this->config['retention']['results_days'] * 86400;
        foreach ($this->huntingResults['recent_hunts'] as $key => $hunt) {
            if (($now - $hunt['end_time']) > $resultsRetention) {
                unset($this->huntingResults['recent_hunts'][$key]);
            }
        }
        
        // 重新索引数组
        $this->huntingResults['recent_hunts'] = array_values($this->huntingResults['recent_hunts']);
    }
} 