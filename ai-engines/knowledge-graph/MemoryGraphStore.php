<?php
/**
 * �ļ�����MemoryGraphStore.php
 * �����������ڴ�֪ʶͼ�״洢ʵ��
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

/**
 * �ڴ�֪ʶͼ�״洢ʵ��
 * 
 * ʹ���ڴ�����洢֪ʶͼ�����ݣ������ڲ��Ժ�С��ģӦ��
 */
class MemoryGraphStore implements GraphStoreInterface
{
    /**
     * ʵ��洢
     * @var array
     */
    private array $entities = [];
    
    /**
     * ��ϵ�洢
     * @var array
     */
    private array $relations = [];
    
    /**
     * ���캯��
     * 
     * @param array $data ��ʼ����
     */
    public function __construct(array $data = []]
    {
        if (isset($data['entities']]) {
            $this->entities = $data['entities'];
        }
        
        if (isset($data['relations']]) {
            $this->relations = $data['relations'];
        }
    }
    
    /**
     * ��ȡʵ��ͨ��ID
     * 
     * @param string $entityId ʵ��ID
     * @return array|null ʵ�����ݣ�����������򷵻�null
     */
    public function getEntityById(string $entityId): ?array
    {
        return $this->entities[$entityId] ?? null;
    }
    
    /**
     * ��ȡ����ʵ��
     * 
     * @return array ����ʵ����б�
     */
    public function getAllEntities(): array
    {
        return array_values($this->entities];
    }
    
    /**
     * ��ȡ��ָ��ʵ����ָ����ϵ��ʵ���б�
     * 
     * @param string $entityId ʵ��ID
     * @param string $relationType ��ϵ����
     * @return array ���ʵ���б�
     */
    public function getRelatedEntities(string $entityId, string $relationType): array
    {
        $result = [];
        
        foreach ($this->relations as $relation) {
            if ($relation['source_id'] === $entityId && $relation['type'] === $relationType) {
                $entity = $this->getEntityById($relation['target_id']];
                if ($entity) {
                    // ����ϵ���Ŷ���ӵ�ʵ����
                    $entity['confidence'] = $relation['confidence'];
                    $result[] = $entity;
                }
            }
        }
        
        return $result;
    }
    
    /**
     * ��ȡ��ָ��ʵ����ָ����ϵ��ʵ���б�����
     * 
     * @param string $relationType ��ϵ����
     * @param string $targetEntityId Ŀ��ʵ��ID
     * @return array ���ʵ���б�
     */
    public function getEntitiesWithRelation(string $relationType, string $targetEntityId): array
    {
        $result = [];
        
        foreach ($this->relations as $relation) {
            if ($relation['target_id'] === $targetEntityId && $relation['type'] === $relationType) {
                $entity = $this->getEntityById($relation['source_id']];
                if ($entity) {
                    // ����ϵ���Ŷ���ӵ�ʵ����
                    $entity['confidence'] = $relation['confidence'];
                    $result[] = $entity;
                }
            }
        }
        
        return $result;
    }
    
    /**
     * ��ȡ����ʵ��֮��Ĺ�ϵ
     * 
     * @param string $sourceEntityId Դʵ��ID
     * @param string $targetEntityId Ŀ��ʵ��ID
     * @param string|null $relationType ��ϵ���ͣ����Ϊnull�򷵻ص�һ���ҵ��Ĺ�ϵ
     * @return array|null ��ϵ���ݣ�����������򷵻�null
     */
    public function getRelationBetween(string $sourceEntityId, string $targetEntityId, ?string $relationType = null): ?array
    {
        foreach ($this->relations as $relation) {
            if ($relation['source_id'] === $sourceEntityId && $relation['target_id'] === $targetEntityId) {
                if ($relationType === null || $relation['type'] === $relationType) {
                    return $relation;
                }
            }
        }
        
        return null;
    }
    
    /**
     * ��ȡ����ʵ��֮������й�ϵ
     * 
     * @param string $sourceEntityId Դʵ��ID
     * @param string $targetEntityId Ŀ��ʵ��ID
     * @return array ��ϵ�����б�
     */
    public function getRelationsBetween(string $sourceEntityId, string $targetEntityId): array
    {
        $result = [];
        
        foreach ($this->relations as $relation) {
            if ($relation['source_id'] === $sourceEntityId && $relation['target_id'] === $targetEntityId) {
                $result[] = $relation;
            }
        }
        
        return $result;
    }
    
