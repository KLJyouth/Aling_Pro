<?php

namespace AlingAi\AI\Visualization;

use Psr\Log\LoggerInterface;
use AlingAi\Core\Container;

/**
 * 数据可视化器
 * 
 * 用于创建高级数据可视化，包括2D和3D视图
 */
class DataVisualizer
{
    private array $config = [];
    private $logger;
    private $container;
    private array $visualizations = [];
    
    /**
     * 构造函数
     * 
     * @param array $config 配置参数
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'dimensions' => 2,
            'coordinate_system' => 'cartesian',
            'projection_type' => 'orthogonal',
            'lighting_enabled' => false
        ], $config);
        
        // 在实际实现中，这里会从容器获取日志组件
        if (class_exists('\AlingAi\Core\Container')) {
            $this->container = \AlingAi\Core\Container::getInstance();
            $this->logger = $this->container->get('logger');
        }
    }
    
    /**
     * 创建线图
     * 
     * @param string $id 可视化ID
     * @param array $data 数据
     * @param array $options 选项
     * @return array 可视化数据
     */
    public function createLineChart(string $id, array $data, array $options = []): array
    {
        $chartOptions = array_merge([
            'title' => $id,
            'x_axis_label' => 'Time',
            'y_axis_label' => 'Value',
            'show_legend' => true,
            'line_colors' => ['#1f77b4', '#ff7f0e', '#2ca02c', '#d62728', '#9467bd']
        ], $options);
        
        $visualization = [
            'id' => $id,
            'type' => 'line_chart',
            'data' => $data,
            'options' => $chartOptions,
            'created_at' => time()
        ];
        
        $this->visualizations[$id] = $visualization;
        
        return $visualization;
    }
    
    /**
     * 创建柱状图
     * 
     * @param string $id 可视化ID
     * @param array $data 数据
     * @param array $options 选项
     * @return array 可视化数据
     */
    public function createBarChart(string $id, array $data, array $options = []): array
    {
        $chartOptions = array_merge([
            'title' => $id,
            'x_axis_label' => 'Category',
            'y_axis_label' => 'Value',
            'show_legend' => true,
            'bar_colors' => ['#1f77b4', '#ff7f0e', '#2ca02c', '#d62728', '#9467bd']
        ], $options);
        
        $visualization = [
            'id' => $id,
            'type' => 'bar_chart',
            'data' => $data,
            'options' => $chartOptions,
            'created_at' => time()
        ];
        
        $this->visualizations[$id] = $visualization;
        
        return $visualization;
    }
    
    /**
     * 创建饼图
     * 
     * @param string $id 可视化ID
     * @param array $data 数据
     * @param array $options 选项
     * @return array 可视化数据
     */
    public function createPieChart(string $id, array $data, array $options = []): array
    {
        $chartOptions = array_merge([
            'title' => $id,
            'show_legend' => true,
            'slice_colors' => ['#1f77b4', '#ff7f0e', '#2ca02c', '#d62728', '#9467bd']
        ], $options);
        
        $visualization = [
            'id' => $id,
            'type' => 'pie_chart',
            'data' => $data,
            'options' => $chartOptions,
            'created_at' => time()
        ];
        
        $this->visualizations[$id] = $visualization;
        
        return $visualization;
    }
    
    /**
     * 创建热图
     * 
     * @param string $id 可视化ID
     * @param array $data 数据
     * @param array $options 选项
     * @return array 可视化数据
     */
    public function createHeatMap(string $id, array $data, array $options = []): array
    {
        $chartOptions = array_merge([
            'title' => $id,
            'x_axis_label' => 'X',
            'y_axis_label' => 'Y',
            'color_scale' => 'viridis'
        ], $options);
        
        $visualization = [
            'id' => $id,
            'type' => 'heat_map',
            'data' => $data,
            'options' => $chartOptions,
            'created_at' => time()
        ];
        
        $this->visualizations[$id] = $visualization;
        
        return $visualization;
    }
    
