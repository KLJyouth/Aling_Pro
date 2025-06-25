<?php
declare(strict_types=1];

/**
 * �ļ�����VoiceIdentifier.php
 * ��������������ʶ���� - �ṩ����ʶ���˵������֤����
 * ����ʱ�䣺2025-01-XX
 * ����޸ģ�2025-01-XX
 * �汾��1.0.0
 * 
 * @package AlingAi\AI\Engines\Speech
 * @author AlingAi Team
 * @license MIT
 */

namespace AlingAi\AI\Engines\Speech;

use Exception;
use InvalidArgumentException;

/**
 * ����ʶ������
 * 
 * �ṩ����ʶ���˵������֤�Ĺ���
 */
class VoiceIdentifier
{
    /**
     * ���ò���
     */
    private array $config;
    
    /**
     * ��ǰʹ�õ�����ʶ��ģ��
     */
    private ?object $model = null;
    
    /**
     * ���캯��
     * 
     * @param array $config ���ò���
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->getDefaultConfig(), $config];
        $this->initializeModel(];
    }
    
    /**
     * ��ȡĬ������
     * 
     * @return array Ĭ������
     */
    private function getDefaultConfig(): array
    {
        return [
            'model' => 'default',  // ����ʶ��ģ�ͣ�default, high_accuracy, fast
            'sample_rate' => 16000,  // �����ʣ�Hz��
            'embedding_size' => 192,  // ������������ά��
            'min_audio_length' => 3.0,  // ��С��Ƶ���ȣ��룩
            'min_confidence' => 0.6,  // ��С���Ŷ���ֵ
            'detect_replay' => true,  // �Ƿ����طŹ���
            'speaker_diarization' => false,  // �Ƿ����˵���˷���
            'max_speakers' => 3,  // ���˵��������
            'api_key' => '',  // API��Կ�����ʹ���ⲿ����
            'api_endpoint' => '',  // API�˵㣨���ʹ���ⲿ����
            'use_local_model' => true,  // �Ƿ�ʹ�ñ���ģ��
            'local_model_path' => '',  // ����ģ��·��
            'storage_path' => './storage/voice_prints',  // ���ƴ洢·��
            'cache_results' => false,  // �Ƿ񻺴�ʶ����
            'cache_dir' => './cache',  // ����Ŀ¼
            'log_level' => 'info'  // ��־����debug, info, warning, error
        ];
    }
    
    /**
     * ��ʼ������ʶ��ģ��
     * 
     * @throws Exception ģ�ͼ���ʧ��ʱ�׳��쳣
     */
    private function initializeModel(): void
    {
        // ģ��ģ�ͳ�ʼ������
        try {
            if ($this->config['use_local_model']) {
                // ���ر���ģ��
                $modelPath = !empty($this->config['local_model_path']) 
                    ? $this->config['local_model_path'] 
                    : __DIR__ . '/models/voiceprint_' . $this->config['model'] . '.onnx';
                
                // ��ʵ��ʵ���У���������ģ���ļ�����ʼ��
                // $this->model = new VoiceprintModel($modelPath];
                $this->model = (object)['name' => 'VoiceprintModel', 'path' => $modelPath];
            } else {
                // ʹ��API����
                if (empty($this->config['api_key']) || empty($this->config['api_endpoint'])) {
                    throw new InvalidArgumentException('ʹ���ⲿAPI����ʱ�����ṩapi_key��api_endpoint'];
                }
                
                // ��ʵ��ʵ���У�������ʼ��API�ͻ���
                // $this->model = new VoiceprintApiClient($this->config['api_endpoint'],  $this->config['api_key']];
                $this->model = (object)[
                    'name' => 'VoiceprintApiClient', 
                    'endpoint' => $this->config['api_endpoint']
                ];
            }
        } catch (Exception $e) {
            throw new Exception('����ʶ��ģ�ͳ�ʼ��ʧ�ܣ�' . $e->getMessage()];
        }
    }
    
