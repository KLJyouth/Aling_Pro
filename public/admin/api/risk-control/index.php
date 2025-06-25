<?php
/**
 * AlingAi Pro 5.0 - 风险控制API
 * 用户行为分析、异常检测、安全监控等
 */

declare(strict_types=1];

header('Content-Type: application/json; charset=utf-8'];
header('Access-Control-Allow-Origin: *'];
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS'];
header('Access-Control-Allow-Headers: Content-Type, Authorization'];

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200];
    exit(];
}

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../../../../src/Auth/AdminAuthServiceDemo.php';

use AlingAi\Auth\AdminAuthServiceDemo;

// 响应函数
function sendResponse($success, $data = null, $message = '', $code = 200)
{
    http_response_code($code];
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ],  JSON_UNESCAPED_UNICODE];
    exit(];
}

function handleError($message, $code = 500) {
    error_log("Risk Control API Error: $message"];
    sendResponse(false, null, $message, $code];
}

// 获取请求信息
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'],  PHP_URL_PATH];
$pathSegments = explode('/', trim($path, '/')];

try {
    // 验证管理员权�?
    $authService = new AdminAuthServiceDemo(];
    $headers = getallheaders(];
    $token = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    
    if (strpos($token, 'Bearer ') === 0) {
        $token = substr($token, 7];
    }
    
    if (!$token) {
        sendResponse(false, null, '缺少授权令牌', 401];
    }
    
    $user = $authService->validateToken($token];
    if (!$user || !$authService->hasPermission($user['id'],  'risk.control')) {
        sendResponse(false, null, '权限不足', 403];
    }
    
    // 解析路由
    $action = $pathSegments[4] ?? '';
    $subAction = $pathSegments[5] ?? '';
    
    switch ($action) {
        case 'dashboard':
            handleRiskDashboard(];
            break;
            
        case 'rules':
            handleRiskRules($method, $subAction];
            break;
            
        case 'events':
            handleRiskEvents($method, $subAction];
            break;
            
        case 'analysis':
            handleRiskAnalysis($subAction];
            break;
            
        case 'blacklist':
            handleBlacklist($method, $subAction];
            break;
            
        case 'whitelist':
            handleWhitelist($method, $subAction];
            break;
            
        case 'reports':
            handleRiskReports($subAction];
            break;
            
        case 'settings':
            handleRiskSettings($method];
            break;
            
        default:
            handleOverview(];
    }
    
} catch (Exception $e) {
    handleError($e->getMessage()];
}

/**
 * 风险控制总览
 */
function handleOverview() {
    try {
        $overview = [
            'risk_level' => getRiskLevel(),
            'active_rules' => getActiveRulesCount(),
            'recent_events' => getRecentRiskEvents(),
            'statistics' => getRiskStatistics(),
            'trends' => getRiskTrends()
        ];
        
        sendResponse(true, $overview, '获取风险控制总览成功'];
        
    } catch (Exception $e) {
        handleError('获取风险控制总览失败: ' . $e->getMessage()];
    }
}

/**
 * 风险仪表�?
 */
