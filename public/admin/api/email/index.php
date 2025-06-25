<?php
/**
 * AlingAi Pro 5.0 - 邮件系统管理API
 * 邮件模板管理、发送追踪和邮件服务配置
 */

declare(strict_types=1];

header("Content-Type: application/json; charset=utf-8"];
header("Access-Control-Allow-Origin: *"];
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"];
header("Access-Control-Allow-Headers: Content-Type, Authorization"];

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200];
    exit(];
}

require_once __DIR__ . "/../../../../vendor/autoload.php";
require_once __DIR__ . "/../../../../src/Auth/AdminAuthServiceDemo.php";

use AlingAi\Auth\AdminAuthServiceDemo;

// 响应函数
function sendResponse($success, $data = null, $message = "", $code = 200)
{
    http_response_code($code];
    echo json_encode([
        "success" => $success,
        "data" => $data,
        "message" => $message,
        "timestamp" => date("Y-m-d H:i:s")
    ],  JSON_UNESCAPED_UNICODE];
    exit(];
}

// 错误处理
function handleError($message, $code = 500) {
    error_log("API Error: $message"];
    sendResponse(false, null, $message, $code];
}

// 数据目录
$dataDir = __DIR__ . "/../../../../data/email";
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true];
}

// 数据文件路径
$templatesFile = $dataDir . "/templates.json";
$logsFile = $dataDir . "/logs.json";
$configFile = $dataDir . "/config.json";
$queueFile = $dataDir . "/queue.json";

// 初始化数据文件
function initDataFile($file, $defaultData = []) {
    if (!file_exists($file)) {
        file_put_contents($file, json_encode($defaultData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)];
    }
}

// 辅助函数
function loadJsonFile($file) {
    return file_exists($file) ? json_decode(file_get_contents($file], true) : [];
}

function saveJsonFile($file, $data) {
    return file_put_contents($file, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)];
}

function generateId() {
    return uniqid() . "_" . bin2hex(random_bytes(4)];
}

function validateEmailTemplate($data) {
    $required = ["name", "subject", "content", "type"];
    foreach ($required as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            return "缺少必填字段: $field";
        }
    }
    
    $validTypes = ["welcome", "verification", "password_reset", "notification", "marketing", "system"];
    if (!in_[$data["type"],  $validTypes)) {
        return "无效的邮件类型";
    }
    
    return null;
}

function generateEmailTemplate($type) {
    $templates = [
        "welcome" => [
            "subject" => "欢迎加入AlingAi Pro！",
            "content" => "<h1>欢迎！</h1><p>感谢您注册AlingAi Pro，开启AI助手新体验。</p><p><a href=\"{{login_url}}\">立即登录</a></p>",
            "variables" => ["username", "login_url", "support_email"]
        ], 
        "verification" => [
            "subject" => "邮箱验证 - AlingAi Pro",
            "content" => "<h1>邮箱验证</h1><p>请点击以下链接验证您的邮箱：</p><p><a href=\"{{verification_url}}\">验证邮箱</a></p><p>验证码：{{code}}</p>",
            "variables" => ["username", "verification_url", "code", "expire_time"]
        ], 
        "password_reset" => [
            "subject" => "密码重置 - AlingAi Pro",
            "content" => "<h1>密码重置</h1><p>请点击以下链接重置您的密码：</p><p><a href=\"{{reset_url}}\">重置密码</a></p><p>如非本人操作，请忽略此邮件。</p>",
            "variables" => ["username", "reset_url", "expire_time"]
        ], 
        "notification" => [
            "subject" => "系统通知 - AlingAi Pro",
            "content" => "<h1>{{title}}</h1><p>{{message}}</p><p>时间：{{timestamp}}</p>",
            "variables" => ["username", "title", "message", "timestamp"]
        ], 
        "marketing" => [
            "subject" => "{{campaign_title}} - AlingAi Pro",
            "content" => "<h1>{{campaign_title}}</h1><p>{{campaign_content}}</p><p><a href=\"{{action_url}}\">{{action_text}}</a></p><p>若不想再收到此类邮件，<a href=\"{{unsubscribe_url}}\">点击此处退订</a></p>",
            "variables" => ["username", "campaign_title", "campaign_content", "action_url", "action_text", "unsubscribe_url"]
        ], 
        "system" => [
            "subject" => "系统消息 - AlingAi Pro",
            "content" => "<h1>系统消息</h1><p>{{message}}</p>",
            "variables" => ["username", "message"]
        ]
    ];
    
    return isset($templates[$type]) ? $templates[$type] : null;
}

// 初始化数据文件
initDataFile($templatesFile, []];
initDataFile($logsFile, []];
initDataFile($configFile, [
    "smtp" => [
        "host" => "",
        "port" => 587,
        "username" => "",
        "password" => "",
        "encryption" => "tls",
        "from_email" => "",
        "from_name" => "AlingAi Pro"
    ], 
    "limits" => [
        "daily_limit" => 1000,
        "hourly_limit" => 100,
        "rate_limit" => 10 // 每分钟
    ], 
    "features" => [
        "tracking_enabled" => true,
        "bounce_handling" => true,
        "unsubscribe_tracking" => true,
        "open_tracking" => true,
        "click_tracking" => true
    ]
]];
initDataFile($queueFile, []];

// 授权验证
function authenticateRequest() {
    $auth = new AdminAuthServiceDemo(];
    
    // 从请求中获取授权信息
    $authHeader = $_SERVER["HTTP_AUTHORIZATION"] ?? "";
    
    if (empty($authHeader)) {
        handleError("未提供认证信息", 401];
    }
    
    // 处理Bearer Token
    $token = str_replace("Bearer ", "", $authHeader];
    if (!$auth->validateToken($token)) {
        handleError("无效的令牌", 401];
    }
    
    $user = $auth->getUserFromToken($token];
    
    // 检查是否是管理员
    if ($user["role"] !== "admin") {
        handleError("无权限访问此API", 403];
    }
    
    return $user;
}

