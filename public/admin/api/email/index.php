<?php
/**
 * AlingAi Pro 5.0 - �ʼ�ϵͳ����API
 * �ʼ�ģ���������׷�ٺ��ʼ���������
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

// ��Ӧ����
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

// ������
function handleError($message, $code = 500) {
    error_log("API Error: $message"];
    sendResponse(false, null, $message, $code];
}

// ����Ŀ¼
$dataDir = __DIR__ . "/../../../../data/email";
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true];
}

// �����ļ�·��
$templatesFile = $dataDir . "/templates.json";
$logsFile = $dataDir . "/logs.json";
$configFile = $dataDir . "/config.json";
$queueFile = $dataDir . "/queue.json";

// ��ʼ�������ļ�
function initDataFile($file, $defaultData = []) {
    if (!file_exists($file)) {
        file_put_contents($file, json_encode($defaultData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)];
    }
}

// ��������
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
            return "ȱ�ٱ����ֶ�: $field";
        }
    }
    
    $validTypes = ["welcome", "verification", "password_reset", "notification", "marketing", "system"];
    if (!in_[$data["type"],  $validTypes)) {
        return "��Ч���ʼ�����";
    }
    
    return null;
}

function generateEmailTemplate($type) {
    $templates = [
        "welcome" => [
            "subject" => "��ӭ����AlingAi Pro��",
            "content" => "<h1>��ӭ��</h1><p>��л��ע��AlingAi Pro������AI���������顣</p><p><a href=\"{{login_url}}\">������¼</a></p>",
            "variables" => ["username", "login_url", "support_email"]
        ], 
        "verification" => [
            "subject" => "������֤ - AlingAi Pro",
            "content" => "<h1>������֤</h1><p>��������������֤�������䣺</p><p><a href=\"{{verification_url}}\">��֤����</a></p><p>��֤�룺{{code}}</p>",
            "variables" => ["username", "verification_url", "code", "expire_time"]
        ], 
        "password_reset" => [
            "subject" => "�������� - AlingAi Pro",
            "content" => "<h1>��������</h1><p>�����������������������룺</p><p><a href=\"{{reset_url}}\">��������</a></p><p>��Ǳ��˲���������Դ��ʼ���</p>",
            "variables" => ["username", "reset_url", "expire_time"]
        ], 
        "notification" => [
            "subject" => "ϵͳ֪ͨ - AlingAi Pro",
            "content" => "<h1>{{title}}</h1><p>{{message}}</p><p>ʱ�䣺{{timestamp}}</p>",
            "variables" => ["username", "title", "message", "timestamp"]
        ], 
        "marketing" => [
            "subject" => "{{campaign_title}} - AlingAi Pro",
            "content" => "<h1>{{campaign_title}}</h1><p>{{campaign_content}}</p><p><a href=\"{{action_url}}\">{{action_text}}</a></p><p>���������յ������ʼ���<a href=\"{{unsubscribe_url}}\">����˴��˶�</a></p>",
            "variables" => ["username", "campaign_title", "campaign_content", "action_url", "action_text", "unsubscribe_url"]
        ], 
        "system" => [
            "subject" => "ϵͳ��Ϣ - AlingAi Pro",
            "content" => "<h1>ϵͳ��Ϣ</h1><p>{{message}}</p>",
            "variables" => ["username", "message"]
        ]
    ];
    
    return isset($templates[$type]) ? $templates[$type] : null;
}

// ��ʼ�������ļ�
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
        "rate_limit" => 10 // ÿ����
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

// ��Ȩ��֤
function authenticateRequest() {
    $auth = new AdminAuthServiceDemo(];
    
    // �������л�ȡ��Ȩ��Ϣ
    $authHeader = $_SERVER["HTTP_AUTHORIZATION"] ?? "";
    
    if (empty($authHeader)) {
        handleError("δ�ṩ��֤��Ϣ", 401];
    }
    
    // ����Bearer Token
    $token = str_replace("Bearer ", "", $authHeader];
    if (!$auth->validateToken($token)) {
        handleError("��Ч������", 401];
    }
    
    $user = $auth->getUserFromToken($token];
    
    // ����Ƿ��ǹ���Ա
    if ($user["role"] !== "admin") {
        handleError("��Ȩ�޷��ʴ�API", 403];
    }
    
    return $user;
}

// ��������
function handleRequest() {
    global $templatesFile, $logsFile, $configFile, $queueFile;
    
    // ��֤����Ա���
    $admin = authenticateRequest(];
    
    $method = $_SERVER["REQUEST_METHOD"];
    $path = parse_url($_SERVER["REQUEST_URI"],  PHP_URL_PATH];
    $pathParts = explode("/", trim($path, "/")];
    $action = $pathParts[count($pathParts) - 1] ?? "";
    
    // ��ȡ��������
    $requestData = json_decode(file_get_contents("php://input"], true) ?? [];
    
    // ����·���ͷ����ַ�����
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

// �����ʼ�ģ��
function handleTemplates($method, $data) {
    global $templatesFile;
    
    $templates = loadJsonFile($templatesFile];
    
    switch ($method) {
        case "GET":
            // ��ȡ����ģ�������ģ��
            $templateId = $_GET["id"] ?? null;
            
            if ($templateId) {
                if (!isset($templates[$templateId])) {
                    handleError("ģ�岻����", 404];
                }
                sendResponse(true, $templates[$templateId],  "�ɹ���ȡģ��"];
            } else {
                sendResponse(true, $templates, "�ɹ���ȡ����ģ��"];
            }
            break;
            
        case "POST":
            // ������ģ��
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
            
            sendResponse(true, $data, "�ɹ�����ģ��", 201];
            break;
            
        case "PUT":
            // ����ģ��
            $templateId = $_GET["id"] ?? null;
            
            if (!$templateId || !isset($templates[$templateId])) {
                handleError("ģ�岻����", 404];
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
            
            sendResponse(true, $data, "�ɹ�����ģ��"];
            break;
            
        case "DELETE":
            // ɾ��ģ��
            $templateId = $_GET["id"] ?? null;
            
            if (!$templateId || !isset($templates[$templateId])) {
                handleError("ģ�岻����", 404];
            }
            
            unset($templates[$templateId]];
            saveJsonFile($templatesFile, $templates];
            
            sendResponse(true, null, "�ɹ�ɾ��ģ��"];
            break;
            
        default:
            handleError("��֧�ֵķ���", 405];
    }
}

// API��Ϣ
function handleApiInfo($method) {
    if ($method !== "GET") {
        handleError("��֧�ֵķ���", 405];
    }
    
    sendResponse(true, [
        "name" => "AlingAi Pro Email API",
        "version" => "5.0.0",
        "description" => "�ʼ�ϵͳ����API - ����ģ�塢��־�����úͷ��Ͷ���",
        "endpoints" => [
            "/templates" => "�ʼ�ģ�����",
            "/logs" => "�ʼ�������־",
            "/config" => "�ʼ�ϵͳ����",
            "/queue" => "�ʼ����Ͷ���",
            "/send-test" => "���Ͳ����ʼ�"
        ]
    ],  "API��Ϣ"];
}

// ������־�����úͶ��еȹ��� - ����汾
function handleLogs($method, $data) {
    global $logsFile;
    $logs = loadJsonFile($logsFile];
    sendResponse(true, $logs, "�ɹ���ȡ�ʼ���־"];
}

function handleConfig($method, $data) {
    global $configFile;
    $config = loadJsonFile($configFile];
    
    if ($method === "GET") {
        // ��ȡ����
        sendResponse(true, $config, "�ɹ���ȡ�ʼ�ϵͳ����"];
    } else if ($method === "POST" || $method === "PUT") {
        // ��������
        saveJsonFile($configFile, $data];
        sendResponse(true, $data, "�ɹ������ʼ�ϵͳ����"];
    } else {
        handleError("��֧�ֵķ���", 405];
    }
}

function handleQueue($method, $data) {
    global $queueFile;
    $queue = loadJsonFile($queueFile];
    sendResponse(true, $queue, "�ɹ���ȡ�ʼ�����"];
}

function handleSendTest($method, $data) {
    if ($method !== "POST") {
        handleError("��֧�ֵķ���", 405];
    }
    
    // ģ������ʼ�����
    sendResponse(true, [
        "status" => "sent",
        "to" => $data["to"] ?? "test@example.com",
        "subject" => $data["subject"] ?? "�����ʼ�",
        "template_id" => $data["template_id"] ?? null,
        "sent_at" => date("Y-m-d H:i:s")
    ],  "�����ʼ��ѷ���"];
}

// ִ��������
try {
    handleRequest(];
} catch (Exception $e) {
    handleError("��������ʱ��������: " . $e->getMessage(), 500];
}

