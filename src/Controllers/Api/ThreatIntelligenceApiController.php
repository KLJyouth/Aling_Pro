<?php

namespace AlingAi\Controllers\Api;

use AlingAi\Core\Controller;
use AlingAi\Core\Response;
use AlingAi\Security\SecurityIntegrationPlatform;
use AlingAi\Core\Container;
use Psr\Log\LoggerInterface;

/**
 * 威胁情报API控制器
 * 
 * 提供威胁指标、活动、行为者、战术、技术等管理功能
 * 增强功能：威胁情报收集、分析、共享、更新
 */
class ThreatIntelligenceApiController extends Controller
{
    private $securityPlatform;
    private $logger;
    private $container;

    public function __construct()
    {
        parent::__construct();
        $this->container = Container::getInstance();
        $this->logger = $this->container->get(LoggerInterface::class);
        $this->securityPlatform = new SecurityIntegrationPlatform($this->logger, $this->container);
    }

    /**
     * 获取威胁指标列表
     * 
     * @return Response
     */
    public function getThreatIndicators(): Response
    {
        try {
            $filters = $this->getRequestQuery();
            $page = (int)($filters['page'] ?? 1);
            $limit = (int)($filters['limit'] ?? 20);
            $type = $filters['type'] ?? null;
            $threat_level = $filters['threat_level'] ?? null;
            
            $indicators = $this->getThreatIndicatorsList($page, $limit, $type, $threat_level);
            
            return Response::success($indicators, '威胁指标列表获取成功');
        } catch (\Exception $e) {
            $this->logger->error('获取威胁指标列表失败', ['error' => $e->getMessage()]);
            return Response::error('获取威胁指标列表失败: ' . $e->getMessage());
        }
    }

