<?php
declare(strict_types=1);

/**
 * 文件名：TextClassifier.php
 * 功能描述：文本分类器 - 实现文本分类功能
 * 创建时间：2025-01-XX
 * 最后修改：2025-01-XX
 * 版本：1.0.0
 *
 * @package AlingAi\AI\Engines\NLP
 * @author AlingAi Team
 * @license MIT
 */

namespace AlingAi\AI\Engines\NLP;

use Exception;
use InvalidArgumentException;

/**
 * 文本分类器
 *
 * 实现文本分类功能，支持多种分类方法和类别
 */
class TextClassifier
{
    /**
     * 配置参数
     */
    private array $config;

    /**
     * 分类模型
     */
    private array $models = [];

    /**
     * 特征提取器
     */
    private array $featureExtractors = [];

    /**
     * 分类结果缓存
     */
    private array $cache = [];
    
    /**
     * 构造函数
     *
     * @param array $config 配置参数
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->getDefaultConfig(), $config);
    }
    
    /**
     * 获取默认配置
     *
     * @return array 默认配置
     */
    private function getDefaultConfig(): array
    {
        return [
            'default_language' => 'zh-CN',
            'supported_languages' => ['zh-CN', 'en-US'],
            'default_algorithm' => 'naive_bayes',
            'use_cache' => true,
            'cache_ttl' => 3600,
            'min_confidence' => 0.6
        ];
    }
    
    /**
     * 分类文本
     *
     * @param string $text 文本内容
     * @param array $options 选项
     * @return array 分类结果
     */
    public function classify(string $text, array $options = []): array
    {
        // 合并选项
        $options = array_merge([
            'language' => $this->config['default_language'],
            'algorithm' => $this->config['default_algorithm'],
            'categories' => [],
            'min_confidence' => $this->config['min_confidence']
        ], $options);
        
        // 检查缓存
        $cacheKey = md5($text . json_encode($options));
        if ($this->config['use_cache'] && isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }
        
        // 根据算法分类
        $result = [];
        switch ($options['algorithm']) {
            case 'naive_bayes':
                $result = $this->classifyWithNaiveBayes($text, $options);
                break;
            case 'svm':
                $result = $this->classifyWithSVM($text, $options);
                break;
            case 'neural_network':
                $result = $this->classifyWithNeuralNetwork($text, $options);
                break;
            default:
                throw new InvalidArgumentException("不支持的分类算法: {$options['algorithm']}");
        }
        
        // 缓存结果
        if ($this->config['use_cache']) {
            $this->cache[$cacheKey] = $result;
        }
        
        return $result;
    }
    
    /**
     * 使用朴素贝叶斯算法分类
     *
     * @param string $text 文本内容
     * @param array $options 选项
     * @return array 分类结果
     */
    private function classifyWithNaiveBayes(string $text, array $options): array
    {
        // 提取特征
        $features = $this->extractFeatures($text, $options['language']);
        
        // 加载模型
        $model = $this->loadModel('naive_bayes', $options['language']);
        
        // 如果指定了类别，只计算这些类别的概率
        $categories = $options['categories'] ?: array_keys($model['categories']);
        
        // 计算每个类别的概率
        $scores = [];
        foreach ($categories as $category) {
            if (!isset($model['categories'][$category])) {
                continue;
            }
            
            $categoryData = $model['categories'][$category];
            $score = $categoryData['prior_probability'];
            
            foreach ($features as $feature => $count) {
                $featureProb = $categoryData['feature_probabilities'][$feature] ?? $model['smoothing'];
                $score += log($featureProb) * $count;
            }
            
            $scores[$category] = $score;
        }
        
        // 归一化分数
        $maxScore = max($scores);
        $expScores = [];
        $sumExp = 0;
        
        foreach ($scores as $category => $score) {
            $expScores[$category] = exp($score - $maxScore);
            $sumExp += $expScores[$category];
        }
        
        $probabilities = [];
        foreach ($expScores as $category => $expScore) {
            $probabilities[$category] = $expScore / $sumExp;
        }
        
        // 排序
        arsort($probabilities);
        
        // 构建结果
        $predictions = [];
        foreach ($probabilities as $category => $probability) {
            if ($probability >= $options['min_confidence']) {
                $predictions[] = [
                    'category' => $category,
                    'confidence' => round($probability, 4)
                ];
            }
        }
        
        return [
            'algorithm' => 'naive_bayes',
            'predictions' => $predictions,
            'top_category' => !empty($predictions) ? $predictions[0]['category'] : null,
            'top_confidence' => !empty($predictions) ? $predictions[0]['confidence'] : 0
        ];
    }
    
