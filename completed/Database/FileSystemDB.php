<?php
/**
 * 文件系统数据库管理器
 * 用于在没有传统数据库时提供数据存储功�?
 */

namespace AlingAi\Database;

/**
 * FileSystemDB �?
 *
 * @package AlingAi\Database
 */
class FileSystemDB
{
    private $dataPath;
    private $tables = [];
    
    /**

    
     * __construct 方法

    
     *

    
     * @param mixed $dataPath

    
     * @return void

    
     */

    
    public function __construct($dataPath = null)
    {
        $this->dataPath = $dataPath ?: __DIR__ . '/../../storage/data';
        $this->ensureDataPath(];
        $this->initializeTables(];
    }
    
    /**
     * 确保数据目录存在
     */
    /**

     * ensureDataPath 方法

     *

     * @return void

     */

    private function ensureDataPath()
    {
        if (!is_dir($this->dataPath)) {
            mkdir($this->dataPath, 0755, true];
        }
    }
    
    /**
     * 初始化表结构
     */
    /**

     * initializeTables 方法

     *

     * @return void

     */

    private function initializeTables()
    {
        $this->tables = [            'users' => [
                'file' => 'users.json',
                'schema' => [
                    'id' => 'integer',
                    'username' => 'string',
                    'email' => 'string',
                    'password_hash' => 'string',
                    'full_name' => 'string',
                    'avatar' => 'string',
                    'role' => 'string',
                    'status' => 'string',
                    'email_verified' => 'boolean',
                    'last_login' => 'datetime',
                    'login_count' => 'integer',
                    'preferences' => 'json',
                    'created_at' => 'datetime',
                    'updated_at' => 'datetime'
                ]
            ], 
            'conversations' => [
                'file' => 'conversations.json',
                'schema' => [
                    'id' => 'integer',
                    'user_id' => 'integer',
                    'title' => 'string',
                    'model' => 'string',
                    'system_prompt' => 'text',
                    'status' => 'string',
                    'message_count' => 'integer',
                    'tokens_used' => 'integer',
                    'last_activity' => 'datetime',
                    'created_at' => 'datetime',
                    'updated_at' => 'datetime'
                ]
            ], 
            'messages' => [
                'file' => 'messages.json',
                'schema' => [
                    'id' => 'integer',
                    'conversation_id' => 'integer',
                    'user_id' => 'integer',
                    'role' => 'string',
                    'content' => 'text',
                    'tokens' => 'integer',
                    'model' => 'string',
                    'metadata' => 'json',
                    'deleted_at' => 'datetime',
                    'created_at' => 'datetime'
                ]
            ], 
            'chat_sessions' => [
                'file' => 'chat_sessions.json',
                'schema' => [
                    'id' => 'integer',
                    'user_id' => 'integer',
                    'title' => 'string',
                    'model' => 'string',
                    'created_at' => 'datetime',
                    'updated_at' => 'datetime'
                ]
            ], 
            'chat_messages' => [
                'file' => 'chat_messages.json',
                'schema' => [
                    'id' => 'integer',
                    'session_id' => 'integer',
                    'user_id' => 'integer',
                    'role' => 'string',
                    'content' => 'text',
                    'model' => 'string',
                    'tokens_used' => 'integer',
                    'created_at' => 'datetime'
                ]
            ], 
            'system_settings' => [
                'file' => 'system_settings.json',
                'schema' => [
                    'id' => 'integer',
                    'key' => 'string',
                    'value' => 'text',
                    'type' => 'string',
                    'updated_at' => 'datetime'
                ]
            ], 
            'system_metrics' => [
                'file' => 'system_metrics.json',
                'schema' => [
                    'id' => 'integer',
                    'metric_type' => 'string',
                    'metric_name' => 'string',
                    'metric_value' => 'float',
                    'metric_unit' => 'string',
                    'status' => 'string',
                    'recorded_at' => 'datetime'
                ]
            ], 
            'ai_conversations' => [
                'file' => 'ai_conversations.json',
                'schema' => [
                    'id' => 'integer',
                    'user_id' => 'integer',
                    'model_name' => 'string',
                    'prompt' => 'text',
                    'response' => 'text',
                    'tokens_used' => 'integer',
                    'response_time' => 'float',
                    'created_at' => 'datetime'
                ]
            ], 
            'email_logs' => [
                'file' => 'email_logs.json',
                'schema' => [
                    'id' => 'integer',
                    'to_email' => 'string',
                    'subject' => 'string',
                    'body' => 'text',
                    'status' => 'string',
                    'sent_at' => 'datetime'
                ]
            ]
        ];
        
        // 初始化表文件
        foreach ($this->tables as $table => $config) {
            $this->initializeTable($table];
        }
    }
    
