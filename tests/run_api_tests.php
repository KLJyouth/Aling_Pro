<?php
/**
 * AlingAi Pro - API Testing Suite
 * 
 * This script tests all API endpoints to ensure they're working correctly
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define constants
define('TEST_START_TIME', microtime(true));
define('BASE_URL', 'http://localhost:3000');
define('API_BASE', BASE_URL . '/api');

// Colors for console output
define('COLOR_GREEN', "\033[32m");
define('COLOR_RED', "\033[31m");
define('COLOR_YELLOW', "\033[33m");
define('COLOR_BLUE', "\033[34m");
define('COLOR_RESET', "\033[0m");

// Test results tracking
$totalTests = 0;
$passedTests = 0;
$failedTests = [];

/**
 * Make an HTTP request to an API endpoint
 * 
 * @param string $endpoint The API endpoint
 * @param string $method The HTTP method
 * @param array $data The data to send
 * @param array $headers Additional headers
 * @return array Response data
 */
function makeRequest($endpoint, $method = 'GET', $data = [], $headers = []) {
    global $totalTests;
    $totalTests++;
    
    $url = API_BASE . $endpoint;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    // Set method
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
    } else if ($method !== 'GET') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    }
    
    // Set data
    if (!empty($data)) {
        $jsonData = json_encode($data);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Content-Length: ' . strlen($jsonData);
    }
    
    // Set headers
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return [
            'success' => false,
            'error' => $error,
            'http_code' => $httpCode
        ];
    }
    
    $decodedResponse = json_decode($response, true);
    
    return [
        'success' => true,
        'data' => $decodedResponse,
        'raw' => $response,
        'http_code' => $httpCode
    ];
}

/**
 * Assert that a condition is true
 * 
 * @param bool $condition The condition to check
 * @param string $message The message to display if the condition is false
 * @return bool Whether the assertion passed
 */
function assert($condition, $message) {
    global $passedTests, $failedTests;
    
    if ($condition) {
        $passedTests++;
        return true;
    } else {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $caller = $trace[1]['function'] ?? 'Unknown';
        $failedTests[] = [
            'test' => $caller,
            'message' => $message
        ];
        return false;
    }
}

/**
 * Print a message to the console
 * 
 * @param string $message The message to print
 * @param string $color The color to use
 */
function printMessage($message, $color = COLOR_RESET) {
    echo $color . $message . COLOR_RESET . "\n";
}

/**
 * Run a test and report the result
 * 
 * @param string $name The name of the test
 * @param callable $testFunction The test function to run
 */
function runTest($name, $testFunction) {
    global $failedTests;
    
    $failedCountBefore = count($failedTests);
    
    printMessage("\n" . COLOR_BLUE . "Running test: " . $name . COLOR_RESET);
    
    try {
        $testFunction();
        
        if (count($failedTests) === $failedCountBefore) {
            printMessage("  " . COLOR_GREEN . "✓ PASSED" . COLOR_RESET);
        } else {
            printMessage("  " . COLOR_RED . "✗ FAILED" . COLOR_RESET);
            
            // Print the failure messages
            $newFailures = array_slice($failedTests, $failedCountBefore);
            foreach ($newFailures as $failure) {
                printMessage("    - " . $failure['message'], COLOR_RED);
            }
        }
    } catch (Exception $e) {
        printMessage("  " . COLOR_RED . "✗ EXCEPTION: " . $e->getMessage() . COLOR_RESET);
        $failedTests[] = [
            'test' => $name,
            'message' => "Exception: " . $e->getMessage()
        ];
    }
}

