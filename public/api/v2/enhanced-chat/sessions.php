<?php
/**
 * AlingAi Pro - Enhanced Chat Sessions API (V2)
 * 
 * Manages chat sessions for the enhanced chat interface
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

// Mock session data
$chatSessions = [
    [
        'id' => 'sess_001',
        'title' => '��Ʒ�������Է���',
        'created_at' => date('Y-m-d H:i:s', time() - 86400 * 1],
        'updated_at' => date('Y-m-d H:i:s', time() - 3600],
        'message_count' => 18,
        'model' => 'gpt-4-turbo',
        'summary' => '�������²�Ʒ���г����Ժͷ����ƻ�',
        'tags' => ['marketing', 'product', 'strategy']
    ], 
    [
        'id' => 'sess_002',
        'title' => '���ݿ��ӻ���Ŀ',
        'created_at' => date('Y-m-d H:i:s', time() - 86400 * 3],
        'updated_at' => date('Y-m-d H:i:s', time() - 86400 * 2],
        'message_count' => 24,
        'model' => 'gpt-4-turbo',
        'summary' => '̽����ʹ��D3.js��������ʽ���ݿ��ӻ��ķ���',
        'tags' => ['visualization', 'data', 'javascript']
    ], 
    [
        'id' => 'sess_003',
        'title' => '����ѧϰģ������',
        'created_at' => date('Y-m-d H:i:s', time() - 86400 * 5],
        'updated_at' => date('Y-m-d H:i:s', time() - 86400 * 4],
        'message_count' => 15,
        'model' => 'gpt-4-turbo',
        'summary' => '�����˲�ͬ����ѧϰģ�͵�����ָ���ѡ�񷽷�',
        'tags' => ['machine-learning', 'AI', 'evaluation']
    ], 
    [
        'id' => 'sess_004',
        'title' => '�Ŷ�Э�����߱Ƚ�',
        'created_at' => date('Y-m-d H:i:s', time() - 86400 * 7],
        'updated_at' => date('Y-m-d H:i:s', time() - 86400 * 6],
        'message_count' => 12,
        'model' => 'gpt-4-turbo',
        'summary' => '�Ƚ��˲�ͬ���Ŷ�Э������Ŀ�����ߵ���ȱ��',
        'tags' => ['tools', 'productivity', 'management']
    ], 
    [
        'id' => 'sess_005',
        'title' => '��վ��ȫ�ӹ̼ƻ�',
        'created_at' => date('Y-m-d H:i:s', time() - 86400 * 10],
        'updated_at' => date('Y-m-d H:i:s', time() - 86400 * 8],
        'message_count' => 21,
        'model' => 'gpt-4-turbo',
        'summary' => '�ƶ���һ��ȫ�����վ��ȫ�ӹ̺�©��ɨ��ƻ�',
        'tags' => ['security', 'web', 'protection']
    ]
];

// Mock session messages data
$sessionMessages = [
    'sess_001' => [
        [
            'id' => 'msg_001_1',
            'role' => 'user',
            'content' => '���Ǽƻ����¸����ȷ����µ�SaaS��Ʒ�����ܰ��ҷ���һ���г�������',
            'timestamp' => date('Y-m-d H:i:s', time() - 86400 * 1 - 3600 * 5)
        ], 
        [
            'id' => 'msg_001_2',
            'role' => 'assistant',
            'content' => '��Ȼ���԰��������SaaS��Ʒ���г����ԡ�Ϊ�˸�����������ԵĽ��飬�����ܷ���һЩ���������Ʒ�Ļ�����Ϣ�𣿱��磺\n\n1. ��Ʒ����Ҫ���ܺͼ�ֵ������ʲô��\n2. Ŀ��ͻ�Ⱥ����˭��\n3. ���ǵ���Ҫ������������Щ��\n4. ��Ʒ�ļ۸�λ������ʲô��Χ��',
            'timestamp' => date('Y-m-d H:i:s', time() - 86400 * 1 - 3600 * 4.9)
        ], 
        [
            'id' => 'msg_001_3',
            'role' => 'user',
            'content' => '����һ��������С��ҵ�Ŀͻ���ϵ����(CRM)ϵͳ����Ҫ���ܰ����ͻ����ݹ�������©�����ٺ��Զ���Ӫ�����ߡ����ǵļ۸�ƻ���ÿ�û�ÿ��15-45��Ԫ����Ҫ�������ְ���Salesforce��HubSpot��Zoho CRM��',
            'timestamp' => date('Y-m-d H:i:s', time() - 86400 * 1 - 3600 * 4.8)
        ], 
        [
            'id' => 'msg_001_4',
            'role' => 'assistant',
            'content' => '��л���ṩ����Ϣ������������С��ҵCRMϵͳ��������һ��ȫ����г����Է�����\n\n### �г���λ����\n\n1. **���컯��ֵ����**\n   - ��Salesforce�ȴ��;���������ȣ�ǿ�����Ľ���������ӣ�\n     * �����ã�����ר����ѵ\n     * �۸�ʵ�ݣ��ʺ�Ԥ�����޵���С��ҵ\n     * רΪ��С��ҵ����������ƣ�������ҵ����Ʒ�ļ򻯰�\n\n2. **Ŀ��ϸ���г�**\n   - רע���ض���ҵ����С��ҵ���磺\n     * רҵ������ѯ����������ƣ�\n     * ���ۺ͵�������\n     * С��������\n     * �Ƽ�������˾\n   - Ϊÿ��ϸ���г��ṩ���ƻ�ģ������ʵ��\n\n### Ӫ������\n\n1. **����Ӫ��**\n   - ���������С��ҵʹ������ݣ�\n     * �������С�ɱ���󻯿ͻ���ֵ\n     * û��רְ�����Ŷ������Ч��������©��\n     * С�Ŷ����ʵʩ��Ч��CRM����\n   - ��ʽ���������͡������顢�������ֻᡢ�����о�\n\n2. **�罻ý�����**\n   - LinkedIn������B2B���ݣ�ֱ�ӽӴ�������\n   - Twitter��������ҵ����Ϳ�����ʾ\n   - Facebook/Instagram��չʾ�ͻ��ɹ��������Ŷ��Ļ�\n\n3. **SEO����**\n   - ���"С����ҵCRM"��"����ʵ�ݵ�CRM"��"�򵥵����۸��ٹ���"�ȹؼ����Ż�\n\n### ���۲���\n\n1. **�����ֵģʽ**\n   - �ṩ������Ѱ汾����ʹ�����ƣ�\n   - ����������·���������û������͹�������\n\n2. **��Ħ������**\n   - 14����ȫ�������ã��������ÿ�\n   - ��ע�����̣�5�������������\n\n3. **��������**\n   - ��Ҫͨ�����������ȡ�ͻ�\n   - ����Ǳ�ڴ�ͻ����ṩ���Ի���ʾ\n   - ���Ǻ������ƻ�����Ϊ��С��ҵ�ṩ�������ѯ��˾����\n\n### ����ʱ��ͷ�������\n\n1. **�����׶�**\n   - ����100-200��Ŀ��ͻ����з�ղ���\n   - �ռ����������б�Ҫ����\n   - �������ڿͻ��ɹ�����\n\n2. **��ʽ����**\n   - ѡ����ҵ����¼���ʱ���\n   - ׼�����Ÿ��ý������\n   - ��֯���緢���\n\n3. **������**\n   - ʵʩ�����Ŀͻ��ɹ��ƻ�\n   - �����ͻ�����ѭ��\n   - ���ٵ��������ʼ����\n\n### �۸����\n\n�������ṩ��ÿ�û�ÿ��15-45��Ԫ�ķ�Χ��\n\n1. **����۸�ģ��**\n   - �����棺$15/�û�/�£�����CRM���ܣ�\n   - רҵ�棺$30/�û�/�£������Զ����͸߼����棩\n   - ��ҵ�棺$45/�û�/�£����й��ܣ�����֧�֣�\n\n2. **�긶�ۿ�**\n   - �ṩ20%�긶�ۿۣ���߿ͻ�������\n\n3. **��ʱ�����Ż�**\n   - ǰ3�����Ż�50%������\n   - "����"�����ۿ�\n\n### �뾺�����ֵĲ��컯\n\n1. **vs Salesforce**�����򵥡������á�רΪС���Ŷ����\n2. **vs HubSpot**�������ļ۸�����Ϊ����Ҫ��Ӫ�����ܸ���\n3. **vs Zoho CRM**�����ִ����û����棬���õ��û�����\n\n### �ؼ���Чָ��\n\n1. ע��������\n2. ����ת����\n3. �ͻ���ȡ�ɱ���CAC��\n4. ÿ�¾��������루MRR��\n5. �ͻ���ʧ��\n6. ���Ƽ�ֵ��NPS��\n\n����Ϊ�����������Щ�������ʺ����Ĳ�Ʒ����֯��״�������������巽����Ҫ����ϸ��������',
            'timestamp' => date('Y-m-d H:i:s', time() - 86400 * 1 - 3600 * 4.5)
        ], 
        // More messages would be here
    ], 
    'sess_002' => [
        [
            'id' => 'msg_002_1',
            'role' => 'user',
            'content' => '����ҪΪ�ҵ���վ��������ʽ���ݿ��ӻ��������Ƽ�һЩ�õ�Javascript����',
            'timestamp' => date('Y-m-d H:i:s', time() - 86400 * 3 - 3600 * 5)
        ], 
        [
            'id' => 'msg_002_2',
            'role' => 'assistant',
            'content' => '��Ȼ���ԣ�JavaScript�������������ݿ��ӻ��⡣����������󡢼���ˮƽ����Ŀ���ͣ�������һЩֵ�ÿ��ǵ�ѡ�\n\n### ����ȫ��Ŀ��ӻ���\n\n1. **D3.js**\n   - ��ǿ�����������ݿ��ӻ���\n   - �������Դ����κ����͵��Զ������ݿ��ӻ�\n   - ѧϰ���߽϶��ͣ����ĵ�������֧�ַǳ���\n   - �ʺ���Ҫ�߶��Զ������Ŀ\n\n2. **Chart.js**\n   - ������������ʹ��\n   - �ṩ8�ֻ���ͼ������\n   - ��Ӧʽ��ƺͶ���Ч��\n   - �ʺ���Ҫ����ʵ�ֻ���ͼ�����Ŀ\n\n3. **Highcharts**\n   - ��ҵ��ͼ��⣨��ҵ��;�踶�ѣ����˺ͷ���ҵ��Ŀ��ѣ�\n   - �ṩ����ͼ�����ͺ�ǿ��Ľ�������\n   - ����ʹ�����ĵ�����\n   - �ʺ���ҵ��Ӧ��\n\n4. **ECharts**\n   - �ٶȿ����Ĺ���ǿ��Ŀ�\n   - �ḻ��ͼ�����ͣ�������ͼ��3Dͼ��\n   - �������죬�ɴ�����ģ���ݼ�\n   - �Դ�����ͷḻ�Ľ�������\n\n### �ض���;�Ŀ��ӻ���\n\n5. **Leaflet.js**\n   - �������Ľ���ʽ��ͼ��\n   - �������Ҫ����ռ����ݿ��ӻ�\n\n6. **Three.js**\n   - 3D���ӻ�\n   - �������Ҫ�������ӵ�3D���ݱ�ʾ\n\n7. **plotly.js**\n   - ��ѧͼ����Ǳ��\n   - �ر��ʺ����ݿ�ѧ�ͷ���Ӧ��\n\n8. **Sigma.js**\n   - רע��ͼ�κ�������ӻ�\n\n### ���ܼ��ɵĽ������\n\n9. **React-Vis** (React)\n   - Uber������React���\n   - ������ReactӦ���д������ӻ��Ĺ���\n\n10. **Vue-Chartjs** (Vue)\n    - Chart.js��Vue��װ\n    - ΪVueӦ���ṩ��API\n\n11. **Angular Chart** (Angular)\n    - ΪAngularӦ�ö��Ƶ�ͼ��������\n\n### �Ƽ�����\n\n- **���ڳ�ѧ��**��Chart.js��һ���ܺõ���㣬����ѧϰ��ʹ��\n- **��Ҫ�߶��Զ���**��D3.js�ṩ��������ԺͿ�����\n- **��ҵ��Ŀ**������Highcharts��ECharts\n- **�����ݼ�**��ECharts��D3.js���Canvas�Ż�\n\n��Ŀ��ӻ���Ŀ������ʲô���͵ģ���Ҫչʾʲô���͵����ݣ��������Ҹ�����������Ե��Ƽ���',
            'timestamp' => date('Y-m-d H:i:s', time() - 86400 * 3 - 3600 * 4.8)
        ], 
        [
            'id' => 'msg_002_3',
            'role' => 'user',
            'content' => '����ҪչʾһЩʱ���������ݺ͵���ֲ����ݡ��Ҷ�D3.js�ܸ���Ȥ��û��ʹ�þ��飬�����ṩһЩ����ָ����',
            'timestamp' => date('Y-m-d H:i:s', time() - 86400 * 3 - 3600 * 4.6)
        ]
        // More messages would be here
    ]
];

// Handle different HTTP methods
$method = $_SERVER['REQUEST_METHOD'];

// Get session ID from URL if provided
$urlParts = explode('/', $_SERVER['REQUEST_URI']];
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
                http_response_code(404];
                echo json_encode([
                    'success' => false,
                    'message' => 'Session not found'
                ]];
                exit;
            }
            
            $messages = $sessionMessages[$urlSessionId] ?? [];
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'session' => $sessionData,
                    'messages' => $messages
                ]
            ]];
        } else {
            // Return all sessions
            echo json_encode([
                'success' => true,
                'data' => [
                    'sessions' => $chatSessions
                ]
            ]];
        }
        break;
        
    case 'POST':
        // Create a new session
        $json = file_get_contents('php://input'];
        $data = json_decode($json, true];
        
        $title = $data['title'] ?? 'New Conversation';
        $model = $data['model'] ?? 'gpt-4-turbo';
        
        $newSession = [
            'id' => 'sess_' . uniqid(),
            'title' => $title,
            'created_at' => date('Y-m-d H:i:s'],
            'updated_at' => date('Y-m-d H:i:s'],
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
        ]];
        break;
        
    case 'DELETE':
        // Delete a session
        if (!$urlSessionId) {
            http_response_code(400];
            echo json_encode([
                'success' => false,
                'message' => 'Session ID is required'
            ]];
            exit;
        }
        
        // In a real implementation, this would delete the session from database
        echo json_encode([
            'success' => true,
            'message' => 'Session deleted successfully',
            'data' => [
                'session_id' => $urlSessionId
            ]
        ]];
        break;
        
    default:
        http_response_code(405];
        echo json_encode([
            'success' => false,
            'message' => 'Method not allowed'
        ]];
}

