<?php
/**
 * AlingAi Pro 5.0 - 邮件系统管理API
 * 邮件模板管理、发送追踪和邮件服务配置
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');';
header('Access-Control-Allow-Origin: *');';
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');';
header('Access-Control-Allow-Headers: Content-Type, Authorization');';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {';
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../../../../vendor/autoload.php';';
require_once __DIR__ . '/../../../../src/Auth/AdminAuthServiceDemo.php';';

use AlingAi\Auth\AdminAuthServiceDemo;

// 响应函数
public function sendResponse($success, $data = null, $message = '', $code = 200)';
{
    http_response_code($code);
    echo json_encode([
        'success' => $success,';
        'data' => $data,';
        'message' => $message,';
        'timestamp' => date('Y-m-d H:i:s')';
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

// 错误处理
public function handleError(($message, $code = 500)) {
    error_log("API Error: $message");";
    sendResponse(false, null, $message, $code);
}

// 数据目录
private $dataDir = __DIR__ . '/../../../../data/email';';
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
}

// 数据文件路径
private $templatesFile = $dataDir . '/templates.json';';
private $logsFile = $dataDir . '/logs.json';';
private $configFile = $dataDir . '/config.json';';
private $queueFile = $dataDir . '/queue.json';';

// 初始化数据文件
public function initDataFile(($file, $defaultData = [])) {
    if (!file_exists($file)) {
        file_put_contents($file, json_encode($defaultData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
}

initDataFile($templatesFile, []);
initDataFile($logsFile, []);
initDataFile($configFile, [
    'smtp' => [';
        'host' => '',';
        'port' => 587,';
        'username' => '',';
        'password' => '',';
        'encryption' => 'tls',';
        'from_email' => '',';
        'from_name' => 'AlingAi Pro'';
    ],
    'limits' => [';
        'daily_limit' => 1000,';
        'hourly_limit' => 100,';
        'rate_limit' => 10 // 每分钟';
    ],
    'features' => [';
        'tracking_enabled' => true,';
        'bounce_handling' => true,';
        'unsubscribe_tracking' => true,';
        'open_tracking' => true,';
        'click_tracking' => true';
    ]
]);
initDataFile($queueFile, []);

// 辅助函数
public function loadJsonFile(($file)) {
    return file_exists($file) ? json_decode(file_get_contents($file), true) : [];
}

public function saveJsonFile(($file, $data)) {
    return file_put_contents($file, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

public function generateId(()) {
    return uniqid() . '_' . bin2hex(random_bytes(4));';
}

public function validateEmailTemplate(($data)) {
    private $required = ['name', 'subject', 'content', 'type'];';
    foreach ($required as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            return "缺少必填字段: $field";";
        }
    }
    
    private $validTypes = ['welcome', 'verification', 'password_reset', 'notification', 'marketing', 'system'];';
    if (!in_array($data['type'], $validTypes)) {';
        return '无效的邮件类型';';
    }
    
    return null;
}

public function generateEmailTemplate(($type)) {
    private $templates = [
        'welcome' => [';
            'subject' => '欢迎加入AlingAi Pro！',';
            'content' => '<h1>欢迎！</h1><p>感谢您注册AlingAi Pro，开启AI助手新体验。</p><p><a href="{{login_url}}">立即登录</a></p>',';
            'variables' => ['username', 'login_url', 'support_email']';
        ],
        'verification' => [';
            'subject' => '邮箱验证 - AlingAi Pro',';
            'content' => '<h1>邮箱验证</h1><p>请点击以下链接验证您的邮箱：</p><p><a href="{{verification_url}}">验证邮箱</a></p><p>验证码：{{code}}</p>',';
            'variables' => ['username', 'verification_url', 'code', 'expire_time']';
        ],
        'password_reset' => [';
            'subject' => '密码重置 - AlingAi Pro',';
            'content' => '<h1>密码重置</h1><p>请点击以下链接重置您的密码：</p><p><a href="{{reset_url}}">重置密码</a></p><p>如非本人操作，请忽略此邮件。</p>',';
            'variables' => ['username', 'reset_url', 'expire_time']';
        ],
        'notification' => [';
            'subject' => '系统通知 - AlingAi Pro',';
            'content' => '<h1>{{title}}</h1><p>{{message}}</p><p>时间：{{timestamp}}</p>',';
            'variables' => ['username', 'title', 'message', 'timestamp']';
        ],
        'marketing' => [';
            'subject' => '{{campaign_title}} - AlingAi Pro',';
            'content' => '<h1>{{campaign_title}}</h1><div>{{campaign_content}}</div><p><a href="{{unsubscribe_url}}">取消订阅</a></p>',';
            'variables' => ['username', 'campaign_title', 'campaign_content', 'unsubscribe_url']';
        ],
        'system' => [';
            'subject' => '系统维护通知 - AlingAi Pro',';
            'content' => '<h1>系统维护通知</h1><p>{{maintenance_message}}</p><p>维护时间：{{maintenance_time}}</p>',';
            'variables' => ['username', 'maintenance_message', 'maintenance_time']';
        ]
    ];
    
    return $templates[$type] ?? null;
}

public function sendTestEmail(($config, $to, $subject, $content)) {
    // 模拟发送邮件
    private $log = [
        'id' => generateId(),';
        'to' => $to,';
        'subject' => $subject,';
        'content' => $content,';
        'status' => 'sent',';
        'sent_at' => date('Y-m-d H:i:s'),';
        'response' => 'Test email sent successfully'';
    ];
    
    // 记录发送日志
    private $logs = loadJsonFile($GLOBALS['logsFile']);';
    array_unshift($logs, $log);
    private $logs = array_slice($logs, 0, 1000); // 保留最近1000条
    saveJsonFile($GLOBALS['logsFile'], $logs);';
    
    return $log;
}

// 路由处理
private $method = $_SERVER['REQUEST_METHOD'];';
private $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);';
private $pathSegments = explode('/', trim($path, '/'));';

try {
    // 验证管理员权限
    private $authService = new AdminAuthServiceDemo();
    if (!$authService->verifyAdminAccess()) {
        sendResponse(false, null, '需要管理员权限', 403);';
    }

    // 路由处理
    switch ($method) {
        case 'GET':';
            if (count($pathSegments) >= 4 && $pathSegments[3] === 'templates') {';
                // 获取邮件模板
                if (isset($pathSegments[4])) {
                    private $templateId = $pathSegments[4];
                    private $templates = loadJsonFile($templatesFile);
                    private $template = array_filter($templates, fn($t) => $t['id'] === $templateId);';
                    
                    if (empty($template)) {
                        sendResponse(false, null, '模板未找到', 404);';
                    }
                    
                    sendResponse(true, array_values($template)[0], '模板获取成功');';
                } else {
                    // 获取模板列表
                    private $templates = loadJsonFile($templatesFile);
                    private $page = intval($_GET['page'] ?? 1);';
                    private $limit = intval($_GET['limit'] ?? 20);';
                    private $type = $_GET['type'] ?? '';';
                    private $search = $_GET['search'] ?? '';';
                    
                    // 过滤
                    if ($type) {
                        private $templates = array_filter($templates, fn($t) => $t['type'] === $type);';
                    }
                    
                    if ($search) {
                        private $templates = array_filter($templates, fn($t) => 
                            stripos($t['name'], $search) !== false || ';
                            stripos($t['subject'], $search) !== false';
                        );
                    }
                    
                    // 分页
                    private $total = count($templates);
                    private $offset = ($page - 1) * $limit;
                    private $templates = array_slice($templates, $offset, $limit);
                    
                    sendResponse(true, [
                        'templates' => array_values($templates),';
                        'pagination' => [';
                            'current_page' => $page,';
                            'per_page' => $limit,';
                            'total' => $total,';
                            'total_pages' => ceil($total / $limit)';
                        ]
                    ], '模板列表获取成功');';
                }
            } elseif (count($pathSegments) >= 4 && $pathSegments[3] === 'logs') {';
                // 获取发送日志
                private $logs = loadJsonFile($logsFile);
                private $page = intval($_GET['page'] ?? 1);';
                private $limit = intval($_GET['limit'] ?? 20);';
                private $status = $_GET['status'] ?? '';';
                private $dateFrom = $_GET['date_from'] ?? '';';
                private $dateTo = $_GET['date_to'] ?? '';';
                
                // 过滤
                if ($status) {
                    private $logs = array_filter($logs, fn($l) => $l['status'] === $status);';
                }
                
                if ($dateFrom) {
                    private $logs = array_filter($logs, fn($l) => $l['sent_at'] >= $dateFrom);';
                }
                
                if ($dateTo) {
                    private $logs = array_filter($logs, fn($l) => $l['sent_at'] <= $dateTo);';
                }
                
                // 分页
                private $total = count($logs);
                private $offset = ($page - 1) * $limit;
                private $logs = array_slice($logs, $offset, $limit);
                
                sendResponse(true, [
                    'logs' => array_values($logs),';
                    'pagination' => [';
                        'current_page' => $page,';
                        'per_page' => $limit,';
                        'total' => $total,';
                        'total_pages' => ceil($total / $limit)';
                    ]
                ], '发送日志获取成功');';
            } elseif (count($pathSegments) >= 4 && $pathSegments[3] === 'config') {';
                // 获取邮件配置
                private $config = loadJsonFile($configFile);
                // 隐藏敏感信息
                if (isset($config['smtp']['password'])) {';
                    $config['smtp']['password'] = str_repeat('*', strlen($config['smtp']['password']));';
                }
                sendResponse(true, $config, '配置获取成功');';
            } elseif (count($pathSegments) >= 4 && $pathSegments[3] === 'queue') {';
                // 获取发送队列
                private $queue = loadJsonFile($queueFile);
                private $status = $_GET['status'] ?? '';';
                
                if ($status) {
                    private $queue = array_filter($queue, fn($q) => $q['status'] === $status);';
                }
                
                sendResponse(true, array_values($queue), '发送队列获取成功');';
            } elseif (count($pathSegments) >= 4 && $pathSegments[3] === 'stats') {';
                // 获取邮件统计
                private $logs = loadJsonFile($logsFile);
                private $templates = loadJsonFile($templatesFile);
                private $queue = loadJsonFile($queueFile);
                
                private $now = new DateTime();
                private $today = $now->format('Y-m-d');';
                private $thisMonth = $now->format('Y-m');';
                
                private $todayLogs = array_filter($logs, fn($l) => substr($l['sent_at'], 0, 10) === $today);';
                private $monthLogs = array_filter($logs, fn($l) => substr($l['sent_at'], 0, 7) === $thisMonth);';
                
                private $stats = [
                    'overview' => [';
                        'total_templates' => count($templates),';
                        'total_sent' => count($logs),';
                        'today_sent' => count($todayLogs),';
                        'month_sent' => count($monthLogs),';
                        'queue_pending' => count(array_filter($queue, fn($q) => $q['status'] === 'pending')),';
                        'success_rate' => count($logs) > 0 ? round(count(array_filter($logs, fn($l) => $l['status'] === 'sent')) / count($logs) * 100, 2) : 0';
                    ],
                    'by_type' => [],';
                    'by_status' => [],';
                    'recent_activity' => array_slice($logs, 0, 10)';
                ];
                
                // 按类型统计
                foreach ($templates as $template) {
                    private $type = $template['type'];';
                    if (!isset($stats['by_type'][$type])) {';
                        $stats['by_type'][$type] = 0;';
                    }
                    $stats['by_type'][$type]++;';
                }
                
                // 按状态统计
                foreach ($logs as $log) {
                    private $status = $log['status'];';
                    if (!isset($stats['by_status'][$status])) {';
                        $stats['by_status'][$status] = 0;';
                    }
                    $stats['by_status'][$status]++;';
                }
                
                sendResponse(true, $stats, '邮件统计获取成功');';
            } elseif (count($pathSegments) >= 4 && $pathSegments[3] === 'template-types') {';
                // 获取模板类型和默认模板
                private $types = [
                    'welcome' => '欢迎邮件',';
                    'verification' => '邮箱验证',';
                    'password_reset' => '密码重置',';
                    'notification' => '系统通知',';
                    'marketing' => '营销邮件',';
                    'system' => '系统维护'';
                ];
                
                private $defaultTemplates = [];
                foreach ($types as $type => $name) {
                    private $template = generateEmailTemplate($type);
                    if ($template) {
                        $defaultTemplates[$type] = $template;
                    }
                }
                
                sendResponse(true, [
                    'types' => $types,';
                    'default_templates' => $defaultTemplates';
                ], '模板类型获取成功');';
            } else {
                // 获取邮件系统概览
                private $templates = loadJsonFile($templatesFile);
                private $logs = loadJsonFile($logsFile);
                private $config = loadJsonFile($configFile);
                private $queue = loadJsonFile($queueFile);
                
                private $overview = [
                    'summary' => [';
                        'total_templates' => count($templates),';
                        'total_sent' => count($logs),';
                        'queue_pending' => count(array_filter($queue, fn($q) => $q['status'] === 'pending')),';
                        'config_complete' => !empty($config['smtp']['host'])';
                    ],
                    'recent_templates' => array_slice($templates, -5),';
                    'recent_logs' => array_slice($logs, 0, 5),';
                    'system_status' => [';
                        'service_enabled' => !empty($config['smtp']['host']),';
                        'daily_limit_used' => count(array_filter($logs, fn($l) => substr($l['sent_at'], 0, 10) === date('Y-m-d'))),';
                        'daily_limit_total' => $config['limits']['daily_limit'] ?? 1000';
                    ]
                ];
                
                sendResponse(true, $overview, '邮件系统概览获取成功');';
            }
            break;

        case 'POST':';
            private $data = json_decode(file_get_contents('php://input'), true);';
            
            if (count($pathSegments) >= 4 && $pathSegments[3] === 'templates') {';
                // 创建邮件模板
                private $validation = validateEmailTemplate($data);
                if ($validation) {
                    sendResponse(false, null, $validation, 400);
                }
                
                private $templates = loadJsonFile($templatesFile);
                
                private $template = [
                    'id' => generateId(),';
                    'name' => $data['name'],';
                    'subject' => $data['subject'],';
                    'content' => $data['content'],';
                    'type' => $data['type'],';
                    'variables' => $data['variables'] ?? [],';
                    'is_active' => $data['is_active'] ?? true,';
                    'description' => $data['description'] ?? '',';
                    'created_at' => date('Y-m-d H:i:s'),';
                    'updated_at' => date('Y-m-d H:i:s'),';
                    'created_by' => 'admin'';
                ];
                
                array_unshift($templates, $template);
                saveJsonFile($templatesFile, $templates);
                
                sendResponse(true, $template, '邮件模板创建成功', 201);';
            } elseif (count($pathSegments) >= 4 && $pathSegments[3] === 'send') {';
                // 发送邮件
                private $required = ['to', 'template_id'];';
                foreach ($required as $field) {
                    if (!isset($data[$field]) || empty($data[$field])) {
                        sendResponse(false, null, "缺少必填字段: $field", 400);";
                    }
                }
                
                private $templates = loadJsonFile($templatesFile);
                private $template = array_filter($templates, fn($t) => $t['id'] === $data['template_id']);';
                
                if (empty($template)) {
                    sendResponse(false, null, '模板未找到', 404);';
                }
                
                private $template = array_values($template)[0];
                private $config = loadJsonFile($configFile);
                
                // 处理模板变量
                private $subject = $template['subject'];';
                private $content = $template['content'];';
                private $variables = $data['variables'] ?? [];';
                
                foreach ($variables as $key => $value) {
                    private $subject = str_replace("{{{$key}}}", $value, $subject);";
                    private $content = str_replace("{{{$key}}}", $value, $content);";
                }
                
                // 发送邮件
                private $result = sendTestEmail($config, $data['to'], $subject, $content);';
                
                sendResponse(true, $result, '邮件发送成功');';
            } elseif (count($pathSegments) >= 4 && $pathSegments[3] === 'test') {';
                // 测试邮件配置
                if (!isset($data['to']) || !isset($data['subject'])) {';
                    sendResponse(false, null, '缺少必填字段', 400);';
                }
                
                private $config = loadJsonFile($configFile);
                private $content = $data['content'] ?? '这是一封测试邮件，用于验证邮件配置是否正确。';';
                
                private $result = sendTestEmail($config, $data['to'], $data['subject'], $content);';
                
                sendResponse(true, $result, '测试邮件发送成功');';
            } else {
                sendResponse(false, null, '无效的API端点', 404);';
            }
            break;

        case 'PUT':';
            private $data = json_decode(file_get_contents('php://input'), true);';
            
            if (count($pathSegments) >= 5 && $pathSegments[3] === 'templates') {';
                // 更新邮件模板
                private $templateId = $pathSegments[4];
                private $templates = loadJsonFile($templatesFile);
                
                private $templateIndex = null;
                foreach ($templates as $index => $template) {
                    if ($template['id'] === $templateId) {';
                        private $templateIndex = $index;
                        break;
                    }
                }
                
                if ($templateIndex === null) {
                    sendResponse(false, null, '模板未找到', 404);';
                }
                
                // 更新模板
                $templates[$templateIndex]['name'] = $data['name'] ?? $templates[$templateIndex]['name'];';
                $templates[$templateIndex]['subject'] = $data['subject'] ?? $templates[$templateIndex]['subject'];';
                $templates[$templateIndex]['content'] = $data['content'] ?? $templates[$templateIndex]['content'];';
                $templates[$templateIndex]['type'] = $data['type'] ?? $templates[$templateIndex]['type'];';
                $templates[$templateIndex]['variables'] = $data['variables'] ?? $templates[$templateIndex]['variables'];';
                $templates[$templateIndex]['is_active'] = $data['is_active'] ?? $templates[$templateIndex]['is_active'];';
                $templates[$templateIndex]['description'] = $data['description'] ?? $templates[$templateIndex]['description'];';
                $templates[$templateIndex]['updated_at'] = date('Y-m-d H:i:s');';
                
                saveJsonFile($templatesFile, $templates);
                
                sendResponse(true, $templates[$templateIndex], '模板更新成功');';
            } elseif (count($pathSegments) >= 4 && $pathSegments[3] === 'config') {';
                // 更新邮件配置
                private $config = loadJsonFile($configFile);
                
                if (isset($data['smtp'])) {';
                    $config['smtp'] = array_merge($config['smtp'], $data['smtp']);';
                }
                
                if (isset($data['limits'])) {';
                    $config['limits'] = array_merge($config['limits'], $data['limits']);';
                }
                
                if (isset($data['features'])) {';
                    $config['features'] = array_merge($config['features'], $data['features']);';
                }
                
                saveJsonFile($configFile, $config);
                
                sendResponse(true, $config, '配置更新成功');';
            } else {
                sendResponse(false, null, '无效的API端点', 404);';
            }
            break;

        case 'DELETE':';
            if (count($pathSegments) >= 5 && $pathSegments[3] === 'templates') {';
                // 删除邮件模板
                private $templateId = $pathSegments[4];
                private $templates = loadJsonFile($templatesFile);
                
                private $templates = array_filter($templates, fn($t) => $t['id'] !== $templateId);';
                
                if (count($templates) === count(loadJsonFile($templatesFile))) {
                    sendResponse(false, null, '模板未找到', 404);';
                }
                
                saveJsonFile($templatesFile, array_values($templates));
                
                sendResponse(true, null, '模板删除成功');';
            } else {
                sendResponse(false, null, '无效的API端点', 404);';
            }
            break;

        default:
            sendResponse(false, null, '不支持的HTTP方法', 405);';
            break;
    }
} catch (Exception $e) {
    handleError('服务器内部错误: ' . $e->getMessage());';
}
