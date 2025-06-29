<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Provider\Alipay;
use Yansongda\Pay\Provider\Wechat;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * 注册服务
     *
     * @return void
     */
    public function register()
    {
        // 注册支付宝
        $this->registerAlipay();
        
        // 注册微信支付
        $this->registerWechat();
        
        // 注册日志通道
        $this->registerLogChannel();
    }

    /**
     * 启动服务
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * 注册支付宝服务
     *
     * @return void
     */
    protected function registerAlipay()
    {
        $this->app->singleton('pay.alipay', function ($app) {
            $config = config('payment.alipay');
            
            // 配置支付宝
            Pay::config([
                'alipay' => [
                    // 必填-支付宝分配的 app_id
                    'app_id' => $config['app_id'],
                    // 必填-应用私钥 字符串或路径
                    'app_secret_cert' => $config['app_secret_cert'],
                    // 必填-应用公钥证书 路径
                    'app_public_cert_path' => $config['app_public_cert'],
                    // 必填-支付宝公钥证书 路径
                    'alipay_public_cert_path' => $config['alipay_public_cert'],
                    // 必填-支付宝根证书 路径
                    'alipay_root_cert_path' => $config['alipay_root_cert'],
                    'return_url' => $config['return_url'],
                    'notify_url' => $config['notify_url'],
                    // 选填-服务商模式下的子商户 app_id
                    'service_provider_id' => '',
                    // 选填-默认为正常模式。可选为： normal, dev
                    'mode' => $config['sandbox'] ? 'dev' : 'normal',
                ],
            ]);
            
            return Pay::alipay();
        });
    }

    /**
     * 注册微信支付服务
     *
     * @return void
     */
    protected function registerWechat()
    {
        $this->app->singleton('pay.wechat', function ($app) {
            $config = config('payment.wechat');
            
            // 配置微信支付
            Pay::config([
                'wechat' => [
                    // 必填-商户号
                    'mch_id' => $config['mch_id'],
                    // 必填-商户秘钥
                    'mch_secret_key' => $config['mch_secret_key'],
                    // 必填-商户私钥 字符串或路径
                    'mch_secret_cert' => $config['mch_secret_key'],
                    // 必填-商户公钥证书路径
                    'mch_public_cert_path' => $config['mch_public_cert_path'] ?? '',
                    // 必填
                    'notify_url' => $config['notify_url'],
                    // 选填-公众号 的 app_id
                    'mp_app_id' => $config['mp_app_id'],
                    // 选填-小程序 的 app_id
                    'mini_app_id' => $config['mini_app_id'],
                    // 选填-app 的 app_id
                    'app_id' => $config['app_id'],
                    // 选填-合单 app_id
                    'combine_app_id' => '',
                    // 选填-合单商户号
                    'combine_mch_id' => '',
                    // 选填-服务商模式下，子商户的 app_id
                    'sub_app_id' => '',
                    // 选填-服务商模式下，子商户的商户号
                    'sub_mch_id' => '',
                    // 选填-默认为正常模式。可选为： normal, dev
                    'mode' => 'normal',
                ],
            ]);
            
            return Pay::wechat();
        });
    }

    /**
     * 注册日志通道
     *
     * @return void
     */
    protected function registerLogChannel()
    {
        $this->app->configureMonologUsing(function ($monolog) {
            $monolog->pushHandler(
                new \Monolog\Handler\RotatingFileHandler(
                    storage_path('logs/payment.log'),
                    7,
                    \Monolog\Logger::INFO
                )
            );
            
            return $monolog;
        });
    }
}
