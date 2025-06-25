<?php

declare(strict_types=1];

namespace AlingAi\Security;

use AlingAi\Services\DatabaseServiceInterface;
use AlingAi\Services\DeepSeekAIService;
use Psr\Log\LoggerInterface;

/**
 * 全球安全情报系统
 * 
 * 三完编译 - 核心组件
 * 功能特�?
 * - 全球威胁情报收集
 * - 3D威胁可视化数据生�?
 * - 智能威胁模式分析
 * - 地理位置威胁映射
 * - 实时威胁态势感知
 * - AI驱动的威胁预�?
 */
/**
 * GlobalThreatIntelligence �?
 *
 * @package AlingAi\Security
 */
class GlobalThreatIntelligence
{
    private DatabaseServiceInterface $database;
    private DeepSeekAIService $aiService;
    private LoggerInterface $logger;
    private array $config;
    private array $threatSources;
    private array $geoThreatMap;

    // 威胁类型常量
    public const THREAT_TYPE_MALWARE = 'malware';
    public const THREAT_TYPE_PHISHING = 'phishing';
    public const THREAT_TYPE_DDoS = 'ddos';
    public const THREAT_TYPE_INJECTION = 'injection';
    public const THREAT_TYPE_BRUTEFORCE = 'bruteforce';
    public const THREAT_TYPE_BOTNET = 'botnet';
    public const THREAT_TYPE_APT = 'apt';

    // 威胁强度级别
    public const INTENSITY_LOW = 1;
    public const INTENSITY_MEDIUM = 2;
    public const INTENSITY_HIGH = 3;
    public const INTENSITY_CRITICAL = 4;
    public const INTENSITY_EXTREME = 5;

    /**


     * __construct 方法


     *


     * @param DatabaseServiceInterface $database


     * @param DeepSeekAIService $aiService


     * @param LoggerInterface $logger


     * @param array $config


     * @return void


     */


    public function __construct(
        DatabaseServiceInterface $database,
        DeepSeekAIService $aiService,
        LoggerInterface $logger,
        array $config = []
    ) {
        $this->database = $database;
        $this->aiService = $aiService;
        $this->logger = $logger;
        $this->config = array_merge($this->getDefaultConfig(), $config];
        $this->initializeThreatSources(];
        $this->geoThreatMap = [];
    }

    /**
     * 获取全球威胁态势3D可视化数�?
     */
    /**

     * getGlobal3DThreatVisualization 方法

     *

     * @return void

     */

