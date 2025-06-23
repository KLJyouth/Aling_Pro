<?php
/**
 * AlingAi Pro - Chat Message API
 * 
 * Processes chat messages and returns AI responses
 */

header('Content-Type: application/json');

// Check authentication
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$token = '';

if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
    $token = $matches[1];
}

// In a real implementation, validate the token
// For demo purposes, we'll just check if token is provided
if (empty($token) && !isset($_GET['token'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Authentication required'
    ]);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Use POST.'
    ]);
    exit;
}

// Get JSON data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validate input
if (!isset($data['message'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Missing message content'
    ]);
    exit;
}

// Extract data
$message = trim($data['message']);
$sessionId = $data['session_id'] ?? 'default';
$model = $data['model'] ?? 'gpt-4';

// Mock responses based on message content
$responses = [
    'hello' => '你好！我是AlingAi助手。有什么我可以帮助你的吗？',
    'hi' => '你好！请问今天有什么需要我帮忙的吗？',
    '你好' => '你好！有什么我能帮助您的吗？',
    'help' => '我是AlingAi智能助手，可以回答问题、提供信息、帮助编写代码、分析数据等。请直接告诉我您需要什么帮助。',
    '帮助' => '我是AlingAi智能助手，能够回答问题、翻译文本、编写代码、分析数据等。请直接告诉我您的需求。',
    'what can you do' => '我可以：\n- 回答各种知识问题\n- 帮助编写和调试代码\n- 分析数据和提供见解\n- 创建文本内容\n- 翻译多种语言\n- 总结长文本\n请直接告诉我您需要什么帮助。',
    'who are you' => '我是AlingAi助手，一个基于先进大型语言模型的人工智能。我被设计用来帮助回答问题、提供信息和协助各种任务。',
    '你是谁' => '我是AlingAi助手，一个由AlingAi公司开发的人工智能助手。我使用先进的大型语言模型，可以帮助您回答问题、完成任务和提供各种信息。'
];

// Check if the message contains any of our keyword triggers
$response = '我理解您的问题是关于"' . $message . '"。让我来回答：\n\n根据我所掌握的信息，这是一个复杂的话题，涉及多个方面。首先，我们需要考虑基本原理和背景知识，然后再深入细节。\n\n您或许会对这个领域的最新研究和发展感兴趣。我推荐您参考几篇相关文献和资源来获取更全面的理解。如果您有更具体的问题，请告诉我，我会提供更有针对性的回答。';

foreach ($responses as $keyword => $reply) {
    if (stripos($message, $keyword) !== false) {
        $response = $reply;
        break;
    }
}

// Add some delay to simulate processing
usleep(rand(300000, 800000)); // 300-800ms

// Mock response time calculation
$processingTime = rand(200, 700) / 1000; // 200-700ms

// Generate message ID
$messageId = 'msg_' . time() . '_' . rand(1000, 9999);

// Return chat response
echo json_encode([
    'success' => true,
    'data' => [
        'message_id' => $messageId,
        'session_id' => $sessionId,
        'reply' => $response,
        'model' => $model,
        'timestamp' => date('Y-m-d H:i:s'),
        'processing_time' => $processingTime,
        'tokens' => [
            'prompt' => strlen($message) / 4, // Rough approximation
            'completion' => strlen($response) / 4, // Rough approximation
            'total' => (strlen($message) + strlen($response)) / 4 // Rough approximation
        ]
    ]
]);
