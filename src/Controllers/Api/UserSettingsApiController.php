<?php

namespace AlingAi\Controllers\Api;

use AlingAi\Core\Database;
use AlingAi\Services\UserService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * 用户设置API控制器
 * 处理用户个人设置的CRUD操作，支持localStorage数据迁移
 */
class UserSettingsApiController
{
    private UserService $userService;
    private Database $database;

    public function __construct()
    {
        $this->userService = new UserService();
        $this->database = Database::getInstance();
    }

    /**
     * 获取用户所有设置
     */
    public function getSettings(Request $request, Response $response): Response
    {
        try {
            $userId = $this->getUserId($request);
            if (!$userId) {
                return $this->errorResponse($response, '用户未登录', 401);
            }

            $settings = $this->getUserSettings($userId);
            
            return $this->successResponse($response, [
                'settings' => $settings,
                'timestamp' => time()
            ]);

        } catch (\Exception $e) {
            error_log("获取用户设置失败: " . $e->getMessage());
            return $this->errorResponse($response, '获取设置失败', 500);
        }
    }

    /**
     * 更新用户设置
     */
    public function updateSettings(Request $request, Response $response): Response
    {
        try {
            $userId = $this->getUserId($request);
            if (!$userId) {
                return $this->errorResponse($response, '用户未登录', 401);
            }

            $data = json_decode($request->getBody()->getContents(), true);
            if (!$data) {
                return $this->errorResponse($response, '无效的请求数据', 400);
            }

            $updated = $this->updateUserSettings($userId, $data);
            
            if ($updated) {
                return $this->successResponse($response, [
                    'message' => '设置更新成功',
                    'updated_count' => count($data)
                ]);
            } else {
                return $this->errorResponse($response, '设置更新失败', 500);
            }

        } catch (\Exception $e) {
            error_log("更新用户设置失败: " . $e->getMessage());
            return $this->errorResponse($response, '更新设置失败', 500);
        }
    }

    /**
     * 批量导入localStorage数据
     */
    public function importLocalStorageData(Request $request, Response $response): Response
    {
        try {
            $userId = $this->getUserId($request);
            if (!$userId) {
                return $this->errorResponse($response, '用户未登录', 401);
            }

            $data = json_decode($request->getBody()->getContents(), true);
            if (!$data || !isset($data['localStorage'])) {
                return $this->errorResponse($response, '无效的localStorage数据', 400);
            }

            $importedCount = $this->importLocalStorage($userId, $data['localStorage']);
            
            return $this->successResponse($response, [
                'message' => 'localStorage数据导入成功',
                'imported_count' => $importedCount,
                'recommendation' => '建议清除浏览器localStorage以避免冲突'
            ]);

        } catch (\Exception $e) {
            error_log("导入localStorage数据失败: " . $e->getMessage());
            return $this->errorResponse($response, '数据导入失败', 500);
        }
    }

