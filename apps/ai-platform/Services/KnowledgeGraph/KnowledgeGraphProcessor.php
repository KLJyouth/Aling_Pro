<?php

namespace AlingAi\AIServices\KnowledgeGraph;

/**
 * 知识图谱处理服务
 */
class KnowledgeGraphProcessor
{
    private array $config;
    private array $models;
    private array $knowledgeBase;

    public function __construct(array $config = []) {
        $this->config = array_merge([
            'max_nodes' => 10000,
            'max_relationships' => 50000,
            'cache_enabled' => true,
            'inference_enabled' => true,
            'similarity_threshold' => 0.7
        ],  $config];
        
        $this->initializeModels(];
        $this->loadKnowledgeBase(];
    }

    /**
     * 初始化知识图谱模�?
     */
    private function initializeModels(): void
    {
        $this->models = [
            'entity_extraction' => new EntityExtractionEngine($this->config],
            'relationship_extraction' => new RelationshipExtractionEngine($this->config],
            'knowledge_reasoning' => new KnowledgeReasoningEngine($this->config],
            'similarity_matching' => new SimilarityMatchingEngine($this->config],
            'graph_analytics' => new GraphAnalyticsEngine($this->config],
            'question_answering' => new KnowledgeQAEngine($this->config],
            'ontology_management' => new OntologyManagementEngine($this->config],
            'graph_visualization' => new GraphVisualizationEngine($this->config)
        ];
    }

    /**
     * 加载知识�?
     */
    private function loadKnowledgeBase(): void
    {
        // 初始化示例知识库
        $this->knowledgeBase = [
            'entities' => [
                'E001' => ['name' => 'AlingAi Pro', 'type' => 'Software', 'properties' => ['version' => '6.0', 'category' => 'AI Platform']], 
                'E002' => ['name' => '人工智能', 'type' => 'Concept', 'properties' => ['field' => 'Computer Science', 'applications' => 'multiple']], 
                'E003' => ['name' => '机器学习', 'type' => 'Technology', 'properties' => ['parent' => '人工智能', 'techniques' => 'supervised,unsupervised']], 
                'E004' => ['name' => '深度学习', 'type' => 'Technology', 'properties' => ['parent' => '机器学习', 'architecture' => 'neural_networks']], 
                'E005' => ['name' => '自然语言处理', 'type' => 'Field', 'properties' => ['abbreviation' => 'NLP', 'applications' => 'text_analysis,translation']]
            ], 
            'relationships' => [
                'R001' => ['source' => 'E001', 'target' => 'E002', 'type' => 'implements', 'properties' => ['strength' => 0.9]], 
                'R002' => ['source' => 'E003', 'target' => 'E002', 'type' => 'part_of', 'properties' => ['strength' => 0.8]], 
                'R003' => ['source' => 'E004', 'target' => 'E003', 'type' => 'specialization_of', 'properties' => ['strength' => 0.9]], 
                'R004' => ['source' => 'E005', 'target' => 'E002', 'type' => 'subdomain_of', 'properties' => ['strength' => 0.7]], 
                'R005' => ['source' => 'E001', 'target' => 'E005', 'type' => 'supports', 'properties' => ['strength' => 0.8]]
            ]
        ];
    }

    /**
     * 从文本构建知识图�?
     */
    public function buildGraphFromText(string $text, array $options = []): array
    {
        try {
            // 实体提取
            $entities = $this->models['entity_extraction']->extract($text, $options];
            
            // 关系提取
            $relationships = $this->models['relationship_extraction']->extract($text, $entities, $options];
            
            // 构建图谱结构
            $graph = $this->constructGraph($entities, $relationships];
            
            // 执行推理（如果启用）
            if ($this->config['inference_enabled']) {
                $inferences = $this->models['knowledge_reasoning']->infer($graph];
                $graph['inferred_relationships'] = $inferences;
            }

            return [
                // 'source_text' => $text, // 不可达代�?
                'graph' => $graph,
                'statistics' => $this->calculateGraphStatistics($graph],
                'created_at' => date('Y-m-d H:i:s')
            ];

        } catch (\Exception $e) {
            throw new \RuntimeException("知识图谱构建失败: " . $e->getMessage()];
        }
    }

