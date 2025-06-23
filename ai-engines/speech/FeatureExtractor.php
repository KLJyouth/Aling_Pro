<?php
/**
 * 文件名：FeatureExtractor.php
 * 功能描述：特征提取器类 - 负责从音频信号中提取声学特征
 * 创建时间：2025-01-XX
 * 最后修改：2025-01-XX
 * 版本：1.0.0
 * 
 * @package AlingAi\Engines\Speech
 * @author AlingAi Team
 * @license MIT
 */

declare(strict_types=1);

namespace AlingAi\Engines\Speech;

use Exception;
use InvalidArgumentException;
use RuntimeException;
use AlingAi\Core\Logger\LoggerInterface;

/**
 * 特征提取器类
 * 
 * 负责从音频信号中提取声学特征，如MFCC、滤波器组能量特征等
 */
class FeatureExtractor
{
    /**
     * @var array 提取器配置
     */
    private array $config;
    
    /**
     * @var LoggerInterface|null 日志记录器
     */
    private ?LoggerInterface $logger;

    /**
     * @var array 支持的特征类型
     */
    private const SUPPORTED_FEATURE_TYPES = [
        'mfcc',         // 梅尔频率倒谱系数
        'fbank',        // 滤波器组能量特征
        'plp',          // 感知线性预测
        'spectrogram',  // 频谱图
        'raw_waveform', // 原始波形
        'pitch'         // 基频特征
    ];
    
    /**
     * 构造函数
     *
     * @param array $config 提取器配置
     * @param LoggerInterface|null $logger 日志记录器
     */
    public function __construct(array $config, ?LoggerInterface $logger = null)
    {
        $this->validateConfig($config);
        $this->config = $config;
        $this->logger = $logger;
        
        if ($this->logger) {
            $this->logger->info('特征提取器初始化完成', [
                'feature_type' => $this->config['feature_type'],
                'sample_rate' => $this->config['sample_rate']
            ]);
        }
    }

    /**
     * 验证配置
     *
     * @param array $config 配置数组
     * @throws InvalidArgumentException 配置无效时抛出异常
     */
    private function validateConfig(array $config): void
    {
        // 验证必要的配置项
        if (!isset($config['feature_type'])) {
            throw new InvalidArgumentException('必须指定特征类型(feature_type)');
        }

        // 验证特征类型
        if (!in_array($config['feature_type'], self::SUPPORTED_FEATURE_TYPES)) {
            throw new InvalidArgumentException(sprintf(
                '不支持的特征类型: %s。支持的类型: %s',
                $config['feature_type'],
                implode(', ', self::SUPPORTED_FEATURE_TYPES)
            ));
        }

        // 验证采样率
        if (!isset($config['sample_rate'])) {
            $config['sample_rate'] = 16000; // 默认采样率
        } elseif (!is_numeric($config['sample_rate']) || $config['sample_rate'] <= 0) {
            throw new InvalidArgumentException('采样率必须是正数');
        }

        // 设置默认窗口大小和步长
        if (!isset($config['window_size'])) {
            $config['window_size'] = 25; // 默认25ms窗口
        }
        if (!isset($config['window_step'])) {
            $config['window_step'] = 10; // 默认10ms步长
        }
    }

    /**
     * 从音频文件提取特征
     *
     * @param string $audioFilePath 音频文件路径
     * @return array 提取的特征
     * @throws Exception 提取失败时抛出异常
     */
    public function extractFromFile(string $audioFilePath): array
    {
        if (!file_exists($audioFilePath)) {
            throw new InvalidArgumentException('音频文件不存在: ' . $audioFilePath);
        }

        if ($this->logger) {
            $this->logger->debug('从音频文件提取特征', ['file' => $audioFilePath]);
        }

        // 读取音频文件
        $audioData = $this->readAudioFile($audioFilePath);
        
        // 提取特征
        return $this->extractFromAudio($audioData);
    }

