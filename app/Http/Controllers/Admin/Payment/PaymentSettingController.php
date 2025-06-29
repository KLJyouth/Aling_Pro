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
     * 显示支付设置页面
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // 按分组获取设置
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
     * 更新支付设置
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            $settings = $request->except("_token", "_method");
            
            foreach ($settings as $key => $value) {
                // 获取设置组
                $group = explode(".", $key)[0];
                $settingKey = explode(".", $key)[1];
                
                $this->paymentService->updateSetting($settingKey, $value, $group);
            }
            
            // 清除设置缓存
            $this->paymentService->clearSettingCache();
            
            return redirect()
                ->route("admin.payment.settings.index")
                ->with("success", "支付设置已更新");
        } catch (\Exception $e) {
            Log::error("更新支付设置失败", ["error" => $e->getMessage()]);
            
            return redirect()
                ->back()
                ->withInput()
                ->with("error", "更新支付设置失败：" . $e->getMessage());
        }
    }

    /**
     * 显示添加新设置表单
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view("admin.payment.settings.create");
    }

    /**
     * 存储新设置
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
                ->with("success", "新设置已添加");
        } catch (\Exception $e) {
            Log::error("添加支付设置失败", ["error" => $e->getMessage()]);
            
            return redirect()
                ->back()
                ->withInput()
                ->with("error", "添加支付设置失败：" . $e->getMessage());
        }
    }

    /**
     * 删除设置
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            // 检查是否为系统设置
            $setting = \DB::table("payment_settings")
                ->where("id", $id)
                ->first();
            
            if (!$setting) {
                return redirect()
                    ->route("admin.payment.settings.index")
                    ->with("error", "设置不存在");
            }
            
            if ($setting->is_system) {
                return redirect()
                    ->route("admin.payment.settings.index")
                    ->with("error", "系统设置不能删除");
            }
            
            \DB::table("payment_settings")
                ->where("id", $id)
                ->delete();
            
            // 清除缓存
            $this->paymentService->clearSettingCache($setting->key);
            
            return redirect()
                ->route("admin.payment.settings.index")
                ->with("success", "设置已删除");
        } catch (\Exception $e) {
            Log::error("删除支付设置失败", ["error" => $e->getMessage()]);
            
            return redirect()
                ->back()
                ->with("error", "删除支付设置失败：" . $e->getMessage());
        }
    }
}
