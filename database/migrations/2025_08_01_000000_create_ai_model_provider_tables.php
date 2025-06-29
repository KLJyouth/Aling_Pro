<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * 运行迁移
     */
    public function up(): void
    {
        // AI模型提供商表
        Schema::create('ai_model_providers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique()->comment('提供商代码');
            $table->string('name', 100)->comment('提供商名称');
            $table->text('description')->nullable()->comment('提供商描述');
            $table->string('logo_url')->nullable()->comment('提供商Logo');
            $table->string('api_base_url')->nullable()->comment('API基础URL');
            $table->json('capabilities')->nullable()->comment('支持的能力');
            $table->json('config_schema')->nullable()->comment('配置模式');
            $table->json('config')->nullable()->comment('提供商配置');
            $table->boolean('is_active')->default(true)->comment('是否激活');
            $table->boolean('is_official')->default(false)->comment('是否官方支持');
            $table->integer('sort_order')->default(0)->comment('排序顺序');
            $table->timestamps();
            $table->softDeletes();
        });

        // AI模型表
        Schema::create('ai_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('ai_model_providers')->onDelete('cascade')->comment('提供商ID');
            $table->string('model_id', 100)->comment('模型ID');
            $table->string('name', 100)->comment('模型名称');
            $table->text('description')->nullable()->comment('模型描述');
            $table->string('version', 50)->nullable()->comment('模型版本');
            $table->enum('type', ['text', 'image', 'audio', 'video', 'multimodal', 'embedding'])->comment('模型类型');
            $table->json('capabilities')->nullable()->comment('模型能力');
            $table->json('parameters')->nullable()->comment('默认参数');
            $table->json('limits')->nullable()->comment('限制信息');
            $table->decimal('price_input', 10, 6)->default(0)->comment('输入价格(每1000tokens)');
            $table->decimal('price_output', 10, 6)->default(0)->comment('输出价格(每1000tokens)');
            $table->boolean('is_active')->default(true)->comment('是否激活');
            $table->integer('sort_order')->default(0)->comment('排序顺序');
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['provider_id', 'model_id']);
        });

        // 智能体表
        Schema::create('ai_agents', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique()->comment('智能体代码');
            $table->string('name', 100)->comment('智能体名称');
            $table->text('description')->nullable()->comment('智能体描述');
            $table->string('provider', 50)->comment('提供商');
            $table->string('version', 50)->nullable()->comment('版本');
            $table->string('logo_url')->nullable()->comment('Logo URL');
            $table->json('capabilities')->nullable()->comment('能力');
            $table->json('parameters')->nullable()->comment('默认参数');
            $table->json('config')->nullable()->comment('配置');
            $table->string('api_endpoint')->nullable()->comment('API端点');
            $table->boolean('requires_auth')->default(true)->comment('是否需要认证');
            $table->boolean('is_active')->default(true)->comment('是否激活');
            $table->integer('sort_order')->default(0)->comment('排序顺序');
            $table->timestamps();
            $table->softDeletes();
        });

        // API密钥表
        Schema::create('ai_api_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('ai_model_providers')->onDelete('cascade')->comment('提供商ID');
            $table->string('name', 100)->comment('密钥名称');
            $table->text('api_key')->comment('API密钥');
            $table->text('api_secret')->nullable()->comment('API密钥');
            $table->json('permissions')->nullable()->comment('权限');
            $table->decimal('monthly_quota', 10, 2)->nullable()->comment('月度配额');
            $table->decimal('used_quota', 10, 2)->default(0)->comment('已用配额');
            $table->dateTime('last_used_at')->nullable()->comment('最后使用时间');
            $table->dateTime('expires_at')->nullable()->comment('过期时间');
            $table->boolean('is_active')->default(true)->comment('是否激活');
            $table->timestamps();
            $table->softDeletes();
        });

        // 使用记录表
        Schema::create('ai_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('ai_model_providers')->onDelete('cascade')->comment('提供商ID');
            $table->foreignId('model_id')->nullable()->constrained('ai_models')->onDelete('set null')->comment('模型ID');
            $table->foreignId('api_key_id')->nullable()->constrained('ai_api_keys')->onDelete('set null')->comment('API密钥ID');
            $table->string('request_id', 100)->nullable()->comment('请求ID');
            $table->string('user_id', 100)->nullable()->comment('用户ID');
            $table->string('session_id', 100)->nullable()->comment('会话ID');
            $table->enum('request_type', ['completion', 'chat', 'image', 'embedding', 'audio', 'video', 'function'])->comment('请求类型');
            $table->json('request_data')->nullable()->comment('请求数据');
            $table->json('response_data')->nullable()->comment('响应数据');
            $table->integer('prompt_tokens')->default(0)->comment('提示词tokens');
            $table->integer('completion_tokens')->default(0)->comment('补全tokens');
            $table->integer('total_tokens')->default(0)->comment('总tokens');
            $table->decimal('cost', 10, 6)->default(0)->comment('成本');
            $table->integer('latency_ms')->nullable()->comment('延迟(毫秒)');
            $table->boolean('is_cached')->default(false)->comment('是否使用缓存');
            $table->boolean('is_success')->default(true)->comment('是否成功');
            $table->text('error_message')->nullable()->comment('错误信息');
            $table->string('ip_address', 45)->nullable()->comment('IP地址');
            $table->timestamps();
            
            $table->index(['provider_id', 'model_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['session_id', 'created_at']);
            $table->index(['request_type', 'created_at']);
        });

        // AI接口配置表
        Schema::create('ai_interface_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique()->comment('配置键');
            $table->text('value')->nullable()->comment('配置值');
            $table->string('group', 50)->default('general')->comment('配置组');
            $table->text('description')->nullable()->comment('描述');
            $table->boolean('is_system')->default(false)->comment('是否系统配置');
            $table->boolean('is_encrypted')->default(false)->comment('是否加密');
            $table->timestamps();
            
            $table->index('group');
        });

        // 插入默认数据
        $this->insertDefaultData();
    }

    /**
     * 回滚迁移
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_usage_logs');
        Schema::dropIfExists('ai_api_keys');
        Schema::dropIfExists('ai_agents');
        Schema::dropIfExists('ai_models');
        Schema::dropIfExists('ai_model_providers');
        Schema::dropIfExists('ai_interface_settings');
    }

    /**
     * 插入默认数据
     */
    private function insertDefaultData(): void
    {
        // 插入默认提供商
        DB::table('ai_model_providers')->insert([
            [
                'code' => 'openai',
                'name' => 'OpenAI',
                'description' => 'OpenAI API提供商，支持GPT系列模型和DALL-E等',
                'logo_url' => '/assets/images/ai-providers/openai.png',
                'api_base_url' => 'https://api.openai.com/v1',
                'capabilities' => json_encode(['text', 'chat', 'image', 'embedding', 'audio']),
                'config_schema' => json_encode([
                    'api_key' => ['type' => 'password', 'required' => true, 'label' => 'API密钥'],
                    'organization' => ['type' => 'text', 'required' => false, 'label' => '组织ID']
                ]),
                'is_active' => true,
                'is_official' => true,
                'sort_order' => 10,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'anthropic',
                'name' => 'Anthropic',
                'description' => 'Anthropic API提供商，支持Claude系列模型',
                'logo_url' => '/assets/images/ai-providers/anthropic.png',
                'api_base_url' => 'https://api.anthropic.com',
                'capabilities' => json_encode(['text', 'chat']),
                'config_schema' => json_encode([
                    'api_key' => ['type' => 'password', 'required' => true, 'label' => 'API密钥']
                ]),
                'is_active' => true,
                'is_official' => true,
                'sort_order' => 20,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'zhipu',
                'name' => '智谱AI',
                'description' => '智谱AI API提供商，支持GLM系列模型',
                'logo_url' => '/assets/images/ai-providers/zhipu.png',
                'api_base_url' => 'https://open.bigmodel.cn/api/paas/v3',
                'capabilities' => json_encode(['text', 'chat', 'embedding']),
                'config_schema' => json_encode([
                    'api_key' => ['type' => 'password', 'required' => true, 'label' => 'API密钥']
                ]),
                'is_active' => true,
                'is_official' => true,
                'sort_order' => 30,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'baidu',
                'name' => '百度文心',
                'description' => '百度文心API提供商，支持文心一言系列模型',
                'logo_url' => '/assets/images/ai-providers/baidu.png',
                'api_base_url' => 'https://aip.baidubce.com/rpc/2.0/ai_custom',
                'capabilities' => json_encode(['text', 'chat', 'image']),
                'config_schema' => json_encode([
                    'api_key' => ['type' => 'text', 'required' => true, 'label' => 'API Key'],
                    'secret_key' => ['type' => 'password', 'required' => true, 'label' => 'Secret Key']
                ]),
                'is_active' => true,
                'is_official' => true,
                'sort_order' => 40,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'aliyun',
                'name' => '阿里通义',
                'description' => '阿里通义API提供商，支持通义千问系列模型',
                'logo_url' => '/assets/images/ai-providers/aliyun.png',
                'api_base_url' => 'https://dashscope.aliyuncs.com',
                'capabilities' => json_encode(['text', 'chat', 'image']),
                'config_schema' => json_encode([
                    'api_key' => ['type' => 'password', 'required' => true, 'label' => 'API密钥']
                ]),
                'is_active' => true,
                'is_official' => true,
                'sort_order' => 50,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'huawei',
                'name' => '华为盘古',
                'description' => '华为盘古API提供商，支持盘古系列大模型',
                'logo_url' => '/assets/images/ai-providers/huawei.png',
                'api_base_url' => 'https://pangu-api.huaweicloud.com',
                'capabilities' => json_encode(['text', 'chat']),
                'config_schema' => json_encode([
                    'api_key' => ['type' => 'password', 'required' => true, 'label' => 'API密钥'],
                    'project_id' => ['type' => 'text', 'required' => true, 'label' => '项目ID']
                ]),
                'is_active' => true,
                'is_official' => true,
                'sort_order' => 60,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        // 插入默认智能体
        DB::table('ai_agents')->insert([
            [
                'code' => 'koudi',
                'name' => '扣子',
                'description' => '扣子智能体，提供多种智能服务',
                'provider' => 'koudi',
                'version' => '1.0',
                'logo_url' => '/assets/images/ai-agents/koudi.png',
                'capabilities' => json_encode(['chat', 'task', 'knowledge']),
                'api_endpoint' => 'https://api.koudi.cn/v1',
                'requires_auth' => true,
                'is_active' => true,
                'sort_order' => 10,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'huawei_agent',
                'name' => '华为智能体',
                'description' => '华为智能体，基于盘古大模型',
                'provider' => 'huawei',
                'version' => '1.0',
                'logo_url' => '/assets/images/ai-agents/huawei.png',
                'capabilities' => json_encode(['chat', 'knowledge', 'analysis']),
                'api_endpoint' => 'https://agent-api.huaweicloud.com/v1',
                'requires_auth' => true,
                'is_active' => true,
                'sort_order' => 20,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'tongyi_agent',
                'name' => '通义智能体',
                'description' => '阿里通义智能体，提供多种智能服务',
                'provider' => 'aliyun',
                'version' => '1.0',
                'logo_url' => '/assets/images/ai-agents/tongyi.png',
                'capabilities' => json_encode(['chat', 'task', 'knowledge', 'analysis']),
                'api_endpoint' => 'https://tongyi-agent.aliyuncs.com/v1',
                'requires_auth' => true,
                'is_active' => true,
                'sort_order' => 30,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        // 插入默认设置
        DB::table('ai_interface_settings')->insert([
            [
                'key' => 'default_provider',
                'value' => 'openai',
                'group' => 'general',
                'description' => '默认AI提供商',
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'default_model',
                'value' => 'gpt-3.5-turbo',
                'group' => 'general',
                'description' => '默认AI模型',
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'enable_usage_logging',
                'value' => '1',
                'group' => 'logging',
                'description' => '启用使用日志记录',
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'enable_cost_tracking',
                'value' => '1',
                'group' => 'billing',
                'description' => '启用成本跟踪',
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'max_tokens_per_request',
                'value' => '4096',
                'group' => 'limits',
                'description' => '每个请求的最大tokens数',
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'api_request_timeout',
                'value' => '30',
                'group' => 'limits',
                'description' => 'API请求超时时间(秒)',
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'enable_response_caching',
                'value' => '1',
                'group' => 'performance',
                'description' => '启用响应缓存',
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'response_cache_ttl',
                'value' => '3600',
                'group' => 'performance',
                'description' => '响应缓存有效期(秒)',
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }
}; 