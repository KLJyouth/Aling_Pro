<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Services\AI\AIAgentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AIAgentController extends Controller
{
    protected \;

    /**
     * 构造函数
     *
     * @param AIAgentService \
     */
    public function __construct(AIAgentService \)
    {
        \->aiAgentService = \;
    }

    /**
     * 显示智能体列表
     *
     * @param Request \
     * @return \Illuminate\View\View
     */
    public function index(Request \)
    {
        \ = \->aiAgentService->getAllAgents();
        
        return view('admin.ai.agents.index', [
            'agents' => \
        ]);
    }

    /**
     * 显示创建智能体表单
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.ai.agents.create');
    }

    /**
     * 存储新的智能体
     *
     * @param Request \
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request \)
    {
        \ = Validator::make(\->all(), [
            'code' => 'required|string|max:50|unique:ai_agents,code',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'provider' => 'required|string|max:50',
            'version' => 'nullable|string|max:50',
            'logo_url' => 'nullable|string|max:255',
            'capabilities' => 'nullable|array',
            'parameters' => 'nullable|array',
            'config' => 'nullable|array',
            'api_endpoint' => 'nullable|string|max:255',
            'requires_auth' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer'
        ]);

        if (\->fails()) {
            return redirect()
                ->route('admin.ai.agents.create')
                ->withErrors(\)
                ->withInput();
        }

        \ = \->validated();
        
        // 设置默认值
        \['requires_auth'] = \->has('requires_auth');
        \['is_active'] = \->has('is_active');
        \['sort_order'] = \['sort_order'] ?? 0;

        \->aiAgentService->createAgent(\);

        return redirect()
            ->route('admin.ai.agents.index')
            ->with('success', '智能体创建成功');
    }

    /**
     * 显示指定的智能体
     *
     * @param int \
     * @return \Illuminate\View\View
     */
    public function show(\)
    {
        \ = \->aiAgentService->getAgent(\);
        
        if (!\) {
            return redirect()
                ->route('admin.ai.agents.index')
                ->with('error', '找不到指定的智能体');
        }

        return view('admin.ai.agents.show', [
            'agent' => \
        ]);
    }

    /**
     * 显示编辑智能体表单
     *
     * @param int \
     * @return \Illuminate\View\View
     */
    public function edit(\)
    {
        \ = \->aiAgentService->getAgent(\);
        
        if (!\) {
            return redirect()
                ->route('admin.ai.agents.index')
                ->with('error', '找不到指定的智能体');
        }

        // 解析JSON字段
        \->capabilities = json_decode(\->capabilities, true);
        \->parameters = json_decode(\->parameters, true);
        \->config = json_decode(\->config, true);

        return view('admin.ai.agents.edit', [
            'agent' => \
        ]);
    }

    /**
     * 更新指定的智能体
     *
     * @param Request \
     * @param int \
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request \, \)
    {
        \ = \->aiAgentService->getAgent(\);
        
        if (!\) {
            return redirect()
                ->route('admin.ai.agents.index')
                ->with('error', '找不到指定的智能体');
        }

        \ = Validator::make(\->all(), [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'provider' => 'required|string|max:50',
            'version' => 'nullable|string|max:50',
            'logo_url' => 'nullable|string|max:255',
            'capabilities' => 'nullable|array',
            'parameters' => 'nullable|array',
            'config' => 'nullable|array',
            'api_endpoint' => 'nullable|string|max:255',
            'requires_auth' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer'
        ]);

        if (\->fails()) {
            return redirect()
                ->route('admin.ai.agents.edit', \)
                ->withErrors(\)
                ->withInput();
        }

        \ = \->validated();
        
        // 设置默认值
        \['requires_auth'] = \->has('requires_auth');
        \['is_active'] = \->has('is_active');
        \['sort_order'] = \['sort_order'] ?? 0;

        \->aiAgentService->updateAgent(\, \);

        return redirect()
            ->route('admin.ai.agents.index')
            ->with('success', '智能体更新成功');
    }

    /**
     * 删除指定的智能体
     *
     * @param int \
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(\)
    {
        \ = \->aiAgentService->getAgent(\);
        
        if (!\) {
            return redirect()
                ->route('admin.ai.agents.index')
                ->with('error', '找不到指定的智能体');
        }

        \->aiAgentService->deleteAgent(\);

        return redirect()
            ->route('admin.ai.agents.index')
            ->with('success', '智能体删除成功');
    }

    /**
     * 切换智能体状态
     *
     * @param Request \
     * @param int \
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggle(Request \, \)
    {
        \ = \->aiAgentService->getAgent(\);
        
        if (!\) {
            return redirect()
                ->route('admin.ai.agents.index')
                ->with('error', '找不到指定的智能体');
        }

        \ = \->input('active', !\->is_active);
        \->aiAgentService->toggleAgent(\, \);

        return redirect()
            ->route('admin.ai.agents.index')
            ->with('success', '智能体状态已更新');
    }

    /**
     * 测试智能体连接
     *
     * @param Request \
     * @param int \
     * @return \Illuminate\Http\JsonResponse
     */
    public function testConnection(Request \, \)
    {
        \ = \->aiAgentService->getAgent(\);
        
        if (!\) {
            return response()->json([
                'success' => false,
                'message' => '找不到指定的智能体'
            ]);
        }

        \ = \->input('config', []);
        
        \ = \->aiAgentService->testAgentConnection(\->code, \);

        return response()->json(\);
    }

    /**
     * 与智能体对话
     *
     * @param Request \
     * @return \Illuminate\Http\JsonResponse
     */
    public function chat(Request \)
    {
        \ = Validator::make(\->all(), [
            'agent_code' => 'required|string',
            'message' => 'required|string',
            'session_id' => 'nullable|string',
            'options' => 'nullable|array'
        ]);

        if (\->fails()) {
            return response()->json([
                'success' => false,
                'message' => '参数验证失败',
                'errors' => \->errors()
            ], 422);
        }

        \ = \->input('agent_code');
        \ = \->input('message');
        \ = \->input('session_id');
        \ = \->input('options', []);

        \ = \->aiAgentService->chatWithAgent(\, \, \, \);

        return response()->json(\);
    }

    /**
     * 执行智能体任务
     *
     * @param Request \
     * @return \Illuminate\Http\JsonResponse
     */
    public function executeTask(Request \)
    {
        \ = Validator::make(\->all(), [
            'agent_code' => 'required|string',
            'task_type' => 'required|string',
            'task_data' => 'required|array'
        ]);

        if (\->fails()) {
            return response()->json([
                'success' => false,
                'message' => '参数验证失败',
                'errors' => \->errors()
            ], 422);
        }

        \ = \->input('agent_code');
        \ = \->input('task_type');
        \ = \->input('task_data');

        \ = \->aiAgentService->executeAgentTask(\, \, \);

        return response()->json(\);
    }
}
