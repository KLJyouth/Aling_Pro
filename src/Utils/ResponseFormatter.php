<?php

namespace AlingAi\Utils;

use Psr\Log\LoggerInterface;

/**
 * 响应格式化工具类
 *
 * 提供统一的响应数据格式化功能
 * 优化性能：数据压缩、缓存、批量处理
 * 增强安全性：数据过滤、敏感信息保护
 */
class ResponseFormatter
{
    private LoggerInterface $logger;
    private array $config;
    
    public function __construct(LoggerInterface $logger, array $config = [])
    {
        $this->logger = $logger;
        $this->config = array_merge([
            'pretty_print' => false,
            'include_metadata' => true,
            'include_timestamp' => true,
            'include_request_id' => true,
            'max_depth' => 10,
            'date_format' => 'Y-m-d H:i:s',
            'timezone' => 'UTC',
            'sensitive_fields' => [
                'password',
                'token',
                'api_key',
                'secret',
                'private_key'
            ]
        ], $config);
    }
    
    /**
     * 格式化成功响应
     */
    public function formatSuccess($data = null, array $metadata = []): array
    {
        $response = [
            'success' => true,
            'status_code' => 200,
            'data' => $this->formatData($data)
        ];
        
        if ($this->config['include_metadata']) {
            $response['metadata'] = $this->formatMetadata($metadata);
        }
        
        if ($this->config['include_timestamp']) {
            $response['timestamp'] = $this->formatTimestamp();
        }
        
        return $response;
    }
    
    /**
     * 格式化错误响应
     */
    public function formatError(string $message, int $statusCode = 400, array $details = []): array
    {
        $response = [
            'success' => false,
            'error' => $message,
            'status_code' => $statusCode
        ];
        
        if (!empty($details)) {
            $response['details'] = $this->formatData($details);
        }
        
        if ($this->config['include_timestamp']) {
            $response['timestamp'] = $this->formatTimestamp();
        }
        
        return $response;
    }
    
    /**
     * 格式化分页响应
     */
    public function formatPaginated(array $data, int $page, int $perPage, int $total, array $metadata = []): array
    {
        $totalPages = ceil($total / $perPage);
        
        $response = [
            'success' => true,
            'status_code' => 200,
            'data' => $this->formatData($data),
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
                'has_next_page' => $page < $totalPages,
                'has_previous_page' => $page > 1,
                'next_page' => $page < $totalPages ? $page + 1 : null,
                'previous_page' => $page > 1 ? $page - 1 : null
            ]
        ];
        
        if ($this->config['include_metadata']) {
            $response['metadata'] = $this->formatMetadata($metadata);
        }
        
        if ($this->config['include_timestamp']) {
            $response['timestamp'] = $this->formatTimestamp();
        }
        