    public function getGlobal3DThreatVisualization(): array
    {
        try {
            $this->logger->info('生成全球3D威胁可视化数�?];

            // 收集威胁数据
            $threatData = $this->collectGlobalThreatData(];
            
            // 生成地理威胁映射
            $geoThreatMap = $this->generateGeoThreatMap($threatData];
            
            // 分析威胁趋势
            $threatTrends = $this->analyzeThreatTrends($threatData];
            
            // 生成3D可视化坐标系
            $visualization3D = $this->generate3DVisualizationData($geoThreatMap, $threatTrends];
            
            // AI预测威胁走向
            $aiPredictions = $this->predictThreatEvolution($threatData];
            
            return [
                'timestamp' => time(),
                'global_threat_level' => $this->calculateGlobalThreatLevel($threatData],
                'threat_data' => $threatData,
                'geo_threat_map' => $geoThreatMap,
                'threat_trends' => $threatTrends,
                'visualization_3d' => $visualization3D,
                'ai_predictions' => $aiPredictions,
                'active_threats_count' => count($threatData],
                'high_risk_regions' => $this->identifyHighRiskRegions($geoThreatMap],
                'threat_vectors' => $this->analyzeThreatVectors($threatData],
                'defense_recommendations' => $this->generateDefenseRecommendations($threatData)
            ];
        } catch (\Exception $e) {
            $this->logger->error('生成3D威胁可视化失�? ' . $e->getMessage()];
            return [
                'error' => '威胁数据生成失败',
                'timestamp' => time(),
                'global_threat_level' => 'unknown'
            ];
        }
    }

    /**
     * 收集全球威胁数据
     */
    /**

     * collectGlobalThreatData 方法

     *

     * @return void

     */

    private function collectGlobalThreatData(): array
    {
        $threats = [];
        
        // 从安全日志收集威�?
        $securityLogs = $this->database->select('security_logs', [
            'ip', 'threat_level', 'threat_type', 'country', 'risk_score', 'created_at'
        ],  [
            'created_at' => ['>=', date('Y-m-d H:i:s', strtotime('-24 hours'))]
        ]];

        foreach ($securityLogs as $log) {
            $threats[] = [
                'id' => uniqid('threat_'],
                'type' => $this->classifyThreatType($log],
                'source_ip' => $log['ip'], 
                'country' => $log['country'] ?? $this->getIPCountry($log['ip']],
                'intensity' => $this->calculateThreatIntensity($log],
                'timestamp' => strtotime($log['created_at']],
                'risk_score' => $log['risk_score'], 
                'coordinates' => $this->getGeoCoordinates($log['ip']],
                'vector' => $this->determineThreatVector($log)
            ];
        }

        // 添加模拟全球威胁情报（用于演示）
        $threats = array_merge($threats, $this->generateSimulatedGlobalThreats()];

        return $threats;
    }

    /**
     * 生成地理威胁映射
     */
    /**

     * generateGeoThreatMap 方法

     *

     * @param array $threatData

     * @return void

     */

    private function generateGeoThreatMap(array $threatData): array
    {
        $geoMap = [];
        
        foreach ($threatData as $threat) {
            $country = $threat['country'];
            
            if (!isset($geoMap[$country])) {
                $geoMap[$country] = [
                    'country' => $country,
                    'coordinates' => $threat['coordinates'], 
                    'threat_count' => 0,
                    'total_risk_score' => 0,
                    'threat_types' => [], 
                    'intensity_distribution' => [
                        self::INTENSITY_LOW => 0,
                        self::INTENSITY_MEDIUM => 0,
                        self::INTENSITY_HIGH => 0,
                        self::INTENSITY_CRITICAL => 0,
                        self::INTENSITY_EXTREME => 0
                    ], 
                    'recent_threats' => []
                ];
            }
            
            $geoMap[$country]['threat_count']++;
            $geoMap[$country]['total_risk_score'] += $threat['risk_score'];
            $geoMap[$country]['threat_types'][$threat['type']] = 
                ($geoMap[$country]['threat_types'][$threat['type']] ?? 0) + 1;
            $geoMap[$country]['intensity_distribution'][$threat['intensity']]++;
            
            if (count($geoMap[$country]['recent_threats']) < 5) {
                $geoMap[$country]['recent_threats'][] = $threat;
            }
        }

        // 计算威胁密度和危险等�?
        foreach ($geoMap as $country => &$data) {
            $data['average_risk_score'] = $data['total_risk_score'] / max($data['threat_count'],  1];
            $data['threat_density'] = $this->calculateThreatDensity($data];
            $data['danger_level'] = $this->calculateDangerLevel($data];
            $data['dominant_threat_type'] = $this->getDominantThreatType($data['threat_types']];
        }

        return $geoMap;
    }

    /**
     * 生成3D可视化数�?
     */
    /**

     * generate3DVisualizationData 方法

     *

     * @param array $geoMap

     * @param array $trends

     * @return void

     */

    private function generate3DVisualizationData(array $geoMap, array $trends): array
    {
        $visualization = [
            'global_sphere' => [
                'radius' => 100,
                'threat_nodes' => [], 
                'connection_lines' => [], 
                'threat_clusters' => [], 
                'heat_zones' => []
            ], 
            'threat_layers' => [
                'surface_threats' => [], 
                'network_threats' => [], 
                'application_threats' => [], 
                'data_threats' => []
            ], 
            'animation_sequences' => [], 
            'interactive_elements' => []
        ];

        // 生成威胁节点
        foreach ($geoMap as $country => $data) {
            $coordinates = $this->convertToSphereCoordinates($data['coordinates']];
            
            $node = [
                'id' => 'node_' . $country,
                'position' => $coordinates,
                'size' => min(max($data['threat_count'] / 10, 0.5], 5.0],
                'color' => $this->getThreatColor($data['danger_level']],
                'intensity' => $data['average_risk_score'] / 100,
                'pulsation_speed' => $this->calculatePulsationSpeed($data],
                'threat_types' => array_keys($data['threat_types']],
                'info' => [
                    'country' => $country,
                    'threats' => $data['threat_count'], 
                    'risk_level' => $data['danger_level']
                ]
            ];
            
            $visualization['global_sphere']['threat_nodes'][] = $node;
        }

        // 生成连接线（攻击路径�?
        $visualization['global_sphere']['connection_lines'] = $this->generateAttackPathLines($geoMap];
        
        // 生成热力区域
        $visualization['global_sphere']['heat_zones'] = $this->generateHeatZones($geoMap];
        
        // 生成动画序列
        $visualization['animation_sequences'] = $this->generateThreatAnimations($trends];

        return $visualization;
    }

    /**
     * AI预测威胁演进
     */
    /**

     * predictThreatEvolution 方法

     *

     * @param array $threatData

     * @return void

     */

    private function predictThreatEvolution(array $threatData): array
    {
        try {
            $analysisPrompt = $this->buildThreatAnalysisPrompt($threatData];
            
            $aiResponse = $this->aiService->analyzeContent($analysisPrompt, [
                'temperature' => 0.7,
                'max_tokens' => 1500,
                'response_format' => 'json'
            ]];

            return [
                'next_24h_prediction' => $this->extractPredictionData($aiResponse, '24h'],
                'next_week_prediction' => $this->extractPredictionData($aiResponse, '7d'],
                'threat_pattern_analysis' => $this->analyzeThreatPatterns($threatData],
                'emerging_threats' => $this->identifyEmergingThreats($threatData],
                'risk_evolution' => $this->predictRiskEvolution($threatData],
                'recommended_actions' => $this->generateActionRecommendations($aiResponse)
            ];
        } catch (\Exception $e) {
            $this->logger->error('AI威胁预测失败: ' . $e->getMessage()];
            return [
                'error' => 'AI预测服务暂时不可�?,
                'fallback_analysis' => $this->generateFallbackPrediction($threatData)
            ];
        }
    }

    /**
     * 计算全球威胁级别
     */
    /**

     * calculateGlobalThreatLevel 方法

     *

     * @param array $threatData

     * @return void

     */

    private function calculateGlobalThreatLevel(array $threatData): string
    {
        if (empty($threatData)) {
            return 'low';
        }

        $totalRisk = array_sum(array_column($threatData, 'risk_score')];
        $averageRisk = $totalRisk / count($threatData];
        $criticalThreats = count(array_filter($threatData, fn($t) => $t['intensity'] >= self::INTENSITY_CRITICAL)];

        if ($criticalThreats > 10 || $averageRisk > 80) {
            return 'extreme';
        } elseif ($criticalThreats > 5 || $averageRisk > 60) {
            return 'high';
        } elseif ($criticalThreats > 0 || $averageRisk > 40) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    /**
     * 分析威胁趋势
     */
    /**

     * analyzeThreatTrends 方法

     *

     * @param array $threatData

     * @return void

     */

    private function analyzeThreatTrends(array $threatData): array
    {
        $trends = [
            'hourly_distribution' => [], 
            'threat_type_trends' => [], 
            'geographic_trends' => [], 
            'intensity_trends' => []
        ];

        // 按小时分析威胁分�?
        for ($hour = 0; $hour < 24; $hour++) {
            $hourThreats = array_filter($threatData, function($threat) use ($hour) {
                return date('H', $threat['timestamp']) == $hour;
            }];
            
            $trends['hourly_distribution'][$hour] = [
                'hour' => $hour,
                'count' => count($hourThreats],
                'average_intensity' => $this->calculateAverageIntensity($hourThreats)
            ];
        }

        // 威胁类型趋势
        $threatTypes = array_column($threatData, 'type'];
        $trends['threat_type_trends'] = array_count_values($threatTypes];

        // 地理趋势
        $countries = array_column($threatData, 'country'];
        $trends['geographic_trends'] = array_count_values($countries];

        return $trends;
    }

    /**
     * 生成模拟全球威胁（用于演示）
     */
    /**

     * generateSimulatedGlobalThreats 方法

     *

     * @return void

     */

    private function generateSimulatedGlobalThreats(): array
    {
        $simulatedThreats = [];
        $threatCountries = [
            'CN' => ['lat' => 35.8617, 'lng' => 104.1954], 
            'US' => ['lat' => 37.0902, 'lng' => -95.7129], 
            'RU' => ['lat' => 61.5240, 'lng' => 105.3188], 
            'DE' => ['lat' => 51.1657, 'lng' => 10.4515], 
            'BR' => ['lat' => -14.2350, 'lng' => -51.9253], 
            'IN' => ['lat' => 20.5937, 'lng' => 78.9629], 
            'JP' => ['lat' => 36.2048, 'lng' => 138.2529], 
            'KR' => ['lat' => 35.9078, 'lng' => 127.7669]
        ];

        foreach ($threatCountries as $country => $coords) {
            $threatCount = rand(5, 25];
            
            for ($i = 0; $i < $threatCount; $i++) {
                $simulatedThreats[] = [
                    'id' => uniqid('sim_threat_'],
                    'type' => $this->getRandomThreatType(),
                    'source_ip' => $this->generateRandomIP(),
                    'country' => $country,
                    'intensity' => rand(1, 5],
                    'timestamp' => time() - rand(0, 86400],
                    'risk_score' => rand(10, 100],
                    'coordinates' => [
                        'lat' => $coords['lat'] + (rand(-500, 500) / 100],
                        'lng' => $coords['lng'] + (rand(-500, 500) / 100)
                    ], 
                    'vector' => $this->generateRandomThreatVector()
                ];
            }
        }

        return $simulatedThreats;
    }

    /**
     * 获取默认配置
     */
    /**

     * getDefaultConfig 方法

     *

     * @return void

     */

    private function getDefaultConfig(): array
    {
        return [
            'threat_intelligence_sources' => [
                'internal_logs' => true,
                'honeypot_data' => true,
                'ip_reputation' => true,
                'malware_feeds' => true
            ], 
            'visualization_settings' => [
                'sphere_radius' => 100,
                'max_nodes' => 200,
                'animation_speed' => 1.0,
                'color_scheme' => 'thermal'
            ], 
            'ai_prediction_config' => [
                'model_temperature' => 0.7,
                'prediction_horizon_hours' => 24,
                'confidence_threshold' => 0.8
            ]
        ];
    }

    /**
     * 初始化威胁源
     */
    /**

     * initializeThreatSources 方法

     *

     * @return void

     */

    private function initializeThreatSources(): void
    {
        $this->threatSources = [
            'security_logs',
            'intrusion_detection',
            'malware_analysis',
            'network_monitoring',
            'honeypot_data'
        ];
    }

    /**
     * 辅助方法
     */
    /**

     * classifyThreatType 方法

     *

     * @param array $log

     * @return void

     */

    private function classifyThreatType(array $log): string
    {
        // 基于日志内容分类威胁类型
        if (isset($log['threat_type'])) {
            return $log['threat_type'];
        }
        
        // 简单的启发式分�?
        $riskScore = $log['risk_score'] ?? 0;
        
        if ($riskScore > 80) {
            return self::THREAT_TYPE_APT;
        } elseif ($riskScore > 60) {
            return self::THREAT_TYPE_MALWARE;
        } elseif ($riskScore > 40) {
            return self::THREAT_TYPE_INJECTION;
        } else {
            return self::THREAT_TYPE_BRUTEFORCE;
        }
    }

    /**


     * calculateThreatIntensity 方法


     *


     * @param array $log


     * @return void


     */


    private function calculateThreatIntensity(array $log): int
    {
        $riskScore = $log['risk_score'] ?? 0;
        
        if ($riskScore >= 90) return self::INTENSITY_EXTREME;
        if ($riskScore >= 70) return self::INTENSITY_CRITICAL;
        if ($riskScore >= 50) return self::INTENSITY_HIGH;
        if ($riskScore >= 30) return self::INTENSITY_MEDIUM;
        return self::INTENSITY_LOW;
    }

    /**


     * getIPCountry 方法


     *


     * @param string $ip


     * @return void


     */


    private function getIPCountry(string $ip): string
    {
        // 简化的IP地理位置查询
        $ipParts = explode('.', $ip];
        if (count($ipParts) !== 4) return 'Unknown';
        
        $firstOctet = (int)$ipParts[0];
        
        // 简单的IP段到国家映射
        $countryMap = [
            1 => 'US', 14 => 'US', 27 => 'US',
            58 => 'CN', 59 => 'CN', 60 => 'CN',
            78 => 'RU', 79 => 'RU', 80 => 'RU',
            81 => 'JP', 82 => 'KR', 83 => 'KR'
        ];
        
        return $countryMap[$firstOctet] ?? 'Unknown';
    }

    /**


     * getGeoCoordinates 方法


     *


     * @param string $ip


     * @return void


     */


    private function getGeoCoordinates(string $ip): array
    {
        $country = $this->getIPCountry($ip];
        
        // 简化的国家坐标映射
        $coordinates = [
            'US' => ['lat' => 37.0902, 'lng' => -95.7129], 
            'CN' => ['lat' => 35.8617, 'lng' => 104.1954], 
            'RU' => ['lat' => 61.5240, 'lng' => 105.3188], 
            'JP' => ['lat' => 36.2048, 'lng' => 138.2529], 
            'KR' => ['lat' => 35.9078, 'lng' => 127.7669]
        ];
        
        return $coordinates[$country] ?? ['lat' => 0, 'lng' => 0];
    }

    /**


     * determineThreatVector 方法


     *


     * @param array $log


     * @return void


     */


    private function determineThreatVector(array $log): string
    {
        $vectors = ['web', 'email', 'network', 'endpoint', 'cloud'];
        return $vectors[array_rand($vectors)];
    }

    /**


     * getRandomThreatType 方法


     *


     * @return void


     */


    private function getRandomThreatType(): string
    {
        $types = [
            self::THREAT_TYPE_MALWARE,
            self::THREAT_TYPE_PHISHING,
            self::THREAT_TYPE_DDoS,
            self::THREAT_TYPE_INJECTION,
            self::THREAT_TYPE_BRUTEFORCE,
            self::THREAT_TYPE_BOTNET
        ];
        
        return $types[array_rand($types)];
    }

    /**


     * generateRandomIP 方法


     *


     * @return void


     */


    private function generateRandomIP(): string
    {
        return rand(1, 255) . '.' . rand(1, 255) . '.' . rand(1, 255) . '.' . rand(1, 255];
    }

    /**


     * generateRandomThreatVector 方法


     *


     * @return void


     */


    private function generateRandomThreatVector(): string
    {
        $vectors = ['HTTP', 'HTTPS', 'FTP', 'SSH', 'SMTP', 'DNS'];
        return $vectors[array_rand($vectors)];
    }

    /**


     * convertToSphereCoordinates 方法


     *


     * @param array $geoCoords


     * @return void


     */


    private function convertToSphereCoordinates(array $geoCoords): array
    {
        $lat = deg2rad($geoCoords['lat']];
        $lng = deg2rad($geoCoords['lng']];
        $radius = 100;
        
        return [
            'x' => $radius * cos($lat) * cos($lng],
            'y' => $radius * sin($lat],
            'z' => $radius * cos($lat) * sin($lng)
        ];
    }

    /**


     * getThreatColor 方法


     *


     * @param string $dangerLevel


     * @return void


     */


    private function getThreatColor(string $dangerLevel): string
    {
        $colors = [
            'low' => '#00ff00',
            'medium' => '#ffff00',
            'high' => '#ff8800',
            'critical' => '#ff0000',
            'extreme' => '#8800ff'
        ];
        
        return $colors[$dangerLevel] ?? '#ffffff';
    }

    /**


     * calculatePulsationSpeed 方法


     *


     * @param array $data


     * @return void


     */


    private function calculatePulsationSpeed(array $data): float
    {
        return min(max($data['average_risk_score'] / 50, 0.1], 2.0];
    }

    /**


     * generateAttackPathLines 方法


     *


     * @param array $geoMap


     * @return void


     */


    private function generateAttackPathLines(array $geoMap): array
    {
        // 生成攻击路径连接线的逻辑
        return [];
    }

    /**


     * generateHeatZones 方法


     *


     * @param array $geoMap


     * @return void


     */


    private function generateHeatZones(array $geoMap): array
    {
        // 生成热力区域的逻辑
        return [];
    }

    /**


     * generateThreatAnimations 方法


     *


     * @param array $trends


     * @return void


     */


    private function generateThreatAnimations(array $trends): array
    {
        // 生成威胁动画序列
        return [];
    }

    /**


     * buildThreatAnalysisPrompt 方法


     *


     * @param array $threatData


     * @return void


     */


    private function buildThreatAnalysisPrompt(array $threatData): string
    {
        $threatSummary = [
            'total_threats' => count($threatData],
            'threat_types' => array_count_values(array_column($threatData, 'type')],
            'average_risk' => array_sum(array_column($threatData, 'risk_score')) / max(count($threatData], 1],
            'countries_affected' => count(array_unique(array_column($threatData, 'country')))
        ];

        return "请分析以下威胁数据并提供预测:\n" . json_encode($threatSummary, JSON_PRETTY_PRINT];
    }

    /**


     * extractPredictionData 方法


     *


     * @param string $aiResponse


     * @param string $timeframe


     * @return void


     */


    private function extractPredictionData(string $aiResponse, string $timeframe): array
    {
        // 从AI响应中提取预测数�?
        return ['prediction' => 'AI分析�?..'];
    }

    /**


     * analyzeThreatPatterns 方法


     *


     * @param array $threatData


     * @return void


     */


    private function analyzeThreatPatterns(array $threatData): array
    {
        return ['patterns' => '模式分析�?..'];
    }

    /**


     * identifyEmergingThreats 方法


     *


     * @param array $threatData


     * @return void


     */


    private function identifyEmergingThreats(array $threatData): array
    {
        return ['emerging' => '新兴威胁识别�?..'];
    }

    /**


     * predictRiskEvolution 方法


     *


     * @param array $threatData


     * @return void


     */


    private function predictRiskEvolution(array $threatData): array
    {
        return ['evolution' => '风险演进预测�?..'];
    }

    /**


     * generateActionRecommendations 方法


     *


     * @param string $aiResponse


     * @return void


     */


    private function generateActionRecommendations(string $aiResponse): array
    {
        return ['recommendations' => '生成建议�?..'];
    }

    /**


     * generateFallbackPrediction 方法


     *


     * @param array $threatData


     * @return void


     */


    private function generateFallbackPrediction(array $threatData): array
    {
        return ['fallback' => '基础预测分析'];
    }

    /**


     * calculateThreatDensity 方法


     *


     * @param array $data


     * @return void


     */


    private function calculateThreatDensity(array $data): float
    {
        return min($data['threat_count'] / 10.0, 1.0];
    }

    /**


     * calculateDangerLevel 方法


     *


     * @param array $data


     * @return void


     */


    private function calculateDangerLevel(array $data): string
    {
        $avgRisk = $data['average_risk_score'];
        
        if ($avgRisk >= 80) return 'extreme';
        if ($avgRisk >= 60) return 'critical';
        if ($avgRisk >= 40) return 'high';
        if ($avgRisk >= 20) return 'medium';
        return 'low';
    }

    /**


     * getDominantThreatType 方法


     *


     * @param array $threatTypes


     * @return void


     */


    private function getDominantThreatType(array $threatTypes): string
    {
        if (empty($threatTypes)) return 'unknown';
        
        return array_search(max($threatTypes], $threatTypes];
    }

    /**


     * identifyHighRiskRegions 方法


     *


     * @param array $geoMap


     * @return void


     */


    private function identifyHighRiskRegions(array $geoMap): array
    {
        $highRiskRegions = [];
        
        foreach ($geoMap as $country => $data) {
            if ($data['danger_level'] === 'critical' || $data['danger_level'] === 'extreme') {
                $highRiskRegions[] = [
                    'country' => $country,
                    'risk_score' => $data['average_risk_score'], 
                    'threat_count' => $data['threat_count'], 
                    'dominant_threat' => $data['dominant_threat_type']
                ];
            }
        }
        
        return $highRiskRegions;
    }

    /**


     * analyzeThreatVectors 方法


     *


     * @param array $threatData


     * @return void


     */


    private function analyzeThreatVectors(array $threatData): array
    {
        $vectors = array_column($threatData, 'vector'];
        return array_count_values($vectors];
    }

    /**


     * generateDefenseRecommendations 方法


     *


     * @param array $threatData


     * @return void


     */


    private function generateDefenseRecommendations(array $threatData): array
    {
        return [
            'immediate_actions' => [
                '加强监控高风险IP�?,
                '更新威胁检测规�?,
                '增加安全日志分析频率'
            ], 
            'strategic_actions' => [
                '部署额外的蜜罐节�?,
                '增强AI威胁检测能�?,
                '建立威胁情报共享机制'
            ]
        ];
    }

    /**


     * calculateAverageIntensity 方法


     *


     * @param array $threats


     * @return void


     */


    private function calculateAverageIntensity(array $threats): float
    {
        if (empty($threats)) return 0.0;
        
        $totalIntensity = array_sum(array_column($threats, 'intensity')];
        return $totalIntensity / count($threats];
    }
}

