<?php
/**
 * AlingAi Pro - Authentication Unit Tests
 * 
 * This file contains unit tests for the authentication API endpoints
 */

use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
{
    /**
     * Test that the login endpoint returns a valid response
     */
    public function testLoginEndpoint()
    {
        // Mock request data
        \ = 'test@example.com';
        \ = 'password123';
        
        // Include the login endpoint file
        ob_start();
        \['REQUEST_METHOD'] = 'POST';
        \['HTTP_CONTENT_TYPE'] = 'application/json';
        
        // Mock the input data
        \ = json_encode([
            'email' => \,
            'password' => \
        ]);
        
        // Create a temporary input stream
        \ = fopen('php://temp', 'r+');
        fwrite(\, \);
        rewind(\);
        
        // Replace the standard input with our temporary stream
        \ = STDIN;
        define('STDIN', \);
        
        // Include the file with output buffering
        include_once __DIR__ . '/../../public/api/v1/auth/login.php';
        
        // Get the output
        \ = ob_get_clean();
        
        // Parse the JSON response
        \ = json_decode(\, true);
        
        // Assert that the response is valid
        \->assertIsArray(\);
        \->assertArrayHasKey('success', \);
        \->assertTrue(\['success']);
        \->assertArrayHasKey('data', \);
        \->assertArrayHasKey('token', \['data']);
        \->assertArrayHasKey('user', \['data']);
        \->assertEquals(\, \['data']['user']['email']);
    }
    
    /**
     * Test that the register endpoint returns a valid response
     */
    public function testRegisterEndpoint()
    {
        // Mock request data
        \ = 'newuser_' . time() . '@example.com';
        \ = 'password123';
        \ = 'newuser_' . time();
        
        // Include the register endpoint file
        ob_start();
        \['REQUEST_METHOD'] = 'POST';
        \['HTTP_CONTENT_TYPE'] = 'application/json';
        
        // Mock the input data
        \ = json_encode([
            'email' => \,
            'password' => \,
            'username' => \
        ]);
        
        // Create a temporary input stream
        \ = fopen('php://temp', 'r+');
        fwrite(\, \);
        rewind(\);
        
        // Replace the standard input with our temporary stream
        \ = STDIN;
        define('STDIN', \);
        
        // Include the file with output buffering
        include_once __DIR__ . '/../../public/api/v1/auth/register.php';
        
        // Get the output
        \ = ob_get_clean();
        
        // Parse the JSON response
        \ = json_decode(\, true);
        
        // Assert that the response is valid
        \->assertIsArray(\);
        \->assertArrayHasKey('success', \);
        \->assertTrue(\['success']);
        \->assertArrayHasKey('data', \);
        \->assertArrayHasKey('user_id', \['data']);
        \->assertArrayHasKey('username', \['data']);
        \->assertEquals(\, \['data']['username']);
        \->assertEquals(\, \['data']['email']);
    }
    
    /**
     * Test that the login endpoint rejects invalid requests
     */
    public function testLoginEndpointRejectsInvalidRequests()
    {
        // Include the login endpoint file
        ob_start();
        \['REQUEST_METHOD'] = 'GET';
        
        // Include the file with output buffering
        include_once __DIR__ . '/../../public/api/v1/auth/login.php';
        
        // Get the output
        \ = ob_get_clean();
        
        // Parse the JSON response
        \ = json_decode(\, true);
        
        // Assert that the response indicates failure
        \->assertIsArray(\);
        \->assertArrayHasKey('success', \);
        \->assertFalse(\['success']);
    }
    
    /**
     * Test that the register endpoint rejects invalid requests
     */
    public function testRegisterEndpointRejectsInvalidRequests()
    {
        // Include the register endpoint file
        ob_start();
        \['REQUEST_METHOD'] = 'GET';
        
        // Include the file with output buffering
        include_once __DIR__ . '/../../public/api/v1/auth/register.php';
        
        // Get the output
        \ = ob_get_clean();
        
        // Parse the JSON response
        \ = json_decode(\, true);
        
        // Assert that the response indicates failure
        \->assertIsArray(\);
        \->assertArrayHasKey('success', \);
        \->assertFalse(\['success']);
    }
}
