<?php
/**
 * 文件名：ReasoningEngine.php
 * 功能描述：推理引擎 - 在知识图谱上进行推理
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

/**
 * 推理引擎
 * 
 * 在知识图谱上进行推理，支持多种推理规则和方法
 */
class ReasoningEngine
{
    /**
     * 配置参数
     */
    private array $config;
    
    /**
     * 推理规则集合
     */
    private array $rules = [];
    
    /**
     * 知识图谱存储接口
     */
    private ?GraphStoreInterface $graphStore = null;

    /**
     * 构造函数
     * 
     * @param array $config 配置参数
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->getDefaultConfig(), $config);
        $this->initializeRules();
    }
    
    /**
     * 获取默认配置
     * 
     * @return array 默认配置
     */
    private function getDefaultConfig(): array
    {
        return [
            "max_inference_depth" => 3,  // 最大推理深度
            "confidence_threshold" => 0.5,  // 最低推理置信度阈值
            "enable_rule_learning" => false,  // 是否启用规则学习
            "enable_probabilistic_reasoning" => true,  // 是否启用概率推理
            "max_results" => 100,  // 最大返回结果数量
            "timeout" => 5000  // 推理超时时间（毫秒）
        ];
    }
    
    /**
     * 初始化推理规则
     */
    private function initializeRules(): void
    {
        // 添加传递性规则
        $this->addTransitiveRules();
        
        // 添加对称性规则
        $this->addSymmetricRules();
        
        // 添加逆关系规则
        $this->addInverseRules();
        
        // 添加层次关系规则
        $this->addHierarchicalRules();
        
        // 添加组合规则
        $this->addCompositeRules();
    }

    /**
     * 添加传递性规则
     */
    private function addTransitiveRules(): void
    {
        // 位置传递性: A位于B，B位于C => A位于C
        $this->rules[] = [
            "name" => "location_transitivity",
            "type" => "transitive",
            "relation" => "LocatedIn",
            "confidence_factor" => 0.9
        ];
        
        // 部分传递性: A是B的一部分，B是C的一部分 => A是C的一部分
        $this->rules[] = [
            "name" => "part_of_transitivity",
            "type" => "transitive",
            "relation" => "PartOf",
            "confidence_factor" => 0.9
        ];
        
        // 包含传递性: A包含B，B包含C => A包含C
        $this->rules[] = [
            "name" => "contains_transitivity",
            "type" => "transitive",
            "relation" => "Contains",
            "confidence_factor" => 0.9
        ];
    }
    
    /**
     * 添加对称性规则
     */
    private function addSymmetricRules(): void
    {
        // 配偶关系对称性: A是B的配偶 => B是A的配偶
        $this->rules[] = [
            "name" => "spouse_symmetry",
            "type" => "symmetric",
            "relation" => "Spouse",
            "confidence_factor" => 1.0
        ];
        
        // 兄弟姐妹关系对称性: A是B的兄弟姐妹 => B是A的兄弟姐妹
        $this->rules[] = [
            "name" => "sibling_symmetry",
            "type" => "symmetric",
            "relation" => "Sibling",
            "confidence_factor" => 1.0
        ];
    }
    
    /**
     * 添加逆关系规则
     */
    private function addInverseRules(): void
    {
        // 父母-子女逆关系: A是B的父母 => B是A的子女
        $this->rules[] = [
            "name" => "parent_child_inverse",
            "type" => "inverse",
            "relation1" => "Parent",
            "relation2" => "Child",
            "confidence_factor" => 1.0
        ];
        
        // 拥有-被拥有逆关系: A拥有B => B被A拥有
        $this->rules[] = [
            "name" => "owns_owned_by_inverse",
            "type" => "inverse",
            "relation1" => "Owns",
            "relation2" => "OwnedBy",
            "confidence_factor" => 1.0
        ];
        
        // 创建-被创建逆关系: A创建了B => B被A创建
        $this->rules[] = [
            "name" => "created_created_by_inverse",
            "type" => "inverse",
            "relation1" => "Created",
            "relation2" => "CreatedBy",
            "confidence_factor" => 1.0
        ];
    }

