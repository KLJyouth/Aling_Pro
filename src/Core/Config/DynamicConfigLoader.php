<?php

namespace AlingAi\Core\Config;

use AlingAi\Core\Database\DatabaseManager;
use PDO;
use Exception;

/**
 * Class DynamicConfigLoader
 *
 * Loads configuration from the database and merges it with file-based configurations.
 */
class DynamicConfigLoader
{
    private $db;
    private static $settingsCache = null;

    public function __construct()
    {
        // Prevent direct instantiation in favor of static method
    }

    /**
     * Loads settings from the database.
     * Implements a simple static cache to avoid multiple DB queries per request.
     *
     * @return array The nested array of dynamic settings.
     */
    public static function load(): array
    {
        if (self::$settingsCache !== null) {
            return self::$settingsCache;
        }

        try {
            $db = DatabaseManager::getInstance()->getConnection();
            $stmt = $db->query("SELECT setting_key, setting_value, setting_type FROM system_settings");
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $settings = [];
            foreach ($results as $row) {
                $keys = explode('.', $row['setting_key']);
                $temp = &$settings;
                foreach ($keys as $key) {
                    if (!isset($temp[$key])) {
                        $temp[$key] = [];
                    }
                    $temp = &$temp[$key];
                }
                $temp = self::castValue($row['setting_value'], $row['setting_type']);
            }
            
            self::$settingsCache = $settings;
            return $settings;

        } catch (Exception $e) {
            // If the database is not available (e.g., during installation), return an empty array.
            // Log the error for debugging purposes.
            error_log("DynamicConfigLoader Error: Could not load settings from DB. " . $e->getMessage());
            return [];
        }
    }

    /**
     * Casts a value to its proper type based on the setting_type column.
     *
     * @param mixed $value The value from the database.
     * @param string $type The target type.
     * @return mixed The casted value.
     */
    private static function castValue($value, $type)
    {
        switch ($type) {
            case 'integer':
                return (int)$value;
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'json':
                // Fallback to original value if json is invalid
                $decoded = json_decode($value, true);
                return (json_last_error() === JSON_ERROR_NONE) ? $decoded : $value;
            case 'string':
            default:
                return (string)$value;
        }
    }
    
    /**
     * Merges the dynamic settings with a base configuration array.
     * The dynamic settings will override the base settings.
     *
     * @param array $baseConfig The base configuration array (e.g., from a file).
     * @return array The merged configuration.
     */
    public static function mergeWith(array $baseConfig): array
    {
        $dynamicConfig = self::load();
        // array_replace_recursive merges arrays, with values from the second array overwriting the first.
        return array_replace_recursive($baseConfig, $dynamicConfig);
    }
} 