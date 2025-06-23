<?php
/**
 * 支付控制器
 * 处理微信支付、支付宝等第三方支付网关集成
 * 
 * @package AlingAi\Controllers
 * @author AlingAi Pro Team
 * @version 1.0.0
 */

declare(strict_types=1);

namespace AlingAi\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Container\ContainerInterface;
use AlingAi\Services\{
    DatabaseServiceInterface,
    CacheService,
    EnhancedUserManagementService
};
use Exception;

class PaymentController extends BaseController
{
    private DatabaseServiceInterface $db;
    private LoggerInterface $logger;
    private CacheService $cache;
    private EnhancedUserManagementService $userService;
    private array $config;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->db = $container->get(DatabaseServiceInterface::class);
        $this->logger = $container->get(LoggerInterface::class);
        $this->cache = $container->get(CacheService::class);
        $this->userService = $container->get(EnhancedUserManagementService::class);
        $this->config = $container->get('settings')['services']['payment'] ?? [];
    }

    /**
     * 创建微信支付订单
     */
    public function createWeChatOrder(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $userId = $this->getUserId($request);
            $data = $this->getJsonBody($request);

            $orderNo = $data['order_no'] ?? '';
            $amount = (float)($data['amount'] ?? 0);
            $description = $data['description'] ?? '钱包充值';

            if (empty($orderNo) || $amount <= 0) {
                return $this->errorResponse($response, '订单参数无效', 400);
            }

            // 验证订单是否存在且属于当前用户
            $transaction = $this->getTransactionByOrderNo($orderNo);
            if (!$transaction || $transaction['user_id'] != $userId) {
                return $this->errorResponse($response, '订单不存在或无权限', 404);
            }

            // 创建微信支付订单
            $wechatOrder = $this->createWeChatPayOrder($orderNo, $amount, $description);

            // 更新交易记录中的支付参数
            $this->updateTransactionPaymentParams($transaction['id'], $wechatOrder);

            return $this->successResponse($response, [
                'order_no' => $orderNo,
                'payment_type' => 'wechat',
                'payment_params' => $wechatOrder,
                'expires_at' => $transaction['expires_at']
            ]);

        } catch (Exception $e) {
            $this->logger->error('创建微信支付订单失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse($response, '创建支付订单失败', 500);
        }
    }

    /**
     * 创建支付宝订单
     */
    public function createAlipayOrder(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $userId = $this->getUserId($request);
            $data = $this->getJsonBody($request);

            $orderNo = $data['order_no'] ?? '';
            $amount = (float)($data['amount'] ?? 0);
            $description = $data['description'] ?? '钱包充值';

            if (empty($orderNo) || $amount <= 0) {
                return $this->errorResponse($response, '订单参数无效', 400);
            }

            // 验证订单是否存在且属于当前用户
            $transaction = $this->getTransactionByOrderNo($orderNo);
            if (!$transaction || $transaction['user_id'] != $userId) {
                return $this->errorResponse($response, '订单不存在或无权限', 404);
            }

            // 创建支付宝订单
            $alipayOrder = $this->createAlipayPayOrder($orderNo, $amount, $description);

            // 更新交易记录中的支付参数
            $this->updateTransactionPaymentParams($transaction['id'], $alipayOrder);

            return $this->successResponse($response, [
                'order_no' => $orderNo,
                'payment_type' => 'alipay',
                'payment_params' => $alipayOrder,
                'expires_at' => $transaction['expires_at']
            ]);

        } catch (Exception $e) {
            $this->logger->error('创建支付宝订单失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse($response, '创建支付订单失败', 500);
        }
    }

    /**
     * 微信支付回调处理
     */
    public function wechatCallback(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $body = (string)$request->getBody();
            $this->logger->info('收到微信支付回调', ['body' => $body]);

            // 验证微信支付签名
            if (!$this->verifyWeChatSignature($request, $body)) {
                $this->logger->warning('微信支付回调签名验证失败');
                return $this->wechatCallbackResponse($response, false, '签名验证失败');
            }

            // 解析回调数据
            $callbackData = $this->parseWeChatCallback($body);
            
            if (!$callbackData) {
                return $this->wechatCallbackResponse($response, false, '回调数据解析失败');
            }

            $orderNo = $callbackData['out_trade_no'] ?? '';
            $transactionId = $callbackData['transaction_id'] ?? '';
            $status = $callbackData['trade_state'] ?? '';

            // 处理支付结果
            $success = $this->processPaymentCallback($orderNo, $transactionId, $status, 'wechat', $callbackData);

            return $this->wechatCallbackResponse($response, $success, $success ? '处理成功' : '处理失败');

        } catch (Exception $e) {
            $this->logger->error('微信支付回调处理失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->wechatCallbackResponse($response, false, '系统错误');
        }
    }

    /**
     * 支付宝回调处理
     */
    public function alipayCallback(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $data = $request->getParsedBody();
            $this->logger->info('收到支付宝回调', ['data' => $data]);

            // 验证支付宝签名
            if (!$this->verifyAlipaySignature($data)) {
                $this->logger->warning('支付宝回调签名验证失败');
                return $response->withStatus(400);
            }

            $orderNo = $data['out_trade_no'] ?? '';
            $transactionId = $data['trade_no'] ?? '';
            $status = $data['trade_status'] ?? '';

            // 处理支付结果
            $success = $this->processPaymentCallback($orderNo, $transactionId, $status, 'alipay', $data);

            return $response->getBody()->write($success ? 'success' : 'fail');

        } catch (Exception $e) {
            $this->logger->error('支付宝回调处理失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $response->withStatus(500);
        }
    }

    /**
     * 查询支付状态
     */
    public function queryPaymentStatus(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $userId = $this->getUserId($request);
            $orderNo = $request->getQueryParams()['order_no'] ?? '';

            if (empty($orderNo)) {
                return $this->errorResponse($response, '订单号不能为空', 400);
            }

            // 查询本地交易记录
            $transaction = $this->getTransactionByOrderNo($orderNo);
            if (!$transaction || $transaction['user_id'] != $userId) {
                return $this->errorResponse($response, '订单不存在或无权限', 404);
            }

            // 如果订单已完成，直接返回状态
            if ($transaction['status'] === 'completed') {
                return $this->successResponse($response, [
                    'order_no' => $orderNo,
                    'status' => 'completed',
                    'amount' => $transaction['amount'],
                    'payment_method' => $transaction['payment_method'],
                    'completed_at' => $transaction['updated_at']
                ]);
            }

            // 如果订单还在处理中，查询第三方支付状态
            $paymentMethod = $transaction['payment_method'];
            $thirdPartyStatus = null;

            if ($paymentMethod === 'wechat') {
                $thirdPartyStatus = $this->queryWeChatPaymentStatus($orderNo);
            } elseif ($paymentMethod === 'alipay') {
                $thirdPartyStatus = $this->queryAlipayPaymentStatus($orderNo);
            }

            // 如果第三方已支付成功，更新本地状态
            if ($thirdPartyStatus && $thirdPartyStatus['paid']) {
                $this->processPaymentCallback(
                    $orderNo,
                    $thirdPartyStatus['transaction_id'],
                    'success',
                    $paymentMethod,
                    $thirdPartyStatus
                );
                $transaction['status'] = 'completed';
            }

            return $this->successResponse($response, [
                'order_no' => $orderNo,
                'status' => $transaction['status'],
                'amount' => $transaction['amount'],
                'payment_method' => $transaction['payment_method'],
                'third_party_status' => $thirdPartyStatus
            ]);

        } catch (Exception $e) {
            $this->logger->error('查询支付状态失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse($response, '查询支付状态失败', 500);
        }
    }

    /**
     * 取消支付订单
     */
    public function cancelPayment(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $userId = $this->getUserId($request);
            $data = $this->getJsonBody($request);
            $orderNo = $data['order_no'] ?? '';

            if (empty($orderNo)) {
                return $this->errorResponse($response, '订单号不能为空', 400);
            }

            // 查询交易记录
            $transaction = $this->getTransactionByOrderNo($orderNo);
            if (!$transaction || $transaction['user_id'] != $userId) {
                return $this->errorResponse($response, '订单不存在或无权限', 404);
            }

            if ($transaction['status'] !== 'pending') {
                return $this->errorResponse($response, '订单状态不允许取消', 400);
            }

            // 更新订单状态为已取消
            $this->updateTransactionStatus($transaction['id'], 'cancelled');

            return $this->successResponse($response, [
                'order_no' => $orderNo,
                'status' => 'cancelled',
                'message' => '订单已取消'
            ]);

        } catch (Exception $e) {
            $this->logger->error('取消支付订单失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse($response, '取消订单失败', 500);
        }
    }

    /**
     * 创建微信支付订单
     */
    private function createWeChatPayOrder(string $orderNo, float $amount, string $description): array
    {
        $appId = $this->config['wechat']['app_id'] ?? '';
        $mchId = $this->config['wechat']['mch_id'] ?? '';
        $key = $this->config['wechat']['key'] ?? '';

        if (empty($appId) || empty($mchId) || empty($key)) {
            throw new Exception('微信支付配置不完整');
        }

        // 构建统一下单参数
        $params = [
            'appid' => $appId,
            'mch_id' => $mchId,
            'nonce_str' => $this->generateNonceStr(),
            'body' => $description,
            'out_trade_no' => $orderNo,
            'total_fee' => (int)($amount * 100), // 转换为分
            'spbill_create_ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'notify_url' => $this->getNotifyUrl('wechat'),
            'trade_type' => 'NATIVE'
        ];

        // 生成签名
        $params['sign'] = $this->generateWeChatSign($params, $key);

        // 调用微信统一下单接口
        $response = $this->callWeChatUnifiedOrder($params);

        if ($response['return_code'] === 'SUCCESS' && $response['result_code'] === 'SUCCESS') {
            return [
                'qr_code' => $response['code_url'],
                'prepay_id' => $response['prepay_id'],
                'order_no' => $orderNo,
                'expires_at' => date('Y-m-d H:i:s', time() + 1800) // 30分钟过期
            ];
        }

        throw new Exception('微信支付下单失败: ' . ($response['err_code_des'] ?? '未知错误'));
    }

    /**
     * 创建支付宝订单
     */
    private function createAlipayPayOrder(string $orderNo, float $amount, string $description): array
    {
        $appId = $this->config['alipay']['app_id'] ?? '';
        $privateKey = $this->config['alipay']['private_key'] ?? '';

        if (empty($appId) || empty($privateKey)) {
            throw new Exception('支付宝配置不完整');
        }

        // 构建支付宝下单参数
        $params = [
            'app_id' => $appId,
            'method' => 'alipay.trade.precreate',
            'charset' => 'utf-8',
            'sign_type' => 'RSA2',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0',
            'notify_url' => $this->getNotifyUrl('alipay'),
            'biz_content' => json_encode([
                'out_trade_no' => $orderNo,
                'total_amount' => number_format($amount, 2, '.', ''),
                'subject' => $description,
                'timeout_express' => '30m'
            ])
        ];

        // 生成签名
        $params['sign'] = $this->generateAlipaySign($params, $privateKey);

        // 调用支付宝预下单接口
        $response = $this->callAlipayPrecreate($params);

        if ($response['code'] === '10000') {
            return [
                'qr_code' => $response['qr_code'],
                'order_no' => $orderNo,
                'expires_at' => date('Y-m-d H:i:s', time() + 1800)
            ];
        }

        throw new Exception('支付宝下单失败: ' . ($response['sub_msg'] ?? '未知错误'));
    }

    /**
     * 处理支付回调
     */
    private function processPaymentCallback(string $orderNo, string $transactionId, string $status, string $paymentMethod, array $callbackData): bool
    {
        try {
            // 查询交易记录
            $transaction = $this->getTransactionByOrderNo($orderNo);
            if (!$transaction) {
                $this->logger->warning('支付回调：交易记录不存在', ['order_no' => $orderNo]);
                return false;
            }

            // 检查是否已处理
            if ($transaction['status'] === 'completed') {
                $this->logger->info('支付回调：订单已处理', ['order_no' => $orderNo]);
                return true;
            }

            // 判断支付是否成功
            $isSuccess = $this->isPaymentSuccess($status, $paymentMethod);

            if ($isSuccess) {
                // 开始数据库事务
                $this->db->beginTransaction();

                try {
                    // 更新交易状态
                    $this->updateTransactionStatus($transaction['id'], 'completed', $transactionId, $callbackData);

                    // 更新用户余额
                    $this->updateUserBalance($transaction['user_id'], $transaction['amount']);

                    // 记录钱包流水
                    $this->recordWalletTransaction($transaction['user_id'], $transaction['amount'], 'recharge', $orderNo);

                    // 发送通知
                    $this->sendPaymentNotification($transaction['user_id'], $transaction['amount'], true);

                    $this->db->commit();

                    $this->logger->info('支付成功处理完成', [
                        'order_no' => $orderNo,
                        'user_id' => $transaction['user_id'],
                        'amount' => $transaction['amount']
                    ]);

                    return true;

                } catch (Exception $e) {
                    $this->db->rollBack();
                    throw $e;
                }
            } else {
                // 支付失败，更新状态
                $this->updateTransactionStatus($transaction['id'], 'failed', $transactionId, $callbackData);
                $this->sendPaymentNotification($transaction['user_id'], $transaction['amount'], false);

                $this->logger->warning('支付失败', [
                    'order_no' => $orderNo,
                    'status' => $status,
                    'callback_data' => $callbackData
                ]);
            }

            return $isSuccess;

        } catch (Exception $e) {
            $this->logger->error('处理支付回调失败', [
                'error' => $e->getMessage(),
                'order_no' => $orderNo,
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * 判断支付是否成功
     */
    private function isPaymentSuccess(string $status, string $paymentMethod): bool
    {
        if ($paymentMethod === 'wechat') {
            return $status === 'SUCCESS';
        } elseif ($paymentMethod === 'alipay') {
            return in_array($status, ['TRADE_SUCCESS', 'TRADE_FINISHED']);
        }
        return false;
    }

    /**
     * 验证微信支付签名
     */
    private function verifyWeChatSignature(ServerRequestInterface $request, string $body): bool
    {
        // 实现微信支付签名验证逻辑
        // 这里需要根据微信支付文档实现具体的签名验证
        return true; // 临时返回true，实际需要实现验证逻辑
    }

    /**
     * 验证支付宝签名
     */
    private function verifyAlipaySignature(array $data): bool
    {
        // 实现支付宝签名验证逻辑
        // 这里需要根据支付宝文档实现具体的签名验证
        return true; // 临时返回true，实际需要实现验证逻辑
    }

    /**
     * 生成随机字符串
     */
    private function generateNonceStr(int $length = 32): string
    {
        return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, $length);
    }

    /**
     * 生成微信支付签名
     */
    private function generateWeChatSign(array $params, string $key): string
    {
        ksort($params);
        $stringA = '';
        foreach ($params as $k => $v) {
            if ($k !== 'sign' && $v !== '') {
                $stringA .= $k . '=' . $v . '&';
            }
        }
        $stringSignTemp = $stringA . 'key=' . $key;
        return strtoupper(md5($stringSignTemp));
    }

    /**
     * 生成支付宝签名
     */
    private function generateAlipaySign(array $params, string $privateKey): string
    {
        ksort($params);
        $stringToBeSigned = '';
        foreach ($params as $k => $v) {
            if ($k !== 'sign' && $v !== '') {
                $stringToBeSigned .= $k . '=' . $v . '&';
            }
        }
        $stringToBeSigned = rtrim($stringToBeSigned, '&');

        openssl_sign($stringToBeSigned, $sign, $privateKey, OPENSSL_ALGO_SHA256);
        return base64_encode($sign);
    }

    /**
     * 获取回调通知URL
     */
    private function getNotifyUrl(string $type): string
    {
        $baseUrl = $_ENV['APP_URL'] ?? 'https://your-domain.com';
        return $baseUrl . '/api/payment/callback/' . $type;
    }

    /**
     * 微信支付回调响应
     */
    private function wechatCallbackResponse(ResponseInterface $response, bool $success, string $message): ResponseInterface
    {
        $xml = $success 
            ? '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>'
            : '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[' . $message . ']]></return_msg></xml>';
        
        $response->getBody()->write($xml);
        return $response->withHeader('Content-Type', 'application/xml');
    }

    // 其他辅助方法...
    private function getTransactionByOrderNo(string $orderNo): ?array
    {
        $sql = "SELECT * FROM wallet_transactions WHERE order_no = ? LIMIT 1";
        $result = $this->db->fetch($sql, [$orderNo]);
        return $result ?: null;
    }

    private function updateTransactionStatus(int $id, string $status, ?string $paymentId = null, ?array $metadata = null): void
    {
        $sql = "UPDATE wallet_transactions SET status = ?, payment_id = ?, metadata = ?, updated_at = NOW() WHERE id = ?";
        $this->db->execute($sql, [$status, $paymentId, json_encode($metadata), $id]);
    }

    private function updateTransactionPaymentParams(int $id, array $params): void
    {
        $sql = "UPDATE wallet_transactions SET metadata = ? WHERE id = ?";
        $this->db->execute($sql, [json_encode($params), $id]);
    }

    private function updateUserBalance(int $userId, float $amount): void
    {
        $sql = "UPDATE users SET wallet_balance = wallet_balance + ? WHERE id = ?";
        $this->db->execute($sql, [$amount, $userId]);
    }

    private function recordWalletTransaction(int $userId, float $amount, string $type, string $orderNo): void
    {
        // 这里可以记录额外的钱包流水记录
    }

    private function sendPaymentNotification(int $userId, float $amount, bool $success): void
    {
        // 发送支付结果通知
    }

    // 模拟第三方API调用方法
    private function callWeChatUnifiedOrder(array $params): array
    {
        // 实际需要调用微信支付统一下单接口
        return [
            'return_code' => 'SUCCESS',
            'result_code' => 'SUCCESS',
            'code_url' => 'weixin://wxpay/bizpayurl?pr=mock_qr_code',
            'prepay_id' => 'wx' . date('YmdHis') . rand(100000, 999999)
        ];
    }

    private function callAlipayPrecreate(array $params): array
    {
        // 实际需要调用支付宝预下单接口
        return [
            'code' => '10000',
            'qr_code' => 'https://qr.alipay.com/mock_qr_code'
        ];
    }

    private function parseWeChatCallback(string $body): ?array
    {
        // 解析微信支付回调XML
        $xml = simplexml_load_string($body);
        return $xml ? json_decode(json_encode($xml), true) : null;
    }

    private function queryWeChatPaymentStatus(string $orderNo): ?array
    {
        // 查询微信支付订单状态
        return null;
    }

    private function queryAlipayPaymentStatus(string $orderNo): ?array
    {
        // 查询支付宝订单状态
        return null;
    }
}
