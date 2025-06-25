<?php
/**
 * �ļ�����ReasoningEngine.php
 * ������������������ - ��֪ʶͼ���Ͻ�������
 * ����ʱ�䣺2025-01-XX
 * ����޸ģ�?025-01-XX
 * �汾��1.0.0
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
 * ��������
 * 
 * ��֪ʶͼ���Ͻ���������֧�ֶ��������������? */
class ReasoningEngine
{
    /**
     * ���ò���
     */
    private array $config;
    
    /**
     * �������򼯺�
     */
    private array $rules = [];
    
    /**
     * ֪ʶͼ�״洢�ӿ�
     */
    private ?GraphStoreInterface $graphStore = null;

    /**
     * ���캯��
     * 
     * @param GraphStoreInterface $graphStore ֪ʶͼ�״洢�ӿ�
     * @param array $config ���ò���
     */
    public function __construct(GraphStoreInterface $graphStore, array $config = [])
    {
        $this->graphStore = $graphStore;
        $this->config = array_merge($this->getDefaultConfig(), $config];
        $this->initializeRules(];
    }
    
    /**
     * ��ȡĬ������
     * 
     * @return array Ĭ������
     */
    private function getDefaultConfig(): array
    {
        return [
            'max_inference_depth' => 3,  // ����������
            'confidence_threshold' => 0.5,  // ����������Ŷ����?            'enable_rule_learning' => false,  // �Ƿ����ù���ѧϰ
            'enable_probabilistic_reasoning' => true,  // �Ƿ����ø�������
            'max_results' => 100,  // ��󷵻ؽ������
            'timeout' => 5000  // ������ʱʱ�䣨���룩
        ];
    }
    
    /**
     * ��ʼ����������
     */
    private function initializeRules(): void
    {
        // ���Ӵ����Թ���
        $this->addTransitiveRules(];
        
        // ���ӶԳ��Թ���
        $this->addSymmetricRules(];
        
        // �������ϵ����?        $this->addInverseRules(];
        
        // ���Ӳ�ι�ϵ����?        $this->addHierarchicalRules(];
        
        // ������Ϲ���?        $this->addCompositeRules(];
    }

    /**
     * ���Ӵ����Թ���
     */
    private function addTransitiveRules(): void
    {
        // λ�ô�����: Aλ��B��Bλ��C => Aλ��C
        $this->rules[] = [
            'name' => 'location_transitivity',
            'type' => 'transitive',
            'relation' => 'LocatedIn',
            'confidence_factor' => 0.9
        ];
        
        // ���ִ�����: A��B��һ���֣�B��C��һ���� => A��C��һ����
        $this->rules[] = [
            'name' => 'part_of_transitivity',
            'type' => 'transitive',
            'relation' => 'PartOf',
            'confidence_factor' => 0.9
        ];
        
        // ����������: A����B��B����C => A����C
        $this->rules[] = [
            'name' => 'contains_transitivity',
            'type' => 'transitive',
            'relation' => 'Contains',
            'confidence_factor' => 0.9
        ];
    }
    
    /**
     * ���ӶԳ��Թ���
     */
    private function addSymmetricRules(): void
    {
        // ��ż��ϵ�Գ���: A��B����ż => B��A����ż
        $this->rules[] = [
            'name' => 'spouse_symmetry',
            'type' => 'symmetric',
            'relation' => 'Spouse',
            'confidence_factor' => 1.0
        ];
        
        // �ֵܽ��ù�ϵ�Գ���: A��B���ֵܽ��� => B��A���ֵܽ���
        $this->rules[] = [
            'name' => 'sibling_symmetry',
            'type' => 'symmetric',
            'relation' => 'Sibling',
            'confidence_factor' => 1.0
        ];
    }
    
    /**
     * �������ϵ����?     */
    private function addInverseRules(): void
    {
        // ��ĸ-��Ů����? A��B�ĸ�ĸ => B��A����Ů
        $this->rules[] = [
            'name' => 'parent_child_inverse',
            'type' => 'inverse',
            'relation1' => 'Parent',
            'relation2' => 'Child',
            'confidence_factor' => 1.0
        ];
        
        // ӵ��-��ӵ������? Aӵ��B => B��Aӵ��
        $this->rules[] = [
            'name' => 'owns_owned_by_inverse',
            'type' => 'inverse',
            'relation1' => 'Owns',
            'relation2' => 'OwnedBy',
            'confidence_factor' => 1.0
        ];
        
        // ����-����������? A������B => B��A����
        $this->rules[] = [
            'name' => 'created_created_by_inverse',
            'type' => 'inverse',
            'relation1' => 'Created',
            'relation2' => 'CreatedBy',
            'confidence_factor' => 1.0
        ];
    }

