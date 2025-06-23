<?php
/**
 * AlingAi Pro - Enhanced Chat Sessions API (V2)
 * 
 * Manages chat sessions for the enhanced chat interface
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

// Mock session data
$chatSessions = [
    [
        'id' => 'sess_001',
        'title' => '产品发布策略分析',
        'created_at' => date('Y-m-d H:i:s', time() - 86400 * 1),
        'updated_at' => date('Y-m-d H:i:s', time() - 3600),
        'message_count' => 18,
        'model' => 'gpt-4-turbo',
        'summary' => '讨论了新产品的市场策略和发布计划',
        'tags' => ['marketing', 'product', 'strategy']
    ],
    [
        'id' => 'sess_002',
        'title' => '数据可视化项目',
        'created_at' => date('Y-m-d H:i:s', time() - 86400 * 3),
        'updated_at' => date('Y-m-d H:i:s', time() - 86400 * 2),
        'message_count' => 24,
        'model' => 'gpt-4-turbo',
        'summary' => '探讨了使用D3.js创建交互式数据可视化的方法',
        'tags' => ['visualization', 'data', 'javascript']
    ],
    [
        'id' => 'sess_003',
        'title' => '机器学习模型评估',
        'created_at' => date('Y-m-d H:i:s', time() - 86400 * 5),
        'updated_at' => date('Y-m-d H:i:s', time() - 86400 * 4),
        'message_count' => 15,
        'model' => 'gpt-4-turbo',
        'summary' => '讨论了不同机器学习模型的评估指标和选择方法',
        'tags' => ['machine-learning', 'AI', 'evaluation']
    ],
    [
        'id' => 'sess_004',
        'title' => '团队协作工具比较',
        'created_at' => date('Y-m-d H:i:s', time() - 86400 * 7),
        'updated_at' => date('Y-m-d H:i:s', time() - 86400 * 6),
        'message_count' => 12,
        'model' => 'gpt-4-turbo',
        'summary' => '比较了不同的团队协作和项目管理工具的优缺点',
        'tags' => ['tools', 'productivity', 'management']
    ],
    [
        'id' => 'sess_005',
        'title' => '网站安全加固计划',
        'created_at' => date('Y-m-d H:i:s', time() - 86400 * 10),
        'updated_at' => date('Y-m-d H:i:s', time() - 86400 * 8),
        'message_count' => 21,
        'model' => 'gpt-4-turbo',
        'summary' => '制定了一个全面的网站安全加固和漏洞扫描计划',
        'tags' => ['security', 'web', 'protection']
    ]
];

// Mock session messages data
$sessionMessages = [
    'sess_001' => [
        [
            'id' => 'msg_001_1',
            'role' => 'user',
            'content' => '我们计划在下个季度发布新的SaaS产品，你能帮我分析一下市场策略吗？',
            'timestamp' => date('Y-m-d H:i:s', time() - 86400 * 1 - 3600 * 5)
        ],
        [
            'id' => 'msg_001_2',
            'role' => 'assistant',
            'content' => '当然可以帮你分析新SaaS产品的市场策略。为了给出更有针对性的建议，请问能分享一些关于这个产品的基本信息吗？比如：\n\n1. 产品的主要功能和价值主张是什么？\n2. 目标客户群体是谁？\n3. 你们的主要竞争对手有哪些？\n4. 产品的价格定位大致是什么范围？',
            'timestamp' => date('Y-m-d H:i:s', time() - 86400 * 1 - 3600 * 4.9)
        ],
        [
            'id' => 'msg_001_3',
            'role' => 'user',
            'content' => '这是一个面向中小企业的客户关系管理(CRM)系统，主要功能包括客户数据管理、销售漏斗跟踪和自动化营销工具。我们的价格计划是每用户每月15-45美元，主要竞争对手包括Salesforce、HubSpot和Zoho CRM。',
            'timestamp' => date('Y-m-d H:i:s', time() - 86400 * 1 - 3600 * 4.8)
        ],
        [
            'id' => 'msg_001_4',
            'role' => 'assistant',
            'content' => '感谢您提供的信息。基于您的中小企业CRM系统，这里是一个全面的市场策略分析：\n\n### 市场定位策略\n\n1. **差异化价值主张**\n   - 与Salesforce等大型竞争对手相比，强调您的解决方案更加：\n     * 简单易用，无需专门培训\n     * 价格实惠，适合预算有限的中小企业\n     * 专为中小企业工作流程设计，不是企业级产品的简化版\n\n2. **目标细分市场**\n   - 专注于特定行业的中小企业，如：\n     * 专业服务（咨询、律所、会计）\n     * 零售和电子商务\n     * 小型制造商\n     * 科技初创公司\n   - 为每个细分市场提供定制化模板和最佳实践\n\n### 营销策略\n\n1. **内容营销**\n   - 创建针对中小企业痛点的内容：\n     * 如何以最小成本最大化客户价值\n     * 没有专职销售团队如何有效管理销售漏斗\n     * 小团队如何实施有效的CRM策略\n   - 格式包括：博客、电子书、网络研讨会、案例研究\n\n2. **社交媒体策略**\n   - LinkedIn：分享B2B内容，直接接触决策者\n   - Twitter：分享行业见解和快速提示\n   - Facebook/Instagram：展示客户成功案例和团队文化\n\n3. **SEO策略**\n   - 针对"小型企业CRM"、"经济实惠的CRM"、"简单的销售跟踪工具"等关键词优化\n\n### 销售策略\n\n1. **免费增值模式**\n   - 提供永久免费版本（有使用限制）\n   - 清晰的升级路径，基于用户数量和功能需求\n\n2. **无摩擦试用**\n   - 14天完全功能试用，无需信用卡\n   - 简化注册流程，5分钟内完成设置\n\n3. **销售流程**\n   - 主要通过自助服务获取客户\n   - 对于潜在大客户，提供个性化演示\n   - 考虑合作伙伴计划，与为中小企业提供服务的咨询公司合作\n\n### 上市时间和发布策略\n\n1. **软发布阶段**\n   - 邀请100-200个目标客户进行封闭测试\n   - 收集反馈并进行必要调整\n   - 创建早期客户成功案例\n\n2. **正式发布**\n   - 选择行业相关事件或时间点\n   - 准备新闻稿和媒体宣传\n   - 组织网络发布活动\n\n3. **发布后**\n   - 实施积极的客户成功计划\n   - 建立客户反馈循环\n   - 快速迭代解决初始问题\n\n### 价格策略\n\n基于您提供的每用户每月15-45美元的范围：\n\n1. **三层价格模型**\n   - 基础版：$15/用户/月（核心CRM功能）\n   - 专业版：$30/用户/月（加入自动化和高级报告）\n   - 企业版：$45/用户/月（所有功能，优先支持）\n\n2. **年付折扣**\n   - 提供20%年付折扣，提高客户留存率\n\n3. **限时发布优惠**\n   - 前3个月优惠50%，或者\n   - "早鸟"永久折扣\n\n### 与竞争对手的差异化\n\n1. **vs Salesforce**：更简单、更经济、专为小型团队设计\n2. **vs HubSpot**：更灵活的价格，无需为不需要的营销功能付费\n3. **vs Zoho CRM**：更现代的用户界面，更好的用户体验\n\n### 关键绩效指标\n\n1. 注册试用数\n2. 试用转化率\n3. 客户获取成本（CAC）\n4. 每月经常性收入（MRR）\n5. 客户流失率\n6. 净推荐值（NPS）\n\n您认为这个策略中哪些部分最适合您的产品和组织现状？还有其他具体方面需要我详细分析的吗？',
            'timestamp' => date('Y-m-d H:i:s', time() - 86400 * 1 - 3600 * 4.5)
        ],
        // More messages would be here
    ],
    'sess_002' => [
        [
            'id' => 'msg_002_1',
            'role' => 'user',
            'content' => '我需要为我的网站创建交互式数据可视化，你能推荐一些好的Javascript库吗？',
            'timestamp' => date('Y-m-d H:i:s', time() - 86400 * 3 - 3600 * 5)
        ],
        [
            'id' => 'msg_002_2',
            'role' => 'assistant',
            'content' => '当然可以！JavaScript有许多优秀的数据可视化库。根据你的需求、技术水平和项目类型，以下是一些值得考虑的选项：\n\n### 功能全面的可视化库\n\n1. **D3.js**\n   - 最强大、最灵活的数据可视化库\n   - 几乎可以创建任何类型的自定义数据可视化\n   - 学习曲线较陡峭，但文档和社区支持非常好\n   - 适合需要高度自定义的项目\n\n2. **Chart.js**\n   - 轻量级且易于使用\n   - 提供8种基本图表类型\n   - 响应式设计和动画效果\n   - 适合需要快速实现基本图表的项目\n\n3. **Highcharts**\n   - 商业级图表库（商业用途需付费，个人和非商业项目免费）\n   - 提供大量图表类型和强大的交互功能\n   - 易于使用且文档完善\n   - 适合企业级应用\n\n4. **ECharts**\n   - 百度开发的功能强大的库\n   - 丰富的图表类型，包括地图、3D图表\n   - 性能优异，可处理大规模数据集\n   - 自带主题和丰富的交互功能\n\n### 特定用途的可视化库\n\n5. **Leaflet.js**\n   - 轻量级的交互式地图库\n   - 如果你需要地理空间数据可视化\n\n6. **Three.js**\n   - 3D可视化\n   - 如果你需要创建复杂的3D数据表示\n\n7. **plotly.js**\n   - 科学图表和仪表板\n   - 特别适合数据科学和分析应用\n\n8. **Sigma.js**\n   - 专注于图形和网络可视化\n\n### 与框架集成的解决方案\n\n9. **React-Vis** (React)\n   - Uber开发的React组件\n   - 简化了在React应用中创建可视化的过程\n\n10. **Vue-Chartjs** (Vue)\n    - Chart.js的Vue封装\n    - 为Vue应用提供简单API\n\n11. **Angular Chart** (Angular)\n    - 为Angular应用定制的图表解决方案\n\n### 推荐策略\n\n- **对于初学者**：Chart.js是一个很好的起点，易于学习和使用\n- **需要高度自定义**：D3.js提供最大的灵活性和控制力\n- **商业项目**：考虑Highcharts或ECharts\n- **大数据集**：ECharts或D3.js配合Canvas优化\n\n你的可视化项目具体是什么类型的？需要展示什么类型的数据？这会帮助我给出更有针对性的推荐。',
            'timestamp' => date('Y-m-d H:i:s', time() - 86400 * 3 - 3600 * 4.8)
        ],
        [
            'id' => 'msg_002_3',
            'role' => 'user',
            'content' => '我需要展示一些时间序列数据和地理分布数据。我对D3.js很感兴趣但没有使用经验，你能提供一些入门指导吗？',
            'timestamp' => date('Y-m-d H:i:s', time() - 86400 * 3 - 3600 * 4.6)
        ]
        // More messages would be here
    ]
];

// Handle different HTTP methods
$method = $_SERVER['REQUEST_METHOD'];

// Get session ID from URL if provided
$urlParts = explode('/', $_SERVER['REQUEST_URI']);
$urlSessionId = end($urlParts) !== 'sessions' ? end($urlParts) : null;

switch ($method) {
    case 'GET':
        // If a specific session ID is provided in the URL
        if ($urlSessionId && $urlSessionId !== 'sessions') {
            // Return specific session and its messages
            $sessionFound = false;
            $sessionData = null;
            
            foreach ($chatSessions as $session) {
                if ($session['id'] === $urlSessionId) {
                    $sessionFound = true;
                    $sessionData = $session;
                    break;
                }
            }
            
            if (!$sessionFound) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Session not found'
                ]);
                exit;
            }
            
            $messages = $sessionMessages[$urlSessionId] ?? [];
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'session' => $sessionData,
                    'messages' => $messages
                ]
            ]);
        } else {
            // Return all sessions
            echo json_encode([
                'success' => true,
                'data' => [
                    'sessions' => $chatSessions
                ]
            ]);
        }
        break;
        
    case 'POST':
        // Create a new session
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        $title = $data['title'] ?? 'New Conversation';
        $model = $data['model'] ?? 'gpt-4-turbo';
        
        $newSession = [
            'id' => 'sess_' . uniqid(),
            'title' => $title,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'message_count' => 0,
            'model' => $model,
            'summary' => '',
            'tags' => []
        ];
        
        echo json_encode([
            'success' => true,
            'data' => [
                'session' => $newSession
            ],
            'message' => 'Session created successfully'
        ]);
        break;
        
    case 'DELETE':
        // Delete a session
        if (!$urlSessionId) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Session ID is required'
            ]);
            exit;
        }
        
        // In a real implementation, this would delete the session from database
        echo json_encode([
            'success' => true,
            'message' => 'Session deleted successfully',
            'data' => [
                'session_id' => $urlSessionId
            ]
        ]);
        break;
        
    default:
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'message' => 'Method not allowed'
        ]);
}
