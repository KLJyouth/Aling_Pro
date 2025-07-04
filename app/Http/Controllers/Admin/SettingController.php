<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SettingService;
use App\Models\Setting;
use Illuminate\Support\Facades\Validator;

/**
 * 网站设置控制器
 * 
 * 处理网站设置相关的请求
 */
class SettingController extends Controller
{
    /**
     * 设置服务
     *
     * @var SettingService
     */
    protected $settingService;
    
    /**
     * 构造函数
     *
     * @param SettingService $settingService
     */
    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
        $this->middleware(["auth", "role:admin"]);
    }
    
    /**
     * 显示设置首页
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $groups = Setting::getAllGroups();
        
        return view("admin.settings.index", compact("groups"));
    }
    
    /**
     * 显示分组设置
     *
     * @param string $group
     * @return \Illuminate\View\View
     */
    public function showGroup($group)
    {
        $settings = Setting::where("group", $group)->orderBy("key")->get();
        
        if ($settings->isEmpty()) {
            abort(404, "设置分组不存在");
        }
        
        return view("admin.settings.group", compact("group", "settings"));
    }
    
    /**
     * 保存分组设置
     *
     * @param Request $request
     * @param string $group
     * @return \Illuminate\Http\RedirectResponse
     */
    public function saveGroup(Request $request, $group)
    {
        $settings = Setting::where("group", $group)->get();
        $rules = [];
        
        // 构建验证规则
        foreach ($settings as $setting) {
            switch ($setting->type) {
                case "integer":
                    $rules[$setting->key] = "nullable|integer";
                    break;
                case "float":
                    $rules[$setting->key] = "nullable|numeric";
                    break;
                case "boolean":
                    $rules[$setting->key] = "nullable|boolean";
                    break;
                case "array":
                case "json":
                    $rules[$setting->key] = "nullable|array";
                    break;
                default:
                    $rules[$setting->key] = "nullable|string";
            }
        }
        
        // 验证请求
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // 保存设置
        $data = $request->only(array_keys($rules));
        $this->settingService->saveGroup($group, $data);
        
        return redirect()->route("admin.settings.group", $group)
            ->with("success", "设置保存成功");
    }
    
    /**
     * 显示创建设置表单
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $groups = Setting::getAllGroups();
        
        return view("admin.settings.create", compact("groups"));
    }
    
    /**
     * 存储新设置
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // 验证请求
        $validator = Validator::make($request->all(), [
            "key" => "required|string|max:255|unique:settings,key",
            "value" => "nullable",
            "group" => "required|string|max:255",
            "type" => "required|in:string,integer,float,boolean,array,json",
            "description" => "nullable|string|max:255",
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // 处理值
        $value = $request->input("value");
        
        switch ($request->input("type")) {
            case "integer":
                $value = (int) $value;
                break;
            case "float":
                $value = (float) $value;
                break;
            case "boolean":
                $value = (bool) $value;
                break;
            case "array":
            case "json":
                if (is_string($value)) {
                    $value = json_decode($value, true) ?: [];
                }
                break;
        }
        
        // 创建设置
        $this->settingService->set(
            $request->input("key"),
            $value,
            $request->input("group"),
            $request->input("type"),
            $request->input("description"),
            false
        );
        
        return redirect()->route("admin.settings.index")
            ->with("success", "设置创建成功");
    }
    
    /**
     * 显示编辑设置表单
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $setting = Setting::findOrFail($id);
        $groups = Setting::getAllGroups();
        
        return view("admin.settings.edit", compact("setting", "groups"));
    }
    
    /**
     * 更新设置
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $setting = Setting::findOrFail($id);
        
        // 验证请求
        $validator = Validator::make($request->all(), [
            "key" => "required|string|max:255|unique:settings,key," . $id,
            "value" => "nullable",
            "group" => "required|string|max:255",
            "type" => "required|in:string,integer,float,boolean,array,json",
            "description" => "nullable|string|max:255",
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // 处理值
        $value = $request->input("value");
        
        switch ($request->input("type")) {
            case "integer":
                $value = (int) $value;
                break;
            case "float":
                $value = (float) $value;
                break;
            case "boolean":
                $value = (bool) $value;
                break;
            case "array":
            case "json":
                if (is_string($value)) {
                    $value = json_decode($value, true) ?: [];
                }
                break;
        }
        
        // 更新设置
        $setting->key = $request->input("key");
        $setting->group = $request->input("group");
        $setting->type = $request->input("type");
        $setting->description = $request->input("description");
        $setting->typed_value = $value;
        $setting->save();
        
        // 更新缓存
        $this->settingService->clearCache();
        
        return redirect()->route("admin.settings.index")
            ->with("success", "设置更新成功");
    }
    
    /**
     * 删除设置
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $setting = Setting::findOrFail($id);
        
        // 系统设置不允许删除
        if ($setting->is_system) {
            return redirect()->back()
                ->with("error", "系统设置不能删除");
        }
        
        // 删除设置
        $key = $setting->key;
        $setting->delete();
        
        // 清除缓存
        $this->settingService->delete($key);
        
        return redirect()->route("admin.settings.index")
            ->with("success", "设置删除成功");
    }
    
    /**
     * 清除设置缓存
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clearCache()
    {
        $this->settingService->clearCache();
        
        return redirect()->route("admin.settings.index")
            ->with("success", "设置缓存已清除");
    }
    
    /**
     * 初始化系统设置
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function initSystemSettings()
    {
        $this->settingService->initSystemSettings();
        
        return redirect()->route("admin.settings.index")
            ->with("success", "系统设置已初始化");
    }
}
