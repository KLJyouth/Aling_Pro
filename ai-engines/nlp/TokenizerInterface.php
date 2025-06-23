<?php declare(strict_types=1);

/**
 * 文件名：TokenizerInterface.php
 * 功能描述：分词器接口 - 定义所有分词器必须实现的方法
 * 创建时间：2025-01-XX
 * 最后修改：2025-01-XX
 * 版本：1.0.0
 *
 * @package AlingAi\Engines\NLP
 * @author AlingAi Team
 * @license MIT
 */

namespace AlingAi\Engines\NLP;

/**
 * 分词器接口
 *
 * 所有分词器都必须实现这个接口，提供基本的文本分词功能
 */
interface TokenizerInterface
{
    /**
     * 分词方法，将文本分割为词元（token）
     *
     * @param string $text 要分词的文本
     * @param array $options 分词选项
     * @return array 分词结果数组，每个元素包含词元文本、位置、类型等信息
     */
    public function tokenize(string $text, array $options = []): array;
    
    /**
     * 获取停用词列表
     *
     * @param string|null $language 语言代码，为null时返回当前设置的语言
     * @return array 停用词列表
     */
    public function getStopwords(?string $language = null): array;
    
    /**
     * 添加自定义停用词
     *
     * @param array $words 要添加的停用词
     * @param string|null $language 语言代码，为null时使用当前设置的语言
     * @return bool 是否添加成功
     */
    public function addStopwords(array $words, ?string $language = null): bool;
    
    /**
     * 移除停用词
     *
     * @param array $words 要移除的停用词
     * @param string|null $language 语言代码，为null时使用当前设置的语言
     * @return bool 是否移除成功
     */
    public function removeStopwords(array $words, ?string $language = null): bool;
    
    /**
     * 将分词结果转换为字符串
     *
     * @param array $tokens 分词结果
     * @param string $delimiter 分隔符
     * @return string 转换后的字符串
     */
    public function tokensToString(array $tokens, string $delimiter = ' '): string;
    
    /**
     * 过滤分词结果
     *
     * @param array $tokens 原始分词结果
     * @param array $options 过滤选项，如移除停用词、标点等
     * @return array 过滤后的分词结果
     */
    public function filterTokens(array $tokens, array $options = []): array;
    
    /**
     * 获取分词器信息
     *
     * @return array 分词器信息，包含名称、版本、支持语言等
     */
    public function getTokenizerInfo(): array;
    
    /**
     * 检测语言
     *
     * @param string $text 要检测的文本
     * @return string|null 检测到的语言代码，如无法检测则返回null
     */
    public function detectLanguage(string $text): ?string;
    
    /**
     * 获取词干
     * 
     * @param string $word 要提取词干的单词
     * @param string|null $language 语言代码，为null时使用当前设置的语言
     * @return string 提取的词干
     */
    public function stem(string $word, ?string $language = null): string;
    
    /**
     * 词形还原
     * 
     * @param string $word 要还原的单词
     * @param string|null $language 语言代码，为null时使用当前设置的语言
     * @return string 还原后的词形（词元）
     */
    public function lemmatize(string $word, ?string $language = null): string;
}
