<?php

namespace AlingAi\Models\Security;

use AlingAi\Models\BaseModel;
use AlingAi\Utils\Database;

/**
 * 安全问题模型
 * 管理系统中的安全问题
 *
 * @package AlingAi\Models\Security
 */
class SecurityIssue extends BaseModel
{
    /**
     * 表名
     *
     * @var string
     */
    protected $table = 'security_issues';
    
    /**
     * 主键
     *
     * @var string
     */
    protected $primaryKey = 'issue_id';
    
    /**
     * 可填充字段
     *
     * @var array
     */
    protected $fillable = [
        'issue_id',
        'title',
        'description',
        'severity',
        'category',
        'status',
        'recommendation',
        'first_detected',
        'last_detected',
        'resolved_at',
        'resolved_by',
        'resolution',
        'detection_count'
    ];
    
    /**
     * 获取所有安全问题
     *
     * @param string|null $status 问题状态
     * @param string|null $severity 严重程度
     * @param string|null $category 问题类别
     * @param int $limit 限制数量
     * @return array 问题列表
     */
    public function getIssues($status = null, $severity = null, $category = null, $limit = 100)
    {
        try {
            $query = "SELECT * FROM {$this->table} WHERE 1=1";
            $params = [];
            
            if ($status) {
                $query .= " AND status = ?";
                $params[] = $status;
            }
            
            if ($severity) {
                $query .= " AND severity = ?";
                $params[] = $severity;
            }
            
            if ($category) {
                $query .= " AND category = ?";
                $params[] = $category;
            }
            
            $query .= " ORDER BY severity ASC, last_detected DESC LIMIT ?";
            $params[] = $limit;
            
            return $this->db->query($query, $params)->fetchAll();
        } catch (\Exception $e) {
            $this->logger->error('获取安全问题失败', ['error' => $e->getMessage()]);
            return [];
        }
    }
    
    /**
     * 获取未解决的问题
     *
     * @param string|null $severity 严重程度
     * @param int $limit 限制数量
     * @return array 问题列表
     */
    public function getUnresolvedIssues($severity = null, $limit = 100)
    {
        return $this->getIssues('open', $severity, null, $limit);
    }
    
