<?php
/**
 * 文件名：SpeechRecognitionEngine.php
 * 功能描述：语音识别引擎 - 处理语音识别的核心功能
 * 创建时间：2025-01-XX
 * 最后修改：2025-01-XX
 * 版本：1.0.0
 * 
 * @package AlingAi\Engines\Speech
 * @author AlingAi Team
 * @license MIT
 */

declare(strict_types=1);

namespace AlingAi\Engines\Speech;

use Exception;
use InvalidArgumentException;
use RuntimeException;
use AlingAi\Core\Logger\LoggerInterface;
use AlingAi\Utils\CacheManager;
use AlingAi\Utils\PerformanceMonitor;

/**
 * 语音识别引擎
 * 
 * 提供语音识别的核心功能，包括语音特征提取、声学模型和语言模型的应用
 */
class SpeechRecognitionEngine
{
    /**
     * @var array 配置参数
     */
    private array $config;

    /**
     * @var LoggerInterface|null 日志记录器
     */
    private ?LoggerInterface $logger;

    /**
     * @var CacheManager|null 缓存管理器
     */
    private ?CacheManager $cache;

    /**
     * @var FeatureExtractor 特征提取器
     */
    private FeatureExtractor $featureExtractor;

    /**
     * @var AcousticModel 声学模型
     */
    private AcousticModel $acousticModel;

    /**
     * @var LanguageModel 语言模型
     */
    private LanguageModel $languageModel;

    /**
     * @var array 支持的音频格式
     */
    private array $supportedFormats = ['wav', 'mp3', 'ogg', 'flac', 'aac', 'm4a'];

    /**
     * @var array 当前识别会话状态
     */
    private array $sessionState = [];

    private PerformanceMonitor $monitor;
    
