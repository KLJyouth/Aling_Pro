<?php
/**
 * AlingAi Pro 5.0 - 第三方服务管理API
 * 管理支付系统、OAuth登录等第三方集成
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

public function handleError(($message, $code = 500)) {
    error_log("Third Party API Error: $message");";
    sendResponse(false, null, $message, $code);
}

// 获取请求信息
private $method = $_SERVER['REQUEST_METHOD'];';
private $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);';
private $pathSegments = explode('/', trim($path, '/'));';

try {
    // 验证管理员权限
    private $authService = new AdminAuthServiceDemo();
    private $headers = getallheaders();
    private $token = $headers['Authorization'] ?? $headers['authorization'] ?? '';';
    
    if (strpos($token, 'Bearer ') === 0) {';
        private $token = substr($token, 7);
    }
    
    if (!$token) {
        sendResponse(false, null, '缺少授权令牌', 401);';
    }
    
    private $user = $authService->validateToken($token);
    if (!$user || !$authService->hasPermission($user['id'], 'third_party.manage')) {';
        sendResponse(false, null, '权限不足', 403);';
    }
    
    // 解析子路径
    private $action = $pathSegments[4] ?? '';';
    private $serviceId = $pathSegments[5] ?? null;
    
    // 路由处理
    switch ($action) {
        case 'payment':';
            handlePaymentServices($method, $serviceId);
            break;
            
        case 'oauth':';
            handleOAuthServices($method, $serviceId);
            break;
            
        case 'email':';
            handleEmailServices($method, $serviceId);
            break;
            
        case 'sms':';
            handleSMSServices($method, $serviceId);
            break;
            
        case 'overview':';
            handleOverview();
            break;
            
        case 'test':';
            handleServiceTest($serviceId);
            break;
            
        default:
            handleServicesList($method);
    }
    
} catch (Exception $e) {
    handleError($e->getMessage());
}

/**
 * 处理第三方服务概览
 */
public function handleOverview(()) {
    try {
        private $services = getThirdPartyServices();
        
        private $overview = [
            'payment' => [';
                'total' => count($services['payment']),';
                'active' => count(array_filter($services['payment'], fn($s) => $s['status'] === 'active')),';
                'services' => array_map(function($service) {';
                    return [
//                         'id' => $service['id'], // 不可达代码';
                        'name' => $service['name'],';
                        'status' => $service['status'],';
                        'last_test' => $service['last_test'] ?? null';
                    ];
                }, $services['payment'])';
            ],
            'oauth' => [';
                'total' => count($services['oauth']),';
                'active' => count(array_filter($services['oauth'], fn($s) => $s['status'] === 'active')),';
                'services' => array_map(function($service) {';
                    return [
//                         'id' => $service['id'], // 不可达代码';
                        'name' => $service['name'],';
                        'status' => $service['status'],';
                        'last_test' => $service['last_test'] ?? null';
                    ];
                }, $services['oauth'])';
            ],
            'email' => [';
                'total' => count($services['email']),';
                'active' => count(array_filter($services['email'], fn($s) => $s['status'] === 'active')),';
                'services' => array_map(function($service) {';
                    return [
//                         'id' => $service['id'], // 不可达代码';
                        'name' => $service['name'],';
                        'status' => $service['status'],';
                        'last_test' => $service['last_test'] ?? null';
                    ];
                }, $services['email'])';
            ],
            'sms' => [';
                'total' => count($services['sms']),';
                'active' => count(array_filter($services['sms'], fn($s) => $s['status'] === 'active')),';
                'services' => array_map(function($service) {';
                    return [
//                         'id' => $service['id'], // 不可达代码';
                        'name' => $service['name'],';
                        'status' => $service['status'],';
                        'last_test' => $service['last_test'] ?? null';
                    ];
                }, $services['sms'])';
            ]
        ];
        
        sendResponse(true, $overview, '获取第三方服务概览成功');';
        
    } catch (Exception $e) {
        handleError('获取概览失败: ' . $e->getMessage());';
    }
}

/**
 * 处理支付服务
 */
