<?php
/**
 * 文件名：QueryProcessor.php
 * 功能描述：知识图谱查询处理器 - 处理和优化知识图谱查询
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
use AlingAi\AI\Engines\NLP\TokenizerInterface;

/**
 * 知识图谱查询处理器
 *
 * 负责处理、解析和优化针对知识图谱的查询
 */
class QueryProcessor
{
    /**
     * 配置参数
     */
    private array $config;
    
    /**
     * 日志器
     */
    private ?LoggerInterface $logger;
    
    /**
     * 分词器
     */
    private ?TokenizerInterface $tokenizer;
    
    /**
     * 图存储接口
     */
    private ?GraphStoreInterface $graphStore;
    
    /**
     * 查询缓存
     */
    private array $queryCache = [];
    
    /**
     * 构造函数
     *
     * @param array $config 配置参数
     * @param LoggerInterface|null $logger 日志器
     * @param TokenizerInterface|null $tokenizer 分词器
     * @param GraphStoreInterface|null $graphStore 图存储
     */
    public function __construct(
        array $config = [], 
        ?LoggerInterface $logger = null,
        ?TokenizerInterface $tokenizer = null,
        ?GraphStoreInterface $graphStore = null
    ) {
        $this->config = array_merge($this->getDefaultConfig(), $config);
        $this->logger = $logger;
        $this->tokenizer = $tokenizer;
        $this->graphStore = $graphStore;
        
        if ($this->logger) {
            $this->logger->info("知识图谱查询处理器初始化成功");
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
            "use_cache" => true,
            "cache_ttl" => 3600,
            "max_results" => 100,
            "timeout" => 30,
            "fuzzy_matching" => true,
            "fuzzy_threshold" => 0.7,
            "query_expansion" => true,
            "query_rewriting" => true,
            "use_synonyms" => true,
            "default_language" => "zh-CN",
            "supported_languages" => ["zh-CN", "en-US"]
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
            "language" => $this->config["default_language"], 
            "max_results" => $this->config["max_results"], 
            "include_metadata" => true,
            "include_confidence" => true,
            "min_confidence" => 0.5
        ], $options);
        
        // 检查缓存
        $cacheKey = md5($query . json_encode($options));
        if ($this->config["use_cache"] && isset($this->queryCache[$cacheKey])) {
            return $this->queryCache[$cacheKey];
        }
        
        try {
            // 查询预处理
            $processedQuery = $this->preprocessQuery($query, $options["language"]);
            
            // 查询理解和意图识别
            $queryIntent = $this->analyzeQueryIntent($processedQuery);
            
            // 转换为结构化查询
            $structuredQuery = $this->convertToStructuredQuery($processedQuery, $queryIntent);
            
            // 执行查询
            $results = $this->executeQuery($structuredQuery, $options);
            
            // 后处理结果
            $finalResults = $this->postprocessResults($results, $query, $options);
            
            // 缓存结果
            if ($this->config["use_cache"]) {
                $this->queryCache[$cacheKey] = $finalResults;
            }
            
            return $finalResults;
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->error("查询处理失败", [
                    "query" => $query,
                    "error" => $e->getMessage(),
                    "trace" => $e->getTraceAsString()
                ]);
            }
            
