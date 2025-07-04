<?php

namespace App\Http\Controllers\Admin\Billing;

use App\Http\Controllers\Controller;
use App\Models\Billing\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * 显示订单列表
     *
     * @param Request \
     * @return \Illuminate\View\View
     */
    public function index(Request \)
    {
        \ = Order::with('user');

        // 筛选条件
        if (\->has('status') && \->status) {
            \->where('status', \->status);
        }

        if (\->has('payment_status') && \->payment_status) {
            \->where('payment_status', \->payment_status);
        }

        if (\->has('payment_method') && \->payment_method) {
            \->where('payment_method', \->payment_method);
        }

        if (\->has('user_id') && \->user_id) {
            \->where('user_id', \->user_id);
        }

        if (\->has('date_start') && \->date_start) {
            \->whereDate('created_at', '>=', \->date_start);
        }

        if (\->has('date_end') && \->date_end) {
            \->whereDate('created_at', '<=', \->date_end);
        }

        if (\->has('search') && \->search) {
            \ = \->search;
            \->where(function (\) use (\) {
                \->where('order_no', 'like', \
%
\$search
%\)
                  ->orWhere('transaction_id', 'like', \%
\$search
%\)
                  ->orWhereHas('user', function (\) use (\) {
                      \->where('name', 'like', \%
\$search
%\)
                               ->orWhere('email', 'like', \%
\$search
%\);
                  });
            });
        }

        // 排序
        \ = \->input('sort', 'created_at');
        \ = \->input('order', 'desc');
        \->orderBy(\, \);

        \ = \->paginate(10);

        return view('admin.billing.orders.index', compact('orders'));
    }
}
