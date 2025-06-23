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

declare(strict_types=1);

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
    private array  = [];
    
    /**
     * 关系存储
     * @var array
     */
    private array  = [];
    
    /**
     * 构造函数
     * 
     * @param array  初始数据
     */
    public function __construct(array  = [])
    {
        if (isset(['entities'])) {
            ->entities = ['entities'];
        }
        
        if (isset(['relations'])) {
            ->relations = ['relations'];
        }
    }
    
    /**
     * 获取实体通过ID
     * 
     * @param string  实体ID
     * @return array|null 实体数据，如果不存在则返回null
     */
    public function getEntityById(string ): ?array
    {
        return ->entities[] ?? null;
    }
    
    /**
     * 获取所有实体
     * 
     * @return array 所有实体的列表
     */
    public function getAllEntities(): array
    {
        return array_values(->entities);
    }
    
    /**
     * 获取与指定实体有指定关系的实体列表
     * 
     * @param string  实体ID
     * @param string  关系类型
     * @return array 相关实体列表
     */
    public function getRelatedEntities(string , string ): array
    {
         = [];
        
        foreach (->relations as ) {
            if (['source_id'] ===  && ['type'] === ) {
                 = ->getEntityById(['target_id']);
                if () {
                    // 将关系置信度添加到实体中
                    ['confidence'] = ['confidence'];
                    [] = ;
                }
            }
        }
        
        return ;
    }
    
    /**
     * 获取与指定实体有指定关系的实体列表（反向）
     * 
     * @param string  关系类型
     * @param string  目标实体ID
     * @return array 相关实体列表
     */
    public function getEntitiesWithRelation(string , string ): array
    {
         = [];
        
        foreach (->relations as ) {
            if (['target_id'] ===  && ['type'] === ) {
                 = ->getEntityById(['source_id']);
                if () {
                    // 将关系置信度添加到实体中
                    ['confidence'] = ['confidence'];
                    [] = ;
                }
            }
        }
        
        return ;
    }
    
    /**
     * 获取两个实体之间的关系
     * 
     * @param string  源实体ID
     * @param string  目标实体ID
     * @param string|null  关系类型，如果为null则返回第一个找到的关系
     * @return array|null 关系数据，如果不存在则返回null
     */
    public function getRelationBetween(string , string , ?string  = null): ?array
    {
        foreach (->relations as ) {
            if (['source_id'] ===  && ['target_id'] === ) {
                if ( === null || ['type'] === ) {
                    return ;
                }
            }
        }
        
        return null;
    }
    
    /**
     * 获取两个实体之间的所有关系
     * 
     * @param string  源实体ID
     * @param string  目标实体ID
     * @return array 关系数据列表
     */
    public function getRelationsBetween(string , string ): array
    {
         = [];
        
        foreach (->relations as ) {
            if (['source_id'] ===  && ['target_id'] === ) {
                [] = ;
            }
        }
        
        return ;
    }
    
    /**
     * 添加实体
     * 
     * @param array  实体数据
     * @return bool 是否添加成功
     */
    public function addEntity(array ): bool
    {
        if (!isset(['id'])) {
            return false;
        }
        
        ->entities[['id']] = ;
        return true;
    }
    
    /**
     * 更新实体
     * 
     * @param string  实体ID
     * @param array  更新的数据
     * @return bool 是否更新成功
     */
    public function updateEntity(string , array ): bool
    {
        if (!isset(->entities[])) {
            return false;
        }
        
        ->entities[] = array_merge(->entities[], );
        return true;
    }
    
    /**
     * 删除实体
     * 
     * @param string  实体ID
     * @return bool 是否删除成功
     */
    public function deleteEntity(string ): bool
    {
        if (!isset(->entities[])) {
            return false;
        }
        
        unset(->entities[]);
        
        // 同时删除相关的关系
        foreach (->relations as  => ) {
            if (['source_id'] ===  || ['target_id'] === ) {
                unset(->relations[]);
            }
        }
        
        // 重新索引关系数组
        ->relations = array_values(->relations);
        
        return true;
    }
    
    /**
     * 添加关系
     * 
     * @param array  关系数据
     * @return bool 是否添加成功
     */
    public function addRelation(array ): bool
    {
        if (!isset(['id']) || !isset(['source_id']) || !isset(['target_id']) || !isset(['type'])) {
            return false;
        }
        
        // 检查源实体和目标实体是否存在
        if (!isset(->entities[['source_id']]) || !isset(->entities[['target_id']])) {
            return false;
        }
        
        // 检查是否已存在相同的关系
        foreach (->relations as ) {
            if (['source_id'] === ['source_id'] && 
                ['target_id'] === ['target_id'] && 
                ['type'] === ['type']) {
                return false;
            }
        }
        
        ->relations[] = ;
        return true;
    }
    
    /**
     * 更新关系
     * 
     * @param string  关系ID
     * @param array  更新的数据
     * @return bool 是否更新成功
     */
    public function updateRelation(string , array ): bool
    {
        foreach (->relations as  => ) {
            if (['id'] === ) {
                ->relations[] = array_merge(, );
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * 删除关系
     * 
     * @param string  关系ID
     * @return bool 是否删除成功
     */
    public function deleteRelation(string ): bool
    {
        foreach (->relations as  => ) {
            if (['id'] === ) {
                unset(->relations[]);
                ->relations = array_values(->relations);
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * 查询实体
     * 
     * @param array  查询条件
     * @param int  限制结果数量
     * @param int  结果偏移量
     * @return array 符合条件的实体列表
     */
    public function queryEntities(array , int  = 100, int  = 0): array
    {
         = [];
        
        foreach (->entities as ) {
             = true;
            
            foreach ( as  => ) {
                if (!isset([]) || [] !== ) {
                     = false;
                    break;
                }
            }
            
            if () {
                [] = ;
            }
            
            if (count() >=  + ) {
                break;
            }
        }
        
        return array_slice(, , );
    }
    
    /**
     * 查询关系
     * 
     * @param array  查询条件
     * @param int  限制结果数量
     * @param int  结果偏移量
     * @return array 符合条件的关系列表
     */
    public function queryRelations(array , int  = 100, int  = 0): array
    {
         = [];
        
        foreach (->relations as ) {
             = true;
            
            foreach ( as  => ) {
                if (!isset([]) || [] !== ) {
                     = false;
                    break;
                }
            }
            
            if () {
                [] = ;
            }
            
            if (count() >=  + ) {
                break;
            }
        }
        
        return array_slice(, , );
    }
}
