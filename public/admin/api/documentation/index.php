<?php
/**
 * AlingAi Pro 5.0 - API�ĵ�����ϵͳ
 * �Զ�ɨ�������API�ĵ���֧��OpenAPI/Swagger��ʽ
 */

declare(strict_types=1);

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit();
}

require_once __DIR__ . "/../../../../vendor/autoload.php";
require_once __DIR__ . "/../../../../src/Auth/AdminAuthServiceDemo.php";

use AlingAi\Auth\AdminAuthServiceDemo;

// ��Ӧ����
function sendResponse($success, $data = null, $message = "", $code = 200)
{
    http_response_code($code);
    echo json_encode([
        "success" => $success,
        "data" => $data,
        "message" => $message,
        "timestamp" => date("Y-m-d H:i:s")
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

// ������
function handleError($message, $code = 500) {
    error_log("API Error: $message");
    sendResponse(false, null, $message, $code);
}

// API�ĵ���������
function generateApiDocumentation() {
    return [
        "openapi" => "3.0.0",
        "info" => [
            "title" => "AlingAi Pro API",
            "description" => "AlingAi Pro API�ĵ�ϵͳ - �û�������ϵͳ��صȹ���",
            "version" => "6.0.0",
            "contact" => [
                "name" => "AlingAi Team",
                "email" => "api@gxggm.com",
                "url" => "https://alingai.com"
            ],
            "license" => [
                "name" => "MIT",
                "url" => "https://opensource.org/licenses/MIT"
            ]
        ],
        "servers" => [
            [
                "url" => "http://localhost",
                "description" => "���ؿ�������"
            ],
            [
                "url" => "https://api.alingai.com",
                "description" => "��������"
            ]
        ],
        "security" => [
            ["bearerAuth" => []],
            ["apiKey" => []]
        ],
        "paths" => generateApiPaths(),
        "components" => generateApiComponents()
    ];
}

function generateApiPaths() {
    return [
        // ��֤���API
        "/api/auth/login" => [
            "post" => [
                "tags" => ["��֤"],
                "summary" => "�û���¼",
                "description" => "ʹ���û���/�����������е�¼",
                "requestBody" => [
                    "required" => true,
                    "content" => [
                        "application/json" => [
                            "schema" => [
                                "type" => "object",
                                "required" => ["username", "password"],
                                "properties" => [
                                    "username" => ["type" => "string", "description" => "�û���������"],
                                    "password" => ["type" => "string", "description" => "����"],
                                    "remember" => ["type" => "boolean", "description" => "��ס��¼״̬"]
                                ]
                            ]
                        ]
                    ]
                ],
                "responses" => [
                    "200" => [
                        "description" => "��¼�ɹ�",
                        "content" => [
                            "application/json" => [
                                "schema" => ["\$ref" => "#/components/schemas/AuthResponse"]
                            ]
                        ]
                    ],
                    "401" => ["description" => "��֤ʧ��"]
                ]
            ]
        ]
    ];
}

function generateApiComponents() {
    return [
        "schemas" => [
            "AuthResponse" => [
                "type" => "object",
                "properties" => [
                    "success" => ["type" => "boolean"],
                    "data" => [
                        "type" => "object",
                        "properties" => [
                            "token" => ["type" => "string"],
                            "user" => ["\$ref" => "#/components/schemas/User"]
                        ]
                    ],
                    "message" => ["type" => "string"]
                ]
            ],
            "User" => [
                "type" => "object",
                "properties" => [
                    "id" => ["type" => "integer"],
                    "username" => ["type" => "string"],
                    "email" => ["type" => "string"],
                    "avatar" => ["type" => "string"],
                    "role" => ["type" => "string"]
                ]
            ]
        ],
        "securitySchemes" => [
            "bearerAuth" => [
                "type" => "http",
                "scheme" => "bearer",
                "bearerFormat" => "JWT"
            ],
            "apiKey" => [
                "type" => "apiKey",
                "in" => "header",
                "name" => "X-API-Key"
            ]
        ]
    ];
}

// ��Ȩ��֤
function authenticateRequest() {
    $auth = new AdminAuthServiceDemo();
    
    // �������л�ȡ��Ȩ��Ϣ
    $authHeader = $_SERVER["HTTP_AUTHORIZATION"] ?? "";
    $apiKey = $_SERVER["HTTP_X_API_KEY"] ?? "";
    
    if (empty($authHeader) && empty($apiKey)) {
        handleError("δ�ṩ��֤��Ϣ", 401);
    }
    
    // ����Bearer Token
    if (!empty($authHeader)) {
        $token = str_replace("Bearer ", "", $authHeader);
        if (!$auth->validateToken($token)) {
            handleError("��Ч������", 401);
        }
        return $auth->getUserFromToken($token);
    }
    
    // ����API Key
    if (!empty($apiKey)) {
        if (!$auth->validateApiKey($apiKey)) {
            handleError("��Ч��API��Կ", 401);
        }
        return $auth->getUserFromApiKey($apiKey);
    }
    
    handleError("��֤ʧ��", 401);
}

// ��������
function handleRequest() {
    $method = $_SERVER["REQUEST_METHOD"];
    $path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
    $pathParts = explode("/", trim($path, "/"));
    $action = $pathParts[count($pathParts) - 1] ?? "";
    
    // �����OPTIONS����CORSԤ����Ӧ����ǰ�洦��
    if ($method === "OPTIONS") {
        return;
    }
    
    // ����·���ͷ����ַ�����
    switch ($action) {
        case "schema":
            // ��ȡAPI�ĵ��ṹ
            sendResponse(true, generateApiDocumentation(), "�ɹ���ȡAPI�ĵ�");
            break;
            
        default:
            // Ĭ�Ϸ���������API�ĵ�
            $docs = generateApiDocumentation();
            sendResponse(true, $docs, "�ɹ���ȡAPI�ĵ�");
    }
}

// ִ��������
try {
    handleRequest();
} catch (Exception $e) {
    handleError("��������ʱ��������: " . $e->getMessage(), 500);
}

