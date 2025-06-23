<?php

namespace AlingAi\Controllers\Api;

use AlingAi\Core\Controller;
use AlingAi\Core\Response;
use AlingAi\Security\AISecurityRedTeamSystem;
use AlingAi\Security\AISecurityBlueTeamSystem;
use AlingAi\Core\Container;
use Psr\Log\LoggerInterface;

/**
 * 红蓝队攻防演练API控制器
 * 
 * 提供攻防演练、场景管理、结果评估、实时监控等功能
 */
class RedBlueTeamApiController extends Controller
{
    private $redTeamSystem;
    private $blueTeamSystem;
    private $logger;
    private $container;

    public function __construct()
    {
        parent::__construct();
        $this->container = Container::getInstance();
        $this->logger = $this->container->get(LoggerInterface::class);
        $this->redTeamSystem = new AISecurityRedTeamSystem($this->logger, $this->container);
        $this->blueTeamSystem = new AISecurityBlueTeamSystem($this->logger, $this->container);
    }

    /**
     * 获取演练场景列表
     * 
     * @return Response
     */
    public function getExerciseScenarios(): Response
    {
        try {
            $scenarios = [
                [
                    'id' => 1,
                    'name' => 'Web应用渗透测试',
                    'description' => '模拟对Web应用的全面渗透测试',
                    'difficulty' => 'medium',
                    'duration' => 120, // 分钟
                    'red_team_tools' => ['nmap', 'burp_suite', 'sqlmap'],
                    'blue_team_tools' => ['waf', 'ids', 'siem'],
                    'targets' => ['web_server', 'database', 'admin_panel'],
                    'objectives' => [
                        '获取管理员权限',
                        '提取敏感数据',
                        '建立持久化访问'
                    ],
                    'status' => 'active'
                ],
                [
                    'id' => 2,
                    'name' => '网络基础设施攻击',
                    'description' => '模拟对网络基础设施的攻击',
                    'difficulty' => 'hard',
                    'duration' => 180,
                    'red_team_tools' => ['metasploit', 'cobalt_strike', 'empire'],
                    'blue_team_tools' => ['firewall', 'ips', 'network_monitor'],
                    'targets' => ['gateway', 'switches', 'servers'],
                    'objectives' => [
                        '网络分段绕过',
                        '横向移动',
                        '数据窃取'
                    ],
                    'status' => 'active'
                ],
                [
                    'id' => 3,
                    'name' => '社会工程学攻击',
                    'description' => '模拟社会工程学攻击场景',
                    'difficulty' => 'easy',
                    'duration' => 60,
                    'red_team_tools' => ['phishing_kit', 'social_engineering_toolkit'],
                    'blue_team_tools' => ['email_filter', 'user_training', 'awareness'],
                    'targets' => ['employees', 'executives', 'contractors'],
                    'objectives' => [
                        '获取凭据',
                        '安装恶意软件',
                        '信息收集'
                    ],
                    'status' => 'active'
                ],
                [
                    'id' => 4,
                    'name' => '无线网络攻击',
                    'description' => '模拟无线网络安全攻击',
                    'difficulty' => 'medium',
                    'duration' => 90,
                    'red_team_tools' => ['aircrack-ng', 'reaver', 'wifite'],
                    'blue_team_tools' => ['wifi_monitor', 'intrusion_detection'],
                    'targets' => ['wifi_networks', 'access_points'],
                    'objectives' => [
                        'WiFi密码破解',
                        '中间人攻击',
                        '网络流量拦截'
                    ],
                    'status' => 'active'
                ],
                [
                    'id' => 5,
                    'name' => '供应链攻击',
                    'description' => '模拟供应链攻击场景',
                    'difficulty' => 'hard',
                    'duration' => 240,
                    'red_team_tools' => ['custom_malware', 'backdoor', 'trojan'],
                    'blue_team_tools' => ['supply_chain_monitor', 'vendor_assessment'],
                    'targets' => ['third_party_systems', 'vendor_access'],
                    'objectives' => [
                        '供应链渗透',
                        '后门植入',
                        '数据泄露'
                    ],
                    'status' => 'active'
                ]
            ];
            
            return Response::success($scenarios, '演练场景列表获取成功');
        } catch (\Exception $e) {
            $this->logger->error('获取演练场景列表失败', ['error' => $e->getMessage()]);
            return Response::error('获取演练场景列表失败: ' . $e->getMessage());
        }
    }

