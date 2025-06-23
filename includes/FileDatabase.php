<?php

declare(strict_types=1);

/**
 * 简单的文件数据库类
 * 用于在没有数据库连接时进行测试
 */
class FileDatabase {
    private string $dataDir;
    
    public function __construct(string $dataDir) {
        $this->dataDir = $dataDir;
        if (!is_dir($this->dataDir)) {
            mkdir($this->dataDir, 0755, true);
        }
    }
    
    public function insert(string $table, array $data): int {
        $data['id'] = $this->getNextId($table);
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $records = $this->getRecords($table);
        $records[] = $data;
        $this->saveRecords($table, $records);
        
        return $data['id'];
    }
    
    public function find(string $table, array $conditions = []): array {
        $records = $this->getRecords($table);
        
        if (empty($conditions)) {
            return $records;
        }
        
        return array_filter($records, function($record) use ($conditions) {
            foreach ($conditions as $field => $value) {
                if (!isset($record[$field]) || $record[$field] !== $value) {
                    return false;
                }
            }
            return true;
        });
    }
    
    public function findOne(string $table, array $conditions): ?array {
        $results = $this->find($table, $conditions);
        return !empty($results) ? array_values($results)[0] : null;
    }
    
    public function update(string $table, array $conditions, array $data): bool {
        $records = $this->getRecords($table);
        $updated = false;
        
        foreach ($records as &$record) {
            $matches = true;
            foreach ($conditions as $field => $value) {
                if (!isset($record[$field]) || $record[$field] !== $value) {
                    $matches = false;
                    break;
                }
            }
            
            if ($matches) {
                $record = array_merge($record, $data);
                $record['updated_at'] = date('Y-m-d H:i:s');
                $updated = true;
            }
        }
        
        if ($updated) {
            $this->saveRecords($table, $records);
        }
        
        return $updated;
    }
    
    public function delete(string $table, array $conditions): bool {
        $records = $this->getRecords($table);
        $originalCount = count($records);
        
        $records = array_filter($records, function($record) use ($conditions) {
            foreach ($conditions as $field => $value) {
                if (isset($record[$field]) && $record[$field] === $value) {
                    return false;
                }
            }
            return true;
        });
        
        if (count($records) < $originalCount) {
            $this->saveRecords($table, array_values($records));
            return true;
        }
        
        return false;
    }
      private function getRecords(string $table): array {
        $file = $this->dataDir . '/' . $table . '.json';
        if (!file_exists($file)) {
            return [];
        }
        
        $content = file_get_contents($file);
        $data = $content ? json_decode($content, true) : [];
        
        // 如果数据是旧格式（带有结构信息），提取实际记录
        if (isset($data['data']) && is_array($data['data'])) {
            return $data['data'];
        }
        
        // 如果数据是混合格式，提取数字键的记录
        $records = [];
        foreach ($data as $key => $value) {
            if (is_numeric($key) && is_array($value)) {
                $records[] = $value;
            }
        }
        
        return $records;
    }
    
    private function saveRecords(string $table, array $records): void {
        $file = $this->dataDir . '/' . $table . '.json';
        file_put_contents($file, json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    private function getNextId(string $table): int {
        $records = $this->getRecords($table);
        $maxId = 0;
        
        foreach ($records as $record) {
            if (isset($record['id']) && $record['id'] > $maxId) {
                $maxId = $record['id'];
            }
        }
        
        return $maxId + 1;
    }
}
