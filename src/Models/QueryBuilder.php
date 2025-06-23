<?php

declare(strict_types=1);

namespace AlingAi\Models;

use AlingAi\Services\DatabaseService;
use Exception;

/**
 * 查询构建器类
 * 为模型提供流畅的查询接口
 */
class QueryBuilder
{
    protected $model;
    protected $wheres = [];
    protected $orderBy = [];
    protected $limitValue;
    protected $offsetValue;
    protected $selectColumns = ['*'];
    protected $relations = [];
    protected $groupBy = [];
    protected static $databaseService;
    
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
     * 添加 WHERE 条件
     */
    public function where($column, $operator = '=', $value = null): self
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }
        
        $this->wheres[] = [
            'type' => 'basic',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => 'and'
        ];
        
        return $this;
    }
    
    /**
     * 添加 OR WHERE 条件
     */
    public function orWhere($column, $operator = '=', $value = null): self
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }
        
        $this->wheres[] = [
            'type' => 'basic',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => 'or'
        ];
        
        return $this;
    }
      /**
     * 添加嵌套 WHERE 条件
     */
    public function whereNested(callable $callback): self
    {
        $query = new static($this->model);
        $callback($query);
        
        $this->wheres[] = [
            'type' => 'nested',
            'query' => $query,
            'boolean' => 'and'
        ];
        
        return $this;
    }
    
    /**
     * WHERE LIKE 查询
     */
    public function whereLike($column, $value): self
    {
        return $this->where($column, 'LIKE', $value);
    }
    
    /**
     * WHERE IN 查询
     */
    public function whereIn($column, array $values): self
    {
        $this->wheres[] = [
            'type' => 'in',
            'column' => $column,
            'values' => $values,
            'boolean' => 'and'
        ];
        
        return $this;
    }
    
    /**
     * WHERE NOT IN 查询
     */
    public function whereNotIn($column, array $values): self
    {
        $this->wheres[] = [
            'type' => 'not_in',
            'column' => $column,
            'values' => $values,
            'boolean' => 'and'
        ];
        
        return $this;
    }
    
    /**
     * WHERE NULL 查询
     */
    public function whereNull($column): self
    {
        $this->wheres[] = [
            'type' => 'null',
            'column' => $column,
            'boolean' => 'and'
        ];
        
        return $this;
    }
    
    /**
     * WHERE NOT NULL 查询
     */
    public function whereNotNull($column): self
    {
        $this->wheres[] = [
            'type' => 'not_null',
            'column' => $column,
            'boolean' => 'and'
        ];
        
        return $this;
    }
    
    /**
     * WHERE BETWEEN 查询
     */
    public function whereBetween($column, array $values): self
    {
        $this->wheres[] = [
            'type' => 'between',
            'column' => $column,
            'values' => $values,
            'boolean' => 'and'
        ];
        
        return $this;
    }
    
    /**
     * WHERE DATE 查询
     */
    public function whereDate($column, $date): self
    {
        return $this->where("DATE({$column})", '=', $date);
    }
    
    /**
     * WHERE MONTH 查询
     */
    public function whereMonth($column, $month): self
    {
        return $this->where("MONTH({$column})", '=', $month);
    }
    
    /**
     * 选择列
     */
    public function select($columns = ['*']): self
    {
        $this->selectColumns = is_array($columns) ? $columns : func_get_args();
        return $this;
    }
    
    /**
     * 原始查询选择
     */
    public function selectRaw($expression): self
    {
        $this->selectColumns[] = $expression;
        return $this;
    }
    
    /**
     * 排序
     */
    public function orderBy($column, $direction = 'asc'): self
    {
        $this->orderBy[] = [
            'column' => $column,
            'direction' => strtolower($direction)
        ];
        
        return $this;
    }
    
    /**
     * 分组
     */
    public function groupBy($column): self
    {
        if (is_array($column)) {
            $this->groupBy = array_merge($this->groupBy, $column);
        } else {
            $this->groupBy[] = $column;
        }
        
        return $this;
    }
    
    /**
     * 限制数量
     */
    public function limit($value): self
    {
        $this->limitValue = $value;
        return $this;
    }
    
    /**
     * 偏移量
     */
    public function offset($value): self
    {
        $this->offsetValue = $value;
        return $this;
    }
    
    /**
     * 分页
     */
    public function skip($value): self
    {
        return $this->offset($value);
    }
    
    /**
     * 取前 N 条
     */
    public function take($value): self
    {
        return $this->limit($value);
    }
    
    /**
     * 预加载关联
     */
    public function with($relations): self
    {
        $this->relations = is_array($relations) ? $relations : func_get_args();
        return $this;
    }
    
    /**
     * 执行查询并获取所有结果
     */
    public function get(): array
    {
        $sql = $this->toSql();
        $bindings = $this->getBindings();
        
        $results = self::$databaseService->select($sql, $bindings);
          return array_map(function($result) {
            $className = get_class($this->model);
            $model = new $className((array)$result);
            $model->exists = true;
            $model->original = (array)$result;
            return $model;
        }, $results);
    }
    
    /**
     * 获取第一条记录
     */
    public function first()
    {
        $this->limit(1);
        $results = $this->get();
        return empty($results) ? null : $results[0];
    }
    
    /**
     * 统计记录数
     */
    public function count(): int
    {
        $originalSelect = $this->selectColumns;
        $this->selectColumns = ['COUNT(*) as count'];
        
        $sql = $this->toSql();
        $bindings = $this->getBindings();
        
        $result = self::$databaseService->selectOne($sql, $bindings);
        
        $this->selectColumns = $originalSelect;
        
        return (int)($result['count'] ?? 0);
    }
    
    /**
     * 求和
     */
    public function sum($column): float
    {
        $originalSelect = $this->selectColumns;
        $this->selectColumns = ["SUM({$column}) as sum"];
        
        $sql = $this->toSql();
        $bindings = $this->getBindings();
        
        $result = self::$databaseService->selectOne($sql, $bindings);
        
        $this->selectColumns = $originalSelect;
        
        return (float)($result['sum'] ?? 0);
    }
    
    /**
     * 求平均值
     */
    public function avg($column): float
    {
        $originalSelect = $this->selectColumns;
        $this->selectColumns = ["AVG({$column}) as avg"];
        
        $sql = $this->toSql();
        $bindings = $this->getBindings();
        
        $result = self::$databaseService->selectOne($sql, $bindings);
        
        $this->selectColumns = $originalSelect;
        
        return (float)($result['avg'] ?? 0);
    }
    
    /**
     * 求最大值
     */
    public function max($column)
    {
        $originalSelect = $this->selectColumns;
        $this->selectColumns = ["MAX({$column}) as max"];
        
        $sql = $this->toSql();
        $bindings = $this->getBindings();
        
        $result = self::$databaseService->selectOne($sql, $bindings);
        
        $this->selectColumns = $originalSelect;
        
        return $result['max'] ?? null;
    }
    
    /**
     * 求最小值
     */
    public function min($column)
    {
        $originalSelect = $this->selectColumns;
        $this->selectColumns = ["MIN({$column}) as min"];
        
        $sql = $this->toSql();
        $bindings = $this->getBindings();
        
        $result = self::$databaseService->selectOne($sql, $bindings);
        
        $this->selectColumns = $originalSelect;
        
        return $result['min'] ?? null;
    }
    
    /**
     * 更新记录
     */
    public function update(array $data): bool
    {
        $sets = [];
        $bindings = [];
        
        foreach ($data as $key => $value) {
            $sets[] = "{$key} = ?";
            $bindings[] = $value;
        }
        
        $sql = "UPDATE {$this->model->getTable()} SET " . implode(', ', $sets);
        
        if (!empty($this->wheres)) {
            $whereClause = $this->buildWhereClause();
            $sql .= " WHERE " . $whereClause['sql'];
            $bindings = array_merge($bindings, $whereClause['bindings']);
        }
        
        return self::$databaseService->execute($sql, $bindings);
    }
    
    /**
     * 删除记录
     */
    public function delete(): bool
    {
        $sql = "DELETE FROM {$this->model->getTable()}";
        $bindings = [];
        
        if (!empty($this->wheres)) {
            $whereClause = $this->buildWhereClause();
            $sql .= " WHERE " . $whereClause['sql'];
            $bindings = $whereClause['bindings'];
        }
        
        return self::$databaseService->execute($sql, $bindings);
    }
    
    /**
     * 插入记录
     */
    public function insert(array $data): bool
    {
        $columns = array_keys($data);
        $values = array_values($data);
        $placeholders = str_repeat('?,', count($values) - 1) . '?';
        
        $sql = "INSERT INTO {$this->model->getTable()} (" . implode(',', $columns) . ") VALUES ({$placeholders})";
        
        return self::$databaseService->execute($sql, $values);
    }
    
    /**
     * 插入并获取 ID
     */
    public function insertGetId(array $data): int
    {
        $this->insert($data);
        return (int)self::$databaseService->lastInsertId();
    }
    
    /**
     * 分页查询
     */
    public function paginate($perPage = 15, $page = 1): array
    {
        $offset = ($page - 1) * $perPage;
        $total = $this->count();
        
        $this->limit($perPage)->offset($offset);
        $items = $this->get();
        
        return [
            'data' => $items,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => ceil($total / $perPage),
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total),
            'has_more_pages' => $page < ceil($total / $perPage)
        ];
    }
    
    /**
     * 生成 SQL 语句
     */
    public function toSql(): string
    {
        $sql = "SELECT " . implode(', ', $this->selectColumns) . " FROM {$this->model->getTable()}";
        
        if (!empty($this->wheres)) {
            $whereClause = $this->buildWhereClause();
            $sql .= " WHERE " . $whereClause['sql'];
        }
        
        if (!empty($this->orderBy)) {
            $orderClauses = [];
            foreach ($this->orderBy as $order) {
                $orderClauses[] = "{$order['column']} {$order['direction']}";
            }
            $sql .= " ORDER BY " . implode(', ', $orderClauses);
        }
        
        if (!empty($this->groupBy)) {
            $sql .= " GROUP BY " . implode(', ', $this->groupBy);
        }
        
        if ($this->limitValue !== null) {
            $sql .= " LIMIT {$this->limitValue}";
        }
        
        if ($this->offsetValue !== null) {
            $sql .= " OFFSET {$this->offsetValue}";
        }
        
        return $sql;
    }
    
    /**
     * 获取绑定参数
     */
    public function getBindings(): array
    {
        if (empty($this->wheres)) {
            return [];
        }
        
        $whereClause = $this->buildWhereClause();
        return $whereClause['bindings'];
    }
    
    /**
     * 构建 WHERE 子句
     */
    protected function buildWhereClause(): array
    {
        $sql = '';
        $bindings = [];
        
        foreach ($this->wheres as $index => $where) {
            if ($index > 0) {
                $sql .= " {$where['boolean']} ";
            }
            
            switch ($where['type']) {
                case 'basic':
                    $sql .= "{$where['column']} {$where['operator']} ?";
                    $bindings[] = $where['value'];
                    break;
                    
                case 'in':
                    $placeholders = str_repeat('?,', count($where['values']) - 1) . '?';
                    $sql .= "{$where['column']} IN ({$placeholders})";
                    $bindings = array_merge($bindings, $where['values']);
                    break;
                    
                case 'not_in':
                    $placeholders = str_repeat('?,', count($where['values']) - 1) . '?';
                    $sql .= "{$where['column']} NOT IN ({$placeholders})";
                    $bindings = array_merge($bindings, $where['values']);
                    break;
                    
                case 'null':
                    $sql .= "{$where['column']} IS NULL";
                    break;
                    
                case 'not_null':
                    $sql .= "{$where['column']} IS NOT NULL";
                    break;
                    
                case 'between':
                    $sql .= "{$where['column']} BETWEEN ? AND ?";
                    $bindings = array_merge($bindings, $where['values']);
                    break;
                    
                case 'nested':
                    $nestedClause = $where['query']->buildWhereClause();
                    $sql .= "({$nestedClause['sql']})";
                    $bindings = array_merge($bindings, $nestedClause['bindings']);
                    break;
            }
        }
        
        return ['sql' => $sql, 'bindings' => $bindings];
    }
    
    /**
     * 获取指定列的值作为数组
     */
    public function pluck($value, $key = null): array
    {
        $results = $this->get();
        
        if ($key === null) {
            return array_column((array)$results, $value);
        } else {
            return array_column((array)$results, $value, $key);
        }
    }
}
