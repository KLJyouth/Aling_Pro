<?php
/**
 * 文件名：POSTagger.php
 * 功能描述：词性标注器 - 实现文本词性标注功能
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
 * 词性标注器
 *
 * 实现文本的词性标注功能，支持多种语言
 */
class POSTagger
{
    /**
     * 配置参数
     */
    private array $config;

    /**
     * 英文词性标注规则
     */
    private array $englishRules;

    /**
     * 中文词性标注规则
     */
    private array $chineseRules;

    /**
     * 构造函数
     *
     * @param array $config 配置参数
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->getDefaultConfig(), $config);
        $this->loadResources();
    }

    /**
     * 加载资源
     */
    private function loadResources(): void
    {
        $this->loadEnglishRules();
        $this->loadChineseRules();
    }

    /**
     * 加载英文词性标注规则
     */
    private function loadEnglishRules(): void
    {
        // 简化版的英文词性标注规则
        $this->englishRules = [
            // 冠词
            'a' => 'DT',
            'an' => 'DT',
            'the' => 'DT',
            
            // 人称代词
            'i' => 'PRP',
            'you' => 'PRP',
            'he' => 'PRP',
            'she' => 'PRP',
            'it' => 'PRP',
            'we' => 'PRP',
            'they' => 'PRP',
            'me' => 'PRP',
            'him' => 'PRP',
            'her' => 'PRP',
            'us' => 'PRP',
            'them' => 'PRP',
            
            // 所有格代词
            'my' => 'PRP$',
            'your' => 'PRP$',
            'his' => 'PRP$',
            'her' => 'PRP$',
            'its' => 'PRP$',
            'our' => 'PRP$',
            'their' => 'PRP$',
            
            // 常见介词
            'in' => 'IN',
            'on' => 'IN',
            'at' => 'IN',
            'by' => 'IN',
            'for' => 'IN',
            'with' => 'IN',
            'about' => 'IN',
            'to' => 'TO',
            'from' => 'IN',
            'of' => 'IN',
            
            // 常见连词
            'and' => 'CC',
            'or' => 'CC',
            'but' => 'CC',
            'if' => 'IN',
            'because' => 'IN',
            'although' => 'IN',
            'though' => 'IN',
            'while' => 'IN',
            'when' => 'WRB',
            'where' => 'WRB',
            'why' => 'WRB',
            'how' => 'WRB',
            
            // 常见动词
            'is' => 'VBZ',
            'am' => 'VBP',
            'are' => 'VBP',
            'was' => 'VBD',
            'were' => 'VBD',
            'be' => 'VB',
            'been' => 'VBN',
            'being' => 'VBG',
            'have' => 'VBP',
            'has' => 'VBZ',
            'had' => 'VBD',
            'do' => 'VBP',
            'does' => 'VBZ',
            'did' => 'VBD',
            'can' => 'MD',
            'could' => 'MD',
            'will' => 'MD',
            'would' => 'MD',
            'shall' => 'MD',
            'should' => 'MD',
            'may' => 'MD',
            'might' => 'MD',
            'must' => 'MD',
            
            // 常见副词
            'very' => 'RB',
            'really' => 'RB',
            'quite' => 'RB',
            'not' => 'RB',
            'never' => 'RB',
            'always' => 'RB',
            'often' => 'RB',
            'sometimes' => 'RB',
            'rarely' => 'RB',
            'seldom' => 'RB',
            'too' => 'RB',
            'also' => 'RB',
            'just' => 'RB',
            'only' => 'RB',
            
            // 数词
            'one' => 'CD',
            'two' => 'CD',
            'three' => 'CD',
            'four' => 'CD',
            'five' => 'CD',
            'six' => 'CD',
            'seven' => 'CD',
            'eight' => 'CD',
            'nine' => 'CD',
            'ten' => 'CD',
            'hundred' => 'CD',
            'thousand' => 'CD',
            'million' => 'CD',
            'billion' => 'CD',
            'first' => 'JJ',
            'second' => 'JJ',
            'third' => 'JJ',
            'fourth' => 'JJ',
            'fifth' => 'JJ'
        ];
    }

