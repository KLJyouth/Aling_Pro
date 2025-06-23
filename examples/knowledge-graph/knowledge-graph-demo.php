<?php
/**
 * 文件名：knowledge-graph-demo.php
 * 功能描述：知识图谱演示 - 展示知识图谱的基本功能
 * 创建时间：2025-01-XX
 * 最后修改：2025-01-XX
 * 版本：1.0.0
 *
 * @package AlingAi\Examples\KnowledgeGraph
 * @author AlingAi Team
 * @license MIT
 */

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use AlingAi\AI\Engines\KnowledgeGraph\MemoryGraphStore;
use AlingAi\AI\Engines\KnowledgeGraph\EntityExtractor;
use AlingAi\AI\Engines\KnowledgeGraph\RelationExtractor;
use AlingAi\AI\Engines\KnowledgeGraph\QueryProcessor;
use AlingAi\AI\Engines\KnowledgeGraph\ReasoningEngine;
use AlingAi\AI\Engines\NLP\UniversalTokenizer;
use AlingAi\Core\Logger\FileLogger;

// 创建日志器
$logger = new FileLogger([
    'log_file' => __DIR__ . '/../../logs/kg-demo.log',
    'log_level' => 'debug'
]);

echo "========== 知识图谱演示 ==========\n\n";

// 创建分词器
$tokenizer = new UniversalTokenizer([
    'default_language' => 'zh-CN',
    'supported_languages' => ['zh-CN', 'en-US']
], $logger);

// 创建图存储
$graphStore = new MemoryGraphStore([
    'enable_persistence' => true,
    'storage_path' => __DIR__ . '/../../data/graph_store.json'
], $logger);

// 创建实体提取器
$entityExtractor = new EntityExtractor([
    'min_confidence' => 0.6
], $logger, $tokenizer);

// 创建关系提取器
$relationExtractor = new RelationExtractor([
    'min_confidence' => 0.6
], $logger, $tokenizer);

// 创建查询处理器
$queryProcessor = new QueryProcessor([
    'fuzzy_matching' => true,
    'query_expansion' => true
], $logger, $tokenizer, $graphStore);

// 创建推理引擎
$reasoningEngine = new ReasoningEngine([
    'max_inference_depth' => 3
], $logger, $graphStore);

// 示例文本
$text = <<<TEXT
人工智能（Artificial Intelligence，简称AI）是计算机科学的一个分支，它企图了解智能的实质，并生产出一种新的能以人类智能相似的方式做出反应的智能机器。人工智能的研究包括机器人、语言识别、图像识别、自然语言处理和专家系统等。随着计算机技术的发展，人工智能已经不再是一个纯粹的概念，而是已经深入到了我们生活的方方面面。

深度学习是人工智能的一个重要分支，它是一种基于人工神经网络的机器学习方法。深度学习通过构建具有多层处理能力的计算模型，来学习数据的多层次表示和特征。

机器学习是人工智能的另一个重要分支，它研究计算机怎样模拟或实现人类的学习行为，以获取新的知识或技能，重新组织已有的知识结构使之不断改善自身性能。
TEXT;

echo "示例文本：\n$text\n\n";

// 1. 提取实体
echo "1. 提取实体\n";
$entities = $entityExtractor->extractEntities($text);
echo "发现 " . count($entities) . " 个实体：\n";
foreach ($entities as $index => $entity) {
    echo ($index + 1) . ". {$entity['text']} (类型: {$entity['type']}, 置信度: {$entity['confidence']})\n";
}
echo "\n";

// 2. 提取关系
echo "2. 提取关系\n";
$relations = $relationExtractor->extractRelations($text, $entities);
echo "发现 " . count($relations) . " 个关系：\n";
foreach ($relations as $index => $relation) {
    echo ($index + 1) . ". {$relation['source']} -> {$relation['type']} -> {$relation['target']} (置信度: {$relation['confidence']})\n";
}
echo "\n";

// 3. 构建知识图谱
echo "3. 构建知识图谱\n";
// 添加实体
foreach ($entities as $entity) {
    $graphStore->addEntity($entity['id'] ?? uniqid('entity_'), [
        'name' => $entity['text'],
        'type' => $entity['type'],
        'confidence' => $entity['confidence'],
        'metadata' => $entity['metadata'] ?? []
    ]);
}

