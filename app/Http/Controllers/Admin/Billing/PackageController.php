<?php

namespace App\Http\Controllers\Admin\Billing;

use App\Http\Controllers\Controller;
use App\Models\Billing\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PackageController extends Controller
{
    /**
     * 显示套餐列表
     *
     * @param Request \
     * @return \Illuminate\View\View
     */
    public function index(Request \)
    {
        \ = Package::query();

        // 筛选条件
        if (\->has('type') && \->type) {
            \->where('type', \->type);
        }

        if (\->has('status') && \->status) {
            \->where('status', \->status);
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

        return view('admin.billing.packages.index', compact('packages'));
    }
}
