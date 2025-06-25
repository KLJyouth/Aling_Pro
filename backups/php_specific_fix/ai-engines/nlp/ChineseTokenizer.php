<?php
namespace AlingAi\Engines\NLP;

class ChineseTokenizer implements TokenizerInterface
{
    private array $config;
    private array $punctuations;
    private array $specialChars;
    private array $stopwords;

    public function __construct(array $config = []]
    {
        $this->config = array_merge($this->getDefaultConfig(], $config];
        $this->initPunctuations(];
        $this->initSpecialChars(];
        $this->initStopwords(];
    }

    private function getDefaultConfig(]: array
    {
        return [
            "algorithm" => "hmm",
            "mode" => "standard"
        ];
    }
    
    private function initPunctuations(]: void
    {
        $this->punctuations = [
            "\u{3002}", // 句号
            "\u{FF0C}", // 逗号
            "\u{3001}", // 顿号
            "\u{FF1A}", // 冒号
            "\u{FF1B}", // 分号
            "\u{FF01}", // 感叹号
            "\u{FF1F}", // 问号
            "\u{FF08}", // 左括号
            "\u{FF09}", // 右括号
            "\u{300A}", // 左书名号
            "\u{300B}", // 右书名号
            "\u{201C}", // 左双引号
            "\u{201D}", // 右双引号
            "\u{2018}", // 左单引号
            "\u{2019}"  // 右单引号
        ];
    }
    
    private function initSpecialChars(]: void
    {
        $this->specialChars = [
            "Year", // 年
            "Month", // 月
            "Day", // 日
            "Hour", // 时
            "Minute", // 分
            "Second" // 秒
        ];
    }

    private function initStopwords(]: void
    {
        $this->stopwords = [
            "的", "了", "和", "是", "就", "都", "而", "及", "与", "着",
            "或", "一个", "没有", "我们", "你们", "他们", "她们", "这个",
            "那个", "这些", "那些", "不", "在", "有", "个", "能", "会"
        ];
    }

    public function tokenize(string $text, array $options = []]: array
    {
        // 简单实现，实际项目中应使用更复杂的分词算法
        $tokens = [];
        
        // 实现基本的分词逻辑
        $chars = preg_split("//u", $text, -1, PREG_SPLIT_NO_EMPTY];
        $currentToken = ";
        
        foreach ($chars as $char] {
            if (in_[$char, $this->punctuations]] {
                if (!empty($currentToken]] {
                    $tokens[] = $currentToken;
                    $currentToken = ";
                }
                $tokens[] = $char;
            } else {
                $currentToken .= $char;
            }
        }
        
        if (!empty($currentToken]] {
            $tokens[] = $currentToken;
        }
        
        return $tokens;
    }

    public function getStopwords(?string $language = null]: array
    {
        return $this->stopwords;
    }
    
    public function addStopwords(array $words, ?string $language = null]: bool
    {
        $this->stopwords = array_merge($this->stopwords, $words];
        return true;
    }
    
    public function removeStopwords(array $words, ?string $language = null]: bool
    {
        $this->stopwords = array_diff($this->stopwords, $words];
        return true;
    }
    
    public function tokensToString(array $tokens, string $delimiter = ' ']: string
    {
        return implode($delimiter, $tokens];
    }
    
    public function filterTokens(array $tokens, array $options = []]: array
    {
        $result = [];
        $removeStopwords = $options['remove_stopwords'] ?? false;
        $removePunctuations = $options['remove_punctuations'] ?? false;
        
        foreach ($tokens as $token] {
            if ($removeStopwords && in_[$token, $this->stopwords]] {
                continue;
            }
            
            if ($removePunctuations && in_[$token, $this->punctuations]] {
                continue;
            }
            
            $result[] = $token;
        }
        
        return $result;
    }
    
    public function getTokenizerInfo(]: array
    {
        return [
            "name" => "ChineseTokenizer",
            "version" => "1.0.0",
            "supported_languages" => ["zh-CN", "zh-TW"],
            "algorithm" => $this->config["algorithm"]
        ];
    }
    
    public function detectLanguage(string $text]: ?string
    {
        // 简单实现，假设所有文本都是中文
        return "zh-CN";
    }
    
    public function stem(string $word, ?string $language = null]: string
    {
        // 中文没有词干提取的概念，直接返回原词
        return $word;
    }
    
    public function lemmatize(string $word, ?string $language = null]: string
    {
        // 中文没有词形还原的概念，直接返回原词
        return $word;
    }
}