// 添加关系
foreach ($relations as $relation) {
    $graphStore->addRelation(
        $relation['source_id'] ?? $relation['source'],
        $relation['target_id'] ?? $relation['target'],
        $relation['type'],
        [
            'confidence' => $relation['confidence'],
            'metadata' => $relation['metadata'] ?? []
        ]
    );
}

echo "知识图谱构建完成，共有 " . $graphStore->getEntityCount() . " 个实体和 " . $graphStore->getRelationCount() . " 个关系。\n\n";

// 4. 查询知识图谱
echo "4. 查询知识图谱\n";

// 自然语言查询示例
$queries = [
    '什么是人工智能？',
    '深度学习与机器学习的关系是什么？',
    '人工智能的分支有哪些？',
    '计算机科学与人工智能的关系'
];

foreach ($queries as $query) {
    echo "查询: $query\n";
    $result = $queryProcessor->processNaturalLanguageQuery($query);
    
    if ($result['success']) {
        echo "找到 " . $result['count'] . " 个结果:\n";
        foreach ($result['results'] as $index => $item) {
            echo "  - ";
            if (isset($item['name'])) {
                echo "{$item['name']}";
                if (isset($item['type'])) {
                    echo " (类型: {$item['type']})";
                }
                if (isset($item['confidence'])) {
                    echo " [置信度: " . round($item['confidence'], 2) . "]";
                }
            } elseif (isset($item['source']) && isset($item['target'])) {
                echo "{$item['source']} -> {$item['type']} -> {$item['target']}";
            }
            echo "\n";
        }
    } else {
        echo "查询失败: " . ($result['error'] ?? '未知错误') . "\n";
    }
    echo "\n";
}

// 5. 知识推理
echo "5. 知识推理\n";

// 添加一些额外的知识用于推理
$graphStore->addEntity('entity_ml_application', [
    'name' => '机器学习应用',
    'type' => 'Application',
    'metadata' => ['description' => '机器学习的实际应用场景']
]);

$graphStore->addRelation('机器学习', 'entity_ml_application', 'HAS_APPLICATION');

$graphStore->addEntity('entity_image_recognition', [
    'name' => '图像识别',
    'type' => 'Technology',
    'metadata' => ['description' => '识别和处理图像的技术']
]);

$graphStore->addRelation('entity_ml_application', 'entity_image_recognition', 'INCLUDES');
$graphStore->addRelation('深度学习', 'entity_image_recognition', 'ENABLES');

// 执行推理
$inferenceQueries = [
    '人工智能可以用于图像识别吗？',
    '哪些技术支持图像识别？',
    '机器学习有哪些应用？'
];

foreach ($inferenceQueries as $query) {
    echo "推理查询: $query\n";
    $inferenceResult = $reasoningEngine->infer($query);
    
    if ($inferenceResult['success']) {
        echo "推理结果:\n";
        foreach ($inferenceResult['inferences'] as $index => $inference) {
            echo "  - " . $inference['statement'] . " [置信度: " . round($inference['confidence'], 2) . "]\n";
            if (!empty($inference['reasoning_path'])) {
                echo "    推理路径: " . implode(' -> ', $inference['reasoning_path']) . "\n";
            }
        }
    } else {
        echo "推理失败: " . ($inferenceResult['error'] ?? '未知错误') . "\n";
    }
    echo "\n";
}

// 6. 保存和加载图谱
echo "6. 保存和加载图谱\n";
$saveResult = $graphStore->saveToFile(__DIR__ . '/../../data/demo_graph.json');
echo "图谱保存" . ($saveResult ? '成功' : '失败') . "\n";

$loadResult = $graphStore->loadFromFile(__DIR__ . '/../../data/demo_graph.json');
echo "图谱加载" . ($loadResult ? '成功' : '失败') . "，共有 " . $graphStore->getEntityCount() . " 个实体和 " . $graphStore->getRelationCount() . " 个关系。\n";

echo "\n完成演示。\n"; 