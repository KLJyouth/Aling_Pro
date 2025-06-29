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
     * ��ʾ�����б�
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
        
        // ɸѡ����
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
        
        // ����
        $sortField = $request->input("sort", "created_at");
        $sortDirection = $request->input("direction", "desc");
        $query->orderBy("payment_transactions." . $sortField, $sortDirection);
        
        // ��ҳ
        $transactions = $query->paginate(20);
        
        // ��ȡ����֧�����أ�����ɸѡ
        $gateways = $this->paymentService->getAllGateways();
        
        return view("admin.payment.transactions.index", compact("transactions", "gateways"));
    }

    /**
     * ��ʾ��������
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
                ->with("error", "���ײ�����");
        }
        
        // ��ȡ�˿��¼
        $refunds = $this->paymentService->getRefundsByTransactionId($transaction->id);
        
        // ��ȡ������־
        $logs = $this->paymentService->getTransactionLogs($transaction->transaction_id);
        
        // ����������Ӧ
        $gatewayResponse = json_decode($transaction->gateway_response, true);
        
        return view("admin.payment.transactions.show", compact("transaction", "refunds", "logs", "gatewayResponse"));
    }

    /**
     * ���½���״̬
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
                ->with("error", "��Ч��״̬");
        }
        
        try {
            $transaction = $this->paymentService->getTransaction($id);
            
            if (!$transaction) {
                return redirect()
                    ->route("admin.payment.transactions.index")
                    ->with("error", "���ײ�����");
            }
            
            // ����״̬
            $this->paymentService->updateTransactionStatus($id, $status, [
                "error_message" => $status === "failed" ? $reason : null
            ]);
            
            // ��¼��־
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
                ->with("success", "����״̬�Ѹ���");
        } catch (\Exception $e) {
            Log::error("���½���״̬ʧ��", ["error" => $e->getMessage()]);
            
            return redirect()
                ->back()
                ->with("error", "���½���״̬ʧ�ܣ�" . $e->getMessage());
        }
    }

    /**
     * �����˿�
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
                    ->with("error", "���ײ�����");
            }
            
            // ��齻��״̬
            if ($transaction->status !== "completed") {
                return redirect()
                    ->back()
                    ->with("error", "ֻ������ɵĽ��ײ����˿�");
            }
            
            // ����˿���
            $amount = (float)$request->input("amount");
            if ($amount > (float)$transaction->amount) {
                return redirect()
                    ->back()
                    ->with("error", "�˿���ܳ������׽��");
            }
            
            // �����˿��¼
            $refundId = $this->paymentService->createRefund([
                "transaction_id" => $transaction->id,
                "amount" => $amount,
                "status" => "pending",
                "reason" => $request->input("reason"),
                "operator" => auth()->id()
            ]);
            
            // ��¼��־
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
            
            // �����ȫ���˿���½���״̬
            if ($amount == (float)$transaction->amount) {
                $this->paymentService->updateTransactionStatus($transaction->transaction_id, "refunded");
            }
            
            return redirect()
                ->route("admin.payment.transactions.show", $id)
                ->with("success", "�˿������Ѵ���");
        } catch (\Exception $e) {
            Log::error("�����˿�ʧ��", ["error" => $e->getMessage()]);
            
            return redirect()
                ->back()
                ->with("error", "�����˿�ʧ�ܣ�" . $e->getMessage());
        }
    }

    /**
     * �����˿�״̬
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
                ->with("error", "��Ч��״̬");
        }
        
        try {
            $refund = $this->paymentService->getRefund($refundId);
            
            if (!$refund) {
                return redirect()
                    ->route("admin.payment.transactions.show", $id)
                    ->with("error", "�˿��¼������");
            }
            
            // ��ȡ������Ϣ
            $transaction = $this->paymentService->getTransaction($id);
            
            // �����˿�״̬
            $this->paymentService->updateRefundStatus($refundId, $status, [
                "error_message" => $status === "failed" ? $reason : null
            ]);
            
            // ��¼��־
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
                ->with("success", "�˿�״̬�Ѹ���");
        } catch (\Exception $e) {
            Log::error("�����˿�״̬ʧ��", ["error" => $e->getMessage()]);
            
            return redirect()
                ->back()
                ->with("error", "�����˿�״̬ʧ�ܣ�" . $e->getMessage());
        }
    }

    /**
     * ������������
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
        
        // Ӧ����index������ͬ��ɸѡ����
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
        
        // ����
        $sortField = $request->input("sort", "created_at");
        $sortDirection = $request->input("direction", "desc");
        $query->orderBy("payment_transactions." . $sortField, $sortDirection);
        
        $transactions = $query->get();
        
        // ����CSV�ļ�
        $filename = "transactions_" . date("Y-m-d_H-i-s") . ".csv";
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];
        
        $columns = [
            "����ID", "����ID", "�û�ID", "֧������", "���", "����", "״̬", 
            "֧����ʽ", "�ͻ���IP", "������Ϣ", "֧��ʱ��", "����ʱ��"
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
     * ��ȡ״̬�ı�
     *
     * @param string $status
     * @return string
     */
    protected function getStatusText($status)
    {
        $statusMap = [
            "pending" => "��֧��",
            "completed" => "�����",
            "failed" => "ʧ��",
            "refunded" => "���˿�"
        ];
        
        return $statusMap[$status] ?? $status;
    }
}
