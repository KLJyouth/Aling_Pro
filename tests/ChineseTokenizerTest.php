// fix_chinese_tokenizer_unit_test.php

<?php
/**
 * 中文分词器单元测试
 * 
 * 测试ChineseTokenizer类中的UTF-8编码和Unicode编码点处理
 */

namespace Tests\AlingAi\AI\Engines\NLP;

use PHPUnit\Framework\TestCase;
use AlingAi\AI\Engines\NLP\ChineseTokenizer;

class ChineseTokenizerTest extends TestCase
{
    private ChineseTokenizer $tokenizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tokenizer = new ChineseTokenizer();
    }

    /**
     * 测试基本分词功能
     */
    public function testBasicTokenization(): void
    {
        $text = "中国人工智能发展迅速";
        $tokens = $this->tokenizer->tokenize($text);
        
        $this->assertNotEmpty($tokens);
        $this->assertContains('中国', array_column($tokens, 'text'));
        $this->assertContains('人工智能', array_column($tokens, 'text'));
    }

    /**
     * 测试Unicode编码点处理
     */
    public function testUnicodeCodepointHandling(): void
    {
        // 测试使用Unicode编码点创建的字符
        $char1 = mb_chr(0x4E2D, 'UTF-8'); // 中
        $char2 = mb_chr(0x56FD, 'UTF-8'); // 国
        $text = $char1 . $char2 . "人工智能";
        
        $tokens = $this->tokenizer->tokenize($text);
        $this->assertContains('中国', array_column($tokens, 'text'));
    }

    /**
     * 测试中文标点符号处理
     */
    public function testChinesePunctuationHandling(): void
    {
        $text = "中国，世界！";
        $tokens = $this->tokenizer->tokenize($text);
        
        $foundPunctuation = false;
        foreach ($tokens as $token) {
            if ($token['type'] === 'punctuation') {
                $foundPunctuation = true;
                break;
            }
        }
        
        $this->assertTrue($foundPunctuation, "应该能够识别标点符号");
    }

    /**
     * 测试日期时间识别
     */
    public function testDateTimeRecognition(): void
    {
        // 使用Unicode编码点创建日期字符
        $year = mb_chr(0x5E74, 'UTF-8'); // 年
        $month = mb_chr(0x6708, 'UTF-8'); // 月
        $day = mb_chr(0x65E5, 'UTF-8'); // 日
        
        $text = "2023" . $year . "10" . $month . "1" . $day;
        $tokens = $this->tokenizer->tokenize($text);
        
        $foundDateTime = false;
        foreach ($tokens as $token) {
            if ($token['type'] === 'datetime') {
                $foundDateTime = true;
                break;
            }
        }
        
        $this->assertTrue($foundDateTime, "应该能够识别日期时间格式");
    }

    /**
     * 测试常见字符识别
     */
    public function testCommonCharacterRecognition(): void
    {
        // 使用ReflectionClass访问私有方法
        $reflector = new \ReflectionClass(ChineseTokenizer::class);
        $method = $reflector->getMethod('isCommonChar');
        $method->setAccessible(true);
        
        // 测试常见字符"的"
        $de = mb_chr(0x7684, 'UTF-8'); // 的
        $result = $method->invoke($this->tokenizer, $de);
        $this->assertTrue($result, "应该能够识别常见字符'的'");
        
        // 测试非常见字符
        $nonCommon = mb_chr(0x732B, 'UTF-8'); // 猫
        $result = $method->invoke($this->tokenizer, $nonCommon);
        $this->assertFalse($result, "不应该将'猫'识别为常见字符");
    }

    /**
     * 测试在不同PHP版本下的兼容性
     */
    public function testPHPVersionCompatibility(): void
    {
        if (version_compare(PHP_VERSION, '8.1.0', '>=')) {
            $text = "PHP8.1兼容性测试，包含标点符号和2023年日期";
            $tokens = $this->tokenizer->tokenize($text);
            $this->assertNotEmpty($tokens, "应该在PHP 8.1及以上版本正常工作");
        } else {
            $this->markTestSkipped("此测试仅在PHP 8.1及以上版本运行");
        }
    }

    /**
     * 测试混合分词算法
     */
    public function testMixedTokenizationAlgorithm(): void
    {
        $this->tokenizer->setConfig(['algorithm' => 'mixed']);
        $text = "这是一个未登录词的混合分词测试";
        $tokens = $this->tokenizer->tokenize($text);
        $this->assertNotEmpty($tokens);
    }

    /**
     * 测试大文本性能
     */
    public function testLargeTextPerformance(): void
    {
        $text = str_repeat("中国人工智能技术发展迅速，推动各行各业数字化转型。", 100);
        
        $startTime = microtime(true);
        $tokens = $this->tokenizer->tokenize($text);
        $endTime = microtime(true);
        
        $this->assertLessThan(
            5.0, 
            $endTime - $startTime, 
            "处理大文本不应该超过5秒"
        );
    }
}
