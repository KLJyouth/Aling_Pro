<?php
/**
 * �ļ�����EntityExtractor.php
 * ����������ʵ����ȡ�� - ���ı�����ȡʵ��
 * ����ʱ�䣺2025-01-XX
 * ����޸ģ�2025-01-XX
 * �汾��1.0.0
 * 
 * @package AlingAi\AI\Engines\KnowledgeGraph
 * @author AlingAi Team
 * @license MIT
 */

declare(strict_types=1];

namespace AlingAi\AI\Engines\KnowledgeGraph;

use Exception;
use InvalidArgumentException;
use AlingAi\AI\Engines\NLP\NERModel;
use AlingAi\AI\Engines\NLP\TokenizerInterface;
use AlingAi\AI\Engines\NLP\UniversalTokenizer;

/**
 * ʵ����ȡ��
 * 
 * ���ı�����ȡʵ�壬֧�ֶ���ʵ�����ͺ�����
 */
class EntityExtractor
{
    /**
     * ���ò���
     */
    private array $config;
    
    /**
     * ����ʵ��ʶ��ģ��
     */
    private ?NERModel $nerModel = null;
    
    /**
     * �ִ���
     */
    private ?TokenizerInterface $tokenizer = null;
    
    /**
     * ʵ������ӳ��
     */
    private array $entityTypeMap = [];


    /**
     * ���캯��
     * 
     * @param array $config ���ò���
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->getDefaultConfig(), $config];
        $this->initializeComponents(];
        $this->initializeEntityTypeMap(];
    }
    
    /**
     * ��ȡĬ������
     * 
     * @return array Ĭ������
     */
    private function getDefaultConfig(): array
    {
        return [
            'confidence_threshold' => 0.7,
            'max_entity_length' => 50,
            'entity_types' => [
                'PERSON', 'ORGANIZATION', 'LOCATION', 'DATE', 'TIME',
                'MONEY', 'PERCENT', 'PRODUCT', 'EVENT', 'WORK_OF_ART'
            ], 
            'enable_entity_linking' => true,
            'enable_coreference_resolution' => true
        ];
    }
    
    /**
     * ��ʼ�����
     */
    private function initializeComponents(): void
    {
        $this->nerModel = new NERModel(];
        $this->tokenizer = new UniversalTokenizer(];
    }
    
    /**
     * ��ʼ��ʵ������ӳ��
     */
    private function initializeEntityTypeMap(): void
    {
        $this->entityTypeMap = [
            'PERSON' => 'Person',
            'ORGANIZATION' => 'Organization',
            'LOCATION' => 'Location',
            'DATE' => 'Date',
            'TIME' => 'Time',
            'MONEY' => 'Money',
            'PERCENT' => 'Percent',
            'PRODUCT' => 'Product',
            'EVENT' => 'Event',
            'WORK_OF_ART' => 'WorkOfArt'
        ];
    }

    
    /**
     * ���ı�����ȡʵ��
     * 
     * @param string $text �����ı�
     * @param array $options ��ȡѡ��
     * @return array ��ȡ��ʵ��
     * @throws InvalidArgumentException
     */
    public function extract(string $text, array $options = []): array
    {
        // ��֤�ı�
        if (empty($text)) {
            throw new InvalidArgumentException('�ı�����Ϊ��'];
        }
        
        // ����ѡ��
        $options = array_merge($this->config, $options];
        
        // �ִ�
        $tokens = $this->tokenizer->tokenize($text];
        
        // ����ʵ��ʶ��
        $entities = $this->nerModel->recognize($tokens];
        
        // ���˺�ת��ʵ��
        $filteredEntities = $this->filterAndTransformEntities($entities, $options];
        
        // ʵ������
        if ($options['enable_entity_linking']) {
            $filteredEntities = $this->linkEntities($filteredEntities, $options];
        }
        
        // ��ָ����
        if ($options['enable_coreference_resolution']) {
            $filteredEntities = $this->resolveCoreferenceEntities($filteredEntities, $text, $options];
        }
        
        return $filteredEntities;
    }

    
    /**
     * ���˺�ת��ʵ��
     * 
     * @param array $entities ʵ���б�
     * @param array $options ѡ��
     * @return array ���˺�ת�����ʵ��
     */
    private function filterAndTransformEntities(array $entities, array $options): array
    {
        $result = [];
        $confidenceThreshold = $options['confidence_threshold'];
        $maxEntityLength = $options['max_entity_length'];
        $allowedEntityTypes = $options['entity_types'];
        
        foreach ($entities as $entity) {
            // ���˵����Ŷ�ʵ��
            if ($entity['confidence'] < $confidenceThreshold) {
                continue;
            }
            
            // ���˹�����ʵ��
            if (mb_strlen($entity['text'],  'UTF-8') > $maxEntityLength) {
                continue;
            }
            
            // ���˲������������б��е�ʵ��
            if (!in_[$entity['type'],  $allowedEntityTypes)) {
                continue;
            }
            
            // ת��ʵ������
            $entityType = $this->mapEntityType($entity['type']];
            
            // ����ʵ�����
            $result[] = [
                'id' => $this->generateEntityId($entity],
                'text' => $entity['text'], 
                'type' => $entityType,
                'confidence' => $entity['confidence'], 
                'start_pos' => $entity['start_pos'], 
                'end_pos' => $entity['end_pos'], 
                'metadata' => [
                    'source' => 'text_extraction',
                    'extraction_time' => time()
                ], 
                'attributes' => $this->extractEntityAttributes($entity)
            ];
        }
        
        return $result;
    }

    
    /**
     * ���˺�ת��ʵ��
     * 
     * @param array $entities ʵ���б�
     * @param array $options ѡ��
     * @return array ���˺�ת�����ʵ��
     */
    private function filterAndTransformEntities(array $entities, array $options): array
    {
        $result = [];
        $confidenceThreshold = $options['confidence_threshold'];
        $maxEntityLength = $options['max_entity_length'];
        $allowedEntityTypes = $options['entity_types'];
        
        foreach ($entities as $entity) {
            // ���˵����Ŷ�ʵ��
            if ($entity['confidence'] < $confidenceThreshold) {
                continue;
            }
            
            // ���˹�����ʵ��
            if (mb_strlen($entity['text'],  'UTF-8') > $maxEntityLength) {
                continue;
            }
            
            // ���˲������������б��е�ʵ��
            if (!in_[$entity['type'],  $allowedEntityTypes)) {
                continue;
            }
            
            // ת��ʵ������
            $entityType = $this->mapEntityType($entity['type']];
            
            // ����ʵ�����
            $result[] = [
                'id' => $this->generateEntityId($entity],
                'text' => $entity['text'], 
                'type' => $entityType,
                'confidence' => $entity['confidence'], 
                'start_pos' => $entity['start_pos'], 
                'end_pos' => $entity['end_pos'], 
                'metadata' => [
                    'source' => 'text_extraction',
                    'extraction_time' => time()
                ], 
                'attributes' => $this->extractEntityAttributes($entity)
            ];
        }
        
        return $result;
    }

