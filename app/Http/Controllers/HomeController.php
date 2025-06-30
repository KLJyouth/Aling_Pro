<?php

namespace App\Http\Controllers;

use App\Models\MembershipLevel;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * ��ʾ��ҳ
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // ��ȡ��Ա�ȼ���Ϣ
        $membershipLevels = MembershipLevel::where("status", "active")
            ->orderBy("sort_order")
            ->take(3)
            ->get();
            
        return view("welcome", compact("membershipLevels"));
    }
}
