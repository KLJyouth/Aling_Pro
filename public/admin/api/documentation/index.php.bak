<?php
/**
 * AlingAi Pro 5.0 - API锟侥碉拷锟斤拷锟斤拷系统
 * 锟皆讹拷扫锟斤拷锟斤拷锟斤拷锟紸PI锟侥碉拷锟斤拷支锟斤拷OpenAPI/Swagger锟斤拷式
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

// 锟斤拷应锟斤拷锟斤拷
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

// 锟斤拷锟斤拷锟斤拷
function handleError($message, $code = 500) {
    error_log("API Error: $message");
    sendResponse(false, null, $message, $code);
}

// API锟侥碉拷锟斤拷锟斤拷锟斤拷锟斤拷
function generateApiDocumentation() {
    return [
        "openapi" => "3.0.0",
        "info" => [
            "title" => "AlingAi Pro API",
            "description" => "AlingAi Pro锟斤拷锟斤拷API锟侥碉拷 - 锟斤拷锟斤拷锟矫伙拷锟斤拷锟斤拷锟斤拷锟斤拷锟届、系统锟斤拷氐锟斤拷锟斤拷泄锟斤拷锟?",
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
                "description" => "锟斤拷锟截匡拷锟斤拷锟斤拷锟斤拷"
            ],
            [
                "url" => "https://api.alingai.com",
                "description" => "锟斤拷锟斤拷锟斤拷锟斤拷"
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
        // 锟斤拷证锟斤拷锟紸PI
        "/api/auth/login" => [
            "post" => [
                "tags" => ["锟斤拷证"],
                "summary" => "锟矫伙拷锟斤拷录",
                "description" => "使锟斤拷锟矫伙拷锟斤拷/锟斤拷锟斤拷锟斤拷锟斤拷锟斤拷锟叫碉拷录",
                "requestBody" => [
                    "required" => true,
                    "content" => [
                        "application/json" => [
                            "schema" => [
                                "type" => "object",
                                "required" => ["username", "password"],
                                "properties" => [
                                    "username" => ["type" => "string", "description" => "锟矫伙拷锟斤拷锟斤拷锟斤拷锟斤拷"],
                                    "password" => ["type" => "string", "description" => "锟斤拷锟斤拷"],
                                    "remember" => ["type" => "boolean", "description" => "锟斤拷住锟斤拷录状态"]
                                ]
                            ]
                        ]
                    ]
                ],
                "responses" => [
                    "200" => [
                        "description" => "锟斤拷录锟缴癸拷",
                        "content" => [
                            "application/json" => [
                                "schema" => ["\$ref" => "#/components/schemas/AuthResponse"]
                            ]
                        ]
                    ],
                    "401" => ["description" => "锟斤拷证失锟斤拷"]
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

// 锟斤拷权锟斤拷证
function authenticateRequest() {
    $auth = new AdminAuthServiceDemo();
    
    // 锟斤拷锟斤拷锟斤拷锟叫伙拷取锟斤拷权锟斤拷息
    $authHeader = $_SERVER["HTTP_AUTHORIZATION"] ?? "";
    $apiKey = $_SERVER["HTTP_X_API_KEY"] ?? "";
    
    if (empty($authHeader) && empty($apiKey)) {
        handleError("未锟结供锟斤拷证锟斤拷息", 401);
    }
    
    // 锟斤拷锟斤拷Bearer Token
    if (!empty($authHeader)) {
        $token = str_replace("Bearer ", "", $authHeader);
        if (!$auth->validateToken($token)) {
            handleError("锟斤拷效锟斤拷锟斤拷锟斤拷", 401);
        }
        return $auth->getUserFromToken($token);
    }
    
    // 锟斤拷锟斤拷API Key
    if (!empty($apiKey)) {
        if (!$auth->validateApiKey($apiKey)) {
            handleError("锟斤拷效锟斤拷API锟斤拷钥", 401);
        }
        return $auth->getUserFromApiKey($apiKey);
    }
    
    handleError("锟斤拷证失锟斤拷", 401);
}

// 锟斤拷锟斤拷锟斤拷锟斤拷
function handleRequest() {
    $method = $_SERVER["REQUEST_METHOD"];
    $path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
    $pathParts = explode("/", trim($path, "/"));
    $action = $pathParts[count($pathParts) - 1] ?? "";
    
    // 锟斤拷锟斤拷锟絆PTIONS锟斤拷锟斤拷CORS预锟斤拷锟斤拷应锟斤拷锟斤拷前锟芥处锟斤拷
    if ($method === "OPTIONS") {
        return;
    }
    
    // 锟斤拷锟斤拷路锟斤拷锟酵凤拷锟斤拷锟街凤拷锟斤拷锟斤拷
    switch ($action) {
        case "schema":
            // 锟斤拷取API锟侥碉拷锟结构
            sendResponse(true, generateApiDocumentation(), "锟缴癸拷锟斤拷取API锟侥碉拷");
            break;
            
        default:
            // 默锟较凤拷锟斤拷锟斤拷锟斤拷锟斤拷API锟侥碉拷
            $docs = generateApiDocumentation();
            sendResponse(true, $docs, "锟缴癸拷锟斤拷取API锟侥碉拷");
    }
}

// 执锟斤拷锟斤拷锟斤拷锟斤拷
try {
    handleRequest();
} catch (Exception $e) {
    handleError("锟斤拷锟斤拷锟斤拷锟斤拷时锟斤拷锟斤拷锟斤拷锟斤拷: " . $e->getMessage(), 500);
}

