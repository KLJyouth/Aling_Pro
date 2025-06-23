<?php

declare(strict_types=1);

namespace AlingAi\Pro\Testing;

use PHPUnit\Framework\TestCase;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use AlingAi\Pro\Core\ErrorHandler;
use AlingAi\Pro\Monitoring\PerformanceMonitor;

/**
 * 基础测试类
 * 
 * 提供通用的测试方法和断言
 * 
 * @package AlingAi\Pro\Testing
 */
abstract class BaseTestCase extends TestCase
{
    protected array $testData = [];
    protected PerformanceMonitor $performanceMonitor;

    protected function setUp(): void
    {
        parent::setUp();
        
        // 初始化性能监控
        $this->performanceMonitor = new PerformanceMonitor(
            app('log'),
            ['enabled' => true]
        );
        
        // 清理测试环境
        $this->cleanupTestEnvironment();
        
        // 准备测试数据
        $this->prepareTestData();
    }

    protected function tearDown(): void
    {
        // 清理测试数据
        $this->cleanupTestData();
        
        parent::tearDown();
    }

    /**
     * 清理测试环境
     */
    protected function cleanupTestEnvironment(): void
    {
        Cache::flush();
        DB::beginTransaction();
    }

    /**
     * 准备测试数据
     */
    protected function prepareTestData(): void
    {
        // 子类可以重写此方法来准备特定的测试数据
    }

    /**
     * 清理测试数据
     */
    protected function cleanupTestData(): void
    {
        DB::rollBack();
    }

    /**
     * 断言 API 响应格式正确
     */
    protected function assertApiResponseFormat(TestResponse $response, int $expectedStatus = 200): void
    {
        $response->assertStatus($expectedStatus);
        $response->assertJsonStructure([
            'success',
            'data',
            'message',
        ]);
    }

    /**
     * 断言 API 成功响应
     */
    protected function assertApiSuccess(TestResponse $response, ?array $expectedData = null): void
    {
        $this->assertApiResponseFormat($response, 200);
        $response->assertJson(['success' => true]);
        
        if ($expectedData) {
            $response->assertJson(['data' => $expectedData]);
        }
    }

    /**
     * 断言 API 错误响应
     */
    protected function assertApiError(TestResponse $response, int $expectedStatus = 400): void
    {
        $this->assertApiResponseFormat($response, $expectedStatus);
        $response->assertJson(['success' => false]);
        $response->assertJsonHasKey('error');
    }

    /**
     * 断言分页响应格式
     */
    protected function assertPaginatedResponse(TestResponse $response): void
    {
        $response->assertJsonStructure([
            'success',
            'data' => [
                'items',
                'pagination' => [
                    'current_page',
                    'per_page',
                    'total',
                    'total_pages',
                ],
            ],
        ]);
    }

    /**
     * 断言数据库有记录
     */
    protected function assertDatabaseHasRecord(string $table, array $data): void
    {
        $this->assertDatabaseHas($table, $data);
    }

    /**
     * 断言数据库没有记录
     */
    protected function assertDatabaseMissingRecord(string $table, array $data): void
    {
        $this->assertDatabaseMissing($table, $data);
    }

    /**
     * 断言缓存存在
     */
    protected function assertCacheHas(string $key): void
    {
        $this->assertTrue(Cache::has($key), "Cache key '{$key}' does not exist");
    }

    /**
     * 断言缓存不存在
     */
    protected function assertCacheMissing(string $key): void
    {
        $this->assertFalse(Cache::has($key), "Cache key '{$key}' should not exist");
    }

    /**
     * 断言响应时间在可接受范围内
     */
    protected function assertResponseTimeAcceptable(float $responseTime, float $maxTime = 1.0): void
    {
        $this->assertLessThan(
            $maxTime,
            $responseTime,
            "Response time {$responseTime}s exceeds maximum {$maxTime}s"
        );
    }

    /**
     * 断言内存使用在可接受范围内
     */
    protected function assertMemoryUsageAcceptable(int $memoryUsage, int $maxMemory = 128 * 1024 * 1024): void
    {
        $this->assertLessThan(
            $maxMemory,
            $memoryUsage,
            "Memory usage {$memoryUsage} bytes exceeds maximum {$maxMemory} bytes"
        );
    }

