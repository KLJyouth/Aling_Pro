<?php

namespace AlingAi\Models;

use AlingAi\Services\DatabaseService;
use DateTime;
use Exception;

/**
 * 基础模型类 - 实现ORM功能
 * 提供数据库操作的统一接口
 */
abstract class BaseModel
{
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $hidden = [];
    protected $casts = [];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $attributes = [];
    protected $original = [];
    protected $exists = false;
    protected $softDelete = true;
    
    protected static $databaseService;
    
    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
        
        if (!self::$databaseService) {
            // 创建一个简单的Monolog Logger实例
            $logger = new \Monolog\Logger('database');
            $logger->pushHandler(new \Monolog\Handler\NullHandler());
            self::$databaseService = new DatabaseService($logger);
        }
    }
    
    /**
     * 填充属性
     */
    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            if (in_array($key, $this->fillable) || empty($this->fillable)) {
                $this->attributes[$key] = $value;
            }
        }
        return $this;
    }
    
    /**
     * 获取属性值
     */
    public function __get($key)
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->castAttribute($key, $this->attributes[$key]);
        }
        return null;
    }
    
    /**
     * 设置属性值
     */
    public function __set($key, $value)
    {
        if (in_array($key, $this->fillable) || empty($this->fillable)) {
            $this->attributes[$key] = $value;
        }
    }
    
    /**
     * 类型转换
     */
    protected function castAttribute($key, $value)
    {
        if (!isset($this->casts[$key]) || $value === null) {
            return $value;
        }
        
        $castType = $this->casts[$key];
        
        switch ($castType) {
            case 'int':
            case 'integer':
                return (int) $value;
            case 'float':
            case 'double':
                return (float) $value;
            case 'string':
                return (string) $value;
            case 'bool':
            case 'boolean':
                return (bool) $value;
            case 'array':
                return is_string($value) ? json_decode($value, true) : $value;
            case 'object':
                return is_string($value) ? json_decode($value) : $value;
            case 'datetime':
                return is_string($value) ? new DateTime($value) : $value;
            default:
                return $value;
        }
    }
    
    /**
     * 转换为数组
     */
    public function toArray(): array
    {
        $array = [];
        foreach ($this->attributes as $key => $value) {
            if (!in_array($key, $this->hidden)) {
                $array[$key] = $this->castAttribute($key, $value);
            }
        }
        return $array;
    }
    
    /**
     * 转换为JSON
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
    
    /**
     * 保存模型
     */
    public function save(): bool
    {
        try {
            if ($this->exists) {
                return $this->update();
            } else {
                return $this->insert();
            }
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * 插入新记录
     */
    protected function insert(): bool
    {
        $this->attributes['created_at'] = date('Y-m-d H:i:s');
        $this->attributes['updated_at'] = date('Y-m-d H:i:s');
        
        $columns = array_keys($this->attributes);
        $values = array_values($this->attributes);
        $placeholders = str_repeat('?,', count($values) - 1) . '?';
        
        $sql = "INSERT INTO {$this->table} (" . implode(',', $columns) . ") VALUES ({$placeholders})";
        
        $result = self::$databaseService->execute($sql, $values);
        
        if ($result) {
            $this->attributes[$this->primaryKey] = self::$databaseService->lastInsertId();
            $this->exists = true;
            $this->original = $this->attributes;
        }
        
        return $result;
    }
    
    /**
     * 更新记录
     */
    protected function update(): bool
    {
        $this->attributes['updated_at'] = date('Y-m-d H:i:s');
        
        $sets = [];
        $values = [];
        
        foreach ($this->attributes as $key => $value) {
            if ($key !== $this->primaryKey) {
                $sets[] = "{$key} = ?";
                $values[] = $value;
            }
        }
        
        $values[] = $this->attributes[$this->primaryKey];
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $sets) . " WHERE {$this->primaryKey} = ?";
        
        return self::$databaseService->execute($sql, $values);
    }
    
    /**
     * 删除记录
     */
    public function delete(): bool
    {
        if (!$this->exists) {
            return false;
        }
        
        if ($this->softDelete) {
            $this->attributes['deleted_at'] = date('Y-m-d H:i:s');
            return $this->update();
        } else {
            $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
            return self::$databaseService->execute($sql, [$this->attributes[$this->primaryKey]]);
        }
    }
    
    /**
     * 静态方法 - 创建查询构建器
     */
    public static function query(): QueryBuilder
    {
        return new QueryBuilder(new static());
    }
    
    /**
     * 静态方法 - 查找所有记录
     */
    public static function all(): array
    {
        return static::query()->get();
    }
    
    /**
     * 静态方法 - 根据ID查找
     */
    public static function find($id)
    {
        return static::query()->where('id', $id)->first();
    }
    
    /**
     * 静态方法 - 根据ID查找或失败
     */
    public static function findOrFail($id)
    {
        $model = static::find($id);
        if (!$model) {
            throw new Exception("Model not found with ID: {$id}");
        }
        return $model;
    }
    
    /**
     * 静态方法 - 创建新记录
     */
    public static function create(array $attributes): self
    {
        $model = new static($attributes);
        $model->save();
        return $model;
    }
    
    /**
     * 静态方法 - where查询
     */
    public static function where($column, $operator = null, $value = null): QueryBuilder
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }
        return static::query()->where($column, $operator, $value);
    }
    
    /**
     * 静态方法 - whereDate查询
     */
    public static function whereDate($column, $date): QueryBuilder
    {
        return static::query()->whereDate($column, $date);
    }
    
    /**
     * 静态方法 - whereBetween查询
     */
    public static function whereBetween($column, array $values): QueryBuilder
    {
        return static::query()->whereBetween($column, $values);
    }
    
    /**
     * 静态方法 - whereMonth查询
     */
    public static function whereMonth($column, $month): QueryBuilder
    {
        return static::query()->whereMonth($column, $month);
    }
    
    /**
     * 静态方法 - whereNull查询
     */
    public static function whereNull($column): QueryBuilder
    {
        return static::query()->whereNull($column);
    }
    
    /**
     * 静态方法 - whereNotNull查询
     */
    public static function whereNotNull($column): QueryBuilder
    {
        return static::query()->whereNotNull($column);
    }
    
    /**
     * 静态方法 - 统计记录数
     */
    public static function count(): int
    {
        return static::query()->count();
    }
    
    /**
     * 静态方法 - 关联查询
     */
    public static function with(array $relations): QueryBuilder
    {
        return static::query()->with($relations);
    }
    
    /**
     * 获取表名
     */
    public function getTable(): string
    {
        return $this->table;
    }
    
    /**
     * 检查记录是否存在
     */
    public function exists(): bool
    {
        return $this->exists;
    }
    
    /**
     * 获取主键名称
     */
    public function getKeyName(): string
    {
        return $this->primaryKey;
    }
}

