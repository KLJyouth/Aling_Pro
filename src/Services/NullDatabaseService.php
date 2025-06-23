<?php
/**
 * 空数据库服务实现
 * 当数据库连接失败时使用，提供兼容的接口但不执行实际操作
 * 
 * @package AlingAi\Services
 */

declare(strict_types=1);

namespace AlingAi\Services;

use PDO;
use Monolog\Logger;

class NullDatabaseService
{
    private Logger $logger;
    
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }
    
    public function getPdo(): ?PDO
    {
        return null;
    }
    
    public function beginTransaction(): bool
    {
        return false;
    }
    
    public function commit(): bool
    {
        return false;
    }
    
    public function rollback(): bool
    {
        return false;
    }
    
    public function select(string $table, array $where = []): array
    {
        $this->logger->debug('NullDatabaseService: select operation skipped');
        return [];
    }
    
    public function selectOne(string $table, array $where = []): ?array
    {
        $this->logger->debug('NullDatabaseService: selectOne operation skipped');
        return null;
    }
    
    public function insert(string $table, array $data): bool
    {
        $this->logger->debug('NullDatabaseService: insert operation skipped');
        return false;
    }
    
    public function update(string $table, array $data, array $where): bool
    {
        $this->logger->debug('NullDatabaseService: update operation skipped');
        return false;
    }
    
    public function delete(string $table, array $where): bool
    {
        $this->logger->debug('NullDatabaseService: delete operation skipped');
        return false;
    }
    
    public function execute(string $sql, array $params = []): bool
    {
        $this->logger->debug('NullDatabaseService: execute operation skipped');
        return false;
    }
    
    public function query(string $sql, array $params = []): array
    {
        $this->logger->debug('NullDatabaseService: query operation skipped');
        return [];
    }
    
    public function lastInsertId(): string
    {
        return '0';
    }
    
    public function count(string $table, array $where = []): int
    {
        $this->logger->debug('NullDatabaseService: count operation skipped');
        return 0;
    }
    
    public function exists(string $table, array $where): bool
    {
        $this->logger->debug('NullDatabaseService: exists operation skipped');
        return false;
    }
}
