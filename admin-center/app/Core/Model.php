<?php
namespace App\Core;

/**
 * 模型基类
 * 提供数据库操作的基本功能
 */
class Model
{
    /**
     * 表名
     * @var string
     */
    protected $table;
    
    /**
     * 主键
     * @var string
     */
    protected $primaryKey = 'id';
    
    /**
     * 可填充的字段
     * @var array
     */
    protected $fillable = [];
    
    /**
     * 不可见的字段（API响应中不包含）
     * @var array
     */
    protected $hidden = [];
    
    /**
     * 创建时间戳字段
     * @var string
     */
    protected $createdAt = 'created_at';
    
    /**
     * 更新时间戳字段
     * @var string
     */
    protected $updatedAt = 'updated_at';
    
    /**
     * 是否使用时间戳
     * @var bool
     */
    protected $timestamps = true;
    
    /**
     * 数据库连接
     * @var string|null
     */
    protected $connection = null;
    
    /**
     * 构造函数
     * @param array $attributes 属性
     */
    public function __construct(array $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            if (in_array($key, $this->fillable)) {
                $this->$key = $value;
            }
        }
    }
    
    /**
     * 获取数据库实例
     * @return \PDO
     */
    protected function getDb()
    {
        return Database::getInstance($this->connection);
    }
    
    /**
     * 根据ID获取一条记录
     * @param int $id ID
     * @return array|null 记录数组或null
     */
    public function find($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * 获取所有记录
     * @return array 记录数组
     */
    public function all()
    {
        $sql = "SELECT * FROM {$this->table}";
        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * 根据条件查询记录
     * @param array $conditions 条件数组，如 ['status' => 'active']
     * @param string $orderBy 排序字段
     * @param string $direction 排序方向（ASC或DESC）
     * @param int|null $limit 限制数量
     * @param int|null $offset 偏移量
     * @return array 记录数组
     */
    public function where(array $conditions, $orderBy = null, $direction = 'ASC', $limit = null, $offset = null)
    {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $sql .= " WHERE ";
            $clauses = [];
            
            foreach ($conditions as $column => $value) {
                if ($value === null) {
                    $clauses[] = "$column IS NULL";
                } else {
                    $clauses[] = "$column = ?";
                    $params[] = $value;
                }
            }
            
            $sql .= implode(" AND ", $clauses);
        }
        
        if ($orderBy) {
            $sql .= " ORDER BY $orderBy $direction";
        }
        
        if ($limit !== null) {
            $sql .= " LIMIT ?";
            $params[] = (int)$limit;
            
            if ($offset !== null) {
                $sql .= " OFFSET ?";
                $params[] = (int)$offset;
            }
        }
        
        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * 根据条件获取第一条记录
     * @param array $conditions 条件数组
     * @param string $orderBy 排序字段
     * @param string $direction 排序方向
     * @return array|null 记录数组或null
     */
    public function first(array $conditions = [], $orderBy = null, $direction = 'ASC')
    {
        $results = $this->where($conditions, $orderBy, $direction, 1);
        return !empty($results) ? $results[0] : null;
    }
    
    /**
     * 创建记录
     * @param array $data 数据
     * @return int|false 插入ID或失败返回false
     */
    public function create(array $data)
    {
        // 过滤不可填充的字段
        $data = array_intersect_key($data, array_flip($this->fillable));
        
        // 添加时间戳
        if ($this->timestamps) {
            $now = date('Y-m-d H:i:s');
            $data[$this->createdAt] = $now;
            $data[$this->updatedAt] = $now;
        }
        
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
        $stmt = $this->getDb()->prepare($sql);
        
        if ($stmt->execute(array_values($data))) {
            return $this->getDb()->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * 更新记录
     * @param int $id ID
     * @param array $data 数据
     * @return bool 是否成功
     */
    public function update($id, array $data)
    {
        // 过滤不可填充的字段
        $data = array_intersect_key($data, array_flip($this->fillable));
        
        // 添加时间戳
        if ($this->timestamps) {
            $data[$this->updatedAt] = date('Y-m-d H:i:s');
        }
        
        $setClause = implode(' = ?, ', array_keys($data)) . ' = ?';
        
        $sql = "UPDATE {$this->table} SET $setClause WHERE {$this->primaryKey} = ?";
        $stmt = $this->getDb()->prepare($sql);
        
        $values = array_values($data);
        $values[] = $id;
        
        return $stmt->execute($values);
    }
    
    /**
     * 删除记录
     * @param int $id ID
     * @return bool 是否成功
     */
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->getDb()->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * 执行原始SQL查询
     * @param string $sql SQL语句
     * @param array $params 参数
     * @return array 结果数组
     */
    public function query($sql, array $params = [])
    {
        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * 执行原始SQL语句（不返回结果）
     * @param string $sql SQL语句
     * @param array $params 参数
     * @return bool 是否成功
     */
    public function execute($sql, array $params = [])
    {
        $stmt = $this->getDb()->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * 获取记录总数
     * @param array $conditions 条件数组
     * @return int 总数
     */
    public function count(array $conditions = [])
    {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $sql .= " WHERE ";
            $clauses = [];
            
            foreach ($conditions as $column => $value) {
                if ($value === null) {
                    $clauses[] = "$column IS NULL";
                } else {
                    $clauses[] = "$column = ?";
                    $params[] = $value;
                }
            }
            
            $sql .= implode(" AND ", $clauses);
        }
        
        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }
    
    /**
     * 分页获取记录
     * @param int $page 页码
     * @param int $perPage 每页数量
     * @param array $conditions 条件
     * @param string $orderBy 排序字段
     * @param string $direction 排序方向
     * @return array 包含records和pagination信息的数组
     */
    public function paginate($page = 1, $perPage = 15, array $conditions = [], $orderBy = null, $direction = 'ASC')
    {
        $totalCount = $this->count($conditions);
        $totalPages = ceil($totalCount / $perPage);
        
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;
        
        $records = $this->where($conditions, $orderBy, $direction, $perPage, $offset);
        
        return [
            'records' => $records,
            'pagination' => [
                'total' => $totalCount,
                'per_page' => $perPage,
                'current_page' => $page,
                'total_pages' => $totalPages,
                'has_more' => $page < $totalPages,
            ],
        ];
    }
} 