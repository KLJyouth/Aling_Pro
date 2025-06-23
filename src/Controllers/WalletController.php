<?php
/**
 * AlingAi Pro - 钱包管理控制器
 * 处理用户钱包、余额、交易记录等功能
 * 
 * @package AlingAi\Pro\Controllers
 * @version 1.0.0
 * @author AlingAi Team
 * @created 2024-12-19
 */

declare(strict_types=1);

namespace AlingAi\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use AlingAi\Services\{
    DatabaseServiceInterface,
    CacheService,
    EnhancedUserManagementService
};
use AlingAi\Models\User;
use AlingAi\Utils\Logger;
use Exception;

class WalletController extends BaseController
{
    private EnhancedUserManagementService $userService;
    private Logger $logger;

    public function __construct(
        DatabaseServiceInterface $db,
        CacheService $cache,
        EnhancedUserManagementService $userService,
        Logger $logger
    ) {
        parent::__construct($db, $cache);
        $this->userService = $userService;
        $this->logger = $logger;
    }

    /**
     * 获取用户钱包信息
     */
    public function getWalletInfo(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $userId = $this->getUserIdFromRequest($request);
            
            // 获取用户信息
            $user = User::findById($this->db, $userId);
            if (!$user) {
                return $this->errorResponse($response, '用户不存在', 404);
            }

            // 获取钱包余额和统计信息
            $walletInfo = $this->getWalletStatistics($userId);
            
            return $this->successResponse($response, [
                'wallet' => [
                    'balance' => (float)$user['wallet_balance'],
                    'currency' => 'CNY',
                    'freeze_amount' => (float)($user['freeze_amount'] ?? 0),
                    'available_balance' => (float)$user['wallet_balance'] - (float)($user['freeze_amount'] ?? 0)
                ],
                'statistics' => $walletInfo['statistics'],
                'recent_transactions' => $walletInfo['recent_transactions']
            ]);

        } catch (Exception $e) {
            $this->logger->error('钱包信息获取失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse($response, '获取钱包信息失败', 500);
        }
    }

    /**
     * 获取交易历史
     */
    public function getTransactionHistory(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $userId = $this->getUserIdFromRequest($request);
            $params = $request->getQueryParams();
            
            $page = (int)($params['page'] ?? 1);
            $limit = min((int)($params['limit'] ?? 20), 100);
            $type = $params['type'] ?? null; // income/expense/transfer
            $startDate = $params['start_date'] ?? null;
            $endDate = $params['end_date'] ?? null;

            $transactions = $this->getTransactionList($userId, $page, $limit, $type, $startDate, $endDate);

            return $this->successResponse($response, $transactions);

        } catch (Exception $e) {
            $this->logger->error('交易历史获取失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse($response, '获取交易历史失败', 500);
        }
    }

    /**
     * 创建充值订单
     */
    public function createRechargeOrder(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $userId = $this->getUserIdFromRequest($request);
            $data = $this->getJsonBody($request);

            // 验证充值金额
            $amount = (float)($data['amount'] ?? 0);
            if ($amount <= 0 || $amount > 10000) {
                return $this->errorResponse($response, '充值金额必须在0.01-10000元之间', 400);
            }

            // 验证支付方式
            $paymentMethod = $data['payment_method'] ?? '';
            if (!in_array($paymentMethod, ['wechat', 'alipay', 'bank'])) {
                return $this->errorResponse($response, '不支持的支付方式', 400);
            }

            // 创建交易记录
            $transactionData = [
                'user_id' => $userId,
                'type' => 'recharge',
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'status' => 'pending',
                'order_no' => $this->generateOrderNumber(),
                'description' => '钱包充值',
                'created_at' => date('Y-m-d H:i:s'),
                'expires_at' => date('Y-m-d H:i:s', time() + 1800) // 30分钟过期
            ];

            $transactionId = $this->createTransaction($transactionData);

            // 生成支付参数（这里需要接入实际支付接口）
            $paymentParams = $this->generatePaymentParams($transactionData);

            return $this->successResponse($response, [
                'transaction_id' => $transactionId,
                'order_no' => $transactionData['order_no'],
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'payment_params' => $paymentParams,
                'expires_at' => $transactionData['expires_at']
            ]);

        } catch (Exception $e) {
            $this->logger->error('创建充值订单失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse($response, '创建充值订单失败', 500);
        }
    }