    /**
     * 创建测试用户
     */
    protected function createTestUser(array $attributes = []): array
    {
        $defaultUser = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $userData = array_merge($defaultUser, $attributes);
        
        $userId = DB::table('users')->insertGetId($userData);
        $userData['id'] = $userId;
        
        return $userData;
    }

    /**
     * 创建测试 API 密钥
     */
    protected function createTestApiKey(int $userId): string
    {
        $apiKey = 'test_' . bin2hex(random_bytes(16));
        
        DB::table('api_keys')->insert([
            'user_id' => $userId,
            'key' => hash('sha256', $apiKey),
            'name' => 'Test API Key',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        
        return $apiKey;
    }

    /**
     * 模拟认证用户
     */
    protected function actingAsUser(array $user): void
    {
        // 设置认证用户上下文
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
    }

    /**
     * 发送带认证的 API 请求
     */
    protected function apiRequest(string $method, string $uri, array $data = [], array $headers = []): TestResponse
    {
        $headers = array_merge([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ], $headers);

        return $this->json($method, $uri, $data, $headers);
    }

    /**
     * 发送带 API 密钥的请求
     */
    protected function apiRequestWithKey(string $method, string $uri, string $apiKey, array $data = []): TestResponse
    {
        return $this->apiRequest($method, $uri, $data, [
            'X-API-Key' => $apiKey,
        ]);
    }

    /**
     * 生成测试文件
     */
    protected function createTestFile(string $content = 'test content', string $filename = 'test.txt'): string
    {
        $tempDir = sys_get_temp_dir();
        $filePath = $tempDir . DIRECTORY_SEPARATOR . $filename;
        
        file_put_contents($filePath, $content);
        
        return $filePath;
    }

    /**
     * 清理测试文件
     */
    protected function cleanupTestFile(string $filePath): void
    {
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    /**
     * 断言日志包含特定条目
     */
    protected function assertLogContains(string $level, string $message): void
    {
        // 这里需要根据实际的日志实现来检查
        // 可以检查日志文件或使用测试日志驱动
    }

    /**
     * 断言邮件已发送
     */
    protected function assertEmailSent(string $to, string $subject): void
    {
        // 这里需要根据实际的邮件实现来检查
        // 可以使用测试邮件驱动
    }

    /**
     * 断言队列任务已派发
     */
    protected function assertJobDispatched(string $jobClass): void
    {
        // 这里需要根据实际的队列实现来检查
    }

    /**
     * 模拟网络请求
     */
    protected function mockHttpRequest(string $url, array $response): void
    {
        // 这里可以使用 HTTP 客户端的模拟功能
    }

    /**
     * 性能测试助手
     */
    protected function measurePerformance(callable $callback): array
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        
        $result = $callback();
        
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        
        return [
            'result' => $result,
            'execution_time' => $endTime - $startTime,
            'memory_used' => $endMemory - $startMemory,
            'peak_memory' => memory_get_peak_usage(true),
        ];
    }

    /**
     * 负载测试助手
     */
    protected function loadTest(callable $callback, int $iterations = 100): array
    {
        $results = [];
        $totalTime = 0;
        $totalMemory = 0;
        
        for ($i = 0; $i < $iterations; $i++) {
            $performance = $this->measurePerformance($callback);
            $results[] = $performance;
            $totalTime += $performance['execution_time'];
            $totalMemory += $performance['memory_used'];
        }
        
        return [
            'iterations' => $iterations,
            'total_time' => $totalTime,
            'average_time' => $totalTime / $iterations,
            'total_memory' => $totalMemory,
            'average_memory' => $totalMemory / $iterations,
            'min_time' => min(array_column($results, 'execution_time')),
            'max_time' => max(array_column($results, 'execution_time')),
            'results' => $results,
        ];
    }

    /**
     * 数据库事务测试助手
     */
    protected function testInTransaction(callable $callback): void
    {
        DB::transaction(function () use ($callback) {
            $callback();
            throw new \Exception('Rollback transaction for testing');
        });
    }

    /**
     * 并发测试助手
     */
    protected function concurrencyTest(callable $callback, int $processes = 5): array
    {
        // 这里需要实现多进程或多线程测试
        // 简化版本，顺序执行
        $results = [];
        
        for ($i = 0; $i < $processes; $i++) {
            $results[] = $this->measurePerformance($callback);
        }
        
        return $results;
    }
}
