<?php
/**
 * 文件名：KnowledgeGraphEngine.php
 * 功能描述：知识图谱引擎 - 实现知识图谱的核心功能
 * 创建时间：2025-01-XX
 * 最后修改：2025-01-XX
 * 版本：1.0.0
 * 
 * @package AlingAi\AI\Engines\KnowledgeGraph
 * @author AlingAi Team
 * @license MIT
 */

declare(strict_types=1);

namespace AlingAi\AI\Engines\KnowledgeGraph;

use Exception;
use InvalidArgumentException;
use AlingAi\Core\Logger\LoggerInterface;
use AlingAi\Utils\CacheManager;
use AlingAi\Utils\PerformanceMonitor;

/**
 * 知识图谱引擎
 * 
 * 提供知识图谱的构建、查询、推理和维护功能
 * 支持多种知识表示和推理方法
 */
class KnowledgeGraphEngine
{
    private LoggerInterface $logger;
    private CacheManager $cache;
    private PerformanceMonitor $monitor;
    
    // 配置参数
    private array $config;
    
    // 组件实例
    private $graphStore;
    private $entityExtractor;
    private $relationExtractor;
    private $reasoningEngine;
    private $queryProcessor;
    
    /**
     * 构造函数
     */
    public function __construct(
        LoggerInterface $logger,
        CacheManager $cache,
        PerformanceMonitor $monitor,
        array $config = []
    ) {
        $this->logger = $logger;
        $this->cache = $cache;
        $this->monitor = $monitor;
        $this->config = array_merge($this->getDefaultConfig(), $config);
        
        $this->initializeComponents();
    }
    
    /**
     * 获取默认配置
     */
    private function getDefaultConfig(): array
    {
        return [
            'graph_store_type' => 'memory', // memory, neo4j, rdf
            'cache_enabled' => true,
            'cache_ttl' => 3600,
            'max_entities' => 1000000,
            'max_relations' => 5000000,
            'default_confidence_threshold' => 0.7,
            'enable_reasoning' => true,
            'reasoning_depth' => 3,
            'enable_entity_linking' => true,
            'enable_relation_prediction' => true,
            'performance_monitoring' => true,
            'ontology_path' => __DIR__ . '/ontology',
            'data_path' => __DIR__ . '/data',
            'database' => [
                'host' => 'localhost',
                'port' => 7687,
                'username' => 'neo4j',
                'password' => 'password',
                'database' => 'knowledge_graph'
            ]
        ];
    }
    
