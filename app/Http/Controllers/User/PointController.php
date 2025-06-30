<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\Membership\PointService;
use Illuminate\Http\Request;

class PointController extends Controller
{
    /**
     * 积分服务
     *
     * @var PointService
     */
    protected $pointService;

    /**
     * 创建控制器实例
     *
     * @param PointService $pointService
     * @return void
     */
    public function __construct(PointService $pointService)
    {
        $this->pointService = $pointService;
        $this->middleware("auth");
    }

    /**
     * 显示用户积分页面
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $pointStats = $this->pointService->getPointStats($user);
        $pointHistory = $this->pointService->getPointHistory($user, 10);
        
        return view("user.points.index", [
            "pointStats" => $pointStats,
            "pointHistory" => $pointHistory,
        ]);
    }
    
    /**
     * 获取更多积分历史
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function history(Request $request)
    {
        $request->validate([
            "offset" => "required|integer|min:0",
            "limit" => "integer|min:1|max:50",
        ]);
        
        $user = $request->user();
        $limit = $request->input("limit", 10);
        $offset = $request->input("offset", 0);
        
        $history = $this->pointService->getPointHistory($user, $limit, $offset);
        
        return response()->json([
            "code" => 0,
            "message" => "获取成功",
            "data" => $history,
        ]);
    }
    
    /**
     * 显示用户积分兑换页面
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function exchange(Request $request)
    {
        $user = $request->user();
        $currentPoints = $this->pointService->getCurrentPoints($user);
        
        // 获取可兑换的项目列表（例如，优惠券、会员天数等）
        // 这里需要根据实际业务逻辑实现
        $exchangeItems = [
            // 示例数据
            [
                "id" => 1,
                "name" => "会员1天",
                "points" => 100,
                "description" => "兑换1天会员时长",
            ],
            [
                "id" => 2,
                "name" => "8折优惠券",
                "points" => 200,
                "description" => "兑换一张8折优惠券，适用于任何套餐",
            ],
        ];
        
        return view("user.points.exchange", [
            "currentPoints" => $currentPoints,
            "exchangeItems" => $exchangeItems,
        ]);
    }
    
    /**
     * 处理积分兑换请求
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function doExchange(Request $request)
    {
        $request->validate([
            "item_id" => "required|integer|min:1",
        ]);
        
        $user = $request->user();
        $itemId = $request->input("item_id");
        
        // 查找兑换项目
        // 这里需要根据实际业务逻辑实现
        $item = null;
        $exchangeItems = [
            // 示例数据，与上面保持一致
            [
                "id" => 1,
                "name" => "会员1天",
                "points" => 100,
                "description" => "兑换1天会员时长",
            ],
            [
                "id" => 2,
                "name" => "8折优惠券",
                "points" => 200,
                "description" => "兑换一张8折优惠券，适用于任何套餐",
            ],
        ];
        
        foreach ($exchangeItems as $exchangeItem) {
            if ($exchangeItem["id"] == $itemId) {
                $item = $exchangeItem;
                break;
            }
        }
        
        if (!$item) {
            return back()->with("error", "兑换项目不存在");
        }
        
        // 检查积分是否足够
        if (!$this->pointService->hasEnoughPoints($user, $item["points"])) {
            return back()->with("error", "积分不足，无法兑换");
        }
        
        // 扣减积分
        $result = $this->pointService->deductPoints(
            $user,
            $item["points"],
            "exchange",
            "兑换{$item["name"]}",
            $item["id"],
            "exchange_item"
        );
        
        if (!$result) {
            return back()->with("error", "兑换失败，请稍后再试");
        }
        
        // 处理兑换逻辑
        // 这里需要根据实际业务逻辑实现
        
        return redirect()->route("user.points.index")->with("status", "兑换成功");
    }
}
