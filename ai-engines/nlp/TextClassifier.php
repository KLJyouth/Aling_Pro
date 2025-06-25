<?php
declare(strict_types=1];

/**
 * �ļ�����TextClassifier.php
 * �����������ı������� - ʵ���ı����๦��
 * ����ʱ�䣺2025-01-XX
 * ����޸ģ�2025-01-XX
 * �汾��1.0.0
 *
 * @package AlingAi\AI\Engines\NLP
 * @author AlingAi Team
 * @license MIT
 */

namespace AlingAi\AI\Engines\NLP;

use Exception;
use InvalidArgumentException;

/**
 * �ı�������
 *
 * ʵ���ı����๦�ܣ�֧�ֶ��ַ��෽�������
 */
class TextClassifier
{
    /**
     * ���ò���
     */
    private array $config;

    /**
     * ����ģ��
     */
    private array $models = [];

    /**
     * ������ȡ��
     */
    private array $featureExtractors = [];

    /**
     * ����������
     */
    private array $cache = [];
    
    /**
     * ���캯��
     *
     * @param array $config ���ò���
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->getDefaultConfig(), $config];
    }
    
    /**
     * ��ȡĬ������
     *
     * @return array Ĭ������
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
     * �����ı�
     *
     * @param string $text �ı�����
     * @param array $options ѡ��
     * @return array ������
     */
    public function classify(string $text, array $options = []): array
    {
        // �ϲ�ѡ��
        $options = array_merge([
            'language' => $this->config['default_language'], 
            'algorithm' => $this->config['default_algorithm'], 
            'categories' => [], 
            'min_confidence' => $this->config['min_confidence']
        ],  $options];
        
        // ��黺��
        $cacheKey = md5($text . json_encode($options)];
        if ($this->config['use_cache'] && isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }
        
        // �����㷨����
        $result = [];
        switch ($options['algorithm']) {
            case 'naive_bayes':
                $result = $this->classifyWithNaiveBayes($text, $options];
                break;
            case 'svm':
                $result = $this->classifyWithSVM($text, $options];
                break;
            case 'neural_network':
                $result = $this->classifyWithNeuralNetwork($text, $options];
                break;
            default:
                throw new InvalidArgumentException("��֧�ֵķ����㷨: {$options['algorithm']}"];
        }
        
        // ������
        if ($this->config['use_cache']) {
            $this->cache[$cacheKey] = $result;
        }
        
        return $result;
    }
    