    /**
     * 获取特定分类的设置
     */
    public function getSettingsByCategory(Request $request, Response $response, array $args): Response
    {
        $userId = $this->getUserId($request);
        if ($userId === null) {
            return $this->errorResponse($response, '认证失败', 401);
        }

        $category = $args['category'] ?? null;
        if (!$category) {
            return $this->errorResponse($response, '缺少分类参数');
        }

        try {
            $settings = $this->fetchSettingsByCategory($userId, $category);
            return $this->successResponse($response, ['settings' => $settings]);
        } catch (\Exception $e) {
            // 在这里可以添加日志记录
            return $this->errorResponse($response, '获取设置失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 重置用户设置到默认值
     */
    public function resetSettings(Request $request, Response $response): Response
    {
        try {
            $userId = $this->getUserId($request);
            if (!$userId) {
                return $this->errorResponse($response, '用户未登录', 401);
            }

            $data = json_decode($request->getBody()->getContents(), true);
            $categories = $data['categories'] ?? ['all'];

            $resetCount = $this->resetUserSettings($userId, $categories);
            
            return $this->successResponse($response, [
                'message' => '设置重置成功',
                'reset_count' => $resetCount
            ]);

        } catch (\Exception $e) {
            error_log("重置用户设置失败: " . $e->getMessage());
            return $this->errorResponse($response, '重置设置失败', 500);
        }
    }

    /**
     * 导出用户设置数据
     */
    public function exportSettings(Request $request, Response $response): Response
    {
        try {
            $userId = $this->getUserId($request);
            if (!$userId) {
                return $this->errorResponse($response, '用户未登录', 401);
            }

            $settings = $this->getUserSettings($userId);
            $exportData = [
                'user_id' => $userId,
                'export_time' => date('Y-m-d H:i:s'),
                'settings' => $settings,
                'version' => '1.0'
            ];
            
            $response->getBody()->write(json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withHeader('Content-Disposition', 'attachment; filename="user_settings_' . $userId . '_' . date('Y_m_d') . '.json"');

        } catch (\Exception $e) {
            error_log("导出用户设置失败: " . $e->getMessage());
            return $this->errorResponse($response, '导出设置失败', 500);
        }
    }

    /**
     * 获取用户设置
     */
    private function getUserSettings(int $userId): array
    {
        $sql = "SELECT setting_key, setting_value, category FROM user_settings WHERE user_id = ? ORDER BY category, setting_key";
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$userId]);
        
        $settings = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $category = $row['category'];
            if (!isset($settings[$category])) {
                $settings[$category] = [];
            }
            $settings[$category][$row['setting_key']] = json_decode($row['setting_value'], true);
        }
        
        return $settings;
    }

    /**
     * 更新用户设置
     */
    private function updateUserSettings(int $userId, array $settings): bool
    {
        $this->database->beginTransaction();
        
        try {
            foreach ($settings as $category => $categorySettings) {
                foreach ($categorySettings as $key => $value) {
                    $this->upsertSetting($userId, $category, $key, $value);
                }
            }
            
            $this->database->commit();
            return true;
            
        } catch (\Exception $e) {
            $this->database->rollback();
            throw $e;
        }
    }

    /**
     * 插入或更新单个设置
     */
    private function upsertSetting(int $userId, string $category, string $key, $value): void
    {
        $sql = "INSERT INTO user_settings (user_id, category, setting_key, setting_value, updated_at) 
                VALUES (?, ?, ?, ?, NOW()) 
                ON DUPLICATE KEY UPDATE 
                setting_value = VALUES(setting_value), 
                updated_at = VALUES(updated_at)";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$userId, $category, $key, json_encode($value)]);
    }

    /**
     * 导入localStorage数据
     */
    private function importLocalStorage(int $userId, array $localStorageData): int
    {
        $importCount = 0;
        $mapping = $this->getLocalStorageMapping();
        
        foreach ($localStorageData as $lsKey => $lsValue) {
            if (isset($mapping[$lsKey])) {
                $config = $mapping[$lsKey];
                $value = $this->transformLocalStorageValue($lsValue, $config['type']);
                
                $this->upsertSetting($userId, $config['category'], $config['db_key'], $value);
                $importCount++;
            }
        }
        
        return $importCount;
    }

    /**
     * localStorage到数据库的映射配置
     */
    private function getLocalStorageMapping(): array
    {
        return [
            // 认证相关
            'token' => ['category' => 'auth', 'db_key' => 'last_token_hash', 'type' => 'string'],
            'guestMode' => ['category' => 'auth', 'db_key' => 'guest_mode_enabled', 'type' => 'boolean'],
            
            // 聊天设置
            'chatSettings' => ['category' => 'chat', 'db_key' => 'chat_preferences', 'type' => 'json'],
            'currentUser' => ['category' => 'chat', 'db_key' => 'current_user_info', 'type' => 'json'],
            'currentSessionId' => ['category' => 'chat', 'db_key' => 'last_session_id', 'type' => 'string'],
            'chatHistory' => ['category' => 'chat', 'db_key' => 'local_chat_history', 'type' => 'json'],
            'voiceInput' => ['category' => 'chat', 'db_key' => 'voice_input_enabled', 'type' => 'boolean'],
            'autoTTS' => ['category' => 'chat', 'db_key' => 'auto_tts_enabled', 'type' => 'boolean'],
            
            // 主题系统
            'theme' => ['category' => 'appearance', 'db_key' => 'theme_preference', 'type' => 'string'],
            'darkMode' => ['category' => 'appearance', 'db_key' => 'dark_mode_enabled', 'type' => 'boolean'],
            'customThemes' => ['category' => 'appearance', 'db_key' => 'custom_themes', 'type' => 'json'],
            
            // 辅助功能
            'accessibility-settings' => ['category' => 'accessibility', 'db_key' => 'accessibility_preferences', 'type' => 'json'],
            'fontSize' => ['category' => 'accessibility', 'db_key' => 'font_size', 'type' => 'string'],
            'highContrast' => ['category' => 'accessibility', 'db_key' => 'high_contrast_enabled', 'type' => 'boolean'],
            
            // 检测系统
            'detectionHistory' => ['category' => 'detection', 'db_key' => 'detection_history', 'type' => 'json'],
            'performanceBaseline' => ['category' => 'detection', 'db_key' => 'performance_baseline', 'type' => 'json'],
            'detectionSettings' => ['category' => 'detection', 'db_key' => 'detection_preferences', 'type' => 'json'],
            
            // 其他状态
            'language' => ['category' => 'general', 'db_key' => 'preferred_language', 'type' => 'string'],
            'timezone' => ['category' => 'general', 'db_key' => 'timezone', 'type' => 'string'],
        ];
    }

    /**
     * 转换localStorage值类型
     */
    private function transformLocalStorageValue($value, string $type)
    {
        switch ($type) {
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'json':
                return is_string($value) ? json_decode($value, true) : $value;
            case 'string':
            default:
                return (string)$value;
        }
    }

    /**
     * 获取分类设置
     */
    private function fetchSettingsByCategory(int $userId, string $category): array
    {
        $sql = "SELECT setting_key, setting_value FROM user_settings WHERE user_id = ? AND category = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$userId, $category]);
        
        $settings = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = json_decode($row['setting_value'], true);
        }
        
        return $settings;
    }

