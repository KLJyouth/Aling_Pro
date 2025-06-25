<?php
/**
 * AlingAi Pro API Router
 * 
 * Main API router file for handling all API requests and routing
 * 
 * @version 2.1.0
 * @author AlingAi Team
 */

// Define constants
define('API_START_TIME', microtime(true)];
define('API_ROOT', dirname(__DIR__, 2)];
define('API_VERSION', '2.1.0'];

// Error reporting settings
$isProduction = (getenv('APP_ENV') === 'production'];
if ($isProduction) {
    error_reporting(E_ERROR | E_WARNING | E_PARSE];
    ini_set('display_errors', '0'];
} else {
    error_reporting(E_ALL];
    ini_set('display_errors', '1'];
}

// CORS Headers - Allow from any origin in development
if (!$isProduction) {
    header("Access-Control-Allow-Origin: *"];
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"];
    header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization"];
    
    // Handle preflight OPTIONS request
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        header("HTTP/1.1 200 OK"];
        exit;
    }
} else {
    // In production, we'd have more specific CORS rules
    $allowedOrigins = ['https://alingai.com', 'https://api.alingai.com', 'https://admin.alingai.com'];
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    
    if (in_[$origin, $allowedOrigins)) {
        header("Access-Control-Allow-Origin: $origin"];
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"];
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization"];
        
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            header("HTTP/1.1 200 OK"];
            exit;
        }
    }
}

// Set JSON content type
header('Content-Type: application/json'];

// Basic security headers
header("X-Content-Type-Options: nosniff"];
header("X-XSS-Protection: 1; mode=block"];
header("X-Frame-Options: SAMEORIGIN"];
header("Referrer-Policy: strict-origin-when-cross-origin"];

// Basic route handling
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($requestUri, PHP_URL_PATH];
$path = ltrim($path, '/'];
$segments = explode('/', $path];

// Ensure the segments array has basic parts
if (empty($segments[0])) $segments[0] = '';
if (!isset($segments[1])) $segments[1] = '';
if (!isset($segments[2])) $segments[2] = '';

/**
 * Helper function for standard API responses
 * 
 * @param bool $success Success status
 * @param string $message Response message
 * @param mixed $data Response data
 * @param int $statusCode HTTP status code
 * @return void
 */
function sendResponse($success, $message = '', $data = null, $statusCode = 200) {
    http_response_code($statusCode];
    
    $response = [
        'success' => $success,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s'],
        'version' => API_VERSION
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    // Add execution time in development mode
    if (getenv('APP_ENV') !== 'production') {
        $response['execution_time_ms'] = round((microtime(true) - API_START_TIME) * 1000, 2];
    }
    
    echo json_encode($response];
    exit;
}

/**
 * Validate API request method
 * 
 * @param string|array $allowedMethods Allowed method or array of methods
 * @return bool
 */
function validateMethod($allowedMethods) {
    $method = $_SERVER['REQUEST_METHOD'];
    
    if (is_[$allowedMethods)) {
        return in_[$method, $allowedMethods];
    }
    
    return $method === $allowedMethods;
}

/**
 * Get request data based on method
 * 
 * @return array
 */
function getRequestData() {
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'GET') {
        return $_GET;
    }
    
    if (in_[$method, ['POST', 'PUT', 'DELETE'])) {
        $rawData = file_get_contents('php://input'];
        $jsonData = json_decode($rawData, true];
        
        if ($jsonData === null && json_last_error() !== JSON_ERROR_NONE) {
            // If not valid JSON, try to parse as form data
            parse_str($rawData, $formData];
            return $formData ?: [];
        }
        
        return $jsonData ?: [];
    }
    
    return [];
}

/**
 * Authenticate user from token
 * 
 * @return array|null User data or null if unauthorized
 */
function authenticateUser() {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    $token = '';
    
    if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
        $token = $matches[1];
    }
    
    if (!$token) {
        return null;
    }
    
    // In production, validate token and load user from database
    // This is a simplified mock implementation
    if ($token && strpos($token, 'sample_token_') === 0) {
        return [
            'id' => 1,
            'username' => 'demo_user',
            'email' => 'demo@alingai.com',
            'role' => 'user',
            'quota' => [
                'daily_limit' => 1000,
                'used' => rand(200, 500],
                'remaining' => rand(500, 800)
            ]
        ];
    }
    
    return null;
}