    /**
     * 加载中文词性标注规则
     */
    private function loadChineseRules(): void
    {
        // 简化版的中文词性标注规则
        $this->chineseRules = [
            // 名词
            '人' => 'n',
            '时间' => 'n',
            '地点' => 'n',
            '国家' => 'n',
            '城市' => 'n',
            '公司' => 'n',
            '学校' => 'n',
            '书' => 'n',
            '电脑' => 'n',
            '手机' => 'n',
            
            // 动词
            '是' => 'v',
            '有' => 'v',
            '去' => 'v',
            '来' => 'v',
            '看' => 'v',
            '听' => 'v',
            '说' => 'v',
            '写' => 'v',
            '读' => 'v',
            '吃' => 'v',
            '喝' => 'v',
            '睡' => 'v',
            '走' => 'v',
            '跑' => 'v',
            '跳' => 'v',
            
            // 形容词
            '好' => 'a',
            '坏' => 'a',
            '大' => 'a',
            '小' => 'a',
            '高' => 'a',
            '低' => 'a',
            '长' => 'a',
            '短' => 'a',
            '快' => 'a',
            '慢' => 'a',
            '新' => 'a',
            '旧' => 'a',
            '红' => 'a',
            '绿' => 'a',
            '蓝' => 'a',
            '黄' => 'a',
            
            // 副词
            '很' => 'd',
            '非常' => 'd',
            '太' => 'd',
            '更' => 'd',
            '最' => 'd',
            '又' => 'd',
            '也' => 'd',
            '还' => 'd',
            '都' => 'd',
            '已经' => 'd',
            '曾经' => 'd',
            '正在' => 'd',
            '将要' => 'd',
            
            // 代词
            '我' => 'r',
            '你' => 'r',
            '他' => 'r',
            '她' => 'r',
            '它' => 'r',
            '我们' => 'r',
            '你们' => 'r',
            '他们' => 'r',
            '她们' => 'r',
            '它们' => 'r',
            '这' => 'r',
            '那' => 'r',
            '这些' => 'r',
            '那些' => 'r',
            '谁' => 'r',
            '什么' => 'r',
            '哪里' => 'r',
            
            // 数词
            '一' => 'm',
            '二' => 'm',
            '三' => 'm',
            '四' => 'm',
            '五' => 'm',
            '六' => 'm',
            '七' => 'm',
            '八' => 'm',
            '九' => 'm',
            '十' => 'm',
            '百' => 'm',
            '千' => 'm',
            '万' => 'm',
            '亿' => 'm',
            
            // 量词
            '个' => 'q',
            '只' => 'q',
            '条' => 'q',
            '张' => 'q',
            '片' => 'q',
            '本' => 'q',
            '册' => 'q',
            '台' => 'q',
            '辆' => 'q',
            '把' => 'q',
            '支' => 'q',
            '根' => 'q',
            
            // 介词
            '在' => 'p',
            '对' => 'p',
            '给' => 'p',
            '从' => 'p',
            '向' => 'p',
            '和' => 'p',
            '与' => 'p',
            '为' => 'p',
            '被' => 'p',
            '把' => 'p',
            
            // 连词
            '和' => 'c',
            '与' => 'c',
            '或者' => 'c',
            '但是' => 'c',
            '因为' => 'c',
            '所以' => 'c',
            '如果' => 'c',
            '虽然' => 'c',
            '尽管' => 'c',
            '不过' => 'c',
            
            // 助词
            '的' => 'u',
            '地' => 'u',
            '得' => 'u',
            '了' => 'u',
            '着' => 'u',
            '过' => 'u',
            
            // 叹词
            '啊' => 'e',
            '哦' => 'e',
            '呀' => 'e',
            '哇' => 'e',
            '嗯' => 'e',
            
            // 标点符号
            '。' => 'w',
            '，' => 'w',
            '！' => 'w',
            '？' => 'w',
            '：' => 'w',
            '；' => 'w',
            '、' => 'w',
            '"' => 'w',
            '"' => 'w',
            ''' => 'w',
            ''' => 'w',
            '（' => 'w',
            '）' => 'w',
            '【' => 'w',
            '】' => 'w',
            '《' => 'w',
            '》' => 'w'
        ];
    }

    /**
     * 获取默认配置
     *
     * @return array 默认配置
     */
    private function getDefaultConfig(): array
    {
        return [
            'model' => 'default', // default, hmm, crf
            'default_language' => 'en',
            'fallback_tag' => [
                'en' => 'NN', // 英文默认为普通名词
                'zh' => 'n'   // 中文默认为名词
            ],
            'use_rules' => true,
            'use_statistical' => true,
            'min_confidence' => 0.6
        ];
    }

    /**
     * 标注词性
     *
     * @param array $tokens 分词结果
     * @param string|null $language 语言代码，如果为null则自动检测
     * @return array 词性标注结果
     */
    public function tag(array $tokens, ?string $language = null): array
    {
        if (empty($tokens)) {
            return [];
        }

        // 检测语言
        if ($language === null) {
            $language = $this->detectLanguage($tokens);
        }

        // 根据语言选择标注方法
        switch ($language) {
            case 'en':
                return $this->tagEnglish($tokens);
            case 'zh':
                return $this->tagChinese($tokens);
            default:
                return $this->tagEnglish($tokens); // 默认使用英文标注
        }
    }

    /**
     * 检测语言
     *
     * @param array $tokens 分词结果
     * @return string 语言代码
     */
    private function detectLanguage(array $tokens): string
    {
        $chineseCount = 0;
        $englishCount = 0;
        $totalCount = count($tokens);

        if ($totalCount === 0) {
            return $this->config['default_language'];
        }

        foreach ($tokens as $token) {
            if (isset($token['text'])) {
                if (preg_match('/\p{Han}/u', $token['text'])) {
                    $chineseCount++;
                } elseif (preg_match('/[a-zA-Z]/u', $token['text'])) {
                    $englishCount++;
                }
            }
        }

        $chineseRatio = $chineseCount / $totalCount;
        $englishRatio = $englishCount / $totalCount;

        if ($chineseRatio > $englishRatio) {
            return 'zh';
        } else {
            return 'en';
        }
    }

