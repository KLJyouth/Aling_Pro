<?php
/**
 * 文件名：MemoryGraphStore.php
 * 功能描述：内存知识图谱存储实现
 * 创建时间：2025-01-XX
 * 最后修改：2025-01-XX
 * 版本：1.0.0
 * 
 * @package AlingAi\AI\Engines\KnowledgeGraph
 * @author AlingAi Team
 * @license MIT
 */

declare(strict_types=1];

namespace AlingAi\AI\Engines\KnowledgeGraph;

/**
 * 内存知识图谱存储实现
 * 
 * 使用内存数组存储知识图谱数据，适用于测试和小规模应用
 */
class MemoryGraphStore implements GraphStoreInterface
{
    /**
     * 实体存储
     * @var array
     */
    private array $entities = [];
    
    /**
     * 关系存储
     * @var array
     */
    private array $relations = [];
    
    /**
     * 构造函数
     * 
     * @param array $data 初始数据
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
     * 获取实体通过ID
     * 
     * @param string $entityId 实体ID
     * @return array|null 实体数据，如果不存在则返回null
     */
    public function getEntityById(string $entityId): ?array
    {
        return $this->entities[$entityId] ?? null;
    }
    
    /**
     * 获取所有实体
     * 
     * @return array 所有实体的列表
     */
    public function getAllEntities(): array
    {
        return array_values($this->entities];
    }
    
    /**
     * 获取与指定实体有指定关系的实体列表
     * 
     * @param string $entityId 实体ID
     * @param string $relationType 关系类型
     * @return array 相关实体列表
     */
    public function getRelatedEntities(string $entityId, string $relationType): array
    {
        $result = [];
        
        foreach ($this->relations as $relation) {
            if ($relation['source_id'] === $entityId && $relation['type'] === $relationType) {
                $entity = $this->getEntityById($relation['target_id']];
                if ($entity) {
                    // 将关系置信度添加到实体中
                    $entity['confidence'] = $relation['confidence'];
                    $result[] = $entity;
                }
            }
        }
        
        return $result;
    }
    
    /**
     * 获取与指定实体有指定关系的实体列表（反向）
     * 
     * @param string $relationType 关系类型
     * @param string $targetEntityId 目标实体ID
     * @return array 相关实体列表
     */
    public function getEntitiesWithRelation(string $relationType, string $targetEntityId): array
    {
        $result = [];
        
        foreach ($this->relations as $relation) {
            if ($relation['target_id'] === $targetEntityId && $relation['type'] === $relationType) {
                $entity = $this->getEntityById($relation['source_id']];
                if ($entity) {
                    // 将关系置信度添加到实体中
                    $entity['confidence'] = $relation['confidence'];
                    $result[] = $entity;
                }
            }
        }
        
        return $result;
    }
    
    /**
     * 获取两个实体之间的关系
     * 
     * @param string $sourceEntityId 源实体ID
     * @param string $targetEntityId 目标实体ID
     * @param string|null $relationType 关系类型，如果为null则返回第一个找到的关系
     * @return array|null 关系数据，如果不存在则返回null
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
     * 获取两个实体之间的所有关系
     * 
     * @param string $sourceEntityId 源实体ID
     * @param string $targetEntityId 目标实体ID
     * @return array 关系数据列表
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
     * 添加实体
     * 
     * @param array $entityData 实体数据
     * @return bool 是否添加成功
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
     * 更新实体
     * 
     * @param string $entityId 实体ID
     * @param array $data 更新的数据
     * @return bool 是否更新成功
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
     * 删除实体
     * 
     * @param string $entityId 实体ID
     * @return bool 是否删除成功
     */
    public function deleteEntity(string $entityId): bool
    {
        if (!isset($this->entities[$entityId]]) {
            return false;
        }
        
        unset($this->entities[$entityId]];
        
        // 同时删除相关的关系
        foreach ($this->relations as $relation) {
            if ($relation['source_id'] === $entityId || $relation['target_id'] === $entityId) {
                unset($this->relations[$relation['id']]];
            }
        }
        
        // 重新索引关系数组
        $this->relations = array_values($this->relations];
        
        return true;
    }
    
    /**
     * 添加关系
     * 
     * @param array $relationData 关系数据
     * @return bool 是否添加成功
     */
    public function addRelation(array $relationData): bool
    {
        if (!isset($relationData['id']] || !isset($relationData['source_id']] || !isset($relationData['target_id']] || !isset($relationData['type']]) {
            return false;
        }
        
        // 检查源实体和目标实体是否存在
        if (!isset($this->entities[$relationData['source_id']]] || !isset($this->entities[$relationData['target_id']]]) {
            return false;
        }
        
        // 检查是否已存在相同的关系
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
     * 更新关系
     * 
     * @param string $relationId 关系ID
     * @param array $data 更新的数据
     * @return bool 是否更新成功
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
     * 删除关系
     * 
     * @param string $relationId 关系ID
     * @return bool 是否删除成功
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
     * 查询实体
     * 
     * @param array $query 查询条件
     * @param int $limit 限制结果数量
     * @param int $offset 结果偏移量
     * @return array 符合条件的实体列表
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
     * 查询关系
     * 
     * @param array $query 查询条件
     * @param int $limit 限制结果数量
     * @param int $offset 结果偏移量
     * @return array 符合条件的关系列表
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

