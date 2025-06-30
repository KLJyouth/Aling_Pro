<?php
/**
 * 文件名：EntityExtractor.php
 * 功能描述：实体提取器 - 从文本中提取实体
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
use AlingAi\AI\Engines\NLP\NERModel;
use AlingAi\AI\Engines\NLP\TokenizerInterface;
use AlingAi\AI\Engines\NLP\UniversalTokenizer;

/**
 * 实体提取器
 * 
 * 从文本中提取实体，支持多种实体类型和语言
 */
class EntityExtractor
{
    /**
     * 配置参数
     */
    private array $config;
    
    /**
     * 命名实体识别模型
     */
    private ?NERModel $nerModel = null;
    
    /**
     * 分词器
     */
    private ?TokenizerInterface $tokenizer = null;
    
    /**
     * 实体类型映射
     */
    private array $entityTypeMap = [];


    /**
     * 构造函数
     * 
     * @param array $config 配置参数
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->getDefaultConfig(), $config);
        $this->initializeComponents();
        $this->initializeEntityTypeMap();
    }
    
    /**
     * 获取默认配置
     * 
     * @return array 默认配置
     */
    private function getDefaultConfig(): array
    {
        return [
            "confidence_threshold" => 0.7,
            "max_entity_length" => 50,
            "entity_types" => [
                "PERSON", "ORGANIZATION", "LOCATION", "DATE", "TIME",
                "MONEY", "PERCENT", "PRODUCT", "EVENT", "WORK_OF_ART"
            ], 
            "enable_entity_linking" => true,
            "enable_coreference_resolution" => true
        ];
    }
    
    /**
     * 初始化组件
     */
    private function initializeComponents(): void
    {
        $this->nerModel = new NERModel();
        $this->tokenizer = new UniversalTokenizer();
    }
    
    /**
     * 初始化实体类型映射
     */
    private function initializeEntityTypeMap(): void
    {
        $this->entityTypeMap = [
            "PERSON" => "Person",
            "ORGANIZATION" => "Organization",
            "LOCATION" => "Location",
            "DATE" => "Date",
            "TIME" => "Time",
            "MONEY" => "Money",
            "PERCENT" => "Percent",
            "PRODUCT" => "Product",
            "EVENT" => "Event",
            "WORK_OF_ART" => "WorkOfArt"
        ];
    }

    
    /**
     * 从文本中提取实体
     * 
     * @param string $text 输入文本
     * @param array $options 提取选项
     * @return array 提取的实体
     * @throws InvalidArgumentException
     */
    public function extract(string $text, array $options = []): array
    {
        // 验证文本
        if (empty($text)) {
            throw new InvalidArgumentException("文本不能为空");
        }
        
        // 合并选项
        $options = array_merge($this->config, $options);
        
        // 分词
        $tokens = $this->tokenizer->tokenize($text);
        
        // 命名实体识别
        $entities = $this->nerModel->recognize($tokens);
        
        // 过滤和转换实体
        $filteredEntities = $this->filterAndTransformEntities($entities, $options);
        
        // 实体链接
        if ($options["enable_entity_linking"]) {
            $filteredEntities = $this->linkEntities($filteredEntities, $options);
        }
        
        // 指代解析
        if ($options["enable_coreference_resolution"]) {
            $filteredEntities = $this->resolveCoreferenceEntities($filteredEntities, $text, $options);
        }
        
        return $filteredEntities;
    }

    
    /**
     * 过滤和转换实体
     * 
     * @param array $entities 实体列表
     * @param array $options 选项
     * @return array 过滤和转换后的实体
     */
    private function filterAndTransformEntities(array $entities, array $options): array
    {
        $result = [];
        $confidenceThreshold = $options["confidence_threshold"];
        $maxEntityLength = $options["max_entity_length"];
        $allowedEntityTypes = $options["entity_types"];
        
        foreach ($entities as $entity) {
            // 过滤低置信度实体
            if ($entity["confidence"] < $confidenceThreshold) {
                continue;
            }
            
            // 过滤过长的实体
            if (mb_strlen($entity["text"], "UTF-8") > $maxEntityLength) {
                continue;
            }
            
            // 过滤不在允许类型列表中的实体
            if (!in_array($entity["type"], $allowedEntityTypes)) {
                continue;
            }
            
            // 转换实体类型
            $entityType = $this->mapEntityType($entity["type"]);
            
            // 构建实体对象
            $result[] = [
                "id" => $this->generateEntityId($entity),
                "text" => $entity["text"], 
                "type" => $entityType,
                "confidence" => $entity["confidence"], 
                "start_pos" => $entity["start_pos"], 
                "end_pos" => $entity["end_pos"], 
                "metadata" => [
                    "source" => "text_extraction",
                    "extraction_time" => time()
                ], 
                "attributes" => $this->extractEntityAttributes($entity)
            ];
        }
        
        return $result;
    }

    /**
     * 映射实体类型
     * 
     * @param string $nerType NER类型
     * @return string 映射后的类型
     */
    private function mapEntityType(string $nerType): string
    {
        return $this->entityTypeMap[$nerType] ?? "Unknown";
    }
    
    /**
     * 生成实体ID
     * 
     * @param array $entity 实体
     * @return string 实体ID
     */
    private function generateEntityId(array $entity): string
    {
        // 使用实体文本、类型和位置生成唯一ID
        $idBase = $entity["text"] . "_" . $entity["type"] . "_" . $entity["start_pos"] . "_" . $entity["end_pos"];
        return md5($idBase);
    }
    