    /**
     * 使用SVM算法分类
     *
     * @param string $text 文本内容
     * @param array $options 选项
     * @return array 分类结果
     */
    private function classifyWithSVM(string $text, array $options): array
    {
        // 提取特征
        $features = $this->extractFeatures($text, $options['language']);
        
        // 加载模型
        $model = $this->loadModel('svm', $options['language']);
        
        // 如果指定了类别，只计算这些类别的概率
        $categories = $options['categories'] ?: array_keys($model['categories']);
        
        // 计算每个类别的得分
        $scores = [];
        foreach ($categories as $category) {
            if (!isset($model['categories'][$category])) {
                continue;
            }
            
            $categoryData = $model['categories'][$category];
            $score = $categoryData['bias'];
            
            foreach ($features as $feature => $count) {
                $weight = $categoryData['weights'][$feature] ?? 0;
                $score += $weight * $count;
            }
            
            $scores[$category] = $score;
        }
        
        // 转换为概率
        $maxScore = max($scores);
        $expScores = [];
        $sumExp = 0;
        
        foreach ($scores as $category => $score) {
            $expScores[$category] = exp($score - $maxScore);
            $sumExp += $expScores[$category];
        }
        
        $probabilities = [];
        foreach ($expScores as $category => $expScore) {
            $probabilities[$category] = $expScore / $sumExp;
        }
        
        // 排序
        arsort($probabilities);
        
        // 构建结果
        $predictions = [];
        foreach ($probabilities as $category => $probability) {
            if ($probability >= $options['min_confidence']) {
                $predictions[] = [
                    'category' => $category,
                    'confidence' => round($probability, 4)
                ];
            }
        }
        
        return [
            'algorithm' => 'svm',
            'predictions' => $predictions,
            'top_category' => !empty($predictions) ? $predictions[0]['category'] : null,
            'top_confidence' => !empty($predictions) ? $predictions[0]['confidence'] : 0
        ];
    }
    
    /**
     * 使用神经网络算法分类
     *
     * @param string $text 文本内容
     * @param array $options 选项
     * @return array 分类结果
     */
    private function classifyWithNeuralNetwork(string $text, array $options): array
    {
        // 提取特征
        $features = $this->extractFeatures($text, $options['language']);
        
        // 加载模型
        $model = $this->loadModel('neural_network', $options['language']);
        
        // 如果指定了类别，只计算这些类别的概率
        $categories = $options['categories'] ?: array_keys($model['categories']);
        
        // 计算每个类别的得分
        $scores = [];
        foreach ($categories as $category) {
            if (!isset($model['categories'][$category])) {
                continue;
            }
            
            $categoryData = $model['categories'][$category];
            $score = $categoryData['bias'];
            
            foreach ($features as $feature => $count) {
                $weight = $categoryData['weights'][$feature] ?? 0;
                $score += $weight * $count;
            }
            
            $scores[$category] = $score;
        }
        
        // 应用softmax函数
        $maxScore = max($scores);
        $expScores = [];
        $sumExp = 0;
        
        foreach ($scores as $category => $score) {
            $expScores[$category] = exp($score - $maxScore);
            $sumExp += $expScores[$category];
        }
        
        $probabilities = [];
        foreach ($expScores as $category => $expScore) {
            $probabilities[$category] = $expScore / $sumExp;
        }
        
        // 排序
        arsort($probabilities);
        
        // 构建结果
        $predictions = [];
        foreach ($probabilities as $category => $probability) {
            if ($probability >= $options['min_confidence']) {
                $predictions[] = [
                    'category' => $category,
                    'confidence' => round($probability, 4)
                ];
            }
        }
        
        return [
            'algorithm' => 'neural_network',
            'predictions' => $predictions,
            'top_category' => !empty($predictions) ? $predictions[0]['category'] : null,
            'top_confidence' => !empty($predictions) ? $predictions[0]['confidence'] : 0
        ];
    }
    
    /**
     * 提取特征
     *
     * @param string $text 文本内容
     * @param string $language 语言
     * @return array 特征向量
     */
    private function extractFeatures(string $text, string $language): array
    {
        // 获取特征提取器
        $extractor = $this->getFeatureExtractor($language);
        
        // 提取特征
        return $extractor->extract($text);
    }
    
    /**
     * 获取特征提取器
     *
     * @param string $language 语言
     * @return object 特征提取器
     */
    private function getFeatureExtractor(string $language): object
    {
        if (!isset($this->featureExtractors[$language])) {
            // 创建特征提取器
            $this->featureExtractors[$language] = $this->createFeatureExtractor($language);
        }
        
        return $this->featureExtractors[$language];
    }
    
