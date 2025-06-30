<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * �����µĿ�����ʵ��
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware("auth");
    }

    /**
     * ��ʾ�����б�
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // ��ȡ�����б�
        $query = $user->orders()->latest();
        
        // ɸѡ��������
        if ($request->has("type")) {
            $query->where("order_type", $request->type);
        }
        
        // ɸѡ����״̬
        if ($request->has("status")) {
            $query->where("status", $request->status);
        }
        
        // ��ҳ
        $orders = $query->paginate(10);
        
        return view("orders.index", compact("orders"));
    }

    /**
     * ��ʾ��������
     *
     * @param  int  $id
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show($id)
    {
        $user = Auth::user();
        
        // ��ȡ����
        $order = Order::where("id", $id)
            ->where("user_id", $user->id)
            ->first();
        
        if (!$order) {
            return redirect()->route("orders")->with("error", "����������");
        }
        
        return view("orders.show", compact("order"));
    }
}
