<?php
/**
 * AlingAi Pro 5.0 - 聊天监控系统API
 * 实时聊天监督、内容过滤和敏感词检�?
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

// 错误处理
function handleError($message, $code = 500) {
    error_log("API Error: $message"];
    sendResponse(false, null, $message, $code];
}

// 数据目录
$dataDir = __DIR__ . '/../../../../data/chat-monitoring';
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true];
}

// 数据文件路径
$sessionsFile = $dataDir . '/sessions.json';
$messagesFile = $dataDir . '/messages.json';
$sensitiveWordsFile = $dataDir . '/sensitive_words.json';
$rulesFile = $dataDir . '/monitoring_rules.json';
$alertsFile = $dataDir . '/alerts.json';
$statisticsFile = $dataDir . '/statistics.json';

// 初始化数据文�?
function initDataFile($file, $defaultData = []) {
    if (!file_exists($file)) {
        file_put_contents($file, json_encode($defaultData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)];
    }
}

// 初始化默认敏感词�?
$defaultSensitiveWords = [
    ['word' => '涉政', 'level' => 'high', 'category' => 'political', 'action' => 'block'], 
    ['word' => '暴力', 'level' => 'high', 'category' => 'violence', 'action' => 'block'], 
    ['word' => '色情', 'level' => 'high', 'category' => 'adult', 'action' => 'block'], 
    ['word' => '赌博', 'level' => 'high', 'category' => 'gambling', 'action' => 'warn'], 
    ['word' => '欺诈', 'level' => 'medium', 'category' => 'fraud', 'action' => 'warn'], 
    ['word' => '垃圾信息', 'level' => 'low', 'category' => 'spam', 'action' => 'flag']
];

// 初始化默认监控规�?
$defaultRules = [
    [
        'id' => 'rule_1',
        'name' => '敏感词检�?,
        'type' => 'keyword',
        'enabled' => true,
        'condition' => 'contains_sensitive_words',
        'action' => 'flag',
        'description' => '检测消息中的敏感词'
    ], 
    [
        'id' => 'rule_2',
        'name' => '频率限制',
        'type' => 'frequency',
        'enabled' => true,
        'condition' => 'message_frequency > 10/minute',
        'action' => 'warn',
        'description' => '检测异常高频发�?
    ], 
    [
        'id' => 'rule_3',
        'name' => '长度检�?,
        'type' => 'length',
        'enabled' => true,
        'condition' => 'message_length > 2000',
        'action' => 'flag',
        'description' => '检测超长消�?
    ], 
    [
        'id' => 'rule_4',
        'name' => '重复内容',
        'type' => 'repetition',
        'enabled' => true,
        'condition' => 'repeated_content > 3',
        'action' => 'warn',
        'description' => '检测重复发送相同内�?
    ]
];

initDataFile($sessionsFile, []];
initDataFile($messagesFile, []];
initDataFile($sensitiveWordsFile, $defaultSensitiveWords];
initDataFile($rulesFile, $defaultRules];
initDataFile($alertsFile, []];
initDataFile($statisticsFile, [
    'daily_stats' => [], 
    'monthly_stats' => [], 
    'alert_counts' => []
]];

// 辅助函数
function loadJsonFile($file) {
    return file_exists($file) ? json_decode(file_get_contents($file], true) : [];
}

function saveJsonFile($file, $data) {
    return file_put_contents($file, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)];
}

function generateId() {
    return uniqid() . '_' . bin2hex(random_bytes(4)];
}

// 生成模拟聊天数据
function generateMockChatData() {
    $users = ['user_1', 'user_2', 'user_3', 'user_4', 'user_5'];
    $messages = [
        '你好，请问如何使用AI助手�?,
        '我需要帮助解决一个编程问�?,
        '今天天气怎么样？',
        '能帮我写一段代码吗�?,
        '感谢你的帮助�?,
        '这个功能很有�?,
        '请问支持哪些编程语言�?,
        '我想了解更多关于AI的信�?,
        '如何提升编程技能？',
        '有什么推荐的学习资源吗？'
    ];
    
    $mockSessions = [];
    $mockMessages = [];
    
    // 生成会话
    for ($i = 1; $i <= 20; $i++) {
        $sessionId = 'session_' . $i;
        $userId = $users[array_rand($users)];
        $startTime = date('Y-m-d H:i:s', strtotime('-' . rand(1, 72) . ' hours')];
        
        $session = [
            'id' => $sessionId,
            'user_id' => $userId,
            'user_ip' => '192.168.1.' . rand(100, 200],
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'start_time' => $startTime,
            'last_activity' => date('Y-m-d H:i:s', strtotime($startTime . ' +' . rand(5, 120) . ' minutes')],
            'message_count' => rand(3, 15],
            'status' => rand(0, 10) > 8 ? 'flagged' : 'normal',
            'flags' => [], 
            'location' => ['country' => 'CN', 'city' => '北京'], 
            'duration' => rand(300, 3600) // �?
        ];
        
        if ($session['status'] === 'flagged') {
            $session['flags'] = ['sensitive_words', 'high_frequency'];
        }
        
        $mockSessions[] = $session;
        
        // 为每个会话生成消�?
        for ($j = 1; $j <= $session['message_count']; $j++) {
            $messageTime = date('Y-m-d H:i:s', strtotime($startTime . ' +' . ($j * rand(30, 300)) . ' seconds')];
            
            $message = [
                'id' => generateId(),
                'session_id' => $sessionId,
                'user_id' => $userId,
                'type' => $j % 2 === 1 ? 'user' : 'assistant',
                'content' => $messages[array_rand($messages)], 
                'timestamp' => $messageTime,
                'tokens' => rand(10, 100],
                'model' => 'gpt-3.5-turbo',
                'status' => rand(0, 20) > 18 ? 'flagged' : 'normal',
                'flags' => [], 
                'sentiment' => ['positive', 'neutral', 'negative'][rand(0, 2)], 
                'confidence' => round(rand(60, 99) / 100, 2],
                'processing_time' => rand(100, 2000) // 毫秒
            ];
            
            if ($message['status'] === 'flagged') {
                $message['flags'] = ['sensitive_content'];
            }
            
            $mockMessages[] = $message;
        }
    }
    
    return [$mockSessions, $mockMessages];
}

// 内容检测函�?
function detectSensitiveContent($content, $sensitiveWords) {
    $flags = [];
    $level = 'normal';
    
    foreach ($sensitiveWords as $wordData) {
        if (stripos($content, $wordData['word']) !== false) {
            $flags[] = [
                'word' => $wordData['word'], 
                'level' => $wordData['level'], 
                'category' => $wordData['category'], 
                'action' => $wordData['action']
            ];
            
            if ($wordData['level'] === 'high') {
                $level = 'high';
            } elseif ($wordData['level'] === 'medium' && $level !== 'high') {
                $level = 'medium';
            }
        }
    }
    
    return ['level' => $level, 'flags' => $flags];
}

// 应用监控规则
function applyMonitoringRules($message, $rules) {
    $alerts = [];
    
    foreach ($rules as $rule) {
        if (!$rule['enabled']) continue;
        
        $triggered = false;
        $alertLevel = 'info';
        
        switch ($rule['type']) {
            case 'length':
                if (strlen($message['content']) > 2000) {
                    $triggered = true;
                    $alertLevel = 'warning';
                }
                break;
                
            case 'frequency':
                // 模拟频率检�?
                if (rand(1, 100) > 95) {
                    $triggered = true;
                    $alertLevel = 'warning';
                }
                break;
                
            case 'repetition':
                // 模拟重复内容检�?
                if (rand(1, 100) > 90) {
                    $triggered = true;
                    $alertLevel = 'info';
                }
                break;
        }
        
        if ($triggered) {
            $alerts[] = [
                'rule_id' => $rule['id'], 
                'rule_name' => $rule['name'], 
                'level' => $alertLevel,
                'action' => $rule['action'], 
                'description' => $rule['description'], 
                'triggered_at' => date('Y-m-d H:i:s')
            ];
        }
    }
    
    return $alerts;
}

// 路由处理
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'],  PHP_URL_PATH];
$pathSegments = explode('/', trim($path, '/')];

try {
    // 验证管理员权�?
    $authService = new AdminAuthServiceDemo(];
    if (!$authService->verifyAdminAccess()) {
        sendResponse(false, null, '需要管理员权限', 403];
    }

    // 路由处理
    switch ($method) {
        case 'GET':
            if (count($pathSegments) >= 4 && $pathSegments[3] === 'sessions') {
                // 获取聊天会话
                if (isset($pathSegments[4])) {
                    $sessionId = $pathSegments[4];
                    $sessions = loadJsonFile($sessionsFile];
                    $session = array_filter($sessions, fn($s) => $s['id'] === $sessionId];
                    
                    if (empty($session)) {
                        // 生成模拟数据
                        list($mockSessions, $mockMessages) = generateMockChatData(];
                        $session = array_filter($mockSessions, fn($s) => $s['id'] === $sessionId];
                        
                        if (empty($session)) {
                            sendResponse(false, null, '会话未找�?, 404];
                        }
                    }
                    
                    sendResponse(true, array_values($session)[0],  '会话获取成功'];
                } else {
                    // 获取会话列表
                    $sessions = loadJsonFile($sessionsFile];
                    
                    // 如果没有数据，生成模拟数�?
                    if (empty($sessions)) {
                        list($mockSessions, $mockMessages) = generateMockChatData(];
                        $sessions = $mockSessions;
                        saveJsonFile($sessionsFile, $sessions];
                        saveJsonFile($messagesFile, $mockMessages];
                    }
                    
                    $page = intval($_GET['page'] ?? 1];
                    $limit = intval($_GET['limit'] ?? 20];
                    $status = $_GET['status'] ?? '';
                    $userId = $_GET['user_id'] ?? '';
                    $dateFrom = $_GET['date_from'] ?? '';
                    $dateTo = $_GET['date_to'] ?? '';
                    
                    // 过滤
                    if ($status) {
                        $sessions = array_filter($sessions, fn($s) => $s['status'] === $status];
                    }
                    
                    if ($userId) {
                        $sessions = array_filter($sessions, fn($s) => $s['user_id'] === $userId];
                    }
                    
                    if ($dateFrom) {
                        $sessions = array_filter($sessions, fn($s) => $s['start_time'] >= $dateFrom];
                    }
                    
                    if ($dateTo) {
                        $sessions = array_filter($sessions, fn($s) => $s['start_time'] <= $dateTo];
                    }
                    
                    // 分页
                    $total = count($sessions];
                    $offset = ($page - 1) * $limit;
                    $sessions = array_slice($sessions, $offset, $limit];
                    
                    sendResponse(true, [
                        'sessions' => array_values($sessions],
                        'pagination' => [
                            'current_page' => $page,
                            'per_page' => $limit,
                            'total' => $total,
                            'total_pages' => ceil($total / $limit)
                        ]
                    ],  '会话列表获取成功'];
                }
            } elseif (count($pathSegments) >= 4 && $pathSegments[3] === 'messages') {
                // 获取聊天消息
                $messages = loadJsonFile($messagesFile];
                
                // 如果没有数据，生成模拟数�?
                if (empty($messages)) {
                    list($mockSessions, $mockMessages) = generateMockChatData(];
                    $messages = $mockMessages;
                    saveJsonFile($messagesFile, $messages];
                }
                
                $page = intval($_GET['page'] ?? 1];
                $limit = intval($_GET['limit'] ?? 50];
                $sessionId = $_GET['session_id'] ?? '';
                $userId = $_GET['user_id'] ?? '';
                $status = $_GET['status'] ?? '';
                $type = $_GET['type'] ?? '';
                
                // 过滤
                if ($sessionId) {
                    $messages = array_filter($messages, fn($m) => $m['session_id'] === $sessionId];
                }
                
                if ($userId) {
                    $messages = array_filter($messages, fn($m) => $m['user_id'] === $userId];
                }
                
                if ($status) {
                    $messages = array_filter($messages, fn($m) => $m['status'] === $status];
                }
                
                if ($type) {
                    $messages = array_filter($messages, fn($m) => $m['type'] === $type];
                }
                
                // 按时间排�?
                usort($messages, fn($a, $b) => strcmp($b['timestamp'],  $a['timestamp'])];
                
                // 分页
                $total = count($messages];
                $offset = ($page - 1) * $limit;
                $messages = array_slice($messages, $offset, $limit];
                
                sendResponse(true, [
                    'messages' => array_values($messages],
                    'pagination' => [
                        'current_page' => $page,
                        'per_page' => $limit,
                        'total' => $total,
                        'total_pages' => ceil($total / $limit)
                    ]
                ],  '消息列表获取成功'];
            } elseif (count($pathSegments) >= 4 && $pathSegments[3] === 'sensitive-words') {
                // 获取敏感词库
                $sensitiveWords = loadJsonFile($sensitiveWordsFile];
                $category = $_GET['category'] ?? '';
                $level = $_GET['level'] ?? '';
                
                if ($category) {
                    $sensitiveWords = array_filter($sensitiveWords, fn($w) => $w['category'] === $category];
                }
                
                if ($level) {
                    $sensitiveWords = array_filter($sensitiveWords, fn($w) => $w['level'] === $level];
                }
                
                sendResponse(true, array_values($sensitiveWords], '敏感词库获取成功'];
            } elseif (count($pathSegments) >= 4 && $pathSegments[3] === 'rules') {
                // 获取监控规则
                $rules = loadJsonFile($rulesFile];
                sendResponse(true, $rules, '监控规则获取成功'];
            } elseif (count($pathSegments) >= 4 && $pathSegments[3] === 'alerts') {
                // 获取警报列表
                $alerts = loadJsonFile($alertsFile];
                $page = intval($_GET['page'] ?? 1];
                $limit = intval($_GET['limit'] ?? 20];
                $level = $_GET['level'] ?? '';
                $status = $_GET['status'] ?? '';
                
                if ($level) {
                    $alerts = array_filter($alerts, fn($a) => $a['level'] === $level];
                }
                
                if ($status) {
                    $alerts = array_filter($alerts, fn($a) => $a['status'] === $status];
                }
                
                // 按时间排�?
                usort($alerts, fn($a, $b) => strcmp($b['created_at'],  $a['created_at'])];
                
                $total = count($alerts];
                $offset = ($page - 1) * $limit;
                $alerts = array_slice($alerts, $offset, $limit];
                
                sendResponse(true, [
                    'alerts' => array_values($alerts],
                    'pagination' => [
                        'current_page' => $page,
                        'per_page' => $limit,
                        'total' => $total,
                        'total_pages' => ceil($total / $limit)
                    ]
                ],  '警报列表获取成功'];
            } elseif (count($pathSegments) >= 4 && $pathSegments[3] === 'stats') {
                // 获取监控统计
                $sessions = loadJsonFile($sessionsFile];
                $messages = loadJsonFile($messagesFile];
                $alerts = loadJsonFile($alertsFile];
                
                // 如果没有数据，生成模拟数�?
                if (empty($sessions)) {
                    list($mockSessions, $mockMessages) = generateMockChatData(];
                    $sessions = $mockSessions;
                    $messages = $mockMessages;
                }
                
                $now = new DateTime(];
                $today = $now->format('Y-m-d'];
                $thisMonth = $now->format('Y-m'];
                
                $todaySessions = array_filter($sessions, fn($s) => substr($s['start_time'],  0, 10) === $today];
                $todayMessages = array_filter($messages, fn($m) => substr($m['timestamp'],  0, 10) === $today];
                $flaggedSessions = array_filter($sessions, fn($s) => $s['status'] === 'flagged'];
                $flaggedMessages = array_filter($messages, fn($m) => $m['status'] === 'flagged'];
                
                $stats = [
                    'overview' => [
                        'total_sessions' => count($sessions],
                        'total_messages' => count($messages],
                        'today_sessions' => count($todaySessions],
                        'today_messages' => count($todayMessages],
                        'flagged_sessions' => count($flaggedSessions],
                        'flagged_messages' => count($flaggedMessages],
                        'active_alerts' => count(array_filter($alerts, fn($a) => $a['status'] === 'active')],
                        'flag_rate' => count($messages) > 0 ? round(count($flaggedMessages) / count($messages) * 100, 2) : 0
                    ], 
                    'hourly_distribution' => [], 
                    'sentiment_analysis' => [
                        'positive' => count(array_filter($messages, fn($m) => $m['sentiment'] === 'positive')],
                        'neutral' => count(array_filter($messages, fn($m) => $m['sentiment'] === 'neutral')],
                        'negative' => count(array_filter($messages, fn($m) => $m['sentiment'] === 'negative'))
                    ], 
                    'top_users' => [], 
                    'flag_categories' => []
                ];
                
                // 生成小时分布数据
                for ($hour = 0; $hour < 24; $hour++) {
                    $hourlyMessages = array_filter($messages, function($m) use ($hour) {
                        return intval(date('H', strtotime($m['timestamp']))) === $hour;
                    }];
                    $stats['hourly_distribution'][$hour] = count($hourlyMessages];
                }
                
                // 统计用户活跃�?
                $userCounts = [];
                foreach ($messages as $message) {
                    $userId = $message['user_id'];
                    if (!isset($userCounts[$userId])) {
                        $userCounts[$userId] = 0;
                    }
                    $userCounts[$userId]++;
                }
                arsort($userCounts];
                $stats['top_users'] = array_slice($userCounts, 0, 10, true];
                
                sendResponse(true, $stats, '监控统计获取成功'];
            } elseif (count($pathSegments) >= 4 && $pathSegments[3] === 'realtime') {
                // 获取实时监控数据
                $realtimeData = [
                    'current_sessions' => rand(10, 50],
                    'messages_per_minute' => rand(50, 200],
                    'alerts_last_hour' => rand(0, 5],
                    'system_load' => [
                        'cpu' => rand(20, 80],
                        'memory' => rand(30, 70],
                        'network' => rand(10, 90)
                    ], 
                    'recent_activity' => []
                ];
                
                // 生成最近活�?
                for ($i = 0; $i < 10; $i++) {
                    $realtimeData['recent_activity'][] = [
                        'type' => ['message', 'session_start', 'alert', 'flag'][rand(0, 3)], 
                        'user_id' => 'user_' . rand(1, 100],
                        'timestamp' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 60) . ' seconds')],
                        'description' => '用户活动描述'
                    ];
                }
                
                sendResponse(true, $realtimeData, '实时监控数据获取成功'];
            } else {
                // 获取聊天监控概览
                $sessions = loadJsonFile($sessionsFile];
                $messages = loadJsonFile($messagesFile];
                $rules = loadJsonFile($rulesFile];
                $sensitiveWords = loadJsonFile($sensitiveWordsFile];
                
                // 如果没有数据，生成模拟数�?
                if (empty($sessions)) {
                    list($mockSessions, $mockMessages) = generateMockChatData(];
                    $sessions = $mockSessions;
                    $messages = $mockMessages;
                }
                
                $overview = [
                    'summary' => [
                        'total_sessions' => count($sessions],
                        'total_messages' => count($messages],
                        'active_rules' => count(array_filter($rules, fn($r) => $r['enabled'])],
                        'sensitive_words_count' => count($sensitiveWords],
                        'flagged_sessions' => count(array_filter($sessions, fn($s) => $s['status'] === 'flagged')],
                        'monitoring_enabled' => true
                    ], 
                    'recent_sessions' => array_slice($sessions, -5],
                    'recent_alerts' => [], 
                    'system_status' => [
                        'monitoring_active' => true,
                        'rules_configured' => count($rules) > 0,
                        'sensitive_words_loaded' => count($sensitiveWords) > 0
                    ]
                ];
                
                sendResponse(true, $overview, '聊天监控概览获取成功'];
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'], true];
            
            if (count($pathSegments) >= 4 && $pathSegments[3] === 'sensitive-words') {
                // 添加敏感�?
                if (!isset($data['word']) || !isset($data['level']) || !isset($data['category'])) {
                    sendResponse(false, null, '缺少必填字段', 400];
                }
                
                $sensitiveWords = loadJsonFile($sensitiveWordsFile];
                
                $newWord = [
                    'id' => generateId(),
                    'word' => $data['word'], 
                    'level' => $data['level'], 
                    'category' => $data['category'], 
                    'action' => $data['action'] ?? 'warn',
                    'created_at' => date('Y-m-d H:i:s'],
                    'created_by' => 'admin'
                ];
                
                $sensitiveWords[] = $newWord;
                saveJsonFile($sensitiveWordsFile, $sensitiveWords];
                
                sendResponse(true, $newWord, '敏感词添加成�?, 201];
            } elseif (count($pathSegments) >= 4 && $pathSegments[3] === 'rules') {
                // 创建监控规则
                if (!isset($data['name']) || !isset($data['type']) || !isset($data['condition'])) {
                    sendResponse(false, null, '缺少必填字段', 400];
                }
                
                $rules = loadJsonFile($rulesFile];
                
                $newRule = [
                    'id' => generateId(),
                    'name' => $data['name'], 
                    'type' => $data['type'], 
                    'enabled' => $data['enabled'] ?? true,
                    'condition' => $data['condition'], 
                    'action' => $data['action'] ?? 'flag',
                    'description' => $data['description'] ?? '',
                    'created_at' => date('Y-m-d H:i:s'],
                    'created_by' => 'admin'
                ];
                
                $rules[] = $newRule;
                saveJsonFile($rulesFile, $rules];
                
                sendResponse(true, $newRule, '监控规则创建成功', 201];
            } elseif (count($pathSegments) >= 4 && $pathSegments[3] === 'analyze') {
                // 分析消息内容
                if (!isset($data['content'])) {
                    sendResponse(false, null, '缺少消息内容', 400];
                }
                
                $sensitiveWords = loadJsonFile($sensitiveWordsFile];
                $rules = loadJsonFile($rulesFile];
                
                $content = $data['content'];
                
                // 敏感词检�?
                $sensitiveResult = detectSensitiveContent($content, $sensitiveWords];
                
                // 模拟消息数据应用规则检�?
                $mockMessage = [
                    'content' => $content,
                    'timestamp' => date('Y-m-d H:i:s'],
                    'user_id' => 'test_user'
                ];
                
                $ruleAlerts = applyMonitoringRules($mockMessage, $rules];
                
                $analysis = [
                    'content_length' => strlen($content],
                    'sensitive_detection' => $sensitiveResult,
                    'rule_alerts' => $ruleAlerts,
                    'risk_level' => $sensitiveResult['level'], 
                    'recommended_action' => count($ruleAlerts) > 0 ? 'review' : 'allow',
                    'analysis_timestamp' => date('Y-m-d H:i:s')
                ];
                
                sendResponse(true, $analysis, '内容分析完成'];
            } else {
                sendResponse(false, null, '无效的API端点', 404];
            }
            break;

        case 'PUT':
            $data = json_decode(file_get_contents('php://input'], true];
            
            if (count($pathSegments) >= 5 && $pathSegments[3] === 'messages') {
                // 标记消息
                $messageId = $pathSegments[4];
                $action = $pathSegments[5] ?? '';
                
                if ($action === 'flag') {
                    $messages = loadJsonFile($messagesFile];
                    
                    foreach ($messages as &$message) {
                        if ($message['id'] === $messageId) {
                            $message['status'] = 'flagged';
                            $message['flags'][] = $data['flag_reason'] ?? 'manual_review';
                            $message['flagged_at'] = date('Y-m-d H:i:s'];
                            $message['flagged_by'] = 'admin';
                            break;
                        }
                    }
                    
                    saveJsonFile($messagesFile, $messages];
                    sendResponse(true, null, '消息已标�?];
                } else {
                    sendResponse(false, null, '无效的操�?, 400];
                }
            } elseif (count($pathSegments) >= 5 && $pathSegments[3] === 'rules') {
                // 更新监控规则
                $ruleId = $pathSegments[4];
                $rules = loadJsonFile($rulesFile];
                
                foreach ($rules as &$rule) {
                    if ($rule['id'] === $ruleId) {
                        $rule['name'] = $data['name'] ?? $rule['name'];
                        $rule['enabled'] = $data['enabled'] ?? $rule['enabled'];
                        $rule['condition'] = $data['condition'] ?? $rule['condition'];
                        $rule['action'] = $data['action'] ?? $rule['action'];
                        $rule['description'] = $data['description'] ?? $rule['description'];
                        $rule['updated_at'] = date('Y-m-d H:i:s'];
                        break;
                    }
                }
                
                saveJsonFile($rulesFile, $rules];
                sendResponse(true, null, '规则更新成功'];
            } else {
                sendResponse(false, null, '无效的API端点', 404];
            }
            break;

        case 'DELETE':
            if (count($pathSegments) >= 5 && $pathSegments[3] === 'sensitive-words') {
                // 删除敏感�?
                $wordId = $pathSegments[4];
                $sensitiveWords = loadJsonFile($sensitiveWordsFile];
                
                $sensitiveWords = array_filter($sensitiveWords, fn($w) => ($w['id'] ?? '') !== $wordId];
                saveJsonFile($sensitiveWordsFile, array_values($sensitiveWords)];
                
                sendResponse(true, null, '敏感词删除成�?];
            } elseif (count($pathSegments) >= 5 && $pathSegments[3] === 'rules') {
                // 删除监控规则
                $ruleId = $pathSegments[4];
                $rules = loadJsonFile($rulesFile];
                
                $rules = array_filter($rules, fn($r) => $r['id'] !== $ruleId];
                saveJsonFile($rulesFile, array_values($rules)];
                
                sendResponse(true, null, '规则删除成功'];
            } else {
                sendResponse(false, null, '无效的API端点', 404];
            }
            break;

        default:
            sendResponse(false, null, '不支持的HTTP方法', 405];
            break;
    }
} catch (Exception $e) {
    handleError('服务器内部错�? ' . $e->getMessage()];
}

