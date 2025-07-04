<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Services\AI\AIModelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AISettingController extends Controller
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
     * 显示AI接口设置页面
     *
     * @param Request \
     * @return \Illuminate\View\View
     */
    public function index(Request \)
    {
        \ = \->input('group', 'general');
        \ = \->aiModelService->getSettingsByGroup(\);
        
        // 获取所有分组
        \ = \->aiModelService->getAllSettings()
            ->pluck('group')
            ->unique()
            ->values()
            ->toArray();
        
        return view('admin.ai.settings.index', [
            'settings' => \,
            'groups' => \,
            'currentGroup' => \
        ]);
    }

    /**
     * 更新AI接口设置
     *
     * @param Request \
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request \)
    {
        \ = \->input('settings', []);
        \ = \->input('group', 'general');
        
        foreach (\ as \ => \) {
            \->aiModelService->updateSetting(\, \, \);
        }

        // 清除缓存
        \->aiModelService->clearSettingsCache();
        
        return redirect()
            ->route('admin.ai.settings.index', ['group' => \])
            ->with('success', 'AI接口设置已更新');
    }

    /**
     * 显示创建设置表单
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // 获取所有分组
        \ = \->aiModelService->getAllSettings()
            ->pluck('group')
            ->unique()
            ->values()
            ->toArray();
        
        return view('admin.ai.settings.create', [
            'groups' => \
        ]);
    }

    /**
     * 存储新的设置
     *
     * @param Request \
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request \)
    {
        \ = Validator::make(\->all(), [
            'key' => 'required|string|max:100|unique:ai_interface_settings,key',
            'value' => 'nullable|string',
            'group' => 'required|string|max:50',
            'description' => 'nullable|string'
        ]);

        if (\->fails()) {
            return redirect()
                ->route('admin.ai.settings.create')
                ->withErrors(\)
                ->withInput();
        }

        \ = \->validated();
        
        \->aiModelService->updateSetting(
            \['key'], 
            \['value'], 
            \['group'], 
            \['description']
        );

        // 清除缓存
        \->aiModelService->clearSettingsCache();

        return redirect()
            ->route('admin.ai.settings.index', ['group' => \['group']])
            ->with('success', '设置创建成功');
    }

    /**
     * 删除指定的设置
     *
     * @param int \
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(\)
    {
        \ = \->aiModelService->getAllSettings()
            ->where('id', \)
            ->first();
        
        if (!\) {
            return redirect()
                ->route('admin.ai.settings.index')
                ->with('error', '找不到指定的设置');
        }

        // 系统设置不允许删除
        if (\->is_system) {
            return redirect()
                ->route('admin.ai.settings.index', ['group' => \->group])
                ->with('error', '系统设置不允许删除');
        }

        // 删除设置
        \ = \->group;
        
        // 这里需要实现删除方法，或者通过DB::table直接删除
        \ = \DB::table('ai_interface_settings')
            ->where('id', \)
            ->delete();

        // 清除缓存
        \->aiModelService->clearSettingsCache(\->key);

        return redirect()
            ->route('admin.ai.settings.index', ['group' => \])
            ->with('success', '设置删除成功');
    }

    /**
     * 显示使用统计页面
     *
     * @param Request \
     * @return \Illuminate\View\View
     */
    public function usageStats(Request \)
    {
        \ = \->input('period', 'month');
        \ = \->input('provider_id');
        
        \ = \->aiModelService->getUsageStatistics(\, \);
        
        // 获取所有提供商
        \ = \->aiModelService->getAllProviders();
        
        return view('admin.ai.settings.usage-stats', [
            'statistics' => \,
            'providers' => \,
            'selectedProviderId' => \,
            'period' => \
        ]);
    }

    /**
     * 清除AI接口缓存
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clearCache()
    {
        \->aiModelService->clearSettingsCache();
        
        return redirect()
            ->route('admin.ai.settings.index')
            ->with('success', 'AI接口缓存已清除');
    }
}
