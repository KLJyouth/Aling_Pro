<?php

namespace AlingAi\Security\QuantumEncryption;

/**
 * QuantumEncryptionInterface
 *
 * @package AlingAi\Security\QuantumEncryption
 */
interface QuantumEncryptionInterface
{
    // 接口方法定义
    /**
     * 获取所有资源
     *
     * @return array
     */
    public function getAll();

    /**
     * 根据ID获取资源
     *
     * @param int $id
     * @return mixed
     */
    public function getById($id);

    /**
     * 创建新资源
     *
     * @param array $data
     * @return mixed
     */
    public function create(array $data);

    /**
     * 更新资源
     *
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function update($id, array $data);

    /**
     * 删除资源
     *
     * @param int $id
     * @return bool
     */
    public function delete($id);
}
