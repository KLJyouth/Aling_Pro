<?php
/**
 * AlingAi Pro 5.0 - 用户管理API
 * 完整的用户CRUD操作API端点
 */

declare(strict_types=1];

header('Content-Type: application/json;charset=utf-8'];
header('Access-Control-Allow-Origin: *'];
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS'];
header('Access-Control-Allow-Headers: Content-Type, Authorization'];

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS'] {
    http_response_code(200];
    exit(];
}

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../../../../src/Auth/AdminAuthServiceDemo.php';

use AlingAi\Auth\AdminAuthServiceDemo;

// 响应函数
function sendResponse($success, $data = null, $message = '', $code = 200]
{
    http_response_code($code];
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s']
    ], JSON_UNESCAPED_UNICODE];
    exit(];
}

// 错误处理
function handleError($message, $code = 500] {
    error_log("API Error: $message"];
    sendResponse(false, null, $message, $code];
}

// 获取请求方法和路�?
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH];
$pathSegments = explode('/', trim($path, '/']];

try {
    // 验证管理员权�?
    $authService = new AdminAuthServiceDemo(];
    $headers = getallheaders(];
    $token = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    
    if (strpos($token, 'Bearer '] === 0] {
        $token = substr($token, 7];
    }
    
    if (!$token] {
        sendResponse(false, null, '缺少授权令牌', 401];
    }
    
    $user = $authService->validateToken($token];
    if (!$user || !$authService->hasPermission($user['id'], 'users.manage']] {
        sendResponse(false, null, '权限不足', 403];
    }
    
    // 解析路由参数
    $userId = null;
    if (count($pathSegments] >= 4 && is_numeric($pathSegments[3]]] {
        $userId = (int]$pathSegments[3];
    }
    
    // 路由处理
    switch ($method] {
        case 'GET':
            if ($userId] {
                handleGetUser($userId];
            } else {
                handleGetUsers(];
            }
            break;
            
        case 'POST':
            handleCreateUser(];
            break;
            
        case 'PUT':
            if ($userId] {
                handleUpdateUser($userId];
            } else {
                sendResponse(false, null, '用户ID不能为空', 400];
            }
            break;
            
        case 'DELETE':
            if ($userId] {
                handleDeleteUser($userId];
            } else {
                sendResponse(false, null, '用户ID不能为空', 400];
            }
            break;
            
        default:
            sendResponse(false, null, '不支持的请求方法', 405];
    }
    
} catch (Exception $e] {
    handleError($e->getMessage(]];
}

/**
 * 获取用户列表
 */
function handleGetUsers(] {
    try {
        $page = (int]($_GET['page'] ?? 1];
        $limit = min((int]($_GET['limit'] ?? 20], 100];// 最�?00�?
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $role = $_GET['role'] ?? '';
        $sortBy = $_GET['sort_by'] ?? 'created_at';
        $sortOrder = $_GET['sort_order'] ?? 'desc';
        
        // 模拟数据�?- 在实际应用中应该连接数据�?
        $usersData = getUsersFromStorage(];
        $totalUsers = count($usersData];
        
        // 应用筛�?
        $filteredUsers = array_filter($usersData, function($user] use ($search, $status, $role] {
            $matchesSearch = empty($search] || 
                stripos($user['username'], $search] !== false ||
                stripos($user['email'], $search] !== false;
                
            $matchesStatus = empty($status] || $user['status'] === $status;
            $matchesRole = empty($role] || $user['role_id'] == $role;
            
            return $matchesSearch && $matchesStatus && $matchesRole;
        }];
        
        // 排序
        usort($filteredUsers, function($a, $b] use ($sortBy, $sortOrder] {
            $result = strcmp($a[$sortBy] ?? '', $b[$sortBy] ?? ''];
            return $sortOrder === 'desc' ? -$result : $result;
        }];
        
        // 分页
        $offset = ($page - 1] * $limit;
        $pagedUsers = array_slice($filteredUsers, $offset, $limit];
        
        // 移除敏感信息
        $safeUsers = array_map(function($user] {
            unset($user['password_hash']];
            return $user;
        }, $pagedUsers];
        
        $response = [
            'users' => $safeUsers,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => count($filteredUsers],
                'total_pages' => ceil(count($filteredUsers] / $limit],
                'has_next' => $page * $limit <count($filteredUsers],
                'has_prev' => $page > 1
            ],
            'filters' => [
                'search' => $search,
                'status' => $status,
                'role' => $role
            ],
            'statistics' => [
                'total_users' => $totalUsers,
                'active_users' => count(array_filter($usersData, fn($u] => $u['status'] === 'active']],
                'blocked_users' => count(array_filter($usersData, fn($u] => $u['status'] === 'blocked']],
                'admin_users' => count(array_filter($usersData, fn($u] => $u['role_id'] <= 2]]
            ]
        ];
        
        sendResponse(true, $response, '获取用户列表成功'];
        
    } catch (Exception $e] {
        handleError('获取用户列表失败: ' . $e->getMessage(]];
    }
}

