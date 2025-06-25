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
    'hello' => '您好！我是AlingAi增强版助手。有什么我可以帮助您的吗？无论是工作、学习，还是创意，我都能提供专业支持。',
    'hi' => '您好！欢迎使用AlingAi增强版助手。今天我能为您做些什么呢？我可以帮助回答问题、编写内容、分析数据或提供创意建议。',
    '你好' => '您好！我是AlingAi增强版助手，很高兴为您服务。您可以向我咨询任何问题，或者告诉我您需要什么帮助。',
    'help' => '我是AlingAi增强版智能助手，相比基础版，我具备：\n\n1. 更深入的内容理解能力\n2. 更精准的信息检索\n3. 更强的代码生成和分析\n4. 通过云连接获取最新资讯\n5. 文档和图像处理能力\n\n您可以尝试问我复杂问题，让我编写代码，或者请我帮您分析数据。有什么我可以帮您的吗？',
    '帮助' => '我是AlingAi增强版智能助手，具备以下能力：\n\n1. 深度回答各种问题\n2. 编写和优化各种编程语言的代码\n3. 分析和可视化数据\n4. 创建各类文档和内容\n5. 提供创意和设计建议\n6. 协助进行研究和学习\n7. 多语言翻译与润色\n\n请告诉我您需要什么帮助，我会尽力提供最佳解决方案。',
    'what can you do' => '作为AlingAi增强版助手，我能提供许多服务：\n\n **信息与知识**：回答问题，提供深度分析和见解\n **内容创作**：撰写文章、报告、广告文案、创意故事等\n **编程开发**：编写、调试和优化代码，解释技术概念\n **数据处理**：分析数据，创建图表，提取见解\n **学习辅助**：解释复杂概念，提供学习资源和方法\n **决策支持**：提供建议，权衡利弊，分析选项\n **创意支持**：头脑风暴，提供创意灵感和新想法\n **语言服务**：翻译，语法检查，内容润色\n\n请问今天您需要哪方面的帮助？',
    'who are you' => '我是AlingAi增强版助手，一个基于最新大型语言模型技术开发的AI系统。我的目标是通过提供有价值、准确和有帮助的回应来支持您的工作和日常生活。我拥有广泛的知识库和能力，包括分析、创作、规划和信息检索等。与基础版相比，我具有更强的上下文理解能力，更精准的专业知识和更自然的交流方式。',
    '你是谁' => '我是AlingAi增强版助手，由AlingAi公司开发的新一代人工智能助手。我基于先进的大语言模型技术，经过了特殊优化，能够提供更加智能、全面和个性化的服务。我可以理解复杂问题，提供深度分析，创建各类内容，并且能够记住我们对话的上下文，提供连贯一致的交流体验。无论是工作、学习还是生活中的各种需求，我都能为您提供专业支持。',
    'code' => '当然，我可以帮您编写代码。以下是一个简单的Python函数示例，用于计算斐波那契数列：\n\n```python\ndef fibonacci(n):\n    """计算斐波那契数列的第n个数"""\n    if n <= 0:\n        return "请输入正整数"\n    elif n == 1:\n        return 0\n    elif n == 2:\n        return 1\n    else:\n        a, b = 0, 1\n        for _ in range(2, n):\n            a, b = b, a + b\n        return b\n\n# 测试函数\nfor i in range(1, 11):\n    print(f"第{i}个斐波那契数是: {fibonacci(i)}")\n```\n\n这段代码使用迭代方法计算斐波那契数列，比递归方法更高效。您需要特定语言的代码或者其他功能的代码吗？',
    'sql' => '以下是一个SQL查询示例，用于从电子商务数据库中查找最畅销的产品：\n\n```sql\nSELECT\n    p.product_id,\n    p.product_name,\n    p.category,\n    p.price,\n    SUM(oi.quantity) as total_quantity_sold,\n    SUM(oi.quantity * p.price) as total_revenue\nFROM\n    products p\nJOIN\n    order_items oi ON p.product_id = oi.product_id\nJOIN\n    orders o ON oi.order_id = o.order_id\nWHERE\n    o.order_date BETWEEN \'2023-01-01\' AND \'2023-12-31\'\n    AND o.status = \'completed\'\nGROUP BY\n    p.product_id, p.product_name, p.category, p.price\nORDER BY\n    total_revenue DESC\nLIMIT 10;\n```\n\n这个查询会返回2023年销售额最高的10个产品，包括产品ID、名称、类别、单价、销售数量和总收入。需要我解释这个查询的任何部分，或者帮您修改它以满足特定需求吗？'
];

// Default response if no keyword matches
$response = '感谢您的提问。我理解您询问的是关于"' . $message . '"的问题。\n\n这是一个很好的问题，让我来详细回答：\n\n首先，这个话题涉及多个方面的知识。从基本原理来说，我们需要考虑...[此处根据实际问题提供详细内容]。\n\n其次，在实践应用中，有几个关键点值得注意：\n1. 确保理解核心概念和原则\n2. 考虑不同场景下的应用方式\n3. 关注最新的研究发展和趋势\n\n如果您有更具体的问题或者需要我深入解释某个特定方面，请告诉我，我很乐意提供更多信息。';

// Check if the message contains any of our keyword triggers
foreach ($responses as $keyword => $reply) {
    if (stripos($message, $keyword) !== false) {
        $response = $reply;
        break;
    }
}

// Generate context-based suggestions based on the message
$suggestions = [];
if (stripos($message, 'code') !== false || stripos($message, '代码') !== false) {
    $suggestions = [
        '这段代码如何优化性能？',
        '能帮我解释一下这个算法吗？',
        '如何添加错误处理？'
    ];
} else if (stripos($message, 'data') !== false || stripos($message, '数据') !== false) {
    $suggestions = [
        '如何可视化这些数据？',
        '能推荐合适的数据分析工具吗？',
        '如何处理数据中的异常值？'
    ];
} else {
    // Default suggestions
    $suggestions = [
        '请详细解释这个概念',
        '有相关的最新研究吗？',
        '这如何应用到实际工作中？'
    ];
}

// Detect user intent (simplified for demo)
$intent = 'inquiry'; // Default intent
if (stripos($message, 'how to') !== false || stripos($message, '如何') !== false) {
    $intent = 'how_to';
} else if (stripos($message, 'what is') !== false || stripos($message, '什么是') !== false) {
    $intent = 'definition';
} else if (stripos($message, 'code') !== false || stripos($message, '代码') !== false) {
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
