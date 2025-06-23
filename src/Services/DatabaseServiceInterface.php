<?php

namespace AlingAi\Services;

/**
 * 数据库服务接口
 */
interface DatabaseServiceInterface
{
    /**
     * 获取数据库连接
     * @return mixed
     */
    public function getConnection();

    /**
     * 执行查询
     * @param string $sql SQL 语句
     * @param array $params 参数
     * @return array 查询结果
     */
    public function query(string $sql, array $params = []): array;

    /**
     * 执行 SQL 语句
     * @param string $sql SQL 语句
     * @param array $params 参数
     * @return bool 执行结果
     */
    public function execute(string $sql, array $params = []): bool;

    /**
     * 插入数据
     * @param string $table 表名
     * @param array $data 数据
     * @return bool 插入结果
     */
    public function insert(string $table, array $data): bool;    /**
     * 查找单条记录
     * @param string $table 表名
     * @param mixed $id 主键
     * @return array|null 记录或 null
     */
    public function find(string $table, $id): ?array;
    
    /**
     * 查找所有记录
     * @param string $table 表名
     * @param array $conditions 条件
     * @return array 记录列表
     */
    public function findAll(string $table, array $conditions = []): array;

    /**
     * 查询多条记录
     * @param string $table 表名
     * @param array $conditions 条件
     * @param array $options 选项 (limit, offset, order等)
     * @return array 记录列表
     */
    public function select(string $table, array $conditions = [], array $options = []): array;    /**
     * 更新数据
     * @param string $table 表名
     * @param mixed $id 主键
     * @param array $data 数据
     * @return bool 更新结果
     */
    public function update(string $table, $id, array $data): bool;
    
    /**
     * 删除数据
     * @param string $table 表名
     * @param mixed $id 主键
     * @return bool 删除结果
     */
    public function delete(string $table, $id): bool;
    
    /**
     * 统计记录数量
     * @param string $table 表名
     * @param array $conditions 条件
     * @return int 记录数量
     */
    public function count(string $table, array $conditions = []): int;

    /**
     * 查找单条记录（根据条件）
     * @param string $table 表名
     * @param array $conditions 条件
     * @return array|null 记录或 null
     */
    public function selectOne(string $table, array $conditions): ?array;

    /**
     * 获取最后插入的ID
     * @return int|string 最后插入的ID
     */
    public function lastInsertId();

    /**
     * 开始事务
     * @return bool
     */
    public function beginTransaction(): bool;

    /**
     * 提交事务
     * @return bool
     */
    public function commit(): bool;

    /**
     * 回滚事务
     * @return bool
     */
    public function rollback(): bool;
}