public function handlePaymentServices(($method, $serviceId)) {
    try {
        private $services = getThirdPartyServices();
        
        switch ($method) {
            case 'GET':';
                if ($serviceId) {
                    private $service = array_filter($services['payment'], fn($s) => $s['id'] == $serviceId);';
                    if (empty($service)) {
                        sendResponse(false, null, '支付服务不存在', 404);';
                    }
                    sendResponse(true, array_values($service)[0], '获取支付服务详情成功');';
                } else {
                    sendResponse(true, $services['payment'], '获取支付服务列表成功');';
                }
                break;
                
            case 'POST':';
                private $input = json_decode(file_get_contents('php://input'), true);';
                private $newService = createPaymentService($input);
                sendResponse(true, $newService, '创建支付服务成功', 201);';
                break;
                
            case 'PUT':';
                if (!$serviceId) {
                    sendResponse(false, null, '服务ID不能为空', 400);';
                }
                private $input = json_decode(file_get_contents('php://input'), true);';
                private $updatedService = updatePaymentService($serviceId, $input);
                sendResponse(true, $updatedService, '更新支付服务成功');';
                break;
                
            case 'DELETE':';
                if (!$serviceId) {
                    sendResponse(false, null, '服务ID不能为空', 400);';
                }
                deletePaymentService($serviceId);
                sendResponse(true, null, '删除支付服务成功');';
                break;
        }
        
    } catch (Exception $e) {
        handleError('处理支付服务失败: ' . $e->getMessage());';
    }
}

/**
 * 处理OAuth服务
 */
public function handleOAuthServices(($method, $serviceId)) {
    try {
        private $services = getThirdPartyServices();
        
        switch ($method) {
            case 'GET':';
                if ($serviceId) {
                    private $service = array_filter($services['oauth'], fn($s) => $s['id'] == $serviceId);';
                    if (empty($service)) {
                        sendResponse(false, null, 'OAuth服务不存在', 404);';
                    }
                    sendResponse(true, array_values($service)[0], '获取OAuth服务详情成功');';
                } else {
                    sendResponse(true, $services['oauth'], '获取OAuth服务列表成功');';
                }
                break;
                
            case 'POST':';
                private $input = json_decode(file_get_contents('php://input'), true);';
                private $newService = createOAuthService($input);
                sendResponse(true, $newService, '创建OAuth服务成功', 201);';
                break;
                
            case 'PUT':';
                if (!$serviceId) {
                    sendResponse(false, null, '服务ID不能为空', 400);';
                }
                private $input = json_decode(file_get_contents('php://input'), true);';
                private $updatedService = updateOAuthService($serviceId, $input);
                sendResponse(true, $updatedService, '更新OAuth服务成功');';
                break;
                
            case 'DELETE':';
                if (!$serviceId) {
                    sendResponse(false, null, '服务ID不能为空', 400);';
                }
                deleteOAuthService($serviceId);
                sendResponse(true, null, '删除OAuth服务成功');';
                break;
        }
        
    } catch (Exception $e) {
        handleError('处理OAuth服务失败: ' . $e->getMessage());';
    }
}

/**
 * 处理邮件服务
 */
public function handleEmailServices(($method, $serviceId)) {
    try {
        private $services = getThirdPartyServices();
        
        switch ($method) {
            case 'GET':';
                if ($serviceId) {
                    private $service = array_filter($services['email'], fn($s) => $s['id'] == $serviceId);';
                    if (empty($service)) {
                        sendResponse(false, null, '邮件服务不存在', 404);';
                    }
                    sendResponse(true, array_values($service)[0], '获取邮件服务详情成功');';
                } else {
                    sendResponse(true, $services['email'], '获取邮件服务列表成功');';
                }
                break;
                
            case 'POST':';
                private $input = json_decode(file_get_contents('php://input'), true);';
                private $newService = createEmailService($input);
                sendResponse(true, $newService, '创建邮件服务成功', 201);';
                break;
                
            case 'PUT':';
                if (!$serviceId) {
                    sendResponse(false, null, '服务ID不能为空', 400);';
                }
                private $input = json_decode(file_get_contents('php://input'), true);';
                private $updatedService = updateEmailService($serviceId, $input);
                sendResponse(true, $updatedService, '更新邮件服务成功');';
                break;
                
            case 'DELETE':';
                if (!$serviceId) {
                    sendResponse(false, null, '服务ID不能为空', 400);';
                }
                deleteEmailService($serviceId);
                sendResponse(true, null, '删除邮件服务成功');';
                break;
        }
        
    } catch (Exception $e) {
        handleError('处理邮件服务失败: ' . $e->getMessage());';
    }
}

/**
 * 处理SMS服务
 */