    /**
     * 处理支付回调
     */
    public function handlePaymentCallback(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $data = $this->getJsonBody($request);
            $orderNo = $data['order_no'] ?? '';
            $status = $data['status'] ?? '';
            $paymentId = $data['payment_id'] ?? '';

            if (empty($orderNo)) {
                return $this->errorResponse($response, '订单号不能为空', 400);
            }

            // 查找交易记录
            $transaction = $this->getTransactionByOrderNo($orderNo);
            if (!$transaction) {
                return $this->errorResponse($response, '交易记录不存在', 404);
            }

            // 验证支付状态
            if ($status === 'success' && $transaction['status'] === 'pending') {
                // 更新交易状态
                $this->updateTransactionStatus($transaction['id'], 'completed', $paymentId);
                
                // 更新用户余额
                $this->updateUserBalance($transaction['user_id'], $transaction['amount']);
                
                // 记录操作日志
                $this->logWalletOperation($transaction['user_id'], 'recharge_success', [
                    'amount' => $transaction['amount'],
                    'order_no' => $orderNo,
                    'payment_id' => $paymentId
                ]);

                return $this->successResponse($response, ['message' => '充值成功']);
            } elseif ($status === 'failed') {
                // 更新交易状态为失败
                $this->updateTransactionStatus($transaction['id'], 'failed', $paymentId);
                
                return $this->successResponse($response, ['message' => '支付失败']);
            }

            return $this->errorResponse($response, '无效的支付状态', 400);

        } catch (Exception $e) {
            $this->logger->error('支付回调处理失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse($response, '支付回调处理失败', 500);
        }
    }

    /**
     * API费用扣除
     */
    public function deductApiCost(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $userId = $this->getUserIdFromRequest($request);
            $data = $this->getJsonBody($request);

            $cost = (float)($data['cost'] ?? 0);
            $apiProvider = $data['api_provider'] ?? '';
            $model = $data['model'] ?? '';
            $tokens = (int)($data['tokens'] ?? 0);

            if ($cost <= 0) {
                return $this->errorResponse($response, '扣费金额必须大于0', 400);
            }

            // 检查余额是否充足
            $user = User::findById($this->db, $userId);
            if (!$user || $user['wallet_balance'] < $cost) {
                return $this->errorResponse($response, '余额不足', 400);
            }

            // 创建扣费交易记录
            $transactionData = [
                'user_id' => $userId,
                'type' => 'api_usage',
                'amount' => -$cost, // 负数表示扣费
                'status' => 'completed',
                'description' => "API调用费用 - {$apiProvider} ({$model})",
                'metadata' => json_encode([
                    'api_provider' => $apiProvider,
                    'model' => $model,
                    'tokens' => $tokens,
                    'cost_per_token' => $tokens > 0 ? $cost / $tokens : 0
                ]),
                'created_at' => date('Y-m-d H:i:s')
            ];

            $transactionId = $this->createTransaction($transactionData);

            // 更新用户余额
            $this->updateUserBalance($userId, -$cost);

            // 更新API使用统计
            $this->updateApiUsageStats($userId, $apiProvider, $model, $tokens, $cost);

            return $this->successResponse($response, [
                'transaction_id' => $transactionId,
                'remaining_balance' => $user['wallet_balance'] - $cost,
                'cost' => $cost
            ]);

        } catch (Exception $e) {
            $this->logger->error('API费用扣除失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse($response, 'API费用扣除失败', 500);
        }
    }

