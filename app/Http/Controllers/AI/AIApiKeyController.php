<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Services\AI\AIModelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AIApiKeyController extends Controller
{
    protected \;

    /**
     * 构造函数
     *
     * @param AIModelService \
     */
    public function __construct(AIModelService \)
    {
        \->aiModelService = \;
    }

    /**
     * 显示API密钥列表
     *
     * @param Request \
     * @return \Illuminate\View\View
     */
    public function index(Request \)
    {
        \ = \->input('provider_id');
        \ = \->aiModelService->getAllApiKeys(\);
        
        // 获取所有提供商
        \ = \->aiModelService->getAllProviders();
        
        return view('admin.ai.api-keys.index', [
            'apiKeys' => \,
            'providers' => \,
            'selectedProviderId' => \
        ]);
    }

    /**
     * 显示创建API密钥表单
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // 获取所有提供商
        \ = \->aiModelService->getAllProviders();
        
        return view('admin.ai.api-keys.create', [
            'providers' => \
        ]);
    }

    /**
     * 存储新的API密钥
     *
     * @param Request \
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request \)
    {
        \ = Validator::make(\->all(), [
            'provider_id' => 'required|integer|exists:ai_model_providers,id',
            'name' => 'required|string|max:100',
            'api_key' => 'required|string',
            'api_secret' => 'nullable|string',
            'permissions' => 'nullable|array',
            'monthly_quota' => 'nullable|numeric',
            'expires_at' => 'nullable|date',
            'is_active' => 'boolean'
        ]);

        if (\->fails()) {
            return redirect()
                ->route('admin.ai.api-keys.create')
                ->withErrors(\)
                ->withInput();
        }

        \ = \->validated();
        
        // 设置默认值
        \['is_active'] = \->has('is_active');
        \['used_quota'] = 0;

        \->aiModelService->createApiKey(\);

        return redirect()
            ->route('admin.ai.api-keys.index')
            ->with('success', 'API密钥创建成功');
    }

    /**
     * 显示指定的API密钥
     *
     * @param int \
     * @return \Illuminate\View\View
     */
    public function show(\)
    {
        \ = \->aiModelService->getApiKey(\);
        
        if (!\) {
            return redirect()
                ->route('admin.ai.api-keys.index')
                ->with('error', '找不到指定的API密钥');
        }

        // 获取提供商信息
        \ = \->aiModelService->getProvider(\->provider_id);

        return view('admin.ai.api-keys.show', [
            'apiKey' => \,
            'provider' => \
        ]);
    }

    /**
     * 显示编辑API密钥表单
     *
     * @param int \
     * @return \Illuminate\View\View
     */
    public function edit(\)
    {
        \ = \->aiModelService->getApiKey(\);
        
        if (!\) {
            return redirect()
                ->route('admin.ai.api-keys.index')
                ->with('error', '找不到指定的API密钥');
        }

        // 获取所有提供商
        \ = \->aiModelService->getAllProviders();

        // 解析JSON字段
        \->permissions = json_decode(\->permissions, true);

        return view('admin.ai.api-keys.edit', [
            'apiKey' => \,
            'providers' => \
        ]);
    }

    /**
     * 更新指定的API密钥
     *
     * @param Request \
     * @param int \
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request \, \)
    {
        \ = \->aiModelService->getApiKey(\);
        
        if (!\) {
            return redirect()
                ->route('admin.ai.api-keys.index')
                ->with('error', '找不到指定的API密钥');
        }

        \ = Validator::make(\->all(), [
            'provider_id' => 'required|integer|exists:ai_model_providers,id',
            'name' => 'required|string|max:100',
            'api_key' => 'required|string',
            'api_secret' => 'nullable|string',
            'permissions' => 'nullable|array',
            'monthly_quota' => 'nullable|numeric',
            'expires_at' => 'nullable|date',
            'is_active' => 'boolean'
        ]);

        if (\->fails()) {
            return redirect()
                ->route('admin.ai.api-keys.edit', \)
                ->withErrors(\)
                ->withInput();
        }

        \ = \->validated();
        
        // 设置默认值
        \['is_active'] = \->has('is_active');

        \->aiModelService->updateApiKey(\, \);

        return redirect()
            ->route('admin.ai.api-keys.index')
            ->with('success', 'API密钥更新成功');
    }

    /**
     * 删除指定的API密钥
     *
     * @param int \
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(\)
    {
        \ = \->aiModelService->getApiKey(\);
        
        if (!\) {
            return redirect()
                ->route('admin.ai.api-keys.index')
                ->with('error', '找不到指定的API密钥');
        }

        \->aiModelService->deleteApiKey(\);

        return redirect()
            ->route('admin.ai.api-keys.index')
            ->with('success', 'API密钥删除成功');
    }

    /**
     * 切换API密钥状态
     *
     * @param Request \
     * @param int \
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggle(Request \, \)
    {
        \ = \->aiModelService->getApiKey(\);
        
        if (!\) {
            return redirect()
                ->route('admin.ai.api-keys.index')
                ->with('error', '找不到指定的API密钥');
        }

        \ = \->input('active', !\->is_active);
        \->aiModelService->toggleApiKey(\, \);

        return redirect()
            ->route('admin.ai.api-keys.index')
            ->with('success', 'API密钥状态已更新');
    }

    /**
     * 重置API密钥使用配额
     *
     * @param int \
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resetQuota(\)
    {
        \ = \->aiModelService->getApiKey(\);
        
        if (!\) {
            return redirect()
                ->route('admin.ai.api-keys.index')
                ->with('error', '找不到指定的API密钥');
        }

        \->aiModelService->updateApiKey(\, [
            'used_quota' => 0
        ]);

        return redirect()
            ->route('admin.ai.api-keys.index')
            ->with('success', 'API密钥使用配额已重置');
    }
}
