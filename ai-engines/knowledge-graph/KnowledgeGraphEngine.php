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
            "graph_store_type" => "memory", // memory, neo4j, rdf
            "cache_enabled" => true,
            "cache_ttl" => 3600,
            "max_entities" => 1000000,
            "max_relations" => 5000000,
            "default_confidence_threshold" => 0.7,
            "enable_reasoning" => true,
            "reasoning_depth" => 3,
            "enable_entity_linking" => true,
            "enable_relation_prediction" => true,
            "performance_monitoring" => true,
            "ontology_path" => __DIR__ . "/ontology",
            "data_path" => __DIR__ . "/data",
            "database" => [
                "host" => "localhost",
                "port" => 7687,
                "username" => "neo4j",
                "password" => "password",
                "database" => "knowledge_graph"
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
            
            $this->logger->info("KnowledgeGraphEngine components initialized successfully");
        } catch (Exception $e) {
            $this->logger->error("Failed to initialize KnowledgeGraphEngine components: " . $e->getMessage());
            throw new Exception("组件初始化失败: " . $e->getMessage());
        }
    }
    
    /**
     * 创建图存储
     */
    private function createGraphStore()
    {
        $storeType = $this->config["graph_store_type"];
        
        switch ($storeType) {
            case "neo4j":
                return new Neo4jGraphStore($this->config["database"]);
            case "rdf":
                return new RDFGraphStore($this->config["database"]);
            case "memory":
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
        $this->monitor->start("build_from_text");
        
        try {
            // 验证文本
            if (empty($text)) {
                throw new InvalidArgumentException("文本不能为空");
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
            if ($options["enable_reasoning"]) {
                $inferredRelations = $this->reason($addedEntities, $addedRelations, $options);
            }
            
            $result = [
                "entities" => $addedEntities,
                "relations" => $addedRelations,
                "inferred_relations" => $inferredRelations,
                "processing_time" => 0
            ];
            
            $this->monitor->end("build_from_text");
            $result["processing_time"] = $this->monitor->getDuration("build_from_text");
            
            $this->logger->info("Knowledge graph built from text", [
                "text_length" => strlen($text),
                "entity_count" => count($addedEntities),
                "relation_count" => count($addedRelations),
                "inferred_relation_count" => count($inferredRelations),
                "processing_time" => $result["processing_time"]
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            $this->monitor->end("build_from_text");
            $this->logger->error("Knowledge graph building failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * 获取默认构建选项
     */
    private function getDefaultBuildOptions(): array
    {
        return [
            "confidence_threshold" => $this->config["default_confidence_threshold"], 
            "enable_entity_linking" => $this->config["enable_entity_linking"], 
            "enable_relation_prediction" => $this->config["enable_relation_prediction"], 
            "enable_reasoning" => $this->config["enable_reasoning"], 
            "reasoning_depth" => $this->config["reasoning_depth"], 
            "language" => "en",
            "domain" => "general",
            "context" => []
        ];
    }
    
    /**
     * 添加实体
     */
    private function addEntities(array $entities, array $options): array
    {
        $addedEntities = [];
        
        foreach ($entities as $entity) {
            if ($entity["confidence"] >= $options["confidence_threshold"]) {

                try {
                    $success = $this->graphStore->addEntity($entity);
                    if ($success) {
                        $addedEntities[] = $entity;
                    }
                } catch (Exception $e) {
                    $this->logger->warning("Failed to add entity: " . $e->getMessage(), [
                        "entity" => $entity
                    ]);
                }
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
            if ($relation["confidence"] >= $options["confidence_threshold"]) {
                try {
                    $success = $this->graphStore->addRelation($relation);
                    if ($success) {
                        $addedRelations[] = $relation;
                    }
                } catch (Exception $e) {
                    $this->logger->warning("Failed to add relation: " . $e->getMessage(), [
                        "relation" => $relation
                    ]);
                }
            }
        }
        
        return $addedRelations;
    }
    
    /**
     * 执行推理
     */
    private function reason(array $entities, array $relations, array $options): array
    {
        if (!$this->config["enable_reasoning"]) {
            return [];
        }
        
        $this->monitor->start("reasoning");
        
        $inferredRelations = $this->reasoningEngine->infer($entities, $relations, [
            "depth" => $options["reasoning_depth"]
        ]);
        
        // 添加推理出的关系
        $addedInferredRelations = [];
        foreach ($inferredRelations as $relation) {
            try {
                $success = $this->graphStore->addRelation($relation);
                if ($success) {
                    $addedInferredRelations[] = $relation;
                }
            } catch (Exception $e) {
                $this->logger->warning("Failed to add inferred relation: " . $e->getMessage(), [
                    "relation" => $relation
                ]);
            }
        }
        
        $this->monitor->end("reasoning");
        
        $this->logger->info("Reasoning completed", [
            "inferred_relations_count" => count($inferredRelations),
            "added_inferred_relations_count" => count($addedInferredRelations),
            "reasoning_time" => $this->monitor->getDuration("reasoning")
        ]);
        
        return $addedInferredRelations;
    }
    
    /**
     * 查询知识图谱
     * 
     * @param string $query 查询语句
     * @param array $options 查询选项
     * @return array 查询结果
     */
    public function query(string $query, array $options = []): array
    {
        $this->monitor->start("query");
        
        try {
            // 处理选项
            $options = array_merge($this->getDefaultQueryOptions(), $options);
            
            // 处理查询
            $result = $this->queryProcessor->process($query, $this->graphStore, $options);
            
            $this->monitor->end("query");
            $result["processing_time"] = $this->monitor->getDuration("query");
            
            $this->logger->info("Knowledge graph query completed", [
                "query" => $query,
                "result_count" => count($result["results"]),
                "processing_time" => $result["processing_time"]
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            $this->monitor->end("query");
            $this->logger->error("Knowledge graph query failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * 获取默认查询选项
     */
    private function getDefaultQueryOptions(): array
    {
        return [
            "limit" => 100,
            "offset" => 0,
            "include_metadata" => true,
            "include_confidence" => true,
            "min_confidence" => $this->config["default_confidence_threshold"],
            "format" => "json"
        ];
    }
    
    /**
     * 获取实体
     * 
     * @param string $entityId 实体ID
     * @return array|null 实体数据
     */
    public function getEntity(string $entityId): ?array
    {
        try {
            return $this->graphStore->getEntityById($entityId);
        } catch (Exception $e) {
            $this->logger->error("Failed to get entity: " . $e->getMessage(), [
                "entity_id" => $entityId
            ]);
            return null;
        }
    }
    
    /**
     * 获取关系
     * 
     * @param string $sourceEntityId 源实体ID
     * @param string $targetEntityId 目标实体ID
     * @param string|null $relationType 关系类型
     * @return array|null 关系数据
     */
    public function getRelation(string $sourceEntityId, string $targetEntityId, ?string $relationType = null): ?array
    {
        try {
            return $this->graphStore->getRelationBetween($sourceEntityId, $targetEntityId, $relationType);
        } catch (Exception $e) {
            $this->logger->error("Failed to get relation: " . $e->getMessage(), [
                "source_entity_id" => $sourceEntityId,
                "target_entity_id" => $targetEntityId,
                "relation_type" => $relationType
            ]);
            return null;
        }
    }
    
    /**
     * 获取相关实体
     * 
     * @param string $entityId 实体ID
     * @param string $relationType 关系类型
     * @return array 相关实体列表
     */
    public function getRelatedEntities(string $entityId, string $relationType): array
    {
        try {
            return $this->graphStore->getRelatedEntities($entityId, $relationType);
        } catch (Exception $e) {
            $this->logger->error("Failed to get related entities: " . $e->getMessage(), [
                "entity_id" => $entityId,
                "relation_type" => $relationType
            ]);
            return [];
        }
    }
    
    /**
     * 查询实体
     * 
     * @param array $criteria 查询条件
     * @param int $limit 限制结果数量
     * @param int $offset 结果偏移量
     * @return array 符合条件的实体列表
     */
    public function queryEntities(array $criteria, int $limit = 100, int $offset = 0): array
    {
        try {
            return $this->graphStore->queryEntities($criteria, $limit, $offset);
        } catch (Exception $e) {
            $this->logger->error("Failed to query entities: " . $e->getMessage(), [
                "criteria" => $criteria
            ]);
            return [];
        }
    }
    
    /**
     * 查询关系
     * 
     * @param array $criteria 查询条件
     * @param int $limit 限制结果数量
     * @param int $offset 结果偏移量
     * @return array 符合条件的关系列表
     */
    public function queryRelations(array $criteria, int $limit = 100, int $offset = 0): array
    {
        try {
            return $this->graphStore->queryRelations($criteria, $limit, $offset);
        } catch (Exception $e) {
            $this->logger->error("Failed to query relations: " . $e->getMessage(), [
                "criteria" => $criteria
            ]);
            return [];
        }
    }
    
    /**
     * 更新实体
     * 
     * @param string $entityId 实体ID
     * @param array $data 更新的数据
     * @return bool 是否更新成功
     */
    public function updateEntity(string $entityId, array $data): bool
    {
        try {
            return $this->graphStore->updateEntity($entityId, $data);
        } catch (Exception $e) {
            $this->logger->error("Failed to update entity: " . $e->getMessage(), [
                "entity_id" => $entityId,
                "data" => $data
            ]);
            return false;
        }
    }
    
    /**
     * 删除实体
     * 
     * @param string $entityId 实体ID
     * @return bool 是否删除成功
     */
    public function deleteEntity(string $entityId): bool
    {
        try {
            return $this->graphStore->deleteEntity($entityId);
        } catch (Exception $e) {
            $this->logger->error("Failed to delete entity: " . $e->getMessage(), [
                "entity_id" => $entityId
            ]);
            return false;
        }
    }
    
    /**
     * 获取知识图谱统计信息
     * 
     * @return array 统计信息
     */
    public function getStatistics(): array
    {
        $entities = $this->graphStore->getAllEntities();
        
        $entityTypes = [];
        foreach ($entities as $entity) {
            $type = $entity["type"] ?? "unknown";
            if (!isset($entityTypes[$type])) {
                $entityTypes[$type] = 0;
            }
            $entityTypes[$type]++;
        }
        
        return [
            "entity_count" => count($entities),
            "entity_types" => $entityTypes,
            "relation_count" => count($this->graphStore->queryRelations([], 1000000)),
            "last_updated" => time()
        ];
    }
}