    /**
     * ���ʵ��
     * 
     * @param array $entityData ʵ������
     * @return bool �Ƿ���ӳɹ�
     */
    public function addEntity(array $entityData): bool
    {
        if (!isset($entityData['id']]) {
            return false;
        }
        
        $this->entities[$entityData['id']] = $entityData;
        return true;
    }
    
    /**
     * ����ʵ��
     * 
     * @param string $entityId ʵ��ID
     * @param array $data ���µ�����
     * @return bool �Ƿ���³ɹ�
     */
    public function updateEntity(string $entityId, array $data): bool
    {
        if (!isset($this->entities[$entityId]]) {
            return false;
        }
        
        $this->entities[$entityId] = array_merge($this->entities[$entityId],  $data];
        return true;
    }
    
    /**
     * ɾ��ʵ��
     * 
     * @param string $entityId ʵ��ID
     * @return bool �Ƿ�ɾ���ɹ�
     */
    public function deleteEntity(string $entityId): bool
    {
        if (!isset($this->entities[$entityId]]) {
            return false;
        }
        
        unset($this->entities[$entityId]];
        
        // ͬʱɾ����صĹ�ϵ
        foreach ($this->relations as $relation) {
            if ($relation['source_id'] === $entityId || $relation['target_id'] === $entityId) {
                unset($this->relations[$relation['id']]];
            }
        }
        
        // ����������ϵ����
        $this->relations = array_values($this->relations];
        
        return true;
    }
    
    /**
     * ��ӹ�ϵ
     * 
     * @param array $relationData ��ϵ����
     * @return bool �Ƿ���ӳɹ�
     */
    public function addRelation(array $relationData): bool
    {
        if (!isset($relationData['id']] || !isset($relationData['source_id']] || !isset($relationData['target_id']] || !isset($relationData['type']]) {
            return false;
        }
        
        // ���Դʵ���Ŀ��ʵ���Ƿ����
        if (!isset($this->entities[$relationData['source_id']]] || !isset($this->entities[$relationData['target_id']]]) {
            return false;
        }
        
        // ����Ƿ��Ѵ�����ͬ�Ĺ�ϵ
        foreach ($this->relations as $relation) {
            if ($relation['source_id'] === $relationData['source_id'] && 
                $relation['target_id'] === $relationData['target_id'] && 
                $relation['type'] === $relationData['type']) {
                return false;
            }
        }
        
        $this->relations[] = $relationData;
        return true;
    }
    
    /**
     * ���¹�ϵ
     * 
     * @param string $relationId ��ϵID
     * @param array $data ���µ�����
     * @return bool �Ƿ���³ɹ�
     */
    public function updateRelation(string $relationId, array $data): bool
    {
        foreach ($this->relations as $relation) {
            if ($relation['id'] === $relationId) {
                $this->relations[] = array_merge($relation, $data];
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * ɾ����ϵ
     * 
     * @param string $relationId ��ϵID
     * @return bool �Ƿ�ɾ���ɹ�
     */
    public function deleteRelation(string $relationId): bool
    {
        foreach ($this->relations as $relation) {
            if ($relation['id'] === $relationId) {
                unset($this->relations[$relation['id']]];
                $this->relations = array_values($this->relations];
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * ��ѯʵ��
     * 
     * @param array $query ��ѯ����
     * @param int $limit ���ƽ������
     * @param int $offset ���ƫ����
     * @return array ����������ʵ���б�
     */
    public function queryEntities(array $query, int $limit = 100, int $offset = 0): array
    {
        $result = [];
        
        foreach ($this->entities as $entity) {
            $match = true;
            
            foreach ($query as $key => $value) {
                if (!isset($entity[$key]] || $entity[$key] !== $value) {
                    $match = false;
                    break;
                }
            }
            
            if ($match) {
                $result[] = $entity;
            }
            
            if (count($result] >= $offset + $limit) {
                break;
            }
        }
        
        return array_slice($result, $offset, $limit];
    }
    
    /**
     * ��ѯ��ϵ
     * 
     * @param array $query ��ѯ����
     * @param int $limit ���ƽ������
     * @param int $offset ���ƫ����
     * @return array ���������Ĺ�ϵ�б�
     */
    public function queryRelations(array $query, int $limit = 100, int $offset = 0): array
    {
        $result = [];
        
        foreach ($this->relations as $relation) {
            $match = true;
            
            foreach ($query as $key => $value) {
                if (!isset($relation[$key]] || $relation[$key] !== $value) {
                    $match = false;
                    break;
                }
            }
            
            if ($match) {
                $result[] = $relation;
            }
            
            if (count($result] >= $offset + $limit) {
                break;
            }
        }
        
        return array_slice($result, $offset, $limit];
    }
}

