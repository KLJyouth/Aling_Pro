<?php
/**
 * 文件名：QueryProcessor.php
 * 功能描述：知识图谱查询处理器 - 处理和优化知识图谱查�?
 * 创建时间�?025-01-XX
 * 最后修改：2025-01-XX
 * 版本�?.0.0
 *
 * @package AlingAi\AI\Engines\KnowledgeGraph
 * @author AlingAi Team
 * @license MIT
 */

declare(strict_types=1];

namespace AlingAi\AI\Engines\KnowledgeGraph;

use Exception;
use InvalidArgumentException;
use AlingAi\Core\Logger\LoggerInterface;
use AlingAi\AI\Engines\NLP\TokenizerInterface;

/**
 * 知识图谱查询处理�?
 *
 * 负责处理、解析和优化针对知识图谱的查�?
 */
class QueryProcessor
{
    /**
     * 配置参数
     */
    private array $config;
    
    /**
     * 日志�?
     */
    private ?LoggerInterface $logger;
    
    /**
     * 分词�?
     */
    private ?TokenizerInterface $tokenizer;
    
    /**
     * 图存储接�?
     */
    private ?GraphStoreInterface $graphStore;
    
    /**
     * 查询缓存
     */
    private array $queryCache = [];
    
    /**
     * 构造函�?
     *
     * @param array $config 配置参数
     * @param LoggerInterface|null $logger 日志�?
     * @param TokenizerInterface|null $tokenizer 分词�?
     * @param GraphStoreInterface|null $graphStore 图存�?
     */
    public function __construct(
        array $config = [], 
        ?LoggerInterface $logger = null,
        ?TokenizerInterface $tokenizer = null,
        ?GraphStoreInterface $graphStore = null
    ) {
        $this->config = array_merge($this->getDefaultConfig(), $config];
        $this->logger = $logger;
        $this->tokenizer = $tokenizer;
        $this->graphStore = $graphStore;
        
        if ($this->logger) {
            $this->logger->info('知识图谱查询处理器初始化成功'];
        }
    }
    
    /**
     * 获取默认配置
     *
     * @return array 默认配置
     */
    private function getDefaultConfig(): array
    {
        return [
            'use_cache' => true,
            'cache_ttl' => 3600,
            'max_results' => 100,
            'timeout' => 30,
            'fuzzy_matching' => true,
            'fuzzy_threshold' => 0.7,
            'query_expansion' => true,
            'query_rewriting' => true,
            'use_synonyms' => true,
            'default_language' => 'zh-CN',
            'supported_languages' => ['zh-CN', 'en-US']
        ];
    }
    
