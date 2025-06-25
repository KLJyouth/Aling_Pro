<?php
/**
 * AlingAi Pro - Enhanced Chat API (V2)
 * 
 * Provides advanced AI chat capabilities with context preservation
 * and additional features like suggestions and intent detection
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
$sessionId = $data['session_id'] ?? 'new_' . time(];
$model = $data['model'] ?? 'gpt-4-turbo';
$history = $data['history'] ?? [];
$systemPrompt = $data['system_prompt'] ?? 'You are a helpful AI assistant.';

// Mock responses based on message content
$responses = [
    'hello' => '���ã�����AlingAi��ǿ�����֡���ʲô�ҿ��԰��������������ǹ�����ѧϰ�����Ǵ��⣬�Ҷ����ṩרҵ֧�֡�',
    'hi' => '���ã���ӭʹ��AlingAi��ǿ�����֡���������Ϊ����Щʲô�أ��ҿ��԰����ش����⡢��д���ݡ��������ݻ��ṩ���⽨�顣',
    '���' => '���ã�����AlingAi��ǿ�����֣��ܸ���Ϊ������������������ѯ�κ����⣬���߸���������Ҫʲô������',
    'help' => '����AlingAi��ǿ���������֣���Ȼ����棬�Ҿ߱���\n\n1. ������������������\n2. ����׼����Ϣ����\n3. ��ǿ�Ĵ������ɺͷ���\n4. ͨ�������ӻ�ȡ������Ѷ\n5. �ĵ���ͼ��������\n\n�����Գ������Ҹ������⣬���ұ�д���룬�������Ұ����������ݡ���ʲô�ҿ��԰�������',
    '����' => '����AlingAi��ǿ���������֣��߱�����������\n\n1. ��Ȼش��������\n2. ��д���Ż����ֱ�����ԵĴ���\n3. �����Ϳ��ӻ�����\n4. ���������ĵ�������\n5. �ṩ�������ƽ���\n6. Э�������о���ѧϰ\n7. �����Է�������ɫ\n\n�����������Ҫʲô�������һᾡ���ṩ��ѽ��������',
    'what can you do' => '��ΪAlingAi��ǿ�����֣������ṩ������\n\n **��Ϣ��֪ʶ**���ش����⣬�ṩ��ȷ����ͼ���\n **���ݴ���**��׫д���¡����桢����İ���������µ�\n **��̿���**����д�����Ժ��Ż����룬���ͼ�������\n **���ݴ���**���������ݣ�����ͼ����ȡ����\n **ѧϰ����**�����͸��Ӹ���ṩѧϰ��Դ�ͷ���\n **����֧��**���ṩ���飬Ȩ�����ף�����ѡ��\n **����֧��**��ͷ�Է籩���ṩ������к����뷨\n **���Է���**�����룬�﷨��飬������ɫ\n\n���ʽ�������Ҫ�ķ���İ�����',
    'who are you' => '����AlingAi��ǿ�����֣�һ���������´�������ģ�ͼ���������AIϵͳ���ҵ�Ŀ����ͨ���ṩ�м�ֵ��׼ȷ���а����Ļ�Ӧ��֧�����Ĺ������ճ������ӵ�й㷺��֪ʶ��������������������������滮����Ϣ�����ȡ����������ȣ��Ҿ��и�ǿ���������������������׼��רҵ֪ʶ�͸���Ȼ�Ľ�����ʽ��',
    '����˭' => '����AlingAi��ǿ�����֣���AlingAi��˾��������һ���˹��������֡��һ����Ƚ��Ĵ�����ģ�ͼ����������������Ż����ܹ��ṩ�������ܡ�ȫ��͸��Ի��ķ����ҿ�����⸴�����⣬�ṩ��ȷ����������������ݣ������ܹ���ס���ǶԻ��������ģ��ṩ����һ�µĽ������顣�����ǹ�����ѧϰ���������еĸ��������Ҷ���Ϊ���ṩרҵ֧�֡�',
    'code' => '��Ȼ���ҿ��԰�����д���롣������һ���򵥵�Python����ʾ�������ڼ���쳲��������У�\n\n```python\ndef fibonacci(n):\n    """����쳲��������еĵ�n����"""\n    if n <= 0:\n        return "������������"\n    elif n == 1:\n        return 0\n    elif n == 2:\n        return 1\n    else:\n        a, b = 0, 1\n        for _ in range(2, n):\n            a, b = b, a + b\n        return b\n\n# ���Ժ���\nfor i in range(1, 11):\n    print(f"��{i}��쳲���������: {fibonacci(i)}")\n```\n\n��δ���ʹ�õ�����������쳲��������У��ȵݹ鷽������Ч������Ҫ�ض����ԵĴ�������������ܵĴ�����',
    'sql' => '������һ��SQL��ѯʾ�������ڴӵ����������ݿ��в�������Ĳ�Ʒ��\n\n```sql\nSELECT\n    p.product_id,\n    p.product_name,\n    p.category,\n    p.price,\n    SUM(oi.quantity) as total_quantity_sold,\n    SUM(oi.quantity * p.price) as total_revenue\nFROM\n    products p\nJOIN\n    order_items oi ON p.product_id = oi.product_id\nJOIN\n    orders o ON oi.order_id = o.order_id\nWHERE\n    o.order_date BETWEEN \'2023-01-01\' AND \'2023-12-31\'\n    AND o.status = \'completed\'\nGROUP BY\n    p.product_id, p.product_name, p.category, p.price\nORDER BY\n    total_revenue DESC\nLIMIT 10;\n```\n\n�����ѯ�᷵��2023�����۶���ߵ�10����Ʒ��������ƷID�����ơ���𡢵��ۡ����������������롣��Ҫ�ҽ��������ѯ���κβ��֣����߰����޸����������ض�������'
];

// Default response if no keyword matches
$response = '��л�������ʡ��������ѯ�ʵ��ǹ���"' . $message . '"�����⡣\n\n����һ���ܺõ����⣬��������ϸ�ش�\n\n���ȣ���������漰��������֪ʶ���ӻ���ԭ����˵��������Ҫ����...[�˴�����ʵ�������ṩ��ϸ����]��\n\n��Σ���ʵ��Ӧ���У��м����ؼ���ֵ��ע�⣺\n1. ȷ�������ĸ����ԭ��\n2. ���ǲ�ͬ�����µ�Ӧ�÷�ʽ\n3. ��ע���µ��о���չ������\n\n������и���������������Ҫ���������ĳ���ض����棬������ң��Һ������ṩ������Ϣ��';

// Check if the message contains any of our keyword triggers
foreach ($responses as $keyword => $reply) {
    if (stripos($message, $keyword) !== false) {
        $response = $reply;
        break;
    }
}

// Generate context-based suggestions based on the message
$suggestions = [];
if (stripos($message, 'code') !== false || stripos($message, '����') !== false) {
    $suggestions = [
        '��δ�������Ż����ܣ�',
        '�ܰ��ҽ���һ������㷨��',
        '�����Ӵ�����'
    ];
} else if (stripos($message, 'data') !== false || stripos($message, '����') !== false) {
    $suggestions = [
        '��ο��ӻ���Щ���ݣ�',
        '���Ƽ����ʵ����ݷ���������',
        '��δ��������е��쳣ֵ��'
    ];
} else {
    // Default suggestions
    $suggestions = [
        '����ϸ�����������',
        '����ص������о���',
        '�����Ӧ�õ�ʵ�ʹ����У�'
    ];
}

// Detect user intent (simplified for demo)
$intent = 'inquiry'; // Default intent
if (stripos($message, 'how to') !== false || stripos($message, '���') !== false) {
    $intent = 'how_to';
} else if (stripos($message, 'what is') !== false || stripos($message, 'ʲô��') !== false) {
    $intent = 'definition';
} else if (stripos($message, 'code') !== false || stripos($message, '����') !== false) {
    $intent = 'coding';
}

// Generate a unique message ID
$messageId = 'msg_' . uniqid(];

// Add a slight delay to simulate processing
usleep(rand(500000, 1200000)]; // 500-1200ms

// Return enhanced response
echo json_encode([
    'success' => true,
    'data' => [
        'message_id' => $messageId,
        'session_id' => $sessionId,
        'reply' => $response,
        'context_preserved' => true,
        'suggestions' => $suggestions,
        'intent' => $intent,
        'model' => $model,
        'timestamp' => date('Y-m-d H:i:s'],
        'processing_info' => [
            'time' => rand(400, 900) / 1000, // 400-900ms
            'tokens' => [
                'input' => strlen($message) / 4,
                'output' => strlen($response) / 4,
                'total' => (strlen($message) + strlen($response)) / 4
            ]
        ], 
        'plugins_used' => [] // No plugins used in this demo
    ]
]];
