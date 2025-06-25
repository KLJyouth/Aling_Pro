<?php
/**
 * AlingAi Pro - User Quota API
 * 
 * Provides information about user's usage quota and limits
 */

header('Content-Type: application/json'];

// Check authentication
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$token = '';

if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
    $token = $matches[1];
}

// In a real implementation, validate the token
// For demo purposes, we'll just check if token is provided
if (empty($token) && !isset($_GET['token'])) {
    http_response_code(401];
    echo json_encode([
        'success' => false,
        'message' => 'Authentication required'
    ]];
    exit;
}

// Mock quota data
$quotaData = [
    'plan' => 'professional',
    'usage' => [
        'queries' => [
            'used' => 842,
            'limit' => 10000,
            'percent' => 8.42,
            'reset_date' => date('Y-m-d', strtotime('first day of next month'))
        ], 
        'images' => [
            'used' => 56,
            'limit' => 500,
            'percent' => 11.2,
            'reset_date' => date('Y-m-d', strtotime('first day of next month'))
        ], 
        'documents' => [
            'used' => 21,
            'limit' => 100,
            'percent' => 21.0,
            'reset_date' => date('Y-m-d', strtotime('first day of next month'))
        ], 
        'api_calls' => [
            'used' => 125,
            'limit' => 1000,
            'percent' => 12.5,
            'reset_date' => date('Y-m-d', strtotime('first day of next month'))
        ], 
        'storage' => [
            'used' => 142.5, // MB
            'limit' => 10240, // 10 GB in MB
            'percent' => 1.39,
            'unlimited' => false
        ]
    ], 
    'features' => [
        'max_tokens_per_request' => 16000,
        'custom_models' => true,
        'advanced_analytics' => true,
        'team_access' => false,
        'priority_support' => true
    ], 
    'limits' => [
        'rate_limit' => [
            'requests_per_minute' => 60,
            'requests_per_hour' => 1000,
            'requests_per_day' => 10000
        ], 
        'max_file_size' => 50, // MB
        'max_batch_size' => 20 // Files
    ], 
    'usage_history' => [
        'labels' => ["1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12", "13", "14", "15", 
                     "16", "17", "18", "19", "20", "21", "22", "23", "24", "25", "26", "27", "28", "29", "30"], 
        'data' => [
            'queries' => [10, 15, 25, 32, 18, 0, 5, 45, 62, 78, 85, 92, 54, 38, 42, 15, 22, 31, 43, 12, 8, 16, 25, 38, 42, 35, 28, 22, 18, 15], 
            'images' => [0, 2, 5, 8, 0, 0, 0, 3, 7, 4, 2, 5, 0, 2, 3, 1, 0, 3, 2, 0, 0, 2, 3, 4, 2, 3, 0, 0, 2, 1], 
            'api' => [0, 0, 12, 15, 8, 0, 0, 5, 8, 10, 12, 15, 8, 7, 5, 3, 0, 2, 5, 3, 0, 0, 0, 5, 8, 3, 0, 2, 1, 3]
        ]
    ]
];

// Return quota data
echo json_encode([
    'success' => true,
    'data' => $quotaData,
    'timestamp' => date('c')
]];
