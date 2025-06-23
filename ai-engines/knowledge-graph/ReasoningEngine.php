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
 * 在知识图谱上进行推理，支持多种推理规则和算法
 */
class ReasoningEngine
{
    /**
     * 配置参数
     */
    private array ;
    
    /**
     * 推理规则集合
     */
    private array  = [];
    
    /**
     * 知识图谱存储接口
     */
    private ?GraphStoreInterface  = null;

    /**
     * 构造函数
     * 
     * @param GraphStoreInterface  知识图谱存储接口
     * @param array  配置参数
     */
    public function __construct(GraphStoreInterface , array  = [])
    {
        ->graphStore = ;
        ->config = array_merge(->getDefaultConfig(), );
        ->initializeRules();
    }
    
    /**
     * 获取默认配置
     * 
     * @return array 默认配置
     */
    private function getDefaultConfig(): array
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
    private function initializeRules(): void
    {
        // 添加传递性规则
        ->addTransitiveRules();
        
        // 添加对称性规则
        ->addSymmetricRules();
        
        // 添加逆关系规则
        ->addInverseRules();
        
        // 添加层次关系规则
        ->addHierarchicalRules();
        
        // 添加组合规则
        ->addCompositeRules();
    }

    /**
     * 添加传递性规则
     */
    private function addTransitiveRules(): void
    {
        // 位置传递性: A位于B，B位于C => A位于C
        ->rules[] = [
            'name' => 'location_transitivity',
            'type' => 'transitive',
            'relation' => 'LocatedIn',
            'confidence_factor' => 0.9
        ];
        
        // 部分传递性: A是B的一部分，B是C的一部分 => A是C的一部分
        ->rules[] = [
            'name' => 'part_of_transitivity',
            'type' => 'transitive',
            'relation' => 'PartOf',
            'confidence_factor' => 0.9
        ];
        
        // 包含传递性: A包含B，B包含C => A包含C
        ->rules[] = [
            'name' => 'contains_transitivity',
            'type' => 'transitive',
            'relation' => 'Contains',
            'confidence_factor' => 0.9
        ];
    }
    
    /**
     * 添加对称性规则
     */
    private function addSymmetricRules(): void
    {
        // 配偶关系对称性: A是B的配偶 => B是A的配偶
        ->rules[] = [
            'name' => 'spouse_symmetry',
            'type' => 'symmetric',
            'relation' => 'Spouse',
            'confidence_factor' => 1.0
        ];
        
        // 兄弟姐妹关系对称性: A是B的兄弟姐妹 => B是A的兄弟姐妹
        ->rules[] = [
            'name' => 'sibling_symmetry',
            'type' => 'symmetric',
            'relation' => 'Sibling',
            'confidence_factor' => 1.0
        ];
    }
    