// 处理请求
function handleRequest() {
    global $templatesFile, $logsFile, $configFile, $queueFile;
    
    // 验证管理员身份
    $admin = authenticateRequest(];
    
    $method = $_SERVER["REQUEST_METHOD"];
    $path = parse_url($_SERVER["REQUEST_URI"],  PHP_URL_PATH];
    $pathParts = explode("/", trim($path, "/")];
    $action = $pathParts[count($pathParts) - 1] ?? "";
    
    // 获取请求数据
    $requestData = json_decode(file_get_contents("php://input"], true) ?? [];
    
    // 根据路径和方法分发请求
    switch ($action) {
        case "templates":
            handleTemplates($method, $requestData];
            break;
            
        case "logs":
            handleLogs($method, $requestData];
            break;
            
        case "config":
            handleConfig($method, $requestData];
            break;
            
        case "queue":
            handleQueue($method, $requestData];
            break;
            
        case "send-test":
            handleSendTest($method, $requestData];
            break;
            
        default:
            handleApiInfo($method];
    }
}

// 处理邮件模板
function handleTemplates($method, $data) {
    global $templatesFile;
    
    $templates = loadJsonFile($templatesFile];
    
    switch ($method) {
        case "GET":
            // 获取单个模板或所有模板
            $templateId = $_GET["id"] ?? null;
            
            if ($templateId) {
                if (!isset($templates[$templateId])) {
                    handleError("模板不存在", 404];
                }
                sendResponse(true, $templates[$templateId],  "成功获取模板"];
            } else {
                sendResponse(true, $templates, "成功获取所有模板"];
            }
            break;
            
        case "POST":
            // 创建新模板
            $validationError = validateEmailTemplate($data];
            if ($validationError) {
                handleError($validationError, 400];
            }
            
            $templateId = generateId(];
            $data["id"] = $templateId;
            $data["created_at"] = date("Y-m-d H:i:s"];
            $data["updated_at"] = $data["created_at"];
            
            $templates[$templateId] = $data;
            saveJsonFile($templatesFile, $templates];
            
            sendResponse(true, $data, "成功创建模板", 201];
            break;
            
        case "PUT":
            // 更新模板
            $templateId = $_GET["id"] ?? null;
            
            if (!$templateId || !isset($templates[$templateId])) {
                handleError("模板不存在", 404];
            }
            
            $validationError = validateEmailTemplate($data];
            if ($validationError) {
                handleError($validationError, 400];
            }
            
            $data["id"] = $templateId;
            $data["created_at"] = $templates[$templateId]["created_at"];
            $data["updated_at"] = date("Y-m-d H:i:s"];
            
            $templates[$templateId] = $data;
            saveJsonFile($templatesFile, $templates];
            
            sendResponse(true, $data, "成功更新模板"];
            break;
            
        case "DELETE":
            // 删除模板
            $templateId = $_GET["id"] ?? null;
            
            if (!$templateId || !isset($templates[$templateId])) {
                handleError("模板不存在", 404];
            }
            
            unset($templates[$templateId]];
            saveJsonFile($templatesFile, $templates];
            
            sendResponse(true, null, "成功删除模板"];
            break;
            
        default:
            handleError("不支持的方法", 405];
    }
}

// API信息
function handleApiInfo($method) {
    if ($method !== "GET") {
        handleError("不支持的方法", 405];
    }
    
    sendResponse(true, [
        "name" => "AlingAi Pro Email API",
        "version" => "5.0.0",
        "description" => "邮件系统管理API - 管理模板、日志、配置和发送队列",
        "endpoints" => [
            "/templates" => "邮件模板管理",
            "/logs" => "邮件发送日志",
            "/config" => "邮件系统配置",
            "/queue" => "邮件发送队列",
            "/send-test" => "发送测试邮件"
        ]
    ],  "API信息"];
}

// 处理日志、配置和队列等功能 - 精简版本
function handleLogs($method, $data) {
    global $logsFile;
    $logs = loadJsonFile($logsFile];
    sendResponse(true, $logs, "成功获取邮件日志"];
}

function handleConfig($method, $data) {
    global $configFile;
    $config = loadJsonFile($configFile];
    
    if ($method === "GET") {
        // 获取配置
        sendResponse(true, $config, "成功获取邮件系统配置"];
    } else if ($method === "POST" || $method === "PUT") {
        // 更新配置
        saveJsonFile($configFile, $data];
        sendResponse(true, $data, "成功更新邮件系统配置"];
    } else {
        handleError("不支持的方法", 405];
    }
}

function handleQueue($method, $data) {
    global $queueFile;
    $queue = loadJsonFile($queueFile];
    sendResponse(true, $queue, "成功获取邮件队列"];
}

function handleSendTest($method, $data) {
    if ($method !== "POST") {
        handleError("不支持的方法", 405];
    }
    
    // 模拟测试邮件发送
    sendResponse(true, [
        "status" => "sent",
        "to" => $data["to"] ?? "test@example.com",
        "subject" => $data["subject"] ?? "测试邮件",
        "template_id" => $data["template_id"] ?? null,
        "sent_at" => date("Y-m-d H:i:s")
    ],  "测试邮件已发送"];
}

// 执行请求处理
try {
    handleRequest(];
} catch (Exception $e) {
    handleError("处理请求时发生错误: " . $e->getMessage(), 500];
}