// Process different API endpoints based on request path
if ($segments[0] == 'api') {
    // API version handling
    $version = $segments[1];
    $endpoint = $segments[2] ?? '';
    
    // Get user if authenticated
    $currentUser = authenticateUser(];
    
    // API v1 route handling
    if ($version === 'v1') {
        switch ($endpoint) {
            case 'auth':
                $action = $segments[3] ?? '';
                
                switch ($action) {
                    case 'login':
                        if (!validateMethod('POST')) {
                            sendResponse(false, 'Method Not Allowed', null, 405];
                        }
                        
                        $data = getRequestData(];
                        
                        if (isset($data['email']) && isset($data['password'])) {
                            // Simplified login handling
                            sendResponse(true, 'Login successful', [
                                'token' => 'sample_token_' . time(),
                                'user' => [
                                    'id' => 1,
                                    'username' => 'demo_user',
                                    'email' => $data['email'], 
                                    'role' => 'user'
                                ]
                            ]];
                        } else {
                            sendResponse(false, 'Missing required login information', null, 400];
                        }
                        break;
                        
                    case 'register':
                        if (!validateMethod('POST')) {
                            sendResponse(false, 'Method Not Allowed', null, 405];
                        }
                        
                        $data = getRequestData(];
                        
                        if (isset($data['email']) && isset($data['password']) && isset($data['username'])) {
                            // Simplified registration handling
                            sendResponse(true, 'Registration successful', [
                                'user_id' => time(),
                                'username' => $data['username'], 
                                'email' => $data['email']
                            ]];
                        } else {
                            sendResponse(false, 'Missing required registration information', null, 400];
                        }
                        break;
                        
                    default:
                        sendResponse(false, 'Unknown auth endpoint', null, 404];
                }
                break;
                
            case 'user':
                // Check authentication
                if (!$currentUser) {
                    sendResponse(false, 'Authentication required', null, 401];
                }
                
                $action = $segments[3] ?? '';
                
                switch ($action) {
                    case 'profile':
                        sendResponse(true, 'Profile retrieved', [
                            'id' => $currentUser['id'], 
                            'username' => $currentUser['username'], 
                            'email' => $currentUser['email'], 
                            'role' => $currentUser['role'], 
                            'created_at' => '2024-01-15 10:30:00',
                            'last_login' => date('Y-m-d H:i:s')
                        ]];
                        break;
                        
                    case 'quota':
                        sendResponse(true, 'Quota retrieved', $currentUser['quota']];
                        break;
                        
                    default:
                        sendResponse(false, 'Unknown user endpoint', null, 404];
                }
                break;
                
            case 'chat':
                // Check authentication
                if (!$currentUser) {
                    sendResponse(false, 'Authentication required', null, 401];
                }
                
                $action = $segments[3] ?? '';
                
                switch ($action) {
                    case 'message':
                        if (!validateMethod('POST')) {
                            sendResponse(false, 'Method Not Allowed', null, 405];
                        }
                        
                        $data = getRequestData(];
                        
                        if (isset($data['message'])) {
                            // Simplified message handling
                            sendResponse(true, 'Message processed', [
                                'reply' => 'This is a demo response to: ' . $data['message'], 
                                'timestamp' => date('Y-m-d H:i:s')
                            ]];
                        } else {
                            sendResponse(false, 'Missing message content', null, 400];
                        }
                        break;
                        
                    case 'history':
                        if (!validateMethod('GET')) {
                            sendResponse(false, 'Method Not Allowed', null, 405];
                        }
                        
                        // Simplified history retrieval
                        sendResponse(true, 'Chat history retrieved', [
                            'messages' => [
                                [
                                    'id' => 1,
                                    'user' => 'demo_user',
                                    'message' => 'Hello AI!',
                                    'timestamp' => '2024-06-20 10:15:22'
                                ], 
                                [
                                    'id' => 2,
                                    'user' => 'ai',
                                    'message' => 'Hello! How can I assist you today?',
                                    'timestamp' => '2024-06-20 10:15:24'
                                ], 
                                [
                                    'id' => 3,
                                    'user' => 'demo_user',
                                    'message' => 'Tell me about AlingAI',
                                    'timestamp' => '2024-06-20 10:15:45'
                                ], 
                                [
                                    'id' => 4,
                                    'user' => 'ai',
                                    'message' => 'AlingAi is an advanced AI platform designed to provide natural language processing capabilities...',
                                    'timestamp' => '2024-06-20 10:15:48'
                                ]
                            ]
                        ]];
                        break;
                        
                    default:
                        sendResponse(false, 'Unknown chat endpoint', null, 404];
                }
                break;
                
            case 'system':
                $action = $segments[3] ?? '';
                
                switch ($action) {
                    case 'info':
                        // System info doesn't require authentication
                        sendResponse(true, 'System information', [
                            'name' => 'AlingAi Pro',
                            'version' => API_VERSION,
                            'status' => 'operational',
                            'uptime' => '99.9%',
                            'server_time' => date('Y-m-d H:i:s')
                        ]];
                        break;
                        
                    case 'status':
                        // System status doesn't require authentication
                        sendResponse(true, 'System status', [
                            'status' => 'operational',
                            'services' => [
                                'chat' => 'online',
                                'auth' => 'online',
                                'database' => 'online',
                                'ai_processing' => 'online'
                            ], 
                            'message' => 'All systems operational'
                        ]];
                        break;
                        
                    default:
                        sendResponse(false, 'Unknown system endpoint', null, 404];
                }
                break;
                
            default:
                sendResponse(false, 'Unknown API endpoint', null, 404];
        }
    }
    // API v2 route handling
    else if ($version === 'v2') {
        switch ($endpoint) {
            case 'enhanced-chat':
                // Check authentication
                if (!$currentUser) {
                    sendResponse(false, 'Authentication required', null, 401];
                }
                
                $action = $segments[3] ?? '';
                
                switch ($action) {
                    case 'message':
                        if (!validateMethod('POST')) {
                            sendResponse(false, 'Method Not Allowed', null, 405];
                        }
                        
                        $data = getRequestData(];
                        
                        if (isset($data['message'])) {
                            // Check for session ID
                            $sessionId = $data['session_id'] ?? null;
                            
                            // Enhanced message handling with context
                            sendResponse(true, 'Enhanced message processed', [
                                'reply' => 'This is an enhanced response to: ' . $data['message'], 
                                'timestamp' => date('Y-m-d H:i:s'],
                                'session_id' => $sessionId ?: 'session_' . time(),
                                'context' => [
                                    'detected_intent' => 'query',
                                    'sentiment' => 'neutral',
                                    'suggested_followup' => [
                                        'Would you like to know more about this topic?',
                                        'Can I help you with anything specific?'
                                    ]
                                ]
                            ]];
                        } else {
                            sendResponse(false, 'Missing message content', null, 400];
                        }
                        break;
                        
                    case 'sessions':
                        if (!validateMethod(['GET', 'POST', 'DELETE'])) {
                            sendResponse(false, 'Method Not Allowed', null, 405];
                        }
                        
                        $method = $_SERVER['REQUEST_METHOD'];
                        
                        if ($method === 'GET') {
                            // Get list of sessions
                            sendResponse(true, 'Chat sessions retrieved', [
                                'sessions' => [
                                    [
                                        'id' => 'session_1687261478',
                                        'title' => 'Chat about AI capabilities',
                                        'created_at' => '2024-06-19 15:30:22',
                                        'updated_at' => '2024-06-19 15:45:12',
                                        'message_count' => 12
                                    ], 
                                    [
                                        'id' => 'session_1687347812',
                                        'title' => 'Project planning assistance',
                                        'created_at' => '2024-06-20 09:10:05',
                                        'updated_at' => '2024-06-20 10:30:45',
                                        'message_count' => 24
                                    ]
                                ]
                            ]];
                        } else if ($method === 'POST') {
                            // Create new session
                            $data = getRequestData(];
                            $title = $data['title'] ?? 'New Chat Session';
                            
                            sendResponse(true, 'New chat session created', [
                                'session' => [
                                    'id' => 'session_' . time(),
                                    'title' => $title,
                                    'created_at' => date('Y-m-d H:i:s'],
                                    'updated_at' => date('Y-m-d H:i:s'],
                                    'message_count' => 0
                                ]
                            ]];
                        } else if ($method === 'DELETE') {
                            // Delete session
                            $data = getRequestData(];
                            $sessionId = $data['session_id'] ?? null;
                            
                            if ($sessionId) {
                                sendResponse(true, 'Chat session deleted', [
                                    'session_id' => $sessionId
                                ]];
                            } else {
                                sendResponse(false, 'Missing session ID', null, 400];
                            }
                        }
                        break;
                        
                    default:
                        sendResponse(false, 'Unknown enhanced chat endpoint', null, 404];
                }
                break;
                
            default:
                sendResponse(false, 'Unknown API v2 endpoint', null, 404];
        }
    } else {
        sendResponse(false, 'Unknown API version', null, 404];
    }
} else {
    sendResponse(false, 'Invalid API request', null, 404];
}