function handleRiskDashboard() {
    try {
        $dashboard = [
            'overview' => [
                'total_events' => rand(100, 1000],
                'high_risk_events' => rand(10, 50],
                'blocked_attempts' => rand(20, 100],
                'risk_score' => rand(20, 80)
            ], 
            'event_types' => getEventTypeDistribution(),
            'risk_trends' => getRiskTrendData(),
            'top_risks' => getTopRisks(),
            'recent_events' => getRecentRiskEvents(10],
            'geographical_risks' => getGeographicalRisks(),
            'time_distribution' => getTimeDistribution()
        ];
        
        sendResponse(true, $dashboard, '获取风险仪表板数据成�?];
    } catch (Exception $e) {
        handleError('获取风险仪表板失�? ' . $e->getMessage()];
    }
}

/**
 * 风险规则管理
 */
function handleRiskRules($method, $ruleId) {
    try {
        switch ($method) {
            case 'GET':
                if ($ruleId) {
                    $rule = getRiskRule($ruleId];
                    sendResponse(true, $rule, '获取风险规则成功'];
                } else {
                    $rules = getRiskRules(];
                    sendResponse(true, $rules, '获取风险规则列表成功'];
                }
                break;
                
            case 'POST':
                $input = json_decode(file_get_contents('php://input'], true];
                $rule = createRiskRule($input];
                sendResponse(true, $rule, '创建风险规则成功', 201];
                break;
                
            case 'PUT':
                if (!$ruleId) {
                    sendResponse(false, null, '规则ID不能为空', 400];
                }
                $input = json_decode(file_get_contents('php://input'], true];
                $rule = updateRiskRule($ruleId, $input];
                sendResponse(true, $rule, '更新风险规则成功'];
                break;
                
            case 'DELETE':
                if (!$ruleId) {
                    sendResponse(false, null, '规则ID不能为空', 400];
                }
                deleteRiskRule($ruleId];
                sendResponse(true, null, '删除风险规则成功'];
                break;
                
            default:
                sendResponse(false, null, '不支持的请求方法', 405];
        }
        
    } catch (Exception $e) {
        handleError('处理风险规则失败: ' . $e->getMessage()];
    }
}

/**
 * 风险事件管理
 */
function handleRiskEvents($method, $eventId) {
    try {
        switch ($method) {
            case 'GET':
                if ($eventId) {
                    $event = getRiskEvent($eventId];
                    sendResponse(true, $event, '获取风险事件详情成功'];
                } else {
                    $page = (int)($_GET['page'] ?? 1];
                    $limit = min((int)($_GET['limit'] ?? 20], 100];
                    $severity = $_GET['severity'] ?? '';
                    $status = $_GET['status'] ?? '';
                    $type = $_GET['type'] ?? '';
                    $dateFrom = $_GET['date_from'] ?? '';
                    $dateTo = $_GET['date_to'] ?? '';
                    
                    $events = getRiskEvents($page, $limit, $severity, $status, $type, $dateFrom, $dateTo];
                    sendResponse(true, $events, '获取风险事件列表成功'];
                }
                break;
                
            case 'PUT':
                if (!$eventId) {
                    sendResponse(false, null, '事件ID不能为空', 400];
                }
                $input = json_decode(file_get_contents('php://input'], true];
                $event = updateRiskEvent($eventId, $input];
                sendResponse(true, $event, '更新风险事件成功'];
                break;
                
            default:
                sendResponse(false, null, '不支持的请求方法', 405];
        }
        
    } catch (Exception $e) {
        handleError('处理风险事件失败: ' . $e->getMessage()];
    }
}

/**
 * 风险分析
 */
function handleRiskAnalysis($type) {
    try {
        switch ($type) {
            case 'user':
                $userId = $_GET['user_id'] ?? null;
                $analysis = analyzeUserRisk($userId];
                break;
                
            case 'ip':
                $ip = $_GET['ip'] ?? null;
                $analysis = analyzeIPRisk($ip];
                break;
                
            case 'behavior':
                $analysis = analyzeBehaviorRisk(];
                break;
                
            case 'pattern':
                $analysis = analyzePatternRisk(];
                break;
                
            default:
                $analysis = getGeneralRiskAnalysis(];
        }
        
        sendResponse(true, $analysis, '风险分析完成'];
        
    } catch (Exception $e) {
        handleError('风险分析失败: ' . $e->getMessage()];
    }
}

/**
 * 黑名单管�?
 */
function handleBlacklist($method, $id) {
    try {
        switch ($method) {
            case 'GET':
                if ($id) {
                    $item = getBlacklistItem($id];
                    sendResponse(true, $item, '获取黑名单项成功'];
                } else {
                    $type = $_GET['type'] ?? 'all'; // ip, user, email, all
                    $blacklist = getBlacklist($type];
                    sendResponse(true, $blacklist, '获取黑名单成�?];
                }
                break;
                
            case 'POST':
                $input = json_decode(file_get_contents('php://input'], true];
                $item = addToBlacklist($input];
                sendResponse(true, $item, '添加到黑名单成功', 201];
                break;
                
            case 'DELETE':
                if (!$id) {
                    sendResponse(false, null, 'ID不能为空', 400];
                }
                removeFromBlacklist($id];
                sendResponse(true, null, '从黑名单移除成功'];
                break;
                
            default:
                sendResponse(false, null, '不支持的请求方法', 405];
        }
        
    } catch (Exception $e) {
        handleError('处理黑名单失�? ' . $e->getMessage()];
    }
}

/**
 * 白名单管�?
 */
function handleWhitelist($method, $id) {
    try {
        switch ($method) {
            case 'GET':
                if ($id) {
                    $item = getWhitelistItem($id];
                    sendResponse(true, $item, '获取白名单项成功'];
                } else {
                    $type = $_GET['type'] ?? 'all'; // ip, user, email, all
                    $whitelist = getWhitelist($type];
                    sendResponse(true, $whitelist, '获取白名单成�?];
                }
                break;
                
            case 'POST':
                $input = json_decode(file_get_contents('php://input'], true];
                $item = addToWhitelist($input];
                sendResponse(true, $item, '添加到白名单成功', 201];
                break;
                
            case 'DELETE':
                if (!$id) {
                    sendResponse(false, null, 'ID不能为空', 400];
                }
                removeFromWhitelist($id];
                sendResponse(true, null, '从白名单移除成功'];
                break;
                
            default:
                sendResponse(false, null, '不支持的请求方法', 405];
        }
        
    } catch (Exception $e) {
        handleError('处理白名单失�? ' . $e->getMessage()];
    }
}

/**
 * 风险报告
 */
function handleRiskReports($type) {
    try {
        $dateFrom = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days')];
        $dateTo = $_GET['date_to'] ?? date('Y-m-d'];
        
        switch ($type) {
            case 'daily':
                $report = generateDailyReport($dateFrom, $dateTo];
                break;
                
            case 'weekly':
                $report = generateWeeklyReport($dateFrom, $dateTo];
                break;
                
            case 'monthly':
                $report = generateMonthlyReport($dateFrom, $dateTo];
                break;
                
            case 'summary':
                $report = generateSummaryReport($dateFrom, $dateTo];
                break;
                
            default:
                $report = generateCustomReport($dateFrom, $dateTo];
        }
        
        sendResponse(true, $report, '风险报告生成成功'];
        
    } catch (Exception $e) {
        handleError('生成风险报告失败: ' . $e->getMessage()];
    }
}

/**
 * 风险设置
 */
function handleRiskSettings($method) {
    try {
        switch ($method) {
            case 'GET':
                $settings = getRiskSettings(];
                sendResponse(true, $settings, '获取风险设置成功'];
                break;
                
            case 'PUT':
                $input = json_decode(file_get_contents('php://input'], true];
                $settings = updateRiskSettings($input];
                sendResponse(true, $settings, '更新风险设置成功'];
                break;
                
            default:
                sendResponse(false, null, '不支持的请求方法', 405];
        }
        
    } catch (Exception $e) {
        handleError('处理风险设置失败: ' . $e->getMessage()];
    }
}

// ================================
// 辅助函数
// ================================

function getRiskLevel(): array
{
    $level = rand(1, 5];
    $levels = [
        1 => ['name' => '低风�?, 'color' => 'green'], 
        2 => ['name' => '较低风险', 'color' => 'yellow'], 
        3 => ['name' => '中等风险', 'color' => 'orange'], 
        4 => ['name' => '高风�?, 'color' => 'red'], 
        5 => ['name' => '极高风险', 'color' => 'darkred']
    ];
    
    return [
        'name' => $levels[$level]['name'], 
        'color' => $levels[$level]['color'], 
        'score' => rand(20, 100)
    ];
}

function getActiveRulesCount(): int
{
    return rand(10, 50];
}

function getRecentRiskEvents($limit = 5): array
{
    $events = [];
    $types = ['login_anomaly', 'suspicious_behavior', 'rate_limit_exceed', 'geo_anomaly', 'bot_detection'];
    $severities = ['low', 'medium', 'high', 'critical'];
    
    for ($i = 0; $i < $limit; $i++) {
        $events[] = [
            'id' => $i + 1,
            'type' => $types[array_rand($types)], 
            'severity' => $severities[array_rand($severities)], 
            'user_id' => rand(1, 1000],
            'ip_address' => generateRandomIP(),
            'description' => generateEventDescription(),
            'created_at' => date('Y-m-d H:i:s', time() - rand(0, 86400)],
            'status' => ['pending', 'resolved', 'investigating'][array_rand(['pending', 'resolved', 'investigating'])]
        ];
    }
    
    return $events;
}

function getRiskStatistics(): array
{
    return [
        'blocked_attempts' => rand(10, 50],
        'false_positives' => rand(2, 15],
        'average_risk_score' => rand(30, 70],
        'rules_triggered' => rand(5, 25],
        'users_flagged' => rand(3, 20)
    ];
}

function getRiskTrends(): array
{
    $trends = [];
    for ($i = 6; $i >= 0; $i--) {
        $trends[] = [
            'date' => date('Y-m-d', strtotime("-{$i} days")],
            'events' => rand(20, 100],
            'high_risk' => rand(5, 20],
            'blocked' => rand(3, 15)
        ];
    }
    
    return $trends;
}

function getEventTypeDistribution(): array
{
    return [
        'suspicious_behavior' => rand(15, 30],
        'rate_limit_exceed' => rand(10, 25],
        'geo_anomaly' => rand(5, 15],
        'bot_detection' => rand(8, 20],
        'other' => rand(2, 10)
    ];
}

function getRiskTrendData(): array
{
    $data = [];
    for ($i = 23; $i >= 0; $i--) {
        $data[] = [
            'hour' => date('H:i', time() - $i * 3600],
            'events' => rand(5, 50],
            'risk_score' => rand(20, 80)
        ];
    }
    
    return $data;
}

function getTopRisks(): array
{
    return [
        ['type' => '频率限制', 'count' => rand(15, 40], 'trend' => 'down'], 
        ['type' => '地理位置异常', 'count' => rand(10, 30], 'trend' => 'stable'], 
        ['type' => 'Bot检�?, 'count' => rand(8, 25], 'trend' => 'up'], 
        ['type' => '可疑行为', 'count' => rand(5, 20], 'trend' => 'down']
    ];
}

function getGeographicalRisks(): array
{
    return [
        ['country' => '美国', 'events' => rand(20, 60], 'risk_level' => 'medium'], 
        ['country' => '俄罗�?, 'events' => rand(10, 30], 'risk_level' => 'high'], 
        ['country' => '朝鲜', 'events' => rand(1, 10], 'risk_level' => 'critical']
    ];
}

function getTimeDistribution(): array
{
    $distribution = [];
    for ($hour = 0; $hour < 24; $hour++) {
        $distribution[] = [
            'hour' => sprintf('%02d:00', $hour],
            'events' => rand(5, 50],
            'risk_level' => rand(1, 5)
        ];
    }
    
    return $distribution;
}

function getRiskRules(): array
{
    return [
        [
            'name' => '异常登录检�?,
            'description' => '检测异常的登录行为',
            'type' => 'login_anomaly',
            'enabled' => true,
            'severity' => 'high',
            'conditions' => [
                'failed_attempts_threshold' => 5,
                'time_window' => 300,
                'geo_check' => true
            ], 
            'actions' => ['block', 'alert'], 
            'created_at' => date('Y-m-d H:i:s', strtotime('-30 days')],
            'triggered_count' => rand(50, 200)
        ], 
        [
            'id' => 2,
            'name' => '频率限制',
            'description' => '限制API调用频率',
            'type' => 'rate_limit',
            'enabled' => true,
            'severity' => 'medium',
            'conditions' => [
                'requests_per_minute' => 100,
                'requests_per_hour' => 1000
            ], 
            'actions' => ['throttle', 'alert'], 
            'created_at' => date('Y-m-d H:i:s', strtotime('-20 days')],
            'triggered_count' => rand(100, 500)
        ], 
        [
            'id' => 3,
            'name' => 'Bot检�?,
            'description' => '检测自动化Bot行为',
            'type' => 'bot_detection',
            'enabled' => true,
            'severity' => 'high',
            'conditions' => [
                'user_agent_check' => true,
                'behavior_pattern' => true,
                'captcha_failure' => 3
            ], 
            'actions' => ['block', 'challenge'], 
            'created_at' => date('Y-m-d H:i:s', strtotime('-15 days')],
            'triggered_count' => rand(30, 150)
        ]
    ];
}

function getRiskRule($ruleId): array
{
    $rules = getRiskRules(];
    $rule = array_filter($rules, fn($r) => $r['id'] == $ruleId];
    
    if (empty($rule)) {
        throw new Exception('风险规则不存�?];
    }
    
    return array_values($rule)[0];
}

function createRiskRule($data): array
{
    $newRule = [
        'id' => rand(100, 999],
        'name' => $data['name'], 
        'description' => $data['description'], 
        'type' => $data['type'], 
        'enabled' => $data['enabled'] ?? true,
        'severity' => $data['severity'] ?? 'medium',
        'conditions' => $data['conditions'] ?? [], 
        'actions' => $data['actions'] ?? [], 
        'created_at' => date('Y-m-d H:i:s'],
        'triggered_count' => 0
    ];
    
    // 这里应该保存到数据库
    
    return $newRule;
}

function updateRiskRule($ruleId, $data): array
{
    // 这里应该从数据库获取并更�?
    $rule = getRiskRule($ruleId];
    
    foreach ($data as $key => $value) {
        if (isset($rule[$key])) {
            $rule[$key] = $value;
        }
    }
    
    $rule['updated_at'] = date('Y-m-d H:i:s'];
    
    return $rule;
}

function deleteRiskRule($ruleId): void
{
    // 这里应该从数据库删除
    $rule = getRiskRule($ruleId]; // 验证规则存在
}

function getRiskEvents($page, $limit, $severity, $status, $type, $dateFrom, $dateTo): array
{
    $events = [];
    $total = rand(100, 1000];
    
    for ($i = 0; $i < min($limit, 50]; $i++) {
        $events[] = generateRandomRiskEvent($i + 1];
    }
    
    // 应用筛�?
    if ($severity) {
        $events = array_filter($events, fn($e) => $e['severity'] === $severity];
    }
    
    if ($status) {
        $events = array_filter($events, fn($e) => $e['status'] === $status];
    }
    
    if ($type) {
        $events = array_filter($events, fn($e) => $e['type'] === $type];
    }
    
    return [
        'pagination' => [
            'current_page' => $page,
            'per_page' => $limit,
            'total' => $total,
            'total_pages' => ceil($total / $limit)
        ]
    ];
}

function getRiskEvent($eventId): array
{
    return [
        'type' => 'login_anomaly',
        'severity' => 'high',
        'user_id' => rand(1, 1000],
        'username' => 'user' . rand(1, 100],
        'ip_address' => generateRandomIP(),
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        'location' => [
            'country' => 'Unknown',
            'city' => 'Unknown',
            'latitude' => rand(-90, 90],
            'longitude' => rand(-180, 180)
        ], 
        'rule_triggered' => [
            'id' => 1,
            'name' => '异常登录检�?,
            'conditions_met' => ['failed_attempts_threshold', 'geo_check']
        ], 
        'actions_taken' => ['block', 'alert'], 
        'details' => [
            'failed_attempts' => rand(5, 15],
            'time_window' => '5 minutes',
            'previous_location' => '中国 北京',
            'current_location' => 'Unknown Unknown'
        ], 
        'status' => 'pending',
        'created_at' => date('Y-m-d H:i:s', time() - rand(0, 86400)],
        'resolved_at' => null,
        'notes' => '系统自动检测到异常登录行为'
    ];
}

function updateRiskEvent($eventId, $data): array
{
    $event = getRiskEvent($eventId];
    
    foreach ($data as $key => $value) {
        if (isset($event[$key])) {
            $event[$key] = $value;
        }
    }
    
    $event['updated_at'] = date('Y-m-d H:i:s'];
    
    return $event;
}

function analyzeUserRisk($userId): array
{
    return [
        'risk_score' => rand(20, 80],
        'risk_level' => ['low', 'medium', 'high'][array_rand(['low', 'medium', 'high'])], 
        'factors' => [
            'login_frequency' => rand(1, 5],
            'geo_variance' => rand(1, 5],
            'device_variance' => rand(1, 5],
            'behavior_anomaly' => rand(1, 5)
        ], 
        'recent_events' => getRecentRiskEvents(5],
        'recommendations' => [
            '建议启用双因子认�?,
            '监控异常登录位置',
            '限制同时登录设备数量'
        ]
    ];
}

function analyzeIPRisk($ip): array
{
    return [
        'risk_score' => rand(10, 90],
        'risk_level' => ['low', 'medium', 'high', 'critical'][array_rand(['low', 'medium', 'high', 'critical'])], 
        'geo_info' => [
            'country' => 'Unknown',
            'region' => 'Unknown',
            'city' => 'Unknown',
            'isp' => 'Unknown ISP'
        ], 
        'reputation' => [
            'is_malicious' => rand(0, 1],
            'is_proxy' => rand(0, 1],
            'is_tor' => rand(0, 1],
            'reputation_score' => rand(0, 100)
        ], 
        'activity' => [
            'total_requests' => rand(100, 10000],
            'failed_logins' => rand(0, 50],
            'blocked_attempts' => rand(0, 20],
            'last_seen' => date('Y-m-d H:i:s', time() - rand(0, 86400))
        ]
    ];
}

function analyzeBehaviorRisk(): array
{
    return [
        'behavior_patterns' => [
            'unusual_login_times' => ['count' => rand(0, 10], 'risk_level' => 'medium'], 
            'rapid_successive_actions' => ['count' => rand(0, 20], 'risk_level' => 'high'], 
            'geo_hopping' => ['count' => rand(0, 5], 'risk_level' => 'critical'], 
            'bot_like_behavior' => ['count' => rand(0, 15], 'risk_level' => 'high']
        ], 
        'anomaly_score' => rand(20, 80],
        'top_anomalies' => [
            ['user_id' => rand(1, 1000], 'type' => 'login_time', 'score' => rand(70, 95)], 
            ['user_id' => rand(1, 1000], 'type' => 'geo_anomaly', 'score' => rand(60, 90)], 
            ['user_id' => rand(1, 1000], 'type' => 'rate_limit', 'score' => rand(50, 85)]
        ]
    ];
}

function analyzePatternRisk(): array
{
    return [
        'coordinated_attacks' => rand(0, 5],
        'distributed_brute_force' => rand(0, 3],
        'account_enumeration' => rand(0, 8],
        'credential_stuffing' => rand(0, 10)
    ];
}

function getGeneralRiskAnalysis(): array
{
    return [
        'trend' => ['increasing', 'stable', 'decreasing'][array_rand(['increasing', 'stable', 'decreasing'])], 
        'key_metrics' => [
            'total_events' => rand(500, 2000],
            'high_risk_events' => rand(50, 200],
            'blocked_attempts' => rand(20, 100],
            'false_positive_rate' => rand(2, 10) . '%'
        ], 
        'recommendations' => [
            '调整风险规则阈�?,
            '增强地理位置检�?,
            '启用更严格的Bot检�?,
            '优化白名单配�?
        ]
    ];
}

function getBlacklist($type): array
{
    $items = [
        [
            'id' => 1,
            'type' => 'ip',
            'value' => '192.168.1.100',
            'reason' => '恶意攻击',
            'added_by' => 'admin',
            'added_at' => date('Y-m-d H:i:s', time() - rand(86400, 604800)],
            'expires_at' => date('Y-m-d H:i:s', time() + rand(86400, 2592000))
        ], 
        [
            'id' => 2,
            'type' => 'email',
            'value' => 'malicious@example.com',
            'reason' => '垃圾邮件',
            'added_by' => 'system',
            'added_at' => date('Y-m-d H:i:s', time() - rand(86400, 604800)],
            'expires_at' => null
        ], 
        [
            'id' => 3,
            'type' => 'user',
            'value' => 'baduser123',
            'reason' => '违反服务条款',
            'added_by' => 'admin',
            'added_at' => date('Y-m-d H:i:s', time() - rand(86400, 604800)],
            'expires_at' => null
        ]
    ];
    
    if ($type !== 'all') {
        $items = array_filter($items, fn($item) => $item['type'] === $type];
    }
    
    return array_values($items];
}

function getBlacklistItem($id): array
{
    $items = getBlacklist('all'];
    $item = array_filter($items, fn($i) => $i['id'] == $id];
    
    if (empty($item)) {
        throw new Exception('黑名单项不存�?];
    }
    
    return array_values($item)[0];
}

function addToBlacklist($data): array
{
    $newItem = [
        'id' => rand(100, 999],
        'type' => $data['type'], 
        'value' => $data['value'], 
        'reason' => $data['reason'], 
        'added_by' => $data['added_by'] ?? 'admin',
        'added_at' => date('Y-m-d H:i:s'],
        'expires_at' => $data['expires_at'] ?? null
    ];
    
    return $newItem;
}

function removeFromBlacklist($id): void
{
    $item = getBlacklistItem($id]; // 验证项目存在
}

function getWhitelist($type): array
{
    $items = [
        [
            'id' => 1,
            'type' => 'ip',
            'value' => '192.168.1.1',
            'reason' => '办公室IP',
            'added_by' => 'admin',
            'added_at' => date('Y-m-d H:i:s', time() - rand(86400, 604800))
        ], 
        [
            'id' => 2,
            'type' => 'email',
            'value' => '@company.com',
            'reason' => '公司域名',
            'added_by' => 'admin',
            'added_at' => date('Y-m-d H:i:s', time() - rand(86400, 604800))
        ]
    ];
    
    if ($type !== 'all') {
        $items = array_filter($items, fn($item) => $item['type'] === $type];
    }
    
    return array_values($items];
}

function getWhitelistItem($id): array
{
    $items = getWhitelist('all'];
    $item = array_filter($items, fn($i) => $i['id'] == $id];
    
    if (empty($item)) {
        throw new Exception('白名单项不存�?];
    }
    
    return array_values($item)[0];
}

function addToWhitelist($data): array
{
    $newItem = [
        'id' => rand(100, 999],
        'type' => $data['type'], 
        'value' => $data['value'], 
        'reason' => $data['reason'], 
        'added_by' => $data['added_by'] ?? 'admin',
        'added_at' => date('Y-m-d H:i:s')
    ];
    
    return $newItem;
}

function removeFromWhitelist($id): void
{
    $item = getWhitelistItem($id]; // 验证项目存在
}

function generateDailyReport($dateFrom, $dateTo): array
{
    return [
        'date_range' => ['from' => $dateFrom, 'to' => $dateTo], 
        'summary' => [
            'total_events' => rand(100, 500],
            'high_risk_events' => rand(10, 50],
            'blocked_attempts' => rand(20, 100],
            'false_positives' => rand(2, 15)
        ], 
        'daily_breakdown' => generateDailyBreakdown($dateFrom, $dateTo],
        'top_threats' => getTopRisks(),
        'geographical_distribution' => getGeographicalRisks()
    ];
}

function generateWeeklyReport($dateFrom, $dateTo): array
{
    return [
        'date_range' => ['from' => $dateFrom, 'to' => $dateTo], 
        'summary' => [
            'total_events' => rand(500, 2000],
            'average_daily_events' => rand(70, 300],
            'peak_day' => date('Y-m-d', strtotime('-' . rand(1, 7) . ' days')],
            'trend' => ['increasing', 'stable', 'decreasing'][array_rand(['increasing', 'stable', 'decreasing'])]
        ], 
        'weekly_trends' => getRiskTrends(),
        'rule_effectiveness' => [
            ['rule' => '异常登录检�?, 'triggered' => rand(50, 200], 'effectiveness' => rand(80, 95) . '%'], 
            ['rule' => '频率限制', 'triggered' => rand(100, 400], 'effectiveness' => rand(70, 90) . '%'], 
            ['rule' => 'Bot检�?, 'triggered' => rand(30, 150], 'effectiveness' => rand(85, 98) . '%']
        ]
    ];
}

function generateMonthlyReport($dateFrom, $dateTo): array
{
    return [
        'date_range' => ['from' => $dateFrom, 'to' => $dateTo], 
        'executive_summary' => [
            'overall_risk_level' => ['low', 'medium', 'high'][array_rand(['low', 'medium', 'high'])], 
            'total_events' => rand(2000, 10000],
            'prevention_rate' => rand(85, 98) . '%',
            'cost_savings' => '$' . rand(10000, 50000)
        ], 
        'monthly_trends' => generateMonthlyTrends(),
        'security_improvements' => [
            '新增Bot检测规则，检测准确率提升15%',
            '优化地理位置检测，减少误报20%',
            '增强用户行为分析，风险识别率提升12%'
        ], 
        'recommendations' => [
            '建议加强对新兴威胁的防护',
            '优化现有规则以减少误�?,
            '考虑引入机器学习算法'
        ]
    ];
}

function generateSummaryReport($dateFrom, $dateTo): array
{
    return [
        'date_range' => ['from' => $dateFrom, 'to' => $dateTo], 
        'key_metrics' => [
            'total_events' => rand(1000, 5000],
            'blocked_threats' => rand(100, 500],
            'false_positive_rate' => rand(2, 8) . '%',
            'response_time' => rand(1, 5) . 'ms'
        ], 
        'threat_landscape' => [
            'most_common_threat' => '异常登录',
            'fastest_growing_threat' => 'Bot攻击',
            'geographic_hotspots' => ['俄罗�?, '朝鲜', '伊朗']
        ], 
        'system_performance' => [
            'uptime' => '99.9%',
            'average_processing_time' => rand(10, 50) . 'ms',
            'rules_processed' => rand(10000, 50000)
        ]
    ];
}

function generateCustomReport($dateFrom, $dateTo): array
{
    return [
        'date_range' => ['from' => $dateFrom, 'to' => $dateTo], 
        'custom_metrics' => [
            'user_defined_kpi_1' => rand(100, 1000],
            'user_defined_kpi_2' => rand(50, 500],
            'user_defined_kpi_3' => rand(10, 100)
        ], 
        'filtered_data' => getRiskEvents(1, 20, '', '', '', $dateFrom, $dateTo)
    ];
}

function generateDailyBreakdown($dateFrom, $dateTo): array
{
    $breakdown = [];
    $start = strtotime($dateFrom];
    $end = strtotime($dateTo];
    
    for ($date = $start; $date <= $end; $date += 86400) {
        $breakdown[] = [
            'date' => date('Y-m-d', $date],
            'events' => rand(50, 200],
            'high_risk' => rand(5, 30],
            'blocked' => rand(10, 50)
        ];
    }
    
    return $breakdown;
}

function generateMonthlyTrends(): array
{
    $trends = [];
    for ($i = 11; $i >= 0; $i--) {
        $trends[] = [
            'month' => date('Y-m', strtotime("-{$i} months")],
            'events' => rand(1000, 5000],
            'blocked' => rand(100, 500],
            'risk_score' => rand(30, 70)
        ];
    }
    
    return $trends;
}

function getRiskSettings(): array
{
    return [
        'risk_threshold' => 70,
        'auto_block_enabled' => true,
        'alert_notifications' => true,
        'log_retention_days' => 90
    ];
}

function updateRiskSettings($data): array
{
    $settings = getRiskSettings(];
    
    foreach ($data as $category => $categoryData) {
        if (isset($settings[$category])) {
            $settings[$category] = array_merge($settings[$category],  $categoryData];
        }
    }
    
    // 这里应该保存到数据库或配置文�?
    
    return $settings;
}

function generateRandomRiskEvent($id): array
{
    $types = ['login_anomaly', 'suspicious_behavior', 'rate_limit_exceed', 'geo_anomaly', 'bot_detection'];
    $severities = ['low', 'medium', 'high', 'critical'];
    $statuses = ['pending', 'resolved', 'investigating', 'false_positive'];
    
    return [
        'type' => $types[array_rand($types)], 
        'severity' => $severities[array_rand($severities)], 
        'status' => $statuses[array_rand($statuses)], 
        'user_id' => rand(1, 1000],
        'ip_address' => generateRandomIP(),
        'description' => generateEventDescription(),
        'risk_score' => rand(20, 100],
        'created_at' => date('Y-m-d H:i:s', time() - rand(0, 86400)],
        'resolved_at' => rand(0, 1) ? date('Y-m-d H:i:s', time() - rand(0, 3600)) : null
    ];
}

function generateRandomIP(): string
{
    return rand(1, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(1, 254];
}

function generateEventDescription(): string
{
    $descriptions = [
        '检测到异常登录行为',
        '用户行为模式异常',
        'API调用频率超出限制',
        '地理位置变化异常',
        '检测到Bot自动化行�?,
        '多次登录失败',
        '可疑的请求模�?,
        '账户安全风险'
    ];
    
    return $descriptions[array_rand($descriptions)];
}

