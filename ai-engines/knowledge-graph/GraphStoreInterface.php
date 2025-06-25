<?php
/**
 * 文件名：GraphStoreInterface.php
 * 功能描述：知识图谱存储接口
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
 * 知识图谱存储接口
 * 
 * 定义了知识图谱存储的基本操作接口，包括实体和关系的增删改查
 */
interface GraphStoreInterface
{
    /**
     * 获取实体通过ID
     * 
     * @param string $entityId 实体ID
     * @return array|null 实体数据，如果不存在则返回null
     */
    public function getEntityById(string $entityId): ?array;
    
    /**
     * 获取所有实体
     * 
     * @return array 所有实体的列表
     */
    public function getAllEntities(): array;
    
    /**
     * 获取与指定实体有指定关系的实体列表
     * 
     * @param string $entityId 实体ID
     * @param string $relationType 关系类型
     * @return array 相关实体列表
     */
    public function getRelatedEntities(string $entityId, string $relationType): array;
    
    /**
     * 获取与指定实体有指定关系的实体列表（反向）
     * 
     * @param string $relationType 关系类型
     * @param string $targetEntityId 目标实体ID
     * @return array 相关实体列表
     */
    public function getEntitiesWithRelation(string $relationType, string $targetEntityId): array;
    
    /**
     * 获取两个实体之间的关系
     * 
     * @param string $sourceEntityId 源实体ID
     * @param string $targetEntityId 目标实体ID
     * @param string|null $relationType 关系类型，如果为null则返回所有类型的关系
     * @return array|null 关系数据，如果不存在则返回null
     */
    public function getRelationBetween(string $sourceEntityId, string $targetEntityId, ?string $relationType = null): ?array;
    
    /**
     * 获取两个实体之间的所有关系
     * 
     * @param string $sourceEntityId 源实体ID
     * @param string $targetEntityId 目标实体ID
     * @return array 关系数据列表
     */
    public function getRelationsBetween(string $sourceEntityId, string $targetEntityId): array;
    
    /**
     * 添加实体
     * 
     * @param array $entityData 实体数据
     * @return bool 是否添加成功
     */
    public function addEntity(array $entityData): bool;
    
    /**
     * 更新实体
     * 
     * @param string $entityId 实体ID
     * @param array $data 更新的数据
     * @return bool 是否更新成功
     */
    public function updateEntity(string $entityId, array $data): bool;
    
    /**
     * 删除实体
     * 
     * @param string $entityId 实体ID
     * @return bool 是否删除成功
     */
    public function deleteEntity(string $entityId): bool;
    
    /**
     * 添加关系
     * 
     * @param array $relationData 关系数据
     * @return bool 是否添加成功
     */
    public function addRelation(array $relationData): bool;
    
    /**
     * 更新关系
     * 
     * @param string $relationId 关系ID
     * @param array $data 更新的数据
     * @return bool 是否更新成功
     */
    public function updateRelation(string $relationId, array $data): bool;
    
    /**
     * 删除关系
     * 
     * @param string $relationId 关系ID
     * @return bool 是否删除成功
     */
    public function deleteRelation(string $relationId): bool;
    
    /**
     * 查询实体
     * 
     * @param array $criteria 查询条件
     * @param int $limit 限制结果数量
     * @param int $offset 结果偏移量
     * @return array 符合条件的实体列表
     */
    public function queryEntities(array $criteria, int $limit = 100, int $offset = 0): array;
    
    /**
     * 查询关系
     * 
     * @param array $criteria 查询条件
     * @param int $limit 限制结果数量
     * @param int $offset 结果偏移量
     * @return array 符合条件的关系列表
     */
    public function queryRelations(array $criteria, int $limit = 100, int $offset = 0): array;
}