    /**
     * 创建预测图
     * 
     * @param string $id 可视化ID
     * @param array $data 数据
     * @param array $options 选项
     * @return array 可视化数据
     */
    public function createForecastChart(string $id, array $data, array $options = []): array
    {
        $chartOptions = array_merge([
            'title' => $id,
            'x_axis_label' => 'Time',
            'y_axis_label' => 'Value',
            'show_legend' => true,
            'show_confidence_interval' => true,
            'line_colors' => ['#1f77b4', '#ff7f0e', '#2ca02c', '#d62728', '#9467bd']
        ], $options);
        
        $visualization = [
            'id' => $id,
            'type' => 'forecast_chart',
            'data' => $data,
            'options' => $chartOptions,
            'created_at' => time()
        ];
        
        $this->visualizations[$id] = $visualization;
        
        return $visualization;
    }
    
    /**
     * 创建3D场景
     * 
     * @param string $id 可视化ID
     * @param array $data 数据
     * @param array $options 选项
     * @return array 可视化数据
     */
    public function create3DScene(string $id, array $data, array $options = []): array
    {
        // 确保已启用3D
        if ($this->config['dimensions'] < 3) {
            throw new \RuntimeException("3D visualization is not enabled. Current dimensions: " . $this->config['dimensions']);
        }
        
        $sceneOptions = array_merge([
            'title' => $id,
            'camera_position' => [0, 0, 100],
            'lighting_type' => 'ambient',
            'background_color' => '#000000'
        ], $options);
        
        $visualization = [
            'id' => $id,
            'type' => '3d_scene',
            'data' => $data,
            'options' => $sceneOptions,
            'created_at' => time()
        ];
        
        $this->visualizations[$id] = $visualization;
        
        return $visualization;
    }
    
    /**
     * 获取可视化
     * 
     * @param string $id 可视化ID
     * @return array|null 可视化数据
     */
    public function getVisualization(string $id): ?array
    {
        return $this->visualizations[$id] ?? null;
    }
    
    /**
     * 获取所有可视化
     * 
     * @return array 所有可视化数据
     */
    public function getAllVisualizations(): array
    {
        return $this->visualizations;
    }
    
    /**
     * 获取配置
     * 
     * @return array 配置
     */
    public function getConfig(): array
    {
        return $this->config;
    }
    
