<?php

namespace App\Http\Controllers\Admin\Security;

use App\Http\Controllers\Controller;
use App\Models\Security\ApiControl\ApiInterface;
use App\Models\Security\ApiControl\ApiKey;
use App\Models\Security\ApiControl\ApiRequestLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiRequestLogController extends Controller
{
    /**
     * 显示API请求日志列表
     *
     * @param Request \
     * @return \Illuminate\View\View
     */
    public function index(Request \)
    {
        \ = ApiRequestLog::query()->with(['apiKey.user', 'apiInterface']);

        // 筛选
        if (\->has('api_key_id') && !empty(\->api_key_id)) {
            \->where('api_key_id', \->api_key_id);
        }

        if (\->has('api_interface_id') && !empty(\->api_interface_id)) {
            \->where('api_interface_id', \->api_interface_id);
        }

        if (\->has('method') && !empty(\->method)) {
            \->where('method', \->method);
        }

        if (\->has('path') && !empty(\->path)) {
            \->where('path', 'like', '%' . \->path . '%');
        }

        if (\->has('status_code') && !empty(\->status_code)) {
            \->where('status_code', \->status_code);
        }

        if (\->has('ip_address') && !empty(\->ip_address)) {
            \->where('ip_address', 'like', '%' . \->ip_address . '%');
        }

        if (\->has('date_range') && !empty(\->date_range)) {
            \ = explode(' - ', \->date_range);
            if (count(\) == 2) {
                \->whereBetween('created_at', [\[0] . ' 00:00:00', \[1] . ' 23:59:59']);
            }
        }

        // 排序
        \ = \->get('sort', 'created_at');
        \ = \->get('order', 'desc');
        \->orderBy(\, \);

        \ = \->paginate(15);
        
        // 获取API密钥和接口列表，用于筛选
        \ = ApiKey::with('user')->get();
        \ = ApiInterface::all();
        
        return view('admin.security.api.request-logs.index', compact('logs', 'apiKeys', 'apiInterfaces'));
    }

    /**
     * 显示指定的API请求日志
     *
     * @param int \
     * @return \Illuminate\View\View
     */
    public function show(\)
    {
        \ = ApiRequestLog::with(['apiKey.user', 'apiInterface'])->findOrFail(\);
        
        return view('admin.security.api.request-logs.show', compact('log'));
    }
}