    /**
     * 初始化单个表文件
     */
    /**

     * initializeTable 方法

     *

     * @param mixed $tableName

     * @return void

     */

    private function initializeTable($tableName)
    {
        $filePath = $this->getTablePath($tableName];
        
        if (!file_exists($filePath)) {
            // 创建空表结构
            $emptyTable = [
                'meta' => [
                    'table' => $tableName,
                    'schema' => $this->tables[$tableName]['schema'], 
                    'auto_increment' => 1,
                    'created_at' => date('Y-m-d H:i:s')
                ], 
                'data' => []
            ];
            
            file_put_contents($filePath, json_encode($emptyTable, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)];
        }
    }
    
    /**
     * 获取表文件路�?
     */
    /**

     * getTablePath 方法

     *

     * @param mixed $tableName

     * @return void

     */

    private function getTablePath($tableName)
    {
        return $this->dataPath . '/' . $this->tables[$tableName]['file'];
    }
    
    /**
     * 读取表数�?
     */
    /**

     * readTable 方法

     *

     * @param mixed $tableName

     * @return void

     */

    private function readTable($tableName)
    {
        $filePath = $this->getTablePath($tableName];
        
        if (!file_exists($filePath)) {
            $this->initializeTable($tableName];
        }
        
        $content = file_get_contents($filePath];
        return json_decode($content, true) ?: ['meta' => [],  'data' => []];
    }
    
    /**
     * 写入表数�?
     */
    /**

     * writeTable 方法

     *

     * @param mixed $tableName

     * @param mixed $tableData

     * @return void

     */

    private function writeTable($tableName, $tableData)
    {
        $filePath = $this->getTablePath($tableName];
        $tableData['meta']['updated_at'] = date('Y-m-d H:i:s'];
        
        return file_put_contents($filePath, json_encode($tableData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)];
    }
    
    /**
     * 插入数据
     */
    /**

     * insert 方法

     *

     * @param mixed $tableName

     * @param mixed $data

     * @return void

     */