    /**
     * 创建特征提取器
     *
     * @param string $language 语言
     * @return object 特征提取器
     */
    private function createFeatureExtractor(string $language): object
    {
        // 简单的特征提取器实现
        return new class($language) {
            private string $language;
            
            public function __construct(string $language)
            {
                $this->language = $language;
            }
            
            public function extract(string $text): array
            {
                // 简单的词袋模型
                $features = [];
                
                // 分词
                $tokens = $this->tokenize($text);
                
                // 统计词频
                foreach ($tokens as $token) {
                    $token = mb_strtolower($token);
                    if (!isset($features[$token])) {
                        $features[$token] = 0;
                    }
                    $features[$token]++;
                }
                
                return $features;
            }
            
            private function tokenize(string $text): array
            {
                if ($this->language === 'zh-CN') {
                    // 中文分词（简化版）
                    $text = preg_replace('/[^\p{Han}]/u', ' ', $text);
                    $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);
                    return array_filter($chars);
                } else {
                    // 英文分词
                    $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);
                    return array_filter(explode(' ', $text));
                }
            }
        };
    }
    
    /**
     * 加载模型
     *
     * @param string $algorithm 算法
     * @param string $language 语言
     * @return array 模型数据
     */
    private function loadModel(string $algorithm, string $language): array
    {
        $key = "{$algorithm}_{$language}";
        
        if (!isset($this->models[$key])) {
            // 加载模型
            $this->models[$key] = $this->createDummyModel($algorithm, $language);
        }
        
        return $this->models[$key];
    }
    
    /**
     * 创建虚拟模型
     *
     * @param string $algorithm 算法
     * @param string $language 语言
     * @return array 模型数据
     */
    private function createDummyModel(string $algorithm, string $language): array
    {
        // 创建一个简单的虚拟模型用于演示
        $model = [
            'algorithm' => $algorithm,
            'language' => $language,
            'categories' => [],
            'smoothing' => 0.01
        ];
        
        // 添加一些虚拟类别
        $categories = ['科技', '体育', '娱乐', '政治', '经济'];
        
        foreach ($categories as $category) {
            $model['categories'][$category] = [
                'prior_probability' => log(1 / count($categories)),
                'feature_probabilities' => [],
                'weights' => [],
                'bias' => 0
            ];
            
            // 添加一些虚拟特征
            for ($i = 0; $i < 10; $i++) {
                $feature = "feature_{$i}";
                $model['categories'][$category]['feature_probabilities'][$feature] = rand(1, 100) / 100;
                $model['categories'][$category]['weights'][$feature] = (rand(-100, 100) / 100);
            }
            
            $model['categories'][$category]['bias'] = (rand(-100, 100) / 100);
        }
        
        return $model;
    }
    
    /**
     * 训练模型
     *
     * @param array $trainingData 训练数据
     * @param array $options 选项
     * @return bool 是否成功
     */
    public function train(array $trainingData, array $options = []): bool
    {
        // 合并选项
        $options = array_merge([
            'language' => $this->config['default_language'],
            'algorithm' => $this->config['default_algorithm'],
            'iterations' => 100,
            'learning_rate' => 0.01
        ], $options);
        
        // 根据算法训练模型
        switch ($options['algorithm']) {
            case 'naive_bayes':
                return $this->trainNaiveBayes($trainingData, $options);
            case 'svm':
                return $this->trainSVM($trainingData, $options);
            case 'neural_network':
                return $this->trainNeuralNetwork($trainingData, $options);
            default:
                throw new InvalidArgumentException("不支持的分类算法: {$options['algorithm']}");
        }
    }
    
    /**
     * 训练朴素贝叶斯模型
     *
     * @param array $trainingData 训练数据
     * @param array $options 选项
     * @return bool 是否成功
     */
    private function trainNaiveBayes(array $trainingData, array $options): bool
    {
        // 实际应用中应该实现真正的朴素贝叶斯训练算法
        return true;
    }
    
    /**
     * 训练SVM模型
     *
     * @param array $trainingData 训练数据
     * @param array $options 选项
     * @return bool 是否成功
     */
    private function trainSVM(array $trainingData, array $options): bool
    {
        // 实际应用中应该实现真正的SVM训练算法
        return true;
    }
    
    /**
     * 训练神经网络模型
     *
     * @param array $trainingData 训练数据
     * @param array $options 选项
     * @return bool 是否成功
     */
    private function trainNeuralNetwork(array $trainingData, array $options): bool
    {
        // 实际应用中应该实现真正的神经网络训练算法
        return true;
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
     */
    public function setConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config);
    }
    
    /**
     * 清除缓存
     */
    public function clearCache(): void
    {
        $this->cache = [];
    }
}
