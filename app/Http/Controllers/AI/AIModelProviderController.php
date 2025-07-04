<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Services\AI\AIModelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AIModelProviderController extends Controller
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
     * 显示模型提供商列表
     *
     * @param Request \
     * @return \Illuminate\View\View
     */
    public function index(Request \)
    {
        \ = \->aiModelService->getAllProviders();
        
        return view('admin.ai.providers.index', [
            'providers' => \
        ]);
    }

    /**
     * 显示创建模型提供商表单
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.ai.providers.create');
    }

    /**
     * 存储新的模型提供商
     *
     * @param Request \
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request \)
    {
        \ = Validator::make(\->all(), [
            'code' => 'required|string|max:50|unique:ai_model_providers,code',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'logo_url' => 'nullable|string|max:255',
            'api_base_url' => 'nullable|string|max:255',
            'capabilities' => 'nullable|array',
            'config_schema' => 'nullable|array',
            'config' => 'nullable|array',
            'is_active' => 'boolean',
            'is_official' => 'boolean',
            'sort_order' => 'integer'
        ]);

        if (\->fails()) {
            return redirect()
                ->route('admin.ai.providers.create')
                ->withErrors(\)
                ->withInput();
        }

        \ = \->validated();
        
        // 设置默认值
        \['is_active'] = \->has('is_active');
        \['is_official'] = \->has('is_official');
        \['sort_order'] = \['sort_order'] ?? 0;

        \->aiModelService->createProvider(\);

        return redirect()
            ->route('admin.ai.providers.index')
            ->with('success', '模型提供商创建成功');
    }

    /**
     * 显示指定的模型提供商
     *
     * @param int \
     * @return \Illuminate\View\View
     */
    public function show(\)
    {
        \ = \->aiModelService->getProvider(\);
        
        if (!\) {
            return redirect()
                ->route('admin.ai.providers.index')
                ->with('error', '找不到指定的模型提供商');
        }

        // 获取提供商的模型
        \ = \->aiModelService->getProviderModels(\);
        
        // 获取API密钥
        \ = \->aiModelService->getAllApiKeys(\);

        return view('admin.ai.providers.show', [
            'provider' => \,
            'models' => \,
            'apiKeys' => \
        ]);
    }

    /**
     * 显示编辑模型提供商表单
     *
     * @param int \
     * @return \Illuminate\View\View
     */
    public function edit(\)
    {
        \ = \->aiModelService->getProvider(\);
        
        if (!\) {
            return redirect()
                ->route('admin.ai.providers.index')
                ->with('error', '找不到指定的模型提供商');
        }

        // 解析JSON字段
        \->capabilities = json_decode(\->capabilities, true);
        \->config_schema = json_decode(\->config_schema, true);
        \->config = json_decode(\->config, true);

        return view('admin.ai.providers.edit', [
            'provider' => \
        ]);
    }

    /**
     * 更新指定的模型提供商
     *
     * @param Request \
     * @param int \
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request \, \)
    {
        \ = \->aiModelService->getProvider(\);
        
        if (!\) {
            return redirect()
                ->route('admin.ai.providers.index')
                ->with('error', '找不到指定的模型提供商');
        }

        \ = Validator::make(\->all(), [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'logo_url' => 'nullable|string|max:255',
            'api_base_url' => 'nullable|string|max:255',
            'capabilities' => 'nullable|array',
            'config_schema' => 'nullable|array',
            'config' => 'nullable|array',
            'is_active' => 'boolean',
            'is_official' => 'boolean',
            'sort_order' => 'integer'
        ]);

        if (\->fails()) {
            return redirect()
                ->route('admin.ai.providers.edit', \)
                ->withErrors(\)
                ->withInput();
        }

        \ = \->validated();
        
        // 设置默认值
        \['is_active'] = \->has('is_active');
        \['is_official'] = \->has('is_official');
        \['sort_order'] = \['sort_order'] ?? 0;

        \->aiModelService->updateProvider(\, \);

        return redirect()
            ->route('admin.ai.providers.index')
            ->with('success', '模型提供商更新成功');
    }

    /**
     * 删除指定的模型提供商
     *
     * @param int \
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(\)
    {
        \ = \->aiModelService->getProvider(\);
        
        if (!\) {
            return redirect()
                ->route('admin.ai.providers.index')
                ->with('error', '找不到指定的模型提供商');
        }

        \->aiModelService->deleteProvider(\);

        return redirect()
            ->route('admin.ai.providers.index')
            ->with('success', '模型提供商删除成功');
    }

    /**
     * 切换模型提供商状态
     *
     * @param Request \
     * @param int \
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggle(Request \, \)
    {
        \ = \->aiModelService->getProvider(\);
        
        if (!\) {
            return redirect()
                ->route('admin.ai.providers.index')
                ->with('error', '找不到指定的模型提供商');
        }

        \ = \->input('active', !\->is_active);
        \->aiModelService->toggleProvider(\, \);

        return redirect()
            ->route('admin.ai.providers.index')
            ->with('success', '模型提供商状态已更新');
    }

    /**
     * 测试模型提供商API连接
     *
     * @param Request \
     * @param int \
     * @return \Illuminate\Http\JsonResponse
     */
    public function testConnection(Request \, \)
    {
        \ = \->aiModelService->getProvider(\);
        
        if (!\) {
            return response()->json([
                'success' => false,
                'message' => '找不到指定的模型提供商'
            ]);
        }

        \ = \->input('config', json_decode(\->config, true) ?? []);
        
        \ = \->aiModelService->testApiConnection(\->code, \);

        return response()->json(\);
    }
}
