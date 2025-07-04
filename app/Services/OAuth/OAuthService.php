<?php

namespace App\Services\OAuth;

use App\Models\OAuth\Provider;
use App\Models\OAuth\UserAccount;
use App\Models\OAuth\OAuthLog;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OAuthService
{
    protected $client;
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->client = new Client([
            "timeout" => 30,
            "connect_timeout" => 10,
            "http_errors" => false,
        ]);
    }
    
    /**
     * 获取授权URL
     *
     * @param string $provider 提供商标识符
     * @return string|null
     */
    public function getAuthorizationUrl($provider)
    {
        $provider = Provider::where("identifier", $provider)
            ->where("is_active", true)
            ->first();
            
        if (!$provider || !$provider->client_id || !$provider->redirect_url) {
            return null;
        }
        
        return $provider->getAuthorizationUrl();
    }
    
    /**
     * 处理OAuth回调
     *
     * @param string $provider 提供商标识符
     * @param string $code 授权码
     * @return array
     */
    public function handleCallback($provider, $code)
    {
        $provider = Provider::where("identifier", $provider)
            ->where("is_active", true)
            ->first();
            
        if (!$provider || !$provider->client_id || !$provider->client_secret) {
            return [
                "success" => false,
                "message" => "提供商未配置或未启用",
            ];
        }
        
        try {
            // 获取访问令牌
            $tokenResponse = $this->getAccessToken($provider, $code);
            
            if (!isset($tokenResponse["access_token"])) {
                OAuthLog::failure("login", "获取访问令牌失败", $provider->id, null, ["code" => $code], $tokenResponse);
                return [
                    "success" => false,
                    "message" => "获取访问令牌失败",
                ];
            }
            
            // 获取用户信息
            $userInfo = $this->getUserInfo($provider, $tokenResponse["access_token"]);
            
            if (!isset($userInfo["id"]) && !isset($userInfo["openid"])) {
                OAuthLog::failure("login", "获取用户信息失败", $provider->id, null, $tokenResponse, $userInfo);
                return [
                    "success" => false,
                    "message" => "获取用户信息失败",
                ];
            }
            
            // 处理用户登录或注册
            $result = $this->handleUserAuthentication($provider, $userInfo, $tokenResponse);
            
            if ($result["success"]) {
                OAuthLog::success(
                    $result["action"], 
                    $provider->id, 
                    $result["user"]->id, 
                    ["code" => $code], 
                    ["user_info" => $userInfo]
                );
            } else {
                OAuthLog::failure(
                    "login", 
                    $result["message"], 
                    $provider->id, 
                    null, 
                    ["code" => $code], 
                    ["user_info" => $userInfo]
                );
            }
            
            return $result;
            
        } catch (\Exception $e) {
            OAuthLog::failure("login", $e->getMessage(), $provider->id);
            return [
                "success" => false,
                "message" => "处理OAuth回调时发生错误: " . $e->getMessage(),
            ];
        }
    }
    
    /**
     * 获取访问令牌
     *
     * @param Provider $provider
     * @param string $code
     * @return array
     */
    protected function getAccessToken(Provider $provider, $code)
    {
        $params = [
            "client_id" => $provider->client_id,
            "client_secret" => $provider->decrypted_client_secret,
            "code" => $code,
            "redirect_uri" => $provider->redirect_url,
            "grant_type" => "authorization_code",
        ];
        
        try {
            $response = $this->client->post($provider->token_url, [
                "form_params" => $params,
                "headers" => [
                    "Accept" => "application/json",
                ],
            ]);
            
            $body = $response->getBody()->getContents();
            
            // 有些API返回的不是JSON格式
            if (strpos($body, "access_token=") !== false) {
                parse_str($body, $result);
                return $result;
            }
            
            return json_decode($body, true) ?: [];
            
        } catch (RequestException $e) {
            return [
                "error" => $e->getMessage(),
            ];
        }
    }
    
    /**
     * 获取用户信息
     *
     * @param Provider $provider
     * @param string $accessToken
     * @return array
     */
    protected function getUserInfo(Provider $provider, $accessToken)
    {
        $headers = [
            "Authorization" => "Bearer " . $accessToken,
            "Accept" => "application/json",
        ];
        
        // 特殊处理微信和QQ
        if ($provider->identifier === "wechat" || $provider->identifier === "qq") {
            return $this->getSpecialProviderUserInfo($provider, $accessToken);
        }
        
        try {
            $response = $this->client->get($provider->user_info_url, [
                "headers" => $headers,
            ]);
            
            $body = $response->getBody()->getContents();
            return json_decode($body, true) ?: [];
            
        } catch (RequestException $e) {
            return [
                "error" => $e->getMessage(),
            ];
        }
    }
    
    /**
     * 获取特殊提供商的用户信息
     *
     * @param Provider $provider
     * @param string $accessToken
     * @return array
     */
    protected function getSpecialProviderUserInfo(Provider $provider, $accessToken)
    {
        if ($provider->identifier === "wechat") {
            // 微信需要使用openid获取用户信息
            try {
                // 先获取openid
                $tokenInfo = json_decode($accessToken, true);
                $openid = $tokenInfo["openid"] ?? null;
                
                if (!$openid) {
                    return ["error" => "无法获取openid"];
                }
                
                $response = $this->client->get($provider->user_info_url, [
                    "query" => [
                        "access_token" => $accessToken,
                        "openid" => $openid,
                    ],
                ]);
                
                $body = $response->getBody()->getContents();
                $userInfo = json_decode($body, true) ?: [];
                $userInfo["id"] = $openid; // 确保有id字段
                
                return $userInfo;
            } catch (RequestException $e) {
                return ["error" => $e->getMessage()];
            }
        } elseif ($provider->identifier === "qq") {
            // QQ需要先获取openid，再获取用户信息
            try {
                // 获取openid
                $response = $this->client->get("https://graph.qq.com/oauth2.0/me", [
                    "query" => [
                        "access_token" => $accessToken,
                        "fmt" => "json",
                    ],
                ]);
                
                $body = $response->getBody()->getContents();
                $openidInfo = json_decode($body, true) ?: [];
                $openid = $openidInfo["openid"] ?? null;
                
                if (!$openid) {
                    return ["error" => "无法获取openid"];
                }
                
                // 获取用户信息
                $response = $this->client->get($provider->user_info_url, [
                    "query" => [
                        "access_token" => $accessToken,
                        "oauth_consumer_key" => $provider->client_id,
                        "openid" => $openid,
                    ],
                ]);
                
                $body = $response->getBody()->getContents();
                $userInfo = json_decode($body, true) ?: [];
                $userInfo["id"] = $openid; // 确保有id字段
                
                return $userInfo;
            } catch (RequestException $e) {
                return ["error" => $e->getMessage()];
            }
        }
        
        return ["error" => "不支持的提供商"];
    }
    
    /**
     * 处理用户认证
     *
     * @param Provider $provider
     * @param array $userInfo
     * @param array $tokenResponse
     * @return array
     */
    protected function handleUserAuthentication(Provider $provider, array $userInfo, array $tokenResponse)
    {
        // 获取提供商用户ID
        $providerUserId = $userInfo["id"] ?? $userInfo["openid"] ?? null;
        
        if (!$providerUserId) {
            return [
                "success" => false,
                "message" => "无法获取提供商用户ID",
            ];
        }
        
        // 查找是否已存在关联账号
        $userAccount = UserAccount::where("provider_id", $provider->id)
            ->where("provider_user_id", $providerUserId)
            ->first();
            
        if ($userAccount) {
            // 已存在关联账号，更新令牌信息
            $userAccount->update([
                "access_token" => $tokenResponse["access_token"],
                "refresh_token" => $tokenResponse["refresh_token"] ?? null,
                "token_expires_at" => isset($tokenResponse["expires_in"]) 
                    ? now()->addSeconds($tokenResponse["expires_in"]) 
                    : null,
                "nickname" => $userInfo["nickname"] ?? $userInfo["name"] ?? null,
                "name" => $userInfo["name"] ?? $userInfo["nickname"] ?? null,
                "email" => $userInfo["email"] ?? null,
                "avatar" => $userInfo["avatar"] ?? $userInfo["avatar_url"] ?? $userInfo["headimgurl"] ?? null,
                "user_data" => $userInfo,
            ]);
            
            return [
                "success" => true,
                "action" => "login",
                "message" => "登录成功",
                "user" => $userAccount->user,
            ];
        }
        
        // 不存在关联账号，检查是否已登录
        if (auth()->check()) {
            // 已登录，关联账号
            $user = auth()->user();
            
            $userAccount = UserAccount::create([
                "user_id" => $user->id,
                "provider_id" => $provider->id,
                "provider_user_id" => $providerUserId,
                "access_token" => $tokenResponse["access_token"],
                "refresh_token" => $tokenResponse["refresh_token"] ?? null,
                "token_expires_at" => isset($tokenResponse["expires_in"]) 
                    ? now()->addSeconds($tokenResponse["expires_in"]) 
                    : null,
                "nickname" => $userInfo["nickname"] ?? $userInfo["name"] ?? null,
                "name" => $userInfo["name"] ?? $userInfo["nickname"] ?? null,
                "email" => $userInfo["email"] ?? null,
                "avatar" => $userInfo["avatar"] ?? $userInfo["avatar_url"] ?? $userInfo["headimgurl"] ?? null,
                "user_data" => $userInfo,
            ]);
            
            return [
                "success" => true,
                "action" => "link",
                "message" => "账号关联成功",
                "user" => $user,
            ];
        }
        
        // 未登录，创建新用户
        DB::beginTransaction();
        
        try {
            // 创建新用户
            $email = $userInfo["email"] ?? null;
            $name = $userInfo["name"] ?? $userInfo["nickname"] ?? "用户" . Str::random(6);
            
            // 如果有邮箱，检查是否已存在
            if ($email) {
                $existingUser = User::where("email", $email)->first();
                
                if ($existingUser) {
                    // 邮箱已存在，关联账号
                    $userAccount = UserAccount::create([
                        "user_id" => $existingUser->id,
                        "provider_id" => $provider->id,
                        "provider_user_id" => $providerUserId,
                        "access_token" => $tokenResponse["access_token"],
                        "refresh_token" => $tokenResponse["refresh_token"] ?? null,
                        "token_expires_at" => isset($tokenResponse["expires_in"]) 
                            ? now()->addSeconds($tokenResponse["expires_in"]) 
                            : null,
                        "nickname" => $userInfo["nickname"] ?? $userInfo["name"] ?? null,
                        "name" => $userInfo["name"] ?? $userInfo["nickname"] ?? null,
                        "email" => $email,
                        "avatar" => $userInfo["avatar"] ?? $userInfo["avatar_url"] ?? $userInfo["headimgurl"] ?? null,
                        "user_data" => $userInfo,
                    ]);
                    
                    DB::commit();
                    
                    return [
                        "success" => true,
                        "action" => "login",
                        "message" => "登录成功",
                        "user" => $existingUser,
                    ];
                }
            }
            
            // 创建新用户
            $user = User::create([
                "name" => $name,
                "email" => $email ?: $provider->identifier . "_" . $providerUserId . "@example.com",
                "password" => Hash::make(Str::random(16)),
                "email_verified_at" => $email ? now() : null, // 如果有邮箱，视为已验证
            ]);
            
            // 创建关联账号
            $userAccount = UserAccount::create([
                "user_id" => $user->id,
                "provider_id" => $provider->id,
                "provider_user_id" => $providerUserId,
                "access_token" => $tokenResponse["access_token"],
                "refresh_token" => $tokenResponse["refresh_token"] ?? null,
                "token_expires_at" => isset($tokenResponse["expires_in"]) 
                    ? now()->addSeconds($tokenResponse["expires_in"]) 
                    : null,
                "nickname" => $userInfo["nickname"] ?? $userInfo["name"] ?? null,
                "name" => $userInfo["name"] ?? $userInfo["nickname"] ?? null,
                "email" => $email,
                "avatar" => $userInfo["avatar"] ?? $userInfo["avatar_url"] ?? $userInfo["headimgurl"] ?? null,
                "user_data" => $userInfo,
            ]);
            
            DB::commit();
            
            return [
                "success" => true,
                "action" => "register",
                "message" => "注册成功",
                "user" => $user,
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return [
                "success" => false,
                "message" => "创建用户失败: " . $e->getMessage(),
            ];
        }
    }
    
    /**
     * 解除账号关联
     *
     * @param int $userId
     * @param int $providerId
     * @return array
     */
    public function unlinkAccount($userId, $providerId)
    {
        $userAccount = UserAccount::where("user_id", $userId)
            ->where("provider_id", $providerId)
            ->first();
            
        if (!$userAccount) {
            return [
                "success" => false,
                "message" => "未找到关联账号",
            ];
        }
        
        // 检查是否只有一个登录方式
        $accountCount = UserAccount::where("user_id", $userId)->count();
        $user = User::find($userId);
        
        // 如果用户没有设置密码且只有一个第三方登录，不允许解除关联
        if (!$user->password && $accountCount <= 1) {
            return [
                "success" => false,
                "message" => "您只有一个登录方式，无法解除关联。请先设置密码",
            ];
        }
        
        // 解除关联
        $provider = $userAccount->provider;
        $userAccount->delete();
        
        OAuthLog::success("unlink", $provider->id, $userId);
        
        return [
            "success" => true,
            "message" => "已解除与" . $provider->name . "的账号关联",
        ];
    }
}
