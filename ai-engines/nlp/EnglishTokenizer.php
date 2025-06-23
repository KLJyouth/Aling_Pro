<?php
/**
 * 文件名：EnglishTokenizer.php
 * 功能描述：英文分词器 - 实现英文文本分词功能
 * 创建时间：2025-01-XX
 * 最后修改：2025-01-XX
 * 版本：1.0.0
 *
 * @package AlingAi\AI\Engines\NLP
 * @author AlingAi Team
 * @license MIT
 */

declare(strict_types=1);

namespace AlingAi\AI\Engines\NLP;

use Exception;
use InvalidArgumentException;

/**
 * 英文分词器
 *
 * 实现英文文本的分词功能
 */
class EnglishTokenizer implements TokenizerInterface
{
    private array $config;
    private array $dictionary;
    private array $stopWords;

    /**
     * 构造函数
     *
     * @param array $config 分词器配置
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->getDefaultConfig(), $config);
        $this->loadResources();
    }
