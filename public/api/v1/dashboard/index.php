<?php
/**
 * AlingAi Pro - Dashboard API
 * 
 * Provides dashboard data for the user dashboard interface
 */

header('Content-Type: application/json');

// Initialize session if needed
session_start();

// Check if user is authenticated (simplified)
$isAuthenticated = isset($_SERVER['HTTP_AUTHORIZATION']) || isset($_GET['token']);

// If not authenticated, return error
if (!$isAuthenticated) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized',
        'code' => 'AUTH_REQUIRED'
    ]);
    exit;
}

// Mock current user data
$userData = [
    'id' => 1001,
    'username' => 'demo_user',
    'email' => 'demo@alingai.com',
    'role' => 'user',
    'avatar' => '/assets/images/avatars/user1.png',
    'plan' => 'professional',
    'registered_date' => '2024-06-01 15:30:45',
    'last_login' => date('Y-m-d H:i:s', time() - 3600 * 24),
    'api_usage' => [
        'used' => 842,
        'limit' => 10000,
        'percent' => 8.42
    ],
    'security_score' => 85,
    'subscription' => [
        'status' => 'active',
        'next_billing' => date('Y-m-d', strtotime('+15 days')),
        'plan_details' => 'Professional Plan - 月度订阅'
    ]
];

// Dashboard data
$dashboardData = [
    'user' => $userData,
    'stats' => [
        'total_queries' => 842,
        'total_conversations' => 56,
        'saved_prompts' => 12,
        'ai_agents' => 3
    ],
    'recent_activities' => [
        [
            'id' => 'act_1001',
            'type' => 'login',
            'description' => '成功登录系统',
            'time' => date('Y-m-d H:i:s', time() - 3600 * 1),
            'ip' => '192.168.1.104',
            'location' => '北京, 中国'
        ],
        [
            'id' => 'act_1002',
            'type' => 'api_call',
            'description' => 'AI模型调用 - GPT-4',
            'time' => date('Y-m-d H:i:s', time() - 3600 * 2),
            'tokens' => 1240
        ],
        [
            'id' => 'act_1003',
            'type' => 'document',
            'description' => '文档上传: 项目分析.pdf',
            'time' => date('Y-m-d H:i:s', time() - 3600 * 5),
            'file_size' => '2.4 MB'
        ],
        [
            'id' => 'act_1004',
            'type' => 'settings',
            'description' => '更新账户设置',
            'time' => date('Y-m-d H:i:s', time() - 3600 * 25)
        ],
        [
            'id' => 'act_1005',
            'type' => 'security',
            'description' => '密码重置',
            'time' => date('Y-m-d H:i:s', time() - 3600 * 72),
            'ip' => '192.168.1.104',
            'location' => '北京, 中国'
        ]
    ],
    'usage_trends' => [
        'labels' => ["一月", "二月", "三月", "四月", "五月", "六月"],
        'data' => [
            'text_queries' => [310, 420, 380, 590, 740, 842],
            'image_generation' => [120, 150, 210, 240, 280, 310],
            'document_analysis' => [50, 90, 120, 150, 190, 220]
        ]
    ],
    'notifications' => [
        [
            'id' => 'notif_001',
            'type' => 'system',
            'title' => '系统更新通知',
            'message' => 'AlingAi Pro 将于6月30日进行系统升级，预计停机1小时',
            'read' => false,
            'time' => date('Y-m-d H:i:s', time() - 3600 * 12)
        ],
        [
            'id' => 'notif_002',
            'type' => 'security',
            'title' => '安全警报',
            'message' => '检测到新设备登录，位置: 上海',
            'read' => true,
            'time' => date('Y-m-d H:i:s', time() - 3600 * 48)
        ],
        [
            'id' => 'notif_003',
            'type' => 'account',
            'title' => '账户提醒',
            'message' => '您的订阅将在15天后到期，请及时续费',
            'read' => false,
            'time' => date('Y-m-d H:i:s', time() - 3600 * 24)
        ],
        [
            'id' => 'notif_004',
            'type' => 'feature',
            'title' => '新功能上线',
            'message' => '量子加密功能已上线，立即体验更强大的安全保障',
            'read' => false,
            'time' => date('Y-m-d H:i:s', time() - 3600 * 72)
        ]
    ]
];

// Return dashboard data
echo json_encode([
    'success' => true,
    'data' => $dashboardData,
    'timestamp' => date('c')
]);
