<?php

namespace App\Http\Controllers\Admin\Payment;

use App\Http\Controllers\Controller;
use App\Services\Payment\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentSettingController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentGatewayService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * ��ʾ֧������ҳ��
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // �������ȡ����
        $generalSettings = $this->paymentService->getSettingsByGroup("general");
        $notificationSettings = $this->paymentService->getSettingsByGroup("notification");
        $securitySettings = $this->paymentService->getSettingsByGroup("security");
        
        return view("admin.payment.settings.index", compact(
            "generalSettings",
            "notificationSettings",
            "securitySettings"
        ));
    }

    /**
     * ����֧������
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            $settings = $request->except("_token", "_method");
            
            foreach ($settings as $key => $value) {
                // ��ȡ������
                $group = explode(".", $key)[0];
                $settingKey = explode(".", $key)[1];
                
                $this->paymentService->updateSetting($settingKey, $value, $group);
            }
            
            // ������û���
            $this->paymentService->clearSettingCache();
            
            return redirect()
                ->route("admin.payment.settings.index")
                ->with("success", "֧�������Ѹ���");
        } catch (\Exception $e) {
            Log::error("����֧������ʧ��", ["error" => $e->getMessage()]);
            
            return redirect()
                ->back()
                ->withInput()
                ->with("error", "����֧������ʧ�ܣ�" . $e->getMessage());
        }
    }

    /**
     * ��ʾ��������ñ�
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view("admin.payment.settings.create");
    }

    /**
     * �洢������
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            "key" => "required|string|max:255|unique:payment_settings,key",
            "value" => "required|string",
            "group" => "required|string|max:50",
            "description" => "nullable|string"
        ]);
        
        try {
            $this->paymentService->updateSetting(
                $request->input("key"),
                $request->input("value"),
                $request->input("group"),
                $request->input("description")
            );
            
            return redirect()
                ->route("admin.payment.settings.index")
                ->with("success", "�����������");
        } catch (\Exception $e) {
            Log::error("���֧������ʧ��", ["error" => $e->getMessage()]);
            
            return redirect()
                ->back()
                ->withInput()
                ->with("error", "���֧������ʧ�ܣ�" . $e->getMessage());
        }
    }

    /**
     * ɾ������
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            // ����Ƿ�Ϊϵͳ����
            $setting = \DB::table("payment_settings")
                ->where("id", $id)
                ->first();
            
            if (!$setting) {
                return redirect()
                    ->route("admin.payment.settings.index")
                    ->with("error", "���ò�����");
            }
            
            if ($setting->is_system) {
                return redirect()
                    ->route("admin.payment.settings.index")
                    ->with("error", "ϵͳ���ò���ɾ��");
            }
            
            \DB::table("payment_settings")
                ->where("id", $id)
                ->delete();
            
            // �������
            $this->paymentService->clearSettingCache($setting->key);
            
            return redirect()
                ->route("admin.payment.settings.index")
                ->with("success", "������ɾ��");
        } catch (\Exception $e) {
            Log::error("ɾ��֧������ʧ��", ["error" => $e->getMessage()]);
            
            return redirect()
                ->back()
                ->with("error", "ɾ��֧������ʧ�ܣ�" . $e->getMessage());
        }
    }
}
