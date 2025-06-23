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
            'confidence_threshold' => 0.7,
            'max_entity_length' => 50,
            'entity_types' => [
                'PERSON', 'ORGANIZATION', 'LOCATION', 'DATE', 'TIME',
                'MONEY', 'PERCENT', 'PRODUCT', 'EVENT', 'WORK_OF_ART'
            ],
            'enable_entity_linking' => true,
            'enable_coreference_resolution' => true
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
            'PERSON' => 'Person',
            'ORGANIZATION' => 'Organization',
            'LOCATION' => 'Location',
            'DATE' => 'Date',
            'TIME' => 'Time',
            'MONEY' => 'Money',
            'PERCENT' => 'Percent',
            'PRODUCT' => 'Product',
            'EVENT' => 'Event',
            'WORK_OF_ART' => 'WorkOfArt'
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
            throw new InvalidArgumentException('文本不能为空');
        }
        
        // 处理选项
        $options = array_merge($this->config, $options);
        
        // 分词
        $tokens = $this->tokenizer->tokenize($text);
        
        // 命名实体识别
        $entities = $this->nerModel->recognize($tokens);
        
        // 过滤和转换实体
        $filteredEntities = $this->filterAndTransformEntities($entities, $options);
        
        // 实体链接
        if ($options['enable_entity_linking']) {
            $filteredEntities = $this->linkEntities($filteredEntities, $options);
        }
        
        // 共指消解
        if ($options['enable_coreference_resolution']) {
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
        $confidenceThreshold = $options['confidence_threshold'];
        $maxEntityLength = $options['max_entity_length'];
        $allowedEntityTypes = $options['entity_types'];
        
        foreach ($entities as $entity) {
            // 过滤低置信度实体
            if ($entity['confidence'] < $confidenceThreshold) {
                continue;
            }
            
            // 过滤过长的实体
            if (mb_strlen($entity['text'], 'UTF-8') > $maxEntityLength) {
                continue;
            }
            
            // 过滤不在允许类型列表中的实体
            if (!in_array($entity['type'], $allowedEntityTypes)) {
                continue;
            }
            
            // 转换实体类型
            $entityType = $this->mapEntityType($entity['type']);
            
            // 构建实体对象
            $result[] = [
                'id' => $this->generateEntityId($entity),
                'text' => $entity['text'],
                'type' => $entityType,
                'confidence' => $entity['confidence'],
                'start_pos' => $entity['start_pos'],
                'end_pos' => $entity['end_pos'],
                'metadata' => [
                    'source' => 'text_extraction',
                    'extraction_time' => time()
                ],
                'attributes' => $this->extractEntityAttributes($entity)
            ];
        }
        
        return $result;
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
        $confidenceThreshold = $options['confidence_threshold'];
        $maxEntityLength = $options['max_entity_length'];
        $allowedEntityTypes = $options['entity_types'];
        
        foreach ($entities as $entity) {
            // 过滤低置信度实体
            if ($entity['confidence'] < $confidenceThreshold) {
                continue;
            }
            
            // 过滤过长的实体
            if (mb_strlen($entity['text'], 'UTF-8') > $maxEntityLength) {
                continue;
            }
            
            // 过滤不在允许类型列表中的实体
            if (!in_array($entity['type'], $allowedEntityTypes)) {
                continue;
            }
            
            // 转换实体类型
            $entityType = $this->mapEntityType($entity['type']);
            
            // 构建实体对象
            $result[] = [
                'id' => $this->generateEntityId($entity),
                'text' => $entity['text'],
                'type' => $entityType,
                'confidence' => $entity['confidence'],
                'start_pos' => $entity['start_pos'],
                'end_pos' => $entity['end_pos'],
                'metadata' => [
                    'source' => 'text_extraction',
                    'extraction_time' => time()
                ],
                'attributes' => $this->extractEntityAttributes($entity)
            ];
        }
        
        return $result;
    }

    /**
     * 映射实体类型
     * 
     * @param string $nerType NER实体类型
     * @return string 映射后的实体类型
     */
    private function mapEntityType(string $nerType): string
    {
        return $this->entityTypeMap[$nerType] ?? 'Unknown';
    }
    
    /**
     * 生成实体ID
     * 
     * @param array $entity 实体
     * @return string 实体ID
     */
    private function generateEntityId(array $entity): string
    {
        return md5($entity['text'] . $entity['type'] . $entity['start_pos'] . $entity['end_pos']);
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
        
        // 根据实体类型提取特定属性
        switch ($entity['type']) {
            case 'PERSON':
                // 可以提取人名的姓、名等信息
                break;
            case 'ORGANIZATION':
                // 可以提取组织机构的类型、行业等信息
                break;
            case 'LOCATION':
                // 可以提取地点的经纬度、所属国家等信息
                break;
            case 'DATE':
                // 可以提取日期的年、月、日等信息
                if (isset($entity['normalized_value'])) {
                    $attributes['normalized_date'] = $entity['normalized_value'];
                }
                break;
            case 'TIME':
                // 可以提取时间的小时、分钟等信息
                if (isset($entity['normalized_value'])) {
                    $attributes['normalized_time'] = $entity['normalized_value'];
                }
                break;
        }
        
        return $attributes;
    }

    /**
     * 判断两个实体是否存在共指关系
     * 
     * @param array $entity1 第一个实体
     * @param array $entity2 第二个实体
     * @param string $text 原始文本
     * @return bool 是否存在共指关系
     */
    private function areCorefEntities(array $entity1, array $entity2, string $text): bool
    {
        // 实现共指关系判断逻辑
        // 这里可以使用更复杂的算法，如基于规则或机器学习的方法
        // 简单实现：检查文本相似度和位置关系
        
        // 如果两个实体文本完全相同，可能是共指
        if ($entity1['text'] === $entity2['text']) {
            return true;
        }
        
        // 如果一个实体是另一个的子串，可能是共指
        if (stripos($entity1['text'], $entity2['text']) !== false || 
            stripos($entity2['text'], $entity1['text']) !== false) {
            return true;
        }
        
        // 更复杂的共指检测逻辑可以在这里实现
        // 例如，检查代词和名词之间的关系，或者使用外部服务
        
        return false;
    }
}