// Test authentication endpoints
function testAuthEndpoints() {
    // Test login endpoint
    $loginData = [
        'email' => 'test@example.com',
        'password' => 'password123'
    ];
    
    $response = makeRequest('/v1/auth/login', 'POST', $loginData);
    assert($response['success'], "Login request failed");
    assert($response['http_code'] === 200, "Login HTTP code should be 200, got " . $response['http_code']);
    assert(isset($response['data']['success']), "Login response should have 'success' field");
    assert($response['data']['success'] === true, "Login should be successful");
    assert(isset($response['data']['data']['token']), "Login response should contain token");
    
    // Store token for later use
    $token = $response['data']['data']['token'] ?? null;
    
    // Test register endpoint
    $registerData = [
        'email' => 'newuser_' . time() . '@example.com',
        'password' => 'password123',
        'username' => 'newuser_' . time()
    ];
    
    $response = makeRequest('/v1/auth/register', 'POST', $registerData);
    assert($response['success'], "Register request failed");
    assert($response['http_code'] === 200, "Register HTTP code should be 200, got " . $response['http_code']);
    assert(isset($response['data']['success']), "Register response should have 'success' field");
    assert($response['data']['success'] === true, "Register should be successful");
    
    return $token;
}

// Test user endpoints
function testUserEndpoints($token) {
    if (!$token) {
        assert(false, "No token available for user tests");
        return;
    }
    
    $headers = ['Authorization: Bearer ' . $token];
    
    // Test profile endpoint
    $response = makeRequest('/v1/user/profile', 'GET', [], $headers);
    assert($response['success'], "Profile request failed");
    assert($response['http_code'] === 200, "Profile HTTP code should be 200, got " . $response['http_code']);
    assert(isset($response['data']['success']), "Profile response should have 'success' field");
    assert($response['data']['success'] === true, "Profile request should be successful");
    assert(isset($response['data']['data']['id']), "Profile response should contain user ID");
    
    // Test quota endpoint
    $response = makeRequest('/v1/user/quota', 'GET', [], $headers);
    assert($response['success'], "Quota request failed");
    assert($response['http_code'] === 200, "Quota HTTP code should be 200, got " . $response['http_code']);
    assert(isset($response['data']['success']), "Quota response should have 'success' field");
    assert($response['data']['success'] === true, "Quota request should be successful");
    assert(isset($response['data']['data']['daily_limit']), "Quota response should contain daily_limit");
}

// Test chat endpoints
function testChatEndpoints($token) {
    if (!$token) {
        assert(false, "No token available for chat tests");
        return;
    }
    
    $headers = ['Authorization: Bearer ' . $token];
    
    // Test message endpoint
    $messageData = [
        'message' => 'Hello, this is a test message'
    ];
    
    $response = makeRequest('/v1/chat/message', 'POST', $messageData, $headers);
    assert($response['success'], "Chat message request failed");
    assert($response['http_code'] === 200, "Chat message HTTP code should be 200, got " . $response['http_code']);
    assert(isset($response['data']['success']), "Chat message response should have 'success' field");
    assert($response['data']['success'] === true, "Chat message request should be successful");
    assert(isset($response['data']['data']['reply']), "Chat message response should contain reply");
    
    // Test history endpoint
    $response = makeRequest('/v1/chat/history', 'GET', [], $headers);
    assert($response['success'], "Chat history request failed");
    assert($response['http_code'] === 200, "Chat history HTTP code should be 200, got " . $response['http_code']);
    assert(isset($response['data']['success']), "Chat history response should have 'success' field");
    assert($response['data']['success'] === true, "Chat history request should be successful");
    assert(isset($response['data']['data']['messages']), "Chat history response should contain messages");
}

