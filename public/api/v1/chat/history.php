<?php
/**
 * AlingAi Pro - Chat History API
 * 
 * Provides chat history data for authenticated users
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

// Get session ID from query param or default to 'all'
$sessionId = $_GET['session_id'] ?? 'all';

// Mock chat sessions for the demo
$chatSessions = [
    [
        'id' => 'sess_001',
        'title' => '项目计划讨论',
        'created_at' => date('Y-m-d H:i:s', time() - 86400 * 2],
        'updated_at' => date('Y-m-d H:i:s', time() - 3600],
        'message_count' => 12,
        'model' => 'gpt-4',
        'summary' => '讨论了项目计划、时间线和资源分配'
    ], 
    [
        'id' => 'sess_002',
        'title' => '代码优化建议',
        'created_at' => date('Y-m-d H:i:s', time() - 86400 * 5],
        'updated_at' => date('Y-m-d H:i:s', time() - 86400 * 4],
        'message_count' => 8,
        'model' => 'gpt-4',
        'summary' => '分析了代码性能问题并提供了优化建议'
    ], 
    [
        'id' => 'sess_003',
        'title' => '市场调研分析',
        'created_at' => date('Y-m-d H:i:s', time() - 86400 * 10],
        'updated_at' => date('Y-m-d H:i:s', time() - 86400 * 10],
        'message_count' => 15,
        'model' => 'gpt-4',
        'summary' => '分析了目标市场数据并提出了营销策略'
    ]
];

// Mock messages for a specific session
$mockMessages = [
    'sess_001' => [
        [
            'id' => 'msg_001',
            'role' => 'user',
            'content' => '你好，我想讨论一下我们的新项目计划。',
            'timestamp' => date('Y-m-d H:i:s', time() - 86400 * 2 - 3600 * 2)
        ], 
        [
            'id' => 'msg_002',
            'role' => 'assistant',
            'content' => '你好！很高兴能帮助你讨论新项目计划。请告诉我更多关于这个项目的信息，比如项目目标、时间线、团队规模等，这样我可以给出更有针对性的建议。',
            'timestamp' => date('Y-m-d H:i:s', time() - 86400 * 2 - 3600 * 2 + 60)
        ], 
        [
            'id' => 'msg_003',
            'role' => 'user',
            'content' => '这是一个为期3个月的网站重构项目，我们有5名开发人员和2名设计师。主要目标是提高网站性能和用户体验。',
            'timestamp' => date('Y-m-d H:i:s', time() - 86400 * 2 - 3600)
        ], 
        [
            'id' => 'msg_004',
            'role' => 'assistant',
            'content' => '了解了，这是一个网站重构项目，重点关注性能和用户体验，团队包括5名开发人员和2名设计师，时间框架为3个月。\n\n对于这种项目，我建议采用以下计划结构：\n\n**第1-2周：分析与规划**\n- 对当前网站进行性能审计和用户体验评估\n- 确定具体的性能指标目标（如加载时间、互动时间等）\n- 创建详细的项目范围文档和需求规格说明书\n- 设计团队开始构思UI/UX改进\n\n**第3-6周：设计与开发初期**\n- 完成新的设计原型和用户流程\n- 开始前端架构的重构\n- 实施核心性能优化（CDN配置、资源压缩、懒加载等）\n- 每周进行团队进度审查\n\n**第7-10周：主要开发阶段**\n- 实现所有主要功能和页面\n- 持续集成和测试\n- 性能测试和优化\n- 解决关键问题和瓶颈\n\n**第11-12周：测试与部署**\n- 全面质量保证测试\n- 用户验收测试\n- 最终性能优化\n- 准备上线和部署策略\n- 部署到生产环境\n\n你认为这个计划结构如何？是否有任何特定领域需要更详细的计划？',
            'timestamp' => date('Y-m-d H:i:s', time() - 86400 * 2 - 3600 + 120)
        ], 
        [
            'id' => 'msg_005',
            'role' => 'user',
            'content' => '这个计划看起来很合理。我有点担心资源分配问题，因为我们的一些开发人员同时还在处理其他项目。你觉得我们应该如何优化资源分配？',
            'timestamp' => date('Y-m-d H:i:s', time() - 3600 * 2)
        ], 
        [
            'id' => 'msg_006',
            'role' => 'assistant',
            'content' => '资源分配确实是一个关键挑战，特别是当团队成员同时处理多个项目时。以下是一些建议：\n\n**1. 采用优先级矩阵**\n- 将项目任务按重要性和紧急性分类\n- 确保关键路径上的任务有专职人员负责\n- 非关键任务可以由分时工作的开发人员处理\n\n**2. 明确的时间分配**\n- 为每个开发人员创建清晰的时间表，指明他们每周在此项目上的具体时间\n- 例如：开发人员A每周投入30小时，开发人员B每周投入20小时\n- 避免假设所有人都能全职参与\n\n**3. 模块化工作划分**\n- 将项目分解为独立模块，便于并行开发\n- 减少依赖关系，使部分时间工作的开发人员能够完整负责某个模块\n\n**4. 引入弹性缓冲时间**\n- 在计划中增加15-20%的缓冲时间\n- 考虑开发人员在多项目之间切换的上下文切换成本\n\n**5. 考虑使用外部资源**\n- 对于一些标准组件或非核心功能，考虑使用外部资源或现成解决方案\n- 这可以减轻内部团队的工作量\n\n**6. 实施敏捷冲刺规划**\n- 使用1-2周的冲刺周期\n- 根据当前可用资源进行现实的冲刺计划\n- 每个冲刺结束时重新评估资源可用性\n\n**7. 设置明确的期望**\n- 与利益相关者沟通可能的资源限制\n- 适当调整时间线或项目范围\n\n这些策略可以帮助您更有效地管理有限的开发资源。您是否已经尝试过其中任何方法？或者您对特定的资源分配方面有更具体的问题？',
            'timestamp' => date('Y-m-d H:i:s', time() - 3600)
        ]
    ], 
    'sess_002' => [
        [
            'id' => 'msg_101',
            'role' => 'user',
            'content' => '我有一段JavaScript代码性能不太好，能帮我看看如何优化吗？',
            'timestamp' => date('Y-m-d H:i:s', time() - 86400 * 5)
        ], 
        [
            'id' => 'msg_102',
            'role' => 'assistant',
            'content' => '当然可以帮你优化JavaScript代码。请分享你的代码，这样我可以提供具体的优化建议。',
            'timestamp' => date('Y-m-d H:i:s', time() - 86400 * 5 + 60)
        ]
        // Additional messages would be here
    ], 
    'sess_003' => [
        [
            'id' => 'msg_201',
            'role' => 'user',
            'content' => '我需要做一份关于人工智能市场的调研报告，能给我一些建议吗？',
            'timestamp' => date('Y-m-d H:i:s', time() - 86400 * 10)
        ], 
        [
            'id' => 'msg_202',
            'role' => 'assistant',
            'content' => '当然可以帮助你准备人工智能市场的调研报告。这是一个广阔且快速发展的领域。我可以提供一些建议来帮助你构建全面的调研报告。',
            'timestamp' => date('Y-m-d H:i:s', time() - 86400 * 10 + 60)
        ]
        // Additional messages would be here
    ]
];

// Return data based on whether a specific session is requested
if ($sessionId != 'all') {
    // Return messages for the specific session
    if (isset($mockMessages[$sessionId])) {
        // Find the session info
        $sessionInfo = null;
        foreach ($chatSessions as $session) {
            if ($session['id'] == $sessionId) {
                $sessionInfo = $session;
                break;
            }
        }
        
        echo json_encode([
            'success' => true,
            'data' => [
                'session' => $sessionInfo,
                'messages' => $mockMessages[$sessionId]
            ]
        ]];
    } else {
        http_response_code(404];
        echo json_encode([
            'success' => false,
            'message' => 'Chat session not found'
        ]];
    }
} else {
    // Return all sessions
    echo json_encode([
        'success' => true,
        'data' => [
            'sessions' => $chatSessions
        ]
    ]];
}
