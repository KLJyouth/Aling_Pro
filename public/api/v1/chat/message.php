<?php
/**
 * AlingAi Pro - Chat Message API
 * 
 * Processes chat messages and returns AI responses
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

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405]; // Method Not Allowed
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Use POST.'
    ]];
    exit;
}

// Get JSON data
$json = file_get_contents('php://input'];
$data = json_decode($json, true];

// Validate input
if (!isset($data['message'])) {
    http_response_code(400];
    echo json_encode([
        'success' => false,
        'message' => 'Missing message content'
    ]];
    exit;
}

// Extract data
$message = trim($data['message']];
$sessionId = $data['session_id'] ?? 'default';
$model = $data['model'] ?? 'gpt-4';

// Mock responses based on message content
$responses = [
    'hello' => '��ã�����AlingAi���֡���ʲô�ҿ��԰��������',
    'hi' => '��ã����ʽ�����ʲô��Ҫ�Ұ�æ����',
    '���' => '��ã���ʲô���ܰ���������',
    'help' => '����AlingAi�������֣����Իش����⡢�ṩ��Ϣ��������д���롢�������ݵȡ���ֱ�Ӹ���������Ҫʲô������',
    '����' => '����AlingAi�������֣��ܹ��ش����⡢�����ı�����д���롢�������ݵȡ���ֱ�Ӹ�������������',
    'what can you do' => '�ҿ��ԣ�\n- �ش����֪ʶ����\n- ������д�͵��Դ���\n- �������ݺ��ṩ����\n- �����ı�����\n- �����������\n- �᳤ܽ�ı�\n��ֱ�Ӹ���������Ҫʲô������',
    'who are you' => '����AlingAi���֣�һ�������Ƚ���������ģ�͵��˹����ܡ��ұ�������������ش����⡢�ṩ��Ϣ��Э����������',
    '����˭' => '����AlingAi���֣�һ����AlingAi��˾�������˹��������֡���ʹ���Ƚ��Ĵ�������ģ�ͣ����԰������ش����⡢���������ṩ������Ϣ��'
];

// Check if the message contains any of our keyword triggers
$response = '��������������ǹ���"' . $message . '"���������ش�\n\n�����������յ���Ϣ������һ�����ӵĻ��⣬�漰������档���ȣ�������Ҫ���ǻ���ԭ��ͱ���֪ʶ��Ȼ��������ϸ�ڡ�\n\n���������������������о��ͷ�չ����Ȥ�����Ƽ����ο���ƪ������׺���Դ����ȡ��ȫ�����⡣������и���������⣬������ң��һ��ṩ��������ԵĻش�';

foreach ($responses as $keyword => $reply) {
    if (stripos($message, $keyword) !== false) {
        $response = $reply;
        break;
    }
}

// Add some delay to simulate processing
usleep(rand(300000, 800000)]; // 300-800ms

// Mock response time calculation
$processingTime = rand(200, 700) / 1000; // 200-700ms

// Generate message ID
$messageId = 'msg_' . time() . '_' . rand(1000, 9999];

// Return chat response
echo json_encode([
    'success' => true,
    'data' => [
        'message_id' => $messageId,
        'session_id' => $sessionId,
        'reply' => $response,
        'model' => $model,
        'timestamp' => date('Y-m-d H:i:s'],
        'processing_time' => $processingTime,
        'tokens' => [
            'prompt' => strlen($message) / 4, // Rough approximation
            'completion' => strlen($response) / 4, // Rough approximation
            'total' => (strlen($message) + strlen($response)) / 4 // Rough approximation
        ]
    ]
]];