    /**
     * ӳ��ʵ������
     * 
     * @param string $nerType NERʵ������
     * @return string ӳ����ʵ������
     */
    private function mapEntityType(string $nerType): string
    {
        return $this->entityTypeMap[$nerType] ?? 'Unknown';
    }
    
    /**
     * ����ʵ��ID
     * 
     * @param array $entity ʵ��
     * @return string ʵ��ID
     */
    private function generateEntityId(array $entity): string
    {
        return md5($entity['text'] . $entity['type'] . $entity['start_pos'] . $entity['end_pos']];
    }
    
    /**
     * ��ȡʵ������
     * 
     * @param array $entity ʵ��
     * @return array ʵ������
     */
    private function extractEntityAttributes(array $entity): array
    {
        $attributes = [];
        
        // ����ʵ��������ȡ�ض�����
        switch ($entity['type']) {
            case 'PERSON':
                // ������ȡ�������ա�������Ϣ
                break;
            case 'ORGANIZATION':
                // ������ȡ��֯���������͡���ҵ����Ϣ
                break;
            case 'LOCATION':
                // ������ȡ�ص�ľ�γ�ȡ��������ҵ���Ϣ
                break;
            case 'DATE':
                // ������ȡ���ڵ��ꡢ�¡��յ���Ϣ
                if (isset($entity['normalized_value'])) {
                    $attributes['normalized_date'] = $entity['normalized_value'];
                }
                break;
            case 'TIME':
                // ������ȡʱ���Сʱ�����ӵ���Ϣ
                if (isset($entity['normalized_value'])) {
                    $attributes['normalized_time'] = $entity['normalized_value'];
                }
                break;
        }
        
        return $attributes;
    }

    /**
     * �ж�����ʵ���Ƿ���ڹ�ָ��ϵ
     * 
     * @param array $entity1 ��һ��ʵ��
     * @param array $entity2 �ڶ���ʵ��
     * @param string $text ԭʼ�ı�
     * @return bool �Ƿ���ڹ�ָ��ϵ
     */
    private function areCorefEntities(array $entity1, array $entity2, string $text): bool
    {
        // ʵ�ֹ�ָ��ϵ�ж��߼�
        // �������ʹ�ø����ӵ��㷨������ڹ�������ѧϰ�ķ���
        // ��ʵ�֣�����ı����ƶȺ�λ�ù�ϵ
        
        // �������ʵ���ı���ȫ��ͬ�������ǹ�ָ
        if ($entity1['text'] === $entity2['text']) {
            return true;
        }
        
        // ���һ��ʵ������һ�����Ӵ��������ǹ�ָ
        if (stripos($entity1['text'],  $entity2['text']) !== false || 
            stripos($entity2['text'],  $entity1['text']) !== false) {
            return true;
        }
        
        // �����ӵĹ�ָ����߼�����������ʵ��
        // ���磬�����ʺ�����֮��Ĺ�ϵ������ʹ���ⲿ����
        
        return false;
    }
}

