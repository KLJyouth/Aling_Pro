<?php
namespace AlingAi\Monitoring\Storage;

use PDO;
use PDOException;
use Psr\Log\LoggerInterface;

/**
 * TimescaleDB存储实现 - 使用TimescaleDB高效存储时序API监控数据
 */
class TimescaleDbStorage implements MetricsStorageInterface
{
    /**
     * @var PDO
     */
    private $pdo;
    
    /**
     * @var LoggerInterface
     */
    private $logger;
    
    /**
     * 构造函数
     */
    public function __construct(PDO $pdo, LoggerInterface $logger)
    {
        $this->pdo = $pdo;
        $this->logger = $logger;
    }

    /**
     * 初始化数据库表和索引
     */
    public function initialize(): bool
    {
        try {
            // 创建API调用指标表
            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS api_metrics (
                    time TIMESTAMPTZ NOT NULL,
                    api_name TEXT NOT NULL,
                    type TEXT NOT NULL,
                    duration DOUBLE PRECISION NOT NULL,
                    success BOOLEAN NOT NULL,
                    error_message TEXT,
                    status_code INTEGER,
                    tags JSONB
                );
                
                -- 将表转换为hypertable（如果尚未转换）
                SELECT create_hypertable('api_metrics', 'time', if_not_exists => TRUE);
                
                -- 创建索引
                CREATE INDEX IF NOT EXISTS idx_api_metrics_api_name ON api_metrics (api_name);
                CREATE INDEX IF NOT EXISTS idx_api_metrics_type ON api_metrics (type);
                CREATE INDEX IF NOT EXISTS idx_api_metrics_success ON api_metrics (success);
            ");
            
            // 创建API可用性指标表
            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS api_availability (
                    time TIMESTAMPTZ NOT NULL,
                    api_name TEXT NOT NULL,
                    type TEXT NOT NULL,
                    available BOOLEAN NOT NULL,
                    reason TEXT
                );
                
                -- 将表转换为hypertable（如果尚未转换）
                SELECT create_hypertable('api_availability', 'time', if_not_exists => TRUE);
                
                -- 创建索引
                CREATE INDEX IF NOT EXISTS idx_api_availability_api_name ON api_availability (api_name);
                CREATE INDEX IF NOT EXISTS idx_api_availability_type ON api_availability (type);
                CREATE INDEX IF NOT EXISTS idx_api_availability_available ON api_availability (available);
            ");
            
            return true;
        } catch (PDOException $e) {
            $this->logger->error("初始化TimescaleDB表失败", [
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function storeMetric(array $metric): bool
    {
        try {
            $sql = "INSERT INTO api_metrics (time, api_name, type, duration, success, error_message, status_code, tags) 
                    VALUES (to_timestamp(:timestamp), :api_name, :type, :duration, :success, :error_message, :status_code, :tags)";
            
            $stmt = $this->pdo->prepare($sql);
            
            $stmt->bindValue(':timestamp', $metric['timestamp'], PDO::PARAM_INT);
            $stmt->bindValue(':api_name', $metric['api_name'], PDO::PARAM_STR);
            $stmt->bindValue(':type', $metric['type'], PDO::PARAM_STR);
            $stmt->bindValue(':duration', $metric['duration'], PDO::PARAM_STR);
            $stmt->bindValue(':success', $metric['success'], PDO::PARAM_BOOL);
            $stmt->bindValue(':error_message', $metric['error_message'], PDO::PARAM_STR);
            $stmt->bindValue(':status_code', $metric['status_code'], PDO::PARAM_INT);
            $stmt->bindValue(':tags', json_encode($metric['tags'] ?? []), PDO::PARAM_STR);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            $this->logger->error("存储API指标失败", [
                'error' => $e->getMessage(),
                'metric' => $metric,
            ]);
            
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function storeAvailabilityMetric(array $metric): bool
    {
        try {
            $sql = "INSERT INTO api_availability (time, api_name, type, available, reason) 
                    VALUES (to_timestamp(:timestamp), :api_name, :type, :available, :reason)";
            
            $stmt = $this->pdo->prepare($sql);
            
            $stmt->bindValue(':timestamp', $metric['timestamp'], PDO::PARAM_INT);
            $stmt->bindValue(':api_name', $metric['api_name'], PDO::PARAM_STR);
            $stmt->bindValue(':type', $metric['type'], PDO::PARAM_STR);
            $stmt->bindValue(':available', $metric['available'], PDO::PARAM_BOOL);
            $stmt->bindValue(':reason', $metric['reason'], PDO::PARAM_STR);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            $this->logger->error("存储API可用性指标失败", [
                'error' => $e->getMessage(),
                'metric' => $metric,
            ]);
            
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getMetricsByTimeRange(?string $apiName = null, int $startTime = 0, ?int $endTime = null): array
    {
        try {
            $endTime = $endTime ?? time();
            
            $conditions = [];
            $params = [];
            
            $conditions[] = "time BETWEEN to_timestamp(:start_time) AND to_timestamp(:end_time)";
            $params[':start_time'] = $startTime;
            $params[':end_time'] = $endTime;
            
            if ($apiName !== null) {
                $conditions[] = "api_name = :api_name";
                $params[':api_name'] = $apiName;
            }
            
            $whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
            
            $sql = "SELECT 
                        EXTRACT(EPOCH FROM time) as timestamp,
                        api_name,
                        type,
                        duration,
                        success,
                        error_message,
                        status_code,
                        tags
                    FROM api_metrics
                    $whereClause
                    ORDER BY time DESC";
            
            $stmt = $this->pdo->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->logger->error("获取API指标失败", [
                'error' => $e->getMessage(),
                'api_name' => $apiName,
                'start_time' => $startTime,
                'end_time' => $endTime,
            ]);
            
            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getRecentMetrics(string $apiName, int $limit = 100): array
    {
        try {
            $sql = "SELECT 
                        EXTRACT(EPOCH FROM time) as timestamp,
                        api_name,
                        type,
                        duration,
                        success,
                        error_message,
                        status_code,
                        tags
                    FROM api_metrics
                    WHERE api_name = :api_name
                    ORDER BY time DESC
                    LIMIT :limit";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':api_name', $apiName, PDO::PARAM_STR);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->logger->error("获取最近API指标失败", [
                'error' => $e->getMessage(),
                'api_name' => $apiName,
                'limit' => $limit,
            ]);
            
            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAverageResponseTime(string $apiName, int $startTime = 0, ?int $endTime = null): ?float
    {
        try {
            $endTime = $endTime ?? time();
            
            $sql = "SELECT AVG(duration) as avg_duration
                    FROM api_metrics
                    WHERE api_name = :api_name
                      AND time BETWEEN to_timestamp(:start_time) AND to_timestamp(:end_time)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':api_name', $apiName, PDO::PARAM_STR);
            $stmt->bindValue(':start_time', $startTime, PDO::PARAM_INT);
            $stmt->bindValue(':end_time', $endTime, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result && $result['avg_duration'] !== null ? (float) $result['avg_duration'] : null;
        } catch (PDOException $e) {
            $this->logger->error("获取API平均响应时间失败", [
                'error' => $e->getMessage(),
                'api_name' => $apiName,
                'start_time' => $startTime,
                'end_time' => $endTime,
            ]);
            
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorRate(string $apiName, int $startTime = 0, ?int $endTime = null): ?float
    {
        try {
            $endTime = $endTime ?? time();
            
            $sql = "WITH api_stats AS (
                        SELECT
                            COUNT(*) as total_calls,
                            COUNT(*) FILTER (WHERE NOT success) as failed_calls
                        FROM api_metrics
                        WHERE api_name = :api_name
                          AND time BETWEEN to_timestamp(:start_time) AND to_timestamp(:end_time)
                    )
                    SELECT
                        CASE WHEN total_calls > 0 THEN failed_calls::float / total_calls ELSE 0 END as error_rate
                    FROM api_stats";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':api_name', $apiName, PDO::PARAM_STR);
            $stmt->bindValue(':start_time', $startTime, PDO::PARAM_INT);
            $stmt->bindValue(':end_time', $endTime, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ? (float) $result['error_rate'] : null;
        } catch (PDOException $e) {
            $this->logger->error("获取API错误率失败", [
                'error' => $e->getMessage(),
                'api_name' => $apiName,
                'start_time' => $startTime,
                'end_time' => $endTime,
            ]);
            
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailabilityPercentage(string $apiName, int $startTime = 0, ?int $endTime = null): ?float
    {
        try {
            $endTime = $endTime ?? time();
            
            $sql = "WITH availability_stats AS (
                        SELECT
                            COUNT(*) as total_checks,
                            COUNT(*) FILTER (WHERE available) as available_checks
                        FROM api_availability
                        WHERE api_name = :api_name
                          AND time BETWEEN to_timestamp(:start_time) AND to_timestamp(:end_time)
                    )
                    SELECT
                        CASE WHEN total_checks > 0 THEN available_checks::float / total_checks * 100 ELSE 0 END as availability
                    FROM availability_stats";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':api_name', $apiName, PDO::PARAM_STR);
            $stmt->bindValue(':start_time', $startTime, PDO::PARAM_INT);
            $stmt->bindValue(':end_time', $endTime, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ? (float) $result['availability'] : null;
        } catch (PDOException $e) {
            $this->logger->error("获取API可用性百分比失败", [
                'error' => $e->getMessage(),
                'api_name' => $apiName,
                'start_time' => $startTime,
                'end_time' => $endTime,
            ]);
            
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAllApiNames(): array
    {
        try {
            $sql = "SELECT DISTINCT api_name FROM api_metrics";
            $stmt = $this->pdo->query($sql);
            
            $result = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $result[] = $row['api_name'];
            }
            
            return $result;
        } catch (PDOException $e) {
            $this->logger->error("获取所有API名称失败", [
                'error' => $e->getMessage(),
            ]);
            
            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function cleanupOldData(int $maxAge): bool
    {
        try {
            $cutoffTime = time() - $maxAge;
            
            // 清理API指标数据
            $sql1 = "DELETE FROM api_metrics WHERE time < to_timestamp(:cutoff_time)";
            $stmt1 = $this->pdo->prepare($sql1);
            $stmt1->bindValue(':cutoff_time', $cutoffTime, PDO::PARAM_INT);
            $stmt1->execute();
            
            // 清理API可用性数据
            $sql2 = "DELETE FROM api_availability WHERE time < to_timestamp(:cutoff_time)";
            $stmt2 = $this->pdo->prepare($sql2);
            $stmt2->bindValue(':cutoff_time', $cutoffTime, PDO::PARAM_INT);
            $stmt2->execute();
            
            return true;
        } catch (PDOException $e) {
            $this->logger->error("清理旧数据失败", [
                'error' => $e->getMessage(),
                'max_age' => $maxAge,
            ]);
            
            return false;
        }
    }
} 