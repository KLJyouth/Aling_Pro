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
        // 管理员角色表
        Schema::create('admin_roles', function (Blueprint \) {
            \->id();
            \->string('name', 50)->unique()->comment('角色名称');
            \->string('display_name', 100)->comment('显示名称');
            \->json('permissions')->nullable()->comment('权限');
            \->string('status', 20)->default('active')->comment('状态');
            \->string('description')->nullable()->comment('描述');
            \->integer('sort_order')->default(0)->comment('排序');
            \->timestamps();
            \->softDeletes();
        });

        // 管理员表
        Schema::create('admin_users', function (Blueprint \) {
            \->id();
            \->string('username', 50)->unique()->comment('用户名');
            \->string('email', 100)->unique()->comment('邮箱');
            \->string('phone', 20)->nullable()->comment('电话');
            \->string('password')->comment('密码');
            \->string('avatar')->nullable()->comment('头像');
            \->string('status', 20)->default('active')->comment('状态');
            \->foreignId('role_id')->constrained('admin_roles')->comment('角色ID');
            \->timestamp('last_login_at')->nullable()->comment('最后登录时间');
            \->string('last_login_ip', 45)->nullable()->comment('最后登录IP');
            \->integer('login_count')->default(0)->comment('登录次数');
            \->string('risk_level', 20)->default('low')->comment('风险等级');
            \->json('metadata')->nullable()->comment('元数据');
            \->rememberToken();
            \->timestamps();
            \->softDeletes();
        });

        // 管理员登录日志表
        Schema::create('admin_login_logs', function (Blueprint \) {
            \->id();
            \->foreignId('admin_id')->constrained('admin_users')->comment('管理员ID');
            \->string('ip_address', 45)->comment('IP地址');
            \->string('user_agent')->nullable()->comment('用户代理');
            \->string('device_type', 20)->nullable()->comment('设备类型');
            \->string('location')->nullable()->comment('位置');
            \->string('status', 20)->comment('状态：success, failed');
            \->string('failure_reason')->nullable()->comment('失败原因');
            \->timestamps();
            
            // 索引
            \->index(['admin_id', 'created_at']);
            \->index('ip_address');
            \->index('status');
        });

        // 管理员操作日志表
        Schema::create('admin_operation_logs', function (Blueprint \) {
            \->id();
            \->foreignId('admin_id')->constrained('admin_users')->comment('管理员ID');
            \->string('module', 50)->comment('模块');
            \->string('action', 50)->comment('操作');
            \->string('method', 10)->comment('请求方法');
            \->string('url')->comment('请求URL');
            \->json('request_data')->nullable()->comment('请求数据');
            \->string('ip_address', 45)->comment('IP地址');
            \->string('user_agent')->nullable()->comment('用户代理');
            \->timestamps();
            
            // 索引
            \->index(['admin_id', 'created_at']);
            \->index('module');
            \->index('action');
        });

        // 管理员授权表
        Schema::create('admin_permissions', function (Blueprint \) {
            \->id();
            \->string('name', 50)->unique()->comment('权限名称');
            \->string('display_name', 100)->comment('显示名称');
            \->string('module', 50)->comment('模块');
            \->string('description')->nullable()->comment('描述');
            \->integer('sort_order')->default(0)->comment('排序');
            \->timestamps();
            
            // 索引
            \->index('module');
        });

        // 管理员权限组表
        Schema::create('admin_permission_groups', function (Blueprint \) {
            \->id();
            \->string('name', 50)->unique()->comment('权限组名称');
            \->string('display_name', 100)->comment('显示名称');
            \->string('description')->nullable()->comment('描述');
            \->integer('sort_order')->default(0)->comment('排序');
            \->timestamps();
        });

        // 权限与权限组关联表
        Schema::create('admin_permission_group_items', function (Blueprint \) {
            \->id();
            \->foreignId('group_id')->constrained('admin_permission_groups')->comment('权限组ID');
            \->foreignId('permission_id')->constrained('admin_permissions')->comment('权限ID');
            \->timestamps();
            
            // 索引
            \->unique(['group_id', 'permission_id']);
        });
    }

    /**
     * 回滚迁移
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_permission_group_items');
        Schema::dropIfExists('admin_permission_groups');
        Schema::dropIfExists('admin_permissions');
        Schema::dropIfExists('admin_operation_logs');
        Schema::dropIfExists('admin_login_logs');
        Schema::dropIfExists('admin_users');
        Schema::dropIfExists('admin_roles');
    }
};
