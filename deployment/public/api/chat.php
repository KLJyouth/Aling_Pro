<?php
/**
 * AI聊天API端点
 * 处理用户与AI助手的对话
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// 处理预检请求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 只允许POST请求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => '只允许POST请求']);
    exit();
}

try {
    // 获取POST数据
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || empty($input['message'])) {
        echo json_encode([
            'success' => false, 
            'message' => '请提供消息内容'
        ]);
        exit();
    }

    $userMessage = trim($input['message']);
    
    // 简单的AI回复逻辑（可以接入真实的AI服务）
    $response = generateAIResponse($userMessage);
    
    echo json_encode([
        'success' => true,
        'response' => $response,
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    error_log("聊天API错误: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => '服务器错误，请稍后重试。'
    ]);
}

/**
 * 生成AI回复（示例实现）
 */
function generateAIResponse($message) {
    $message = strtolower($message);
    
    // 预定义的回复模板
    $responses = [
        // 问候语
        'hello' => '您好！我是AlingAi Pro的智能助手，很高兴为您服务！',
        'hi' => '您好！有什么可以帮助您的吗？',
        '你好' => '您好！我是AlingAi Pro的AI助手，请问有什么可以帮助您的吗？',
        '您好' => '您好！很高兴为您服务，请问有什么问题需要咨询吗？',
        
        // 产品相关
        'alingai' => 'AlingAi Pro是我们的旗舰AI产品，提供智能对话、数据分析、自动化等功能。',
        '产品' => '我们的主要产品包括AlingAi Pro智能平台、AI解决方案和定制化服务。',
        '服务' => '我们提供AI技术咨询、产品定制开发、技术支持等全方位服务。',
        '价格' => '我们提供多种价格方案，包括基础版、专业版和企业版，具体价格请联系我们的销售团队。',
        
        // 技术相关
        '技术' => '我们专注于人工智能、机器学习、自然语言处理等前沿技术。',
        'ai' => '人工智能是我们的核心技术领域，我们在深度学习、NLP、计算机视觉等方面都有丰富经验。',
        '机器学习' => '机器学习是我们的核心技术之一，我们可以提供从算法设计到模型部署的全流程服务。',
        
        // 联系方式
        '联系' => '您可以通过以下方式联系我们：\n电话：400-888-8888\n邮箱：contact@alingai.com\n地址：广西壮族自治区防城港市防城区防东路实验学校院内3栋204房',
        '电话' => '我们的客服电话是：400-888-8888',
        '邮箱' => '您可以发送邮件到：contact@alingai.com',
        '地址' => '我们的地址是：广西壮族自治区防城港市防城区防东路实验学校院内3栋204房',
        
        // 帮助
        '帮助' => '我可以为您介绍我们的产品和服务，回答技术问题，或者帮您联系我们的专业团队。',
        '功能' => 'AlingAi Pro具有智能对话、数据分析、自动化处理、个性化推荐等多种功能。',
        
        // 其他
        '谢谢' => '不客气！如果您还有其他问题，随时可以问我。',
        '再见' => '再见！感谢您使用AlingAi Pro，期待再次为您服务！',
    ];
    
    // 关键词匹配
    foreach ($responses as $keyword => $response) {
        if (strpos($message, $keyword) !== false) {
            return $response;
        }
    }
    
    // 模糊匹配
    if (preg_match('/(怎么|如何|怎样)/', $message)) {
        if (strpos($message, '使用') !== false || strpos($message, '操作') !== false) {
            return '关于产品使用，我们有详细的使用文档和视频教程。您也可以联系我们的技术支持团队获得帮助。';
        }
        if (strpos($message, '购买') !== false || strpos($message, '获得') !== false) {
            return '您可以通过我们的官网购买，或者联系销售团队获取定制方案。电话：400-888-8888';
        }
    }
    
    if (preg_match('/(什么|啥|介绍)/', $message)) {
        if (strpos($message, '公司') !== false) {
            return 'AlingAi Pro是一家专注于人工智能技术的创新公司，我们致力于为客户提供最先进的AI解决方案。';
        }
        if (strpos($message, '团队') !== false) {
            return '我们拥有一支专业的技术团队，成员来自全国各地，都是信息安全和AI领域的专家。';
        }
    }
    
    if (preg_match('/(可以|能否|能不能)/', $message)) {
        return '当然可以！请告诉我您的具体需求，我会尽力为您提供帮助或者安排专业人员与您联系。';
    }
    
    // 默认回复
    $defaultResponses = [
        '感谢您的咨询！请问您想了解我们的哪个方面呢？比如产品功能、技术服务或者合作方式？',
        '我很乐意为您提供帮助！请详细描述您的需求，我会为您提供最合适的解决方案。',
        '这是一个很好的问题！我建议您可以联系我们的专业团队获得更详细的答案。电话：400-888-8888',
        '为了给您最准确的答案，建议您联系我们的技术专家。您可以通过联系表单留言，我们会尽快回复您。',
    ];
    
    return $defaultResponses[array_rand($defaultResponses)];
}
?>
