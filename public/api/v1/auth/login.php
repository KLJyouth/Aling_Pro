<?php
/**
 * AlingAi Pro API - Login Endpoint
 * 
 * Handles user authentication and returns a token if successful
 */

// Verify request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method Not Allowed',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

// Get request data
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($data['email']) || !isset($data['password'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Missing required login information',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

// Simple sanitization
$email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
$password = $data['password']; // In production, should validate password strength

// In a real implementation, we would:
// 1. Verify credentials against database
// 2. Generate a secure JWT token
// 3. Set refresh token if applicable
// 4. Log the login attempt

// For demo purposes, we'll just return a success response with fake token
$token = 'sample_token_' . time();

// Return the login response
http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => 'Login successful',
    'data' => [
        'token' => $token,
        'user' => [
            'id' => 1,
            'username' => 'demo_user',
            'email' => $email,
            'role' => 'user',
            'last_login' => date('Y-m-d H:i:s')
        ]
    ],
    'timestamp' => date('Y-m-d H:i:s')
]);
exit;
