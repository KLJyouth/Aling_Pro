<?php
/**
 * 英文分词器测试
 */

require_once __DIR__ . '/../ai-engines/nlp/TokenizerInterface.php';
require_once __DIR__ . '/../ai-engines/nlp/EnglishTokenizer.php';

use AlingAi\AI\Engines\NLP\EnglishTokenizer;

// 测试EnglishTokenizer是否可以正确实例化
try {
    $tokenizer = new EnglishTokenizer();
    echo "EnglishTokenizer实例化成功!\n";
    
    // 测试分词功能
    $text = "Hello world! This is a test for the English tokenizer.";
    $tokens = $tokenizer->tokenize($text);
    
    echo "分词结果：\n";
    foreach ($tokens as $token) {
        echo "- {$token['text']} (类型: {$token['type']})\n";
    }
    
    echo "\n测试通过！\n";
} catch (Throwable $e) {
    echo "错误: " . $e->getMessage() . "\n";
    echo "在文件: " . $e->getFile() . " 第 " . $e->getLine() . " 行\n";
    echo "堆栈跟踪:\n" . $e->getTraceAsString() . "\n";
} 