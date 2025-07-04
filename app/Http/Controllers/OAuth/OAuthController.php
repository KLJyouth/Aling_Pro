<?php

namespace App\Http\Controllers\OAuth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\OAuth\OAuthService;
use App\Models\OAuth\Provider;
use App\Models\OAuth\UserAccount;
use App\Models\OAuth\OAuthLog;
use Illuminate\Support\Facades\Auth;

class OAuthController extends Controller
{
    protected $oauthService;
    
    /**
     * 构造函数
     *
     * @param OAuthService $oauthService
     */
    public function __construct(OAuthService $oauthService)
    {
        $this->oauthService = $oauthService;
    }
    
    /**
     * 重定向到第三方授权页面
     *
     * @param Request $request
     * @param string $provider
     * @return \Illuminate\Http\Response
     */
    public function redirect(Request $request, $provider)
    {
        $authUrl = $this->oauthService->getAuthorizationUrl($provider);
        
        if (!$authUrl) {
            return redirect()->route("login")
                ->with("error", "未找到或未启用该登录方式");
        }
        
        // 保存重定向URL
        if ($request->has("redirect")) {
            session()->put("oauth_redirect", $request->input("redirect"));
        }
        
        return redirect($authUrl);
    }
    
    /**
     * 处理第三方授权回调
     *
     * @param Request $request
     * @param string $provider
     * @return \Illuminate\Http\Response
     */
    public function callback(Request $request, $provider)
    {
        if ($request->has("error") || !$request->has("code")) {
            $errorMessage = $request->input("error_description") ?: $request->input("error") ?: "授权失败";
            
            OAuthLog::failure("login", $errorMessage, null, null, $request->all());
            
            return redirect()->route("login")
                ->with("error", "第三方登录失败: " . $errorMessage);
        }
        
        $code = $request->input("code");
        $result = $this->oauthService->handleCallback($provider, $code);
        
        if (!$result["success"]) {
            return redirect()->route("login")
                ->with("error", "第三方登录失败: " . $result["message"]);
        }
        
        // 登录用户
        Auth::login($result["user"]);
        
        // 检查是否有重定向URL
        $redirectUrl = session("oauth_redirect");
        session()->forget("oauth_redirect");
        
        if ($redirectUrl) {
            return redirect($redirectUrl);
        }
        
        return redirect()->intended("/");
    }
    
    /**
     * 解除账号关联
     *
     * @param Request $request
     * @param int $providerId
     * @return \Illuminate\Http\Response
     */
    public function unlink(Request $request, $providerId)
    {
        $result = $this->oauthService->unlinkAccount(Auth::id(), $providerId);
        
        if (!$result["success"]) {
            return redirect()->back()
                ->with("error", $result["message"]);
        }
        
        return redirect()->back()
            ->with("success", $result["message"]);
    }
}