    /**
     * ����Ƶ��ȡ������������
     * 
     * @param string $audioPath ��Ƶ�ļ�·��
     * @return array ������������
     * @throws InvalidArgumentException ��Ƶ�ļ���Чʱ�׳��쳣
     * @throws Exception ������ȡʧ��ʱ�׳��쳣
     */
    public function extractVoicePrint(string $audioPath): array
    {
        // ��֤��Ƶ�ļ�
        $this->validateAudio($audioPath];
        
        // ��ȡ��������
        try {
            // ��ʵ��ʵ���У���������ģ�ʹ�����Ƶ����ȡ����
            // ģ��������ȡ����
            // return $this->model->extractFeatures($audioPath];
            
            // ����ģ������
            $embeddingSize = $this->config['embedding_size'];
            $embedding = [];
            
            // �����������������ʵ��Ӧ���л�����ʵ������������
            for ($i = 0; $i < $embeddingSize; $i++) {
                $embedding[] = (float) mt_rand(-100, 100) / 100;
            }
            
            // ��һ����������
            $norm = sqrt(array_sum(array_map(function($x) { return $x * $x; }, $embedding))];
            $embedding = array_map(function($x) use ($norm) { return $x / $norm; }, $embedding];
            
            return [
                'embedding' => $embedding,
                'dimensionality' => $embeddingSize,
                'audio_info' => [
                    'duration' => 5.2,  // �ٶ���Ƶ���ȣ��룩
                    'sample_rate' => $this->config['sample_rate']
                ]
            ];
        } catch (Exception $e) {
            throw new Exception('����������ȡʧ�ܣ�' . $e->getMessage()];
        }
    }
    
    /**
     * ��֤��Ƶ�ļ�
     * 
     * @param string $audioPath ��Ƶ�ļ�·��
     * @throws InvalidArgumentException ��Ƶ�ļ���Чʱ�׳��쳣
     */
    private function validateAudio(string $audioPath): void
    {
        if (!file_exists($audioPath)) {
            throw new InvalidArgumentException('��Ƶ�ļ������ڣ�' . $audioPath];
        }
        
        // ����ļ���С
        $fileSize = filesize($audioPath];
        if ($fileSize <= 0) {
            throw new InvalidArgumentException('��Ƶ�ļ�Ϊ�գ�' . $audioPath];
        }
        
        // ��ʵ��Ӧ���У����������Ƶ��ʽ�������ʡ���������
        // �����ܽ�����ƵԤ������ʽת�����ز����ȣ�
    }
    
    /**
     * ע������
     * 
     * @param string $audioPath ��Ƶ�ļ�·��
     * @param string $speakerId ˵����ID
     * @param array $metadata Ԫ���ݣ���ѡ��
     * @return bool ע���Ƿ�ɹ�
     * @throws Exception ע��ʧ��ʱ�׳��쳣
     */
    public function enrollSpeaker(string $audioPath, string $speakerId, array $metadata = []): bool
    {
        // ��ȡ��������
        $voicePrint = $this->extractVoicePrint($audioPath];
        
        // �洢����
        try {
            $voicePrintData = [
                'speaker_id' => $speakerId,
                'embedding' => $voicePrint['embedding'], 
                'metadata' => $metadata,
                'created_at' => date('Y-m-d H:i:s'],
                'audio_info' => $voicePrint['audio_info']
            ];
            
            // ȷ���洢Ŀ¼����
            $storagePath = $this->config['storage_path'];
            if (!is_dir($storagePath) && !mkdir($storagePath, 0755, true)) {
                throw new Exception('�޷��������ƴ洢Ŀ¼��' . $storagePath];
            }
            
            // ������������
            $filePath = $storagePath . '/' . $speakerId . '.json';
            $saved = file_put_contents($filePath, json_encode($voicePrintData, JSON_PRETTY_PRINT)];
            
            return $saved !== false;
        } catch (Exception $e) {
            throw new Exception('����ע��ʧ�ܣ�' . $e->getMessage()];
        }
    }
    
    /**
     * ��֤˵�������
     * 
     * @param string $audioPath ����֤����Ƶ�ļ�·��
     * @param string $speakerId ��ע���˵����ID
     * @return array ��֤���������ƥ��������Ƿ�ͨ����֤
     * @throws InvalidArgumentException ������Чʱ�׳��쳣
     * @throws Exception ��֤���̳���ʱ�׳��쳣
     */
    public function verifySpeaker(string $audioPath, string $speakerId): array
    {
        // ��ȡ����֤��Ƶ����������
        $testVoicePrint = $this->extractVoicePrint($audioPath];
        
        // ������ע�������
        $enrolledVoicePrint = $this->loadEnrolledVoicePrint($speakerId];
        
        // ����طŹ�����������ã�
        if ($this->config['detect_replay']) {
            $isReplay = $this->detectReplayAttack($audioPath];
            if ($isReplay) {
                return [
                    'match' => false,
                    'score' => 0.0,
                    'message' => '��⵽�طŹ���',
                    'is_replay' => true
                ];
            }
        }
        
        // �������ƶ�
        $score = $this->calculateSimilarity(
            $testVoicePrint['embedding'],  
            $enrolledVoicePrint['embedding']
        ];
        
        // �ж��Ƿ�ƥ��
        $isMatch = $score >= $this->config['min_confidence'];
        
        return [
            'match' => $isMatch,
            'score' => $score,
            'message' => $isMatch ? '������֤ͨ��' : '���Ʋ�ƥ��',
            'is_replay' => false
        ];
    }
    
