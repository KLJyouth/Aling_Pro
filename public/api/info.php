<?php
header("Content-Type: application/json");

echo json_encode([
    "message" => "AlingAi Pro API",
    "current_version" => "v2",
    "available_versions" => ["v1", "v2"],
    "endpoints" => [
        "/api/v1/system/info" => "System information (v1)",
        "/api/v2/enhanced/dashboard" => "Enhanced dashboard (v2)",
        "/api/v1/security/overview" => "Security overview",
        "/api/v2/ai/agents" => "AI agents management"
    ]
]);