    /**
     * 处理自然语言查询
     *
     * @param string $query 自然语言查询
     * @param array $options 查询选项
     * @return array 查询结果
     */
    public function processNaturalLanguageQuery(string $query, array $options = []): array
    {
        // 合并选项
        $options = array_merge([
            'language' => $this->config['default_language'], 
            'max_results' => $this->config['max_results'], 
            'include_metadata' => true,
            'include_confidence' => true,
            'min_confidence' => 0.5
        ],  $options];
        
        // 检查缓�?
        $cacheKey = md5($query . json_encode($options)];
        if ($this->config['use_cache'] && isset($this->queryCache[$cacheKey])) {
            return $this->queryCache[$cacheKey];
        }
        
        try {
            // 查询预处�?
            $processedQuery = $this->preprocessQuery($query, $options['language']];
            
            // 查询理解和意图识�?
            $queryIntent = $this->analyzeQueryIntent($processedQuery];
            
            // 转换为结构化查询
            $structuredQuery = $this->convertToStructuredQuery($processedQuery, $queryIntent];
            
            // 执行查询
            $results = $this->executeQuery($structuredQuery, $options];
            
            // 后处理结�?
            $finalResults = $this->postprocessResults($results, $query, $options];
            
            // 缓存结果
            if ($this->config['use_cache']) {
                $this->queryCache[$cacheKey] = $finalResults;
            }
            
            return $finalResults;
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->error('查询处理失败', [
                    'query' => $query,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]];
            }
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'query' => $query,
                'results' => []
            ];
        }
    }
    
    /**
     * 执行结构化查�?
     *
     * @param array $structuredQuery 结构化查�?
     * @param array $options 查询选项
     * @return array 查询结果
     */
    public function executeStructuredQuery(array $structuredQuery, array $options = []): array
    {
        // 检查图存储是否可用
        if (!$this->graphStore) {
            throw new Exception('图存储未初始化，无法执行查询'];
        }
        
        // 合并选项
        $options = array_merge([
            'max_results' => $this->config['max_results'], 
            'timeout' => $this->config['timeout'], 
            'include_metadata' => true
        ],  $options];
        
        // 检查缓�?
        $cacheKey = md5(json_encode($structuredQuery) . json_encode($options)];
        if ($this->config['use_cache'] && isset($this->queryCache[$cacheKey])) {
            return $this->queryCache[$cacheKey];
        }
        
        // 查询优化
        $optimizedQuery = $this->optimizeQuery($structuredQuery];
        
        // 执行查询
        $results = $this->executeQuery($optimizedQuery, $options];
        
        // 缓存结果
        if ($this->config['use_cache']) {
            $this->queryCache[$cacheKey] = $results;
        }
        
        return $results;
    }
    
    /**
     * 查询预处�?
     *
     * @param string $query 原始查询
     * @param string $language 语言
     * @return array 预处理后的查询信�?
     */
    private function preprocessQuery(string $query, string $language): array
    {
        // 规范化查�?
        $query = $this->normalizeQuery($query];
        
        // 分词
        $tokens = $this->tokenizeQuery($query, $language];
        
        // 移除停用�?
        $filteredTokens = $this->removeStopwords($tokens, $language];
        
        // 查询扩展
        if ($this->config['query_expansion']) {
            $expandedTokens = $this->expandQuery($filteredTokens, $language];
        } else {
            $expandedTokens = $filteredTokens;
        }
        
        return [
            'original_query' => $query,
            'tokens' => $tokens,
            'filtered_tokens' => $filteredTokens,
            'expanded_tokens' => $expandedTokens,
            'language' => $language
        ];
    }
    
    /**
     * 规范化查�?
     *
     * @param string $query 原始查询
     * @return string 规范化后的查�?
     */
    private function normalizeQuery(string $query): string
    {
        // 去除多余空格
        $query = preg_replace('/\s+/', ' ', trim($query)];
        
        // 转换为小写（对于英文�?
        $query = mb_strtolower($query];
        
        return $query;
    }
    
    /**
     * 分词
     *
     * @param string $query 查询
     * @param string $language 语言
     * @return array 分词结果
     */
    private function tokenizeQuery(string $query, string $language): array
    {
        if ($this->tokenizer) {
            $tokens = $this->tokenizer->tokenize($query];
            return array_column($tokens, 'text'];
        }
        
        // 简单分�?
        if ($language === 'zh-CN') {
            // 中文按字符分�?
            return preg_split('//u', $query, -1, PREG_SPLIT_NO_EMPTY];
        } else {
            // 英文按空格分�?
            return explode(' ', $query];
        }
    }
    
    /**
     * 移除停用�?
     *
     * @param array $tokens 词元数组
     * @param string $language 语言
     * @return array 过滤后的词元数组
     */
    private function removeStopwords(array $tokens, string $language): array
    {
        // 如果有分词器，使用分词器的停用词功能
        if ($this->tokenizer) {
            $stopwords = $this->tokenizer->getStopwords($language];
            return array_filter($tokens, function($token) use ($stopwords) {
                return !in_[mb_strtolower($token], $stopwords];
            }];
        }
        
        // 简单的停用词列�?
        $stopwords = [];
        if ($language === 'zh-CN') {
            $stopwords = ['�?, '�?, '�?, '�?, '�?, '�?, '�?, '�?, '�?, '�?, '�?, '�?, '�?, '�?];
        } elseif ($language === 'en-US') {
            $stopwords = ['the', 'a', 'an', 'and', 'or', 'but', 'if', 'then', 'else', 'when', 'at', 'from', 'by', 'on', 'for', 'to', 'in', 'of'];
        }
        
        return array_filter($tokens, function($token) use ($stopwords) {
            return !in_[mb_strtolower($token], $stopwords];
        }];
    }
    
    /**
     * 查询扩展
     *
     * @param array $tokens 词元数组
     * @param string $language 语言
     * @return array 扩展后的词元数组
     */
    private function expandQuery(array $tokens, string $language): array
    {
        if (!$this->config['query_expansion']) {
            return $tokens;
        }
        
        $expandedTokens = $tokens;
        
        // 添加同义�?
        if ($this->config['use_synonyms']) {
            foreach ($tokens as $token) {
                $synonyms = $this->getSynonyms($token, $language];
                $expandedTokens = array_merge($expandedTokens, $synonyms];
            }
        }
        
        // 去重
        return array_unique($expandedTokens];
    }
    
    /**
     * 获取同义�?
     *
     * @param string $word 单词
     * @param string $language 语言
     * @return array 同义词数�?
     */
    private function getSynonyms(string $word, string $language): array
    {
        // 简单的同义词表
        $synonyms = [];
        
        // 实际应用中可能需要更复杂的同义词库或外部API
        $commonSynonyms = [
            'zh-CN' => [
                '人工智能' => ['AI', '智能系统', '机器智能'], 
                '计算�? => ['电脑', '计算设备'], 
                '数据' => ['信息', '资料'], 
                '网络' => ['互联�?, '网际网络'], 
                '软件' => ['应用程序', '程序'], 
                '硬件' => ['设备', '装置']
            ], 
            'en-US' => [
                'artificial intelligence' => ['ai', 'machine intelligence'], 
                'computer' => ['pc', 'computing device'], 
                'data' => ['information', 'records'], 
                'network' => ['internet', 'web'], 
                'software' => ['application', 'program'], 
                'hardware' => ['device', 'equipment']
            ]
        ];
        
        if (isset($commonSynonyms[$language][$word])) {
            $synonyms = $commonSynonyms[$language][$word];
        }
        
        return $synonyms;
    }
    
    /**
     * 分析查询意图
     *
     * @param array $processedQuery 预处理后的查�?
     * @return array 查询意图
     */
    private function analyzeQueryIntent(array $processedQuery): array
    {
        $tokens = $processedQuery['filtered_tokens'];
        $originalQuery = $processedQuery['original_query'];
        
        // 意图类型
        $intentType = 'unknown';
        $confidence = 0.0;
        $entities = [];
        $relations = [];
        $properties = [];
        
        // 简单的意图识别规则
        if (preg_match('/什么是|what is|who is|谁是|定义|define|explain|解释/i', $originalQuery)) {
            $intentType = 'definition';
            $confidence = 0.8;
        } elseif (preg_match('/关系|relation|between|之间|连接|connect/i', $originalQuery)) {
            $intentType = 'relation';
            $confidence = 0.7;
        } elseif (preg_match('/列出|list|show|显示|给我|give me|所有|all/i', $originalQuery)) {
            $intentType = 'listing';
            $confidence = 0.7;
        } elseif (preg_match('/比较|compare|区别|difference|相似|similar/i', $originalQuery)) {
            $intentType = 'comparison';
            $confidence = 0.7;
        } elseif (preg_match('/如何|how to|怎么|方法|method|步骤|steps/i', $originalQuery)) {
            $intentType = 'procedure';
            $confidence = 0.7;
        } else {
            // 默认为实体查�?
            $intentType = 'entity';
            $confidence = 0.5;
        }
        
        // 提取可能的实�?
        if ($this->graphStore) {
            foreach ($tokens as $token) {
                // 检查是否为已知实体
                $entityCheck = $this->graphStore->findEntity($token, ['fuzzy' => $this->config['fuzzy_matching']]];
                if ($entityCheck && !empty($entityCheck['entities'])) {
                    $entities[] = $entityCheck['entities'][0];
                }
                
                // 检查是否为已知关系
                $relationCheck = $this->graphStore->findRelation($token, ['fuzzy' => $this->config['fuzzy_matching']]];
                if ($relationCheck && !empty($relationCheck['relations'])) {
                    $relations[] = $relationCheck['relations'][0];
                }
            }
        }
        
        return [
            'type' => $intentType,
            'confidence' => $confidence,
            'entities' => $entities,
            'relations' => $relations,
            'properties' => $properties
        ];
    }
    
    /**
     * 转换为结构化查询
     *
     * @param array $processedQuery 预处理后的查�?
     * @param array $queryIntent 查询意图
     * @return array 结构化查�?
     */
    private function convertToStructuredQuery(array $processedQuery, array $queryIntent): array
    {
        $structuredQuery = [
            'type' => $queryIntent['type'], 
            'entities' => $queryIntent['entities'], 
            'relations' => $queryIntent['relations'], 
            'properties' => $queryIntent['properties'], 
            'filters' => [], 
            'sort' => [], 
            'limit' => $this->config['max_results'], 
            'offset' => 0,
            'original_query' => $processedQuery['original_query'], 
            'tokens' => $processedQuery['filtered_tokens']
        ];
        
        // 根据不同意图类型构建查询
        switch ($queryIntent['type']) {
            case 'definition':
                // 定义查询：查找实体的定义或描�?
                $structuredQuery['query_template'] = 'MATCH (e:Entity) WHERE e.name = {entity_name} RETURN e';
                $structuredQuery['parameters'] = [
                    'entity_name' => !empty($queryIntent['entities']) ? $queryIntent['entities'][0]['name'] : implode(' ', $processedQuery['filtered_tokens'])
                ];
                break;
                
            case 'relation':
                // 关系查询：查找实体之间的关系
                if (count($queryIntent['entities']) >= 2) {
                    $structuredQuery['query_template'] = 'MATCH (e1:Entity)-[r]-(e2:Entity) WHERE e1.name = {entity1_name} AND e2.name = {entity2_name} RETURN e1, r, e2';
                    $structuredQuery['parameters'] = [
                        'entity1_name' => $queryIntent['entities'][0]['name'], 
                        'entity2_name' => $queryIntent['entities'][1]['name']
                    ];
                } elseif (count($queryIntent['entities']) == 1 && count($queryIntent['relations']) >= 1) {
                    $structuredQuery['query_template'] = 'MATCH (e1:Entity)-[r:{relation_type}]-(e2:Entity) WHERE e1.name = {entity_name} RETURN e1, r, e2';
                    $structuredQuery['parameters'] = [
                        'entity_name' => $queryIntent['entities'][0]['name'], 
                        'relation_type' => $queryIntent['relations'][0]['type']
                    ];
                } else {
                    $structuredQuery['query_template'] = 'MATCH (e1:Entity)-[r]-(e2:Entity) WHERE e1.name CONTAINS {query} OR e2.name CONTAINS {query} RETURN e1, r, e2 LIMIT {limit}';
                    $structuredQuery['parameters'] = [
                        'query' => implode(' ', $processedQuery['filtered_tokens']],
                        'limit' => $this->config['max_results']
                    ];
                }
                break;
                
            case 'listing':
                // 列表查询：列出符合条件的实体
                if (!empty($queryIntent['entities'])) {
                    $structuredQuery['query_template'] = 'MATCH (e:Entity)-[r]-(related) WHERE e.name = {entity_name} RETURN related';
                    $structuredQuery['parameters'] = [
                        'entity_name' => $queryIntent['entities'][0]['name']
                    ];
                } else {
                    $structuredQuery['query_template'] = 'MATCH (e:Entity) WHERE e.name CONTAINS {query} RETURN e LIMIT {limit}';
                    $structuredQuery['parameters'] = [
                        'query' => implode(' ', $processedQuery['filtered_tokens']],
                        'limit' => $this->config['max_results']
                    ];
                }
                break;
                
            case 'comparison':
                // 比较查询：比较两个实�?
                if (count($queryIntent['entities']) >= 2) {
                    $structuredQuery['query_template'] = 'MATCH (e1:Entity], (e2:Entity) WHERE e1.name = {entity1_name} AND e2.name = {entity2_name} RETURN e1, e2';
                    $structuredQuery['parameters'] = [
                        'entity1_name' => $queryIntent['entities'][0]['name'], 
                        'entity2_name' => $queryIntent['entities'][1]['name']
                    ];
                } else {
                    $structuredQuery['query_template'] = 'MATCH (e:Entity) WHERE e.name CONTAINS {query} RETURN e LIMIT {limit}';
                    $structuredQuery['parameters'] = [
                        'query' => implode(' ', $processedQuery['filtered_tokens']],
                        'limit' => $this->config['max_results']
                    ];
                }
                break;
                
            case 'procedure':
                // 步骤查询：查找操作步�?
                if (!empty($queryIntent['entities'])) {
                    $structuredQuery['query_template'] = 'MATCH (e:Entity)-[r:HAS_STEP]->(step) WHERE e.name = {entity_name} RETURN e, r, step ORDER BY step.order';
                    $structuredQuery['parameters'] = [
                        'entity_name' => $queryIntent['entities'][0]['name']
                    ];
                } else {
                    $structuredQuery['query_template'] = 'MATCH (e:Entity)-[r:HAS_STEP]->(step) WHERE e.name CONTAINS {query} RETURN e, r, step ORDER BY step.order';
                    $structuredQuery['parameters'] = [
                        'query' => implode(' ', $processedQuery['filtered_tokens'])
                    ];
                }
                break;
                
            default:
                // 实体查询：查找匹配的实体
                $structuredQuery['query_template'] = 'MATCH (e:Entity) WHERE e.name CONTAINS {query} RETURN e LIMIT {limit}';
                $structuredQuery['parameters'] = [
                    'query' => implode(' ', $processedQuery['filtered_tokens']],
                    'limit' => $this->config['max_results']
                ];
                break;
        }
        
        return $structuredQuery;
    }
    
    /**
     * 优化查询
     *
     * @param array $query 查询
     * @return array 优化后的查询
     */
    private function optimizeQuery(array $query): array
    {
        // 简单的查询优化
        $optimizedQuery = $query;
        
        // 添加索引提示
        if (isset($query['query_template']) && strpos($query['query_template'],  'MATCH (e:Entity)') !== false) {
            $optimizedQuery['query_template'] = str_replace('MATCH (e:Entity)', 'MATCH (e:Entity) USING INDEX e:Entity(name)', $query['query_template']];
        }
        
        // 限制结果数量
        if (!isset($query['limit']) || $query['limit'] > $this->config['max_results']) {
            $optimizedQuery['limit'] = $this->config['max_results'];
        }
        
        return $optimizedQuery;
    }
    
    /**
     * 执行查询
     *
     * @param array $query 查询
     * @param array $options 选项
     * @return array 查询结果
     */
    private function executeQuery(array $query, array $options): array
    {
        // 检查图存储是否可用
        if (!$this->graphStore) {
            throw new Exception('图存储未初始化，无法执行查询'];
        }
        
        // 执行查询
        $startTime = microtime(true];
        $results = $this->graphStore->query($query['query_template'],  $query['parameters'] ?? []];
        $endTime = microtime(true];
        
        return [
            'success' => true,
            'query' => $query,
            'results' => $results,
            'count' => count($results],
            'execution_time' => round($endTime - $startTime, 4],
            'options' => $options
        ];
    }
    
    /**
     * 后处理结�?
     *
     * @param array $results 查询结果
     * @param string $originalQuery 原始查询
     * @param array $options 选项
     * @return array 处理后的结果
     */
    private function postprocessResults(array $results, string $originalQuery, array $options): array
    {
        // 过滤结果
        $filteredResults = $results;
        
        // 如果需要包含置信度
        if ($options['include_confidence']) {
            $filteredResults = $this->addConfidenceScores($filteredResults, $originalQuery];
        }
        
        // 如果设置了最小置信度
        if (isset($options['min_confidence']) && $options['include_confidence']) {
            $filteredResults['results'] = array_filter($filteredResults['results'],  function($item) use ($options) {
                return $item['confidence'] >= $options['min_confidence'];
            }];
        }
        
        // 如果不需要包含元数据
        if (!$options['include_metadata']) {
            foreach ($filteredResults['results'] as &$item) {
                unset($item['metadata']];
            }
        }
        
        // 限制结果数量
        if (count($filteredResults['results']) > $options['max_results']) {
            $filteredResults['results'] = array_slice($filteredResults['results'],  0, $options['max_results']];
        }
        
        $filteredResults['count'] = count($filteredResults['results']];
        
        return $filteredResults;
    }
    
    /**
     * 添加置信度分�?
     *
     * @param array $results 查询结果
     * @param string $query 原始查询
     * @return array 添加置信度后的结�?
     */
    private function addConfidenceScores(array $results, string $query): array
    {
        foreach ($results['results'] as &$item) {
            // 简单的置信度计�?
            $nameRelevance = 0;
            $descriptionRelevance = 0;
            
            if (isset($item['name'])) {
                $nameRelevance = $this->calculateRelevance($query, $item['name']];
            }
            
            if (isset($item['description'])) {
                $descriptionRelevance = $this->calculateRelevance($query, $item['description']];
            }
            
            // 综合置信�?
            $item['confidence'] = ($nameRelevance * 0.7 + $descriptionRelevance * 0.3];
        }
        
        // 按置信度排序
        usort($results['results'],  function($a, $b) {
            return $b['confidence'] <=> $a['confidence'];
        }];
        
        return $results;
    }
    
    /**
     * 计算相关�?
     *
     * @param string $query 查询
     * @param string $text 文本
     * @return float 相关�?
     */
    private function calculateRelevance(string $query, string $text): float
    {
        // 简单的相关度计�?
        $query = mb_strtolower($query];
        $text = mb_strtolower($text];
        
        // 如果完全匹配
        if ($query === $text) {
            return 1.0;
        }
        
        // 如果包含
        if (mb_strpos($text, $query) !== false) {
            return 0.8;
        }
        
        // 计算词汇重叠�?
        $queryWords = explode(' ', $query];
        $textWords = explode(' ', $text];
        
        $intersection = array_intersect($queryWords, $textWords];
        $union = array_unique(array_merge($queryWords, $textWords)];
        
        if (count($union) === 0) {
            return 0.0;
        }
        
        return count($intersection) / count($union];
    }
    
    /**
     * 设置图存�?
     *
     * @param GraphStoreInterface $graphStore 图存�?
     */
    public function setGraphStore(GraphStoreInterface $graphStore): void
    {
        $this->graphStore = $graphStore;
    }
    
    /**
     * 设置分词�?
     *
     * @param TokenizerInterface $tokenizer 分词�?
     */
    public function setTokenizer(TokenizerInterface $tokenizer): void
    {
        $this->tokenizer = $tokenizer;
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
        $this->config = array_merge($this->config, $config];
    }
    
    /**
     * 清除缓存
     */
    public function clearCache(): void
    {
        $this->queryCache = [];
    }
}