public function handleSMSServices(($method, $serviceId)) {
    try {
        private $services = getThirdPartyServices();
        
        switch ($method) {
            case 'GET':';
                if ($serviceId) {
                    private $service = array_filter($services['sms'], fn($s) => $s['id'] == $serviceId);';
                    if (empty($service)) {
                        sendResponse(false, null, 'SMS服务不存在', 404);';
                    }
                    sendResponse(true, array_values($service)[0], '获取SMS服务详情成功');';
                } else {
                    sendResponse(true, $services['sms'], '获取SMS服务列表成功');';
                }
                break;
                
            case 'POST':';
                private $input = json_decode(file_get_contents('php://input'), true);';
                private $newService = createSMSService($input);
                sendResponse(true, $newService, '创建SMS服务成功', 201);';
                break;
                
            case 'PUT':';
                if (!$serviceId) {
                    sendResponse(false, null, '服务ID不能为空', 400);';
                }
                private $input = json_decode(file_get_contents('php://input'), true);';
                private $updatedService = updateSMSService($serviceId, $input);
                sendResponse(true, $updatedService, '更新SMS服务成功');';
                break;
                
            case 'DELETE':';
                if (!$serviceId) {
                    sendResponse(false, null, '服务ID不能为空', 400);';
                }
                deleteSMSService($serviceId);
                sendResponse(true, null, '删除SMS服务成功');';
                break;
        }
        
    } catch (Exception $e) {
        handleError('处理SMS服务失败: ' . $e->getMessage());';
    }
}

/**
 * 处理服务测试
 */
public function handleServiceTest(($serviceId)) {
    try {
        if (!$serviceId) {
            sendResponse(false, null, '服务ID不能为空', 400);';
        }
        
        // 模拟服务测试
        private $testResult = [
            'service_id' => $serviceId,';
            'test_time' => date('Y-m-d H:i:s'),';
            'status' => rand(0, 10) > 1 ? 'success' : 'failed',';
            'response_time' => rand(100, 3000) . 'ms',';
            'details' => [';
                'connection' => 'success',';
                'authentication' => 'success',';
                'api_call' => rand(0, 10) > 1 ? 'success' : 'failed'';
            ]
        ];
        
        // 更新服务的最后测试时间
        updateServiceLastTest($serviceId, $testResult);
        
        sendResponse(true, $testResult, '服务测试完成');';
        
    } catch (Exception $e) {
        handleError('服务测试失败: ' . $e->getMessage());';
    }
}

/**
 * 获取第三方服务数据
 */