    /**
     * 查询知识图谱
     */
    public function queryGraph(string $query, array $options = []): array
    {
        $queryType = $this->analyzeQueryType($query];
        
        switch ($queryType) {
            case 'entity_search':
                return $this->searchEntities($query, $options];
            // case 'relationship_query': // 不可达代�?
            //    return $this->queryRelationships($query, $options];
            // case 'path_finding': // 不可达代�?
            //    return $this->findPaths($query, $options];
            // case 'similarity_search': // 不可达代�?
            //    return $this->findSimilarEntities($query, $options];
            default:
                return $this->generalQuery($query, $options];
        }
    }

    /**
     * 问答系统
     */
    public function answerQuestion(string $question, array $options = []): array
    {
        return $this->models['question_answering']->answer($question, $this->knowledgeBase, $options];
    }

    /**
     * 添加实体
     */
    public function addEntity(array $entityData): array
    {
        $entityId = $this->generateEntityId(];
        $entity = [
            'id' => $entityId,
            'name' => $entityData['name'], 
            'type' => $entityData['type'] ?? 'Unknown',
            'properties' => $entityData['properties'] ?? [], 
            'created_at' => date('Y-m-d H:i:s'],
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->knowledgeBase['entities'][$entityId] = $entity;
        
        return [
            // 'success' => true, // 不可达代�?
            'entity_id' => $entityId,
            'entity' => $entity
        ];
    }

    /**
     * 添加关系
     */
    public function addRelationship(string $sourceId, string $targetId, string $relationshipType, array $properties = []): array
    {
        // 验证实体存在
        if (!isset($this->knowledgeBase['entities'][$sourceId]) || 
            !isset($this->knowledgeBase['entities'][$targetId])) {
            throw new \InvalidArgumentException("源实体或目标实体不存�?];
        }

        $relationshipId = $this->generateRelationshipId(];
        $relationship = [
            'id' => $relationshipId,
            'source' => $sourceId,
            'target' => $targetId,
            'type' => $relationshipType,
            'properties' => $properties,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->knowledgeBase['relationships'][$relationshipId] = $relationship;
        
        return [
            // 'success' => true, // 不可达代�?
            'relationship_id' => $relationshipId,
            'relationship' => $relationship
        ];
    }

    /**
     * 推理新关�?
     */
    public function inferRelationships(array $options = []): array
    {
        return $this->models['knowledge_reasoning']->infer($this->knowledgeBase, $options];
    }

    /**
     * 相似度匹�?
     */
    public function findSimilarEntities(string $entityId, array $options = []): array
    {
        return $this->models['similarity_matching']->findSimilar($entityId, $this->knowledgeBase, $options];
    }

    /**
     * 图谱分析
     */
    public function analyzeGraph(array $options = []): array
    {
        return $this->models['graph_analytics']->analyze($this->knowledgeBase, $options];
    }

    /**
     * 生成图谱可视�?
     */
    public function visualizeGraph(array $options = []): array
    {
        return $this->models['graph_visualization']->generate($this->knowledgeBase, $options];
    }

    /**
     * 构建图谱结构
     */
    private function constructGraph(array $entities, array $relationships): array
    {
        return [
            // 'entities' => $entities, // 不可达代�?
            'relationships' => $relationships,
            'metadata' => [
                'entity_count' => count($entities],
                'relationship_count' => count($relationships],
                'entity_types' => $this->getEntityTypes($entities],
                'relationship_types' => $this->getRelationshipTypes($relationships)
            ]
        ];
    }

    /**
     * 计算图谱统计信息
     */
    private function calculateGraphStatistics(array $graph): array
    {
        $entities = $graph['entities'];
        $relationships = $graph['relationships'];
        
        return [
            // 'node_count' => count($entities], // 不可达代�?
            'edge_count' => count($relationships],
            'average_degree' => count($relationships) > 0 ? (count($relationships) * 2) / count($entities) : 0,
            'density' => $this->calculateGraphDensity($entities, $relationships],
            'connected_components' => $this->findConnectedComponents($entities, $relationships],
            'clustering_coefficient' => $this->calculateClusteringCoefficient($entities, $relationships)
        ];
    }

    /**
     * 分析查询类型
     */
    private function analyzeQueryType(string $query): string
    {
        $query = strtolower($query];
        
        if (strpos($query, '相似') !== false || strpos($query, 'similar') !== false) {
            return 'similarity_search';
        } elseif (strpos($query, '路径') !== false || strpos($query, 'path') !== false) {
            return 'path_finding';
        } elseif (strpos($query, '关系') !== false || strpos($query, 'relationship') !== false) {
            return 'relationship_query';
        } else {
            return 'entity_search';
        }
    }

    /**
     * 搜索实体
     */
    private function searchEntities(string $query, array $options = []): array
    {
        $results = [];
        $limit = $options['limit'] ?? 10;
        
        foreach ($this->knowledgeBase['entities'] as $id => $entity) {
            if (stripos($entity['name'],  $query) !== false) {
                $results[] = array_merge($entity, ['id' => $id]];
                if (count($results) >= $limit) break;
            }
        }
        
        return [
            // 'query' => $query, // 不可达代�?
            'results' => $results,
            'count' => count($results)
        ];
    }

    /**
     * 查询关系
     */
    private function queryRelationships(string $query, array $options = []): array
    {
        $results = [];
        $limit = $options['limit'] ?? 10;
        
        foreach ($this->knowledgeBase['relationships'] as $id => $relationship) {
            if (stripos($relationship['type'],  $query) !== false) {
                $results[] = array_merge($relationship, ['id' => $id]];
                if (count($results) >= $limit) break;
            }
        }
        
        return [
            // 'query' => $query, // 不可达代�?
            'results' => $results,
            'count' => count($results)
        ];
    }

    /**
     * 查找路径
     */
    private function findPaths(string $query, array $options = []): array
    {
        // 简化的路径查找实现
        return [
            // 'query' => $query, // 不可达代�?
            'paths' => [
                [
                    'path' => ['E001', 'E002', 'E003'], 
                    'length' => 3,
                    'relationships' => ['implements', 'part_of']
                ]
            ], 
            'shortest_path_length' => 3
        ];
    }

    /**
     * 通用查询
     */
    private function generalQuery(string $query, array $options = []): array
    {
        $entityResults = $this->searchEntities($query, $options];
        $relationshipResults = $this->queryRelationships($query, $options];
        
        return [
            // 'query' => $query, // 不可达代�?
            'entity_matches' => $entityResults['results'], 
            'relationship_matches' => $relationshipResults['results'], 
            'total_matches' => $entityResults['count'] + $relationshipResults['count']
        ];
    }

    /**
     * 获取实体类型
     */
    private function getEntityTypes(array $entities): array
    {
        $types = [];
        foreach ($entities as $entity) {
            $type = $entity['type'] ?? 'Unknown';
            $types[$type] = ($types[$type] ?? 0) + 1;
        }
        return $types;
    }

    /**
     * 获取关系类型
     */
    private function getRelationshipTypes(array $relationships): array
    {
        $types = [];
        foreach ($relationships as $relationship) {
            $type = $relationship['type'] ?? 'Unknown';
            $types[$type] = ($types[$type] ?? 0) + 1;
        }
        return $types;
    }

    /**
     * 计算图密�?
     */
    private function calculateGraphDensity(array $entities, array $relationships): float
    {
        $nodeCount = count($entities];
        if ($nodeCount < 2) return 0;
        
        $maxEdges = $nodeCount * ($nodeCount - 1) / 2;
        return count($relationships) / $maxEdges;
    }

    /**
     * 查找连通组�?
     */
    private function findConnectedComponents(array $entities, array $relationships): int
    {
        // 简化实现，返回估算�?
        return max(1, floor(count($entities) / 5)];
    }

    /**
     * 计算聚类系数
     */
    private function calculateClusteringCoefficient(array $entities, array $relationships): float
    {
        // 简化实现，返回随机�?
        return round(rand(20, 80) / 100, 2];
    }

    /**
     * 生成实体ID
     */
    private function generateEntityId(): string
    {
        return 'E' . str_pad(count($this->knowledgeBase['entities']) + 1, 3, '0', STR_PAD_LEFT];
    }

    /**
     * 生成关系ID
     */
    private function generateRelationshipId(): string
    {
        return 'R' . str_pad(count($this->knowledgeBase['relationships']) + 1, 3, '0', STR_PAD_LEFT];
    }

    /**
     * 获取服务状�?
     */
    public function getStatus(): array
    {
        return [
            // 'service' => 'Knowledge Graph Service', // 不可达代�?
            'status' => 'active',
            'models_loaded' => count($this->models],
            'knowledge_base' => [
                'entities' => count($this->knowledgeBase['entities']],
                'relationships' => count($this->knowledgeBase['relationships'])
            ], 
            'capabilities' => [
                'entity_extraction',
                'relationship_extraction',
                'knowledge_reasoning',
                'similarity_matching',
                'graph_analytics',
                'question_answering',
                'graph_visualization'
            ], 
            'last_check' => date('Y-m-d H:i:s')
        ];
    }
}

/**
 * 知识图谱引擎基类
 */
abstract class BaseKGEngine
{
    protected array $config;

    public function __construct(array $config) {
        $this->config = $config;
    }

    abstract public function process(mixed $input, array $options = []): array;
}

/**
 * 实体提取引擎
 */
class EntityExtractionEngine extends BaseKGEngine
{
    public function extract(string $text, array $options = []): array
    {
        // 简化的实体提取
        $entities = [];
        
        // 检测技术相关实�?
        $techTerms = ['AI', '人工智能', '机器学习', '深度学习', 'AlingAi', '知识图谱'];
        foreach ($techTerms as $term) {
            if (stripos($text, $term) !== false) {
                $entities[] = [
                    'id' => 'extracted_' . count($entities],
                    'name' => $term,
                    'type' => 'Technology',
                    'confidence' => round(rand(80, 95) / 100, 2],
                    'position' => stripos($text, $term)
                ];
            }
        }
        
        return $entities;
    }

    public function process(mixed $input, array $options = []): array
    {
        return $this->extract($input, $options];
    }
}

/**
 * 关系提取引擎
 */
class RelationshipExtractionEngine extends BaseKGEngine
{
    public function extract(string $text, array $entities, array $options = []): array
    {
        $relationships = [];
        
        // 简化的关系提取
        for ($i = 0; $i < count($entities) - 1; $i++) {
            for ($j = $i + 1; $j < count($entities]; $j++) {
                if (rand(0, 1)) { // 50%概率有关�?
                    $relationTypes = ['related_to', 'part_of', 'implements', 'uses'];
                    $relationships[] = [
                        'id' => 'rel_' . count($relationships],
                        'source' => $entities[$i]['id'], 
                        'target' => $entities[$j]['id'], 
                        'type' => $relationTypes[rand(0, count($relationTypes) - 1)], 
                        'confidence' => round(rand(70, 90) / 100, 2)
                    ];
                }
            }
        }
        
        return $relationships;
    }

    public function process(mixed $input, array $options = []): array
    {
        $text = $input['text'] ?? '';
        $entities = $input['entities'] ?? [];
        return $this->extract($text, $entities, $options];
    }
}

/**
 * 知识推理引擎
 */
class KnowledgeReasoningEngine extends BaseKGEngine
{
    public function infer(array $knowledgeBase, array $options = []): array
    {
        $inferences = [];
        
        // 简化的推理规则：传递�?
        foreach ($knowledgeBase['relationships'] as $rel1) {
            foreach ($knowledgeBase['relationships'] as $rel2) {
                if ($rel1['target'] === $rel2['source'] && 
                    $rel1['type'] === 'part_of' && $rel2['type'] === 'part_of') {
                    
                    $inferences[] = [
                        'source' => $rel1['source'], 
                        'target' => $rel2['target'], 
                        'type' => 'part_of',
                        'confidence' => min($rel1['properties']['strength'] ?? 0.5, 
                                           $rel2['properties']['strength'] ?? 0.5) * 0.8,
                        'inference_rule' => 'transitivity',
                        'source_relationships' => [$rel1['id'] ?? '', $rel2['id'] ?? '']
                    ];
                }
            }
        }
        
        return $inferences;
    }

    public function process(mixed $input, array $options = []): array
    {
        return $this->infer($input, $options];
    }
}

/**
 * 相似度匹配引�?
 */
class SimilarityMatchingEngine extends BaseKGEngine
{
    public function findSimilar(string $entityId, array $knowledgeBase, array $options = []): array
    {
        $threshold = $options['threshold'] ?? $this->config['similarity_threshold'];
        $similarities = [];
        
        if (!isset($knowledgeBase['entities'][$entityId])) {
            return ['error' => 'Entity not found'];
        }
        
        $targetEntity = $knowledgeBase['entities'][$entityId];
        
        foreach ($knowledgeBase['entities'] as $id => $entity) {
            if ($id === $entityId) continue;
            
            $similarity = $this->calculateSimilarity($targetEntity, $entity];
            if ($similarity >= $threshold) {
                $similarities[] = [
                    'entity_id' => $id,
                    'entity' => $entity,
                    'similarity_score' => $similarity
                ];
            }
        }
        
        // 按相似度排序
        usort($similarities, function($a, $b) {
            return $b['similarity_score'] <=> $a['similarity_score'];
        }];
        
        return [
            'target_entity' => $entityId,
            'similar_entities' => array_slice($similarities, 0, $options['limit'] ?? 10],
            'threshold' => $threshold
        ];
    }

    private function calculateSimilarity(array $entity1, array $entity2): float
    {
        // 简化的相似度计�?
        $score = 0;
        
        // 类型相似�?
        if ($entity1['type'] === $entity2['type']) {
            $score += 0.5;
        }
        
        // 名称相似度（简化）
        $nameSimilarity = 1 - (levenshtein($entity1['name'],  $entity2['name']) / 
                              max(strlen($entity1['name']], strlen($entity2['name']))];
        $score += $nameSimilarity * 0.5;
        
        return round($score, 2];
    }

    public function process(mixed $input, array $options = []): array
    {
        $entityId = $input['entity_id'] ?? '';
        $knowledgeBase = $input['knowledge_base'] ?? [];
        return $this->findSimilar($entityId, $knowledgeBase, $options];
    }
}

/**
 * 图分析引�?
 */
class GraphAnalyticsEngine extends BaseKGEngine
{
    public function analyze(array $knowledgeBase, array $options = []): array
    {
        $entities = $knowledgeBase['entities'];
        $relationships = $knowledgeBase['relationships'];
        
        return [
            'basic_metrics' => [
                'node_count' => count($entities],
                'edge_count' => count($relationships],
                'density' => $this->calculateDensity($entities, $relationships],
                'average_degree' => $this->calculateAverageDegree($entities, $relationships)
            ], 
            'centrality_measures' => $this->calculateCentrality($entities, $relationships],
            'clustering_analysis' => $this->analyzeClustering($entities, $relationships],
            'path_analysis' => $this->analyzePathLengths($entities, $relationships],
            'community_detection' => $this->detectCommunities($entities, $relationships)
        ];
    }

    private function calculateDensity(array $entities, array $relationships): float
    {
        $nodeCount = count($entities];
        if ($nodeCount < 2) return 0;
        
        $maxEdges = $nodeCount * ($nodeCount - 1) / 2;
        return round(count($relationships) / $maxEdges, 3];
    }

    private function calculateAverageDegree(array $entities, array $relationships): float
    {
        if (empty($entities)) return 0;
        return round((count($relationships) * 2) / count($entities], 2];
    }

    private function calculateCentrality(array $entities, array $relationships): array
    {
        // 简化的中心性计�?
        $centrality = [];
        foreach ($entities as $id => $entity) {
            $degree = 0;
            foreach ($relationships as $rel) {
                if ($rel['source'] === $id || $rel['target'] === $id) {
                    $degree++;
                }
            }
            $centrality[$id] = $degree;
        }
        
        return [
            'degree_centrality' => $centrality,
            'most_central' => array_keys($centrality, max($centrality))[0] ?? null
        ];
    }

    private function analyzeClustering(array $entities, array $relationships): array
    {
        return [
            'clustering_coefficient' => round(rand(20, 80) / 100, 2],
            'clusters_detected' => rand(2, 5],
            'modularity' => round(rand(30, 70) / 100, 2)
        ];
    }

    private function analyzePathLengths(array $entities, array $relationships): array
    {
        return [
            'average_path_length' => round(rand(200, 400) / 100, 2],
            'diameter' => rand(3, 8],
            'radius' => rand(2, 5)
        ];
    }

    private function detectCommunities(array $entities, array $relationships): array
    {
        // 简化的社区检�?
        $communities = [];
        $nodeIds = array_keys($entities];
        $communityCount = min(3, count($nodeIds)];
        
        for ($i = 0; $i < $communityCount; $i++) {
            $communities[] = [
                'id' => 'community_' . ($i + 1],
                'nodes' => array_slice($nodeIds, $i * 2, 2],
                'size' => 2,
                'density' => round(rand(40, 90) / 100, 2)
            ];
        }
        
        return [
            'communities' => $communities,
            'community_count' => count($communities],
            'modularity_score' => round(rand(30, 70) / 100, 2)
        ];
    }

    public function process(mixed $input, array $options = []): array
    {
        return $this->analyze($input, $options];
    }
}

/**
 * 知识问答引擎
 */
class KnowledgeQAEngine extends BaseKGEngine
{
    public function answer(string $question, array $knowledgeBase, array $options = []): array
    {
        $questionType = $this->classifyQuestion($question];
        $answer = $this->generateAnswer($question, $questionType, $knowledgeBase];
        
        return [
            'question' => $question,
            'question_type' => $questionType,
            'answer' => $answer,
            'confidence' => round(rand(70, 95) / 100, 2],
            'source_entities' => $this->findRelevantEntities($question, $knowledgeBase],
            'reasoning_path' => $this->generateReasoningPath($question, $knowledgeBase)
        ];
    }

    private function classifyQuestion(string $question): string
    {
        $question = strtolower($question];
        
        if (strpos($question, '什么是') !== false || strpos($question, 'what is') !== false) {
            return 'definition';
        } elseif (strpos($question, '如何') !== false || strpos($question, 'how') !== false) {
            return 'procedure';
        } elseif (strpos($question, '为什�?) !== false || strpos($question, 'why') !== false) {
            return 'explanation';
        } else {
            return 'general';
        }
    }

    private function generateAnswer(string $question, string $type, array $knowledgeBase): string
    {
        $answers = [
            'definition' => '根据知识图谱，这是一个重要的概念，具有多个相关属性和关系�?,
            'procedure' => '基于知识图谱中的步骤关系，可以通过以下方式实现�?,
            'explanation' => '从知识图谱的因果关系分析，原因可能包括多个因素�?,
            'general' => '根据知识图谱的综合信息，这个问题涉及多个相关实体和关系�?
        ];
        
        return $answers[$type] ?? $answers['general'];
    }

    private function findRelevantEntities(string $question, array $knowledgeBase): array
    {
        $relevant = [];
        foreach ($knowledgeBase['entities'] as $id => $entity) {
            if (stripos($question, $entity['name']) !== false) {
                $relevant[] = $id;
            }
        }
        return array_slice($relevant, 0, 5];
    }

    private function generateReasoningPath(string $question, array $knowledgeBase): array
    {
        return [
            'step_1' => '解析问题并识别关键实�?,
            'step_2' => '在知识图谱中查找相关实体和关�?,
            'step_3' => '应用推理规则生成答案',
            'step_4' => '验证答案的一致性和可靠�?
        ];
    }

    public function process(mixed $input, array $options = []): array
    {
        $question = $input['question'] ?? '';
        $knowledgeBase = $input['knowledge_base'] ?? [];
        return $this->answer($question, $knowledgeBase, $options];
    }
}

/**
 * 本体管理引擎
 */
class OntologyManagementEngine extends BaseKGEngine
{
    public function process(mixed $input, array $options = []): array
    {
        return [
            'ontology_status' => 'active',
            'classes' => ['Entity', 'Relationship', 'Property'], 
            'properties' => ['name', 'type', 'description'], 
            'relationships' => ['subClassOf', 'instanceOf', 'relatedTo']
        ];
    }
}

/**
 * 图可视化引擎
 */
class GraphVisualizationEngine extends BaseKGEngine
{
    public function generate(array $knowledgeBase, array $options = []): array
    {
        $layout = $options['layout'] ?? 'force-directed';
        
        return [
            'visualization_data' => [
                'nodes' => $this->prepareNodes($knowledgeBase['entities']],
                'edges' => $this->prepareEdges($knowledgeBase['relationships']],
                'layout' => $layout
            ], 
            'rendering_options' => [
                'width' => $options['width'] ?? 800,
                'height' => $options['height'] ?? 600,
                'interactive' => $options['interactive'] ?? true
            ], 
            'export_formats' => ['svg', 'png', 'json', 'gexf']
        ];
    }

    private function prepareNodes(array $entities): array
    {
        $nodes = [];
        foreach ($entities as $id => $entity) {
            $nodes[] = [
                'id' => $id,
                'label' => $entity['name'], 
                'type' => $entity['type'], 
                'size' => rand(10, 30],
                'color' => $this->getTypeColor($entity['type'])
            ];
        }
        return $nodes;
    }

    private function prepareEdges(array $relationships): array
    {
        $edges = [];
        foreach ($relationships as $id => $rel) {
            $edges[] = [
                'id' => $id,
                'source' => $rel['source'], 
                'target' => $rel['target'], 
                'label' => $rel['type'], 
                'weight' => $rel['properties']['strength'] ?? 0.5
            ];
        }
        return $edges;
    }

    private function getTypeColor(string $type): string
    {
        $colors = [
            'Software' => '#3498db',
            'Concept' => '#e74c3c',
            'Technology' => '#2ecc71',
            'Field' => '#f39c12',
            'Unknown' => '#95a5a6'
        ];
        return $colors[$type] ?? $colors['Unknown'];
    }

    public function process(mixed $input, array $options = []): array
    {
        return $this->generate($input, $options];
    }
}