// Test enhanced chat endpoints (v2)
function testEnhancedChatEndpoints($token) {
    if (!$token) {
        assert(false, "No token available for enhanced chat tests");
        return;
    }
    
    $headers = ['Authorization: Bearer ' . $token];
    
    // Test enhanced message endpoint
    $messageData = [
        'message' => 'Hello, this is a test message for enhanced chat',
        'model' => 'gpt-4-turbo'
    ];
    
    $response = makeRequest('/v2/enhanced-chat/message', 'POST', $messageData, $headers);
    assert($response['success'], "Enhanced chat message request failed");
    assert($response['http_code'] === 200, "Enhanced chat message HTTP code should be 200, got " . $response['http_code']);
    assert(isset($response['data']['success']), "Enhanced chat message response should have 'success' field");
    assert($response['data']['success'] === true, "Enhanced chat message request should be successful");
    assert(isset($response['data']['data']['reply']), "Enhanced chat message response should contain reply");
    assert(isset($response['data']['data']['session_id']), "Enhanced chat message response should contain session_id");
    
    // Test sessions endpoint
    $response = makeRequest('/v2/enhanced-chat/sessions', 'GET', [], $headers);
    assert($response['success'], "Enhanced chat sessions request failed");
    assert($response['http_code'] === 200, "Enhanced chat sessions HTTP code should be 200, got " . $response['http_code']);
    assert(isset($response['data']['success']), "Enhanced chat sessions response should have 'success' field");
    assert($response['data']['success'] === true, "Enhanced chat sessions request should be successful");
    assert(isset($response['data']['data']['sessions']), "Enhanced chat sessions response should contain sessions");
    
    // Test creating a new session
    $sessionData = [
        'title' => 'Test Session ' . time()
    ];
    
    $response = makeRequest('/v2/enhanced-chat/sessions', 'POST', $sessionData, $headers);
    assert($response['success'], "Create session request failed");
    assert($response['http_code'] === 200, "Create session HTTP code should be 200, got " . $response['http_code']);
    assert(isset($response['data']['success']), "Create session response should have 'success' field");
    assert($response['data']['success'] === true, "Create session request should be successful");
    assert(isset($response['data']['data']['session']['id']), "Create session response should contain session ID");
}

// Test system endpoints
function testSystemEndpoints() {
    // Test info endpoint
    $response = makeRequest('/v1/system/info', 'GET');
    assert($response['success'], "System info request failed");
    assert($response['http_code'] === 200, "System info HTTP code should be 200, got " . $response['http_code']);
    assert(isset($response['data']['success']), "System info response should have 'success' field");
    assert($response['data']['success'] === true, "System info request should be successful");
    assert(isset($response['data']['data']['version']), "System info response should contain version");
    
    // Test status endpoint
    $response = makeRequest('/v1/system/status', 'GET');
    assert($response['success'], "System status request failed");
    assert($response['http_code'] === 200, "System status HTTP code should be 200, got " . $response['http_code']);
    assert(isset($response['data']['success']), "System status response should have 'success' field");
    assert($response['data']['success'] === true, "System status request should be successful");
    assert(isset($response['data']['data']['status']), "System status response should contain status");
}

// Run the tests
printMessage("\n" . COLOR_BLUE . "=== AlingAi Pro API Testing Suite ===" . COLOR_RESET);
printMessage("Starting tests at: " . date('Y-m-d H:i:s'));

// Run authentication tests first to get a token
$token = null;
runTest("Authentication Endpoints", function() use (&$token) {
    $token = testAuthEndpoints();
});

// Run other tests using the token
runTest("User Endpoints", function() use ($token) {
    testUserEndpoints($token);
});

runTest("Chat Endpoints", function() use ($token) {
    testChatEndpoints($token);
});

runTest("Enhanced Chat Endpoints", function() use ($token) {
    testEnhancedChatEndpoints($token);
});

runTest("System Endpoints", function() {
    testSystemEndpoints();
});

// Print test summary
$testDuration = round(microtime(true) - TEST_START_TIME, 2);
printMessage("\n" . COLOR_BLUE . "=== Test Summary ===" . COLOR_RESET);
printMessage("Total tests: $totalTests");
printMessage("Passed tests: " . COLOR_GREEN . $passedTests . COLOR_RESET);
printMessage("Failed tests: " . (count($failedTests) > 0 ? COLOR_RED : COLOR_GREEN) . count($failedTests) . COLOR_RESET);
printMessage("Duration: {$testDuration}s");

// Print failed tests if any
if (count($failedTests) > 0) {
    printMessage("\n" . COLOR_RED . "=== Failed Tests ===" . COLOR_RESET);
    foreach ($failedTests as $index => $failure) {
        printMessage(($index + 1) . ". " . $failure['test'] . ": " . $failure['message'], COLOR_RED);
    }
}

printMessage("\n" . COLOR_BLUE . "=== End of Test Suite ===" . COLOR_RESET);
