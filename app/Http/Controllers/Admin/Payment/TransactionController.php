<?php

namespace App\Http\Controllers\Admin\Payment;

use App\Http\Controllers\Controller;
use App\Services\Payment\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentGatewayService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * 显示交易列表
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = DB::table("payment_transactions")
            ->leftJoin("payment_gateways", "payment_transactions.gateway_id", "=", "payment_gateways.id")
            ->select(
                "payment_transactions.*",
                "payment_gateways.name as gateway_name",
                "payment_gateways.code as gateway_code"
            )
            ->whereNull("payment_transactions.deleted_at");
        
        // 筛选条件
        if ($request->filled("gateway_id")) {
            $query->where("payment_transactions.gateway_id", $request->input("gateway_id"));
        }
        
        if ($request->filled("status")) {
            $query->where("payment_transactions.status", $request->input("status"));
        }
        
        if ($request->filled("order_id")) {
            $query->where("payment_transactions.order_id", "like", "%" . $request->input("order_id") . "%");
        }
        
        if ($request->filled("transaction_id")) {
            $query->where("payment_transactions.transaction_id", "like", "%" . $request->input("transaction_id") . "%");
        }
        
        if ($request->filled("user_id")) {
            $query->where("payment_transactions.user_id", $request->input("user_id"));
        }
        
        if ($request->filled("date_from")) {
            $query->where("payment_transactions.created_at", ">=", $request->input("date_from") . " 00:00:00");
        }
        
        if ($request->filled("date_to")) {
            $query->where("payment_transactions.created_at", "<=", $request->input("date_to") . " 23:59:59");
        }
        
        // 排序
        $sortField = $request->input("sort", "created_at");
        $sortDirection = $request->input("direction", "desc");
        $query->orderBy("payment_transactions." . $sortField, $sortDirection);
        
        // 分页
        $transactions = $query->paginate(20);
        
        // 获取所有支付网关，用于筛选
        $gateways = $this->paymentService->getAllGateways();
        
        return view("admin.payment.transactions.index", compact("transactions", "gateways"));
    }

    /**
     * 显示交易详情
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $transaction = DB::table("payment_transactions")
            ->leftJoin("payment_gateways", "payment_transactions.gateway_id", "=", "payment_gateways.id")
            ->select(
                "payment_transactions.*",
                "payment_gateways.name as gateway_name",
                "payment_gateways.code as gateway_code"
            )
            ->where("payment_transactions.transaction_id", $id)
            ->whereNull("payment_transactions.deleted_at")
            ->first();
        
        if (!$transaction) {
            return redirect()
                ->route("admin.payment.transactions.index")
                ->with("error", "交易不存在");
        }
        
        // 获取退款记录
        $refunds = $this->paymentService->getRefundsByTransactionId($transaction->id);
        
        // 获取交易日志
        $logs = $this->paymentService->getTransactionLogs($transaction->transaction_id);
        
        // 解析网关响应
        $gatewayResponse = json_decode($transaction->gateway_response, true);
        
        return view("admin.payment.transactions.show", compact("transaction", "refunds", "logs", "gatewayResponse"));
    }

    /**
     * 更新交易状态
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function updateStatus(Request $request, $id)
    {
        $status = $request->input("status");
        $reason = $request->input("reason");
        
        if (!in_array($status, ["completed", "failed", "refunded"])) {
            return redirect()
                ->back()
                ->with("error", "无效的状态");
        }
        
        try {
            $transaction = $this->paymentService->getTransaction($id);
            
            if (!$transaction) {
                return redirect()
                    ->route("admin.payment.transactions.index")
                    ->with("error", "交易不存在");
            }
            
            // 更新状态
            $this->paymentService->updateTransactionStatus($id, $status, [
                "error_message" => $status === "failed" ? $reason : null
            ]);
            
            // 记录日志
            $this->paymentService->logGatewayAction(
                $transaction->gateway_id,
                "status_change",
                ["old_status" => $transaction->status, "new_status" => $status, "reason" => $reason],
                [],
                true,
                null,
                $id
            );
            
            return redirect()
                ->route("admin.payment.transactions.show", $id)
                ->with("success", "交易状态已更新");
        } catch (\Exception $e) {
            Log::error("更新交易状态失败", ["error" => $e->getMessage()]);
            
            return redirect()
                ->back()
                ->with("error", "更新交易状态失败：" . $e->getMessage());
        }
    }

    /**
     * 创建退款
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function createRefund(Request $request, $id)
    {
        $request->validate([
            "amount" => "required|numeric|min:0.01",
            "reason" => "required|string"
        ]);
        
        try {
            $transaction = $this->paymentService->getTransaction($id);
            
            if (!$transaction) {
                return redirect()
                    ->route("admin.payment.transactions.index")
                    ->with("error", "交易不存在");
            }
            
            // 检查交易状态
            if ($transaction->status !== "completed") {
                return redirect()
                    ->back()
                    ->with("error", "只有已完成的交易才能退款");
            }
            
            // 检查退款金额
            $amount = (float)$request->input("amount");
            if ($amount > (float)$transaction->amount) {
                return redirect()
                    ->back()
                    ->with("error", "退款金额不能超过交易金额");
            }
            
            // 创建退款记录
            $refundId = $this->paymentService->createRefund([
                "transaction_id" => $transaction->id,
                "amount" => $amount,
                "status" => "pending",
                "reason" => $request->input("reason"),
                "operator" => auth()->id()
            ]);
            
            // 记录日志
            $this->paymentService->logGatewayAction(
                $transaction->gateway_id,
                "refund_created",
                [
                    "transaction_id" => $transaction->transaction_id,
                    "amount" => $amount,
                    "reason" => $request->input("reason")
                ],
                ["refund_id" => $refundId],
                true,
                null,
                $transaction->transaction_id
            );
            
            // 如果是全额退款，更新交易状态
            if ($amount == (float)$transaction->amount) {
                $this->paymentService->updateTransactionStatus($transaction->transaction_id, "refunded");
            }
            
            return redirect()
                ->route("admin.payment.transactions.show", $id)
                ->with("success", "退款申请已创建");
        } catch (\Exception $e) {
            Log::error("创建退款失败", ["error" => $e->getMessage()]);
            
            return redirect()
                ->back()
                ->with("error", "创建退款失败：" . $e->getMessage());
        }
    }

    /**
     * 更新退款状态
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @param  string  $refundId
     * @return \Illuminate\Http\Response
     */
    public function updateRefundStatus(Request $request, $id, $refundId)
    {
        $status = $request->input("status");
        $reason = $request->input("reason");
        
        if (!in_array($status, ["completed", "failed"])) {
            return redirect()
                ->back()
                ->with("error", "无效的状态");
        }
        
        try {
            $refund = $this->paymentService->getRefund($refundId);
            
            if (!$refund) {
                return redirect()
                    ->route("admin.payment.transactions.show", $id)
                    ->with("error", "退款记录不存在");
            }
            
            // 获取交易信息
            $transaction = $this->paymentService->getTransaction($id);
            
            // 更新退款状态
            $this->paymentService->updateRefundStatus($refundId, $status, [
                "error_message" => $status === "failed" ? $reason : null
            ]);
            
            // 记录日志
            $this->paymentService->logGatewayAction(
                $transaction->gateway_id,
                "refund_status_change",
                [
                    "refund_id" => $refundId,
                    "old_status" => $refund->status,
                    "new_status" => $status,
                    "reason" => $reason
                ],
                [],
                true,
                null,
                $transaction->transaction_id
            );
            
            return redirect()
                ->route("admin.payment.transactions.show", $id)
                ->with("success", "退款状态已更新");
        } catch (\Exception $e) {
            Log::error("更新退款状态失败", ["error" => $e->getMessage()]);
            
            return redirect()
                ->back()
                ->with("error", "更新退款状态失败：" . $e->getMessage());
        }
    }

    /**
     * 导出交易数据
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request)
    {
        $query = DB::table("payment_transactions")
            ->leftJoin("payment_gateways", "payment_transactions.gateway_id", "=", "payment_gateways.id")
            ->select(
                "payment_transactions.transaction_id",
                "payment_transactions.order_id",
                "payment_transactions.user_id",
                "payment_gateways.name as gateway_name",
                "payment_transactions.amount",
                "payment_transactions.currency",
                "payment_transactions.status",
                "payment_transactions.payment_method",
                "payment_transactions.client_ip",
                "payment_transactions.error_message",
                "payment_transactions.paid_at",
                "payment_transactions.created_at"
            )
            ->whereNull("payment_transactions.deleted_at");
        
        // 应用与index方法相同的筛选条件
        if ($request->filled("gateway_id")) {
            $query->where("payment_transactions.gateway_id", $request->input("gateway_id"));
        }
        
        if ($request->filled("status")) {
            $query->where("payment_transactions.status", $request->input("status"));
        }
        
        if ($request->filled("order_id")) {
            $query->where("payment_transactions.order_id", "like", "%" . $request->input("order_id") . "%");
        }
        
        if ($request->filled("transaction_id")) {
            $query->where("payment_transactions.transaction_id", "like", "%" . $request->input("transaction_id") . "%");
        }
        
        if ($request->filled("user_id")) {
            $query->where("payment_transactions.user_id", $request->input("user_id"));
        }
        
        if ($request->filled("date_from")) {
            $query->where("payment_transactions.created_at", ">=", $request->input("date_from") . " 00:00:00");
        }
        
        if ($request->filled("date_to")) {
            $query->where("payment_transactions.created_at", "<=", $request->input("date_to") . " 23:59:59");
        }
        
        // 排序
        $sortField = $request->input("sort", "created_at");
        $sortDirection = $request->input("direction", "desc");
        $query->orderBy("payment_transactions." . $sortField, $sortDirection);
        
        $transactions = $query->get();
        
        // 生成CSV文件
        $filename = "transactions_" . date("Y-m-d_H-i-s") . ".csv";
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];
        
        $columns = [
            "交易ID", "订单ID", "用户ID", "支付网关", "金额", "货币", "状态", 
            "支付方式", "客户端IP", "错误信息", "支付时间", "创建时间"
        ];
        
        $callback = function() use ($transactions, $columns) {
            $file = fopen("php://output", "w");
            fputcsv($file, $columns);
            
            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->transaction_id,
                    $transaction->order_id,
                    $transaction->user_id,
                    $transaction->gateway_name,
                    $transaction->amount,
                    $transaction->currency,
                    $this->getStatusText($transaction->status),
                    $transaction->payment_method,
                    $transaction->client_ip,
                    $transaction->error_message,
                    $transaction->paid_at,
                    $transaction->created_at
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * 获取状态文本
     *
     * @param string $status
     * @return string
     */
    protected function getStatusText($status)
    {
        $statusMap = [
            "pending" => "待支付",
            "completed" => "已完成",
            "failed" => "失败",
            "refunded" => "已退款"
        ];
        
        return $statusMap[$status] ?? $status;
    }
}
