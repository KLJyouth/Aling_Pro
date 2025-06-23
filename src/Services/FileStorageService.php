<?php

namespace AlingAi\Services;

use Psr\Log\LoggerInterface;

/**
 * 基于文件的数据存储服务
 * 当数据库不可用时使用此服务
 */
class FileStorageService implements DatabaseServiceInterface
{
    private string $storageDir;
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->storageDir = dirname(__DIR__, 2) . '/storage/data';
        
        // 确保存储目录存在
        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0755, true);
        }
    }

    public function getConnection()
    {
        return $this; // 返回自身作为连接对象
    }

    public function query(string $sql, array $params = []): array
    {
        // 简化的查询解析，主要用于基本的 CRUD 操作
        $sql = trim($sql);
        
        if (stripos($sql, 'SELECT') === 0) {
            return $this->handleSelect($sql, $params);
        } elseif (stripos($sql, 'INSERT') === 0) {
            return $this->handleInsert($sql, $params);
        } elseif (stripos($sql, 'UPDATE') === 0) {
            return $this->handleUpdate($sql, $params);
        } elseif (stripos($sql, 'DELETE') === 0) {
            return $this->handleDelete($sql, $params);
        }
        
        $this->logger->warning("不支持的 SQL 操作: " . substr($sql, 0, 50));
        return [];
    }

    public function execute(string $sql, array $params = []): bool
    {
        try {
            $this->query($sql, $params);
            return true;
        } catch (\Exception $e) {
            $this->logger->error("SQL 执行失败: " . $e->getMessage());
            return false;
        }
    }

    public function insert(string $table, array $data): bool
    {
        try {
            $record = array_merge($data, [
                'id' => $this->generateId(),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            return $this->saveRecord($table, $record);
        } catch (\Exception $e) {
            $this->logger->error("插入数据失败: " . $e->getMessage());
            return false;
        }
    }

    public function find(string $table, $id): ?array
    {
        $records = $this->loadTable($table);
        
        foreach ($records as $record) {
            if ($record['id'] == $id) {
                return $record;
            }
        }
        
        return null;
    }

    public function findAll(string $table, array $conditions = []): array
    {
        $records = $this->loadTable($table);
        
        if (empty($conditions)) {
            return $records;
        }
        
        return array_filter($records, function ($record) use ($conditions) {
            foreach ($conditions as $field => $value) {
                if (!isset($record[$field]) || $record[$field] != $value) {
                    return false;
                }
            }
            return true;        });
    }

    public function select(string $table, array $conditions = [], array $options = []): array
    {
        try {
            $records = $this->findAll($table, $conditions);
            
            // 处理排序（简单实现）
            if (isset($options['order'])) {
                // 此处可以实现排序逻辑，暂时跳过
            }
            
            // 处理限制
            if (isset($options['limit'])) {
                $offset = $options['offset'] ?? 0;
                $records = array_slice($records, $offset, $options['limit']);
            }
            
            return $records;
        } catch (\Exception $e) {
            $this->logger->error('Select failed', [
                'table' => $table,
                'conditions' => $conditions,
                'options' => $options,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    public function update(string $table, $id, array $data): bool
    {
        try {
            $records = $this->loadTable($table);
            $updated = false;
            
            for ($i = 0; $i < count($records); $i++) {
                if ($records[$i]['id'] == $id) {
                    $records[$i] = array_merge($records[$i], $data, [
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                    $updated = true;
                    break;
                }
            }
            
            if ($updated) {
                return $this->saveTable($table, $records);
            }
            
            return false;
        } catch (\Exception $e) {
            $this->logger->error("更新数据失败: " . $e->getMessage());
            return false;
        }
    }

    public function delete(string $table, $id): bool
    {
        try {
            $records = $this->loadTable($table);
            $originalCount = count($records);
            
            $records = array_filter($records, function ($record) use ($id) {
                return $record['id'] != $id;
            });
            
            if (count($records) < $originalCount) {
                return $this->saveTable($table, array_values($records));
            }
            
            return false;
        } catch (\Exception $e) {
            $this->logger->error("删除数据失败: " . $e->getMessage());
            return false;
        }
    }

    private function handleSelect(string $sql, array $params): array
    {
        // 简单的表名提取
        if (preg_match('/FROM\s+(\w+)/i', $sql, $matches)) {
            $table = $matches[1];
            return $this->loadTable($table);
        }
        
        return [];
    }

    private function handleInsert(string $sql, array $params): array
    {
        // 简单的 INSERT 解析
        if (preg_match('/INSERT\s+INTO\s+(\w+)/i', $sql, $matches)) {
            $table = $matches[1];
            // 这里可以进一步解析 VALUES，但现在简化处理
            $this->logger->info("文件存储：INSERT 操作到表 $table");
        }
        
        return [];
    }

    private function handleUpdate(string $sql, array $params): array
    {
        if (preg_match('/UPDATE\s+(\w+)/i', $sql, $matches)) {
            $table = $matches[1];
            $this->logger->info("文件存储：UPDATE 操作到表 $table");
        }
        
        return [];
    }

    private function handleDelete(string $sql, array $params): array
    {
        if (preg_match('/DELETE\s+FROM\s+(\w+)/i', $sql, $matches)) {
            $table = $matches[1];
            $this->logger->info("文件存储：DELETE 操作到表 $table");
        }
        
        return [];
    }

    private function loadTable(string $table): array
    {
        $filePath = $this->storageDir . '/' . $table . '.json';
        
        if (!file_exists($filePath)) {
            return [];
        }
        
        $content = file_get_contents($filePath);
        $data = json_decode($content, true);
        
        return $data ?: [];
    }

    private function saveTable(string $table, array $records): bool
    {
        $filePath = $this->storageDir . '/' . $table . '.json';
        
        $content = json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        return file_put_contents($filePath, $content) !== false;
    }

    private function saveRecord(string $table, array $record): bool
    {
        $records = $this->loadTable($table);
        $records[] = $record;
        
        return $this->saveTable($table, $records);
    }    private function generateId(): string
    {
        return uniqid('', true);
    }

    public function count(string $table, array $conditions = []): int
    {
        try {
            $records = $this->findAll($table, $conditions);
            return count($records);
        } catch (\Exception $e) {
            $this->logger->error('Count failed', [
                'table' => $table,
                'conditions' => $conditions,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }    public function selectOne(string $table, array $conditions): ?array
    {
        try {
            $records = $this->findAll($table, $conditions);
            return !empty($records) ? $records[0] : null;
        } catch (\Exception $e) {
            $this->logger->error('SelectOne failed', [
                'table' => $table,
                'conditions' => $conditions,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    public function lastInsertId()
    {
        // 文件存储服务不支持自增ID，返回当前时间戳作为模拟
        return time();
    }

    public function beginTransaction(): bool
    {
        // 文件存储不支持事务，但返回 true 以保持兼容性
        return true;
    }

    public function commit(): bool
    {
        return true;
    }

    public function rollback(): bool
    {
        return true;
    }
}
