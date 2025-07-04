<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * 运行迁移
     * 
     * @return void
     */
    public function up()
    {
        Schema::create("oauth_providers", function (Blueprint $table) {
            $table->id();
            $table->string("name"); // 提供商名称
            $table->string("identifier")->unique(); // 提供商标识符
            $table->string("icon")->nullable(); // 图标
            $table->text("description")->nullable(); // 描述
            $table->boolean("is_active")->default(true); // 是否启用
            $table->string("client_id")->nullable(); // 客户端ID
            $table->text("client_secret")->nullable(); // 客户端密钥
            $table->string("redirect_url")->nullable(); // 回调URL
            $table->string("auth_url")->nullable(); // 授权URL
            $table->string("token_url")->nullable(); // 令牌URL
            $table->string("user_info_url")->nullable(); // 用户信息URL
            $table->json("scopes")->nullable(); // 权限范围
            $table->json("config")->nullable(); // 额外配置
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create("oauth_user_accounts", function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained()->onDelete("cascade");
            $table->foreignId("provider_id")->constrained("oauth_providers")->onDelete("cascade");
            $table->string("provider_user_id"); // 第三方用户ID
            $table->string("nickname")->nullable(); // 昵称
            $table->string("name")->nullable(); // 姓名
            $table->string("email")->nullable(); // 邮箱
            $table->string("avatar")->nullable(); // 头像
            $table->text("access_token")->nullable(); // 访问令牌
            $table->text("refresh_token")->nullable(); // 刷新令牌
            $table->timestamp("token_expires_at")->nullable(); // 令牌过期时间
            $table->json("user_data")->nullable(); // 原始用户数据
            $table->timestamps();

            // 索引
            $table->unique(["provider_id", "provider_user_id"]);
        });

        Schema::create("oauth_logs", function (Blueprint $table) {
            $table->id();
            $table->foreignId("provider_id")->nullable()->constrained("oauth_providers")->nullOnDelete();
            $table->foreignId("user_id")->nullable()->constrained()->nullOnDelete();
            $table->string("action"); // 操作类型：login, register, link, unlink
            $table->string("status"); // 状态：success, failed
            $table->string("ip_address")->nullable(); // IP地址
            $table->text("user_agent")->nullable(); // 用户代理
            $table->text("error_message")->nullable(); // 错误信息
            $table->json("request_data")->nullable(); // 请求数据
            $table->json("response_data")->nullable(); // 响应数据
            $table->timestamps();
        });

        // 插入默认的OAuth提供商
        DB::table("oauth_providers")->insert([
            [
                "name" => "微信",
                "identifier" => "wechat",
                "icon" => "fab fa-weixin",
                "description" => "微信登录",
                "is_active" => true,
                "auth_url" => "https://open.weixin.qq.com/connect/qrconnect",
                "token_url" => "https://api.weixin.qq.com/sns/oauth2/access_token",
                "user_info_url" => "https://api.weixin.qq.com/sns/userinfo",
                "scopes" => json_encode(["snsapi_login"]),
                "created_at" => now(),
                "updated_at" => now()
            ],
            [
                "name" => "QQ",
                "identifier" => "qq",
                "icon" => "fab fa-qq",
                "description" => "QQ登录",
                "is_active" => true,
                "auth_url" => "https://graph.qq.com/oauth2.0/authorize",
                "token_url" => "https://graph.qq.com/oauth2.0/token",
                "user_info_url" => "https://graph.qq.com/user/get_user_info",
                "scopes" => json_encode(["get_user_info"]),
                "created_at" => now(),
                "updated_at" => now()
            ],
            [
                "name" => "微博",
                "identifier" => "weibo",
                "icon" => "fab fa-weibo",
                "description" => "微博登录",
                "is_active" => true,
                "auth_url" => "https://api.weibo.com/oauth2/authorize",
                "token_url" => "https://api.weibo.com/oauth2/access_token",
                "user_info_url" => "https://api.weibo.com/2/users/show.json",
                "scopes" => json_encode(["email"]),
                "created_at" => now(),
                "updated_at" => now()
            ],
            [
                "name" => "GitHub",
                "identifier" => "github",
                "icon" => "fab fa-github",
                "description" => "GitHub登录",
                "is_active" => true,
                "auth_url" => "https://github.com/login/oauth/authorize",
                "token_url" => "https://github.com/login/oauth/access_token",
                "user_info_url" => "https://api.github.com/user",
                "scopes" => json_encode(["user:email"]),
                "created_at" => now(),
                "updated_at" => now()
            ],
            [
                "name" => "Google",
                "identifier" => "google",
                "icon" => "fab fa-google",
                "description" => "Google登录",
                "is_active" => true,
                "auth_url" => "https://accounts.google.com/o/oauth2/auth",
                "token_url" => "https://oauth2.googleapis.com/token",
                "user_info_url" => "https://www.googleapis.com/oauth2/v3/userinfo",
                "scopes" => json_encode(["profile", "email"]),
                "created_at" => now(),
                "updated_at" => now()
            ],
            [
                "name" => "飞书",
                "identifier" => "feishu",
                "icon" => "fas fa-feather-alt",
                "description" => "飞书登录",
                "is_active" => true,
                "auth_url" => "https://open.feishu.cn/open-apis/authen/v1/index",
                "token_url" => "https://open.feishu.cn/open-apis/authen/v1/access_token",
                "user_info_url" => "https://open.feishu.cn/open-apis/authen/v1/user_info",
                "scopes" => json_encode(["contact:user.base"]),
                "created_at" => now(),
                "updated_at" => now()
            ],
            [
                "name" => "钉钉",
                "identifier" => "dingtalk",
                "icon" => "fas fa-d",
                "description" => "钉钉登录",
                "is_active" => true,
                "auth_url" => "https://oapi.dingtalk.com/connect/qrconnect",
                "token_url" => "https://oapi.dingtalk.com/sns/gettoken",
                "user_info_url" => "https://oapi.dingtalk.com/sns/getuserinfo",
                "scopes" => json_encode(["snsapi_login"]),
                "created_at" => now(),
                "updated_at" => now()
            ]
        ]);
    }

    /**
     * 回滚迁移
     * 
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("oauth_logs");
        Schema::dropIfExists("oauth_user_accounts");
        Schema::dropIfExists("oauth_providers");
    }
};
