<?php
declare(strict_types=1];

/**
 * 文件名：SpeechRecognizer.php
 * 功能描述：语音识别器 - 提供语音识别的便捷API
 * 创建时间�?025-01-XX
 * 最后修改：2025-01-XX
 * 版本�?.0.0
 * 
 * @package AlingAi\AI\Engines\Speech
 * @author AlingAi Team
 * @license MIT
 */

namespace AlingAi\AI\Engines\Speech;

use Exception;
use InvalidArgumentException;
use AlingAi\Core\Logger\LoggerInterface;

/**
 * 语音识别�?
 * 
 * 提供语音识别的便捷API，封装SpeechRecognitionEngine的复杂�?
 */
class SpeechRecognizer
{
    /**
     * 语音识别引擎实例
     */
    private SpeechRecognitionEngine $engine;
    
    /**
     * 日志接口
     */
    private LoggerInterface $logger;
    
    /**
     * 配置参数
     */
    private array $config;
    
    /**
     * 预定义的配置预设
     */
    private array $presets = [];
    
    /**
     * 构造函�?
     * 
     * @param SpeechRecognitionEngine $engine 语音识别引擎实例
     * @param LoggerInterface $logger 日志接口
     * @param array $config 配置参数
     */
    public function __construct(
        SpeechRecognitionEngine $engine,
        LoggerInterface $logger,
        array $config = []
    ) {
        $this->engine = $engine;
        $this->logger = $logger;
        $this->config = array_merge($this->getDefaultConfig(), $config];
        $this->initializePresets(];
    }
    
    /**
     * 获取默认配置
     * 
     * @return array 默认配置
     */
    private function getDefaultConfig(): array
    {
        return [
            'output_format' => 'text',  // 输出格式：text, json, array
            'include_confidence' => false,  // 是否包含置信�?
            'include_timestamps' => false,  // 是否包含时间�?
            'include_alternatives' => false,  // 是否包含备选结�?
            'max_alternatives' => 3,  // 最大备选结果数�?
            'default_language' => 'zh-CN',  // 默认语言
            'profanity_filter' => false,  // 是否过滤敏感�?
            'auto_language_detection' => true,  // 是否自动检测语言
            'auto_punctuation' => true,  // 是否自动添加标点
            'word_level_timestamps' => false,  // 是否提供单词级时间戳
            'timeout' => 30,  // 超时时间（秒�?
            'max_audio_size' => 50 * 1024 * 1024,  // 最大音频大小（50MB�?
            'supported_formats' => ['wav', 'mp3', 'ogg', 'flac', 'm4a']  // 支持的音频格�?
        ];
    }
    
    /**
     * 初始化预设配�?
     */
    private function initializePresets(): void
    {
        // 标准识别预设
        $this->presets['standard'] = [
            'output_format' => 'text',
            'include_confidence' => false,
            'include_timestamps' => false,
            'auto_punctuation' => true
        ];
        
        // 详细识别预设
        $this->presets['detailed'] = [
            'output_format' => 'array',
            'include_confidence' => true,
            'include_timestamps' => true,
            'include_alternatives' => true,
            'word_level_timestamps' => true
        ];
        
        // 会议识别预设
        $this->presets['meeting'] = [
            'output_format' => 'array',
            'include_timestamps' => true,
            'word_level_timestamps' => true,
            'auto_punctuation' => true,
            'speaker_diarization' => true
        ];
        
        // 快速识别预设（优先速度�?
        $this->presets['fast'] = [
            'output_format' => 'text',
            'include_confidence' => false,
            'include_timestamps' => false,
            'include_alternatives' => false,
            'auto_punctuation' => false
        ];
        
        // 高精度识别预设（优先准确率）
        $this->presets['accurate'] = [
            'output_format' => 'array',
            'include_confidence' => true,
            'include_alternatives' => true,
            'max_alternatives' => 5,
            'auto_punctuation' => true
        ];
    }
    
