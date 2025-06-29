<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Provider\Alipay;
use Yansongda\Pay\Provider\Wechat;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * ע�����
     *
     * @return void
     */
    public function register()
    {
        // ע��֧����
        $this->registerAlipay();
        
        // ע��΢��֧��
        $this->registerWechat();
        
        // ע����־ͨ��
        $this->registerLogChannel();
    }

    /**
     * ��������
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * ע��֧��������
     *
     * @return void
     */
    protected function registerAlipay()
    {
        $this->app->singleton('pay.alipay', function ($app) {
            $config = config('payment.alipay');
            
            // ����֧����
            Pay::config([
                'alipay' => [
                    // ����-֧��������� app_id
                    'app_id' => $config['app_id'],
                    // ����-Ӧ��˽Կ �ַ�����·��
                    'app_secret_cert' => $config['app_secret_cert'],
                    // ����-Ӧ�ù�Կ֤�� ·��
                    'app_public_cert_path' => $config['app_public_cert'],
                    // ����-֧������Կ֤�� ·��
                    'alipay_public_cert_path' => $config['alipay_public_cert'],
                    // ����-֧������֤�� ·��
                    'alipay_root_cert_path' => $config['alipay_root_cert'],
                    'return_url' => $config['return_url'],
                    'notify_url' => $config['notify_url'],
                    // ѡ��-������ģʽ�µ����̻� app_id
                    'service_provider_id' => '',
                    // ѡ��-Ĭ��Ϊ����ģʽ����ѡΪ�� normal, dev
                    'mode' => $config['sandbox'] ? 'dev' : 'normal',
                ],
            ]);
            
            return Pay::alipay();
        });
    }

    /**
     * ע��΢��֧������
     *
     * @return void
     */
    protected function registerWechat()
    {
        $this->app->singleton('pay.wechat', function ($app) {
            $config = config('payment.wechat');
            
            // ����΢��֧��
            Pay::config([
                'wechat' => [
                    // ����-�̻���
                    'mch_id' => $config['mch_id'],
                    // ����-�̻���Կ
                    'mch_secret_key' => $config['mch_secret_key'],
                    // ����-�̻�˽Կ �ַ�����·��
                    'mch_secret_cert' => $config['mch_secret_key'],
                    // ����-�̻���Կ֤��·��
                    'mch_public_cert_path' => $config['mch_public_cert_path'] ?? '',
                    // ����
                    'notify_url' => $config['notify_url'],
                    // ѡ��-���ں� �� app_id
                    'mp_app_id' => $config['mp_app_id'],
                    // ѡ��-С���� �� app_id
                    'mini_app_id' => $config['mini_app_id'],
                    // ѡ��-app �� app_id
                    'app_id' => $config['app_id'],
                    // ѡ��-�ϵ� app_id
                    'combine_app_id' => '',
                    // ѡ��-�ϵ��̻���
                    'combine_mch_id' => '',
                    // ѡ��-������ģʽ�£����̻��� app_id
                    'sub_app_id' => '',
                    // ѡ��-������ģʽ�£����̻����̻���
                    'sub_mch_id' => '',
                    // ѡ��-Ĭ��Ϊ����ģʽ����ѡΪ�� normal, dev
                    'mode' => 'normal',
                ],
            ]);
            
            return Pay::wechat();
        });
    }

    /**
     * ע����־ͨ��
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