    /**
     * ʹ�����ر�Ҷ˹�㷨����
     *
     * @param string $text �ı�����
     * @param array $options ѡ��
     * @return array ������
     */
    private function classifyWithNaiveBayes(string $text, array $options): array
    {
        // ��ȡ����
        $features = $this->extractFeatures($text, $options['language']];
        
        // ����ģ��
        $model = $this->loadModel('naive_bayes', $options['language']];
        
        // ���ָ�������ֻ������Щ���ĸ���
        $categories = $options['categories'] ?: array_keys($model['categories']];
        
        // ����ÿ�����ĸ���
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
        
        // ��һ������
        $maxScore = max($scores];
        $expScores = [];
        $sumExp = 0;
        
        foreach ($scores as $category => $score) {
            $expScores[$category] = exp($score - $maxScore];
            $sumExp += $expScores[$category];
        }
        
        $probabilities = [];
        foreach ($expScores as $category => $expScore) {
            $probabilities[$category] = $expScore / $sumExp;
        }
        
        // ����
        arsort($probabilities];
        
        // �������
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
     * ʹ��SVM�㷨����
     *
     * @param string $text �ı�����
     * @param array $options ѡ��
     * @return array ������
     */
    private function classifyWithSVM(string $text, array $options): array
    {
        // ��ȡ����
        $features = $this->extractFeatures($text, $options['language']];
        
        // ����ģ��
        $model = $this->loadModel('svm', $options['language']];
        
        // ���ָ�������ֻ������Щ���ĸ���
        $categories = $options['categories'] ?: array_keys($model['categories']];
        
        // ����ÿ�����ĵ÷�
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
        
        // ת��Ϊ����
        $maxScore = max($scores];
        $expScores = [];
        $sumExp = 0;
        
        foreach ($scores as $category => $score) {
            $expScores[$category] = exp($score - $maxScore];
            $sumExp += $expScores[$category];
        }
        
        $probabilities = [];
        foreach ($expScores as $category => $expScore) {
            $probabilities[$category] = $expScore / $sumExp;
        }
        
        // ����
        arsort($probabilities];
        
        // �������
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
     * ʹ���������㷨����
     *
     * @param string $text �ı�����
     * @param array $options ѡ��
     * @return array ������
     */
    private function classifyWithNeuralNetwork(string $text, array $options): array
    {
        // ��ȡ����
        $features = $this->extractFeatures($text, $options['language']];
        
        // ����ģ��
        $model = $this->loadModel('neural_network', $options['language']];
        
        // ���ָ�������ֻ������Щ���ĸ���
        $categories = $options['categories'] ?: array_keys($model['categories']];
        
        // ����ÿ�����ĵ÷�
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
        
        // Ӧ��softmax����
        $maxScore = max($scores];
        $expScores = [];
        $sumExp = 0;
        
        foreach ($scores as $category => $score) {
            $expScores[$category] = exp($score - $maxScore];
            $sumExp += $expScores[$category];
        }
        
        $probabilities = [];
        foreach ($expScores as $category => $expScore) {
            $probabilities[$category] = $expScore / $sumExp;
        }
        
        // ����
        arsort($probabilities];
        
        // �������
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
     * ��ȡ����
     *
     * @param string $text �ı�����
     * @param string $language ����
     * @return array ��������
     */
    private function extractFeatures(string $text, string $language): array
    {
        // ��ȡ������ȡ��
        $extractor = $this->getFeatureExtractor($language];
        
        // ��ȡ����
        return $extractor->extract($text];
    }
    
    /**
     * ��ȡ������ȡ��
     *
     * @param string $language ����
     * @return object ������ȡ��
     */
    private function getFeatureExtractor(string $language): object
    {
        if (!isset($this->featureExtractors[$language])) {
            // ����������ȡ��
            $this->featureExtractors[$language] = $this->createFeatureExtractor($language];
        }
        
        return $this->featureExtractors[$language];
    }
    
    /**
     * ����������ȡ��
     *
     * @param string $language ����
     * @return object ������ȡ��
     */
    private function createFeatureExtractor(string $language): object
    {
        // �򵥵�������ȡ��ʵ��
        return new class($language) {
            private string $language;
            
            public function __construct(string $language)
            {
                $this->language = $language;
            }
            
            public function extract(string $text): array
            {
                // �򵥵Ĵʴ�ģ��
                $features = [];
                
                // �ִ�
                $tokens = $this->tokenize($text];
                
                // ͳ�ƴ�Ƶ
                foreach ($tokens as $token) {
                    $token = mb_strtolower($token];
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
                    // ���ķִʣ��򻯰棩
                    $text = preg_replace('/[^\p{Han}]/u', ' ', $text];
                    $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY];
                    return array_filter($chars];
                } else {
                    // Ӣ�ķִ�
                    $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text];
                    return array_filter(explode(' ', $text)];
                }
            }
        };
    }
    
    /**
     * ����ģ��
     *
     * @param string $algorithm �㷨
     * @param string $language ����
     * @return array ģ������
     */
    private function loadModel(string $algorithm, string $language): array
    {
        $key = "{$algorithm}_{$language}";
        
        if (!isset($this->models[$key])) {
            // ����ģ��
            $this->models[$key] = $this->createDummyModel($algorithm, $language];
        }
        
        return $this->models[$key];
    }
    
    /**
     * ��������ģ��
     *
     * @param string $algorithm �㷨
     * @param string $language ����
     * @return array ģ������
     */
    private function createDummyModel(string $algorithm, string $language): array
    {
        // ����һ���򵥵�����ģ��������ʾ
        $model = [
            'algorithm' => $algorithm,
            'language' => $language,
            'categories' => [], 
            'smoothing' => 0.01
        ];
        
        // ���һЩ�������
        $categories = ['�Ƽ�', '����', '����', '����', '����'];
        
        foreach ($categories as $category) {
            $model['categories'][$category] = [
                'prior_probability' => log(1 / count($categories)],
                'feature_probabilities' => [], 
                'weights' => [], 
                'bias' => 0
            ];
            
            // ���һЩ��������
            for ($i = 0; $i < 10; $i++) {
                $feature = "feature_{$i}";
                $model['categories'][$category]['feature_probabilities'][$feature] = rand(1, 100) / 100;
                $model['categories'][$category]['weights'][$feature] = (rand(-100, 100) / 100];
            }
            
            $model['categories'][$category]['bias'] = (rand(-100, 100) / 100];
        }
        
        return $model;
    }
    
    /**
     * ѵ��ģ��
     *
     * @param array $trainingData ѵ������
     * @param array $options ѡ��
     * @return bool �Ƿ�ɹ�
     */
    public function train(array $trainingData, array $options = []): bool
    {
        // �ϲ�ѡ��
        $options = array_merge([
            'language' => $this->config['default_language'], 
            'algorithm' => $this->config['default_algorithm'], 
            'iterations' => 100,
            'learning_rate' => 0.01
        ],  $options];
        
        // �����㷨ѵ��ģ��
        switch ($options['algorithm']) {
            case 'naive_bayes':
                return $this->trainNaiveBayes($trainingData, $options];
            case 'svm':
                return $this->trainSVM($trainingData, $options];
            case 'neural_network':
                return $this->trainNeuralNetwork($trainingData, $options];
            default:
                throw new InvalidArgumentException("��֧�ֵķ����㷨: {$options['algorithm']}"];
        }
    }
    
    /**
     * ѵ�����ر�Ҷ˹ģ��
     *
     * @param array $trainingData ѵ������
     * @param array $options ѡ��
     * @return bool �Ƿ�ɹ�
     */
    private function trainNaiveBayes(array $trainingData, array $options): bool
    {
        // ʵ��Ӧ����Ӧ��ʵ�����������ر�Ҷ˹ѵ���㷨
        return true;
    }
    
    /**
     * ѵ��SVMģ��
     *
     * @param array $trainingData ѵ������
     * @param array $options ѡ��
     * @return bool �Ƿ�ɹ�
     */
    private function trainSVM(array $trainingData, array $options): bool
    {
        // ʵ��Ӧ����Ӧ��ʵ��������SVMѵ���㷨
        return true;
    }
    
    /**
     * ѵ��������ģ��
     *
     * @param array $trainingData ѵ������
     * @param array $options ѡ��
     * @return bool �Ƿ�ɹ�
     */
    private function trainNeuralNetwork(array $trainingData, array $options): bool
    {
        // ʵ��Ӧ����Ӧ��ʵ��������������ѵ���㷨
        return true;
    }
    
    /**
     * ��ȡ����
     *
     * @return array ����
     */
    public function getConfig(): array
    {
        return $this->config;
    }
    
    /**
     * ��������
     *
     * @param array $config ����
     */
    public function setConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config];
    }
    
    /**
     * �������
     */
    public function clearCache(): void
    {
        $this->cache = [];
    }
}