    /**
     * 从音频文件识别文�?
     * 
     * @param string $audioPath 音频文件路径
     * @param array $options 识别选项
     * @return mixed 识别结果，根据output_format返回不同类型
     * @throws InvalidArgumentException 音频文件无效时抛出异�?
     * @throws Exception 识别失败时抛出异�?
     */
    public function recognizeFile(string $audioPath, array $options = [])
    {
        try {
            // 合并选项
            $options = array_merge($this->config, $options];
            
            // 验证音频文件
            $this->validateAudioFile($audioPath];
            
            // 调用引擎进行识别
            $result = $this->engine->recognize($audioPath, $options['default_language'] ?? null];
            
            // 处理结果
            return $this->formatResult($result, $options];
        } catch (Exception $e) {
            $this->logger->error('语音识别失败�? . $e->getMessage(), [
                'audio_path' => $audioPath,
                'options' => $options
            ]];
            throw new Exception('语音识别失败�? . $e->getMessage()];
        }
    }
    
    /**
     * 从音频字节数据识别文�?
     * 
     * @param string $audioData 音频字节数据
     * @param string $format 音频格式
     * @param array $options 识别选项
     * @return mixed 识别结果，根据output_format返回不同类型
     * @throws InvalidArgumentException 音频数据无效时抛出异�?
     * @throws Exception 识别失败时抛出异�?
     */
    public function recognizeAudio(string $audioData, string $format, array $options = [])
    {
        try {
            // 合并选项
            $options = array_merge($this->config, $options];
            
            // 验证格式
            if (!in_[$format, $this->config['supported_formats'])) {
                throw new InvalidArgumentException('不支持的音频格式�? . $format];
            }
            
            // 将音频数据保存为临时文件
            $tempFile = tempnam(sys_get_temp_dir(), 'speech_') . '.' . $format;
            file_put_contents($tempFile, $audioData];
            
            try {
                // 调用文件识别方法
                $result = $this->recognizeFile($tempFile, $options];
                
                // 清理临时文件
                @unlink($tempFile];
                
                return $result;
            } catch (Exception $e) {
                // 清理临时文件并抛出异�?
                @unlink($tempFile];
                throw $e;
            }
        } catch (Exception $e) {
            $this->logger->error('语音识别失败�? . $e->getMessage(), [
                'format' => $format,
                'options' => $options
            ]];
            throw new Exception('语音识别失败�? . $e->getMessage()];
        }
    }
    
    /**
     * 流式识别音频�?
     * 
     * @param resource $stream 音频流资�?
     * @param callable $callback 回调函数，用于接收实时识别结�?
     * @param array $options 识别选项
     * @throws InvalidArgumentException 音频流无效时抛出异常
     * @throws Exception 识别失败时抛出异�?
     */
    public function recognizeStream($stream, callable $callback, array $options = []): void
    {
        try {
            // 合并选项
            $options = array_merge($this->config, $options];
            
            // 验证�?
            if (!is_resource($stream)) {
                throw new InvalidArgumentException('无效的音频流'];
            }
            
            // 包装回调函数
            $wrappedCallback = function ($data) use ($callback, $options) {
                $result = $this->formatResult($data, $options];
                $callback($result];
            };
            
            // 调用引擎进行流式识别
            $this->engine->streamingRecognize(
                $stream, 
                $options['default_language'] ?? null, 
                $wrappedCallback
            ];
        } catch (Exception $e) {
            $this->logger->error('流式语音识别失败�? . $e->getMessage(), [
                'options' => $options
            ]];
            throw new Exception('流式语音识别失败�? . $e->getMessage()];
        }
    }
    
    /**
     * 使用预设进行识别
     * 
     * @param string $audioPath 音频文件路径
     * @param string $presetName 预设名称
     * @param array $additionalOptions 额外选项，将覆盖预设选项
     * @return mixed 识别结果
     * @throws InvalidArgumentException 预设名称无效时抛出异�?
     * @throws Exception 识别失败时抛出异�?
     */
    public function recognizeWithPreset(string $audioPath, string $presetName, array $additionalOptions = [])
    {
        if (!isset($this->presets[$presetName])) {
            throw new InvalidArgumentException('无效的预设名称：' . $presetName];
        }
        
        // 合并预设选项和额外选项
        $options = array_merge($this->presets[$presetName],  $additionalOptions];
        
        return $this->recognizeFile($audioPath, $options];
    }
    
    /**
     * 验证音频文件
     * 
     * @param string $audioPath 音频文件路径
     * @throws InvalidArgumentException 音频文件无效时抛出异�?
     */
    private function validateAudioFile(string $audioPath): void
    {
        if (!file_exists($audioPath)) {
            throw new InvalidArgumentException('音频文件不存在：' . $audioPath];
        }
        
        $fileSize = filesize($audioPath];
        if ($fileSize <= 0) {
            throw new InvalidArgumentException('音频文件为空�? . $audioPath];
        }
        
        if ($fileSize > $this->config['max_audio_size']) {
            throw new InvalidArgumentException(sprintf(
                '音频文件过大�?s MB（最大允许：%s MB�?, 
                round($fileSize / (1024 * 1024], 2], 
                round($this->config['max_audio_size'] / (1024 * 1024], 2)
            )];
        }
        
        // 验证文件格式
        $extension = strtolower(pathinfo($audioPath, PATHINFO_EXTENSION)];
        if (!in_[$extension, $this->config['supported_formats'])) {
            throw new InvalidArgumentException('不支持的音频格式�? . $extension];
        }
    }
    
    /**
     * 格式化识别结�?
     * 
     * @param array $result 原始识别结果
     * @param array $options 格式化选项
     * @return mixed 格式化后的结�?
     */
    private function formatResult(array $result, array $options)
    {
        // 根据选项过滤结果
        if (!$options['include_confidence']) {
            unset($result['confidence']];
        }
        
        if (!$options['include_timestamps']) {
            unset($result['segments']];
        }
        
        if (!$options['include_alternatives'] && isset($result['alternatives'])) {
            unset($result['alternatives']];
        } elseif (isset($result['alternatives']) && count($result['alternatives']) > $options['max_alternatives']) {
            $result['alternatives'] = array_slice($result['alternatives'],  0, $options['max_alternatives']];
        }
        
        // 根据输出格式返回结果
        switch ($options['output_format']) {
            case 'text':
                return $result['transcript'] ?? '';
            
            case 'json':
                return json_encode($result, JSON_UNESCAPED_UNICODE];
            
            case 'array':
            default:
                return $result;
        }
    }
    
    /**
     * 获取支持的音频格�?
     * 
     * @return array 支持的音频格式列�?
     */
    public function getSupportedFormats(): array
    {
        return $this->engine->getSupportedFormats(];
    }
    
    /**
     * 获取支持的语言
     * 
     * @return array 支持的语言列表
     */
    public function getSupportedLanguages(): array
    {
        return $this->engine->getSupportedLanguages(];
    }
    
    /**
     * 获取可用的预�?
     * 
     * @return array 预设列表
     */
    public function getAvailablePresets(): array
    {
        return array_keys($this->presets];
    }
    
    /**
     * 获取预设配置
     * 
     * @param string $presetName 预设名称
     * @return array|null 预设配置，不存在时返回null
     */
    public function getPresetConfig(string $presetName): ?array
    {
        return $this->presets[$presetName] ?? null;
    }
    
    /**
     * 添加自定义预�?
     * 
     * @param string $presetName 预设名称
     * @param array $presetConfig 预设配置
     * @return bool 添加是否成功
     */
    public function addPreset(string $presetName, array $presetConfig): bool
    {
        if (isset($this->presets[$presetName])) {
            return false;
        }
        
        $this->presets[$presetName] = $presetConfig;
        return true;
    }
    
    /**
     * 更新当前配置
     * 
     * @param array $newConfig 新配�?
     */
    public function updateConfig(array $newConfig): void
    {
        $this->config = array_merge($this->config, $newConfig];
    }
    
    /**
     * 获取当前配置
     * 
     * @return array 当前配置
     */
    public function getConfig(): array
    {
        return $this->config;
    }
}