    /**
     * 英文词性标注
     *
     * @param array $tokens 分词结果
     * @return array 词性标注结果
     */
    private function tagEnglish(array $tokens): array
    {
        $result = [];

        foreach ($tokens as $token) {
            if (!isset($token['text'])) {
                continue;
            }

            $text = $token['text'];
            $lowerText = strtolower($text);
            $tag = $this->config['fallback_tag']['en'];
            $confidence = 0.5;

            // 使用规则标注
            if ($this->config['use_rules']) {
                if (isset($this->englishRules[$lowerText])) {
                    $tag = $this->englishRules[$lowerText];
                    $confidence = 0.9;
                } else {
                    // 简单的后缀规则
                    if (preg_match('/ing$/i', $text)) {
                        $tag = 'VBG'; // 现在分词
                        $confidence = 0.8;
                    } elseif (preg_match('/ed$/i', $text)) {
                        $tag = 'VBD'; // 过去式
                        $confidence = 0.8;
                    } elseif (preg_match('/s$/i', $text) && !preg_match('/ss$/i', $text)) {
                        $tag = 'NNS'; // 复数名词
                        $confidence = 0.7;
                    } elseif (preg_match('/ly$/i', $text)) {
                        $tag = 'RB'; // 副词
                        $confidence = 0.8;
                    } elseif (preg_match('/able$/i', $text) || preg_match('/ible$/i', $text)) {
                        $tag = 'JJ'; // 形容词
                        $confidence = 0.8;
                    } elseif (preg_match('/^\d+$/i', $text)) {
                        $tag = 'CD'; // 基数词
                        $confidence = 0.9;
                    } elseif (preg_match('/^[A-Z][a-z]*$/i', $text)) {
                        $tag = 'NNP'; // 专有名词
                        $confidence = 0.7;
                    }
                }
            }

            // 使用统计方法标注（简化版）
            if ($this->config['use_statistical'] && $confidence < $this->config['min_confidence']) {
                // 这里应该使用统计模型，但为了简化，我们只使用一些简单的启发式规则
                if (strlen($text) > 2 && !isset($this->englishRules[$lowerText])) {
                    $tag = 'NN'; // 默认为名词
                    $confidence = 0.6;
                }
            }

            $result[] = [
                'token' => $token,
                'tag' => $tag,
                'confidence' => $confidence
            ];
        }

        return $result;
    }

    /**
     * 中文词性标注
     *
     * @param array $tokens 分词结果
     * @return array 词性标注结果
     */
    private function tagChinese(array $tokens): array
    {
        $result = [];

        foreach ($tokens as $token) {
            if (!isset($token['text'])) {
                continue;
            }

            $text = $token['text'];
            $tag = $this->config['fallback_tag']['zh'];
            $confidence = 0.5;

            // 使用规则标注
            if ($this->config['use_rules']) {
                if (isset($this->chineseRules[$text])) {
                    $tag = $this->chineseRules[$text];
                    $confidence = 0.9;
                } else {
                    // 简单的规则
                    if (preg_match('/^[0-9一二三四五六七八九十百千万亿]+$/u', $text)) {
                        $tag = 'm'; // 数词
                        $confidence = 0.9;
                    } elseif (preg_match('/[。，！？：；、（）【】《》""'']/u', $text)) {
                        $tag = 'w'; // 标点符号
                        $confidence = 0.9;
                    }
                }
            }

            // 使用统计方法标注（简化版）
            if ($this->config['use_statistical'] && $confidence < $this->config['min_confidence']) {
                // 这里应该使用统计模型，但为了简化，我们只使用一些简单的启发式规则
                if (mb_strlen($text, 'UTF-8') > 1 && !isset($this->chineseRules[$text])) {
                    $tag = 'n'; // 默认为名词
                    $confidence = 0.6;
                }
            }

            $result[] = [
                'token' => $token,
                'tag' => $tag,
                'confidence' => $confidence
            ];
        }

        return $result;
    }

    /**
     * 获取配置
     *
     * @return array 配置
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * 设置配置
     *
     * @param array $config 配置
     * @return void
     */
    public function setConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * 获取英文词性标注规则
     *
     * @return array 英文词性标注规则
     */
    public function getEnglishRules(): array
    {
        return $this->englishRules;
    }

    /**
     * 添加英文词性标注规则
     *
     * @param string $word 单词
     * @param string $tag 词性标签
     * @return void
     */
    public function addEnglishRule(string $word, string $tag): void
    {
        $this->englishRules[strtolower($word)] = $tag;
    }

    /**
     * 获取中文词性标注规则
     *
     * @return array 中文词性标注规则
     */
    public function getChineseRules(): array
    {
        return $this->chineseRules;
    }

    /**
     * 添加中文词性标注规则
     *
     * @param string $word 单词
     * @param string $tag 词性标签
     * @return void
     */
    public function addChineseRule(string $word, string $tag): void
    {
        $this->chineseRules[$word] = $tag;
    }
}