    /**
     * 添加层次关系规则
     */
    private function addHierarchicalRules(): void
    {
        // 组织层次规则: A是B的一部分，B是组织 => A是组织
        $this->rules[] = [
            "name" => "organization_hierarchy",
            "type" => "hierarchical",
            "relation" => "PartOf",
            "source_type" => null,
            "target_type" => "Organization",
            "inferred_type" => "Organization",
            "confidence_factor" => 0.8
        ];
        
        // 地点层次规则: A位于B，B是地点 => A是地点
        $this->rules[] = [
            "name" => "location_hierarchy",
            "type" => "hierarchical",
            "relation" => "LocatedIn",
            "source_type" => null,
            "target_type" => "Location",
            "inferred_type" => "Location",
            "confidence_factor" => 0.8
        ];
    }
    
    /**
     * 添加组合规则
     */
    private function addCompositeRules(): void
    {
        // 同事关系: A为C工作，B为C工作 => A和B是同事
        $this->rules[] = [
            "name" => "colleague_relation",
            "type" => "composite",
            "relation1" => "WorksFor",
            "relation2" => "WorksFor",
            "inferred_relation" => "Colleague",
            "pattern" => "common_target",
            "confidence_factor" => 0.7
        ];
        
        // 祖父母关系: A是B的父母，B是C的父母 => A是C的祖父母
        $this->rules[] = [
            "name" => "grandparent_relation",
            "type" => "composite",
            "relation1" => "Parent",
            "relation2" => "Parent",
            "inferred_relation" => "Grandparent",
            "pattern" => "chain",
            "confidence_factor" => 0.9
        ];
    }