    /**
     * 钱包转账
     */
    public function transfer(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $fromUserId = $this->getUserIdFromRequest($request);
            $data = $this->getJsonBody($request);

            $toUserId = (int)($data['to_user_id'] ?? 0);
            $amount = (float)($data['amount'] ?? 0);
            $description = $data['description'] ?? '钱包转账';

            // 验证参数
            if ($toUserId <= 0 || $amount <= 0) {
                return $this->errorResponse($response, '参数错误', 400);
            }

            if ($fromUserId === $toUserId) {
                return $this->errorResponse($response, '不能向自己转账', 400);
            }

            // 检查转出用户余额
            $fromUser = User::findById($this->db, $fromUserId);
            if (!$fromUser || $fromUser['wallet_balance'] < $amount) {
                return $this->errorResponse($response, '余额不足', 400);
            }

            // 检查转入用户是否存在
            $toUser = User::findById($this->db, $toUserId);
            if (!$toUser) {
                return $this->errorResponse($response, '转入用户不存在', 404);
            }

            // 开始数据库事务
            $this->db->beginTransaction();

            try {
                // 创建转出交易记录
                $transferOutData = [
                    'user_id' => $fromUserId,
                    'type' => 'transfer_out',
                    'amount' => -$amount,
                    'status' => 'completed',
                    'description' => "转账给用户 {$toUser['username']} - {$description}",
                    'metadata' => json_encode(['to_user_id' => $toUserId]),
                    'created_at' => date('Y-m-d H:i:s')
                ];

                // 创建转入交易记录
                $transferInData = [
                    'user_id' => $toUserId,
                    'type' => 'transfer_in',
                    'amount' => $amount,
                    'status' => 'completed',
                    'description' => "来自用户 {$fromUser['username']} 的转账 - {$description}",
                    'metadata' => json_encode(['from_user_id' => $fromUserId]),
                    'created_at' => date('Y-m-d H:i:s')
                ];

                $this->createTransaction($transferOutData);
                $this->createTransaction($transferInData);

                // 更新用户余额
                $this->updateUserBalance($fromUserId, -$amount);
                $this->updateUserBalance($toUserId, $amount);

                $this->db->commit();

                return $this->successResponse($response, [
                    'message' => '转账成功',
                    'amount' => $amount,
                    'to_user' => $toUser['username']
                ]);

            } catch (Exception $e) {
                $this->db->rollback();
                throw $e;
            }

        } catch (Exception $e) {
            $this->logger->error('钱包转账失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse($response, '转账失败', 500);
        }
    }

    /**
     * 获取钱包统计信息
     */
    private function getWalletStatistics(int $userId): array
    {
        $sql = "
            SELECT 
                COUNT(*) as total_transactions,
                SUM(CASE WHEN amount > 0 THEN amount ELSE 0 END) as total_income,
                SUM(CASE WHEN amount < 0 THEN ABS(amount) ELSE 0 END) as total_expense,
                AVG(CASE WHEN amount > 0 THEN amount ELSE NULL END) as avg_income
            FROM wallet_transactions 
            WHERE user_id = ? AND status = 'completed'
        ";

        $statistics = $this->db->fetchOne($sql, [$userId]);

        // 获取最近的交易记录
        $recentSql = "
            SELECT id, type, amount, description, created_at
            FROM wallet_transactions 
            WHERE user_id = ? AND status = 'completed'
            ORDER BY created_at DESC 
            LIMIT 5
        ";

        $recentTransactions = $this->db->fetchAll($recentSql, [$userId]);

        return [
            'statistics' => [
                'total_transactions' => (int)$statistics['total_transactions'],
                'total_income' => (float)$statistics['total_income'],
                'total_expense' => (float)$statistics['total_expense'],
                'avg_income' => (float)$statistics['avg_income']
            ],
            'recent_transactions' => $recentTransactions
        ];
    }

