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
        'title' => '��Ŀ�ƻ�����',
        'created_at' => date('Y-m-d H:i:s', time() - 86400 * 2],
        'updated_at' => date('Y-m-d H:i:s', time() - 3600],
        'message_count' => 12,
        'model' => 'gpt-4',
        'summary' => '��������Ŀ�ƻ���ʱ���ߺ���Դ����'
    ], 
    [
        'id' => 'sess_002',
        'title' => '�����Ż�����',
        'created_at' => date('Y-m-d H:i:s', time() - 86400 * 5],
        'updated_at' => date('Y-m-d H:i:s', time() - 86400 * 4],
        'message_count' => 8,
        'model' => 'gpt-4',
        'summary' => '�����˴����������Ⲣ�ṩ���Ż�����'
    ], 
    [
        'id' => 'sess_003',
        'title' => '�г����з���',
        'created_at' => date('Y-m-d H:i:s', time() - 86400 * 10],
        'updated_at' => date('Y-m-d H:i:s', time() - 86400 * 10],
        'message_count' => 15,
        'model' => 'gpt-4',
        'summary' => '������Ŀ���г����ݲ������Ӫ������'
    ]
];

// Mock messages for a specific session
$mockMessages = [
    'sess_001' => [
        [
            'id' => 'msg_001',
            'role' => 'user',
            'content' => '��ã���������һ�����ǵ�����Ŀ�ƻ���',
            'timestamp' => date('Y-m-d H:i:s', time() - 86400 * 2 - 3600 * 2)
        ], 
        [
            'id' => 'msg_002',
            'role' => 'assistant',
            'content' => '��ã��ܸ����ܰ�������������Ŀ�ƻ���������Ҹ�����������Ŀ����Ϣ��������ĿĿ�ꡢʱ���ߡ��Ŷӹ�ģ�ȣ������ҿ��Ը�����������ԵĽ��顣',
            'timestamp' => date('Y-m-d H:i:s', time() - 86400 * 2 - 3600 * 2 + 60)
        ], 
        [
            'id' => 'msg_003',
            'role' => 'user',
            'content' => '����һ��Ϊ��3���µ���վ�ع���Ŀ��������5��������Ա��2�����ʦ����ҪĿ���������վ���ܺ��û����顣',
            'timestamp' => date('Y-m-d H:i:s', time() - 86400 * 2 - 3600)
        ], 
        [
            'id' => 'msg_004',
            'role' => 'assistant',
            'content' => '�˽��ˣ�����һ����վ�ع���Ŀ���ص��ע���ܺ��û����飬�ŶӰ���5��������Ա��2�����ʦ��ʱ����Ϊ3���¡�\n\n����������Ŀ���ҽ���������¼ƻ��ṹ��\n\n**��1-2�ܣ�������滮**\n- �Ե�ǰ��վ����������ƺ��û���������\n- ȷ�����������ָ��Ŀ�꣨�����ʱ�䡢����ʱ��ȣ�\n- ������ϸ����Ŀ��Χ�ĵ���������˵����\n- ����Ŷӿ�ʼ��˼UI/UX�Ľ�\n\n**��3-6�ܣ�����뿪������**\n- ����µ����ԭ�ͺ��û�����\n- ��ʼǰ�˼ܹ����ع�\n- ʵʩ���������Ż���CDN���á���Դѹ���������صȣ�\n- ÿ�ܽ����Ŷӽ������\n\n**��7-10�ܣ���Ҫ�����׶�**\n- ʵ��������Ҫ���ܺ�ҳ��\n- �������ɺͲ���\n- ���ܲ��Ժ��Ż�\n- ����ؼ������ƿ��\n\n**��11-12�ܣ������벿��**\n- ȫ��������֤����\n- �û����ղ���\n- ���������Ż�\n- ׼�����ߺͲ������\n- ������������\n\n����Ϊ����ƻ��ṹ��Σ��Ƿ����κ��ض�������Ҫ����ϸ�ļƻ���',
            'timestamp' => date('Y-m-d H:i:s', time() - 86400 * 2 - 3600 + 120)
        ], 
        [
            'id' => 'msg_005',
            'role' => 'user',
            'content' => '����ƻ��������ܺ������е㵣����Դ�������⣬��Ϊ���ǵ�һЩ������Աͬʱ���ڴ���������Ŀ�����������Ӧ������Ż���Դ���䣿',
            'timestamp' => date('Y-m-d H:i:s', time() - 3600 * 2)
        ], 
        [
            'id' => 'msg_006',
            'role' => 'assistant',
            'content' => '��Դ����ȷʵ��һ���ؼ���ս���ر��ǵ��Ŷӳ�Աͬʱ��������Ŀʱ��������һЩ���飺\n\n**1. �������ȼ�����**\n- ����Ŀ������Ҫ�Ժͽ����Է���\n- ȷ���ؼ�·���ϵ�������רְ��Ա����\n- �ǹؼ���������ɷ�ʱ�����Ŀ�����Ա����\n\n**2. ��ȷ��ʱ�����**\n- Ϊÿ��������Ա����������ʱ���ָ������ÿ���ڴ���Ŀ�ϵľ���ʱ��\n- ���磺������ԱAÿ��Ͷ��30Сʱ��������ԱBÿ��Ͷ��20Сʱ\n- ������������˶���ȫְ����\n\n**3. ģ�黯��������**\n- ����Ŀ�ֽ�Ϊ����ģ�飬���ڲ��п���\n- ����������ϵ��ʹ����ʱ�乤���Ŀ�����Ա�ܹ���������ĳ��ģ��\n\n**4. ���뵯�Ի���ʱ��**\n- �ڼƻ�������15-20%�Ļ���ʱ��\n- ���ǿ�����Ա�ڶ���Ŀ֮���л����������л��ɱ�\n\n**5. ����ʹ���ⲿ��Դ**\n- ����һЩ��׼�����Ǻ��Ĺ��ܣ�����ʹ���ⲿ��Դ���ֳɽ������\n- ����Լ����ڲ��ŶӵĹ�����\n\n**6. ʵʩ���ݳ�̹滮**\n- ʹ��1-2�ܵĳ������\n- ���ݵ�ǰ������Դ������ʵ�ĳ�̼ƻ�\n- ÿ����̽���ʱ����������Դ������\n\n**7. ������ȷ������**\n- ����������߹�ͨ���ܵ���Դ����\n- �ʵ�����ʱ���߻���Ŀ��Χ\n\n��Щ���Կ��԰���������Ч�ع������޵Ŀ�����Դ�����Ƿ��Ѿ����Թ������κη��������������ض�����Դ���䷽���и���������⣿',
            'timestamp' => date('Y-m-d H:i:s', time() - 3600)
        ]
    ], 
    'sess_002' => [
        [
            'id' => 'msg_101',
            'role' => 'user',
            'content' => '����һ��JavaScript�������ܲ�̫�ã��ܰ��ҿ�������Ż���',
            'timestamp' => date('Y-m-d H:i:s', time() - 86400 * 5)
        ], 
        [
            'id' => 'msg_102',
            'role' => 'assistant',
            'content' => '��Ȼ���԰����Ż�JavaScript���롣�������Ĵ��룬�����ҿ����ṩ������Ż����顣',
            'timestamp' => date('Y-m-d H:i:s', time() - 86400 * 5 + 60)
        ]
        // Additional messages would be here
    ], 
    'sess_003' => [
        [
            'id' => 'msg_201',
            'role' => 'user',
            'content' => '����Ҫ��һ�ݹ����˹������г��ĵ��б��棬�ܸ���һЩ������',
            'timestamp' => date('Y-m-d H:i:s', time() - 86400 * 10)
        ], 
        [
            'id' => 'msg_202',
            'role' => 'assistant',
            'content' => '��Ȼ���԰�����׼���˹������г��ĵ��б��档����һ�������ҿ��ٷ�չ�������ҿ����ṩһЩ�����������㹹��ȫ��ĵ��б��档',
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
