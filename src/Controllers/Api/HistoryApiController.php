<?php

namespace AlingAi\Controllers\Api;

use AlingAi\Controllers\Api\BaseApiController;

/**
 * 历史记录API控制器
 * 处理聊天历史、会话管理等历史记录相关的API请求
 */
class HistoryApiController extends BaseApiController
{
    /**
     * 测试端点
     */
    public function test()
    {
        return $this->success([
            'message' => '历史记录API控制器工作正常',
            'controller' => 'HistoryApiController',
            'timestamp' => date('Y-m-d H:i:s'),
            'endpoints' => [
                'GET /api/history/test' => '测试端点',
                'GET /api/history/sessions' => '获取历史会话列表',
                'GET /api/history' => '获取历史消息',
                'POST /api/history' => '保存历史记录',
                'GET /api/history/{id}' => '获取特定历史记录',
                'DELETE /api/history/{id}' => '删除历史记录'
            ]
        ]);
    }

    /**
     * 获取历史会话列表
     * GET /api/history/sessions
     */
    public function getSessions()
    {
        try {
            // 模拟会话数据 - 实际应该从数据库获取
            $sessions = [
                [
                    'id' => '1',
                    'title' => '关于AI技术的讨论',
                    'created_at' => date('Y-m-d H:i:s', time() - 3600),
                    'updated_at' => date('Y-m-d H:i:s', time() - 1800),
                    'message_count' => 15,
                    'last_message' => '感谢您的解释，我对AI有了更深的理解。'
                ],
                [
                    'id' => '2', 
                    'title' => '编程问题咨询',
                    'created_at' => date('Y-m-d H:i:s', time() - 7200),
                    'updated_at' => date('Y-m-d H:i:s', time() - 3600),
                    'message_count' => 8,
                    'last_message' => '这个PHP代码示例很有帮助。'
                ],
                [
                    'id' => '3',
                    'title' => '产品功能介绍',
                    'created_at' => date('Y-m-d H:i:s', time() - 14400),
                    'updated_at' => date('Y-m-d H:i:s', time() - 7200),
                    'message_count' => 22,
                    'last_message' => '我想了解更多关于云服务的信息。'
                ]
            ];

            return $this->success([
                'sessions' => $sessions,
                'total' => count($sessions),
                'message' => '历史会话获取成功'
            ]);

        } catch (\Exception $e) {
            return $this->error('获取历史会话失败: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * 获取历史消息
     * GET /api/history
     */
    public function getMessages()
    {
        try {
            $sessionId = $_GET['session_id'] ?? null;
            $limit = intval($_GET['limit'] ?? 50);
            $offset = intval($_GET['offset'] ?? 0);

            // 模拟历史消息数据 - 实际应该从数据库获取
            $messages = [
                [
                    'id' => '1',
                    'session_id' => $sessionId ?? '1',
                    'type' => 'user',
                    'content' => '你好，我想了解一下AI技术。',
                    'timestamp' => date('Y-m-d H:i:s', time() - 3600),
                    'metadata' => []
                ],
                [
                    'id' => '2',
                    'session_id' => $sessionId ?? '1',
                    'type' => 'assistant',
                    'content' => '您好！我很乐意为您介绍AI技术。人工智能是一个涵盖机器学习、自然语言处理、计算机视觉等多个领域的技术。您想了解哪个方面呢？',
                    'timestamp' => date('Y-m-d H:i:s', time() - 3580),
                    'metadata' => [
                        'model' => 'deepseek-chat',
                        'tokens_used' => 125
                    ]
                ],
                [
                    'id' => '3',
                    'session_id' => $sessionId ?? '1',
                    'type' => 'user',
                    'content' => '我对机器学习比较感兴趣，能详细说说吗？',
                    'timestamp' => date('Y-m-d H:i:s', time() - 3500),
                    'metadata' => []
                ],
                [
                    'id' => '4',
                    'session_id' => $sessionId ?? '1',
                    'type' => 'assistant',
                    'content' => '机器学习是AI的核心分支，它让计算机能够从数据中学习规律，而不需要明确编程。主要分为监督学习、无监督学习和强化学习三种类型...',
                    'timestamp' => date('Y-m-d H:i:s', time() - 3480),
                    'metadata' => [
                        'model' => 'deepseek-chat',
                        'tokens_used' => 200
                    ]
                ]
            ];

            // 应用分页
            $paginatedMessages = array_slice($messages, $offset, $limit);

            return $this->success([
                'messages' => $paginatedMessages,
                'total' => count($messages),
                'limit' => $limit,
                'offset' => $offset,
                'session_id' => $sessionId,
                'message' => '历史消息获取成功'
            ]);

        } catch (\Exception $e) {
            return $this->error('获取历史消息失败: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * 保存历史记录
     * POST /api/history
     */
    public function saveHistory()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                return $this->error('无效的请求数据', [], 400);
            }

            $sessionId = $input['session_id'] ?? null;
            $messages = $input['messages'] ?? [];
            $title = $input['title'] ?? '新对话';

            // 验证必需字段
            if (empty($messages)) {
                return $this->error('消息数据不能为空', [], 400);
            }

            // 模拟保存操作 - 实际应该保存到数据库
            $savedData = [
                'session_id' => $sessionId ?? uniqid('session_'),
                'title' => $title,
                'messages_count' => count($messages),
                'saved_at' => date('Y-m-d H:i:s'),
                'status' => 'saved'
            ];

            // 记录操作日志
            error_log("History saved: " . json_encode($savedData));

            return $this->success([
                'data' => $savedData,
                'message' => '历史记录保存成功'
            ]);

        } catch (\Exception $e) {
            return $this->error('保存历史记录失败: ' . $e->getMessage(), [], 500);
        }
    }    /**
     * 获取特定历史记录
     * GET /api/history/{id}
     */
    public function getHistoryById($requestData = null, $params = null)
    {
        try {
            // 从参数中获取ID
            $id = $params['id'] ?? '1';
            
            // 模拟获取特定历史记录 - 实际应该从数据库获取
            $history = [
                'id' => $id,
                'title' => '历史对话 #' . $id,
                'created_at' => date('Y-m-d H:i:s', time() - 7200),
                'updated_at' => date('Y-m-d H:i:s', time() - 3600),
                'messages' => [
                    [
                        'id' => '1',
                        'type' => 'user',
                        'content' => '你好',
                        'timestamp' => date('Y-m-d H:i:s', time() - 7200)
                    ],
                    [
                        'id' => '2',
                        'type' => 'assistant',
                        'content' => '您好！有什么我可以帮助您的吗？',
                        'timestamp' => date('Y-m-d H:i:s', time() - 7180)
                    ]
                ]
            ];

            return $this->success([
                'history' => $history,
                'message' => '历史记录获取成功'
            ]);

        } catch (\Exception $e) {
            return $this->error('获取历史记录失败: ' . $e->getMessage(), [], 500);
        }
    }    /**
     * 删除历史记录
     * DELETE /api/history/{id}
     */
    public function deleteHistory($requestData = null, $params = null)
    {
        try {
            // 从参数中获取ID
            $id = $params['id'] ?? '1';
            
            // 模拟删除操作 - 实际应该从数据库删除
            $deletedData = [
                'id' => $id,
                'deleted_at' => date('Y-m-d H:i:s'),
                'status' => 'deleted'
            ];

            // 记录操作日志
            error_log("History deleted: " . json_encode($deletedData));

            return $this->success([
                'data' => $deletedData,
                'message' => '历史记录删除成功'
            ]);

        } catch (\Exception $e) {
            return $this->error('删除历史记录失败: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * 清空历史记录
     * DELETE /api/history
     */
    public function clearHistory()
    {
        try {
            // 模拟清空操作 - 实际应该清空数据库相应记录
            $clearedData = [
                'cleared_at' => date('Y-m-d H:i:s'),
                'status' => 'cleared',
                'message' => '所有历史记录已清空'
            ];

            // 记录操作日志
            error_log("History cleared: " . json_encode($clearedData));

            return $this->success([
                'data' => $clearedData,
                'message' => '历史记录清空成功'
            ]);

        } catch (\Exception $e) {
            return $this->error('清空历史记录失败: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * 搜索历史记录
     * GET /api/history/search
     */
    public function searchHistory()
    {
        try {
            $query = $_GET['q'] ?? '';
            $limit = intval($_GET['limit'] ?? 20);
            $offset = intval($_GET['offset'] ?? 0);

            if (empty($query)) {
                return $this->error('搜索关键词不能为空', [], 400);
            }

            // 模拟搜索结果 - 实际应该从数据库搜索
            $results = [
                [
                    'id' => '1',
                    'title' => 'AI技术讨论',
                    'content' => '人工智能技术的应用场景...',
                    'timestamp' => date('Y-m-d H:i:s', time() - 3600),
                    'relevance' => 0.95
                ],
                [
                    'id' => '2',
                    'title' => '机器学习入门',
                    'content' => '机器学习的基本概念和原理...',
                    'timestamp' => date('Y-m-d H:i:s', time() - 7200),
                    'relevance' => 0.87
                ]
            ];

            // 应用分页
            $paginatedResults = array_slice($results, $offset, $limit);

            return $this->success([
                'results' => $paginatedResults,
                'total' => count($results),
                'query' => $query,
                'limit' => $limit,
                'offset' => $offset,
                'message' => '搜索完成'
            ]);

        } catch (\Exception $e) {
            return $this->error('搜索历史记录失败: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * 导出历史记录
     * GET /api/history/export
     */
    public function exportHistory()
    {
        try {
            $format = $_GET['format'] ?? 'json';
            $sessionId = $_GET['session_id'] ?? null;

            // 模拟导出数据 - 实际应该从数据库获取
            $exportData = [
                'export_info' => [
                    'format' => $format,
                    'session_id' => $sessionId,
                    'exported_at' => date('Y-m-d H:i:s'),
                    'total_messages' => 4
                ],
                'data' => [
                    [
                        'type' => 'user',
                        'content' => '你好，我想了解AI技术。',
                        'timestamp' => date('Y-m-d H:i:s', time() - 3600)
                    ],
                    [
                        'type' => 'assistant',
                        'content' => '您好！我很乐意为您介绍AI技术...',
                        'timestamp' => date('Y-m-d H:i:s', time() - 3580)
                    ]
                ]
            ];

            return $this->success([
                'export' => $exportData,
                'message' => '历史记录导出成功'
            ]);

        } catch (\Exception $e) {
            return $this->error('导出历史记录失败: ' . $e->getMessage(), [], 500);
        }
    }
}