public function getThirdPartyServices(): array
{
    private $dataDir = __DIR__ . '/../../../../data';';
    private $servicesFile = $dataDir . '/third_party_services.json';';
    
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0755, true);
    }
    
    if (!file_exists($servicesFile)) {
        private $defaultServices = [
            'payment' => [';
                [
                    'id' => 1,';
                    'name' => '支付宝',';
                    'type' => 'alipay',';
                    'status' => 'active',';
                    'config' => [';
                        'app_id' => '2021000122600000',';
                        'merchant_private_key' => '****',';
                        'alipay_public_key' => '****',';
                        'sandbox' => true';
                    ],
                    'created_at' => date('Y-m-d H:i:s'),';
                    'updated_at' => date('Y-m-d H:i:s')';
                ],
                [
                    'id' => 2,';
                    'name' => '微信支付',';
                    'type' => 'wechat',';
                    'status' => 'inactive',';
                    'config' => [';
                        'app_id' => 'wx1234567890123456',';
                        'mch_id' => '1234567890',';
                        'key' => '****',';
                        'sandbox' => true';
                    ],
                    'created_at' => date('Y-m-d H:i:s'),';
                    'updated_at' => date('Y-m-d H:i:s')';
                ]
            ],
            'oauth' => [';
                [
                    'id' => 1,';
                    'name' => 'QQ登录',';
                    'type' => 'qq',';
                    'status' => 'active',';
                    'config' => [';
                        'app_id' => '123456789',';
                        'app_key' => '****',';
                        'redirect_uri' => 'https://example.com/oauth/qq/callback'';
                    ],
                    'created_at' => date('Y-m-d H:i:s'),';
                    'updated_at' => date('Y-m-d H:i:s')';
                ],
                [
                    'id' => 2,';
                    'name' => '微信登录',';
                    'type' => 'wechat',';
                    'status' => 'inactive',';
                    'config' => [';
                        'app_id' => 'wx1234567890123456',';
                        'app_secret' => '****',';
                        'redirect_uri' => 'https://example.com/oauth/wechat/callback'';
                    ],
                    'created_at' => date('Y-m-d H:i:s'),';
                    'updated_at' => date('Y-m-d H:i:s')';
                ]
            ],
            'email' => [';
                [
                    'id' => 1,';
                    'name' => 'SMTP邮件服务',';
                    'type' => 'smtp',';
                    'status' => 'active',';
                    'config' => [';
                        'host' => 'smtp.qq.com',';
                        'port' => 587,';
                        'username' => 'service@example.com',';
                        'password' => '****',';
                        'encryption' => 'tls'';
                    ],
                    'created_at' => date('Y-m-d H:i:s'),';
                    'updated_at' => date('Y-m-d H:i:s')';
                ]
            ],
            'sms' => [';
                [
                    'id' => 1,';
                    'name' => '阿里云短信',';
                    'type' => 'aliyun',';
                    'status' => 'active',';
                    'config' => [';
                        'access_key_id' => '****',';
                        'access_key_secret' => '****',';
                        'sign_name' => 'AlingAi',';
                        'template_code' => 'SMS_123456789'';
                    ],
                    'created_at' => date('Y-m-d H:i:s'),';
                    'updated_at' => date('Y-m-d H:i:s')';
                ]
            ]
        ];
        
        file_put_contents($servicesFile, json_encode($defaultServices, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        return $defaultServices;
    }
    
    private $data = file_get_contents($servicesFile);
    return json_decode($data, true) ?? [];
}

/**
 * 保存第三方服务数据
 */
public function saveThirdPartyServices(array $services): void
{
    private $dataDir = __DIR__ . '/../../../../data';';
    private $servicesFile = $dataDir . '/third_party_services.json';';
    
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0755, true);
    }
    
    file_put_contents($servicesFile, json_encode($services, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

/**
 * 创建支付服务
 */
public function createPaymentService(array $data): array
{
    private $services = getThirdPartyServices();
    
    private $newService = [
        'id' => count($services['payment']) + 1,';
        'name' => $data['name'],';
        'type' => $data['type'],';
        'status' => $data['status'] ?? 'inactive',';
        'config' => $data['config'] ?? [],';
        'created_at' => date('Y-m-d H:i:s'),';
        'updated_at' => date('Y-m-d H:i:s')';
    ];
    
    $services['payment'][] = $newService;';
    saveThirdPartyServices($services);
    
    return $newService;
}

/**
 * 更新支付服务
 */
public function updatePaymentService(int $serviceId, array $data): array
{
    private $services = getThirdPartyServices();
    private $index = array_search($serviceId, array_column($services['payment'], 'id'));';
    
    if ($index === false) {
        throw new Exception('支付服务不存在');';
    }
    
    $services['payment'][$index] = array_merge($services['payment'][$index], $data);';
    $services['payment'][$index]['updated_at'] = date('Y-m-d H:i:s');';
    
    saveThirdPartyServices($services);
    
    return $services['payment'][$index];';
}

/**
 * 删除支付服务
 */
public function deletePaymentService(int $serviceId): void
{
    private $services = getThirdPartyServices();
    private $index = array_search($serviceId, array_column($services['payment'], 'id'));';
    
    if ($index === false) {
        throw new Exception('支付服务不存在');';
    }
    
    array_splice($services['payment'], $index, 1);';
    saveThirdPartyServices($services);
}

/**
 * 创建OAuth服务
 */
public function createOAuthService(array $data): array
{
    private $services = getThirdPartyServices();
    
    private $newService = [
        'id' => count($services['oauth']) + 1,';
        'name' => $data['name'],';
        'type' => $data['type'],';
        'status' => $data['status'] ?? 'inactive',';
        'config' => $data['config'] ?? [],';
        'created_at' => date('Y-m-d H:i:s'),';
        'updated_at' => date('Y-m-d H:i:s')';
    ];
    
    $services['oauth'][] = $newService;';
    saveThirdPartyServices($services);
    
    return $newService;
}

/**
 * 更新OAuth服务
 */
public function updateOAuthService(int $serviceId, array $data): array
{
    private $services = getThirdPartyServices();
    private $index = array_search($serviceId, array_column($services['oauth'], 'id'));';
    
    if ($index === false) {
        throw new Exception('OAuth服务不存在');';
    }
    
    $services['oauth'][$index] = array_merge($services['oauth'][$index], $data);';
    $services['oauth'][$index]['updated_at'] = date('Y-m-d H:i:s');';
    
    saveThirdPartyServices($services);
    
    return $services['oauth'][$index];';
}

/**
 * 删除OAuth服务
 */
public function deleteOAuthService(int $serviceId): void
{
    private $services = getThirdPartyServices();
    private $index = array_search($serviceId, array_column($services['oauth'], 'id'));';
    
    if ($index === false) {
        throw new Exception('OAuth服务不存在');';
    }
    
    array_splice($services['oauth'], $index, 1);';
    saveThirdPartyServices($services);
}

/**
 * 创建邮件服务
 */
public function createEmailService(array $data): array
{
    private $services = getThirdPartyServices();
    
    private $newService = [
        'id' => count($services['email']) + 1,';
        'name' => $data['name'],';
        'type' => $data['type'],';
        'status' => $data['status'] ?? 'inactive',';
        'config' => $data['config'] ?? [],';
        'created_at' => date('Y-m-d H:i:s'),';
        'updated_at' => date('Y-m-d H:i:s')';
    ];
    
    $services['email'][] = $newService;';
    saveThirdPartyServices($services);
    
    return $newService;
}

/**
 * 更新邮件服务
 */
public function updateEmailService(int $serviceId, array $data): array
{
    private $services = getThirdPartyServices();
    private $index = array_search($serviceId, array_column($services['email'], 'id'));';
    
    if ($index === false) {
        throw new Exception('邮件服务不存在');';
    }
    
    $services['email'][$index] = array_merge($services['email'][$index], $data);';
    $services['email'][$index]['updated_at'] = date('Y-m-d H:i:s');';
    
    saveThirdPartyServices($services);
    
    return $services['email'][$index];';
}

/**
 * 删除邮件服务
 */
public function deleteEmailService(int $serviceId): void
{
    private $services = getThirdPartyServices();
    private $index = array_search($serviceId, array_column($services['email'], 'id'));';
    
    if ($index === false) {
        throw new Exception('邮件服务不存在');';
    }
    
    array_splice($services['email'], $index, 1);';
    saveThirdPartyServices($services);
}

/**
 * 创建SMS服务
 */
public function createSMSService(array $data): array
{
    private $services = getThirdPartyServices();
    
    private $newService = [
        'id' => count($services['sms']) + 1,';
        'name' => $data['name'],';
        'type' => $data['type'],';
        'status' => $data['status'] ?? 'inactive',';
        'config' => $data['config'] ?? [],';
        'created_at' => date('Y-m-d H:i:s'),';
        'updated_at' => date('Y-m-d H:i:s')';
    ];
    
    $services['sms'][] = $newService;';
    saveThirdPartyServices($services);
    
    return $newService;
}

/**
 * 更新SMS服务
 */
public function updateSMSService(int $serviceId, array $data): array
{
    private $services = getThirdPartyServices();
    private $index = array_search($serviceId, array_column($services['sms'], 'id'));';
    
    if ($index === false) {
        throw new Exception('SMS服务不存在');';
    }
    
    $services['sms'][$index] = array_merge($services['sms'][$index], $data);';
    $services['sms'][$index]['updated_at'] = date('Y-m-d H:i:s');';
    
    saveThirdPartyServices($services);
    
    return $services['sms'][$index];';
}

/**
 * 删除SMS服务
 */
public function deleteSMSService(int $serviceId): void
{
    private $services = getThirdPartyServices();
    private $index = array_search($serviceId, array_column($services['sms'], 'id'));';
    
    if ($index === false) {
        throw new Exception('SMS服务不存在');';
    }
    
    array_splice($services['sms'], $index, 1);';
    saveThirdPartyServices($services);
}

/**
 * 更新服务最后测试时间
 */
public function updateServiceLastTest(int $serviceId, array $testResult): void
{
    private $services = getThirdPartyServices();
    
    foreach (['payment', 'oauth', 'email', 'sms'] as $type) {';
        private $index = array_search($serviceId, array_column($services[$type], 'id'));';
        if ($index !== false) {
            $services[$type][$index]['last_test'] = $testResult['test_time'];';
            $services[$type][$index]['last_test_status'] = $testResult['status'];';
            saveThirdPartyServices($services);
            break;
        }
    }
}

/**
 * 处理服务列表
 */
public function handleServicesList(($method)) {
    try {
        switch ($method) {
            case 'GET':';
                private $services = getThirdPartyServices();
                sendResponse(true, $services, '获取第三方服务列表成功');';
                break;
                
            default:
                sendResponse(false, null, '不支持的请求方法', 405);';
        }
        
    } catch (Exception $e) {
        handleError('处理服务列表失败: ' . $e->getMessage());';
    }
}