    /**
     * 获取问题详情
     *
     * @param string $issueId 问题ID
     * @return array|null 问题详情
     */
    public function getIssue($issueId)
    {
        try {
            $issue = $this->db->query(
                "SELECT * FROM {$this->table} WHERE issue_id = ?",
                [$issueId]
            )->fetch();
            
            return $issue ?: null;
        } catch (\Exception $e) {
            $this->logger->error('获取问题详情失败', [
                'issue_id' => $issueId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    /**
     * 创建或更新安全问题
     *
     * @param array $data 问题数据
     * @return string|bool 问题ID或失败
     */
    public function saveIssue($data)
    {
        try {
            // 检查问题是否已存在
            $existingIssue = $this->getIssue($data['issue_id']);
            
            if ($existingIssue) {
                // 更新现有问题
                $updateData = [
                    'last_detected' => time(),
                    'detection_count' => $existingIssue['detection_count'] + 1
                ];
                
                // 如果严重程度更高，更新严重程度
                if ($this->compareSeverity($data['severity'], $existingIssue['severity']) < 0) {
                    $updateData['severity'] = $data['severity'];
                }
                
                // 如果提供了新的描述或建议，更新它们
                if (!empty($data['description'])) {
                    $updateData['description'] = $data['description'];
                }
                
                if (!empty($data['recommendation'])) {
                    $updateData['recommendation'] = $data['recommendation'];
                }
                
                $this->db->update($this->table, $updateData, ['issue_id' => $data['issue_id']]);
                
                return $data['issue_id'];
            } else {
                // 创建新问题
                $issueData = [
                    'issue_id' => $data['issue_id'],
                    'title' => $data['title'],
                    'description' => $data['description'] ?? '',
                    'severity' => $data['severity'] ?? 'medium',
                    'category' => $data['category'] ?? 'general',
                    'status' => 'open',
                    'recommendation' => $data['recommendation'] ?? '',
                    'first_detected' => time(),
                    'last_detected' => time(),
                    'resolved_at' => null,
                    'resolved_by' => null,
                    'resolution' => null,
                    'detection_count' => 1
                ];
                
                $this->db->insert($this->table, $issueData);
                
                return $data['issue_id'];
            }
        } catch (\Exception $e) {
            $this->logger->error('保存安全问题失败', [
                'issue_id' => $data['issue_id'] ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * 解决安全问题
     *
     * @param string $issueId 问题ID
     * @param string $resolvedBy 解决者
     * @param string $resolution 解决方案
     * @return bool 是否成功
     */
    public function resolveIssue($issueId, $resolvedBy, $resolution = '')
    {
        try {
            $this->db->update($this->table, [
                'status' => 'resolved',
                'resolved_at' => time(),
                'resolved_by' => $resolvedBy,
                'resolution' => $resolution
            ], ['issue_id' => $issueId]);
            
            return true;
        } catch (\Exception $e) {
            $this->logger->error('解决安全问题失败', [
                'issue_id' => $issueId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * 重新打开安全问题
     *
     * @param string $issueId 问题ID
     * @return bool 是否成功
     */
    public function reopenIssue($issueId)
    {
        try {
            $this->db->update($this->table, [
                'status' => 'open',
                'resolved_at' => null,
                'resolved_by' => null,
                'resolution' => null
            ], ['issue_id' => $issueId]);
            
            return true;
        } catch (\Exception $e) {
            $this->logger->error('重新打开安全问题失败', [
                'issue_id' => $issueId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * 获取问题统计
     *
     * @return array 统计数据
     */
    public function getStatistics()
    {
        try {
            // 按状态统计
            $byStatus = $this->db->query(
                "SELECT status, COUNT(*) as count FROM {$this->table} GROUP BY status"
            )->fetchAll();
            
            // 按严重程度统计
            $bySeverity = $this->db->query(
                "SELECT severity, COUNT(*) as count FROM {$this->table} GROUP BY severity"
            )->fetchAll();
            
            // 按类别统计
            $byCategory = $this->db->query(
                "SELECT category, COUNT(*) as count FROM {$this->table} GROUP BY category"
            )->fetchAll();
            
            // 未解决的问题数量
            $unresolved = $this->db->query(
                "SELECT COUNT(*) as count FROM {$this->table} WHERE status = 'open'"
            )->fetch()['count'];
            
            // 最近30天发现的问题数量
            $cutoffTime = time() - (30 * 86400);
            $recent = $this->db->query(
                "SELECT COUNT(*) as count FROM {$this->table} WHERE first_detected > ?",
                [$cutoffTime]
            )->fetch()['count'];
            
            return [
                'total' => count($byStatus),
                'unresolved' => $unresolved,
                'recent' => $recent,
                'by_status' => $this->formatStatistics($byStatus),
                'by_severity' => $this->formatStatistics($bySeverity),
                'by_category' => $this->formatStatistics($byCategory)
            ];
        } catch (\Exception $e) {
            $this->logger->error('获取安全问题统计失败', ['error' => $e->getMessage()]);
            return [
                'total' => 0,
                'unresolved' => 0,
                'recent' => 0,
                'by_status' => [],
                'by_severity' => [],
                'by_category' => []
            ];
        }
    }
    
    /**
     * 格式化统计数据
     * 
     * @param array $statistics 统计数据
     * @return array 格式化后的统计数据
     */
    protected function formatStatistics($statistics)
    {
        $result = [];
        foreach ($statistics as $item) {
            $key = array_keys($item)[0];
            $result[$item[$key]] = $item['count'];
        }
        return $result;
    }
    
    /**
     * 比较严重程度
     * 
     * @param string $a 严重程度A
     * @param string $b 严重程度B
     * @return int 比较结果（-1: A更严重，0: 相同，1: B更严重）
     */
    protected function compareSeverity($a, $b)
    {
        $severityOrder = [
            'critical' => 0,
            'high' => 1,
            'medium' => 2,
            'low' => 3,
            'info' => 4
        ];
        
        $severityA = isset($severityOrder[$a]) ? $severityOrder[$a] : 5;
        $severityB = isset($severityOrder[$b]) ? $severityOrder[$b] : 5;
        
        if ($severityA < $severityB) {
            return -1;
        } elseif ($severityA > $severityB) {
            return 1;
        } else {
            return 0;
        }
    }
} 