<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Billing\PaymentMethod;
use App\Models\Billing\RechargePackage;
use App\Models\Billing\Transaction;
use App\Models\Billing\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RechargeController extends Controller
{
    /**
     * 显示充值页面
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        
        // 获取用户钱包
        $wallet = UserWallet::firstOrCreate(
            ["user_id" => $user->id],
            ["balance" => 0, "points" => 0]
        );
        
        // 获取充值套餐
        $packages = RechargePackage::where("status", 1)
            ->orderBy("sort_order")
            ->orderBy("amount")
            ->get();
        
        // 获取支付方式
        $paymentMethods = PaymentMethod::where("status", 1)
            ->orderBy("sort_order")
            ->get();
        
        // 获取最近交易记录
        $transactions = Transaction::where("user_id", $user->id)
            ->where("type", "recharge")
            ->orderBy("created_at", "desc")
            ->limit(5)
            ->get();
        
        return view("user.recharge.index", compact("wallet", "packages", "paymentMethods", "transactions"));
    }
    
    /**
     * 创建充值订单
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        
        // 验证输入
        $validator = Validator::make($request->all(), [
            "package_id" => "nullable|exists:recharge_packages,id",
            "amount" => "required_without:package_id|numeric|min:1",
            "payment_method_id" => "required|exists:payment_methods,id",
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // 开始事务
        DB::beginTransaction();
        
        try {
            // 确定充值金额和赠送积分
            $amount = 0;
            $bonusPoints = 0;
            
            if ($request->has("package_id")) {
                $package = RechargePackage::findOrFail($request->input("package_id"));
                $amount = $package->amount;
                $bonusPoints = $package->bonus_points;
            } else {
                $amount = $request->input("amount");
                
                // 根据充值金额计算赠送积分（示例规则）
                if ($amount >= 100) {
                    $bonusPoints = $amount * 0.1; // 10%
                } elseif ($amount >= 50) {
                    $bonusPoints = $amount * 0.05; // 5%
                }
            }
            
            // 获取支付方式
            $paymentMethod = PaymentMethod::findOrFail($request->input("payment_method_id"));
            
            // 创建交易记录
            $transaction = new Transaction();
            $transaction->user_id = $user->id;
            $transaction->type = "recharge";
            $transaction->amount = $amount;
            $transaction->points = $bonusPoints;
            $transaction->status = "pending";
            $transaction->payment_method = $paymentMethod->code;
            $transaction->transaction_no = "R" . date("YmdHis") . Str::random(6);
            $transaction->metadata = [
                "package_id" => $request->input("package_id"),
                "client_ip" => $request->ip(),
                "user_agent" => $request->userAgent(),
            ];
            $transaction->save();
            
            DB::commit();
            
            // 根据支付方式跳转到相应的支付页面
            return redirect()->route("user.recharge.pay", $transaction->id);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with("error", "创建充值订单失败：" . $e->getMessage())
                ->withInput();
        }
    }

    
    /**
     * 显示支付页面
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function pay($id)
    {
        $user = Auth::user();
        
        $transaction = Transaction::where("user_id", $user->id)
            ->where("id", $id)
            ->where("type", "recharge")
            ->where("status", "pending")
            ->firstOrFail();
        
        // 获取支付方式
        $paymentMethod = PaymentMethod::where("code", $transaction->payment_method)
            ->where("status", 1)
            ->firstOrFail();
        
        // 生成支付参数
        $payParams = $this->generatePayParams($transaction, $paymentMethod);
        
        return view("user.recharge.pay", compact("transaction", "paymentMethod", "payParams"));
    }
    
    /**
     * 生成支付参数
     *
     * @param Transaction $transaction
     * @param PaymentMethod $paymentMethod
     * @return array
     */
    protected function generatePayParams($transaction, $paymentMethod)
    {
        $params = [];
        
        switch ($paymentMethod->code) {
            case "alipay":
                $params = [
                    "out_trade_no" => $transaction->transaction_no,
                    "total_amount" => $transaction->amount,
                    "subject" => "账户充值",
                    "body" => "充值 " . $transaction->amount . " 元",
                    "return_url" => route("user.recharge.return", ["method" => "alipay"]),
                    "notify_url" => route("payment.notify", ["method" => "alipay"]),
                ];
                break;
                
            case "wechat":
                $params = [
                    "out_trade_no" => $transaction->transaction_no,
                    "total_fee" => $transaction->amount * 100, // 微信支付金额单位为分
                    "body" => "账户充值",
                    "attach" => "充值 " . $transaction->amount . " 元",
                    "trade_type" => "NATIVE", // 二维码支付
                    "notify_url" => route("payment.notify", ["method" => "wechat"]),
                ];
                break;
                
            case "paypal":
                $params = [
                    "invoice_id" => $transaction->transaction_no,
                    "amount" => $transaction->amount,
                    "currency" => "USD", // 假设使用美元
                    "description" => "账户充值",
                    "return_url" => route("user.recharge.return", ["method" => "paypal"]),
                    "cancel_url" => route("user.recharge.index"),
                    "notify_url" => route("payment.notify", ["method" => "paypal"]),
                ];
                break;
        }
        
        return $params;
    }
    
    /**
     * 处理支付返回
     *
     * @param Request $request
     * @param string $method
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleReturn(Request $request, $method)
    {
        $user = Auth::user();
        
        switch ($method) {
            case "alipay":
                // 处理支付宝返回
                $outTradeNo = $request->input("out_trade_no");
                break;
                
            case "wechat":
                // 处理微信支付返回
                $outTradeNo = $request->input("out_trade_no");
                break;
                
            case "paypal":
                // 处理PayPal返回
                $outTradeNo = $request->input("invoice_id");
                break;
                
            default:
                return redirect()->route("user.recharge.index")
                    ->with("error", "不支持的支付方式");
        }
        
        // 查找交易记录
        $transaction = Transaction::where("user_id", $user->id)
            ->where("transaction_no", $outTradeNo)
            ->where("type", "recharge")
            ->first();
        
        if (!$transaction) {
            return redirect()->route("user.recharge.index")
                ->with("error", "交易记录不存在");
        }
        
        // 如果交易已经成功，直接跳转到结果页面
        if ($transaction->status === "success") {
            return redirect()->route("user.recharge.result", $transaction->id)
                ->with("success", "充值成功");
        }
        
        // 如果交易仍在处理中，跳转到结果页面等待
        return redirect()->route("user.recharge.result", $transaction->id);
    }
    
    /**
     * 显示充值结果页面
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function result($id)
    {
        $user = Auth::user();
        
        $transaction = Transaction::where("user_id", $user->id)
            ->where("id", $id)
            ->where("type", "recharge")
            ->firstOrFail();
        
        return view("user.recharge.result", compact("transaction"));
    }
    
    /**
     * 显示充值记录页面
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function records(Request $request)
    {
        $user = Auth::user();
        
        $query = Transaction::where("user_id", $user->id)
            ->where("type", "recharge");
        
        // 按状态筛选
        if ($request->has("status") && !empty($request->input("status"))) {
            $query->where("status", $request->input("status"));
        }
        
        // 按日期范围筛选
        if ($request->has("start_date") && !empty($request->input("start_date"))) {
            $query->whereDate("created_at", ">=", $request->input("start_date"));
        }
        
        if ($request->has("end_date") && !empty($request->input("end_date"))) {
            $query->whereDate("created_at", "<=", $request->input("end_date"));
        }
        
        // 排序
        $query->orderBy("created_at", "desc");
        
        $transactions = $query->paginate(10);
        
        return view("user.recharge.records", compact("transactions"));
    }
}