/**
 * 获取单个用户详情
 */
function handleGetUser($userId] {
    try {
        $users = getUsersFromStorage(];
        $user = array_filter($users, fn($u] => $u['id'] == $userId];
        
        if (empty($user]] {
            sendResponse(false, null, '用户不存�?, 404];
        }
        
        $user = array_values($user][0];
        unset($user['password_hash']];// 移除敏感信息
        
        // 添加额外的用户信�?
        $user['chat_history_count'] = rand(0, 100];
        $user['token_usage'] = rand(1000, 50000];
        $user['last_activity'] = date('Y-m-d H:i:s', strtotime('-' . rand(1, 72] . ' hours']];
        $user['device_count'] = rand(1, 5];
        
        sendResponse(true, $user, '获取用户详情成功'];
        
    } catch (Exception $e] {
        handleError('获取用户详情失败: ' . $e->getMessage(]];
    }
}

/**
 * 创建新用�?
 */
function handleCreateUser(] {
    try {
        $input = json_decode(file_get_contents('php://input'], true];
        
        // 验证必填字段
        $required = ['username', 'email', 'password', 'role_id'];
        foreach ($required as $field] {
            if (empty($input[$field]]] {
                sendResponse(false, null, "字段 {$field} 不能为空", 400];
            }
        }
        
        // 验证邮箱格式
        if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL]] {
            sendResponse(false, null, '邮箱格式不正�?, 400];
        }
        
        // 检查用户名和邮箱是否已存在
        $users = getUsersFromStorage(];
        foreach ($users as $user] {
            if ($user['username'] === $input['username']] {
                sendResponse(false, null, '用户名已存在', 400];
            }
            if ($user['email'] === $input['email']] {
                sendResponse(false, null, '邮箱已存�?, 400];
            }
        }
        
        // 创建新用�?
        $newUser = [
            'id' => count($users] + 1,
            'username' => $input['username'],
            'email' => $input['email'],
            'password_hash' => password_hash($input['password'], PASSWORD_DEFAULT],
            'role_id' => (int]$input['role_id'],
            'status' => $input['status'] ?? 'active',
            'balance' => (float]($input['balance'] ?? 0],
            'total_tokens' => (int]($input['total_tokens'] ?? 0],
            'phone' => $input['phone'] ?? null,
            'notes' => $input['notes'] ?? null,
            'created_at' => date('Y-m-d H:i:s'],
            'updated_at' => date('Y-m-d H:i:s']
        ];
        
        // 保存用户
        $users[] = $newUser;
        saveUsersToStorage($users];
        
        // 返回创建的用户（不包含密码）
        unset($newUser['password_hash']];
        
        sendResponse(true, $newUser, '用户创建成功', 201];
        
    } catch (Exception $e] {
        handleError('创建用户失败: ' . $e->getMessage(]];
    }
}

/**
 * 更新用户信息
 */
function handleUpdateUser($userId] {
    try {
        $input = json_decode(file_get_contents('php://input'], true];
        
        $users = getUsersFromStorage(];
        $userIndex = array_search($userId, array_column($users, 'id']];
        
        if ($userIndex === false] {
            sendResponse(false, null, '用户不存�?, 404];
        }
        
        // 检查邮箱唯一性（如果要更新邮箱）
        if (isset($input['email']] && $input['email'] !== $users[$userIndex]['email']] {
            foreach ($users as $user] {
                if ($user['email'] === $input['email'] && $user['id'] != $userId] {
                    sendResponse(false, null, '邮箱已被使用', 400];
                }
            }
        }
        
        // 可更新的字段
        $updatableFields = [
            'username', 'email', 'role_id', 'status', 'balance', 
            'total_tokens', 'phone', 'notes'
        ];
        
        // 更新字段
        foreach ($updatableFields as $field] {
            if (isset($input[$field]]] {
                $users[$userIndex][$field] = $input[$field];
            }
        }
        
        // 更新密码（如果提供）
        if (!empty($input['password']]] {
            $users[$userIndex]['password_hash'] = password_hash($input['password'], PASSWORD_DEFAULT];
        }
        
        $users[$userIndex]['updated_at'] = date('Y-m-d H:i:s'];
        
        // 保存更新
        saveUsersToStorage($users];
        
        // 返回更新后的用户信息（不包含密码�?
        $updatedUser = $users[$userIndex];
        unset($updatedUser['password_hash']];
        
        sendResponse(true, $updatedUser, '用户更新成功'];
        
    } catch (Exception $e] {
        handleError('更新用户失败: ' . $e->getMessage(]];
    }
}

/**
 * 删除用户
 */
function handleDeleteUser($userId] {
    try {
        $users = getUsersFromStorage(];
        $userIndex = array_search($userId, array_column($users, 'id']];
        
        if ($userIndex === false] {
            sendResponse(false, null, '用户不存�?, 404];
        }
        
        // 检查是否是最后一个管理员
        $user = $users[$userIndex];
        if ($user['role_id'] <= 2] { // 管理员角�?
            $adminCount = count(array_filter($users, fn($u] => $u['role_id'] <= 2]];
            if ($adminCount <= 1] {
                sendResponse(false, null, '不能删除最后一个管理员用户', 400];
            }
        }
        
        // 删除用户
        array_splice($users, $userIndex, 1];
        saveUsersToStorage($users];
        
        sendResponse(true, null, '用户删除成功'];
        
    } catch (Exception $e] {
        handleError('删除用户失败: ' . $e->getMessage(]];
    }
}

/**
 * 从存储中获取用户数据
 */
function getUsersFromStorage(]: array
{
    $dataDir = __DIR__ . '/../../../../data';
    $usersFile = $dataDir . '/admin_users.json';
    
    if (!is_dir($dataDir]] {
        mkdir($dataDir, 0755, true];
    }
    
    if (!file_exists($usersFile]] {
        // 初始化默认用户数�?
        $defaultUsers = [
            [
                'id' => 1,
                'username' => 'admin',
                'email' => 'admin@alingai.com',
                'password_hash' => password_hash('admin123', PASSWORD_DEFAULT],
                'role_id' => 1,
                'status' => 'active',
                'balance' => 10000.00,
                'total_tokens' => 1000000,
                'phone' => null,
                'notes' => '系统默认管理�?,
                'created_at' => date('Y-m-d H:i:s'],
                'updated_at' => date('Y-m-d H:i:s']
            ],
            [
                'id' => 2,
                'username' => 'user1',
                'email' => 'user1@example.com',
                'password_hash' => password_hash('user123', PASSWORD_DEFAULT],
                'role_id' => 4,
                'status' => 'active',
                'balance' => 100.00,
                'total_tokens' => 5000,
                'phone' => '13800138001',
                'notes' => '测试用户1',
                'created_at' => date('Y-m-d H:i:s', strtotime('-7 days']],
                'updated_at' => date('Y-m-d H:i:s', strtotime('-1 day']]
            ],
            [
                'id' => 3,
                'username' => 'user2',
                'email' => 'user2@example.com',
                'password_hash' => password_hash('user123', PASSWORD_DEFAULT],
                'role_id' => 4,
                'status' => 'blocked',
                'balance' => 0.00,
                'total_tokens' => 2000,
                'phone' => '13800138002',
                'notes' => '测试用户2 - 已封�?,
                'created_at' => date('Y-m-d H:i:s', strtotime('-14 days']],
                'updated_at' => date('Y-m-d H:i:s', strtotime('-3 days']]
            ]
        ];
        
        file_put_contents($usersFile, json_encode($defaultUsers, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT]];
        return $defaultUsers;
    }
    
    $data = file_get_contents($usersFile];
    return json_decode($data, true] ?? [];
}

/**
 * 保存用户数据到存�?
 */
function saveUsersToStorage(array $users]: void
{
    $dataDir = __DIR__ . '/../../../../data';
    $usersFile = $dataDir . '/admin_users.json';
    
    if (!is_dir($dataDir]] {
        mkdir($dataDir, 0755, true];
    }
    
    file_put_contents($usersFile, json_encode($users, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT]];
}

