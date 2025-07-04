<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 运行迁移
     *
     * @return void
     */
    public function up()
    {
        // API接口表
        Schema::create('api_interfaces', function (Blueprint \) {
            \->id();
            \->string('name', 100)->comment('接口名称');
            \->string('path')->comment('接口路径');
            \->string('method', 10)->comment('请求方法');
            \->string('version', 10)->default('v1')->comment('接口版本');
            \->string('category', 50)->default('general')->comment('接口分类');
            \->text('description')->nullable()->comment('接口描述');
            \->integer('rate_limit')->default(100)->comment('速率限制');
            \->integer('rate_window')->default(60)->comment('速率窗口(秒)');
            \->boolean('requires_auth')->default(true)->comment('是否需要认证');
            \->string('required_role')->nullable()->comment('所需角色');
            \->string('status', 20)->default('active')->comment('状态');
            \->timestamps();
            
            // 索引
            \->unique(['path', 'method', 'version']);
            \->index('category');
            \->index('status');
        });

        // API风控规则表
        Schema::create('api_risk_rules', function (Blueprint \) {
            \->id();
            \->string('name', 100)->comment('规则名称');
            \->string('rule_type', 20)->comment('规则类型：frequency, amount, behavior, ip, device, geo');
            \->json('conditions')->comment('条件');
            \->decimal('threshold_value', 10, 2)->nullable()->comment('阈值');
            \->integer('time_window')->nullable()->comment('时间窗口(秒)');
            \->string('action', 20)->comment('动作：warn, block, suspend, review');
            \->integer('priority')->default(5)->comment('优先级');
            \->string('status', 20)->default('active')->comment('状态');
            \->text('description')->nullable()->comment('描述');
            \->foreignId('created_by')->nullable()->constrained('admin_users')->comment('创建人');
            \->timestamps();
            
            // 索引
            \->index('rule_type');
            \->index('status');
            \->index('priority');
        });

        // API风控事件表
        Schema::create('api_risk_events', function (Blueprint \) {
            \->id();
            \->foreignId('user_id')->nullable()->constrained('users')->comment('用户ID');
            \->foreignId('rule_id')->constrained('api_risk_rules')->comment('规则ID');
            \->string('event_type', 50)->comment('事件类型');
            \->string('risk_level', 20)->comment('风险等级：low, medium, high, critical');
            \->integer('risk_score')->default(0)->comment('风险分数');
            \->text('description')->nullable()->comment('描述');
            \->json('trigger_data')->nullable()->comment('触发数据');
            \->string('action_taken', 50)->nullable()->comment('采取的动作');
            \->string('status', 20)->default('pending')->comment('状态：pending, processed, ignored, false_positive');
            \->foreignId('processed_by')->nullable()->constrained('admin_users')->comment('处理人');
            \->timestamp('processed_at')->nullable()->comment('处理时间');
            \->text('notes')->nullable()->comment('备注');
            \->timestamps();
            
            // 索引
            \->index(['user_id', 'created_at']);
            \->index('status');
            \->index('risk_level');
            \->index('event_type');
        });

        // API黑名单表
        Schema::create('api_blacklists', function (Blueprint \) {
            \->id();
            \->string('list_type', 20)->comment('黑名单类型：ip, email, phone, device, keyword');
            \->string('value')->comment('黑名单值');
            \->text('reason')->nullable()->comment('原因');
            \->string('source', 20)->default('manual')->comment('来源：manual, auto, import');
            \->timestamp('expires_at')->nullable()->comment('过期时间');
            \->string('status', 20)->default('active')->comment('状态：active, expired, removed');
            \->foreignId('created_by')->nullable()->constrained('admin_users')->comment('创建人');
            \->timestamps();
            
            // 索引
            \->unique(['list_type', 'value']);
            \->index(['list_type', 'value']);
            \->index('expires_at');
            \->index('status');
        });

        // API访问控制表
        Schema::create('api_access_controls', function (Blueprint \) {
            \->id();
            \->foreignId('interface_id')->constrained('api_interfaces')->comment('接口ID');
            \->string('control_type', 20)->comment('控制类型：rate_limit, ip_restriction, time_restriction, geo_restriction');
            \->json('control_config')->comment('控制配置');
            \->string('status', 20)->default('active')->comment('状态');
            \->text('description')->nullable()->comment('描述');
            \->foreignId('created_by')->nullable()->constrained('admin_users')->comment('创建人');
            \->timestamps();
            
            // 索引
            \->index(['interface_id', 'control_type']);
            \->index('status');
        });

        // API安全审计表
        Schema::create('api_security_audits', function (Blueprint \) {
            \->id();
            \->foreignId('interface_id')->nullable()->constrained('api_interfaces')->comment('接口ID');
            \->string('audit_type', 50)->comment('审计类型');
            \->string('severity', 20)->comment('严重性：low, medium, high, critical');
            \->text('description')->comment('描述');
            \->json('details')->nullable()->comment('详情');
            \->string('status', 20)->default('open')->comment('状态：open, in_progress, resolved, closed');
            \->foreignId('assigned_to')->nullable()->constrained('admin_users')->comment('分配给');
            \->timestamp('resolved_at')->nullable()->comment('解决时间');
            \->text('resolution')->nullable()->comment('解决方案');
            \->timestamps();
            
            // 索引
            \->index('audit_type');
            \->index('severity');
            \->index('status');
        });

        // API异常检测配置表
        Schema::create('api_anomaly_configs', function (Blueprint \) {
            \->id();
            \->string('name', 100)->comment('配置名称');
            \->foreignId('interface_id')->nullable()->constrained('api_interfaces')->comment('接口ID');
            \->string('metric_type', 50)->comment('指标类型：volume, latency, error_rate, payload_size, etc');
            \->string('detection_method', 50)->comment('检测方法：threshold, z_score, mad, ewma, etc');
            \->json('parameters')->comment('参数');
            \->string('sensitivity', 20)->default('medium')->comment('敏感度：low, medium, high');
            \->string('action', 20)->default('alert')->comment('动作：alert, block, throttle');
            \->string('status', 20)->default('active')->comment('状态');
            \->text('description')->nullable()->comment('描述');
            \->timestamps();
            
            // 索引
            \->index(['interface_id', 'metric_type']);
            \->index('status');
        });

        // API异常事件表
        Schema::create('api_anomaly_events', function (Blueprint \) {
            \->id();
            \->foreignId('config_id')->constrained('api_anomaly_configs')->comment('配置ID');
            \->foreignId('interface_id')->nullable()->constrained('api_interfaces')->comment('接口ID');
            \->string('event_type', 50)->comment('事件类型');
            \->decimal('observed_value', 15, 4)->comment('观测值');
            \->decimal('expected_value', 15, 4)->nullable()->comment('预期值');
            \->decimal('deviation', 10, 4)->nullable()->comment('偏差');
            \->string('severity', 20)->comment('严重性：low, medium, high, critical');
            \->json('context_data')->nullable()->comment('上下文数据');
            \->string('action_taken', 50)->nullable()->comment('采取的动作');
            \->string('status', 20)->default('open')->comment('状态：open, investigating, resolved, false_positive');
            \->foreignId('processed_by')->nullable()->constrained('admin_users')->comment('处理人');
            \->timestamp('processed_at')->nullable()->comment('处理时间');
            \->text('notes')->nullable()->comment('备注');
            \->timestamps();
            
            // 索引
            \->index(['interface_id', 'created_at']);
            \->index('event_type');
            \->index('severity');
            \->index('status');
        });
    }

    /**
     * 回滚迁移
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('api_anomaly_events');
        Schema::dropIfExists('api_anomaly_configs');
        Schema::dropIfExists('api_security_audits');
        Schema::dropIfExists('api_access_controls');
        Schema::dropIfExists('api_blacklists');
        Schema::dropIfExists('api_risk_events');
        Schema::dropIfExists('api_risk_rules');
        Schema::dropIfExists('api_interfaces');
    }
};