    public function __construct(
        array $config = [],
        ?LoggerInterface $logger = null,
        ?CacheManager $cache = null,
        PerformanceMonitor $monitor
    ) {
        $this->config = $this->mergeConfig($config);
        $this->logger = $logger;
        $this->cache = $cache;
        $this->monitor = $monitor;

        try {
            $this->initializeComponents();

            if ($this->logger) {
                $this->logger->info('语音识别引擎初始化成功', [
                    'config' => [
                        'model_type' => $this->config['model_type'],
                        'language' => $this->config['language'],
                        'sample_rate' => $this->config['sample_rate']
                    ]
                ]);
            }
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->error('语音识别引擎初始化失败', ['error' => $e->getMessage()]);
            }
            throw $e;
        }
    }
    
    /**
     * 合并配置
     *
     * @param array $config 用户配置
     * @return array 合并后的配置
     */
    private function mergeConfig(array $config): array
    {
        // 默认配置
        $defaultConfig = [
            'model_type' => 'hybrid', // hybrid, e2e
            'language' => 'zh-CN',
            'sample_rate' => 16000,
            'channels' => 1,
            'bit_depth' => 16,
            'max_audio_length' => 300, // 最大音频长度(秒)
            'feature_type' => 'mfcc',
            'use_vad' => true,
            'vad_mode' => 'aggressive',
            'beam_size' => 10,
            'enable_punctuation' => true,
            'enable_speaker_diarization' => false,
            'enable_interim_results' => false,
            'use_gpu' => false,
            'num_threads' => 4,
            'timeout' => 30000, // 毫秒
            'cache_enabled' => true,
            'cache_ttl' => 3600
        ];

        return array_merge($defaultConfig, $config);
    }
    
    /**
     * 初始化组件
     *
     * @throws Exception 初始化失败时抛出异常
     */
    private function initializeComponents(): void
    {
        // 初始化特征提取器
        $featureConfig = [
            'sample_rate' => $this->config['sample_rate'],
            'feature_type' => $this->config['feature_type'],
            'use_vad' => $this->config['use_vad'],
            'vad_mode' => $this->config['vad_mode']
        ];
        $this->featureExtractor = new FeatureExtractor($featureConfig, $this->logger, $this->cache);

        // 初始化声学模型
        $acousticConfig = [
            'model_type' => $this->config['model_type'],
            'language' => $this->config['language'],
            'use_gpu' => $this->config['use_gpu'],
            'beam_size' => $this->config['beam_size'],
            'num_threads' => $this->config['num_threads']
        ];
        $this->acousticModel = new AcousticModel($acousticConfig, $this->logger, $this->cache);

        // 初始化语言模型
        $languageConfig = [
            'model_type' => $this->config['model_type'] === 'hybrid' ? 'ngram' : 'transformer',
            'language' => $this->config['language'],
            'enable_punctuation' => $this->config['enable_punctuation']
        ];
        $this->languageModel = new LanguageModel($languageConfig, $this->logger, $this->cache);
    }
    
    /**
     * 识别音频文件
     *
     * @param string $audioFile 音频文件路径
     * @param array $options 识别选项
     * @return array 识别结果
     * @throws InvalidArgumentException 参数无效时抛出异常
     * @throws RuntimeException 识别失败时抛出异常
     */
    public function recognizeFile(string $audioFile, array $options = []): array
    {
        if (!file_exists($audioFile)) {
            throw new InvalidArgumentException("音频文件不存在: {$audioFile}");
        }

        $fileInfo = pathinfo($audioFile);
        if (!isset($fileInfo['extension']) || !in_array(strtolower($fileInfo['extension']), $this->supportedFormats)) {
            throw new InvalidArgumentException("不支持的音频格式: {$fileInfo['extension']}");
        }

        $options = array_merge($this->config, $options);
            
            // 检查缓存
        if ($options['cache_enabled']) {
            $cacheKey = 'asr_' . md5_file($audioFile) . '_' . md5(json_encode($options));
            if ($this->cache && $this->cache->has($cacheKey)) {
                if ($this->logger) {
                    $this->logger->debug('从缓存获取识别结果', ['audio_file' => $audioFile]);
                }
                return $this->cache->get($cacheKey);
            }
        }

        try {
            // 记录开始时间
            $startTime = microtime(true);

            if ($this->logger) {
                $this->logger->info('开始处理音频文件', ['audio_file' => $audioFile]);
            }
            
            // 提取特征
            $features = $this->featureExtractor->extractFromFile($audioFile);
            
            // 使用声学模型生成假设
            $acousticResults = $this->acousticModel->decode($features);
            
            // 使用语言模型重评分
            $recognitionResults = $this->languageModel->rescoreHypotheses($acousticResults);
            
            // 后处理
            $finalResults = $this->postProcess($recognitionResults, $options);

            // 计算处理时间
            $processingTime = microtime(true) - $startTime;
            
            $result = [
                'status' => 'success',
                'text' => $this->joinTranscripts($finalResults),
                'segments' => $finalResults,
                'processing_time' => $processingTime,
                'audio_length' => $features['audio_length'] ?? 0,
                'language' => $options['language'],
                'model_type' => $options['model_type'],
                'confidence' => $this->calculateOverallConfidence($finalResults)
            ];
            
            // 缓存结果
            if ($options['cache_enabled'] && $this->cache) {
                $this->cache->set($cacheKey, $result, $options['cache_ttl']);
            }

            if ($this->logger) {
                $this->logger->info('音频文件处理完成', [
                    'audio_file' => $audioFile,
                    'processing_time' => $processingTime,
                    'confidence' => $result['confidence']
                ]);
            }
            
            return $result;
            
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->error('音频文件处理失败', [
                    'audio_file' => $audioFile,
                    'error' => $e->getMessage()
                ]);
            }
            throw new RuntimeException('识别失败: ' . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * 开始流式识别会话
     *
     * @param array $options 识别选项
     * @return string 会话ID
     */
    public function startSession(array $options = []): string
    {
        $sessionId = uniqid('asr_session_');

        // 合并配置
        $sessionConfig = array_merge($this->config, $options);

        // 创建会话状态
        $this->sessionState[$sessionId] = [
            'config' => $sessionConfig,
            'status' => 'initialized',
            'features' => [],
            'partial_results' => [],
            'final_results' => [],
            'start_time' => microtime(true),
            'last_activity' => microtime(true),
            'total_audio_length' => 0
        ];

        if ($this->logger) {
            $this->logger->info('创建流式识别会话', ['session_id' => $sessionId]);
        }

        return $sessionId;
    }
    
    /**
     * 处理音频流片段
     *
     * @param string $sessionId 会话ID
     * @param string $audioChunk 音频数据
     * @param bool $isLast 是否为最后一个片段
     * @return array 识别结果
     * @throws InvalidArgumentException 参数无效时抛出异常
     * @throws RuntimeException 处理失败时抛出异常
     */
    public function processChunk(string $sessionId, string $audioChunk, bool $isLast = false): array
    {
        // 验证会话ID
        if (!isset($this->sessionState[$sessionId])) {
            throw new InvalidArgumentException("无效的会话ID: {$sessionId}");
        }

        // 获取会话状态
        $session = &$this->sessionState[$sessionId];
        $session['last_activity'] = microtime(true);

        try {
            // 提取特征
            $chunkFeatures = $this->featureExtractor->extractFromBuffer($audioChunk);
            
            // 添加到会话特征
            $session['features'] = array_merge($session['features'], $chunkFeatures);
            $session['total_audio_length'] += $chunkFeatures['audio_length'] ?? 0;

            // 如果启用了中间结果或是最后一个片段才进行解码
            $results = [];
            if ($session['config']['enable_interim_results'] || $isLast) {
                // 使用声学模型生成假设
                $acousticResults = $this->acousticModel->decode($session['features']);

                // 使用语言模型重评分
                $recognitionResults = $this->languageModel->rescoreHypotheses($acousticResults);

                // 后处理
                $results = $this->postProcess($recognitionResults, $session['config']);

                if ($isLast) {
                    $session['status'] = 'completed';
                    $session['final_results'] = $results;
                } else {
                    $session['status'] = 'processing';
                    $session['partial_results'] = $results;
                }
            }

            $response = [
                'session_id' => $sessionId,
                'status' => $session['status'],
                'is_final' => $isLast,
                'text' => $this->joinTranscripts($results),
                'segments' => $results,
                'audio_length' => $session['total_audio_length'],
                'processing_time' => microtime(true) - $session['start_time']
            ];

            if ($this->logger) {
                $this->logger->debug('处理音频流片段', [
                    'session_id' => $sessionId,
                    'chunk_size' => strlen($audioChunk),
                    'is_last' => $isLast,
                    'status' => $session['status']
                ]);
            }

            // 如果是最后一个片段，清理会话资源
            if ($isLast) {
                $this->endSession($sessionId);
            }

            return $response;

        } catch (Exception $e) {
            $session['status'] = 'error';
            
            if ($this->logger) {
                $this->logger->error('音频流处理失败', [
                    'session_id' => $sessionId,
                    'error' => $e->getMessage()
                ]);
            }
            
            throw new RuntimeException('音频流处理失败: ' . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * 结束流式识别会话
     *
     * @param string $sessionId 会话ID
     * @return void
     */
    public function endSession(string $sessionId): void
    {
        if (isset($this->sessionState[$sessionId])) {
            $session = $this->sessionState[$sessionId];
            
            // 记录会话信息
            if ($this->logger) {
                $this->logger->info('结束流式识别会话', [
                    'session_id' => $sessionId,
                    'total_audio_length' => $session['total_audio_length'],
                    'processing_time' => microtime(true) - $session['start_time'],
                    'final_status' => $session['status']
                ]);
            }
            
            // 清理会话资源
            unset($this->sessionState[$sessionId]);
        }
    }
    
    /**
     * 后处理识别结果
     *
     * @param array $recognitionResults 识别结果
     * @param array $options 处理选项
     * @return array 处理后的结果
     */
    private function postProcess(array $recognitionResults, array $options): array
    {
        $results = $recognitionResults;
        
        // 添加标点符号
        if ($options['enable_punctuation'] && !empty($results)) {
            $results = $this->addPunctuation($results);
        }
        
        // 添加说话人分离信息
        if ($options['enable_speaker_diarization'] && !empty($results)) {
            $results = $this->addSpeakerDiarization($results);
        }
        
        return $results;
    }
    
    /**
     * 添加标点符号
     *
     * @param array $results 识别结果
     * @return array 添加标点符号后的结果
     */
    private function addPunctuation(array $results): array
    {
        // 简单模拟添加标点符号的过程
        // 实际应用中应该使用更复杂的算法或模型
        foreach ($results as &$result) {
            if (isset($result['text'])) {
                // 简单地在句尾添加句号
                $result['text'] = rtrim($result['text']) . '。';
            }
        }
        
        return $results;
    }
    
    /**
     * 添加说话人分离信息
     *
     * @param array $results 识别结果
     * @return array 添加说话人信息后的结果
     */
    private function addSpeakerDiarization(array $results): array
    {
        // 简单模拟说话人分离
        // 实际应用中应该使用专门的说话人分离模型
        $speakerCounter = 0;
        $currentSpeaker = "speaker_" . $speakerCounter;
        
        foreach ($results as &$result) {
            // 随机分配说话人
            if (rand(0, 10) > 7) { // 30%的概率换说话人
                $speakerCounter++;
                $currentSpeaker = "speaker_" . $speakerCounter;
            }
            
            $result['speaker'] = $currentSpeaker;
        }
        
        return $results;
    }
    
    /**
     * 连接转录文本
     *
     * @param array $results 识别结果
     * @return string 连接后的文本
     */
    private function joinTranscripts(array $results): string
    {
        $texts = [];
        foreach ($results as $result) {
            if (isset($result['text'])) {
                $texts[] = $result['text'];
            }
        }
        
        return implode(' ', $texts);
    }
    
    /**
     * 计算整体置信度
     *
     * @param array $results 识别结果
     * @return float 整体置信度
     */
    private function calculateOverallConfidence(array $results): float
    {
        if (empty($results)) {
            return 0.0;
        }
        
        $totalConfidence = 0.0;
        $count = 0;
        
        foreach ($results as $result) {
            if (isset($result['confidence'])) {
                $totalConfidence += $result['confidence'];
                $count++;
            }
        }
        
        return $count > 0 ? $totalConfidence / $count : 0.0;
    }
    
    /**
     * 获取引擎配置
     *
     * @return array 配置参数
     */
    public function getConfig(): array
    {
        return $this->config;
    }
    
    /**
     * 更新引擎配置
     *
     * @param array $config 新的配置参数
     * @return void
     */
    public function updateConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config);
        
        // 更新子组件配置
        if (isset($config['feature_type']) || isset($config['use_vad']) || isset($config['vad_mode'])) {
            $this->featureExtractor->setConfig([
                'feature_type' => $this->config['feature_type'],
                'use_vad' => $this->config['use_vad'],
                'vad_mode' => $this->config['vad_mode']
            ]);
        }
        
        if (isset($config['model_type']) || isset($config['beam_size'])) {
            $this->acousticModel->setConfig([
                'model_type' => $this->config['model_type'],
                'beam_size' => $this->config['beam_size']
            ]);
        }
        
        if (isset($config['enable_punctuation'])) {
            $this->languageModel->setConfig([
                'enable_punctuation' => $this->config['enable_punctuation']
            ]);
        }
    }
    
    /**
     * 获取支持的语言列表
     *
     * @return array 支持的语言列表
     */
    public function getSupportedLanguages(): array
    {
        return [
            'zh-CN' => '简体中文',
            'en-US' => '英语(美国)',
            'ja-JP' => '日语',
            'ko-KR' => '韩语',
            'fr-FR' => '法语',
            'de-DE' => '德语',
            'es-ES' => '西班牙语',
            'ru-RU' => '俄语',
            'ar-SA' => '阿拉伯语'
        ];
    }
    
    /**
     * 清理引擎资源
     */
    public function cleanup(): void
    {
        // 清理所有会话
        foreach (array_keys($this->sessionState) as $sessionId) {
            $this->endSession($sessionId);
        }
        
        // 清理模型资源
        $this->acousticModel->releaseModel();
        $this->languageModel->releaseModel();
        
        if ($this->logger) {
            $this->logger->info('语音识别引擎资源已释放');
        }
    }
}