    /**
     * 设置配置
     * 
     * @param array $config 配置
     */
    public function setConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config);
        
        // 如果维度改变，可能需要重新生成可视化
        if (isset($config['dimensions']) && $config['dimensions'] !== $this->config['dimensions']) {
            // 在实际实现中，这里会处理维度变更
        }
    }
    
    /**
     * 创建图表
     * 
     * @param string $id 可视化ID
     * @param array $data 数据
     * @param array $options 选项
     * @return array 可视化数据
     */
    public function createChart(string $id, array $data, array $options = []): array
    {
        $chartOptions = array_merge([
            'title' => $id,
            'chart_type' => 'line',
            'x_axis_label' => 'X',
            'y_axis_label' => 'Y',
            'show_legend' => true
        ], $options);
        
        $visualization = [
            'id' => $id,
            'type' => 'chart',
            'data' => $data,
            'options' => $chartOptions,
            'created_at' => time()
        ];
        
        $this->visualizations[$id] = $visualization;
        
        return $visualization;
    }
    
    /**
     * 创建仪表盘
     * 
     * @param string $id 可视化ID
     * @param array $data 数据
     * @param array $options 选项
     * @return array 可视化数据
     */
    public function createGauge(string $id, array $data, array $options = []): array
    {
        $gaugeOptions = array_merge([
            'title' => $id,
            'min' => 0,
            'max' => 100,
            'units' => '',
            'threshold_colors' => ['#009900', '#FFFF00', '#FF0000'],
            'thresholds' => [30, 70]
        ], $options);
        
        $visualization = [
            'id' => $id,
            'type' => 'gauge',
            'data' => $data,
            'options' => $gaugeOptions,
            'created_at' => time()
        ];
        
        $this->visualizations[$id] = $visualization;
        
        return $visualization;
    }
    
    /**
     * 创建网络图
     * 
     * @param string $id 可视化ID
     * @param array $data 数据
     * @param array $options 选项
     * @return array 可视化数据
     */
    public function createGraph(string $id, array $data, array $options = []): array
    {
        $graphOptions = array_merge([
            'title' => $id,
            'directed' => true,
            'layout' => 'force',
            'node_size' => 10,
            'node_color' => '#1f77b4',
            'link_color' => '#999999',
            'show_labels' => true
        ], $options);
        
        $visualization = [
            'id' => $id,
            'type' => 'graph',
            'data' => $data,
            'options' => $graphOptions,
            'created_at' => time()
        ];
        
        $this->visualizations[$id] = $visualization;
        
        return $visualization;
    }
    
    /**
     * 创建时间线
     * 
     * @param string $id 可视化ID
     * @param array $data 数据
     * @param array $options 选项
     * @return array 可视化数据
     */
    public function createTimeline(string $id, array $data, array $options = []): array
    {
        $timelineOptions = array_merge([
            'title' => $id,
            'scale' => 'time',
            'show_points' => true,
            'show_intervals' => true,
            'interval_colors' => ['#1f77b4', '#ff7f0e', '#2ca02c', '#d62728', '#9467bd']
        ], $options);
        
        $visualization = [
            'id' => $id,
            'type' => 'timeline',
            'data' => $data,
            'options' => $timelineOptions,
            'created_at' => time()
        ];
        
        $this->visualizations[$id] = $visualization;
        
        return $visualization;
    }
    
    /**
     * 创建树状图
     * 
     * @param string $id 可视化ID
     * @param array $data 数据
     * @param array $options 选项
     * @return array 可视化数据
     */
    public function createTree(string $id, array $data, array $options = []): array
    {
        $treeOptions = array_merge([
            'title' => $id,
            'orientation' => 'vertical',
            'node_size' => 10,
            'node_color' => '#1f77b4',
            'link_color' => '#999999',
            'show_labels' => true
        ], $options);
        
        $visualization = [
            'id' => $id,
            'type' => 'tree',
            'data' => $data,
            'options' => $treeOptions,
            'created_at' => time()
        ];
        
        $this->visualizations[$id] = $visualization;
        
        return $visualization;
    }
    
    /**
     * 创建地图
     * 
     * @param string $id 可视化ID
     * @param array $data 数据
     * @param array $options 选项
     * @return array 可视化数据
     */
    public function createMap(string $id, array $data, array $options = []): array
    {
        $mapOptions = array_merge([
            'title' => $id,
            'map_type' => 'world',
            'zoom' => 1,
            'center' => [0, 0],
            'marker_color' => '#1f77b4',
            'area_colors' => ['#deebf7', '#9ecae1', '#3182bd']
        ], $options);
        
        $visualization = [
            'id' => $id,
            'type' => 'map',
            'data' => $data,
            'options' => $mapOptions,
            'created_at' => time()
        ];
        
        $this->visualizations[$id] = $visualization;
        
        return $visualization;
    }
    
    /**
     * 创建表格
     * 
     * @param string $id 可视化ID
     * @param array $data 数据
     * @param array $options 选项
     * @return array 可视化数据
     */
    public function createTable(string $id, array $data, array $options = []): array
    {
        $tableOptions = array_merge([
            'title' => $id,
            'show_header' => true,
            'striped' => true,
            'bordered' => true,
            'sortable' => true,
            'filterable' => true,
            'pagination' => true,
            'page_size' => 10
        ], $options);
        
        $visualization = [
            'id' => $id,
            'type' => 'table',
            'data' => $data,
            'options' => $tableOptions,
            'created_at' => time()
        ];
        
        $this->visualizations[$id] = $visualization;
        
        return $visualization;
    }
} 