    /**
     * 执行推理
     * 
     * @param array $entities 实体集合
     * @param array $relations 关系集合
     * @param array $options 推理选项
     * @return array 推理结果
     * @throws Exception 如果推理过程中出现错误
     */
    public function infer(array $entities, array $relations, array $options = []): array
    {
        // 合并选项
        $options = array_merge([
            "depth" => $this->config["max_inference_depth"],
            "confidence_threshold" => $this->config["confidence_threshold"],
            "max_results" => $this->config["max_results"]
        ], $options);
        
        // 初始化结果
        $inferredRelations = [];
        $startTime = microtime(true);
        
        try {
            // 应用传递性规则
            $transitiveRelations = $this->applyTransitiveRules($entities, $relations, $options);
            $inferredRelations = array_merge($inferredRelations, $transitiveRelations);
            
            // 应用对称性规则
            $symmetricRelations = $this->applySymmetricRules($entities, $relations, $options);
            $inferredRelations = array_merge($inferredRelations, $symmetricRelations);
            
            // 应用逆关系规则
            $inverseRelations = $this->applyInverseRules($entities, $relations, $options);
            $inferredRelations = array_merge($inferredRelations, $inverseRelations);
            
            // 应用层次关系规则
            $hierarchicalRelations = $this->applyHierarchicalRules($entities, $relations, $options);
            $inferredRelations = array_merge($inferredRelations, $hierarchicalRelations);
            
            // 应用组合规则
            $compositeRelations = $this->applyCompositeRules($entities, $relations, $options);
            $inferredRelations = array_merge($inferredRelations, $compositeRelations);
            
            // 去重并限制结果数量
            $inferredRelations = $this->deduplicateRelations($inferredRelations);
            $inferredRelations = array_slice($inferredRelations, 0, $options["max_results"]);
            
            // 添加元数据
            $endTime = microtime(true);
            $metadata = [
                "inference_time" => round(($endTime - $startTime) * 1000), // 毫秒
                "rule_count" => count($this->rules),
                "input_entity_count" => count($entities),
                "input_relation_count" => count($relations),
                "inferred_relation_count" => count($inferredRelations)
            ];
            
            return [
                "relations" => $inferredRelations,
                "metadata" => $metadata
            ];
            
        } catch (Exception $e) {
            throw new Exception("推理过程中出现错误: " . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * 应用传递性规则
     * 
     * @param array $entities 实体集合
     * @param array $relations 关系集合
     * @param array $options 推理选项
     * @return array 推理出的新关系
     */
    private function applyTransitiveRules(array $entities, array $relations, array $options): array
    {
        $inferredRelations = [];
        
        // 获取传递性规则
        $transitiveRules = array_filter($this->rules, function($rule) {
            return $rule["type"] === "transitive";
        });
        
        foreach ($transitiveRules as $rule) {
            $relationName = $rule["relation"];
            $confidenceFactor = $rule["confidence_factor"];
            
            // 构建关系图
            $graph = [];
            foreach ($relations as $relation) {
                if ($relation["type"] === $relationName) {
                    $sourceId = $relation["source_id"];
                    $targetId = $relation["target_id"];
                    
                    if (!isset($graph[$sourceId])) {
                        $graph[$sourceId] = [];
                    }
                    
                    $graph[$sourceId][] = [
                        "target_id" => $targetId,
                        "confidence" => $relation["confidence"] ?? 1.0
                    ];
                }
            }
            
            // 对每个实体应用传递性规则
            foreach ($entities as $entity) {
                $entityId = $entity["id"];
                
                if (isset($graph[$entityId])) {
                    // 执行深度优先搜索
                    $visited = [];
                    $path = [];
                    $this->dfsTransitive($graph, $entityId, $entityId, $visited, $path, $inferredRelations, $confidenceFactor, $options["depth"]);
                }
            }
        }
        
        return $inferredRelations;
    }
    
    /**
     * 传递性规则的深度优先搜索
     * 
     * @param array $graph 关系图
     * @param string $sourceId 源实体ID
     * @param string $currentId 当前实体ID
     * @param array $visited 已访问节点
     * @param array $path 当前路径
     * @param array &$inferredRelations 推理出的关系
     * @param float $confidenceFactor 置信度因子
     * @param int $depth 剩余深度
     */
    private function dfsTransitive(array $graph, string $sourceId, string $currentId, array $visited, array $path, array &$inferredRelations, float $confidenceFactor, int $depth): void
    {
        if ($depth <= 0) {
            return;
        }
        
        $visited[$currentId] = true;
        $path[] = $currentId;
        
        if (isset($graph[$currentId])) {
            foreach ($graph[$currentId] as $edge) {
                $targetId = $edge["target_id"];
                $confidence = $edge["confidence"];
                
                if ($targetId !== $sourceId && !isset($visited[$targetId])) {
                    // 递归搜索
                    $this->dfsTransitive($graph, $sourceId, $targetId, $visited, $path, $inferredRelations, $confidenceFactor, $depth - 1);
                    
                    // 如果路径长度大于1，添加推理关系
                    if (count($path) > 1 && $sourceId !== $targetId) {
                        // 计算传递置信度
                        $transitiveConfidence = $confidence * $confidenceFactor;
                        
                        // 如果置信度高于阈值，添加推理关系
                        if ($transitiveConfidence >= $this->config["confidence_threshold"]) {
                            $inferredRelations[] = [
                                "id" => "inferred_" . md5($sourceId . "_" . $targetId . "_" . time()),
                                "source_id" => $sourceId,
                                "target_id" => $targetId,
                                "type" => $graph[$currentId][0]["type"],
                                "confidence" => $transitiveConfidence,
                                "provenance" => [
                                    "rule" => "transitive",
                                    "path" => $path,
                                    "confidence_factor" => $confidenceFactor
                                ]
                            ];
                        }
                    }
                }
            }
        }
        
        array_pop($path);
        unset($visited[$currentId]);
    }
    
    /**
     * 应用对称性规则
     * 
     * @param array $entities 实体集合
     * @param array $relations 关系集合
     * @param array $options 推理选项
     * @return array 推理出的新关系
     */
    private function applySymmetricRules(array $entities, array $relations, array $options): array
    {
        $inferredRelations = [];
        
        // 获取对称性规则
        $symmetricRules = array_filter($this->rules, function($rule) {
            return $rule["type"] === "symmetric";
        });
        
        foreach ($symmetricRules as $rule) {
            $relationName = $rule["relation"];
            $confidenceFactor = $rule["confidence_factor"];
            
            foreach ($relations as $relation) {
                if ($relation["type"] === $relationName) {
                    $sourceId = $relation["source_id"];
                    $targetId = $relation["target_id"];
                    $confidence = $relation["confidence"] ?? 1.0;
                    
                    // 计算对称置信度
                    $symmetricConfidence = $confidence * $confidenceFactor;
                    
                    // 如果置信度高于阈值，添加推理关系
                    if ($symmetricConfidence >= $this->config["confidence_threshold"]) {
                        $inferredRelations[] = [
                            "id" => "inferred_" . md5($targetId . "_" . $sourceId . "_" . time()),
                            "source_id" => $targetId,
                            "target_id" => $sourceId,
                            "type" => $relationName,
                            "confidence" => $symmetricConfidence,
                            "provenance" => [
                                "rule" => "symmetric",
                                "original_relation_id" => $relation["id"],
                                "confidence_factor" => $confidenceFactor
                            ]
                        ];
                    }
                }
            }
        }
        
        return $inferredRelations;
    }
    
    /**
     * 应用逆关系规则
     * 
     * @param array $entities 实体集合
     * @param array $relations 关系集合
     * @param array $options 推理选项
     * @return array 推理出的新关系
     */
    private function applyInverseRules(array $entities, array $relations, array $options): array
    {
        $inferredRelations = [];
        
        // 获取逆关系规则
        $inverseRules = array_filter($this->rules, function($rule) {
            return $rule["type"] === "inverse";
        });
        
        foreach ($inverseRules as $rule) {
            $relation1 = $rule["relation1"];
            $relation2 = $rule["relation2"];
            $confidenceFactor = $rule["confidence_factor"];
            
            foreach ($relations as $relation) {
                if ($relation["type"] === $relation1) {
                    $sourceId = $relation["source_id"];
                    $targetId = $relation["target_id"];
                    $confidence = $relation["confidence"] ?? 1.0;
                    
                    // 计算逆关系置信度
                    $inverseConfidence = $confidence * $confidenceFactor;
                    
                    // 如果置信度高于阈值，添加推理关系
                    if ($inverseConfidence >= $this->config["confidence_threshold"]) {
                        $inferredRelations[] = [
                            "id" => "inferred_" . md5($targetId . "_" . $sourceId . "_" . time()),
                            "source_id" => $targetId,
                            "target_id" => $sourceId,
                            "type" => $relation2,
                            "confidence" => $inverseConfidence,
                            "provenance" => [
                                "rule" => "inverse",
                                "original_relation_id" => $relation["id"],
                                "original_relation_type" => $relation1,
                                "confidence_factor" => $confidenceFactor
                            ]
                        ];
                    }
                } elseif ($relation["type"] === $relation2) {
                    $sourceId = $relation["source_id"];
                    $targetId = $relation["target_id"];
                    $confidence = $relation["confidence"] ?? 1.0;
                    
                    // 计算逆关系置信度
                    $inverseConfidence = $confidence * $confidenceFactor;
                    
                    // 如果置信度高于阈值，添加推理关系
                    if ($inverseConfidence >= $this->config["confidence_threshold"]) {
                        $inferredRelations[] = [
                            "id" => "inferred_" . md5($targetId . "_" . $sourceId . "_" . time()),
                            "source_id" => $targetId,
                            "target_id" => $sourceId,
                            "type" => $relation1,
                            "confidence" => $inverseConfidence,
                            "provenance" => [
                                "rule" => "inverse",
                                "original_relation_id" => $relation["id"],
                                "original_relation_type" => $relation2,
                                "confidence_factor" => $confidenceFactor
                            ]
                        ];
                    }
                }
            }
        }
        
        return $inferredRelations;
    }
