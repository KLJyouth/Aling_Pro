<?php

namespace AlingAi\Security;

use Psr\Log\LoggerInterface;
use AlingAi\Core\Container;
use AlingAi\AI\Visualization\DataVisualizer;

/**
 * 增强3D威胁可视化系统
 *
 * 提供3D威胁可视化、实时监控和交互式安全分析
 * 增强安全性：直观的威胁展示、实时监控和智能分析
 * 优化性能：高效渲染和智能数据聚合
 */
class Enhanced3DThreatVisualizationSystem
{
    private $logger;
    private $container;
    private $config = [];
    private $dataVisualizer;
    private $threatData = [];
    private $visualizationLayers = [];
    private $interactiveElements = [];
    private $realTimeFeeds = [];
    private $renderQueue = [];
    private $lastUpdate = 0;
    private $updateInterval = 1000; // 1秒更新一次
    
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
        $this->initializeVisualizationLayers();
    }
    
    /**
     * 加载配置
     * 
     * @return array
     */
    private function loadConfiguration(): array
    {
        return [
            'visualization' => [
                'enabled' => env('3D_VISUALIZATION_ENABLED', true),
                'render_engine' => env('3D_RENDER_ENGINE', 'webgl'),
                'quality' => env('3D_QUALITY', 'high'),
                'fps_limit' => env('3D_FPS_LIMIT', 60),
                'auto_rotate' => env('3D_AUTO_ROTATE', true)
            ],
            'layers' => [
                'threat_indicators' => env('3D_THREAT_LAYER', true),
                'network_topology' => env('3D_NETWORK_LAYER', true),
                'geographic_data' => env('3D_GEO_LAYER', true),
                'temporal_data' => env('3D_TEMPORAL_LAYER', true),
                'risk_heatmap' => env('3D_RISK_LAYER', true)
            ],
            'interactivity' => [
                'mouse_control' => env('3D_MOUSE_CONTROL', true),
                'keyboard_control' => env('3D_KEYBOARD_CONTROL', true),
                'touch_control' => env('3D_TOUCH_CONTROL', true),
                'zoom_limits' => [
                    'min' => env('3D_MIN_ZOOM', 0.1),
                    'max' => env('3D_MAX_ZOOM', 10.0)
                ]
            ],
            'data_sources' => [
                'real_time' => env('3D_REAL_TIME_DATA', true),
                'historical' => env('3D_HISTORICAL_DATA', true),
                'predictive' => env('3D_PREDICTIVE_DATA', true),
                'external_feeds' => env('3D_EXTERNAL_FEEDS', true)
            ],
            'performance' => [
                'max_objects' => env('3D_MAX_OBJECTS', 10000),
                'lod_enabled' => env('3D_LOD_ENABLED', true),
                'culling_enabled' => env('3D_CULLING_ENABLED', true),
                'batch_rendering' => env('3D_BATCH_RENDERING', true)
            ]
        ];
    }
    
    /**
     * 初始化组件
     */
    private function initializeComponents(): void
    {
        // 初始化数据可视化器
        $this->dataVisualizer = new DataVisualizer([
            'dimensions' => 3,
            'coordinate_system' => 'cartesian',
            'projection_type' => 'perspective',
            'lighting_enabled' => true
        ]);
        
        // 初始化威胁数据
        $this->threatData = [
            'active_threats' => [],
            'threat_history' => [],
            'threat_patterns' => [],
            'risk_zones' => [],
            'attack_vectors' => []
        ];
        
        // 初始化可视化层
        $this->visualizationLayers = [
            'threat_indicators' => [],
            'network_topology' => [],
            'geographic_data' => [],
            'temporal_data' => [],
            'risk_heatmap' => []
        ];
        
        // 初始化交互元素
        $this->interactiveElements = [
            'clickable_objects' => [],
            'hover_effects' => [],
            'selection_handlers' => [],
            'animation_controllers' => []
        ];
        
        // 初始化实时数据流
        $this->realTimeFeeds = [
            'threat_events' => [],
            'network_traffic' => [],
            'system_metrics' => [],
            'user_activity' => []
        ];
        
        // 初始化渲染队列
        $this->renderQueue = [
            'high_priority' => [],
            'normal_priority' => [],
            'low_priority' => []
        ];
    }
    
    /**
     * 初始化可视化层
     */
    private function initializeVisualizationLayers(): void
    {
        // 威胁指标层
        $this->visualizationLayers['threat_indicators'] = [
            'enabled' => $this->config['layers']['threat_indicators'],
            'objects' => [],
            'style' => [
                'color_scheme' => 'threat_level',
                'size_scale' => 'severity',
                'animation' => 'pulse',
                'opacity' => 0.8
            ]
        ];
        
        // 网络拓扑层
        $this->visualizationLayers['network_topology'] = [
            'enabled' => $this->config['layers']['network_topology'],
            'objects' => [],
            'style' => [
                'node_style' => 'sphere',
                'edge_style' => 'line',
                'color_scheme' => 'traffic_volume',
                'thickness_scale' => 'bandwidth'
            ]
        ];
        
        // 地理数据层
        $this->visualizationLayers['geographic_data'] = [
            'enabled' => $this->config['layers']['geographic_data'],
            'objects' => [],
            'style' => [
                'projection' => 'mercator',
                'terrain_enabled' => true,
                'marker_style' => 'pin',
                'cluster_enabled' => true
            ]
        ];
        
        // 时间数据层
        $this->visualizationLayers['temporal_data'] = [
            'enabled' => $this->config['layers']['temporal_data'],
            'objects' => [],
            'style' => [
                'timeline_enabled' => true,
                'animation_speed' => 1.0,
                'time_scale' => 'auto',
                'playback_controls' => true
            ]
        ];
        
        // 风险热图层
        $this->visualizationLayers['risk_heatmap'] = [
            'enabled' => $this->config['layers']['risk_heatmap'],
            'objects' => [],
            'style' => [
                'color_gradient' => 'red_to_green',
                'intensity_scale' => 'risk_level',
                'blur_radius' => 5,
                'opacity' => 0.6
            ]
        ];
    }
    
    /**
     * 更新威胁数据
     * 
     * @param array $threatData 威胁数据
     * @return array 更新结果
     */
    public function updateThreatData(array $threatData): array
    {
        $this->logger->debug('更新3D威胁可视化数据', [
            'threat_count' => count($threatData['threats'] ?? [])
        ]);
        
        $updateResult = [
            'updated' => false,
            'objects_created' => 0,
            'objects_updated' => 0,
            'objects_removed' => 0
        ];
        
        // 更新活跃威胁
        if (isset($threatData['threats'])) {
            $this->updateActiveThreats($threatData['threats']);
            $updateResult['objects_updated'] += count($threatData['threats']);
        }
        
        // 更新威胁历史
        if (isset($threatData['history'])) {
            $this->updateThreatHistory($threatData['history']);
        }
        
        // 更新威胁模式
        if (isset($threatData['patterns'])) {
            $this->updateThreatPatterns($threatData['patterns']);
        }
        
        // 更新风险区域
        if (isset($threatData['risk_zones'])) {
            $this->updateRiskZones($threatData['risk_zones']);
        }
        
        // 更新攻击向量
        if (isset($threatData['attack_vectors'])) {
            $this->updateAttackVectors($threatData['attack_vectors']);
        }
        
        // 重新生成可视化对象
        $this->regenerateVisualizationObjects();
        
        // 更新渲染队列
        $this->updateRenderQueue();
        
        $updateResult['updated'] = true;
        
        $this->logger->info('3D威胁可视化数据更新完成', [
            'objects_updated' => $updateResult['objects_updated']
        ]);
        
        return $updateResult;
    }
    
    /**
     * 更新活跃威胁
     * 
     * @param array $threats 威胁列表
     */
    private function updateActiveThreats(array $threats): void
    {
        $this->threatData['active_threats'] = [];
        
        foreach ($threats as $threat) {
            $threatObject = [
                'id' => $threat['id'] ?? uniqid('threat_', true),
                'type' => $threat['type'] ?? 'unknown',
                'severity' => $threat['severity'] ?? 'low',
                'position' => $this->calculateThreatPosition($threat),
                'size' => $this->calculateThreatSize($threat),
                'color' => $this->calculateThreatColor($threat),
                'metadata' => $threat['metadata'] ?? [],
                'timestamp' => time()
            ];
            
            $this->threatData['active_threats'][] = $threatObject;
        }
    }
    
    /**
     * 计算威胁位置
     * 
     * @param array $threat 威胁数据
     * @return array 3D位置坐标
     */
    private function calculateThreatPosition(array $threat): array
    {
        // 基于威胁类型和来源计算3D位置
        $type = $threat['type'] ?? 'unknown';
        $source = $threat['source'] ?? 'unknown';
        
        // 简化的位置计算
        $x = hash('crc32', $type) % 100 - 50; // -50 到 50
        $y = hash('crc32', $source) % 100 - 50; // -50 到 50
        $z = (time() % 100) - 50; // 基于时间的Z坐标
        
        return [$x, $y, $z];
    }
    
    /**
     * 计算威胁大小
     * 
     * @param array $threat 威胁数据
     * @return float 大小值
     */
    private function calculateThreatSize(array $threat): float
    {
        $severity = $threat['severity'] ?? 'low';
        $severitySizes = [
            'critical' => 3.0,
            'high' => 2.0,
            'medium' => 1.5,
            'low' => 1.0
        ];
        
        return $severitySizes[$severity] ?? 1.0;
    }
    
    /**
     * 计算威胁颜色
     * 
     * @param array $threat 威胁数据
     * @return array RGB颜色值
     */
    private function calculateThreatColor(array $threat): array
    {
        $severity = $threat['severity'] ?? 'low';
        $severityColors = [
            'critical' => [255, 0, 0],    // 红色
            'high' => [255, 165, 0],      // 橙色
            'medium' => [255, 255, 0],    // 黄色
            'low' => [0, 255, 0]          // 绿色
        ];
        
        return $severityColors[$severity] ?? [128, 128, 128];
    }
    
    /**
     * 更新威胁历史
     * 
     * @param array $history 历史数据
     */
    private function updateThreatHistory(array $history): void
    {
        $this->threatData['threat_history'] = array_merge(
            $this->threatData['threat_history'],
            $history
        );
        
        // 限制历史数据大小
        if (count($this->threatData['threat_history']) > 10000) {
            $this->threatData['threat_history'] = array_slice(
                $this->threatData['threat_history'],
                -5000
            );
        }
    }
    
    /**
     * 更新威胁模式
     * 
     * @param array $patterns 模式数据
     */
    private function updateThreatPatterns(array $patterns): void
    {
        $this->threatData['threat_patterns'] = $patterns;
    }
    
    /**
     * 更新风险区域
     * 
     * @param array $riskZones 风险区域数据
     */
    private function updateRiskZones(array $riskZones): void
    {
        $this->threatData['risk_zones'] = [];
        
        foreach ($riskZones as $zone) {
            $zoneObject = [
                'id' => $zone['id'] ?? uniqid('zone_', true),
                'name' => $zone['name'] ?? 'Unknown Zone',
                'risk_level' => $zone['risk_level'] ?? 0.0,
                'center' => $zone['center'] ?? [0, 0, 0],
                'radius' => $zone['radius'] ?? 10.0,
                'color' => $this->calculateRiskColor($zone['risk_level'] ?? 0.0),
                'opacity' => ($zone['risk_level'] ?? 0.0) * 0.8 + 0.2
            ];
            
            $this->threatData['risk_zones'][] = $zoneObject;
        }
    }
    
    /**
     * 计算风险颜色
     * 
     * @param float $riskLevel 风险级别 (0-1)
     * @return array RGB颜色值
     */
    private function calculateRiskColor(float $riskLevel): array
    {
        if ($riskLevel >= 0.8) {
            return [255, 0, 0]; // 红色
        } elseif ($riskLevel >= 0.6) {
            return [255, 165, 0]; // 橙色
        } elseif ($riskLevel >= 0.4) {
            return [255, 255, 0]; // 黄色
        } else {
            return [0, 255, 0]; // 绿色
        }
    }
    
    /**
     * 更新攻击向量
     * 
     * @param array $attackVectors 攻击向量数据
     */
    private function updateAttackVectors(array $attackVectors): void
    {
        $this->threatData['attack_vectors'] = [];
        
        foreach ($attackVectors as $vector) {
            $vectorObject = [
                'id' => $vector['id'] ?? uniqid('vector_', true),
                'source' => $vector['source'] ?? [0, 0, 0],
                'target' => $vector['target'] ?? [0, 0, 0],
                'type' => $vector['type'] ?? 'unknown',
                'intensity' => $vector['intensity'] ?? 1.0,
                'color' => $this->calculateVectorColor($vector['type'] ?? 'unknown'),
                'width' => $vector['intensity'] ?? 1.0
            ];
            
            $this->threatData['attack_vectors'][] = $vectorObject;
        }
    }
    
    /**
     * 计算向量颜色
     * 
     * @param string $type 向量类型
     * @return array RGB颜色值
     */
    private function calculateVectorColor(string $type): array
    {
        $typeColors = [
            'ddos' => [255, 0, 0],        // 红色
            'malware' => [255, 0, 255],   // 紫色
            'phishing' => [0, 255, 255],  // 青色
            'sql_injection' => [255, 255, 0], // 黄色
            'xss' => [255, 165, 0],       // 橙色
            'default' => [128, 128, 128]  // 灰色
        ];
        
        return $typeColors[$type] ?? $typeColors['default'];
    }
    
    /**
     * 重新生成可视化对象
     */
    private function regenerateVisualizationObjects(): void
    {
        // 清空现有对象
        foreach ($this->visualizationLayers as &$layer) {
            $layer['objects'] = [];
        }
        
        // 生成威胁指标对象
        if ($this->visualizationLayers['threat_indicators']['enabled']) {
            $this->generateThreatIndicatorObjects();
        }
        
        // 生成网络拓扑对象
        if ($this->visualizationLayers['network_topology']['enabled']) {
            $this->generateNetworkTopologyObjects();
        }
        
        // 生成地理数据对象
        if ($this->visualizationLayers['geographic_data']['enabled']) {
            $this->generateGeographicObjects();
        }
        
        // 生成时间数据对象
        if ($this->visualizationLayers['temporal_data']['enabled']) {
            $this->generateTemporalObjects();
        }
        
        // 生成风险热图对象
        if ($this->visualizationLayers['risk_heatmap']['enabled']) {
            $this->generateRiskHeatmapObjects();
        }
    }
    
    /**
     * 生成威胁指标对象
     */
    private function generateThreatIndicatorObjects(): void
    {
        foreach ($this->threatData['active_threats'] as $threat) {
            $object = [
                'type' => 'sphere',
                'position' => $threat['position'],
                'size' => $threat['size'],
                'color' => $threat['color'],
                'opacity' => 0.8,
                'animation' => 'pulse',
                'metadata' => $threat['metadata'],
                'interactive' => true
            ];
            
            $this->visualizationLayers['threat_indicators']['objects'][] = $object;
        }
    }
    
    /**
     * 生成网络拓扑对象
     */
    private function generateNetworkTopologyObjects(): void
    {
        // 生成节点
        $nodes = [];
        foreach ($this->threatData['active_threats'] as $threat) {
            $node = [
                'type' => 'sphere',
                'position' => $threat['position'],
                'size' => 1.0,
                'color' => [100, 100, 255],
                'label' => $threat['type']
            ];
            $nodes[] = $node;
        }
        
        // 生成连接
        $connections = [];
        for ($i = 0; $i < count($nodes) - 1; $i++) {
            for ($j = $i + 1; $j < count($nodes); $j++) {
                $connection = [
                    'type' => 'line',
                    'start' => $nodes[$i]['position'],
                    'end' => $nodes[$j]['position'],
                    'color' => [200, 200, 200],
                    'width' => 0.1
                ];
                $connections[] = $connection;
            }
        }
        
        $this->visualizationLayers['network_topology']['objects'] = array_merge($nodes, $connections);
    }
    
    /**
     * 生成地理数据对象
     */
    private function generateGeographicObjects(): void
    {
        foreach ($this->threatData['active_threats'] as $threat) {
            $object = [
                'type' => 'pin',
                'position' => $threat['position'],
                'size' => $threat['size'],
                'color' => $threat['color'],
                'label' => $threat['type'],
                'metadata' => $threat['metadata']
            ];
            
            $this->visualizationLayers['geographic_data']['objects'][] = $object;
        }
    }
    
    /**
     * 生成时间数据对象
     */
    private function generateTemporalObjects(): void
    {
        $timelineObjects = [];
        $currentTime = time();
        
        foreach ($this->threatData['threat_history'] as $index => $event) {
            $timeOffset = ($currentTime - ($event['timestamp'] ?? $currentTime)) / 3600; // 小时
            $position = [
                $index * 2 - 50, // X坐标
                $timeOffset,     // Y坐标（时间）
                0                // Z坐标
            ];
            
            $object = [
                'type' => 'cube',
                'position' => $position,
                'size' => 0.5,
                'color' => [255, 255, 255],
                'opacity' => 0.6,
                'metadata' => $event
            ];
            
            $timelineObjects[] = $object;
        }
        
        $this->visualizationLayers['temporal_data']['objects'] = $timelineObjects;
    }
    
    /**
     * 生成风险热图对象
     */
    private function generateRiskHeatmapObjects(): void
    {
        foreach ($this->threatData['risk_zones'] as $zone) {
            $object = [
                'type' => 'sphere',
                'position' => $zone['center'],
                'size' => $zone['radius'],
                'color' => $zone['color'],
                'opacity' => $zone['opacity'],
                'wireframe' => true,
                'metadata' => $zone
            ];
            
            $this->visualizationLayers['risk_heatmap']['objects'][] = $object;
        }
    }
    
    /**
     * 更新渲染队列
     */
    private function updateRenderQueue(): void
    {
        // 清空渲染队列
        foreach ($this->renderQueue as &$queue) {
            $queue = [];
        }
        
        // 按优先级添加对象到渲染队列
        foreach ($this->visualizationLayers as $layerName => $layer) {
            if (!$layer['enabled']) {
                continue;
            }
            
            $priority = $this->getLayerPriority($layerName);
            
            foreach ($layer['objects'] as $object) {
                $this->renderQueue[$priority][] = [
                    'layer' => $layerName,
                    'object' => $object,
                    'style' => $layer['style']
                ];
            }
        }
    }
    
    /**
     * 获取层优先级
     * 
     * @param string $layerName 层名称
     * @return string 优先级
     */
    private function getLayerPriority(string $layerName): string
    {
        $priorities = [
            'threat_indicators' => 'high_priority',
            'risk_heatmap' => 'high_priority',
            'network_topology' => 'normal_priority',
            'geographic_data' => 'normal_priority',
            'temporal_data' => 'low_priority'
        ];
        
        return $priorities[$layerName] ?? 'normal_priority';
    }
    
    /**
     * 获取可视化数据
     * 
     * @return array 可视化数据
     */
    public function getVisualizationData(): array
    {
        $this->performCleanup();
        
        return [
            'timestamp' => time(),
            'layers' => $this->visualizationLayers,
            'render_queue' => $this->renderQueue,
            'interactive_elements' => $this->interactiveElements,
            'camera' => [
                'position' => [0, 0, 100],
                'target' => [0, 0, 0],
                'fov' => 75,
                'near' => 0.1,
                'far' => 1000
            ],
            'lighting' => [
                'ambient' => [0.3, 0.3, 0.3],
                'directional' => [
                    'color' => [1.0, 1.0, 1.0],
                    'position' => [10, 10, 10],
                    'intensity' => 0.8
                ]
            ],
            'controls' => [
                'auto_rotate' => $this->config['visualization']['auto_rotate'],
                'mouse_control' => $this->config['interactivity']['mouse_control'],
                'keyboard_control' => $this->config['interactivity']['keyboard_control'],
                'touch_control' => $this->config['interactivity']['touch_control']
            ],
            'performance' => [
                'total_objects' => $this->countTotalObjects(),
                'render_quality' => $this->config['visualization']['quality'],
                'fps_limit' => $this->config['visualization']['fps_limit']
            ]
        ];
    }
    
    /**
     * 计算总对象数
     * 
     * @return int 对象总数
     */
    private function countTotalObjects(): int
    {
        $total = 0;
        foreach ($this->visualizationLayers as $layer) {
            $total += count($layer['objects']);
        }
        return $total;
    }
    
    /**
     * 处理交互事件
     * 
     * @param array $eventData 事件数据
     * @return array 处理结果
     */
    public function handleInteraction(array $eventData): array
    {
        $result = [
            'handled' => false,
            'action' => 'none',
            'data' => null
        ];
        
        $eventType = $eventData['type'] ?? '';
        $targetId = $eventData['target_id'] ?? '';
        
        switch ($eventType) {
            case 'click':
                $result = $this->handleClickEvent($targetId);
                break;
            case 'hover':
                $result = $this->handleHoverEvent($targetId);
                break;
            case 'select':
                $result = $this->handleSelectEvent($targetId);
                break;
            case 'zoom':
                $result = $this->handleZoomEvent($eventData);
                break;
            case 'rotate':
                $result = $this->handleRotateEvent($eventData);
                break;
            default:
                $result['handled'] = false;
                break;
        }
        
        return $result;
    }
    
    /**
     * 处理点击事件
     * 
     * @param string $targetId 目标ID
     * @return array 处理结果
     */
    private function handleClickEvent(string $targetId): array
    {
        // 查找目标对象
        $targetObject = $this->findObjectById($targetId);
        
        if ($targetObject) {
            return [
                'handled' => true,
                'action' => 'show_details',
                'data' => $targetObject['metadata'] ?? []
            ];
        }
        
        return [
            'handled' => false,
            'action' => 'none',
            'data' => null
        ];
    }
    
    /**
     * 处理悬停事件
     * 
     * @param string $targetId 目标ID
     * @return array 处理结果
     */
    private function handleHoverEvent(string $targetId): array
    {
        $targetObject = $this->findObjectById($targetId);
        
        if ($targetObject) {
            return [
                'handled' => true,
                'action' => 'show_tooltip',
                'data' => [
                    'label' => $targetObject['metadata']['type'] ?? 'Unknown',
                    'severity' => $targetObject['metadata']['severity'] ?? 'low'
                ]
            ];
        }
        
        return [
            'handled' => false,
            'action' => 'none',
            'data' => null
        ];
    }
    
    /**
     * 处理选择事件
     * 
     * @param string $targetId 目标ID
     * @return array 处理结果
     */
    private function handleSelectEvent(string $targetId): array
    {
        $targetObject = $this->findObjectById($targetId);
        
        if ($targetObject) {
            return [
                'handled' => true,
                'action' => 'highlight_object',
                'data' => $targetObject
            ];
        }
        
        return [
            'handled' => false,
            'action' => 'none',
            'data' => null
        ];
    }
    
    /**
     * 处理缩放事件
     * 
     * @param array $eventData 事件数据
     * @return array 处理结果
     */
    private function handleZoomEvent(array $eventData): array
    {
        $zoomLevel = $eventData['zoom_level'] ?? 1.0;
        $limits = $this->config['interactivity']['zoom_limits'];
        
        if ($zoomLevel >= $limits['min'] && $zoomLevel <= $limits['max']) {
            return [
                'handled' => true,
                'action' => 'update_camera',
                'data' => ['zoom' => $zoomLevel]
            ];
        }
        
        return [
            'handled' => false,
            'action' => 'none',
            'data' => null
        ];
    }
    
    /**
     * 处理旋转事件
     * 
     * @param array $eventData 事件数据
     * @return array 处理结果
     */
    private function handleRotateEvent(array $eventData): array
    {
        $rotation = $eventData['rotation'] ?? [0, 0, 0];
        
        return [
            'handled' => true,
            'action' => 'update_camera',
            'data' => ['rotation' => $rotation]
        ];
    }
    
    /**
     * 根据ID查找对象
     * 
     * @param string $objectId 对象ID
     * @return array|null 对象数据
     */
    private function findObjectById(string $objectId): ?array
    {
        foreach ($this->visualizationLayers as $layer) {
            foreach ($layer['objects'] as $object) {
                if (($object['metadata']['id'] ?? '') === $objectId) {
                    return $object;
                }
            }
        }
        
        return null;
    }
    
    /**
     * 获取系统状态
     * 
     * @return array 系统状态
     */
    public function getStatus(): array
    {
        return [
            'total_objects' => $this->countTotalObjects(),
            'active_layers' => count(array_filter($this->visualizationLayers, function($layer) {
                return $layer['enabled'];
            })),
            'render_queue_size' => array_sum(array_map('count', $this->renderQueue)),
            'threat_data' => [
                'active_threats' => count($this->threatData['active_threats']),
                'risk_zones' => count($this->threatData['risk_zones']),
                'attack_vectors' => count($this->threatData['attack_vectors'])
            ],
            'performance' => [
                'last_update' => date('Y-m-d H:i:s', $this->lastUpdate),
                'update_interval' => $this->updateInterval,
                'render_quality' => $this->config['visualization']['quality']
            ]
        ];
    }
    
    /**
     * 执行清理
     */
    private function performCleanup(): void
    {
        $currentTime = time();
        
        // 清理过期的威胁数据
        $this->threatData['active_threats'] = array_filter(
            $this->threatData['active_threats'],
            function($threat) use ($currentTime) {
                return $currentTime - $threat['timestamp'] < 3600; // 1小时内
            }
        );
        
        // 清理过期的历史数据
        if (count($this->threatData['threat_history']) > 10000) {
            $this->threatData['threat_history'] = array_slice(
                $this->threatData['threat_history'],
                -5000
            );
        }
        
        $this->lastUpdate = $currentTime;
    }
}