    /**
     * 提取实体属性
     * 
     * @param array $entity 实体
     * @return array 实体属性
     */
    private function extractEntityAttributes(array $entity): array
    {
        $attributes = [];
        
        // 根据实体类型提取不同属性
        switch ($entity["type"]) {
            case "PERSON":
                // 可能的人物属性
                if (isset($entity["gender"])) {
                    $attributes["gender"] = $entity["gender"];
                }
                break;
                
            case "ORGANIZATION":
                // 可能的组织属性
                if (isset($entity["org_type"])) {
                    $attributes["organizationType"] = $entity["org_type"];
                }
                break;
                
            case "LOCATION":
                // 可能的位置属性
                if (isset($entity["location_type"])) {
                    $attributes["locationType"] = $entity["location_type"];
                }
                if (isset($entity["coordinates"])) {
                    $attributes["coordinates"] = $entity["coordinates"];
                }
                break;
                
            case "DATE":
                // 可能的日期属性
                if (isset($entity["normalized_date"])) {
                    $attributes["normalizedDate"] = $entity["normalized_date"];
                }
                break;
        }
        
        return $attributes;
    }
    
    /**
     * 实体链接 - 将提取的实体链接到知识库
     * 
     * @param array $entities 实体列表
     * @param array $options 选项
     * @return array 链接后的实体
     */
    private function linkEntities(array $entities, array $options): array
    {
        // 实际项目中，这里会调用知识库API进行实体链接
        // 本实现为模拟
        
        foreach ($entities as &$entity) {
            // 模拟知识库链接
            $entity["knowledge_links"] = $this->simulateKnowledgeLinks($entity);
        }
        
        return $entities;
    }
    
    /**
     * 模拟知识库链接
     * 
     * @param array $entity 实体
     * @return array 知识链接
     */
    private function simulateKnowledgeLinks(array $entity): array
    {
        $links = [];
        
        // 根据实体类型生成不同的模拟链接
        switch ($entity["type"]) {
            case "Person":
                $links[] = [
                    "source" => "internal_kb",
                    "id" => "person_" . md5($entity["text"]),
                    "confidence" => 0.85,
                    "url" => "/kb/person/" . urlencode($entity["text"])
                ];
                break;
                
            case "Organization":
                $links[] = [
                    "source" => "internal_kb",
                    "id" => "org_" . md5($entity["text"]),
                    "confidence" => 0.82,
                    "url" => "/kb/organization/" . urlencode($entity["text"])
                ];
                break;
                
            case "Location":
                $links[] = [
                    "source" => "internal_kb",
                    "id" => "loc_" . md5($entity["text"]),
                    "confidence" => 0.9,
                    "url" => "/kb/location/" . urlencode($entity["text"])
                ];
                break;
        }
        
        return $links;
    }
    
    /**
     * 解析指代实体 - 解决代词和指代问题
     * 
     * @param array $entities 实体列表
     * @param string $text 原文本
     * @param array $options 选项
     * @return array 解析后的实体
     */
    private function resolveCoreferenceEntities(array $entities, string $text, array $options): array
    {
        // 实际项目中，这里会使用指代解析模型
        // 本实现为简化版
        
        // 模拟一些指代关系
        $coreferences = $this->simulateCoreferences($text, $entities);
        
        // 合并指代实体
        foreach ($coreferences as $coref) {
            foreach ($entities as &$entity) {
                if ($entity["id"] === $coref["entity_id"]) {
                    if (!isset($entity["coreferences"])) {
                        $entity["coreferences"] = [];
                    }
                    $entity["coreferences"][] = [
                        "text" => $coref["mention"],
                        "start_pos" => $coref["start_pos"],
                        "end_pos" => $coref["end_pos"],
                        "type" => "pronoun"
                    ];
                    break;
                }
            }
        }
        
        return $entities;
    }
    
    /**
     * 模拟指代关系
     * 
     * @param string $text 文本
     * @param array $entities 实体列表
     * @return array 指代关系
     */
    private function simulateCoreferences(string $text, array $entities): array
    {
        $coreferences = [];
        
        // 这里仅做简单模拟
        // 实际项目中需要使用更复杂的指代解析算法
        
        // 查找人称代词
        $pronouns = ["他", "她", "它", "他们", "她们", "它们", "这个", "那个", "这些", "那些"];
        
        foreach ($pronouns as $pronoun) {
            $pos = mb_strpos($text, $pronoun);
            while ($pos !== false) {
                // 找到最近的前置实体
                $closestEntity = null;
                $minDistance = PHP_INT_MAX;
                
                foreach ($entities as $entity) {
                    if ($entity["end_pos"] < $pos) {
                        $distance = $pos - $entity["end_pos"];
                        if ($distance < $minDistance) {
                            $minDistance = $distance;
                            $closestEntity = $entity;
                        }
                    }
                }
                
                if ($closestEntity && $minDistance < 100) { // 简单的距离阈值
                    $coreferences[] = [
                        "entity_id" => $closestEntity["id"],
                        "mention" => $pronoun,
                        "start_pos" => $pos,
                        "end_pos" => $pos + mb_strlen($pronoun)
                    ];
                }
                
                $pos = mb_strpos($text, $pronoun, $pos + mb_strlen($pronoun));
            }
        }
        
        return $coreferences;
    }
    
    /**
     * 批量提取实体
     * 
     * @param array $texts 文本数组
     * @param array $options 选项
     * @return array 实体数组
     */
    public function batchExtract(array $texts, array $options = []): array
    {
        $results = [];
        
        foreach ($texts as $index => $text) {
            try {
                $results[$index] = $this->extract($text, $options);
            } catch (Exception $e) {
                $results[$index] = [
                    "error" => $e->getMessage()
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * 获取支持的实体类型
     * 
     * @return array 支持的实体类型
     */
    public function getSupportedEntityTypes(): array
    {
        return array_values($this->entityTypeMap);
    }
}
