<?php
/**
 * 文件名：TextProcessor.php
 * 功能描述：文本处理器 - 提供文本规范化和分段功能
 * 创建时间�?025-01-XX
 * 最后修改：2025-01-XX
 * 版本�?.0.0
 * 
 * @package AlingAi\Engines\Speech
 * @author AlingAi Team
 * @license MIT
 */

declare(strict_types=1];

namespace AlingAi\Engines\Speech;

use Exception;
use InvalidArgumentException;
use AlingAi\Core\Logger\LoggerInterface;

/**
 * 文本处理�?
 * 
 * 负责处理文本规范化、分段、符号转换等
 */
class TextProcessor
{
    /**
     * @var array 配置参数
     */
    private array $config;
    
    /**
     * @var LoggerInterface|null 日志记录�?
     */
    private ?LoggerInterface $logger;
    
    /**
     * @var array 语言配置
     */
    private array $languageConfig = [];
    
    /**
     * @var array 数字到文本的映射 (中文)
     */
    private array $numberToTextZh = [
        '0' => '�?,
        '1' => '一',
        '2' => '�?,
        '3' => '�?,
        '4' => '�?,
        '5' => '�?,
        '6' => '�?,
        '7' => '�?,
        '8' => '�?,
        '9' => '�?,
        '10' => '�?
    ];
    
    /**
     * @var array 符号映射
     */
    private array $symbolMap = [
        '!' => '叹号',
        '@' => '艾特',
        '#' => '井号',
        '$' => '美元',
        '%' => '百分�?,
        '^' => '脱字�?,
        '&' => '和号',
        '*' => '星号',
        '(' => '左括�?,
        ')' => '右括�?,
        '-' => '减号',
        '_' => '下划�?,
        '+' => '加号',
        '=' => '等号',
        '[' => '左方括号',
        ']' => '右方括号',
        '{' => '左花括号',
        '}' => '右花括号',
        '|' => '竖线',
        '\\' => '反斜�?,
        ':' => '冒号',
        ';' => '分号',
        '"' => '引号',
        '\'' => '单引�?,
        '<' => '小于�?,
        '>' => '大于�?,
        ',' => '逗号',
        '.' => '�?,
        '?' => '问号',
        '/' => '斜杠'
    ];

    /**
     * 构造函�?
     *
     * @param array $config 配置参数
     * @param LoggerInterface|null $logger 日志记录�?
     */
    public function __construct(array $config = [],  ?LoggerInterface $logger = null)
    {
        $this->config = $this->mergeConfig($config];
        $this->logger = $logger;
        
        $this->loadLanguageConfigs(];
        
        if ($this->logger) {
            $this->logger->info('文本处理器初始化成功', [
                'default_language' => $this->config['default_language']
            ]];
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
            'default_language' => 'zh-CN',
            'segment_max_length' => 100,
            'normalize_numbers' => true,
            'normalize_symbols' => true,
            'normalize_abbreviations' => true,
            'replace_emojis' => true,
            'preserve_formatting' => false
        ];
        
        return array_merge($defaultConfig, $config];
    }
    
    /**
     * 加载语言配置
     */
    private function loadLanguageConfigs(): void
    {
        // 中文配置
        $this->languageConfig['zh-CN'] = [
            'sentence_delimiters' => ['�?, '�?, '�?, '.', '!', '?'], 
            'pause_delimiters' => ['�?, '�?, '�?, ',', ';'], 
            'abbreviations' => [
                'GDP' => '国内生产总�?,
                'NBA' => '美国职业篮球联赛',
                'CEO' => '首席执行�?,
                'AI' => '人工智能'
            ]
        ];
        
        // 英文配置
        $this->languageConfig['en-US'] = [
            'sentence_delimiters' => ['.', '!', '?'], 
            'pause_delimiters' => [',', ';', ':'], 
            'abbreviations' => [
                'Mr.' => 'Mister',
                'Mrs.' => 'Misses',
                'Dr.' => 'Doctor',
                'PhD' => 'Doctor of Philosophy',
                'i.e.' => 'that is',
                'e.g.' => 'for example'
            ]
        ];
    }
    
    /**
     * 处理文本
     *
     * @param string $text 输入文本
     * @param string|null $language 语言代码
     * @return string 处理后的文本
     * @throws InvalidArgumentException 参数无效时抛出异�?
     */
    public function process(string $text, ?string $language = null): string
    {
        $language = $language ?? $this->config['default_language'];
        
        if (!isset($this->languageConfig[$language])) {
            throw new InvalidArgumentException("不支持的语言: {$language}"];
        }
        
        try {
            if ($this->logger) {
                $this->logger->debug('开始处理文�?, [
                    'text_length' => mb_strlen($text],
                    'language' => $language
                ]];
            }
            
            // 进行多步骤处�?
            $processed = $text;
            
            // 去除多余空白
            $processed = $this->normalizeWhitespace($processed];
            
            // 处理缩略�?
            if ($this->config['normalize_abbreviations']) {
                $processed = $this->expandAbbreviations($processed, $language];
            }
            
            if ($this->logger) {
                $this->logger->debug('文本处理完成', [
                    'original_length' => mb_strlen($text],
                    'processed_length' => mb_strlen($processed)
                ]];
            }
            
            return $processed;
            
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->error('文本处理失败', ['error' => $e->getMessage()]];
            }
            throw $e;
        }
    }
    
