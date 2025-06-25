<?php
/**
 * API??????
 */

echo "=================================================\n";
echo "AlingAi Pro API??????\n";
echo "=================================================\n\n";

$baseUrl = "http://localhost:8000";
$endpoints = [
    "/api/info",
    "/api/v1/system/info",
    "/health",
    "/api/v2/enhanced/dashboard",
    "/api/v2/agents/system/status"
];

foreach ($endpoints as $endpoint) {
    echo "????: {$endpoint}\n";
    
    $url = $baseUrl . $endpoint;
    
    $context = stream_context_create([
        "http" => [
            "ignore_errors" => true,
            "timeout" => 10
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    $httpCode = 0;
    
    if (isset($http_response_header)) {
        foreach ($http_response_header as $header) {
            if (preg_match("/^HTTP\/\d+\.\d+\s+(\d+)/", $header, $matches)) {
                $httpCode = intval($matches[1]);
                break;
            }
        }
    }
    
    echo "  ???: {$httpCode}\n";
    
    if ($httpCode == 200) {
        $data = json_decode($response, true);
        if ($data) {
            echo "  ??: " . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        } else {
            echo "  ??: {$response}\n";
        }
    } else {
        echo "  ??: ??????\n";
        if ($response) {
            echo "  ??: {$response}\n";
        }
    }
    
    echo "\n";
}

echo "????!\n";