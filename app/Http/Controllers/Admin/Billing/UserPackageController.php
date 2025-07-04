<?php

namespace App\Http\Controllers\Admin\Billing;

use App\Http\Controllers\Controller;
use App\Models\Billing\Package;
use App\Models\Billing\UserPackage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserPackageController extends Controller
{
    /**
     * 显示用户套餐列表
     *
     * @param Request \
     * @return \Illuminate\View\View
     */
    public function index(Request \)
    {
        \ = UserPackage::with(['user', 'package']);

        // 筛选条件
        if (\->has('user_id') && \->user_id) {
            \->where('user_id', \->user_id);
        }

        if (\->has('package_id') && \->package_id) {
            \->where('package_id', \->package_id);
        }

        if (\->has('status') && \->status) {
            \->where('status', \->status);
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
            })->orWhereHas('package', function (\) use (\) {
                \->where('name', 'like', \%
\$search
%\)
                  ->orWhere('code', 'like', \%
\$search
%\);
            });
        }

        // 排序
        \ = \->input('sort', 'created_at');
        \ = \->input('order', 'desc');
        \->orderBy(\, \);

        \ = \->paginate(10);
        \ = Package::pluck('name', 'id');

        return view('admin.billing.user_packages.index', compact('userPackages', 'packages'));
    }
}
