<?php
/**
 * �ļ�����GraphStoreInterface.php
 * ����������֪ʶͼ�״洢�ӿ�
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
 * ֪ʶͼ�״洢�ӿ�
 * 
 * ������֪ʶͼ�״洢�Ļ��������ӿڣ�����ʵ��͹�ϵ����ɾ�Ĳ�
 */
interface GraphStoreInterface
{
    /**
     * ��ȡʵ��ͨ��ID
     * 
     * @param string $entityId ʵ��ID
     * @return array|null ʵ�����ݣ�����������򷵻�null
     */
    public function getEntityById(string $entityId): ?array;
    
    /**
     * ��ȡ����ʵ��
     * 
     * @return array ����ʵ����б�
     */
    public function getAllEntities(): array;
    
    /**
     * ��ȡ��ָ��ʵ����ָ����ϵ��ʵ���б�
     * 
     * @param string $entityId ʵ��ID
     * @param string $relationType ��ϵ����
     * @return array ���ʵ���б�
     */
    public function getRelatedEntities(string $entityId, string $relationType): array;
    
    /**
     * ��ȡ��ָ��ʵ����ָ����ϵ��ʵ���б�����
     * 
     * @param string $relationType ��ϵ����
     * @param string $targetEntityId Ŀ��ʵ��ID
     * @return array ���ʵ���б�
     */
    public function getEntitiesWithRelation(string $relationType, string $targetEntityId): array;
    
    /**
     * ��ȡ����ʵ��֮��Ĺ�ϵ
     * 
     * @param string $sourceEntityId Դʵ��ID
     * @param string $targetEntityId Ŀ��ʵ��ID
     * @param string|null $relationType ��ϵ���ͣ����Ϊnull�򷵻��������͵Ĺ�ϵ
     * @return array|null ��ϵ���ݣ�����������򷵻�null
     */
    public function getRelationBetween(string $sourceEntityId, string $targetEntityId, ?string $relationType = null): ?array;
    
    /**
     * ��ȡ����ʵ��֮������й�ϵ
     * 
     * @param string $sourceEntityId Դʵ��ID
     * @param string $targetEntityId Ŀ��ʵ��ID
     * @return array ��ϵ�����б�
     */
    public function getRelationsBetween(string $sourceEntityId, string $targetEntityId): array;
    
    /**
     * ���ʵ��
     * 
     * @param array $entityData ʵ������
     * @return bool �Ƿ���ӳɹ�
     */
    public function addEntity(array $entityData): bool;
    
    /**
     * ����ʵ��
     * 
     * @param string $entityId ʵ��ID
     * @param array $data ���µ�����
     * @return bool �Ƿ���³ɹ�
     */
    public function updateEntity(string $entityId, array $data): bool;
    
    /**
     * ɾ��ʵ��
     * 
     * @param string $entityId ʵ��ID
     * @return bool �Ƿ�ɾ���ɹ�
     */
    public function deleteEntity(string $entityId): bool;
    
    /**
     * ��ӹ�ϵ
     * 
     * @param array $relationData ��ϵ����
     * @return bool �Ƿ���ӳɹ�
     */
    public function addRelation(array $relationData): bool;
    
    /**
     * ���¹�ϵ
     * 
     * @param string $relationId ��ϵID
     * @param array $data ���µ�����
     * @return bool �Ƿ���³ɹ�
     */
    public function updateRelation(string $relationId, array $data): bool;
    
    /**
     * ɾ����ϵ
     * 
     * @param string $relationId ��ϵID
     * @return bool �Ƿ�ɾ���ɹ�
     */
    public function deleteRelation(string $relationId): bool;
    
    /**
     * ��ѯʵ��
     * 
     * @param array $criteria ��ѯ����
     * @param int $limit ���ƽ������
     * @param int $offset ���ƫ����
     * @return array ����������ʵ���б�
     */
    public function queryEntities(array $criteria, int $limit = 100, int $offset = 0): array;
    
    /**
     * ��ѯ��ϵ
     * 
     * @param array $criteria ��ѯ����
     * @param int $limit ���ƽ������
     * @param int $offset ���ƫ����
     * @return array ���������Ĺ�ϵ�б�
     */
    public function queryRelations(array $criteria, int $limit = 100, int $offset = 0): array;
}

