<?php

namespace AlingAi\Models\Security;

use AlingAi\Models\BaseModel;
use AlingAi\Utils\Database;

/**
 * 安全测试结果模型
 * 管理系统中的安全测试结果
 *
 * @package AlingAi\Models\Security
 */
class SecurityTestResult extends BaseModel
{
    /**
     * 表名
     *
     * @var string
     */
    protected $table = 'security_test_results';
    
    /**
     * 主键
     *
     * @var string
     */
    protected $primaryKey = 'result_id';
    
    /**
     * 可填充字段
     *
     * @var array
     */
    protected $fillable = [
        'result_id',
        'test_type',
        'results',
        'status',
        'score',
        'created_at',
        'created_by',
        'duration'
    ];
    
    /**
     * 保存测试结果
     *
     * @param array $data 测试结果数据
     * @return string|bool 结果ID或失败
     */
    public function saveResult($data)
    {
        try {
            // 生成唯一结果ID
            $resultId = $this->generateResultId();
            
            // 准备测试结果数据
            $resultData = [
                'result_id' => $resultId,
                'test_type' => $data['test_type'],
                'results' => is_array($data['results']) ? json_encode($data['results']) : $data['results'],
                'status' => $data['status'] ?? 'completed',
                'score' => $data['score'] ?? null,
                'created_at' => time(),
                'created_by' => $data['created_by'] ?? null,
                'duration' => $data['duration'] ?? null
            ];
            
            // 保存到数据库
            $this->db->insert($this->table, $resultData);
            
            return $resultId;
        } catch (\Exception $e) {
            $this->logger->error('保存测试结果失败', ['error' => $e->getMessage()]);
            return false;
        }
    }
    
    /**
     * 获取测试结果
     *
     * @param string $resultId 结果ID
     * @return array|null 测试结果
     */
    public function getResult($resultId)
    {
        try {
            $result = $this->db->query(
                "SELECT * FROM {$this->table} WHERE result_id = ?",
                [$resultId]
            )->fetch();
            
            if ($result && isset($result['results'])) {
                $result['results'] = json_decode($result['results'], true);
            }
            
            return $result ?: null;
        } catch (\Exception $e) {
            $this->logger->error('获取测试结果失败', [
                'result_id' => $resultId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    /**
     * 获取最近的测试结果
     *
     * @param string|null $testType 测试类型
     * @param int $limit 限制数量
     * @return array 测试结果列表
     */
    public function getRecentResults($testType = null, $limit = 10)
    {
        try {
            $query = "SELECT * FROM {$this->table}";
            $params = [];
            
            if ($testType) {
                $query .= " WHERE test_type = ?";
                $params[] = $testType;
            }
            
            $query .= " ORDER BY created_at DESC LIMIT ?";
            $params[] = $limit;
            
            $results = $this->db->query($query, $params)->fetchAll();
            
            // 解码结果JSON
            foreach ($results as &$result) {
                if (isset($result['results'])) {
                    $result['results'] = json_decode($result['results'], true);
                }
            }
            
            return $results;
        } catch (\Exception $e) {
            $this->logger->error('获取最近测试结果失败', ['error' => $e->getMessage()]);
            return [];
        }
    }
    
    /**
     * 获取测试历史
     *
     * @param int $days 天数
     * @param string|null $testType 测试类型
     * @return array 测试历史
     */
    public function getTestHistory($days = 30, $testType = null)
    {
        try {
            $cutoffTime = time() - ($days * 86400);
            
            $query = "SELECT * FROM {$this->table} WHERE created_at > ?";
            $params = [$cutoffTime];
            
            if ($testType) {
                $query .= " AND test_type = ?";
                $params[] = $testType;
            }
            
            $query .= " ORDER BY created_at DESC";
            
            $results = $this->db->query($query, $params)->fetchAll();
            
            // 按日期分组
            $history = [];
            foreach ($results as $result) {
                $date = date('Y-m-d', $result['created_at']);
                
                if (!isset($history[$date])) {
                    $history[$date] = [];
                }
                
                // 不包含完整结果，以减小数据量
                unset($result['results']);
                $history[$date][] = $result;
            }
            
            return $history;
        } catch (\Exception $e) {
            $this->logger->error('获取测试历史失败', ['error' => $e->getMessage()]);
            return [];
        }
    }
    
    /**
     * 获取安全评分趋势
     *
     * @param int $days 天数
     * @return array 评分趋势
     */
    public function getScoreTrend($days = 30)
    {
        try {
            $cutoffTime = time() - ($days * 86400);
            
            $query = "SELECT DATE(FROM_UNIXTIME(created_at)) as date, AVG(score) as avg_score FROM {$this->table} 
                     WHERE created_at > ? AND score IS NOT NULL 
                     GROUP BY date 
                     ORDER BY date";
            
            $trend = $this->db->query($query, [$cutoffTime])->fetchAll();
            
            return $trend;
        } catch (\Exception $e) {
            $this->logger->error('获取安全评分趋势失败', ['error' => $e->getMessage()]);
            return [];
        }
    }
    
    /**
     * 删除旧的测试结果
     *
     * @param int $days 保留天数
     * @return int 删除的记录数
     */
    public function deleteOldResults($days = 90)
    {
        try {
            $cutoffTime = time() - ($days * 86400);
            
            $count = $this->db->execute(
                "DELETE FROM {$this->table} WHERE created_at < ?",
                [$cutoffTime]
            );
            
            return $count;
        } catch (\Exception $e) {
            $this->logger->error('删除旧测试结果失败', ['error' => $e->getMessage()]);
            return 0;
        }
    }
    
    /**
     * 生成唯一结果ID
     *
     * @return string 结果ID
     */
    protected function generateResultId()
    {
        return 'test_result_' . uniqid() . '_' . time();
    }
} 