    /**
     * 从音频数据提取特征
     *
     * @param array $audioData 音频数据
     * @return array 提取的特征
     */
    public function extractFromAudio(array $audioData): array
    {
        if ($this->logger) {
            $this->logger->debug('从音频数据提取特征', [
                'data_length' => count($audioData),
                'feature_type' => $this->config['feature_type']
            ]);
        }

        try {
            // 预处理音频
            $preprocessedAudio = $this->preprocessAudio($audioData);
            
            // 根据特征类型选择不同的提取方法
            switch ($this->config['feature_type']) {
                case 'mfcc':
                    return $this->extractMfcc($preprocessedAudio);
                case 'fbank':
                    return $this->extractFbank($preprocessedAudio);
                case 'plp':
                    return $this->extractPlp($preprocessedAudio);
                case 'spectrogram':
                    return $this->extractSpectrogram($preprocessedAudio);
                case 'raw_waveform':
                    return $this->extractRawWaveform($preprocessedAudio);
                case 'pitch':
                    return $this->extractPitch($preprocessedAudio);
                default:
                    throw new RuntimeException('不支持的特征类型: ' . $this->config['feature_type']);
            }
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->error('特征提取失败', ['error' => $e->getMessage()]);
            }
            throw $e;
        }
    }

    /**
     * 预处理音频数据
     *
     * @param array $audioData 原始音频数据
     * @return array 预处理后的音频数据
     */
    private function preprocessAudio(array $audioData): array
    {
        if ($this->logger) {
            $this->logger->debug('预处理音频数据', [
                'data_length' => count($audioData),
                'preprocess_options' => $this->getPreprocessOptions()
            ]);
        }

        // 应用预处理步骤
        $processed = $audioData;

        // 1. 预加重(Pre-emphasis)
        if ($this->config['pre_emphasis'] ?? true) {
            $preemphCoef = $this->config['pre_emphasis_coef'] ?? 0.97;
            $processed = $this->applyPreEmphasis($processed, $preemphCoef);
        }

        // 2. 端点检测(VAD)
        if ($this->config['vad'] ?? false) {
            $processed = $this->applyVad($processed);
        }

        // 3. 归一化
        if ($this->config['normalize'] ?? true) {
            $processed = $this->normalizeAudio($processed);
        }

        return $processed;
    }

    /**
     * 获取预处理选项
     *
     * @return array 预处理选项
     */
    private function getPreprocessOptions(): array
    {
        return [
            'pre_emphasis' => $this->config['pre_emphasis'] ?? true,
            'pre_emphasis_coef' => $this->config['pre_emphasis_coef'] ?? 0.97,
            'vad' => $this->config['vad'] ?? false,
            'normalize' => $this->config['normalize'] ?? true
        ];
    }
    
    /**
     * 应用预加重滤波
     *
     * @param array $audioData 音频数据
     * @param float $coefficient 预加重系数
     * @return array 处理后的音频数据
     */
    private function applyPreEmphasis(array $audioData, float $coefficient): array
    {
        $result = [];
        $result[0] = $audioData[0];
        
        for ($i = 1; $i < count($audioData); $i++) {
            $result[$i] = $audioData[$i] - $coefficient * $audioData[$i - 1];
        }
        
        return $result;
    }

    /**
     * 应用语音活动检测(VAD)
     *
     * @param array $audioData 音频数据
     * @return array 处理后的音频数据
     */
    private function applyVad(array $audioData): array
    {
        // 模拟VAD处理
        // 实际实现应该使用能量阈值或更复杂的VAD算法
        return $audioData;
    }

    /**
     * 归一化音频数据
     *
     * @param array $audioData 音频数据
     * @return array 归一化后的音频数据
     */
    private function normalizeAudio(array $audioData): array
    {
        // 找出最大绝对值
        $maxAbs = 0;
        foreach ($audioData as $sample) {
            $abs = abs($sample);
            if ($abs > $maxAbs) {
                $maxAbs = $abs;
            }
        }
        
        // 如果最大值为0，直接返回
        if ($maxAbs === 0) {
            return $audioData;
        }
        
        // 归一化
        $result = [];
        foreach ($audioData as $sample) {
            $result[] = $sample / $maxAbs;
        }
        
        return $result;
    }

    /**
     * 从音频文件读取数据
     *
     * @param string $audioFilePath 音频文件路径
     * @return array 音频数据
     */
    private function readAudioFile(string $audioFilePath): array
    {
        // 模拟读取音频文件的过程
        // 实际应该使用音频处理库来读取不同格式的音频文件
        
        if ($this->logger) {
            $this->logger->debug('读取音频文件', ['file' => $audioFilePath]);
        }
        
        // 返回模拟的音频数据
        return array_fill(0, 16000, 0); // 模拟1秒的静音
    }

    /**
     * 提取MFCC特征
     *
     * @param array $audioData 预处理后的音频数据
     * @return array MFCC特征
     */
    private function extractMfcc(array $audioData): array
    {
        if ($this->logger) {
            $this->logger->debug('提取MFCC特征');
        }
        
        // 帧分割
        $frames = $this->splitIntoFrames($audioData);
        
        // 应用窗函数
        $windowedFrames = $this->applyWindow($frames);
        
        // FFT变换
        $spectrums = $this->computeFft($windowedFrames);
        
        // 应用梅尔滤波器组
        $melFilterbankEnergies = $this->applyMelFilterbank($spectrums);
        
        // 对数变换
        $logMelFilterbankEnergies = $this->applyLog($melFilterbankEnergies);
        
        // 离散余弦变换(DCT)
        $mfccs = $this->applyDct($logMelFilterbankEnergies);
        
        // 提取最终特征（通常取前12-13个系数，加上能量项）
        $numCoeffs = $this->config['num_cepstral'] ?? 13;
        $features = $this->extractCoefficients($mfccs, $numCoeffs);
        
        return [
            'features' => $features,
            'feature_type' => 'mfcc',
            'num_frames' => count($features),
            'feature_dim' => count($features[0] ?? [])
        ];
    }

    /**
     * 提取滤波器组能量特征(Fbank)
     *
     * @param array $audioData 预处理后的音频数据
     * @return array Fbank特征
     */
    private function extractFbank(array $audioData): array
    {
        if ($this->logger) {
            $this->logger->debug('提取滤波器组能量特征');
        }
        
        // 帧分割
        $frames = $this->splitIntoFrames($audioData);
        
        // 应用窗函数
        $windowedFrames = $this->applyWindow($frames);
        
        // FFT变换
        $spectrums = $this->computeFft($windowedFrames);
        
        // 应用梅尔滤波器组
        $melFilterbankEnergies = $this->applyMelFilterbank($spectrums);
        
        // 对数变换
        $logMelFilterbankEnergies = $this->applyLog($melFilterbankEnergies);
        
        return [
            'features' => $logMelFilterbankEnergies,
            'feature_type' => 'fbank',
            'num_frames' => count($logMelFilterbankEnergies),
            'feature_dim' => count($logMelFilterbankEnergies[0] ?? [])
        ];
    }

    /**
     * 提取感知线性预测(PLP)特征
     *
     * @param array $audioData 预处理后的音频数据
     * @return array PLP特征
     */
    private function extractPlp(array $audioData): array
    {
        if ($this->logger) {
            $this->logger->debug('提取感知线性预测特征');
        }
        
        // PLP提取过程(简化版)
        // 实际应该实现完整的PLP算法
        
        // 返回模拟特征
        $numFrames = (int)(count($audioData) / ($this->config['window_step'] * $this->config['sample_rate'] / 1000));
        $featureDim = 13;
        $features = [];
        
        for ($i = 0; $i < $numFrames; $i++) {
            $features[$i] = array_fill(0, $featureDim, 0);
        }
        
        return [
            'features' => $features,
            'feature_type' => 'plp',
            'num_frames' => $numFrames,
            'feature_dim' => $featureDim
        ];
    }

    /**
     * 提取频谱图特征
     *
     * @param array $audioData 预处理后的音频数据
     * @return array 频谱图特征
     */
    private function extractSpectrogram(array $audioData): array
    {
        if ($this->logger) {
            $this->logger->debug('提取频谱图特征');
        }
        
        // 帧分割
        $frames = $this->splitIntoFrames($audioData);
        
        // 应用窗函数
        $windowedFrames = $this->applyWindow($frames);
        
        // FFT变换
        $spectrums = $this->computeFft($windowedFrames);
        
        // 计算功率谱
        $powerSpectrums = [];
        foreach ($spectrums as $spectrum) {
            $powerSpectrum = [];
            foreach ($spectrum as $bin) {
                $powerSpectrum[] = $bin * $bin;
            }
            $powerSpectrums[] = $powerSpectrum;
        }
        
        return [
            'features' => $powerSpectrums,
            'feature_type' => 'spectrogram',
            'num_frames' => count($powerSpectrums),
            'feature_dim' => count($powerSpectrums[0] ?? [])
        ];
    }

    /**
     * 提取原始波形特征
     *
     * @param array $audioData 预处理后的音频数据
     * @return array 原始波形特征
     */
    private function extractRawWaveform(array $audioData): array
    {
        if ($this->logger) {
            $this->logger->debug('提取原始波形特征');
        }
        
        // 帧分割
        $frames = $this->splitIntoFrames($audioData);
        
        return [
            'features' => $frames,
            'feature_type' => 'raw_waveform',
            'num_frames' => count($frames),
            'feature_dim' => count($frames[0] ?? [])
        ];
    }

    /**
     * 提取基频特征
     *
     * @param array $audioData 预处理后的音频数据
     * @return array 基频特征
     */
    private function extractPitch(array $audioData): array
    {
        if ($this->logger) {
            $this->logger->debug('提取基频特征');
        }
        
        // 基频提取(简化版)
        // 实际应该实现自相关或RAPT等基频提取算法
        
        // 帧分割
        $frames = $this->splitIntoFrames($audioData);
        
        // 模拟基频提取结果
        $pitchFeatures = [];
        foreach ($frames as $frame) {
            // 随机基频值(模拟)
            $pitchFeatures[] = [
                'f0' => rand(80, 400), // Hz
                'voiced_prob' => rand(0, 100) / 100.0
            ];
        }
        
        return [
            'features' => $pitchFeatures,
            'feature_type' => 'pitch',
            'num_frames' => count($pitchFeatures),
            'feature_dim' => 2 // f0和voiced_prob
        ];
    }

    /**
     * 将音频分割成帧
     *
     * @param array $audioData 音频数据
     * @return array 分帧后的数据
     */
    private function splitIntoFrames(array $audioData): array
    {
        // 计算帧长和帧移(采样点数)
        $windowSize = (int)($this->config['window_size'] * $this->config['sample_rate'] / 1000);
        $windowStep = (int)($this->config['window_step'] * $this->config['sample_rate'] / 1000);
        
        // 计算帧数
        $numFrames = (int)((count($audioData) - $windowSize) / $windowStep) + 1;
        
        // 分帧
        $frames = [];
        for ($i = 0; $i < $numFrames; $i++) {
            $startIdx = $i * $windowStep;
            $frames[$i] = array_slice($audioData, $startIdx, $windowSize);
        }
        
        return $frames;
    }

    /**
     * 应用窗函数
     *
     * @param array $frames 分帧后的数据
     * @return array 应用窗函数后的数据
     */
    private function applyWindow(array $frames): array
    {
        // 窗函数类型
        $windowType = $this->config['window_type'] ?? 'hamming';
        
        $windowedFrames = [];
        foreach ($frames as $frame) {
            $frameLength = count($frame);
            $window = $this->generateWindow($frameLength, $windowType);
            
            // 应用窗函数
            $windowedFrame = [];
            for ($i = 0; $i < $frameLength; $i++) {
                $windowedFrame[$i] = $frame[$i] * $window[$i];
            }
            
            $windowedFrames[] = $windowedFrame;
        }
        
        return $windowedFrames;
    }

    /**
     * 生成窗函数
     *
     * @param int $length 窗长度
     * @param string $type 窗类型
     * @return array 窗函数
     */
    private function generateWindow(int $length, string $type): array
    {
        $window = [];
        
        switch ($type) {
            case 'hamming':
                for ($i = 0; $i < $length; $i++) {
                    $window[$i] = 0.54 - 0.46 * cos(2 * M_PI * $i / ($length - 1));
                }
                break;
                
            case 'hanning':
                for ($i = 0; $i < $length; $i++) {
                    $window[$i] = 0.5 * (1 - cos(2 * M_PI * $i / ($length - 1)));
                }
                break;
                
            case 'blackman':
                for ($i = 0; $i < $length; $i++) {
                    $window[$i] = 0.42 - 0.5 * cos(2 * M_PI * $i / ($length - 1)) + 0.08 * cos(4 * M_PI * $i / ($length - 1));
                }
                break;
                
            case 'rectangular':
                // 矩形窗
                $window = array_fill(0, $length, 1.0);
                break;
                
            default:
                // 默认使用汉明窗
                for ($i = 0; $i < $length; $i++) {
                    $window[$i] = 0.54 - 0.46 * cos(2 * M_PI * $i / ($length - 1));
                }
        }
        
        return $window;
    }
    
    /**
     * 计算FFT
     *
     * @param array $windowedFrames 加窗后的帧
     * @return array FFT结果
     */
    private function computeFft(array $windowedFrames): array
    {
        // 模拟FFT计算
        // 实际应该使用FFT库或算法实现
        
        $spectrums = [];
        foreach ($windowedFrames as $frame) {
            // 生成模拟的频谱
            $frameLength = count($frame);
            $fftSize = $this->config['fft_size'] ?? (1 << (int)ceil(log($frameLength, 2)));
            
            // 模拟频谱(仅用于演示)
            $spectrum = array_fill(0, $fftSize / 2 + 1, 0);
            $spectrums[] = $spectrum;
        }
        
        return $spectrums;
    }

    /**
     * 应用梅尔滤波器组
     *
     * @param array $spectrums FFT频谱
     * @return array 滤波器组能量
     */
    private function applyMelFilterbank(array $spectrums): array
    {
        // 模拟梅尔滤波器组处理
        // 实际应该创建和应用真实的梅尔滤波器组
        
        $numFilters = $this->config['num_filters'] ?? 26;
        $filterbankEnergies = [];
        
        foreach ($spectrums as $spectrum) {
        // 生成模拟的滤波器组能量
            $filterbankEnergies[] = array_fill(0, $numFilters, 0);
        }
        
        return $filterbankEnergies;
    }

    /**
     * 应用对数变换
     *
     * @param array $melFilterbankEnergies 梅尔滤波器组能量
     * @return array 对数变换结果
     */
    private function applyLog(array $melFilterbankEnergies): array
    {
        // 应用对数变换
        $logEnergies = [];
        
        foreach ($melFilterbankEnergies as $frame) {
            $logFrame = [];
            foreach ($frame as $energy) {
                // 防止对数为负无穷
                $energy = max($energy, 1e-10);
                $logFrame[] = log($energy);
            }
            $logEnergies[] = $logFrame;
        }
        
        return $logEnergies;
    }

    /**
     * 应用离散余弦变换
     *
     * @param array $logMelFilterbankEnergies 对数梅尔滤波器组能量
     * @return array DCT结果
     */
    private function applyDct(array $logMelFilterbankEnergies): array
    {
        // 模拟DCT变换
        // 实际应该实现真实的DCT变换
        
        $dctResults = [];
        
        foreach ($logMelFilterbankEnergies as $frame) {
            $numFilters = count($frame);
            $dctFrame = array_fill(0, $numFilters, 0);
            $dctResults[] = $dctFrame;
        }
        
        return $dctResults;
    }

    /**
     * 提取系数
     *
     * @param array $mfccs 所有MFCC系数
     * @param int $numCoeffs 要提取的系数数量
     * @return array 提取的系数
     */
    private function extractCoefficients(array $mfccs, int $numCoeffs): array
    {
        $features = [];
        
        foreach ($mfccs as $frame) {
            $features[] = array_slice($frame, 0, $numCoeffs);
        }
        
        return $features;
    }
    
    /**
     * 获取配置
     *
     * @return array 提取器配置
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * 设置配置
     *
     * @param array $config 新的配置
     */
    public function setConfig(array $config): void
    {
        $this->validateConfig($config);
        $this->config = $config;
    }
} 