    /**
     * 创建演练场景
     * 
     * @return Response
     */
    public function createExerciseScenario(): Response
    {
        try {
            $data = $this->getRequestData();
            
            // 验证必填字段
            $requiredFields = ['name', 'description', 'difficulty', 'duration'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    return Response::error("缺少必填字段: {$field}");
                }
            }
            
            $scenario = [
                'id' => time(),
                'name' => $data['name'],
                'description' => $data['description'],
                'difficulty' => $data['difficulty'],
                'duration' => $data['duration'],
                'red_team_tools' => $data['red_team_tools'] ?? [],
                'blue_team_tools' => $data['blue_team_tools'] ?? [],
                'targets' => $data['targets'] ?? [],
                'objectives' => $data['objectives'] ?? [],
                'status' => 'active',
                'created_at' => time()
            ];
            
            $this->logger->info('创建演练场景', ['scenario_id' => $scenario['id']]);
            
            return Response::success($scenario, '演练场景创建成功');
        } catch (\Exception $e) {
            $this->logger->error('创建演练场景失败', ['error' => $e->getMessage()]);
            return Response::error('创建演练场景失败: ' . $e->getMessage());
        }
    }

    /**
     * 启动攻防演练
     * 
     * @return Response
     */
    public function startExercise(): Response
    {
        try {
            $data = $this->getRequestData();
            
            if (empty($data['scenario_id']) || empty($data['teams'])) {
                return Response::error('缺少必填字段: scenario_id 或 teams');
            }
            
            $exerciseId = uniqid('exercise_');
            
            // 启动红队系统
            $redTeamConfig = [
                'scenario_id' => $data['scenario_id'],
                'targets' => $data['targets'] ?? [],
                'attack_type' => $data['attack_type'] ?? 'comprehensive'
            ];
            
            $redTeamResults = $this->redTeamSystem->executeAutomatedAttack(
                $data['targets'] ?? [],
                $data['attack_type'] ?? 'comprehensive',
                $redTeamConfig
            );
            
            // 启动蓝队系统
            $blueTeamConfig = [
                'scenario_id' => $data['scenario_id'],
                'defense_level' => $data['defense_level'] ?? 'standard',
                'monitoring_enabled' => true
            ];
            
            $blueTeamResults = $this->blueTeamSystem->startAutomatedDefense($blueTeamConfig);
            
            $exercise = [
                'exercise_id' => $exerciseId,
                'scenario_id' => $data['scenario_id'],
                'teams' => $data['teams'],
                'red_team_results' => $redTeamResults,
                'blue_team_results' => $blueTeamResults,
                'start_time' => time(),
                'status' => 'in_progress',
                'duration' => 0
            ];
            
            $this->logger->info('攻防演练启动', ['exercise_id' => $exerciseId]);
            
            return Response::success($exercise, '攻防演练启动成功');
        } catch (\Exception $e) {
            $this->logger->error('启动攻防演练失败', ['error' => $e->getMessage()]);
            return Response::error('启动攻防演练失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取演练状态
     * 
     * @param string $exerciseId 演练ID
     * @return Response
     */
    public function getExerciseStatus(string $exerciseId): Response
    {
        try {
            // 模拟演练状态
            $status = [
                'exercise_id' => $exerciseId,
                'status' => 'in_progress',
                'progress' => 65, // 百分比
                'elapsed_time' => 78, // 分钟
                'remaining_time' => 42, // 分钟
                'red_team_progress' => [
                    'attacks_executed' => 15,
                    'successful_attacks' => 8,
                    'current_target' => 'database_server',
                    'next_action' => 'privilege_escalation'
                ],
                'blue_team_progress' => [
                    'threats_detected' => 12,
                    'incidents_responded' => 8,
                    'defense_effectiveness' => 75,
                    'current_focus' => 'network_monitoring'
                ],
                'real_time_events' => [
                    [
                        'timestamp' => time() - 300,
                        'type' => 'attack',
                        'description' => 'SQL注入攻击尝试',
                        'severity' => 'medium'
                    ],
                    [
                        'timestamp' => time() - 180,
                        'type' => 'defense',
                        'description' => 'WAF阻止恶意请求',
                        'severity' => 'low'
                    ],
                    [
                        'timestamp' => time() - 60,
                        'type' => 'attack',
                        'description' => '成功获取数据库访问权限',
                        'severity' => 'high'
                    ]
                ]
            ];
            
            return Response::success($status, '演练状态获取成功');
        } catch (\Exception $e) {
            $this->logger->error('获取演练状态失败', ['error' => $e->getMessage()]);
            return Response::error('获取演练状态失败: ' . $e->getMessage());
        }
    }

    /**
     * 执行红队攻击
     * 
     * @return Response
     */
    public function executeRedTeamAttack(): Response
    {
        try {
            $data = $this->getRequestData();
            
            if (empty($data['targets']) || empty($data['attack_type'])) {
                return Response::error('缺少必填字段: targets 或 attack_type');
            }
            
            $attackResults = $this->redTeamSystem->executeAutomatedAttack(
                $data['targets'],
                $data['attack_type'],
                $data['options'] ?? []
            );
            
            $this->logger->info('红队攻击执行完成', [
                'attack_id' => $attackResults['attack_id'],
                'effectiveness_score' => $attackResults['effectiveness_score']
            ]);
            
            return Response::success($attackResults, '红队攻击执行成功');
        } catch (\Exception $e) {
            $this->logger->error('执行红队攻击失败', ['error' => $e->getMessage()]);
            return Response::error('执行红队攻击失败: ' . $e->getMessage());
        }
    }

    /**
     * 执行蓝队防御
     * 
     * @return Response
     */
    public function executeBlueTeamDefense(): Response
    {
        try {
            $data = $this->getRequestData();
            
            $defenseResults = $this->blueTeamSystem->startAutomatedDefense(
                $data['config'] ?? []
            );
            
            $this->logger->info('蓝队防御启动完成', [
                'defense_id' => $defenseResults['defense_id']
            ]);
            
            return Response::success($defenseResults, '蓝队防御启动成功');
        } catch (\Exception $e) {
            $this->logger->error('执行蓝队防御失败', ['error' => $e->getMessage()]);
            return Response::error('执行蓝队防御失败: ' . $e->getMessage());
        }
    }

    /**
     * 执行威胁检测
     * 
     * @return Response
     */
    public function performThreatDetection(): Response
    {
        try {
            $data = $this->getRequestData();
            
            $detectionResults = $this->blueTeamSystem->performThreatDetection(
                $data['data'] ?? [],
                $data['detection_type'] ?? 'comprehensive'
            );
            
            return Response::success($detectionResults, '威胁检测执行成功');
        } catch (\Exception $e) {
            $this->logger->error('执行威胁检测失败', ['error' => $e->getMessage()]);
            return Response::error('执行威胁检测失败: ' . $e->getMessage());
        }
    }

    /**
     * 执行事件响应
     * 
     * @return Response
     */
    public function executeIncidentResponse(): Response
    {
        try {
            $data = $this->getRequestData();
            
            if (empty($data['incident'])) {
                return Response::error('缺少事件信息');
            }
            
            $responseResults = $this->blueTeamSystem->executeIncidentResponse(
                $data['incident'],
                $data['response_level'] ?? 'standard'
            );
            
            return Response::success($responseResults, '事件响应执行成功');
        } catch (\Exception $e) {
            $this->logger->error('执行事件响应失败', ['error' => $e->getMessage()]);
            return Response::error('执行事件响应失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取演练结果
     * 
     * @param string $exerciseId 演练ID
     * @return Response
     */
    public function getExerciseResults(string $exerciseId): Response
    {
        try {
            // 模拟演练结果
            $results = [
                'exercise_id' => $exerciseId,
                'scenario_name' => 'Web应用渗透测试',
                'duration' => 120, // 分钟
                'completion_time' => time(),
                'red_team_score' => 85,
                'blue_team_score' => 78,
                'overall_winner' => 'red_team',
                'red_team_achievements' => [
                    '成功获取管理员权限',
                    '提取敏感数据',
                    '建立持久化访问'
                ],
                'blue_team_achievements' => [
                    '检测到80%的攻击',
                    '成功阻止数据泄露',
                    '快速响应安全事件'
                ],
                'attack_metrics' => [
                    'total_attacks' => 25,
                    'successful_attacks' => 15,
                    'failed_attacks' => 10,
                    'attack_success_rate' => 60
                ],
                'defense_metrics' => [
                    'threats_detected' => 20,
                    'incidents_responded' => 18,
                    'response_time_avg' => 5.2, // 分钟
                    'detection_rate' => 80
                ],
                'lessons_learned' => [
                    '需要加强Web应用安全',
                    '改进威胁检测能力',
                    '优化事件响应流程'
                ],
                'recommendations' => [
                    '实施WAF防护',
                    '加强访问控制',
                    '完善监控体系'
                ]
            ];
            
            return Response::success($results, '演练结果获取成功');
        } catch (\Exception $e) {
            $this->logger->error('获取演练结果失败', ['error' => $e->getMessage()]);
            return Response::error('获取演练结果失败: ' . $e->getMessage());
        }
    }

    /**
     * 生成演练报告
     * 
     * @param string $exerciseId 演练ID
     * @return Response
     */
    public function generateExerciseReport(string $exerciseId): Response
    {
        try {
            $data = $this->getRequestData();
            $format = $data['format'] ?? 'pdf';
            
            $report = [
                'report_id' => uniqid('report_'),
                'exercise_id' => $exerciseId,
                'report_type' => 'exercise_report',
                'format' => $format,
                'generated_at' => time(),
                'file_size' => rand(1000000, 5000000),
                'download_url' => "/downloads/exercises/report_{$exerciseId}.{$format}",
                'status' => 'completed'
            ];
            
            $this->logger->info('演练报告生成完成', [
                'exercise_id' => $exerciseId,
                'report_id' => $report['report_id']
            ]);
            
            return Response::success($report, '演练报告生成成功');
        } catch (\Exception $e) {
            $this->logger->error('生成演练报告失败', ['error' => $e->getMessage()]);
            return Response::error('生成演练报告失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取演练历史
     * 
     * @return Response
     */
    public function getExerciseHistory(): Response
    {
        try {
            $filters = $this->getRequestQuery();
            $limit = $filters['limit'] ?? 10;
            
            $history = [];
            for ($i = 1; $i <= $limit; $i++) {
                $history[] = [
                    'exercise_id' => "exercise_" . (time() - $i * 86400),
                    'scenario_name' => ['Web应用渗透测试', '网络基础设施攻击', '社会工程学攻击'][array_rand(['Web应用渗透测试', '网络基础设施攻击', '社会工程学攻击'])],
                    'start_time' => time() - $i * 86400,
                    'duration' => rand(60, 240),
                    'red_team_score' => rand(60, 95),
                    'blue_team_score' => rand(60, 95),
                    'winner' => ['red_team', 'blue_team', 'draw'][array_rand(['red_team', 'blue_team', 'draw'])],
                    'status' => 'completed'
                ];
            }
            
            return Response::success($history, '演练历史获取成功');
        } catch (\Exception $e) {
            $this->logger->error('获取演练历史失败', ['error' => $e->getMessage()]);
            return Response::error('获取演练历史失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取演练统计
     * 
     * @return Response
     */
    public function getExerciseStatistics(): Response
    {
        try {
            $statistics = [
                'total_exercises' => 156,
                'completed_exercises' => 142,
                'ongoing_exercises' => 3,
                'scheduled_exercises' => 11,
                'red_team_wins' => 78,
                'blue_team_wins' => 52,
                'draws' => 12,
                'average_duration' => 135, // 分钟
                'most_popular_scenario' => 'Web应用渗透测试',
                'success_rate' => 91,
                'monthly_trends' => [
                    'january' => 12,
                    'february' => 15,
                    'march' => 18,
                    'april' => 14,
                    'may' => 16,
                    'june' => 20
                ],
                'team_performance' => [
                    'red_team_avg_score' => 82,
                    'blue_team_avg_score' => 76,
                    'best_red_team_score' => 95,
                    'best_blue_team_score' => 89
                ]
            ];
            
            return Response::success($statistics, '演练统计获取成功');
        } catch (\Exception $e) {
            $this->logger->error('获取演练统计失败', ['error' => $e->getMessage()]);
            return Response::error('获取演练统计失败: ' . $e->getMessage());
        }
    }

    /**
     * 停止演练
     * 
     * @param string $exerciseId 演练ID
     * @return Response
     */
    public function stopExercise(string $exerciseId): Response
    {
        try {
            $result = [
                'exercise_id' => $exerciseId,
                'stop_time' => time(),
                'status' => 'stopped',
                'reason' => '手动停止',
                'final_results' => [
                    'red_team_score' => 85,
                    'blue_team_score' => 78,
                    'winner' => 'red_team'
                ]
            ];
            
            $this->logger->info('演练停止', ['exercise_id' => $exerciseId]);
            
            return Response::success($result, '演练停止成功');
        } catch (\Exception $e) {
            $this->logger->error('停止演练失败', ['error' => $e->getMessage()]);
            return Response::error('停止演练失败: ' . $e->getMessage());
        }
    }

    private function getRequestData(): array
    {
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?? [];
    }

    private function getRequestQuery(): array
    {
        return $_GET ?? [];
    }
} 