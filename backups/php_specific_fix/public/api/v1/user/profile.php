<?php
/**
 * AlingAi Pro - User Profile API
 * 
 * Provides user profile data for authenticated users
 */

header("Content-Type: application/json"];

// Check authentication
$authHeader = $_SERVER["HTTP_AUTHORIZATION"] ?? ";
$token = ";

if (preg_match("/Bearer\s+(.*]$/i", $authHeader, $matches]] {
    $token = $matches[1];
}

// In a real implementation, validate the token
// For demo purposes, we'll just check if token is provided
if (empty($token] && !isset($_GET["token"]]] {
    http_response_code(401];
    echo json_encode([
        "success" => false,
        "message" => "Authentication required"
    ]];
    exit;
}

// Mock user profile data
$userProfile = [
    "id" => 1001,
    "username" => "demo_user",
    "email" => "demo@alingai.com",
    "first_name" => "测试",
    "last_name" => "用户",
    "profile_picture" => "/assets/images/avatars/user1.png",
    "phone" => "+86 138 **** 5678",
    "country" => "中国",
    "city" => "北京",
    "company" => "AlingAi科技有限公司",
    "job_title" => "数据科学家",
    "timezone" => "Asia/Shanghai",
    "language" => "zh-CN",
    "created_at" => "2024-06-01 15:30:45",
    "last_login" => date("Y-m-d H:i:s", time(] - 3600 * 24],
    "two_factor_enabled" => true,
    "account_status" => "active",
    "subscription" => [
        "plan" => "professional",
        "status" => "active",
        "next_billing" => date("Y-m-d", strtotime("+15 days"]],
        "features" => [
            "max_queries" => 10000,
            "custom_models" => true,
            "advanced_analytics" => true,
            "team_access" => false,
            "priority_support" => true
        ],
        "billing_history" => [
            [
                "id" => "inv_001",
                "date" => date("Y-m-d", strtotime("-15 days"]],
                "amount" => 299.00,
                "status" => "paid",
                "method" => "尾号4321的信用卡"
            ],
            [
                "id" => "inv_002",
                "date" => date("Y-m-d", strtotime("-45 days"]],
                "amount" => 299.00,
                "status" => "paid",
                "method" => "尾号4321的信用卡"
            ]
        ]
    ],
    "usage" => [
        "current_month" => [
            "queries" => 842,
            "images_generated" => 56,
            "documents_processed" => 21,
            "api_calls" => 125
        ],
        "limit" => [
            "queries" => 10000,
            "images" => 500,
            "documents" => 100,
            "api_calls" => 1000
        ]
    ],
    "preferences" => [
        "default_model" => "gpt-4",
        "theme" => "dark",
        "notifications" => [
            "email_alerts" => true,
            "security_alerts" => true,
            "product_updates" => false,
            "marketing" => false
        ],
        "auto_logout" => 30, // minutes
        "advanced_features" => true
    ]
];

// Return user profile
echo json_encode([
    "success" => true,
    "data" => $userProfile
]];