    public function insert($tableName, $data)
    {
        if (!isset($this->tables[$tableName])) {
            throw new \Exception("Table {$tableName} does not exist"];
        }
        
        $table = $this->readTable($tableName];
          // 自动设置ID
        if (!isset($data['id'])) {
            $data['id'] = $table['meta']['auto_increment'] ?? 1;
            $table['meta']['auto_increment'] = $data['id'] + 1;
        }
        
        // 设置时间�?
        if (!isset($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s'];
        }
        if (!isset($data['updated_at'])) {
            $data['updated_at'] = date('Y-m-d H:i:s'];
        }
        
        $table['data'][] = $data;
        $this->writeTable($tableName, $table];
        
        return $data['id'];
    }
    
    /**
     * 查找数据
     */
    /**

     * select 方法

     *

     * @param mixed $tableName

     * @param mixed $conditions

     * @param mixed $limit

     * @param mixed $offset

     * @return void

     */

    public function select($tableName, $conditions = [],  $limit = null, $offset = 0)
    {
        if (!isset($this->tables[$tableName])) {
            throw new \Exception("Table {$tableName} does not exist"];
        }
        
        $table = $this->readTable($tableName];
        $results = $table['data'];
        
        // 应用条件过滤
        if (!empty($conditions)) {
            $results = array_filter($results, function($row) use ($conditions) {
                foreach ($conditions as $key => $value) {
                    if (!isset($row[$key]) || $row[$key] != $value) {
                        return false;
                    }
                }
                return true;
            }];
        }
        
        // 应用分页
        if ($offset > 0) {
            $results = array_slice($results, $offset];
        }
        
        if ($limit !== null) {
            $results = array_slice($results, 0, $limit];
        }
        
        return array_values($results];
    }
    
    /**
     * 查找单条记录
     */
    /**

     * selectOne 方法

     *

     * @param mixed $tableName

     * @param mixed $conditions

     * @return void

     */

    public function selectOne($tableName, $conditions = [])
    {
        $results = $this->select($tableName, $conditions, 1];
        return !empty($results) ? $results[0] : null;
    }
    
    /**
     * 更新数据
     */
    /**

     * update 方法

     *

     * @param mixed $tableName

     * @param mixed $conditions

     * @param mixed $data

     * @return void

     */

    public function update($tableName, $conditions, $data)
    {
        if (!isset($this->tables[$tableName])) {
            throw new \Exception("Table {$tableName} does not exist"];
        }
        
        $table = $this->readTable($tableName];
        $updated = 0;
        
        foreach ($table['data'] as &$row) {
            $match = true;
            foreach ($conditions as $key => $value) {
                if (!isset($row[$key]) || $row[$key] != $value) {
                    $match = false;
                    break;
                }
            }
            
            if ($match) {
                foreach ($data as $key => $value) {
                    $row[$key] = $value;
                }
                $row['updated_at'] = date('Y-m-d H:i:s'];
                $updated++;
            }
        }
        
        if ($updated > 0) {
            $this->writeTable($tableName, $table];
        }
        
        return $updated;
    }
    
    /**
     * 删除数据
     */
    /**

     * delete 方法

     *

     * @param mixed $tableName

     * @param mixed $conditions

     * @return void

     */

    public function delete($tableName, $conditions)
    {
        if (!isset($this->tables[$tableName])) {
            throw new \Exception("Table {$tableName} does not exist"];
        }
        
        $table = $this->readTable($tableName];
        $originalCount = count($table['data']];
        
        $table['data'] = array_filter($table['data'],  function($row) use ($conditions) {
            foreach ($conditions as $key => $value) {
                if (!isset($row[$key]) || $row[$key] != $value) {
                    return true; // 保留不匹配的�?
                }
            }
            return false; // 删除匹配的行
        }];
        
        $table['data'] = array_values($table['data']]; // 重新索引
        $deleted = $originalCount - count($table['data']];
        
        if ($deleted > 0) {
            $this->writeTable($tableName, $table];
        }
        
        return $deleted;
    }
    
    /**
     * 获取表统计信�?
     */
    /**

     * getTableStats 方法

     *

     * @param mixed $tableName

     * @return void

     */

    public function getTableStats($tableName)
    {
        if (!isset($this->tables[$tableName])) {
            throw new \Exception("Table {$tableName} does not exist"];
        }
        
        $table = $this->readTable($tableName];
        
        return [
            'table' => $tableName,
            'record_count' => count($table['data']],
            'auto_increment' => $table['meta']['auto_increment'] ?? 1,
            'created_at' => $table['meta']['created_at'] ?? null,
            'updated_at' => $table['meta']['updated_at'] ?? null
        ];
    }
    
    /**
     * 获取所有表的统计信�?
     */
    /**

     * getAllTablesStats 方法

     *

     * @return void

     */

    public function getAllTablesStats()
    {
        $stats = [];
        foreach (array_keys($this->tables) as $tableName) {
            $stats[$tableName] = $this->getTableStats($tableName];
        }
        return $stats;
    }
    
    /**
     * 备份数据
     */
    /**

     * backup 方法

     *

     * @param mixed $backupPath

     * @return void

     */

    public function backup($backupPath = null)
    {
        $backupPath = $backupPath ?: $this->dataPath . '/../backup';
        
        if (!is_dir($backupPath)) {
            mkdir($backupPath, 0755, true];
        }
        
        $backupFile = $backupPath . '/backup_' . date('Y-m-d_H-i-s') . '.json';
        
        $allData = [];
        foreach (array_keys($this->tables) as $tableName) {
            $allData[$tableName] = $this->readTable($tableName];
        }
        
        file_put_contents($backupFile, json_encode($allData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)];
        
        return $backupFile;
    }
    
    /**
     * 恢复数据
     */
    /**

     * restore 方法

     *

     * @param mixed $backupFile

     * @return void

     */

    public function restore($backupFile)
    {
        if (!file_exists($backupFile)) {
            throw new \Exception("Backup file does not exist: {$backupFile}"];
        }
        
        $backupData = json_decode(file_get_contents($backupFile], true];
        
        if (!$backupData) {
            throw new \Exception("Invalid backup file format"];
        }
        
        foreach ($backupData as $tableName => $tableData) {
            if (isset($this->tables[$tableName])) {
                $this->writeTable($tableName, $tableData];
            }
        }
        
        return true;
    }
}