    /**
     * 文本分段
     * 
     * @param string $text 输入文本
     * @param string|null $language 语言代码
     * @return array 分段后的文本数组
     */
    public function segment(string $text, ?string $language = null): array
    {
        $language = $language ?? $this->config['default_language'];
        $langConfig = $this->languageConfig[$language] ?? $this->languageConfig['en-US'];
        
        // 先按句子分割
        $sentences = $this->splitIntoSentences($text, $language];
        
        // 再处理长句子，确保每个片段不超过最大长�?
        $segments = [];
        
        foreach ($sentences as $sentence) {
            if (mb_strlen($sentence) <= $this->config['segment_max_length']) {
                $segments[] = $sentence;
            } else {
                // 长句按暂停符分割
                $parts = $this->splitLongSentence($sentence, $language];
                foreach ($parts as $part) {
                    $segments[] = $part;
                }
            }
        }
        
        if ($this->logger) {
            $this->logger->debug('文本分段完成', [
                'original_length' => mb_strlen($text],
                'segments_count' => count($segments)
            ]];
        }
        
        return $segments;
    }
    
    /**
     * 将文本分割为句子
     *
     * @param string $text 输入文本
     * @param string $language 语言代码
     * @return array 句子数组
     */
    private function splitIntoSentences(string $text, string $language): array
    {
        $langConfig = $this->languageConfig[$language] ?? $this->languageConfig['en-US'];
        $delimiters = $langConfig['sentence_delimiters'];
        
        // 使用正则表达式分�?
        $pattern = '/([';
        foreach ($delimiters as $delimiter) {
            $pattern .= preg_quote($delimiter, '/'];
        }
        $pattern .= '])/u';
        
        $parts = preg_split($pattern, $text, -1, PREG_SPLIT_DELIM_CAPTURE];
        
        $sentences = [];
        $currentSentence = '';
        
        for ($i = 0; $i < count($parts]; $i++) {
            $currentSentence .= $parts[$i];
            
            // 如果当前部分是分隔符，并且不是句子的最后一部分
            if (in_[$parts[$i],  $delimiters) && isset($parts[$i+1])) {
                // 如果下一部分不是空白，添加当前句子并重置
                if (trim($parts[$i+1]) !== '') {
                    $sentences[] = $currentSentence;
                    $currentSentence = '';
                }
            }
        }
        
        // 添加最后一个句�?如果�?
        if (!empty($currentSentence)) {
            $sentences[] = $currentSentence;
        }
        
        return $sentences;
    }
    
    /**
     * 分割长句�?
     *
     * @param string $sentence 长句�?
     * @param string $language 语言代码
     * @return array 分割后的部分
     */
    private function splitLongSentence(string $sentence, string $language): array
    {
        $langConfig = $this->languageConfig[$language] ?? $this->languageConfig['en-US'];
        $delimiters = $langConfig['pause_delimiters'];
        
        // 使用暂停符分�?
        $pattern = '/([';
        foreach ($delimiters as $delimiter) {
            $pattern .= preg_quote($delimiter, '/'];
        }
        $pattern .= '])/u';
        
        $parts = preg_split($pattern, $sentence, -1, PREG_SPLIT_DELIM_CAPTURE];
        
        $segments = [];
        $currentSegment = '';
        
        for ($i = 0; $i < count($parts]; $i++) {
            $tempSegment = $currentSegment . $parts[$i];
            
            // 如果添加当前部分后长度超过限制，则添加当前段并重�?
            if (mb_strlen($tempSegment) > $this->config['segment_max_length'] && !empty($currentSegment)) {
                $segments[] = $currentSegment;
                $currentSegment = $parts[$i];
            } else {
                $currentSegment = $tempSegment;
            }
            
            // 如果当前是分隔符并且已经有一定长度，考虑在此处分�?
            if (in_[$parts[$i],  $delimiters) && mb_strlen($currentSegment) > $this->config['segment_max_length'] / 2) {
                $segments[] = $currentSegment;
                $currentSegment = '';
            }
        }
        
        // 添加最后一个段(如果�?
        if (!empty($currentSegment)) {
            $segments[] = $currentSegment;
        }
        
        return $segments;
    }
    
    /**
     * 规范化空白字�?
     *
     * @param string $text 输入文本
     * @return string 处理后的文本
     */
    private function normalizeWhitespace(string $text): string
    {
        // 替换多个空白为单个空�?
        $text = preg_replace('/\s+/u', ' ', $text];
        
        // 去除开头和结尾的空�?
        return trim($text];
    }
    
    /**
     * 展开缩略�?
     *
     * @param string $text 输入文本
     * @param string $language 语言代码
     * @return string 处理后的文本
     */
    private function expandAbbreviations(string $text, string $language): string
    {
        $langConfig = $this->languageConfig[$language] ?? null;
        
        if ($langConfig && isset($langConfig['abbreviations'])) {
            foreach ($langConfig['abbreviations'] as $abbr => $expansion) {
                // 使用单词边界确保只替换整个缩略词
                $pattern = '/\b' . preg_quote($abbr, '/') . '\b/u';
                $text = preg_replace($pattern, $expansion, $text];
            }
        }
        
        return $text;
    }
    
    /**
     * 设置配置
     *
     * @param array $config 新配�?
     * @return void
     */
    public function setConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config];
    }
    
    /**
     * 获取配置
     *
     * @return array 当前配置
     */
    public function getConfig(): array
    {
        return $this->config;
    }
} 

