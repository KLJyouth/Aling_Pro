<?php

namespace App\Http\Controllers;

use App\Models\MembershipLevel;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * 显示首页
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // 获取会员等级信息
        $membershipLevels = MembershipLevel::where("status", "active")
            ->orderBy("sort_order")
            ->take(3)
            ->get();
            
        return view("welcome", compact("membershipLevels"));
    }
}
