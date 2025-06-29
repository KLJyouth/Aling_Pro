<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePaymentGatewayTables extends Migration
{
    /**
     * ����Ǩ��
     *
     * @return void
     */
    public function up()
    {
        // ֧�����ر�
        Schema::create("payment_gateways", function (Blueprint $table) {
            $table->id();
            $table->string("name"); // ֧����������
            $table->string("code")->unique(); // ֧�����ش��룬��alipay, wechat, paypal
            $table->string("description")->nullable(); // ����
            $table->text("config"); // ������Ϣ��JSON��ʽ�洢
            $table->boolean("is_active")->default(false); // �Ƿ�����
            $table->boolean("is_test_mode")->default(false); // �Ƿ�Ϊ����ģʽ
            $table->string("logo")->nullable(); // ֧������logo
            $table->integer("sort_order")->default(0); // ����
            $table->timestamps();
            $table->softDeletes();

            $table->index("code");
            $table->index("is_active");
        });

        // ֧�����׼�¼��
        Schema::create("payment_transactions", function (Blueprint $table) {
            $table->id();
            $table->string("transaction_id")->unique(); // ����ID
            $table->foreignId("gateway_id")->constrained("payment_gateways"); // ֧������ID
            $table->string("order_id"); // ����ID
            $table->string("user_id")->nullable(); // �û�ID
            $table->decimal("amount", 10, 2); // ���
            $table->string("currency", 10)->default("CNY"); // ����
            $table->string("status"); // ״̬��pending, completed, failed, refunded
            $table->text("gateway_response")->nullable(); // ֧�����ط��ص�ԭʼ����
            $table->string("payment_method")->nullable(); // ֧����ʽ
            $table->string("client_ip")->nullable(); // �ͻ���IP
            $table->string("error_message")->nullable(); // ������Ϣ
            $table->timestamp("paid_at")->nullable(); // ֧��ʱ��
            $table->timestamps();
            $table->softDeletes();

            $table->index("transaction_id");
            $table->index("order_id");
            $table->index("user_id");
            $table->index("status");
            $table->index("paid_at");
        });

        // �˿��¼��
        Schema::create("payment_refunds", function (Blueprint $table) {
            $table->id();
            $table->string("refund_id")->unique(); // �˿�ID
            $table->foreignId("transaction_id")->constrained("payment_transactions"); // ����ID
            $table->decimal("amount", 10, 2); // �˿���
            $table->string("status"); // ״̬��pending, completed, failed
            $table->string("reason")->nullable(); // �˿�ԭ��
            $table->text("gateway_response")->nullable(); // ֧�����ط��ص�ԭʼ����
            $table->string("operator")->nullable(); // ������
            $table->timestamp("refunded_at")->nullable(); // �˿�ʱ��
            $table->timestamps();
            $table->softDeletes();

            $table->index("refund_id");
            $table->index("status");
        });

        // ֧��������־��
        Schema::create("payment_gateway_logs", function (Blueprint $table) {
            $table->id();
            $table->foreignId("gateway_id")->constrained("payment_gateways"); // ֧������ID
            $table->string("transaction_id")->nullable(); // ����ID
            $table->string("action"); // �������ͣ�payment, refund, notification, etc.
            $table->text("request")->nullable(); // ��������
            $table->text("response")->nullable(); // ��Ӧ����
            $table->string("ip_address")->nullable(); // IP��ַ
            $table->string("user_agent")->nullable(); // �û�����
            $table->boolean("is_success")->default(false); // �Ƿ�ɹ�
            $table->string("error_message")->nullable(); // ������Ϣ
            $table->timestamps();

            $table->index("gateway_id");
            $table->index("transaction_id");
            $table->index("action");
            $table->index("is_success");
            $table->index("created_at");
        });

        // ֧�����ñ�
        Schema::create("payment_settings", function (Blueprint $table) {
            $table->id();
            $table->string("key")->unique(); // ���ü�
            $table->text("value")->nullable(); // ����ֵ
            $table->string("group")->default("general"); // ����
            $table->string("description")->nullable(); // ����
            $table->boolean("is_system")->default(false); // �Ƿ�Ϊϵͳ����
            $table->timestamps();

            $table->index("key");
            $table->index("group");
        });

        // ����Ĭ������
        DB::table("payment_settings")->insert([
            [
                "key" => "payment_currency",
                "value" => "CNY",
                "group" => "general",
                "description" => "Ĭ��֧������",
                "is_system" => true,
                "created_at" => now(),
                "updated_at" => now()
            ],
            [
                "key" => "payment_expire_time",
                "value" => "30", // ��λ������
                "group" => "general",
                "description" => "֧������ʱ�䣨���ӣ�",
                "is_system" => true,
                "created_at" => now(),
                "updated_at" => now()
            ],
            [
                "key" => "auto_complete_payment",
                "value" => "true",
                "group" => "general",
                "description" => "�Զ����֧��",
                "is_system" => true,
                "created_at" => now(),
                "updated_at" => now()
            ],
            [
                "key" => "payment_notification_email",
                "value" => "admin@example.com",
                "group" => "notification",
                "description" => "֧��֪ͨ����",
                "is_system" => true,
                "created_at" => now(),
                "updated_at" => now()
            ],
            [
                "key" => "payment_success_template",
                "value" => "���Ķ��� {order_id} ��֧���ɹ�����{amount}",
                "group" => "notification",
                "description" => "֧���ɹ�֪ͨģ��",
                "is_system" => true,
                "created_at" => now(),
                "updated_at" => now()
            ],
            [
                "key" => "payment_failed_template",
                "value" => "���Ķ��� {order_id} ֧��ʧ�ܣ�ԭ��{reason}",
                "group" => "notification",
                "description" => "֧��ʧ��֪ͨģ��",
                "is_system" => true,
                "created_at" => now(),
                "updated_at" => now()
            ]
        ]);
    }

    /**
     * �ع�Ǩ��
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("payment_settings");
        Schema::dropIfExists("payment_gateway_logs");
        Schema::dropIfExists("payment_refunds");
        Schema::dropIfExists("payment_transactions");
        Schema::dropIfExists("payment_gateways");
    }
}