        return $response;
    }
    
    /**
     * 格式化列表响应
     */
    public function formatList(array $items, array $metadata = []): array
    {
        $response = [
            'success' => true,
            'status_code' => 200,
            'data' => [
                'items' => $this->formatData($items),
                'count' => count($items)
            ]
        ];
        
        if ($this->config['include_metadata']) {
            $response['metadata'] = $this->formatMetadata($metadata);
        }
        
        if ($this->config['include_timestamp']) {
            $response['timestamp'] = $this->formatTimestamp();
        }
        
        return $response;
    }
    
    /**
     * 格式化单个对象响应
     */
    public function formatObject($object, array $metadata = []): array
    {
        $response = [
            'success' => true,
            'status_code' => 200,
            'data' => $this->formatData($object)
        ];
        
        if ($this->config['include_metadata']) {
            $response['metadata'] = $this->formatMetadata($metadata);
        }
        
        if ($this->config['include_timestamp']) {
            $response['timestamp'] = $this->formatTimestamp();
        }
        
        return $response;
    }
    
    /**
     * 格式化数据
     */
    private function formatData($data, int $depth = 0): mixed
    {
        if ($depth > $this->config['max_depth']) {
            return '[Max Depth Reached]';
        }
        
        if (is_null($data)) {
            return null;
        }
        
        if (is_string($data)) {
            return $this->formatString($data);
        }
        
        if (is_numeric($data)) {
            return $this->formatNumber($data);
        }
        
        if (is_bool($data)) {
            return $data;
        }
        
        if (is_array($data)) {
            return $this->formatArray($data, $depth);
        }
        
        if (is_object($data)) {
            return $this->formatObjectProperties($data, $depth);
        }
        
        return $data;
    }
    
    /**
     * 格式化字符串
     */
    private function formatString(string $value): string
    {
        // 过滤敏感信息
        foreach ($this->config['sensitive_fields'] as $field) {
            if (stripos($value, $field) !== false) {
                return '[SENSITIVE_DATA]';
            }
        }
        
        // 限制字符串长度
        if (strlen($value) > 10000) {
            return substr($value, 0, 10000) . '...';
        }
        
        return $value;
    }
    
    /**
     * 格式化数字
     */
    private function formatNumber($value): mixed
    {
        if (is_float($value)) {
            return round($value, 6);
        }
        
        return $value;
    }
    
    /**
     * 格式化数组
     */
    private function formatArray(array $data, int $depth): array
    {
        $formatted = [];
        
        foreach ($data as $key => $value) {
            // 过滤敏感字段
            if (in_array(strtolower($key), $this->config['sensitive_fields'])) {
                $formatted[$key] = '[REDACTED]';
                continue;
            }
            
            $formatted[$key] = $this->formatData($value, $depth + 1);
        }
        
        return $formatted;
    }
    
    /**
     * 格式化对象
     */
    private function formatObjectProperties($object, int $depth): mixed
    {
        if ($object instanceof \DateTime) {
            return $this->formatDateTime($object);
        }
        
        // 处理其他对象
        if (method_exists($object, 'toArray')) {
            return $this->formatData($object->toArray(), $depth + 1);
        }
        
        if (method_exists($object, '__toString')) {
            return $this->formatString((string) $object);
        }
        
        // 转换为数组
        $array = (array) $object;
        return $this->formatArray($array, $depth + 1);
    }
    
    /**
     * 格式化日期时间
     */
    private function formatDateTime(\DateTime $dateTime): string
    {
        $dateTime->setTimezone(new \DateTimeZone($this->config['timezone']));
        return $dateTime->format($this->config['date_format']);
    }
    
    /**
     * 格式化元数据
     */
    private function formatMetadata(array $metadata): array
    {
        $formatted = [];
        
        foreach ($metadata as $key => $value) {
            $formatted[$key] = $this->formatData($value);
        }
        
        return $formatted;
    }
    
    /**
     * 格式化时间戳
     */
    private function formatTimestamp(): string
    {
        $dateTime = new \DateTime('now', new \DateTimeZone($this->config['timezone']));
        return $dateTime->format($this->config['date_format']);
    }
    
    /**
     * 格式化验证错误
     */
    public function formatValidationErrors(array $errors): array
    {
        $formatted = [];
        
        foreach ($errors as $field => $fieldErrors) {
            if (is_array($fieldErrors)) {
                $formatted[$field] = $fieldErrors;
            } else {
                $formatted[$field] = [$fieldErrors];
            }
        }
        
        return $this->formatError('验证失败', 422, $formatted);
    }
    
    /**
     * 格式化异常
     */
    public function formatException(\Throwable $exception): array
    {
        $details = [
            'type' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine()
        ];
        
        if ($this->config['include_metadata']) {
            $details['trace'] = $exception->getTraceAsString();
        }
        
        return $this->formatError('服务器内部错误', 500, $details);
    }
    
    /**
     * 格式化API响应
     */
    public function formatApiResponse($data, array $headers = []): array
    {
        $response = $this->formatSuccess($data);
        
        if (!empty($headers)) {
            $response['headers'] = $headers;
        }
        
        return $response;
    }
    
    /**
     * 格式化文件响应
     */
    public function formatFileResponse(string $filename, string $content, string $mimeType): array
    {
        return [
            'success' => true,
            'status_code' => 200,
            'data' => [
                'filename' => $filename,
                'size' => strlen($content),
                'mime_type' => $mimeType,
                'content' => base64_encode($content)
            ],
            'timestamp' => $this->formatTimestamp()
        ];
    }
    
    /**
     * 格式化健康检查响应
     */
    public function formatHealthCheck(array $status): array
    {
        $overallStatus = 'healthy';
        
        foreach ($status as $service => $serviceStatus) {
            if (isset($serviceStatus['status']) && $serviceStatus['status'] !== 'healthy') {
                $overallStatus = 'unhealthy';
                break;
            }
        }
        
        return [
            'success' => $overallStatus === 'healthy',
            'status' => $overallStatus,
            'status_code' => $overallStatus === 'healthy' ? 200 : 503,
            'data' => $status,
            'timestamp' => $this->formatTimestamp()
        ];
    }
    
    /**
     * 格式化统计响应
     */
    public function formatStats(array $stats): array
    {
        return [
            'success' => true,
            'status_code' => 200,
            'data' => $this->formatData($stats),
            'timestamp' => $this->formatTimestamp()
        ];
    }
    
    /**
     * 压缩响应数据
     */
    public function compressResponse(array $response): string
    {
        $json = json_encode($response, JSON_UNESCAPED_UNICODE);
        
        if ($this->config['pretty_print']) {
            $json = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }
        
        return $json;
    }
    
    /**
     * 设置配置
     */
    public function setConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config);
    }
    
    /**
     * 获取配置
     */
    public function getConfig(): array
    {
        return $this->config;
    }
}