            return [
                "success" => false,
                "error" => $e->getMessage(),
                "query" => $query,
                "results" => []
            ];
        }
    }
    
    /**
     * 执行结构化查询
     *
     * @param array $structuredQuery 结构化查询
     * @param array $options 查询选项
     * @return array 查询结果
     */
    public function executeStructuredQuery(array $structuredQuery, array $options = []): array
    {
        // 检查图存储是否可用
        if (!$this->graphStore) {
            throw new Exception("图存储未初始化，无法执行查询");
        }
        
        // 合并选项
        $options = array_merge([
            "max_results" => $this->config["max_results"], 
            "timeout" => $this->config["timeout"], 
            "include_metadata" => true
        ], $options);
        
        // 检查缓存
        $cacheKey = md5(json_encode($structuredQuery) . json_encode($options));
        if ($this->config["use_cache"] && isset($this->queryCache[$cacheKey])) {
            return $this->queryCache[$cacheKey];
        }
        
        // 查询优化
        $optimizedQuery = $this->optimizeQuery($structuredQuery);
        
        // 执行查询
        $results = $this->executeQuery($optimizedQuery, $options);
        
        // 缓存结果
        if ($this->config["use_cache"]) {
            $this->queryCache[$cacheKey] = $results;
        }
        
        return $results;
    }
    
    /**
     * 查询预处理
     *
     * @param string $query 原始查询
     * @param string $language 语言
     * @return array 预处理后的查询信息
     */
    private function preprocessQuery(string $query, string $language): array
    {
        // 规范化查询
        $query = $this->normalizeQuery($query);
        
        // 分词
        $tokens = $this->tokenizeQuery($query, $language);
        
        // 移除停用词
        $filteredTokens = $this->removeStopwords($tokens, $language);
        
        // 查询扩展
        if ($this->config["query_expansion"]) {
            $expandedTokens = $this->expandQuery($filteredTokens, $language);
        } else {
            $expandedTokens = $filteredTokens;
        }
        
        return [
            "original_query" => $query,
            "tokens" => $tokens,
            "filtered_tokens" => $filteredTokens,
            "expanded_tokens" => $expandedTokens,
            "language" => $language
        ];
    }
    
    /**
     * 规范化查询
     *
     * @param string $query 原始查询
     * @return string 规范化后的查询
     */
    private function normalizeQuery(string $query): string
    {
        // 去除多余空格
        $query = preg_replace("/\s+/", " ", trim($query));

        
        // 转换为小写（对于不区分大小写的语言）
        if (in_array($language, ["en-US"])) {
            $query = mb_strtolower($query);
        }
        
        // 移除特殊字符
        $query = preg_replace("/[^\p{L}\p{N}\s]/u", " ", $query);
        
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
            return $this->tokenizer->tokenize($query);
        }
        
        // 简单分词实现（实际项目中应使用专业分词器）
        if ($language === "zh-CN") {
            // 中文分词（简单实现）
            $chars = preg_split("//u", $query, -1, PREG_SPLIT_NO_EMPTY);
            $tokens = [];
            $currentToken = "";
            
            foreach ($chars as $char) {
                if (preg_match("/[\p{Han}]/u", $char)) {
                    // 中文字符
                    if ($currentToken !== "") {
                        $tokens[] = $currentToken;
                        $currentToken = "";
                    }
                    $tokens[] = $char;
                } elseif (preg_match("/[\p{L}\p{N}]/u", $char)) {
                    // 字母和数字
                    $currentToken .= $char;
                } else {
                    // 其他字符
                    if ($currentToken !== "") {
                        $tokens[] = $currentToken;
                        $currentToken = "";
                    }
                }
            }
            
            if ($currentToken !== "") {
                $tokens[] = $currentToken;
            }
            
            return $tokens;
        } else {
            // 英文分词
            return preg_split("/\s+/", $query, -1, PREG_SPLIT_NO_EMPTY);
        }
    }
    
    /**
     * 移除停用词
     *
     * @param array $tokens 分词结果
     * @param string $language 语言
     * @return array 过滤后的分词
     */
    private function removeStopwords(array $tokens, string $language): array
    {
        // 简单的停用词列表（实际项目中应使用更完整的列表）
        $stopwords = [];
        
        if ($language === "zh-CN") {
            $stopwords = ["的", "了", "是", "在", "我", "有", "和", "就", "不", "人", "都", "一", "一个", "上", "也", "很", "到", "说", "要", "去", "你", "会", "着", "没有", "看", "好", "自己", "这"];
        } elseif ($language === "en-US") {
            $stopwords = ["the", "a", "an", "and", "or", "but", "is", "are", "was", "were", "be", "been", "being", "in", "on", "at", "to", "for", "with", "by", "about", "against", "between", "into", "through"];
        }
        
        return array_values(array_filter($tokens, function($token) use ($stopwords) {
            return !in_array($token, $stopwords);
        }));
    }
    
    /**
     * 查询扩展
     *
     * @param array $tokens 分词结果
     * @param string $language 语言
     * @return array 扩展后的分词
     */
    private function expandQuery(array $tokens, string $language): array
    {
        if (!$this->config["query_expansion"]) {
            return $tokens;
        }
        
        $expandedTokens = $tokens;
        
        // 添加同义词（实际项目中应使用同义词库或词向量模型）
        if ($this->config["use_synonyms"]) {
            foreach ($tokens as $token) {
                $synonyms = $this->getSynonyms($token, $language);
                $expandedTokens = array_merge($expandedTokens, $synonyms);
            }
        }
        
        return array_unique($expandedTokens);
    }
    
    /**
     * 获取同义词
     *
     * @param string $token 词语
     * @param string $language 语言
     * @return array 同义词列表
     */
    private function getSynonyms(string $token, string $language): array
    {
        // 简单的同义词示例（实际项目中应使用同义词库或词向量模型）
        $synonyms = [];
        
        // 这里仅作为示例
        if ($language === "zh-CN") {
            $synonymDict = [
                "公司" => ["企业", "机构", "单位"],
                "研究" => ["研发", "探索", "调研"],
                "技术" => ["科技", "工艺", "技能"],
                "产品" => ["商品", "制品", "货品"],
                "服务" => ["服务项目", "支持"]
            ];
        } else {
            $synonymDict = [
                "company" => ["enterprise", "firm", "corporation"],
                "research" => ["study", "investigation", "exploration"],
                "technology" => ["tech", "technique", "engineering"],
                "product" => ["item", "goods", "merchandise"],
                "service" => ["support", "assistance", "aid"]
            ];
        }
        
        return $synonymDict[$token] ?? [];
    }
    
    /**
     * 分析查询意图
     *
     * @param array $processedQuery 预处理后的查询
     * @return array 查询意图
     */
    private function analyzeQueryIntent(array $processedQuery): array
    {
        // 实际项目中，这里应该使用更复杂的意图识别算法
        
        $tokens = $processedQuery["filtered_tokens"];
        $query = $processedQuery["original_query"];
        
        // 简单的意图识别
        $entityTypes = [];
        $relationTypes = [];
        $queryType = "entity_search"; // 默认为实体搜索
        
        // 检查是否包含关系查询关键词
        $relationKeywords = ["关系", "联系", "之间", "相关", "连接", "relation", "connection", "between", "related"];
        foreach ($relationKeywords as $keyword) {
            if (strpos($query, $keyword) !== false) {
                $queryType = "relation_search";
                break;
            }
        }
        
        // 检查是否包含路径查询关键词
        $pathKeywords = ["路径", "途径", "如何", "怎样", "path", "route", "how"];
        foreach ($pathKeywords as $keyword) {
            if (strpos($query, $keyword) !== false) {
                $queryType = "path_search";
                break;
            }
        }
        
        // 简单的实体类型识别
        $entityTypeKeywords = [
            "人物" => "Person",
            "人" => "Person",
            "组织" => "Organization",
            "公司" => "Organization",
            "地点" => "Location",
            "地方" => "Location",
            "事件" => "Event",
            "活动" => "Event",
            "产品" => "Product",
            "作品" => "WorkOfArt",
            "时间" => "Time",
            "日期" => "Date"
        ];
        
        foreach ($tokens as $token) {
            if (isset($entityTypeKeywords[$token])) {
                $entityTypes[] = $entityTypeKeywords[$token];
            }
        }
        
        // 简单的关系类型识别
        $relationTypeKeywords = [
            "创建" => "created",
            "属于" => "belongs_to",
            "包含" => "contains",
            "位于" => "located_in",
            "工作于" => "works_at",
            "参与" => "participated_in",
            "发生于" => "occurred_at"
        ];
        
        foreach ($tokens as $token) {
            if (isset($relationTypeKeywords[$token])) {
                $relationTypes[] = $relationTypeKeywords[$token];
            }
        }
        
        return [
            "query_type" => $queryType,
            "entity_types" => array_unique($entityTypes),
            "relation_types" => array_unique($relationTypes),
            "confidence" => 0.8 // 简单示例，实际应根据分析结果计算
        ];
    }
    
    /**
     * 转换为结构化查询
     *
     * @param array $processedQuery 预处理后的查询
     * @param array $queryIntent 查询意图
     * @return array 结构化查询
     */
    private function convertToStructuredQuery(array $processedQuery, array $queryIntent): array
    {
        $structuredQuery = [
            "type" => $queryIntent["query_type"],
            "tokens" => $processedQuery["expanded_tokens"],
            "filters" => []
        ];
        
        // 添加实体类型过滤器
        if (!empty($queryIntent["entity_types"])) {
            $structuredQuery["filters"]["entity_types"] = $queryIntent["entity_types"];
        }
        
        // 添加关系类型过滤器
        if (!empty($queryIntent["relation_types"])) {
            $structuredQuery["filters"]["relation_types"] = $queryIntent["relation_types"];
        }
        
        return $structuredQuery;
    }
    
    /**
     * 优化查询
     *
     * @param array $structuredQuery 结构化查询
     * @return array 优化后的查询
     */
    private function optimizeQuery(array $structuredQuery): array
    {
        // 实际项目中，这里应该实现查询优化逻辑
        // 例如：重写查询、调整执行计划等
        
        if ($this->config["query_rewriting"]) {
            // 简单的查询重写示例
            if ($structuredQuery["type"] === "entity_search" && empty($structuredQuery["filters"])) {
                // 为空查询添加默认过滤器
                $structuredQuery["filters"]["limit"] = $this->config["max_results"];
            }
        }
        
        return $structuredQuery;
    }
    
    /**
     * 执行查询
     *
     * @param array $structuredQuery 结构化查询
     * @param array $options 查询选项
     * @return array 查询结果
     */
    private function executeQuery(array $structuredQuery, array $options): array
    {
        if (!$this->graphStore) {
            throw new Exception("图存储未初始化，无法执行查询");
        }
        
        $startTime = microtime(true);
        $results = [];
        
        try {
            switch ($structuredQuery["type"]) {
                case "entity_search":
                    $results = $this->executeEntitySearch($structuredQuery, $options);
                    break;
                    
                case "relation_search":
                    $results = $this->executeRelationSearch($structuredQuery, $options);
                    break;
                    
                case "path_search":
                    $results = $this->executePathSearch($structuredQuery, $options);
                    break;
                    
                default:
                    throw new InvalidArgumentException("不支持的查询类型: " . $structuredQuery["type"]);
            }
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->error("查询执行失败", [
                    "query" => $structuredQuery,
                    "error" => $e->getMessage()
                ]);
            }
            throw $e;
        }
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        return [
            "results" => $results,
            "count" => count($results),
            "execution_time" => $executionTime,
            "query" => $structuredQuery
        ];
    }

    
    /**
     * 执行实体搜索
     *
     * @param array $structuredQuery 结构化查询
     * @param array $options 查询选项
     * @return array 查询结果
     */
    private function executeEntitySearch(array $structuredQuery, array $options): array
    {
        $criteria = [];
        $tokens = $structuredQuery["tokens"];
        $filters = $structuredQuery["filters"] ?? [];
        
        // 构建查询条件
        if (!empty($tokens)) {
            // 简单的文本匹配
            $textFilter = [];
            foreach ($tokens as $token) {
                $textFilter[] = ["text" => ["$" => $token]];
            }
            $criteria["$or"] = $textFilter;
        }
        
        // 添加实体类型过滤
        if (!empty($filters["entity_types"])) {
            $criteria["type"] = ["$in" => $filters["entity_types"]];
        }
        
        // 执行查询
        $entities = $this->graphStore->queryEntities($criteria, $options["max_results"]);
        
        // 如果启用了模糊匹配并且结果不足，尝试模糊搜索
        if ($this->config["fuzzy_matching"] && count($entities) < $options["max_results"] && !empty($tokens)) {
            $fuzzyEntities = $this->executeFuzzyEntitySearch($tokens, $filters, $options);
            
            // 合并结果并去重
            $entityIds = array_column($entities, "id");
            foreach ($fuzzyEntities as $entity) {
                if (!in_array($entity["id"], $entityIds)) {
                    $entities[] = $entity;
                    $entityIds[] = $entity["id"];
                    
                    // 达到最大结果数时停止
                    if (count($entities) >= $options["max_results"]) {
                        break;
                    }
                }
            }
        }
        
        return $entities;
    }
    
    /**
     * 执行模糊实体搜索
     *
     * @param array $tokens 查询词
     * @param array $filters 过滤条件
     * @param array $options 查询选项
     * @return array 查询结果
     */
    private function executeFuzzyEntitySearch(array $tokens, array $filters, array $options): array
    {
        // 实际项目中，这里应该实现基于编辑距离或其他相似度算法的模糊搜索
        // 这里简单模拟模糊搜索结果
        
        $allEntities = $this->graphStore->getAllEntities();
        $fuzzyResults = [];
        
        foreach ($allEntities as $entity) {
            // 检查实体类型过滤
            if (!empty($filters["entity_types"]) && !in_array($entity["type"], $filters["entity_types"])) {
                continue;
            }
            
            // 计算相似度
            $maxSimilarity = 0;
            foreach ($tokens as $token) {
                $similarity = $this->calculateTextSimilarity($token, $entity["text"]);
                $maxSimilarity = max($maxSimilarity, $similarity);
            }
            
            // 如果相似度超过阈值，添加到结果
            if ($maxSimilarity >= $this->config["fuzzy_threshold"]) {
                $entity["similarity"] = $maxSimilarity;
                $fuzzyResults[] = $entity;
            }
        }
        
        // 按相似度排序
        usort($fuzzyResults, function($a, $b) {
            return $b["similarity"] <=> $a["similarity"];
        });
        
        return $fuzzyResults;
    }
    
    /**
     * 计算文本相似度
     *
     * @param string $text1 文本1
     * @param string $text2 文本2
     * @return float 相似度 (0-1)
     */
    private function calculateTextSimilarity(string $text1, string $text2): float
    {
        // 简单的相似度计算（实际项目中应使用更复杂的算法）
        if ($text1 === $text2) {
            return 1.0;
        }
        
        if (empty($text1) || empty($text2)) {
            return 0.0;
        }
        
        // 检查包含关系
        if (stripos($text2, $text1) !== false) {
            return 0.8;
        }
        
        if (stripos($text1, $text2) !== false) {
            return 0.8;
        }
        
        // 简单的编辑距离计算
        $len1 = mb_strlen($text1);
        $len2 = mb_strlen($text2);
        
        // 如果长度差异太大，直接返回低相似度
        if (abs($len1 - $len2) / max($len1, $len2) > 0.5) {
            return 0.1;
        }
        
        // 计算字符重叠
        $chars1 = preg_split("//u", $text1, -1, PREG_SPLIT_NO_EMPTY);
        $chars2 = preg_split("//u", $text2, -1, PREG_SPLIT_NO_EMPTY);
        
        $common = count(array_intersect($chars1, $chars2));
        $total = count(array_unique(array_merge($chars1, $chars2)));
        
        return $common / $total;
    }
    
    /**
     * 执行关系搜索
     *
     * @param array $structuredQuery 结构化查询
     * @param array $options 查询选项
     * @return array 查询结果
     */
    private function executeRelationSearch(array $structuredQuery, array $options): array
    {
        $criteria = [];
        $filters = $structuredQuery["filters"] ?? [];
        
        // 添加关系类型过滤
        if (!empty($filters["relation_types"])) {
            $criteria["type"] = ["$in" => $filters["relation_types"]];
        }
        
        // 执行查询
        return $this->graphStore->queryRelations($criteria, $options["max_results"]);
    }
    
    /**
     * 执行路径搜索
     *
     * @param array $structuredQuery 结构化查询
     * @param array $options 查询选项
     * @return array 查询结果
     */
    private function executePathSearch(array $structuredQuery, array $options): array
    {
        // 实际项目中，这里应该实现路径搜索算法
        // 例如：广度优先搜索、最短路径算法等
        
        // 这里返回空结果，因为简化实现不支持路径搜索
        return [];
    }
    
    /**
     * 后处理结果
     *
     * @param array $results 查询结果
     * @param string $originalQuery 原始查询
     * @param array $options 查询选项
     * @return array 处理后的结果
     */
    private function postprocessResults(array $results, string $originalQuery, array $options): array
    {
        // 过滤低置信度结果
        if (isset($options["min_confidence"])) {
            $minConfidence = $options["min_confidence"];
            
            if (isset($results["results"])) {
                $results["results"] = array_filter($results["results"], function($item) use ($minConfidence) {
                    return !isset($item["confidence"]) || $item["confidence"] >= $minConfidence;
                });
                $results["count"] = count($results["results"]);
            }
        }
        
        // 移除元数据（如果需要）
        if (!($options["include_metadata"] ?? true)) {
            if (isset($results["results"])) {
                foreach ($results["results"] as &$item) {
                    if (isset($item["metadata"])) {
                        unset($item["metadata"]);
                    }
                }
            }
        }
        
        // 移除置信度（如果需要）
        if (!($options["include_confidence"] ?? true)) {
            if (isset($results["results"])) {
                foreach ($results["results"] as &$item) {
                    if (isset($item["confidence"])) {
                        unset($item["confidence"]);
                    }
                }
            }
        }
        
        // 添加查询信息
        $results["original_query"] = $originalQuery;
        $results["success"] = true;
        
        return $results;
    }
    
    /**
     * 处理查询
     *
     * @param string $query 查询
     * @param GraphStoreInterface $graphStore 图存储
     * @param array $options 选项
     * @return array 查询结果
     */
    public function process(string $query, GraphStoreInterface $graphStore, array $options = []): array
    {
        $this->graphStore = $graphStore;
        return $this->processNaturalLanguageQuery($query, $options);
    }
    
    /**
     * 清除查询缓存
     */
    public function clearCache(): void
    {
        $this->queryCache = [];
    }
}
