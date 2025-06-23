<?php

namespace AlingAi\Controllers\Api;

use AlingAi\Core\Database\DatabaseManager;
use AlingAi\Core\Security\SecurityAnalyzer;
use Exception;
use PDO;

class SettingsController
{
    private $db;
    private $securityAnalyzer;

    public function __construct()
    {
        $this->db = DatabaseManager::getInstance()->getConnection();
        $this->securityAnalyzer = new SecurityAnalyzer();
    }

    /**
     * Handle GET requests to fetch system settings.
     * This endpoint provides public-safe configurations.
     * For full configuration access, proper authentication and authorization are required.
     */
    public function handleRequest()
    {
        // Basic security check for the request
        $securityResult = $this->securityAnalyzer->analyzeRequest();
        if (!$securityResult['safe']) {
            http_response_code(403);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['status' => 'error', 'message' => 'Security threat detected: ' . $securityResult['message']]);
            // In a real application, log this event.
            return;
        }

        // For now, we assume any authenticated user can view settings.
        // In a real scenario, you'd check for specific permissions.
        // For simplicity, we'll just check for a generic 'Authorization' header.
        if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
            http_response_code(401);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Authentication required.']);
            return;
        }

        // Add more robust authentication/authorization here (e.g., validate the token)
        
        try {
            $settings = $this->getSettings();
            http_response_code(200);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['status' => 'success', 'data' => $settings], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['status' => 'error', 'message' => 'An internal server error occurred.']);
            // In a real application, log the error message: $e->getMessage()
        }
    }

    /**
     * Fetches settings from the database and formats them into a nested array.
     *
     * @return array The formatted settings.
     * @throws Exception
     */
    private function getSettings()
    {
        $stmt = $this->db->query("SELECT setting_key, setting_value, setting_type FROM system_settings WHERE is_dynamic = 1");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $settings = [];
        foreach ($results as $row) {
            // Do not expose sensitive keys like 'app.key' to the API
            if ($row['setting_key'] === 'app.key') {
                continue;
            }

            $keys = explode('.', $row['setting_key']);
            $temp = &$settings;
            foreach ($keys as $key) {
                if (!isset($temp[$key])) {
                    $temp[$key] = [];
                }
                $temp = &$temp[$key];
            }
            $temp = $this->castValue($row['setting_value'], $row['setting_type']);
        }

        return $settings;
    }

    /**
     * Casts a value to its proper type based on the setting_type column.
     *
     * @param mixed $value The value from the database.
     * @param string $type The target type.
     * @return mixed The casted value.
     */
    private function castValue($value, $type)
    {
        switch ($type) {
            case 'integer':
                return (int)$value;
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'json':
                return json_decode($value, true);
            case 'string':
            default:
                return (string)$value;
        }
    }
} 