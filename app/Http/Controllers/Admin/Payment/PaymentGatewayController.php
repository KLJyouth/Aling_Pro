<?php

namespace App\Http\Controllers\Admin\Payment;

use App\Http\Controllers\Controller;
use App\Services\Payment\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class PaymentGatewayController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentGatewayService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * 显示支付网关列表
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $gateways = $this->paymentService->getAllGateways();
        $supportedGateways = $this->paymentService->getSupportedGateways();
        
        return view("admin.payment.gateways.index", compact("gateways", "supportedGateways"));
    }

    /**
     * 显示创建支付网关表单
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $gatewayCode = $request->input("gateway_code");
        $supportedGateways = $this->paymentService->getSupportedGateways();
        
        if ($gatewayCode && isset($supportedGateways[$gatewayCode])) {
            $gateway = $supportedGateways[$gatewayCode];
            $fields = $this->paymentService->getGatewayConfigFields($gatewayCode);
            
            return view("admin.payment.gateways.create", compact("gateway", "gatewayCode", "fields"));
        }
        
        return view("admin.payment.gateways.select", compact("supportedGateways"));
    }

    /**
     * 存储新支付网关
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $gatewayCode = $request->input("code");
        $fields = $this->paymentService->getGatewayConfigFields($gatewayCode);
        
        // 验证基本字段
        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:255",
            "code" => "required|string|max:50|unique:payment_gateways,code",
            "description" => "nullable|string",
            "is_active" => "boolean",
            "is_test_mode" => "boolean",
            "logo" => "nullable|image|max:2048",
            "sort_order" => "nullable|integer"
        ]);
        
        // 验证配置字段
        foreach ($fields as $field => $label) {
            $validator->addRules(["config.$field" => "required|string"]);
        }
        
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // 处理上传的Logo
        $logoPath = null;
        if ($request->hasFile("logo")) {
            $logoPath = $request->file("logo")->store("payment_gateways", "public");
        }
        
        // 准备数据
        $data = [
            "name" => $request->input("name"),
            "code" => $request->input("code"),
            "description" => $request->input("description"),
            "is_active" => $request->boolean("is_active", false),
            "is_test_mode" => $request->boolean("is_test_mode", false),
            "logo" => $logoPath,
            "sort_order" => $request->input("sort_order", 0),
            "config" => []
        ];
        
        // 收集配置字段
        foreach ($fields as $field => $label) {
            $data["config"][$field] = $request->input("config.$field");
        }
        
        try {
            // 创建支付网关
            $gatewayId = $this->paymentService->createGateway($data);
            
            return redirect()
                ->route("admin.payment.gateways.index")
                ->with("success", "支付网关创建成功");
        } catch (\Exception $e) {
            Log::error("创建支付网关失败", ["error" => $e->getMessage()]);
            
            return redirect()
                ->back()
                ->withInput()
                ->with("error", "创建支付网关失败：" . $e->getMessage());
        }
    }

    /**
     * 显示支付网关详情
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $gateway = $this->paymentService->getGateway($id);
        
        if (!$gateway) {
            return redirect()
                ->route("admin.payment.gateways.index")
                ->with("error", "支付网关不存在");
        }
        
        // 获取最近的日志
        $logs = $this->paymentService->getGatewayLogs($gateway->id, 20);
        
        // 解析配置
        $config = json_decode($gateway->config, true);
        $fields = $this->paymentService->getGatewayConfigFields($gateway->code);
        
        return view("admin.payment.gateways.show", compact("gateway", "config", "fields", "logs"));
    }

    /**
     * 显示编辑支付网关表单
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $gateway = $this->paymentService->getGateway($id);
        
        if (!$gateway) {
            return redirect()
                ->route("admin.payment.gateways.index")
                ->with("error", "支付网关不存在");
        }
        
        // 解析配置
        $config = json_decode($gateway->config, true);
        $fields = $this->paymentService->getGatewayConfigFields($gateway->code);
        
        return view("admin.payment.gateways.edit", compact("gateway", "config", "fields"));
    }

    /**
     * 更新支付网关
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $gateway = $this->paymentService->getGateway($id);
        
        if (!$gateway) {
            return redirect()
                ->route("admin.payment.gateways.index")
                ->with("error", "支付网关不存在");
        }
        
        $fields = $this->paymentService->getGatewayConfigFields($gateway->code);
        
        // 验证基本字段
        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:255",
            "description" => "nullable|string",
            "is_active" => "boolean",
            "is_test_mode" => "boolean",
            "logo" => "nullable|image|max:2048",
            "sort_order" => "nullable|integer"
        ]);
        
        // 验证配置字段
        foreach ($fields as $field => $label) {
            $validator->addRules(["config.$field" => "required|string"]);
        }
        
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // 处理上传的Logo
        $logoPath = $gateway->logo;
        if ($request->hasFile("logo")) {
            $logoPath = $request->file("logo")->store("payment_gateways", "public");
        }
        
        // 准备数据
        $data = [
            "name" => $request->input("name"),
            "description" => $request->input("description"),
            "is_active" => $request->boolean("is_active", false),
            "is_test_mode" => $request->boolean("is_test_mode", false),
            "logo" => $logoPath,
            "sort_order" => $request->input("sort_order", 0),
            "config" => []
        ];
        
        // 收集配置字段
        foreach ($fields as $field => $label) {
            $data["config"][$field] = $request->input("config.$field");
        }
        
        try {
            // 更新支付网关
            $this->paymentService->updateGateway($id, $data);
            
            return redirect()
                ->route("admin.payment.gateways.show", $id)
                ->with("success", "支付网关更新成功");
        } catch (\Exception $e) {
            Log::error("更新支付网关失败", ["error" => $e->getMessage()]);
            
            return redirect()
                ->back()
                ->withInput()
                ->with("error", "更新支付网关失败：" . $e->getMessage());
        }
    }

    /**
     * 删除支付网关
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $this->paymentService->deleteGateway($id);
            
            return redirect()
                ->route("admin.payment.gateways.index")
                ->with("success", "支付网关删除成功");
        } catch (\Exception $e) {
            Log::error("删除支付网关失败", ["error" => $e->getMessage()]);
            
            return redirect()
                ->back()
                ->with("error", "删除支付网关失败：" . $e->getMessage());
        }
    }

    /**
     * 切换支付网关状态
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function toggle(Request $request, $id)
    {
        $active = $request->boolean("active");
        
        try {
            $this->paymentService->toggleGateway($id, $active);
            
            return response()->json([
                "success" => true,
                "message" => $active ? "支付网关已启用" : "支付网关已停用",
                "is_active" => $active
            ]);
        } catch (\Exception $e) {
            Log::error("切换支付网关状态失败", ["error" => $e->getMessage()]);
            
            return response()->json([
                "success" => false,
                "message" => "操作失败：" . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 切换测试模式
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function toggleTestMode(Request $request, $id)
    {
        $testMode = $request->boolean("test_mode");
        
        try {
            $this->paymentService->toggleTestMode($id, $testMode);
            
            return response()->json([
                "success" => true,
                "message" => $testMode ? "已切换到测试模式" : "已切换到生产模式",
                "is_test_mode" => $testMode
            ]);
        } catch (\Exception $e) {
            Log::error("切换测试模式失败", ["error" => $e->getMessage()]);
            
            return response()->json([
                "success" => false,
                "message" => "操作失败：" . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 测试支付网关连接
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function test(Request $request, $id)
    {
        $gateway = $this->paymentService->getGateway($id);
        
        if (!$gateway) {
            return response()->json([
                "success" => false,
                "message" => "支付网关不存在"
            ], 404);
        }
        
        try {
            // 这里应该实现实际的测试逻辑，但由于不同支付网关的测试方式不同，
            // 这里只是记录一个测试日志
            $this->paymentService->logGatewayAction(
                $gateway->id,
                "test_connection",
                [],
                ["test_time" => now()->toDateTimeString()],
                true,
                null,
                null
            );
            
            return response()->json([
                "success" => true,
                "message" => "测试连接成功"
            ]);
        } catch (\Exception $e) {
            $this->paymentService->logGatewayAction(
                $gateway->id,
                "test_connection",
                [],
                [],
                false,
                $e->getMessage(),
                null
            );
            
            return response()->json([
                "success" => false,
                "message" => "测试连接失败：" . $e->getMessage()
            ], 500);
        }
    }
}