    /**
     * 添加逆关系规则
     */
    private function addInverseRules(): void
    {
        // 父母-子女逆关系: A是B的父母 => B是A的子女
        ->rules[] = [
            'name' => 'parent_child_inverse',
            'type' => 'inverse',
            'relation1' => 'Parent',
            'relation2' => 'Child',
            'confidence_factor' => 1.0
        ];
        
        // 拥有-被拥有逆关系: A拥有B => B被A拥有
        ->rules[] = [
            'name' => 'owns_owned_by_inverse',
            'type' => 'inverse',
            'relation1' => 'Owns',
            'relation2' => 'OwnedBy',
            'confidence_factor' => 1.0
        ];
        
        // 创建-被创建逆关系: A创建了B => B被A创建
        ->rules[] = [
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
    private function addHierarchicalRules(): void
    {
        // 组织层次关系: A是B的一部分，B是组织 => A是组织
        ->rules[] = [
            'name' => 'organization_hierarchy',
            'type' => 'hierarchical',
            'relation' => 'PartOf',
            'source_type' => null,
            'target_type' => 'Organization',
            'inferred_type' => 'Organization',
            'confidence_factor' => 0.8
        ];
        
        // 地理层次关系: A位于B，B是地点 => A是地点
        ->rules[] = [
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
    private function addCompositeRules(): void
    {
        // 同事关系: A在C工作，B在C工作 => A和B是同事
        ->rules[] = [
            'name' => 'colleague_relation',
            'type' => 'composite',
            'relation1' => 'WorksFor',
            'relation2' => 'WorksFor',
            'inferred_relation' => 'Colleague',
            'pattern' => 'common_target',
            'confidence_factor' => 0.7
        ];
        
        // 家族关系: A是B的父母，B是C的父母 => A是C的祖父母
        ->rules[] = [
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
     * 添加层次关系规则
     */
    private function addHierarchicalRules(): void
    {
        // 组织层次关系: A是B的一部分，B是组织 => A是组织
        ->rules[] = [
            'name' => 'organization_hierarchy',
            'type' => 'hierarchical',
            'relation' => 'PartOf',
            'source_type' => null,
            'target_type' => 'Organization',
            'inferred_type' => 'Organization',
            'confidence_factor' => 0.8
        ];
        
        // 地理层次关系: A位于B，B是地点 => A是地点
        ->rules[] = [
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
    private function addCompositeRules(): void
    {
        // 同事关系: A在C工作，B在C工作 => A和B是同事
        ->rules[] = [
            'name' => 'colleague_relation',
            'type' => 'composite',
            'relation1' => 'WorksFor',
            'relation2' => 'WorksFor',
            'inferred_relation' => 'Colleague',
            'pattern' => 'common_target',
            'confidence_factor' => 0.7
        ];
        
        // 家族关系: A是B的父母，B是C的父母 => A是C的祖父母
        ->rules[] = [
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
    public function reason(array ): array
    {
        // 验证查询参数
        ->validateQuery();
        
        // 初始化结果集
         = [];
         = microtime(true);
        
        // 根据查询类型执行不同的推理策略
        switch (['type']) {
            case 'path_finding':
                 = ->findPath();
                break;
            case 'entity_completion':
                 = ->completeEntity();
                break;
            case 'relation_prediction':
                 = ->predictRelation();
                break;
            case 'rule_based_inference':
                 = ->applyRules();
                break;
            default:
                throw new InvalidArgumentException('不支持的查询类型: ' . ['type']);
        }
        
        // 检查超时
         = (microtime(true) - ) * 1000; // 转换为毫秒
        if ( > ->config['timeout']) {
            ['timeout'] = true;
            ['message'] = '推理操作超时，结果可能不完整';
        }
        
        // 限制返回结果数量
        if (isset(['entities']) && count(['entities']) > ->config['max_results']) {
            ['entities'] = array_slice(['entities'], 0, ->config['max_results']);
            ['truncated'] = true;
        }
        
        if (isset(['relations']) && count(['relations']) > ->config['max_results']) {
            ['relations'] = array_slice(['relations'], 0, ->config['max_results']);
            ['truncated'] = true;
        }
        
        return ;
    }

    /**
     * 验证查询参数
     * 
     * @param array  查询参数
     * @throws InvalidArgumentException 如果参数无效
     */
    private function validateQuery(array ): void
    {
        // 检查必需的参数
        if (!isset(['type'])) {
            throw new InvalidArgumentException('缺少必需的查询类型参数');
        }
        
        // 验证查询类型
         = ['path_finding', 'entity_completion', 'relation_prediction', 'rule_based_inference'];
        if (!in_array(['type'], )) {
            throw new InvalidArgumentException('无效的查询类型: ' . ['type']);
        }
        
        // 根据查询类型验证特定参数
        switch (['type']) {
            case 'path_finding':
                if (!isset(['source_entity']) || !isset(['target_entity'])) {
                    throw new InvalidArgumentException('路径查找需要源实体和目标实体');
                }
                break;
            case 'entity_completion':
                if (!isset(['source_entity']) || !isset(['relation'])) {
                    throw new InvalidArgumentException('实体补全需要源实体和关系类型');
                }
                break;
            case 'relation_prediction':
                if (!isset(['source_entity']) || !isset(['target_entity'])) {
                    throw new InvalidArgumentException('关系预测需要源实体和目标实体');
                }
                break;
            case 'rule_based_inference':
                if (!isset(['entities']) && !isset(['relations'])) {
                    throw new InvalidArgumentException('基于规则的推理需要初始实体或关系');
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
    private function findPath(array ): array
    {
         = ['source_entity'];
         = ['target_entity'];
         = ['max_depth'] ?? ->config['max_inference_depth'];
         = ['relation_types'] ?? null;
        
        // 初始化结果
         = [
            'paths' => [],
            'source_entity' => ,
            'target_entity' => 
        ];
        
        // 使用广度优先搜索查找路径
         = [[['id']]]; // 初始路径只包含源实体ID
         = [['id'] => true]; // 记录已访问的实体
         = 0;
        
        while (!empty() &&  < ->config['max_results']) {
             = array_shift();
             = end();
            
            // 如果到达目标实体，则记录路径
            if ( === ['id']) {
                 = ->buildCompletePath();
                ['paths'][] = ;
                ++;
                continue;
            }
            
            // 如果达到最大深度，则不再扩展
            if (count() >= ) {
                continue;
            }
            
            // 获取当前实体的所有关系
             = ->graphStore->getEntityRelations(, );
            
            // 遍历关系，扩展路径
            foreach ( as ) {
                 = (['source_id'] === ) ? ['target_id'] : ['source_id'];
                
                // 避免环路
                if (isset([])) {
                    continue;
                }
                
                // 添加到队列和已访问集合
                 = ;
                [] = ;
                [] = ;
                [] = true;
            }
        }
        
        return ;
    }

    /**
     * 构建完整路径
     * 
     * @param array  路径中的实体ID列表
     * @return array 完整路径信息
     */
    private function buildCompletePath(array ): array
    {
         = [];
         = [];
        
        // 获取路径中的每个实体
        foreach ( as ) {
             = ->graphStore->getEntityById();
            if () {
                [] = ;
            }
        }
        
        // 获取相邻实体之间的关系
        for ( = 0;  < count() - 1; ++) {
             = [];
             = [ + 1];
             = ->graphStore->getRelationBetween(, );
            if () {
                [] = ;
            }
        }
        
        return [
            'entities' => ,
            'relations' => ,
            'confidence' => ->calculatePathConfidence()
        ];
    }

    /**
     * 计算路径置信度
     * 
     * @param array  路径中的关系列表
     * @return float 路径置信度
     */
    private function calculatePathConfidence(array ): float
    {
        if (empty()) {
            return 0.0;
        }
        
        // 计算所有关系置信度的乘积，并根据路径长度进行调整
         = 1.0;
        foreach ( as ) {
             *= ['confidence'];
        }
        
        // 路径越长，置信度越低
         = 1.0 / (1.0 + 0.1 * (count() - 1));
         *= ;
        
        return ;
    }

    /**
     * 实体补全
     * 
     * @param array  查询参数
     * @return array 实体补全结果
     */
    private function completeEntity(array ): array
    {
         = ['source_entity'];
         = ['relation'];
         = ['limit'] ?? ->config['max_results'];
         = ['confidence_threshold'] ?? ->config['confidence_threshold'];
        
        // 初始化结果
         = [
            'source_entity' => ,
            'relation' => ,
            'target_entities' => []
        ];
        
        // 直接查询满足关系的目标实体
         = ->graphStore->getRelatedEntities(['id'], );
        
        // 过滤低置信度结果
        foreach ( as ) {
            if (['confidence'] >= ) {
                ['target_entities'][] = ;
            }
        }
        
        // 如果直接结果不足，尝试使用规则推理
        if (count(['target_entities']) <  && ->config['enable_rule_learning']) {
             = ->inferRelatedEntities(, , );
            
            // 合并结果，避免重复
             = array_map(function() { return ['id']; }, ['target_entities']);
            foreach ( as ) {
                if (!in_array(['id'], )) {
                    ['target_entities'][] = ;
                    [] = ['id'];
                    
                    // 达到限制数量则停止
                    if (count(['target_entities']) >= ) {
                        break;
                    }
                }
            }
        }
        
        // 按置信度排序
        usort(['target_entities'], function(, ) {
            return ['confidence'] <=> ['confidence'];
        });
        
        return ;
    }

    /**
     * 推理相关实体
     * 
     * @param array  源实体
     * @param string  目标关系类型
     * @param float  置信度阈值
     * @return array 推理得到的相关实体列表
     */
    private function inferRelatedEntities(array , string , float ): array
    {
         = [];
        
        // 遍历所有规则
        foreach (->rules as ) {
            // 对于组合规则
            if (['type'] === 'composite' && ['inferred_relation'] === ) {
                // 链式规则: A与B有关系1，B与C有关系2 => A与C有关系3
                if (['pattern'] === 'chain') {
                    // 获取与源实体有关系1的中间实体
                     = ->graphStore->getRelatedEntities(['id'], ['relation1']);
                    
                    foreach ( as ) {
                        // 获取与中间实体有关系2的目标实体
                         = ->graphStore->getRelatedEntities(['id'], ['relation2']);
                        
                        foreach ( as ) {
                            // 计算推理置信度
                             = ['confidence'] * ['confidence'] * ['confidence_factor'];
                            
                            if ( >= ) {
                                ['confidence'] = ;
                                ['inferred'] = true;
                                ['inference_path'] = [
                                    'rule' => ['name'],
                                    'intermediate' => ['id']
                                ];
                                [] = ;
                            }
                        }
                    }
                }
                // 共同目标规则: A与C有关系1，B与C有关系2 => A与B有关系3
                else if (['pattern'] === 'common_target') {
                    // 获取与源实体有关系1的共同目标实体
                     = ->graphStore->getRelatedEntities(['id'], ['relation1']);
                    
                    foreach ( as ) {
                        // 获取与共同目标有关系2的目标实体
                         = ->graphStore->getEntitiesWithRelation(['relation2'], ['id']);
                        
                        foreach ( as ) {
                            // 计算推理置信度
                             = ['confidence'] * ['confidence'] * ['confidence_factor'];
                            
                            if ( >= ) {
                                ['confidence'] = ;
                                ['inferred'] = true;
                                ['inference_path'] = [
                                    'rule' => ['name'],
                                    'common_target' => ['id']
                                ];
                                [] = ;
                            }
                        }
                    }
                }
            }
            // 对于对称性规则
            else if (['type'] === 'symmetric' && ['relation'] === ) {
                // 如果A与B有对称关系，则B与A也有同样的关系
                 = ->graphStore->getEntitiesWithRelation(['relation'], ['id']);
                
                foreach ( as ) {
                     = ['confidence'] * ['confidence_factor'];
                    
                    if ( >= ) {
                        ['confidence'] = ;
                        ['inferred'] = true;
                        ['inference_path'] = [
                            'rule' => ['name']
                        ];
                        [] = ;
                    }
                }
            }
            // 对于逆关系规则
            else if (['type'] === 'inverse' && ['relation2'] === ) {
                // 如果A与B有关系1，则B与A有关系2
                 = ->graphStore->getEntitiesWithRelation(['relation1'], ['id']);
                
                foreach ( as ) {
                     = ['confidence'] * ['confidence_factor'];
                    
                    if ( >= ) {
                        ['confidence'] = ;
                        ['inferred'] = true;
                        ['inference_path'] = [
                            'rule' => ['name']
                        ];
                        [] = ;
                    }
                }
            }
        }
        
        return ;
    }

    /**
     * 预测关系
     * 
     * @param array  查询参数
     * @return array 关系预测结果
     */
    private function predictRelation(array ): array
    {
         = ['source_entity'];
         = ['target_entity'];
         = ['confidence_threshold'] ?? ->config['confidence_threshold'];
        
        // 初始化结果
         = [
            'source_entity' => ,
            'target_entity' => ,
            'relations' => []
        ];
        
        // 直接查询两个实体之间的关系
         = ->graphStore->getRelationsBetween(['id'], ['id']);
        
        // 过滤低置信度关系
        foreach ( as ) {
            if (['confidence'] >= ) {
                ['relations'][] = ;
            }
        }
        
        // 如果启用规则推理，尝试推理可能的关系
        if (->config['enable_rule_learning']) {
             = ->inferPossibleRelations(, , );
            
            // 合并结果，避免重复
             = array_map(function() { return ['type']; }, ['relations']);
            foreach ( as ) {
                if (!in_array(['type'], )) {
                    ['relations'][] = ;
                    [] = ['type'];
                }
            }
        }
        
        // 按置信度排序
        usort(['relations'], function(, ) {
            return ['confidence'] <=> ['confidence'];
        });
        
        return ;
    }

    /**
     * 推理可能的关系
     * 
     * @param array  源实体
     * @param array  目标实体
     * @param float  置信度阈值
     * @return array 推理得到的可能关系
     */
    private function inferPossibleRelations(array , array , float ): array
    {
         = [];
        
        // 遍历所有规则
        foreach (->rules as ) {
            // 对于组合规则
            if (['type'] === 'composite') {
                // 链式规则: A与B有关系1，B与C有关系2 => A与C有关系3
                if (['pattern'] === 'chain') {
                    // 查找可能的中间实体
                     = ->findIntermediateEntities(['id'], ['id'], ['relation1'], ['relation2']);
                    
                    if (!empty()) {
                        // 创建推理关系
                         = [
                            'id' => md5(['id'] . ['id'] . ['inferred_relation']),
                            'source_id' => ['id'],
                            'target_id' => ['id'],
                            'type' => ['inferred_relation'],
                            'inferred' => true,
                            'confidence' => ->calculateInferredRelationConfidence(, ['confidence_factor']),
                            'inference_path' => [
                                'rule' => ['name'],
                                'intermediates' => array_map(function() { return ['id']; }, )
                            ]
                        ];
                        
                        if (['confidence'] >= ) {
                            [] = ;
                        }
                    }
                }
            }
            // 对于对称性规则
            else if (['type'] === 'symmetric') {
                // 检查是否存在反向关系
                 = ->graphStore->getRelationBetween(['id'], ['id'], ['relation']);
                
                if () {
                     = [
                        'id' => md5(['id'] . ['id'] . ['relation']),
                        'source_id' => ['id'],
                        'target_id' => ['id'],
                        'type' => ['relation'],
                        'inferred' => true,
                        'confidence' => ['confidence'] * ['confidence_factor'],
                        'inference_path' => [
                            'rule' => ['name'],
                            'symmetric_relation_id' => ['id']
                        ]
                    ];
                    
                    if (['confidence'] >= ) {
                        [] = ;
                    }
                }
            }
            // 对于逆关系规则
            else if (['type'] === 'inverse') {
                // 检查是否存在逆关系
                 = ->graphStore->getRelationBetween(['id'], ['id'], ['relation1']);
                
                if () {
                     = [
                        'id' => md5(['id'] . ['id'] . ['relation2']),
                        'source_id' => ['id'],
                        'target_id' => ['id'],
                        'type' => ['relation2'],
                        'inferred' => true,
                        'confidence' => ['confidence'] * ['confidence_factor'],
                        'inference_path' => [
                            'rule' => ['name'],
                            'inverse_relation_id' => ['id']
                        ]
                    ];
                    
                    if (['confidence'] >= ) {
                        [] = ;
                    }
                }
            }
        }
        
        return ;
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
    private function findIntermediateEntities(string , string , string , string ): array
    {
         = [];
        
        // 获取与源实体有关系1的实体
         = ->graphStore->getRelatedEntities(, );
        
        // 获取与目标实体有关系2的实体
         = ->graphStore->getEntitiesWithRelation(, );
        
        // 找出共同的实体（交集）
         = array_map(function() { return ['id']; }, );
         = array_map(function() { return ['id']; }, );
        
         = array_intersect(, );
        
        // 构建中间实体列表
        foreach ( as ) {
             = array_search(, );
             = array_search(, );
            
            if ( !== false &&  !== false) {
                [] = [];
            }
        }
        
        return ;
    }
    
    /**
     * 计算推理关系置信度
     * 
     * @param array  中间实体列表
     * @param float  置信度因子
     * @return float 推理关系置信度
     */
    private function calculateInferredRelationConfidence(array , float ): float
    {
        if (empty()) {
            return 0.0;
        }
        
        // 使用最高置信度的中间实体路径
         = 0.0;
        foreach ( as ) {
             = ['confidence'] * ;
            if ( > ) {
                 = ;
            }
        }
        
        return ;
    }

    /**
     * 应用规则进行推理
     * 
     * @param array  查询参数
     * @return array 推理结果
     */
    private function applyRules(array ): array
    {
         = ['rules'] ?? null;
         = ['max_depth'] ?? ->config['max_inference_depth'];
         = ['confidence_threshold'] ?? ->config['confidence_threshold'];
         = ['entity_id'] ?? null;
         = ['relation_types'] ?? null;
        
        // 初始化结果
         = [
            'inferred_facts' => [],
            'applied_rules' => []
        ];
        
        // 如果指定了实体ID，则从该实体开始推理
        if () {
             = ->graphStore->getEntityById();
            if (!) {
                return ;
            }
            
            ['entity'] = ;
             = ->inferFactsFromEntity(, , , , );
            ['inferred_facts'] = ;
        }
        // 否则对所有实体应用规则
        else {
             = ->graphStore->getAllEntities();
            foreach ( as ) {
                 = ->inferFactsFromEntity(, , , , 1);
                if (!empty()) {
                    ['inferred_facts'] = array_merge(['inferred_facts'], );
                }
                
                // 限制推理结果数量
                if (count(['inferred_facts']) >= ->config['max_results']) {
                    break;
                }
            }
        }
        
        // 记录应用的规则
         = [];
        foreach (['inferred_facts'] as ) {
            if (isset(['inference_path']['rule']) && !in_array(['inference_path']['rule'], )) {
                [] = ['inference_path']['rule'];
            }
        }
        
        foreach ( as ) {
            foreach (->rules as ) {
                if (['name'] === ) {
                    ['applied_rules'][] = ;
                    break;
                }
            }
        }
        
        return ;
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
    private function inferFactsFromEntity(array , ?array  = null, ?array  = null, float  = 0.5, int  = 1): array
    {
         = [];
         = []; // 用于避免重复推理
         = [['entity' => , 'depth' => 0]];
        
        while (!empty() && count() < ->config['max_results']) {
             = array_shift();
             = ['entity'];
             = ['depth'];
            
            if ( >= ) {
                continue;
            }
            
            // 对当前实体应用所有规则
            foreach (->rules as ) {
                // 如果指定了规则名称，则只应用这些规则
                if ( !== null && !in_array(['name'], )) {
                    continue;
                }
                
                // 根据规则类型应用不同的推理策略
                switch (['type']) {
                    case 'composite':
                         = ->applyCompositeRule(, , , );
                        break;
                    case 'symmetric':
                         = ->applySymmetricRule(, , , );
                        break;
                    case 'transitive':
                         = ->applyTransitiveRule(, , , );
                        break;
                    case 'hierarchical':
                         = ->applyHierarchicalRule(, , );
                        break;
                    case 'inverse':
                         = ->applyInverseRule(, , , );
                        break;
                    default:
                         = [];
                }
                
                // 添加新推理的事实到结果中
                foreach ( as ) {
                     = ['type'] . '_' . ['source_id'] . '_' . ['target_id'];
                    
                    if (!isset([])) {
                        [] = ;
                        [] = true;
                        
                        // 如果推理深度未达到最大值，将相关实体加入队列继续推理
                        if ( + 1 < ) {
                             = ->graphStore->getEntityById(['target_id']);
                            if () {
                                [] = ['entity' => , 'depth' =>  + 1];
                            }
                        }
                    }
                }
            }
        }
        
        return ;
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
    private function applyCompositeRule(array , array , ?array  = null, float  = 0.5): array
    {
         = [];
        
        // 链式规则: A与B有关系1，B与C有关系2 => A与C有关系3
        if (['pattern'] === 'chain') {
            // 如果指定了关系类型，且推理关系不在列表中，则跳过
            if ( !== null && !in_array(['inferred_relation'], )) {
                return [];
            }
            
            // 获取与源实体有关系1的中间实体
             = ->graphStore->getRelatedEntities(['id'], ['relation1']);
            
            foreach ( as ) {
                // 获取与中间实体有关系2的目标实体
                 = ->graphStore->getRelatedEntities(['id'], ['relation2']);
                
                foreach ( as ) {
                    // 避免自环
                    if (['id'] === ['id']) {
                        continue;
                    }
                    
                    // 计算推理置信度
                     = ['confidence'] * ['confidence'] * ['confidence_factor'];
                    
                    if ( >= ) {
                        // 创建推理关系
                        [] = [
                            'id' => md5(['id'] . ['id'] . ['inferred_relation']),
                            'source_id' => ['id'],
                            'target_id' => ['id'],
                            'type' => ['inferred_relation'],
                            'inferred' => true,
                            'confidence' => ,
                            'inference_path' => [
                                'rule' => ['name'],
                                'intermediate' => ['id']
                            ]
                        ];
                    }
                }
            }
        }
        // 共同目标规则: A与C有关系1，B与C有关系2 => A与B有关系3
        else if (['pattern'] === 'common_target') {
            // 如果指定了关系类型，且推理关系不在列表中，则跳过
            if ( !== null && !in_array(['inferred_relation'], )) {
                return [];
            }
            
            // 获取与源实体有关系1的共同目标实体
             = ->graphStore->getRelatedEntities(['id'], ['relation1']);
            
            foreach ( as ) {
                // 获取与共同目标有关系2的目标实体
                 = ->graphStore->getEntitiesWithRelation(['relation2'], ['id']);
                
                foreach ( as ) {
                    // 避免自环
                    if (['id'] === ['id']) {
                        continue;
                    }
                    
                    // 计算推理置信度
                     = ['confidence'] * ['confidence'] * ['confidence_factor'];
                    
                    if ( >= ) {
                        // 创建推理关系
                        [] = [
                            'id' => md5(['id'] . ['id'] . ['inferred_relation']),
                            'source_id' => ['id'],
                            'target_id' => ['id'],
                            'type' => ['inferred_relation'],
                            'inferred' => true,
                            'confidence' => ,
                            'inference_path' => [
                                'rule' => ['name'],
                                'common_target' => ['id']
                            ]
                        ];
                    }
                }
            }
        }
        
        return ;
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
    private function applySymmetricRule(array , array , ?array  = null, float  = 0.5): array
    {
         = [];
        
        // 如果指定了关系类型，且规则关系不在列表中，则跳过
        if ( !== null && !in_array(['relation'], )) {
            return [];
        }
        
        // 获取与实体有关系的目标实体
         = ->graphStore->getEntitiesWithRelation(['relation'], ['id']);
        
        foreach ( as ) {
            // 检查是否已存在反向关系
             = ->graphStore->getRelationBetween(['id'], ['id'], ['relation']);
            
            // 如果不存在反向关系，则创建推理关系
            if (!) {
                 = ['confidence'] * ['confidence_factor'];
                
                if ( >= ) {
                    [] = [
                        'id' => md5(['id'] . ['id'] . ['relation']),
                        'source_id' => ['id'],
                        'target_id' => ['id'],
                        'type' => ['relation'],
                        'inferred' => true,
                        'confidence' => ,
                        'inference_path' => [
                            'rule' => ['name'],
                            'symmetric_relation_id' => md5(['id'] . ['id'] . ['relation'])
                        ]
                    ];
                }
            }
        }
        
        return ;
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
    private function applyTransitiveRule(array , array , ?array  = null, float  = 0.5): array
    {
         = [];
        
        // 如果指定了关系类型，且规则关系不在列表中，则跳过
        if ( !== null && !in_array(['relation'], )) {
            return [];
        }
        
        // 获取与实体有指定关系的目标实体
         = ->graphStore->getRelatedEntities(['id'], ['relation']);
        
        foreach ( as ) {
            // 获取与中间实体有相同关系的目标实体
             = ->graphStore->getRelatedEntities(['id'], ['relation']);
            
            foreach ( as ) {
                // 避免自环和直接关系
                if (['id'] === ['id'] || ['id'] === ['id']) {
                    continue;
                }
                
                // 检查是否已存在直接关系
                 = ->graphStore->getRelationBetween(['id'], ['id'], ['relation']);
                
                // 如果不存在直接关系，则创建推理关系
                if (!) {
                     = ['confidence'] * ['confidence'] * ['confidence_factor'];
                    
                    if ( >= ) {
                        [] = [
                            'id' => md5(['id'] . ['id'] . ['relation']),
                            'source_id' => ['id'],
                            'target_id' => ['id'],
                            'type' => ['relation'],
                            'inferred' => true,
                            'confidence' => ,
                            'inference_path' => [
                                'rule' => ['name'],
                                'intermediate' => ['id']
                            ]
                        ];
                    }
                }
            }
        }
        
        return ;
    }

    /**
     * 应用层次关系规则
     * 
     * @param array  实体
     * @param array  规则
     * @param float  置信度阈值
     * @return array 推理得到的事实列表
     */
    private function applyHierarchicalRule(array , array , float  = 0.5): array
    {
         = [];
        
        // 检查实体类型是否符合规则要求
        if (['source_type'] !== null && ['type'] !== ['source_type']) {
            return [];
        }
        
        // 获取与实体有指定关系的目标实体
         = ->graphStore->getRelatedEntities(['id'], ['relation']);
        
        foreach ( as ) {
            // 检查目标实体类型是否符合规则要求
            if (['target_type'] !== null && ['type'] !== ['target_type']) {
                continue;
            }
            
            // 计算推理置信度
             = ['confidence'] * ['confidence_factor'];
            
            if ( >= ) {
                // 创建推理事实：实体继承目标实体的类型
                [] = [
                    'id' => md5(['id'] . '_type_' . ['inferred_type']),
                    'entity_id' => ['id'],
                    'type' => 'type_assertion',
                    'value' => ['inferred_type'],
                    'inferred' => true,
                    'confidence' => ,
                    'inference_path' => [
                        'rule' => ['name'],
                        'relation_target' => ['id']
                    ]
                ];
            }
        }
        
        return ;
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
    private function applyInverseRule(array , array , ?array  = null, float  = 0.5): array
    {
         = [];
        
        // 如果指定了关系类型，且推理关系不在列表中，则跳过
        if ( !== null && !in_array(['relation2'], )) {
            return [];
        }
        
        // 获取与实体有关系1的目标实体
         = ->graphStore->getEntitiesWithRelation(['relation1'], ['id']);
        
        foreach ( as ) {
            // 检查是否已存在逆关系
             = ->graphStore->getRelationBetween(['id'], ['id'], ['relation2']);
            
            // 如果不存在逆关系，则创建推理关系
            if (!) {
                 = ['confidence'] * ['confidence_factor'];
                
                if ( >= ) {
                    [] = [
                        'id' => md5(['id'] . ['id'] . ['relation2']),
                        'source_id' => ['id'],
                        'target_id' => ['id'],
                        'type' => ['relation2'],
                        'inferred' => true,
                        'confidence' => ,
                        'inference_path' => [
                            'rule' => ['name'],
                            'inverse_relation_id' => md5(['id'] . ['id'] . ['relation1'])
                        ]
                    ];
                }
            }
        }
        
        return ;
    }
}