    /**
     * 重置用户设置
     */
    private function resetUserSettings(int $userId, array $categories): int
    {
        if (in_array('all', $categories)) {
            $sql = "DELETE FROM user_settings WHERE user_id = ?";
            $stmt = $this->database->prepare($sql);
            $stmt->execute([$userId]);
            return $stmt->rowCount();
        } else {
            $placeholders = str_repeat('?,', count($categories) - 1) . '?';
            $sql = "DELETE FROM user_settings WHERE user_id = ? AND category IN ($placeholders)";
            $stmt = $this->database->prepare($sql);
            $stmt->execute(array_merge([$userId], $categories));
            return $stmt->rowCount();
        }
    }

    /**
     * 获取用户ID
     */
    private function getUserId(Request $request): ?int
    {
        $authHeader = $request->getHeaderLine('Authorization');
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return null;
        }

        $token = substr($authHeader, 7);
        return $this->userService->getUserIdFromToken($token);
    }

    /**
     * 成功响应
     */
    private function successResponse(Response $response, array $data): Response
    {
        $response->getBody()->write(json_encode([
            'success' => true,
            'data' => $data
        ], JSON_UNESCAPED_UNICODE));
        
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * 错误响应
     */
    private function errorResponse(Response $response, string $message, int $code = 400): Response
    {
        $response->getBody()->write(json_encode([
            'success' => false,
            'error' => $message,
            'code' => $code
        ], JSON_UNESCAPED_UNICODE));
        
        return $response->withStatus($code)->withHeader('Content-Type', 'application/json');
    }
}
