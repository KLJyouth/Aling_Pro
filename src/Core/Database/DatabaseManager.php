<?php

declare(strict_types=1);

namespace AlingAi\Core\Database;

use PDO;
use PDOException;
use Psr\Log\LoggerInterface;

/**
 * 数据库管理器
 * 提供统一的数据库连接和操作接口
 */
class DatabaseManager
{
    private ?PDO $connection = null;
    private LoggerInterface $logger;
    private array $config;

    public function __construct(?LoggerInterface $logger = null, array $config = [])
    {
        $this->logger = $logger ?? $this->createDefaultLogger();
        $this->config = array_merge($this->getDefaultConfig(), $config);
    }

    /**
     * 获取数据库连接
     */
    public function getConnection(): PDO
    {
        if ($this->connection === null) {
            $this->connection = $this->createConnection();
        }

        return $this->connection;
    }

    /**
     * 创建数据库连接
     */
    private function createConnection(): PDO
    {
        try {
            $dsn = $this->buildDsn();
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];

            $connection = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                $options
            );

            $this->logger->info('数据库连接创建成功', [
                'host' => $this->config['host'],
                'database' => $this->config['database']
            ]);

            return $connection;

        } catch (PDOException $e) {
            $this->logger->error('数据库连接失败', [
                'error' => $e->getMessage(),
                'host' => $this->config['host'],
                'database' => $this->config['database']
            ]);
            throw $e;
        }
    }

    /**
     * 构建DSN字符串
     */
    private function buildDsn(): string
    {
        $host = $this->config['host'];
        $port = $this->config['port'];
        $database = $this->config['database'];
        $charset = $this->config['charset'];

        return "mysql:host={$host};port={$port};dbname={$database};charset={$charset}";
    }

    /**
     * 执行查询
     */
    public function query(string $sql, array $params = []): \PDOStatement
    {
        $connection = $this->getConnection();
        $stmt = $connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * 执行插入、更新、删除操作
     */
    public function execute(string $sql, array $params = []): int
    {
        $connection = $this->getConnection();
        $stmt = $connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * 获取最后插入的ID
     */
    public function lastInsertId(): string
    {
        return $this->getConnection()->lastInsertId();
    }

    /**
     * 开始事务
     */
    public function beginTransaction(): bool
    {
        return $this->getConnection()->beginTransaction();
    }

    /**
     * 提交事务
     */
    public function commit(): bool
    {
        return $this->getConnection()->commit();
    }

    /**
     * 回滚事务
     */
    public function rollback(): bool
    {
        return $this->getConnection()->rollback();
    }

    /**
     * 检查连接是否有效
     */
    public function isConnected(): bool
    {
        try {
            $this->getConnection()->query('SELECT 1');
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * 关闭连接
     */
    public function close(): void
    {
        $this->connection = null;
    }

    /**
     * 获取默认配置
     */
    private function getDefaultConfig(): array
    {
        return [
            'host' => getenv('DB_HOST') ?: 'localhost',
            'port' => getenv('DB_PORT') ?: 3306,
            'database' => getenv('DB_DATABASE') ?: 'alingai_pro',
            'username' => getenv('DB_USERNAME') ?: 'root',
            'password' => getenv('DB_PASSWORD') ?: '',
            'charset' => 'utf8mb4'
        ];
    }

    /**
     * 创建默认日志记录器
     */
    private function createDefaultLogger(): LoggerInterface
    {
        // 如果没有提供日志记录器，创建一个简单的实现
        return new class implements LoggerInterface {
            public function emergency($message, array $context = []): void {}
            public function alert($message, array $context = []): void {}
            public function critical($message, array $context = []): void {}
            public function error($message, array $context = []): void {}
            public function warning($message, array $context = []): void {}
            public function notice($message, array $context = []): void {}
            public function info($message, array $context = []): void {}
            public function debug($message, array $context = []): void {}
            public function log($level, $message, array $context = []): void {}
        };
    }
}