    /**
     * ���Ӳ�ι�ϵ����?     */
    private function addHierarchicalRules(): void
    {
        // ��֯��ι��? A��B��һ���֣�B����֯ => A����֯
        $this->rules[] = [
            'name' => 'organization_hierarchy',
            'type' => 'hierarchical',
            'relation' => 'PartOf',
            'source_type' => null,
            'target_type' => 'Organization',
            'inferred_type' => 'Organization',
            'confidence_factor' => 0.8
        ];
        
        // ������ι��? Aλ��B��B�ǵص� => A�ǵص�
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
     * ������Ϲ���?     */
    private function addCompositeRules(): void
    {
        // ͬ¹ϵ: A��C������B��C���� => A��B��ͬ��
        $this->rules[] = [
            'name' => 'colleague_relation',
            'type' => 'composite',
            'relation1' => 'WorksFor',
            'relation2' => 'WorksFor',
            'inferred_relation' => 'Colleague',
            'pattern' => 'common_target',
            'confidence_factor' => 0.7
        ];
        
        // ������? A��B�ĸ�ĸ��B��C�ĸ�ĸ => A��C���游ĸ
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
     * ִ������
     * 
     * @param array  ��ѯ����
     * @return array �������?     * @throws Exception ������������г��ִ���?     */
    public function reason(array $query): array
    {
        // ֤ѯ
        $this->validateQuery($query];
        
        // ʼ
        $result = [];
        $startTime = microtime(true];
        
        // ݲѯִвͬ
        switch ($query['type']) {
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
                throw new InvalidArgumentException('ֵ֧Ĳѯ: ' . $query['type']];
        }
        
        // 鳬�?        $duration = (microtime(true) - $startTime) * 1000; // תΪ
        if ($duration > $this->config['timeout']) {
            $result['timeout'] = true;
            $result['message'] = 'ʱܲ';
        }
        
        // Ʒؽ
        if (isset($result['entities']) && count($result['entities']) > $this->config['max_results']) {
            $result['entities'] = array_slice($result['entities'],  0, $this->config['max_results']];
            $result['truncated'] = true;
        }
        
        if (isset($result['relations']) && count($result['relations']) > $this->config['max_results']) {
            $result['relations'] = array_slice($result['relations'],  0, $this->config['max_results']];
            $result['truncated'] = true;
        }
        
        return $result;
    }

    /**
     * ֤ѯ
     * 
     * @param array  ѯ
     * @throws InvalidArgumentException ����������?     */
    private function validateQuery(array $query): void
    {
        // Ĳ
        if (!isset($query['type'])) {
            throw new InvalidArgumentException('ȱٱĲѯͲ'];
        }
        
        // ֤ѯ
        $supportedTypes = ['path_finding', 'entity_completion', 'relation_prediction', 'rule_based_inference'];
        if (!in_[$query['type'],  $supportedTypes)) {
            throw new InvalidArgumentException('ЧĲѯ: ' . $query['type']];
        }
        
        // ݲѯ֤ض
        switch ($query['type']) {
            case 'path_finding':
                if (!isset($query['source_entity']) || !isset($query['target_entity'])) {
                    throw new InvalidArgumentException('·ҪԴʵĿʵ'];
                }
                break;
            case 'entity_completion':
                if (!isset($query['source_entity']) || !isset($query['relation'])) {
                    throw new InvalidArgumentException('ʵ岹ȫҪԴʵ͹�?];
                }
                break;
            case 'relation_prediction':
                if (!isset($query['source_entity']) || !isset($query['target_entity'])) {
                    throw new InvalidArgumentException('ϵԤҪԴʵĿʵ'];
                }
                break;
            case 'rule_based_inference':
                if (!isset($query['entities']) && !isset($query['relations'])) {
                    throw new InvalidArgumentException('ڹҪʼʵϵ'];
                }
                break;
        }
    }

    /**
     * ·
     * 
     * @param array  ѯ
     * @return array ·ҽ
     */
    private function findPath(array $query): array
    {
        $sourceEntity = $query['source_entity'];
        $targetEntity = $query['target_entity'];
        $maxDepth = $query['max_depth'] ?? $this->config['max_inference_depth'];
        $relationTypes = $query['relation_types'] ?? null;
        
        // ʼ
        $result = [
            'paths' => [], 
            'source_entity' => $sourceEntity,
            'target_entity' => $targetEntity
        ];
        
        // ʹ�ù��������������·��?        $paths = [[['id']]];// ��ʼ·��ֻ����Դʵ��ID
        $visited = [['id') => true];// ��¼�ѷ��ʵ�ʵ��
        $pathCount = 0;
        
        while (!empty($paths) && $pathCount < $this->config['max_results']) {
            $path = array_shift($paths];
            $currentEntity = end($path];
            
            // Ŀʵ壬¼�?            if ($currentEntity === ['id']) {
                $result['paths'][] = $this->buildCompletePath($path];
                $pathCount++;
                continue;
            }
            
            // ﵽȣ�?            if (count($path) >= $maxDepth) {
                continue;
            }
            
            // ȡǰʵйϵ
            $relations = $this->graphStore->getEntityRelations($currentEntity, $relationTypes];
            
            // ϵչ·
            foreach ($relations as $relation) {
                $nextEntity = ($relation['source_id'] === $currentEntity) ? $relation['target_id'):  $relation['source_id'];
                
                // ⻷�?                if (isset($visited[$nextEntity])) {
                    continue;
                }
                
                // ӵкѷʼ
                $newPath = $path;
                $newPath[] = $nextEntity;
                $visited[$nextEntity] = true;
            }
        }
        
        return $result;
    }

