<?php
/**
 * HTTP客户端工具类
 * 
 * @package AlingAi\Utils
 * @version 2.0.0
 * @author AlingAi Team
 * @created 2024-12-19
 */

declare(strict_types=1);

namespace AlingAi\Utils;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Monolog\Logger;

class HttpClient
{
    private Client $client;
    private Logger $logger;
    private array $defaultOptions;
    
    public function __construct(Logger $logger, array $options = [])
    {
        $this->logger = $logger;
        $this->defaultOptions = array_merge([
            'timeout' => 30,
            'connect_timeout' => 10,
            'verify' => false,
            'headers' => [
                'User-Agent' => 'AlingAi-Pro/2.0',
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ]
        ], $options);
        
        $this->client = new Client($this->defaultOptions);
    }
    
    /**
     * GET请求
     */
    public function get(string $url, array $options = []): array
    {
        return $this->request('GET', $url, $options);
    }
    
    /**
     * POST请求
     */
    public function post(string $url, array $data = [], array $options = []): array
    {
        if (!empty($data)) {
            $options['json'] = $data;
        }
        return $this->request('POST', $url, $options);
    }
    
    /**
     * PUT请求
     */
    public function put(string $url, array $data = [], array $options = []): array
    {
        if (!empty($data)) {
            $options['json'] = $data;
        }
        return $this->request('PUT', $url, $options);
    }
    
    /**
     * DELETE请求
     */
    public function delete(string $url, array $options = []): array
    {
        return $this->request('DELETE', $url, $options);
    }
    
    /**
     * 通用请求方法
     */
    public function request(string $method, string $url, array $options = []): array
    {
        try {
            $startTime = microtime(true);
            
            $this->logger->info('HTTP请求开始', [
                'method' => $method,
                'url' => $url,
                'options' => $options
            ]);
            
            $response = $this->client->request($method, $url, $options);
            $body = $response->getBody()->getContents();
            $data = json_decode($body, true) ?: [];
            
            $duration = microtime(true) - $startTime;
            
            $this->logger->info('HTTP请求成功', [
                'method' => $method,
                'url' => $url,
                'status_code' => $response->getStatusCode(),
                'duration' => round($duration, 3)
            ]);
            
            return [
                'success' => true,
                'status_code' => $response->getStatusCode(),
                'headers' => $response->getHeaders(),
                'data' => $data,
                'raw_body' => $body,
                'duration' => $duration
            ];
            
        } catch (RequestException $e) {
            $duration = microtime(true) - $startTime;
            
            $this->logger->error('HTTP请求失败', [
                'method' => $method,
                'url' => $url,
                'error' => $e->getMessage(),
                'duration' => round($duration, 3)
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status_code' => $e->getResponse() ? $e->getResponse()->getStatusCode() : 0,
                'duration' => $duration
            ];
        }
    }
    
    /**
     * 下载文件
     */
    public function download(string $url, string $destination, array $options = []): bool
    {
        try {
            $options['sink'] = $destination;
            $response = $this->client->get($url, $options);
            
            $this->logger->info('文件下载成功', [
                'url' => $url,
                'destination' => $destination,
                'size' => filesize($destination)
            ]);
            
            return $response->getStatusCode() === 200;
            
        } catch (RequestException $e) {
            $this->logger->error('文件下载失败', [
                'url' => $url,
                'destination' => $destination,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
    
    /**
     * 上传文件
     */
    public function upload(string $url, string $filePath, array $options = []): array
    {
        try {
            if (!file_exists($filePath)) {
                throw new \InvalidArgumentException("文件不存在: {$filePath}");
            }
            
            $options['multipart'] = [
                [
                    'name' => 'file',
                    'contents' => fopen($filePath, 'r'),
                    'filename' => basename($filePath)
                ]
            ];
            
            return $this->request('POST', $url, $options);
            
        } catch (\Exception $e) {
            $this->logger->error('文件上传失败', [
                'url' => $url,
                'file' => $filePath,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 设置默认头部
     */
    public function setDefaultHeaders(array $headers): void
    {
        $this->defaultOptions['headers'] = array_merge($this->defaultOptions['headers'], $headers);
        $this->client = new Client($this->defaultOptions);
    }
    
    /**
     * 设置认证头部
     */
    public function setAuth(string $token, string $type = 'Bearer'): void
    {
        $this->setDefaultHeaders([
            'Authorization' => "{$type} {$token}"
        ]);
    }
    
    /**
     * 批量请求
     */
    public function batch(array $requests): array
    {
        $results = [];
        
        foreach ($requests as $key => $request) {
            $method = $request['method'] ?? 'GET';
            $url = $request['url'] ?? '';
            $options = $request['options'] ?? [];
            
            $results[$key] = $this->request($method, $url, $options);
        }
        
        return $results;
    }
    
    /**
     * 检查URL是否可访问
     */
    public function ping(string $url, int $timeout = 5): bool
    {
        try {
            $response = $this->client->head($url, ['timeout' => $timeout]);
            return $response->getStatusCode() < 400;
        } catch (RequestException $e) {
            return false;
        }
    }
    
    /**
     * 获取客户端实例
     */
    public function getClient(): Client
    {
        return $this->client;
    }
}
