<?php
/**
 * 完整API系统测试脚本
 * 测试所有API端点和量子加密功能
 */

require_once __DIR__ . '/vendor/autoload.php';

class CompleteAPISystemTester
{
    private string $baseUrl = 'http://localhost:8000';
    private array $testResults = [];
    private int $successCount = 0;
    private int $failureCount = 0;
    
    public function runCompleteTest(): void
    {
        echo "=== AlingAi Pro 完整API系统测试 ===\n\n";
        
        // 1. 测试基本API端点
        $this->testBasicEndpoints();
        
        // 2. 测试v1 API端点
        $this->testV1APIEndpoints();
        
        // 3. 测试v2 API端点
        $this->testV2APIEndpoints();
        
        // 4. 测试v3 API端点
        $this->testV3APIEndpoints();
        
        // 5. 测试管理API端点
        $this->testAdminAPIEndpoints();
        
        // 6. 测试安全API端点
        $this->testSecurityAPIEndpoints();
        
        // 7. 测试量子加密系统
        $this->testQuantumEncryption();
        
        // 8. 测试AI相关端点
        $this->testAIEndpoints();
        
        // 9. 测试系统端点
        $this->testSystemEndpoints();
        
        // 输出测试结果汇总
        $this->printTestSummary();
    }
    
    private function testBasicEndpoints(): void
    {
        echo "1. 测试基本API端点\n";
        echo "-------------------\n";
        
        $endpoints = [
            '/api/health',
            '/api/version',
            '/api/status',
            '/api/test'
        ];
        
        foreach ($endpoints as $endpoint) {
            $this->testEndpoint($endpoint, 'GET');
        }
        echo "\n";
    }
    
    private function testV1APIEndpoints(): void
    {
        echo "2. 测试V1 API端点\n";
        echo "------------------\n";
        
        $endpoints = [
            '/api/v1/auth/login',
            '/api/v1/auth/status',
            '/api/v1/users/profile',
            '/api/v1/users/list',
            '/api/v1/system/info',
            '/api/v1/system/health',
            '/api/v1/security/overview',
            '/api/v1/ai/agents',
            '/api/v1/blockchain/wallet'
        ];
        
        foreach ($endpoints as $endpoint) {
            $method = (strpos($endpoint, 'login') !== false) ? 'POST' : 'GET';
            $this->testEndpoint($endpoint, $method);
        }
        echo "\n";
    }
    
    private function testV2APIEndpoints(): void
    {
        echo "3. 测试V2 API端点\n";
        echo "------------------\n";
        
        $endpoints = [
            '/api/v2/enhanced/dashboard',
            '/api/v2/enhanced/analytics',
            '/api/v2/admin/system/status',
            '/api/v2/admin/users/manage',
            '/api/v2/security/quantum/status',
            '/api/v2/ai/advanced/query',
            '/api/v2/cache/management'
        ];
        
        foreach ($endpoints as $endpoint) {
            $this->testEndpoint($endpoint, 'GET');
        }
        echo "\n";
    }
    
    private function testV3APIEndpoints(): void
    {
        echo "4. 测试V3 API端点\n";
        echo "------------------\n";
        
        $endpoints = [
            '/api/v3/next-gen/interface',
            '/api/v3/next-gen/features',
            '/api/v3/quantum/advanced',
            '/api/v3/ai/neural/network',
            '/api/v3/blockchain/smart-contracts'
        ];
        
        foreach ($endpoints as $endpoint) {
            $this->testEndpoint($endpoint, 'GET');
        }
        echo "\n";
    }
    
    private function testAdminAPIEndpoints(): void
    {
        echo "5. 测试管理API端点\n";
        echo "-------------------\n";
        
        $endpoints = [
            '/api/admin/dashboard',
            '/api/admin/users',
            '/api/admin/system',
            '/api/admin/logs'
        ];
        
        foreach ($endpoints as $endpoint) {
            $this->testEndpoint($endpoint, 'GET');
        }
        echo "\n";
    }
    
    private function testSecurityAPIEndpoints(): void
    {
        echo "6. 测试安全API端点\n";
        echo "-------------------\n";
        
        $endpoints = [
            '/api/security/status',
            '/api/security/scan',
            '/api/security/threats'
        ];
        
        foreach ($endpoints as $endpoint) {
            $this->testEndpoint($endpoint, 'GET');
        }
        echo "\n";
    }
    
