<?php

namespace AlingAi\Controllers\Api;

use AlingAi\Core\Controller;
use AlingAi\Core\Response;
use AlingAi\Security\SecurityIntegrationPlatform;
use AlingAi\Security\RealTimeAttackResponseSystem;
use AlingAi\Core\Container;
use Psr\Log\LoggerInterface;

/**
 * 安全事件响应API控制器
 * 
 * 提供安全事件的管理、分类、优先级、状态、分配等功能
 * 增强功能：事件生命周期管理、响应计划、执行跟踪、统计分析
 */
class IncidentResponseApiController extends Controller
{
    private $securityPlatform;
    private $responseSystem;
    private $logger;
    private $container;

    public function __construct()
    {
        parent::__construct();
        $this->container = Container::getInstance();
        $this->logger = $this->container->get(LoggerInterface::class);
        $this->securityPlatform = new SecurityIntegrationPlatform($this->logger, $this->container);
        $this->responseSystem = new RealTimeAttackResponseSystem($this->logger, $this->container);
    }

    /**
     * 获取事件列表
     * 
     * @return Response
     */
    public function getIncidents(): Response
    {
        try {
            $filters = $this->getRequestQuery();
            $page = (int)($filters['page'] ?? 1);
            $limit = (int)($filters['limit'] ?? 20);
            $status = $filters['status'] ?? null;
            $priority = $filters['priority'] ?? null;
            $type = $filters['type'] ?? null;
            
            $incidents = $this->getIncidentList($page, $limit, $status, $priority, $type);
            
            return Response::success($incidents, '事件列表获取成功');
        } catch (\Exception $e) {
            $this->logger->error('获取事件列表失败', ['error' => $e->getMessage()]);
            return Response::error('获取事件列表失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取单个事件详情
     * 
     * @param int $id 事件ID
     * @return Response
     */
    public function getIncident(int $id): Response
    {
        try {
            $incident = $this->getIncidentDetails($id);
            
            if (!$incident) {
                return Response::error('事件不存在', 404);
            }
            
            return Response::success($incident, '事件详情获取成功');
        } catch (\Exception $e) {
            $this->logger->error('获取事件详情失败', ['error' => $e->getMessage()]);
            return Response::error('获取事件详情失败: ' . $e->getMessage());
        }
    }

    /**
     * 创建新事件
     * 
     * @return Response
     */
    public function createIncident(): Response
    {
        try {
            $data = $this->getRequestData();
            
            // 验证必填字段
            $requiredFields = ['title', 'description', 'type', 'severity'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    return Response::error("缺少必填字段: {$field}");
                }
            }
            
            $incident = $this->createNewIncident($data);
            
            $this->logger->info('创建安全事件', ['incident_id' => $incident['id']]);
            
            return Response::success($incident, '事件创建成功');
        } catch (\Exception $e) {
            $this->logger->error('创建事件失败', ['error' => $e->getMessage()]);
            return Response::error('创建事件失败: ' . $e->getMessage());
        }
    }

    /**
     * 更新事件
     * 
     * @param int $id 事件ID
     * @return Response
     */
    public function updateIncident(int $id): Response
    {
        try {
            $data = $this->getRequestData();
            
            $incident = $this->updateIncidentData($id, $data);
            
            if (!$incident) {
                return Response::error('事件不存在', 404);
            }
            
            $this->logger->info('更新安全事件', ['incident_id' => $id]);
            
            return Response::success($incident, '事件更新成功');
        } catch (\Exception $e) {
            $this->logger->error('更新事件失败', ['error' => $e->getMessage()]);
            return Response::error('更新事件失败: ' . $e->getMessage());
        }
    }

    /**
     * 删除事件
     * 
     * @param int $id 事件ID
     * @return Response
     */
    public function deleteIncident(int $id): Response
    {
        try {
            $result = $this->deleteIncidentData($id);
            
            if (!$result) {
                return Response::error('事件不存在或无法删除', 404);
            }
            
            $this->logger->info('删除安全事件', ['incident_id' => $id]);
            
            return Response::success(['id' => $id], '事件删除成功');
        } catch (\Exception $e) {
            $this->logger->error('删除事件失败', ['error' => $e->getMessage()]);
            return Response::error('删除事件失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取事件分类
     * 
     * @param int $id 事件ID
     * @return Response
     */
    public function classifyIncident(int $id): Response
    {
        try {
            $classification = $this->getIncidentClassification($id);
            
            return Response::success($classification, '事件分类获取成功');
        } catch (\Exception $e) {
            $this->logger->error('获取事件分类失败', ['error' => $e->getMessage()]);
            return Response::error('获取事件分类失败: ' . $e->getMessage());
        }
    }

    /**
     * 更新事件分类
     * 
     * @param int $id 事件ID
     * @return Response
     */
    public function updateIncidentClassification(int $id): Response
    {
        try {
            $data = $this->getRequestData();
            
            if (empty($data['classification'])) {
                return Response::error('缺少分类信息');
            }
            
            $classification = $this->updateIncidentClassificationData($id, $data['classification']);
            
            $this->logger->info('更新事件分类', ['incident_id' => $id, 'classification' => $data['classification']]);
            
            return Response::success($classification, '事件分类更新成功');
        } catch (\Exception $e) {
            $this->logger->error('更新事件分类失败', ['error' => $e->getMessage()]);
            return Response::error('更新事件分类失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取事件优先级
     * 
     * @param int $id 事件ID
     * @return Response
     */
    public function getIncidentPriority(int $id): Response
    {
        try {
            $priority = $this->getIncidentPriorityData($id);
            
            return Response::success($priority, '事件优先级获取成功');
        } catch (\Exception $e) {
            $this->logger->error('获取事件优先级失败', ['error' => $e->getMessage()]);
            return Response::error('获取事件优先级失败: ' . $e->getMessage());
        }
    }

    /**
     * 更新事件优先级
     * 
     * @param int $id 事件ID
     * @return Response
     */
    public function updateIncidentPriority(int $id): Response
    {
        try {
            $data = $this->getRequestData();
            
            if (empty($data['priority'])) {
                return Response::error('缺少优先级信息');
            }
            
            $validPriorities = ['low', 'medium', 'high', 'critical'];
            if (!in_array($data['priority'], $validPriorities)) {
                return Response::error('无效的优先级值');
            }
            
            $priority = $this->updateIncidentPriorityData($id, $data['priority']);
            
            $this->logger->info('更新事件优先级', ['incident_id' => $id, 'priority' => $data['priority']]);
            
            return Response::success($priority, '事件优先级更新成功');
        } catch (\Exception $e) {
            $this->logger->error('更新事件优先级失败', ['error' => $e->getMessage()]);
            return Response::error('更新事件优先级失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取事件状态
     * 
     * @param int $id 事件ID
     * @return Response
     */
    public function getIncidentStatus(int $id): Response
    {
        try {
            $status = $this->getIncidentStatusData($id);
            
            return Response::success($status, '事件状态获取成功');
        } catch (\Exception $e) {
            $this->logger->error('获取事件状态失败', ['error' => $e->getMessage()]);
            return Response::error('获取事件状态失败: ' . $e->getMessage());
        }
    }

    /**
     * 更新事件状态
     * 
     * @param int $id 事件ID
     * @return Response
     */
    public function updateIncidentStatus(int $id): Response
    {
        try {
            $data = $this->getRequestData();
            
            if (empty($data['status'])) {
                return Response::error('缺少状态信息');
            }
            
            $validStatuses = ['open', 'in_progress', 'resolved', 'closed', 'escalated'];
            if (!in_array($data['status'], $validStatuses)) {
                return Response::error('无效的状态值');
            }
            
            $status = $this->updateIncidentStatusData($id, $data['status']);
            
            $this->logger->info('更新事件状态', ['incident_id' => $id, 'status' => $data['status']]);
            
            return Response::success($status, '事件状态更新成功');
        } catch (\Exception $e) {
            $this->logger->error('更新事件状态失败', ['error' => $e->getMessage()]);
            return Response::error('更新事件状态失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取事件分配
     * 
     * @param int $id 事件ID
     * @return Response
     */
    public function getIncidentAssignment(int $id): Response
    {
        try {
            $assignment = $this->getIncidentAssignmentData($id);
            
            return Response::success($assignment, '事件分配获取成功');
        } catch (\Exception $e) {
            $this->logger->error('获取事件分配失败', ['error' => $e->getMessage()]);
            return Response::error('获取事件分配失败: ' . $e->getMessage());
        }
    }

    /**
     * 分配事件
     * 
     * @param int $id 事件ID
     * @return Response
     */
    public function assignIncident(int $id): Response
    {
        try {
            $data = $this->getRequestData();
            
            if (empty($data['assignee_id'])) {
                return Response::error('缺少分配人信息');
            }
            
            $assignment = $this->assignIncidentData($id, $data['assignee_id']);
            
            $this->logger->info('分配事件', ['incident_id' => $id, 'assignee_id' => $data['assignee_id']]);
            
            return Response::success($assignment, '事件分配成功');
        } catch (\Exception $e) {
            $this->logger->error('分配事件失败', ['error' => $e->getMessage()]);
            return Response::error('分配事件失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取事件评论
     * 
     * @param int $id 事件ID
     * @return Response
     */
    public function getIncidentComments(int $id): Response
    {
        try {
            $comments = $this->getIncidentCommentsData($id);
            
            return Response::success($comments, '事件评论获取成功');
        } catch (\Exception $e) {
            $this->logger->error('获取事件评论失败', ['error' => $e->getMessage()]);
            return Response::error('获取事件评论失败: ' . $e->getMessage());
        }
    }

    /**
     * 添加事件评论
     * 
     * @param int $id 事件ID
     * @return Response
     */
    public function addIncidentComment(int $id): Response
    {
        try {
            $data = $this->getRequestData();
            
            if (empty($data['content'])) {
                return Response::error('评论内容不能为空');
            }
            
            $comment = $this->addIncidentCommentData($id, $data['content']);
            
            $this->logger->info('添加事件评论', ['incident_id' => $id]);
            
            return Response::success($comment, '评论添加成功');
        } catch (\Exception $e) {
            $this->logger->error('添加事件评论失败', ['error' => $e->getMessage()]);
            return Response::error('添加事件评论失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取响应计划
     * 
     * @return Response
     */
    public function getResponsePlans(): Response
    {
        try {
            $plans = [
                [
                    'id' => 1,
                    'name' => 'SQL注入攻击响应计划',
                    'description' => '针对SQL注入攻击的标准响应流程',
                    'type' => 'sql_injection',
                    'priority' => 'high',
                    'steps' => [
                        ['step' => 1, 'action' => '立即阻断攻击源IP', 'timeout' => 30],
                        ['step' => 2, 'action' => '分析攻击载荷', 'timeout' => 300],
                        ['step' => 3, 'action' => '检查数据库完整性', 'timeout' => 600],
                        ['step' => 4, 'action' => '修复漏洞', 'timeout' => 1800],
                        ['step' => 5, 'action' => '恢复服务', 'timeout' => 300]
                    ],
                    'created_at' => time() - 86400
                ],
                [
                    'id' => 2,
                    'name' => 'DDoS攻击响应计划',
                    'description' => '针对DDoS攻击的响应流程',
                    'type' => 'ddos',
                    'priority' => 'critical',
                    'steps' => [
                        ['step' => 1, 'action' => '启用DDoS防护', 'timeout' => 60],
                        ['step' => 2, 'action' => '分析攻击流量', 'timeout' => 300],
                        ['step' => 3, 'action' => '联系ISP', 'timeout' => 600],
                        ['step' => 4, 'action' => '调整防护策略', 'timeout' => 900],
                        ['step' => 5, 'action' => '监控恢复情况', 'timeout' => 3600]
                    ],
                    'created_at' => time() - 172800
                ],
                [
                    'id' => 3,
                    'name' => '恶意软件感染响应计划',
                    'description' => '针对恶意软件感染的响应流程',
                    'type' => 'malware',
                    'priority' => 'high',
                    'steps' => [
                        ['step' => 1, 'action' => '隔离受感染系统', 'timeout' => 60],
                        ['step' => 2, 'action' => '分析恶意软件', 'timeout' => 600],
                        ['step' => 3, 'action' => '清理恶意文件', 'timeout' => 1800],
                        ['step' => 4, 'action' => '修复系统漏洞', 'timeout' => 3600],
                        ['step' => 5, 'action' => '恢复系统服务', 'timeout' => 900]
                    ],
                    'created_at' => time() - 259200
                ]
            ];
            
            return Response::success($plans, '响应计划列表获取成功');
        } catch (\Exception $e) {
            $this->logger->error('获取响应计划失败', ['error' => $e->getMessage()]);
            return Response::error('获取响应计划失败: ' . $e->getMessage());
        }
    }

    /**
     * 执行响应
     * 
     * @param int $id 事件ID
     * @return Response
     */
    public function executeResponse(int $id): Response
    {
        try {
            $data = $this->getRequestData();
            $planId = $data['plan_id'] ?? null;
            
            if (!$planId) {
                return Response::error('缺少响应计划ID');
            }
            
            $execution = $this->executeResponsePlan($id, $planId);
            
            $this->logger->info('执行响应计划', ['incident_id' => $id, 'plan_id' => $planId]);
            
            return Response::success($execution, '响应计划执行成功');
        } catch (\Exception $e) {
            $this->logger->error('执行响应计划失败', ['error' => $e->getMessage()]);
            return Response::error('执行响应计划失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取执行状态
     * 
     * @param int $id 事件ID
     * @return Response
     */
    public function getExecutionStatus(int $id): Response
    {
        try {
            $status = $this->getExecutionStatusData($id);
            
            return Response::success($status, '执行状态获取成功');
        } catch (\Exception $e) {
            $this->logger->error('获取执行状态失败', ['error' => $e->getMessage()]);
            return Response::error('获取执行状态失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取事件统计
     * 
     * @return Response
     */
    public function getIncidentStatistics(): Response
    {
        try {
            $statistics = [
                'total_incidents' => 1250,
                'open_incidents' => 45,
                'resolved_incidents' => 1150,
                'critical_incidents' => 12,
                'high_priority_incidents' => 28,
                'average_resolution_time' => 4.5, // 小时
                'incidents_by_type' => [
                    'sql_injection' => 156,
                    'xss' => 89,
                    'brute_force' => 234,
                    'ddos' => 67,
                    'malware' => 45,
                    'phishing' => 123,
                    'other' => 536
                ],
                'incidents_by_status' => [
                    'open' => 45,
                    'in_progress' => 23,
                    'resolved' => 1150,
                    'closed' => 32
                ],
                'incidents_by_priority' => [
                    'critical' => 12,
                    'high' => 28,
                    'medium' => 156,
                    'low' => 1054
                ]
            ];
            
            return Response::success($statistics, '事件统计获取成功');
        } catch (\Exception $e) {
            $this->logger->error('获取事件统计失败', ['error' => $e->getMessage()]);
            return Response::error('获取事件统计失败: ' . $e->getMessage());
        }
    }

    // 辅助方法
    private function getRequestQuery(): array
    {
        return $_GET ?? [];
    }

    private function getRequestData(): array
    {
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?? [];
    }

    private function getIncidentList(int $page, int $limit, ?string $status, ?string $priority, ?string $type): array
    {
        // 模拟事件列表数据
        $incidents = [];
        for ($i = 1; $i <= $limit; $i++) {
            $incidentId = ($page - 1) * $limit + $i;
            $incidents[] = [
                'id' => $incidentId,
                'title' => "安全事件 #{$incidentId}",
                'description' => "这是一个安全事件的描述",
                'type' => ['sql_injection', 'xss', 'brute_force', 'ddos'][array_rand(['sql_injection', 'xss', 'brute_force', 'ddos'])],
                'severity' => ['low', 'medium', 'high', 'critical'][array_rand(['low', 'medium', 'high', 'critical'])],
                'status' => ['open', 'in_progress', 'resolved', 'closed'][array_rand(['open', 'in_progress', 'resolved', 'closed'])],
                'priority' => ['low', 'medium', 'high', 'critical'][array_rand(['low', 'medium', 'high', 'critical'])],
                'assignee' => ['admin', 'security_team', 'system_admin'][array_rand(['admin', 'security_team', 'system_admin'])],
                'created_at' => time() - rand(0, 86400 * 30),
                'updated_at' => time() - rand(0, 86400 * 7)
            ];
        }
        
        return [
            'incidents' => $incidents,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => 1250,
                'pages' => ceil(1250 / $limit)
            ]
        ];
    }

    private function getIncidentDetails(int $id): ?array
    {
        return [
            'id' => $id,
            'title' => "安全事件 #{$id}",
            'description' => "这是一个详细的安全事件描述",
            'type' => 'sql_injection',
            'severity' => 'high',
            'status' => 'in_progress',
            'priority' => 'high',
            'assignee' => 'security_team',
            'source_ip' => '192.168.1.100',
            'payload' => "'; DROP TABLE users; --",
            'created_at' => time() - 86400,
            'updated_at' => time() - 3600,
            'comments' => [
                [
                    'id' => 1,
                    'content' => '开始分析攻击载荷',
                    'author' => 'admin',
                    'created_at' => time() - 7200
                ],
                [
                    'id' => 2,
                    'content' => '已确认SQL注入攻击',
                    'author' => 'security_team',
                    'created_at' => time() - 3600
                ]
            ]
        ];
    }

    private function createNewIncident(array $data): array
    {
        return [
            'id' => time(),
            'title' => $data['title'],
            'description' => $data['description'],
            'type' => $data['type'],
            'severity' => $data['severity'],
            'status' => 'open',
            'priority' => $data['priority'] ?? 'medium',
            'assignee' => null,
            'created_at' => time(),
            'updated_at' => time()
        ];
    }

    private function updateIncidentData(int $id, array $data): ?array
    {
        return [
            'id' => $id,
            'title' => $data['title'] ?? "安全事件 #{$id}",
            'description' => $data['description'] ?? "更新的事件描述",
            'type' => $data['type'] ?? 'unknown',
            'severity' => $data['severity'] ?? 'medium',
            'status' => $data['status'] ?? 'open',
            'priority' => $data['priority'] ?? 'medium',
            'updated_at' => time()
        ];
    }

    private function deleteIncidentData(int $id): bool
    {
        return true; // 模拟删除成功
    }

    private function getIncidentClassification(int $id): array
    {
        return [
            'incident_id' => $id,
            'classification' => 'sql_injection',
            'confidence' => 0.95,
            'indicators' => ['sql_keywords', 'malicious_payload'],
            'updated_at' => time()
        ];
    }

    private function updateIncidentClassificationData(int $id, string $classification): array
    {
        return [
            'incident_id' => $id,
            'classification' => $classification,
            'updated_at' => time()
        ];
    }

    private function getIncidentPriorityData(int $id): array
    {
        return [
            'incident_id' => $id,
            'priority' => 'high',
            'updated_at' => time()
        ];
    }

    private function updateIncidentPriorityData(int $id, string $priority): array
    {
        return [
            'incident_id' => $id,
            'priority' => $priority,
            'updated_at' => time()
        ];
    }

    private function getIncidentStatusData(int $id): array
    {
        return [
            'incident_id' => $id,
            'status' => 'in_progress',
            'updated_at' => time()
        ];
    }

    private function updateIncidentStatusData(int $id, string $status): array
    {
        return [
            'incident_id' => $id,
            'status' => $status,
            'updated_at' => time()
        ];
    }

    private function getIncidentAssignmentData(int $id): array
    {
        return [
            'incident_id' => $id,
            'assignee_id' => 'security_team',
            'assigned_at' => time() - 3600
        ];
    }

    private function assignIncidentData(int $id, string $assigneeId): array
    {
        return [
            'incident_id' => $id,
            'assignee_id' => $assigneeId,
            'assigned_at' => time()
        ];
    }

    private function getIncidentCommentsData(int $id): array
    {
        return [
            [
                'id' => 1,
                'incident_id' => $id,
                'content' => '开始分析攻击载荷',
                'author' => 'admin',
                'created_at' => time() - 7200
            ],
            [
                'id' => 2,
                'incident_id' => $id,
                'content' => '已确认SQL注入攻击',
                'author' => 'security_team',
                'created_at' => time() - 3600
            ]
        ];
    }

    private function addIncidentCommentData(int $id, string $content): array
    {
        return [
            'id' => time(),
            'incident_id' => $id,
            'content' => $content,
            'author' => 'current_user',
            'created_at' => time()
        ];
    }

    private function executeResponsePlan(int $incidentId, int $planId): array
    {
        return [
            'execution_id' => time(),
            'incident_id' => $incidentId,
            'plan_id' => $planId,
            'status' => 'running',
            'started_at' => time(),
            'current_step' => 1,
            'total_steps' => 5
        ];
    }

    private function getExecutionStatusData(int $id): array
    {
        return [
            'incident_id' => $id,
            'execution_id' => time() - 3600,
            'status' => 'running',
            'current_step' => 2,
            'total_steps' => 5,
            'started_at' => time() - 3600,
            'estimated_completion' => time() + 1800
        ];
    }
} 