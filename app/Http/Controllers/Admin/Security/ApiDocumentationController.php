<?php

namespace App\Http\Controllers\Admin\Security;

use App\Http\Controllers\Controller;
use App\Models\Security\ApiControl\ApiInterface;
use Illuminate\Http\Request;

class ApiDocumentationController extends Controller
{
    /**
     * 显示API文档首页
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // 获取所有接口，按分组组织
        \ = ApiInterface::where('status', 'active')
            ->orderBy('group')
            ->orderBy('sort_order')
            ->get();
            
        // 按分组整理接口
        \ = \->groupBy('group');
        
        return view('admin.security.api.documentation.index', compact('groupedInterfaces'));
    }
    
    /**
     * 显示接口详情
     *
     * @param int \
     * @return \Illuminate\View\View
     */
    public function show(\)
    {
        \ = ApiInterface::with(['parameters', 'responses'])->findOrFail(\);
        
        // 获取相关接口
        \ = ApiInterface::where('group', \->group)
            ->where('id', '!=', \->id)
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->get();
            
        return view('admin.security.api.documentation.show', compact('interface', 'relatedInterfaces'));
    }
}
