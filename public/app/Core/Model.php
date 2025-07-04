<?php
/**
 * 模型基类
 * 
 * 所有模型的基类
 * 
 * @package App\Core
 */

namespace App\Core;

class Model
{
    /**
     * 表名
     * 
     * @var string
     */
    protected $table;
    
    /**
     * 主键
     * 
     * @var string
     */
    protected $primaryKey = "id";
    
    /**
     * 可填充字段
     * 
     * @var array
     */
    protected $fillable = [];
    
    /**
     * 构造函数
     * 
     * @param string|null $table 表名
     */
    public function __construct($table = null)
    {
        if ($table) {
            $this->table = $table;
        }
    }
    
    /**
     * 获取所有记录
     * 
     * @param string $orderBy 排序字段
     * @param string $order 排序方式
     * @return array
     */
    public function all($orderBy = null, $order = "ASC")
    {
        $sql = "SELECT * FROM {$this->table}";
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy} {$order}";
        }
        
        return Database::fetchAll($sql);
    }
    
    /**
     * 根据ID查找记录
     * 
     * @param int|string $id ID值
     * @return array|null
     */
    public function find($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return Database::fetchOne($sql, [$id]);
    }
    
    /**
     * 根据条件查找记录
     * 
     * @param array $conditions 条件数组，键为字段名，值为字段值
     * @param string $orderBy 排序字段
     * @param string $order 排序方式
     * @return array
     */
    public function where(array $conditions, $orderBy = null, $order = "ASC")
    {
        $sql = "SELECT * FROM {$this->table} WHERE ";
        $params = [];
        $whereClause = [];
        
        foreach ($conditions as $field => $value) {
            $whereClause[] = "{$field} = ?";
            $params[] = $value;
        }
        
        $sql .= implode(" AND ", $whereClause);
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy} {$order}";
        }
        
        return Database::fetchAll($sql, $params);
    }
    
    /**
     * 根据条件查找第一条记录
     * 
     * @param array $conditions 条件数组，键为字段名，值为字段值
     * @return array|null
     */
    public function firstWhere(array $conditions)
    {
        $sql = "SELECT * FROM {$this->table} WHERE ";
        $params = [];
        $whereClause = [];
        
        foreach ($conditions as $field => $value) {
            $whereClause[] = "{$field} = ?";
            $params[] = $value;
        }
        
        $sql .= implode(" AND ", $whereClause);
        $sql .= " LIMIT 1";
        
        return Database::fetchOne($sql, $params);
    }
    
    /**
     * 创建记录
     * 
     * @param array $data 数据数组，键为字段名，值为字段值
     * @return int|string 插入的ID
     */
    public function create(array $data)
    {
        // 过滤不可填充字段
        if (!empty($this->fillable)) {
            $data = array_intersect_key($data, array_flip($this->fillable));
        }
        
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), "?");
        
        $sql = "INSERT INTO {$this->table} (" . implode(", ", $fields) . ") VALUES (" . implode(", ", $placeholders) . ")";
        
        Database::execute($sql, array_values($data));
        return Database::lastInsertId();
    }
    
    /**
     * 更新记录
     * 
     * @param int|string $id ID值
     * @param array $data 数据数组，键为字段名，值为字段值
     * @return int 受影响的行数
     */
    public function update($id, array $data)
    {
        // 过滤不可填充字段
        if (!empty($this->fillable)) {
            $data = array_intersect_key($data, array_flip($this->fillable));
        }
        
        $setClause = [];
        $params = [];
        
        foreach ($data as $field => $value) {
            $setClause[] = "{$field} = ?";
            $params[] = $value;
        }
        
        $params[] = $id;
        
        $sql = "UPDATE {$this->table} SET " . implode(", ", $setClause) . " WHERE {$this->primaryKey} = ?";
        
        return Database::execute($sql, $params);
    }
    
    /**
     * 删除记录
     * 
     * @param int|string $id ID值
     * @return int 受影响的行数
     */
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return Database::execute($sql, [$id]);
    }
    
    /**
     * 根据条件删除记录
     * 
     * @param array $conditions 条件数组，键为字段名，值为字段值
     * @return int 受影响的行数
     */
    public function deleteWhere(array $conditions)
    {
        $sql = "DELETE FROM {$this->table} WHERE ";
        $params = [];
        $whereClause = [];
        
        foreach ($conditions as $field => $value) {
            $whereClause[] = "{$field} = ?";
            $params[] = $value;
        }
        
        $sql .= implode(" AND ", $whereClause);
        
        return Database::execute($sql, $params);
    }
    
    /**
     * 计数
     * 
     * @param array|null $conditions 条件数组，键为字段名，值为字段值
     * @return int
     */
    public function count(array $conditions = null)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        $params = [];
        
        if ($conditions) {
            $sql .= " WHERE ";
            $whereClause = [];
            
            foreach ($conditions as $field => $value) {
                $whereClause[] = "{$field} = ?";
                $params[] = $value;
            }
            
            $sql .= implode(" AND ", $whereClause);
        }
        
        return (int) Database::fetchValue($sql, $params);
    }
}