    /**
     * 添加威胁指标
     * 
     * @return Response
     */
    public function addThreatIndicator(): Response
    {
        try {
            $data = $this->getRequestData();
            
            // 验证必填字段
            $requiredFields = ['type', 'value', 'threat_level'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    return Response::error("缺少必填字段: {$field}");
                }
            }
            
            $indicator = $this->createThreatIndicator($data);
            
            $this->logger->info('添加威胁指标', ['indicator_id' => $indicator['id']]);
            
            return Response::success($indicator, '威胁指标添加成功');
        } catch (\Exception $e) {
            $this->logger->error('添加威胁指标失败', ['error' => $e->getMessage()]);
            return Response::error('添加威胁指标失败: ' . $e->getMessage());
        }
    }

    /**
     * 更新威胁指标
     * 
     * @param int $id 指标ID
     * @return Response
     */
    public function updateThreatIndicator(int $id): Response
    {
        try {
            $data = $this->getRequestData();
            
            $indicator = $this->updateThreatIndicatorData($id, $data);
            
            if (!$indicator) {
                return Response::error('威胁指标不存在', 404);
            }
            
            $this->logger->info('更新威胁指标', ['indicator_id' => $id]);
            
            return Response::success($indicator, '威胁指标更新成功');
        } catch (\Exception $e) {
            $this->logger->error('更新威胁指标失败', ['error' => $e->getMessage()]);
            return Response::error('更新威胁指标失败: ' . $e->getMessage());
        }
    }

    /**
     * 删除威胁指标
     * 
     * @param int $id 指标ID
     * @return Response
     */
    public function deleteThreatIndicator(int $id): Response
    {
        try {
            $result = $this->deleteThreatIndicatorData($id);
            
            if (!$result) {
                return Response::error('威胁指标不存在或无法删除', 404);
            }
            
            $this->logger->info('删除威胁指标', ['indicator_id' => $id]);
            
            return Response::success(['id' => $id], '威胁指标删除成功');
        } catch (\Exception $e) {
            $this->logger->error('删除威胁指标失败', ['error' => $e->getMessage()]);
            return Response::error('删除威胁指标失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取威胁活动列表
     * 
     * @return Response
     */
    public function getThreatCampaigns(): Response
    {
        try {
            $campaigns = [
                [
                    'id' => 1,
                    'name' => 'APT-29 钓鱼活动',
                    'description' => '针对政府机构的钓鱼攻击活动',
                    'threat_actor' => 'APT-29',
                    'target_sectors' => ['government', 'defense'],
                    'attack_techniques' => ['phishing', 'spear_phishing', 'credential_theft'],
                    'first_seen' => time() - 86400 * 30,
                    'last_seen' => time() - 86400 * 5,
                    'status' => 'active',
                    'threat_level' => 'high',
                    'indicators_count' => 45
                ],
                [
                    'id' => 2,
                    'name' => 'Lazarus 银行攻击',
                    'description' => '针对金融机构的恶意软件攻击',
                    'threat_actor' => 'Lazarus Group',
                    'target_sectors' => ['financial', 'banking'],
                    'attack_techniques' => ['malware', 'ransomware', 'data_exfiltration'],
                    'first_seen' => time() - 86400 * 60,
                    'last_seen' => time() - 86400 * 10,
                    'status' => 'active',
                    'threat_level' => 'critical',
                    'indicators_count' => 78
                ],
                [
                    'id' => 3,
                    'name' => 'Emotet 垃圾邮件活动',
                    'description' => '大规模垃圾邮件传播恶意软件',
                    'threat_actor' => 'Unknown',
                    'target_sectors' => ['all'],
                    'attack_techniques' => ['spam', 'malware_distribution', 'botnet'],
                    'first_seen' => time() - 86400 * 90,
                    'last_seen' => time() - 86400 * 2,
                    'status' => 'active',
                    'threat_level' => 'medium',
                    'indicators_count' => 156
                ]
            ];
            
            return Response::success($campaigns, '威胁活动列表获取成功');
        } catch (\Exception $e) {
            $this->logger->error('获取威胁活动列表失败', ['error' => $e->getMessage()]);
            return Response::error('获取威胁活动列表失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取威胁活动详情
     * 
     * @param int $id 活动ID
     * @return Response
     */
    public function getThreatCampaign(int $id): Response
    {
        try {
            $campaign = $this->getThreatCampaignDetails($id);
            
            if (!$campaign) {
                return Response::error('威胁活动不存在', 404);
            }
            
            return Response::success($campaign, '威胁活动详情获取成功');
        } catch (\Exception $e) {
            $this->logger->error('获取威胁活动详情失败', ['error' => $e->getMessage()]);
            return Response::error('获取威胁活动详情失败: ' . $e->getMessage());
        }
    }

    /**
     * 创建威胁活动
     * 
     * @return Response
     */
    public function createThreatCampaign(): Response
    {
        try {
            $data = $this->getRequestData();
            
            // 验证必填字段
            $requiredFields = ['name', 'description', 'threat_actor'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    return Response::error("缺少必填字段: {$field}");
                }
            }
            
            $campaign = $this->createThreatCampaignData($data);
            
            $this->logger->info('创建威胁活动', ['campaign_id' => $campaign['id']]);
            
            return Response::success($campaign, '威胁活动创建成功');
        } catch (\Exception $e) {
            $this->logger->error('创建威胁活动失败', ['error' => $e->getMessage()]);
            return Response::error('创建威胁活动失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取威胁行为者列表
     * 
     * @return Response
     */
    public function getThreatActors(): Response
    {
        try {
            $actors = [
                [
                    'id' => 1,
                    'name' => 'APT-29',
                    'aliases' => ['Cozy Bear', 'CozyDuke', 'The Dukes'],
                    'country' => 'Russia',
                    'motivation' => 'espionage',
                    'target_sectors' => ['government', 'defense', 'energy'],
                    'attack_techniques' => ['phishing', 'spear_phishing', 'malware', 'credential_theft'],
                    'first_seen' => time() - 86400 * 365 * 5,
                    'last_seen' => time() - 86400 * 5,
                    'threat_level' => 'high',
                    'campaigns_count' => 12
                ],
                [
                    'id' => 2,
                    'name' => 'Lazarus Group',
                    'aliases' => ['Hidden Cobra', 'Guardians of Peace'],
                    'country' => 'North Korea',
                    'motivation' => 'financial_gain',
                    'target_sectors' => ['financial', 'banking', 'cryptocurrency'],
                    'attack_techniques' => ['malware', 'ransomware', 'data_exfiltration', 'destructive_attacks'],
                    'first_seen' => time() - 86400 * 365 * 8,
                    'last_seen' => time() - 86400 * 10,
                    'threat_level' => 'critical',
                    'campaigns_count' => 25
                ],
                [
                    'id' => 3,
                    'name' => 'APT-41',
                    'aliases' => ['BARIUM', 'Winnti Group'],
                    'country' => 'China',
                    'motivation' => 'espionage',
                    'target_sectors' => ['technology', 'gaming', 'healthcare'],
                    'attack_techniques' => ['supply_chain_attacks', 'malware', 'credential_theft'],
                    'first_seen' => time() - 86400 * 365 * 6,
                    'last_seen' => time() - 86400 * 15,
                    'threat_level' => 'high',
                    'campaigns_count' => 18
                ]
            ];
            
            return Response::success($actors, '威胁行为者列表获取成功');
        } catch (\Exception $e) {
            $this->logger->error('获取威胁行为者列表失败', ['error' => $e->getMessage()]);
            return Response::error('获取威胁行为者列表失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取威胁行为者详情
     * 
     * @param int $id 行为者ID
     * @return Response
     */
    public function getThreatActor(int $id): Response
    {
        try {
            $actor = $this->getThreatActorDetails($id);
            
            if (!$actor) {
                return Response::error('威胁行为者不存在', 404);
            }
            
            return Response::success($actor, '威胁行为者详情获取成功');
        } catch (\Exception $e) {
            $this->logger->error('获取威胁行为者详情失败', ['error' => $e->getMessage()]);
            return Response::error('获取威胁行为者详情失败: ' . $e->getMessage());
        }
    }

    /**
     * 创建威胁行为者
     * 
     * @return Response
     */
    public function createThreatActor(): Response
    {
        try {
            $data = $this->getRequestData();
            
            // 验证必填字段
            $requiredFields = ['name', 'country', 'motivation'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    return Response::error("缺少必填字段: {$field}");
                }
            }
            
            $actor = $this->createThreatActorData($data);
            
            $this->logger->info('创建威胁行为者', ['actor_id' => $actor['id']]);
            
            return Response::success($actor, '威胁行为者创建成功');
        } catch (\Exception $e) {
            $this->logger->error('创建威胁行为者失败', ['error' => $e->getMessage()]);
            return Response::error('创建威胁行为者失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取威胁战术列表
     * 
     * @return Response
     */
    public function getThreatTactics(): Response
    {
        try {
            $tactics = [
                [
                    'id' => 'TA0001',
                    'name' => '初始访问',
                    'description' => '攻击者试图进入网络',
                    'techniques_count' => 9,
                    'examples' => ['钓鱼攻击', '漏洞利用', '供应链攻击']
                ],
                [
                    'id' => 'TA0002',
                    'name' => '执行',
                    'description' => '攻击者试图运行恶意代码',
                    'techniques_count' => 14,
                    'examples' => ['命令行界面', '用户执行', '服务执行']
                ],
                [
                    'id' => 'TA0003',
                    'name' => '持久化',
                    'description' => '攻击者试图保持其存在',
                    'techniques_count' => 19,
                    'examples' => ['账户操作', '引导或登录自动启动', '计划任务/作业']
                ],
                [
                    'id' => 'TA0004',
                    'name' => '权限提升',
                    'description' => '攻击者试图获得更高级别的权限',
                    'techniques_count' => 14,
                    'examples' => ['利用漏洞', '进程注入', '令牌操作']
                ],
                [
                    'id' => 'TA0005',
                    'name' => '防御规避',
                    'description' => '攻击者试图避免被发现',
                    'techniques_count' => 17,
                    'examples' => ['禁用或修改工具', '隐藏工件', '混淆文件或信息']
                ]
            ];
            
            return Response::success($tactics, '威胁战术列表获取成功');
        } catch (\Exception $e) {
            $this->logger->error('获取威胁战术列表失败', ['error' => $e->getMessage()]);
            return Response::error('获取威胁战术列表失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取威胁技术列表
     * 
     * @return Response
     */
    public function getThreatTechniques(): Response
    {
        try {
            $techniques = [
                [
                    'id' => 'T1078',
                    'name' => '有效账户',
                    'tactic' => 'TA0001',
                    'description' => '攻击者可能获取和滥用现有账户的凭据',
                    'sub_techniques' => [
                        ['id' => 'T1078.001', 'name' => '默认账户'],
                        ['id' => 'T1078.002', 'name' => '域账户'],
                        ['id' => 'T1078.003', 'name' => '本地账户']
                    ]
                ],
                [
                    'id' => 'T1059',
                    'name' => '命令和脚本解释器',
                    'tactic' => 'TA0002',
                    'description' => '攻击者可能滥用命令和脚本解释器来执行命令',
                    'sub_techniques' => [
                        ['id' => 'T1059.001', 'name' => 'PowerShell'],
                        ['id' => 'T1059.002', 'name' => 'AppleScript'],
                        ['id' => 'T1059.003', 'name' => 'Windows命令Shell']
                    ]
                ],
                [
                    'id' => 'T1136',
                    'name' => '创建账户',
                    'tactic' => 'TA0003',
                    'description' => '攻击者可能创建一个账户来维持对系统的访问',
                    'sub_techniques' => [
                        ['id' => 'T1136.001', 'name' => '本地账户'],
                        ['id' => 'T1136.002', 'name' => '域账户']
                    ]
                ]
            ];
            
            return Response::success($techniques, '威胁技术列表获取成功');
        } catch (\Exception $e) {
            $this->logger->error('获取威胁技术列表失败', ['error' => $e->getMessage()]);
            return Response::error('获取威胁技术列表失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取恶意软件情报
     * 
     * @return Response
     */
    public function getMalwareIntel(): Response
    {
        try {
            $malware = [
                [
                    'id' => 1,
                    'name' => 'Emotet',
                    'type' => 'banking_trojan',
                    'description' => '高级银行木马，具有模块化架构',
                    'threat_level' => 'high',
                    'first_seen' => time() - 86400 * 365 * 5,
                    'last_seen' => time() - 86400 * 2,
                    'indicators_count' => 234,
                    'affected_countries' => ['US', 'UK', 'DE', 'FR'],
                    'capabilities' => ['credential_theft', 'email_hijacking', 'lateral_movement']
                ],
                [
                    'id' => 2,
                    'name' => 'WannaCry',
                    'type' => 'ransomware',
                    'description' => '利用EternalBlue漏洞传播的勒索软件',
                    'threat_level' => 'critical',
                    'first_seen' => time() - 86400 * 365 * 3,
                    'last_seen' => time() - 86400 * 30,
                    'indicators_count' => 89,
                    'affected_countries' => ['US', 'UK', 'CN', 'RU'],
                    'capabilities' => ['file_encryption', 'network_propagation', 'destructive_attacks']
                ],
                [
                    'id' => 3,
                    'name' => 'TrickBot',
                    'type' => 'banking_trojan',
                    'description' => '模块化银行木马，具有多种功能',
                    'threat_level' => 'high',
                    'first_seen' => time() - 86400 * 365 * 4,
                    'last_seen' => time() - 86400 * 5,
                    'indicators_count' => 156,
                    'affected_countries' => ['US', 'UK', 'DE', 'AU'],
                    'capabilities' => ['credential_theft', 'cryptocurrency_mining', 'ransomware_deployment']
                ]
            ];
            
            return Response::success($malware, '恶意软件情报获取成功');
        } catch (\Exception $e) {
            $this->logger->error('获取恶意软件情报失败', ['error' => $e->getMessage()]);
            return Response::error('获取恶意软件情报失败: ' . $e->getMessage());
        }
    }

    /**
     * 搜索威胁情报
     * 
     * @return Response
     */
    public function searchThreatIntel(): Response
    {
        try {
            $data = $this->getRequestData();
            $query = $data['query'] ?? '';
            $type = $data['type'] ?? 'all';
            
            if (empty($query)) {
                return Response::error('搜索查询不能为空');
            }
            
            $results = $this->searchThreatIntelligence($query, $type);
            
            return Response::success($results, '威胁情报搜索成功');
        } catch (\Exception $e) {
            $this->logger->error('搜索威胁情报失败', ['error' => $e->getMessage()]);
            return Response::error('搜索威胁情报失败: ' . $e->getMessage());
        }
    }

    /**
     * 同步威胁情报
     * 
     * @return Response
     */
    public function syncThreatIntel(): Response
    {
        try {
            $data = $this->getRequestData();
            $source = $data['source'] ?? 'all';
            
            $syncResult = $this->synchronizeThreatIntelligence($source);
            
            $this->logger->info('同步威胁情报', ['source' => $source, 'result' => $syncResult]);
            
            return Response::success($syncResult, '威胁情报同步成功');
        } catch (\Exception $e) {
            $this->logger->error('同步威胁情报失败', ['error' => $e->getMessage()]);
            return Response::error('同步威胁情报失败: ' . $e->getMessage());
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

    private function getThreatIndicatorsList(int $page, int $limit, ?string $type, ?string $threat_level): array
    {
        // 模拟威胁指标数据
        $indicators = [];
        for ($i = 1; $i <= $limit; $i++) {
            $indicatorId = ($page - 1) * $limit + $i;
            $indicators[] = [
                'id' => $indicatorId,
                'type' => ['ip', 'domain', 'url', 'hash'][array_rand(['ip', 'domain', 'url', 'hash'])],
                'value' => $this->generateIndicatorValue($indicatorId),
                'threat_level' => ['low', 'medium', 'high', 'critical'][array_rand(['low', 'medium', 'high', 'critical'])],
                'confidence' => rand(50, 100) / 100,
                'first_seen' => time() - rand(0, 86400 * 30),
                'last_seen' => time() - rand(0, 86400 * 7),
                'tags' => ['malware', 'phishing', 'botnet'][array_rand(['malware', 'phishing', 'botnet'])]
            ];
        }
        
        return [
            'indicators' => $indicators,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => 1250,
                'pages' => ceil(1250 / $limit)
            ]
        ];
    }

    private function generateIndicatorValue(int $id): string
    {
        $types = [
            'ip' => rand(1, 255) . '.' . rand(1, 255) . '.' . rand(1, 255) . '.' . rand(1, 255),
            'domain' => 'malicious' . $id . '.com',
            'url' => 'https://malicious' . $id . '.com/payload',
            'hash' => md5('malware' . $id)
        ];
        
        return $types[array_rand($types)];
    }

    private function createThreatIndicator(array $data): array
    {
        return [
            'id' => time(),
            'type' => $data['type'],
            'value' => $data['value'],
            'threat_level' => $data['threat_level'],
            'confidence' => $data['confidence'] ?? 0.8,
            'first_seen' => time(),
            'last_seen' => time(),
            'tags' => $data['tags'] ?? []
        ];
    }

    private function updateThreatIndicatorData(int $id, array $data): ?array
    {
        return [
            'id' => $id,
            'type' => $data['type'] ?? 'unknown',
            'value' => $data['value'] ?? '',
            'threat_level' => $data['threat_level'] ?? 'medium',
            'confidence' => $data['confidence'] ?? 0.8,
            'updated_at' => time()
        ];
    }

    private function deleteThreatIndicatorData(int $id): bool
    {
        return true; // 模拟删除成功
    }

    private function getThreatCampaignDetails(int $id): ?array
    {
        return [
            'id' => $id,
            'name' => "威胁活动 #{$id}",
            'description' => '这是一个详细的威胁活动描述',
            'threat_actor' => 'APT-29',
            'target_sectors' => ['government', 'defense'],
            'attack_techniques' => ['phishing', 'spear_phishing'],
            'first_seen' => time() - 86400 * 30,
            'last_seen' => time() - 86400 * 5,
            'status' => 'active',
            'threat_level' => 'high',
            'indicators' => [
                ['type' => 'ip', 'value' => '192.168.1.100'],
                ['type' => 'domain', 'value' => 'malicious.com'],
                ['type' => 'url', 'value' => 'https://malicious.com/payload']
            ]
        ];
    }

    private function createThreatCampaignData(array $data): array
    {
        return [
            'id' => time(),
            'name' => $data['name'],
            'description' => $data['description'],
            'threat_actor' => $data['threat_actor'],
            'target_sectors' => $data['target_sectors'] ?? [],
            'attack_techniques' => $data['attack_techniques'] ?? [],
            'first_seen' => time(),
            'last_seen' => time(),
            'status' => 'active',
            'threat_level' => $data['threat_level'] ?? 'medium'
        ];
    }

    private function getThreatActorDetails(int $id): ?array
    {
        return [
            'id' => $id,
            'name' => "威胁行为者 #{$id}",
            'aliases' => ['Alias1', 'Alias2'],
            'country' => 'Unknown',
            'motivation' => 'espionage',
            'target_sectors' => ['government', 'defense'],
            'attack_techniques' => ['phishing', 'malware'],
            'first_seen' => time() - 86400 * 365,
            'last_seen' => time() - 86400 * 10,
            'threat_level' => 'high',
            'campaigns' => [
                ['id' => 1, 'name' => 'Campaign 1'],
                ['id' => 2, 'name' => 'Campaign 2']
            ]
        ];
    }

    private function createThreatActorData(array $data): array
    {
        return [
            'id' => time(),
            'name' => $data['name'],
            'aliases' => $data['aliases'] ?? [],
            'country' => $data['country'],
            'motivation' => $data['motivation'],
            'target_sectors' => $data['target_sectors'] ?? [],
            'attack_techniques' => $data['attack_techniques'] ?? [],
            'first_seen' => time(),
            'last_seen' => time(),
            'threat_level' => $data['threat_level'] ?? 'medium'
        ];
    }

    private function searchThreatIntelligence(string $query, string $type): array
    {
        // 模拟搜索结果
        return [
            'query' => $query,
            'type' => $type,
            'total_results' => rand(10, 100),
            'results' => [
                [
                    'type' => 'indicator',
                    'id' => 1,
                    'value' => $query,
                    'threat_level' => 'high',
                    'confidence' => 0.9
                ],
                [
                    'type' => 'campaign',
                    'id' => 1,
                    'name' => "Campaign containing {$query}",
                    'threat_level' => 'medium'
                ]
            ]
        ];
    }

    private function synchronizeThreatIntelligence(string $source): array
    {
        // 模拟同步结果
        return [
            'source' => $source,
            'sync_time' => time(),
            'new_indicators' => rand(10, 50),
            'updated_indicators' => rand(5, 20),
            'deleted_indicators' => rand(1, 10),
            'status' => 'completed'
        ];
    }
} 