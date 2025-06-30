<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\Membership\ReferralService;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    /**
     * 推荐服务
     *
     * @var ReferralService
     */
    protected $referralService;

    /**
     * 创建控制器实例
     *
     * @param ReferralService $referralService
     * @return void
     */
    public function __construct(ReferralService $referralService)
    {
        $this->referralService = $referralService;
        $this->middleware("auth");
    }

    /**
     * 显示用户推荐页面
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // 确保用户有推荐码
        $referralCode = $this->referralService->generateReferralCode($user);
        
        // 获取用户的推荐列表
        $referrals = $this->referralService->getUserReferrals($user);
        
        // 生成推荐链接
        $referralLink = url("/register") . "?ref=" . $referralCode;
        
        return view("user.referrals.index", [
            "referralCode" => $referralCode,
            "referralLink" => $referralLink,
            "referrals" => $referrals,
        ]);
    }

    /**
     * 处理推荐注册
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processRegisterReferral(Request $request)
    {
        // 注意：这个方法通常会在注册控制器中调用，而不是直接暴露为路由
        // 这里仅作为示例
        
        $referralCode = $request->input("ref");
        $user = $request->user(); // 这里假设用户已经注册并登录
        
        if (!$referralCode) {
            return back();
        }
        
        // 处理推荐
        $result = $this->referralService->processReferral($user, $referralCode);
        
        if ($result) {
            return back()->with("status", "推荐已记录，感谢您使用推荐码！");
        }
        
        return back();
    }

    /**
     * 获取更多推荐列表
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function moreReferrals(Request $request)
    {
        $request->validate([
            "offset" => "required|integer|min:0",
            "limit" => "integer|min:1|max:50",
        ]);
        
        $user = $request->user();
        $limit = $request->input("limit", 10);
        $offset = $request->input("offset", 0);
        
        $referrals = $this->referralService->getUserReferrals($user, $limit, $offset);
        
        return response()->json([
            "code" => 0,
            "message" => "获取成功",
            "data" => $referrals,
        ]);
    }
}