    /**
     * ������ע�������
     * 
     * @param string $speakerId ˵����ID
     * @return array ��������
     * @throws InvalidArgumentException ˵����δע��ʱ�׳��쳣
     */
    private function loadEnrolledVoicePrint(string $speakerId): array
    {
        $filePath = $this->config['storage_path'] . '/' . $speakerId . '.json';
        
        if (!file_exists($filePath)) {
            throw new InvalidArgumentException('˵����δע�᣺' . $speakerId];
        }
        
        $data = json_decode(file_get_contents($filePath], true];
        
        if ($data === null) {
            throw new InvalidArgumentException('����������Ч��' . $speakerId];
        }
        
        return $data;
    }
    
    /**
     * ��������������������֮������ƶ�
     * 
     * @param array $embedding1 ��һ����������
     * @param array $embedding2 �ڶ�����������
     * @return float ���ƶȷ�����0-1֮�䣩
     */
    private function calculateSimilarity(array $embedding1, array $embedding2): float
    {
        // ȷ������������ά����ͬ
        if (count($embedding1) != count($embedding2)) {
            throw new InvalidArgumentException('��������ά�Ȳ�ƥ��'];
        }
        
        // �����������ƶ�
        $dotProduct = 0;
        $norm1 = 0;
        $norm2 = 0;
        
        for ($i = 0; $i < count($embedding1]; $i++) {
            $dotProduct += $embedding1[$i] * $embedding2[$i];
            $norm1 += $embedding1[$i] * $embedding1[$i];
            $norm2 += $embedding2[$i] * $embedding2[$i];
        }
        
        $norm1 = sqrt($norm1];
        $norm2 = sqrt($norm2];
        
        if ($norm1 == 0 || $norm2 == 0) {
            return 0.0;
        }
        
        // �������ƶȵķ�Χ��[-1, 1]������ת��Ϊ[0, 1]
        $similarity = $dotProduct / ($norm1 * $norm2];
        return ($similarity + 1) / 2;
    }
    
    /**
     * ����طŹ���
     * 
     * @param string $audioPath ��Ƶ�ļ�·��
     * @return bool �Ƿ��⵽�طŹ���
     */
    private function detectReplayAttack(string $audioPath): bool
    {
        // ʵ��Ӧ���У������������ӵ��طŹ�������㷨
        // ���ܻ������Ƶ��Ƶ�����ԡ�����������˵���˻��Ե�
        
        // ģ��ʵ�֣�������ؽ����ʵ��Ӧ���в�Ӧ����������
        return mt_rand(0, 100) < 5; // 5% �ĸ��ʼ��Ϊ�طŹ���
    }
    
    /**
     * ʶ��δ֪˵����
     * 
     * @param string $audioPath ��Ƶ�ļ�·��
     * @return array ʶ�������������ܵ�˵����ID�б���ƥ�����
     * @throws Exception ʶ����̳���ʱ�׳��쳣
     */
    public function identifySpeaker(string $audioPath): array
    {
        // ��ȡ��������
        $testVoicePrint = $this->extractVoicePrint($audioPath];
        
        // ��ȡ������ע���˵����
        $enrolledSpeakers = $this->getAllEnrolledSpeakers(];
        
        if (empty($enrolledSpeakers)) {
            return [
                'identified' => false,
                'message' => 'û����ע���˵����',
                'candidates' => []
            ];
        }
        
        // ������ÿ����ע��˵���˵����ƶ�
        $results = [];
        foreach ($enrolledSpeakers as $speaker) {
            $score = $this->calculateSimilarity(
                $testVoicePrint['embedding'], 
                $speaker['embedding']
            ];
            
            $results[] = [
                'speaker_id' => $speaker['speaker_id'], 
                'score' => $score,
                'metadata' => $speaker['metadata'] ?? []
            ];
        }
        
        // �����ƶȷ�������
        usort($results, function($a, $b) {
            return $b['score'] <=> $a['score'];
        }];
        
        // �ж��Ƿ�ʶ��˵����
        $bestMatch = $results[0];
        $identified = $bestMatch['score'] >= $this->config['min_confidence'];
        
        return [
            'identified' => $identified,
            'best_match' => $bestMatch,
            'message' => $identified ? '��ʶ��˵����' : 'δ��ʶ��˵����',
            'candidates' => array_slice($results, 0, 5) // ����ǰ5������ܵĽ��
        ];
    }
    