    /**
     * 初始化组件
     */
    private function initializeComponents(): void
    {
        try {
            $this->graphStore = $this->createGraphStore();
            $this->entityExtractor = $this->createEntityExtractor();
            $this->relationExtractor = $this->createRelationExtractor();
            $this->reasoningEngine = $this->createReasoningEngine();
            $this->queryProcessor = $this->createQueryProcessor();
            
            $this->logger->info('KnowledgeGraphEngine components initialized successfully');
        } catch (Exception $e) {
            $this->logger->error('Failed to initialize KnowledgeGraphEngine components: ' . $e->getMessage());
            throw new Exception('组件初始化失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 创建图存储
     */
    private function createGraphStore()
    {
        $storeType = $this->config['graph_store_type'];
        
        switch ($storeType) {
            case 'neo4j':
                return new Neo4jGraphStore($this->config['database']);
            case 'rdf':
                return new RDFGraphStore($this->config['database']);
            case 'memory':
            default:
                return new MemoryGraphStore();
        }
    }
    
    /**
     * 创建实体提取器
     */
    private function createEntityExtractor()
    {
        return new EntityExtractor($this->config);
    }
    
    /**
     * 创建关系提取器
     */
    private function createRelationExtractor()
    {
        return new RelationExtractor($this->config);
    }
    
    /**
     * 创建推理引擎
     */
    private function createReasoningEngine()
    {
        return new ReasoningEngine($this->config);
    }
    
    /**
     * 创建查询处理器
     */
    private function createQueryProcessor()
    {
        return new QueryProcessor($this->config);
    }
    
    /**
     * 从文本构建知识图谱
     * 
     * @param string $text 输入文本
     * @param array $options 构建选项
     * @return array 构建结果
     * @throws InvalidArgumentException
     */
    public function buildFromText(string $text, array $options = []): array
    {
        $this->monitor->start('build_from_text');
        
        try {
            // 验证文本
            if (empty($text)) {
                throw new InvalidArgumentException('文本不能为空');
            }
            
            // 处理选项
            $options = array_merge($this->getDefaultBuildOptions(), $options);
            
            // 提取实体
            $entities = $this->entityExtractor->extract($text, $options);
            
            // 提取关系
            $relations = $this->relationExtractor->extract($text, $entities, $options);
            
            // 添加到图存储
            $addedEntities = $this->addEntities($entities, $options);
            $addedRelations = $this->addRelations($relations, $options);
            
            // 执行推理
            $inferredRelations = [];
            if ($options['enable_reasoning']) {
                $inferredRelations = $this->reason($addedEntities, $addedRelations, $options);
            }
            
            $result = [
                'entities' => $addedEntities,
                'relations' => $addedRelations,
                'inferred_relations' => $inferredRelations,
                'processing_time' => 0
            ];
            
            $this->monitor->end('build_from_text');
            $result['processing_time'] = $this->monitor->getDuration('build_from_text');
            
            $this->logger->info('Knowledge graph built from text', [
                'text_length' => strlen($text),
                'entity_count' => count($addedEntities),
                'relation_count' => count($addedRelations),
                'inferred_relation_count' => count($inferredRelations),
                'processing_time' => $result['processing_time']
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            $this->monitor->end('build_from_text');
            $this->logger->error('Knowledge graph building failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * 获取默认构建选项
     */
    private function getDefaultBuildOptions(): array
    {
        return [
            'confidence_threshold' => $this->config['default_confidence_threshold'],
            'enable_entity_linking' => $this->config['enable_entity_linking'],
            'enable_relation_prediction' => $this->config['enable_relation_prediction'],
            'enable_reasoning' => $this->config['enable_reasoning'],
            'reasoning_depth' => $this->config['reasoning_depth'],
            'language' => 'en',
            'domain' => 'general',
            'context' => []
        ];
    }
    
    /**
     * 添加实体
     */
    private function addEntities(array $entities, array $options): array
    {
        $addedEntities = [];
        
        foreach ($entities as $entity) {
            if ($entity['confidence'] >= $options['confidence_threshold']) {
                $entityId = $this->graphStore->addEntity($entity);
                $entity['id'] = $entityId;
                $addedEntities[] = $entity;
            }
        }
        
        return $addedEntities;
    }
    
    /**
     * 添加关系
     */
    private function addRelations(array $relations, array $options): array
    {
        $addedRelations = [];
        
        foreach ($relations as $relation) {
            if ($relation['confidence'] >= $options['confidence_threshold']) {
                $relationId = $this->graphStore->addRelation($relation);
                $relation['id'] = $relationId;
                $addedRelations[] = $relation;
            }
        }
        
        return $addedRelations;
    }
    
    /**
     * 执行推理
     */
    private function reason(array $entities, array $relations, array $options): array
    {
        $this->monitor->start('reasoning');
        
        // 执行推理
        $inferredRelations = $this->reasoningEngine->infer(
            $entities,
            $relations,
            $this->graphStore,
            $options['reasoning_depth']
        );
        
        // 添加推理关系到图存储
        $addedInferredRelations = [];
        foreach ($inferredRelations as $relation) {
            if ($relation['confidence'] >= $options['confidence_threshold']) {
                $relationId = $this->graphStore->addRelation($relation, true);
                $relation['id'] = $relationId;
                $addedInferredRelations[] = $relation;
            }
        }
        
        $this->monitor->end('reasoning');
        
        return $addedInferredRelations;
    }
    
    /**
     * 查询知识图谱
     * 
     * @param string $query 查询字符串
     * @param string $queryType 查询类型 (natural, sparql, cypher)
     * @param array $options 查询选项
     * @return array 查询结果
     */
    public function query(string $query, string $queryType = 'natural', array $options = []): array
    {
        $this->monitor->start('query');
        
        try {
            // 处理选项
            $options = array_merge($this->getDefaultQueryOptions(), $options);
            
            // 处理查询
            $processedQuery = $this->queryProcessor->process($query, $queryType);
            
            // 执行查询
            $queryResults = $this->graphStore->query($processedQuery, $queryType);
            
            // 格式化结果
            $formattedResults = $this->formatQueryResults($queryResults, $options);
            
            $result = [
                'query' => $query,
                'query_type' => $queryType,
                'results' => $formattedResults,
                'result_count' => count($formattedResults),
                'processing_time' => 0
            ];
            
            $this->monitor->end('query');
            $result['processing_time'] = $this->monitor->getDuration('query');
            
            $this->logger->info('Knowledge graph query executed', [
                'query' => $query,
                'query_type' => $queryType,
                'result_count' => count($formattedResults),
                'processing_time' => $result['processing_time']
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            $this->monitor->end('query');
            $this->logger->error('Knowledge graph query failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * 获取默认查询选项
     */
    private function getDefaultQueryOptions(): array
    {
        return [
            'limit' => 100,
            'offset' => 0,
            'format' => 'json',
            'include_metadata' => true,
            'include_confidence' => true,
            'min_confidence' => 0.5,
            'reasoning' => true
        ];
    }
    
    /**
     * 格式化查询结果
     */
    private function formatQueryResults(array $results, array $options): array
    {
        $formattedResults = [];
        
        foreach ($results as $result) {
            // 根据选项过滤结果
            if (isset($result['confidence']) && $result['confidence'] < $options['min_confidence']) {
                continue;
            }
            
            // 格式化结果
            $formattedResult = $result;
            
            if (!$options['include_metadata']) {
                unset($formattedResult['metadata']);
            }
            
            if (!$options['include_confidence']) {
                unset($formattedResult['confidence']);
            }
            
            $formattedResults[] = $formattedResult;
        }
        
        // 应用分页
        $limit = $options['limit'];
        $offset = $options['offset'];
        
        return array_slice($formattedResults, $offset, $limit);
    }
    
    /**
     * 添加实体
     * 
     * @param array $entity 实体数据
     * @return string 实体ID
     */
    public function addEntity(array $entity): string
    {
        $this->validateEntity($entity);
        return $this->graphStore->addEntity($entity);
    }
    
    /**
     * 添加关系
     * 
     * @param array $relation 关系数据
     * @return string 关系ID
     */
    public function addRelation(array $relation): string
    {
        $this->validateRelation($relation);
        return $this->graphStore->addRelation($relation);
    }
    
    /**
     * 验证实体
     */
    private function validateEntity(array $entity): void
    {
        if (!isset($entity['type'])) {
            throw new InvalidArgumentException('实体必须指定类型');
        }
        
        if (!isset($entity['name'])) {
            throw new InvalidArgumentException('实体必须指定名称');
        }
    }
    
    /**
     * 验证关系
     */
    private function validateRelation(array $relation): void
    {
        if (!isset($relation['type'])) {
            throw new InvalidArgumentException('关系必须指定类型');
        }
        
        if (!isset($relation['source'])) {
            throw new InvalidArgumentException('关系必须指定源实体');
        }
        
        if (!isset($relation['target'])) {
            throw new InvalidArgumentException('关系必须指定目标实体');
        }
    }
    
    /**
     * 获取实体
     * 
     * @param string $entityId 实体ID
     * @return array|null 实体数据
     */
    public function getEntity(string $entityId): ?array
    {
        return $this->graphStore->getEntity($entityId);
    }
    
    /**
     * 获取关系
     * 
     * @param string $relationId 关系ID
     * @return array|null 关系数据
     */
    public function getRelation(string $relationId): ?array
    {
        return $this->graphStore->getRelation($relationId);
    }
    
    /**
     * 更新实体
     * 
     * @param string $entityId 实体ID
     * @param array $data 更新数据
     * @return bool 是否成功
     */
    public function updateEntity(string $entityId, array $data): bool
    {
        return $this->graphStore->updateEntity($entityId, $data);
    }
    
    /**
     * 更新关系
     * 
     * @param string $relationId 关系ID
     * @param array $data 更新数据
     * @return bool 是否成功
     */
    public function updateRelation(string $relationId, array $data): bool
    {
        return $this->graphStore->updateRelation($relationId, $data);
    }
    
    /**
     * 删除实体
     * 
     * @param string $entityId 实体ID
     * @return bool 是否成功
     */
    public function deleteEntity(string $entityId): bool
    {
        return $this->graphStore->deleteEntity($entityId);
    }
    
    /**
     * 删除关系
     * 
     * @param string $relationId 关系ID
     * @return bool 是否成功
     */
    public function deleteRelation(string $relationId): bool
    {
        return $this->graphStore->deleteRelation($relationId);
    }
    
    /**
     * 获取实体关系
     * 
     * @param string $entityId 实体ID
     * @param string $direction 方向 (outgoing, incoming, both)
     * @return array 关系列表
     */
    public function getEntityRelations(string $entityId, string $direction = 'both'): array
    {
        return $this->graphStore->getEntityRelations($entityId, $direction);
    }
    
    /**
     * 导入知识图谱
     * 
     * @param string $filePath 文件路径
     * @param string $format 文件格式 (json, rdf, csv)
     * @return array 导入结果
     */
    public function importGraph(string $filePath, string $format = 'json'): array
    {
        $this->monitor->start('import_graph');
        
        try {
            if (!file_exists($filePath)) {
                throw new InvalidArgumentException('文件不存在: ' . $filePath);
            }
            
            $importResult = $this->graphStore->importGraph($filePath, $format);
            
            $this->monitor->end('import_graph');
            $importResult['processing_time'] = $this->monitor->getDuration('import_graph');
            
            $this->logger->info('Knowledge graph imported', [
                'file_path' => $filePath,
                'format' => $format,
                'entity_count' => $importResult['entity_count'],
                'relation_count' => $importResult['relation_count'],
                'processing_time' => $importResult['processing_time']
            ]);
            
            return $importResult;
            
        } catch (Exception $e) {
            $this->monitor->end('import_graph');
            $this->logger->error('Knowledge graph import failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * 导出知识图谱
     * 
     * @param string $filePath 文件路径
     * @param string $format 文件格式 (json, rdf, csv)
     * @return array 导出结果
     */
    public function exportGraph(string $filePath, string $format = 'json'): array
    {
        $this->monitor->start('export_graph');
        
        try {
            $exportResult = $this->graphStore->exportGraph($filePath, $format);
            
            $this->monitor->end('export_graph');
            $exportResult['processing_time'] = $this->monitor->getDuration('export_graph');
            
            $this->logger->info('Knowledge graph exported', [
                'file_path' => $filePath,
                'format' => $format,
                'entity_count' => $exportResult['entity_count'],
                'relation_count' => $exportResult['relation_count'],
                'processing_time' => $exportResult['processing_time']
            ]);
            
            return $exportResult;
            
        } catch (Exception $e) {
            $this->monitor->end('export_graph');
            $this->logger->error('Knowledge graph export failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * 获取统计信息
     * 
     * @return array 统计信息
     */
    public function getStatistics(): array
    {
        return $this->graphStore->getStatistics();
    }
    
    /**
     * 清空知识图谱
     * 
     * @return bool 是否成功
     */
    public function clearGraph(): bool
    {
        return $this->graphStore->clearGraph();
    }
    
    /**
     * 获取性能统计
     */
    public function getPerformanceStats(): array
    {
        return $this->monitor->getStats();
    }
    
    /**
     * 清理缓存
     */
    public function clearCache(): void
    {
        if ($this->config['cache_enabled']) {
            $this->cache->clear();
            $this->logger->info('KnowledgeGraphEngine cache cleared');
        }
    }
}
