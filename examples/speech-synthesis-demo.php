<?php
/**
 * 文件名：speech-synthesis-demo.php
 * 功能描述：语音合成模块示例演示
 * 创建时间：2025-01-XX
 * 最后修改：2025-01-XX
 * 版本：1.0.0
 *
 * 本示例演示了如何使用AlingAi的语音合成模块
 */

require_once __DIR__ . '/../vendor/autoload.php';

use AlingAi\Core\Logger\FileLogger;
use AlingAi\Utils\CacheManager;
use AlingAi\Engines\Speech\TextProcessor;
use AlingAi\Engines\Speech\SpeechSynthesisEngine;

// 创建日志记录器
$logger = new FileLogger([
    'log_dir' => __DIR__ . '/../logs',
    'log_filename' => 'speech-synthesis-demo.log',
    'log_level' => 'debug'
]);

// 创建缓存管理器
$cache = new CacheManager([
    'cache_dir' => __DIR__ . '/../cache/speech',
    'ttl' => 3600
]);

// 设置语音合成引擎的配置
$synthesisConfig = [
    'model_type' => 'neural',               // neural, parametric, concatenative
    'voice_id' => 'default',                // 默认声音
    'language' => 'zh-CN',                  // 语言
    'sample_rate' => 22050,                 // 采样率
    'audio_channels' => 1,                  // 声道数
    'pitch_adjustment' => 0.0,              // 音调调整 (-1.0 to 1.0)
    'speaking_rate' => 1.0,                 // 语速 (0.5 to 2.0)
    'volume' => 1.0,                        // 音量 (0.0 to 2.0)
    'output_format' => 'wav',               // 输出格式
    'use_gpu' => false,                     // 是否使用GPU
    'cache_enabled' => true                 // 是否启用缓存
];

// 显示标题
echo "============================================================\n";
echo "                AlingAi 语音合成演示程序                      \n";
echo "============================================================\n\n";

try {
    // 创建文本处理器
    echo "初始化文本处理器...\n";
    $textProcessor = new TextProcessor([
        'default_language' => 'zh-CN',
        'normalize_abbreviations' => true
    ], $logger);
    
    // 创建语音合成引擎
    echo "初始化语音合成引擎...\n";
    $synthesisEngine = new SpeechSynthesisEngine($synthesisConfig, $logger, $cache);
    
    // 显示可用声音列表
    echo "\n可用声音列表:\n";
    $voices = $synthesisEngine->getAvailableVoices();
    
    echo str_repeat("-", 60) . "\n";
    echo sprintf("%-15s | %-20s | %-10s\n", "声音ID", "名称", "语言");
    echo str_repeat("-", 60) . "\n";
    
    foreach ($voices as $voiceId => $voice) {
        echo sprintf("%-15s | %-20s | %-10s\n", 
            $voiceId, 
            $voice['name'], 
            $voice['language']
        );
    }
    echo str_repeat("-", 60) . "\n\n";
    
    // 示例文本
    $text = "欢迎使用AlingAi语音合成系统。这个系统可以将文本转换为自然的语音。";
    echo "示例文本: \n$text\n\n";
    
    // 使用文本处理器
    echo "处理文本...\n";
    $processedText = $textProcessor->process($text);
    echo "处理后文本: \n$processedText\n\n";
    
    // 文本分段
    echo "文本分段...\n";
    $segments = $textProcessor->segment($text);
    echo "分段结果 (" . count($segments) . " 个片段):\n";
    foreach ($segments as $i => $segment) {
        echo ($i+1) . ": $segment\n";
    }
    echo "\n";
    
    // 语音合成
    echo "执行语音合成...\n";
    $outputDir = __DIR__ . '/../output/speech';
    
    // 确保输出目录存在
    if (!file_exists($outputDir)) {
        mkdir($outputDir, 0755, true);
    }
    
    $outputFile = $outputDir . '/synthesis_demo_' . date('Ymd_His') . '.wav';
    
    // 调用合成API，将输出保存到文件
    $startTime = microtime(true);
    $result = $synthesisEngine->synthesizeToFile($text, $outputFile);
    $duration = microtime(true) - $startTime;
    
    // 显示合成结果
    echo "\n合成结果:\n";
    echo "- 输出文件: $outputFile\n";
    echo "- 文件大小: " . number_format($result['file_size'] / 1024, 2) . " KB\n";
    echo "- 音频时长: " . number_format($result['duration'], 2) . " 秒\n";
    echo "- 处理时间: " . number_format($duration, 2) . " 秒\n";
    echo "- 实时系数: " . number_format($result['duration'] / $duration, 2) . "x\n";
    
    // 测试不同参数
    echo "\n测试不同语音参数...\n";
    
    // 测试较慢语速
    $slowConfig = $synthesisConfig;
    $slowConfig['speaking_rate'] = 0.8;
    $slowFile = $outputDir . '/synthesis_slow_' . date('Ymd_His') . '.wav';
    
    echo "生成较慢语速语音...\n";
    $synthesisEngine->updateConfig($slowConfig);
    $slowResult = $synthesisEngine->synthesizeToFile($text, $slowFile);
    
    // 测试较高音调
    $highPitchConfig = $synthesisConfig;
    $highPitchConfig['pitch_adjustment'] = 0.3;
    $highPitchFile = $outputDir . '/synthesis_high_pitch_' . date('Ymd_His') . '.wav';
    
    echo "生成较高音调语音...\n";
    $synthesisEngine->updateConfig($highPitchConfig);
    $highPitchResult = $synthesisEngine->synthesizeToFile($text, $highPitchFile);
    
    // 恢复默认配置
    $synthesisEngine->updateConfig($synthesisConfig);
    
    // 测试流式合成
    echo "\n测试流式合成...\n";
    $longText = str_repeat($text, 5); // 重复文本5次模拟长文本
    
    echo "进行流式合成 (文本长度: " . mb_strlen($longText) . " 字符)...\n";
    
    $segmentCount = 0;
    $startTime = microtime(true);
    
    $result = $synthesisEngine->streamSynthesize($longText, function($segment, $index, $total) use (&$segmentCount) {
        echo "收到片段 " . ($index + 1) . " / $total (长度: " . mb_strlen($segment['text']) . " 字符)\n";
        $segmentCount++;
    });
    
    $duration = microtime(true) - $startTime;
    
    echo "\n流式合成结果:\n";
    echo "- 总片段数: $segmentCount\n";
    echo "- 总时长: " . number_format($result['total_duration'], 2) . " 秒\n";
    echo "- 处理时间: " . number_format($duration, 2) . " 秒\n";
    echo "- 实时系数: " . number_format($result['total_duration'] / $duration, 2) . "x\n";
    
    // 清理资源
    $synthesisEngine->cleanup();
    
    echo "\n演示完成! 语音文件已保存到: $outputDir\n";
    
} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
    echo "文件: " . $e->getFile() . " 行数: " . $e->getLine() . "\n";
    
    // 记录异常
    $logger->error("语音合成演示异常", [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}

/**
 * 注意: 在实际系统中，需要实现所有依赖的类:
 * - TextProcessor
 * - SpeechSynthesisEngine
 * - SynthesisAcousticModel 
 * - VocoderModel
 * 
 * 当前这个演示假设所有这些类都已经实现并可用。
 * 如果您运行这个示例时遇到缺少类的错误，请确保先实现这些依赖类。
 */ 