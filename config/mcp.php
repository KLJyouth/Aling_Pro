<?php

return [
    /*
    |--------------------------------------------------------------------------
    | MCP 基础配置
    |--------------------------------------------------------------------------
    |
    | 这些配置用于连接管理控制平台(Management Control Platform)
    | 包括API基础URL、密钥等信息
    |
    */

    // MCP API 基础URL
    "base_url" => env("MCP_BASE_URL", "https://mcp.alingai.pro/api/v1"),

    // MCP API 密钥
    "api_key" => env("MCP_API_KEY", ""),

    // MCP API 密钥
    "api_secret" => env("MCP_API_SECRET", ""),

    // 是否记录所有API调用
    "log_all_calls" => env("MCP_LOG_ALL_CALLS", true),

    // 是否启用MCP功能
    "enabled" => env("MCP_ENABLED", true),

    /*
    |--------------------------------------------------------------------------
    | MCP 功能配置
    |--------------------------------------------------------------------------
    |
    | 这些配置用于控制MCP的各项功能
    |
    */

    // 系统监控配置
    "monitoring" => [
        // 是否启用系统监控
        "enabled" => env("MCP_MONITORING_ENABLED", true),
        
        // 监控数据上报间隔（秒）
        "report_interval" => env("MCP_MONITORING_INTERVAL", 300),
        
        // 监控的指标
        "metrics" => [
            "cpu_usage" => true,
            "memory_usage" => true,
            "disk_usage" => true,
            "network_traffic" => true,
            "active_users" => true,
            "api_requests" => true,
        ],
    ],

    // 系统维护配置
    "maintenance" => [
        // 是否允许远程维护
        "allow_remote" => env("MCP_ALLOW_REMOTE_MAINTENANCE", false),
        
        // 允许的维护任务
        "allowed_tasks" => [
            "clear_cache",
            "optimize_database",
            "backup_database",
            "update_system_settings",
        ],
    ],

    // 安全配置
    "security" => [
        // 是否启用安全监控
        "monitoring_enabled" => env("MCP_SECURITY_MONITORING_ENABLED", true),
        
        // 是否允许远程安全控制
        "allow_remote_control" => env("MCP_ALLOW_REMOTE_SECURITY_CONTROL", false),
        
        // 安全事件上报
        "report_security_events" => env("MCP_REPORT_SECURITY_EVENTS", true),
    ],
];