/**
 * 查询构建器类
 */
class QueryBuilder
{
    protected $model;
    protected $wheres = [];
    protected $orders = [];
    protected $limit;
    protected $offset;
    protected $withs = [];
    protected $selects = ['*'];
      public function __construct(BaseModel $model)
    {
        $this->model = $model;
        
        if (!self::$databaseService) {
            // 创建一个简单的Monolog Logger实例
            $logger = new \Monolog\Logger('database');
            $logger->pushHandler(new \Monolog\Handler\NullHandler());
            self::$databaseService = new DatabaseService($logger);
        }
    }
    
    /**
     * WHERE 条件
     */
    public function where($column, $operator = null, $value = null): self
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }
        
        $this->wheres[] = [
            'type' => 'basic',
            'column' => $column,
            'operator' => $operator,
            'value' => $value
        ];
        
        return $this;
    }
    
    /**
     * WHERE DATE 条件
     */
    public function whereDate($column, $date): self
    {
        $this->wheres[] = [
            'type' => 'date',
            'column' => $column,
            'value' => $date
        ];
        
        return $this;
    }
    
    /**
     * WHERE BETWEEN 条件
     */
    public function whereBetween($column, array $values): self
    {
        $this->wheres[] = [
            'type' => 'between',
            'column' => $column,
            'values' => $values
        ];
        
        return $this;
    }
    
    /**
     * WHERE MONTH 条件
     */
    public function whereMonth($column, $month): self
    {
        $this->wheres[] = [
            'type' => 'month',
            'column' => $column,
            'value' => $month
        ];
        
        return $this;
    }
    
    /**
     * WHERE NULL 条件
     */
    public function whereNull($column): self
    {
        $this->wheres[] = [
            'type' => 'null',
            'column' => $column
        ];
        
        return $this;
    }
    
    /**
     * WHERE NOT NULL 条件
     */
    public function whereNotNull($column): self
    {
        $this->wheres[] = [
            'type' => 'not_null',
            'column' => $column
        ];
        
        return $this;
    }
    
    /**
     * 关联查询
     */
    public function with(array $relations): self
    {
        $this->withs = array_merge($this->withs, $relations);
        return $this;
    }
    
    /**
     * 排序
     */
    public function orderBy($column, $direction = 'asc'): self
    {
        $this->orders[] = [
            'column' => $column,
            'direction' => $direction
        ];
        
        return $this;
    }
    
    /**
     * 限制记录数
     */
    public function limit($limit): self
    {
        $this->limit = $limit;
        return $this;
    }
    
    /**
     * 偏移量
     */
    public function offset($offset): self
    {
        $this->offset = $offset;
        return $this;
    }
    
    /**
     * 分页查询
     */
    public function paginate(int $perPage = 15, array $columns = ['*'], string $pageName = 'page', int $page = 1): array
    {
        $total = $this->count();
        $totalPages = ceil($total / $perPage);
        
        $this->limit = $perPage;
        $this->offset = ($page - 1) * $perPage;
        
        $items = $this->get();
        
        return [
            'data' => $items,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'total_pages' => $totalPages,
            'has_next' => $page < $totalPages,
            'has_prev' => $page > 1,
            'from' => ($page - 1) * $perPage + 1,
            'to' => min($page * $perPage, $total)
        ];
    }
    
    /**
     * 查找记录或抛出异常
     */
    public function findOrFail($id): BaseModel
    {
        $model = $this->where($this->model->getKeyName(), $id)->first();
        
        if (!$model) {
            throw new \AlingAi\Exceptions\ModelNotFoundException(
                "No query results for model [" . get_class($this->model) . "] {$id}"
            );
        }
        
        return $model;
    }
    
    /**
     * 获取第一条记录
     */
    public function first(): ?BaseModel
    {
        $originalLimit = $this->limit;
        $this->limit = 1;
        
        $results = $this->get();
        $this->limit = $originalLimit;
        
        return $results[0] ?? null;
    }
      /**
     * 获取所有记录
     */
    public function get(): array
    {
        $sql = $this->buildSelectSql();
        $bindings = $this->getBindings();
        
        $results = self::$databaseService->query($sql, $bindings);
        
        $models = [];
        foreach ($results as $row) {
            $model = new $this->model($row);
            $model->exists = true;
            $model->original = $row;
            $models[] = $model;
        }
        
        return $models;
    }
    
    /**
     * 统计记录数
     */
    public function count(): int
    {
        $sql = $this->buildCountSql();
        $bindings = $this->getBindings();
        
        $result = self::$databaseService->query($sql, $bindings);
        return (int) ($result[0]['count'] ?? 0);
    }
    
    /**
     * 检查记录是否存在
     */
    public function exists(): bool
    {
        return $this->count() > 0;
    }
    
    /**
     * 构建SELECT SQL
     */
    protected function buildSelectSql(): string
    {
        $sql = "SELECT " . implode(',', $this->selects) . " FROM {$this->model->getTable()}";
        
        if (!empty($this->wheres)) {
            $sql .= " WHERE " . $this->buildWhereClause();
        }
        
        if (!empty($this->orders)) {
            $orderClauses = [];
            foreach ($this->orders as $order) {
                $orderClauses[] = "{$order['column']} {$order['direction']}";
            }
            $sql .= " ORDER BY " . implode(', ', $orderClauses);
        }
        
        if ($this->limit) {
            $sql .= " LIMIT {$this->limit}";
        }
        
        if ($this->offset) {
            $sql .= " OFFSET {$this->offset}";
        }
        
        return $sql;
    }
    
    /**
     * 构建COUNT SQL
     */
    protected function buildCountSql(): string
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->model->getTable()}";
        
        if (!empty($this->wheres)) {
            $sql .= " WHERE " . $this->buildWhereClause();
        }
        
        return $sql;
    }
    
    /**
     * 构建WHERE子句
     */
    protected function buildWhereClause(): string
    {
        $clauses = [];
        
        foreach ($this->wheres as $where) {
            switch ($where['type']) {
                case 'basic':
                    $clauses[] = "{$where['column']} {$where['operator']} ?";
                    break;
                case 'date':
                    $clauses[] = "DATE({$where['column']}) = ?";
                    break;
                case 'between':
                    $clauses[] = "{$where['column']} BETWEEN ? AND ?";
                    break;
                case 'month':
                    $clauses[] = "MONTH({$where['column']}) = ?";
                    break;
                case 'null':
                    $clauses[] = "{$where['column']} IS NULL";
                    break;
                case 'not_null':
                    $clauses[] = "{$where['column']} IS NOT NULL";
                    break;
            }
        }
        
        return implode(' AND ', $clauses);
    }
    
    /**
     * 获取绑定参数
     */
    protected function getBindings(): array
    {
        $bindings = [];
        
        foreach ($this->wheres as $where) {
            switch ($where['type']) {
                case 'basic':
                case 'date':
                case 'month':
                    $bindings[] = $where['value'];
                    break;
                case 'between':
                    $bindings[] = $where['values'][0];
                    $bindings[] = $where['values'][1];
                    break;
                // null 和 not_null 不需要绑定参数
            }
        }
        
        return $bindings;
    }
    
    protected static $databaseService;
}
