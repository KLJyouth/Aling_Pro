<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * 创建新的控制器实例
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware("auth");
    }

    /**
     * 显示订单列表
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // 获取订单列表
        $query = $user->orders()->latest();
        
        // 筛选订单类型
        if ($request->has("type")) {
            $query->where("order_type", $request->type);
        }
        
        // 筛选订单状态
        if ($request->has("status")) {
            $query->where("status", $request->status);
        }
        
        // 分页
        $orders = $query->paginate(10);
        
        return view("orders.index", compact("orders"));
    }

    /**
     * 显示订单详情
     *
     * @param  int  $id
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show($id)
    {
        $user = Auth::user();
        
        // 获取订单
        $order = Order::where("id", $id)
            ->where("user_id", $user->id)
            ->first();
        
        if (!$order) {
            return redirect()->route("orders")->with("error", "订单不存在");
        }
        
        return view("orders.show", compact("order"));
    }
}
