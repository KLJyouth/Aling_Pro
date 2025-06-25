<?php
declare(strict_types=1];

/**
 * 文件名：SpeechSynthesizer.php
 * 功能描述：语音合成器 - 提供文本到语音的便捷API
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
 * 语音合成�?
 * 
 * 提供文本到语音的便捷API，封装SpeechSynthesisEngine的复杂�?
 */
class SpeechSynthesizer
{
    /**
     * 语音合成引擎实例
     */
    private SpeechSynthesisEngine $engine;
    
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
     * @param SpeechSynthesisEngine $engine 语音合成引擎实例
     * @param LoggerInterface $logger 日志接口
     * @param array $config 配置参数
     */
    public function __construct(
        SpeechSynthesisEngine $engine,
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
            'output_format' => 'wav',  // 输出格式：wav, mp3, ogg, flac
            'sample_rate' => 22050,  // 采样率（Hz�?
            'bit_depth' => 16,  // 位深度（bit�?
            'channels' => 1,  // 声道�?
            'default_language' => 'zh-CN',  // 默认语言
            'default_voice' => 'female1',  // 默认声音
            'default_speed' => 1.0,  // 默认速度
            'default_pitch' => 1.0,  // 默认音调
            'default_volume' => 1.0,  // 默认音量
            'default_emotion' => 'neutral',  // 默认情感
            'cache_enabled' => true,  // 是否启用缓存
            'cache_ttl' => 3600,  // 缓存有效期（秒）
            'output_dir' => './storage/speech',  // 输出目录
            'max_text_length' => 5000,  // 最大文本长�?
            'enable_ssml' => true,  // 是否启用SSML
            'auto_punctuation' => true,  // 是否自动添加标点
            'normalize_text' => true,  // 是否规范化文�?
            'word_timing' => false,  // 是否生成单词级时间戳
            'streaming_chunk_size' => 1024  // 流式合成的块大小（字节）
        ];
    }
    
    /**
     * 初始化预设配�?
     */
    private function initializePresets(): void
    {
        // 标准合成预设
        $this->presets['standard'] = [
            'output_format' => 'wav',
            'sample_rate' => 22050,
            'speed' => 1.0,
            'pitch' => 1.0,
            'volume' => 1.0,
            'emotion' => 'neutral'
        ];
        
        // 高质量音频预�?
        $this->presets['high_quality'] = [
            'output_format' => 'flac',
            'sample_rate' => 44100,
            'bit_depth' => 24,
            'channels' => 2
        ];
        
        // 小文件预�?
        $this->presets['small_file'] = [
            'output_format' => 'mp3',
            'sample_rate' => 16000,
            'bit_depth' => 16,
            'channels' => 1
        ];
        
        // 快速语音预�?
        $this->presets['fast'] = [
            'output_format' => 'wav',
            'sample_rate' => 16000,
            'speed' => 1.2,
            'pitch' => 1.0
        ];
        
        // 慢速语音预�?
        $this->presets['slow'] = [
            'output_format' => 'wav',
            'sample_rate' => 22050,
            'speed' => 0.8,
            'pitch' => 0.95
        ];
        
        // 儿童语音预设
        $this->presets['child'] = [
            'output_format' => 'wav',
            'sample_rate' => 22050,
            'speed' => 1.1,
            'pitch' => 1.3,
            'volume' => 0.9
        ];
        
        // 长者语音预�?
        $this->presets['elderly'] = [
            'output_format' => 'wav',
            'sample_rate' => 22050,
            'speed' => 0.85,
            'pitch' => 0.85,
            'volume' => 1.0
        ];
        
        // 电话语音预设
        $this->presets['telephone'] = [
            'output_format' => 'wav',
            'sample_rate' => 8000,
            'bit_depth' => 16,
            'channels' => 1
        ];
        
        // 情感预设 - 快乐
        $this->presets['happy'] = [
            'output_format' => 'wav',
            'sample_rate' => 22050,
            'speed' => 1.05,
            'pitch' => 1.1,
            'volume' => 1.0,
            'emotion' => 'happy'
        ];
        
        // 情感预设 - 伤心
        $this->presets['sad'] = [
            'output_format' => 'wav',
            'sample_rate' => 22050,
            'speed' => 0.9,
            'pitch' => 0.9,
            'volume' => 0.8,
            'emotion' => 'sad'
        ];
    }
    
    /**
     * 将文本合成为语音
     * 
     * @param string $text 要合成的文本
     * @param array $options 合成选项
     * @return array 合成结果，包含音频数据和元信�?
     * @throws InvalidArgumentException 参数无效时抛出异�?
     * @throws Exception 合成失败时抛出异�?
     */
    public function synthesize(string $text, array $options = []): array
    {
        try {
            // 合并选项
            $options = array_merge($this->config, $options];
            
            // 验证文本
            $this->validateText($text, $options];
            
            // 规范化文�?
            if ($options['normalize_text']) {
                $text = $this->normalizeText($text, $options['default_language']];
            }
            
            // 准备合成选项
            $synthesisOptions = $this->prepareSynthesisOptions($options];
            
            // 调用引擎进行合成
            $result = $this->engine->synthesize($text, $synthesisOptions];
            
            // 处理结果
            return $result;
        } catch (Exception $e) {
            $this->logger->error('语音合成失败�? . $e->getMessage(), [
                'text' => $this->truncateLogText($text],
                'options' => $options
            ]];
            throw new Exception('语音合成失败�? . $e->getMessage()];
        }
    }
    
    /**
     * 将文本合成为语音并保存到文件
     * 
     * @param string $text 要合成的文本
     * @param string $outputPath 输出文件路径
     * @param array $options 合成选项
     * @return bool 合成是否成功
     * @throws InvalidArgumentException 参数无效时抛出异�?
     * @throws Exception 合成失败时抛出异�?
     */
    public function synthesizeToFile(string $text, string $outputPath, array $options = []): bool
    {
        // 确保输出目录存在
        $dir = dirname($outputPath];
        if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
            throw new Exception('无法创建输出目录�? . $dir];
        }
        
        // 自动检测输出格�?
        $extension = strtolower(pathinfo($outputPath, PATHINFO_EXTENSION)];
        if (!empty($extension) && !isset($options['output_format'])) {
            $options['output_format'] = $extension;
        }
        
        // 合成语音
        try {
            $result = $this->synthesize($text, $options];
            
            // 保存到文�?
            file_put_contents($outputPath, $result['audio_data']];
            
            $this->logger->info('语音合成成功并保存到文件', [
                'output_path' => $outputPath,
                'duration' => $result['duration'], 
                'file_size' => strlen($result['audio_data'])
            ]];
            
            return true;
        } catch (Exception $e) {
            $this->logger->error('保存合成语音失败�? . $e->getMessage(), [
                'output_path' => $outputPath
            ]];
            throw new Exception('保存合成语音失败�? . $e->getMessage()];
        }
    }
    
    /**
     * 流式合成语音
     * 
     * @param string $text 要合成的文本
     * @param callable $callback 回调函数，用于接收实时合成的音频数据
     * @param array $options 合成选项
     * @throws InvalidArgumentException 参数无效时抛出异�?
     * @throws Exception 合成失败时抛出异�?
     */
    public function streamingSynthesize(string $text, callable $callback, array $options = []): void
    {
        try {
            // 合并选项
            $options = array_merge($this->config, $options];
            
            // 验证文本
            $this->validateText($text, $options];
            
            // 规范化文�?
            if ($options['normalize_text']) {
                $text = $this->normalizeText($text, $options['default_language']];
            }
            
            // 准备合成选项
            $synthesisOptions = $this->prepareSynthesisOptions($options];
            
            // 调用引擎进行流式合成
            $this->engine->streamingSynthesize($text, $callback, $synthesisOptions];
        } catch (Exception $e) {
            $this->logger->error('流式语音合成失败�? . $e->getMessage(), [
                'text' => $this->truncateLogText($text],
                'options' => $options
            ]];
            throw new Exception('流式语音合成失败�? . $e->getMessage()];
        }
    }
    
    /**
     * 批量合成语音
     * 
     * @param array $texts 要合成的文本数组
     * @param array $options 合成选项
     * @return array 合成结果数组
     * @throws InvalidArgumentException 参数无效时抛出异�?
     * @throws Exception 合成失败时抛出异�?
     */
    public function batchSynthesize(array $texts, array $options = []): array
    {
        try {
            // 合并选项
            $options = array_merge($this->config, $options];
            
            // 准备合成选项
            $synthesisOptions = $this->prepareSynthesisOptions($options];
            
            // 处理每个文本
            $processedTexts = [];
            foreach ($texts as $key => $text) {
                // 验证文本
                $this->validateText($text, $options];
                
                // 规范化文�?
                if ($options['normalize_text']) {
                    $processedTexts[$key] = $this->normalizeText($text, $options['default_language']];
                } else {
                    $processedTexts[$key] = $text;
                }
            }
            
            // 调用引擎进行批量合成
            return $this->engine->batchSynthesize($processedTexts, $synthesisOptions];
        } catch (Exception $e) {
            $this->logger->error('批量语音合成失败�? . $e->getMessage(), [
                'text_count' => count($texts],
                'options' => $options
            ]];
            throw new Exception('批量语音合成失败�? . $e->getMessage()];
        }
    }
    
    /**
     * 使用预设合成语音
     * 
     * @param string $text 要合成的文本
     * @param string $presetName 预设名称
     * @param array $additionalOptions 额外选项，将覆盖预设选项
     * @return array 合成结果
     * @throws InvalidArgumentException 预设名称无效时抛出异�?
     * @throws Exception 合成失败时抛出异�?
     */
    public function synthesizeWithPreset(string $text, string $presetName, array $additionalOptions = []): array
    {
        if (!isset($this->presets[$presetName])) {
            throw new InvalidArgumentException('无效的预设名称：' . $presetName];
        }
        
        // 合并预设选项和额外选项
        $options = array_merge($this->presets[$presetName],  $additionalOptions];
        
        return $this->synthesize($text, $options];
    }
    
    /**
     * 验证文本
     * 
     * @param string $text 要验证的文本
     * @param array $options 选项
     * @throws InvalidArgumentException 文本无效时抛出异�?
     */
    private function validateText(string $text, array $options): void
    {
        // 检查文本长�?
        $textLength = mb_strlen($text];
        if ($textLength <= 0) {
            throw new InvalidArgumentException('文本不能为空'];
        }
        
        if ($textLength > $options['max_text_length']) {
            throw new InvalidArgumentException(sprintf(
                '文本长度过长�?d 字符（最大允许：%d 字符�?,
                $textLength,
                $options['max_text_length']
            )];
        }
        
        // 检查SSML语法（如果启用了SSML�?
        if ($options['enable_ssml'] && $this->containsSSML($text)) {
            if (!$this->validateSSML($text)) {
                throw new InvalidArgumentException('无效的SSML语法'];
            }
        }
    }
    
    /**
     * 检查文本是否包含SSML标记
     * 
     * @param string $text 要检查的文本
     * @return bool 是否包含SSML标记
     */
    private function containsSSML(string $text): bool
    {
        return preg_match('/<speak[^>]*>|<\/speak>|<voice[^>]*>|<\/voice>|<prosody[^>]*>|<\/prosody>/', $text) === 1;
    }
    
    /**
     * 验证SSML语法
     * 
     * @param string $text 包含SSML的文�?
     * @return bool 语法是否有效
     */
    private function validateSSML(string $text): bool
    {
        // 简单验证，检查标签是否闭�?
        // 实际应用中，这里应该使用XML解析器进行完整的验证
        $pairs = [
            '/<speak[^>]*>/' => '/<\/speak>/',
            '/<voice[^>]*>/' => '/<\/voice>/',
            '/<prosody[^>]*>/' => '/<\/prosody>/',
            '/<emphasis[^>]*>/' => '/<\/emphasis>/',
            '/<say-as[^>]*>/' => '/<\/say-as>/',
            '/<break[^>]*\/>/' => null,
            '/<mark[^>]*\/>/' => null
        ];
        
        foreach ($pairs as $openTag => $closeTag) {
            $openCount = preg_match_all($openTag, $text];
            
            if ($closeTag === null) {
                // 自闭合标签不需要检查关闭标�?
                continue;
            }
            
            $closeCount = preg_match_all($closeTag, $text];
            
            if ($openCount !== $closeCount) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * 规范化文�?
     * 
     * @param string $text 要规范化的文�?
     * @param string $language 语言代码
     * @return string 规范化后的文�?
     */
    private function normalizeText(string $text, string $language): string
    {
        // 在实际应用中，这里应该使用TextProcessor进行完整的规范化
        // 简单模拟规范化过程
        
        // 删除多余的空白字�?
        $text = preg_replace('/\s+/', ' ', trim($text)];
        
        // 确保句子以标点符号结�?
        if ($this->config['auto_punctuation'] && !preg_match('/[.!?。！？]$/', $text)) {
            if (in_[$language, ['zh-CN', 'zh-TW', 'ja-JP', 'ko-KR'])) {
                $text .= '�?;
            } else {
                $text .= '.';
            }
        }
        
        return $text;
    }
    
    /**
     * 准备合成选项
     * 
     * @param array $options 原始选项
     * @return array 适用于引擎的选项
     */
    private function prepareSynthesisOptions(array $options): array
    {
        // 提取与引擎相关的选项
        return [
            'language' => $options['default_language'], 
            'voice' => $options['default_voice'], 
            'output_format' => $options['output_format'], 
            'sample_rate' => $options['sample_rate'], 
            'bit_depth' => $options['bit_depth'], 
            'channels' => $options['channels'], 
            'speed' => $options['default_speed'], 
            'pitch' => $options['default_pitch'], 
            'volume' => $options['default_volume'], 
            'emotion' => $options['default_emotion'], 
            'enable_word_timing' => $options['word_timing'], 
            'cache_enabled' => $options['cache_enabled'], 
            'cache_ttl' => $options['cache_ttl']
        ];
    }
    
    /**
     * 截断日志文本
     * 
     * @param string $text 要截断的文本
     * @param int $maxLength 最大长�?
     * @return string 截断后的文本
     */
    private function truncateLogText(string $text, int $maxLength = 100): string
    {
        if (mb_strlen($text) <= $maxLength) {
            return $text;
        }
        
        return mb_substr($text, 0, $maxLength) . '...';
    }
    
    /**
     * 获取支持的语音格�?
     * 
     * @return array 支持的格式列�?
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
     * 获取支持的声�?
     * 
     * @param string|null $language 语言代码，为null时返回所有声�?
     * @return array 支持的声音列�?
     */
    public function getSupportedVoices(?string $language = null): array
    {
        return $this->engine->getSupportedVoices($language];
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
    
    /**
     * 清理缓存
     * 
     * @return bool 清理是否成功
     */
    public function clearCache(): bool
    {
        return $this->engine->clearCache(];
    }
}

