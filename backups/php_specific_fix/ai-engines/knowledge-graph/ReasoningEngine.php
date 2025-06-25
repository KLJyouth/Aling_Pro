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

declare(strict_types=1];

namespace AlingAi\AI\Engines\KnowledgeGraph;

use Exception;
use InvalidArgumentException;

/**
 * 推理引擎
 * 
 * 在知识图谱上进行推理，支持多种推理规则和算法
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
     * @param GraphStoreInterface $graphStore 知识图谱存储接口
     * @param array $config 配置参数
     */
    public function __construct(GraphStoreInterface $graphStore, array $config = []]
    {
        $this->graphStore = $graphStore;
        $this->config = array_merge($this->getDefaultConfig(], $config];
        $this->initializeRules(];
    }
    
    /**
     * 获取默认配置
     * 
     * @return array 默认配置
     */
    private function getDefaultConfig(]: array
    {
        return [
            'max_inference_depth' => 3,  // 最大推理深度
            'confidence_threshold' => 0.5,  // 推理结果置信度阈值
            'enable_rule_learning' => false,  // 是否启用规则学习
            'enable_probabilistic_reasoning' => true,  // 是否启用概率推理
            'max_results' => 100,  // 最大返回结果数量
            'timeout' => 5000  // 推理超时时间（毫秒）
        ];
    }
    
    /**
     * 初始化推理规则
     */
    private function initializeRules(]: void
    {
        // 添加传递性规则
        $this->addTransitiveRules(];
        
        // 添加对称性规则
        $this->addSymmetricRules(];
        
        // 添加逆关系规则
        $this->addInverseRules(];
        
        // 添加层次关系规则
        $this->addHierarchicalRules(];
        
        // 添加组合规则
        $this->addCompositeRules(];
    }

    /**
     * 添加传递性规则
     */
    private function addTransitiveRules(]: void
    {
        // 位置传递性: A位于B，B位于C => A位于C
        $this->rules[] = [
            'name' => 'location_transitivity',
            'type' => 'transitive',
            'relation' => 'LocatedIn',
            'confidence_factor' => 0.9
        ];
        
        // 部分传递性: A是B的一部分，B是C的一部分 => A是C的一部分
        $this->rules[] = [
            'name' => 'part_of_transitivity',
            'type' => 'transitive',
            'relation' => 'PartOf',
            'confidence_factor' => 0.9
        ];
        
        // 包含传递性: A包含B，B包含C => A包含C
        $this->rules[] = [
            'name' => 'contains_transitivity',
            'type' => 'transitive',
            'relation' => 'Contains',
            'confidence_factor' => 0.9
        ];
    }
    
    /**
     * 添加对称性规则
     */
    private function addSymmetricRules(]: void
    {
        // 配偶关系对称性: A是B的配偶 => B是A的配偶
        $this->rules[] = [
            'name' => 'spouse_symmetry',
            'type' => 'symmetric',
            'relation' => 'Spouse',
            'confidence_factor' => 1.0
        ];
        
        // 兄弟姐妹关系对称性: A是B的兄弟姐妹 => B是A的兄弟姐妹
        $this->rules[] = [
            'name' => 'sibling_symmetry',
            'type' => 'symmetric',
            'relation' => 'Sibling',
            'confidence_factor' => 1.0
        ];
    }
    
    /**
     * 添加逆关系规则
     */
    private function addInverseRules(]: void
    {
        // 父母-子女逆关系: A是B的父母 => B是A的子女
        $this->rules[] = [
            'name' => 'parent_child_inverse',
            'type' => 'inverse',
            'relation1' => 'Parent',
            'relation2' => 'Child',
            'confidence_factor' => 1.0
        ];
        
        // 拥有-被拥有逆关系: A拥有B => B被A拥有
        $this->rules[] = [
            'name' => 'owns_owned_by_inverse',
            'type' => 'inverse',
            'relation1' => 'Owns',
            'relation2' => 'OwnedBy',
            'confidence_factor' => 1.0
        ];
        
        // 创建-被创建逆关系: A创建了B => B被A创建
        $this->rules[] = [
            'name' => 'created_created_by_inverse',
            'type' => 'inverse',
            'relation1' => 'Created',
            'relation2' => 'CreatedBy',
            'confidence_factor' => 1.0
        ];
    }

    /**
     * 添加层次关系规则
     */
    private function addHierarchicalRules(]: void
    {
        // 组织层次关系: A是B的一部分，B是组织 => A是组织
        $this->rules[] = [
            'name' => 'organization_hierarchy',
            'type' => 'hierarchical',
            'relation' => 'PartOf',
            'source_type' => null,
            'target_type' => 'Organization',
            'inferred_type' => 'Organization',
            'confidence_factor' => 0.8
        ];
        
        // 地理层次关系: A位于B，B是地点 => A是地点
        $this->rules[] = [
            'name' => 'location_hierarchy',
            'type' => 'hierarchical',
            'relation' => 'LocatedIn',
            'source_type' => null,
            'target_type' => 'Location',
            'inferred_type' => 'Location',
            'confidence_factor' => 0.8
        ];
    }
    
    /**
     * 添加组合规则
     */
    private function addCompositeRules(]: void
    {
        // 同事关系: A在C工作，B在C工作 => A和B是同事
        $this->rules[] = [
            'name' => 'colleague_relation',
            'type' => 'composite',
            'relation1' => 'WorksFor',
            'relation2' => 'WorksFor',
            'inferred_relation' => 'Colleague',
            'pattern' => 'common_target',
            'confidence_factor' => 0.7
        ];
        
        // 家族关系: A是B的父母，B是C的父母 => A是C的祖父母
        $this->rules[] = [
            'name' => 'grandparent_relation',
            'type' => 'composite',
            'relation1' => 'Parent',
            'relation2' => 'Parent',
            'inferred_relation' => 'Grandparent',
            'pattern' => 'chain',
            'confidence_factor' => 0.9
        ];
    }

    /**
     * 执行推理
     * 
     * @param array  查询参数
     * @return array 推理结果
     * @throws Exception 如果推理过程中出现错误
     */
    public function reason(array $query]: array
    {
        // 验证查询参数
        $this->validateQuery($query];
        
        // 初始化结果集
        $result = [];
        $startTime = microtime(true];
        
        // 根据查询类型执行不同的推理策略
        switch ($query['type']] {
            case 'path_finding':
                $result = $this->findPath($query];
                break;
            case 'entity_completion':
                $result = $this->completeEntity($query];
                break;
            case 'relation_prediction':
                $result = $this->predictRelation($query];
                break;
            case 'rule_based_inference':
                $result = $this->applyRules($query];
                break;
            default:
                throw new InvalidArgumentException('不支持的查询类型: ' . $query['type']];
        }
        
        // 检查超时
        $duration = (microtime(true] - $startTime] * 1000;// 转换为毫秒
        if ($duration > $this->config['timeout']] {
            $result['timeout'] = true;
            $result['message'] = '推理操作超时，结果可能不完整';
        }
        
        // 限制返回结果数量
        if (isset($result['entities']] && count($result['entities']] > $this->config['max_results']] {
            $result['entities'] = array_slice($result['entities'], 0, $this->config['max_results']];
            $result['truncated'] = true;
        }
        
        if (isset($result['relations']] && count($result['relations']] > $this->config['max_results']] {
            $result['relations'] = array_slice($result['relations'], 0, $this->config['max_results']];
            $result['truncated'] = true;
        }
        
        return $result;
    }

    /**
     * 验证查询参数
     * 
     * @param array  查询参数
     * @throws InvalidArgumentException 如果参数无效
     */
    private function validateQuery(array $query]: void
    {
        // 检查必需的参数
        if (!isset($query['type']]] {
            throw new InvalidArgumentException('缺少必需的查询类型参数'];
        }
        
        // 验证查询类型
        $supportedTypes = ['path_finding', 'entity_completion', 'relation_prediction', 'rule_based_inference'];
        if (!in_[$query['type'], $supportedTypes]] {
            throw new InvalidArgumentException('无效的查询类型: ' . $query['type']];
        }
        
        // 根据查询类型验证特定参数
        switch ($query['type']] {
            case 'path_finding':
                if (!isset($query['source_entity']] || !isset($query['target_entity']]] {
                    throw new InvalidArgumentException('路径查找需要源实体和目标实体'];
                }
                break;
            case 'entity_completion':
                if (!isset($query['source_entity']] || !isset($query['relation']]] {
                    throw new InvalidArgumentException('实体补全需要源实体和关系类型'];
                }
                break;
            case 'relation_prediction':
                if (!isset($query['source_entity']] || !isset($query['target_entity']]] {
                    throw new InvalidArgumentException('关系预测需要源实体和目标实体'];
                }
                break;
            case 'rule_based_inference':
                if (!isset($query['entities']] && !isset($query['relations']]] {
                    throw new InvalidArgumentException('基于规则的推理需要初始实体或关系'];
                }
                break;
        }
    }

    /**
     * 查找路径
     * 
     * @param array  查询参数
     * @return array 路径查找结果
     */
    private function findPath(array $query]: array
    {
        $sourceEntity = $query['source_entity'];
        $targetEntity = $query['target_entity'];
        $maxDepth = $query['max_depth'] ?? $this->config['max_inference_depth'];
        $relationTypes = $query['relation_types'] ?? null;
        
        // 初始化结果
        $result = [
            'paths' => [],
            'source_entity' => $sourceEntity,
            'target_entity' => $targetEntity
        ];
        
        // 使用广度优先搜索查找路径
        $paths = [[['id']]];// 初始路径只包含源实体ID
        $visited = [['id'] => true];// 记录已访问的实体
        $pathCount = 0;
        
        while (!empty($paths] && $pathCount <$this->config['max_results']] {
            $path = array_shift($paths];
            $currentEntity = end($path];
            
            // 如果到达目标实体，则记录路径
            if ($currentEntity === ['id']] {
                $result['paths'][] = $this->buildCompletePath($path];
                $pathCount++;
                continue;
            }
            
            // 如果达到最大深度，则不再扩展
            if (count($path] >= $maxDepth] {
                continue;
            }
            
            // 获取当前实体的所有关系
            $relations = $this->graphStore->getEntityRelations($currentEntity, $relationTypes];
            
            // 遍历关系，扩展路径
            foreach ($relations as $relation] {
                $nextEntity = ($relation['source_id'] === $currentEntity] ? $relation['target_id'] : $relation['source_id'];
                
                // 避免环路
                if (isset($visited[$nextEntity]]] {
                    continue;
                }
                
                // 添加到队列和已访问集合
                $newPath = $path;
                $newPath[] = $nextEntity;
                $visited[$nextEntity] = true;
            }
        }
        
        return $result;
    }

    /**
     * 构建完整路径
     * 
     * @param array  路径中的实体ID列表
     * @return array 完整路径信息
     */
    private function buildCompletePath(array $path]: array
    {
        $entities = [];
        $relations = [];
        
        // 获取路径中的每个实体
        foreach ($path as $entityId] {
            $entity = $this->graphStore->getEntityById($entityId];
            if ($entity] {
                $entities[] = $entity;
            }
        }
        
        // 获取相邻实体之间的关系
        for ($i = 0;$i <count($entities] - 1;$i++] {
            $relation = $this->graphStore->getRelationBetween($entities[$i]['id'], $entities[$i + 1]['id']];
            if ($relation] {
                $relations[] = $relation;
            }
        }
        
        return [
            'entities' => $entities,
            'relations' => $relations,
            'confidence' => $this->calculatePathConfidence($relations]
        ];
    }

    /**
     * 计算路径置信度
     * 
     * @param array  路径中的关系列表
     * @return float 路径置信度
     */
    private function calculatePathConfidence(array $relations]: float
    {
        if (empty($relations]] {
            return 0.0;
        }
        
        // 计算所有关系置信度的乘积，并根据路径长度进行调整
        $confidence = 1.0;
        foreach ($relations as $relation] {
            $confidence *= $relation['confidence'];
        }
        
        // 路径越长，置信度越低
        $confidence = 1.0 / (1.0 + 0.1 * (count($relations] - 1]];
        $confidence *= $confidence;
        
        return $confidence;
    }

    /**
     * 实体补全
     * 
     * @param array  查询参数
     * @return array 实体补全结果
     */
    private function completeEntity(array $query]: array
    {
        $sourceEntity = $query['source_entity'];
        $relation = $query['relation'];
        $limit = $query['limit'] ?? $this->config['max_results'];
        $confidenceThreshold = $query['confidence_threshold'] ?? $this->config['confidence_threshold'];
        
        // 初始化结果
        $result = [
            'source_entity' => $sourceEntity,
            'relation' => $relation,
            'target_entities' => []
        ];
        
        // 直接查询满足关系的目标实体
        $entities = $this->graphStore->getRelatedEntities(['id'], $relation];
        
        // 过滤低置信度结果
        foreach ($entities as $entity] {
            if ($entity['confidence'] >= $confidenceThreshold] {
                $result['target_entities'][] = $entity;
            }
        }
        
        // 如果直接结果不足，尝试使用规则推理
        if (count($result['target_entities']] <$limit && $this->config['enable_rule_learning']] {
            $inferredEntities = $this->inferRelatedEntities($sourceEntity, $relation, $confidenceThreshold];
            
            // 合并结果，避免重复
            $targetEntities = array_map(function($entity] { return $entity['id'];}, $result['target_entities']];
            foreach ($inferredEntities as $entity] {
                if (!in_[$entity['id'], $targetEntities]] {
                    $result['target_entities'][] = $entity;
                    $targetEntities[] = $entity['id'];
                    
                    // 达到限制数量则停止
                    if (count($result['target_entities']] >= $limit] {
                        break;
                    }
                }
            }
        }
        
        // 按置信度排序
        usort($result['target_entities'], function($a, $b] {
            return $a['confidence'] <=> $b['confidence'];
        }];
        
        return $result;
    }

    /**
     * 推理相关实体
     * 
     * @param array  源实体
     * @param string  目标关系类型
     * @param float  置信度阈值
     * @return array 推理得到的相关实体列表
     */
    private function inferRelatedEntities(array $sourceEntity, string $relation, float $confidenceThreshold]: array
    {
        $inferredEntities = [];
        
        // 遍历所有规则
        foreach ($this->rules as $rule] {
            // 对于组合规则
            if ($rule['type'] === 'composite' && $rule['inferred_relation'] === $relation] {
                // 链式规则: A与B有关系1，B与C有关系2 => A与C有关系3
                if ($rule['pattern'] === 'chain'] {
                    // 获取与源实体有关系1的中间实体
                    $intermediateEntities = $this->graphStore->getRelatedEntities(['id'], ['relation1']];
                    
                    foreach ($intermediateEntities as $intermediateEntity] {
                        // 获取与中间实体有关系2的目标实体
                        $targetEntities = $this->graphStore->getRelatedEntities(['id'], ['relation2']];
                        
                        foreach ($targetEntities as $targetEntity] {
                            // 计算推理置信度
                            $confidence = $intermediateEntity['confidence'] * $targetEntity['confidence'] * $rule['confidence_factor'];
                            
                            if ($confidence >= $confidenceThreshold] {
                                $inferredEntities[] = [
                                    'id' => $targetEntity['id'],
                                    'confidence' => $confidence,
                                    'inferred' => true,
                                    'inference_path' => [
                                        'rule' => $rule['name'],
                                        'intermediate' => $intermediateEntity['id']
                                    ]
                                ];
                            }
                        }
                    }
                }
                // 共同目标规则: A与C有关系1，B与C有关系2 => A与B有关系3
                else if ($rule['pattern'] === 'common_target'] {
                    // 获取与源实体有关系1的共同目标实体
                    $commonTargetEntities = $this->graphStore->getRelatedEntities(['id'], ['relation1']];
                    
                    foreach ($commonTargetEntities as $commonTargetEntity] {
                        // 获取与共同目标有关系2的目标实体
                        $targetEntities = $this->graphStore->getEntitiesWithRelation(['relation2'], ['id']];
                        
                        foreach ($targetEntities as $targetEntity] {
                            // 计算推理置信度
                            $confidence = $commonTargetEntity['confidence'] * $targetEntity['confidence'] * $rule['confidence_factor'];
                            
                            if ($confidence >= $confidenceThreshold] {
                                $inferredEntities[] = [
                                    'id' => $targetEntity['id'],
                                    'confidence' => $confidence,
                                    'inferred' => true,
                                    'inference_path' => [
                                        'rule' => $rule['name'],
                                        'common_target' => $commonTargetEntity['id']
                                    ]
                                ];
                            }
                        }
                    }
                }
            }
            // 对于对称性规则
            else if ($rule['type'] === 'symmetric' && $rule['relation'] === $relation] {
                // 如果A与B有对称关系，则B与A也有同样的关系
                $entities = $this->graphStore->getEntitiesWithRelation(['relation'], ['id']];
                
                foreach ($entities as $entity] {
                    $confidence = $entity['confidence'] * $rule['confidence_factor'];
                    
                    if ($confidence >= $confidenceThreshold] {
                        $inferredEntities[] = [
                            'id' => $entity['id'],
                            'confidence' => $confidence,
                            'inferred' => true,
                            'inference_path' => [
                                'rule' => $rule['name']
                            ]
                        ];
                    }
                }
            }
            // 对于逆关系规则
            else if ($rule['type'] === 'inverse' && $rule['relation2'] === $relation] {
                // 如果A与B有关系1，则B与A有关系2
                $entities = $this->graphStore->getEntitiesWithRelation(['relation1'], ['id']];
                
                foreach ($entities as $entity] {
                    $confidence = $entity['confidence'] * $rule['confidence_factor'];
                    
                    if ($confidence >= $confidenceThreshold] {
                        $inferredEntities[] = [
                            'id' => $entity['id'],
                            'confidence' => $confidence,
                            'inferred' => true,
                            'inference_path' => [
                                'rule' => $rule['name']
                            ]
                        ];
                    }
                }
            }
        }
        
        return $inferredEntities;
    }

    /**
     * 预测关系
     * 
     * @param array  查询参数
     * @return array 关系预测结果
     */
    private function predictRelation(array $query]: array
    {
        $sourceEntity = $query['source_entity'];
        $targetEntity = $query['target_entity'];
        $confidenceThreshold = $query['confidence_threshold'] ?? $this->config['confidence_threshold'];
        
        // 初始化结果
        $result = [
            'source_entity' => $sourceEntity,
            'target_entity' => $targetEntity,
            'relations' => []
        ];
        
        // 直接查询两个实体之间的关系
        $relations = $this->graphStore->getRelationsBetween(['id'], ['id']];
        
        // 过滤低置信度关系
        foreach ($relations as $relation] {
            if ($relation['confidence'] >= $confidenceThreshold] {
                $result['relations'][] = $relation;
            }
        }
        
        // 如果启用规则推理，尝试推理可能的关系
        if ($this->config['enable_rule_learning']] {
            $possibleRelations = $this->inferPossibleRelations($sourceEntity, $targetEntity, $confidenceThreshold];
            
            // 合并结果，避免重复
            $result['relations'] = array_merge($result['relations'], $possibleRelations];
        }
        
        // 按置信度排序
        usort($result['relations'], function($a, $b] {
            return $a['confidence'] <=> $b['confidence'];
        }];
        
        return $result;
    }

    /**
     * 推理可能的关系
     * 
     * @param array  源实体
     * @param array  目标实体
     * @param float  置信度阈值
     * @return array 推理得到的可能关系
     */
    private function inferPossibleRelations(array $sourceEntity, array $targetEntity, float $confidenceThreshold]: array
    {
        $possibleRelations = [];
        
        // 遍历所有规则
        foreach ($this->rules as $rule] {
            // 对于组合规则
            if ($rule['type'] === 'composite'] {
                // 链式规则: A与B有关系1，B与C有关系2 => A与C有关系3
                if ($rule['pattern'] === 'chain'] {
                    // 查找可能的中间实体
                    $intermediateEntities = $this->findIntermediateEntities($sourceEntity['id'], $targetEntity['id'], $rule['relation1'], $rule['relation2']];
                    
                    if (!empty($intermediateEntities]] {
                        // 创建推理关系
                        $possibleRelations[] = [
                            'id' => md5($sourceEntity['id'] . $targetEntity['id'] . $rule['inferred_relation']],
                            'source_id' => $sourceEntity['id'],
                            'target_id' => $targetEntity['id'],
                            'type' => $rule['inferred_relation'],
                            'inferred' => true,
                            'confidence' => $this->calculateInferredRelationConfidence($intermediateEntities, $rule['confidence_factor']],
                            'inference_path' => [
                                'rule' => $rule['name'],
                                'intermediates' => array_map(function($entity] { return $entity['id'];}, $intermediateEntities]
                            ]
                        ];
                        
                        if ($possibleRelations[count($possibleRelations] - 1]['confidence'] >= $confidenceThreshold] {
                            $possibleRelations[] = $possibleRelations[count($possibleRelations] - 1];
                        }
                    }
                }
            }
            // 对于对称性规则
            else if ($rule['type'] === 'symmetric'] {
                // 检查是否存在反向关系
                $relation = $this->graphStore->getRelationBetween($sourceEntity['id'], $targetEntity['id'], $rule['relation']];
                
                if ($relation] {
                    $possibleRelations[] = [
                        'id' => md5($sourceEntity['id'] . $targetEntity['id'] . $rule['relation']],
                        'source_id' => $sourceEntity['id'],
                        'target_id' => $targetEntity['id'],
                        'type' => $rule['relation'],
                        'inferred' => true,
                        'confidence' => $sourceEntity['confidence'] * $rule['confidence_factor'],
                        'inference_path' => [
                            'rule' => $rule['name'],
                            'symmetric_relation_id' => $relation['id']
                        ]
                    ];
                    
                    if ($possibleRelations[count($possibleRelations] - 1]['confidence'] >= $confidenceThreshold] {
                        $possibleRelations[] = $possibleRelations[count($possibleRelations] - 1];
                    }
                }
            }
            // 对于逆关系规则
            else if ($rule['type'] === 'inverse'] {
                // 检查是否存在逆关系
                $relation = $this->graphStore->getRelationBetween($sourceEntity['id'], $targetEntity['id'], $rule['relation1']];
                
                if ($relation] {
                    $possibleRelations[] = [
                        'id' => md5($sourceEntity['id'] . $targetEntity['id'] . $rule['relation2']],
                        'source_id' => $sourceEntity['id'],
                        'target_id' => $targetEntity['id'],
                        'type' => $rule['relation2'],
                        'inferred' => true,
                        'confidence' => $sourceEntity['confidence'] * $rule['confidence_factor'],
                        'inference_path' => [
                            'rule' => $rule['name'],
                            'inverse_relation_id' => $relation['id']
                        ]
                    ];
                    
                    if ($possibleRelations[count($possibleRelations] - 1]['confidence'] >= $confidenceThreshold] {
                        $possibleRelations[] = $possibleRelations[count($possibleRelations] - 1];
                    }
                }
            }
        }
        
        return $possibleRelations;
    }

    /**
     * 查找中间实体
     * 
     * @param string  源实体ID
     * @param string  目标实体ID
     * @param string  关系1
     * @param string  关系2
     * @return array 中间实体列表
     */
    private function findIntermediateEntities(string $sourceId, string $targetId, string $relation1, string $relation2]: array
    {
        $intermediateEntities = [];
        
        // 获取与源实体有关系1的实体
        $entities = $this->graphStore->getRelatedEntities($sourceId, $relation1];
        
        // 获取与目标实体有关系2的实体
        $targetEntities = $this->graphStore->getEntitiesWithRelation($targetId, $relation2];
        
        // 找出共同的实体（交集）
        $commonEntities = array_map(function($entity] { return $entity['id'];}, $entities];
        $targetIds = array_map(function($entity] { return $entity['id'];}, $targetEntities];
        
        $commonIds = array_intersect($commonEntities, $targetIds];
        
        // 构建中间实体列表
        foreach ($commonIds as $commonId] {
            $intermediateEntities[] = [
                'id' => $commonId,
                'confidence' => 1.0
            ];
        }
        
        return $intermediateEntities;
    }
    
    /**
     * 计算推理关系置信度
     * 
     * @param array  中间实体列表
     * @param float  置信度因子
     * @return float 推理关系置信度
     */
    private function calculateInferredRelationConfidence(array $intermediateEntities, float $confidenceFactor]: float
    {
        if (empty($intermediateEntities]] {
            return 0.0;
        }
        
        // 使用最高置信度的中间实体路径
        $confidence = 0.0;
        foreach ($intermediateEntities as $entity] {
            $confidence += $entity['confidence'] * $confidenceFactor;
        }
        
        return $confidence;
    }

    /**
     * 应用规则进行推理
     * 
     * @param array  查询参数
     * @return array 推理结果
     */
    private function applyRules(array $query]: array
    {
        $rules = $query['rules'] ?? null;
        $maxDepth = $query['max_depth'] ?? $this->config['max_inference_depth'];
        $confidenceThreshold = $query['confidence_threshold'] ?? $this->config['confidence_threshold'];
        $entityId = $query['entity_id'] ?? null;
        $relationTypes = $query['relation_types'] ?? null;
        
        // 初始化结果
        $result = [
            'inferred_facts' => [],
            'applied_rules' => []
        ];
        
        // 如果指定了实体ID，则从该实体开始推理
        if ($entityId] {
            $entity = $this->graphStore->getEntityById($entityId];
            if (!$entity] {
                return $result;
            }
            
            $result['entity'] = $entity;
            $inferredFacts = $this->inferFactsFromEntity($entity, $rules, $relationTypes, $confidenceThreshold, $maxDepth];
            $result['inferred_facts'] = $inferredFacts;
        }
        // 否则对所有实体应用规则
        else {
            $entities = $this->graphStore->getAllEntities(];
            foreach ($entities as $entity] {
                $inferredFacts = $this->inferFactsFromEntity($entity, $rules, $relationTypes, $confidenceThreshold, 1];
                if (!empty($inferredFacts]] {
                    $result['inferred_facts'] = array_merge($result['inferred_facts'], $inferredFacts];
                }
                
                // 限制推理结果数量
                if (count($result['inferred_facts']] >= $this->config['max_results']] {
                    break;
                }
            }
        }
        
        // 记录应用的规则
        $appliedRules = [];
        foreach ($result['inferred_facts'] as $fact] {
            if (isset($fact['inference_path']['rule']] && !in_[$fact['inference_path']['rule'], $rules]] {
                $appliedRules[] = $fact['inference_path']['rule'];
            }
        }
        
        foreach ($appliedRules as $ruleName] {
            foreach ($this->rules as $rule] {
                if ($rule['name'] === $ruleName] {
                    $result['applied_rules'][] = $rule;
                    break;
                }
            }
        }
        
        return $result;
    }
    
    /**
     * 从实体推理事实
     * 
     * @param array  实体
     * @param array|null  规则名称列表
     * @param array|null  关系类型列表
     * @param float  置信度阈值
     * @param int  最大推理深度
     * @return array 推理得到的事实列表
     */
    private function inferFactsFromEntity(array $entity, ?array $rules = null, ?array $relationTypes = null, float $confidenceThreshold = 0.5, int $maxDepth = 1]: array
    {
        $inferredFacts = [];
        $visited = [];
        $queue = [['entity' => $entity, 'depth' => 0]];
        
        while (!empty($queue] && count($inferredFacts] <$this->config['max_results']] {
            $current = array_shift($queue];
            $currentEntity = $current['entity'];
            $currentDepth = $current['depth'];
            
            if ($currentDepth >= $maxDepth] {
                continue;
            }
            
            // 对当前实体应用所有规则
            foreach ($this->rules as $rule] {
                // 如果指定了规则名称，则只应用这些规则
                if ($rules !== null && !in_[$rule['name'], $rules]] {
                    continue;
                }
                
                // 根据规则类型应用不同的推理策略
                switch ($rule['type']] {
                    case 'composite':
                        $inferredFacts = array_merge($inferredFacts, $this->applyCompositeRule($currentEntity, $rule, $relationTypes, $confidenceThreshold]];
                        break;
                    case 'symmetric':
                        $inferredFacts = array_merge($inferredFacts, $this->applySymmetricRule($currentEntity, $rule, $relationTypes, $confidenceThreshold]];
                        break;
                    case 'transitive':
                        $inferredFacts = array_merge($inferredFacts, $this->applyTransitiveRule($currentEntity, $rule, $relationTypes, $confidenceThreshold]];
                        break;
                    case 'hierarchical':
                        $inferredFacts = array_merge($inferredFacts, $this->applyHierarchicalRule($currentEntity, $rule, $confidenceThreshold]];
                        break;
                    case 'inverse':
                        $inferredFacts = array_merge($inferredFacts, $this->applyInverseRule($currentEntity, $rule, $relationTypes, $confidenceThreshold]];
                        break;
                    default:
                        $inferredFacts = array_merge($inferredFacts, []];
                }
            }
        }
        
        return $inferredFacts;
    }

    /**
     * 应用组合规则
     * 
     * @param array  实体
     * @param array  规则
     * @param array|null  关系类型列表
     * @param float  置信度阈值
     * @return array 推理得到的事实列表
     */
    private function applyCompositeRule(array $entity, array $rule, ?array $relationTypes = null, float $confidenceThreshold = 0.5]: array
    {
        $inferredFacts = [];
        
        // 链式规则: A与B有关系1，B与C有关系2 => A与C有关系3
        if ($rule['pattern'] === 'chain'] {
            // 如果指定了关系类型，且推理关系不在列表中，则跳过
            if ($relationTypes !== null && !in_[$rule['inferred_relation'], $relationTypes]] {
                return [];
            }
            
            // 获取与源实体有关系1的中间实体
            $intermediateEntities = $this->graphStore->getRelatedEntities(['id'], [$rule['relation1']]];
            
            foreach ($intermediateEntities as $intermediateEntity] {
                // 获取与中间实体有关系2的目标实体
                $targetEntities = $this->graphStore->getRelatedEntities(['id'], [$rule['relation2']]];
                
                foreach ($targetEntities as $targetEntity] {
                    // 避免自环
                    if ($intermediateEntity['id'] === $targetEntity['id']] {
                        continue;
                    }
                    
                    // 计算推理置信度
                    $confidence = $intermediateEntity['confidence'] * $targetEntity['confidence'] * $rule['confidence_factor'];
                    
                    if ($confidence >= $confidenceThreshold] {
                        // 创建推理关系
                        $inferredFacts[] = [
                            'id' => md5($intermediateEntity['id'] . $targetEntity['id'] . $rule['inferred_relation']],
                            'source_id' => $intermediateEntity['id'],
                            'target_id' => $targetEntity['id'],
                            'type' => $rule['inferred_relation'],
                            'inferred' => true,
                            'confidence' => $confidence,
                            'inference_path' => [
                                'rule' => $rule['name'],
                                'intermediate' => $intermediateEntity['id']
                            ]
                        ];
                    }
                }
            }
        }
        // 共同目标规则: A与C有关系1，B与C有关系2 => A与B有关系3
        else if ($rule['pattern'] === 'common_target'] {
            // 如果指定了关系类型，且推理关系不在列表中，则跳过
            if ($relationTypes !== null && !in_[$rule['inferred_relation'], $relationTypes]] {
                return [];
            }
            
            // 获取与源实体有关系1的共同目标实体
            $commonTargetEntities = $this->graphStore->getRelatedEntities(['id'], [$rule['relation1']]];
            
            foreach ($commonTargetEntities as $commonTargetEntity] {
                // 获取与共同目标有关系2的目标实体
                $targetEntities = $this->graphStore->getEntitiesWithRelation(['relation2'], ['id']];
                
                foreach ($targetEntities as $targetEntity] {
                    // 避免自环
                    if ($commonTargetEntity['id'] === $targetEntity['id']] {
                        continue;
                    }
                    
                    // 计算推理置信度
                    $confidence = $commonTargetEntity['confidence'] * $targetEntity['confidence'] * $rule['confidence_factor'];
                    
                    if ($confidence >= $confidenceThreshold] {
                        // 创建推理关系
                        $inferredFacts[] = [
                            'id' => md5($commonTargetEntity['id'] . $targetEntity['id'] . $rule['inferred_relation']],
                            'source_id' => $commonTargetEntity['id'],
                            'target_id' => $targetEntity['id'],
                            'type' => $rule['inferred_relation'],
                            'inferred' => true,
                            'confidence' => $confidence,
                            'inference_path' => [
                                'rule' => $rule['name'],
                                'common_target' => $commonTargetEntity['id']
                            ]
                        ];
                    }
                }
            }
        }
        
        return $inferredFacts;
    }

    /**
     * 应用对称性规则
     * 
     * @param array  实体
     * @param array  规则
     * @param array|null  关系类型列表
     * @param float  置信度阈值
     * @return array 推理得到的事实列表
     */
    private function applySymmetricRule(array $entity, array $rule, ?array $relationTypes = null, float $confidenceThreshold = 0.5]: array
    {
        $inferredFacts = [];
        
        // 如果指定了关系类型，且规则关系不在列表中，则跳过
        if ($relationTypes !== null && !in_[$rule['relation'], $relationTypes]] {
            return [];
        }
        
        // 获取与实体有关系的目标实体
        $targetEntities = $this->graphStore->getEntitiesWithRelation(['relation'], ['id']];
        
        foreach ($targetEntities as $targetEntity] {
            // 检查是否已存在反向关系
            $relation = $this->graphStore->getRelationBetween($entity['id'], $targetEntity['id'], $rule['relation']];
            
            // 如果不存在反向关系，则创建推理关系
            if (!$relation] {
                $confidence = $entity['confidence'] * $rule['confidence_factor'];
                
                if ($confidence >= $confidenceThreshold] {
                    $inferredFacts[] = [
                        'id' => md5($entity['id'] . $targetEntity['id'] . $rule['relation']],
                        'source_id' => $entity['id'],
                        'target_id' => $targetEntity['id'],
                        'type' => $rule['relation'],
                        'inferred' => true,
                        'confidence' => $confidence,
                        'inference_path' => [
                            'rule' => $rule['name'],
                            'symmetric_relation_id' => md5($entity['id'] . $targetEntity['id'] . $rule['relation']]
                        ]
                    ];
                }
            }
        }
        
        return $inferredFacts;
    }
    
    /**
     * 应用传递性规则
     * 
     * @param array  实体
     * @param array  规则
     * @param array|null  关系类型列表
     * @param float  置信度阈值
     * @return array 推理得到的事实列表
     */
    private function applyTransitiveRule(array $entity, array $rule, ?array $relationTypes = null, float $confidenceThreshold = 0.5]: array
    {
        $inferredFacts = [];
        
        // 如果指定了关系类型，且规则关系不在列表中，则跳过
        if ($relationTypes !== null && !in_[$rule['relation'], $relationTypes]] {
            return [];
        }
        
        // 获取与实体有指定关系的目标实体
        $targetEntities = $this->graphStore->getRelatedEntities(['id'], [$rule['relation']]];
        
        foreach ($targetEntities as $targetEntity] {
            // 获取与中间实体有相同关系的目标实体
            $intermediateEntities = $this->graphStore->getRelatedEntities(['id'], [$rule['relation']]];
            
            foreach ($intermediateEntities as $intermediateEntity] {
                // 避免自环和直接关系
                if ($intermediateEntity['id'] === $entity['id'] || $intermediateEntity['id'] === $targetEntity['id']] {
                    continue;
                }
                
                // 检查是否已存在直接关系
                $relation = $this->graphStore->getRelationBetween($intermediateEntity['id'], $targetEntity['id'], $rule['relation']];
                
                // 如果不存在直接关系，则创建推理关系
                if (!$relation] {
                    $confidence = $intermediateEntity['confidence'] * $targetEntity['confidence'] * $rule['confidence_factor'];
                    
                    if ($confidence >= $confidenceThreshold] {
                        $inferredFacts[] = [
                            'id' => md5($intermediateEntity['id'] . $targetEntity['id'] . $rule['relation']],
                            'source_id' => $intermediateEntity['id'],
                            'target_id' => $targetEntity['id'],
                            'type' => $rule['relation'],
                            'inferred' => true,
                            'confidence' => $confidence,
                            'inference_path' => [
                                'rule' => $rule['name'],
                                'intermediate' => $intermediateEntity['id']
                            ]
                        ];
                    }
                }
            }
        }
        
        return $inferredFacts;
    }

    /**
     * 应用层次关系规则
     * 
     * @param array  实体
     * @param array  规则
     * @param float  置信度阈值
     * @return array 推理得到的事实列表
     */
    private function applyHierarchicalRule(array $entity, array $rule, float $confidenceThreshold = 0.5]: array
    {
        $inferredFacts = [];
        
        // 检查实体类型是否符合规则要求
        if ($entity['source_type'] !== null && $entity['type'] !== $entity['source_type']] {
            return [];
        }
        
        // 获取与实体有指定关系的目标实体
        $targetEntities = $this->graphStore->getRelatedEntities(['id'], [$rule['relation']]];
        
        foreach ($targetEntities as $targetEntity] {
            // 检查目标实体类型是否符合规则要求
            if ($targetEntity['target_type'] !== null && $targetEntity['type'] !== $targetEntity['target_type']] {
                continue;
            }
            
            // 计算推理置信度
            $confidence = $entity['confidence'] * $rule['confidence_factor'];
            
            if ($confidence >= $confidenceThreshold] {
                // 创建推理事实：实体继承目标实体的类型
                $inferredFacts[] = [
                    'id' => md5($entity['id'] . '_type_' . $rule['inferred_type']],
                    'entity_id' => $entity['id'],
                    'type' => 'type_assertion',
                    'value' => $rule['inferred_type'],
                    'inferred' => true,
                    'confidence' => $confidence,
                    'inference_path' => [
                        'rule' => $rule['name'],
                        'relation_target' => $targetEntity['id']
                    ]
                ];
            }
        }
        
        return $inferredFacts;
    }
    
    /**
     * 应用逆关系规则
     * 
     * @param array  实体
     * @param array  规则
     * @param array|null  关系类型列表
     * @param float  置信度阈值
     * @return array 推理得到的事实列表
     */
    private function applyInverseRule(array $entity, array $rule, ?array $relationTypes = null, float $confidenceThreshold = 0.5]: array
    {
        $inferredFacts = [];
        
        // 如果指定了关系类型，且推理关系不在列表中，则跳过
        if ($relationTypes !== null && !in_[$rule['relation2'], $relationTypes]] {
            return [];
        }
        
        // 获取与实体有关系1的目标实体
        $entities = $this->graphStore->getEntitiesWithRelation(['relation1'], ['id']];
        
        foreach ($entities as $entity] {
            // 检查是否已存在逆关系
            $relation = $this->graphStore->getRelationBetween($entity['id'], $entity['id'], $rule['relation2']];
            
            // 如果不存在逆关系，则创建推理关系
            if (!$relation] {
                $confidence = $entity['confidence'] * $rule['confidence_factor'];
                
                if ($confidence >= $confidenceThreshold] {
                    $inferredFacts[] = [
                        'id' => md5($entity['id'] . $entity['id'] . $rule['relation2']],
                        'source_id' => $entity['id'],
                        'target_id' => $entity['id'],
                        'type' => $rule['relation2'],
                        'inferred' => true,
                        'confidence' => $confidence,
                        'inference_path' => [
                            'rule' => $rule['name'],
                            'inverse_relation_id' => md5($entity['id'] . $entity['id'] . $rule['relation1']]
                        ]
                    ];
                }
            }
        }
        
        return $inferredFacts;
    }
}

