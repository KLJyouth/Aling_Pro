<?php

namespace App\Http\Controllers\Admin\Billing;

use App\Http\Controllers\Controller;
use App\Models\Billing\Package;
use App\Models\Billing\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * 显示商品列表
     *
     * @param Request \
     * @return \Illuminate\View\View
     */
    public function index(Request \)
    {
        \ = Product::with('package');

        // 筛选条件
        if (\->has('package_id') && \->package_id) {
            \->where('package_id', \->package_id);
        }

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
        \ = Package::pluck('name', 'id');

        return view('admin.billing.products.index', compact('products', 'packages'));
    }
}
