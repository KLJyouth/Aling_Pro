<?php
declare(strict_types=1);

/**
 * 语音处理模块演示文件
 * 
 * 本文件演示了如何使用AlingAi语音处理模块的各种功能
 */

require_once __DIR__ . '/../autoload.php';

use AlingAi\Core\Logger\FileLogger;
use AlingAi\Utils\CacheManager;
use AlingAi\Engines\Speech\SpeechRecognizer;
use AlingAi\Engines\Speech\SpeechSynthesizer;
use AlingAi\Engines\Speech\VoiceIdentifier;

// 配置日志
$logger = new FileLogger(__DIR__ . '/../logs/speech-demo.log');
$logger->info('语音处理模块演示开始');

// 配置缓存
$cacheConfig = [
    'path' => __DIR__ . '/../cache',
    'prefix' => 'speech_',
    'ttl' => 3600
];
$cache = new CacheManager(CacheManager::DRIVER_MEMORY, $cacheConfig, $logger);

/**
 * 1. 语音识别演示
 */
$recognizer = new SpeechRecognizer([
    'model' => 'standard',
    'language' => 'zh-CN',
    'sampleRate' => 16000,
], $logger, $cache);

// 识别音频文件
$audioFile = __DIR__ . '/samples/speech_sample.wav';
try {
    $recognitionResult = $recognizer->recognizeFile($audioFile);
    echo "语音识别结果：" . PHP_EOL;
    echo "文本：" . $recognitionResult['text'] . PHP_EOL;
    echo "置信度：" . $recognitionResult['confidence'] . PHP_EOL;
    echo PHP_EOL;
} catch (Exception $e) {
    echo "语音识别出错：" . $e->getMessage() . PHP_EOL;
}

/**
 * 2. 语音合成演示
 */
$synthesizer = new SpeechSynthesizer([
    'voice' => 'female_1',
    'language' => 'zh-CN',
    'rate' => 1.0,
    'pitch' => 1.0,
], $logger, $cache);

// 合成语音
$text = "欢迎使用AlingAi语音处理模块，这是一段合成语音的演示";
try {
    $outputFile = __DIR__ . '/output/synthesized_speech.wav';
    $synthesisResult = $synthesizer->synthesize($text, $outputFile);
    echo "语音合成完成：" . PHP_EOL;
    echo "输出文件：" . $outputFile . PHP_EOL;
    echo "时长：" . $synthesisResult['duration'] . " 秒" . PHP_EOL;
    echo "字符数：" . $synthesisResult['charCount'] . PHP_EOL;
    echo PHP_EOL;
} catch (Exception $e) {
    echo "语音合成出错：" . $e->getMessage() . PHP_EOL;
}

/**
 * 3. 声纹识别演示
 */
$voiceIdentifier = new VoiceIdentifier([
    'modelType' => 'speaker_identification',
    'threshold' => 0.75,
], $logger, $cache);

// 注册声纹
$speakerId = 'user_123';
$registrationFile = __DIR__ . '/samples/voice_registration.wav';
try {
    $voiceIdentifier->registerVoice($speakerId, $registrationFile);
    echo "声纹注册完成，话者ID：" . $speakerId . PHP_EOL;
    
    // 验证声纹
    $verificationFile = __DIR__ . '/samples/voice_verification.wav';
    $verificationResult = $voiceIdentifier->verifyVoice($speakerId, $verificationFile);
    echo "声纹验证结果：" . PHP_EOL;
    echo "匹配分数：" . $verificationResult['score'] . PHP_EOL;
    echo "是否通过验证：" . ($verificationResult['verified'] ? '是' : '否') . PHP_EOL;
    echo PHP_EOL;
    
    // 识别说话人
    $identificationFile = __DIR__ . '/samples/voice_identification.wav';
    $identificationResult = $voiceIdentifier->identifySpeaker($identificationFile);
    echo "说话人识别结果：" . PHP_EOL;
    echo "识别出的说话人ID：" . $identificationResult['speakerId'] . PHP_EOL;
    echo "置信度：" . $identificationResult['confidence'] . PHP_EOL;
} catch (Exception $e) {
    echo "声纹识别出错：" . $e->getMessage() . PHP_EOL;
}

$logger->info('语音处理模块演示结束');
echo "演示完成！" . PHP_EOL;
