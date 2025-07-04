<?php

namespace App\Http\Controllers\Admin\Membership;

use App\Http\Controllers\Controller;
use App\Models\Membership\MembershipLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MembershipLevelController extends Controller
{
    /**
     * 显示会员等级列表
     *
     * @param Request \
     * @return \Illuminate\View\View
     */
    public function index(Request \)
    {
        \ = MembershipLevel::query();

        // 筛选条件
        if (\->has('status') && \->status) {
            \->where('status', \->status);
        }

        if (\->has('is_featured') && \->is_featured !== null) {
            \->where('is_featured', (bool)\->is_featured);
        }

        if (\->has('search') && \->search) {
            \ = \->search;
            \->where(function (\) use (\) {
                \->where('name', 'like', \
%
\$search
%\)
                  ->orWhere('code', 'like', \%
\$search
%\)
                  ->orWhere('description', 'like', \%
\$search
%\);
            });
        }

        // 排序
        \ = \->input('sort', 'sort_order');
        \ = \->input('order', 'asc');
        \->orderBy(\, \);

        \ = \->paginate(10);

        return view('admin.membership.levels.index', compact('membershipLevels'));
    }
}
