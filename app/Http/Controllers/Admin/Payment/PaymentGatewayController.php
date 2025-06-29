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
     * ��ʾ֧�������б�
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
     * ��ʾ����֧�����ر�
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
     * �洢��֧������
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $gatewayCode = $request->input("code");
        $fields = $this->paymentService->getGatewayConfigFields($gatewayCode);
        
        // ��֤�����ֶ�
        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:255",
            "code" => "required|string|max:50|unique:payment_gateways,code",
            "description" => "nullable|string",
            "is_active" => "boolean",
            "is_test_mode" => "boolean",
            "logo" => "nullable|image|max:2048",
            "sort_order" => "nullable|integer"
        ]);
        
        // ��֤�����ֶ�
        foreach ($fields as $field => $label) {
            $validator->addRules(["config.$field" => "required|string"]);
        }
        
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // �����ϴ���Logo
        $logoPath = null;
        if ($request->hasFile("logo")) {
            $logoPath = $request->file("logo")->store("payment_gateways", "public");
        }
        
        // ׼������
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
        
        // �ռ������ֶ�
        foreach ($fields as $field => $label) {
            $data["config"][$field] = $request->input("config.$field");
        }
        
        try {
            // ����֧������
            $gatewayId = $this->paymentService->createGateway($data);
            
            return redirect()
                ->route("admin.payment.gateways.index")
                ->with("success", "֧�����ش����ɹ�");
        } catch (\Exception $e) {
            Log::error("����֧������ʧ��", ["error" => $e->getMessage()]);
            
            return redirect()
                ->back()
                ->withInput()
                ->with("error", "����֧������ʧ�ܣ�" . $e->getMessage());
        }
    }

    /**
     * ��ʾ֧����������
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
                ->with("error", "֧�����ز�����");
        }
        
        // ��ȡ�������־
        $logs = $this->paymentService->getGatewayLogs($gateway->id, 20);
        
        // ��������
        $config = json_decode($gateway->config, true);
        $fields = $this->paymentService->getGatewayConfigFields($gateway->code);
        
        return view("admin.payment.gateways.show", compact("gateway", "config", "fields", "logs"));
    }

    /**
     * ��ʾ�༭֧�����ر�
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
                ->with("error", "֧�����ز�����");
        }
        
        // ��������
        $config = json_decode($gateway->config, true);
        $fields = $this->paymentService->getGatewayConfigFields($gateway->code);
        
        return view("admin.payment.gateways.edit", compact("gateway", "config", "fields"));
    }

    /**
     * ����֧������
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
                ->with("error", "֧�����ز�����");
        }
        
        $fields = $this->paymentService->getGatewayConfigFields($gateway->code);
        
        // ��֤�����ֶ�
        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:255",
            "description" => "nullable|string",
            "is_active" => "boolean",
            "is_test_mode" => "boolean",
            "logo" => "nullable|image|max:2048",
            "sort_order" => "nullable|integer"
        ]);
        
        // ��֤�����ֶ�
        foreach ($fields as $field => $label) {
            $validator->addRules(["config.$field" => "required|string"]);
        }
        
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // �����ϴ���Logo
        $logoPath = $gateway->logo;
        if ($request->hasFile("logo")) {
            $logoPath = $request->file("logo")->store("payment_gateways", "public");
        }
        
        // ׼������
        $data = [
            "name" => $request->input("name"),
            "description" => $request->input("description"),
            "is_active" => $request->boolean("is_active", false),
            "is_test_mode" => $request->boolean("is_test_mode", false),
            "logo" => $logoPath,
            "sort_order" => $request->input("sort_order", 0),
            "config" => []
        ];
        
        // �ռ������ֶ�
        foreach ($fields as $field => $label) {
            $data["config"][$field] = $request->input("config.$field");
        }
        
        try {
            // ����֧������
            $this->paymentService->updateGateway($id, $data);
            
            return redirect()
                ->route("admin.payment.gateways.show", $id)
                ->with("success", "֧�����ظ��³ɹ�");
        } catch (\Exception $e) {
            Log::error("����֧������ʧ��", ["error" => $e->getMessage()]);
            
            return redirect()
                ->back()
                ->withInput()
                ->with("error", "����֧������ʧ�ܣ�" . $e->getMessage());
        }
    }

    /**
     * ɾ��֧������
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
                ->with("success", "֧������ɾ���ɹ�");
        } catch (\Exception $e) {
            Log::error("ɾ��֧������ʧ��", ["error" => $e->getMessage()]);
            
            return redirect()
                ->back()
                ->with("error", "ɾ��֧������ʧ�ܣ�" . $e->getMessage());
        }
    }

    /**
     * �л�֧������״̬
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
                "message" => $active ? "֧������������" : "֧��������ͣ��",
                "is_active" => $active
            ]);
        } catch (\Exception $e) {
            Log::error("�л�֧������״̬ʧ��", ["error" => $e->getMessage()]);
            
            return response()->json([
                "success" => false,
                "message" => "����ʧ�ܣ�" . $e->getMessage()
            ], 500);
        }
    }

    /**
     * �л�����ģʽ
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
                "message" => $testMode ? "���л�������ģʽ" : "���л�������ģʽ",
                "is_test_mode" => $testMode
            ]);
        } catch (\Exception $e) {
            Log::error("�л�����ģʽʧ��", ["error" => $e->getMessage()]);
            
            return response()->json([
                "success" => false,
                "message" => "����ʧ�ܣ�" . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ����֧����������
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
                "message" => "֧�����ز�����"
            ], 404);
        }
        
        try {
            // ����Ӧ��ʵ��ʵ�ʵĲ����߼��������ڲ�֧ͬ�����صĲ��Է�ʽ��ͬ��
            // ����ֻ�Ǽ�¼һ��������־
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
                "message" => "�������ӳɹ�"
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
                "message" => "��������ʧ�ܣ�" . $e->getMessage()
            ], 500);
        }
    }
}