    private function testQuantumEncryption(): void
    {
        echo "7. 测试量子加密系统\n";
        echo "--------------------\n";
        
        $endpoints = [
            '/api/quantum/status',
            '/api/quantum/random',
            '/api/quantum/encrypt',
            '/api/quantum/decrypt'
        ];
        
        foreach ($endpoints as $endpoint) {
            $method = (strpos($endpoint, 'encrypt') !== false || strpos($endpoint, 'decrypt') !== false) ? 'POST' : 'GET';
            $this->testEndpoint($endpoint, $method);
        }
        echo "\n";
    }
    
    private function testAIEndpoints(): void
    {
        echo "8. 测试AI相关端点\n";
        echo "------------------\n";
        
        $endpoints = [
            '/api/ai/chat',
            '/api/ai/agents',
            '/api/ai/models'
        ];
        
        foreach ($endpoints as $endpoint) {
            $this->testEndpoint($endpoint, 'GET');
        }
        echo "\n";
    }
    
    private function testSystemEndpoints(): void
    {
        echo "9. 测试系统端点\n";
        echo "----------------\n";
        
        $endpoints = [
            '/api/system/metrics',
            '/api/system/performance',
            '/api/system/cache'
        ];
        
        foreach ($endpoints as $endpoint) {
            $this->testEndpoint($endpoint, 'GET');
        }
        echo "\n";
    }
    
    private function testEndpoint(string $endpoint, string $method = 'GET'): void
    {
        $url = $this->baseUrl . $endpoint;
        $startTime = microtime(true);
        
        try {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'X-API-Version: v6.0',
                    'X-API-Timestamp: ' . time(),
                    'X-API-Nonce: ' . bin2hex(random_bytes(16))
                ]
            ]);
            
            if ($method === 'POST') {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['test' => 'data']));
            }
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);
            curl_close($ch);
            
            $status = ($httpCode >= 200 && $httpCode < 400) ? 'SUCCESS' : 'FAILURE';
            $statusColor = ($status === 'SUCCESS') ? "\033[32m" : "\033[31m";
            $resetColor = "\033[0m";
            
            if ($status === 'SUCCESS') {
                $this->successCount++;
            } else {
                $this->failureCount++;
            }
            
            echo sprintf(
                "  %s%-7s%s %s %s (%dms) [HTTP %d]\n",
                $statusColor,
                $status,
                $resetColor,
                $method,
                $endpoint,
                $responseTime,
                $httpCode
            );
            
            // 记录详细结果
            $this->testResults[] = [
                'endpoint' => $endpoint,
                'method' => $method,
                'status' => $status,
                'http_code' => $httpCode,
                'response_time' => $responseTime,
                'response_length' => strlen($response ?? ''),
                'has_json_response' => json_decode($response) !== null
            ];
            
        } catch (Exception $e) {
            $this->failureCount++;
            echo sprintf(
                "  \033[31mERROR\033[0m   %s %s - %s\n",
                $method,
                $endpoint,
                $e->getMessage()
            );
        }
    }
    
    private function printTestSummary(): void
    {
        echo "=== 测试结果汇总 ===\n";
        echo "总测试数: " . ($this->successCount + $this->failureCount) . "\n";
        echo "\033[32m成功: {$this->successCount}\033[0m\n";
        echo "\033[31m失败: {$this->failureCount}\033[0m\n";
        
        $successRate = ($this->successCount + $this->failureCount) > 0 ? 
            round(($this->successCount / ($this->successCount + $this->failureCount)) * 100, 1) : 0;
        echo "成功率: {$successRate}%\n\n";
        
        // 显示响应时间统计
        $responseTimes = array_column($this->testResults, 'response_time');
        if (!empty($responseTimes)) {
            echo "响应时间统计:\n";
            echo "  平均: " . round(array_sum($responseTimes) / count($responseTimes), 2) . "ms\n";
            echo "  最小: " . min($responseTimes) . "ms\n";
            echo "  最大: " . max($responseTimes) . "ms\n\n";
        }
        
        // 显示JSON响应统计
        $jsonResponses = array_filter($this->testResults, function($result) {
            return $result['has_json_response'];
        });
        echo "JSON格式响应: " . count($jsonResponses) . "/" . count($this->testResults) . "\n\n";
        
        echo "测试完成! 🚀\n";
    }
}

// 运行测试
$tester = new CompleteAPISystemTester();
$tester->runCompleteTest();
