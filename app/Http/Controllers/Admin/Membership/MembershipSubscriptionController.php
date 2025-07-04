<?php

namespace App\Http\Controllers\Admin\Membership;

use App\Http\Controllers\Controller;
use App\Models\Membership\MembershipLevel;
use App\Models\Membership\MembershipSubscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MembershipSubscriptionController extends Controller
{
    /**
     * 显示会员订阅列表
     *
     * @param Request \
     * @return \Illuminate\View\View
     */
    public function index(Request \)
    {
        \ = MembershipSubscription::with(['user', 'membershipLevel']);

        // 筛选条件
        if (\->has('membership_level_id') && \->membership_level_id) {
            \->where('membership_level_id', \->membership_level_id);
        }

        if (\->has('status') && \->status) {
            \->where('status', \->status);
        }

        if (\->has('auto_renew') && \->auto_renew !== null) {
            \->where('auto_renew', (bool)\->auto_renew);
        }

        if (\->has('search') && \->search) {
            \ = \->search;
            \->whereHas('user', function (\) use (\) {
                \->where('name', 'like', \
%
\$search
%\)
                  ->orWhere('email', 'like', \%
\$search
%\);
            })->orWhere('subscription_no', 'like', \%
\$search
%\);
        }

        // 排序
        \ = \->input('sort', 'created_at');
        \ = \->input('order', 'desc');
        \->orderBy(\, \);

        \ = \->paginate(10);
        \ = MembershipLevel::pluck('name', 'id');

        return view('admin.membership.subscriptions.index', compact('subscriptions', 'membershipLevels'));
    }
}