    /**
     * ·
     * 
     * @param array  ·еʵIDб
     * @return array ·Ϣ
     */
    private function buildCompletePath(array $path): array
    {
        $entities = [];
        $relations = [];
        
        // ȡ·еÿʵ
        foreach ($path as $entityId) {
            $entity = $this->graphStore->getEntityById($entityId];
            if ($entity) {
                $entities[] = $entity;
            }
        }
        
        // ȡʵ֮Ĺϵ
        for ($i = 0; $i < count($entities) - 1; $i++) {
            $relation = $this->graphStore->getRelationBetween($entities[$i]['id'],  $entities[$i + 1]['id']];
            if ($relation) {
                $relations[] = $relation;
            }
        }
        
        return [
            'entities' => $entities,
            'relations' => $relations,
            'confidence' => $this->calculatePathConfidence($relations)
        ];
    }

    /**
     * ·Ŷ
     * 
     * @param array  ·еĹϵб
     * @return float ·Ŷ
     */
    private function calculatePathConfidence(array $relations): float
    {
        if (empty($relations)) {
            return 0.0;
        }
        
        // йϵŶȵĳ˻·Ƚе
        $confidence = 1.0;
        foreach ($relations as $relation) {
            $confidence *= $relation['confidence'];
        }
        
        // ·ԽŶԽ
        $confidence = 1.0 / (1.0 + 0.1 * (count($relations) - 1)];
        $confidence *= $confidence;
        
        return $confidence;
    }

    /**
     * ʵ岹�?     * 
     * @param array  ѯ
     * @return array ʵ岹�?     */
    private function completeEntity(array $query): array
    {
        $sourceEntity = $query['source_entity'];
        $relation = $query['relation'];
        $limit = $query['limit'] ?? $this->config['max_results'];
        $confidenceThreshold = $query['confidence_threshold'] ?? $this->config['confidence_threshold'];
        
        // ʼ
        $result = [
            'source_entity' => $sourceEntity,
            'relation' => $relation,
            'target_entities' => []
        ];
        
        // ֱӲѯϵĿʵ
        $entities = $this->graphStore->getRelatedEntities('id'],  $relation];
        
        // ˵ŶȽ
        foreach ($entities as $entity) {
            if ($entity['confidence'] >= $confidenceThreshold) {
                $result['target_entities'][] = $entity;
            }
        }
        
        // ֱӽ㣬ʹ�?        if (count($result['target_entities']) < $limit && $this->config['enable_rule_learning']) {
            $inferredEntities = $this->inferRelatedEntities($sourceEntity, $relation, $confidenceThreshold];
            