    /**
     * 获取交易列表
     */
    private function getTransactionList(int $userId, int $page, int $limit, ?string $type, ?string $startDate, ?string $endDate): array
    {
        $offset = ($page - 1) * $limit;
        $where = ['user_id = ?'];
        $params = [$userId];

        if ($type) {
            $where[] = 'type = ?';
            $params[] = $type;
        }

        if ($startDate) {
            $where[] = 'created_at >= ?';
            $params[] = $startDate . ' 00:00:00';
        }

        if ($endDate) {
            $where[] = 'created_at <= ?';
            $params[] = $endDate . ' 23:59:59';
        }

        $whereClause = implode(' AND ', $where);

        // 获取总数
        $countSql = "SELECT COUNT(*) as total FROM wallet_transactions WHERE {$whereClause}";
        $total = $this->db->fetchOne($countSql, $params)['total'];

        // 获取交易列表
        $sql = "
            SELECT id, type, amount, status, description, payment_method, order_no, metadata, created_at
            FROM wallet_transactions 
            WHERE {$whereClause}
            ORDER BY created_at DESC 
            LIMIT {$limit} OFFSET {$offset}
        ";

        $transactions = $this->db->fetchAll($sql, $params);

        // 处理元数据
        foreach ($transactions as &$transaction) {
            if ($transaction['metadata']) {
                $transaction['metadata'] = json_decode($transaction['metadata'], true);
            }
        }

        return [
            'transactions' => $transactions,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => (int)$total,
                'pages' => ceil($total / $limit)
            ]
        ];
    }

    /**
     * 生成订单号
     */
    private function generateOrderNumber(): string
    {
        return 'WLT' . date('YmdHis') . sprintf('%06d', mt_rand(0, 999999));
    }

    /**
     * 生成支付参数
     */
    private function generatePaymentParams(array $transactionData): array
    {
        // 这里应该接入真实的支付接口（微信支付、支付宝等）
        // 目前返回模拟数据
        return [
            'qr_code' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==',
            'payment_url' => "https://payment.example.com/pay/{$transactionData['order_no']}",
            'app_pay_params' => [
                'appid' => 'your_app_id',
                'partnerid' => 'your_partner_id',
                'prepayid' => 'prepay_' . $transactionData['order_no'],
                'package' => 'Sign=WXPay',
                'noncestr' => uniqid(),
                'timestamp' => time(),
            ]
        ];
    }

    /**
     * 创建交易记录
     */
    private function createTransaction(array $data): int
    {
        $sql = "
            INSERT INTO wallet_transactions (
                user_id, type, amount, status, description, payment_method, 
                order_no, metadata, created_at, updated_at, expires_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)
        ";

        return $this->db->execute($sql, [
            $data['user_id'],
            $data['type'],
            $data['amount'],
            $data['status'] ?? 'pending',
            $data['description'] ?? '',
            $data['payment_method'] ?? null,
            $data['order_no'] ?? null,
            $data['metadata'] ?? null,
            $data['created_at'],
            $data['expires_at'] ?? null
        ]);
    }

    /**
     * 根据订单号获取交易记录
     */
    private function getTransactionByOrderNo(string $orderNo): ?array
    {
        $sql = "SELECT * FROM wallet_transactions WHERE order_no = ? LIMIT 1";
        return $this->db->fetchOne($sql, [$orderNo]);
    }

    /**
     * 更新交易状态
     */
    private function updateTransactionStatus(int $transactionId, string $status, ?string $paymentId = null): void
    {
        $sql = "UPDATE wallet_transactions SET status = ?, payment_id = ?, updated_at = NOW() WHERE id = ?";
        $this->db->execute($sql, [$status, $paymentId, $transactionId]);
    }

    /**
     * 更新用户余额
     */
    private function updateUserBalance(int $userId, float $amount): void
    {
        $sql = "UPDATE users SET wallet_balance = wallet_balance + ?, updated_at = NOW() WHERE id = ?";
        $this->db->execute($sql, [$amount, $userId]);
    }

    /**
     * 更新API使用统计
     */
    private function updateApiUsageStats(int $userId, string $apiProvider, string $model, int $tokens, float $cost): void
    {
        $date = date('Y-m-d');
        
        $sql = "
            INSERT INTO api_usage_stats (user_id, api_provider, model_name, date, tokens_used, cost, request_count, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, 1, NOW(), NOW())
            ON DUPLICATE KEY UPDATE 
                tokens_used = tokens_used + VALUES(tokens_used),
                cost = cost + VALUES(cost),
                request_count = request_count + 1,
                updated_at = NOW()
        ";

        $this->db->execute($sql, [$userId, $apiProvider, $model, $date, $tokens, $cost]);
    }

    /**
     * 记录钱包操作日志
     */
    private function logWalletOperation(int $userId, string $operation, array $data): void
    {
        $this->logger->info("钱包操作: {$operation}", [
            'user_id' => $userId,
            'operation' => $operation,
            'data' => $data
        ]);
    }

    /**
     * 从请求中获取用户ID
     */
    private function getUserIdFromRequest(ServerRequestInterface $request): int
    {
        $user = $request->getAttribute('user');
        if (!$user || !isset($user['id'])) {
            throw new Exception('用户未认证');
        }
        return (int)$user['id'];
    }

    /**
     * 获取JSON请求体
     */
    private function getJsonBody(ServerRequestInterface $request): array
    {
        $body = $request->getBody()->getContents();
        $data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('无效的JSON数据');
        }
        
        return $data ?? [];
    }
}
