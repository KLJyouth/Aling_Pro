<?php
/**
 * AlingAi Pro - User Registration API
 * 
 * Handles new user registration
 */

header('Content-Type: application/json'];

// Accept preflight requests for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *'];
    header('Access-Control-Allow-Methods: POST, OPTIONS'];
    header('Access-Control-Allow-Headers: Content-Type, Authorization'];
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405]; // Method Not Allowed
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Use POST.'
    ]];
    exit;
}

// Get JSON data
$json = file_get_contents('php://input'];
$data = json_decode($json, true];

// Validate input
if (!isset($data['username']) || !isset($data['email']) || !isset($data['password'])) {
    http_response_code(400];
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields'
    ]];
    exit;
}

// Extract data
$username = trim($data['username']];
$email = trim($data['email']];
$password = $data['password'];

// Basic validation
if (strlen($username) < 3) {
    http_response_code(400];
    echo json_encode([
        'success' => false,
        'message' => 'Username must be at least 3 characters long'
    ]];
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400];
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email address'
    ]];
    exit;
}

if (strlen($password) < 6) {
    http_response_code(400];
    echo json_encode([
        'success' => false,
        'message' => 'Password must be at least 6 characters long'
    ]];
    exit;
}

// In a real implementation, check if email is already in use
// For demo, we'll simulate this if the email is demo@alingai.com
if ($email === 'demo@alingai.com') {
    http_response_code(400];
    echo json_encode([
        'success' => false,
        'message' => 'Email address is already in use'
    ]];
    exit;
}

// Generate user ID (in a real app, this would be from the database)
$userId = time(];

// Return success with the new user ID and information
echo json_encode([
    'success' => true,
    'message' => 'Registration successful',
    'data' => [
        'user_id' => $userId,
        'username' => $username,
        'email' => $email,
        'created_at' => date('Y-m-d H:i:s')
    ]
]];