            // ϲظ
            $targetEntities = array_map(function($entity) { return $entity['id']; }, $result['target_entities']];
            foreach ($inferredEntities as $entity) {
                if (!in_[$entity['id'],  $targetEntities)) {
                    $result['target_entities'][] = $entity;
                    $targetEntities[] = $entity['id'];
                    
                    // ﵽͣ�?                    if (count($result['target_entities']) >= $limit) {
                        break;
                    }
                }
            }
        }
        
        // Ŷ
        usort($result['target_entities'],  function($a, $b) {
            return $a['confidence'] <=> $b['confidence'];
        }];
        
        return $result;
    }

    /**
     * ʵ
     * 
     * @param array  Դʵ
     * @param string  Ŀϵ
     * @param float  Ŷֵ
     * @return array õʵб
     */
    private function inferRelatedEntities(array $sourceEntity, string $relation, float $confidenceThreshold): array
    {
        $inferredEntities = [];
        
        // й
        foreach ($this->rules as $rule) {
            // Ϲ
            if ($rule['type'] === 'composite' && $rule['inferred_relation'] === $relation) {
                // ʽ: ABйϵ1BCйϵ2 => ACйϵ3
                if ($rule['pattern'] === 'chain') {
                    // ȡԴʵйϵ1мʵ
                    $intermediateEntities = $this->graphStore->getRelatedEntities('id'],  ['relation1']];
                    
                    foreach ($intermediateEntities as $intermediateEntity) {
                        // ȡмʵйϵ2Ŀʵ
                        $targetEntities = $this->graphStore->getRelatedEntities('id'],  ['relation2']];
                        
                        foreach ($targetEntities as $targetEntity) {
                            // Ŷ
                            $confidence = $intermediateEntity['confidence'] * $targetEntity['confidence'] * $rule['confidence_factor'];
                            
                            if ($confidence >= $confidenceThreshold) {
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
                // ͬĿ: ACйϵ1BCйϵ2 => ABйϵ3
                else if ($rule['pattern'] === 'common_target') {
                    // ȡԴʵйϵ1ĹͬĿʵ
                    $commonTargetEntities = $this->graphStore->getRelatedEntities('id'],  ['relation1']];
                    
                    foreach ($commonTargetEntities as $commonTargetEntity) {
                        // ȡ빲ͬĿй�?Ŀʵ
                        $targetEntities = $this->graphStore->getEntitiesWithRelation('relation2'],  ['id']];
                        
                        foreach ($targetEntities as $targetEntity) {
                            // Ŷ
                            $confidence = $commonTargetEntity['confidence'] * $targetEntity['confidence'] * $rule['confidence_factor'];
                            
                            if ($confidence >= $confidenceThreshold) {
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
            // ڶԳԹ
            else if ($rule['type'] === 'symmetric' && $rule['relation'] === $relation) {
                // ABжԳƹϵBAҲͬĹϵ
                $entities = $this->graphStore->getEntitiesWithRelation('relation'],  ['id']];
                
                foreach ($entities as $entity) {
                    $confidence = $entity['confidence'] * $rule['confidence_factor'];
                    
                    if ($confidence >= $confidenceThreshold) {
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
            // ϵ
            else if ($rule['type'] === 'inverse' && $rule['relation2'] === $relation) {
            // �������ϵ����?            else if ($rule['type'] === 'inverse' && $rule['relation2'] === $relation) {
                // ���A��B�й�ϵ1����B��A�й�ϵ2
                $entities = $this->graphStore->getEntitiesWithRelation('relation1'],  ['id']];
                
                foreach ($entities as $entity) {
                    $confidence = $entity['confidence'] * $rule['confidence_factor'];
                    
                    if ($confidence >= $confidenceThreshold) {
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
     * Ԥ����?     * 
     * @param array  ��ѯ����
     * @return array ��ϵԤ����
     */
    private function predictRelation(array $query): array
    {
        $sourceEntity = $query['source_entity'];
        $targetEntity = $query['target_entity'];
        $confidenceThreshold = $query['confidence_threshold'] ?? $this->config['confidence_threshold'];
        
        // ��ʼ�����?        $result = [
            'source_entity' => $sourceEntity,
            'target_entity' => $targetEntity,
            'relations' => []
        ];
        
        // ֱ�Ӳ�ѯ����ʵ��֮��Ĺ��?        $relations = $this->graphStore->getRelationsBetween('id'],  ['id']];
        
        // ���˵����Ŷȹ�ϵ
        foreach ($relations as $relation) {
            if ($relation['confidence'] >= $confidenceThreshold) {
                $result['relations'][] = $relation;
            }
        }
        
        // ������ù��������������������ܵĹ��?        if ($this->config['enable_rule_learning']) {
            $possibleRelations = $this->inferPossibleRelations($sourceEntity, $targetEntity, $confidenceThreshold];
            
            // �ϲ�����������ظ�?            $result['relations'] = array_merge($result['relations'],  $possibleRelations];
        }
        
        // �����Ŷ�����
        usort($result['relations'],  function($a, $b) {
            return $a['confidence'] <=> $b['confidence'];
        }];
        
        return $result;
    }

    /**
     * �������ܵĹ�ϵ
     * 
     * @param array  Դʵ��
     * @param array  Ŀ��ʵ��
     * @param float  ���Ŷ���ֵ
     * @return array �����õ��Ŀ��ܹ�ϵ
     */
    private function inferPossibleRelations(array $sourceEntity, array $targetEntity, float $confidenceThreshold): array
    {
        $possibleRelations = [];
        
        // �������й���
        foreach ($this->rules as $rule) {
            // ������Ϲ���?            if ($rule['type'] === 'composite') {
                // ��ʽ����: A��B�й�ϵ1��B��C�й�ϵ2 => A��C�й�ϵ3
                if ($rule['pattern'] === 'chain') {
                    // ���ҿ��ܵ��м�ʵ��
                    $intermediateEntities = $this->findIntermediateEntities($sourceEntity['id'],  $targetEntity['id'],  $rule['relation1'],  $rule['relation2']];
                    
                    if (!empty($intermediateEntities]) {
                        // ����������ϵ
                        $possibleRelations[] = [
                            'id' => md5($sourceEntity['id'] . $targetEntity['id'] . $rule['inferred_relation']], 
                            'source_id' => $sourceEntity['id'], 
                            'target_id' => $targetEntity['id'], 
                            'type' => $rule['inferred_relation'], 
                            'inferred' => true,
                            'confidence' => $this->calculateInferredRelationConfidence($intermediateEntities, $rule['confidence_factor']], 
                            'inference_path' => [
                                'rule' => $rule['name'], 
                                'intermediates' => array_map(function($entity) { return $entity['id'];}, $intermediateEntities]
                            ]
                        ];
                        
                        if ($possibleRelations[count($possibleRelations) - 1]['confidence'] >= $confidenceThreshold) {
                            $possibleRelations[] = $possibleRelations[count($possibleRelations) - 1];
                        }
                    }
                }
            }
            // ���ڶԳ��Թ���
            else if ($rule['type'] === 'symmetric') {
                // ����Ƿ���ڷ�����?                $relation = $this->graphStore->getRelationBetween($sourceEntity['id'],  $targetEntity['id'],  $rule['relation']];
                
                if ($relation) {
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
                    
                    if ($possibleRelations[count($possibleRelations) - 1]['confidence'] >= $confidenceThreshold) {
                        $possibleRelations[] = $possibleRelations[count($possibleRelations) - 1];
                    }
                }
            }
            // �������ϵ����?            else if ($rule['type'] === 'inverse') {
                // ����Ƿ��������?                $relation = $this->graphStore->getRelationBetween($sourceEntity['id'],  $targetEntity['id'],  $rule['relation1']];
                
                if ($relation) {
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
                    
                    if ($possibleRelations[count($possibleRelations) - 1]['confidence'] >= $confidenceThreshold) {
                        $possibleRelations[] = $possibleRelations[count($possibleRelations) - 1];
                    }
                }
            }
        }
        
        return $possibleRelations;
    }

    /**
     * �����м�ʵ��
     * 
     * @param string  Դʵ��ID
     * @param string  Ŀ��ʵ��ID
     * @param string  ��ϵ1
     * @param string  ��ϵ2
     * @return array �м�ʵ���б�
     */
    private function findIntermediateEntities(string $sourceId, string $targetId, string $relation1, string $relation2): array
    {
        $intermediateEntities = [];
        
        // ��ȡ��Դʵ���й�ϵ1��ʵ��
        $entities = $this->graphStore->getRelatedEntities($sourceId, $relation1];
        
        // ��ȡ��Ŀ��ʵ���й�ϵ2��ʵ��
        $targetEntities = $this->graphStore->getEntitiesWithRelation($targetId, $relation2];
        
        // �ҳ���ͬ��ʵ�壨������
        $commonEntities = array_map(function($entity) { return $entity['id'];}, $entities];
        $targetIds = array_map(function($entity) { return $entity['id'];}, $targetEntities];
        
        $commonIds = array_intersect($commonEntities, $targetIds];
        
        // �����м�ʵ���б�
        foreach ($commonIds as $commonId) {
            $intermediateEntities[] = [
                'id' => $commonId,
                'confidence' => 1.0
            ];
        }
        
        return $intermediateEntities;
    }
    
    /**
     * ����������ϵ���Ŷ�
     * 
     * @param array  �м�ʵ���б�
     * @param float  ���Ŷ�����
     * @return float ������ϵ���Ŷ�
     */
    private function calculateInferredRelationConfidence(array $intermediateEntities, float $confidenceFactor): float
    {
        if (empty($intermediateEntities]) {
            return 0.0;
        }
        
        // ʹ��������Ŷȵ��м�ʵ��·��?        $confidence = 0.0;
        foreach ($intermediateEntities as $entity) {
            $confidence += $entity['confidence'] * $confidenceFactor;
        }
        
        return $confidence;
    }

    /**
     * Ӧ�ù����������?     * 
     * @param array  ��ѯ����
     * @return array �������?     */
    private function applyRules(array $query): array
    {
        $rules = $query['rules'] ?? null;
        $maxDepth = $query['max_depth'] ?? $this->config['max_inference_depth'];
        $confidenceThreshold = $query['confidence_threshold'] ?? $this->config['confidence_threshold'];
        $entityId = $query['entity_id'] ?? null;
        $relationTypes = $query['relation_types'] ?? null;
        
        // ��ʼ�����?        $result = [
            'inferred_facts' => [], 
            'applied_rules' => []
        ];
        
        // ���ָ����ʵ��ID����Ӹ�ʵ�忪ʼ����?        if ($entityId) {
            $entity = $this->graphStore->getEntityById($entityId];
            if (!$entity) {
                return $result;
            }
            
            $result['entity'] = $entity;
            $inferredFacts = $this->inferFactsFromEntity($entity, $rules, $relationTypes, $confidenceThreshold, $maxDepth];
            $result['inferred_facts'] = $inferredFacts;
        }
        // ���������ʵ��Ӧ�ù���?        else {
            $entities = $this->graphStore->getAllEntities(];
            foreach ($entities as $entity) {
                $inferredFacts = $this->inferFactsFromEntity($entity, $rules, $relationTypes, $confidenceThreshold, 1];
                if (!empty($inferredFacts]) {
                    $result['inferred_facts'] = array_merge($result['inferred_facts'],  $inferredFacts];
                }
                
                // ���������������?                if (count($result['inferred_facts']] >= $this->config['max_results']) {
                    break;
                }
            }
        }
        
        // ��¼Ӧ�õĹ���
        $appliedRules = [];
        foreach ($result['inferred_facts'] as $fact) {
            if (isset($fact['inference_path']['rule']] && !in_[$fact['inference_path']['rule'],  $rules]) {
                $appliedRules[] = $fact['inference_path']['rule'];
            }
        }
        
        foreach ($appliedRules as $ruleName) {
            foreach ($this->rules as $rule) {
                if ($rule['name'] === $ruleName) {
                    $result['applied_rules'][] = $rule;
                    break;
                }
            }
        }
        
        return $result;
    }
    
    /**
     * ��ʵ��������ʵ
     * 
     * @param array  ʵ��
     * @param array|null  ���������б�
     * @param array|null  ��ϵ�����б�
     * @param float  ���Ŷ���ֵ
     * @param int  ����������
     * @return array �����õ�����ʵ�б�
     */
    private function inferFactsFromEntity(array $entity, ?array $rules = null, ?array $relationTypes = null, float $confidenceThreshold = 0.5, int $maxDepth = 1): array
    {
        $inferredFacts = [];
        $visited = [];
        $queue = [['entity' => $entity, 'depth' => 0]];
        
        while (!empty($queue] && count($inferredFacts] <$this->config['max_results']) {
            $current = array_shift($queue];
            $currentEntity = $current['entity'];
            $currentDepth = $current['depth'];
            
            if ($currentDepth >= $maxDepth) {
                continue;
            }
            
            // �Ե�ǰʵ��Ӧ�����й���
            foreach ($this->rules as $rule) {
                // ���ָ���˹������ƣ���ֻӦ����Щ����?                if ($rules !== null && !in_[$rule['name'],  $rules]) {
                    continue;
                }
                
                // ���ݹ�������Ӧ�ò�ͬ����������
                switch ($rule['type']) {
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
     * Ӧ����Ϲ���?     * 
     * @param array  ʵ��
     * @param array  ����
     * @param array|null  ��ϵ�����б�
     * @param float  ���Ŷ���ֵ
     * @return array �����õ�����ʵ�б�
     */
    private function applyCompositeRule(array $entity, array $rule, ?array $relationTypes = null, float $confidenceThreshold = 0.5): array
    {
        $inferredFacts = [];
        
        // ��ʽ����: A��B�й�ϵ1��B��C�й�ϵ2 => A��C�й�ϵ3
        if ($rule['pattern'] === 'chain') {
            // ���ָ���˹�ϵ���ͣ���������ϵ�����б��У�������?            if ($relationTypes !== null && !in_[$rule['inferred_relation'],  $relationTypes]) {
                return [];
            }
            
            // ��ȡ��Դʵ���й�ϵ1���м�ʵ��
            $intermediateEntities = $this->graphStore->getRelatedEntities('id'],  [$rule['relation1']]];
            
            foreach ($intermediateEntities as $intermediateEntity) {
                // ��ȡ���м�ʵ���й�ϵ2��Ŀ��ʵ��
                $targetEntities = $this->graphStore->getRelatedEntities('id'],  [$rule['relation2']]];
                
                foreach ($targetEntities as $targetEntity) {
                    // �����Ի�
                    if ($intermediateEntity['id'] === $targetEntity['id']) {
                        continue;
                    }
                    
                    // �����������Ŷ�
                    $confidence = $intermediateEntity['confidence'] * $targetEntity['confidence'] * $rule['confidence_factor'];
                    
                    if ($confidence >= $confidenceThreshold) {
                        // ����������ϵ
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
        // ��ͬĿ�����? A��C�й�ϵ1��B��C�й�ϵ2 => A��B�й�ϵ3
        else if ($rule['pattern'] === 'common_target') {
            // ���ָ���˹�ϵ���ͣ���������ϵ�����б��У�������?            if ($relationTypes !== null && !in_[$rule['inferred_relation'],  $relationTypes]) {
                return [];
            }
            
            // ��ȡ��Դʵ���й�ϵ1�Ĺ�ͬĿ��ʵ��
            $commonTargetEntities = $this->graphStore->getRelatedEntities('id'],  [$rule['relation1']]];
            
            foreach ($commonTargetEntities as $commonTargetEntity) {
                // ��ȡ�빲ͬĿ���й�ϵ2��Ŀ��ʵ��
                $targetEntities = $this->graphStore->getEntitiesWithRelation('relation2'],  ['id']];
                
                foreach ($targetEntities as $targetEntity) {
                    // �����Ի�
                    if ($commonTargetEntity['id'] === $targetEntity['id']) {
                        continue;
                    }
                    
                    // �����������Ŷ�
                    $confidence = $commonTargetEntity['confidence'] * $targetEntity['confidence'] * $rule['confidence_factor'];
                    
                    if ($confidence >= $confidenceThreshold) {
                        // ����������ϵ
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
     * Ӧ�öԳ��Թ���
     * 
     * @param array  ʵ��
     * @param array  ����
     * @param array|null  ��ϵ�����б�
     * @param float  ���Ŷ���ֵ
     * @return array �����õ�����ʵ�б�
     */
    private function applySymmetricRule(array $entity, array $rule, ?array $relationTypes = null, float $confidenceThreshold = 0.5): array
    {
        $inferredFacts = [];
        
        // ���ָ���˹�ϵ���ͣ��ҹ����ϵ�����б��У�������
        if ($relationTypes !== null && !in_[$rule['relation'],  $relationTypes]) {
            return [];
        }
        
        // ��ȡ��ʵ���й�ϵ��Ŀ��ʵ��
        $targetEntities = $this->graphStore->getEntitiesWithRelation('relation'],  ['id']];
        
        foreach ($targetEntities as $targetEntity) {
            // ����Ƿ��Ѵ��ڷ����ϵ
            $relation = $this->graphStore->getRelationBetween($entity['id'],  $targetEntity['id'],  $rule['relation']];
            
            // ��������ڷ����ϵ���򴴽�������ϵ
            if (!$relation) {
                $confidence = $entity['confidence'] * $rule['confidence_factor'];
                
                if ($confidence >= $confidenceThreshold) {
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
     * Ӧ�ô����Թ���
     * 
     * @param array  ʵ��
     * @param array  ����
     * @param array|null  ��ϵ�����б�
     * @param float  ���Ŷ���ֵ
     * @return array �����õ�����ʵ�б�
     */
    private function applyTransitiveRule(array $entity, array $rule, ?array $relationTypes = null, float $confidenceThreshold = 0.5): array
    {
        $inferredFacts = [];
        
        // ���ָ���˹�ϵ���ͣ��ҹ����ϵ�����б��У�������
        if ($relationTypes !== null && !in_[$rule['relation'],  $relationTypes]) {
            return [];
        }
        
        // ��ȡ��ʵ����ָ����ϵ��Ŀ��ʵ��
        $targetEntities = $this->graphStore->getRelatedEntities('id'],  [$rule['relation']]];
        
        foreach ($targetEntities as $targetEntity) {
            // ��ȡ���м�ʵ������ͬ��ϵ��Ŀ��ʵ��
            $intermediateEntities = $this->graphStore->getRelatedEntities('id'],  [$rule['relation']]];
            
            foreach ($intermediateEntities as $intermediateEntity) {
                // �����Ի���ֱ�ӹ�ϵ
                if ($intermediateEntity['id'] === $entity['id'] || $intermediateEntity['id'] === $targetEntity['id']) {
                    continue;
                }
                
                // ����Ƿ��Ѵ���ֱ�ӹ��?                $relation = $this->graphStore->getRelationBetween($intermediateEntity['id'],  $targetEntity['id'],  $rule['relation']];
                
                // ���������ֱ�ӹ�ϵ���򴴽��������?                if (!$relation) {
                    $confidence = $intermediateEntity['confidence'] * $targetEntity['confidence'] * $rule['confidence_factor'];
                    
                    if ($confidence >= $confidenceThreshold) {
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
     * Ӧ�ò�ι�ϵ����?     * 
     * @param array  ʵ��
     * @param array  ����
     * @param float  ���Ŷ���ֵ
     * @return array �����õ�����ʵ�б�
     */
    private function applyHierarchicalRule(array $entity, array $rule, float $confidenceThreshold = 0.5): array
    {
        $inferredFacts = [];
        
        // ���ʵ�������Ƿ���Ϲ���Ҫ��
        if ($entity['source_type'] !== null && $entity['type'] !== $entity['source_type']) {
            return [];
        }
        
        // ��ȡ��ʵ����ָ����ϵ��Ŀ��ʵ��
        $targetEntities = $this->graphStore->getRelatedEntities('id'],  [$rule['relation']]];
        
        foreach ($targetEntities as $targetEntity) {
            // ���Ŀ��ʵ�������Ƿ���Ϲ���Ҫ��
            if ($targetEntity['target_type'] !== null && $targetEntity['type'] !== $targetEntity['target_type']) {
                continue;
            }
            
            // �����������Ŷ�
            $confidence = $entity['confidence'] * $rule['confidence_factor'];
            
            if ($confidence >= $confidenceThreshold) {
                // ����������ʵ��ʵ��̳�Ŀ��ʵ�������
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
     * Ӧ�����ϵ����?     * 
     * @param array  ʵ��
     * @param array  ����
     * @param array|null  ��ϵ�����б�
     * @param float  ���Ŷ���ֵ
     * @return array �����õ�����ʵ�б�
     */
    private function applyInverseRule(array $entity, array $rule, ?array $relationTypes = null, float $confidenceThreshold = 0.5): array
    {
        $inferredFacts = [];
        
        // ���ָ���˹�ϵ���ͣ���������ϵ�����б��У�������?        if ($relationTypes !== null && !in_[$rule['relation2'],  $relationTypes]) {
            return [];
        }
        
        // ��ȡ��ʵ���й�ϵ1��Ŀ��ʵ��
        $entities = $this->graphStore->getEntitiesWithRelation('relation1'],  ['id']];
        
        foreach ($entities as $entity) {
            // ����Ƿ��Ѵ������ϵ
            $relation = $this->graphStore->getRelationBetween($entity['id'],  $entity['id'],  $rule['relation2']];
            
            // ������������ϵ���򴴽�������ϵ
            if (!$relation) {
                $confidence = $entity['confidence'] * $rule['confidence_factor'];
                
                if ($confidence >= $confidenceThreshold) {
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


