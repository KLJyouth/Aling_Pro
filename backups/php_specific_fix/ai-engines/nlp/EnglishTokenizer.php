<?php
namespace AlingAi\Engines\NLP;

class EnglishTokenizer implements TokenizerInterface
{
    private array $config;
    private array $dictionary;
    private array $stopwords;

    public function __construct(array $config = []]
    {
        $this->config = array_merge($this->getDefaultConfig(], $config];
        $this->loadResources(];
    }
    
    private function loadResources(]: void
    {
        $this->loadDictionary(];
        $this->loadStopwords(];
    }

    private function loadDictionary(]: void
    {
        $this->dictionary = [
            "artificial", "intelligence", "machine", "learning"
        ];
    }

    private function loadStopwords(]: void
    {
        $this->stopwords = ["a", "an", "the", "and", "or", "but"];
    }

    private function getDefaultConfig(]: array
    {
        return [
            "algorithm" => "word_boundary",
            "lowercase" => true
        ];
    }

    public function tokenize(string $text, array $options = []]: array
    {
        $tokens = preg_split("/\s+/", $text, -1, PREG_SPLIT_NO_EMPTY];
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
    
    public function tokensToString(array $tokens, string $delimiter = " "]: string
    {
        return implode($delimiter, $tokens];
    }
    
    public function filterTokens(array $tokens, array $options = []]: array
    {
        return $tokens;
    }
    
    public function getTokenizerInfo(]: array
    {
        return [
            "name" => "EnglishTokenizer",
            "version" => "1.0.0",
            "supported_languages" => ["en"]
        ];
    }
    
    public function detectLanguage(string $text]: ?string
    {
        return "en";
    }
    
    public function stem(string $word, ?string $language = null]: string
    {
        return $word;
    }
    
    public function lemmatize(string $word, ?string $language = null]: string
    {
        return $word;
    }
}