    /**
     * ��ȡ������ע���˵����
     * 
     * @return array ��ע��˵�����б�
     */
    private function getAllEnrolledSpeakers(): array
    {
        $storagePath = $this->config['storage_path'];
        $speakers = [];
        
        if (!is_dir($storagePath)) {
            return $speakers;
        }
        
        $files = glob($storagePath . '/*.json'];
        foreach ($files as $file) {
            try {
                $data = json_decode(file_get_contents($file], true];
                if ($data && isset($data['speaker_id']) && isset($data['embedding'])) {
                    $speakers[] = $data;
                }
            } catch (Exception $e) {
                // �����޷��������ļ�
                continue;
            }
        }
        
        return $speakers;
    }
    
    /**
     * ˵���˷��루��Ϊ���˵���ˣ�
     * 
     * @param string $audioPath ��Ƶ�ļ�·��
     * @return array ������������ÿ�ε�˵����ID��ʱ���
     * @throws Exception ������̳���ʱ�׳��쳣
     */
    public function diarizeSpeakers(string $audioPath): array
    {
        if (!$this->config['speaker_diarization']) {
            throw new InvalidArgumentException('˵���˷��빦��δ����'];
        }
        
        // ��֤��Ƶ�ļ�
        $this->validateAudio($audioPath];
        
        // ģ��˵���˷������
        // ��ʵ��Ӧ���У�������漰���ӵ������ֶκ;����㷨
        
        $segments = [];
        $audioDuration = 120; // ģ��120�����Ƶ
        $currentTime = 0;
        
        // ����ģ��ֶ�����
        while ($currentTime < $audioDuration) {
            $segmentDuration = mt_rand(3, 15]; // ÿ��3-15��
            $speakerId = 'speaker_' . mt_rand(1, $this->config['max_speakers']];
            
            $segments[] = [
                'start_time' => $currentTime,
                'end_time' => $currentTime + $segmentDuration,
                'duration' => $segmentDuration,
                'speaker_id' => $speakerId
            ];
            
            $currentTime += $segmentDuration;
        }
        
        return [
            'audio_path' => $audioPath,
            'num_speakers' => min($this->config['max_speakers'],  3], // ����ʶ�𵽵�˵��������
            'segments' => $segments,
            'audio_duration' => $audioDuration
        ];
    }
    
    /**
     * ��������
     * 
     * @param array $newConfig ������
     * @return bool �����Ƿ�ɹ�
     */
    public function updateConfig(array $newConfig): bool
    {
        $oldConfig = $this->config;
        $this->config = array_merge($this->config, $newConfig];
        
        // ���ģ����ص����÷����仯�����³�ʼ��ģ��
        $modelConfigKeys = ['model', 'use_local_model', 'local_model_path', 'api_key', 'api_endpoint'];
        $needReinitialize = false;
        
        foreach ($modelConfigKeys as $key) {
            if (isset($newConfig[$key]) && $newConfig[$key] !== $oldConfig[$key]) {
                $needReinitialize = true;
                break;
            }
        }
        
        if ($needReinitialize) {
            try {
                $this->initializeModel(];
            } catch (Exception $e) {
                // �����ʼ��ʧ�ܣ����˵�ԭ����
                $this->config = $oldConfig;
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * ��ȡ��ǰ����
     * 
     * @return array ��ǰ����
     */
    public function getConfig(): array
    {
        return $this->config;
    }
    
    /**
     * ɾ��ע�������
     * 
     * @param string $speakerId ˵����ID
     * @return bool ɾ���Ƿ�ɹ�
     */
    public function deleteSpeaker(string $speakerId): bool
    {
        $filePath = $this->config['storage_path'] . '/' . $speakerId . '.json';
        
        if (!file_exists($filePath)) {
            return false;
        }
        
        return unlink($filePath];
    }
    
    /**
     * �������л���
     * 
     * @return bool �����Ƿ�ɹ�
     */
    public function clearCache(): bool
    {
        if (!$this->config['cache_results'] || !is_dir($this->config['cache_dir'])) {
            return true;
        }
        
        $files = glob($this->config['cache_dir'] . '/*'];
        $success = true;
        
        foreach ($files as $file) {
            if (is_file($file)) {
                $success &= unlink($file];
            }
        }
        
        return $success;
    }
}


